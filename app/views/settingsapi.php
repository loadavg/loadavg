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

if (isset($_POST['update_settings'])) {


	/////////////////////////////////////////////////////////////////////
	//updates the general settings here as api settings are stored there

	//we clean input here for items with checkbox values for some reason not sure if we still need to
	$_POST['formsettings']['apiserver'] = ( !isset($_POST['formsettings']['apiserver']) ) ? "false" : "true";

	// Loop throught settings
	$settings_file = APP_PATH . '/config/' . LoadAvg::$settings_ini;
	
	$settings = LoadAvg::$_settings->general;

	$postsettings = $_POST['formsettings'];

  //$mergedsettings = LoadAvg::ini_merge ($settings, $postsettings);
  $replaced_settings = array_replace($settings, $postsettings);

  //LoadAvg::write_php_ini($mergedsettings, $settings_file);
  LoadAvg::write_php_ini($replaced_settings, $settings_file);

  //dont work only page reload work
  //$settings = LoadAvg::$_settings->general;


    /////////////////////////////////////////////////////////////////////
    //test api connection done inline on reload for now need AJAX

  if (isset($_POST['Test_Settings'])) 
    header('Location: '. strtok($_SERVER["REQUEST_URI"],'&') . '&test=true' );
  else
    header('Location: '. strtok($_SERVER["REQUEST_URI"],'&') );

}

?>

<form action="" method="post">

	<input type="hidden" name="update_settings" value="1" />

  <div class="innerAll">

  	<div class="well">
  		<h4>API Settings</h4>
  	</div>

  	<div class="separator bottom"></div>

  	<div class="well">

  		<div class="row-fluid">
  			<div class="span3">
  				<strong>Connect to API</strong>
  			</div>
  			<div class="span9 right">
  				<div class="toggle-button" data-togglebutton-style-enabled="success" style="width: 100px; height: 25px;">
  					<input name="formsettings[settings][apiserver]" type="checkbox" value="true" <?php if ( $settings['settings']['apiserver'] == "true" ) { ?>checked="checked"<?php } ?>>
  				</div>
  			</div>
  		</div>

      <div class="row-fluid">
        <div class="span3">
          <strong>API URL</strong>
        </div>
        <div class="span9 right">
          <input type="text" name="formsettings[api][url]" value="<?php echo $settings['api']['url']; ?>" size="4" class="span6 left">
        </div>
      </div>

      <div class="row-fluid">
        <div class="span3">
          <strong>API Key</strong>
        </div>
        <div class="span9 right">
  				<input type="text" name="formsettings[api][key]" value="<?php echo $settings['api']['key']; ?>" size="4" class="span6 left">
        </div>
      </div>

      <div class="row-fluid">
        <div class="span3">
          <strong>Server Token</strong>
        </div>
        <div class="span9 right">
          <input type="text" name="formsettings[api][server_token]" value="<?php echo $settings['api']['server_token']; ?>" size="4" class="span6 left">
        </div>
      </div>


      <?php if ( $settings['settings']['apiserver'] == "false" ) { ?>
      <div class="row-fluid">
        <div class="center">
          <br>
          <strong>Sign up for a <a href="http://www.gridload.com">Free GridLoad Monitoring Account</a></strong>
          <br>
          And access your server data & analytics anywhere
        </div>
      </div>      
      <?php } ?>

  	</div>


    <?php
    if    (isset($_GET['test']) && !empty($_GET['test']) ) { 
    ?>
    <div class="separator bottom"></div>
    <div class="well">
    <?php

        $status = LoadAvg::testApiConnection( true );

        if ($status) {

        ?>
      <div class="row-fluid">
        <div class="center">
          <br>
          <strong>Account Status:</strong> Active 
        </div>
      </div> 
        <?php
        }
        else {
        ?>
      <div class="row-fluid">
        <div class="center">
          <br>
          <strong>Account Status:</strong> Inactive 
        </div>
      </div>
        <?php
        }
    ?>
    </div>
    <?php
    }
    ?>


  	<div class="separator bottom"></div>
      <div class="panel">
        <input type="submit" class="btn btn-primary pull-left" value="Save Settings" name="Save Settings">
      </div>

      <div class="panel">
        <input type="submit" class="btn btn-primary pull-right" value="Test Settings" name="Test Settings">
      </div>
    </div>
    <div class="separator bottom"></div>

</form>


<?php } ?>
