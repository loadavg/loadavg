<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
 * Get and send API data to server
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

require_once dirname(__FILE__) . '/globals.php'; // including required globals
include 'class.LoadAvg.php'; // including Main Controller

$loadavg = new LoadAvg(); // Initializing Main Controller
$loaded = LoadAvg::$_settings->general['modules']; // Loaded modules
$logdir = APP_PATH . '/../logs/'; // path to logfiles folder
$response = array();

// Check for each module we have loaded
foreach ( $loaded as $module => $value ) {
	if ( $value == "false" ) continue;
	$moduleSettings = LoadAvg::$_settings->$module; // Settings for each loaded modules
	// Check if loaded module needs loggable capabilities
	if ( $moduleSettings['module']['logable'] == "true" ) {
		foreach ( $moduleSettings['logging']['args'] as $args) { // loop trought module logging arguments
			$args = json_decode($args); // decode arguments
			$class = LoadAvg::$_classes[$module]; // load module information
			$caller = $args->function;
			$class->logfile = $logdir . $args->logfile; // the modules logfile
			$responseData = $class->$caller('api'); // call data gethering function of module
			$data = explode("|", $responseData); // parsing response data
			$timestamp = $data[0];
			$response[$module] = array("data" => $responseData, "timestamp" => $timestamp); // Populating response array
		}
	}
}

// Sending data to API server
$response = $loadavg->sendApiData($response);

// Displaying API server response
echo $response . PHP_EOL;