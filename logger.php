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

//require_once './globals.php'; // including required globals
require_once dirname(__FILE__) . '/globals.php'; // including required globals

include 'class.Utility.php'; // for logger module

///////////////////////////////////////////////////////////////
//

//for testing the system timzeone info
$timezone = false;
if  ( (defined('STDIN') && isset($argv[1]) && ($argv[1] == 'timezone'))   ) {
	$timezone = true;
}

if ($timezone) {

	$systemTimeZone = exec('date +%Z');
	echo 'Server time : '.  $systemTimeZone ."\n";

	//$timestamp = time();
	//echo 'PHP time : ' . date("Y-m-d H:i:s", $timestamp ) . "\n";
	echo 'PHP Core timezone : ' . date_default_timezone_get() . "\n";

}
//
/////////////////////////////////////////////////////////////

include 'class.Logger.php'; // for logger module
$logger = new Logger(); // Initializing Main Controller

if ($timezone) 
	echo 'Logger time: ' . date_default_timezone_get() ."\n";

include 'class.Timer.php'; // for logger module
$timer = new Timer(); // Initializing Timer


include 'class.Alert.php'; // for alerts module
$alert = new Alert(); // Initializing Alert


// List of modules and thier status 
//$loadedModules = Logger::$_settings->general['modules']; 
$loadedModules = Logger::$_modules; 
//var_dump($loadedModules);
//var_dump(Logger::$_modules);

//grab the log directory properly
//need to grab from system settings.ini instead
//$logdir = LoadAvg::$_settings->general['logs_dir']; // Loaded modules
$logdir = LOG_PATH;




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
// Delete/rotate out old log files

//TODO: update for nested log folders!
$logger->rotateLogFiles($logdir);


//TODO when sending api data we call data gathering 2x this is unnecssary
//we only need to call 1x and return data as string or true/false

//we can add 3 different modes to caller
//disk - log data to disk, default
//apionly - send back for api only no logging
//api - log to disk and send back for api
$logMode = "disk";

if ( $api ) 
	$logMode = "api";


if (!$testmode) {

	if (LOGDEBUG) echo "Start Main LOOP \n"; 

	//if we are collecting alert data reset array
	//Logger::viewAlerts();
	if (ALERTS) {
		$alert->initializeAlerts();
	}

	// Check for each module we have loaded
	foreach ( $loadedModules as $module => $value ) {

		if (LOGDEBUG) echo 'Module : ' . $module . ' status ' . $value . "\n";

		if ( $value == "false" ) 
			continue;

		// Settings for each loaded modules
		$moduleSettings = Logger::$_settings->$module;


		// Check if loaded module needs loggable capabilities
		if ( $moduleSettings['module']['logable'] == "true" ) {

			// load module information
			$class = Logger::$_classes[$module]; 

			// loop through module logging arguments
			//multiple args mean multiple charts like mysql or network modules
			foreach ( $moduleSettings['logging']['args'] as $args) { 

				$args = json_decode($args); // decode arguments

				$class->logfile = $logdir . $args->logfile; // the modules logfile si read from args

				//check for logdir
				if ( isset($moduleSettings['module']['hasownlogdir']) && 
					$moduleSettings['module']['hasownlogdir'] == "true" ) {
					$class->logdir =  $args->logdir; // the modules logdir as read from args
				}

				//see if we are timing, if so set start time
				if  ( $timemode  ) 
					$st = $timer->getTime();

				//
				//run modules logger
				$responseData = $class->logData($logMode);


				// if API then collect data for API server
				if ( $api ) {

					//TODO: nead a way to deal with modules that return more than one dataset for api
					//this is for networking module
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


				if  ( $timemode  ) {
					$et = $timer->getTime();
					echo "Module " . $module . " Time : " .   ($et - $st)   . " \n";
				}

			}
		}
	}

	if (ALERTS) {
		//$alert->viewAlerts();
		$alert->writeAlerts();
	}

	// Send data to API server
	if ( $api ) {
		//print_r($response) ;
		$apistatus = $logger->sendApiData($response);
	 }

}

/*
 * testing section
 * used to test if logger is running
 *
 * EXECUTE: php logger.php status
 */
if  ( $testmode  ) {

	echo "Testing Logger \n";

	$logger->testLoggerCore($api);

}

/*
 * timing section
 * used to time logger
 *
 * EXECUTE: php logger.php time
 */
if  ( $timemode ) {

	$timer->setFinishTime(); // Setting page load finish time

	$page_load = $timer->getPageLoadTime(); // Calculating page load time

	$mytime = (float) $timer->timeFinish - (float) $timer->timeStart;

	echo "End   Time : " . $timer->timeFinish . " \n"; 
	echo "Total Time : " . $mytime . " \n"; 
	echo "           : " . $page_load . " \n"; 
}

?>
