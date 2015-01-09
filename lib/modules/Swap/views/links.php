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
	$links = LoadAvg::getRangeLinks();	

	//get settings for this module
	$modSettings = LoadAvg::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];

	//if we are changing mode
	if  ( isset($_GET['swapmode']) || !empty($_GET['swapmode']))  {

		$newmode = $_GET['swapmode'];

		switch ( $newmode) {
			case "true": 	$mydata['settings']['display_limiting'] = "true";
						$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;

			case "false": 	$mydata['settings']['display_limiting'] = "false";
						$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $links = $links . "swapmode=false"; break;
			case "false": $links = $links . "swapmode=true"; break;
		}
	}


?>

<strong>Swap usage in</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'MB' : '%'; ?></a>

