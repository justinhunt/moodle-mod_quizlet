<?php
/// original file: mod/glossary/export.php
/// modified by JR 17 JAN 2011

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/quizletimport/quizlet.php');
//require_once("lib.php");

$id = required_param('id', PARAM_INT);      // Course Module ID
 
$url = new moodle_url('/mod/quizletimport/export_to_quiz.php', array('id'=>$id));


$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('quizletimport', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}


require_login($course->id, false, $cm);


  //Initialize Quizlet and deal with oauth etc
	//i  - send off to auth screen
	//ii - arrive back unauth, but with oauth2code
	//iii - complete auth by getting access token
	 $args = array(
		'api_scope' => 'read'
	);
	$qiz  = new quizlet($args);
	
	
	//if authenticated fill our select box with users sets
	//otherwise show a login/authorize link
	$select = "";
	if($qiz->is_authenticated()){
		$endpoint = '/users/@username@/sets';
			$params = null;
			/*
			$params=array();
			$params['term']='silla';
			$params['q']='spanish';
			$endpoint = '/search/sets';
			*/

			$mysets = $qiz->request($endpoint,$params);
			if($mysets['success']){
				$select = "<select name='quizletset[]' multiple size='10'>";
				$options = array();
				foreach ($mysets['data'] as $quizletset){
					//NB ugly delimeter that passes all the way through. urrrghh
					//but it is just to create a viewable name, so no stress if the name gets messed up
					$select .= "<option value='" . $quizletset->id . "-"  . preg_replace("/[^A-Za-z0-9]/", "_", $quizletset->title ).  "'>" . $quizletset->title . "</option>";
				}
				$select .= "</select>";
			}
	}
	

	//create question types
	$qtypes = array();
	$qtypes['multichoice_abc'] =get_string('multichoice', 'quizletimport').
    					' ('. get_string('answernumberingabc', 'qtype_multichoice').')';
    $qtypes['multichoice_ABCD'] =get_string('multichoice', 'quizletimport').
    					' ('. get_string('answernumberingABCD', 'qtype_multichoice').')'; 
	$qtypes['multichoice_123'] =get_string('multichoice', 'quizletimport').
    					' ('. get_string('answernumbering123', 'qtype_multichoice').')';
    $qtypes['multichoice_none'] =get_string('multichoice', 'quizletimport').
    					' ('. get_string('answernumberingnone', 'qtype_multichoice').')';
    $qtypes['shortanswer_0'] =get_string('shortanswer_0', 'quizletimport');
	$qtypes['shortanswer_1'] =get_string('shortanswer_1', 'quizletimport');



$strexportfile = get_string("exportfile", "quizletimport");
$strexportdragdrop = get_string("exportdragdrop", "quizletimport");
$strexportentries = get_string('exportentriestoxml', 'quizletimport');

// not needed here
/*
$PAGE->set_url('/blocks/block_quizletquiz/export_to_quiz.php', array('id'=>$cm->id));
$PAGE->navbar->add($strexportentries);
$PAGE->set_title(format_string($glossary->name));
$PAGE->set_heading($course->fullname);
*/

echo $OUTPUT->header();
echo $OUTPUT->heading($strexportentries);
echo $OUTPUT->box_start('generalbox');
?>
    <form action="exportfile_to_quiz.php" method="post">
    <table border="0" cellpadding="6" cellspacing="6" width="100%">
    <tr><td align="center">
        <input type="submit" value="<?php p($strexportfile)?>" />
    </td></tr></table>
    <div>
    </div>

        <div>
    <input type="hidden" name="id" value="<?php p($cm->id)?>" />
    <input type="hidden" name="exporttype" value="quiz" />
    
    <?php
    //question types
    foreach ($qtypes as $qcode=>$qname){
			echo("<input type='checkbox' name='questiontype[]' value='$qcode'>$qname</input><br />");
		}
	?>
	  
    </div>
    <?php
    echo get_string('usersets', 'quizletimport') . '<br />'  . $select;
    ?>

    </form>
 <?php
    echo $OUTPUT->box_end();
   echo $OUTPUT->box_start('generalbox');
?>
   <form action="exportfile_to_quiz.php" method="post">
    <table border="0" cellpadding="6" cellspacing="6" width="100%">
    <tr><td align="center">
        <input type="submit" value="<?php p($strexportdragdrop)?>" />
    </td></tr></table>
    <div>
    </div>

        <div>
    <input type="hidden" name="id" value="<?php p($cm->id)?>" />
    <input type="hidden" name="exporttype" value="dragdrop" />
<?php
   //what kind of quizlet activity are we going to display
		$activities = array('flashcards' => get_string('acttype_flashcards', 'quizletimport'),
				'scatter'=>get_string('acttype_scatter', 'quizletimport'),
				'spacerace'=>get_string('acttype_spacerace', 'quizletimport'),
				'test'=>get_string('acttype_test', 'quizletimport'),
				'speller'=>get_string('acttype_speller', 'quizletimport'),
				'learn'=>get_string('acttype_learn', 'quizletimport'));
		foreach ($activities as $aid=>$atitle){
			echo("<input type='checkbox' name='activitytype[]' value='$aid'>$atitle</input><br />");
		}
		 echo get_string('usersets', 'quizletimport') . '<br />' . $select;
	
?>
    </div>
    </form>
<?php
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
?>
