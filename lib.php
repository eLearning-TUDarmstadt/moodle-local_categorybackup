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
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');


class CategoryBackup {
  private $userId = 8;

  function __construct() {
    /*
    echo "__construct\n";

    $course = 48;
    $userid = 8;
    $cat = coursecat::get(110);
    $cat->
    $bc = new backup_controller(backup::TYPE_1COURSE, $course, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $userid);
    $bc->execute_plan();
    */
   $this->backup(68);
  }

  public function backup($categoryId) {
    global $CFG;
    require_once($CFG->dirroot . "/lib/coursecatlib.php");
    $cat = coursecat::get($categoryId);
    echo "Starting backup for category " . $cat->name . "\n";

    global $DB;

    $sql = "SELECT
            	c.id,
            	c.fullname,
            	c.shortname,
            	ccat.name AS fb,
            	(SELECT name FROM {course_categories} WHERE id = ccat.parent) as semester
            FROM
            	{course} c,
            	{course_categories} ccat
            WHERE
            	c.category = ccat.id AND
            	ccat.parent = " . $categoryId;

    $courses = $DB->get_records_sql($sql);
    $count = count($courses);
    echo "Number of courses: " . $count . "\n";

    $i = 1;
    foreach ($courses as $courseId => $course) {
      try {
        echo "[" . $i . " / " . $count . "] " . $course->shortname . " (#" . $course->id . ")\n";
        $bc = new backup_controller(backup::TYPE_1COURSE, $courseId, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $this->userId);
        $bc->execute_plan();
      } catch (Exception $e) {
        echo $e->getMessage() . "\n";
      }
      $i++;
    }
    //echo print_r($courses, true);
  }
}
