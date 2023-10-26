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

namespace enrol_idnumber\task;

/**
 * Sync enrolment task.
 *
 * @package    enrol_idnumber
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_enrolments_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('sync_enrolments_task', 'enrol_idnumber');
    }

    /**
     * Run cron.
     *
     * @return int
     */
    public function execute(): int {
        global $CFG;

        require_once("$CFG->dirroot/enrol/idnumber/lib.php");

        // Instance of enrol_idnumber_plugin.
        $plugin = enrol_get_plugin('idnumber');
        $trace  = new \text_progress_trace();
        $result = $plugin->sync($trace);
        $trace->finished();
        return $result;
    }
}
