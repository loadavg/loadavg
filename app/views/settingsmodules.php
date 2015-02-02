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

$loadModules->updateModuleSettings();
//LoadModules::updateModuleSettings();

//not changed either as written to file above
$loadedModules = LoadModules::$_settings->general['modules']; 

//now we need to update UI cookie used to hide and show modules by position
$loadModules->updateUIcookieSorting ($loadedModules);

//die;


//LoadAvg::$_settings->general = $settings;

/* rebuild logs
 * needed for when you turn a module on that has no logs
 * this needs to only rebuild logs for modules that have been turned on
 */

//why dont this work ?
$loadavg->runLogger();

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


        <?php 
        $modules = LoadModules::$_modules; 
		$interfaces = LoadUtility::getNetworkInterfaces(); 
		
        foreach ($modules as $module => $moduleName) { 

        	//with this we can now move over to AJAX settings
        	//as settings are now loaded per moduel weather on or off
    		$moduleSettings = LoadUtility::getSettings($module, 'modules' );

        	// if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" )
        	$moduleStatus = $settings['modules'][$module];
   			?>
			
			<div class="well">

				<!--
				module name is rendered here along with checkbox for status
				-->
		    	<div class="row-fluid">
		            <div class="span3">
		                    <h4> <?php echo $module; ?> Module</h4>
		            </div>
		            <div class="span9 right">

						<!--
						here we create the check box / on off slider
						name is used to submit data to form needs cleaning up
						-->

						<input type="checkbox" checkbox-type="my-checkbox-databox" data-target="<?php echo $module; ?>" 
						name="formsettings[modules][<?php echo $module; ?>]" <?php if ( $moduleStatus == "true" ) { ?>checked="checked"<?php } ?> >

		            </div>
		        </div>

				<!--
				module description is done here
				-->
				<div>
		        <?php 
		        echo $moduleSettings['module']['description'];
		        ?> 
				</div>


				<!--
				module settings is done here
				-->
				<div class="viewdetails_<?php echo $module; ?>" <?php if ( $moduleStatus == "false" ) { ?>style="display:none"<?php } ?> >

				<div class="separator bottom"></div>

		            <?php
		           
		            //indiviudual module settings here
		            //if ( isset($settings['modules'][$module]) && $settings['modules'][$module] == "true" ) {
		           
		            if ( isset($settings['modules'][$module]) ) {

		            	//$moduleSettings = LoadModules::$_settings->$module;
		            	
		            	if ( isset($moduleSettings['module']['has_settings']) && $moduleSettings['module']['has_settings'] == "true") {
		            		?>

			    				<div class="row-fluid">

		                		<?php
		                		if ($module == "Network") {


		                			echo "<strong>Network Interfaces</strong><br><br>";

									foreach ($interfaces as $interface) { 

										$interface_name = trim($interface['name']); ?>

										<div class="row-fluid">
											<div class="span3">
												<strong>Monitor: <?php echo trim($interface['name']); ?></strong>
											</div>
											<div class="span9 right">

							                    <input name="formsettings[network_interface][<?php echo $interface_name; ?>]" checkbox-type="my-checkbox" value="true" type="checkbox"
							                    	<?php
							                    		if ( isset($settings['network_interface'][trim($interface['name'])]) && $settings['network_interface'][trim($interface['name'])] == "true" )
							                    		{ ?>checked="checked"<?php }
							                    	?>
							                    >

											</div>
										</div>
									<?php } 
									echo "<br><strong>Network Settings</strong><br><br>";						
								}
						        
		                        foreach ($moduleSettings['settings'] as $setting => $value) {

		                        	//if ($setting == "display_limiting")
		                        	//better if $settings ends in limiting as also gets network settings 
		                        	// hack this in better so we skip the divs below but data is still recorded for POST

		                        	?>
		                        	<div class="row-fluid">
		                        		<div class="span3">
		                        			<strong><?php echo ucwords(str_replace("_"," ",$setting)); ?></strong>
		                        		</div>
		                        		<div class="span9 right">

		                        			<?php 

											if ($setting == "display_limiting") { ?>

													<input type = "hidden" name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>"  data-size="small" type="checkbox" value="<?php echo $value; ?>" 
													<?php if ( $value == "true" ) { ?>checked="checked"<?php } ?>>


		                        			<?php } else if ( $value == 'true' || $value == 'false') { ?>

		                        				<!-- means its a checkbox -->

													<input name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>" checkbox-type="my-checkbox" data-size="small" type="checkbox" value="<?php echo $value; ?>" 
													<?php if ( $value == "true" ) { ?>checked="checked"<?php } ?>>

		                        			<?php } else { ?>

		                        				<!-- means its a regular data settings -->

		                        				<div class="pull-right">
		                        					<input type="text" name="<?php echo $module.'_settings[settings]['.$setting.']'; ?>" value="<?php echo $value; ?>" size="40" class="span6 left">	                        					
		                        				</div>  

		                        			<?php } ?>

		                        		</div>
		                        	</div>
		                        	<?php
		                        }
		                        ?>

		            		</div> <!-- close well -->
		            	<?php
		            	}
		            }

		    		?>

				</div> <!-- close out the viewdetails section -->

		    </div> <!-- close well -->

    <div class="separator bottom"></div>

	<?php
    } 
    ?>

	<div class="separator bottom"></div>

    <div class="panel">
        	<input type="submit" class="btn btn-primary" value="Save Settings">
    </div>

</div>
</form>
<?php } ?>
