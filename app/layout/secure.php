<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Secure your installation
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>	

<div class="well">
	<h3>Secure your installation!</h3>

	<p>
		For security reasons, you need to delete the <span class="label label-info">/install</span> folder 
		before you can run LoadAvg<br> 
		<br>
		To do this go to the location you installed LoadAvg and type:<br>
		<br>
		<span class="label label-info">rm -rf install</span>
		<br><br>
		LoadAvg will not run until this has been done.
		<br><br>
		After you have removed the install folder hit <span class="label label-info">Check again</span> 
	to login<br><br>
	</p>
	<button class="btn btn-primary" onclick="location.reload();">Check again!</button>
</div>
	

