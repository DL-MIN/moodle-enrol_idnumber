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

use core\event\user_loggedin;

/**
 * Course ID number enrolment plugin implementation.
 *
 * @package    enrol_idnumber
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_idnumber_plugin extends enrol_plugin {

    /**
     * SQL statement to get enrolments by course ID number.
     */
    protected const SQL_ENROL_BY_IDNUMBER = "SELECT DISTINCT e.*, c.idnumber
                                             FROM {enrol} e RIGHT JOIN {course} c ON c.id = e.courseid
                                             WHERE e.enrol = 'idnumber' AND e.status = 0 AND c.idnumber = ?";

    /**
     * SQL statement to get enrolments by ID.
     */
    protected const SQL_ENROL_BY_ID = "SELECT DISTINCT e.*, c.idnumber
                                       FROM {enrol} e RIGHT JOIN {course} c ON c.id = e.courseid
                                       WHERE e.enrol = 'idnumber' AND e.status = 0 AND c.idnumber != '' AND e.id = ?";

    /**
     * SQL statement to get all enrolments.
     */
    protected const SQL_ENROL_ALL = "SELECT DISTINCT e.*, c.idnumber
                                     FROM {enrol} e RIGHT JOIN {course} c
                                     ON c.id = e.courseid
                                     WHERE e.enrol = 'idnumber' AND e.status = 0 AND c.idnumber != ''";

    /**
     * Called from the event observer on user login.
     *
     * @param user_loggedin $event
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function user_loggedin(user_loggedin $event) {
        if (!enrol_is_enabled('idnumber')) {
            return;
        }

        // Instance of enrol_idnumber_plugin.
        $plugin = enrol_get_plugin('idnumber');
        $plugin->process_user_enrolments($event->userid);
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param object $course
     * @param object $data form data
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function course_updated($inserted, $course, $data) {
        global $DB;

        parent::course_updated($inserted, $course, $data);

        if (!$inserted) {
            $enrolid = $DB->get_field('enrol', 'id', ['enrol' => 'idnumber', 'courseid' => $course->id]);
            $this->process_course_enrolments(new \null_progress_trace(), $enrolid);
        }
    }

    /**
     * Add new instance of enrol plugin.
     *
     * @param object $course
     * @param array|null $fields
     * @return int id of new instance, null if can not be created
     * @throws coding_exception
     * @throws dml_exception
     */
    public function add_instance($course, array $fields = null) {
        $return = parent::add_instance($course, $fields);
        if ($return) {
            self::process_course_enrolments(new \null_progress_trace(), $return);
        }
        return $return;
    }

    /**
     * Update instance of enrol plugin.
     *
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @since Moodle 3.1
     */
    public function update_instance($instance, $data) {
        $return = parent::update_instance($instance, $data);
        self::process_course_enrolments(new \null_progress_trace(), $instance->id);
        return $return;
    }

    /**
     * Execute synchronisation.
     *
     * @param progress_trace $trace
     * @return int exit code, 0 means ok, 2 means plugin disabled
     * @throws coding_exception
     * @throws dml_exception
     */
    public function sync(progress_trace $trace): int {
        if (!enrol_is_enabled('idnumber')) {
            return 2;
        }

        $this->process_course_enrolments($trace);

        return 0;
    }

    /**
     * Check and enrol a specific user into courses.
     *
     * @param string $userid
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function process_user_enrolments(string $userid) {
        global $DB;

        if (!$userid) {
            new coding_exception('Invalid $userid in process_user_enrolments()');
        }

        // Fetch user.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        profile_load_data($user);
        $profilefieldname = 'profile_field_' . $this->get_config('profilefield');
        $profilefieldsep  = $this->get_config('profilefield_separator');

        // Iterate through profile field items.
        foreach (explode($profilefieldsep, $user->$profilefieldname) as $item) {
            $enrol = $DB->get_record_sql(self::SQL_ENROL_BY_IDNUMBER, [$item]);
            if ($enrol) {
                $this->enrol_user_by_id(new \null_progress_trace(), $userid, $enrol);
            }
        }
    }

    /**
     * Check and enrol users into courses, optionally by enrolment ID.
     *
     * @param progress_trace $trace
     * @param int $enrolid
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function process_course_enrolments(progress_trace $trace, int $enrolid = 0) {
        global $DB;

        $sep = $this->get_config('profilefield_separator');
        $sql = "SELECT DISTINCT *
                FROM (SELECT u.id, {$DB->sql_concat("'{$sep}'", 'uid.data', "'{$sep}'")} AS courseids
                      FROM {user} u
                               RIGHT JOIN {user_info_data} uid ON u.id = uid.userid
                      WHERE u.confirmed = 1
                        AND u.deleted = 0
                        AND u.suspended = 0
                        AND uid.fieldid = (SELECT DISTINCT id FROM {user_info_field} WHERE shortname = ?)) AS q
                WHERE q.courseids LIKE {$DB->sql_concat("'%{$sep}'", '?', "'{$sep}%'")} ";

        $trace->output('Starting user enrolment synchronisation...');

        if (!$enrolid) {
            $enrols = $DB->get_records_sql(self::SQL_ENROL_ALL);
        } else {
            $enrols = $DB->get_records_sql(self::SQL_ENROL_BY_ID, [$enrolid]);
        }

        if (!$enrols) {
            new coding_exception('Invalid $courseid or missing enrolment process_course_enrolments()');
        }

        foreach ($enrols as $enrol) {
            $users = $DB->get_records_sql($sql, [$this->get_config('profilefield'), $enrol->idnumber]);
            foreach ($users ?? [] as $user) {
                $this->enrol_user_by_id($trace, $user->id, $enrol);
            }

        }
    }

    /**
     * Enrol user by enrolment ID.
     *
     * @param progress_trace $trace
     * @param string $userid
     * @param object $enrol
     * @throws coding_exception
     */
    protected function enrol_user_by_id(progress_trace $trace, string $userid, object $enrol) {
        if (!is_enrolled(context_course::instance($enrol->courseid), $userid)) {
            $this->enrol_user($enrol, $userid, $enrol->roleid);
            $trace->output("enrolling: $userid ==> $enrol->courseid via idnumber $enrol->idnumber", 1);
        }
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $DB;

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_idnumber'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_idnumber');
        $mform->setDefault('status', $this->get_config('status'));

        $roles = $this->get_roleid_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'enrol_idnumber'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));

        $course = $DB->get_record('course', ['id' => $instance->courseid]);
        if ($course) {
            $a = ['settings'  => new moodle_url('/course/edit.php', ['id' => $course->id]),
                  'idnumber'  => $course->idnumber,
                  'startdate' => userdate($course->startdate),
            ];

            $mform->addElement('static', 'idnumbercourse', get_string('idnumbercourse'),
                    get_string(empty($course->idnumber) ? 'idnumbercourse_unset' : 'idnumbercourse_set', 'enrol_idnumber',
                            $a));
            $mform->addHelpButton('idnumbercourse', 'idnumbercourse');

            $mform->addElement('static', 'startdatecourse', get_string('startdate'),
                    get_string('startdatecourse_set', 'enrol_idnumber', $a));
            $mform->addHelpButton('startdatecourse', 'startdate');
        }
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;

        $errors = [];

        $validstatus = array_keys($this->get_status_options());
        $validroles  = array_keys($this->get_roleid_options($instance, $context));
        $tovalidate  = [
                'status' => $validstatus,
                'roleid' => $validroles,
        ];

        $course = $DB->get_record('course', ['id' => $instance->courseid]);

        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors     = array_merge($errors, $typeerrors);

        if ($data['status'] == ENROL_INSTANCE_ENABLED && empty($course->idnumber)) {
            $errors['idnumbercourse'] = get_string('idnumbercourse_error', 'enrol_idnumber');
        }

        return $errors;
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     * @throws coding_exception
     */
    protected function get_status_options() {
        $options = [ENROL_INSTANCE_ENABLED  => get_string('yes'),
                    ENROL_INSTANCE_DISABLED => get_string('no'),
        ];
        return $options;
    }

    /**
     * Return an array of valid options for the roleid.
     *
     * @param stdClass $instance
     * @param context $context
     * @return array
     */
    protected function get_roleid_options($instance, $context) {
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        return $roles;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     *
     * @param stdClass $course
     * @return int id of new instance
     * @throws coding_exception
     * @throws dml_exception
     */
    public function add_default_instance($course) {
        $fields = $this->get_instance_defaults();
        return $this->add_instance($course, $fields);
    }

    /**
     * Returns defaults for new instances.
     *
     * @return array
     * @since Moodle 3.1
     */
    public function get_instance_defaults() {
        $fields           = [];
        $fields['status'] = $this->get_config('status');
        $fields['roleid'] = $this->get_config('roleid');

        return $fields;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means user with 'enrol/idnumber:unenrol' may unenrol others freely, false means nobody may touch
     *         user_enrolments
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means it is possible to change enrol period and status in user_enrolments table
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Return whether or not, given the current state, it is possible to add a new instance
     * of this enrolment plugin to the course.
     *
     * @param int $courseid
     * @return boolean
     * @throws coding_exception
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        return (has_capability('moodle/course:enrolconfig', $context) && has_capability('enrol/idnumber:config', $context));
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     * @throws coding_exception
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/idnumber:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     * @throws coding_exception
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/idnumber:config', $context);
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     * @throws coding_exception
     * @throws dml_exception
     * @throws restore_step_exception
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;

        if ($instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => $this->get_name()])) {
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $userid
     * @param int $oldinstancestatus
     * @throws coding_exception
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    /**
     * This returns false for backwards compatibility, but it is really recommended.
     *
     * @return bool
     */
    public function use_standard_editing_ui() {
        return true;
    }
}

