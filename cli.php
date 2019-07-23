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

define('CLI_SCRIPT', 1);
require_once dirname(__FILE__).'/../../config.php';
require_once $CFG->dirroot . '/local/categorybackup/lib.php';

require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/cronlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/gradelib.php');

/// emulate normal session
cron_setup_user();

// Auswerten der CMD-Argumente

$cb = new CategoryBackup();
$cb->cron();

/*
Cleaner::log("Cleaner CLI gestartet\n");

if(paramsContain("--pretend", $argv) || paramsContain("-p", $argv)) {
    $c = new Cleaner();
    Cleaner::log("Mode: Pretend\n");
    echo "ID\tUsername\tVorname\tNachname\tZuletzt aktiv\n";
    foreach ($c->usersToBeDeleted as $user) {
        echo $user->id; t();
        echo $user->username; t();
        echo $user->firstname; t();
        echo $user->lastname; t();
        echo date(Cleaner::$DATE_FORMAT, $user->lastaccess);
        echo "\n";
    }
    echo count($c->usersToBeDeleted) . " Benutzer würden gelöscht\n";
} else if (paramsContain("--delete", $argv) || paramsContain("-d", $argv)) {
    Cleaner::log("Mode: Delete\n");
    $c = new Cleaner();
    $c->deleteUsers();
} else {
    Cleaner::log("Ungültige Parameter!\n");
    echo "Gültige Parameter:\n";
    echo " --pretend bzw. -p:\t Zeigt die Liste der zu löschenden User an\n";
    echo " --delete bzw. -d:\t Löscht abgelaufene User\n";
}
Cleaner::log("ENDE\n");
//$cleaner = new Cleaner();

//$cleaner->deleteUsers();

function paramsContain($p, $argv) {
    for($i = 1; $i < count($argv); $i++) {
        if(strcmp($argv[$i], $p) === 0) {
            return true;
        }
    }
    return false;
}

function t() {
    echo "\t";
}
*/
