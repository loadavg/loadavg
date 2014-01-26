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


require_once dirname(__FILE__) . '/globals.php'; // including required globals
include 'class.LoadAvg.php'; // including Main Controller

$loadavg = new LoadAvg(); // Initializing Main Controller
$loaded = LoadAvg::$_settings->general['modules']; // Loaded modules
$logdir = APP_PATH . '/../logs/'; // path to logfiles folder

// Delete old logs
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

// Check for each module we have loaded
foreach ( $loaded as $module => $value ) {
	if ( $value == "false" ) continue;
	$moduleSettings = LoadAvg::$_settings->$module;
	// Check if loaded module needs loggable capabilities
	if ( $moduleSettings['module']['logable'] == "true" ) {
		foreach ( $moduleSettings['logging']['args'] as $args) { // loop trought module logging arguments
			$args = json_decode($args); // decode arguments
			$class = LoadAvg::$_classes[$module]; // load module information
			$caller = $args->function;
			$class->logfile = $logdir . $args->logfile; // the modules logfile
			$class->$caller(); // call data gethering function of module
		}
	}
}
