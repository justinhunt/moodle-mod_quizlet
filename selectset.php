<?php
/// original file: mod/glossary/export.php
/// modified by JR 17 JAN 2011
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

function qdisplayerror($qmessage) {
     echo $qmessage;
}//end of func

function qdisplayforms($qiz, $courseid, $search_form, $data, $caller){

global $OUTPUT;

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
	if($qiz->is_authenticated()){
		//default is to list our sets
		$searchresult = $qiz->do_search($param_searchtext,$param_searchtype);
	
		if($searchresult['success']){
			if(is_array($searchresult['data'])){
				$setdata = $searchresult['data'];	
			}else{
				$setdata = $searchresult['data']->sets;
			}
			$select_qselect = $qiz->fetch_set_selectlist($setdata,'selectedset',false);
		}else{
			//complain that we got no sets here
			echo "NO SETS!!!";
		}
	}
	

echo $OUTPUT->heading(get_string('selectset','quizletimport'));
echo $OUTPUT->box_start('generalbox');
$search_form->display();
echo $OUTPUT->box_end();
echo $OUTPUT->box_start('generalbox');
//$submit = html_writer::tag('input',null,array('type'=>'submit','value'=>'Use this set','name'=>'selectsetsubmit','name'=>'selectsetsubmit'));

$caller = new moodle_url($caller);
 $params = $caller->params();
 $fields="";
  $actionurl = $caller->out_omit_querystring(true);
 foreach ($params as $var => $val) {
            $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
}
$fields .= $select_qselect;
$submit = html_writer::tag('input',null,array('type'=>'submit','value'=>'Use this set','name'=>'selectsetsubmit','name'=>'selectsetsubmit'));
$cancel =  html_writer::link($caller,get_string('cancel'));
$fields .= html_writer::start_tag('div');
$fields .= $submit . $cancel;
$fields .= html_writer::end_tag('div');
echo html_writer::tag('form',$fields,array('action'=>$actionurl,'method'=>'get'));

	//echo $OUTPUT->close_window_button(get_string('cancel'));
	echo $OUTPUT->box_end();
	echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->box_end();
}
?>
