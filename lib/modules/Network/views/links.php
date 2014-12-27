<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network module links
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>


<?php

	//clean up links first
/*
	if (
		(isset($_GET['minDate']) && !empty($_GET['minDate'])) &&
		(isset($_GET['maxDate']) && !empty($_GET['maxDate'])) &&
		(isset($_GET['logdate']) && !empty($_GET['logdate']))
		) {
		$links = "?minDate=" . $_GET['minDate'] . "&maxDate=" . $_GET['maxDate'] . "&logdate=" . $_GET['logdate'] ."&";
	} elseif (
		(isset($_GET['logdate']) && !empty($_GET['logdate']))
		) {
		$links = "?logdate=" . $_GET['logdate'] . "&";
	} else {
		$links = "?";
	}
*/
	//get date range links for header here
	$links = LoadAvg::getRangeLinks();

	//get settings for this module
	$modSettings = LoadAvg::$_settings->$module;


	//currently we dont store settings per network interface but we need to!
	//settings are across all network interfaces however we are coded her as if its possible!
	// would need something like the following in the ini files
	// 		$mydata['settings' . $interface ]['transfer_limiting'] = "true";

	if ( $chart->type == "Transmit") {

		//get the displaymode setting from the settings subsection for this module
		$thedata = $modSettings['settings']['transfer_limiting'];

		//if we are changing mode
		if  ( isset($_GET[$interface . 'transfermode']) || !empty($_GET[$interface . 'transfermode']))  {

			$newmode = $_GET[$interface . 'transfermode'];

			switch ( $newmode) {
				case "true": 	$mydata['settings']['transfer_limiting'] = "true";
							$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;

				case "false": 	$mydata['settings']['transfer_limiting'] = "false";
							$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;
			}		
		} else {

			//if not build the links
			switch ( $thedata) {
				case "true": $links = $links . $interface ."transfermode=false"; break;
				case "false": $links = $links . $interface ."transfermode=true"; break;
			}
		}

	}

	if ( $chart->type == "Receive") {

		//get the displaymode setting from the settings subsection for this module
		$thedata = $modSettings['settings']['receive_limiting'];

		//if we are changing mode
		if  ( isset($_GET[$interface . 'receivemode']) || !empty($_GET[$interface . 'receivemode']))  {

			$newmode = $_GET[$interface . 'receivemode'];

			switch ( $newmode) {
				case "true": 	$mydata['settings']['receive_limiting'] = "true";
							$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;

				case "false": 	$mydata['settings']['receive_limiting'] = "false";
							$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;
			}		
		} else {

			//if not build the links
			switch ( $thedata) {
				case "true": $links = $links . $interface . "receivemode=false"; break;
				case "false": $links = $links . $interface . "receivemode=true"; break;
			}
		}

	}

?>

<strong><?php echo $chart->type; ?> data display</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'fixed' : 'fitted'; ?></a>

