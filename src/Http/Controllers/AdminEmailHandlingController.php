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
use Lasallecms\Formhandling\AdminFormhandling\AdminFormBaseController;
use Lasallecms\Helpers\Dates\DatesHelper;
use Lasallecms\Helpers\HTML\HTMLHelper;
use Lasallecms\Helpers\Images\ImagesHelper;

use Lasallecrm\Lasallecrmemail\Models\Email_message;

use Lasallecrm\Lasallecrmemail\Repositories\Email_messageRepository;
use Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository;

use Lasallecrm\Lasallecrmemail\Processing\EmailProcessing;

// Laravel classes
use Illuminate\Http\Request;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;


/**
 *
 *
 * Class EmailController
 * @package Lasallecrm\Lasallecrmemail\Controllers
 */
class AdminEmailHandlingController extends AdminFormBaseController
{
    /**
     * @var Lasallecrm\Lasallecrmemail\Models\Email_message
     */
    protected $model;

    /**
     * @var Lasallecrm\Lasallecrmemail\Repositories\Email_messageRepository
     */
    protected $repository;

    /**
     * @var Lasallecrm\Lasallecrmemail\Repositories\Email_attachmentRepository
     */
    protected $email_attachmentRepository;

    /**
     * @var Lasallecrm\Lasallecrmemail\Processing\EmailProcessing
     */
    protected $createEmailMessageFormProcessing;


    /**
     * AdminEmailHandlingController constructor.
     *
     * @param Email_message              $model
     * @param Email_messageRepository    $email_messageRepository
     * @param Email_attachmentRepository $email_attachmentRepository
     * @param EmailProcessing            $emailProcessing
     */
    public function __construct(
        Email_message              $model,
        Email_messageRepository    $repository,
        Email_attachmentRepository $email_attachmentRepository,
        EmailProcessing            $emailProcessing
    ) {

        // execute AdminController's construct method first in order to run the middleware
        parent::__construct();

        // Inject the email_message model
        $this->model                       = $model;

        // Inject repositories
        $this->repository                  = $repository;
        $this->email_attachmentRepository  = $email_attachmentRepository;

        // Inject the relevant model into the email_message repository
        //$this->repository->injectModelIntoRepository($this->model->model_namespace."\\".$this->model->model_class);

        $this->emailProcessing             = $emailProcessing;
    }


    /**
     * Display emails
     * GET /admin/emailhandling/index
     *
     * @return Response
     */
    public function index() {

        // Is this user allowed to do this?
        if (!$this->repository->isUserAllowed('index')) {
            Session::flash('status_code', 400 );
            $message = "You are not allowed to view the list of ".$this->tableName;
            Session::flash('message', $message);
            return view('formhandling::warnings/' . config('lasallecmsadmin.admin_template_name') . '/user_not_allowed', [
                'package_title'                => $this->model->package_title,
                'table_type_plural'            => $this->model->table,
                'resource_route_name'          => $this->model->resource_route_name,
                'table_type_singular'          => strtolower($this->model->model_class),
                'HTMLHelper'                   => HTMLHelper::class,
            ]);
        }

        // If this user has locked records for this table, then unlock 'em
        $this->repository->unlockMyRecords($this->model->table);

        $records = $this->repository->getEmailMessagesForAdminIndex(Auth::user()->id);

        return view('lasallecrmemail::admin/emailhandling/index',
            [
                'records'                      => $records ,
                'package_title'                => $this->model->package_title,
                'table_name'                   => $this->model->table,
                'model_class'                  => $this->model->model_class,
                'resource_route_name'          => $this->model->resource_route_name,
                'DatesHelper'                  => DatesHelper::class,
                'HTMLHelper'                   => HTMLHelper::class,
                'Config'                       => Config::class,
                'admin_template_name'          => config('lasallecmsadmin.admin_template_name'),
            ]);
    }

    /**
     * CREATE form
     * get /admin/emailhandling/create
     *
     * @return response
     */
    public function create() {

        // Is this user allowed to do this?
        if (!$this->repository->isUserAllowed('create')) {
            Session::flash('status_code', 400 );
            $message = "You are not allowed to create ".$this->model->table;
            Session::flash('message', $message);
            return view('formhandling::warnings/' . config('lasallecmsadmin.admin_template_name') . '/user_not_allowed', [
                'package_title'                => $this->model->package_title,
                'table_type_plural'            => $this->model->table,
                'resource_route_name'          => $this->model->resource_route_name,
                'table_type_singular'          => strtolower($this->model->model_class),
                'HTMLHelper'                   => HTMLHelper::class,
            ]);
        }

        return view('lasallecrmemail::admin/emailhandling/create',
        [
            'user'                         => Auth::user(),
            'repository'                   => $this->repository,
            'package_title'                => $this->model->package_title,
            'table_name'                   => $this->model->table,
            'model_class'                  => $this->model->model_class,
            'resource_route_name'          => $this->model->resource_route_name,
            'DatesHelper'                  => DatesHelper::class,
            'HTMLHelper'                   => HTMLHelper::class,
            'Config'                       => Config::class,
            'admin_template_name'          => config('lasallecmsadmin.admin_template_name'),
        ]);
    }

    /**
     * Store a newly created resource in storage
     * POST admin/emailhandling/create
     *
     * @param  Request   $request
     * @return Response
     */
    public function store(Request $request) {

        // Get a washed array of the create form's input fields
        $data = $this->emailProcessing->washCreateForm($request);

        // Validate the create form's input fields
        $validator = $this->emailProcessing->validateCreateForm($data);

        // Did validate pass or fail?
        if ($validator->fails()) {
            return redirect('admin/emailhandling/create')
                ->withErrors($validator)
                ->withInput()
            ;
        }

        // Populate the fields
        $data = $this->emailProcessing->populateCreateFields($data);

        // INSERT the record
        $savedOk = $this->repository->insertNewRecord($data);

        // Assuming the INSERT succeeded !
        $message =  "You successfully created your new email message!";
        Session::flash('message', $message);
        Session::flash('status_code', '200' );

        return Redirect::route('admin.emailhandling.index');
    }

    /**
     * Display the specified record
     * GET /admin/emailhandling/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {

        // Mark this email as read
        $this->repository->markEmailAsRead($id);

        return view('lasallecrmemail::admin/emailhandling/show',
            [
                'record'                       => $this->repository->getFind($id),
                'records_attachments'          => $this->email_attachmentRepository->getEmailAttachmentsForAdminShow($id),
                'package_title'                => $this->model->package_title,
                'table_name'                   => $this->model->table,
                'model_class'                  => $this->model->model_class,
                'resource_route_name'          => $this->model->resource_route_name,
                'DatesHelper'                  => DatesHelper::class,
                'HTMLHelper'                   => HTMLHelper::class,
                'ImagesHelper'                 => ImagesHelper::class,
                'Config'                       => Config::class,
                'admin_template_name'          => config('lasallecmsadmin.admin_template_name'),
            ]);
    }
}