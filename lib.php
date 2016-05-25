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

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

class CategoryBackup {

    private $active = null;
    private $categories = null;
    // courses in selected categories
    private $courses = null;
    // 01/19/2038 @ 3:03am (UTC)
    private $FAR_FUTURE = 2147483000;

    function __construct() {
        require_once '../../config.php';
        global $CFG;

        $this->active = $CFG->local_categorybackup_active;
        $this->categories = explode(',', $CFG->local_categorybackup_categories);

        $this->collectCoursesInCategories();

        $this->deactivateBackup();
    }

    /**
     * Collects recursivly all courses in the selected categories
     * 
     * (The courses to backup)
     * 
     * @global type $CFG
     */
    private function collectCoursesInCategories() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        $courses = array();
        foreach ($this->categories as $id) {
            $courses_tmp = coursecat::get($id, IGNORE_MISSING, true)->get_courses(array('recursive' => true));
            $courses = $courses + $courses_tmp;
        }
        $this->courses = $courses;
    }

    /**
     * Deactivates the backup for all courses that are not in one of the selected categories
     * 
     * Deactivation means: Setting the next date for backup to one in the far future
     */
    public function deactivateBackup() {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/backup/util/helper/backup_cron_helper.class.php';

        $rs = $DB->get_recordset('course');
        foreach ($rs as $course) {
            $course_to_backup = isset($this->courses[$course->id]);

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
                $backupcourse->nextstarttime = $this->FAR_FUTURE;
                $DB->update_record('backup_courses', $backupcourse);
            } else {
                echo $course->id . "\t" . $course->fullname . "\n";
                if ($backupcourse && $backupcourse->nextstarttime == $this->FAR_FUTURE) {
                    $backupcourse->nextstarttime = 0;
                    $DB->update_record('backup_courses', $backupcourse);
                }
            }
        }
        $rs->close();
    }

}
