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

	$settings_file = HOME_PATH . '/app/config/' . LoadAvg::$settings_ini;
	
	$settings = LoadAvg::$_settings->general;

	//echo '<pre>PRESETTINGS</pre>';
	//echo '<pre>';var_dump($settings);echo'</pre>';

	//need to check all modules status first
	//as form drops unchecked values when posted for some reason
	$modules = LoadModules::$_modules;

	foreach ($modules as $module => $moduleName) { 

		$_POST['formsettings']['modules'][$module] = ( !isset($_POST['formsettings']['modules'][$module]) ) ? "false" : "true";

		//echo 'MODULE' . $module . $settings['modules'][$module] . "<br>";
		//echo 'POST' . $_POST['formsettings']['modules'][$module]. "<br>";	
	}


	/* Need to loop thorugh interfaces as well as when all off all dispapear */

	$interfaces = LoadUtility::getNetworkInterfaces();

	foreach ($interfaces as $interface) { 

		$_POST['formsettings']['network_interface'][$interface['name']] = ( !isset($_POST['formsettings']['network_interface'][$interface['name']]) ) ? "false" : "true";

		//echo 'Monitor:' . trim($interface['name']); 
		//echo 'POST' . $_POST['formsettings']['network_interface'][$interface['name']]. "<br>";	

	}
	  
	$postsettings = $_POST['formsettings'];

	//echo '<pre>POSTSETTINGS</pre>';
	//echo '<pre>';var_dump($postsettings);echo'</pre>';

	//$mergedsettings = LoadAvg::ini_merge ($settings, $postsettings);
  	$mergedsettings = array_replace($settings, $postsettings);

	//echo '<pre>MERGESETTINGS</pre>';
	//echo '<pre>';var_dump($mergedsettings);echo'</pre>';

	//echo '<pre>';var_dump($generalSettings);echo'</pre>';
	//exit;

	//die;

	LoadUtility::write_php_ini($mergedsettings, $settings_file);


	///////////////////////////////////////////////////
	//updates all the modules settings here

	//these are dirty dirty hacks! until we can rewrite the settings using proper api	
	$_POST['Disk_settings']['settings']['display_limiting'] = ( !isset($_POST['Disk_settings']['settings']['display_limiting']) ) ? "false" : "true";
	$_POST['Memory_settings']['settings']['display_limiting'] = ( !isset($_POST['Memory_settings']['settings']['display_limiting']) ) ? "false" : "true";
	$_POST['Cpu_settings']['settings']['display_limiting'] = ( !isset($_POST['Cpu_settings']['settings']['display_limiting']) ) ? "false" : "true";

	$_POST['Network_settings']['settings']['transfer_limiting'] = ( !isset($_POST['Network_settings']['settings']['transfer_limiting']) ) ? "false" : "true";
	$_POST['Network_settings']['settings']['receive_limiting'] = ( !isset($_POST['Network_settings']['settings']['receive_limiting']) ) ? "false" : "true";

	$_POST['Mysql_settings']['settings']['show_queries'] = ( !isset($_POST['Mysql_settings']['settings']['show_queries']) ) ? "false" : "true";

	//echo '<pre>';var_dump($_POST);echo'</pre>';

	$modules = LoadModules::$_modules;

    foreach ($modules as $module => $moduleName) {
    
    //echo $moduleName;

		if (isset($_POST[$module . '_settings'])) {

			$module_config_file = HOME_PATH . '/lib/modules/' . $module . '/' . strtolower( $module ) . '.ini.php';
			
			$module_config_ini = parse_ini_file( $module_config_file , true );

			//the array replace is deleting out missing data on posts
			//when the data has not been modified
			//$replaced_settings = array_replace($module_config_ini, $_POST[$module . '_settings']);
  			$replaced_settings = LoadUtility::ini_merge ($module_config_ini, $_POST[$module . '_settings']);

			LoadUtility::write_php_ini($replaced_settings, $module_config_file);

			//why is this here ?
			//$fh = fopen($module_config_file, "a"); fwrite($fh, "\n"); fclose($fh);
			
		}

	}



//exit;
/* 
 * need to reload settings here after posting
 * as for some reason after a post the data isnt updated internally
 */

//still old here mate
//$settings = LoadAvg::$_settings->general;

LoadModules::updateModuleSettings();

//not changed either as written to file above
$loaded = LoadModules::$_settings->general['modules']; 

//now we need to update UI cookie used to hide and show modules by position
LoadModules::updateUIcookieSorting ($loaded);

//die;


//LoadAvg::$_settings->general = $settings;

/* rebuild logs
 * needed for when you turn a module on that has no logs
 * this needs to only rebuild logs for modules that have been turned on
 */

//why dont this work ?
//$loadavg->rebuildLogs();

/* force reload settings page now */
header('Location: '.$_SERVER['REQUEST_URI']);

}

?>


<form action="" method="post" autocomplete="off">
	<input type="hidden" name="update_settings" value="1" />


<div class="innerAll">
	<!--
	<h2>Settings</h2>
	-->
	<div class="well">

		<h4>Module settings</h4>

	</div>

	<div class="separator bottom"></div>

    <div class="panel">
        	<input type="submit" class="btn btn-primary" value="Save Settings">
    </div>

	<div class="separator bottom"></div>



    <!-- 
      * this is where we loop through all the modules
      * and deal with their individual settings
	-->	

	<div class="well">



                <?php 
                	$modules = LoadModules::$_modules; 
					$interfaces = LoadUtility::getNetworkInterfaces(); 
				?>
                
                <?php foreach ($modules as $module => $moduleName) { ?>
				<div class="separator bottom"></div>
            	<div class="row-fluid">
                    <div class="span3">
                            <h4> <?php echo $module; ?> Module</h4>
                    </div>
                    <div class="span9 right">
                        <div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
                            <input name="formsettings[modules][<?php echo $module; ?>]" value="true" type="checkbox"
                            	<?php if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" )
                            		{ ?>checked="checked"<?php }
                            	?>
                            >
                        </div>
                    </div>

                </div>
				<div class="separator bottom"></div>
                <?php
                //indiviudual module settings here
                //need a way to load this dynamically when the module is activated above!
                if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" ) {

                	$moduleSettings = LoadModules::$_settings->$module;
                	
                	if ( isset($moduleSettings['module']['has_settings']) && $moduleSettings['module']['has_settings'] == "true") {
                		?>

                		<div class="well">

                		<?php

                		if ($module == "Network") {

                			echo "<strong>Network Interfaces</strong><br><br>";

							foreach ($interfaces as $interface) { ?>
							<div class="row-fluid">
								<div class="span3">
									<strong>Monitor: <?php echo trim($interface['name']); ?></strong>
								</div>
								<div class="span9 right">
									<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
					                    <input name="formsettings[network_interface][<?php echo trim($interface['name']); ?>]" value="true" type="checkbox"
					                    	<?php
					                    		if ( isset($settings['network_interface'][trim($interface['name'])]) && $settings['network_interface'][trim($interface['name'])] == "true" )
					                    		{ ?>checked="checked"<?php }
					                    	?>
					                    >
					                </div>
								</div>
							</div>
							<?php } 
							echo "<br>";
                			echo "<strong>Network Settings</strong><br><br>";						
						}
				        ?>



	                        <?php
	                        foreach ($moduleSettings['settings'] as $setting => $value) {
	                        	?>
	                        	<div class="row-fluid">
	                        		<div class="span3">
	                        			<strong><?php echo ucwords(str_replace("_"," ",$setting)); ?></strong>
	                        		</div>
	                        		<div class="span9 right">

	                        			<?php if ( $value == 'true' || $value == 'false') { ?>

											<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
												<input name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>" type="checkbox" value="<?php echo $value; ?>" 
												<?php if ( $value == "true" ) { ?>checked="checked"<?php } ?>>
											</div>	  

	                        			<?php } else { ?>

	                        				<div class="pull-right">
	                        					<input type="text" name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>" value="<?php echo $value; ?>" size="40" class="span6 left">	                        					
	                        				</div>  

	                        			<?php } ?>

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

    <div class="panel">
        	<input type="submit" class="btn btn-primary" value="Save Settings">
    </div>

</div>
</form>
<?php } ?>
