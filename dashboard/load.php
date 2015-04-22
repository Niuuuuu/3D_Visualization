<?php 
$configuration = $_GET['c'];
$fileHandle = $configuration . ".txt";
$fp = fopen($fileHandle, "r");
while(!feof($fp)) {
  echo fgets($fp);
}


fclose($fp);
?>
