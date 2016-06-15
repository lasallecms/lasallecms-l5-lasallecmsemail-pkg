<?php

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
 * @package    Specialized inbound email handling package for the LaSalle Customer Relationship Management package
 * @link       http://LaSalleCRM.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */


return [

    /*
    |--------------------------------------------------------------------------
    | Attachment path
    |--------------------------------------------------------------------------
    |
    | Where in your public folder do  you want to save attachments?
    | For example, 'attachments' = http://domain.com/attachments.
    |
    | Not set up to save to S3 (or anywhere else).
    |
    |Update: 'attachment_path' can reside under the "public" or "storage" folder,
    |as set in lasallecmsfrontend.images_parent_folder_uploaded
    |
    */
    'attachment_path' => 'attachments',

    /*
    |--------------------------------------------------------------------------
    | Approved file extensions for attachments
    |--------------------------------------------------------------------------
    |
    | Upload only files with these extensions.
    |
    | Lowercase only.
    |
    | To upload all file extensions, leave this array empty.
    |
    */
    'inbound_attachments_approved_file_extensions' => [
        'jpg',
        'png',
        'gif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Inbound emails from these senders only
    |--------------------------------------------------------------------------
    |
    | Optionally allow inbound emails from these senders only.
    |
    | For Mailgun, it is the email specified in the "senders" post var.
    |
    */
    'inbound_emails_from_allowed_senders_only' => true,

    'inbound_emails_from_allowed_senders_only_list_of_senders' => [
        'info@southlasalle.com',
    ],
];

