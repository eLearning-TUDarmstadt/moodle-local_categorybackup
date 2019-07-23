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
 * The main entry file of the plugin.
 *
 * Provide the site-wide setting and specific configuration for each assignment.
 *
 * @package    local_categorybackup
 * @copyright  2019 Benedikt Schneider (@Nullmann)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_categorybackup\task;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Class send_submissions
 * @package local_categorybackup\task
 */
class start_backup extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_name', 'local_categorybackup');
    }

    /**
     * Execute the backups.
     */
    public function execute() {
        global $CFG;
        require_once $CFG->dirroot . '/local/categorybackup/lib.php';

        $cb = new \CategoryBackup();
        $cb->cron();
    }
}
