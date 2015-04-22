<?php

include('config.php');

$pdo = new PDO("mysql:host=$dbhost;port=$dbport;dbname=$dbname",$dbuser,$dbpass);
$sql = "SELECT * from metrics
		WHERE kpi_id = 5
		ORDER BY value desc
		LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
	$data['value'] = $row['value'];
}

exit(json_encode($data));
?>