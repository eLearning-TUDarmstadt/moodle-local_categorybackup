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
 * @package local_categorybackup
 * @copyright  2016 Steffen Pegenau
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_categorybackup', get_string('pluginname', 'local_categorybackup'));
    $ADMIN->add('localplugins', $settings);
    
    // Selective backup active?
    $settings->add(new admin_setting_configcheckbox('local_categorybackup_active', get_string('active'), '', 0, $yes='1', $no='0'));
    
    // Select categories    
    global $DB;
    $choices = array();
    $categories = $DB->get_records_sql("SELECT id, name FROM {course_categories} WHERE parent=0");
    foreach ($categories as $id => $category) {
        $choices[$category->id] = $id . " - " .$category->name;
    }
    $settings->add(new admin_setting_configmultiselect('local_categorybackup_categories', get_string('categories'), 'Only courses in selected categories will be backed up', '', $choices));

}