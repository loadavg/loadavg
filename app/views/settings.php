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
	if ( !empty($_POST['formsettings']['settings']['password']) && strlen($_POST['formsettings']['settings']['password']) > 0 ) {
		$_POST['formsettings']['settings']['password'] = md5($_POST['formsettings']['settings']['password']);
	} else {
		$_POST['formsettings']['settings']['password'] = $_POST['formsettings']['settings']['password2'];
	}

	//remove password 2 as its not stored just aproxy to update password
	unset($_POST['formsettings']['settings']['password2']);


	$_POST['formsettings']['settings']['https'] = ( !isset($_POST['formsettings']['settings']['https']) ) ? "false" : "true";
	$_POST['formsettings']['settings']['checkforupdates'] = ( !isset($_POST['formsettings']['settings']['checkforupdates']) ) ? "false" : "true";
	$_POST['formsettings']['settings']['allow_anyone'] = ( !isset($_POST['formsettings']['settings']['allow_anyone']) ) ? "false" : "true";
	$_POST['formsettings']['settings']['ban_ip'] = ( !isset($_POST['formsettings']['settings']['ban_ip']) ) ? "false" : "true";
	$_POST['formsettings']['settings']['apiserver'] = ( !isset($_POST['formsettings']['settings']['apiserver']) ) ? "false" : "true";
	$_POST['formsettings']['settings']['autoreload'] = ( !isset($_POST['formsettings']['settings']['autoreload']) ) ? "false" : "true";

	// Loop throught settings
	$settings_file = APP_PATH . '/config/' . LoadAvg::$settings_ini;
	
	//get current settings from memory
	$settings = LoadAvg::$_settings->general;

	//need to remove password 2 from this fucker ?
	$postsettings = $_POST['formsettings'];

	//what is better here - ini_merge or array_replace ?
	//need to test instances where we add new variables to the mix

	/*
	echo '<pre>File Settings:';
	print_r($settings);
	echo '</pre>';

	echo '<pre>Posted settings';
	print_r($postsettings);
	echo '</pre>';
	*/

	$replaced_settings = LoadUtility::ini_merge ($settings, $postsettings);
	//$replaced_settings = array_replace($settings, $postsettings);
	
	/*
	echo '<pre>';
	print_r($replaced_settings);
	echo '</pre>';

	die;
	*/
	
	LoadUtility::write_php_ini($replaced_settings, $settings_file);


	///////////////////////////////////////////////////
	//updates all the modules settings here

	//exit;
	/* 
	 * need to reload settings here after posting
	 * as for some reason after a post the data isnt updated internally
	 */

	$settings = LoadAvg::$_settings->general;

	/* force reload settings page now */
	header('Location: '.$_SERVER['REQUEST_URI']);

}

?>

<!--
		//if things get crazy for any reason then we need to just delete all cookies 
		//maybe add to settings >

		//dirty short term hack deleted cookie
		//if(isset($_COOKIE['loadUIcookie'])) {
		//	setcookie('loadUIcookie', null, -1, "/");
    	//	unset($_COOKIE['loadUIcookie']);
		//}
-->


<form action="" method="post">
	<input type="hidden" name="update_settings" value="1" />

	<input type="hidden" name="formsettings[settings][password2]" value="<?php echo $settings['settings']['password']; ?>" />
	
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
				<input type="text" name="formsettings[settings][title]" value="<?php echo $settings['settings']['title']; ?>" >
			</div>
		</div>


		<div class="row-fluid">
			<div class="span3">
				<strong>Select time-zone</strong>
			</div>
			<div class="span9 right">


			<?php
			$timezones = LoadAvg::getTimezones();
			print '<select name="formsettings[settings][clienttimezone]" id="timezone">';

			foreach($timezones as $region => $list)
			{
				print '<optgroup label="' . $region . '">' . "\n";
				foreach($list as $thetimezone => $name)
				{
					print '<option name="' . $thetimezone . '"';
					$check = $settings['settings']['clienttimezone'];
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

				<input name="formsettings[settings][checkforupdates]" type="checkbox" checkbox-type="my-checkbox" 
				value="true" <?php if ( $settings['settings']['checkforupdates'] == "true" ) { ?>checked="checked"<?php } ?>>
			    <div class="separator bottom"></div>

			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Chart(s) format</strong>
			</div>
			<div class="span9 right">
				<select name="formsettings[settings][chart_type]">
					<option value="24" <?php if ( $settings['settings']['chart_type'] == "24" ) { ?>selected="selected"<?php } ?>>All day</option>
					<option value="12" <?php if ( $settings['settings']['chart_type'] == "12" ) { ?>selected="selected"<?php } ?>>12 Hour</option>
					<option value="6" <?php if ( $settings['settings']['chart_type'] == "16" ) { ?>selected="selected"<?php } ?>>6 Hour</option>
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

				<input name="formsettings[settings][https]" type="checkbox" checkbox-type="my-checkbox" 
				value="true" <?php if ( $settings['settings']['https'] == "true" ) { ?>checked="checked"<?php } ?>>
	            <div class="separator bottom"></div>

			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Allow anyone to view charts</strong>
			</div>
			<div class="span8 right">

				<input name="formsettings[settings][allow_anyone]" type="checkbox" checkbox-type="my-checkbox" 
				value="true" <?php if ( $settings['settings']['allow_anyone'] == "true" ) { ?>checked="checked"<?php } ?>>
        		<div class="separator bottom"></div>

			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Ban blocked IP's</strong>
			</div>
			<div class="span8 right">

				<input name="formsettings[settings][ban_ip]" type="checkbox" checkbox-type="my-checkbox" 
				value="true" <?php if ( $settings['settings']['ban_ip'] == "true" ) { ?>checked="checked"<?php } ?>>
            	<div class="separator bottom"></div>

			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Days to remember me for (when active)</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[settings][rememberme_interval]" value="<?php echo $settings['settings']['rememberme_interval']; ?>" >
			</div>
		</div>

	</div>

	<div class="separator bottom"></div>

	<div class="well">
    <h4>Interface settings</h4>

		<div class="row-fluid">
			<div class="span4">
				<strong>Auto reload page</strong>
			</div>
			<div class="span8 right">

					<input name="formsettings[settings][autoreload]" type="checkbox" checkbox-type="my-checkbox" 
					value="true" <?php if ( $settings['settings']['autoreload'] == "true" ) { ?>checked="checked"<?php } ?>>

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
				<input type="text" name="formsettings[settings][username]" value="<?php echo $settings['settings']['username']; ?>" >
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Password</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="formsettings[settings][password]" />
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
