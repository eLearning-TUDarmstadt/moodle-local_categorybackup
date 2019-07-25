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
 * @copyright  2016 Steffen Pegenau, 2019 Benedikt Schneider
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_categorybackup', get_string('pluginname', 'local_categorybackup'));
    $ADMIN->add('localplugins', $settings);

    // Explanation and useful links.
    $a = new stdClass();
    $a->automatedbackupsurl = html_writer::link(new moodle_url('/admin/settings.php?section=automated', array()), get_string('clickhere'));
    $a->lastbackupsurl = html_writer::link(new moodle_url('/local/categorybackup/lastbackups.php', array()), get_string('clickhere'));
    $settings->add(new admin_setting_heading('local_categorybackup_links',
        get_string('settings_heading', 'local_categorybackup'),
        get_string('explanation', 'local_categorybackup', $a)));

    // Selective backup active.
    $settings->add(new admin_setting_configcheckbox('local_categorybackup_active', get_string('active'), '', 0, $yes='1', $no='0'));

    // Select categories.
    global $DB;
    $choices = array();
    $categories = $DB->get_records_sql("SELECT id, name FROM {course_categories} WHERE parent=0");
    foreach ($categories as $id => $category) {
        $choices[$category->id] = $id . " - " .$category->name;
    }
    $settings->add(new admin_setting_configmultiselect('local_categorybackup_categories', get_string('categories'),
        get_string('cat_selection_desc', 'local_categorybackup'), array(), $choices));

    /*
    // Show link to last backups.
    $moodle_url = html_writer::link(new moodle_url('/local/categorybackup/lastbackups.php', array()), get_string('clickhere'));
    $settings->add(new admin_setting_description('local_categorybackup_linktolastbackups',
        get_string('last_backups_link', 'local_categorybackup'), $moodle_url));

    // Show link to automated backups.
    $moodle_url = html_writer::link(new moodle_url('/admin/settings.php?section=automated', array()), get_string('clickhere'));
    $settings->add(new admin_setting_description('local_categorybackup_linktoautobackups',
        get_string('auto_backup_desc', 'local_categorybackup'), $moodle_url));
    */
}
