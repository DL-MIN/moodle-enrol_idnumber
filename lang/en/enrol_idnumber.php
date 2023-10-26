<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language pack 'en'.
 *
 * @package    enrol_idnumber
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']       = 'Enrol by Course ID number';
$string['pluginname_desc']  = 'Course ID number enrolment plugin synchronises users\' profile fields with course participants.';
$string['privacy:metadata'] = 'The Enrol by Course ID number enrolment plugin does not store any personal data.';

// Settings.
$string['defaultrole']                 = 'Default role';
$string['defaultrole_desc']            = 'Default role used to enrol users with this plugin.';
$string['profilefield']                = 'Profile field';
$string['profilefield_desc']           = 'The custom profile field used for lookups for course ID numbers.';
$string['profilefield_separator']      = 'Profile field separator';
$string['profilefield_separator_desc'] = 'The delimiter symbol used for separation of the specified list items.';
$string['no_custom_field']             =
        'There seems to be no custom profile field. <a href="{$a}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Edit" role="img" aria-label="Edit"></i></a>';
$string['no_profile_field_selected']   = 'No profile field has been selected in the enrol_idnumber plugin settings.';

// Instance settings.
$string['status']               = 'Enable course ID number enrolments';
$string['status_desc']          = 'Allow course access of internally enrolled users. This should be kept enabled in most cases.';
$string['status_help']          =
        'This setting determines whether users can be automatically enrolled if the selected profile field contains the course ID number.';
$string['idnumbercourse_set']   =
        '<div class="alert alert-info" role="alert" data-aria-autofocus="true">{$a->idnumber} <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Edit" role="img" aria-label="Edit"></i></a></div>';
$string['idnumbercourse_unset'] =
        '<div class="alert alert-warning" role="alert" data-aria-autofocus="true">This course doesn\'t have an id number, which is required for this enrollment method. <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Edit" role="img" aria-label="Edit"></i></a></div>';
$string['idnumbercourse_error'] = 'Enrolments can not be enabled without specifying the course idnumber!';
$string['startdatecourse_set']  =
        '<div class="alert alert-info" role="alert" data-aria-autofocus="true">{$a->startdate} <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Edit" role="img" aria-label="Edit"></i></a></div>';

// Tasks.
$string['sync_enrolments_task'] = 'Synchronise enrolments for Enrol by Course ID number';
