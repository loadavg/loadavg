<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network charts derived from views/chart.php
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


<?php if ( $chartData ) { ?>				
<ul class="unstyled">
	<?php
	foreach ($chartData['info']['line'] as $line) {
		switch ($line['type']) {
			case "file":
				echo '<li>'; include $line['file']; echo '</li>';
				break;
			case "line":
				echo '<li>' . $line['formatted_line'] . '</li>';
		}
	}
	?>
</ul>
<?php } ?>	


