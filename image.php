<?php
require('functions.php');

//Pager
$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;

$data = getArtistImage($page,$itemsPerPage);
echo json_encode($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";

?>
