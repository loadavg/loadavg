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

	if ( $chart->type == "Transmit") {
?>
	<strong>Mysql transmit data MB</strong>
<?php
	}

	if ( $chart->type == "Receive") {
?>
	<strong>Mysql receive data MB</strong>

<?php

	}
?>

<?php

	if ( $chart->type == "Queries") {
?>
	<strong>Mysql queries</strong>
<?php
	}
?>