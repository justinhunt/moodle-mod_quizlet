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


//require a few lines
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

//require completion lib, so we can check our completion status
require_once($CFG->dirroot.'/lib/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // quizletimport instance ID - it should be named as the first character of the module
$oauth2code = optional_param('oauth2code', 0, PARAM_RAW); //an oauth2 code recieved from quizlet via callback at /admin/oauth2callback.php

if ($id) {
    $cm         = get_coursemodule_from_id('quizletimport', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizletimport  = $DB->get_record('quizletimport', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $quizletimport  = $DB->get_record('quizletimport', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $quizletimport->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('quizletimport', $quizletimport->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

global $USER;

//this is important cos we use this to figure out how long student was on page
add_to_log($course->id, 'quizletimport', 'view', "view.php?id={$cm->id}", $quizletimport->name, $cm->id);


/// Print the page header
$PAGE->set_url('/mod/quizletimport/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizletimport->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('quizletimport-'.$somevar);

$qih = new quizletimport_helper($quizletimport,$course,$cm);

// Output starts here
echo $OUTPUT->header();

if ($quizletimport->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('quizletimport', $quizletimport, $cm->id), 'generalbox mod_introbox', 'quizletimportintro');
}

/*
// Get current completion state
$completion = new completion_info($course);
$data = $completion->get_data($cm, false, $USER->id);

// Is the activity already complete
$completed= $data->viewed == COMPLETION_VIEWED;

//set the js to the page
$options = array($quizletimport->mintime,$cm->id, $completed);
$PAGE->requires->js_init_call('M.mod_quizletimport.timer.init', $options, false);
*/

$qih->initialise_timer($PAGE);

// Replace the following lines with your own code
/*
// DO oauth2 and quizlet stuff
$qiq  = new quizletimport_quizlet($quizletimport);
$qmessage = false;
if($qiq->quizlet->is_authenticated()){
	$endpoint = '/users/@username@/sets';
	$params = null;

	$mysets = $qiq->quizlet->request($endpoint,$params);
	if($mysets['success']){
		print_r($mysets['data']);
	}else{
		$qmessage =  $mysets['error'];
	}
}else if($oauth2code){
	$quizlet = $qiq->quizlet;
	$result  = $quizlet->get_access_token($oauth2code);
	if($result['success']){
		print_r($result['data']);
	}else{
		$qmessage = $result['error'];
	}
}else{
	$qmessage = '<a href="' . $qiq->quizlet->fetch_auth_url() . '">Step 1: Start Authorization</a>';
}

if($qmessage){
	echo $qmessage;
}
*/
//print_r($quizletimport);
/*
 $args = array(
			'api_scope' => 'read'
        );
$qiz  = new quizlet($args);
*/
$qiz=$qih->quizlet;

//display our quizlet activity
$embedcode = $qiz->fetch_embed_code($quizletimport->quizletset,$quizletimport->activitytype);
echo $embedcode;

//output completed tag
$completed = $qih->fetch_completed_tag();
echo $completed;
//echo html_writer::tag('div',  get_string('completed', 'quizletimport'),array('id' => 'quizletimport-completed'));

$timer=$qih->fetch_countdown_timer();
/*
//output time left counter
$timer = html_writer::tag('div', get_string('timeleft', 'quiz') . ' ' .
            html_writer::tag('span', '', array('id' => 'quizletimport-time-left')),
            array('id' => 'quizletimport-timer', 'role' => 'timer',
                'aria-atomic' => 'true', 'aria-relevant' => 'text'));
*/
echo $timer;

// Finish the page
echo $OUTPUT->footer();
