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

$settings = LoadAvg::$_settings->general;

$settings_file = APP_PATH . '/config/settings.ini';

require_once APP_PATH . '/layout/header.php';

if ( isset( $_GET['check'] ) ) {
	if ( $loadavg->checkWritePermissions( $settings_file ) ) {

		//try to delete installer...
		if ( $loadavg->checkInstaller() ) {
			header("Location: index.php");
		} 
		else 
		{

			//try to delete installer first if we have permissions

			$installer_file = HOME_PATH . "/install/index.php";
			$installer_loc = HOME_PATH . "/install/";

			unlink($installer_file);
			rmdir($installer_loc);

			if ( $loadavg->checkInstaller() ) {
				header("Location: index.php");
			}
			else
			{ 
			?>

			<div class="well">
				<h3>Secure your installation!</h3>

					<p>For security reasons, you need to delete the <span class="label label-info">/install</span> folder 
					before you can run LoadAvg<br> 
					<br>
					To do this go to the location you installed LoadAvg and type:<br>
					<br>
					<span class="label label-info">rm -rf install</span>
					<br><br>
					LoadAvg will not run until this has been done.
					<br><br>
					After you have removed the install folder hit <span class="label label-info">Check again</span> 
				to login<br><br>
				</p>
				<button class="btn btn-primary" onclick="location.reload();">Check again!</button>
			</div>
			
			<!--
			<script>alert("PLEASE REMOVE install.php FROM YOUR /public FOLDER!");</script>
			-->

			<?php
			require_once APP_PATH . '/layout/footer.php'; 
			exit;
			}
		}
	} else {
		header("Location: " . SCRIPT_ROOT . "/install/index.php?step=1");
	}
} else {
	$loadavg->checkInstall();
}


/* 
 * Create first log files for all active modules 
 * only executes if there are no log files
 */

$loadavg->createFirstLogs();

/* 
 * Set the current period
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

$loaded = LoadAvg::$_settings->general['modules']; 
$logdir = APP_PATH . '/../logs/';

if ($settings['allow_anyone'] == "false" && !$loadavg->isLoggedIn()) {
	include( APP_PATH . '/views/login.php');
} else {
	if (isset($_GET['page']) && file_exists( APP_PATH . '/views/' . $_GET['page'] . '.php' ) ) {
		if ( !$loadavg->isLoggedIn() && $_GET['page'] == "settings" && (isset($settings['allow_anyone']) && $settings['allow_anyone'] == "true")) {
			if (!$loadavg->isLoggedIn()) { include( APP_PATH . '/views/login.php'); }
		} else {
			require_once APP_PATH . '/views/' . $_GET['page'] . '.php';
		}
	} else {
		require_once APP_PATH . '/views/index.php';
	}
}

/*
 * finish polling time to generate charts
 */

$loadavg->setFinishTime(); // Setting page load finish time

$page_load = $loadavg->getPageLoadTime(); // Calculating page load time


require_once APP_PATH . '/layout/footer.php'; ?>