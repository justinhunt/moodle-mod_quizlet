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
 * This file keeps track of upgrades to the quizlet module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_quizlet
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute quizlet upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_quizlet_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // initial change
    if ($oldversion < 2014021100) {

        // Define field activitytype to be added to quizlet
        $table = new xmldb_table('quizlet');
        $field = new xmldb_field('activitytype', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'name');

        // Add field activitytype
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field quizletset to be added to quizlet
        $field = new xmldb_field('quizletset', XMLDB_TYPE_CHAR, 255, null, null, null, '','activitytype');

        // Add field quizletset
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
         // Define field mintime to be added to quizlet
        $field = new xmldb_field('mintime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'quizletset');


        // Add field mintime
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Once we reach this point, we can store the new version and consider the module
        // upgraded to the version 2007040100 so the next time this block is skipped
        upgrade_mod_savepoint(true, 2014021100, 'quizlet');
    }
    
      // added showcompletion and showcountdown fields
    if ($oldversion < 2014022300) {

        // Define field activitytype to be added to quizlet
        $table = new xmldb_table('quizlet');
        $field = new xmldb_field('showcountdown', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'mintime');

        // Add field showcountdown
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // add field show completion
         $field = new xmldb_field('showcompletion', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'showcountdown');

        // Add field quizletset
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        

        // Once we reach this point, we can store the new version and consider the module upgraded
        upgrade_mod_savepoint(true, 2014022300, 'quizlet');
    }
      // added showcompletion and showcountdown fields
    if ($oldversion < 2014060800) {

        // Define field activitytype to be added to quizlet
        $table = new xmldb_table('quizlet');
        $field = new xmldb_field('quizletsettitle', XMLDB_TYPE_CHAR, 255, null, null, null, '','quizletset');

        // Add field showcountdown
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        

        // Once we reach this point, we can store the new version and consider the module upgraded
        upgrade_mod_savepoint(true, 2014060800, 'quizlet');
    }
    
     if ($oldversion < 2014070101) {

        // Define table timedpage_log to be created.
        $table = new xmldb_table('quizlet_log');

        // Adding fields to table timedpage_log.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('action', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table timedpage_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table timedpage_log.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Conditionally launch create table for timedpage_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Timedpage savepoint reached.
        upgrade_mod_savepoint(true, 2014070101, 'quizlet');
    }


    return true;
}
