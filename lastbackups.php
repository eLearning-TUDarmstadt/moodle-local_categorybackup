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
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T');

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}



require_once '../../config.php';

global $DB, $CFG;

require_login();
require_capability('local/categorybackup:view', context_system::instance());


$output = "<html><head>";

// Bootstrap
$output .= '
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script type="text/javascript">

$(window).ready(function() {
	$("#load").click(function() {
		var categories = $("#menuSemester").val().join(",");
		window.location.href = "?categories=" + categories;
	});
});

</script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<style>
	#menuSemester {
		height:250px;
		margin-bottom: 20px;
	}
</style>
';

$output .= "</head><body><div class='container'>";

$category_ids = explode(',', $CFG->local_categorybackup_categories);

$categories = empty($_GET['categories']) ? $CFG->local_categorybackup_categories : $_GET['categories'];

$output .= "<h4>Backups sollten fuer diese Kategorien erstellt werden:</h4>";
$output .= "<ul>";

require_once($CFG->dirroot . '/lib/coursecatlib.php');
$catnames = [];
foreach ($category_ids as $id) {
	$cat = coursecat::get($id);
	$catnames[] = $cat->name;
	$output .= "<li>" . $cat->name . "</li>";
}

$output .= "</ul>";


require_once $CFG->libdir . '/outputcomponents.php';

$sql = "SELECT
	f.id as fileid,
	f.filearea,
	f.filename,
	f.filesize,
	f.timecreated,
	co.id as course,
	co.fullname,
	co.shortname,
	ccat.name as fb,
	(SELECT cat.name FROM {course_categories} cat WHERE cat.id = ccat.parent) as semester
FROM
	{files} f,
	{context} c,
	{course} co,
	{course_categories} ccat
WHERE
	f.filesize != 0 AND
	f.component = 'backup' AND
	c.id = f.contextid AND
	c.contextlevel = 50 AND
	co.id = c.instanceid AND
	ccat.id = co.category AND
	ccat.parent IN ($categories)
ORDER BY
	f.timecreated DESC";

$results = $DB->get_records_sql($sql);

// Row Names
$term = "Semester";
$department = "FB";
$courseId = "Kurs ID";
$fullname = "Kurs";
$created = "Backup erstellt";
$size = "Backup Groesse";
$wanted = "Ungewollt";

$table = new html_table();
$table->attributes = array("class" => "table table-striped table-bordered table-hover table-condensed table-responsive");
$table->head = array($courseId, $term, $department, $fullname, $created, $size, $wanted);

function isUnwantedBackup($catnames, $semester, $fb) {
	foreach ($catnames as $name) {
		if($semester == $name || $fb == $name) {
			return "";
		}
	}
	return "=JA=";
}

$date_format = "d.m.Y H:i:s";
$totalsize = 0;
foreach ($results as $fileid => $f) {
	$totalsize += $f->filesize;
    $size = formatBytes($f->filesize);
    $created = date($date_format, $f->timecreated);

    $unwanted = isUnwantedBackup($catnames, $f->semester, $f->fb);
    $table->data[] = array($f->course, $f->semester, $f->fb,  $f->fullname, $created, $size, $unwanted);
}

$semesterResult = $DB->get_records('course_categories', array('depth' => 1));

$semesters = array_reduce($semesterResult, function($acc, $obj) {
	$acc[$obj->id] = $obj->name;
	return $acc;
});

$backupsCount = count($results);

// Now the table
$output .= "<h4>Bereits erstellte Backups $backupsCount (aus Tabelle <i>files</i> ermittelt, Gesamtgroesse: " .formatBytes($totalsize). "):</h4>";
$output .= html_writer::select($semesters, 'Semester', explode(',', $categories), $nothing = array('' => 'choosedots'), array('multiple' => 'multiple', 'class' => 'form-control'));
$output .= '<div><button id="load" class="btn btn-primary">Backups anzeigen</button></div>';
$output .= "<br><br>";
$output .= html_writer::table($table);
$output .= "</div>";
$output .= "</body></html>";
echo $output;
//echo "<pre>".print_r($results, true)."</pre>";
