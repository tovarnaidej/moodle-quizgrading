<?php 

var_dump($_POST);

global $DB;

$query = "UPDATE {quizgrading} SET 
procent=:procent,
dosezene_kazenske=':dosezene_kazenske',
izpis_opravil=':izpis_opravil',
max_kaz_poligon=:max_kaz_poligon,
dosezene_kazenske=:max_kaz_poligon
WHERE quizid=:quizid
";

$params = Array();
$params['procent'] = $_POST['procent'];
$params['dosezene_kazenske'] = $_POST['dosezene_kazenske'];
$params['izpis_opravil'] = $_POST['izpis_opravil'];
$params['max_kaz_poligon'] = $_POST['max_kaz_poligon'];
$params['max_kaz_voznja'] = $_POST['max_kaz_voznja'];
$params['dosezene_kazenske'] = $_POST['dosezene_kazenske'];
$params['quizid'] = $_POST['quizid'];

$DB->execute($query, $params);



?>