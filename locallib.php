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
 * Internal library of functions for module quizlet
 *
 * All the quizlet specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_quizlet
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
//require completion lib, so we can check our completion status
require_once($CFG->dirroot.'/lib/completionlib.php');
//require quizlet library
require_once(dirname(__FILE__).'/quizlet.php');

/**
 * File browsing support class
 */
class quizlet_helper {

    //$quizlet = null;
    //$course = null;
    //$cmid = null;
	
    /**
     * Constructor of quizlet plugin
     *
     * @param array $options
     */
    public function __construct($quizlet,$course,$cm) {
        global $CFG;

        $args = array(
			'api_scope' => 'read'
        );

        $this->quizlet = new quizlet($args);
        $this->course = $course;
        $this->cm = $cm;
        $this->qi = $quizlet;
    }
    
      /**
     * Output the JavaScript required to initialise the countdown timer.
     * @param int $timerstartvalue time remaining, in seconds.
     */
    public function initialise_timer($page) {
		if($this->qi->mintime==0){
			$config = get_config('quizlet');
			switch($this->qi->activitytype){
				case quizlet::TYPE_SCATTER:
					$this->qi->mintime = $config->def_scatter_mintime;
					break;
				case quizlet::TYPE_SPACERACE:
					$this->qi->mintime = $config->def_spacerace_mintime;
					break;
				case quizlet::TYPE_TEST:
					$this->qi->mintime = $config->def_test_mintime;
					break;
				case quizlet::TYPE_SPELLER:
					$this->qi->mintime = $config->def_speller_mintime;
					break;
				case quizlet::TYPE_LEARN:
					$this->qi->mintime = $config->def_learn_mintime;
					break;
				case quizlet::TYPE_CARDS:	
				default:
					$this->qi->mintime = $config->def_flashcards_mintime;
					
			}
		}
        $options = array($this->qi->mintime,$this->qi->showcountdown>0,$this->cm->id,$this->qi->showcompletion>0,$this->is_complete());
        $page->requires->js_init_call('M.mod_quizlet.timer.init', $options, false);
    }
    
    public function is_complete(){
        global $USER;
         // Get current completion state
        $completion = new completion_info($this->course);
        $data = $completion->get_data($this->cm, false, $USER->id);

        // Is the activity already complete
        $completed= $data->viewed == COMPLETION_VIEWED;    
        return $completed;
    }
    
       /**
     * Return the HTML of the quiz timer.
     * @return string HTML content.
     */
    public function fetch_completed_tag() {
        return html_writer::tag('div',  get_string('completed', 'quizlet'),array('id' => 'quizlet-completed'));
        
    }
    
       /**
     * Return the HTML of the quiz timer.
     * @return string HTML content.
     */
    public function fetch_countdown_timer() {
        return html_writer::tag('div', get_string('timeleft', 'quizlet') . ' ' .
            html_writer::tag('span', '', array('id' => 'quizlet-time-left')),
            array('id' => 'quizlet-timer', 'role' => 'timer',
                'aria-atomic' => 'true', 'aria-relevant' => 'text'));
    }
    

	
	   /**
     * Set quizlet option
     * @param array $options
     * @return mixed
     */
     /*
    public function set_option($options = array()) {
        if (!empty($options['quizlet_key'])) {
            set_config('quizlet_key', trim($options['quizlet_key']), 'quizlet');
        }
        if (!empty($options['quizlet_secret'])) {
            set_config('quizlet_secret', trim($options['quizlet_secret']), 'quizlet');
        }
  
        unset($options['quizlet_key']);
        unset($options['quizlet_secret']);
        $ret = parent::set_option($options);
        return $ret;
    }
    */

    /**
     * Get quizlet options
     * @param string $config
     * @return mixed
     */
     /*
    public function get_option($config = '') {
        if ($config==='quizlet_key') {
            return trim(get_config('quizlet', 'quizlet_key'));
        } elseif ($config==='quizlet_secret') {
            return trim(get_config('quizlet', 'quizlet_secret'));
        } else {
            $options = parent::get_option();
            $options['quizlet_key'] = trim(get_config('quizlet', 'quizlet_key'));
            $options['quizlet_secret'] = trim(get_config('quizlet', 'quizlet_secret'));
        }
        return $options;
    }
    */
}//end of quizlet import