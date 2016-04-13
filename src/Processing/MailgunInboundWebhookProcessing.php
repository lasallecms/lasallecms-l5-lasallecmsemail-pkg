<?php

namespace Lasallecrm\Lasallecrmemail\Processing;

/**
 *
 * Email handling package for the LaSalle Customer Relationship Management package.
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
 * @package    Email handling package for the LaSalle Customer Relationship Management package
 * @link       http://LaSalleCRM.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing;
use Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository;

// Laravel classes
use Illuminate\Http\Request;

// Third party classes
use Carbon\Carbon;


/**
 * Mailgun specific processing methods
 *
 * Class MailgunInboundWebhookProcessing
 * @package Lasallecrm\Lasallecrmemail\Processing
 */
class MailgunInboundWebhookProcessing
{
    use PrepareForPersist;


    /**
     * @var Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing
     */
    protected $genericEmailProcessing;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository
     */
    protected $email_attachmentRepository;


    /**
     * inboundEmailMailgunController constructor.
     * @param Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing       $genericEmailProcessing
     * @param Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository $email_attachmentRepository
     */
    public function __construct(
        Request                    $request,
        GenericEmailProcessing     $genericEmailProcessing,
        Email_attachmentRepository $email_attachmentRepository
    ) {
        $this->request                    = $request;
        $this->genericEmailProcessing     = $genericEmailProcessing;
        $this->email_attachmentRepository = $email_attachmentRepository;
    }


    /**
     * Map the non-attachment vars from the inbound email webhook to the email_messages fields
     *
     * @return array
     */
    public function mapInboundPostVarsToEmail_messagesFields() {

        $data = [];

        $data['user_id']            = $this->getUserIdByMappedEmailAddress();

        $data['priority_id']        = null;

        $data['from_email_address'] = trim($this->request->input('sender'));
        $data['from_name']          = $this->genericWashText($this->request ->input('from'));


        $data['to_email_address']   = $this->setToEmailAddressField();
        $data['to_name']            = $this->setToField();

        $data['subject']            = $this->genericWashText($this->request->input('subject'));
        $data['slug']               = $this->genericCreateSlug($data['subject']);
        $data['body']               = $this->setBodyField();
        $data['message_header']     = json_decode($this->request->input('message-headers'));
        $data['sent']               = 1;
        $data['sent_timestamp']     = Carbon::now();
        $data['read']               = 0;
        $data['archived']           = 0;
        $data['created_at']         = Carbon::now();
        $data['created_by']         = $data['user_id'];
        $data['updated_at']         = Carbon::now();
        $data['updated_by']         = $data['user_id'];
        $data['locked_at']          = null;
        $data['locked_by']          = null;

        return $data;
    }

    /**
     * Map the attachment vars from the inbound email webhook to the email_attachments fields
     *
     * @param  object  $request   The post request object
     * @return array
     */
    public function mapInboundPostVarsToEmail_attachmentsFields() {

    }

    /**
     * Ensure the authenticity of inbound Mailgun request
     *
     * https://documentation.mailgun.com/user_manual.html#webhooks
     * https://github.com/mailgun/mailgun-php/blob/master/src/Mailgun/Mailgun.php
     * http://php.net/manual/en/function.hash-hmac.php
     *
     * @param  timestamp  $timestamp  Mailgun's timestamp in the POST request
     * @param  string     $token      Mailguns's token in the POST request
     * @paraam string     $signature  Mailgun's signature in the POST request
     * @return bool
     */
    public function verifyWebhookSignature() {

        $timestamp = $this->request->input('timestamp');
        $token     = $this->request->input('token');
        $signature = $this->request->input('signature');


        // The Mailgun config param is an array, so grab the full array
        $configMailgun = config('services.mailgun');

        $hmac = hash_hmac('sha256', $timestamp. $token, $configMailgun['secret']);

        if(function_exists('hash_equals')) {

            // hash_equals is constant time, but will not be introduced until PHP 5.6
            return hash_equals($hmac, $signature);
        }

        return ($hmac == $signature);
    }

    /**
     * Does the recipient's email address map to an email address in the "users" database table?
     *
     * @return bool
     */
    public function isInboundEmailToEmailAddressMapToUser() {

        // We map an inbound Mailgun route to a record in the "users" table, by email address
        $mappedRoutes = config('lasallecrmemail.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                return true;
            }
        }

        return false;
    }


    /**
     * Does the mapped user actually exist in the "users" db table?
     *
     * @return bool
     */
    public function isMappedUserExistInUsersTable() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecrmemail.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }


        if ($this->genericEmailProcessing->getUsersIdByEmailAddress($userEmailAddress)) {
            return true;
        }

        return false;
    }

    /**
     * Get the user's id (from the "users" table) using the mapped email address
     *
     * @return int
     */
    public function getUserIdByMappedEmailAddress() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecrmemail.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }

        return $this->genericEmailProcessing->getUsersIdByEmailAddress($userEmailAddress);
    }

    /**
     * Set the "to_email_address" db field
     *
     * @return string
     */
    public function setToEmailAddressField() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecrmemail.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                return $user;
            }
        }
    }

    /**
     * Set the "to_email_address" db field
     *
     * @return string
     */
    public function setToField() {

        // get the mapped recipient (valid because $this->isInboundEmailToEmailAddressMapToUser() is already done)
        $mappedRoutes = config('lasallecrmemail.inbound_map_mailgun_routes_with_user_email_address');

        foreach ($mappedRoutes as $route => $user) {

            // if "from" email address is the same as the route specified in the config...
            if ($this->request->input('recipient') == $route) {
                $userEmailAddress = $user;
            }
        }

        return $this->genericEmailProcessing->getUsersNameByEmailAddress($userEmailAddress);
    }

    /**
     * Set the email's body field
     *
     * @return mixed
     */
    public function setBodyField() {

        if ($this->request->input('stripped-html')) {
            return $this->request->input('stripped-html');
        }

        return $this->request->input('body-plain');
    }

    /**
     * Process attachments
     *
     * @param  int   $emailMessageID    The ID of the just inserted "email_messages" record
     * @return void
     */
    public function processAttachments($emailMessageID) {

        $numberOfAttachments = $this->request->input('attachment-count');
        $attachmentPath      = public_path() . "/".config('lasallecrmemail.attachment_path')."/";

        // INSERT into the "email_attachments" db table
        for ($i = 1; $i <= $numberOfAttachments; $i++) {
            $data = $this->prepareAttachmentDataForInsert($emailMessageID, $i, $attachmentPath);
            $this->email_attachmentRepository->insertNewRecord($data);
        }

        // Upload
        //$this->request->file('attachment-'.$i)->move($attachmentPath, $this->request->file('attachment-'.$i)->getClientOriginalName());
    }

    /**
     * Prepare the data for the INSERT into the "email_attachments" db table.
     *
     * @param  int    $emailMessageID    The ID of the just inserted "email_messages" record
     * @param  int    $attachment        What attachment number? eg, attachment-1. AKA, Mailgun's "attachment-x" post var
     * @param  string $attachmentPath    Where are the attachments saved?
     * @return array
     */
    public function prepareAttachmentDataForInsert($emailMessageID, $attachment, $attachmentPath) {

        $data = [];
        $data['email_messages_id']   = $emailMessageID;
        $data['attachment_path']     = $attachmentPath;
        //$data['attachment_filename'] = $this->request ->file('attachment-'.$attachment)->getClientOriginalName();
        $data['attachment_filename'] = "jonathon1.jpg";
        $data['comments']            = null;

        return $data;
    }
}