<?php

if($_POST['viz'] == 'web')
{
	if($_POST['chart'] == 'scatter')
	{
		$url = "Location: /capstone/dashboard/scatterplot.php";
	}
	else
	{
		$url = "Location: /capstone/dashboard/bargraph.php";
	}
	
}
else
{
	if($_POST['chart'] == 'scatter')
	{
		$url = "Location: /capstone/dashboard/scatterplot2.php";
	}
	else
	{
		$url = "Location: /capstone/dashboard/bargraph2.php";
	}
}

$kpi = $_POST['kpi'];


$httpquery = http_build_query(array('sites' => $_POST['sites'],
								'kpi' => $_POST['kpi']));

header($url."?".$httpquery);
die();