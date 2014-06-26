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

require_once APP_PATH . '/layout/header.php'; // Including header view

if ( isset($_GET['step'])) {
	$step = $_GET['step']; // if step argument exists set the step
} else {
	$step = 1; // setting default step
}

$settings_file = APP_PATH . '/config/settings.ini'; // path to settings INI file
?>
<div class="innerAll">
<?php
switch ( $step )
{
	default:
	case 1: // Checking for permissions. If we have permissions to write go to step 2 if nut display message with information on
			// how to run the configuration tool and reload the page so the check runs again...
		if ( !$loadavg->checkWritePermissions( $settings_file ) )
		{
			?>
			<h4>Installation: Step 1</h4>
			<div class="well">
				<b>There is a problem with your permissions</b>
				
				<p>In order to properly install LoadAvg <?php echo $settings['version']; ?> on your server you need to give the script write permissions to some file(s)</p>
				<p>Please run <span class="badge badge-info">chmod 777 configure</span> and then <span class="badge badge-info">./configure</span> from the console. And click the <b>Retry</b> button</p>

				<button class="btn btn-primary" onclick="location.reload();">Retry</button>
			</div>
			<?php
		} else {
			header("Location: install.php?step=2"); // redirecting to step 2
		}
		break;
	case 2: // Configuration: Username, password, Site name, Check for updates
		if ( $loadavg->checkWritePermissions( $settings_file ) ) {
		?>
		<h4>Installation: Step 2</h4>
		<div class="well">
			<form class="form-horizontal">
				<input type="hidden" name="step" value="3">

				<div class="control-group">
					<label class="control-label" for="inputSiteName">Site name</label>
					<div class="controls">
						<input type="text" id="inputSiteName" name="title" value="<?php echo $settings['title']; ?>" placeholder="Site name">
						<span class="help-block">Change default site title</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="inputUsername">Username</label>
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
						<input type="checkbox" id="inputUpdate" name="checkforupdates">
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
		if ( $loadavg->checkWritePermissions( $settings_file ) ) {
			$errorMsg = '';

			$settings['title'] = ( isset( $_GET['title'] ) && !empty( $_GET['title'] ) ) ? $_GET['title'] : $errorMsg .= '<li>Title not set!</li>';
			$settings['username'] = ( isset( $_GET['username'] ) && !empty( $_GET['username'] ) ) ? $_GET['username'] : $errorMsg .= '<li>Username not set!</li>';
			$settings['password'] = ( isset( $_GET['password'] ) && !empty( $_GET['password'] ) ) ? $_GET['password'] : $errorMsg .= '<li>Password not set!</li>';
			$settings['checkforupdates'] = ( isset( $_GET['checkforupdates'] ) && !empty( $_GET['checkforupdates'] ) ) ? "true" : "false";
			$settings['https'] = ( isset( $_GET['https'] ) && !empty( $_GET['https'] ) ) ? "true" : "false";
			$password2 = ( isset( $_GET['password2'] ) && !empty( $_GET['password2'] ) ) ? $_GET['password2'] : $errorMsg .= '<li>Re-typed password not set!</li>';

			$settings['network_interface'] = array();
			
			$match = ( $settings['password'] == $password2 ) ? true : $errorMsg .= '<li>Passwords do not match!</li>';
			$settings['password'] = md5($settings['password']);
			?>
			<h4>Installation: Complete</h4>
			<div class="well">
				<?php
				if ( strlen( $errorMsg ) > 0) {
					?>
					<h3>Error(s).</h3>
					<ul>
						<?php echo $errorMsg; ?>
					</ul>
					<?php
				} else {
					//var_dump($settings);
					$loadavg->write_php_ini( $settings, $settings_file);
					$fh = fopen($settings_file, "a"); fwrite($fh, "\n"); fclose($fh);
					?>
					<b>Thank you for useing LoadAvg <?php echo $settings['version']; ?></b>
					<p>Before start using LoadAvg <?php echo $settings['version']; ?>, execute these <span class="label label-info">crontab -e</span> and insert this line <span class="label label-info">*/5 * * * * /usr/bin/php -q <?php echo dirname(APP_PATH); ?>/logger.php /dev/null 2>1</span></p>
					<p>For security reasons, you should delete the <span class="label label-info">install.php</span> file from your <span class="label label-info">/public</span> folder.</p>
					<?php
				}
				?>
				<?php
				if ( strlen( $errorMsg ) > 0) {
					?><a class="btn btn-primary" href="?step=2">Go back!</a><?php	
				} else {
					?><a class="btn btn-primary" href="index.php?check=1">Login</a><?php
				}
				?>
			</div>
			<?php
		} else {
			header("Location: ?step=1");
		}
		break;
}
?>
</div>
<?php
// Including the footer view
require_once APP_PATH . '/layout/footer.php'; ?>