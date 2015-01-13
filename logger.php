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


include 'class.Logger.php'; // for logger module
$logger = new Logger(); // Initializing Main Controller

include 'class.Timer.php'; // for logger module
$timer = new Timer(); // Initializing Timer

$loaded = Logger::$_settings->general['modules']; // Loaded modules

//grab the log diretory
$logdir = LOG_PATH;

//need to grab from system settings.ini instead
//$logdir = LoadAvg::$_settings->general['logs_dir']; // Loaded modules


//for testing the system
$testmode = false;

if  ( (defined('STDIN') && isset($argv[1]) && ($argv[1] == 'status'))   ) {
	$testmode = true;
}

$timemode = false;
$st = $et = null;

if  ( (defined('STDIN') && isset($argv[1]) && ($argv[1] == 'time'))   ) {
	$timemode = true;
	$timer->setStartTime(); // Setting page load start time

	echo "Start Time : " . $timer->timeStart . " \n"; 

}

//check for api server data transfer
$api = false;

if (Logger::$_settings->general['settings']['apiserver'] == "true") {
	$api = true; 
}

//array of data from logging used to send to api
$response = array();

////////////////////////////////////////////////
// Delete old log files

$logger->rotateLogFiles($logdir);


//when sending api data we call data gathering 2x this is unnecssary
//we only need to call 1x and return data as string or true/false

if (!$testmode) {

	// Check for each module we have loaded
	foreach ( $loaded as $module => $value ) {
		if ( $value == "false" ) continue;

		// Settings for each loaded modules
		$moduleSettings = Logger::$_settings->$module;

		// Check if loaded module needs loggable capabilities
		if ( $moduleSettings['module']['logable'] == "true" ) {
			foreach ( $moduleSettings['logging']['args'] as $args) { // loop trought module logging arguments


				$args = json_decode($args); // decode arguments
				$class = Logger::$_classes[$module]; // load module information

				//the modules logging function is read from the args
				$caller = $args->function;

				$class->logfile = $logdir . $args->logfile; // the modules logfile si read from args


				if  ( $timemode  ) 
					$st = $timer->getTime();

				//we can add 3 different modes to caller
				//log - log data
				//api - send back for api only no logging
				//logapi - log and send back for api
				$logMode = "api";

				// collect data for API server
				if ( $api ) {
					$responseData = $class->$caller($logMode);

					if (is_array($responseData))
					{
						$timestamp = "";
						$dataInterface = "";

						foreach ($responseData as $interface => $value) {
						    //echo 'INT: ' . $interface . ' VAL: ' . $value . "\n";
							$data = explode("|", $value); // parsing response data
							$dataInterface[$interface] = array("data" => $value, "timestamp" => $data[0]);
						}
						$response[$module] = $dataInterface;

					} else {
						$data = explode("|", $responseData); // parsing response data
						$timestamp = $data[0];
						$response[$module] = array("data" => $responseData, "timestamp" => $timestamp); // Populating response array
					}
				}
				else
					$class->$caller(); 

				if  ( $timemode  ) {
					$et = $timer->getTime();
					echo "Module " . $module . " Time : " .   ($et - $st)   . " \n";
				}

			}
		}
	}

	// Send data to API server
	if ( $api ) {
		//print_r($response) ;
		$apistatus = $logger->sendApiData($response);
	 }

}

/////////////////////////////////////////////////////////
// testing section

// used to test if logger is running
if  ( $testmode  ) {

	echo "Testing Logger \n";

	$logger_status = $logger->testLogs();

	if ( $logger_status )
		echo "The logger appears to be running \n";
	else 
		echo "The logger does not seem to be running \n"; 

	// Sending data to API server
	if ( $api ) {

		echo "API Active, Testing API \n";

		$apistatus = $logger->testApiConnection(true);

		if ( $apistatus )
			echo "The API appears to be running \n";
		else 
			echo "The API does not seem to be running \n"; 
	 }
}

/////////////////////////////////////////////////////////
// timing  section
if  ( $timemode ) {

	$timer->setFinishTime(); // Setting page load finish time

	$page_load = $timer->getPageLoadTime(); // Calculating page load time

	$mytime = (float) $timer->timeFinish - (float) $timer->timeStart;

	echo "End   Time : " . $timer->timeFinish . " \n"; 

	echo "Total Time : " . $mytime . " \n"; 

	echo "           : " . $page_load . " \n"; 

}

?>
