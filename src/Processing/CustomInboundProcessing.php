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
use Lasallecrm\Lasallecrmemail\Processing\BaseProcessing;

// Laravel classes
use Illuminate\Http\Request;

// Laravel facades
use Illuminate\Support\Facades\DB;

// Third party classes
use Carbon\Carbon;

/**
 * Class CustomInboundProcessing
 * @package Lasallecrm\Lasallecrmemail\Http\Controllers
 */
class CustomInboundProcessing extends BaseProcessing
{
    use PrepareForPersist;


    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @param Illuminate\Http\Request  $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Parse the subject line
     *
     * @return array
     */
    public function parseSubjectLine() {

        $data = [];

        // subject line in the form "123456,654321", where 123456 = userID and 654321 = order number
        $subjectLine = explode(',', $this->request->input('subject'));
        $data['userID']                 = $subjectLine[0];
        $data['orderNumber']            = $subjectLine[1];
        $data['alternate_sort_string1'] = $data['orderNumber'];

        return $data;
    }

    /**
     * Parse the comments
     *
     * @return string
     */
    public function parseComments() {

        // what is between the word "comments", in the body, is the actual comments
        // to INSERT into the "email_attachments" db table
        $comments = $this->request->input('body-plain');
        $comments = explode("comments", $comments);

        // Preface comments with the date
        return "(".Carbon::now()->toDateTimeString().") ".trim($comments[1]);
    }

    /**
     * Is customer in the "users" table?
     *
     * @param  int  $userID     User ID in "users" db table
     * @return bool
     */
    public function isCustomerInUsersTable($userID) {
        $result =  DB::table('users')
            ->where('id', $userID)
            ->value('id');
        ;

        if (count($result) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Is the order number in the specially created "custom_order_number" db table?
     *      *
     * @param  string   $orderNumber     The order number parsed from the inbound email's subject line
     * @return bool
     */
    public function isOrdernumberInCustomordernumberTable($orderNumber) {
        $result =  DB::table('custom_order_number')
            ->where('order_number', $orderNumber)
            ->value('order_number');
        ;

        if (count($result) == 0) {
            return false;
        }

        return true;
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
}
