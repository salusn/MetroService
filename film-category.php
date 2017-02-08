<?php
require('functions.php');

$id = $_GET['id'];

$data = get_film_category($id);
echo json_encode($data);