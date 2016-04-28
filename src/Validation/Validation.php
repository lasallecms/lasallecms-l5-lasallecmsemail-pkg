<?php

namespace Lasallecms\Lasallecmsemail\Validation;

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


class Validation
{
    /**
     * All attachments have approved file extensions?
     *
     * @param  array  $mappedVars     Inbound POST vars mapped to database fields
     * @return bool
     */
    public function attachmentsHaveApprovedFileExtensions($mappedVars) {

        if ($mappedVars['number_of_attachments'] == 0) {
            return true;
        }

        $approvedFileExtensions = config('lasallecmsemail.inbound_attachments_approved_file_extensions');
        if (empty($approvedFileExtensions)) {
            return true;
        }


        for ($i = 1; $i <= $mappedVars['number_of_attachments']; $i++) {
            $fileExtension = strtolower($mappedVars['attachment-'.$i]->getClientOriginalExtension());

            if (!in_array($fileExtension, $approvedFileExtensions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if emails must come from a list of approved senders. That is, the person sending
     * the email is allowed to send us inbound emails.
     *
     * @param  string   $emailAddress    Email address of the person sending the email
     * @return bool
     */
    public function emailsComeFromListOfApprovedSenders($emailAddress) {

        // are we checking that inbound emails come from a pre-approved list of senders?
        if (!$this->isInboundEmailsFromAllowedSendersOnly()) {

            // we are *not* checking that emails come from a pre-approved list of senders
            return true;
        }

        // yes, we are checking that emails come from a pre-approved list of senders
        if ($this->isInboundEmailsFromAllowedSendersOnlyListOfSsenders($emailAddress)) {

            // the sender is, indeed, on the list of pre-approved senders
            return true;
        }

        // the sender is not on the list of pre-approved senders
        return false;
    }


    /**
     * Does the config setting allow inbound emails from specified senders (email addresses) only?
     *
     * @return bool
     */
    public function isInboundEmailsFromAllowedSendersOnly() {
        return config('lasallecmsemail.inbound_emails_from_allowed_senders_only');
    }

    /**
     * Is the inbound email from an allowed sender?
     *
     * @param  string  $senderEmailAddress   Who is sending us the email?
     * @return bool
     */
    public function isInboundEmailsFromAllowedSendersOnlyListOfSsenders($senderEmailAddress) {
        $allowedSenders = config('lasallecmsemail.inbound_emails_from_allowed_senders_only_list_of_senders');

        return in_array($senderEmailAddress, $allowedSenders);
    }

    /**
     * Did the attachments successfully upload to the local server's /tmp/ folder?
     *
     * @param  array  $mappedVars     Inbound POST vars mapped to database fields
     * @return bool
     */
    public function verifyAttachmentUploadToTmpFolder($mappedVars) {

        for ($i = 1; $i <= $mappedVars['number_of_attachments']; $i++) {

            if (!$mappedVars['attachment-'.$i]->isValid()) {
                return false;
            }
        }

        return true;
    }
}



