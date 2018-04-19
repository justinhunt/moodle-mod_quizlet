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
 * Receives a JS communication from browser that page is complete.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_quizlet
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or

if ($id) {
    $cm         = get_coursemodule_from_id('quizlet', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizlet  = $DB->get_record('quizlet', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

global $DB,$USER;

$sql = "SELECT MAX(time) FROM {quizlet_log} WHERE cmid=" . $cm->id
			. " AND course=" . $course->id 
			. " AND userid=" . $USER->id 
			. " AND action='view'";
			
$starttime = $DB->get_field_sql($sql);

if($starttime && (time() - $starttime) > $quizlet->mintime){
	$completion = new completion_info($course);
	$completion->set_module_viewed($cm);
	$return =array('success'=>true);
	echo json_encode($return);
}else{
	$return =array('success'=>false);
	echo json_encode($return);
}
