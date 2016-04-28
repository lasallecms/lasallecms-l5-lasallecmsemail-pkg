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

// This is *not* a command handler!

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecms\Lasallecmsemail\Models\Email_message;

// Laravel classes
use Illuminate\Http\Request;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

// Third party classes
use Carbon\Carbon;


/**
 * Class CreateEmailMessageFormProcessing
 * @package Lasallecms\Lasallecmsemail\Processing\EmailProcessing
 */
class AdminEmailProcessing
{
    use PrepareForPersist;

    /**
     * @var use Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Lasallecms\Lasallecmsemail\Models\Email_message
     */
    protected $email_message;


    /**
     * EmailProcessing constructor.
     * @param Request         $request
     * @param Email_message   $email_message
     */
    public function __construct(Request $request, Email_message $email_message) {
        $this->request                       = $request;
        $this->email_message                 = $email_message;
    }



    ///////////////////////////////////////////////////////////////////
    ///////     CREATE EMAIL_MESSAGES RECORD                      /////
    ///////////////////////////////////////////////////////////////////

    /**
     * Are the create form inputs ok?
     *
     * @param  array   $data   Array of create form input fields
     * @return Laravel validate object
     */
    public function validateCreateForm($data) {
        return Validator::make($data, [
            'to_email_address' => 'required|email',
            'to_name'          => 'max:255',
            'subject'          => 'required|min:5|max:255',
            'body'             => 'required|min:10',
        ]);
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
     * Populate the rest of the fields required to INSERT a new email_messages record
     *
     * @param  array  $data
     * @return array
     */
    public function populateCreateFields($data) {

        $data['user_id']            = Auth::user()->id;
        $data['priority_id']        = null;
        $data['from_email_address'] = $this->request->input('from_email_address');
        $data['from_name']          = $this->request->input('from_name');
        $data['slug']               = $this->genericCreateSlug($data['subject']);
        $data['sent']               = 0;
        $data['sent_timestamp']     = Carbon::now();
        $data['read']               = 0;
        $data['archived']           = 0;
        $data['created_at']         = Carbon::now();
        $data['created_by']         = Auth::user()->id;
        $data['updated_at']         = Carbon::now();
        $data['updated_by']         = Auth::user()->id;

        return $data;
    }



    ///////////////////////////////////////////////////////////////////
    ///////          UPDATE EMAIL_MESSAGES RECORD                 /////
    ///////////////////////////////////////////////////////////////////

    // Update requires exact same washing as washCreateForm($data),
    // so no need for washUpdateForm($data)

    // Update requires exact same validation as validateCreateForm($data),
    // so no need for validateUpdateForm($data)

    /**
     * Populate the rest of the fields required to INSERT a new email_messages record
     *
     * @param  array  $data
     * @return array
     */
    public function populateUpdateFields($data) {

        $data['id']                 = $this->request->input('id');
        $data['user_id']            = $this->request->input('user_id');
        $data['priority_id']        = $this->request->input('priority_id');


        if (isset($data['from_email_address'])) {
            $data['from_email_address'] = $this->request->input('from_email_address');
        }

        if (isset($data['from_name'])) {
            $data['from_name']          = $this->request->input('from_name');
        }

        if (isset($data['to_email_address'])) {
            $data['to_email_address']   = $this->request->input('to_email_address');
        }

        if (isset($data['to_name'] )) {
            $data['to_name']            = $this->request->input('to_name');
        }


        $data['priority_id']        = $this->request->input('priority_id');
        $data['archived']           = $this->request->input('archived');
        $data['updated_at']         = Carbon::now();
        $data['updated_by']         = Auth::user()->id;

        return $data;
    }



    ///////////////////////////////////////////////////////////////////
    ///////          SEND THE EMAIL MESSAGE                       /////
    ///////////////////////////////////////////////////////////////////


    /**
     * Send the email
     *
     * It is assumed that the email resides in the "email_messages" database table.
     *
     * @param  int   $id   The "email_messages" table's ID field
     * @return void
     */
    public function sendEmail($id) {

        // Prep the email
        $data = $this->prepEmailData($id);

        // What blade file to use?
        $emailBladeFile = 'lasallecmsemail::email.send_email';

        // Send da email
        Mail::queue($emailBladeFile, ['data' => $data], function ($message) use ($data) {

            $message->from($data['from_email_address'], $data['from_name']);


            // sender" substitute a custom address of the sender at send time
            // http://swiftmailer.org/docs/sending.html
            $message->sender($data['sender_email_address'], $data['site_name']);

            $message->to($data['to_email_address'] , $data['to_email_address']);

            //$message->cc($address, $name = null);
            //$message->bcc($address, $name = null)

            $message->replyTo($data['from_email_address'], $data['from_name']) ;

            $message->subject($data['subject']);

            //$message->attach($file, array $options = []);

        });

    }

    /**
     * Prepare the email
     *
     * @param  int   $id   The "email_messages" table's ID field
     * @return array
     */
    public function prepEmailData($id) {

        $email = $this->email_message->find($id);

        // Build the email data
        // $data is an array
        $data = [];
        $data['from_name']            = $email->from_name;
        $data['from_email_address']   = $email->from_email_address;
        $data['to_name']              = $email->to_name;
        $data['to_email_address']     = $email->to_email_address;
        $data['subject']              = $email->subject;
        $data['body']                 = $email->body;
        $data['site_name']            = config('lasallecmsfrontend.site_name');
        $data['sender_email_address'] = "info@southlasalle.com";

        return $data;
    }
}