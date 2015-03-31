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


	//get date range links for header here
	$links = LoadModules::getRangeLinks();

	//$displaylinks = $links;


	//get settings for this module
	$modSettings = LoadModules::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];


	//if we are changing mode
	if  ( isset($_GET['loadmode']) || !empty($_GET['loadmode']))  {

		$newmode = $_GET['loadmode'];

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
			case "true": $links = $links . "loadmode=false"; break;
			case "false": $links = $links . "loadmode=true"; break;
		}
	}






?>

<?php echo $load_mode ?> Hash average</strong>
<p>
<a href="<?php echo $links; ?>load=1" class="<?php echo ($load == '1') ? 'strong' : ''; ?>">1 min</a> | 
<a href="<?php echo $links; ?>load=2" class="<?php echo ($load == '2') ? 'strong' : ''; ?>">5 min</a> | 
<a href="<?php echo $links; ?>load=3" class="<?php echo ($load == '3') ? 'strong' : ''; ?>">15 min</a>
</p>

<?php

// need to add links to $links when both are used!

?>
<strong>Data display</strong> <a href="<?php echo $links; ?>"><?php echo ($thedata == 'true') ? 'fixed' : 'fitted'; ?></a>
