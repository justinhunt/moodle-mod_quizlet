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
 * quizlet module admin settings and defaults
 *
 * @package    mod
 * @subpackage quizlet
 * @copyright  2014 Justin Hunt (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

	  $settings->add(new admin_setting_configtext('quizlet/apikey',
        get_string('apikey', 'quizlet'), get_string('apikeyexplain', 'quizlet'), 'YOUR API KEY', PARAM_TEXT));
		
	 $settings->add(new admin_setting_configtext('quizlet/apisecret',
        get_string('apisecret', 'quizlet'), get_string('apisecretexplain', 'quizlet'), 'YOUR API SECRET', PARAM_TEXT));
	
	//flashcards dimensions
    $settings->add(new admin_setting_heading('quizlet/flashcardsdimensions', get_string('acttype_flashcards', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/flashcardswidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/flashcardsheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
	
	//scatter dimensions		
	 $settings->add(new admin_setting_heading('quizlet/scatterdimensions', get_string('acttype_scatter', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/scatterwidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/scatterheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
	
	//learn dimensions		
	$settings->add(new admin_setting_heading('quizlet/learndimensions', get_string('acttype_learn', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/learnwidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/learnheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
	
	//spelling dimensions		
	$settings->add(new admin_setting_heading('quizlet/spellerdimensions', get_string('acttype_speller', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/spellerwidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/spellerheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
	
	//spacerace dimensions		
	$settings->add(new admin_setting_heading('quizlet/spaceracedimensions', get_string('acttype_spacerace', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/spaceracewidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/spaceraceheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
			
	//test dimensions		
	$settings->add(new admin_setting_heading('quizlet/testdimensions', get_string('acttype_test', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/testwidth',
			get_string('width', 'quizlet'), '', '100%', PARAM_TEXT));
	$settings->add(new admin_setting_configtext('quizlet/testheight',
			get_string('height', 'quizlet'), '', '410', PARAM_INT));
			
	//default times	
	$settings->add(new admin_setting_heading('quizlet/defaultmintime', get_string('defmintime_heading', 'quizlet'), ''));
	$settings->add(new admin_setting_configtext('quizlet/def_flashcards_mintime',
			get_string('acttype_flashcards', 'quizlet') , '', 180, PARAM_INT));
	$settings->add(new admin_setting_configtext('quizlet/def_scatter_mintime',
			get_string('acttype_scatter', 'quizlet') , '', 120, PARAM_INT));
	$settings->add(new admin_setting_configtext('quizlet/def_spacerace_mintime',
			get_string('acttype_spacerace', 'quizlet') , '', 420, PARAM_INT));
	$settings->add(new admin_setting_configtext('quizlet/def_learn_mintime',
			get_string('acttype_learn', 'quizlet') , '', 360, PARAM_INT));
	$settings->add(new admin_setting_configtext('quizlet/def_speller_mintime',
			get_string('acttype_speller', 'quizlet') , '', 360, PARAM_INT));
	$settings->add(new admin_setting_configtext('quizlet/def_test_mintime',
			get_string('acttype_test', 'quizlet') , '', 420, PARAM_INT));
			
	//default completion
	$settings->add(new admin_setting_heading('quizlet/defaultcompletion', get_string('defcompletion_heading', 'quizlet'), ''));
	//The size of the youtube player on the various screens		
	$options = array(0 => new lang_string('no'),
						   1 => new lang_string('yes'));
					
	$settings->add(new admin_setting_configselect('quizlet/def_showcompletion',
						new lang_string('showcompletion', 'quizlet'),'', 1, $options));
						
	$settings->add(new admin_setting_configselect('quizlet/def_showcountdown',
						new lang_string('showcountdown', 'quizlet'),'', 1, $options));
	
	

}
