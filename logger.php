<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Get and log data
*
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

// initialize LoadAvg and grab data

require_once dirname(__FILE__) . '/globals.php'; // including required globals
include 'class.LoadAvg.php'; // including Main Controller

$loadavg = new LoadAvg(); // Initializing Main Controller
$loaded = LoadAvg::$_settings->general['modules']; // Loaded modules
$logdir = HOME_PATH . '/logs/'; // path to logfiles folder

// used to test log creation
//if  ( (defined('STDIN') && isset($argv[1]) && ($argv[1] == 'create'))   ) {
//
//	$logger_status = $loadavg->createFirstLogs();
//	die;
//}


// Delete old log files
// should we execute this every time ?
$fromDate = strtotime("-". LoadAvg::$_settings->general['daystokeep'] ." days 00:00:00");
$dates = $loadavg->getDates();
foreach ( $dates as $date ) {
	$date = strtotime($date);
	if ($date < $fromDate) {
		$mask = $logdir . "*_" . date("Y-m-d", $date) . "*.log";
		array_map( 'unlink', glob( $mask ) );
	}
}
// End of delete old logs


//check for api server data transfer
$api = false;
if (LoadAvg::$_settings->general['apiserver'] == "true") {
	$api = true; 
}

$response = array();
//


// Check for each module we have loaded
foreach ( $loaded as $module => $value ) {
	if ( $value == "false" ) continue;

	// Settings for each loaded modules
	$moduleSettings = LoadAvg::$_settings->$module;

	// Check if loaded module needs loggable capabilities
	if ( $moduleSettings['module']['logable'] == "true" ) {
		foreach ( $moduleSettings['logging']['args'] as $args) { // loop trought module logging arguments

			$args = json_decode($args); // decode arguments
			$class = LoadAvg::$_classes[$module]; // load module information
			$caller = $args->function;

			$class->logfile = $logdir . $args->logfile; // the modules logfile

			$class->$caller(); // call data gethering function of module

			// collect data for API server
			if ( $api ) {

				$responseData = $class->$caller('api'); // call data gethering function of module
				$data = explode("|", $responseData); // parsing response data
				$timestamp = $data[0];

				$response[$module] = array("data" => $responseData, "timestamp" => $timestamp); // Populating response array
			}
			// end collect data for API server	


		}
	}
}

// used to test if logger is running
if  ( (defined('STDIN') && isset($argv[1]) && ($argv[1] == 'status'))   ) {

	$logger_status = $loadavg->testLogs();

	if ( $logger_status )
		echo "The logger appears to be running \n";
	else 
		echo "The logger does not seem to be running \n"; 
}




// Sending data to API server
if ( $api ) {
	$response = $loadavg->sendApiData($response);
 }

?>
