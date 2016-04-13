<?php

namespace Lasallecrm\Lasallecrmemail\Http\Controllers;

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
use Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing;
use Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing;
use Lasallecrm\Lasallecrmemail\Repositories\Email_messageRepository;
use Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository;
use Lasallecrm\Lasallecrmemail\LoginToken\CreateLoginToken;
use Lasallecrm\Lasallecrmemail\LoginToken\SendLoginTokenEmail;

// Laravel classes
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

// Laravel facades
use Illuminate\Support\Facades\DB;

// Third party classes
use Carbon\Carbon;


/**
 * This custom inbound email process has an email coming in from an employee, but the email pertains to another user
 * (a customer). So the employee and customer both need to be users (in the "users" table).
 *
 * Also, the attachments relate to an order number, which is stored in the "email_attachments" table's
 * "alternate_sort_string1" field.
 *
 * Class customInboundEmailMailgunController
 * @package Lasallecrm\Lasallecrmemail\Http\Controllers
 */
class CustomInboundEmailMailgunController extends Controller
{
    /**
     * Handle the standard Mailgun inbound webhook.
     *
     * -----------------------------------------------------------------------------------------------------------------
     * Flow:
     *
     * (i)   get an inbound email into the "email_messages" database table
     * (ii)  get inbound attachments into the "email_attachments" database table
     * (iii) save the inbound attachments
     *
     *
     * -----------------------------------------------------------------------------------------------------------------
     * Rules:
     *
     * (i)  the email must be addressed to someone in the "users" database table, since they must
     *      log in to see their emails
     *
     * (ii) one inbound email address maps to one Mailgun inbound route maps to one "users" table ID
     *
     *
     * -----------------------------------------------------------------------------------------------------------------
     * Map database fields with Mailgun's parsed variables:
     *
     *  email_messages fields       Mailgun parsed post var
     *  ---------------------      ------------------------
     *    user_id                  the user_id associated with "recipient"
     *    from_email_address       sender
     *    from_name                from
     *    to_email_address         To
     *    to_name
     *    subject                  subject
     *    body                     stripped-html / body-plain
     *    message_headers          message-headers
     *
     *
     *  email_attachments field       Mailgun parsed post var
     *  -----------------------      ------------------------
     *   email_messages_id            "email_messages" db table's ID
     *   attachment_path              config('lasallecrmemail.attachment_path')
     *   attachment_filename          getClientOriginalName(attachment-1)
     *
     * $request->file('photo')->move(public_path().'/'.$attachment_path, $fileName);
     *
     *
     * -----------------------------------------------------------------------------------------------------------------
     * Links:
     *
     * securing Mailgun webhooks: https://documentation.mailgun.com/user_manual.html#webhooks
     * Symfony file uploads:      http://api.symfony.com/2.7/Symfony/Component/HttpFoundation/File/UploadedFile.html
     *
     */


    use PrepareForPersist;


    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing
     */
    protected $mailgunInboundWebhookProcessing;

    /**
     * @var Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing
     */
    protected $genericEmailProcessing;

    /**
     * @var Lasallecrm\Lasallecrmemail\Repositories\Email_messageRepository
     */
    protected $repository;

    /**
     * @var Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository
     */
    protected $email_attachmentrepository;

    /**
     * @var Lasallecrm\Lasallecrmemail\Logintoken\CreateLoginToken;
     */
    protected $createLoginToken;

    /**
     * @var Lasallecrm\Lasallecrmemail\Logintoken\SendLoginTokenEmail
     */
    protected $sendLoginTokenEmail;


    /**
     * inboundEmailMailgunController constructor.
     * @param Illuminate\Http\Request                                                $request
     * @param Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing  $mailgunInboundWebhookProcessing
     * @param Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing           $genericEmailProcessing
     * @param Email_messageRepository                                                $repository
     * @param Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository     $email_attachmentRepository
     * @param Lasallecrm\Lasallecrmemail\Logintoken\CreateLoginToken                 $createLoginToken
     * @param Lasallecrm\Lasallecrmemail\Logintoken\SendLoginTokenEmail              $sendLoginTokenEmail
     */
    public function __construct(
        Request                          $request,
        MailgunInboundWebhookProcessing  $mailgunInboundWebhookProcessing,
        GenericEmailProcessing           $genericEmailProcessing,
        Email_messageRepository          $repository,
        Email_attachmentRepository       $email_attachmentRepository,
        CreateLoginToken                 $createLoginToken,
        SendLoginTokenEmail              $sendLoginTokenEmail
    ) {
        $this->request                         = $request;
        $this->mailgunInboundWebhookProcessing = $mailgunInboundWebhookProcessing;
        $this->genericEmailProcessing          = $genericEmailProcessing;
        $this->repository                      = $repository;
        $this->email_attachmentrepository      = $email_attachmentRepository;
        $this->createLoginToken                = $createLoginToken;
        $this->sendLoginTokenEmail             = $sendLoginTokenEmail;
    }


    /**
     * Handle a standard inbound webhook POST request from Mailgun
     *
     * If Mailgun receives a 200 (Success) code it will determine the
     * webhook POST is successful and not retry.
     *
     * If Mailgun receives a 406 (Not Acceptable) code, Mailgun will
     * determine the POST is rejected and not retry.
     *
     * https://documentation.mailgun.com/user_manual.html#webhooks
     *
     * @param Request $request
     * @return mixed
     */
    public function inboundStandardHandling() {

        // Is Mailgun's inbound POST request authentic?
        if (!$this->mailgunInboundWebhookProcessing->verifyWebhookSignature()) {
            return response('Invalid signature.', 406);
        }


        // Are we checking that the inbound email is from a pre-approved sender? If so, do the check.
        // The sender is an employee, who must have a record in the "users" table
        if (!$this->genericEmailProcessing->emailsComeFromListOfApprovedSenders($this->request->input('sender'))) {

            // sender is not on the list of pre-approved senders

            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->getSubject().".  Your email has been rejected because you are not a pre-approved sender";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Invalid sender.', 406);
        }


        // Does the Mailgun route map to a user?
        // Let's do this check on the employee
        if (!$this->mailgunInboundWebhookProcessing->isInboundEmailToEmailAddressMapToUser()) {

            // "To" is not mapped to a user

            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->getSubject().".  Your email has been rejected because your recipient is not allowed";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Invalid recipient.', 406);
        }


        // Does the mapped user actually exist in the "users" db table?
        // Let's do this check on the employee
        if (!$this->mailgunInboundWebhookProcessing->isMappedUserExistInUsersTable()) {
            return response('Invalid sender.', 406);
        }


        // Before we continue, at this point we need to validate that there are attachment(s).
        if (!$this->request->input('attachment-count')) {

            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->getSubject().".  Your email has been rejected because there are no attachments";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Invalid recipient.', 406);
        }


        // Before we continue, at this point we will pretend that the customer sent the inbound email.

        $input = [];


        // Parse the subject line.
        // subject line in the form "123456,654321", where 123456 = userID and 654321 = order number
        $subjectLine = explode(',', $this->request->input('subject'));
        $input['userID']                 = $subjectLine[0];
        $input['orderNumber']            = $subjectLine[1];
        $input['alternate_sort_string1'] = $input['orderNumber'];

        // Parse the body.
        // what is between the word "comments" is the actual comments to INSERT into the "email_attachments" db table
        $comments = $this->request->input('body-plain');
        $comments = explode("comments", $comments);

        $input['comments'] = trim($comments[1]);


        // Does the customer actually exists in the "users" db table?
        $result =  DB::table('users')
            ->where('id', $input['userID'])
            ->value('id');
        ;
        if (count($result) == 0) {
            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->getSubject().".  Your email has been rejected because the customer assigned as ".$input['userID']." is *not* set up as a user.";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Invalid recipient.', 406);
        }


        // Build the data for INSERT into email_messages
        $data = $this->mapCustomInboundPostVarsToEmail_messagesFields($input);


        // INSERT into email_messages
        $savedOk = $this->repository->insertNewRecord($data);

        if (!$savedOk) {
            $message = "RE: ".$this->getSubject().".  Your email to ".$this->request->input('recipient')." was not successfully processed. Something wrong happened when saving to the database (to email_messages). Please resend!";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);
            return response('Invalid processing.', 406);
        }


        // Process attachments
        $this->mailgunInboundWebhookProcessing->processAttachments($savedOk.$input);


        // Create a Login Token
        $this->createLoginToken->createLoginToken($input['userID']);

        // Send Login Token email
        $this->sendLoginTokenEmail->sendEmail($input['userID']);


        // Notification email to inbound email's sender
        $message = "RE: ".$this->getSubject().".  Your email to ".$this->request->input('recipient')." was successfully processed";
        $this->genericEmailProcessing->sendEmailNotificationToSender($message);


        return response('Success!', 200);
    }



    /**
     * Map the non-attachment vars from the inbound email webhook to the email_messages fields
     *
     * THIS HAS CUSTOM DATA MASSAGING. I DO NOT WANT TO MESS UP THE STANDARD PROCESSING WITH
     * CUSTOM STUFF, SO I HAVE THIS METHOD HERE.
     *
     * @param  array  $input    Data that was figured out from specific POST vars
     * @return array
     */
    public function mapCustomInboundPostVarsToEmail_messagesFields($input) {

        $data = [];

        $data['user_id']            = $input['userID'];

        $data['priority_id']        = null;

        $data['from_email_address'] = trim($this->request->input('sender'));
        $data['from_name']          = $this->genericWashText($this->request->input('from'));

        $data['to_email_address']   = DB::table('users')->where('id', $input['userID'])->value('email');
        $data['to_name']            = DB::table('users')->where('id', $input['userID'])->value('name');

        $data['subject']            = $this->getSubject();
        $data['slug']               = $this->genericCreateSlug($data['subject']);

        if ($this->request->input('stripped-html')) {
            $data['body'] = $this->request->input('stripped-html');
        } else {
            $data['body'] = $this->request->input('body-plain');
        }

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
     * Get the concatenated subject line
     *
     * @return string
     */
    public function getSubject() {
        return $this->request->input('subject') . " " . Carbon::now()->toDateTimeString();
    }
}