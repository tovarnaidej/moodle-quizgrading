<?php
global $CFG;
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
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');

/**
 * Module instance settings form
 *
 * @package    mod_quizgrading
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class nastavitve_form extends moodleform {

    public function definition() {

        global $CFG;
		global $DB;
		global $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 
        
        $quizgrading = $DB->get_record('quizgrading', array('coursemoduleid'=>$this->_customdata['quizid']));
		
		//var_dump($quizgrading);
 /*
        $mform->addElement('header', 'quizgradingfieldset', 'Nastavitve točkovanja');
        $mform->addElement('text', 'procent', 'Minimalno procentov', array('size' => '24'));
		
		$mform->addElement('text', 'max_kaz_poligon', 'Max. kazenskih poligon', array('size' => '24'));
		$mform->addElement('text', 'max_kaz_voznja', 'Max. kazenskih vožnja', array('size' => '24'));
		
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
		
		$mform->addElement('hidden', 'shrani_nastavitve', '1');
		$mform->addElement('hidden', 'quizid', $this->_customdata['quizid']);
		*/
		//$mform->addElement('header', 'quizgradingfieldset', 'Nastavitve kategorij');
		
		//$html = get_quiz_categories($this->_customdata['quizid']);
		
		//$mform->addElement('html', "<div id='kategorijeConfig'>".$html."</div>");
		
		//style="height:300px;overflow-y: auto;"
		$htmlAttr = '<div>
		<table cellpadding="5">
		<tr>
			<th>Atribut</th>
			<th>Prikazan</th>
			<th>Pozicija</th>
		</tr>';

		$user = $DB->get_record('user', array('id'=>$USER->id));
	
		$attributeConfig = $DB->get_records('quizgrading_att_config', array('quizid'=>$this->_customdata['quizid']));
		
		$attCfgArr = Array();
		
		foreach($attributeConfig as $key=>$object)
		{
			$attCfgArr[$object->atribut] = $object;
		}
	
		$dovoljeniAtributi = Array('id','username','email','icq','skype','yahoo','aim','msn','phone1','phone2','institution','department','address','city','country','description','middlename','vprasanja');
	
		foreach($user as $key=>$value)
		{
			if(in_array($key, $dovoljeniAtributi))
			{
				$prikazi = 0;
				$pozicija = 0;
			
				if(key_exists($key, $attCfgArr))
				{
					$prikazi = 	$attCfgArr[$key]->prikazi;
					$pozicija = 	$attCfgArr[$key]->pozicija;
				}
				
				$htmlAttr .= '<tr>
					<td valign="top">'.$key.'</td>
					<td valign="top"><select id="prikazi_'.$key.'"><option '.((!$prikazi) ? "selected" : "").' value="0">NE</option><option '.(($prikazi) ? "selected" : "").' value="1">DA</option></select></td>
					<td valign="top"><input id="pozicija_'.$key.'" type="text" value="'.$pozicija.'" name="'.$key.'" /></td>
					<td valign="top"><button id="shrani_'.$key.'" class="btn_shrani">SHRANI</button></td>
				</tr>';
	
			}
			
		}
	
		  
		  $htmlAttr .= '<tr>
		  	<td valign="top">Prikaži vprašanja</td>
			  	<td valign="top"><select id="prikazi_vprasanja"><option value="1">DA</option><option value="0">NE</option></select></td>
				<td valign="top"><input id="pozicija_vprasanja" type="text" value="0" name="vprasanja" /></td>
				<td valign="top"><button id="shrani_vprasanja" class="btn_shrani">SHRANI</button></td>
			  </tr>
			</table>
		</div>';
		
		$mform->addElement('header', 'quizgradingfieldset', 'Nastavitve atributov');

		$mform->addElement('html', $htmlAttr);
		
		//$this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
	
	function getForm()
	{
		return $this->_form;
	}
	
	function setQuiz($_quizid)
	{
		$this->quizid = $_quizid;
	}
}
