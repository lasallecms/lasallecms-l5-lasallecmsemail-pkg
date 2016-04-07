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
use Lasallecrm\Lasallecrmemail\Models\Email_message;

/**
 * Class Email_messageRepository
 * @package Lasallecrm\Lasallecrmemail\Repository
 */
class Email_messageRepository extends BaseRepository
{
    /**
     * Instance of model
     *
     * @var Lasallecms\Lasallecrmemail\Models\Email_message
     */
    protected $model;


    /**
     * Inject the model
     *
     * @param  Lasallecms\Lasallecrmemail\Models\Email_message
     */
    public function __construct(Email_message $model) {
        $this->model = $model;
    }

    /**
     * @param  int                 $userID     Logged in user
     * @return mixed
     */
    public function getEmailMessagesForAdminIndex($userID) {
        return $this->model->where('user_id', $userID)
            ->where('archived', 0)
            ->orderBy('sent_timestamp', 'desc')
            ->get()
        ;
    }

    /**
     * Mark an email as read by setting the "read" field to true
     *
     * @param  int  $id    The "email_messages" table's ID field
     * @return void
     */
    public function markEmailAsRead($id) {

        $emailMessage = $this->model->find($id);

        $emailMessage->read = 1;

        return $emailMessage->save();
    }

    /**
     * Mark an email as sent by setting the "sent" field to true
     *
     * @param  int  $id    The "email_messages" table's ID field
     * @return void
     */
    public function markEmailAsSent($id) {

        $emailMessage = $this->model->find($id);

        $emailMessage->sent = 1;

        return $emailMessage->save();
    }

    /**
     * Save the model to the database.
     *
     * https://laravel.com/docs/5.1/eloquent#basic-inserts
     *
     * @param $data
     */
    public function insertNewRecord($data) {

        $emailMessage = new Email_message;

        $emailMessage->user_id             = $data['user_id'];
        $emailMessage->priority_id         = $data['priority_id'];
        $emailMessage->from_email_address  = $data['from_email_address'];
        $emailMessage->from_name           = $data['from_name'];
        $emailMessage->to_email_address    = $data['to_email_address'];
        $emailMessage->to_name             = $data['to_name'];
        $emailMessage->subject             = $data['subject'];
        $emailMessage->slug                = $data['slug'];
        $emailMessage->body                = $data['body'];
        $emailMessage->sent                = $data['sent'];
        $emailMessage->sent_timestamp      = $data['sent_timestamp'];
        $emailMessage->read                = $data['read'];
        $emailMessage->archived            = $data['archived'];
        $emailMessage->created_at          = $data['created_at'];
        $emailMessage->created_by          = $data['created_by'];
        $emailMessage->updated_at          = $data['updated_at'];
        $emailMessage->updated_by          = $data['updated_by'];

        return $emailMessage->save();
    }

    /**
     * Save the model to the database.
     *
     * https://laravel.com/docs/5.1/eloquent#basic-inserts
     *
     * @param $data
     */
    public function updateNewRecord($data) {

        $emailMessage = $this->model->find($data['id']);

        $emailMessage->user_id             = $data['user_id'];
        $emailMessage->priority_id         = $data['priority_id'];


        if (isset($data['from_email_address'])) {
            $emailMessage->from_email_address  = $data['from_email_address'];
        }

        if (isset($data['from_name'])) {
            $emailMessage->from_name = $data['from_name'];
        }

        if (isset($data['to_email_address'])) {
            $emailMessage->to_email_address  = $data['to_email_address'];
        }

        if (isset($data['to_name'])) {
            $emailMessage->to_name = $data['to_name'];
        }

        $emailMessage->subject             = $data['subject'];
        $emailMessage->body                = $data['body'];
        $emailMessage->priority_id         = $data['priority_id'];
        $emailMessage->archived            = $data['archived'];
        $emailMessage->updated_at          = $data['updated_at'];
        $emailMessage->updated_by          = $data['updated_by'];

        return $emailMessage->save();
    }
}