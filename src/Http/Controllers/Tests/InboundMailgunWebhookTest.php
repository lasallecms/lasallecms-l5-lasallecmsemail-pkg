<?php

namespace Lasallecms\Lasallecmsemail\Http\Controllers\Tests;

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


// LaSalle Software
use Lasallecms\Helpers\Dates\DatesHelper;
use Lasallecms\Helpers\HTML\HTMLHelper;

use Lasallecrm\Lasallecrmemail\Models\Email_message;

// Laravel classes
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

// Laravel facades
use Illuminate\Support\Facades\Config;

// Third party classes
use Collective\Html\FormFacade as Form;


/**
 * Class InboundMailgunWebhookTest
 * @package Lasallecms\Lasallecmsemail\Http\Controllers\Tests
 */
class InboundMailgunWebhookTest extends Controller
{
    /**
     * @var Lasallecrm\Lasallecrmemail\Models\Email_message
     */
    protected $email_message_model;

    /**
     * AdminEmailHandlingController constructor.
     *
     * @param Email_message              $model
     * @param Email_messageRepository    $repository
     * @param Email_attachmentRepository $email_attachmentRepository
     * @param AdminEmailProcessing       $adminEmailProcessing
     */
    public function __construct(Email_message $email_message_model) {

        $this->email_message_model = $email_message_model;
    }

    public function testViewInboundMailgunWebhook() {

        return view('lasallecrmemail::tests/testInboundMailgunWebhook', [

            'package_title'                => $this->email_message_model->package_title,
            'resource_route_name'          => $this->email_message_model->resource_route_name,
            'DatesHelper'                  => DatesHelper::class,
            'HTMLHelper'                   => HTMLHelper::class,
            'Config'                       => Config::class,
            'admin_size_input_text_box'    => Config::get('lasallecmsadmin.admin_size_input_text_box'),
            'admin_template_name'          => config('lasallecmsadmin.admin_template_name'),
            'Form'                         => Form::class,
        ]);
    }
}
