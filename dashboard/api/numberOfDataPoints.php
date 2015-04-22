<?php

include('../config.php');
$pdo = new PDO("mysql:host=$dbhost;port=$dbport;dbname=$dbname",$dbuser,$dbpass);

$formData = $_POST;

if(!empty($formData['startDate']) && isset($formData['sites']) > 0)
{
	//Calculate the period to check for datapoints

	$startDate = $formData['startDate'];
	$endDate = date("Y-m-d", strtotime($startDate. ' + '.$formData['period'].' days'));

	$sites = $formData['sites'];

	$query = "SELECT count(*) as count from metrics 
			  WHERE date BETWEEN :startDate AND :endDate";


	$bindArray = array(
		':startDate' => $startDate,
		':endDate' => $endDate,
	);


	if($formData['sites'][0] != 'all')
	{
		$string = implode("','", $formData['sites']);
		$queryString = "'".$string."'";

		$query .=  ' AND `site_id` IN ('.$queryString.')';
	}

	$axisString = "'".$formData['xAxis']."', "."'".$formData['yAxis']."', "."'".$formData['zAxis']."'";
	$query .= ' AND `kpi_id` IN ('.$axisString.')';
	
	$stmt = $pdo->prepare($query);
	//$stmt->bindParam(":startDate", $startDate);
	//$stmt->bindParam(":endDate", $endDate);


	foreach($bindArray as $key => $value)
	{
		$stmt->bindParam($key, $bindArray[$key]);
	}
	

	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$count = $row['count'];
	}

	$response['dataPoints'] = $count;
}
else
{
	$response['dataPoints'] = 111;
}

echo json_encode($response);