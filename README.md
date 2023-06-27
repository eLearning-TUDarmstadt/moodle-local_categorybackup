# moodle-local_categorybackup
Changes the automatic backup process to only create backups for specified course categories only (not for all courses).

## Prerequisites
* This version is tested on Moodle 3.6. 3.7, 3.8, 3.9, 3.10, 3.11, 4.0, 4.1, 4.2
* For Moodle 3.5 and older use branch MOODLE_35_STABLE

## Installation
1. Put the files to the folder /local/categorybackup in your Moodle directory.
2. Visit the administration page of your Moodle site and finish the installation progress.
3. Select the course categories to be backed up in admin/settings.php?section=local_categorybackup
4. Activate automated backups in MOODLE/admin/settings.php?section=automated

## Configuration
The configuration page is located at:

*Site administration / Plugins / Local plugins / Course categories for backup*

There you will find two configuration options:
1. **Active** - If set, backups will be created for courses in selected categories **only**!
2. **Course categories** - Select the course categories you wish to backup here. Backups will also be created for sub-categories.
