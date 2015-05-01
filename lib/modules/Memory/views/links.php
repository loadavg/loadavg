<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Hardware/CPU links
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
	$links = LoadModules::getRangeLinks();


	//get settings for this module
	$modSettings = LoadModules::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];

	//if we are changing mode
	if  ( isset($_GET['memorymode']) || !empty($_GET['memorymode']))  {

		$newmode = $_GET['memorymode'];

		switch ( $newmode) {
			case "true": 	$mydata['settings']['display_limiting'] = "true";
						$mergedsettings = LoadUtility::ini_merge ($modSettings, $mydata);
						LoadUtility::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;

			case "false": 	$mydata['settings']['display_limiting'] = "false";
						$mergedsettings = LoadUtility::ini_merge ($modSettings, $mydata);
						LoadUtility::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $links = $links . "memorymode=false"; break;
			case "false": $links = $links . "memorymode=true"; break;
		}
	}


?>

<strong>Memory usage in</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'MB' : '%'; ?></a>

