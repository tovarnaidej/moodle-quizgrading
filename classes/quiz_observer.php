<?php
class mod_quizgrading_quiz_observer {
	public static function observe_one($event)
	{
		echo "Observe one <br />";
		//var_dump($event);
		echo "EO Observe one";
		//file_put_contents("observe_one.txt", var_export($event), FILE_APPEND);
	}
	
	public static function observe_submit($event)
	{
		echo "Observe submit <br />";
		//var_dump($event);
		echo "EO Observe submit";
		//file_put_contents("observe_one.txt", var_export($event), FILE_APPEND);
	}

	public static function observe_all($event)
	{
		//echo "Observe all <br />";
		global $CFG;
		global $DB;
		//var_dump($event,str_replace('\\','',$event->eventname));
		switch(str_replace('\\','',$event->eventname))
		{
			case "mod_quizeventattempt_reviewed":	
				$cm = $DB->get_record('course_modules', array('instance'=>$event->other['quizid'],'module'=>12));
				
				$quizgrading = $DB->get_records('quizgrading',array('coursemoduleid'=>$cm->id),'tip_instance ASC');
				
				//$quizgrading = $DB->get_record('quizgrading',array('coursemoduleid'=>$cm->id,'tip_instance'=>1));
				
				$quizgrading = current($quizgrading);
				
				
				
				$cm = $DB->get_record('course_modules', array('instance'=>$quizgrading->id,'module'=>33));	
				$attempt = $DB->get_record('quiz_attempts', array('id'=>$event->objectid));

				is_quiz_success($event->other['quizid'],$attempt->uniqueid,true,$cm->id);
				file_put_contents(dirname(__FILE__)."/observe_reviewed.txt", var_export($event,true), FILE_APPEND);
				break;
		}
		
	}
}
