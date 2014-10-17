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

<strong>
<?php

$load_mode = "";

switch ( ( isset($_GET['load']) || !empty($_GET['load'])) ? $_GET['load'] : '2' ) {
	case "1": $load_mode = "1 min"; $load = 1; break;
	case "3": $load_mode = "15 min"; $load = 3; break;
	default:
	case 2:
		$load_mode = "5 min"; $load = 2; break;
}

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

	$displaylinks = $links;


	//get settings for this module
	$cpuSettings = LoadAvg::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $cpuSettings['settings']['display_limiting'];


	//if we are changing mode
	if  ( isset($_GET['loadmode']) || !empty($_GET['loadmode']))  {

		$newmode = $_GET['loadmode'];

		switch ( $newmode) {
			case "1": 	$mydata['settings']['display_limiting'] = "true";
						$mergedsettings = LoadAvg::ini_merge ($cpuSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;

			case "2": 	$mydata['settings']['display_limiting'] = "false";
						$mergedsettings = LoadAvg::ini_merge ($cpuSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $displaylinks = $displaylinks . "loadmode=2"; break;
			case "false": $displaylinks = $displaylinks . "loadmode=1"; break;
		}
	}






?>

<?php echo $load_mode ?> load average</strong>
<p>
<a href="<?php echo $links; ?>load=1" class="<?php echo ($load == '1') ? 'strong' : ''; ?>">1 min</a> | 
<a href="<?php echo $links; ?>load=2" class="<?php echo ($load == '2') ? 'strong' : ''; ?>">5 min</a> | 
<a href="<?php echo $links; ?>load=3" class="<?php echo ($load == '3') ? 'strong' : ''; ?>">15 min</a>
</p>

<?php

// need to add links to displaylinks when both are used!

?>
<strong>Data display</strong> <a href="<?php echo $displaylinks; ?>"><?php echo ($thedata == 'true') ? 'fixed' : 'fitted'; ?></a>
