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
if (!$loadavg->isLoggedIn() && !LoadAvg::checkInstall()) { include('login.php'); }
else {
?>

<?php
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



	// foreach ($settings as $key => $value) {
	// 	// Check if value is array | if YES then its a section
	// 	if (!is_array($value)) {
	// 		$value = (is_string($value)) ? "\"$value\"" : $value;
	// 		$setting_to_save = $key . " = " . $value . PHP_EOL;
	// 		fwrite($settings_file_handler, $setting_to_save);
	// 	} else if ( is_array($value) ) {
	// 		$setting_to_save = "[" . $key . "]" . PHP_EOL;
	// 		fwrite($settings_file_handler, $setting_to_save);
	// 		foreach ($settings[$key] as $key => $value) {
	// 			$value = (is_string($value)) ? "\"$value\"" : $value;
	// 			$setting_to_save = $key . " = " . $value . PHP_EOL;
	// 			fwrite($settings_file_handler, $setting_to_save);
	// 		}
	// 	}
	// }

	fwrite($settings_file_handler, "\n");
	fclose($settings_file_handler);

	$modules = LoadAvg::$_modules;
    foreach ($modules as $module => $moduleName) {
		if (isset($_POST[$module . '_settings'])) {
			$module_config_file = APP_PATH . '/../lib/modules/' . $module . '/' . strtolower( $module ) . '.ini';
			$module_config_ini = parse_ini_file( $module_config_file , true );

			$replaced_settings = array_replace($module_config_ini, $_POST[$module . '_settings']);
			LoadAvg::write_php_ini($replaced_settings, $module_config_file);
			$fh = fopen($module_config_file, "a"); fwrite($fh, "\n"); fclose($fh);
		}
	}

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
				<strong>Select time-zone</strong>
			</div>
			<div class="span9 right">
				<select name="settings[general][timezone]" id="timezone">
					<optgroup label="Africa">
<option name="Africa/Abidjan" <?php if ($settings['timezone'] == "Africa/Abidjan") { echo 'selected="selected"'; } ?>>Africa/Abidjan</option>
<option name="Africa/Accra" <?php if ($settings['timezone'] == "Africa/Accra") { echo 'selected="selected"'; } ?>>Africa/Accra</option>
<option name="Africa/Addis_Ababa" <?php if ($settings['timezone'] == "Africa/Addis_Ababa") { echo 'selected="selected"'; } ?>>Africa/Addis_Ababa</option>
<option name="Africa/Algiers" <?php if ($settings['timezone'] == "Africa/Algiers") { echo 'selected="selected"'; } ?>>Africa/Algiers</option>
<option name="Africa/Asmara" <?php if ($settings['timezone'] == "Africa/Asmara") { echo 'selected="selected"'; } ?>>Africa/Asmara</option>
<option name="Africa/Bamako" <?php if ($settings['timezone'] == "Africa/Bamako") { echo 'selected="selected"'; } ?>>Africa/Bamako</option>
<option name="Africa/Bangui" <?php if ($settings['timezone'] == "Africa/Bangui") { echo 'selected="selected"'; } ?>>Africa/Bangui</option>
<option name="Africa/Banjul" <?php if ($settings['timezone'] == "Africa/Banjul") { echo 'selected="selected"'; } ?>>Africa/Banjul</option>
<option name="Africa/Bissau" <?php if ($settings['timezone'] == "Africa/Bissau") { echo 'selected="selected"'; } ?>>Africa/Bissau</option>
<option name="Africa/Blantyre" <?php if ($settings['timezone'] == "Africa/Blantyre") { echo 'selected="selected"'; } ?>>Africa/Blantyre</option>
<option name="Africa/Brazzaville" <?php if ($settings['timezone'] == "Africa/Brazzaville") { echo 'selected="selected"'; } ?>>Africa/Brazzaville</option>
<option name="Africa/Bujumbura" <?php if ($settings['timezone'] == "Africa/Bujumbura") { echo 'selected="selected"'; } ?>>Africa/Bujumbura</option>
<option name="Africa/Cairo" <?php if ($settings['timezone'] == "Africa/Cairo") { echo 'selected="selected"'; } ?>>Africa/Cairo</option>
<option name="Africa/Casablanca" <?php if ($settings['timezone'] == "Africa/Casablanca") { echo 'selected="selected"'; } ?>>Africa/Casablanca</option>
<option name="Africa/Ceuta" <?php if ($settings['timezone'] == "Africa/Ceuta") { echo 'selected="selected"'; } ?>>Africa/Ceuta</option>
<option name="Africa/Conakry" <?php if ($settings['timezone'] == "Africa/Conakry") { echo 'selected="selected"'; } ?>>Africa/Conakry</option>
<option name="Africa/Dakar" <?php if ($settings['timezone'] == "Africa/Dakar") { echo 'selected="selected"'; } ?>>Africa/Dakar</option>
<option name="Africa/Dar_es_Salaam" <?php if ($settings['timezone'] == "Africa/Dar_es_Salaam") { echo 'selected="selected"'; } ?>>Africa/Dar_es_Salaam</option>
<option name="Africa/Djibouti" <?php if ($settings['timezone'] == "Africa/Djibouti") { echo 'selected="selected"'; } ?>>Africa/Djibouti</option>
<option name="Africa/Douala" <?php if ($settings['timezone'] == "Africa/Douala") { echo 'selected="selected"'; } ?>>Africa/Douala</option>
<option name="Africa/El_Aaiun" <?php if ($settings['timezone'] == "Africa/El_Aaiun") { echo 'selected="selected"'; } ?>>Africa/El_Aaiun</option>
<option name="Africa/Freetown" <?php if ($settings['timezone'] == "Africa/Freetown") { echo 'selected="selected"'; } ?>>Africa/Freetown</option>
<option name="Africa/Gaborone" <?php if ($settings['timezone'] == "Africa/Gaborone") { echo 'selected="selected"'; } ?>>Africa/Gaborone</option>
<option name="Africa/Harare" <?php if ($settings['timezone'] == "Africa/Harare") { echo 'selected="selected"'; } ?>>Africa/Harare</option>
<option name="Africa/Johannesburg" <?php if ($settings['timezone'] == "Africa/Johannesburg") { echo 'selected="selected"'; } ?>>Africa/Johannesburg</option>
<option name="Africa/Juba" <?php if ($settings['timezone'] == "Africa/Juba") { echo 'selected="selected"'; } ?>>Africa/Juba</option>
<option name="Africa/Kampala" <?php if ($settings['timezone'] == "Africa/Kampala") { echo 'selected="selected"'; } ?>>Africa/Kampala</option>
<option name="Africa/Khartoum" <?php if ($settings['timezone'] == "Africa/Khartoum") { echo 'selected="selected"'; } ?>>Africa/Khartoum</option>
<option name="Africa/Kigali" <?php if ($settings['timezone'] == "Africa/Kigali") { echo 'selected="selected"'; } ?>>Africa/Kigali</option>
<option name="Africa/Kinshasa" <?php if ($settings['timezone'] == "Africa/Kinshasa") { echo 'selected="selected"'; } ?>>Africa/Kinshasa</option>
<option name="Africa/Lagos" <?php if ($settings['timezone'] == "Africa/Lagos") { echo 'selected="selected"'; } ?>>Africa/Lagos</option>
<option name="Africa/Libreville" <?php if ($settings['timezone'] == "Africa/Libreville") { echo 'selected="selected"'; } ?>>Africa/Libreville</option>
<option name="Africa/Lome" <?php if ($settings['timezone'] == "Africa/Lome") { echo 'selected="selected"'; } ?>>Africa/Lome</option>
<option name="Africa/Luanda" <?php if ($settings['timezone'] == "Africa/Luanda") { echo 'selected="selected"'; } ?>>Africa/Luanda</option>
<option name="Africa/Lubumbashi" <?php if ($settings['timezone'] == "Africa/Lubumbashi") { echo 'selected="selected"'; } ?>>Africa/Lubumbashi</option>
<option name="Africa/Lusaka" <?php if ($settings['timezone'] == "Africa/Lusaka") { echo 'selected="selected"'; } ?>>Africa/Lusaka</option>
<option name="Africa/Malabo" <?php if ($settings['timezone'] == "Africa/Malabo") { echo 'selected="selected"'; } ?>>Africa/Malabo</option>
<option name="Africa/Maputo" <?php if ($settings['timezone'] == "Africa/Maputo") { echo 'selected="selected"'; } ?>>Africa/Maputo</option>
<option name="Africa/Maseru" <?php if ($settings['timezone'] == "Africa/Maseru") { echo 'selected="selected"'; } ?>>Africa/Maseru</option>
<option name="Africa/Mbabane" <?php if ($settings['timezone'] == "Africa/Mbabane") { echo 'selected="selected"'; } ?>>Africa/Mbabane</option>
<option name="Africa/Mogadishu" <?php if ($settings['timezone'] == "Africa/Mogadishu") { echo 'selected="selected"'; } ?>>Africa/Mogadishu</option>
<option name="Africa/Monrovia" <?php if ($settings['timezone'] == "Africa/Monrovia") { echo 'selected="selected"'; } ?>>Africa/Monrovia</option>
<option name="Africa/Nairobi" <?php if ($settings['timezone'] == "Africa/Nairobi") { echo 'selected="selected"'; } ?>>Africa/Nairobi</option>
<option name="Africa/Ndjamena" <?php if ($settings['timezone'] == "Africa/Ndjamena") { echo 'selected="selected"'; } ?>>Africa/Ndjamena</option>
<option name="Africa/Niamey" <?php if ($settings['timezone'] == "Africa/Niamey") { echo 'selected="selected"'; } ?>>Africa/Niamey</option>
<option name="Africa/Nouakchott" <?php if ($settings['timezone'] == "Africa/Nouakchott") { echo 'selected="selected"'; } ?>>Africa/Nouakchott</option>
<option name="Africa/Ouagadougou" <?php if ($settings['timezone'] == "Africa/Ouagadougou") { echo 'selected="selected"'; } ?>>Africa/Ouagadougou</option>
<option name="Africa/Porto-Novo" <?php if ($settings['timezone'] == "Africa/Porto-Novo") { echo 'selected="selected"'; } ?>>Africa/Porto-Novo</option>
<option name="Africa/Sao_Tome" <?php if ($settings['timezone'] == "Africa/Sao_Tome") { echo 'selected="selected"'; } ?>>Africa/Sao_Tome</option>
<option name="Africa/Tripoli" <?php if ($settings['timezone'] == "Africa/Tripoli") { echo 'selected="selected"'; } ?>>Africa/Tripoli</option>
<option name="Africa/Tunis" <?php if ($settings['timezone'] == "Africa/Tunis") { echo 'selected="selected"'; } ?>>Africa/Tunis</option>
<option name="Africa/Windhoek" <?php if ($settings['timezone'] == "Africa/Windhoek") { echo 'selected="selected"'; } ?>>Africa/Windhoek</option>
<optgroup>
<optgroup label="America">
<option name="America/Adak" <?php if ($settings['timezone'] == "America/Adak") { echo 'selected="selected"'; } ?>>America/Adak</option>
<option name="America/Anchorage" <?php if ($settings['timezone'] == "America/Anchorage") { echo 'selected="selected"'; } ?>>America/Anchorage</option>
<option name="America/Anguilla" <?php if ($settings['timezone'] == "America/Anguilla") { echo 'selected="selected"'; } ?>>America/Anguilla</option>
<option name="America/Antigua" <?php if ($settings['timezone'] == "America/Antigua") { echo 'selected="selected"'; } ?>>America/Antigua</option>
<option name="America/Araguaina" <?php if ($settings['timezone'] == "America/Araguaina") { echo 'selected="selected"'; } ?>>America/Araguaina</option>
<option name="America/Argentina/Buenos_Aires" <?php if ($settings['timezone'] == "America/Argentina/Buenos_Aires") { echo 'selected="selected"'; } ?>>America/Argentina/Buenos_Aires</option>
<option name="America/Argentina/Catamarca" <?php if ($settings['timezone'] == "America/Argentina/Catamarca") { echo 'selected="selected"'; } ?>>America/Argentina/Catamarca</option>
<option name="America/Argentina/Cordoba" <?php if ($settings['timezone'] == "America/Argentina/Cordoba") { echo 'selected="selected"'; } ?>>America/Argentina/Cordoba</option>
<option name="America/Argentina/Jujuy" <?php if ($settings['timezone'] == "America/Argentina/Jujuy") { echo 'selected="selected"'; } ?>>America/Argentina/Jujuy</option>
<option name="America/Argentina/La_Rioja" <?php if ($settings['timezone'] == "America/Argentina/La_Rioja") { echo 'selected="selected"'; } ?>>America/Argentina/La_Rioja</option>
<option name="America/Argentina/Mendoza" <?php if ($settings['timezone'] == "America/Argentina/Mendoza") { echo 'selected="selected"'; } ?>>America/Argentina/Mendoza</option>
<option name="America/Argentina/Rio_Gallegos" <?php if ($settings['timezone'] == "America/Argentina/Rio_Gallegos") { echo 'selected="selected"'; } ?>>America/Argentina/Rio_Gallegos</option>
<option name="America/Argentina/Salta" <?php if ($settings['timezone'] == "America/Argentina/Salta") { echo 'selected="selected"'; } ?>>America/Argentina/Salta</option>
<option name="America/Argentina/San_Juan" <?php if ($settings['timezone'] == "America/Argentina/San_Juan") { echo 'selected="selected"'; } ?>>America/Argentina/San_Juan</option>
<option name="America/Argentina/San_Luis" <?php if ($settings['timezone'] == "America/Argentina/San_Luis") { echo 'selected="selected"'; } ?>>America/Argentina/San_Luis</option>
<option name="America/Argentina/Tucuman" <?php if ($settings['timezone'] == "America/Argentina/Tucuman") { echo 'selected="selected"'; } ?>>America/Argentina/Tucuman</option>
<option name="America/Argentina/Ushuaia" <?php if ($settings['timezone'] == "America/Argentina/Ushuaia") { echo 'selected="selected"'; } ?>>America/Argentina/Ushuaia</option>
<option name="America/Aruba" <?php if ($settings['timezone'] == "America/Aruba") { echo 'selected="selected"'; } ?>>America/Aruba</option>
<option name="America/Asuncion" <?php if ($settings['timezone'] == "America/Asuncion") { echo 'selected="selected"'; } ?>>America/Asuncion</option>
<option name="America/Atikokan" <?php if ($settings['timezone'] == "America/Atikokan") { echo 'selected="selected"'; } ?>>America/Atikokan</option>
<option name="America/Bahia" <?php if ($settings['timezone'] == "America/Bahia") { echo 'selected="selected"'; } ?>>America/Bahia</option>
<option name="America/Bahia_Banderas" <?php if ($settings['timezone'] == "America/Bahia_Banderas") { echo 'selected="selected"'; } ?>>America/Bahia_Banderas</option>
<option name="America/Barbados" <?php if ($settings['timezone'] == "America/Barbados") { echo 'selected="selected"'; } ?>>America/Barbados</option>
<option name="America/Belem" <?php if ($settings['timezone'] == "America/Belem") { echo 'selected="selected"'; } ?>>America/Belem</option>
<option name="America/Belize" <?php if ($settings['timezone'] == "America/Belize") { echo 'selected="selected"'; } ?>>America/Belize</option>
<option name="America/Blanc-Sablon" <?php if ($settings['timezone'] == "America/Blanc-Sablon") { echo 'selected="selected"'; } ?>>America/Blanc-Sablon</option>
<option name="America/Boa_Vista" <?php if ($settings['timezone'] == "America/Boa_Vista") { echo 'selected="selected"'; } ?>>America/Boa_Vista</option>
<option name="America/Bogota" <?php if ($settings['timezone'] == "America/Bogota") { echo 'selected="selected"'; } ?>>America/Bogota</option>
<option name="America/Boise" <?php if ($settings['timezone'] == "America/Boise") { echo 'selected="selected"'; } ?>>America/Boise</option>
<option name="America/Cambridge_Bay" <?php if ($settings['timezone'] == "America/Cambridge_Bay") { echo 'selected="selected"'; } ?>>America/Cambridge_Bay</option>
<option name="America/Campo_Grande" <?php if ($settings['timezone'] == "America/Campo_Grande") { echo 'selected="selected"'; } ?>>America/Campo_Grande</option>
<option name="America/Cancun" <?php if ($settings['timezone'] == "America/Cancun") { echo 'selected="selected"'; } ?>>America/Cancun</option>
<option name="America/Caracas" <?php if ($settings['timezone'] == "America/Caracas") { echo 'selected="selected"'; } ?>>America/Caracas</option>
<option name="America/Cayenne" <?php if ($settings['timezone'] == "America/Cayenne") { echo 'selected="selected"'; } ?>>America/Cayenne</option>
<option name="America/Cayman" <?php if ($settings['timezone'] == "America/Cayman") { echo 'selected="selected"'; } ?>>America/Cayman</option>
<option name="America/Chicago" <?php if ($settings['timezone'] == "America/Chicago") { echo 'selected="selected"'; } ?>>America/Chicago</option>
<option name="America/Chihuahua" <?php if ($settings['timezone'] == "America/Chihuahua") { echo 'selected="selected"'; } ?>>America/Chihuahua</option>
<option name="America/Costa_Rica" <?php if ($settings['timezone'] == "America/Costa_Rica") { echo 'selected="selected"'; } ?>>America/Costa_Rica</option>
<option name="America/Creston" <?php if ($settings['timezone'] == "America/Creston") { echo 'selected="selected"'; } ?>>America/Creston</option>
<option name="America/Cuiaba" <?php if ($settings['timezone'] == "America/Cuiaba") { echo 'selected="selected"'; } ?>>America/Cuiaba</option>
<option name="America/Curacao" <?php if ($settings['timezone'] == "America/Curacao") { echo 'selected="selected"'; } ?>>America/Curacao</option>
<option name="America/Danmarkshavn" <?php if ($settings['timezone'] == "America/Danmarkshavn") { echo 'selected="selected"'; } ?>>America/Danmarkshavn</option>
<option name="America/Dawson" <?php if ($settings['timezone'] == "America/Dawson") { echo 'selected="selected"'; } ?>>America/Dawson</option>
<option name="America/Dawson_Creek" <?php if ($settings['timezone'] == "America/Dawson_Creek") { echo 'selected="selected"'; } ?>>America/Dawson_Creek</option>
<option name="America/Denver" <?php if ($settings['timezone'] == "America/Denver") { echo 'selected="selected"'; } ?>>America/Denver</option>
<option name="America/Detroit" <?php if ($settings['timezone'] == "America/Detroit") { echo 'selected="selected"'; } ?>>America/Detroit</option>
<option name="America/Dominica" <?php if ($settings['timezone'] == "America/Dominica") { echo 'selected="selected"'; } ?>>America/Dominica</option>
<option name="America/Edmonton" <?php if ($settings['timezone'] == "America/Edmonton") { echo 'selected="selected"'; } ?>>America/Edmonton</option>
<option name="America/Eirunepe" <?php if ($settings['timezone'] == "America/Eirunepe") { echo 'selected="selected"'; } ?>>America/Eirunepe</option>
<option name="America/El_Salvador" <?php if ($settings['timezone'] == "America/El_Salvador") { echo 'selected="selected"'; } ?>>America/El_Salvador</option>
<option name="America/Fortaleza" <?php if ($settings['timezone'] == "America/Fortaleza") { echo 'selected="selected"'; } ?>>America/Fortaleza</option>
<option name="America/Glace_Bay" <?php if ($settings['timezone'] == "America/Glace_Bay") { echo 'selected="selected"'; } ?>>America/Glace_Bay</option>
<option name="America/Godthab" <?php if ($settings['timezone'] == "America/Godthab") { echo 'selected="selected"'; } ?>>America/Godthab</option>
<option name="America/Goose_Bay" <?php if ($settings['timezone'] == "America/Goose_Bay") { echo 'selected="selected"'; } ?>>America/Goose_Bay</option>
<option name="America/Grand_Turk" <?php if ($settings['timezone'] == "America/Grand_Turk") { echo 'selected="selected"'; } ?>>America/Grand_Turk</option>
<option name="America/Grenada" <?php if ($settings['timezone'] == "America/Grenada") { echo 'selected="selected"'; } ?>>America/Grenada</option>
<option name="America/Guadeloupe" <?php if ($settings['timezone'] == "America/Guadeloupe") { echo 'selected="selected"'; } ?>>America/Guadeloupe</option>
<option name="America/Guatemala" <?php if ($settings['timezone'] == "America/Guatemala") { echo 'selected="selected"'; } ?>>America/Guatemala</option>
<option name="America/Guayaquil" <?php if ($settings['timezone'] == "America/Guayaquil") { echo 'selected="selected"'; } ?>>America/Guayaquil</option>
<option name="America/Guyana" <?php if ($settings['timezone'] == "America/Guyana") { echo 'selected="selected"'; } ?>>America/Guyana</option>
<option name="America/Halifax" <?php if ($settings['timezone'] == "America/Halifax") { echo 'selected="selected"'; } ?>>America/Halifax</option>
<option name="America/Havana" <?php if ($settings['timezone'] == "America/Havana") { echo 'selected="selected"'; } ?>>America/Havana</option>
<option name="America/Hermosillo" <?php if ($settings['timezone'] == "America/Hermosillo") { echo 'selected="selected"'; } ?>>America/Hermosillo</option>
<option name="America/Indiana/Indianapolis" <?php if ($settings['timezone'] == "America/Indiana/Indianapolis") { echo 'selected="selected"'; } ?>>America/Indiana/Indianapolis</option>
<option name="America/Indiana/Knox" <?php if ($settings['timezone'] == "America/Indiana/Knox") { echo 'selected="selected"'; } ?>>America/Indiana/Knox</option>
<option name="America/Indiana/Marengo" <?php if ($settings['timezone'] == "America/Indiana/Marengo") { echo 'selected="selected"'; } ?>>America/Indiana/Marengo</option>
<option name="America/Indiana/Petersburg" <?php if ($settings['timezone'] == "America/Indiana/Petersburg") { echo 'selected="selected"'; } ?>>America/Indiana/Petersburg</option>
<option name="America/Indiana/Tell_City" <?php if ($settings['timezone'] == "America/Indiana/Tell_City") { echo 'selected="selected"'; } ?>>America/Indiana/Tell_City</option>
<option name="America/Indiana/Vevay" <?php if ($settings['timezone'] == "America/Indiana/Vevay") { echo 'selected="selected"'; } ?>>America/Indiana/Vevay</option>
<option name="America/Indiana/Vincennes" <?php if ($settings['timezone'] == "America/Indiana/Vincennes") { echo 'selected="selected"'; } ?>>America/Indiana/Vincennes</option>
<option name="America/Indiana/Winamac" <?php if ($settings['timezone'] == "America/Indiana/Winamac") { echo 'selected="selected"'; } ?>>America/Indiana/Winamac</option>
<option name="America/Inuvik" <?php if ($settings['timezone'] == "America/Inuvik") { echo 'selected="selected"'; } ?>>America/Inuvik</option>
<option name="America/Iqaluit" <?php if ($settings['timezone'] == "America/Iqaluit") { echo 'selected="selected"'; } ?>>America/Iqaluit</option>
<option name="America/Jamaica" <?php if ($settings['timezone'] == "America/Jamaica") { echo 'selected="selected"'; } ?>>America/Jamaica</option>
<option name="America/Juneau" <?php if ($settings['timezone'] == "America/Juneau") { echo 'selected="selected"'; } ?>>America/Juneau</option>
<option name="America/Kentucky/Louisville" <?php if ($settings['timezone'] == "America/Kentucky/Louisville") { echo 'selected="selected"'; } ?>>America/Kentucky/Louisville</option>
<option name="America/Kentucky/Monticello" <?php if ($settings['timezone'] == "America/Kentucky/Monticello") { echo 'selected="selected"'; } ?>>America/Kentucky/Monticello</option>
<option name="America/Kralendijk" <?php if ($settings['timezone'] == "America/Kralendijk") { echo 'selected="selected"'; } ?>>America/Kralendijk</option>
<option name="America/La_Paz" <?php if ($settings['timezone'] == "America/La_Paz") { echo 'selected="selected"'; } ?>>America/La_Paz</option>
<option name="America/Lima" <?php if ($settings['timezone'] == "America/Lima") { echo 'selected="selected"'; } ?>>America/Lima</option>
<option name="America/Los_Angeles" <?php if ($settings['timezone'] == "America/Los_Angeles") { echo 'selected="selected"'; } ?>>America/Los_Angeles</option>
<option name="America/Lower_Princes" <?php if ($settings['timezone'] == "America/Lower_Princes") { echo 'selected="selected"'; } ?>>America/Lower_Princes</option>
<option name="America/Maceio" <?php if ($settings['timezone'] == "America/Maceio") { echo 'selected="selected"'; } ?>>America/Maceio</option>
<option name="America/Managua" <?php if ($settings['timezone'] == "America/Managua") { echo 'selected="selected"'; } ?>>America/Managua</option>
<option name="America/Manaus" <?php if ($settings['timezone'] == "America/Manaus") { echo 'selected="selected"'; } ?>>America/Manaus</option>
<option name="America/Marigot" <?php if ($settings['timezone'] == "America/Marigot") { echo 'selected="selected"'; } ?>>America/Marigot</option>
<option name="America/Martinique" <?php if ($settings['timezone'] == "America/Martinique") { echo 'selected="selected"'; } ?>>America/Martinique</option>
<option name="America/Matamoros" <?php if ($settings['timezone'] == "America/Matamoros") { echo 'selected="selected"'; } ?>>America/Matamoros</option>
<option name="America/Mazatlan" <?php if ($settings['timezone'] == "America/Mazatlan") { echo 'selected="selected"'; } ?>>America/Mazatlan</option>
<option name="America/Menominee" <?php if ($settings['timezone'] == "America/Menominee") { echo 'selected="selected"'; } ?>>America/Menominee</option>
<option name="America/Merida" <?php if ($settings['timezone'] == "America/Merida") { echo 'selected="selected"'; } ?>>America/Merida</option>
<option name="America/Metlakatla" <?php if ($settings['timezone'] == "America/Metlakatla") { echo 'selected="selected"'; } ?>>America/Metlakatla</option>
<option name="America/Mexico_City" <?php if ($settings['timezone'] == "America/Mexico_City") { echo 'selected="selected"'; } ?>>America/Mexico_City</option>
<option name="America/Miquelon" <?php if ($settings['timezone'] == "America/Miquelon") { echo 'selected="selected"'; } ?>>America/Miquelon</option>
<option name="America/Moncton" <?php if ($settings['timezone'] == "America/Moncton") { echo 'selected="selected"'; } ?>>America/Moncton</option>
<option name="America/Monterrey" <?php if ($settings['timezone'] == "America/Monterrey") { echo 'selected="selected"'; } ?>>America/Monterrey</option>
<option name="America/Montevideo" <?php if ($settings['timezone'] == "America/Montevideo") { echo 'selected="selected"'; } ?>>America/Montevideo</option>
<option name="America/Montreal" <?php if ($settings['timezone'] == "America/Montreal") { echo 'selected="selected"'; } ?>>America/Montreal</option>
<option name="America/Montserrat" <?php if ($settings['timezone'] == "America/Montserrat") { echo 'selected="selected"'; } ?>>America/Montserrat</option>
<option name="America/Nassau" <?php if ($settings['timezone'] == "America/Nassau") { echo 'selected="selected"'; } ?>>America/Nassau</option>
<option name="America/New_York" <?php if ($settings['timezone'] == "America/New_York") { echo 'selected="selected"'; } ?>>America/New_York</option>
<option name="America/Nipigon" <?php if ($settings['timezone'] == "America/Nipigon") { echo 'selected="selected"'; } ?>>America/Nipigon</option>
<option name="America/Nome" <?php if ($settings['timezone'] == "America/Nome") { echo 'selected="selected"'; } ?>>America/Nome</option>
<option name="America/Noronha" <?php if ($settings['timezone'] == "America/Noronha") { echo 'selected="selected"'; } ?>>America/Noronha</option>
<option name="America/North_Dakota/Beulah" <?php if ($settings['timezone'] == "America/North_Dakota/Beulah") { echo 'selected="selected"'; } ?>>America/North_Dakota/Beulah</option>
<option name="America/North_Dakota/Center" <?php if ($settings['timezone'] == "America/North_Dakota/Center") { echo 'selected="selected"'; } ?>>America/North_Dakota/Center</option>
<option name="America/North_Dakota/New_Salem" <?php if ($settings['timezone'] == "America/North_Dakota/New_Salem") { echo 'selected="selected"'; } ?>>America/North_Dakota/New_Salem</option>
<option name="America/Ojinaga" <?php if ($settings['timezone'] == "America/Ojinaga") { echo 'selected="selected"'; } ?>>America/Ojinaga</option>
<option name="America/Panama" <?php if ($settings['timezone'] == "America/Panama") { echo 'selected="selected"'; } ?>>America/Panama</option>
<option name="America/Pangnirtung" <?php if ($settings['timezone'] == "America/Pangnirtung") { echo 'selected="selected"'; } ?>>America/Pangnirtung</option>
<option name="America/Paramaribo" <?php if ($settings['timezone'] == "America/Paramaribo") { echo 'selected="selected"'; } ?>>America/Paramaribo</option>
<option name="America/Phoenix" <?php if ($settings['timezone'] == "America/Phoenix") { echo 'selected="selected"'; } ?>>America/Phoenix</option>
<option name="America/Port-au-Prince" <?php if ($settings['timezone'] == "America/Port-au-Prince") { echo 'selected="selected"'; } ?>>America/Port-au-Prince</option>
<option name="America/Port_of_Spain" <?php if ($settings['timezone'] == "America/Port_of_Spain") { echo 'selected="selected"'; } ?>>America/Port_of_Spain</option>
<option name="America/Porto_Velho" <?php if ($settings['timezone'] == "America/Porto_Velho") { echo 'selected="selected"'; } ?>>America/Porto_Velho</option>
<option name="America/Puerto_Rico" <?php if ($settings['timezone'] == "America/Puerto_Rico") { echo 'selected="selected"'; } ?>>America/Puerto_Rico</option>
<option name="America/Rainy_River" <?php if ($settings['timezone'] == "America/Rainy_River") { echo 'selected="selected"'; } ?>>America/Rainy_River</option>
<option name="America/Rankin_Inlet" <?php if ($settings['timezone'] == "America/Rankin_Inlet") { echo 'selected="selected"'; } ?>>America/Rankin_Inlet</option>
<option name="America/Recife" <?php if ($settings['timezone'] == "America/Recife") { echo 'selected="selected"'; } ?>>America/Recife</option>
<option name="America/Regina" <?php if ($settings['timezone'] == "America/Regina") { echo 'selected="selected"'; } ?>>America/Regina</option>
<option name="America/Resolute" <?php if ($settings['timezone'] == "America/Resolute") { echo 'selected="selected"'; } ?>>America/Resolute</option>
<option name="America/Rio_Branco" <?php if ($settings['timezone'] == "America/Rio_Branco") { echo 'selected="selected"'; } ?>>America/Rio_Branco</option>
<option name="America/Santa_Isabel" <?php if ($settings['timezone'] == "America/Santa_Isabel") { echo 'selected="selected"'; } ?>>America/Santa_Isabel</option>
<option name="America/Santarem" <?php if ($settings['timezone'] == "America/Santarem") { echo 'selected="selected"'; } ?>>America/Santarem</option>
<option name="America/Santiago" <?php if ($settings['timezone'] == "America/Santiago") { echo 'selected="selected"'; } ?>>America/Santiago</option>
<option name="America/Santo_Domingo" <?php if ($settings['timezone'] == "America/Santo_Domingo") { echo 'selected="selected"'; } ?>>America/Santo_Domingo</option>
<option name="America/Sao_Paulo" <?php if ($settings['timezone'] == "America/Sao_Paulo") { echo 'selected="selected"'; } ?>>America/Sao_Paulo</option>
<option name="America/Scoresbysund" <?php if ($settings['timezone'] == "America/Scoresbysund") { echo 'selected="selected"'; } ?>>America/Scoresbysund</option>
<option name="America/Shiprock" <?php if ($settings['timezone'] == "America/Shiprock") { echo 'selected="selected"'; } ?>>America/Shiprock</option>
<option name="America/Sitka" <?php if ($settings['timezone'] == "America/Sitka") { echo 'selected="selected"'; } ?>>America/Sitka</option>
<option name="America/St_Barthelemy" <?php if ($settings['timezone'] == "America/St_Barthelemy") { echo 'selected="selected"'; } ?>>America/St_Barthelemy</option>
<option name="America/St_Johns" <?php if ($settings['timezone'] == "America/St_Johns") { echo 'selected="selected"'; } ?>>America/St_Johns</option>
<option name="America/St_Kitts" <?php if ($settings['timezone'] == "America/St_Kitts") { echo 'selected="selected"'; } ?>>America/St_Kitts</option>
<option name="America/St_Lucia" <?php if ($settings['timezone'] == "America/St_Lucia") { echo 'selected="selected"'; } ?>>America/St_Lucia</option>
<option name="America/St_Thomas" <?php if ($settings['timezone'] == "America/St_Thomas") { echo 'selected="selected"'; } ?>>America/St_Thomas</option>
<option name="America/St_Vincent" <?php if ($settings['timezone'] == "America/St_Vincent") { echo 'selected="selected"'; } ?>>America/St_Vincent</option>
<option name="America/Swift_Current" <?php if ($settings['timezone'] == "America/Swift_Current") { echo 'selected="selected"'; } ?>>America/Swift_Current</option>
<option name="America/Tegucigalpa" <?php if ($settings['timezone'] == "America/Tegucigalpa") { echo 'selected="selected"'; } ?>>America/Tegucigalpa</option>
<option name="America/Thule" <?php if ($settings['timezone'] == "America/Thule") { echo 'selected="selected"'; } ?>>America/Thule</option>
<option name="America/Thunder_Bay" <?php if ($settings['timezone'] == "America/Thunder_Bay") { echo 'selected="selected"'; } ?>>America/Thunder_Bay</option>
<option name="America/Tijuana" <?php if ($settings['timezone'] == "America/Tijuana") { echo 'selected="selected"'; } ?>>America/Tijuana</option>
<option name="America/Toronto" <?php if ($settings['timezone'] == "America/Toronto") { echo 'selected="selected"'; } ?>>America/Toronto</option>
<option name="America/Tortola" <?php if ($settings['timezone'] == "America/Tortola") { echo 'selected="selected"'; } ?>>America/Tortola</option>
<option name="America/Vancouver" <?php if ($settings['timezone'] == "America/Vancouver") { echo 'selected="selected"'; } ?>>America/Vancouver</option>
<option name="America/Whitehorse" <?php if ($settings['timezone'] == "America/Whitehorse") { echo 'selected="selected"'; } ?>>America/Whitehorse</option>
<option name="America/Winnipeg" <?php if ($settings['timezone'] == "America/Winnipeg") { echo 'selected="selected"'; } ?>>America/Winnipeg</option>
<option name="America/Yakutat" <?php if ($settings['timezone'] == "America/Yakutat") { echo 'selected="selected"'; } ?>>America/Yakutat</option>
<option name="America/Yellowknife" <?php if ($settings['timezone'] == "America/Yellowknife") { echo 'selected="selected"'; } ?>>America/Yellowknife</option>
<optgroup>
<optgroup label="Antarctica">
<option name="Antarctica/Casey" <?php if ($settings['timezone'] == "Antarctica/Casey") { echo 'selected="selected"'; } ?>>Antarctica/Casey</option>
<option name="Antarctica/Davis" <?php if ($settings['timezone'] == "Antarctica/Davis") { echo 'selected="selected"'; } ?>>Antarctica/Davis</option>
<option name="Antarctica/DumontDUrville" <?php if ($settings['timezone'] == "Antarctica/DumontDUrville") { echo 'selected="selected"'; } ?>>Antarctica/DumontDUrville</option>
<option name="Antarctica/Macquarie" <?php if ($settings['timezone'] == "Antarctica/Macquarie") { echo 'selected="selected"'; } ?>>Antarctica/Macquarie</option>
<option name="Antarctica/Mawson" <?php if ($settings['timezone'] == "Antarctica/Mawson") { echo 'selected="selected"'; } ?>>Antarctica/Mawson</option>
<option name="Antarctica/McMurdo" <?php if ($settings['timezone'] == "Antarctica/McMurdo") { echo 'selected="selected"'; } ?>>Antarctica/McMurdo</option>
<option name="Antarctica/Palmer" <?php if ($settings['timezone'] == "Antarctica/Palmer") { echo 'selected="selected"'; } ?>>Antarctica/Palmer</option>
<option name="Antarctica/Rothera" <?php if ($settings['timezone'] == "Antarctica/Rothera") { echo 'selected="selected"'; } ?>>Antarctica/Rothera</option>
<option name="Antarctica/South_Pole" <?php if ($settings['timezone'] == "Antarctica/South_Pole") { echo 'selected="selected"'; } ?>>Antarctica/South_Pole</option>
<option name="Antarctica/Syowa" <?php if ($settings['timezone'] == "Antarctica/Syowa") { echo 'selected="selected"'; } ?>>Antarctica/Syowa</option>
<option name="Antarctica/Vostok" <?php if ($settings['timezone'] == "Antarctica/Vostok") { echo 'selected="selected"'; } ?>>Antarctica/Vostok</option>
<optgroup>
<optgroup label="Aisa">
<option name="Asia/Aden" <?php if ($settings['timezone'] == "Asia/Aden") { echo 'selected="selected"'; } ?>>Asia/Aden</option>
<option name="Asia/Almaty" <?php if ($settings['timezone'] == "Asia/Almaty") { echo 'selected="selected"'; } ?>>Asia/Almaty</option>
<option name="Asia/Amman" <?php if ($settings['timezone'] == "Asia/Amman") { echo 'selected="selected"'; } ?>>Asia/Amman</option>
<option name="Asia/Anadyr" <?php if ($settings['timezone'] == "Asia/Anadyr") { echo 'selected="selected"'; } ?>>Asia/Anadyr</option>
<option name="Asia/Aqtau" <?php if ($settings['timezone'] == "Asia/Aqtau") { echo 'selected="selected"'; } ?>>Asia/Aqtau</option>
<option name="Asia/Aqtobe" <?php if ($settings['timezone'] == "Asia/Aqtobe") { echo 'selected="selected"'; } ?>>Asia/Aqtobe</option>
<option name="Asia/Ashgabat" <?php if ($settings['timezone'] == "Asia/Ashgabat") { echo 'selected="selected"'; } ?>>Asia/Ashgabat</option>
<option name="Asia/Baghdad" <?php if ($settings['timezone'] == "Asia/Baghdad") { echo 'selected="selected"'; } ?>>Asia/Baghdad</option>
<option name="Asia/Bahrain" <?php if ($settings['timezone'] == "Asia/Bahrain") { echo 'selected="selected"'; } ?>>Asia/Bahrain</option>
<option name="Asia/Baku" <?php if ($settings['timezone'] == "Asia/Baku") { echo 'selected="selected"'; } ?>>Asia/Baku</option>
<option name="Asia/Bangkok" <?php if ($settings['timezone'] == "Asia/Bangkok") { echo 'selected="selected"'; } ?>>Asia/Bangkok</option>
<option name="Asia/Beirut" <?php if ($settings['timezone'] == "Asia/Beirut") { echo 'selected="selected"'; } ?>>Asia/Beirut</option>
<option name="Asia/Bishkek" <?php if ($settings['timezone'] == "Asia/Bishkek") { echo 'selected="selected"'; } ?>>Asia/Bishkek</option>
<option name="Asia/Brunei" <?php if ($settings['timezone'] == "Asia/Brunei") { echo 'selected="selected"'; } ?>>Asia/Brunei</option>
<option name="Asia/Choibalsan" <?php if ($settings['timezone'] == "Asia/Choibalsan") { echo 'selected="selected"'; } ?>>Asia/Choibalsan</option>
<option name="Asia/Chongqing" <?php if ($settings['timezone'] == "Asia/Chongqing") { echo 'selected="selected"'; } ?>>Asia/Chongqing</option>
<option name="Asia/Colombo" <?php if ($settings['timezone'] == "Asia/Colombo") { echo 'selected="selected"'; } ?>>Asia/Colombo</option>
<option name="Asia/Damascus" <?php if ($settings['timezone'] == "Asia/Damascus") { echo 'selected="selected"'; } ?>>Asia/Damascus</option>
<option name="Asia/Dhaka" <?php if ($settings['timezone'] == "Asia/Dhaka") { echo 'selected="selected"'; } ?>>Asia/Dhaka</option>
<option name="Asia/Dili" <?php if ($settings['timezone'] == "Asia/Dili") { echo 'selected="selected"'; } ?>>Asia/Dili</option>
<option name="Asia/Dubai" <?php if ($settings['timezone'] == "Asia/Dubai") { echo 'selected="selected"'; } ?>>Asia/Dubai</option>
<option name="Asia/Dushanbe" <?php if ($settings['timezone'] == "Asia/Dushanbe") { echo 'selected="selected"'; } ?>>Asia/Dushanbe</option>
<option name="Asia/Gaza" <?php if ($settings['timezone'] == "Asia/Gaza") { echo 'selected="selected"'; } ?>>Asia/Gaza</option>
<option name="Asia/Harbin" <?php if ($settings['timezone'] == "Asia/Harbin") { echo 'selected="selected"'; } ?>>Asia/Harbin</option>
<option name="Asia/Hebron" <?php if ($settings['timezone'] == "Asia/Hebron") { echo 'selected="selected"'; } ?>>Asia/Hebron</option>
<option name="Asia/Ho_Chi_Minh" <?php if ($settings['timezone'] == "Asia/Ho_Chi_Minh") { echo 'selected="selected"'; } ?>>Asia/Ho_Chi_Minh</option>
<option name="Asia/Hong_Kong" <?php if ($settings['timezone'] == "Asia/Hong_Kong") { echo 'selected="selected"'; } ?>>Asia/Hong_Kong</option>
<option name="Asia/Hovd" <?php if ($settings['timezone'] == "Asia/Hovd") { echo 'selected="selected"'; } ?>>Asia/Hovd</option>
<option name="Asia/Irkutsk" <?php if ($settings['timezone'] == "Asia/Irkutsk") { echo 'selected="selected"'; } ?>>Asia/Irkutsk</option>
<option name="Asia/Jakarta" <?php if ($settings['timezone'] == "Asia/Jakarta") { echo 'selected="selected"'; } ?>>Asia/Jakarta</option>
<option name="Asia/Jayapura" <?php if ($settings['timezone'] == "Asia/Jayapura") { echo 'selected="selected"'; } ?>>Asia/Jayapura</option>
<option name="Asia/Jerusalem" <?php if ($settings['timezone'] == "Asia/Jerusalem") { echo 'selected="selected"'; } ?>>Asia/Jerusalem</option>
<option name="Asia/Kabul" <?php if ($settings['timezone'] == "Asia/Kabul") { echo 'selected="selected"'; } ?>>Asia/Kabul</option>
<option name="Asia/Kamchatka" <?php if ($settings['timezone'] == "Asia/Kamchatka") { echo 'selected="selected"'; } ?>>Asia/Kamchatka</option>
<option name="Asia/Karachi" <?php if ($settings['timezone'] == "Asia/Karachi") { echo 'selected="selected"'; } ?>>Asia/Karachi</option>
<option name="Asia/Kashgar" <?php if ($settings['timezone'] == "Asia/Kashgar") { echo 'selected="selected"'; } ?>>Asia/Kashgar</option>
<option name="Asia/Kathmandu" <?php if ($settings['timezone'] == "Asia/Kathmandu") { echo 'selected="selected"'; } ?>>Asia/Kathmandu</option>
<option name="Asia/Khandyga" <?php if ($settings['timezone'] == "Asia/Khandyga") { echo 'selected="selected"'; } ?>>Asia/Khandyga</option>
<option name="Asia/Kolkata" <?php if ($settings['timezone'] == "Asia/Kolkata") { echo 'selected="selected"'; } ?>>Asia/Kolkata</option>
<option name="Asia/Krasnoyarsk" <?php if ($settings['timezone'] == "Asia/Krasnoyarsk") { echo 'selected="selected"'; } ?>>Asia/Krasnoyarsk</option>
<option name="Asia/Kuala_Lumpur" <?php if ($settings['timezone'] == "Asia/Kuala_Lumpur") { echo 'selected="selected"'; } ?>>Asia/Kuala_Lumpur</option>
<option name="Asia/Kuching" <?php if ($settings['timezone'] == "Asia/Kuching") { echo 'selected="selected"'; } ?>>Asia/Kuching</option>
<option name="Asia/Kuwait" <?php if ($settings['timezone'] == "Asia/Kuwait") { echo 'selected="selected"'; } ?>>Asia/Kuwait</option>
<option name="Asia/Macau" <?php if ($settings['timezone'] == "Asia/Macau") { echo 'selected="selected"'; } ?>>Asia/Macau</option>
<option name="Asia/Magadan" <?php if ($settings['timezone'] == "Asia/Magadan") { echo 'selected="selected"'; } ?>>Asia/Magadan</option>
<option name="Asia/Makassar" <?php if ($settings['timezone'] == "Asia/Makassar") { echo 'selected="selected"'; } ?>>Asia/Makassar</option>
<option name="Asia/Manila" <?php if ($settings['timezone'] == "Asia/Manila") { echo 'selected="selected"'; } ?>>Asia/Manila</option>
<option name="Asia/Muscat" <?php if ($settings['timezone'] == "Asia/Muscat") { echo 'selected="selected"'; } ?>>Asia/Muscat</option>
<option name="Asia/Nicosia" <?php if ($settings['timezone'] == "Asia/Nicosia") { echo 'selected="selected"'; } ?>>Asia/Nicosia</option>
<option name="Asia/Novokuznetsk" <?php if ($settings['timezone'] == "Asia/Novokuznetsk") { echo 'selected="selected"'; } ?>>Asia/Novokuznetsk</option>
<option name="Asia/Novosibirsk" <?php if ($settings['timezone'] == "Asia/Novosibirsk") { echo 'selected="selected"'; } ?>>Asia/Novosibirsk</option>
<option name="Asia/Omsk" <?php if ($settings['timezone'] == "Asia/Omsk") { echo 'selected="selected"'; } ?>>Asia/Omsk</option>
<option name="Asia/Oral" <?php if ($settings['timezone'] == "Asia/Oral") { echo 'selected="selected"'; } ?>>Asia/Oral</option>
<option name="Asia/Phnom_Penh" <?php if ($settings['timezone'] == "Asia/Phnom_Penh") { echo 'selected="selected"'; } ?>>Asia/Phnom_Penh</option>
<option name="Asia/Pontianak" <?php if ($settings['timezone'] == "Asia/Pontianak") { echo 'selected="selected"'; } ?>>Asia/Pontianak</option>
<option name="Asia/Pyongyang" <?php if ($settings['timezone'] == "Asia/Pyongyang") { echo 'selected="selected"'; } ?>>Asia/Pyongyang</option>
<option name="Asia/Qatar" <?php if ($settings['timezone'] == "Asia/Qatar") { echo 'selected="selected"'; } ?>>Asia/Qatar</option>
<option name="Asia/Qyzylorda" <?php if ($settings['timezone'] == "Asia/Qyzylorda") { echo 'selected="selected"'; } ?>>Asia/Qyzylorda</option>
<option name="Asia/Rangoon" <?php if ($settings['timezone'] == "Asia/Rangoon") { echo 'selected="selected"'; } ?>>Asia/Rangoon</option>
<option name="Asia/Riyadh" <?php if ($settings['timezone'] == "Asia/Riyadh") { echo 'selected="selected"'; } ?>>Asia/Riyadh</option>
<option name="Asia/Sakhalin" <?php if ($settings['timezone'] == "Asia/Sakhalin") { echo 'selected="selected"'; } ?>>Asia/Sakhalin</option>
<option name="Asia/Samarkand" <?php if ($settings['timezone'] == "Asia/Samarkand") { echo 'selected="selected"'; } ?>>Asia/Samarkand</option>
<option name="Asia/Seoul" <?php if ($settings['timezone'] == "Asia/Seoul") { echo 'selected="selected"'; } ?>>Asia/Seoul</option>
<option name="Asia/Shanghai" <?php if ($settings['timezone'] == "Asia/Shanghai") { echo 'selected="selected"'; } ?>>Asia/Shanghai</option>
<option name="Asia/Singapore" <?php if ($settings['timezone'] == "Asia/Singapore") { echo 'selected="selected"'; } ?>>Asia/Singapore</option>
<option name="Asia/Taipei" <?php if ($settings['timezone'] == "Asia/Taipei") { echo 'selected="selected"'; } ?>>Asia/Taipei</option>
<option name="Asia/Tashkent" <?php if ($settings['timezone'] == "Asia/Tashkent") { echo 'selected="selected"'; } ?>>Asia/Tashkent</option>
<option name="Asia/Tbilisi" <?php if ($settings['timezone'] == "Asia/Tbilisi") { echo 'selected="selected"'; } ?>>Asia/Tbilisi</option>
<option name="Asia/Tehran" <?php if ($settings['timezone'] == "Asia/Tehran") { echo 'selected="selected"'; } ?>>Asia/Tehran</option>
<option name="Asia/Thimphu" <?php if ($settings['timezone'] == "Asia/Thimphu") { echo 'selected="selected"'; } ?>>Asia/Thimphu</option>
<option name="Asia/Tokyo" <?php if ($settings['timezone'] == "Asia/Tokyo") { echo 'selected="selected"'; } ?>>Asia/Tokyo</option>
<option name="Asia/Ulaanbaatar" <?php if ($settings['timezone'] == "Asia/Ulaanbaatar") { echo 'selected="selected"'; } ?>>Asia/Ulaanbaatar</option>
<option name="Asia/Urumqi" <?php if ($settings['timezone'] == "Asia/Urumqi") { echo 'selected="selected"'; } ?>>Asia/Urumqi</option>
<option name="Asia/Ust-Nera" <?php if ($settings['timezone'] == "Asia/Ust-Nera") { echo 'selected="selected"'; } ?>>Asia/Ust-Nera</option>
<option name="Asia/Vientiane" <?php if ($settings['timezone'] == "Asia/Vientiane") { echo 'selected="selected"'; } ?>>Asia/Vientiane</option>
<option name="Asia/Vladivostok" <?php if ($settings['timezone'] == "Asia/Vladivostok") { echo 'selected="selected"'; } ?>>Asia/Vladivostok</option>
<option name="Asia/Yakutsk" <?php if ($settings['timezone'] == "Asia/Yakutsk") { echo 'selected="selected"'; } ?>>Asia/Yakutsk</option>
<option name="Asia/Yekaterinburg" <?php if ($settings['timezone'] == "Asia/Yekaterinburg") { echo 'selected="selected"'; } ?>>Asia/Yekaterinburg</option>
<option name="Asia/Yerevan" <?php if ($settings['timezone'] == "Asia/Yerevan") { echo 'selected="selected"'; } ?>>Asia/Yerevan</option>
<optgroup>
<optgroup label="Atlantic">
<option name="Atlantic/Azores" <?php if ($settings['timezone'] == "Atlantic/Azores") { echo 'selected="selected"'; } ?>>Atlantic/Azores</option>
<option name="Atlantic/Bermuda" <?php if ($settings['timezone'] == "Atlantic/Bermuda") { echo 'selected="selected"'; } ?>>Atlantic/Bermuda</option>
<option name="Atlantic/Canary" <?php if ($settings['timezone'] == "Atlantic/Canary") { echo 'selected="selected"'; } ?>>Atlantic/Canary</option>
<option name="Atlantic/Cape_Verde" <?php if ($settings['timezone'] == "Atlantic/Cape_Verde") { echo 'selected="selected"'; } ?>>Atlantic/Cape_Verde</option>
<option name="Atlantic/Faroe" <?php if ($settings['timezone'] == "Atlantic/Faroe") { echo 'selected="selected"'; } ?>>Atlantic/Faroe</option>
<option name="Atlantic/Madeira" <?php if ($settings['timezone'] == "Atlantic/Madeira") { echo 'selected="selected"'; } ?>>Atlantic/Madeira</option>
<option name="Atlantic/Reykjavik" <?php if ($settings['timezone'] == "Atlantic/Reykjavik") { echo 'selected="selected"'; } ?>>Atlantic/Reykjavik</option>
<option name="Atlantic/South_Georgia" <?php if ($settings['timezone'] == "Atlantic/South_Georgia") { echo 'selected="selected"'; } ?>>Atlantic/South_Georgia</option>
<option name="Atlantic/St_Helena" <?php if ($settings['timezone'] == "Atlantic/St_Helena") { echo 'selected="selected"'; } ?>>Atlantic/St_Helena</option>
<option name="Atlantic/Stanley" <?php if ($settings['timezone'] == "Atlantic/Stanley") { echo 'selected="selected"'; } ?>>Atlantic/Stanley</option>
<optgroup>
<optgroup label="Europe">
<option name="Europe/Amsterdam" <?php if ($settings['timezone'] == "Europe/Amsterdam") { echo 'selected="selected"'; } ?>>Europe/Amsterdam</option>
<option name="Europe/Andorra" <?php if ($settings['timezone'] == "Europe/Andorra") { echo 'selected="selected"'; } ?>>Europe/Andorra</option>
<option name="Europe/Athens" <?php if ($settings['timezone'] == "Europe/Athens") { echo 'selected="selected"'; } ?>>Europe/Athens</option>
<option name="Europe/Belgrade" <?php if ($settings['timezone'] == "Europe/Belgrade") { echo 'selected="selected"'; } ?>>Europe/Belgrade</option>
<option name="Europe/Berlin" <?php if ($settings['timezone'] == "Europe/Berlin") { echo 'selected="selected"'; } ?>>Europe/Berlin</option>
<option name="Europe/Bratislava" <?php if ($settings['timezone'] == "Europe/Bratislava") { echo 'selected="selected"'; } ?>>Europe/Bratislava</option>
<option name="Europe/Brussels" <?php if ($settings['timezone'] == "Europe/Brussels") { echo 'selected="selected"'; } ?>>Europe/Brussels</option>
<option name="Europe/Bucharest" <?php if ($settings['timezone'] == "Europe/Bucharest") { echo 'selected="selected"'; } ?>>Europe/Bucharest</option>
<option name="Europe/Budapest" <?php if ($settings['timezone'] == "Europe/Budapest") { echo 'selected="selected"'; } ?>>Europe/Budapest</option>
<option name="Europe/Busingen" <?php if ($settings['timezone'] == "Europe/Busingen") { echo 'selected="selected"'; } ?>>Europe/Busingen</option>
<option name="Europe/Chisinau" <?php if ($settings['timezone'] == "Europe/Chisinau") { echo 'selected="selected"'; } ?>>Europe/Chisinau</option>
<option name="Europe/Copenhagen" <?php if ($settings['timezone'] == "Europe/Copenhagen") { echo 'selected="selected"'; } ?>>Europe/Copenhagen</option>
<option name="Europe/Dublin" <?php if ($settings['timezone'] == "Europe/Dublin") { echo 'selected="selected"'; } ?>>Europe/Dublin</option>
<option name="Europe/Gibraltar" <?php if ($settings['timezone'] == "Europe/Gibraltar") { echo 'selected="selected"'; } ?>>Europe/Gibraltar</option>
<option name="Europe/Guernsey" <?php if ($settings['timezone'] == "Europe/Guernsey") { echo 'selected="selected"'; } ?>>Europe/Guernsey</option>
<option name="Europe/Helsinki" <?php if ($settings['timezone'] == "Europe/Helsinki") { echo 'selected="selected"'; } ?>>Europe/Helsinki</option>
<option name="Europe/Isle_of_Man" <?php if ($settings['timezone'] == "Europe/Isle_of_Man") { echo 'selected="selected"'; } ?>>Europe/Isle_of_Man</option>
<option name="Europe/Istanbul" <?php if ($settings['timezone'] == "Europe/Istanbul") { echo 'selected="selected"'; } ?>>Europe/Istanbul</option>
<option name="Europe/Jersey" <?php if ($settings['timezone'] == "Europe/Jersey") { echo 'selected="selected"'; } ?>>Europe/Jersey</option>
<option name="Europe/Kaliningrad" <?php if ($settings['timezone'] == "Europe/Kaliningrad") { echo 'selected="selected"'; } ?>>Europe/Kaliningrad</option>
<option name="Europe/Kiev" <?php if ($settings['timezone'] == "Europe/Kiev") { echo 'selected="selected"'; } ?>>Europe/Kiev</option>
<option name="Europe/Lisbon" <?php if ($settings['timezone'] == "Europe/Lisbon") { echo 'selected="selected"'; } ?>>Europe/Lisbon</option>
<option name="Europe/Ljubljana" <?php if ($settings['timezone'] == "Europe/Ljubljana") { echo 'selected="selected"'; } ?>>Europe/Ljubljana</option>
<option name="Europe/London" <?php if ($settings['timezone'] == "Europe/London") { echo 'selected="selected"'; } ?>>Europe/London</option>
<option name="Europe/Luxembourg" <?php if ($settings['timezone'] == "Europe/Luxembourg") { echo 'selected="selected"'; } ?>>Europe/Luxembourg</option>
<option name="Europe/Madrid" <?php if ($settings['timezone'] == "Europe/Madrid") { echo 'selected="selected"'; } ?>>Europe/Madrid</option>
<option name="Europe/Malta" <?php if ($settings['timezone'] == "Europe/Malta") { echo 'selected="selected"'; } ?>>Europe/Malta</option>
<option name="Europe/Mariehamn" <?php if ($settings['timezone'] == "Europe/Mariehamn") { echo 'selected="selected"'; } ?>>Europe/Mariehamn</option>
<option name="Europe/Minsk" <?php if ($settings['timezone'] == "Europe/Minsk") { echo 'selected="selected"'; } ?>>Europe/Minsk</option>
<option name="Europe/Monaco" <?php if ($settings['timezone'] == "Europe/Monaco") { echo 'selected="selected"'; } ?>>Europe/Monaco</option>
<option name="Europe/Moscow" <?php if ($settings['timezone'] == "Europe/Moscow") { echo 'selected="selected"'; } ?>>Europe/Moscow</option>
<option name="Europe/Oslo" <?php if ($settings['timezone'] == "Europe/Oslo") { echo 'selected="selected"'; } ?>>Europe/Oslo</option>
<option name="Europe/Paris" <?php if ($settings['timezone'] == "Europe/Paris") { echo 'selected="selected"'; } ?>>Europe/Paris</option>
<option name="Europe/Podgorica" <?php if ($settings['timezone'] == "Europe/Podgorica") { echo 'selected="selected"'; } ?>>Europe/Podgorica</option>
<option name="Europe/Prague" <?php if ($settings['timezone'] == "Europe/Prague") { echo 'selected="selected"'; } ?>>Europe/Prague</option>
<option name="Europe/Riga" <?php if ($settings['timezone'] == "Europe/Riga") { echo 'selected="selected"'; } ?>>Europe/Riga</option>
<option name="Europe/Rome" <?php if ($settings['timezone'] == "Europe/Rome") { echo 'selected="selected"'; } ?>>Europe/Rome</option>
<option name="Europe/Samara" <?php if ($settings['timezone'] == "Europe/Samara") { echo 'selected="selected"'; } ?>>Europe/Samara</option>
<option name="Europe/San_Marino" <?php if ($settings['timezone'] == "Europe/San_Marino") { echo 'selected="selected"'; } ?>>Europe/San_Marino</option>
<option name="Europe/Sarajevo" <?php if ($settings['timezone'] == "Europe/Sarajevo") { echo 'selected="selected"'; } ?>>Europe/Sarajevo</option>
<option name="Europe/Simferopol" <?php if ($settings['timezone'] == "Europe/Simferopol") { echo 'selected="selected"'; } ?>>Europe/Simferopol</option>
<option name="Europe/Skopje" <?php if ($settings['timezone'] == "Europe/Skopje") { echo 'selected="selected"'; } ?>>Europe/Skopje</option>
<option name="Europe/Sofia" <?php if ($settings['timezone'] == "Europe/Sofia") { echo 'selected="selected"'; } ?>>Europe/Sofia</option>
<option name="Europe/Stockholm" <?php if ($settings['timezone'] == "Europe/Stockholm") { echo 'selected="selected"'; } ?>>Europe/Stockholm</option>
<option name="Europe/Tallinn" <?php if ($settings['timezone'] == "Europe/Tallinn") { echo 'selected="selected"'; } ?>>Europe/Tallinn</option>
<option name="Europe/Tirane" <?php if ($settings['timezone'] == "Europe/Tirane") { echo 'selected="selected"'; } ?>>Europe/Tirane</option>
<option name="Europe/Uzhgorod" <?php if ($settings['timezone'] == "Europe/Uzhgorod") { echo 'selected="selected"'; } ?>>Europe/Uzhgorod</option>
<option name="Europe/Vaduz" <?php if ($settings['timezone'] == "Europe/Vaduz") { echo 'selected="selected"'; } ?>>Europe/Vaduz</option>
<option name="Europe/Vatican" <?php if ($settings['timezone'] == "Europe/Vatican") { echo 'selected="selected"'; } ?>>Europe/Vatican</option>
<option name="Europe/Vienna" <?php if ($settings['timezone'] == "Europe/Vienna") { echo 'selected="selected"'; } ?>>Europe/Vienna</option>
<option name="Europe/Vilnius" <?php if ($settings['timezone'] == "Europe/Vilnius") { echo 'selected="selected"'; } ?>>Europe/Vilnius</option>
<option name="Europe/Volgograd" <?php if ($settings['timezone'] == "Europe/Volgograd") { echo 'selected="selected"'; } ?>>Europe/Volgograd</option>
<option name="Europe/Warsaw" <?php if ($settings['timezone'] == "Europe/Warsaw") { echo 'selected="selected"'; } ?>>Europe/Warsaw</option>
<option name="Europe/Zagreb" <?php if ($settings['timezone'] == "Europe/Zagreb") { echo 'selected="selected"'; } ?>>Europe/Zagreb</option>
<option name="Europe/Zaporozhye" <?php if ($settings['timezone'] == "Europe/Zaporozhye") { echo 'selected="selected"'; } ?>>Europe/Zaporozhye</option>
<option name="Europe/Zurich" <?php if ($settings['timezone'] == "Europe/Zurich") { echo 'selected="selected"'; } ?>>Europe/Zurich</option>
<optgroup>
<optgroup label="Indian">
<option name="Indian/Antananarivo" <?php if ($settings['timezone'] == "Indian/Antananarivo") { echo 'selected="selected"'; } ?>>Indian/Antananarivo</option>
<option name="Indian/Chagos" <?php if ($settings['timezone'] == "Indian/Chagos") { echo 'selected="selected"'; } ?>>Indian/Chagos</option>
<option name="Indian/Christmas" <?php if ($settings['timezone'] == "Indian/Christmas") { echo 'selected="selected"'; } ?>>Indian/Christmas</option>
<option name="Indian/Cocos" <?php if ($settings['timezone'] == "Indian/Cocos") { echo 'selected="selected"'; } ?>>Indian/Cocos</option>
<option name="Indian/Comoro" <?php if ($settings['timezone'] == "Indian/Comoro") { echo 'selected="selected"'; } ?>>Indian/Comoro</option>
<option name="Indian/Kerguelen" <?php if ($settings['timezone'] == "Indian/Kerguelen") { echo 'selected="selected"'; } ?>>Indian/Kerguelen</option>
<option name="Indian/Mahe" <?php if ($settings['timezone'] == "Indian/Mahe") { echo 'selected="selected"'; } ?>>Indian/Mahe</option>
<option name="Indian/Maldives" <?php if ($settings['timezone'] == "Indian/Maldives") { echo 'selected="selected"'; } ?>>Indian/Maldives</option>
<option name="Indian/Mauritius" <?php if ($settings['timezone'] == "Indian/Mauritius") { echo 'selected="selected"'; } ?>>Indian/Mauritius</option>
<option name="Indian/Mayotte" <?php if ($settings['timezone'] == "Indian/Mayotte") { echo 'selected="selected"'; } ?>>Indian/Mayotte</option>
<option name="Indian/Reunion" <?php if ($settings['timezone'] == "Indian/Reunion") { echo 'selected="selected"'; } ?>>Indian/Reunion</option>
<optgroup>
<optgroup label="Pacific">
<option name="Pacific/Apia" <?php if ($settings['timezone'] == "Pacific/Apia") { echo 'selected="selected"'; } ?>>Pacific/Apia</option>
<option name="Pacific/Auckland" <?php if ($settings['timezone'] == "Pacific/Auckland") { echo 'selected="selected"'; } ?>>Pacific/Auckland</option>
<option name="Pacific/Chatham" <?php if ($settings['timezone'] == "Pacific/Chatham") { echo 'selected="selected"'; } ?>>Pacific/Chatham</option>
<option name="Pacific/Chuuk" <?php if ($settings['timezone'] == "Pacific/Chuuk") { echo 'selected="selected"'; } ?>>Pacific/Chuuk</option>
<option name="Pacific/Easter" <?php if ($settings['timezone'] == "Pacific/Easter") { echo 'selected="selected"'; } ?>>Pacific/Easter</option>
<option name="Pacific/Efate" <?php if ($settings['timezone'] == "Pacific/Efate") { echo 'selected="selected"'; } ?>>Pacific/Efate</option>
<option name="Pacific/Enderbury" <?php if ($settings['timezone'] == "Pacific/Enderbury") { echo 'selected="selected"'; } ?>>Pacific/Enderbury</option>
<option name="Pacific/Fakaofo" <?php if ($settings['timezone'] == "Pacific/Fakaofo") { echo 'selected="selected"'; } ?>>Pacific/Fakaofo</option>
<option name="Pacific/Fiji" <?php if ($settings['timezone'] == "Pacific/Fiji") { echo 'selected="selected"'; } ?>>Pacific/Fiji</option>
<option name="Pacific/Funafuti" <?php if ($settings['timezone'] == "Pacific/Funafuti") { echo 'selected="selected"'; } ?>>Pacific/Funafuti</option>
<option name="Pacific/Galapagos" <?php if ($settings['timezone'] == "Pacific/Galapagos") { echo 'selected="selected"'; } ?>>Pacific/Galapagos</option>
<option name="Pacific/Gambier" <?php if ($settings['timezone'] == "Pacific/Gambier") { echo 'selected="selected"'; } ?>>Pacific/Gambier</option>
<option name="Pacific/Guadalcanal" <?php if ($settings['timezone'] == "Pacific/Guadalcanal") { echo 'selected="selected"'; } ?>>Pacific/Guadalcanal</option>
<option name="Pacific/Guam" <?php if ($settings['timezone'] == "Pacific/Guam") { echo 'selected="selected"'; } ?>>Pacific/Guam</option>
<option name="Pacific/Honolulu" <?php if ($settings['timezone'] == "Pacific/Honolulu") { echo 'selected="selected"'; } ?>>Pacific/Honolulu</option>
<option name="Pacific/Johnston" <?php if ($settings['timezone'] == "Pacific/Johnston") { echo 'selected="selected"'; } ?>>Pacific/Johnston</option>
<option name="Pacific/Kiritimati" <?php if ($settings['timezone'] == "Pacific/Kiritimati") { echo 'selected="selected"'; } ?>>Pacific/Kiritimati</option>
<option name="Pacific/Kosrae" <?php if ($settings['timezone'] == "Pacific/Kosrae") { echo 'selected="selected"'; } ?>>Pacific/Kosrae</option>
<option name="Pacific/Kwajalein" <?php if ($settings['timezone'] == "Pacific/Kwajalein") { echo 'selected="selected"'; } ?>>Pacific/Kwajalein</option>
<option name="Pacific/Majuro" <?php if ($settings['timezone'] == "Pacific/Majuro") { echo 'selected="selected"'; } ?>>Pacific/Majuro</option>
<option name="Pacific/Marquesas" <?php if ($settings['timezone'] == "Pacific/Marquesas") { echo 'selected="selected"'; } ?>>Pacific/Marquesas</option>
<option name="Pacific/Midway" <?php if ($settings['timezone'] == "Pacific/Midway") { echo 'selected="selected"'; } ?>>Pacific/Midway</option>
<option name="Pacific/Nauru" <?php if ($settings['timezone'] == "Pacific/Nauru") { echo 'selected="selected"'; } ?>>Pacific/Nauru</option>
<option name="Pacific/Niue" <?php if ($settings['timezone'] == "Pacific/Niue") { echo 'selected="selected"'; } ?>>Pacific/Niue</option>
<option name="Pacific/Norfolk" <?php if ($settings['timezone'] == "Pacific/Norfolk") { echo 'selected="selected"'; } ?>>Pacific/Norfolk</option>
<option name="Pacific/Noumea" <?php if ($settings['timezone'] == "Pacific/Noumea") { echo 'selected="selected"'; } ?>>Pacific/Noumea</option>
<option name="Pacific/Pago_Pago" <?php if ($settings['timezone'] == "Pacific/Pago_Pago") { echo 'selected="selected"'; } ?>>Pacific/Pago_Pago</option>
<option name="Pacific/Palau" <?php if ($settings['timezone'] == "Pacific/Palau") { echo 'selected="selected"'; } ?>>Pacific/Palau</option>
<option name="Pacific/Pitcairn" <?php if ($settings['timezone'] == "Pacific/Pitcairn") { echo 'selected="selected"'; } ?>>Pacific/Pitcairn</option>
<option name="Pacific/Pohnpei" <?php if ($settings['timezone'] == "Pacific/Pohnpei") { echo 'selected="selected"'; } ?>>Pacific/Pohnpei</option>
<option name="Pacific/Port_Moresby" <?php if ($settings['timezone'] == "Pacific/Port_Moresby") { echo 'selected="selected"'; } ?>>Pacific/Port_Moresby</option>
<option name="Pacific/Rarotonga" <?php if ($settings['timezone'] == "Pacific/Rarotonga") { echo 'selected="selected"'; } ?>>Pacific/Rarotonga</option>
<option name="Pacific/Saipan" <?php if ($settings['timezone'] == "Pacific/Saipan") { echo 'selected="selected"'; } ?>>Pacific/Saipan</option>
<option name="Pacific/Tahiti" <?php if ($settings['timezone'] == "Pacific/Tahiti") { echo 'selected="selected"'; } ?>>Pacific/Tahiti</option>
<option name="Pacific/Tarawa" <?php if ($settings['timezone'] == "Pacific/Tarawa") { echo 'selected="selected"'; } ?>>Pacific/Tarawa</option>
<option name="Pacific/Tongatapu" <?php if ($settings['timezone'] == "Pacific/Tongatapu") { echo 'selected="selected"'; } ?>>Pacific/Tongatapu</option>
<option name="Pacific/Wake" <?php if ($settings['timezone'] == "Pacific/Wake") { echo 'selected="selected"'; } ?>>Pacific/Wake</option>
<option name="Pacific/Wallis" <?php if ($settings['timezone'] == "Pacific/Wallis") { echo 'selected="selected"'; } ?>>Pacific/Wallis</option>
<optgroup>
</select>
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

		<div class="row-fluid">
			<div class="span3">
				<strong>Username</strong>
			</div>
			<div class="span9 right">
				<input type="text" name="settings[general][username]" value="<?php echo $settings['username']; ?>" size="4" class="span2 center">
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

	<div class="well">
                <h4>API settings</h4>
                <div class="row-fluid">
                        <div class="span3">
                                <strong>API URL</strong>
                        </div>
                        <div class="span9 right">
                                <input type="text" name="settings[api][url]" value="<?php echo $settings['api']['url']; ?>" size="4" class="span4 center">
                        </div>
                </div>

                <div class="row-fluid">
                        <div class="span3">
                                <strong>API Key</strong>
                        </div>
                        <div class="span9 right">
				<input type="text" name="settings[api][key]" value="<?php echo $settings['api']['key']; ?>" size="4" class="span3 center">
                        </div>
                </div>

                <div class="row-fluid">
                        <div class="span3">
                                <strong>API Username</strong>
                        </div>
                        <div class="span9 right">
				<input type="text" name="settings[api][username]" value="<?php echo $settings['api']['username']; ?>" size="4" class="span2 center">
                        </div>
                </div>

                <div class="row-fluid">
                        <div class="span3">
                                <strong>API Server ID</strong>
                        </div>
                        <div class="span9 right">
                                <input type="text" name="settings[api][server]" value="<?php echo $settings['api']['server']; ?>" size="4" class="span2 center">
                        </div>
                </div>
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
                    <input name="settings[network_interface][<?php echo trim($interface['name']); ?>]" value="true" type="checkbox" <?php if ( $settings['network_interface'][trim($interface['name'])] == "true" ) { ?>checked="checked"<?php } ?>>
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
                            <input name="settings[modules][<?php echo $module; ?>]" value="true" type="checkbox" <?php if ( $settings['modules'][$module] == "true" ) { ?>checked="checked"<?php } ?>>
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
