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
 * @package    mod
 * @subpackage quizletimport
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_quizletimport_activity_task
 */

/**
 * Define the complete quizletimport structure for backup, with file and id annotations
 */
class backup_quizletimport_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $quizletimport = new backup_nested_element('quizletimport', array('id'), array(
            'name','activitytype','quizletset','mintime','showcountdown','showcompletion', 
            'intro', 'introformat', 'timecreated', 'timemodified'));

        // Build the tree
        // (love this)

        // Define sources
        $quizletimport->set_source_table('quizletimport', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none)

        // Define file annotations
        $quizletimport->annotate_files('mod_quizletimport', 'intro', null); // This file areas haven't itemid

        // Return the root element (quizletimport), wrapped into standard activity structure
        return $this->prepare_activity_structure($quizletimport);
    }
}
