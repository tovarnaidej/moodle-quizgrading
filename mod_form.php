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
 * The main quizgrading configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_quizgrading
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_quizgrading
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quizgrading_mod_form extends moodleform_mod {

	function add_completion_rules() {
        $mform = & $this->_form;

        $group = array();
        $group[] = & $mform->createElement('checkbox', 'enablecompletion', ' ', get_string('enablecompletion', 'booking'));
        $mform->addGroup($group, 'enablecompletiongroup', get_string('enablecompletiongroup', 'booking'), array(' '), false);

        return array('enablecompletiongroup');
    }

    function completion_rule_enabled($data) {
        return !empty($data['enablecompletion']);
    }
    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('quizgradingname', 'quizgrading'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'quizgradingname', 'quizgrading');

        // Adding the standard "intro" and "introformat" fields.
        $this->add_intro_editor();

        // Adding the rest of quizgrading settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('text', 'coursemoduleid', 'ID Kviza', array('size' => '24'));
		$mform->addElement('text', 'bookingid', 'Booking ID', array('size' => '24'));
		
		$optionsTip = array(
			'1' => 'Teorija',
			'2' => 'Kolesarski izpit',
			'3' => 'Tekmovanje posameznikov',
			'4' => 'Tekmovanje skupin'
		);
		$mform->addElement('select', 'tip_instance', 'Tip instance', $optionsTip);

        $mform->addElement('header', 'quizgradingfieldset', 'Nastavitve');
        $mform->addElement('text', 'procent', 'Minimalno procentov', array('size' => '24'));
		
		$mform->addElement('text', 'max_kaz_poligon', 'Max. kazenskih poligon', array('size' => '24'));
		$mform->addElement('text', 'max_kaz_voznja', 'Max. kazenskih vožnja', array('size' => '24'));
		
		$mform->addElement('text', 'max_uvrst_pos', 'Max. uvrstitev za zaključitev', array('size' => '24'));
		$mform->addElement('text', 'max_uvrst_skup', 'Max. uvrstitev za zaključitev skupinsko', array('size' => '24'));
		
		$optionsDosezene = array(
		    'dosezene' => 'Dosežene točke',
		    'kazenske' => 'Kazenske točke'
		);
		
		$mform->addElement('select', 'dosezene_kazenske', 'Izpis doseženih ali kazenskih točk', $optionsDosezene);
		
		$optionsOpravil = array(
		    'DA' => 'DA',
		    'NE' => 'NE'
		);
		$mform->addElement('select', 'izpis_opravil', 'Naj se izpiše podatek ali je udeleženec kviz opravil', $optionsOpravil);
		
		

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
