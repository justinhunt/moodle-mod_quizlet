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
 * The main quizlet configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_quizlet
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/quizlet/locallib.php');

/**
 * Module instance settings form
 */
class mod_quizlet_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
    	global $CFG,$COURSE, $PAGE;

        $mform = $this->_form;
	//get our config and renderer	
        $config = get_config('quizlet');
        $renderer = $PAGE->get_renderer('mod_quizlet');
        
        //load up some JS to help us select/update quizlet sets
        //set the js to the page
       
        $opts = array();
        $opts['qidbox']='id_' . 'quizletset';
        $opts['qnamebox']='id_' . 'quizletsettitle';
        $PAGE->requires->js_init_call('M.mod_quizlet.selectionhelper.init', array($opts), false);
        
        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('quizletname', 'quizlet'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'quizletname', 'quizlet');

        // Adding the standard "intro" and "introformat" fields
        if($CFG->version < 2015051100){
        	$this->add_intro_editor();
        }else{
        	$this->standard_intro_elements();
		}
        //-------------------------------------------------------------------------------
        //Initialize Quizlet and deal with oauth etc
        //i  - send off to auth screen
        //ii - arrive back unauth, but with oauth2code
        //iii - complete auth by getting access token
         $args = array(
			'api_scope' => 'read'
        );
		$qiz  = new quizlet($args);
		$oauth2code = optional_param('oauth2code', 0, PARAM_RAW);
		$qmessage = false;
		if(!$qiz->is_authenticated() && $oauth2code){
			$result  = $qiz->get_access_token($oauth2code);
			if(!$result['success']){
				$qmessage = $result['error'];
			}
		}

		//get quizlet search form
		//$search_form = new quizlet_search_form();
		//$search_form->display();
		//$data = $search_form->get_data();

		//if authenticated fill our select box with users sets
		//otherwise show a login/authorize link
		/*
		if($qiz->is_authenticated()){
			$endpoint = 'users/@username@/sets';
				$params = null;
				$mysets = $qiz->request($endpoint,$params);
				
				if($mysets['success']){
					$options = array();
					foreach ($mysets['data'] as $quizletset){
						$options[$quizletset->id] = $quizletset->title;
					}
					
					$qset = $mform->addElement('select', 'quizletset', get_string('usersets', 'quizlet'), $options);
					$qset->setMultiple(false);
					$mform->setType('quizletset', PARAM_TEXT);
					
					//also add a jumping off point for our quiz maker	
					$cmid = optional_param('update', 0, PARAM_INT); // course_module ID		              
         			$mform->addElement('static', 'createmquiz', get_string('createmquiz', 'quizlet'), '<a href="' . $CFG->wwwroot . '/mod/quizlet/export_to_quiz.php?id=' . $cmid . '">' . get_string('createmquiz', 'quizlet') . '</a>');
           
				}else{
					$qmessage =  $mysets['error'];
				}
		}else{
			 $mform->addElement('static', 'quizletauthorize', get_string('quizletloginlabel', 'quizlet'), '<a href="' . $qiz->fetch_auth_url() . '">' . get_string('quizletlogin', 'quizlet') . '</a>');
                         $mform->addElement('text', 'quizletset', get_string('quizletsetinput', 'quizlet'),array('size' => '64'));
                         $mform->setType('quizletset', PARAM_TEXT);                
        }
        */
        
      //our jump off link to the select screen
   //   $mform->addElement('header', 'selectsetheader', get_string('selectset', 'quizlet'));
      $ssurl =  new moodle_url('/mod/quizlet/selectset.php', array('courseid'=>$COURSE->id,'caller'=>$PAGE->url));
       //$mform->addElement('static', 'selectset', get_string('selectset', 'quizlet'), html_writer::link($ssurl,get_string('selectset', 'quizlet')));
      $mform->addElement('static', 'selectset', get_string('selectset', 'quizlet'), $renderer->show_popup_page($ssurl,get_string('selectset', 'quizlet')));
      
      
      
      
	  //showing the current quizlet set
	  $mform->addElement('text', 'quizletset', get_string('quizletsetid', 'quizlet'),array('size' => '64'));
      $mform->setType('quizletset', PARAM_TEXT);   
      $mform->addElement('text', 'quizletsettitle', get_string('quizletsettitle', 'quizlet'),array('size' => '64'));
      $mform->setType('quizletsettitle', PARAM_TEXT); 
   //   $mform->setExpanded('selectsetheader'); 
   //   $mform->closeHeaderBefore('activitytype');
      
     

  /*
		//if along the way we got an error back from quizlet, lets display it.
		if($qmessage){
			$mform->addElement('static', 'quizleterror', get_string('quizleterror', 'quizlet'), $qmessage);
		}
		*/
		
		//what kind of quizlet activity are we going to display
		$activities = array($qiz::TYPE_CARDS => get_string('acttype_flashcards', 'quizlet'),
				$qiz::TYPE_SCATTER=>get_string('acttype_scatter', 'quizlet'),
				$qiz::TYPE_SPACERACE=>get_string('acttype_spacerace', 'quizlet'),
				$qiz::TYPE_TEST=>get_string('acttype_test', 'quizlet'),
				$qiz::TYPE_SPELLER=>get_string('acttype_speller', 'quizlet'),
				$qiz::TYPE_LEARN=>get_string('acttype_learn', 'quizlet'));
				
		$select = $mform->addElement('select', 'activitytype', get_string('activitytype', 'quizlet'), $activities);
		
		//Add a place to set a mimumum time after which the activity is recorded complete
	   $mform->addElement('duration', 'mintime', get_string('mintime', 'quizlet'));
       $mform->setDefault('mintime',0);
	   $mform->addElement('static', 'mintimedetails', '',get_string('mintimedetails', 'quizlet'));
       
       //show countdown timer
	   $mform->addElement('selectyesno', 'showcountdown', get_string('showcountdown', 'quizlet'));
       $mform->setDefault('showcountdown',$config->def_showcountdown);
       
      //show completion tag
	   $mform->addElement('selectyesno', 'showcompletion', get_string('showcompletion', 'quizlet'));
       $mform->setDefault('showcompletion',$config->def_showcompletion);
      

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
    
    public function definition_after_data() {
        parent::definition_after_data();
        $selectedset = optional_param('selectedset', '', PARAM_TEXT);
        if(!empty($selectedset)){
        	$selectedarray = explode('-',$selectedset);
	        $mform =& $this->_form;
	        $qset =& $mform->getElement('quizletset');
	        $qsettitle =& $mform->getElement('quizletsettitle');
	        $qset->setValue($selectedarray[0]);
	        $qsettitle->setValue($selectedarray[1]);
	    }//end of if block
	}//end of function

}//end of calss
