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

//run this code if the settings have been changed or updated

if (isset($_POST['update_settings'])) {

	///////////////////////////////////////////////////
	//updates the general settings here

	//check to see if password was updated
	if ( !empty($_POST['formsettings']['password']) && strlen($_POST['formsettings']['password']) > 0 ) {
		$_POST['formsettings']['password'] = md5($_POST['formsettings']['password']);
	} else {
		$_POST['formsettings']['password'] = $_POST['formsettings']['password2'];
	}

	unset($_POST['formsettings']['password2']);

	$_POST['formsettings']['https'] = ( !isset($_POST['formsettings']['https']) ) ? "false" : "true";
	$_POST['formsettings']['checkforupdates'] = ( !isset($_POST['formsettings']['checkforupdates']) ) ? "false" : "true";
	$_POST['formsettings']['allow_anyone'] = ( !isset($_POST['formsettings']['allow_anyone']) ) ? "false" : "true";
	$_POST['formsettings']['ban_ip'] = ( !isset($_POST['formsettings']['ban_ip']) ) ? "false" : "true";
	$_POST['formsettings']['apiserver'] = ( !isset($_POST['formsettings']['apiserver']) ) ? "false" : "true";

	// Loop throught settings
	$settings_file = APP_PATH . '/config/' . LoadAvg::$settings_ini;
	
	//get current settings from memory
	$settings = LoadAvg::$_settings->general;

	$postsettings = $_POST['formsettings'];

	//what is better here - ini_merge or array_replace ?
	//need to test instances where we add new variables to the mix
	
	//$mergedsettings = LoadAvg::ini_merge ($settings, $postsettings);
	$replaced_settings = array_replace($settings, $postsettings);

	LoadAvg::write_php_ini($replaced_settings, $settings_file);


	///////////////////////////////////////////////////
	//updates all the modules settings here

	//exit;
	/* 
	 * need to reload settings here after posting
	 * as for some reason after a post the data isnt updated internally
	 */

	$settings = LoadAvg::$_settings->general;
	//LoadAvg::$_settings->general = $settings;

	/* rebuild logs
	 * needed for when you turn a module on that has no logs
	 * this needs to only rebuild logs for modules that have been turned on
	 */
	//$loadavg->rebuildLogs();

	/* force reload settings page now */
	header('Location: '.$_SERVER['REQUEST_URI']);

}

?>


<form action="" method="post">
	<input type="hidden" name="update_settings" value="1" />

	<input type="hidden" name="formsettings[password2]" value="<?php echo $settings['password']; ?>" />
	
<div class="innerAll">

	<div class="well">
		<h4>System settings</h4>
	</div>

	<div class="separator bottom"></div>

	<div class="well">

		<h4>Core settings</h4>

		<div class="row-fluid">
			<div class="span3">
				<strong>Server name</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[title]" value="<?php echo $settings['title']; ?>" >
			</div>
		</div>


		<div class="row-fluid">
			<div class="span3">
				<strong>Select time-zone</strong>
			</div>
			<div class="span9 right">


			<?php
			$timezones = LoadAvg::getTimezones();
			print '<select name="formsettings[timezone]" id="timezone">';

			foreach($timezones as $region => $list)
			{
				print '<optgroup label="' . $region . '">' . "\n";
				foreach($list as $thetimezone => $name)
				{
					print '<option name="' . $thetimezone . '"';
					$check = $settings['timezone'];
					if (  $check == $thetimezone )  { print ' selected="selected"'; }
					print '>' . $thetimezone . '</option>' . "\n";
				}
				print '<optgroup>' . "\n";
			}
			print '</select>';
			?>


			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Check for updates</strong>
			</div>
			<div class="span9 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="formsettings[checkforupdates]" type="checkbox" value="true" <?php if ( $settings['checkforupdates'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Chart(s) format</strong>
			</div>
			<div class="span9 right">
				<select name="formsettings[chart_type]">
					<option value="24" <?php if ( $settings['chart_type'] == "24" ) { ?>selected="selected"<?php } ?>>All day</option>
					<option value="12" <?php if ( $settings['chart_type'] == "12" ) { ?>selected="selected"<?php } ?>>12 Hour</option>
					<option value="6" <?php if ( $settings['chart_type'] == "16" ) { ?>selected="selected"<?php } ?>>6 Hour</option>
				</select>
			</div>
		</div>


	</div>

	<div class="separator bottom"></div>

	<div class="well">
    <h4>Security settings</h4>

		<div class="row-fluid">
			<div class="span4">
				<strong>Force secure connection</strong>
			</div>
			<div class="span8 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="formsettings[https]" type="checkbox" value="true" <?php if ( $settings['https'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Allow anyone to view charts</strong>
			</div>
			<div class="span8 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="formsettings[allow_anyone]" type="checkbox" value="true" <?php if ( $settings['allow_anyone'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Ban blocked IP's</strong>
			</div>
			<div class="span8 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="formsettings[ban_ip]" type="checkbox" value="true" <?php if ( $settings['ban_ip'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Days to remember me for (when active)</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[rememberme_interval]" value="<?php echo $settings['rememberme_interval']; ?>" >
			</div>
		</div>

	</div>


	<div class="separator bottom"></div>

	<div class="well">
    <h4>Username and Password</h4>

		<div class="row-fluid">
			<div class="span3">
				<strong>Username</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[username]" value="<?php echo $settings['username']; ?>" >
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Password</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[password]" />
			</div>
		</div>

	</div>

	<div class="separator bottom"></div>

    <div class="panel">
    	<input type="submit" class="btn btn-primary" value="Save Settings">
    </div>

</div>

</form>
<?php } ?>
