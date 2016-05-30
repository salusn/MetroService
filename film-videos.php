<?php
require('functions.php');

$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;

$cat = $_GET['category'];
//$id = $_GET['id'];

if($cat == "trailers"){
	$data = getFilmTrailers($cat,$page,$itemsPerPage);
}

if($cat == "clips"){
	$data = getFilmClips($cat,$page,$itemsPerPage);
}
echo json_encode($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
?>
