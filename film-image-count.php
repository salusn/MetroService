<?php
require 'functions.php';

$data = film_image_count();
//print_r($data);exit;

echo json_encode($data);
exit;
?>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


<?php

$output = "";
$index = 1;

foreach ($data as $key => $value) {
	foreach ($value as $k => $v) {
		if ($v != "") {

			$c = ceil($key / 50);

			$link = '<ul class="list-group">';

			for ($i = 1; $i <= $c; $i++) {

				$link .= '<li class="list-group-item">http://old.metromatinee.com/api/MetroService/film-images.php?q=&id=' . $v . '&page=' . $i . '&count=50</li>';
			}

			$link .= "</ul>";

			$output .= '<div class="panel panel-default">
					  <div class="panel-heading">
					  <span class="badge">' . $key . '</span>
					  <span class="badge">' . $c . '</span>
					  ' . $index . ' # ' . $v . '</div>
					  <div class="panel-body">
					    ' . $link . '
					  </div>
					</div>';

			$index++;
		}
	}
}

?>

<div class="container">
	<div class="col-md-6">
		<?php echo $output; ?>
	</div>
</div>