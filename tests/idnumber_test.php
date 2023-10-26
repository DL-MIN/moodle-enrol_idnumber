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

namespace enrol_idnumber;

/**
 * Unit tests for the enrol_idnumber.
 *
 * @package    enrol_idnumber
 * @category   test
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idnumber_test extends \advanced_testcase {

    /**
     * Enable enrol plugin.
     */
    protected function enable_plugin() {
        $enabled             = enrol_get_plugins(true);
        $enabled['idnumber'] = true;
        $enabled             = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * Disable enrol plugin.
     */
    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['idnumber']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * Run basic tests to ensure plugin can be enabled/disabled.
     *
     * @covers \enrol_idnumber_plugin
     */
    public function test_basics() {
        $this->assertFalse(enrol_is_enabled('idnumber'));
        $plugin = enrol_get_plugin('idnumber');
        $this->assertInstanceOf('enrol_idnumber_plugin', $plugin);
        $this->assertEquals(current(get_archetype_roles('student'))->id, get_config('enrol_idnumber', 'roleid'));
        $this->assertEquals(',', get_config('enrol_idnumber', 'profilefield_separator'));
    }

    /**
     * Just make sure the sync does not throw any errors when nothing to do.
     *
     * @covers \enrol_idnumber_plugin
     */
    public function test_sync_nothing() {
        $this->resetAfterTest();

        $this->disable_plugin();
        $plugin = enrol_get_plugin('idnumber');
        $this->assertNotEmpty($plugin);

        $plugin->sync(new \null_progress_trace());
        $this->enable_plugin();
        $plugin->sync(new \null_progress_trace());
    }

    /**
     * Test enrolment sync by creating courses and users.
     *
     * @covers \enrol_idnumber_plugin
     */
    public function test_sync() {
        global $DB;

        $this->resetAfterTest();

        $plugin = enrol_get_plugin('idnumber');
        $this->enable_plugin();

        // Create a new profile field.
        $profilefield = ['datatype' => 'text', 'shortname' => 'testidnumbers', 'name' => 'testidnumbers'];
        $DB->insert_record('user_info_field', (object) $profilefield);

        // Get roles.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->assertNotEmpty($managerrole);

        // Configure plugin.
        $separator = array_rand(array_flip([',', ';', '@', '|', '/', '$', '%', '+', ':']));
        $plugin->set_config('status', ENROL_INSTANCE_DISABLED);
        $plugin->set_config('profilefield', 'testidnumbers');
        $plugin->set_config('profilefield_separator', $separator);
        $this->assertEquals($separator, $plugin->get_config('profilefield_separator'));

        // Create courses.
        $idnumbercourse = array_map(fn() => rand(10, 100000000), array_fill(0, 3, null));
        $course1        = $this->getDataGenerator()->create_course(['idnumber' => $idnumbercourse[0]]);
        $course2        = $this->getDataGenerator()->create_course(['idnumber' => $idnumbercourse[1]]);
        $course3        = $this->getDataGenerator()->create_course();

        // Create enrolments.
        $plugin->add_instance($course1, ['status' => ENROL_INSTANCE_ENABLED, 'roleid' => $studentrole->id]);
        $plugin->add_instance($course2, ['status' => ENROL_INSTANCE_ENABLED, 'roleid' => $teacherrole->id]);
        $plugin->add_instance($course3, ['status' => ENROL_INSTANCE_ENABLED, 'roleid' => $managerrole->id]);

        // User 1.
        $user1idnumbers   = array_map(fn() => rand(10, 100000000), array_fill(0, 10, null));
        $user1idnumbers[] = $idnumbercourse[0];
        shuffle($user1idnumbers);
        $user1 = $this->getDataGenerator()->create_user(['profile_field_testidnumbers' => implode($separator, $user1idnumbers)]);

        // User 2.
        $user2idnumbers   = array_map(fn() => rand(10, 100000000), array_fill(0, 2, null));
        $user2idnumbers[] = $idnumbercourse[1];
        shuffle($user2idnumbers);
        $user2 = $this->getDataGenerator()->create_user(['profile_field_testidnumbers' => implode($separator, $user2idnumbers)]);

        // User 3-5.
        $user3 = $this->getDataGenerator()->create_user(['profile_field_testidnumbers' => $idnumbercourse[0]]);
        $user4 = $this->getDataGenerator()->create_user(['profile_field_testidnumbers' => '']);
        $user5 = $this->getDataGenerator()->create_user(['profile_field_testidnumbers' => implode($separator, $idnumbercourse)]);

        $this->assertEquals(4, $DB->count_records('course'));
        $this->assertEquals(6, $DB->count_records('enrol', ['enrol' => 'idnumber']));
        $this->assertEquals(0, $DB->count_records('user_enrolments'));

        // Synchronize all courses/users.
        $plugin->sync(new \null_progress_trace());

        $this->assertEquals(5, $DB->count_records('user_enrolments'));

        // Course 1.
        $this->assert_is_enrolled($course1->id, $user1->id, $studentrole->id, ENROL_USER_ACTIVE);
        $this->assert_is_not_enrolled($course1->id, $user2->id);
        $this->assert_is_enrolled($course1->id, $user3->id, $studentrole->id, ENROL_USER_ACTIVE);
        $this->assert_is_not_enrolled($course1->id, $user4->id);
        $this->assert_is_enrolled($course1->id, $user5->id, $studentrole->id, ENROL_USER_ACTIVE);

        // Course 2.
        $this->assert_is_not_enrolled($course2->id, $user1->id);
        $this->assert_is_enrolled($course2->id, $user2->id, $teacherrole->id, ENROL_USER_ACTIVE);
        $this->assert_is_not_enrolled($course2->id, $user3->id);
        $this->assert_is_not_enrolled($course2->id, $user4->id);
        $this->assert_is_enrolled($course2->id, $user5->id, $teacherrole->id, ENROL_USER_ACTIVE);

        // Course 3.
        $this->assert_is_not_enrolled($course3->id, $user1->id);
        $this->assert_is_not_enrolled($course3->id, $user2->id);
        $this->assert_is_not_enrolled($course3->id, $user3->id);
        $this->assert_is_not_enrolled($course3->id, $user4->id);
        $this->assert_is_not_enrolled($course3->id, $user5->id);
    }

    /**
     * Assert that the user is enrolled in a specific course with a given role.
     *
     * @param int $courseid
     * @param int $userid
     * @param int $roleid
     * @param int|null $status
     */
    public function assert_is_enrolled(int $courseid, int $userid, int $roleid, int|null $status = null) {
        global $DB;

        $context  = \context_course::instance($courseid);
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'idnumber', 'status' => ENROL_INSTANCE_ENABLED]);

        $this->assertNotEmpty($instance);
        $ue = $DB->get_record('user_enrolments', ['enrolid' => $instance->id, 'userid' => $userid]);
        $this->assertNotEmpty($ue);
        if (isset($status)) {
            $this->assertEquals($status, $ue->status);
        }
        if ($roleid) {
            $this->assertTrue($DB->record_exists('role_assignments',
                    ['contextid' => $context->id, 'userid' => $userid, 'roleid' => $roleid, 'component' => 'enrol_idnumber']));
        } else {
            $this->assertFalse($DB->record_exists('role_assignments',
                    ['contextid' => $context->id, 'userid' => $userid, 'component' => 'enrol_idnumber']));
        }
    }

    /**
     * Assert that the user is not enrolled in a specific course.
     *
     * @param int $courseid
     * @param int $userid
     */
    public function assert_is_not_enrolled(int $courseid, int $userid) {
        $context = \context_course::instance($courseid);
        $this->assertFalse(is_enrolled($context, $userid));
    }
}

