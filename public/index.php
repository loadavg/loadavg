<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main index file
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

require_once '../globals.php';

/* Session */
ob_start(); 
session_start();

/* Initialize LoadAvg */ 
include 'class.LoadAvg.php';
$loadavg = new LoadAvg();

//grab core settings
$settings = LoadAvg::$_settings->general;



//draw the header
require_once APP_PATH . '/layout/header.php';

/* 
 * check for successful installation
 */	

//check if installation is complete passed over by installer
if ( isset( $_GET['check'] ) ) 
{
	$loadavg->cleanUpInstaller();
} else {
	//check installation has been cleaned up for security reasons
	$loadavg->checkInstall();
}

/* 
 * Grab the current period if a period has been selected
 */

if ( 
	( isset($_GET['minDate']) && !empty($_GET['minDate']) ) &&
	( isset($_GET['maxDate']) && !empty($_GET['maxDate']) )
	) 
{
	LoadAvg::$period = true;
	LoadAvg::$period_minDate = date("Y-m-d", strtotime($_GET['minDate']));
	LoadAvg::$period_maxDate = date("Y-m-d", strtotime($_GET['maxDate']));
}


/*
 * start polling time to generate charts
 */

$loadavg->setStartTime(); // Setting page load start time

/*
 * draw the current page view
 */

//array of modules and status either on or off
$loaded = LoadAvg::$_settings->general['modules']; 

//grab the log diretory
$logdir = HOME_PATH . '/logs/';

//security check for all access
if ( isset($settings['allow_anyone']) && $settings['allow_anyone'] == "false" && !$loadavg->isLoggedIn() ) 
{
	include( APP_PATH . '/views/login.php');
} 
else 
{
	//draw current page
	if (isset($_GET['page']) && file_exists( APP_PATH . '/views/' . $_GET['page'] . '.php' ) ) 
	{
		require_once APP_PATH . '/views/' . $_GET['page'] . '.php';
	} 
	else 
	{
		//if page doesnt exist redirect to index, can be modified to a page not found if need be
		require_once APP_PATH . '/views/index.php';
	}
}

/* 
 * finish polling time to generate charts
 */

// set page load finish time
$loadavg->setFinishTime(); 

// Calculating total page load time
$page_load = $loadavg->getPageLoadTime(); 

//draw the footer
require_once APP_PATH . '/layout/footer.php'; 
?>