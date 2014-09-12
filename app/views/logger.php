<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Settings module interface
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
if (!$loadavg->isLoggedIn() && !LoadAvg::checkInstall()) {
	include('login.php');
}
else {
?>


<div class="well lh70-style">
    <b>LoadAvg Logger</b>
    <div class="pull-right">
		STATUS
    </div>
</div>

<div class="innerAll">

			<div class="well">

					<b>Logging in LoadAvg <?php echo $settings['version']; ?></b>
					<br><br>
					<p>
					LoadAvg records log data at the system level using a logger. For it to function correctly 
					you need to you need to set up the logger.
					</p>

					<b>To set up logging</b>
					<br><br>					
					<p>Edit your crontab by executing the following command at the command line as root or superuser:<br>
					<br>
					<span class="label label-info">crontab -e</span>
					<br>
					<br>
					It should have opened up your crontab in your editor, insert this line and save your changes<br>
					<br>
					<span class="label label-info">*/5 * * * * /usr/bin/php -q <?php echo dirname(APP_PATH); ?>/logger.php /dev/null 2>1</span>
					</p>

					<b>Need help ?</b>
					<br><br>					
					<p>To get help setting up your logger you can refer to the LoadAvg knowledgebase at<br>
					<br>
					<a href="http://www.loadavg.com/kb/logging/" target="new">http://www.loadavg.com/kb/logging/</a><br>
					<br>
					</p>

			</div>
			</div>

<?php } ?>
