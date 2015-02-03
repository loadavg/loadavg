<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main controller class for LoadAvg 2.0
*
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class loadPlugins
{

	public static $settings_ini; //location of settings.ini file
	public static $_settings; // storing standard settings and/or loaded modules settings
	
	public static $_classes; // storing loaded modules classes
	public static $_plugins; // storing and managing plugins


	/**
	 * setSettings
	 *
	 * Stores the standard settings
	 *
	 * @param string $module name of the module
	 * @param array $args array of module settings
	 */

	public static function setSettings($module, $args)
	{
		@self::$_settings->$module = $args;
	}

	/**
	 * __construct
	 *
	 * Class constructor
	 *
	 */

	public function __construct()
	{

		//set timezone and load in settings
		date_default_timezone_set("UTC");
		self::$settings_ini = "settings.ini.php";

		$this->setSettings('general',
			parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true)
		);


		//get the date and timezone
		date_default_timezone_set(self::$_settings->general['settings']['timezone']);

		//self::$current_date = (isset($_GET['logdate']) && !empty($_GET['logdate'])) ? $_GET['logdate'] : date("Y-m-d");

		//generate list of all modules
		//$this->generateModuleList('modules');
		LoadUtility::generateExtensionList( 'plugins', self::$_plugins );

		//load all charting modules that are enabled
		//$this->loadModules('modules');
		LoadUtility::loadExtensions( 'plugins', self::$_settings, self::$_classes);

	}

	


	/**
	 * updateModuleSettings
	 *
	 * Called by modulesettings to read settings back in after changes...
	 *
	 */

	public static function updateModuleSettings()
	{

		LoadModules::setSettings('general',
			parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true)
		);

				//generate list of all modules
		//$this->generateModuleList('modules');
		LoadUtility::generateExtensionList( 'plugins', self::$_plugins );

		//load all charting modules that are enabled
		//$this->loadModules('modules');
		LoadUtility::loadExtensions( 'plugins', self::$_settings, self::$_classes);
	}


	public function buildPluginMenu( ) {


		//if module is true in settings.ini file then we load it in 
		foreach ( self::$_settings->general['plugins'] as $key => &$value ) {

			//echo 'VALUE: ' . $value . '   ' . 'KEY: ' . $key . '<br>';

			//if value is true plugin is active
			if ( $value == "true" ) {

				$pluginClass = LoadPlugins::$_classes[$key]; 

				$pluginData =  $pluginClass->getPluginData();

				//var_dump ($pluginData);
			}
		}
	}




}
