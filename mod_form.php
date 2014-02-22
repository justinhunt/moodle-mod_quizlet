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
 * The main quizletimport configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_quizletimport
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/quizletimport/locallib.php');

/**
 * Module instance settings form
 */
class mod_quizletimport_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('quizletimportname', 'quizletimport'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'quizletimportname', 'quizletimport');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

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


		//if authenticated fill our select box with users sets
		//otherwise show a login/authorize link
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
					$options = array();
					foreach ($mysets['data'] as $quizletset){
						$options[$quizletset->id] = $quizletset->title;
					}
					//$attributes = array('size'=>5);
					$select = $mform->addElement('select', 'quizletset', get_string('usersets', 'quizletimport'), $options);
					$select->setMultiple(false);
				}else{
					$qmessage =  $mysets['error'];
				}
		}else{
			 $mform->addElement('static', 'quizletauthorize', get_string('quizletloginlabel', 'quizletimport'), '<a href="' . $qiz->fetch_auth_url() . '">' . get_string('quizletlogin', 'quizletimport') . '</a>');
		}

		//if along the way we got an error back from quizlet, lets display it.
		if($qmessage){
			$mform->addElement('static', 'quizleterror', get_string('quizleterror', 'quizletimport'), $qmessage);
		}
		
		//what kind of quizlet activity are we going to display
		$activities = array($qiz::TYPE_CARDS => get_string('acttype_flashcards', 'quizletimport'),
				$qiz::TYPE_SCATTER=>get_string('acttype_scatter', 'quizletimport'),
				$qiz::TYPE_SPACERACE=>get_string('acttype_spacerace', 'quizletimport'),
				$qiz::TYPE_TEST=>get_string('acttype_test', 'quizletimport'),
				$qiz::TYPE_SPELLER=>get_string('acttype_speller', 'quizletimport'),
				$qiz::TYPE_LEARN=>get_string('acttype_learn', 'quizletimport'),
				$qiz::TYPE_MOODLE_QUIZ=>get_string('acttype_moodlequiz', 'quizletimport'));
				
		$select = $mform->addElement('select', 'activitytype', get_string('activitytype', 'quizletimport'), $activities);
		
		//Add a place to set a mimumum time after which the activity is recorded complete
	   $mform->addElement('duration', 'mintime', get_string('mintime', 'quizletimport'));    
       $mform->setDefault('mintime',0);
      

        $mform->addElement('header', 'quizletimportfieldset', get_string('quizletimportfieldset', 'quizletimport'));
        $mform->addElement('static', 'label2', 'quizletimportsetting2', 'Your quizletimport fields go here. Replace me!');

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
