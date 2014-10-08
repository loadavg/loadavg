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

//locate the PHP binary
$php_location = PHP_BINDIR . "/php";

?>


<?php
if (!$loadavg->isLoggedIn() && !LoadAvg::checkInstall()) {
	include('login.php');
}
else {
?>

<!-- 
	need to create a function to test if the logger is running here 
	and update STATUS with the status
	Also want to add a button to test the logger and display results in a dialog
-->

<div class="well lh70-style">
    <b>LoadAvg Logger</b>
    <div class="pull-right">

	<?php
	$logger_Status = false;
	$logger_Status = $loadavg->testLogs(false);
	?>
	Logger Status:
	<?php   
	if ($logger_Status == true) {
		echo "<strong>Running</strong>";
	} else {
		echo "<strong>Not Running</strong>";
	}
	?>
    
    </div>
</div>

<div class="innerAll">

			<div class="well">

					<b>Logging in LoadAvg <?php echo $settings['version']; ?></b>
					<br><br>
					<p>
					LoadAvg records log data at the system level using a file called logger.php that is located in the root of your LoadAvg installation. 
					For it to function correctly you need to you need to set up a cron job that runs the logger every 5 minutes.
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
					<span class="label label-info">*/5 * * * * <?php echo $php_location; ?> -q <?php echo dirname(APP_PATH); ?>/logger.php /dev/null 2>1</span>
					</p>

					<b>Testing the logger</b>
					<br><br>					
					<p>You can test the logger by running following command at the command line as root or superuser:<br>
					<br>
					<span class="label label-info"><?php echo $php_location; ?> <?php echo dirname(APP_PATH); ?>/logger.php status</span>
					<br><br>					
					<p>If there are no errors then you are all set to go.<br>
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
