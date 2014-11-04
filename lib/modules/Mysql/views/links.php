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
	// draw chart labels here

	if ( $chart->type == "Transmit") {
	?>
		<strong>Mysql transmit data MB</strong><br>
	<?php
	}

	if ( $chart->type == "Receive") {
	?>
		<strong>Mysql receive data MB</strong><br>
	<?php
	}

	if ( $chart->type == "Queries") {
	?>
		<strong>Showing Mysql queries</strong><br>
	<?php
	}
?>

<?php

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


	//get settings for this module
	$mysqlSettings = LoadAvg::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $mysqlSettings['settings']['show_queries'];

	//if we are changing mode
	
	if  ( isset($_GET['mysqlmode']) || !empty($_GET['mysqlmode']))  {

		$newmode = $_GET['mysqlmode'];

		switch ( $newmode) {
			case "true": 	$mydata['settings']['show_queries'] = "true";
							$mergedsettings = LoadAvg::ini_merge ($mysqlSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;

			case "false": 	$mydata['settings']['show_queries'] = "false";
							$mergedsettings = LoadAvg::ini_merge ($mysqlSettings, $mydata);
							LoadAvg::write_module_ini($mergedsettings, $module);
							header("Location: " . $links);						
							break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $links = $links . "mysqlmode=false"; break;
			case "false": $links = $links . "mysqlmode=true"; break;
		}
	}

	if ( $chart->type == "Transmit" || $chart->type == "Queries") 
	{
	?>
		<a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'View bandwidth' : 'View queries'; ?></a>
	<?php
	}

?>








