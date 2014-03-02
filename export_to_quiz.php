<?php
/// original file: mod/glossary/export.php
/// modified by JR 17 JAN 2011

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/quizletimport/quizlet.php');
//require_once("lib.php");

$id = required_param('id', PARAM_INT);      // Course Module ID
$param_searchtext = optional_param('searchtext','', PARAM_TEXT);
$param_searchtype = optional_param('searchtype','', PARAM_TEXT);
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
	
	
	//here add searchable fields
	$searchform = '<form action="export_to_quiz.php" method="post">';
	$searchform  .= '<input type="text" name="searchtext"  /><br />';
	$searchform  .='<input type="hidden" name="id" value="' . $id . '"/>'; 
	$searchform  .='<input type="submit" name="searchtype" value="' . get_string("searchmysets", "quizletimport") . '" />';
	$searchform  .='<input type="submit" name="searchtype" value="' . get_string("searchtitles", "quizletimport") . '" />';
	$searchform  .='<input type="submit" name="searchtype" value="' . get_string("searchusers", "quizletimport") . '" />';
	//never works? not even from their website api tester
	//$searchform  .='<input type="submit" name="searchtype" value="' . get_string("searchterms", "quizletimport") . '" />';
	$searchform  .='</form>';
	
	//if authenticated fill our select box with users sets
	//otherwise show a login/authorize link
	$select = "";
	if($qiz->is_authenticated()){
		//default is to list our sets
		$params = null;
		if($param_searchtext =='' || $param_searchtype== get_string("searchmysets", "quizletimport")){
			$endpoint = '/users/@username@/sets';
			$mysets = $qiz->request($endpoint,$params);
			if($mysets['success']){
				$mysetsdata = $mysets['data'];
			}
		}else{
			switch ($param_searchtype){	
				case get_string("searchusers", "quizletimport"):
					$params=array();
					$params['creator']=$param_searchtext;
					$endpoint = '/search/sets';
					break;
				case get_string("searchterms", "quizletimport"):
					$params=array();
					$params['term']=$param_searchtext;
					$endpoint = '/search/sets';
					break;	
				case get_string("searchtitles", "quizletimport"):
				default:
					$params=array();
					$params['q']=$param_searchtext;
					$endpoint = '/search/sets';
					break;	
			}
			
			$mysets = $qiz->request($endpoint,$params);
			if($mysets['success']){
				$mysetsdata = $mysets['data']->sets;
			}
		}

			if($mysets['success']){
				$select_qexport = $qiz->fetch_set_selectlist($mysetsdata,'quizletset_qexport',true);
				$select_ddrop = $qiz->fetch_set_selectlist($mysetsdata,'quizletset_ddrop',true);
				/*
				$select = "<select name='quizletset[]' multiple size='10'>";
				$options = array();
				foreach ($mysetsdata as $quizletset){
					//NB ugly delimeter that passes all the way through. urrrghh
					//but it is just to create a viewable name, so no stress if the name gets messed up
					if(empty($quizletset) || empty($quizletset->id)){continue;}
					$qdescription = $quizletset->title;
					$qdescription  .= ' (' . $quizletset->term_count . ')';
					$qdescription  .= ' Author:' . $quizletset->created_by;
					$qdescription  .= ' images:' . ($quizletset->has_images ? 'yes' : 'no') ;
					$select .= "<option value='" . $quizletset->id . "-"  . preg_replace("/[^A-Za-z0-9]/", "_", $quizletset->title ).  "'>" . $qdescription . "</option>";
				}
				$select .= "</select>";
				*/
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
echo $searchform;
echo $OUTPUT->box_end();
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
    echo get_string('availablesets', 'quizletimport') . '<br />'  . $select_qexport;
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
		 echo get_string('availablesets', 'quizletimport') . '<br />' . $select_ddrop;
	
?>
    </div>
    </form>
<?php
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
?>
