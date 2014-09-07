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

<?php

//run this code if the settings have been changed or updated

if (isset($_POST['update_settings'])) {

	if ( !empty($_POST['settings']['general']['password']) && strlen($_POST['settings']['general']['password']) > 0 ) {
		$_POST['settings']['general']['password'] = md5($_POST['settings']['general']['password']);
	} else {
		$_POST['settings']['general']['password'] = $_POST['settings']['general']['password2'];
	}

	unset($_POST['settings']['general']['password2']);

	$_POST['settings']['general']['https'] = ( !isset($_POST['settings']['general']['https']) ) ? "false" : "true";
	$_POST['settings']['general']['checkforupdates'] = ( !isset($_POST['settings']['general']['checkforupdates']) ) ? "false" : "true";
	$_POST['settings']['general']['allow_anyone'] = ( !isset($_POST['settings']['general']['allow_anyone']) ) ? "false" : "true";
	$_POST['settings']['general']['apiserver'] = ( !isset($_POST['settings']['general']['apiserver']) ) ? "false" : "true";

	// Loop throught settings
	$settings_file = APP_PATH . '/config/' . LoadAvg::$settings_ini;
	$settings = $_POST['settings'];
	$setting_to_save = null;


	$generalSettings = $_POST['settings']['general'];

	// echo '<pre>';var_dump($generalSettings);echo'</pre>';
	// exit;

	unlink($settings_file);
	$settings_file_handler = fopen($settings_file, "wa");

	foreach ( $generalSettings as $key => $value ):
		$value = (is_string($value)) ? "\"$value\"" : $value;
		$setting_to_save = $key . " = " . $value . PHP_EOL;
		// var_dump($setting_to_save);
		fwrite($settings_file_handler, $setting_to_save);
	endforeach;

	$settings = $_POST['settings'];

	foreach ( $settings as $key => $value ):
		if ( $key == "general" ) continue;

		$setting_to_save = "[" . $key . "]" . PHP_EOL;
		fwrite($settings_file_handler, $setting_to_save);
		// var_dump($setting_to_save);
		foreach ($settings[$key] as $key => $value) {
			$value = (is_string($value)) ? "\"$value\"" : $value;
			$setting_to_save = $key . " = " . $value . PHP_EOL;
			// var_dump($setting_to_save);
			fwrite($settings_file_handler, $setting_to_save);
		}
	endforeach;


	fwrite($settings_file_handler, "\n");
	fclose($settings_file_handler);

	///////////////////////////////////////////////////
	//updates all the modules settings here
	$modules = LoadAvg::$_modules;
    foreach ($modules as $module => $moduleName) {

//    	echo $moduleName;

		if (isset($_POST[$module . '_settings'])) {

			    	echo $moduleName;

			$module_config_file = APP_PATH . '/../lib/modules/' . $module . '/' . strtolower( $module ) . '.ini';
			$module_config_ini = parse_ini_file( $module_config_file , true );

			$replaced_settings = array_replace($module_config_ini, $_POST[$module . '_settings']);

			LoadAvg::write_php_ini($replaced_settings, $module_config_file);
			$fh = fopen($module_config_file, "a"); fwrite($fh, "\n"); fclose($fh);
		}
	}

/* need to reload settings here after posting
   as for some reason after a post the data isnt updated */

$settings = LoadAvg::$_settings->general;

/* rebuild logs
   needed for when you turn a module on that has no logs
   this needs to only rebuild logs for modules that have been turned on */

$loadavg->rebuildLogs();

//die;

/* reload settings now */
header('Location: '.$_SERVER['REQUEST_URI']);

}

?>


<form action="" method="post">
	<input type="hidden" name="update_settings" value="1" />
	<input type="hidden" name="settings[general][version]" value="<?php echo $settings['version']; ?>" />
	<input type="hidden" name="settings[general][extensions_dir]" value="<?php echo $settings['extensions_dir']; ?>" />
	<input type="hidden" name="settings[general][logs_dir]" value="<?php echo $settings['logs_dir']; ?>" />
	<input type="hidden" name="settings[general][title]" value="<?php echo $settings['title']; ?>" />
	<input type="hidden" name="settings[general][password2]" value="<?php echo $settings['password']; ?>" />
<div class="innerAll">
	<!--
	<h2>Settings</h2>
	-->
	<div class="well">

		<h4>Default settings</h4>

		<div class="row-fluid">
			<div class="span3">
				<strong>Server name</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="settings[general][title]" value="<?php echo $settings['title']; ?>" >
			</div>
		</div>


		<div class="row-fluid">
			<div class="span3">
				<strong>Select time-zone</strong>
			</div>
			<div class="span9 right">


			<?php

			$timezones = LoadAvg::getTimezones();

			print '<select name="settings[general][timezone]" id="timezone">';

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
				<strong>Days to keep</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="settings[general][daystokeep]" value="<?php echo $settings['daystokeep']; ?>" size="4" class="span2 center">
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Check for updates</strong>
			</div>
			<div class="span9 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="settings[general][checkforupdates]" type="checkbox" value="true" <?php if ( $settings['checkforupdates'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Force secure connection</strong>
			</div>
			<div class="span8 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="settings[general][https]" type="checkbox" value="true" <?php if ( $settings['https'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span4">
				<strong>Allow anyone to view charts</strong>
			</div>
			<div class="span8 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="settings[general][allow_anyone]" type="checkbox" value="true" <?php if ( $settings['allow_anyone'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Chart(s) format</strong>
			</div>
			<div class="span9 right">
				<select name="settings[general][chart_type]">
					<option value="1" <?php if ( $settings['chart_type'] == "1" ) { ?>selected="selected"<?php } ?>>Hourly</option>
					<option value="24" <?php if ( $settings['chart_type'] == "24" ) { ?>selected="selected"<?php } ?>>All day</option>
				</select>
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
				<input type="text" name="settings[general][username]" value="<?php echo $settings['username']; ?>" >
			</div>
		</div>

		<div class="row-fluid">
			<div class="span3">
				<strong>Password</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="settings[general][password]" />
			</div>
		</div>

	</div>

	<div class="separator bottom"></div>










	<div class="separator bottom"></div>

	<div class="well">
    <h4>API settings</h4>

		<div class="row-fluid">
			<div class="span3">
				<strong>Connect to API</strong>
			</div>
			<div class="span9 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					<input name="settings[general][apiserver]" type="checkbox" value="true" <?php if ( $settings['apiserver'] == "true" ) { ?>checked="checked"<?php } ?>>
				</div>
			</div>
		</div>

    <div class="row-fluid">
      <div class="span3">
        <strong>API URL</strong>
      </div>
      <div class="span9 right">
        <input type="text" name="settings[api][url]" value="<?php echo $settings['api']['url']; ?>" size="4" class="span6 left">
      </div>
    </div>

    <div class="row-fluid">
      <div class="span3">
        <strong>API Key</strong>
      </div>
      <div class="span9 right">
				<input type="text" name="settings[api][key]" value="<?php echo $settings['api']['key']; ?>" size="4" class="span6 left">
      </div>
    </div>

    <!-- <div class="row-fluid">
      <div class="span3">
        <strong>API Username</strong>
      </div>
      <div class="span9 right">
				<input type="text" name="settings[api][username]" value="<?php //echo $settings['api']['username']; ?>" size="4" class="span6 left">
      </div>
    </div> -->

    <div class="row-fluid">
      <div class="span3">
        <strong>Server Token</strong>
      </div>
      <div class="span9 right">
        <input type="text" name="settings[api][server_token]" value="<?php echo $settings['api']['server_token']; ?>" size="4" class="span6 left">
      </div>
    </div>

    <!-- <div class="row-fluid">
      <div class="span3">
        <strong>API Server ID</strong>
      </div>
      <div class="span9 right">
        <input type="text" name="settings[api][server]" value="<?php //echo $settings['api']['server']; ?>" size="4" class="span6 left">
      </div>
    </div> -->
	</div>

	<div class="separator bottom"></div>

	<div class="well">
		<h4>Network interfaces</h4>
		<?php $interfaces = LoadAvg::getNetworkInterfaces(); ?>
		<?php foreach ($interfaces as $interface) { ?>
		<div class="row-fluid">
			<div class="span3">
				<strong>Monitor: <?php echo trim($interface['name']); ?></strong>
			</div>
			<div class="span9 right">
				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
                    <input name="settings[network_interface][<?php echo trim($interface['name']); ?>]" value="true" type="checkbox"
                    	<?php
                    		if ( isset($settings['network_interface'][trim($interface['name'])]) && $settings['network_interface'][trim($interface['name'])] == "true" )
                    		{ ?>checked="checked"<?php }
                    	?>
                    >
                </div>
			</div>
		</div>
		<?php } ?>
	</div>

<div class="separator bottom"></div>

	<div class="well">
                <h4>Modules</h4>
                <?php $modules = LoadAvg::$_modules; ?>
                <?php foreach ($modules as $module => $moduleName) { ?>
				<div class="separator bottom"></div>
            	<div class="row-fluid">
                    <div class="span3">
                            <strong><?php echo $module; ?></strong>
                    </div>
                    <div class="span9 right">
                        <div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
                            <input name="settings[modules][<?php echo $module; ?>]" value="true" type="checkbox"
                            	<?php if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" )
                            		{ ?>checked="checked"<?php }
                            	?>
                            >
                        </div>
                    </div>
                </div>
				<div class="separator bottom"></div>
                <?php
                if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" ) {
                	$moduleSettings = LoadAvg::$_settings->$module;
                	if ( isset($moduleSettings['module']['has_settings']) && $moduleSettings['module']['has_settings'] == "true") {
                		?>
                		<div class="well">

            				<strong><?php echo $module; ?> module settings:</strong>

	                        <?php
	                        foreach ($moduleSettings['settings'] as $setting => $value) {
	                        	?>
	                        	<div class="row-fluid">
	                        		<div class="span3">
	                        			<strong><?php echo ucwords(str_replace("_"," ",$setting)); ?></strong>
	                        		</div>
	                        		<div class="span9 right">
	                        			<div class="pull-right">
	                        				<input type="text" name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>" value="<?php echo $value; ?>" class="span5 center">
	                        			</div>
	                        		</div>
	                        	</div>
	                        	<?php
	                        }
	                        ?>
                		</div>
                		<?php
                	}
                }
                ?>
                <?php } ?>
        </div>

		<div class="separator bottom"></div>
		<div class="separator bottom"></div>

        <div class="panel">
        	<input type="submit" class="btn btn-primary" value="Save Settings">
        </div>
</div>
</form>
<?php } ?>
