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
use Lasallecms\Lasallecmsfrontend\Http\Controllers\FrontendBaseController;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class FrontendCustomerCareDashboardController
 * @package Lasallecrm\Lasallecrmemail\Http\Controllers
 */
class FrontendCustomerCareDashboardController extends FrontendBaseController
{

    public function __construct() {
        // execute parent's construct method first in order to run the middleware
        parent::__construct();

        // Must be logged in...
        $this->middleware('auth');
    }


    /**
     * Display all order numbers for a user
     *
     * @return response
     */
    public function displayAllAlternatesortstring1Links() {

        // Get all the order numbers for a given user
        $alternateSortString1s = DB::table('email_messages')
            ->join('email_attachments', 'email_messages.id', '=', 'email_attachments.email_messages_id')
            ->select('email_attachments.alternate_sort_string1')
            ->where('email_messages.user_id', '=', Auth::user()->id)
            ->distinct()
            ->orderBy('email_attachments.alternate_sort_string1', 'desc')
            ->get()
        ;

        return view('lasallecrmemail::frontend/display_attachments_list_of_alternate_sort1', [
            'alternateSortString1s' => $alternateSortString1s,
            'url'                   => config('app.url'),
            'username'              => Auth::user()->name,
        ]);
    }

    /**
     * @param   string   $alternatesortstring1  Display all "email_attachments" records
     *                                          for a single alternate_sort_string1
     * @return  response
     */
    public function displaySingleAlternatesortstring1($alternatesortstring1) {

        $attachments = DB::table('email_attachments')
            ->where('alternate_sort_string1', '=', $alternatesortstring1)
            ->orderBy('email_messages_id', 'desc')
            ->get()
        ;

        return view('lasallecrmemail::frontend/display_attachments_for_one_alternate_sort1', [
            'attachments'            => $attachments,
            'url'                    => config('app.url'),
            'username'               => Auth::user()->name,
            'alternatesortstring1'   => $alternatesortstring1,
            'attachment_path'        => config('lasallecrmemail.attachment_path'),
        ]);
    }
}
