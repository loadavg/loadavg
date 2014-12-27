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
/*
 * Security wrapper for private pages
 *
 */

if (!$loadavg->isLoggedIn() && !LoadAvg::checkInstall()) {
	include('login.php');
}
else {
?>

	<?php
	if (isset($_POST['Run_Logger'])) {

		$loadavg->rebuildLogs();

		/* force reload settings page now */
		header('Location: '. '/public/index.php?page=settingslogger'  );
    }

	if (isset($_POST['update_settings'])) {

		/////////////////////////////////////////////////////////////////////
		//updates the general settings here as api settings are stored there

		// Loop throught settings
		$settings_file = APP_PATH . '/config/' . LoadAvg::$settings_ini;
		
		$settings = LoadAvg::$_settings->general;

		$postsettings = $_POST['formsettings'];

		//$mergedsettings = LoadAvg::ini_merge ($settings, $postsettings);
		$replaced_settings = array_replace($settings, $postsettings);

		//LoadAvg::write_php_ini($mergedsettings, $settings_file);
		LoadAvg::write_php_ini($replaced_settings, $settings_file);

		/* force reload settings page now */

		//echo 'URI ' . $_SERVER['REQUEST_URI'] ;
    	header('Location: '. strtok($_SERVER["REQUEST_URI"],'&') );

	}

	//locate php binary for logger location text below
	$php_location = PHP_BINDIR . "/php";
		
	?>

	<div class="innerAll">

		<div class="well">
		  		<h4>Logger</h4>
		</div>

	  	<div class="separator bottom"></div>

		<div class="well">

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

			<b>Logging in LoadAvg <?php echo $settings['settings']['version']; ?></b>
			<br><br>
			<p>
			LoadAvg records log data at the system level using a file called logger.php that is located in the root of your LoadAvg installation. 
			For it to function correctly you need to you need to set up a cron job that runs the logger according to the logger interval, default is every 5 minutes.
			</p>

			<form action="" method="post">

				<input type="hidden" name="update_settings" value="1" />

		    	<div class="row-fluid">
		    		<div class="span3">
		    			<strong>Days to keep</strong>
		    		</div>
		        	<div class="span9 right">
		        		<input type="text" name="formsettings[settings][daystokeep]" value="<?php echo $settings['settings']['daystokeep']; ?>" size="4" class="span6 left">
		        	</div>
		    	</div>

		    	<div class="row-fluid">
		    		<div class="span3">
		    			<strong>Logger interval</strong>
		    		</div>
		    		<div class="span9 right">
		        		<input type="text" name="formsettings[settings][logger_interval]" value="<?php echo $settings['settings']['logger_interval']; ?>" size="4" class="span6 left">
		        	</div>
		    	</div>

				<div class="separator bottom"></div>

			    <div class="panel">
			    	<input type="submit" class="btn btn-primary pull-left" value="Save Settings" name="Save Settings">
			    </div>

			    <div class="panel">
			    	<input type="submit" class="btn btn-primary pull-right" value="Run Logger" name="Run Logger">
			    </div>
			    
				<div class="separator bottom"></div>

			</form>

		</div>


		<div class="separator bottom"></div>

		<div class="well">

			<b>To set up logging</b>
			<br><br>					
			<p>Edit your crontab by executing the following command at the command line as root or superuser:<br>
			<br>
			<span class="label label-info">crontab -e</span>
			<br>
			<br>
			It should have opened up your crontab in your editor, insert this line and save your changes<br>
			<br>
			<span class="label label-info">*/<?php echo $settings['settings']['logger_interval'] ?> * * * * <?php echo $php_location; ?> -q <?php echo dirname(APP_PATH); ?>/logger.php /dev/null 2>1</span>
			</p>

		</div>

		<div class="separator bottom"></div>

		<div class="well">

			<b>Testing the logger</b>
			<br><br>					
			<p>You can test the logger by running following command at the command line as root or superuser:<br>
			<br>
			<span class="label label-info"><?php echo $php_location; ?> <?php echo dirname(APP_PATH); ?>/logger.php status</span>
			<br><br>					
			<p>If there are no errors then you are all set to go.<br>
			</p>

		</div>

		<div class="separator bottom"></div>

		<div class="well">

			<b>Need help ?</b>
			<br><br>					
			<p>To get help setting up your logger you can refer to the LoadAvg knowledgebase at<br>
			<br>
			<a href="http://www.loadavg.com/kb/logging/" target="new">http://www.loadavg.com/kb/logging/</a><br>
			</p>

		</div>

	</div>

<?php } ?>
