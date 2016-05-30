<?php
require('functions.php');

$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;

$cat = $_GET['category'];
//$id = $_GET['id'];

if($cat == "artist"){
	$data = getArtistAwards($cat,$page,$itemsPerPage);
}

if($cat == "films"){
	$data = getFilmAwards($cat,$page,$itemsPerPage);
}
echo json_encode($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
?>
