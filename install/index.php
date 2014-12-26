<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* LoadAvg Installer
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

require_once '../globals.php'; // including required globals

/* Session */
ob_start(); 

include 'class.LoadAvg.php'; // including Main Controller

$loadavg = new LoadAvg(); // Initializing Main Controller
$settings = LoadAvg::$_settings->general; // Default settings


$flooding = false;

// Including header view
require_once APP_PATH . '/layout/header.php'; 

//check for settings
$settings_file = APP_PATH . '/config/settings.ini.php'; // path to settings INI file

//read some system data
$settingsActive = $loadavg->checkWritePermissions( $settings_file );
$logStatus = $loadavg->testLogs(false);

//check if any data has been passed in
$step = 1;
if ( isset($_GET['step']) ) 
	$step = $_GET['step']; // if step argument exists set the step

//if (isset($_GET['step'])) ? $step=1; : $step = $_GET['step'];

//forceinstall is used to force a install when script has detected an upgrade
$forceInstall = false;
if ( isset($_GET['forceinstall']))	
	$forceInstall = true;

//see if we can get the version number
if ( isset($settings['settings']['version'])) 
	$version = $settings['settings']['version']; 

//if script is newer than stored version its a upgrade
$upgrade = false;
if ( (float)SCRIPT_VERSION > (float)$version )
{
	if ( ($settingsActive && $logStatus && !$forceInstall) ) {
		$upgrade = true;
	}
}

//if its a upgrade jump to upgrade portion
if ( $upgrade )
{
	if ($step != 5)
		$step = 4;
}

?>


<div class="innerAll">
<?php
switch ( $step )
{
	default:
	case 1: // Checking for permissions. If we have permissions to write go to step 2 if nut display message with information on
			// how to run the configuration tool and reload the page so the check runs again...
		if ( !$settingsActive )
		{
			?>
			<h4>Installation: Step 1</h4>
			<div class="well">
				<b>There is a problem with your permissions</b>
				
				<p>In order to properly install LoadAvg <?php echo $settings['settings']['version']; ?> on your server you need to give the script write permissions to some file(s)</p>
				<p>Please run <span class="badge badge-info">chmod 777 configure</span> and then <span class="badge badge-info">./configure</span> from the console. And click the <b>Retry</b> button</p>

				<button class="btn btn-primary" onclick="location.reload();">Retry</button>
			</div>
			<?php
		} else {
			$location = "Location: index.php?step=2";
			if ($forceInstall) 
				$location += "&forceinstall=1"; 

			header( $location ); // redirecting to step 2
		}
		break;


	case 2: // Configuration: Username, password, Site name, Check for updates
		if ( $settingsActive ) {
		?>
		<h4>Installation: Step 2</h4>
		<div class="well">
			<form class="form-horizontal">
				<input type="hidden" name="step" value="3">

				<?php if ($forceInstall) { ?>
				<input type="hidden" name="forceinstall" value="1">
				<?php } ?>

				<div class="control-group">
					<label class="control-label" for="inputSiteName">Server name</label>
					<div class="controls">
					<?php  
						// Get hostname for default site name
						$settings['settings']['title']=gethostname(); 
					?>

						<input type="text" id="inputSiteName" name="title" value="<?php echo $settings['settings']['title']; ?>" placeholder="Site name">
						<span class="help-block">Set the server name</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputUsername">Username or email address</label>
					<div class="controls">
						<input type="text" id="inputUsername" name="username" placeholder="Username">
						<span class="help-block">Please type in your desired username</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputPassword">Password</label>
					<div class="controls">
						<input type="password" id="inputPassword" name="password" placeholder="Password">
						<span class="help-block">Please type in your desired password</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputPassword2">Re-type password</label>
					<div class="controls">
						<input type="password" id="inputPassword2" name="password2" placeholder="Re-type password">
						<span class="help-block">Please re-type in your desired password to check if they match</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputUpdate">Automatically check for update(s).</label>
					<div class="controls">
						<input type="checkbox" id="inputUpdate" name="checkforupdates" checked="checked">
						<span class="help-block">Please check this checkbox if you want to automatically check for new update(s).</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputUpdate">Force HTTPS.</label>
					<div class="controls">
						<input type="checkbox" id="inputUpdate" name="https">
						<span class="help-block">Please check this checkbox if you want to force secure connections.</span>
					</div>
				</div>

				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Proceed</button>
						<button type="reset" class="btn btn-warning">Reset</button>
					</div>
				</div>
			</form>
		</div>

		<?php
		} else {
			// If user comes directly to second step without write permissions or running the configuration tool
			// redirect to the first step!
			header("Location: ?step=1");
		}
		break;


	case 3: // Complete the installation, default settings saved you can now remove the install.php from your public folder
		if ( $settingsActive ) {
			$errorMsg = '';

			$settings['settings']['title'] = ( isset( $_GET['title'] ) && !empty( $_GET['title'] ) ) ? $_GET['title'] : $errorMsg .= '<li>Title not set!</li>';
			$settings['settings']['username'] = ( isset( $_GET['username'] ) && !empty( $_GET['username'] ) ) ? $_GET['username'] : $errorMsg .= '<li>Username not set!</li>';
			$settings['settings']['password'] = ( isset( $_GET['password'] ) && !empty( $_GET['password'] ) ) ? $_GET['password'] : $errorMsg .= '<li>Password not set!</li>';
			$settings['settings']['checkforupdates'] = ( isset( $_GET['checkforupdates'] ) && !empty( $_GET['checkforupdates'] ) ) ? "true" : "false";
			$settings['settings']['https'] = ( isset( $_GET['https'] ) && !empty( $_GET['https'] ) ) ? "true" : "false";
			$password2 = ( isset( $_GET['password2'] ) && !empty( $_GET['password2'] ) ) ? $_GET['password2'] : $errorMsg .= '<li>Re-typed password not set!</li>';

			$settings['network_interface'] = array();
			
			$match = ( $settings['settings']['password'] == $password2 ) ? true : $errorMsg .= '<li>Passwords do not match!</li>';
			$settings['settings']['password'] = md5($settings['settings']['password']);

			if (ini_get('date.timezone')) {
		    	$settings['settings']['timezone']=ini_get('date.timezone'); 
			}
			?>

				<?php
				if ( strlen( $errorMsg ) > 0) {
				?>

					<h4>Installation Errors</h4>
					<div class="well">

					<h3>Error(s).</h3>
					<ul>
						<?php echo $errorMsg; ?>
					</ul>
					<?php
				} else { ?>

					<h4>Installation Complete</h4>
					<div class="well">

					<?php
					// write settings file out
					//var_dump($settings);
					$loadavg->write_php_ini( $settings, $settings_file);
					$fh = fopen($settings_file, "a"); fwrite($fh, "\n"); fclose($fh);
					?>

					<b>Thank you for installing LoadAvg <?php echo $settings['settings']['version']; ?></b>
					<br><br>
					<p>
					LoadAvg records log data at the system level. For it to function correctly 
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

					<?php
				}
				?>
				<?php
				if ( strlen( $errorMsg ) > 0) {
					?><a class="btn btn-primary" href="?step=2<?php if ($forceInstall) echo '&forceinstall=1'; ?>">Go back!</a><?php	
				} else {
					?><a class="btn btn-primary" href="<?php echo SCRIPT_ROOT ?>public/index.php?check=1">Continue</a><?php
				}
				?>
			</div>
			<?php
		} else {
			header("Location: ?step=1");
		}
		break;


	case 4: // this is a upgrade - for now we just clean up after us when we upgrade
		if ( $settingsActive )
		{
			?>
			<h4>Upgrade</h4>
			<div class="well">
				<b>This appears to be a upgrade</b>
				<br><br>
				Select if you want to upgrade now
				<br><br>
				<button class="btn btn-primary" onclick="location.href='index.php?step=5'">Upgrade</button>
				<br><br>
				or if you want to run the installer again
				<br><br>
				<button class="btn btn-primary" onclick="location.href='index.php?step=2&forceinstall=1'">Install</button>



			</div>
			<?php
		} else {
			header("Location: index.php?step=2"); // redirecting to step 2
		}
		break;

	case 5: // this is a upgrade - for now we just clean up after us when we upgrade
		if ( $settingsActive )
		{
			?>
			<h4>Upgrade Complete</h4>
			<div class="well">
				<b>The upgrade is now complete</b>
				<br><br>
				Go on to LoadAvg


				<?php
				if ( isset($settings['settings']['version'])) 
					$settings['settings']['version'] = SCRIPT_VERSION;

				//var_dump($settings);
				$loadavg->write_php_ini( $settings, $settings_file);
				$fh = fopen($settings_file, "a"); fwrite($fh, "\n"); fclose($fh);
				?>

				<br><br>
				<a class="btn btn-primary" href="<?php echo SCRIPT_ROOT ?>public/index.php?check=1">Continue</a>
			</div>
			<?php
		} else {
			header("Location: index.php?step=2"); // redirecting to step 2
		}
		break;


}
?>
</div>

<?php
// Including the footer view
require_once APP_PATH . '/layout/footer.php'; 
?>
