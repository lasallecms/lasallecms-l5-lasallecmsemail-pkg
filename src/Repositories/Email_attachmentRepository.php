<?php

namespace Lasallecrm\Lasallecrmemail\Repositories;

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
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecrm\Lasallecrmemail\Models\Email_attachment;

/**
 * Class Email_attachmentRepository
 * @package Lasallecrm\Lasallecrmemail\Repository
 */
class Email_attachmentRepository extends BaseRepository
{
    /**
     * Instance of model
     *
     * @var Lasallecrm\Lasallecrmemail\Models\Email_attachment
     */
    protected $model;


    /**
     * Inject the model
     *
     * @param  Lasallecms\Lasallecrmemail\Models\Email_attachment
     */
    public function __construct(Email_attachment $model) {
        $this->model = $model;
    }

    /**
     * @param  int   $id    ID for the email messages table
     * @return mixed
     */
    public function  getEmailAttachmentsForAdminShow($id) {
        return $this->model->where('email_messages_id', $id)->get();
    }

    /**
     * Save the model to the database.
     *
     * https://laravel.com/docs/5.1/eloquent#basic-inserts
     *
     * @param $data
     */
    public function insertNewRecord($data) {

        $emailAttachment = new Email_attachment;

        $emailAttachment->email_messages_id          = $data['email_messages_id'];
        $emailAttachment->attachment_path            = $data['attachment_path'];
        $emailAttachment->attachment_filename        = $data['attachment_filename'];
        $emailAttachment->alternate_sort_string1     = $data['alternate_sort_string1'];
        $emailAttachment->alternate_sort_string2     = $data['alternate_sort_string2'];
        $emailAttachment->comments                   = $data['comments'];

        return $emailAttachment->save();
    }
}