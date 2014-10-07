<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Mod Quizlet Import Set Select Page
 * @package   mod_quizletimport
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global  $USER, $COURSE;	

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/quizletimport/quizlet.php');

require_login();
if (isguestuser()) {
    die();
}

//Set up page
$context = context_user::instance($USER->id);
//require_capability('moodle/user:viewalldetails', $context);
$PAGE->set_context($context);

//get params
$caller = optional_param('caller', '', PARAM_URL);
$oauth2code = optional_param('oauth2code', 0, PARAM_RAW);

//get quizlet search form
$search_form = new quizlet_search_form();
$data = $search_form->get_data();


//make double sure we have the course id in id
if(empty($data->courseid)){
	$courseid = optional_param('courseid',$COURSE->id, PARAM_INT);
}else{
	$courseid = $data->courseid;
}
//make sure we keep the caller URL
if(!empty($data->caller)){
	$caller = $data->caller;
}else{
	$search_form->set_data(array('caller'=>$caller));
}

//prepare rest of page and data
$url = new moodle_url('/mod/quizletimport/selectset.php', array('courseid'=>$courseid,'caller'=>$caller));
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');



  //Initialize Quizlet and deal with oauth etc
	//i  - send off to auth screen
	//ii - arrive back unauth, but with oauth2code
	//iii - complete auth by getting access token
	 $args = array(
		'api_scope' => 'read'
	);
	$qiz  = new quizlet($args);
	
	$qmessage = false;
	if(!$qiz->is_authenticated() && $oauth2code){
                $result  = $qiz->get_access_token($oauth2code);
                if(!$result['success']){
                        $qmessage = $result['error'];
                }   
     }
	
echo $OUTPUT->header();

	if($qmessage){
		qdisplayerror($qmessage);
	}elseif(!$qiz->is_authenticated()){
		$authlink= '<a href="' . $qiz->fetch_auth_url() . '">' . get_string('quizletlogin', 'quizletimport') . '</a>';
         qdisplayerror($authlink);
	}else{
		qdisplayforms($qiz, $courseid, $search_form, $data, $caller);
	}

echo $OUTPUT->footer();


 /**
 * Displays any errors from the previous search
 * 
 * @param string $qmessage errors from the previous search.
 */
function qdisplayerror($qmessage) {
     echo $qmessage;
}//end of func

  /**
 * Output the two forms required for this page
 * i) the search form (searching quizlet)
 * ii) the select form (selecting set and returning to caller)
 * 
 * @param quizlet $qiz the quizlet object
 * @param int $courseid the course id
 * @param quizlet_search_form $search_form the quizlet search form
 * @param stdClass $data the submitted data from the previous search
 * @param string $caller the url of the page that we should return the data to
 * @return string HTML content.
 */
function qdisplayforms($qiz, $courseid, $search_form, $data, $caller){

	global $OUTPUT, $PAGE;

	//perform our search based on search form submission (if there was one)
	$param_searchtext = '';
	$param_searchtype = '';
	if(!empty($data->searchtext)){
		$param_searchtext = $data->searchtext;
	}
	if(!empty($data->searchtype)){
		$param_searchtype = $data->searchtype;
	}
	
	//if authenticated fill our select box with users sets
	//otherwise show a login/authorize link
	$select = "";
	$selectboxname = 'selectedset';
	if($qiz->is_authenticated()){
		//default is to list our sets
		$searchresult = $qiz->do_search($param_searchtext,$param_searchtype);
	
		if($searchresult['success']){
			if(is_array($searchresult['data'])){
				$setdata = $searchresult['data'];	
			}else{
				$setdata = $searchresult['data']->sets;
			}
			$select_qselect = $qiz->fetch_set_selectlist($setdata,$selectboxname,false);
		}else{
			//complain that we got no sets here
			echo "NO SETS!!!";
		}
	}
	
	//begin the output
	echo $OUTPUT->heading(get_string('selectset','quizletimport'));
	echo $OUTPUT->box_start('generalbox');
	//display the quizlet search form
	$search_form->display();
	echo $OUTPUT->box_end();
	echo $OUTPUT->box_start('generalbox');
	//$submit = html_writer::tag('input',null,array('type'=>'submit','value'=>'Use this set','name'=>'selectsetsubmit','name'=>'selectsetsubmit'));
	
	//prepare the select form
	//first prepare the caller url as action, and params as hidden fields in form submission
	$caller = new moodle_url($caller);
	$params = $caller->params();
	$fields="";
	$actionurl = $caller->out_omit_querystring(true);
	 foreach ($params as $var => $val) {
				$fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
	}
	
	//add the select box containing results of previous search (if any)
	$fields .= $select_qselect;
	
	//prepare the two buttons (submit and cancel) 
	$submit = html_writer::tag('input',null,array('type'=>'submit','value'=>'Use this set','name'=>'selectsetsubmit','name'=>'selectsetsubmit'));
	$preview = html_writer::tag('input',null,array('type'=>'button','value'=>'Preview below','name'=>'selectsetsubmit','onClick'=>'M.mod_quizletimport.iframehelper.update()'));
	$cancel =  html_writer::link($caller,get_string('cancel'));
	$fields .= html_writer::start_tag('div');
	$fields .= $submit . $preview . $cancel;
	$fields .= html_writer::end_tag('div');
	
	//send the form out to browser and finish up
	echo html_writer::tag('form',$fields,array('action'=>$actionurl,'method'=>'get'));

	//echo $OUTPUT->close_window_button(get_string('cancel'));
	echo $OUTPUT->box_end();
	echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->box_end();
    
    //add our preview iframe box
    //set up js
    $iframename = "quizletimport_sampleset_flashcards";
    $jsoptions = array($iframename,$selectboxname);
    $PAGE->requires->js_init_call('M.mod_quizletimport.iframehelper.init', $jsoptions, false);
    //output the iframe
    echo $OUTPUT->box_start('generalbox');
    $iframe = "<iframe id='$iframename' name='$iframename' src=\"\" height=\"350\" width=\"550\" style=\"border:0;\"></iframe>";
    echo $iframe;
    echo $OUTPUT->box_end();
    
}
?>
