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

	//get current settings 
	$settings = LoadAvg::$_settings->general;

	//need to check all modules status first
	//as form drops unchecked values when posted for some reason
	$modules = LoadPlugins::$_plugins;

	foreach ($plugins as $plugin => $pluginName) { 

		$_POST['formsettings']['plugins'][$plugin] = ( !isset($_POST['formsettings']['plugins'][$plugin]) ) ? "false" : "true";

	}

	//grab changes / posts  
	$postsettings = $_POST['formsettings'];

	//merge in with current settings
  	$mergedsettings = array_replace($settings, $postsettings);

  	//write out new inin file
	$settings_file = HOME_PATH . '/app/config/' . LoadAvg::$settings_ini;

	LoadUtility::write_php_ini($mergedsettings, $settings_file);


	/* 
	 * need to reload settings here after posting
	 * as for some reason after a post the data isnt updated internally
	 */

	$loadPlugins->updateModuleSettings();

	/* force reload settings page now */
	header('Location: '.$_SERVER['REQUEST_URI']);

}

?>


<form action="" method="post" autocomplete="off">
	<input type="hidden" name="update_settings" value="1" />


<div class="innerAll">
	<div class="well">
		<h4>Plugin settings</h4>
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
    	$plugins = LoadPlugins::$_plugins; 
    
		foreach ($plugins as $plugin => $pluginName) { 

			//grab settings data for module
    		$pluginSettings = LoadUtility::getSettings($plugin, 'plugins' );

    		//var_dump ( $pluginSettings );

        	?>

		<div class="well">

			<div class="separator bottom"></div>
			<div class="row-fluid">

			    <div class="span3">
			            <h4> <?php echo $plugin; ?> Plugin</h4>
			    </div>

			    <div class="span9 right">
		            <input name="formsettings[plugins][<?php echo $plugin; ?>]" checkbox-type="my-checkbox" value="true" type="checkbox"
		            	<?php if ( isset($settings['plugins'][$plugin]) && $settings['plugins'][$plugin] == "true" )
		            		{ ?>checked="checked"<?php }
		            	?>
		            >
			    </div>

			</div>

	        <?php
	        //and now some text here from plugin description
	        //cant show these unless module is laoded!!!
	        if (isset ($pluginSettings)) {
	        
	        	echo $pluginSettings['module']['description'];

			    if ( $pluginSettings['module']['has_settings'] == true) {

					echo '<div class="separator bottom"></div>';


			    	if ($plugin == "Process") {

			    		foreach ($pluginSettings['settings'] as $setting => $value) {
		                ?>
		                        	
			                        	<div class="row-fluid">
			                        		<div class="span3">
			                        			<strong><?php echo ucwords(str_replace("_"," ",$setting)); ?></strong>
			                        		</div>
			                        		<div class="span9 right">

			                        			<?php 

												if ( $value == 'true' || $value == 'false') { ?>

			                        				<!-- means its a checkbox -->

														<input name="<?php echo $plugin.'_settings[settings]['.$setting.']'; ?>" checkbox-type="my-checkbox" type="checkbox" value="<?php echo $value; ?>" 
														<?php if ( $value == "true" ) { ?>checked="checked"<?php } ?>>

			                        			<?php } else { ?>

			                        				<!-- means its a regular data settings -->

			                        				<div class="pull-right">
			                        					<input type="text" name="<?php echo $plugin.'_settings[settings]['.$setting.']'; ?>" value="<?php echo $value; ?>" size="40" class="span6 left">	                        					
			                        				</div>  

			                        			<?php } ?>

			                        		</div>
			                        	</div>

		                <?php 
			    		}

			    	}

			    } 


	    	}

	        ?>



			<div class="separator bottom"></div>

    	</div> <!--  close well -->
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
