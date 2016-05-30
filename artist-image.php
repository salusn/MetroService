<?php
require('functions.php');

//Pager
$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;

//Id as parameter
//$id = $_GET['id'];
//print_r($id);

$data = getArtistImagenew($page,$itemsPerPage);
//print_r($data);
echo json_encode($data);
	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";

?>
