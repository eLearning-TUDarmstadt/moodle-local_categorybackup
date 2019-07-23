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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

class CategoryBackup {

    // True: Selective backup active
    private $active = null;
    private $categories = null;
    // courses in selected categories
    private $courses = null;
    // 01/19/2038 @ 3:03am (UTC)
    private $FAR_FUTURE = 2147483000;

    function __construct() {
        global $CFG;
        // Get config data
        $this->active = $CFG->local_categorybackup_active;
        $this->categories = explode(',', $CFG->local_categorybackup_categories);
    }

    public function cron() {
        if ($this->active) {
            echo "Selective backup active...\n";
            $this->collectCoursesInCategories();
            $this->deactivateBackup();
        } else {
            echo "Selective backup NOT active...\n";
            $this->activateBackup();
        }
    }

    /**
     * Sets the FAR_FUTURE date for the next run to zero
     * (Create backup in the next run)
     */
    private function activateBackup() {
        require_once '../../config.php';
        global $DB;

        $sql = "UPDATE {backup_courses} SET nextstarttime=0 WHERE nextstarttime=" . $this->FAR_FUTURE;

        $DB->execute($sql);
    }

    /**
     * Collects recursivly all courses in the selected categories
     *
     * (The courses to backup)
     */
    private function collectCoursesInCategories() {
        $courses = array();
        foreach ($this->categories as $id) {
            $courses_tmp = \core_course_category::get($id, IGNORE_MISSING, true)->get_courses(array('recursive' => true));
            $courses = $courses + $courses_tmp;
        }
        $this->courses = $courses;
    }

    /**
     * Deactivates the backup for all courses that are not in one of the selected categories
     *
     * Deactivation means: Setting the next date for backup to one in the far future
     */
    private function deactivateBackup() {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/backup/util/helper/backup_cron_helper.class.php';

        $rs = $DB->get_recordset('course');

        $now = time();
        foreach ($rs as $course) {
            $course_to_backup = isset($this->courses[$course->id]) && $course->id != 1;

            // Try to get schedule record for course
            $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));

            if (!$course_to_backup) {
                // No record yet...
                if (!$backupcourse) {
                    $backupcourse = new stdClass;
                    $backupcourse->courseid = $course->id;
                    $DB->insert_record('backup_courses', $backupcourse);
                    $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));
                }
                // Set date and update
                $backupcourse->laststatus = 1; // OK
                $backupcourse->laststarttime = $now;
                $backupcourse->lastendtime = $now;
                $backupcourse->nextstarttime = $this->FAR_FUTURE;
                $DB->update_record('backup_courses', $backupcourse);
            } else {
                echo $course->id . "\t" . $course->fullname . "\n";
                // No record yet...
                if (!$backupcourse) {
                    $backupcourse = new stdClass;
                    $backupcourse->courseid = $course->id;
                    $backupcourse->laststatus = 5; // Not yet run
                    $DB->insert_record('backup_courses', $backupcourse);
                    $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));
                }
                if ($backupcourse->nextstarttime == $this->FAR_FUTURE) {
                    $backupcourse->nextstarttime = 0;
                    $backupcourse->laststarttime = 0;
                    $backupcourse->lastendtime = 0;
                    $backupcourse->laststatus = 5; // Not yet run
                }
                $DB->update_record('backup_courses', $backupcourse);
            }
        }
        $rs->close();
    }

}
