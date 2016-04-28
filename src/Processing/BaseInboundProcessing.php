<?php

namespace Lasallecms\Lasallecmsemail\Processing;

/**
 *
 * Email handling package for the LaSalle Content Management System.
 *
 * Based on the Laravel 5 Framework.
 *
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Email handling package for the LaSalle Content Management System
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecms\Lasallecmsemail\Repositories\Email_messageRepository;
use Lasallecms\Lasallecmsemail\Repositories\Email_attachmentRepository;
use Lasallecms\Lasallecmstokenbasedlogin\Repositories\UserTokenbasedloginRepository;

// Laravel facades
use Illuminate\Support\Facades\Mail;

// Third party classes
use Carbon\Carbon;

/**
 * Class BaseInboundProcessing
 * @package Lasallecms\Lasallecmsemail\Processing
 */
class BaseInboundProcessing
{
    use PrepareForPersist;


    /**
     * @var Lasallecms\Lasallecmsemail\Repositories\Email_messageRepository
     */
    protected $email_messageRespository;

    /**
     * @var Lasallecms\Lasallecmsemail\Repositories\Email_attachmentRepository
     */
    protected $email_attachmentRepository;

    /**
     * @var Lasallecms\Lasallecmstokenbasedlogin\Repositories\UserTokenbasedloginRepository
     */
    protected $userTokenbasedloginRepository;


    /**
     * BaseInboundProcessing constructor.
     *
     * @param Lasallecms\Lasallecmsemail\Repositories\Email_messageRepository                 $email_messagesRespository
     * @param Lasallecms\Lasallecmsemail\Repositories\Email_attachmentRepository              $email_attachmentRepository
     * @param Lasallecms\Lasallecmstokenbasedlogin\Repositories\UserTokenbasedloginRepository $userTokenbasedloginRepository
     */
    public function __construct(
        Email_messageRepository    $email_messageRespository,
        Email_attachmentRepository $email_attachmentRepository,
        UserTokenbasedloginRepository $userTokenbasedloginRepository
    ) {
        $this->email_messageRespository      = $email_messageRespository;
        $this->email_attachmentRepository    = $email_attachmentRepository;
        $this->userTokenbasedloginRepository = $userTokenbasedloginRepository;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////
    //                           SEND EMAIL NOTIFICATION                                          //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Send an email notification to sender
     *
     * @param  string  $message              Message to put in the body of the email
     * @param  array   $mappedVars           Inbound POST vars mapped to database fields
     * @return void
     */
    public function sendEmailNotificationToSender($message, $mappedVars) {

        // Prep the email
        $data = $this->prepareNotificationEmailData($message, $mappedVars);

        // What blade file to use?
        $emailBladeFile = 'lasallecmsemail::email.notification_email_to_inbound_sender';

        // Send da email
        Mail::queue($emailBladeFile, ['data' => $data], function ($message) use ($data) {

            $message->from($data['from_email_address'], $data['from_name']);
            $message->to($data['to_email_address'] , $data['to_email_address']);
            $message->subject($data['subject']);
        });
    }

    /**
     * Prepare the data needed to send out a notification email to the inbound email's sender
     *
     * NOTE! USING MAILGUN SPECIFIC POST VARS FOR THE "TO" EMAIL FIELDS!
     *
     *
     * @param  string  $message              Message to put in the body of the email
     * @param  array   $mappedVars           Inbound POST vars mapped to database fields
     * @return array
     */
    public function prepareNotificationEmailData($message, $mappedVars) {

        $data = [];

        // Build the email data
        $data['site_name']            = config('lasallecmsfrontend.site_name');

        $data['from_name']            = $data['site_name'];
        $data['from_email_address']   = config('lasallecmsusermanagement.administrator_first_among_equals_email');
        $data['to_email_address']     = trim($mappedVars['from_email_address']);
        $data['to_name']              = $this->genericWashText($data['from_name']);
        $data['subject']              = "Notification email from ".$data['site_name'];
        $data['message']              = $message;

        return $data;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    //                              GENERAL PROCESSING                                            //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Modify the subject line for notification emails
     *
     * @param  string $subject  The subject line
     * @return string
     */
    public function modifiedSubjectLine($subject) {
        return trim($subject) . " " . Carbon::now()->toDateTimeString();
    }

    /**
     * Build the data for the INSERT into email_messages.
     *
     * Some fields are already in the $data array.
     * These are the db fields that are missing in the $data array.
     *
     * @param  array   $mappedVars           Inbound POST vars mapped to database fields
     * @return array
     */
    public function buildDataForDatabaseInsert($mappedVars) {

        $data = [];

        $data['priority_id']        = null;
        $data['slug']               = $this->genericCreateSlug($mappedVars['subject']);
        $data['sent']               = 1;
        $data['sent_timestamp']     = Carbon::now();
        $data['read']               = 0;
        $data['archived']           = 0;
        $data['created_at']         = Carbon::now();
        $data['created_by']         = $mappedVars['user_id'];
        $data['updated_at']         = Carbon::now();
        $data['updated_by']         = $mappedVars['user_id'];
        $data['locked_at']          = null;
        $data['locked_by']          = null;

        return $data;
    }

    /**
     * Create a new "email_messages" record.
     *
     * If the save is successful, returns the new email_messages.id
     * If the save fails, returns false
     *
     * @param  array   $data    Inbound POST vars, and processed vars,
     * @return mixed
     */
    public function insertEmail_message($data) {
        return $this->email_messageRespository->insertNewRecord($data);
    }


    /**
     * Process attachments
     *
     * @param  int    $emailMessageID    The ID of the just inserted "email_messages" record
     * @param  array  $data              Inbound POST vars, and processed vars,
     * @return void
     */
    public function processAttachments($emailMessageID, $data=null) {

        $attachmentPath      = public_path() . "/".config('lasallecmsemail.attachment_path')."/";

        // INSERT into the "email_attachments" db table
        for ($i = 1; $i <= $data['number_of_attachments']; $i++) {
            $data = array_merge($data, $this->prepareAttachmentDataForInsert($emailMessageID, $i, $attachmentPath, $data));
            $this->email_attachmentRepository->insertNewRecord($data);

            $data['attachment-'.$i]->move($attachmentPath, $data['attachment-'.$i]->getClientOriginalName());
        }
    }

    /**
     * Prepare the data for the INSERT into the "email_attachments" db table.
     *
     * @param  int    $emailMessageID    The ID of the just inserted "email_messages" record
     * @param  int    $attachment        What attachment number? eg, attachment-1. AKA, Mailgun's "attachment-x" post var
     * @param  string $attachmentPath    Where are the attachments saved?
     * @param  array  $data              Inbound POST vars, and processed vars,
     * @return array
     */
    public function prepareAttachmentDataForInsert($emailMessageID, $attachment, $attachmentPath, $data=null) {

        $data1 = [];
        $data1['email_messages_id']   = $emailMessageID;
        $data1['attachment_path']     = $attachmentPath;
        $data1['attachment_filename'] = $data['attachment-'.$attachment]->getClientOriginalName();

        if (!empty($data['alternate_sort_string1'])) {
            $data1['alternate_sort_string1'] = $data['alternate_sort_string1'];
        } else {
            $data1['alternate_sort_string1'] = null;
        }

        if (!empty($data['alternate_sort_string2'])) {
            $data1['alternate_sort_string2'] = $data['alternate_sort_string2'];
        } else {
            $data1['alternate_sort_string2'] = null;
        }

        if (!empty($data['comments'])) {
            $data1['comments']         = $data['comments'];
        } else {
            $data1['comments']         = null;
        }

        return $data1;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////
    //                                TOKEN BASED LOGIN                                           //
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Create the token for token based login; and, send out the email with the token based login link, to a user
     *
     * @param  int     $user_id   ID field of the "users" db table
     * @param  array   $email     Email fields for the outgoing email with the token login link
     * @return void
     */
    public function manageTokenBasedLogin($user_id, $email) {

        //-------------------------------------------------------------
        // Create a Login Token so customer login bypasses login form
        //-------------------------------------------------------------
        $this->userTokenbasedloginRepository->createLoginToken($user_id);

        //-------------------------------------------------------------
        // Send Login Token email to the customer
        //-------------------------------------------------------------
        $this->sendLoginTokenEmail->sendEmail($user_id, $email);
    }
}