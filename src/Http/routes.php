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
 * @package    Email handling package for the LaSalle Customer Relationship Management package
 * @link       http://LaSalleCRM.com
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */


/* ===================================================================================================================
   Please note that for webhooks from Mailgun (or whatever API calling a LaSalle Software endpoint),
   app/Http/Middleware/VerifyCsrfToken.php "except" property must be set for route groups related to those endpoints.

   Here is an example:

        namespace App\Http\Middleware;

        use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

        class VerifyCsrfToken extends BaseVerifier
        {
             protected $except = [
                'email/*'
             ];
        }
   =================================================================================================================== */


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Front end routes
Route::group(array('prefix' => 'email'), function() {

    // Standard inbound processing
    Route::post('inboundemailstandardhandling', [
        'as'   => 'inboundEmailMailgunController',
        'uses' => 'inboundEmailMailgunController@inboundStandardHandling'
    ]);

    // A custom inbound processing
    Route::post('inboundemailcustomhandling', [
        'as'   => 'inboundEmailCustomMailgunController',
        'uses' => 'CustomInboundEmailMailgunController@inboundCustomHandling'
    ]);
});

// This route is for the custom inbound handling... that the uploaded pics are viewable by logged in customers
Route::group(array('prefix' => 'customercare'), function() {
    Route::get('displayorders', [
        'as'   => 'FrontendCustomerCareDashboard',
        'uses' => 'FrontendCustomerCareDashboardController@displayAllAlternatesortstring1Links'
    ]);

    Route::get('displayorderupdates/{alternatesortstring1}',
        'FrontendCustomerCareDashboardController@displaySingleAlternatesortstring1');
});


// Admin routes
Route::group(array('prefix' => 'admin'), function()
{
    // Email Messages
    Route::resource('emailhandling', 'AdminEmailHandlingController');
    Route::post('emailhandling/confirmDeletion/{id}', 'AdminEmailHandlingController@confirmDeletion');
    Route::post('emailhandling/confirmDeletionMultipleRows', 'AdminEmailHandlingController@confirmDeletionMultipleRows');
    Route::post('emailhandling/destroyMultipleRecords', 'AdminEmailHandlingController@destroyMultipleRecords');
});



// Front end test routes
/*
Route::get('tests/inboundMailgunWebhook', [
    'as'   => 'test.inboundMailgunWebhook',
    'uses' => 'Tests\InboundMailgunWebhookTest@testViewInboundMailgunWebhook'
]);
*/



