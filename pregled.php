<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

require_login();

if(!is_numeric($_GET['quizid']) OR !is_numeric($_GET['attempt_id']) OR !is_numeric($_GET['gradingid']))
	die("Napačni parametri!");

global $DB;

echo $OUTPUT->header();
echo $OUTPUT->box_start();


$quizResult = $DB->get_record('quizgrading_results', array('quizid'=>$_GET['quizid'],'attempt_id'=>$_GET['attempt_id']));

$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$_GET['gradingid'];
	
$gradingConfig = $DB->get_record_sql($query);


if($quizResult)
{
	
	$vprasanja = $quizResult->vprasanja;
	$vprasanja = explode(";", $vprasanja);
	
	$config_db = $DB->get_record('quizgrading_config', array('quizid'=>$_GET['quizid']));
	
	if(isset($config_db->config))
	$config_db = explode(";", $config_db->config);
	
	$config_db[0] = $gradingConfig->procent;
	$config_db[1] = $gradingConfig->dosezene_kazenske;
	$config_db[2] = $gradingConfig->izpis_opravil;
	

	$tipIzpisa = "dosezene";
	$izpisiUspesnost = "DA";
	 
	if(count($config_db) == 3)
	{
		$tipIzpisa = $config_db[1];
		$izpisiUspesnost = $config_db[2];
	}
	
	$query = "SELECT q_att.*,question.category,question.questiontext FROM {question_attempts} q_att, {question} question WHERE q_att.questionid=question.id AND q_att.questionusageid=".$quizResult->attempt_id;
	//echo $query;
	$slots2 = $DB->get_records_sql($query);
	
	//var_dump($slots2);
	
	$slots = $DB->get_records('quizgrading_attempt_info',array('quizid'=>$_GET['quizid'],'attempt_id'=>trim($_GET['attempt_id'])));
	
	//echo $_GET['attempt_id']." ".$_GET['quizid'];
	
	//$slots = $DB->get_records('quizgrading_attempt_info',array('quizid'=>$_GET['quizid']));
	
	//var_dump($slots);
	
	echo $OUTPUT->heading($quizResult->quizname." - pregled");
	echo "<div style='margin-top:20px;'><b>Ime in priimek</b>: ".$quizResult->firstname." ".$quizResult->lastname." </div>";
	echo "<div><b>Institucija</b>: ".$quizResult->institution." </div>";	
	echo "<div><b>Kviz</b>: ".$quizResult->quizname." </div>";	
	
	echo "<div><b>Možnih točk</b>: ".$quizResult->moznih_tock." </div>";
	
	if($tipIzpisa == "dosezene")
	echo "<div><b>Doseženih točk</b>: ".$quizResult->dosezeno_tock." </div>";
	else
	echo "<div><b>Kazenskih točk</b>: ".$quizResult->kazenske_tocke." </div>";
	
	if($izpisiUspesnost == "DA")
	echo "<div><b>Opravil</b>: ".(($quizResult->status_kviza == 1) ? "da" : "ne")." </div>";
	
	echo "<div style='margin-bottom:30px;'>&nbsp</div>";
	
	//var_dump($slots);
	
	//echo "<ul>";
	foreach($slots as $key=>$object)
	{
		$cat = $DB->get_record('quizgrading_category_config',array('category'=>$object->category,'quizid'=>$_GET['quizid']));
		//var_dump($cat);
		if(!$cat)
		{
			$cat = $DB->get_record('question_categories',array('id'=>$object->category));
			$cat->izpisi_kaj = substr($cat->name, 0,stripos($cat->name,' '));
			$cat->izpisi = "DA";
			$cat->tocke = 1;
		}
		
		//var_dump($object);
		//echo "<br /><br />";
		
		//<span style='background-color:#FFF;'><img class='' alt='Incorrect' title='Incorrect' src='/theme/image.php/clean/core/1424702011/i/grade_incorrect'></span>
		
		//if($cat->izpisi == "DA")
		try
		{
			echo "<div style='margin-bottom:5px;background-color:".(($object->rightanswer == $object->responsesummary) ? "#cfc" : "#fcc").";'>
			<img src='' />
			<b>".$cat->izpisi_kaj."</b> <b>Vprašanje:</b> ".strip_tags($object->questionsummary)." - <b>Označen odgovor:</b> ".$object->responsesummary." <b>Pravilen odgovor:</b> ".$object->rightanswer."<b>Št. točk:</b>".$cat->tocke."</div>";
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
			//echo "<li><div style='background-color:".(($object->rightanswer == $object->responsesummary) ? "#cfc" : "#fcc").";'><b>Kategorija</b>: ".$cat->izpisi_kaj." <b>Pravilen odgovor:</b> ".$object->rightanswer." <b>Označen odgovor:</b> ".$object->responsesummary."</div></li>";
		
	}
	//echo "</ul>";
	echo "<div style='margin-bottom:30px;'>&nbsp</div>";
}


echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>