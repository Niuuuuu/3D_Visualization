<?php
//Spreadsheet readers needed to convert Excel File
include('spreadsheet-reader-master/SpreadsheetReader_XLSX.php');
include('spreadsheet-reader-master/SpreadsheetReader.php');

// Turn off all error reporting
//error_reporting(0);

$colors = new Colors();

//Collect database information
echo "MySQL Host (127.0.0.1): ";
$handle = fopen ("php://stdin","r");
$dbhost = trim(fgets($handle));

echo "MySQL Port (8889 for MAMP, 3306 for Native): ";
$dbport = trim(fgets($handle));

echo "Database username: ";
$dbuser = trim(fgets($handle));

echo "Database password: ";
$dbpass = trim(fgets($handle));

echo "Testing connection to Database";

//Test the connection
try
{
	$dsn = "mysql:host=$dbhost;port=$dbport;";
	$pdo = new PDO($dsn,$dbuser,$dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	echo $colors->getColoredString("\t \t \tSuccess \n", "green", null);

} catch (Exception $e) {
	echo $colors->getColoredString("\t \t \tFailed \n", "red", null);
	echo $colors->getColoredString($e->getMessage()."\n", null, "red");
	exit();
}


echo "Has a database been already created? [Y/n]: ";
$dbResponse = strtolower(trim(fgets($handle)));
if($dbResponse == "n")
{
	//Database has not been created, lets make one
	echo "Name for the new database: ";
	$dbname = trim(fgets($handle));
	echo "Creating new database";
	try {

		//Create the database
		$stmt = $pdo->prepare("CREATE DATABASE `$dbname`");
		$stmt->execute();

		//Select the database that was just created
		$stmt = $pdo->prepare("USE `$dbname`");
		$stmt->execute();

		echo $colors->getColoredString("\t \t \t \tSuccess \n", "green", null);
	} catch (Exception $e) {
		echo $colors->getColoredString("\t \t \tFailed \n", "red", null);
		echo $colors->getColoredString($e->getMessage()."\n", null, "red");
		exit();
	}


	createTables();


}
else
{
	echo "Name for the existing database: ";
	$dbname = trim(fgets($handle));

	echo "Selecting database";
	try
	{
		//Select the database
		$stmt = $pdo->prepare("USE `$dbname`");
		$stmt->execute();
		echo $colors->getColoredString("\t \t \t \tSuccess \n", "green", null);
	} catch (Exception $e) {
		echo $colors->getColoredString("\t \t \t \tFailed \n", "red", null);
		echo $colors->getColoredString($e->getMessage()."\n", null, "red");
		exit();
	}

	echo "Have the tables already been created? (kpis, metrics, sites) [Y/n]: ";
	$tableResponse = strtolower(trim(fgets($handle)));
	if($tableResponse == "n")
	{
		createTables();
	}


}


echo "Filename of spreadsheet to parse (Must be in current Directory): ";
$filename = trim(fgets($handle));
echo "Checking if file exists";
if(file_exists($filename))
{
	echo $colors->getColoredString("\t \t \tSuccess \n", "green", null);
	

	$reader = new SpreadsheetReader($filename);
	$sheets = $reader->sheets();

	foreach($sheets as $index => $name){
		//Loop through each worksheet
		echo "Inserting Data For : ".$name."\n";

		$reader->ChangeSheet($index);

		//Insert KPI Name into SQL
		$sql = "INSERT INTO kpis (name) VALUES (:name)";
	    $stmt = $pdo->prepare($sql);
	    $params = array(
	        ":name" => $name,
	    );

	   	$data = $stmt->execute($params);

	   	//Fetch the KPI ID -> used later
		$kpiID = $pdo->lastInsertId();

		$i = 0;

		foreach($reader as $row){
			if($i != 0)
			{
				$concatID = $row[0];

				$explodedID = explode('-', $concatID); //Split the string based of hyphens

				$SiteID = $explodedID[0];
				echo "\t $SiteID \n";
				$equipmentID = $explodedID[1];

				preg_match("/([a-zA-Z]+)(\d+)/", $equipmentID, $equipmentArray); //Split the string into characters and letters

				$equipmentID  = $equipmentArray[1];
				$cellID = $equipmentArray[2];

				$kpiName = $explodedID[2];

				//Insert site into DB
				$sql = "INSERT IGNORE into sites (id, equipment_id, cell_id) VALUES (:id, :equipment, :cell)";
				$stmt = $pdo->prepare($sql);
			    $params = array(
			        ":id" => $SiteID,
			        ":equipment" => $equipmentID,
			        ":cell" => $cellID,
			    );
			   	$stmt->execute($params);

				$j = 0;
				foreach($row as $entry)
				{
					if ($j != 0)
					{
						//We need to reformat the date for SQL
						$date = new DateTime($indexArray[$j]);


						//Insert datapoint into DB
						$sql = "INSERT into metrics (site_id, kpi_id, value, date) VALUES (:site, :kpi, :value, :date)";
						$stmt = $pdo->prepare($sql);
					    $params = array(
					        ":site" => $SiteID,
					        ":kpi" => $kpiID,
					        ":value" => $entry,
					        ":date" => $date->format('Y-m-d'),
					    );
					   	$stmt->execute($params);
					}
					$j++;
				}
			}
			else
			{
				$indexArray = $row;
			}

			$i++;
		}
	}
}
else
{
	echo $colors->getColoredString("\t \t \t \tFailed \n", "red", null);
	echo $colors->getColoredString("Cannot open file! Make sure it exists! \n", null, "red");
	exit();
}


function createTables()
{
	global $pdo;
	global $colors;
	//Make the new tables
	try{
		echo "Creating tables";

		$stmt = $pdo->prepare('CREATE TABLE `kpis` (
  			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 			`name` varchar(255) DEFAULT NULL,
  			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;');
		$stmt->execute();
		echo $colors->getColoredString("\n\t Created kpis \n", "green", null);

		$stmt = $pdo->prepare("CREATE TABLE `metrics` (
			`site_id` varchar(6) DEFAULT NULL,
			`kpi_id` int(11) DEFAULT NULL,
			`value` decimal(24,12) DEFAULT NULL,
			`date` date DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
		$stmt->execute();
		echo $colors->getColoredString("\t Created metrics \n", "green", null);

		$stmt = $pdo->prepare("CREATE TABLE `sites` (
			`id` varchar(6) NOT NULL DEFAULT '',
			`equipment_id` varchar(5) DEFAULT NULL,
			`cell_id` varchar(12) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
		$stmt->execute();
		echo $colors->getColoredString("\t Created sites \n", "green", null);

	} catch (Exception $e) {

		echo $colors->getColoredString("\t \t \tFailed \n", "red", null);
		echo $colors->getColoredString($e->getMessage()."\n", null, "red");

	}
	echo $colors->getColoredString("Finished! \n", "green", null);
}


class Colors {
		private $foreground_colors = array();
		private $background_colors = array();
 
		public function __construct() {
			// Set up shell colors
			$this->foreground_colors['black'] = '0;30';
			$this->foreground_colors['dark_gray'] = '1;30';
			$this->foreground_colors['blue'] = '0;34';
			$this->foreground_colors['light_blue'] = '1;34';
			$this->foreground_colors['green'] = '0;32';
			$this->foreground_colors['light_green'] = '1;32';
			$this->foreground_colors['cyan'] = '0;36';
			$this->foreground_colors['light_cyan'] = '1;36';
			$this->foreground_colors['red'] = '0;31';
			$this->foreground_colors['light_red'] = '1;31';
			$this->foreground_colors['purple'] = '0;35';
			$this->foreground_colors['light_purple'] = '1;35';
			$this->foreground_colors['brown'] = '0;33';
			$this->foreground_colors['yellow'] = '1;33';
			$this->foreground_colors['light_gray'] = '0;37';
			$this->foreground_colors['white'] = '1;37';
 
			$this->background_colors['black'] = '40';
			$this->background_colors['red'] = '41';
			$this->background_colors['green'] = '42';
			$this->background_colors['yellow'] = '43';
			$this->background_colors['blue'] = '44';
			$this->background_colors['magenta'] = '45';
			$this->background_colors['cyan'] = '46';
			$this->background_colors['light_gray'] = '47';
		}
 
		// Returns colored string
		public function getColoredString($string, $foreground_color = null, $background_color = null) {
			$colored_string = "";
 
			// Check if given foreground color found
			if (isset($this->foreground_colors[$foreground_color])) {
				$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
			}
			// Check if given background color found
			if (isset($this->background_colors[$background_color])) {
				$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
			}
 
			// Add string and end coloring
			$colored_string .=  $string . "\033[0m";
 
			return $colored_string;
		}
 
		// Returns all foreground color names
		public function getForegroundColors() {
			return array_keys($this->foreground_colors);
		}
 
		// Returns all background color names
		public function getBackgroundColors() {
			return array_keys($this->background_colors);
		}
	}