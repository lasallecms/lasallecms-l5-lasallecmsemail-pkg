<?php

namespace Lasallecms\Lasallecmsemail\Models;

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
use Lasallecms\Lasallecmsapi\Models\BaseModel;

/**
 * Class Email_message
 * @package Lasallecms\Lasallecmsemail\Models
 */
class Email_message extends BaseModel
{
    ///////////////////////////////////////////////////////////////////
    ///////////     MANDATORY USER DEFINED PROPERTIES      ////////////
    ///////////              MODIFY THESE!                /////////////
    ///////////////////////////////////////////////////////////////////

    // LARAVEL MODEL CLASS PROPERTIES
    /**
     * The database table used by the model.
     *
     * The convention is plural -- and plural is assumed.
     *
     * Lowercase.
     *
     * @var string
     */
    public $table = 'email_messages';

    /**
     * Which fields may be mass assigned
     * @var array
     */
    protected $fillable = [
        'user_id', 'priority_id', 'from_name', 'from_email_address', 'to_name', 'to_email_address', 'subject', 'body', 'read', 'archived',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * LaSalle Software handles the created_at and updated_at fields, so false.
     *
     * @var bool
     */
    public $timestamps = false;


    // PACKAGE PROPERTIES
    /**
     * Name of this package
     *
     * @var string
     */
    public $package_title = "LaSalleCRM";


    // MODEL PROPERTIES

    /**
     * Model class namespace.
     *
     * Do *NOT* specify the model's class.
     *
     * @var string
     */
    public $model_namespace = "Lasallecrm\Lasallecrmemail\Models";

    /**
     * Model's class.
     *
     * Convention is capitalized and singular -- which is assumed.
     *
     * @var string
     */
    public $model_class = "Email_message";

    // RESOURCE ROUTE PROPERTIES

    /**
     * The base URL of the resource routes.
     *
     * Frequently is the same as the table name.
     *
     * By convention, plural.
     *
     * Lowercase.
     *
     * @var string
     */
    public $resource_route_name   = "emailhandling";


    // FORM PROCESSORS PROPERTIES.
    // THESE ARE THE ADMIN CRUD COMMAND HANDLERS.
    // THE ONLY REASON YOU HAVE TO CREATE THESE COMMAND HANDLERS AT ALL IS THAT
    // THE EVENTS DIFFER. EVERYTHING THAT HAPPENS UP TO THE "PERSIST" IS PRETTY STANDARD.

    /*
     * Namespace of the Form Processors
     *
     * MUST *NOT* have a slash at the end of the string
     *
     * @var string
     */
    public $namespace_formprocessor = 'Lasallecms\Lasallecmsemail\Processing';

    /*
     * Namespace and class name of the DELETE (DESTROY) Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_delete = 'DeleteEmail_messageFormProcessing';



    // USER GROUPS & ROLES PROPERTIES

    /**
     * User groups that are allowed to execute each controller action
     *
     * @var array
     */
    public $allowed_user_groups = [
        ['index'   => ['Super Administrator']],
        ['create'  => ['Super Administrator']],
        ['store'   => ['Super Administrator']],
        ['edit'    => ['Super Administrator']],
        ['update'  => ['Super Administrator']],
        ['destroy' => ['Super Administrator']],
    ];


    // FIELD LIST PROPERTIES

    /*
     * Field list
     *
     * ID and TITLE must go first.
     *
     * Forms will list fields in the order fields are listed in this array.
     *
     * HERE FOR THE EXPRESS PURPOSE OF MAKING THE DELETION WORK
     * https://github.com/lasallecms/lasallecms-l5-lasallecmsapi-pkg/blob/579412dbc44f498f00d78184d5c69dc75372fcab/src/Repositories/Traits/Persist.php::destroyRecord($id)
     *
     * BASICALLY, A BLANK ARRAY ON PURPOSE!
     * THE DELETION DOES NOT DELETE ATTACHMENT RECORDS NOR ATTACHMENT FILES
     *
     * @var array
     */
    public $field_list = [
            [
            'name'                  => 'id',
            'type'                  => 'int',
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'center',
        ],
    ];

    /**
     * Big help to have this specific field define thusly.
     *
     * @var array
     */
    public $priorityIdField = [
        'name'                  => 'priority_id',
        'alternate_form_name'   => 'Priority',
        'type'                  => 'related_table',
        'related_table_name'    => 'lookup_todo_priority_types',
        'related_namespace'     => 'Lasallecrm\Todo\Models',
        'related_model_class'   => 'Lookup_todo_priority_type',
        'related_fk_constraint' => false,
        'related_pivot_table'   => false,
        'nullable'              => true,
        'info'                  => 'Optional.',
        'index_skip'            => false,
        'index_align'           => 'center',
    ];



    ///////////////////////////////////////////////////////////////////
    //////////////        RELATIONSHIPS             ///////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Many to many relationship with email attachments..
     *
     * Method name must be:
     *    * the model name,
     *    * NOT the table name,
     *    * singular;
     *    * lowercase.
     *
     * @return Eloquent
     */
    public function email_attachment() {
        return $this->belongsToMany('Lasallecrm\Lasallecrmemail\Models\Email_attachment', 'email_attachments');
    }

    /**
     * One to one relationship with user_id.
     *
     * Method name must be:
     *    * the model name,
     *    * NOT the table name,
     *    * singular;
     *    * lowercase.
     *
     * @return Eloquent
     */
    public function user() {
        return $this->belongsTo('Lasallecms\Lasallecmsapi\Models\User');
    }



    ///////////////////////////////////////////////////////////////////
    //////////////        OTHER METHODS             ///////////////////
    ///////////////////////////////////////////////////////////////////

    // none
}