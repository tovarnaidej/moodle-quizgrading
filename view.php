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
 * Prints a particular instance of quizgrading
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_quizgrading
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace quizgrading with the name of your module and remove this line.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... quizgrading instance ID - it should be named as the first character of the module.
$recalculate  = optional_param('recalculate', 0, PARAM_INT);


if ($id) {
    $cm         = get_coursemodule_from_id('quizgrading', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $quizgrading  = $DB->get_record('quizgrading', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $quizgrading  = $DB->get_record('quizgrading', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $quizgrading->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('quizgrading', $quizgrading->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);


$event = \mod_quizgrading\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $quizgrading);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/quizgrading/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($quizgrading->name));
$PAGE->set_heading(format_string($quizgrading->name));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('quizgrading-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($quizgrading->intro) {
    echo $OUTPUT->box(format_module_intro('quizgrading', $quizgrading, $cm->id), 'generalbox mod_introbox', 'quizgradingintro');
}


$context = context_module::instance($cm->id);
//$context = get_context_instance(CONTEXT_MODULE, $cm->id, true);
$roles = get_user_roles($context, $USER->id, true);

//var_dump($contextmodule,$context);

$coursemodel = $DB->get_record('course_modules', array('id'=>$quizgrading->coursemoduleid));

$quiz = $DB->get_record('quiz',array('id'=>$coursemodel->instance));



if($recalculate)
{
	is_quiz_success_regrading($quiz->id,true,$cm->id);
}


$role = "admin";

foreach($roles as $key=>$object)
{
	
	if($object->shortname == "student")
		{$role = "student";}
}

$query = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = :userid AND cm.id = :bookingid
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";


//$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quiz->id." AND mentorid IN (".$USER->id.")";
$query = "SELECT * FROM {quizgrading_results} WHERE quizid=:quizid AND mentorid IN (:userid)";

//$bookings = $DB->get_records_sql($query,array('userid'=>$USER->id,'bookingid'=>$quizgrading->bookingid));
$bookings = $DB->get_records_sql($query,array('userid'=>$USER->id,'quizid'=>$quiz->id));
//$bookings = $DB->get_records_sql($query);

if(count($bookings) > 0)
{
	$role = "student";
}


$zadnji_booking = get_quiz_last_booking_mentor($quiz->id,$USER->id,$cm->id);

$thispageurl = "";
$datum = "";
$os = "";
$booking = "";
$solsko_leto = "";
$opravil = "";


switch($role)
{
	case "student":
			if(count($bookings) > 0)
			{
				$tabs = array(array(
				new tabobject('view', new moodle_url($thispageurl,
				array('courses' => 'view','id'=>$cm->id)), "Rezultati"),
				));
				$activetab = false;
				
				if(isset($_GET['courses']))
				$activetab = $_GET['courses'];
				
				if(!$activetab) $activetab = "view";
				
				//print_tabs($tabs, $activetab);
				
				if(isset($_GET['datum']))
				$datum = ($_GET['datum']) ? $_GET['datum'] : ""; 
				
				if(isset($_GET['os']))
				$os = ($_GET['os']) ? $_GET['os'] : "";
				
				if(isset($_GET['booking']))
				$booking = ($_GET['booking']) ? $_GET['booking'] : "";
				
				if(isset($_GET['solsko_leto']))
				$solsko_leto = ($_GET['solsko_leto']) ? $_GET['solsko_leto'] : "";
				
				if(isset($_GET['opravil']))
				$opravil = ($_GET['opravil'] == "0" OR $_GET['opravil'] == "1") ? $_GET['opravil'] : "";
				
				if($solsko_leto == "")
				{
					if(mktime(0, 0, 0, date("m")  , date("d"), date("Y")) > mktime(0, 0, 0, 8  , 31, date("Y")))
					{
						$solsko_leto = date("Y")."/".(date("y")+1);
					}
					else
					{
						$solsko_leto = (date("Y")-1)."/".(date("y"));
					}
				}

				if($zadnji_booking)
				{
					if($datum=="")
					{
						$datum = $zadnji_booking->timefinish;
					}
					
					if($booking == "")
					{
						$booking = $zadnji_booking->optionid;
					}
				}
				
				$dates = get_quiz_dates_mentor($quiz->id,$USER->id,$cm->id,$solsko_leto,$booking);
				
				require_once("quizgrade_view_mentor.php");
			}
			else
			{
				$dates = get_quiz_dates($quiz->id);
				require_once("quizgrade_view_student.php");
			}
		break;
	default:
		$dates = get_quiz_dates($quiz->id);
		$tabs = array(array(
		new tabobject('nastavitve', new moodle_url($thispageurl,
		array('courses' => 'nastavitve','id'=>$cm->id)), "Nastavitve"),
		new tabobject('view', new moodle_url($thispageurl,
		array('courses' => 'view','id'=>$cm->id)), "Rezultati"),
		));
		
		$activetab = false;
		if(isset($_GET['courses']))
		{
			$activetab = $_GET['courses'];
		}
		
		if(!$activetab) $activetab = "view";
		
		print_tabs($tabs, $activetab);
		
		if(isset($_GET['datum']))
		$datum = ($_GET['datum']) ? $_GET['datum'] : ""; 
		
		if(isset($_GET['os']))
		$os = ($_GET['os']) ? $_GET['os'] : "";
		
		if(isset($_GET['booking']))
		$booking = ($_GET['booking']) ? $_GET['booking'] : "";
		
		if(isset($_GET['solsko_leto']))
			$solsko_leto = ($_GET['solsko_leto']) ? $_GET['solsko_leto'] : "";
				
				if($solsko_leto == "")
				{
					if(mktime(0, 0, 0, date("m")  , date("d"), date("Y")) > mktime(0, 0, 0, 8  , 31, date("Y")))
					{
						$solsko_leto = date("Y")."/".(date("y")+1);
					}
					else
					{
						$solsko_leto = (date("Y")-1)."/".(date("y"));
					}
				}
		
		
		require_once("quizgrade_view.php");

		
		break;
}


// Finish the page.
echo $OUTPUT->footer();
