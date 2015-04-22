<?php

include('config.php');

$pdo = new PDO("mysql:host=$dbhost;port=$dbport;dbname=$dbname",$dbuser,$dbpass);
$sql = "SELECT * from metrics
		WHERE kpi_id = 5
		ORDER BY site_id, date";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
	$data[$row['site_id']][$row['date']] = $row['value'];
}

exit(json_encode($data));
?>