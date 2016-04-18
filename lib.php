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
 * Library of interface functions and constants for module quizgrading
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the quizgrading specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_quizgrading
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/*
 * Example constant:
 * define('quizgrading_ULTIMATE_ANSWER', 42);
 */

/**
 * Moodle core API
 */

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function quizgrading_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
		case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
       case FEATURE_COMPLETION_HAS_RULES: return true;
        default:
            return null;
    }
}

function quizgrading_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

	$gradingConfig = "";
	
	//$query = "SELECT gr.*,cm.* FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm->instance;
	
	//$gradingConfig2 = $DB->get_record_sql($query);
	
    if (!($gradingConfig = $DB->get_record('quizgrading', array('id' => $cm->instance)))) {
        throw new Exception("Can't find quizgrading {$cm->instance}");
    }
	
	$coursemodel = $DB->get_record('course_modules', array('id'=>$gradingConfig->coursemoduleid));

	$quiz = $DB->get_record('quiz',array('id'=>$coursemodel->instance));
	
	
	$query = "SELECT * FROM {quizgrading_results} WHERE userid=? AND quizid=? ORDER BY datum_resitve DESC LIMIT 1";
	
	$result = $DB->get_record_sql($query,array($userid,$quiz->id));
	
	$izpitOpravil = 0;
				
	if($result->status_kviza && $result->tocke_poligon >= 0 && $result->tocke_poligon <= $gradingConfig->max_kaz_poligon && $result->tocke_voznja >= 0 && $result->tocke_voznja <= $gradingConfig->max_kaz_voznja) $izpitOpravil = 1;
		
			
	//return TRUE;
	
	switch($gradingConfig->tip_instance)
	{
		case "1":
			if($result->status_kviza)
			{
				return TRUE;
			}
			else {
				return FALSE;
			}
			break;
		case "2":
			if($izpitOpravil)
			{
				return TRUE;
			}
			else {
				return FALSE;
			}
			break;
		case "3":
			if($result->status_kviza && $result->uvrstitev_posamezniki > 0  && $result->uvrstitev_posamezniki <= $gradingConfig->max_uvrst_pos)
			{
				return TRUE;
			}
			else {
				return FALSE;
			}
		break;
		case "4":
			if($izpitOpravil && $result->uvrstitev_skupina > 0 && $result->uvrstitev_skupina <= $gradingConfig->max_uvrst_skup)
			{
				return TRUE;
			}
			else {
				return FALSE;
			}
			break;
	}

	return $type;
}

/**
 * Saves a new instance of the quizgrading into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $quizgrading An object from the form in mod_form.php
 * @param mod_quizgrading_mod_form $mform
 * @return int The id of the newly inserted quizgrading record
 */
function quizgrading_add_instance(stdClass $quizgrading, mod_quizgrading_mod_form $mform = null) {
    global $DB;

    $quizgrading->timecreated = time();

    // You may have to add extra stuff in here.

    return $DB->insert_record('quizgrading', $quizgrading);
}

/**
 * Updates an instance of the quizgrading in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $quizgrading An object from the form in mod_form.php
 * @param mod_quizgrading_mod_form $mform
 * @return boolean Success/Fail
 */
function quizgrading_update_instance(stdClass $quizgrading, mod_quizgrading_mod_form $mform = null) {
    global $DB;

    $quizgrading->timemodified = time();
    $quizgrading->id = $quizgrading->instance;

    // You may have to add extra stuff in here.

    return $DB->update_record('quizgrading', $quizgrading);
}

/**
 * Removes an instance of the quizgrading from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function quizgrading_delete_instance($id) {
    global $DB;

    if (! $quizgrading = $DB->get_record('quizgrading', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('quizgrading', array('id' => $quizgrading->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function quizgrading_user_outline($course, $user, $mod, $quizgrading) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $quizgrading the module instance record
 * @return void, is supposed to echp directly
 */
function quizgrading_user_complete($course, $user, $mod, $quizgrading) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in quizgrading activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function quizgrading_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link quizgrading_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function quizgrading_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see quizgrading_get_recent_mod_activity()}
 *
 * @return void
 */
function quizgrading_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function quizgrading_cron () {

    return true; 
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function quizgrading_get_extra_capabilities() {
    return array();
}

/**
 * Gradebook API                                                              //
 */

/**
 * Is a given scale used by the instance of quizgrading?
 *
 * This function returns if a scale is being used by one quizgrading
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $quizgradingid ID of an instance of this module
 * @return bool true if the scale is used by the given quizgrading instance
 */
function quizgrading_scale_used($quizgradingid, $scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('quizgrading', array('id' => $quizgradingid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of quizgrading.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any quizgrading instance
 */
function quizgrading_scale_used_anywhere($scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('quizgrading', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give quizgrading instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $quizgrading instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function quizgrading_grade_item_update(stdClass $quizgrading, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $item = array();
    $item['itemname'] = clean_param($quizgrading->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $quizgrading->grade;
    $item['grademin']  = 0;

    grade_update('mod/quizgrading', $quizgrading->course, 'mod', 'quizgrading', $quizgrading->id, 0, null, $item);
}

/**
 * Update quizgrading grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $quizgrading instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function quizgrading_update_grades(stdClass $quizgrading, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    $grades = array(); // Populate array of grade objects indexed by userid. @example .

    grade_update('mod/quizgrading', $quizgrading->course, 'mod', 'quizgrading', $quizgrading->id, 0, $grades);
}

/**
 * File API                                                                   //
 */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function quizgrading_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for quizgrading file areas
 *
 * @package mod_quizgrading
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function quizgrading_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the quizgrading file areas
 *
 * @package mod_quizgrading
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the quizgrading's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function quizgrading_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/**
 * Navigation API                                                             //
 */

/**
 * Extends the global navigation tree by adding quizgrading nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the quizgrading module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function quizgrading_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the quizgrading settings
 *
 * This function is called when the context for the page is a quizgrading module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $quizgradingnode {@link navigation_node}
 */
function quizgrading_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $quizgradingnode=null) {
}

function is_quiz_success_regrading($quizid,$zapisi,$gradingid)
{
	
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	require_login();
	
	global $CFG;
	global $DB;
	
	require_once($CFG->libdir . '/completionlib.php');
	
	if(!is_numeric($quizid))
	die("Napačni parametri!");
	
	$config = Array();

	$config['P'] = 70;

	$quiz = $DB->get_record('quiz', array('id'=>$quizid));
	//$attempt = $DB->get_record('quiz_attempts', array('uniqueid'=>$attemptid));
	
	
	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$gradingid;
	
	$gradingConfig = $DB->get_record_sql($query);

	$config['P'] = $gradingConfig->procent;
	$config_db['DK'] = $gradingConfig->dosezene_kazenske;
	$config_db['O'] = $gradingConfig->izpis_opravil;
	

	//$query = "SELECT q_att.*,question.category FROM {question_attempts} q_att, {question} question WHERE q_att.questionid=question.id AND q_att.questionusageid=".$attemptid;
	$query = "SELECT * FROM {quizgrading_results} WHERE quizgradingid=".$gradingid." AND quizid=".$quizid;
	
	$slots = $DB->get_records_sql($query);
	

	
	//var_dump($slots);echo "<br /><br />";
	foreach($slots as $key=>$object)
	{
		$tocke = Array();
		$skupnotock = Array();
		
		try
		{
			$category_config = $DB->get_records('quizgrading_category_config', array('quizid'=>$quizid));
			
			foreach($category_config as $conf)
			{
				$tocke[$conf->category] = $conf->tocke;
				
				if($conf->skupnotock != 'N')
					$skupnotock[$conf->category] = $conf->skupnotock;
				else $skupnotock[$conf->category] = PHP_INT_MAX;
			}
		}
		catch(Exception $e)	{}
		
		//var_dump($skupnotock);
		//echo "<br /><br />";
		
		$naredil = TRUE;
		$vprasanjaSplit = explode(',', $object->vprasanja);
		$vprasanja = Array();
		
		$negativneTocke = 0;
		$skupajTockeQuiz = 0;
		
		$vprasanja = Array();
		
		foreach($vprasanjaSplit as $vpr)
		{
			$vprSplit = explode('|',$vpr);
			
			$cat = $DB->get_record('quizgrading_category_config',array('category'=>$vprSplit[0],'quizid'=>$quizid));

			if(!$cat)
			{
				$cat = $DB->get_record('question_categories',array('id'=>$vprSplit[0]));
				$cat->izpisi_kaj = substr($cat->name, 0,stripos($cat->name,' '));
				$cat->izpisi = "DA";
			}
			
			
			if($vprSplit[1] == "NE")
			{
				
				$vprasanja[] = $vpr; 
				
				if(array_key_exists(intval($vprSplit[0]),$skupnotock))
				{
					if($skupnotock[intval($vprSplit[0])] < 0)
					{
						$naredil = FALSE;
						//break;
					}
						
					$skupnotock[intval($vprSplit[0])] = $skupnotock[intval($vprSplit[0])] - $tocke[intval($vprSplit[0])];
					
					$negativneTocke+=$tocke[intval($vprSplit[0])];
					$skupajTockeQuiz+=$tocke[intval($vprSplit[0])];
					
					if($skupnotock[intval($vprSplit[0])] < 0)
					{
						$naredil = FALSE;
						//break;
					}
				}
				else {
					
					$cat = $DB->get_record('question_categories', array('id'=>$vprSplit[0]));
					
					while(!array_key_exists(intval($cat->id),$skupnotock) AND $cat->parent > 0)
					{
						$cat = get_next_cat($cat->parent);
					}
					
					if(array_key_exists(intval($cat->id),$skupnotock))
					{
						if($skupnotock[intval($cat->id)] < 0)
						{
							$naredil = FALSE;
							//break;
						}
						
						$skupnotock[intval($cat->id)] = $skupnotock[intval($cat->id)] - $tocke[intval($cat->id)];
						$negativneTocke+=$tocke[intval($cat->id)];
						$skupajTockeQuiz+=$tocke[intval($cat->id)];
						
						if($skupnotock[intval($vprSplit[0])] < 0)
						{
							$naredil = FALSE;
							//break;
						}
					}
					else
					{
						$skupajTockeQuiz++;
						$negativneTocke++;
					}
				}
			}
			else
			{
				$vprasanja[] = $vpr;
				
				if($tocke[intval($vprSplit[0])])
				$skupajTockeQuiz+=(($tocke[intval($vprSplit[0])] > 0) ? $tocke[intval($vprSplit[0])] : 1);
				else $skupajTockeQuiz+=1;
			}
			
		}

		
		
		//ZAPISI
		$percentage = 0;
	
		if($skupajTockeQuiz > 0)
		$percentage = (($skupajTockeQuiz-$negativneTocke)*100)/$skupajTockeQuiz;
		
		if(intval($percentage) < intval($config['P'])) $naredil = FALSE;
	
		
		if($zapisi)
		{
			require_once($CFG->dirroot.'/user/profile/lib.php');
			$DB->delete_records('quizgrading_results', array('quizid'=>$quizid,'attempt_id'=>$object->attempt_id));
			
			if(!$DB->record_exists('quizgrading_results', array('quizid'=>$quizid,'attempt_id'=>$object->attempt_id)))
			{
				$user = $DB->get_record('user', array('id'=>$object->userid));
				
				profile_load_data($user);
				
				//var_dump($user);
				
				$query = "SELECT *,ba.userid bauserid,bt.userid btuserid FROM
	                {booking_answers} AS ba
	                    LEFT JOIN
	                {booking_options} AS bo ON bo.id = ba.optionid
	                    LEFT JOIN
	                {booking_teachers} AS bt ON ba.optionid = bt.optionid
	                	LEFT JOIN
	                {course_modules} cm ON bo.bookingid=cm.instance 
	            WHERE ba.userid = ".$user->id." AND cm.id = ".$gradingConfig->bookingid."
	            ORDER BY ba.timemodified DESC
	            LIMIT 1;";
		
				$bookings = $DB->get_record_sql($query);
				
				//var_dump($bookings);
				$mentor = "";
				
				if($bookings)
				$mentor = $DB->get_record('user', array('id'=>$bookings->btuserid));
	
	//var_dump($user);
				
				$record = new stdClass();
				$record->quizid = $quizid;
				$record->quizgradingid = $gradingConfig->cmid;
				$record->attempt_id = $object->attempt_id;
				$record->quizname = $object->quizname;
				$record->course = $object->course;
				$record->sumgrades = $skupajTockeQuiz;
				$record->userid = $object->userid;
				$record->username = $object->username;
				$record->firstname = $object->firstname;
				$record->lastname = $object->lastname;
				$record->email = $object->email;
				$record->institution = $object->institution;
				$record->dosezeno_tock = ($skupajTockeQuiz-$negativneTocke);
				$record->kazenske_tocke = $negativneTocke;
				$record->moznih_tock = $skupajTockeQuiz;
				$record->procent = intval($percentage);
				$record->vprasanja = $object->vprasanja;
				$record->status_kviza = ($naredil) ? 1 : 0;
				$record->datum_resitve = $object->datum_resitve;
				$record->datum_vpisa = strtotime("now");
				
				try
				{
					if($mentor)
					{
						$record->mentor = $mentor->firstname." ".$mentor->lastname." (".$mentor->username.")";
					}
	
					if($bookings)
					{
						$record->optionid = ($bookings && $bookings->optionid) ? $bookings->optionid : 0;
						$record->naziv_izvedbe = ($bookings) ? $bookings->text : "";
						$record->mentorid = $bookings->btuserid;
					}
				}
				catch(Exception $e) {}

				try
				{
					$record->datum_rojstva = $object->datum_rojstva;
				}catch(Exception $e) {}
				
				if($naredil && false)
				{
					$course = $DB->get_record('course', array('id' => $gradingConfig->course), '*', MUST_EXIST);
					$cm = get_coursemodule_from_instance('quizgrading', $gradingConfig->id, $course->id, false, MUST_EXIST);
				
					$completion=new completion_info($course);
	
					if($completion->is_enabled($cm)) {
					    //$completion->update_state($cm,COMPLETION_COMPLETE,$object->userid);
					}
				}
				
				try
				{
					$lastinsertid = $DB->insert_record('quizgrading_results', $record);
				}
				catch(Exception $e)
				{
					//var_dump($e);
				}
			}
		}

	}

//var_dump($skupnotock);

	

	return $naredil;
}

function is_quiz_success($quizid,$attemptid,$zapisi,$gradingid)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	require_login();
	
	global $CFG;
	global $DB;
	
	require_once($CFG->libdir . '/completionlib.php');
	
	if(!is_numeric($quizid) OR !is_numeric($attemptid))
	die("Napačni parametri!");
	
	$config = Array();

	$config['P'] = 70;
	$config_db['DK'] = "dosezene";
	$config_db['O'] = "DA";

	$quiz = $DB->get_record('quiz', array('id'=>$quizid));
	$attempt = $DB->get_record('quiz_attempts', array('uniqueid'=>$attemptid));
	
	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$gradingid;
	
	$gradingConfig = $DB->get_record_sql($query);
	//var_dump($gradingConfig);
	if($gradingConfig)
	{
		$config['P'] = $gradingConfig->procent;
		$config_db['DK'] = $gradingConfig->dosezene_kazenske;
		$config_db['O'] = $gradingConfig->izpis_opravil;
	}
	
	$tocke = Array();
	$skupnotock = Array();
	
	try
	{
		$category_config = $DB->get_records('quizgrading_category_config', array('quizid'=>$quizid));
		
		foreach($category_config as $conf)
		{
			$tocke[$conf->category] = $conf->tocke;
			
			if($conf->skupnotock != 'N')
				$skupnotock[$conf->category] = $conf->skupnotock;
			else $skupnotock[$conf->category] = PHP_INT_MAX;
		}
	}
	catch(Exception $e)	{}
	
	$query = "SELECT q_att.*,question.category,question.questiontext FROM {question_attempts} q_att, {question} question WHERE q_att.questionid=question.id AND q_att.questionusageid=".$attemptid;
	
	$slots = $DB->get_records_sql($query);
	

	$negativneTocke = 0;
	$skupajTockeQuiz = 0;
	
	$naredil = TRUE;
	
	$vprasanja = Array();
	
	$attemptInfo = Array();
	
	//var_dump($slots);echo "<br /><br />";
	//$DB->delete_records('quizgrading_attempt_info', array('quizid'=>$quizid,'attempt_id'=>$attemptid));
	//$DB->delete_records('quizgrading_attempt_info', array('quizid'=>$quizid));
	
	foreach($slots as $key=>$object)
	{
		$attemptInfoRecord = new stdClass(); 
		$attemptInfoRecord->quizid = $quizid;
		$attemptInfoRecord->userid = $attempt->userid;
		$attemptInfoRecord->quizgradingid = $gradingConfig->cmid;
		$attemptInfoRecord->attempt_id = $attemptid;
		$attemptInfoRecord->category = $object->category;
		$attemptInfoRecord->questionsummary = $object->questiontext;
		$attemptInfoRecord->rightanswer = $object->rightanswer;
		$attemptInfoRecord->responsesummary = $object->responsesummary;
		$attemptInfoRecord->timecreated = strtotime("now");
		$attemptInfoRecord->timemodified = 0;
		
		try
		{
			
			$query = "SELECT COUNT(*) count FROM {quizgrading_attempt_info} WHERE quizid=? AND attempt_id=? AND questionsummary=?";
			$result = $DB->get_record_sql($query,array($quizid,$attemptid,$object->questiontext));
			
			//var_dump($result);
			
			if(!$result->count)
			{
				$DB->insert_record('quizgrading_attempt_info', $attemptInfoRecord);
			}
		}
		catch(Exception $e)
		{
			//var_dump($e);
		}
		
		$cat = $DB->get_record('quizgrading_category_config',array('category'=>$object->category,'quizid'=>$quizid));
			
		if(!$cat)
		{
			$cat = $DB->get_record('question_categories',array('id'=>$object->category));
			$cat->izpisi_kaj = substr($cat->name, 0,stripos($cat->name,' '));
			$cat->izpisi = "DA";
		}

		
		if($object->rightanswer != $object->responsesummary)
		{
			
			$vprasanja[] = $object->category."|NE|".$cat->izpisi_kaj; 
			
			if(array_key_exists(intval($object->category),$skupnotock))
			{
				if($skupnotock[intval($object->category)] < 0)
				{
					
					$naredil = FALSE;
					//break;
				}
					
				$skupnotock[intval($object->category)] = $skupnotock[intval($object->category)] - $tocke[intval($object->category)];
				
				$negativneTocke+=$tocke[intval($object->category)];
				$skupajTockeQuiz+=$tocke[intval($object->category)];
				
				if($skupnotock[intval($object->category)] < 0)
				{
					$naredil = FALSE;
					//break;
				}
			}
			else {
				
				$cat = $DB->get_record('question_categories', array('id'=>$object->category));
				
				while(!array_key_exists(intval($cat->id),$skupnotock) AND $cat->parent > 0)
				{
					$cat = get_next_cat($cat->parent);
				}
				
				if(array_key_exists(intval($cat->id),$skupnotock))
				{
					if($skupnotock[intval($cat->id)] < 0)
					{
						$naredil = FALSE;
						//break;
					}
					
					$skupnotock[intval($cat->id)] = $skupnotock[intval($cat->id)] - $tocke[intval($cat->id)];
					$negativneTocke+=$tocke[intval($cat->id)];
					$skupajTockeQuiz+=$tocke[intval($cat->id)];
					
					if($skupnotock[intval($object->category)] < 0)
					{
						$naredil = FALSE;
						//break;
					}
				}
				else
				{
					$skupajTockeQuiz++;
					$negativneTocke++;
				}
			}
		}
		else
		{
			$vprasanja[] = $object->category."|OK|".$cat->izpisi_kaj;
			
			if($tocke[intval($object->category)])
			$skupajTockeQuiz+=(($tocke[intval($object->category)] > 0) ? $tocke[intval($object->category)] : 1);
			else $skupajTockeQuiz+=1;
		}
	}

	
	$percentage = 0;
	
	if($skupajTockeQuiz > 0)
	$percentage = (($skupajTockeQuiz-$negativneTocke)*100)/$skupajTockeQuiz;
	
	if(intval($percentage) < intval($config['P'])) 
	{
		//echo $attempt->userid."<br />";
		$naredil = FALSE;
	}




	if($zapisi)
	{
		require_once($CFG->dirroot.'/user/profile/lib.php');
		//$DB->delete_records('quizgrading_results', array('quizid'=>$quizid,'attempt_id'=>$attemptid));
		
		if(!$DB->record_exists('quizgrading_results', array('quizid'=>$quizid,'attempt_id'=>$attemptid)))
		{
			$user = $DB->get_record('user', array('id'=>$attempt->userid));
			
			profile_load_data($user);
			
			$query = "SELECT *,ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE ba.userid = ".$user->id." AND cm.id = ".$gradingConfig->bookingid."
            ORDER BY ba.timemodified DESC
            LIMIT 1;";
	
			$bookings = $DB->get_record_sql($query);

			$mentor = false;

			if($bookings)
			$mentor = $DB->get_record('user', array('id'=>$bookings->btuserid));

			
			$record = new stdClass();
			$record->quizid = $quizid;
			$record->quizgradingid = $gradingConfig->cmid;
			$record->attempt_id = $attemptid;
			$record->quizname = $quiz->name;
			$record->course = $quiz->course;
			$record->sumgrades = $skupajTockeQuiz;
			$record->userid = $attempt->userid;
			$record->username = $user->username;
			$record->firstname = $user->firstname;
			$record->lastname = $user->lastname;
			$record->email = $user->email;
			$record->institution = $user->institution;
			$record->dosezeno_tock = ($skupajTockeQuiz-$negativneTocke);
			$record->kazenske_tocke = $negativneTocke;
			$record->moznih_tock = $skupajTockeQuiz;
			$record->procent = intval($percentage);
			$record->vprasanja = implode(",", $vprasanja);
			$record->status_kviza = ($naredil) ? 1 : 0;
			$record->datum_resitve = $attempt->timefinish;
			$record->datum_vpisa = strtotime("now");
			try
			{
				if($mentor)
				{
					$record->mentor = $mentor->firstname." ".$mentor->lastname." (".$mentor->username.")";
					//$record->mentorid = $mentor->userid;
				}

				if($bookings)
				{
					$record->optionid = ($bookings && $bookings->optionid) ? $bookings->optionid : 0;
					$record->naziv_izvedbe = ($bookings) ? $bookings->text : "";
					$record->mentorid = $bookings->btuserid;
				}
			}
			catch(Exception $e) {}

			try
			{
				//$record->datum_rojstva = $user->profile_field_Datumrojstva;
				if(!is_null($user->profile_field_datumrojstva))
				$record->datum_rojstva = $user->profile_field_datumrojstva;
			}catch(Exception $e) {}
			

			try
			{
				$lastinsertid = $DB->insert_record('quizgrading_results', $record);
			}
			catch(Exception $e)
			{
				//var_dump($e);
			}
		}
	}

	return $naredil;
}

function refresh_quiz_attempts()
{
	if(!is_siteadmin())
	die("Nimate pravice za dostop!");
	
	global $DB;
	

}

function get_quizgrade_config($quizid)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;
	
	$config_db = $DB->get_record('quizgrading_config', array('quizid'=>$quizid));
	
	return $config_db->config;
}

function get_quiz_dates($quizid,$solsko_leto="",$optionid=null,$os=null)
{
	global $DB;
	//$query = "SELECT DISTINCT(DATE(FROM_UNIXTIME(`timefinish`))) timefinish FROM {quiz_attempts} att WHERE att.state='finished' AND quiz=".$quizid." ORDER BY timefinish DESC";
	
	//$quizes = $DB->get_records_sql($query);
	
	$solskoLetoQuery = "";
	if($solsko_leto != "" && $solsko_leto != "all")
	{
		$split = explode("/",$solsko_leto);
		$mejniSpodaj = mktime(0, 0, 0, 8  , 31, $split[0]);
		$mejniZgoraj = mktime(0, 0, 0, 8  , 31, $split[0]+1);
		
		$solskoLetoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
		
	}
	
	$optionQuery = "";
	if(!is_null($optionid) && $optionid != "")
	{
		$optionQuery = " AND optionid=".$optionid." ";
	}
	
	$osQuery = "";
	if(!is_null($os) AND $os != "")
	{
		$osQuery = " AND institution='".$os."' ";
	}
	
	$query = "SELECT datum_resitve,DATE(FROM_UNIXTIME(`datum_resitve`)) timefinish FROM {quizgrading_results} WHERE quizid=:quizid ".$optionQuery.$osQuery.$solskoLetoQuery." GROUP BY DATE(FROM_UNIXTIME(`datum_resitve`)) ORDER BY datum_resitve DESC";
	
	$bookings = $DB->get_records_sql($query, array('quizid'=>$quizid));
	
	
	return $bookings;
}

function get_quiz_dates_mentor($quizid,$mentorID,$cm,$solsko_leto,$optionid=null,$os=null)
{
	global $DB;
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	

	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
	$gradingConfig = $DB->get_record_sql($query);
	

	$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = ".$mentorID." AND cm.id = ".$gradingConfig->bookingid."
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

	//echo $queryMentor;
	$bookings = $DB->get_records_sql($queryMentor);
		
	//var_dump($bookings);
		
		
		
	foreach($bookings as $booking)
	{
		$userIdji[] = $booking->bauserid;
	}
	$userIdji[] = $mentorID;
	
	$solskoLetoQuery = "";
	if($solsko_leto != "")
	{
		$split = explode("/",$solsko_leto);
		$mejniSpodaj = mktime(0, 0, 0, 8  , 31, $split[0]);
		$mejniZgoraj = mktime(0, 0, 0, 8  , 31, $split[0]+1);
		
		$solskoLetoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
		
	}
	
	$optionQuery = "";
	if(!is_null($optionid) && $optionid != "")
	{
		$optionQuery = " AND optionid=".$optionid." ";
	}
	
	$osQuery = "";
	if(!is_null($os) AND $os != "")
	{
		$osQuery = " AND institution='".$os."' ";
	}
	
	$query = "SELECT datum_resitve,DATE(FROM_UNIXTIME(`datum_resitve`)) timefinish FROM {quizgrading_results} WHERE quizid=:quizid ".$optionQuery.$osQuery." AND mentorid in (".$mentorID.")".$solskoLetoQuery." GROUP BY DATE(FROM_UNIXTIME(`datum_resitve`)) ORDER BY datum_resitve DESC";
	
	$bookings = $DB->get_records_sql($query, array('quizid'=>$quizid));
	
	return $bookings;
}

function get_quiz_bookings($quizid,$solsko_leto)
{
	global $DB;
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	
	$solskoLetoQuery = "";
	if($solsko_leto != "" && $solsko_leto != "all")
	{
		$split = explode("/",$solsko_leto);
		$mejniSpodaj = mktime(0, 0, 0, 8  , 31, $split[0]);
		$mejniZgoraj = mktime(0, 0, 0, 8  , 31, $split[0]+1);
		
		$solskoLetoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
		
	}
	
	$query = "SELECT optionid,naziv_izvedbe FROM {quizgrading_results} WHERE quizid=:quizid ".$solskoLetoQuery." GROUP BY optionid";
	$bookings = $DB->get_records_sql($query, array('quizid'=>$quizid));
	
	return $bookings;
}

function get_quiz_bookings_mentor($quizid,$mentorID,$cm)
{
	global $DB;
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
	$gradingConfig = $DB->get_record_sql($query);
	
	$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = ".$mentorID." AND cm.id = ".$gradingConfig->bookingid."
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

	//echo $queryMentor;
	$bookings = $DB->get_records_sql($queryMentor);
		
	//var_dump($bookings);
		
		
		
	foreach($bookings as $booking)
	{
		$userIdji[] = $booking->bauserid;
	}
	$userIdji[] = $mentorID;
	
	$query = "SELECT optionid,naziv_izvedbe FROM {quizgrading_results} WHERE quizid=:quizid AND mentorid in (".$mentorID.") GROUP BY optionid";
	$bookings = $DB->get_records_sql($query, array('quizid'=>$quizid));
	
	return $bookings;
}

function get_quiz_last_booking_mentor($quizid,$mentorID,$cm)
{
	global $DB;
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
	$gradingConfig = $DB->get_record_sql($query);
	
	$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = ".$mentorID." AND cm.id = ".$gradingConfig->bookingid."
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

	//echo $queryMentor;
	$bookings = $DB->get_records_sql($queryMentor);
		
	//var_dump($bookings);
		
		
		
	foreach($bookings as $booking)
	{
		$userIdji[] = $booking->bauserid;
	}
	$userIdji[] = $mentorID;
	
	$query = "SELECT optionid,naziv_izvedbe,datum_resitve,DATE(FROM_UNIXTIME(`datum_resitve`)) timefinish FROM {quizgrading_results} WHERE quizid=:quizid AND mentorid in (".$mentorID.") ORDER BY datum_resitve DESC LIMIT 1";
	$bookings = $DB->get_record_sql($query, array('quizid'=>$quizid));
	
	return $bookings;
}

function get_quiz_institutions_mentor($quizid,$cm = 0,$mentorID = 0,$bookingid=null,$datum=null)
{
	global $DB;
	
	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
	$gradingConfig = $DB->get_record_sql($query);
	
	$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = ".$mentorID." AND cm.id = ".$gradingConfig->bookingid."
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

	$bookings = $DB->get_records_sql($queryMentor);
		

	foreach($bookings as $booking)
	{
		$userIdji[] = $booking->bauserid;
	}
	$userIdji[] = $mentorID;
	
	$bookingQuery = "";
	if(!is_null($bookingid) AND $bookingid != "")
	{
		$bookingQuery = " AND optionid=".$bookingid." ";
	}
	
	$datumQuery = "";
	if(!is_null($datum) AND $datum != "" AND $datum != "vsi")
	{
		$datumQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE('".$datum."') ";
	}

	$query = "SELECT DISTINCT(institution) institution FROM {quizgrading_results} att WHERE att.mentorid IN (".$mentorID.") ".$bookingQuery.$datumQuery." AND quizid=".$quizid." ORDER BY datum_resitve DESC";
	//$query = "SELECT DISTINCT(institution) institution FROM {quiz_attempts} att,{user} usr WHERE att.userid=usr.id AND att.userid IN (".implode(',',$userIdji).") AND att.state='finished' AND quiz=".$quizid." ORDER BY timefinish DESC";
	
	$quizes = $DB->get_records_sql($query);
	
	return $quizes;
}

function get_quiz_institutions($quizid,$bookingid=null,$datum=null,$solsko_leto)
{
	global $DB;

	$bookingQuery = "";
	if(!is_null($bookingid) AND $bookingid != "")
	{
		$bookingQuery = " AND optionid=".$bookingid." ";
	}
	
	$datumQuery = "";
	if(!is_null($datum) AND $datum != "" AND $datum != "vsi")
	{
		$datumQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE('".$datum."') ";
	}
	
	$solskoLetoQuery = "";
	if($solsko_leto != "" && $solsko_leto != "all")
	{
		$split = explode("/",$solsko_leto);
		$mejniSpodaj = mktime(0, 0, 0, 8  , 31, $split[0]);
		$mejniZgoraj = mktime(0, 0, 0, 8  , 31, $split[0]+1);
		
		$solskoLetoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
		
	}

	$query = "SELECT DISTINCT(institution) institution FROM {quizgrading_results} att WHERE quizid=".$quizid.$bookingQuery.$datumQuery.$solskoLetoQuery." ORDER BY datum_resitve DESC";
	//$query = "SELECT DISTINCT(institution) institution FROM {quiz_attempts} att,{user} usr WHERE att.userid=usr.id AND att.userid IN (".implode(',',$userIdji).") AND att.state='finished' AND quiz=".$quizid." ORDER BY timefinish DESC";
	 
	$quizes = $DB->get_records_sql($query);
	
	return $quizes;
}

function get_quizgrade_view($quizid,$zapisi,$student = false,$gradingid,$date,$order,$mentor = false,$page=0,$generirajStevilke=false,$preracun=false)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	
	global $DB;
	global $PAGE;
	global $USER;
	global $CFG;

	$datumQuery = "";
	$datumQuery2 = "";
	
	$osQuery = "";
	$bookingQuery = "";
	
	$letoQuery = "";
	$opravilQuery = "";
	
	//PREVERIMO ALI JE MENTOR IN ALI JE NASTAVLJEN DATUM IN IZVEDBA
	$preracunaj = false;
	
	//if($preracun AND ($mentor AND !is_null($date) && isset($date['datum']) && isset($date['booking']) && $date['datum'] > 0 AND $date['booking'] != ''))
	if(($mentor AND !is_null($date) && isset($date['datum']) && isset($date['booking']) && $date['datum'] > 0 AND $date['booking'] != ''))
	{
		$preracunaj=true;
	}
	
	
	if(!is_null($date) && isset($date['opravil']) && $date['opravil'] != "")
	{
		if($date['opravil'] == "1")
		{
			$opravilQuery = " AND status_kviza=1 ";
		}
		else
		{
			$opravilQuery = " AND status_kviza=0 AND (SELECT COUNT(*) FROM {quizgrading_results} qgr_cnt WHERE qgr_cnt.quizid=".$quizid." AND qgr_cnt.userid={quizgrading_results}.userid AND qgr_cnt.status_kviza=1) <= 0 ";
			//echo $opravilQuery;
		}
	}
	
	if(!is_null($date) && isset($date['datum']) && $date['datum'] > 0)
	{
		$datumStr = $date['datum'];
		
		$datumQuery = " DATE(FROM_UNIXTIME(`timefinish`)) = DATE('".$datumStr."') AND ";
		$datumQuery2 = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE('".$datumStr."') ";
	}
	
	if(!is_null($date) && isset($date['os'])  && $date['os'] != '')
	{
		$osQuery = " AND institution LIKE '%".($date['os'])."%' ";
	}
	
	if(!is_null($date) && isset($date['booking'])  && $date['booking'] != '')
	{
		$bookingQuery = " AND optionid=".($date['booking'])." ";
	}

	if(!is_null($date) && isset($date['leto'])  && $date['leto'] != ''   && $date['leto'] != 'all')
	{
		$split = explode("/",$date['leto']);
		$letoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
	}
	
	$orderQuery = " ORDER BY dosezeno_tock DESC ";
	
	if(isset($order['orderby']) && trim($order['orderby']) != "")
	{
		$orderQuery = " ORDER BY ".$order['orderby']." ".$order['order'];
	}
	

	$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$gradingid;
	
	$gradingConfig = $DB->get_record_sql($query);
	
	$userIdji = Array();
	
	if($mentor)
	{
		/*$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE bt.userid = ".$USER->id." AND cm.id = ".$gradingConfig->bookingid."
            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

		$bookings = $DB->get_records_sql($queryMentor);
		
		//var_dump($bookings);

		foreach($bookings as $booking)
		{
			$userIdji[] = $booking->bauserid;
		}
		$userIdji[] = $USER->id;*/
		
		/*
		$queryMentor = "SELECT userid bauserid FROM {quizgrading_results} WHERE mentorid=".$USER->id;

		$bookings = $DB->get_records_sql($queryMentor);
		
		$userIdji = Array();
		foreach($bookings as $booking)
		{
			$userIdji[] = $booking->bauserid;
		}
		$userIdji[] = $USER->id;
		
		*/
		$query = "SELECT * FROM {quiz_attempts} att WHERE att.state='finished' AND quiz=".$quizid;
	}
	else {
		if($student)
		$query = "SELECT * FROM {quiz_attempts} att WHERE att.state='finished' AND att.userid=".$USER->id." AND quiz=".$quizid;
		else
		$query = "SELECT * FROM {quiz_attempts} att WHERE ".$datumQuery." att.state='finished' AND quiz=".$quizid;
	}


	if($preracun OR $student)
	{
		$quizes = $DB->get_records_sql($query);
	}
	
	/*
	$query = "SELECT ba.userid bauserid,bt.userid btuserid FROM
                {booking_answers} AS ba
                    LEFT JOIN
                {booking_options} AS bo ON bo.id = ba.optionid
                    LEFT JOIN
                {booking_teachers} AS bt ON ba.optionid = bt.optionid
                	LEFT JOIN
                {course_modules} cm ON bo.bookingid=cm.instance 
            WHERE cm.id = ".$gradingConfig->bookingid."
            ORDER BY ba.timemodified DESC
            LIMIT 1;";
	
	$bookings = $DB->get_record_sql($query);*/
	
	
	$cfgObj[0] = $gradingConfig->procent;
	$cfgObj[1] = $gradingConfig->dosezene_kazenske;
	$cfgObj[2] = $gradingConfig->izpis_opravil;

	$tipIzpisa = "dosezene";
	$izpisiUspesnost = "DA";
	
	if(count($cfgObj) == 3)
	{
		$tipIzpisa = $cfgObj[1];
		$izpisiUspesnost = $cfgObj[2];
	}


	$table = new html_table();
	$table->head = array('Kviz','Uporabnik','Vprašanja','Uspešnost','');
	
	$tableHead = Array();

	if($preracun OR $student)
	{
		foreach($quizes as $key=>$object)
		{
			$quizid = $object->quiz;
			$attepmtid = $object->uniqueid;
			
			is_quiz_success($quizid,$attepmtid,$zapisi,$gradingid);
		}
	}
	$quizes = null;

	
	$limitQuery = " LIMIT 25 ";
	
	if($page > 0)
		$limitQuery = " LIMIT ".($page*25).",25";
	
	$countQuery = "";
	
	if($mentor)
	{
		if($tipIzpisa == "dosezene")
		{
			//$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid IN (".implode(',',$userIdji).")".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$orderQuery.$limitQuery;
			$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.")".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$opravilQuery.$orderQuery.$limitQuery;

			//$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid IN (".implode(',',$userIdji).")".$osQuery.$bookingQuery.$datumQuery2.$letoQuery;
			$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.")".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$opravilQuery;
			
		}
		else 
		{
			if($orderQuery == "") $orderQuery = " ORDER BY kazenske_tocke ASC";
			//$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid IN (".implode(',',$userIdji).") ".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$orderQuery.$limitQuery;
			$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") ".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$opravilQuery.$orderQuery.$limitQuery;
		
			//$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid IN (".implode(',',$userIdji).") ".$osQuery.$bookingQuery.$datumQuery2.$letoQuery;
			$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") ".$osQuery.$bookingQuery.$datumQuery2.$letoQuery.$opravilQuery;
					
		}
	}
	else
	{
		if($student)
		{
			if($tipIzpisa == "dosezene")
			{
				$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid=".$USER->id.$orderQuery.$limitQuery;
				$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid=".$USER->id;
			}
			else 
			{
				if($orderQuery == "") $orderQuery = " ORDER BY kazenske_tocke ASC";
				$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid=".$USER->id.$orderQuery.$limitQuery;
			
				$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid=".$USER->id;
				
			}
		}
		else {
			if($tipIzpisa == "dosezene")
			{
				$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$osQuery.$bookingQuery.$letoQuery.$datumQuery2.$orderQuery.$limitQuery;
				$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$osQuery.$bookingQuery.$letoQuery.$datumQuery2;
			
			}
			else 
			{
				if($orderQuery == "") $orderQuery = " ORDER BY kazenske_tocke ASC";
				$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid.$osQuery.$bookingQuery.$letoQuery.$datumQuery2.$orderQuery.$limitQuery;

				$countQuery = "SELECT COUNT(*) count FROM {quizgrading_results} WHERE quizid=".$quizid.$osQuery.$bookingQuery.$letoQuery.$datumQuery2;
			}
		}
	}
	
	//ZAČETEK IZRAČUNA UVRSTITVE POSAMEZNIKA
	if($mentor)
	{
		if($tipIzpisa == "dosezene")
		{
			//$queryVrstniRed = "SELECT id,tocke_poligon,tocke_voznja,status_kviza,(dosezeno_tock+tocke_poligon+tocke_voznja) tocke FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND userid IN (".implode(',',$userIdji).") ORDER BY (dosezeno_tock+tocke_poligon+tocke_voznja) DESC";
			$queryVrstniRed = "SELECT id,tocke_poligon,tocke_voznja,status_kviza,(dosezeno_tock+tocke_poligon+tocke_voznja) tocke FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") ORDER BY (dosezeno_tock+tocke_poligon+tocke_voznja) DESC";
		}
		else
		{
			$queryVrstniRed = "SELECT id,tocke_poligon,tocke_voznja,status_kviza,(kazenske_tocke+tocke_poligon+tocke_voznja) tocke FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") ORDER BY (kazenske_tocke+tocke_poligon+tocke_voznja) ASC";
		}
		
		if($preracunaj)
		{
	
			$vrstniRedResult = $DB->get_records_sql($queryVrstniRed);
		
			$uvrstitev = 1;
			$tocke_prev = 0;
			$delitev = false;
			$stevec_delitev = 0;
			
			
			
			foreach($vrstniRedResult as $key=>$object)
			{
		
				if($object->tocke_poligon > $gradingConfig->max_kaz_poligon OR $object->tocke_voznja > $gradingConfig->max_kaz_voznja OR $object->tocke_poligon < 0 OR $object->tocke_voznja < 0 OR $object->status_kviza==0)
				{
						$object->uvrstitev_posamezniki = 888;
						$tocke_prev = -1;
				}
				else
				{
					if($object->tocke != $tocke_prev)
					{
						if($delitev) { $uvrstitev+=$stevec_delitev; $stevec_delitev = 0; }
						
						$object->uvrstitev_posamezniki = $uvrstitev;
						$tocke_prev = $object->tocke;
						$uvrstitev++;
						$delitev = false;
					}
					else
					{
						$delitev = true;
						$stevec_delitev++;
						$object->uvrstitev_posamezniki = $uvrstitev-1;
						$tocke_prev = $object->tocke;
					}
				}
				
				
				$DB->update_record('quizgrading_results', $object);
				
	
			}
			$vrstniRedResult = null;
		}
		//KONEC IZRAČUNA UVRSTITVE POSAMEZNIKA
	
	}
	else {
		
		if($tipIzpisa == "dosezene")
		{
			$queryVrstniRed = "SELECT id,tocke_poligon,tocke_voznja,status_kviza,(dosezeno_tock+tocke_poligon+tocke_voznja) tocke FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." ORDER BY (dosezeno_tock+tocke_poligon+tocke_voznja) DESC";
		}
		else
		{
			$queryVrstniRed = "SELECT id,tocke_poligon,tocke_voznja,status_kviza,(kazenske_tocke+tocke_poligon+tocke_voznja) tocke FROM {quizgrading_results} WHERE quizid=".$quizid.$datumQuery2." ORDER BY (kazenske_tocke+tocke_poligon+tocke_voznja) ASC";
		}
		
	}
	
	
	
	if($mentor)
	{
		if($tipIzpisa == "dosezene")
		{
			$queryVrstniRed = "SELECT skupina,SUM(IF(dosezeno_tock > 0,dosezeno_tock,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke FROM {quizgrading_results} WHERE skupina IN (SELECT skupina FROM {quizgrading_results} WHERE skupina > 0 AND quizid=".$quizid." GROUP BY skupina)  AND quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") GROUP BY skupina ORDER BY (IF(dosezeno_tock > 0,dosezeno_tock,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) DESC";
		}
		else
		{
			$queryVrstniRed = "SELECT skupina,SUM(IF(kazenske_tocke > 0,kazenske_tocke,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke FROM {quizgrading_results} WHERE skupina IN (SELECT skupina FROM {quizgrading_results} WHERE skupina > 0 AND quizid=".$quizid." GROUP BY skupina)  AND quizid=".$quizid.$datumQuery2." AND mentorid IN (".$USER->id.") GROUP BY skupina ORDER BY SUM(IF(kazenske_tocke > 0,kazenske_tocke,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) ASC";
		}
	}
	else
	{
		if($tipIzpisa == "dosezene")
		{
			$queryVrstniRed = "SELECT skupina,SUM(IF(dosezeno_tock > 0,dosezeno_tock,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke FROM {quizgrading_results} WHERE skupina IN (SELECT skupina FROM {quizgrading_results} WHERE skupina > 0 AND quizid=".$quizid." GROUP BY skupina)  AND quizid=".$quizid.$datumQuery2." GROUP BY skupina ORDER BY (IF(dosezeno_tock > 0,dosezeno_tock,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) DESC";
		}
		else
		{
			$queryVrstniRed = "SELECT skupina,SUM(IF(kazenske_tocke > 0,kazenske_tocke,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke FROM {quizgrading_results} WHERE skupina IN (SELECT skupina FROM {quizgrading_results} WHERE skupina > 0 AND quizid=".$quizid." GROUP BY skupina)  AND quizid=".$quizid.$datumQuery2." GROUP BY skupina ORDER BY SUM(IF(kazenske_tocke > 0,kazenske_tocke,0)+IF(tocke_poligon > 0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) ASC";
		}
	}

	if($preracunaj)
	{
		$vrstniRedResult = $DB->get_records_sql($queryVrstniRed);
		
		$uvrstitev = 1;
		$tocke_prev = 0;
		$delitev = false;
		$stevec_delitev = 0;
		
		foreach($vrstniRedResult as $key=>$object)
		{
			if($object->tocke < 0)
			{
					$object->uvrstitev_skupina = 888;
					$tocke_prev = 0;
			}
			else {
		
	
				if($object->tocke != $tocke_prev)
				{
					if($delitev) { $uvrstitev+=$stevec_delitev; $stevec_delitev = 0; }
					
					$object->uvrstitev_skupina = $uvrstitev;
					$tocke_prev = $object->tocke;
					$uvrstitev++;
					$delitev = false;
				}
				else
				{
					$delitev = true;
					$stevec_delitev++;
					$object->uvrstitev_skupina = $uvrstitev-1;
					$tocke_prev = $object->tocke;
				}
			}
			

			$updateQry = "UPDATE {quizgrading_results} SET uvrstitev_skupina=".$object->uvrstitev_skupina." WHERE quizid=".$quizid." AND skupina=".$object->skupina;
			$DB->execute($updateQry);
			
		}
		$vrstniRedResult = null;
		
		$updateQry = "UPDATE {quizgrading_results} SET tocke_skupina=-888,uvrstitev_skupina=888 WHERE quizid=".$quizid." AND skupina <= 0";
		$DB->execute($updateQry);
	}

	
	$gradingResults = $DB->get_records_sql($query);

	$resultCountObj = $DB->get_record_sql($countQuery);
	

	if($resultCountObj)
	{
		$resultCount = $resultCountObj->count;
		$resultCountObj = null;
	}


	$table = new html_table();
	$table->head = array('Kviz','Uporabnik','Vprašanja','Uspešnost','');
	
	$tableHead = Array();
	
	$query = "SELECT * FROM {quizgrading_att_config} WHERE quizid=".$quizid." AND prikazi=1 ORDER BY pozicija ASC";
	$attrConf = $DB->get_records_sql($query);
	
	$st_dresa = 1;
	$st_dresa = ($page*25)+1;
	
	//var_dump($gradingResults);

	foreach($gradingResults as $key=>$object)
	{
		$tableHead = Array('<a class="order" id="optionid" href="">Naziv izvedbe</a>');
		$tableContent = Array($object->naziv_izvedbe);
		 
		$tableHead[] = '<a class="order" id="datum_resitve" href="">Datum izvedbe</a>';
		$tableContent[] = date("d.m.Y",$object->datum_resitve);
		
		$url = new moodle_url('/user/profile.php', array('id'=>$object->userid));
		$link = html_writer::link($url, $object->firstname);
		$tableHead[] = '<a class="order" id="firstname" href="">Ime</a>';
		$tableContent[] = $link;
		
		$url = new moodle_url('/user/profile.php', array('id'=>$object->userid));
		$link = html_writer::link($url, $object->lastname);
		$tableHead[] = '<a class="order" id="lastname" href="">Priimek</a>';
		$tableContent[] = $link;
		
		if(!$mentor && !$student)
		{
			$url = new moodle_url('/user/profile.php', array('id'=>$object->mentorid));
			$link = html_writer::link($url, $object->mentorid);
			$tableHead[] = '<a class="order" id="mentorid" href="">Mentor ID</a>';
			$tableContent[] = $link;
		}
		
		$user = $DB->get_record('user', array('id'=>$object->userid));
		$userArr = get_object_vars($user);

		foreach($attrConf as $conf)
		{
			$tableHead[] = '<a class="order" id="'.$conf->atribut.'" href="">'.$conf->atribut.'</a>';
			$tableContent[] = $userArr[$conf->atribut];
		}
		

		if($gradingConfig->tip_instance == "1")
		{
			/*ZAČETEK VPRAŠANJ*/
			$vprasanja = explode(",",$object->vprasanja);
			
			foreach($vprasanja as $vprasanje)
			{
				$split = explode("|",$vprasanje);
				
				$cat = $DB->get_record('quizgrading_category_config',array('category'=>$split[0],'quizid'=>$quizid));
	
				if(!$cat)
				{
					$cat = $DB->get_record('question_categories',array('id'=>$split[0]));
					$cat->izpisi_kaj = substr($cat->name, 0,stripos($cat->name,' '));
					$cat->izpisi = "DA";
				}
				
				if($tipIzpisa == "dosezene" && $cat->izpisi == "DA")
				{
					$tableHead[] = "";
					$tableContent[] = "<div style='background-color:".(($split[1] == "OK") ? "#cfc" : "#fcc").";'>".$cat->izpisi_kaj."</div>";
				}
			}
		}
		
		if($izpisiUspesnost == "DA")
		{
			$tableHead[] = "<a class='order' id='status_kviza' href=''>Opravil</a>";
			$tableContent[] = (($object->status_kviza) ? "<div style='width:20px;background-color:#cfc;'>da</div>" : "<div style='width:20px;background-color:#fcc;'>ne</div>");
		}
		
		
		if($zapisi)
		{
			if($tipIzpisa == "dosezene")
			{
				$tableHead[] = "<a class='order' style='text-align:center;' id='dosezeno_tock' href=''>Točke <br />(dosežene)</a>";
				$tableContent[] = $object->dosezeno_tock;
			}
			else 
			{
				$tableHead[] = "<a class='order' style='text-align:center;' id='kazenske_tocke' href=''>Točke <br />(kazenske)</a>";
				$tableContent[] = $object->kazenske_tocke;
			}
		}
		
		if($gradingConfig->tip_instance != "1")
		{
		
			$startnaStCell = new html_table_cell();
			$startnaStCell->style ='text-align:center;';
			
			if($student && !$mentor)
			{
				$startnaStCell->text = "<a class='order' id='startna_st' href=''>Štartna št.</a>";
			}
			else {
				$startnaStCell->text = "<a class='order' id='startna_st' href=''>Štartna št. <button type='button'>Generiraj</button></a>";
			}
			
			
			$tableHead[] = $startnaStCell;
			
	
			if($object->startna_st > 0)
			{
				$tableContent[] = "<a href='' class='clickable' id='".$object->id."-startna_st'>".$object->startna_st."</a>";
			}
			else
			{
				if($generirajStevilke)
				{
					$tableContent[] = "<a href='' class='clickable' id='".$object->id."-startna_st'>".$st_dresa."</a>";
		
					$record = new stdClass();
					$record->id = $object->id;
					$record->startna_st = $st_dresa;
					
					$DB->update_record('quizgrading_results', $record);
				}
				else
				{
					$tableContent[] = "<a href='' class='clickable' id='".$object->id."-startna_st'>".$object->startna_st."</a>";
				}
				$st_dresa++;
			}
		
		
			$tableHead[] = "<a class='order' id='tocke_poligon' href=''>Poligon</a>";
			$tableContent[] = "<a href='' style='".(($object->tocke_poligon >= 0 && $object->tocke_poligon <= $gradingConfig->max_kaz_poligon) ? "background-color:#cfc;" : "background-color:#fcc;")."' class='clickable' id='".$object->id."-tocke_poligon'>".$object->tocke_poligon."</a>";
			
			$tableHead[] = "<a class='order' id='tocke_voznja' href=''>Vožnja</a>";
			$tableContent[] = "<a href='' style='".(($object->tocke_voznja >= 0 && $object->tocke_voznja <= $gradingConfig->max_kaz_voznja) ? "background-color:#cfc;" : "background-color:#fcc;")."' class='clickable' id='".$object->id."-tocke_voznja'>".$object->tocke_voznja."</a>";
			
			$tableHead[] = "<a class='order' id='tocke_skupaj' href=''>Skupaj</a>";
			
			$skupaj_tock_izpit = 0;
			
			if($gradingConfig->tip_instance == "1")
			{
				if($tipIzpisa == "dosezene")
				{
					$skupaj_tock_izpit = $object->dosezeno_tock+$object->tocke_voznja+$object->tocke_poligon;
					$tableContent[] = ($skupaj_tock_izpit > 0) ? $skupaj_tock_izpit : 0;
				}
				else 
				{
					$skupaj_tock_izpit = $object->kazenske_tocke+$object->tocke_voznja+$object->tocke_poligon;
					$tableContent[] = ($skupaj_tock_izpit > 0) ? $skupaj_tock_izpit : 0;
				}
			}
			else
			{
				$tocke_poligon = 0;
				$tocke_voznja = 0;
				if($object->tocke_poligon > 0) $tocke_poligon = $object->tocke_poligon;
				if($object->tocke_voznja > 0) $tocke_voznja = $object->tocke_voznja;
				
				$izpitOpravil = 0;
				
				if($object->status_kviza && $object->tocke_poligon >= 0 && $object->tocke_poligon <= $gradingConfig->max_kaz_poligon && $object->tocke_voznja >= 0 && $object->tocke_voznja <= $gradingConfig->max_kaz_voznja) $izpitOpravil = 1;
				
				
				
				if($tipIzpisa == "dosezene")
				{
					//$tableContent[] = (($izpitOpravil) ? "<div style='width:20px;background-color:#cfc;'>".($object->dosezeno_tock+$tocke_voznja+$tocke_poligon)."</div>" : "<div style='width:20px;background-color:#fcc;'>".($object->dosezeno_tock+$tocke_voznja+$tocke_poligon)."</div>");
					
					$tableContent[] = (($izpitOpravil) ? "<div style='width:20px;background-color:#cfc;'>".($object->tocke_skupaj)."</div>" : "<div style='width:20px;background-color:#fcc;'>".($object->tocke_skupaj)."</div>");
					
					$skupaj_tock_izpit = $object->dosezeno_tock+$tocke_voznja+$tocke_poligon;
				}
				else 
				{
					//$tableContent[] = (($izpitOpravil) ? "<div style='width:20px;background-color:#cfc;'>".($object->kazenske_tocke+$tocke_voznja+$tocke_poligon)."</div>" : "<div style='width:20px;background-color:#fcc;'>".($object->kazenske_tocke+$tocke_voznja+$tocke_poligon)."</div>");
					$tableContent[] = (($izpitOpravil) ? "<div style='width:20px;background-color:#cfc;'>".($object->tocke_skupaj)."</div>" : "<div style='width:20px;background-color:#fcc;'>".($object->tocke_skupaj)."</div>");
					$skupaj_tock_izpit = $object->kazenske_tocke+$tocke_voznja+$tocke_poligon;
				}
	
			}
	
			if($skupaj_tock_izpit > 0)
			{
				$object->tocke_skupaj = $skupaj_tock_izpit;
				$DB->update_record('quizgrading_results', $object);
			}
		}

		if($preracunaj)
		{
			require_once($CFG->libdir . '/completionlib.php');
			//nastavi completion na imcomplete
			$course = $DB->get_record('course', array('id' => $gradingConfig->course), '*', MUST_EXIST);
			$cm = get_coursemodule_from_instance('quizgrading', $gradingConfig->id, $course->id, false, MUST_EXIST);
					
			$completion=new completion_info($course);

			if($completion->is_enabled($cm)) {

				$completion->update_state($cm,COMPLETION_UNKNOWN ,$object->userid);
			}
		}
		

		if($gradingConfig->tip_instance == "3" )
		{
			$tableHead[] = "<a class='order' id='uvrstitev_posamezniki' href=''>Vrstni red posamezniki</a>";
			$tableContent[] = $object->uvrstitev_posamezniki;
		}
		
		if($gradingConfig->tip_instance == "4" )
		{
			$tableHead[] = "<a class='order' id='skupina' href=''>Skupina</a>";
			$tableContent[] = "<a href='' class='clickable' id='".$object->id."-skupina'>".$object->skupina."</a>";
			
			
			$tableHead[] = "<a class='order' id='tocke_skupina' href=''>Skupaj skupina</a>";
			
			if($object->skupina > 0)
			{
				if($mentor)
				{
					if($tipIzpisa == "dosezene"){
						//mentorid IN (".$USER->id.")
						//$queryTockeSkupina = "SELECT SUM(dosezeno_tock+IF(tocke_poligon>0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke_skupina FROM {quizgrading_results} WHERE skupina=".$object->skupina." AND userid IN (".implode(',',$userIdji).") AND quizid=".$object->quizid.$datumQuery2;
						$queryTockeSkupina = "SELECT SUM(dosezeno_tock+IF(tocke_poligon>0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke_skupina FROM {quizgrading_results} WHERE skupina=".$object->skupina." AND mentorid IN (".$USER->id.") AND quizid=".$object->quizid.$datumQuery2;
						
					}
					else {
						$queryTockeSkupina = "SELECT SUM(kazenske_tocke+IF(tocke_poligon>0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke_skupina FROM {quizgrading_results} WHERE skupina=".$object->skupina." AND mentorid IN (".$USER->id.") AND quizid=".$object->quizid.$datumQuery2;
					}
				}
				else {
					
				
					if($tipIzpisa == "dosezene"){
						$queryTockeSkupina = "SELECT SUM(dosezeno_tock+IF(tocke_poligon>0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke_skupina FROM {quizgrading_results} WHERE skupina=".$object->skupina." AND quizid=".$object->quizid;
					}
					else {
						$queryTockeSkupina = "SELECT SUM(kazenske_tocke+IF(tocke_poligon>0,tocke_poligon,0)+IF(tocke_voznja > 0,tocke_voznja,0)) tocke_skupina FROM {quizgrading_results} WHERE skupina=".$object->skupina." AND quizid=".$object->quizid;
						
					}
				}
				
				
				$tockeSkupina = $DB->get_record_sql($queryTockeSkupina);
				$tableContent[] = $tockeSkupina->tocke_skupina;
				$object->tocke_skupina = $tockeSkupina->tocke_skupina;
				
				//$tockeSkupina = null;
				
				if($preracunaj && $tockeSkupina->tocke_skupina > 0)
				{
					$DB->update_record('quizgrading_results', $object);
				}
				$tockeSkupina = null;
				
			}
			else {
				$tableContent[] = 0;
			}
			
			$tableHead[] = "<a class='order' id='uvrstitev_skupina' href=''>Vrstni red skupina</a>";
			$tableContent[] = $object->uvrstitev_skupina;
		
		}
		
		$tableContent[] = "<a target='_blank' href='".$CFG->wwwroot."/mod/quizgrading/pregled.php?quizid=".$quizid."&attempt_id=".$object->attempt_id."&gradingid=".$gradingid."'>pregled</a>";
		
		if(!$student OR $mentor)
		{
			$tableContent[] = "<a target='_blank' href='".$CFG->wwwroot."/mod/quizgrading/edit.php?quizid=".$quizid."&attempt_id=".$object->attempt_id."'>uredi</a>";
		}
		
		
		
		$table->data[] = $tableContent;
	}
	$gradingResults = null;

	$row1 = new html_table_row();
	$cell1 = new html_table_cell(); 
	
	$cell1->colspan = count($tableContent);
	
	$pagesString = "";
	
	for($i=1;$i<=ceil($resultCount/25);$i++)
	$pagesString.="<a class='page' href='#'>".$i."</a> ";
	
	$cell1->text = $pagesString; 
	
	$row1->cells[] = $cell1; 
	
	$table->data[] = $row1;
	
	if($tableHead)
	$table->head = $tableHead;
	

	return html_writer::table($table);
}

function save_quizgrade_config($quizid,$config)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	if(!is_numeric($quizid))
	die("Napaka parametra kviza");
	
	global $DB;
	
	if($DB->record_exists('quizgrading_config', array('quizid'=>$quizid)))
	{
		$cfgObj = $DB->get_record('quizgrading_config', array('quizid'=>$quizid));
		
		$cfgObj->config = $config;
		
		$DB->update_record('quizgrading_config', $cfgObj);
	}
	else
	{
		$object = new stdClass();
		$object->quizid = $quizid;
		$object->config = $config;
		$object->timecreated = 0;
		$object->timemodified = 0;
		
		$DB->insert_record('quizgrading_config', $object);
	}
	
}

function get_next_cat($cat_id)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;

	$cat = $DB->get_record('question_categories', array('id'=>$cat_id));
	
	return $cat;
}

function check_child_cat($cat_id)
{
	global $DB;

	$cats = $DB->get_records('question_categories', array('parent'=>$cat_id));
	
	return $cats;
}

function get_child_cat($cat_id,&$catsTree,&$catsReturn,$quizid)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;

	$cats = $DB->get_records('question_categories', array('parent'=>$cat_id));
	
	foreach($cats as $cat)
	{
		if(count(check_child_cat($cat->id)) > 0)
			get_child_cat($cat->id,$catsTree,$catsReturn,$quizid);
		
		$conf = $DB->get_record('quizgrading_category_config', array('category'=>$cat->id,'quizid'=>$quizid));
		//var_dump($cat->id);
		if($conf)
		{
			$cat->tocke = $conf->tocke;
			$cat->skupnotock = $conf->skupnotock;
			$cat->izpisi = $conf->izpisi;
			$cat->izpisi_kaj = $conf->izpisi_kaj;
		}
		else {
			$cat->tocke = 0;
			$cat->skupnotock = 0;
			$cat->izpisi_kaj = false;
			$cat->izpisi = "DA";
		}
		
		$catsTree[$cat->parent][$cat->id] = $cat;
		$catsReturn[$cat->parent] = $cat;
	}
	
	return $cats;
}

function insert_update_cat_config($data)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;
	
	
	if($DB->record_exists('quizgrading_category_config', array('category'=>$data['category'],'quizid'=>$data['quizid'])))
	{
		$query = "UPDATE {quizgrading_category_config} SET 
		tocke='".$data['tocke']."',
		skupnotock='".$data['skupnotock']."',
		izpisi='".$data['izpisi']."',
		izpisi_kaj='".$data['izpisikaj']."'
		WHERE quizid=".$data['quizid']." AND category=".$data['category'];
		
		try
		{
		$DB->execute($query);
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
	else
	{
		$record1 = new stdClass();
		$record1->quizid         = $data['quizid'];
		$record1->category = $data['category'];
		$record1->tocke = $data['tocke'];
		$record1->skupnotock = $data['skupnotock'];
		$record1->izpisi = $data['izpisi'];
		$record1->izpisi_kaj = $data['izpisikaj'];
		$record1->timecreated = 0;
		$record1->timemodified = 0;

		$lastinsertid = $DB->insert_record('quizgrading_category_config', $record1);	
	}
	
	return 1;
}

function insert_update_attribute($data)
{
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;
	
	
	if($DB->record_exists('quizgrading_att_config', array('atribut'=>$data['atribut'],'quizid'=>$data['quizid'])))
	{
		$query = "UPDATE {quizgrading_att_config} SET 
		pozicija=".$data['pozicija'].",
		prikazi=".$data['prikazi']."
		WHERE quizid=".$data['quizid']." AND atribut='".$data['atribut']."'";
		
		try
		{
			$DB->execute($query);
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
	else
	{
		$record1 = new stdClass();
		$record1->quizid         = $data['quizid'];
		$record1->atribut = $data['atribut'];
		$record1->prikazi = $data['prikazi'];
		$record1->pozicija = $data['pozicija'];

		$lastinsertid = $DB->insert_record('quizgrading_att_config', $record1);	
	}
	
	return 1;
}

function get_quiz_categories($quizid)
{
	//error_reporting(E_ALL);
//ini_set('display_errors', 1);
	//if(!is_siteadmin())
	//die("Nimate pravice za dostop!");
	
	global $DB;
	
	$query = "SELECT cats.* FROM {quiz_slots} slots,{question} question,{question_categories} cats WHERE slots.questionid=question.id AND question.category=cats.id AND slots.quizid=".$quizid." GROUP BY slots.quizid,question.category ORDER BY cats.name ASC";
	$cats = $DB->get_records_sql($query);
	
	//var_dump($cats);

	$catsReturn = Array();
	$catsReturn[0] = Array();
	
	$catsTree = Array();
	
	foreach($cats as $key=>$value)
	{
		//echo $value->parent." parentzgoraj<br />";
		$catsReturn[$value->parent] = $value;
		
		$cat = $value;
		
		$catZaChilde = $value;
		
		$conf = $DB->get_record('quizgrading_category_config', array('category'=>$cat->id,'quizid'=>$quizid));
		if($conf)
		{
			$cat->tocke = $conf->tocke;
			$cat->skupnotock = $conf->skupnotock;
			$cat->izpisi = $conf->izpisi;
			$cat->izpisi_kaj = $conf->izpisi_kaj;
		}
		else {
			$cat->tocke = 0;
			$cat->skupnotock = 'N';
			$cat->izpisi_kaj = false;
			$cat->izpisi = "DA";
		}
		
		$catChild = get_child_cat($catZaChilde->id,$catsTree,$catsReturn,$quizid);
		
		if($cat->parent > 0)
		{
			while($cat->parent > 0)
			{
				//echo $value->parent." ".$cat->parent."<br />";
				$catsTree[$cat->parent][$cat->id] = $cat;
				$catsReturn[$cat->parent] = $cat;
				$cat = get_next_cat($cat->parent);
				
				
				
				$conf = $DB->get_record('quizgrading_category_config', array('category'=>$cat->id,'quizid'=>$quizid));
				if($conf)
				{
					$cat->tocke = $conf->tocke;
					$cat->skupnotock = $conf->skupnotock;
					$cat->izpisi = $conf->izpisi;
					$cat->izpisi_kaj = $conf->izpisi_kaj;
				}
				else {
					$cat->tocke = 0;
					$cat->skupnotock = 'N';
					$cat->izpisi_kaj = false;
					$cat->izpisi = "DA";
				}
			}
			
			$catsTree[$cat->parent][$cat->id] = $cat;
			
			$catsReturn[$cat->parent][$cat->id] = $cat;
			
		}

		//echo "<br /><br />";
	}
	
	
	global $PAGE;
	$table = new html_table();
	$table->head = array('Naziv kategorije','Število točk','Skupno število točk','Izpis','','Izpis kaj');

	$output = generateTree($catsTree,$catsTree[0],0,$table);
	
	$output = html_writer::table($table);
	 
	return $output;
}

function generateTree($object,$data,$pos=0,&$table)
{
	$html = "";
	if(count($data) > 0)
	{
		foreach($data as $cat)
		{
			if(!$cat->izpisi) $cat->izpisi = "DA";
			
			if($pos == 0)
			{
				$html.="<div style='clear:both;'>&nbsp;</div>";
				$table->data[] = array('','','','','','','');
			}
			$name = "<div style='clear:both;margin-left:".$pos."px;'>".$cat->name."</div>";
			
			$izpisSelect = "<select id='izpis_".$cat->id."'><option value='DA'>DA</option><option ".(($cat->izpisi == "NE") ? "selected" : "")." value='NE'>NE</option></select>";
			
			$imeKategorije = substr($cat->name, 0,stripos($cat->name,' '));
			
			$table->data[] = array($name,"<input type='text' id='tocke_".$cat->id."' value='".$cat->tocke."' />","<input type='text' id='skupnotock_".$cat->id."' value='".$cat->skupnotock."' />",$izpisSelect,"<button class='saveBtn' id='shrani_".$cat->id."'>SHRANI</button>","<input type='text' id='izpisikaj_".$cat->id."' value='".(($cat->izpisi_kaj) ? $cat->izpisi_kaj : $imeKategorije)."' />");

			if(isset($object[$cat->id]))
			$html.=generateTree($object,$object[$cat->id],($pos+25),$table);
		}
	}

	return $html;
}

function save_editable($quizgradingid,$name,$value,$datum)
{
	global $DB;
	
	if(!is_numeric($quizgradingid)) return false;

	$result = $DB->get_record('quizgrading_results',array('id'=>$quizgradingid));	
	/*
	if(!is_null($date) && isset($date['datum']) && $date['datum'] > 0)
	{
		$datumStr = $date['datum'];
		
		$datumQuery = " DATE(FROM_UNIXTIME(`timefinish`)) = DATE('".$datumStr."') AND ";
		$datumQuery2 = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE('".$datumStr."') ";
	}*/
	
	//echo $result->datum_resitve."<br />";
	//echo "SELECT * FROM {quizgrading_results} WHERE quizid=".$result->quizid." AND startna_st='".$value."' AND id != ".$quizgradingid." AND datum_resitve = ".$result->datum_resitve;
	
	$record = new stdClass();
	$record->id         = $quizgradingid;
	$record->$name = $value;
	
	if($name == 'startna_st')
	{
		if($value > 0 AND $DB->record_exists_sql("SELECT * FROM {quizgrading_results} WHERE quizid=".$result->quizid." AND startna_st='".$value."' AND id != ".$quizgradingid." AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE(FROM_UNIXTIME(".$result->datum_resitve.")) "))
		{
			return false;
		}
	}
	
	$DB->update_record('quizgrading_results', $record);
	
	return $record;
}

function generiraj_startne_st($quizid,$mentorID=null,$cm=null,$optionid=null,$datum=null)
{
	global $DB;
	global $USER;
	
	if(is_null($mentorID) OR is_null($optionid) OR is_null($datum))
	{
		return;
	}
	
	$userIdji = Array();
	if(!is_null($mentorID))
	{
		$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
		$gradingConfig = $DB->get_record_sql($query);
		
		$userIdji = Array();
		
	
		$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
	                {booking_answers} AS ba
	                    LEFT JOIN
	                {booking_options} AS bo ON bo.id = ba.optionid
	                    LEFT JOIN
	                {booking_teachers} AS bt ON ba.optionid = bt.optionid
	                	LEFT JOIN
	                {course_modules} cm ON bo.bookingid=cm.instance 
	            WHERE bt.userid = ".$USER->id." AND cm.id = ".$gradingConfig->bookingid."
	            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

		$bookings = $DB->get_records_sql($queryMentor);

		
		foreach($bookings as $booking)
		{
			$userIdji[] = $booking->bauserid;
		}
		$userIdji[] = $USER->id;
		
		//var_dump($userIdji);
	}
	
	if(count($userIdji) > 0)
	{

		$query = "SELECT * FROM {quizgrading_results} WHERE mentorid IN (".$mentorID.") AND quizid=".$quizid." AND DATE(FROM_UNIXTIME(`datum_resitve`)) = DATE('".$datum."') "." AND optionid=".$optionid." ";
	}
	else
	{
		$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid;
	}
	
	$reseni = $DB->get_records_sql($query);
	
	
	
	$stevilke = Array();
	
	foreach($reseni as $key=>$object)
	{
		if($object->startna_st > 0)
		{
			$stevilke[] = $object->startna_st;
		}
	}
	
	foreach($reseni as $key=>$object)
	{
		if($object->startna_st <= 0)
		{
			$object->startna_st = mt_rand(1, count($reseni));
			while(in_array($object->startna_st, $stevilke))
			{
				$object->startna_st = mt_rand(1, count($reseni));
			}
			$stevilke[] = $object->startna_st;
			$DB->update_record('quizgrading_results', $object);
			//var_dump($object);
		}
	}
	
	//return $quizid;
}

function reset_attempt($data)
{
	//var_dump($data);
}

function get_solska_leta($quizid,$mentorID=null,$cm=null)
{
	global $DB;
	global $USER;
	
	$userIdji = Array();
	$solskaLeta = Array();
	if(!is_null($mentorID))
	{
		$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
		$gradingConfig = $DB->get_record_sql($query);
		
		$userIdji = Array();
		
	
		$queryMentor = "SELECT ba.userid bauserid,bt.userid btuserid FROM
	                {booking_answers} AS ba
	                    LEFT JOIN
	                {booking_options} AS bo ON bo.id = ba.optionid
	                    LEFT JOIN
	                {booking_teachers} AS bt ON ba.optionid = bt.optionid
	                	LEFT JOIN
	                {course_modules} cm ON bo.bookingid=cm.instance 
	            WHERE bt.userid = ".$USER->id." AND cm.id = ".$gradingConfig->bookingid."
	            GROUP BY ba.userid ORDER BY ba.timemodified DESC;";

		$bookings = $DB->get_records_sql($queryMentor);

		
		foreach($bookings as $booking)
		{
			$userIdji[] = $booking->bauserid;
		}
		$userIdji[] = $USER->id;
		
		
		if(count($userIdji) > 0)
		{
			
		}
		else
		{
			$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid."  GROUP BY datum_resitve";
		}
		
		$query = "SELECT * FROM {quizgrading_results} WHERE mentorid IN (".$mentorID.") AND quizid=".$quizid." GROUP BY datum_resitve";
		
		$reseni = $DB->get_records_sql($query);
		
		$output = "";
		
		foreach($reseni as $resen)
		{
			$output.=date("d.m.Y",$resen->datum_resitve)." ";
			
			$mejniDatum = mktime(0, 0, 0, 8 , 31, date("Y",$resen->datum_resitve));
			
			//var_dump((strtotime(date("d.m.Y",$resen->datum_resitve)) > strtotime(date("d.m.Y",$mejniDatum))));
			
			if(strtotime(date("d.m.Y",$resen->datum_resitve)) > strtotime(date("d.m.Y",$mejniDatum)))
			{
				if(!in_array((date("Y",$resen->datum_resitve)."/".(date("y",$resen->datum_resitve)+1)), $solskaLeta))
					$solskaLeta[] = date("Y",$resen->datum_resitve)."/".(date("y",$resen->datum_resitve)+1);
			}
			else
			{
				if(!in_array(((date("Y",$resen->datum_resitve)-1)."/".date("y",$resen->datum_resitve)), $solskaLeta))
					$solskaLeta[] = (date("Y",$resen->datum_resitve)-1)."/".date("y",$resen->datum_resitve);
			}	
			
		}
		
		
		
		return $solskaLeta;
	}
	else {
		$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm;
	
		$gradingConfig = $DB->get_record_sql($query);
		
		$query = "SELECT * FROM {quizgrading_results} WHERE quizid=".$quizid."  GROUP BY datum_resitve";
		
		$reseni = $DB->get_records_sql($query);
		
		$output = "";
		
		foreach($reseni as $resen)
		{
			$output.=date("d.m.Y",$resen->datum_resitve)." ";
			
			$mejniDatum = mktime(0, 0, 0, 8 , 31, date("Y",$resen->datum_resitve));
			
			if(strtotime(date("d.m.Y",$resen->datum_resitve)) > strtotime(date("d.m.Y",$mejniDatum)))
			{
				if(!in_array((date("Y",$resen->datum_resitve)."/".(date("y",$resen->datum_resitve)+1)), $solskaLeta))
					$solskaLeta[] = date("Y",$resen->datum_resitve)."/".(date("y",$resen->datum_resitve)+1);
			}
			else
			{
				if(!in_array(((date("Y",$resen->datum_resitve)-1)."/".date("y",$resen->datum_resitve)), $solskaLeta))
					$solskaLeta[] = (date("Y",$resen->datum_resitve)-1)."/".date("y",$resen->datum_resitve);
			}	
			
		}

		return $solskaLeta;
	}

}
