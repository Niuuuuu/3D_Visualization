<?php 
$configuration = $_GET['c'];
$fileHandle = $configuration . ".txt";
$fp = fopen($fileHandle, "w");

$inputStartDate = $_GET['sd'];
$inputPeriod = $_GET['ip'];
$xAxis = $_GET['x'];
$yAxis = $_GET['y'];
$zAxis = $_GET['z'];
$xAggregation = $_GET['xA'];
$yAggregation = $_GET['yA'];
$zAggregation = $_GET['zA'];
$inputXAxisScalingFactor = $_GET['xSF'];
$inputYAxisScalingFactor = $_GET['ySF'];
$inputZAxisScalingFactor = $_GET['zSF'];
$inputGraphType = $_GET['iGT'];

$saveString = $inputStartDate . PHP_EOL . $inputPeriod . PHP_EOL . $xAxis . PHP_EOL . $yAxis . PHP_EOL . $zAxis . PHP_EOL . $xAggregation . PHP_EOL . $yAggregation . PHP_EOL . $zAggregation . PHP_EOL . $inputXAxisScalingFactor  . PHP_EOL . $inputYAxisScalingFactor . PHP_EOL . $inputZAxisScalingFactor . PHP_EOL . $inputGraphType;
fwrite($fp, $saveString);
fclose($fp);
?>
