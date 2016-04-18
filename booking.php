<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $DB;

$query = "SELECT * FROM {booking_teachers}";

$data = $DB->get_records_sql($query);

var_dump($data);

?>