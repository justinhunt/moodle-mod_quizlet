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
 * Prints a particular instance of quizlet
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_quizlet
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
$n  = optional_param('n', 0, PARAM_INT);  // quizlet instance ID - it should be named as the first character of the module
$oauth2code = optional_param('oauth2code', 0, PARAM_RAW); //an oauth2 code recieved from quizlet via callback at /admin/oauth2callback.php

if ($id) {
    $cm         = get_coursemodule_from_id('quizlet', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizlet  = $DB->get_record('quizlet', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $quizlet  = $DB->get_record('quizlet', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $quizlet->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('quizlet', $quizlet->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

global $USER;

//this is important cos we use this to figure out how long student was on page
if($CFG->version<2014051200){
	add_to_log($course->id, 'quizlet', 'view', "view.php?id={$cm->id}", $quizlet->name, $cm->id);
}else{
	// Trigger module viewed event.
	$event = \mod_quizlet\event\course_module_viewed::create(array(
	   'objectid' => $quizlet->id,
	   'context' => $context
	));
	$event->add_record_snapshot('course_modules', $cm);
	$event->add_record_snapshot('course', $course);
	$event->add_record_snapshot('quizlet', $quizlet);
	$event->trigger();
}

// now we record the time viewed like this
//NB this is very similar to the old log method(we just removed the 'module' check/field)
$viewincident = new stdClass();
$viewincident->time         = time();
$viewincident->cmid = $cm->id;
$viewincident->course = $course->id;
$viewincident->userid = $USER->id;
$viewincident->action = 'view';
$DB->insert_record('quizlet_log', $viewincident, false);



/// Print the page header
$PAGE->set_url('/mod/quizlet/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizlet->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('quizlet-'.$somevar);

$qih = new quizlet_helper($quizlet,$course,$cm);

//get our renderer
$renderer = $PAGE->get_renderer('mod_quizlet');

// Output starts here
echo $renderer->header();

if ($quizlet->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('quizlet', $quizlet, $cm->id), 'generalbox mod_introbox', 'quizletintro');
}

/*
// Get current completion state
$completion = new completion_info($course);
$data = $completion->get_data($cm, false, $USER->id);

// Is the activity already complete
$completed= $data->viewed == COMPLETION_VIEWED;

//set the js to the page
$options = array($quizlet->mintime,$cm->id, $completed);
$PAGE->requires->js_init_call('M.mod_quizlet.timer.init', $options, false);
*/

$qih->initialise_timer($PAGE);

// Replace the following lines with your own code
/*
// DO oauth2 and quizlet stuff
$qiq  = new quizlet_quizlet($quizlet);
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
//print_r($quizlet);
/*
 $args = array(
			'api_scope' => 'read'
        );
$qiz  = new quizlet($args);
*/
$qiz=$qih->quizlet;

//display our quizlet activity
$embedcode = $qiz->fetch_embed_code($quizlet->quizletset,$quizlet->activitytype);
echo $embedcode;

//output completed tag
$completed = $qih->fetch_completed_tag();
echo $completed;
//echo html_writer::tag('div',  get_string('completed', 'quizlet'),array('id' => 'quizlet-completed'));

$timer=$qih->fetch_countdown_timer();
/*
//output time left counter
$timer = html_writer::tag('div', get_string('timeleft', 'quiz') . ' ' .
            html_writer::tag('span', '', array('id' => 'quizlet-time-left')),
            array('id' => 'quizlet-timer', 'role' => 'timer',
                'aria-atomic' => 'true', 'aria-relevant' => 'text'));
*/
echo $timer;

// Finish the page
echo $renderer->footer();

