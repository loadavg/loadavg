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

	//get date range links for header here
	$links = LoadModules::getRangeLinks();

	//get settings for this module
	$modSettings = LoadModules::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];

	//if we are changing mode
	if  ( isset($_GET['diskmode']) || !empty($_GET['diskmode']))  {

		$newmode = $_GET['diskmode'];

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
			case "true": $links = $links . "diskmode=false"; break;
			case "false": $links = $links . "diskmode=true"; break;
		}
	}


?>

<strong>Disk usage in</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'MB' : '%'; ?></a>

