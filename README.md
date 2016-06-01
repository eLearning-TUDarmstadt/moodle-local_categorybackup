# moodle-tool_categorybackup
Create backups for specified course categories only (not for all courses)

## Installation
1. Put the files to the folder /local/categorybackup in your Moodle directory.
2. Add a cron job for the file **/local/categorybackup/cli.php**, that runs a short time (for example 15 minutes) before **/admin/cli/automated_backup.php** is called.
3. Visit the administration page of your Moodle site and finish the installation progress.

## Configuration
The configuration page is located at:

*Site administration / Plugins / Local plugins / Course categories for backup*

There you will find two configuration options:
1. *Active*

If set, backups will be created for courses in selected categories **only**!
2. *Course categories*

Select the course categories you wish to backup here. Backups will be created for sub-categories within, too. 
