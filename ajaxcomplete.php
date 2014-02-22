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
 * Prints a particular instance of quizletimport
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_quizletimport
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or

if ($id) {
    $cm         = get_coursemodule_from_id('quizletimport', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizletimport  = $DB->get_record('quizletimport', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

global $DB,$USER;

$sql = "SELECT MAX(time) FROM {log} WHERE cmid=" . $cm->id
			. " AND course=" . $course->id 
			. " AND userid=" . $USER->id 
			. " AND module='quizletimport'" 
			. " AND action='view'";
			
$starttime = $DB->get_field_sql($sql);

if($starttime && (time() - $starttime) > $quizletimport->mintime){
	$completion = new completion_info($course);
	$completion->set_module_viewed($cm);
	$return =array('success'=>true);
	echo json_encode($return);
}else{
	$return =array('success'=>false);
	echo json_encode($return);
}
