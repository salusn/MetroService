<?php
require 'functions.php';

$page = $_GET['page'];
$itemsPerPage = isset($_GET['count']) ? $_GET['count'] : 1;
$id = $_GET['id'];
$data = getArtistImage($id, $page, $itemsPerPage);
echo json_encode($data);

?>
