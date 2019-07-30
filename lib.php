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

/**
 * This plugin sets the nextstarttime value of the backup_courses table high or low, depending on the settings.
 *
 */
class CategoryBackup {

    private $active = null; // True: Selective backup is actived in settings.
    private $categories = null;
    private $courses = null; // Courses in selected categories.
    private $FAR_FUTURE = 2147483000; // 01/19/2038 @ 3:03am (UTC).

    function __construct() {
        global $CFG;
        // Get config data.
        $this->active = $CFG->local_categorybackup_active;
        $this->categories = explode(',', $CFG->local_categorybackup_categories);
    }

    public function cron() {
        if ($this->active) {
            echo "\nSelective backup is actived in settings. Activate backups for the following courses: \n\n";
            $this->collectCoursesInCategories();
            $this->deactivateBackup();
        } else {
            echo "\nSelective backup is NOT actived in settings. Activate backups for all courses. \n\n";
            $this->activateBackup();
        }
    }

    /**
     * Sets the FAR_FUTURE date for the next run to zero
     * (Create backup in the next run)
     */
    private function activateBackup() {
        global $DB;

        // If this plugin set the nextstarttime in the past, make it be backed up in the next automated backup.
        $sql = "UPDATE {backup_courses} SET nextstarttime=0 WHERE nextstarttime=" . $this->FAR_FUTURE;

        $DB->execute($sql);
    }

    /**
     * Collects recursivly all courses in the selected categories
     *
     * (Returns the courses to backup in class variable)
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
    private function deactivateBackup() {
        global $CFG, $DB;
        require_once $CFG->dirroot . '/backup/util/helper/backup_cron_helper.class.php';

        $rs = $DB->get_recordset('course');

        $now = time();
        echo "ID \t | \t Course fullname \n"; // Table header.
        echo "-------------------------- \n";
        foreach ($rs as $course) {
            // Should a backup be created?
            $course_to_backup = isset($this->courses[$course->id]) && $course->id != 1;

            // Get past schedule record for course.
            $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));

            if (!$course_to_backup) {
                // The course is not in one of the selected categories.
                if (!$backupcourse) {
                    // Create new record for this course.
                    $backupcourse = new stdClass;
                    $backupcourse->courseid = $course->id;
                    $DB->insert_record('backup_courses', $backupcourse);
                    $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));
                }
                // Set date and update.
                $backupcourse->laststatus = 1; // OK.
                $backupcourse->laststarttime = $now;
                $backupcourse->lastendtime = $now;
                $backupcourse->nextstarttime = $this->FAR_FUTURE;
                $DB->update_record('backup_courses', $backupcourse);
            } else {
                // The course should be backed up the next time the automated backups are run.
                echo $course->id . "\t | \t " . $course->fullname . "\n";
                if (!$backupcourse) {
                    // Create new record for this course.
                    $backupcourse = new stdClass;
                    $backupcourse->courseid = $course->id;
                    $backupcourse->laststatus = 5; // Not yet run.
                    $DB->insert_record('backup_courses', $backupcourse);
                    $backupcourse = $DB->get_record('backup_courses', array('courseid' => $course->id));
                }
                if ($backupcourse->nextstarttime == $this->FAR_FUTURE) {
                    $backupcourse->nextstarttime = 0;
                    $backupcourse->laststarttime = 0;
                    $backupcourse->lastendtime = 0;
                    $backupcourse->laststatus = 5; // Not yet run.
                }
                $DB->update_record('backup_courses', $backupcourse);
            }
        }
        echo "\n";
        $rs->close();
    }

}
