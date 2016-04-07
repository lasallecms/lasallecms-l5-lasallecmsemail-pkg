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

// Laravel facades
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

// Laravel classes
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;


/**
 * Class LelyController
 * @package Lasallecrm\Lasallecrmemail\Controllers
 */
class LelyController extends Controller
{
    public function inboundget() {


        $vars = ['request' => 'get', 'method' => 'inbound' ];

        $data = [];
        $data['subject'] = "SENT FROM INBOUND";
        $data['to']      = "krugerbloom@gmail.com";

        $emailBladeFile = 'lasallecrmemail::email.test';

        Mail::send($emailBladeFile, ['vars' => $vars], function ($message) use ($data) {
            $message->subject($data['subject']);
            $message->to($data['to']);
        });


        return "<h1>Hello inboundget! ==> email sent!</h1>";
    }


// http://stackoverflow.com/questions/24605291/retrieve-attachment-from-mailgun-form-post-php
    public function inboundpost(Request $request) {

        $vars = $request->all();



        // move_uploaded_file($attachment['tmp_name'], $destinationPath . $attachment['name']);
        //$destinationPath = public_path() . "/" . Config::get('lasallecmsfrontend.images_folder_uploaded');
        $destinationPath = public_path() . "/lely/";

        if ($request->hasFile('attachment-1')) {
            //$vars['bob-attach-1'] = $request->file('attachment-1');
            $request->file('attachment-1')->move($destinationPath, 'bobby.jpg');
        }

        if ($request->hasFile('attachment-2')) {
            $vars['bob-attach-2'] = $request->file('attachment-2');
        }

        if ($request->hasFile('attachment-3')) {
            $vars['bob-attach-3'] = $request->file('attachment-3');
        }

        if ($request->hasFile('attachment-4')) {
            $vars['bob-attach-4'] = $request->file('attachment-4');
        }

        if ($request->hasFile('attachment-5')) {
            $vars['bob-attach-5'] = $request->file('attachment-5');
        }



        //stripped-text =
        $strippedText          = $request->input('stripped-text');
        $vars['stripped-text'] = $strippedText;

        // client
        $startClient  = 8;
        $endClient    = strpos('order') - 1;
        $client = substr($strippedText, $startClient, $endClient-$startClient);
        $vars['client'] = $client;

        $startOrder   = strpos('order') + 7;
        $endOrder     = strpos('comment') - 1;

        $startComment = strpos('comment') + 8;
        $endComment   = strpos('**end**') - 1;



        $data = [];
        $data['subject'] = "MAILGUN ROUTE";
        $data['to']      = "krugerbloom@gmail.com";

        $emailBladeFile = 'lasallecrmemail::email.test';

        Mail::send($emailBladeFile, ['vars' => $vars], function ($message) use ($data) {
            $message->subject($data['subject']);
            $message->to($data['to']);
        });

        return;
    }
}