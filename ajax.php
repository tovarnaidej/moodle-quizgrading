<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
global $USER;

$returnObject = Array();
switch($_GET['action'])
{
	case "get_view":
		$date = Array();
		$date['datum'] = $_GET['datum'];
		$date['leto'] = $_GET['leto'];
		
		$date['os'] = $_GET['os'];
		
		$date['booking'] = $_GET['booking'];
		
		$order = Array();
		if(isset($_GET['orderby']))
			$order['orderby'] = $_GET['orderby'];
		
		if(isset($_GET['order']))
			$order['order'] = $_GET['order'];
		
		$preracun = (isset($_GET['preracun']) && $_GET['preracun']=="1") ? true : false;
		
		
		$result = get_quizgrade_view($_GET['quizid'],true,false,$_GET['gradingid'],$date,$order,false,$_GET['page'],false,$preracun);
		$returnObject['result'] = $result;
	break;
	case "st_dresa":
		$date = Array();
		$date['datum'] = $_GET['datum'];
		$date['leto'] = $_GET['leto'];
		
		$date['os'] = $_GET['os'];
		
		$order = Array();
		if(isset($_GET['orderby']))
			$order['orderby'] = $_GET['orderby'];
		
		if(isset($_GET['order']))
			$order['order'] = $_GET['order'];
		
		
		$result = get_quizgrade_view($_GET['quizid'],true,false,$_GET['gradingid'],$date,$order,false,$_GET['page'],$_GET['st_dresa']);
		$returnObject['result'] = $result;
	break;
	case "get_view_student":
		$result = get_quizgrade_view($_GET['quizid'],true,true,$_GET['gradingid'],null,null);
		$returnObject['result'] = $result;
	break;	
	case "get_view_mentor":
		
		$date = Array();
		$date['datum'] = $_GET['datum'];
		$date['leto'] = $_GET['leto'];
		
		$date['os'] = $_GET['os'];
		
		$date['booking'] = $_GET['booking'];
		$date['opravil'] = $_GET['opravil'];
		
		
		$order = Array();
		if(isset($_GET['orderby']))
			$order['orderby'] = $_GET['orderby'];
		
		if(isset($_GET['order']))
			$order['order'] = $_GET['order'];
		
		$preracun = (isset($_GET['preracun']) && $_GET['preracun']=="1") ? true : false;
		
		$result = get_quizgrade_view($_GET['quizid'],true,false,$_GET['gradingid'],$date,$order,true,$_GET['page'],false,$preracun);
		$returnObject['result'] = $result;
	break;	
	case "get_categories":
		$result = get_quiz_categories($_GET['quizid']);
		$returnObject['result'] = $result;
	break;
	
	case "get_config":
		$result = get_quizgrade_config($_GET['quizid']);
		$returnObject['result'] = $result;
	break;
	
	case "insert_update_cat_config":
		$result = insert_update_cat_config($_POST);
		$returnObject['result'] = $result;
	break;
	case "save_config":
		$result = save_quizgrade_config($_POST['quizid'],$_POST['config']);
	break;
	
	case "is_quiz_success_regrading":
		$result = is_quiz_success_regrading($_GET['quizid'],true,$_GET['gradingid']);
		$returnObject['result'] = true;
	break;
	
	case "insert_update_attribute":
		$result = insert_update_attribute($_POST);
		$returnObject['result'] = $result;
		break;
	case "save_editable":
		$result = save_editable($_POST['quizgradingid'],$_POST['name'],$_POST['value'],$_POST['datum']);
		$returnObject['result'] = $result;
	break;
	case "generiraj_startne_st":
		$result = generiraj_startne_st($_GET['quizid']);
		$returnObject['result'] = $result;
	break;
	case "generiraj_startne_st_mentor":
		$result = generiraj_startne_st($_GET['quizid'],$USER->id,$_GET['gradingid'],$_GET['booking'],$_GET['datum']);
		$returnObject['result'] = $result;
	break;
	case "get_quiz_dates_mentor":
		
		$dates = get_quiz_dates_mentor($_GET['quizid'],$USER->id,$_GET['cmid'],$_GET['solskoleto'],$_GET['booking'],$_GET['os']);
		foreach($dates as $key=>$object)
		{
			$date = new DateTime($object->timefinish);
			$object->datum = $date->format('d.m.Y');
		}
		$returnObject['result'] = ($dates);
	break;
	case "get_quiz_bookings_mentor":
		$bookings = get_quiz_bookings_mentor($_GET['quizid'],$USER->id,$_GET['cmid']);
		$returnObject['result'] = ($bookings);
	break;
	case "get_quiz_institutions_mentor":
		$institutions = get_quiz_institutions_mentor($_GET['quizid'],$_GET['cmid'],$USER->id,$_GET['booking'],$_GET['datum']);
		$returnObject['result'] = ($institutions);
	break;
	case "get_quiz_bookings":
		 $bookings = get_quiz_bookings($_GET['quizid']);
		 $returnObject['result'] = ($bookings);
	break;
	case "get_quiz_dates":
		
		$dates = get_quiz_dates($_GET['quizid'],$_GET['solskoleto'],$_GET['booking'],$_GET['os']);
		foreach($dates as $key=>$object)
		{
			$date = new DateTime($object->timefinish);
			$object->datum = $date->format('d.m.Y');
		}
		$returnObject['result'] = ($dates);
	break;
	case "get_quiz_institutions":
		$institutions = get_quiz_institutions($_GET['quizid'],$_GET['booking'],$_GET['datum']);
		$returnObject['result'] = ($institutions);
	break;
}

echo json_encode($returnObject);

?>