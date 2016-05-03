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
 * This file keeps track of upgrades to the quizgrading module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_quizgrading
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute quizgrading upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_quizgrading_upgrade($oldversion) {
    global $DB;
  
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    /*
     * And upgrade begins here. For each one, you'll need one
     * block of code similar to the next one. Please, delete
     * this comment lines once this file start handling proper
     * upgrade code.
     *
     * if ($oldversion < YYYYMMDD00) { //New version in version.php
     * }
     *
     * Lines below (this included)  MUST BE DELETED once you get the first version
     * of your module ready to be installed. They are here only
     * for demonstrative purposes and to show how the quizgrading
     * iself has been upgraded.
     *
     * For each upgrade block, the file quizgrading/version.php
     * needs to be updated . Such change allows Moodle to know
     * that this file has to be processed.
     *
     * To know more about how to write correct DB upgrade scripts it's
     * highly recommended to read information available at:
     *   http://docs.moodle.org/en/Development:XMLDB_Documentation
     * and to play with the XMLDB Editor (in the admin menu) and its
     * PHP generation posibilities.
     *
     * First example, some fields were added to install.xml on 2007/04/01
     */
    if ($oldversion < 2007040100) {

        // Define field course to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');

        // Add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field intro to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'name');

        // Add field intro.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field introformat to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'intro');

        // Add field introformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Once we reach this point, we can store the new version and consider the module
        // ... upgraded to the version 2007040100 so the next time this block is skipped.
        upgrade_mod_savepoint(true, 2007040100, 'quizgrading');
    }

    // Second example, some hours later, the same day 2007/04/01
    // ... two more fields and one index were added to install.xml (note the micro increment
    // ... "01" in the last two digits of the version).
    if ($oldversion < 2007040101) {

        // Define field timecreated to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'introformat');

        // Add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timemodified to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'timecreated');

        // Add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index course (not unique) to be added to quizgrading.
        $table = new xmldb_table('quizgrading');
        $index = new xmldb_index('courseindex', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Add index to course field.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Another save point reached.
        upgrade_mod_savepoint(true, 2007040101, 'quizgrading');
    }

    // Third example, the next day, 2007/04/02 (with the trailing 00),
    // some actions were performed to install.php related with the module.
    if ($oldversion < 2007040200) {

        // Insert code here to perform some actions (same as in install.php).

        upgrade_mod_savepoint(true, 2007040200, 'quizgrading');
    }
	
	if($oldversion < 2015031600) 
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('datum_rojstva', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
			
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
			
		upgrade_mod_savepoint(true, 2015031600, 'quizgrading');
	}
	
	if($oldversion < 2015032301) 
	{
		$table = new xmldb_table('quizgrading_category_config');

        $field = new xmldb_field('skupnotock', XMLDB_TYPE_CHAR, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
			
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		else {
			$dbman->change_field_type($table, $field);
		}
		
		upgrade_mod_savepoint(true, 2015032301, 'quizgrading');	
	}
	
	if($oldversion < 2015032500)
	{
		$table = new xmldb_table('quizgrading_attempt_info');

		$field1 = new xmldb_field('id');
		$field1->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        
        $table->addField($field1);
		
		$key1 = new xmldb_key('primary');
		$key1->set_attributes(XMLDB_KEY_PRIMARY, array('id'), null, null);
		$table->addKey($key1);
		
		$status = $dbman->create_table($table);

		upgrade_mod_savepoint(true, 2015032500, 'quizgrading');	
	}

	if($oldversion < 2015032501)
	{
		$table = new xmldb_table('quizgrading_attempt_info');

		
		$field2 = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
		
		$field3 = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
		
		$field4 = new xmldb_field('quizgradingid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }
		
		$field5 = new xmldb_field('attempt_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }
		
		$field6 = new xmldb_field('category', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field6)) {
            $dbman->add_field($table, $field6);
        }
		
		$field7 = new xmldb_field('questionsummary');
		$field7->set_attributes(XMLDB_TYPE_TEXT, 'text', null, null, null, null, '');
		if (!$dbman->field_exists($table, $field7)) {
            $dbman->add_field($table, $field7);
        }
		
		$field8 = new xmldb_field('rightanswer');
		$field8->set_attributes(XMLDB_TYPE_TEXT, 'text', null, null, null, null, '');
		if (!$dbman->field_exists($table, $field8)) {
            $dbman->add_field($table, $field8);
        }
		
		$field9 = new xmldb_field('responsesummary');
		$field9->set_attributes(XMLDB_TYPE_TEXT, 'text', null, null, null, null, '');
		if (!$dbman->field_exists($table, $field9)) {
            $dbman->add_field($table, $field9);
        }
		
		$field10 = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field10)) {
            $dbman->add_field($table, $field10);
        }
		
		$field11 = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
		if (!$dbman->field_exists($table, $field11)) {
            $dbman->add_field($table, $field11);
        }

		$index1 = new xmldb_index('quizid');
		$index1->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('quizid'));
		
		$table->addIndex($index1);
		
		
		upgrade_mod_savepoint(true, 2015032501, 'quizgrading');	
	}

	if($oldversion < 2015040900)
	{
		$table = new xmldb_table('quizgrading_att_config');

		$field1 = new xmldb_field('id');
		$field1->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        
        $table->addField($field1);
		
		$key1 = new xmldb_key('primary');
		$key1->set_attributes(XMLDB_KEY_PRIMARY, array('id'), null, null);
		$table->addKey($key1);
		
		
		$field2 = new xmldb_field('quizid');
		$field2->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->addField($field2);
		
		$field2 = new xmldb_field('atribut');
		$field2->set_attributes(XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->addField($field2);
		
		$field2 = new xmldb_field('prikazi');
		$field2->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->addField($field2);
		
		$field2 = new xmldb_field('pozicija');
		$field2->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->addField($field2);
		
		$index1 = new xmldb_index('quizid');
		$index1->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('quizid'));
		
		$table->addIndex($index1);
		
		$status = $dbman->create_table($table);

		upgrade_mod_savepoint(true, 2015040900, 'quizgrading');	
	}

	if($oldversion < 2015041700)
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('startna_st', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
			
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015041700, 'quizgrading');	
	}
	
	if($oldversion < 2015042000)
	{
		$table = new xmldb_table('quizgrading');

        $field = new xmldb_field('tip_instance', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');
			
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015042000, 'quizgrading');	
	}
	
	if($oldversion < 2015042001)
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('tocke_poligon', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_voznja', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('uvrstitev_posamezniki', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('skupina', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_skupina', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('uvrstitev_skupina', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015042001, 'quizgrading');	
	}

	if($oldversion < 2015042002)
	{
		$table = new xmldb_table('quizgrading');

        $field = new xmldb_field('max_kaz_poligon', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('max_kaz_voznja', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0','');	
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015042002, 'quizgrading');	
	}
	
	if($oldversion < 2015042100)
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('tocke_poligon', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -1,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_voznja', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -1,'');	
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015042100, 'quizgrading');	
	}
	
	if($oldversion < 2015042300)
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('tocke_skupaj', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 0,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_mod_savepoint(true, 2015042300, 'quizgrading');	
	}
	
	if($oldversion < 2015042301)
	{
		$table = new xmldb_table('quizgrading_results');

        $field = new xmldb_field('tocke_poligon', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_voznja', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_skupaj', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('tocke_skupaj', XMLDB_TYPE_INTEGER, '10', XMLDB_SIGNED, XMLDB_NULL, null, -888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('uvrstitev_posamezniki', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('uvrstitev_skupina', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 888,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		
		upgrade_mod_savepoint(true, 2015042301, 'quizgrading');	
	}
	
	if($oldversion < 2015042400)
	{
		$table = new xmldb_table('quizgrading_results');


        $field = new xmldb_field('optionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 0,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('naziv_izvedbe', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NULL, null, '','');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		
		upgrade_mod_savepoint(true, 2015042400, 'quizgrading');	
	}
	
	if($oldversion < 2015050700)
	{
		$table = new xmldb_table('quizgrading');


        $field = new xmldb_field('max_uvrst_pos', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 0,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('max_uvrst_skup', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 0,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

		upgrade_mod_savepoint(true, 2015050700, 'quizgrading');	
	}
	
	if($oldversion < 2015060900)
	{
		$table = new xmldb_table('quizgrading_results');
		
		$index1 = new xmldb_index('quizid_userid');
		$index1->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('quizid','userid'));
		
		$dbman->add_index($table, $index1);
		
		upgrade_mod_savepoint(true, 2015060900, 'quizgrading');	
	}
	
	if($oldversion < 2015082101)
	{
		$table = new xmldb_table('quizgrading_results');


        $field = new xmldb_field('mentorid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NULL, null, 0,'');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		
		upgrade_mod_savepoint(true, 2015082101, 'quizgrading');	
	}
	
	if($oldversion < 2016042600)
	{
		$table = new xmldb_table('quizgrading_results');


        $field = new xmldb_field('organizator', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NULL, null, '','');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('lokacija', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NULL, null, '','');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		
		upgrade_mod_savepoint(true, 2016042600, 'quizgrading');	
	}
	
	if($oldversion < 2016042601)
	{
		$table = new xmldb_table('quizgrading');


        $field = new xmldb_field('organizator', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NULL, null, '','');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('lokacija', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NULL, null, '','');	 
		
		if ($dbman->field_exists($table, $field))
		{$dbman->drop_field($table, $field);}
		
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		
		upgrade_mod_savepoint(true, 2016042601, 'quizgrading');	
	}
	
	/*
	 * <FIELD NAME="max_kaz_poligon" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" DEFAULT="0" SEQUENCE="false" PREVIOUS="timecreated"/>
        <FIELD NAME="max_kaz_voznja" TYPE="int" LENGTH="10" NOTNULL="true" UNSIGNED="true" DEFAULT="0" SEQUENCE="false" PREVIOUS="timecreated"/>
	 * */

	
	//$dbman->change_field_type($table, $field, $continue=true, $feedback=true)

    /*
     * And that's all. Please, examine and understand the 3 example blocks above. Also
     * it's interesting to look how other modules are using this script. Remember that
     * the basic idea is to have "blocks" of code (each one being executed only once,
     * when the module version (version.php) is updated.
     *
     * Lines above (this included) MUST BE DELETED once you get the first version of
     * yout module working. Each time you need to modify something in the module (DB
     * related, you'll raise the version and add one upgrade block here.
     *
     * Finally, return of upgrade result (true, all went good) to Moodle.
     */
    return true;
}
