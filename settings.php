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
 * Settings.
 *
 * @package    enrol_idnumber
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Fields to use in the selector.
    $profilefields = $DB->get_records('user_info_field');
    if ($profilefields) {
        $options = [];
        foreach ($profilefields as $item) {
            $options[$item->shortname] = $item->name;
        }

        if (!array_key_exists(get_config('enrol_idnumber', 'profilefield'), $options) &&
                !(defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
            \core\notification::warning(
                    get_string('no_profile_field_selected', 'enrol_idnumber', $CFG->wwwroot . '/user/profile/index.php')
            );
        }

        $settings->add(new admin_setting_configmultiselect(
                'enrol_idnumber/profilefield',
                get_string('profilefield', 'enrol_idnumber'),
                get_string('profilefield_desc', 'enrol_idnumber'),
                [],
                $options
        ));
    } else if (!(defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
        \core\notification::warning(get_string('no_custom_field', 'enrol_idnumber', $CFG->wwwroot . '/user/profile/index.php'));
    }

    // Field separator.
    $settings->add(new admin_setting_configtext('enrol_idnumber/profilefield_separator',
            get_string('profilefield_separator', 'enrol_idnumber'), get_string('profilefield_separator_desc', 'enrol_idnumber'),
            ',', PARAM_TEXT, 5));

    // Default settings.
    $settings->add(new admin_setting_heading('enrol_idnumber_defaults', get_string('enrolinstancedefaults', 'admin'),
            get_string('enrolinstancedefaults_desc', 'admin')));

    // Default enrolment.
    $settings->add(new admin_setting_configcheckbox('enrol_idnumber/defaultenrol',
            get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));

    // Default status.
    $options = [ENROL_INSTANCE_ENABLED  => get_string('yes'),
                ENROL_INSTANCE_DISABLED => get_string('no'),
    ];
    $settings->add(new admin_setting_configselect('enrol_idnumber/status', get_string('status', 'enrol_idnumber'),
            get_string('status_desc', 'enrol_idnumber'), ENROL_INSTANCE_DISABLED, $options));

    // Default role.
    $settings->add(new admin_setting_configselect(
            'enrol_idnumber/roleid',
            get_string('defaultrole', 'enrol_idnumber'),
            get_string('defaultrole_desc', 'enrol_idnumber'),
            current(get_archetype_roles('student'))->id,
            get_default_enrol_roles(context_system::instance())
    ));
}

