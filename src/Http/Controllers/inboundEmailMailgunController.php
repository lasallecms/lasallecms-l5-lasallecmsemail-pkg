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
use Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing;
use Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing;
use Lasallecrm\Lasallecrmemail\Repositories\Email_messageRepository;
use Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository;
use Lasallecrm\Lasallecrmemail\LoginToken\CreateLoginToken;
use Lasallecrm\Lasallecrmemail\LoginToken\SendLoginTokenEmail;

// Laravel classes
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class inboundEmailMailgunController
 * @package Lasallecrm\Lasallecrmemail\Http\Controllers
 */
class inboundEmailMailgunController extends Controller
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
     * @param Lasallecrm\Lasallecrmemail\Processing\MailgunInboundWebhookProcessing  $mailgunInboundWebhookProcessing
     * @param Lasallecrm\Lasallecrmemail\Processing\GenericEmailProcessing           $genericEmailProcessing
     * @param Email_messageRepository                                                $repository
     * @param Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository     $email_attachmentRepository
     * @param Lasallecrm\Lasallecrmemail\Logintoken\CreateLoginToken                 $createLoginToken
     * @param Lasallecrm\Lasallecrmemail\Logintoken\SendLoginTokenEmail              $sendLoginTokenEmail
     */
    public function __construct(
        MailgunInboundWebhookProcessing  $mailgunInboundWebhookProcessing,
        GenericEmailProcessing           $genericEmailProcessing,
        Email_messageRepository          $repository,
        Email_attachmentRepository       $email_attachmentRepository,
        CreateLoginToken                 $createLoginToken,
        SendLoginTokenEmail              $sendLoginTokenEmail
    ) {
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
    public function inboundStandardHandling(Request $request) {

        // Is Mailgun's inbound POST request authentic?
        if (!$this->mailgunInboundWebhookProcessing->verifyWebhookSignature()) {
            return response('Invalid signature.', 406);
        }


        // If there are attachments, did the upload to the /tmp/ folder succeed?
        if  ($request->input('attachment-count') > 0)  {

            if (!$this->mailgunInboundWebhookProcessing->verifyAttachmentUploadToTmpFolder()) {

                // Send an email back to sender that this email is rejected
                $message = "RE: \".$this->mailgunInboundWebhookProcessing->getSubject().\".  Your email has been rejected because your attachment(s) did not successfully upload to the local /tmp/ folder.";
                $this->genericEmailProcessing->sendEmailNotificationToSender($message);

                // send response to Mailgun
                return response('Attachments failed to upload to the local /tmp/ folder.', 406);
            }
        }


        // The atachments must be pre-authorized extensions
        if (!$this->mailgunInboundWebhookProcessing->attachmentsHaveApprovedFileExtensions()) {

            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->mailgunInboundWebhookProcessing->getSubject().".  Your email has been rejected because at least one attachment has an unapproved file extension.";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('At least one attachment has an unapproved file extension.', 406);
        }


        // Inbound email is from a pre-approved sender
        if (!$this->genericEmailProcessing->emailsComeFromListOfApprovedSenders($request->input('sender'))) {

            // Sender is not on the list of pre-approved senders.
            // Send an email back to sender that this email is rejected
            $message = "RE: ".$this->mailgunInboundWebhookProcessing->getSubject().".  Your email has been rejected because you are not a pre-approved sender";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Person who sent email is not an approved sender.', 406);
        }


        //-------------------------------------------------------------
        // If there are attachments, did the upload to the /tmp/ folder succeed?
        //-------------------------------------------------------------
        if  ($this->request->input('attachment-count') > 0)  {

            if (!$this->mailgunInboundWebhookProcessing->verifyAttachmentUploadToTmpFolder()) {

                // Send an email back to sender that this email is rejected
                $message = "RE: ".$this->mailgunInboundWebhookProcessing->getSubject().".  Your email has been rejected because your attachment(s) did not successfully upload to the local /tmp/ folder.";
                $this->genericEmailProcessing->sendEmailNotificationToSender($message);

                // send response to Mailgun
                return response('Attachment(s) did not successfully upload to the local /tmp/ folder.', 406);
            }
        }


        // Does the Mailgun route map to a user?
        if (!$this->mailgunInboundWebhookProcessing->isInboundEmailToEmailAddressMapToUser()) {

            // "To" is not mapped to a user

            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->mailgunInboundWebhookProcessing->getSubject().".  Your email has been rejected because the email address you used is not approved.";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('The email address you used is not approved.', 406);
        }


        // Does the mapped user actually exist in the "users" db table?
        if (!$this->mailgunInboundWebhookProcessing->isMappedUserExistInUsersTable()) {
            // send an email back to sender that this email is rejected
            $message = "RE: ".$this->customInboundProcessing->getSubject().".  Your email has been rejected because you do not exist as a web application user.";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);

            // send response to Mailgun
            return response('Person who sent email does not exist as a web application user.', 406);
        }


        // build the data for INSERT into email_messages
        // do up a new "message_headers" field
        $data = $this->mailgunInboundWebhookProcessing->mapInboundPostVarsToEmail_messagesFields();


        // INSERT
        $savedOk = $this->repository->insertNewRecord($data);

        if (!$savedOk) {
             $message = "RE: ".$this->mailgunInboundWebhookProcessing->getSubject().".  Your email to ".$request->input('recipient')."was not successfully processed";
            $this->genericEmailProcessing->sendEmailNotificationToSender($message);
            return response('Invalid processing.', 406);
        }


        // Process attachments
        if ($request->input('attachment-count')) {

            // $savedOk returns the ID of the recently INSERTed record
            $this->mailgunInboundWebhookProcessing->processAttachments($savedOk);
        }


        // Create a Login Token
     //   $userId =  $this->mailgunInboundWebhookProcessing->getUserIdByMappedEmailAddress();
     //   $this->createLoginToken->createLoginToken($userId);

        // Send Login Token email
     //   $this->sendLoginTokenEmail->sendEmail($userId);


        // Notification email to inbound email's sender
        $message = "RE: ".$request->input('subject').".  Your email to ".$request->input('recipient')." was successfully processed";
        $this->genericEmailProcessing->sendEmailNotificationToSender($message);


        return response('Success!', 200);
    }
}