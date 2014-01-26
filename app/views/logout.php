<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* logout module interface
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>

<?php
LoadAvg::logOut();
header('Location: index.php');
?>