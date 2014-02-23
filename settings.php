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
 * quizletimport module admin settings and defaults
 *
 * @package    mod
 * @subpackage quizletimport
 * @copyright  2014 Justin Hunt (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('quizletimport/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));


    //--- modedit defaults -----------------------------------------------------------------------------------

	  $settings->add(new admin_setting_configtext('quizletimport/apikey',
        get_string('apikey', 'quizletimport'), get_string('apikeyexplain', 'quizletimport'), 'YOUR API KEY', PARAM_TEXT));
		
	 $settings->add(new admin_setting_configtext('quizletimport/apisecret',
        get_string('apisecret', 'quizletimport'), get_string('apisecretexplain', 'quizletimport'), 'YOUR API SECRET', PARAM_TEXT));
	
	//flashcards dimensions
    $settings->add(new admin_setting_heading('quizletimport/flashcardsdimensions', get_string('acttype_flashcards', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/flashcardswidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/flashcardsheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
	
	//scatter dimensions		
	 $settings->add(new admin_setting_heading('quizletimport/scatterdimensions', get_string('acttype_scatter', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/scatterwidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/scatterheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
	
	//learn dimensions		
	$settings->add(new admin_setting_heading('quizletimport/learndimensions', get_string('acttype_learn', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/learnwidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/learnheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
	
	//spelling dimensions		
	$settings->add(new admin_setting_heading('quizletimport/spellerdimensions', get_string('acttype_speller', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/spellerwidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/spellerheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
	
	//spacerace dimensions		
	$settings->add(new admin_setting_heading('quizletimport/spaceracedimensions', get_string('acttype_spacerace', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/spaceracewidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/spaceraceheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
			
	//test dimensions		
	$settings->add(new admin_setting_heading('quizletimport/testdimensions', get_string('acttype_test', 'quizletimport'), ''));
	$settings->add(new admin_setting_configtext('quizletimport/testwidth', 
			get_string('width', 'quizletimport'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizletimport/testheight', 
			get_string('height', 'quizletimport'), '', '410', PARAM_INT));
	
	
	

}
