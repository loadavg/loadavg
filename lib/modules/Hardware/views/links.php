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
switch ( ( isset($_GET['load']) || !empty($_GET['load'])) ? $_GET['load'] : '2' ) {
	case "1": echo "1 min"; $load = 1; break;
	case "3": echo "15 min"; $load = 3; break;
	default:
	case 2:
		echo "5 min"; $load = 2; break;
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
?>
 load average</strong>
<p>
<a href="<?php echo $links; ?>load=1" class="<?php echo ($load == '1') ? 'strong' : ''; ?>">1 min</a> | 
<a href="<?php echo $links; ?>load=2" class="<?php echo ($load == '2') ? 'strong' : ''; ?>">5 min</a> | 
<a href="<?php echo $links; ?>load=3" class="<?php echo ($load == '3') ? 'strong' : ''; ?>">15 min</a>
</p>

