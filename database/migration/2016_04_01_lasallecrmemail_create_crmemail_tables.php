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


// Laravel classes
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmemailTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        ///////////////////////////////////////////////////////////////////////
        ////                     Main Table                                ////
        ///////////////////////////////////////////////////////////////////////

        if (!Schema::hasTable('email_messages'))
        {
            Schema::create('email_messages', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');

                // FROM https://github.com/lasallecrm/lasallecrm-l5-todo-pkg
                $table->integer('priority_id')->nullable()->unsigned();
                $table->foreign('priority_id')->references('id')->on('lookup_todo_priority_types');

                $table->string('from_email_address');
                $table->string('from_name')->nullable();
                $table->string('to_email_address');
                $table->string('to_name')->nullable();

                $table->string('subject');
                $table->string('slug');

                $table->text('body');

                $table->boolean('sent')->default(false);
                $table->boolean('sent_timestamp')->nullable();
                $table->boolean('read')->default(false);
                $table->boolean('archived')->default(false);


                $table->timestamp('created_at');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
                $table->timestamp('updated_at');
                $table->integer('updated_by')->unsigned();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->timestamp('locked_at')->nullable();
                $table->integer('locked_by')->nullable()->unsigned();
                $table->foreign('locked_by')->references('id')->on('users');
            });
        }


        ///////////////////////////////////////////////////////////////////////
        ////            Table Relating to the Main Table                   ////
        ///////////////////////////////////////////////////////////////////////

        if (!Schema::hasTable('email_attachments'))
        {
            Schema::create('email_attachments', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();

                $table->integer('email_messages_id')->unsigned()->index();
                $table->foreign('email_messages_id')->references('id')->on('email_messages')->onDelete('cascade');

                $table->string('attachment_path');
                $table->string('attachment_filename');
                $table->text('comments');
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        // Disable foreign key constraints or these DROPs will not work
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');


        ///////////////////////////////////////////////////////////////////////
        ////                     Main Table                                ////
        ///////////////////////////////////////////////////////////////////////

        Schema::table('email_messages', function($table){
            $table->dropIndex('email_messages_title_unique');
            $table->dropForeign('email_messages_user_id_foreign');
            $table->dropForeign('email_messages_priority_id_foreign');
            $table->dropForeign('email_messages_created_by_foreign');
            $table->dropForeign('email_messages_updated_by_foreign');
            $table->dropForeign('email_messages_locked_by_foreign');
        });
        Schema::dropIfExists('email_messages');


        ///////////////////////////////////////////////////////////////////////
        ////            Table Relating to the Main Table                   ////
        ///////////////////////////////////////////////////////////////////////

        Schema::table('email_attachments', function($table){
            $table->dropForeign('email_attachments_email_messages_id_foreign');
        });
        Schema::dropIfExists('email_attachments');


        // Enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
