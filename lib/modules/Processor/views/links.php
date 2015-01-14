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

$processor_mode = "";

switch ( ( isset($_GET['processor']) || !empty($_GET['processor'])) ? $_GET['processor'] : '1' ) {
	case "2": $processor_mode = "User"; $processor = 2; break;
	case "3": $processor_mode = "Nice"; $processor = 3; break;
	case "4": $processor_mode = "Sys"; $processor = 4; break;
	default:
	case "1":
		$processor_mode = "All"; $processor = 1; break;
}


	//get date range links for header here
	$links = loadModules::getRangeLinks();

	$displaylinks = $links;


	//get settings for this module
	$modSettings = loadModules::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];


	//if we are changing mode
	if  ( isset($_GET['processormode']) || !empty($_GET['processormode']))  {

		$newmode = $_GET['processormode'];

		switch ( $newmode) {
			case "true": 	$mydata['settings']['display_limiting'] = "true";
						$mergedsettings = loadModules::ini_merge ($modSettings, $mydata);
						loadModules::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;

			case "false": 	$mydata['settings']['display_limiting'] = "false";
						$mergedsettings = loadModules::ini_merge ($modSettings, $mydata);
						loadModules::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $displaylinks = $displaylinks . "processormode=false"; break;
			case "false": $displaylinks = $displaylinks . "processormode=true"; break;
		}
	}






?>

<?php echo $processor_mode ?> CPU usage</strong>
<p>
<a href="<?php echo $links; ?>processor=1" class="<?php echo ($processor == '1') ? 'strong' : ''; ?>">All</a> | 
<a href="<?php echo $links; ?>processor=2" class="<?php echo ($processor == '2') ? 'strong' : ''; ?>">User</a> | 
<a href="<?php echo $links; ?>processor=3" class="<?php echo ($processor == '3') ? 'strong' : ''; ?>">Nice</a> | 
<a href="<?php echo $links; ?>processor=4" class="<?php echo ($processor == '4') ? 'strong' : ''; ?>">Sys</a>
</p>

<?php

// need to add links to displaylinks when both are used!

?>
<strong>Data display</strong> <a href="<?php echo $displaylinks; ?>"><?php echo ($thedata == 'true') ? 'fixed' : 'fitted'; ?></a>
