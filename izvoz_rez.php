<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


global $DB;

$datumQuery = "";
$quizResult = "";
$osQuery = "";
$bookingQuery = "";
$letoQuery = "";

$mentor = false;

if(isset($_GET['mentor']) && $_GET['mentor'] =="1")
{
	$mentor = true;
}

//array(5) { ["datum"]=> string(10) "2015-07-08" ["leto"]=> string(7) "2014/15" ["os"]=> string(0) "" ["booking"]=> string(3) "816" ["quizid"]=> string(4) "5431" }

if(isset($_GET['os'])  && $_GET['os'] != '')
{
		$osQuery = " AND institution LIKE '%".($_GET['os'])."%' ";
}

if(isset($_GET['booking'])  && $_GET['booking'] != '')
{
	$bookingQuery = " AND optionid=".($_GET['booking'])." ";
}
	
if(isset($_GET['leto'])  && $_GET['leto'] != '')
{
	$split = explode("/",$_GET['leto']);
	$letoQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve`)) > DATE('".$split[0]."-8-31') AND DATE(FROM_UNIXTIME(`datum_resitve`)) <= DATE('".($split[0]+1)."-8-31') ";
}

$mentorQuery = "";

if($mentor)
{
	$mentorQuery=" AND mentorid=".$USER->id." ";
}

$query = "SELECT id,startna_st,firstname,lastname,institution,kazenske_tocke,tocke_poligon,tocke_voznja,tocke_voznja,tocke_skupaj,uvrstitev_posamezniki,skupina,tocke_skupina,uvrstitev_skupina
FROM {quizgrading_results} WHERE quizid=? ".$datumQuery.$osQuery.$bookingQuery.$letoQuery.$mentorQuery;

/*
$query = "SELECT id,quizgradingid,quizid,attempt_id,quizname,course,sumgrades,userid,username,firstname,lastname,email,institution,mentorid,mentor,dosezeno_tock,kazenske_tocke,moznih_tock,procent,vprasanja,status_kviza,DATE(FROM_UNIXTIME(datum_rojstva)) datum_rojstva,DATE(FROM_UNIXTIME(datum_resitve)) datum_resitve,DATE(FROM_UNIXTIME(datum_vpisa)) datum_vpisa,
startna_st,tocke_poligon,tocke_voznja,uvrstitev_posamezniki,skupina,tocke_skupina,uvrstitev_skupina,tocke_skupaj,optionid,naziv_izvedbe,lokacija,organizator
FROM {quizgrading_results} WHERE quizid=? ".$datumQuery.$osQuery.$bookingQuery.$letoQuery.$mentorQuery;*/

if(!is_null($_GET['datum']) && $_GET['datum'] > 0)
{
	$datumStr = $_GET['datum'];
		
	$datumQuery = " AND DATE(FROM_UNIXTIME(`datum_resitve` )) = DATE('".$datumStr."')";
	
	$query = "SELECT id,startna_st,firstname,lastname,institution,kazenske_tocke,tocke_poligon,tocke_voznja,tocke_voznja,tocke_skupaj,uvrstitev_posamezniki,skupina,tocke_skupina,uvrstitev_skupina 
FROM {quizgrading_results} WHERE quizid=? ".$datumQuery.$osQuery.$bookingQuery.$letoQuery.$mentorQuery;

	$quizResult = $DB->get_records_sql($query,array($_GET['quizid']));
}
else
{
	$query = "SELECT id,startna_st,firstname,lastname,institution,kazenske_tocke,tocke_poligon,tocke_voznja,tocke_voznja,tocke_skupaj,uvrstitev_posamezniki,skupina,tocke_skupina,uvrstitev_skupina 
FROM {quizgrading_results} WHERE quizid=? ".$datumQuery.$osQuery.$bookingQuery.$letoQuery.$mentorQuery;

	$quizResult = $DB->get_records_sql($query,array($_GET['quizid']));
}


//$quizResult = $DB->get_records('quizgrading_results', array('quizid'=>$_GET['quizid']));

//$quizResult = $DB->get_records_sql($query,array($_GET['quizid']));

$delimiter = ';';


if (count($quizResult) > 0) {
	
	$filename = "izvoz.csv";
	
    $f = fopen('php://memory', 'w');

    $idx = 0;
    foreach ($quizResult as $element) {
    	if($idx==0)
		{
			$header = array_keys((array)$element);
    		fputcsv($f, $header, $delimiter);
			
			$idx = 1;
		}
		
        fputcsv($f, (array)$element, $delimiter);
    }
	
	fseek($f, 0);
	//header('Content-Type: application/csv');
	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachement; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    echo "\xEF\xBB\xBF";
    fpassthru($f);
}

?>