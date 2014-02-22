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
 * This file keeps track of upgrades to the quizletimport module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_quizletimport
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute quizletimport upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_quizletimport_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // First example, some fields were added to install.xml on 2007/04/01
    if ($oldversion < 2014021100) {

        // Define field activitytype to be added to quizletimport
        $table = new xmldb_table('quizletimport');
        $field = new xmldb_field('activitytype', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'name');

        // Add field activitytype
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field quizletset to be added to quizletimport
        $field = new xmldb_field('quizletset', XMLDB_TYPE_CHAR, 255, null, null, null, '','activitytype');

        // Add field quizletset
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
         // Define field mintime to be added to quizletimport
        $field = new xmldb_field('mintime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'quizletset');


        // Add field mintime
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Once we reach this point, we can store the new version and consider the module
        // upgraded to the version 2007040100 so the next time this block is skipped
        upgrade_mod_savepoint(true, 2014021100, 'quizletimport');
    }

    return true;
}
