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
include 'class.Charts.php';

class loadModules
{

	public static $settings_ini; //location of settings.ini file
	public static $_settings; // storing standard settings and/or loaded modules settings
	
	public static $_classes; // storing loaded modules classes
	public static $_modules; // storing and managing modules

	private static $_timezones; // Cache of timezones

	public static $date_range; // range of data to be charted


	/**
	 * setSettings
	 *
	 * Stores the standard settings
	 *
	 * @param string $module name of the module
	 * @param array $args array of module settings
	 */

	public function setSettings($module, $args)
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
		LoadUtility::generateExtensionList( 'modules', self::$_modules );

		//load all charting modules that are enabled
		//$this->loadModules('modules');
		LoadUtility::loadExtensions( 'modules', self::$_settings, self::$_classes);



	}

	/**
	 * setDateRange
	 *
	 * Sets the range for which we want data to be charted
	 *
	 * @param dateRange array of dates and times
	 */

	public function setDateRange($dateRange)
	{
		@self::$date_range = $dateRange;
	}


	/**
	 * parseInfo
	 *
	 * Parses ini file data for a module into lines of text for legend display
	 *
	 * @param array $info array with info lines from the classes INI file
	 * @param array $variables variables to format lines
	 * @param string $class class name of module
	 * @return array $return formatted info lines
	 */

	public static function parseInfo( $info, $variables, $class )
	{
		$return = array();
		
		foreach ( $info as $line ) {
			$line = json_decode($line);

			if (isset($line->type) && isset($line->filename)) {
				$return['info']['line'][] = array("type" => "file", "file" => 'modules' . DIRECTORY_SEPARATOR . $class . DIRECTORY_SEPARATOR . $line->filename);
				continue;
			}

			if ( strstr($line->args, "|")) {
				$lineArgs = explode("|", $line->args);
				$args = array();
				foreach ($lineArgs as $arg) {
					$args[] = $variables[$arg];
				}
				$line = vsprintf($line->format, $args);
			} else {
				$line = sprintf($line->format, $variables[$line->args]);
			}
			$return['info']['line'][] = array( "type" => "line", "formatted_line" => $line );
		}

		return $return;
	}


	/**
	 * getRangeLinks
	 *
	 * builds links for header when ranges are used to pass them around
	 */

	public function getRangeLinks ()
	{

		$links = "";

		if (
			(isset($_GET['minDate']) && !empty($_GET['minDate'])) &&
			(isset($_GET['maxDate']) && !empty($_GET['maxDate'])) &&
			(isset($_GET['logdate']) && !empty($_GET['logdate']))
			) {
			$links = "?minDate=" . $_GET['minDate'] . "&maxDate=" . $_GET['maxDate'] . "&logdate=" . $_GET['logdate'] ."&";
		} elseif (
			(isset($_GET['logdate']) && !empty($_GET['logdate']))
			) {
			$links = "?logdate=" . $_GET['logdate'] . "&";
		} else {
			$links = "?";
		}

		return $links;

	}



	/**
	 * getLoggerInterval
	 *
	 * User login, checks username and password from default settings to match.
	 *
	 * @param string $username the username
	 * @param string $password the password
	 */

	public function getLoggerInterval( ) 
	{

		$interval = LoadModules::$_settings->general['settings']['logger_interval'];

		if  ( $interval ) {

			$interval = $interval * 60;
			return $interval;

		} else {

			return false;
		}

	}



	/**
	 * getUIcookie
	 *
	 * used to get status of accordions - collapsed or visable from the loadUI cookie
	 * using code to manage accordion state is in common.js
	 *
	 */

	public function getUIcookie ( &$data1,  &$data2, $module) 
	{

		//these are the default values 
		$data1 = "accordion-body collapse in";
		$data2 = "true";

		//if cookie exist greb it here
		//if not we return default values above
		if (isset($_COOKIE["loadUIcookie"]))
			$myCookie = $_COOKIE["loadUIcookie"];
		else
			return false;
		
		$cookie = stripslashes($myCookie);

		$savedCardArray = json_decode($cookie, true);

			//echo '<pre>';
			//var_dump( $savedCardArray);
			//echo '</pre>';

		//now loop thorugh cookies
		foreach ($savedCardArray as &$value) {

			$myval = explode("=", $value);

			if ($module == $myval[0]) {

				if ($myval[1] == "true") {
					$data1 = "accordion-body collapse in";
				    $data2 = "true";
				}
				else {
					$data1 = "accordion-body collapse";
				    $data2 = "false";
				   }
			}

		}
	}

		
	public static function getUIcookieSorting (&$returnArray) 
	{

		//these are the default values 
		//$data1 = "accordion-body collapse in";
		//$data2 = "true";

		//if cookie exist greb it here
		//if not we return default values above
		if (isset($_COOKIE["loadUIcookie"]))
			$myCookie = $_COOKIE["loadUIcookie"];
		else
			return false;
		
		$cookie = stripslashes($myCookie);

		$savedCardArray = json_decode($cookie, true);

			//echo '<pre>';
			//var_dump( $savedCardArray);

		//now loop thorugh cookies

		$returnArray = "";

		foreach ($savedCardArray as &$value) {

			$myval = explode("=", $value);

			//echo 'myval :' . $myval[0];
			//echo 'value :' . $myval[1];

			$returnArray[$myval[0]]="true";


		}

		return true;

			//echo '</pre>';

	}


	/**
	 * getDates
	 *
	 * Gets date range from logfiles to populate the select box from topbar
	 * NOTE: Was changed to static
	 *
	 * @return array $return array with list of dates
	 */

	//grabs dates of ALL log files to be safe but would be faster if it did just one module

	public static function getDates()
	{
		$dates = array();

		foreach ( glob(dirname(__FILE__) . "/../logs/*.log") as $file ) {
			preg_match("/([0-9-]+)/", basename($file), $output_array);
		
			if ( isset( $output_array[0] ) && !empty( $output_array[0] ) )
				$dates[] = $output_array[0];
		}

 		//get rid of all duplicate dates
		$dates = array_unique($dates);

		//need to properly sort the array before returning it
		asort ($dates);

		return $dates;
	}

	



	/**
	 * getTimezones
	 *
	 * Get the (cached) list of all possible timezones
	 *
	 */

	public static function getTimezones()
	{
		if (is_array(LoadModules::$_timezones)) {
			return LoadModules::$_timezones;
		}

		LoadModules::$_timezones = array();

		$regions = array(
		    'Africa' => DateTimeZone::AFRICA,
		    'America' => DateTimeZone::AMERICA,
		    'Antarctica' => DateTimeZone::ANTARCTICA,
		    'Aisa' => DateTimeZone::ASIA,
		    'Atlantic' => DateTimeZone::ATLANTIC,
		    'Europe' => DateTimeZone::EUROPE,
		    'Indian' => DateTimeZone::INDIAN,
		    'Pacific' => DateTimeZone::PACIFIC
		);

		foreach ($regions as $name => $mask)
		{
		    $zones = DateTimeZone::listIdentifiers($mask);
		    foreach($zones as $timezone)
		    {
				// Lets sample the time there right now
				$time = new DateTime(NULL, new DateTimeZone($timezone));

				// Us dumb Americans can't handle millitary time
				$ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';

				// Remove region name and add a sample time
				LoadModules::$_timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
			}
		}

		return LoadModules::$_timezones;

	}

}
