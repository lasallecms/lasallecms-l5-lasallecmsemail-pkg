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

// This is *not* a command handler!

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecrm\Lasallecrmemail\Models\Email_message;

// Laravel classes
use Illuminate\Http\Request;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// Third party classes
use Carbon\Carbon;


/**
 * Class CreateEmailMessageFormProcessing
 * @package Lasallecrm\Lasallecrmemail\Processing\EmailProcessing
 */
class EmailProcessing
{
    use PrepareForPersist;

    /**
     * @var use Illuminate\Http\Request
     */
    protected $request;


    public function __construct(Request $request) {
        $this->request = $request;
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
}