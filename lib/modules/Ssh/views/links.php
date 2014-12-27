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

$ssh_mode = "";

switch ( ( isset($_GET['ssh']) || !empty($_GET['ssh'])) ? $_GET['ssh'] : '1' ) {
	case "2": $ssh_mode = "Failed"; $ssh = 2; break;
	case "3": $ssh_mode = "Invalid"; $ssh = 3; break;
	default:
	case "1":
		$ssh_mode = "Accepted"; $ssh = 1; break;
}

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

	//can we kill this now ? as per CPU ?
	$displaylinks = $links;


	//get settings for this module
	$modSettings = LoadAvg::$_settings->$module;

	//get the display_limiting setting from the settings subsection for this module
	$thedata = $modSettings['settings']['display_limiting'];


	//if we are changing mode
	if  ( isset($_GET['sshmode']) || !empty($_GET['sshmode']))  {

		$newmode = $_GET['sshmode'];

		switch ( $newmode) {
			case "true": 	$mydata['settings']['display_limiting'] = "true";
						$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;

			case "false": 	$mydata['settings']['display_limiting'] = "false";
						$mergedsettings = LoadAvg::ini_merge ($modSettings, $mydata);
						LoadAvg::write_module_ini($mergedsettings, $module);
						header("Location: " . $displaylinks);						
						break;
		}		
	} else {

		//if not build the links
		switch ( $thedata) {
			case "true": $displaylinks = $displaylinks . "sshmode=false"; break;
			case "false": $displaylinks = $displaylinks . "sshmode=true"; break;
		}
	}
?>


<?php
if ($thedata == 'true') {
?>

<strong><?php echo $ssh_mode ?> SSH usage data</strong>
<p>
<a href="<?php echo $links; ?>ssh=1" class="<?php echo ($ssh == '1') ? 'strong' : ''; ?>">Accepted</a> | 
<a href="<?php echo $links; ?>ssh=2" class="<?php echo ($ssh == '2') ? 'strong' : ''; ?>">Failed</a> | 
<a href="<?php echo $links; ?>ssh=3" class="<?php echo ($ssh == '3') ? 'strong' : ''; ?>">Invalid</a> 
</p>

<?php
} else {
?>
<strong>SSH usage data</strong>
<br>
<?php
}
?>
<strong>Show</strong> <a href="<?php echo $displaylinks; ?>"><?php echo ($thedata == 'true') ? 'All data' : 'Individual data'; ?></a>






	