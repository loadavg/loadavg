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

/*
The API so far:

- the client collects data for the enabled modules / plugins
- and then sends this data to the server which logs it into a simple mysql DB

- this file grabs the data for the current period
- sends it to the sendApiData method in lib/class.LoadAvg.php
- which then pushes it via cURL to the server
- 
*/

// initialize LoadAvg and grab data

require_once dirname(__FILE__) . '/globals.php'; // including required globals
include 'class.LoadAvg.php'; // including Main Controller

$loadavg = new LoadAvg(); // Initializing Main Controller
$loaded = LoadAvg::$_settings->general['modules']; // Loaded modules
$logdir = APP_PATH . '/../logs/'; // path to logfiles folder

$response = array();

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
			$responseData = $class->$caller('api'); // call data gethering function of module
			$data = explode("|", $responseData); // parsing response data
			$timestamp = $data[0];
			$response[$module] = array("data" => $responseData, "timestamp" => $timestamp); // Populating response array
		}
	}
}

// want to see what we are sending over ?
//var_dump($response); exit;

// Sending data to API server
$response = $loadavg->sendApiData($response);

// Displaying API server response
echo $response . PHP_EOL;