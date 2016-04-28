<?php

namespace Lasallecms\Lasallecmsemail\Processing;

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



///////////////////////////////////////////////////////////////////
///            THIS IS A COMMAND HANDLER                        ///
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
///  NOTE: THE REPOSITORY IS THE BASE REPOSITORY, NOT A         ///
///  REPOSITORY SPECIFIC TO THE MODEL. THE REASON IS TO         ///
///  FACILITATE AUTOMATION OF ADMIN FORMS. YOU CAN ALWAYS       ///
///  DO A MODEL-SPECIFIC REPOSITORY IF NEED BE.                 ///
///////////////////////////////////////////////////////////////////
// LaSalle Software
use Lasallecms\Lasallecmsemail\Repositories\Email_messageRepository;
use Lasallecms\Lasallecmsadmin\FormProcessing\BaseFormProcessing;


/*
 * Process a deletion.
 *
 * FYI: BaseFormProcessing implements the FormProcessing interface.
 */
class DeleteEmail_messageFormProcessing extends BaseFormProcessing
{
    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Repositories\PostRepository
     */
    protected $repository;

    ///////////////////////////////////////////////////////////////////
    /// SPECIFY THE TYPE OF PERSIST THAT IS GOING ON HERE:          ///
    ///  * "create"  for INSERT                                     ///
    ///  * "update   for UPDATE                                     ///
    ///  * "destroy" for DELETE                                     ///
    ///////////////////////////////////////////////////////////////////

    /*
     * Type of persist
     *
     * @var string
     */
    protected $type = "destroy";

    ///////////////////////////////////////////////////////////////////
    /// SPECIFY THE FULL NAMESPACE AND CLASS NAME OF THE MODEL      ///
    ///////////////////////////////////////////////////////////////////
    /*
     * Namespace and class name of the model
     *
     * @var string
     */
    protected $namespaceClassnameModel = "Lasallecms\Lasallecmsemail\Models\Email_message";


    ///////////////////////////////////////////////////////////////////
    ///   USUALLY THERE IS NOTHING ELSE TO MODIFY FROM HERE ON IN   ///
    ///////////////////////////////////////////////////////////////////

    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsemail\Repositories\Email_messageRepository
     */
    public function __construct(Email_messageRepository $repository) {
        $this->repository = $repository;
    }

    /*
     * The processing steps.
     *
     * @param  The command bus object   $deletePostCommand
     * @return The custom response array
     */
    public function quarterback($id) {

        // DELETE record
        if (!$this->persist($id, $this->type))
        {
            // Prepare the response array, and then return to the edit form with error messages
            // Laravel's https://github.com/laravel/framework/blob/5.0/src/Illuminate/Database/Eloquent/Model.php
            //  does not prepare a MessageBag object, so we'll whip up an error message in the
            //  originating controller
            return $this->prepareResponseArray('persist_failed', 500, $id);
        }
        // Prepare the response array, and then return to the command
        return $this->prepareResponseArray('create_successful', 200, $id);
    }
}
