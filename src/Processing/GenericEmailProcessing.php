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

// Laravel classes
use Illuminate\Http\Request;

// Laravel facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Common processing methods
 *
 * Class GenericEmailProcessing
 * @package Lasallecrm\Lasallecrmemail\Processing
 */
class GenericEmailProcessing
{
    use PrepareForPersist;


    /**
     * @var Illuminate\Http\Request
     */
    protected $request;


    public function __construct(Request $request) {
        $this->request = $request;
    }


    /**
     * Quick find the user ID using the user's email address.
     *
     * The select must succeed because validateUserEmail() has already happened
     *
     * @param  string  $emailAddress   User's email address in the "users" table
     * @return int
     */
    public function getUsersIdByEmailAddress($emailAddress) {
        return DB::table('users')
            ->where('email', $emailAddress)
            ->value('id');
        ;
    }

    /**
     * Quick find the user name using the user's email address.
     *
     * The select must succeed because validateUserEmail() has already happened
     *
     * @param  string  $emailAddress   User's email address in the "users" table
     * @return int
     */
    public function getUsersNameByEmailAddress($emailAddress) {
        return DB::table('users')
            ->where('email', $emailAddress)
            ->value('name');
        ;
    }

    /**
     * Wash the create form's input fields
     *
     * @return array
     */
    public function washCreateForm() {

        // An array to hold the fields we'll neeed to INSERT the record
        $data = [];

        // Wash the input fields
        $data['to_email_address'] = trim($this->request->input('to_email_address'));
        $data['to_name']          = $this->genericWashText($this->request->input('to_name'));
        $data['subject']          = $this->genericWashText($this->request->input('subject'));
        $data['body']             = $this->genericWashText($this->request->input('body'));

        return $data;
    }


    /**
     * Check if emails must come from a list of approved senders
     *
     * @param  string   $emailAddress    Email address of the person sending the email
     * @return bool
     */
    public function emailsComeFromListOfApprovedSenders($emailAddress) {

        // are we checking that inbound emails come from a pre-approved list of senders?
        if (!$this->isInboundEmailsFromAllowedSendersOnly()) {

            // we are *not* checking that emails come from a pre-approved list of senders
            return true;
        }

        // yes, we are checking that emails come from a pre-approved list of senders
        if ($this->isInboundEmailsFromAllowedSendersOnlyListOfSsenders($emailAddress)) {

            // the sender is, indeed, on the list of pre-approved senders
            return true;
        }

        // the sender is not on the list of pre-approved senders
        return false;
    }


    /**
     * Does the config setting allow inbound emails from specified senders (email addresses) only?
     *
     * @return bool
     */
    public function isInboundEmailsFromAllowedSendersOnly() {
        return config('lasallecrmemail.inbound_emails_from_allowed_senders_only');
    }

    /**
     * Is the inbound email from an allowed sender?
     *
     * @param  string  $senderEmailAddress   Who is sending us the email?
     * @param  bool
     */
    public function isInboundEmailsFromAllowedSendersOnlyListOfSsenders($senderEmailAddress) {
        $allowedSenders = config('lasallecrmemail.inbound_emails_from_allowed_senders_only_list_of_senders');

        return in_array($senderEmailAddress, $allowedSenders);
    }

    /**
     * Send an email notification to sender
     *
     * @param  string  $message              Message to put in the body of the email
     * @return void
     */
    public function sendEmailNotificationToSender($message) {

        // Prep the email
        $data = $this->prepareNotificationEmailData($message);

        // What blade file to use?
        $emailBladeFile = 'lasallecrmemail::email.notification_email_to_inbound_sender';

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
     * @return array
     */
    public function prepareNotificationEmailData($message) {

        $data = [];

        // Build the email data
        $data['site_name']            = config('lasallecmsfrontend.site_name');

        $data['from_name']            = $data['site_name'];
        $data['from_email_address']   = config('lasallecmsusermanagement.administrator_first_among_equals_email');
        $data['to_email_address']     = trim($this->request->input('sender'));
        $data['to_name']              = $this->genericWashText($this->request->input('from'));
        $data['subject']              = "Notification email from ".$data['site_name'];
        $data['message']              = "Re: ".$data['subject'].":  ".$message;

        $data['sender_email_address'] = "info@southlasalle.com";

        return $data;
    }
}