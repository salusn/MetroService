<?php
require('functions.php');

//Pager
$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;

$data = getArtistClipsByArtistID($page,$itemsPerPage);
//print_r($data);
echo json_encode($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";

?>
