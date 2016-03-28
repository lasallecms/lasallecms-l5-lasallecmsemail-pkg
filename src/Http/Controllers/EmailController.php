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
 * Class EmailController
 * @package Lasallecrm\Lasallecrmemail\Controllers
 */
class EmailController extends Controller
{
    public function inboundget() {
		return "<h1>Hello inboundget!</h1>";
	}


// http://stackoverflow.com/questions/24605291/retrieve-attachment-from-mailgun-form-post-php
    public function inboundpost(Request $request) {

        $vars = $request->all();

        $data = [];
        $data['subject'] = "MAILGUN ROUTE";
        $data['to']      = "krugerbloom@gmail.com";

        $emailBladeFile = 'lasallecrmemail::email.test';

        Mail::queue($emailBladeFile, ['vars' => $vars], function ($message) use ($data) {
            $message->subject($data['subject'])
                ->to($data['to'])
            ;
        });

        return;
    }

}