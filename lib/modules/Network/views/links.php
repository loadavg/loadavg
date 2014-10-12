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
/*
	//clean up links first
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

	//will need to know if we are in transmit or receive for a device
	//as they can work independently
	//and need to work acorss multiple devices!


	//get settings for this module
	//$cpuSettings = LoadAvg::$_settings->$module;

	//get the displaymode setting from the settings subsection for this module
	//$thedata = $cpuSettings['settings']['displaymode'];

/*
	//if we are changing mode
	if  ( isset($_GET['diskmode']) || !empty($_GET['diskmode']))  {

		$newmode = $_GET['diskmode'];

		switch ( $newmode) {
			case "1": 	$mydata['settings']['displaymode'] = "true";
						$mergedsettings = LoadAvg::ini_merge ($cpuSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;

			case "2": 	$mydata['settings']['displaymode'] = "false";
						$mergedsettings = LoadAvg::ini_merge ($cpuSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $links);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $links = $links . "diskmode=2"; break;
			case "false": $links = $links . "diskmode=1"; break;
		}
	}
*/

$links ="/";
$thedata ='true';
?>

<strong>Data displayed</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'fitted' : 'fixed'; ?></a>

