<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

if(!is_numeric($_GET['quizid']) OR !is_numeric($_GET['attempt_id']))
	die("Napačni parametri!");

global $DB;

if(isset($_POST['firstname']) AND is_numeric($_POST['id']))
{
	$record = new stdClass();
	$record->id = $_POST['id'];
	$record->firstname = $_POST['firstname'];
	$record->lastname = $_POST['lastname'];
	$record->email = $_POST['email'];
	$record->institution = $_POST['institution'];
	$record->organizator = $_POST['organizator'];
	$record->lokacija = $_POST['lokacija'];
	//$record->quizname = $_POST['quizname'];
	
	try
	{
		$record->datum_rojstva = strtotime($_POST['datum_rojstva']);
	}catch(Exception $e) {}
	
	$DB->update_record('quizgrading_results', $record);
}

echo $OUTPUT->header();
echo $OUTPUT->box_start();


$quizResult = $DB->get_record('quizgrading_results', array('quizid'=>$_GET['quizid'],'attempt_id'=>$_GET['attempt_id']));


if($quizResult)
{
	$vprasanja = $quizResult->vprasanja;
	$vprasanja = explode(";", $vprasanja);
	
	$config_db = $DB->get_record('quizgrading_config', array('quizid'=>$_GET['quizid']));
	
	if(isset($config_db->config))
	{
		$config_db = explode(";", $config_db->config);
	}
	
	
 
	$tipIzpisa = "dosezene";
	$izpisiUspesnost = "DA";
	 
	if(count($config_db) == 3)
	{
		$tipIzpisa = $config_db[1];
		$izpisiUspesnost = $config_db[2];
	}
	
	$query = "SELECT q_att.*,question.category,question.questiontext FROM {question_attempts} q_att, {question} question WHERE q_att.questionid=question.id AND q_att.questionusageid=".$quizResult->attempt_id;

	$slots = $DB->get_records('quizgrading_attempt_info',array('quizid'=>$_GET['quizid'],'attempt_id'=>$_GET['attempt_id']));
	
	//var_dump($_SERVER['HTTP_REFERER']);
	
	echo $OUTPUT->heading($quizResult->quizname." - pregled");
	//echo "<div style='float:right;'>Nazaj</div>";
	echo "<form method='post' action=''>";
	echo "<input type='hidden' value='".$quizResult->id."' name='id' />";
	echo "<div style='margin-top:20px;'><b>Ime in priimek</b>: <input type='text' value='".$quizResult->firstname."' name='firstname' /> <input type='text' name='lastname' value='".$quizResult->lastname."' /> </div>";
	echo "<div><b>Institucija</b>: <input type='text' name='institution' value='".$quizResult->institution."' /> </div>";	
	echo "<div><b>E-pošta</b>: <input type='text' name='email' value='".$quizResult->email."' /> </div>";
	echo "<div><b>Datum rojstva</b>: <input type='text' name='datum_rojstva' value='".date('d.m.Y',$quizResult->datum_rojstva)."' /> </div>";	
	
	echo "<div><b>Organizator</b>: <input type='text' name='organizator' value='".$quizResult->organizator."' /> </div>";	
	echo "<div><b>Lokacija</b>: <input type='text' name='lokacija' value='".$quizResult->lokacija."' /> </div>";	
	
	echo "<div><b>Možnih točk</b>: ".$quizResult->moznih_tock." </div>";
	
	if($tipIzpisa == "dosezene")
	echo "<div><b>Doseženih točk</b>: ".$quizResult->dosezeno_tock." </div>";
	else
	echo "<div><b>Kazenskih točk</b>: ".$quizResult->kazenske_tocke." </div>";
	echo "<div style='margin-bottom:30px;'>&nbsp</div>";
	echo "<input type='submit' value='Shrani' /></form>";
	echo "<div style='margin-bottom:30px;'>&nbsp</div>";


	echo "<div style='margin-bottom:30px;'>&nbsp</div>";
}


echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>