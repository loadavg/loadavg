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
		LoadUtility::generateExtensionList( 'modules', self::$_modules );

		//load all charting modules that are enabled
		//$this->loadModules('modules');
		LoadUtility::loadExtensions( 'modules', self::$_settings, self::$_classes);
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
	 * renderCharts
	 *
	 * builds links for header when ranges are used to pass them around
	 */


	public function renderCharts ( $chartList, $drawAvg = true )
	{

        foreach ( $chartList as $module => $value ) { // looping through all the modules in the settings.ini file
            
            if ( $value === "false" ) continue; // if modules is disabled ... moving on.

            //fix for issues with cookies
            if (!isset(LoadModules::$_settings->$module))
                continue;

            $moduleSettings = LoadModules::$_settings->$module; // if module is enabled ... get his settings
            
            if ( $moduleSettings['module']['logable'] == "true" ) { // if module has loggable enabled it has a chart
                
                $class = LoadModules::$_classes[$module];

                //tabbed modules have more than 1 chart in them
                if (isset($moduleSettings['module']['tabbed']) 
                	&& $moduleSettings['module']['tabbed'] == "true") {
 
                    //uses the modules views/chart code
                   $class->generateTabbedChart( $module, $drawAvg );

                } else {
                	//uses the global function in class.Charts.php
        			$class->generateChart( $module, $drawAvg );
                }

            }
        }
    }


	public function renderSingleChart ( $module, $drawAvg = true )
	{

        if (!isset(LoadModules::$_settings->$module))
            return false;
                        
        //get the class so we can call functions
        $class = LoadModules::$_classes[$module];

        //render the chart
        $class->generateChart( $module, $drawAvg );

        return true;

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

		$cookieArray = json_decode($cookie, true);

		//now loop thorugh cookies
		//foreach ($cookieArray as &$value) {

		foreach ($cookieArray as $key =>$value) {

			//$myval = explode(":", $value);

			if ($module == $key) {

				if ($value == "open") {
					$data1 = "accordion-body collapse in";
				    $data2 = "true";
				}

				if ($value == "closed") {
					$data1 = "accordion-body collapse";
				    $data2 = "false";
				}

				//if none then its open...
				if ($value != "open" && $value != "closed"  ) {
					$data1 = "accordion-body collapse in";
				    $data2 = "true";
				}

			}

		}
	}

		
	public static function getUIcookieSorting (&$returnArray) 
	{
		//if cookie exist greb it here
		//if not we return default values above
		if (isset($_COOKIE["loadUIcookie"]))
			$myCookie = $_COOKIE["loadUIcookie"];
		else
			return false;
		
		$cookie = stripslashes($myCookie);
		$cookieArray = json_decode($cookie, true);

		//parse out as true so they can be shown
		$returnArray = null;
		foreach ($cookieArray as $key =>$value) {
			$returnArray[$key]="true";
		}

        //checks for problems with cookies 
        if (  array_keys($returnArray) == range(0, count($returnArray) - 1)  ) {
            return false;
        } 

		return true;
	}

	//updates cookies according to new module settings
	//for when modules are turned on or off

	public static function updateUIcookieSorting ($newSettings) 
	{

		//if cookie exist greb it here
		//if not we return as false and cookie isnt used
		if (isset($_COOKIE["loadUIcookie"]))
			$myCookie = $_COOKIE["loadUIcookie"];
		else
			return false;
		
		//got cookie lets clean it up
		$cookie = stripslashes($myCookie);
		$currentCookie = json_decode($cookie, true);

		//now parse newSettings and drop all false values as cookies are only for true values
		$cleanSettings = null;
		foreach ($newSettings as $key =>$value) {

			if ($value=="true") {
				$cleanSettings[$key]="true";
			}

		}

		//echo '<pre>cleanSettings'; var_dump( $cleanSettings); echo '</pre>';

		//echo '<pre>CoockieData'; var_dump( $currentCookie); echo '</pre>';

		// now we need to update cookie to remove or add items from cleanSettings....
		//if item crossess over ski[p it

		$newCookie = null;
		foreach ($cleanSettings as $key =>$value) {

			//if value is in currentCookie
			//grab from currentCookie
 			$newvalue = false;
			//check if key is already in cookies
			foreach ($currentCookie as $cookiekey => $cookievalue) {
			  if ( $key == $cookiekey ) {
			    $newvalue = $cookievalue;
			  }
			}

			if ($newvalue) {
				$newCookie[$key]=$newvalue;
			}

			else
				$newCookie[$key]="open";
		}


		//echo '<pre>newCookie'; var_dump( $newCookie); echo '</pre>';

		//now we need to preserve the sorting!!!
		//as sorting is in the cookie...!!

		//easy way ? compare old coockie against new cookie...

		//so clean out oldcookie
		//then add missing data to old cookie

		$finalCookie = null;

		foreach ($currentCookie as $key =>$value) {

			//check if key is already in cookies
			foreach ($newCookie as $cookiekey => $cookievalue) {

			  if ( $key == $cookiekey ) {
			    $finalCookie[$key]= $cookievalue;
			  }

			}
		}

		//now go thorugh the new cookie and see if we left anything out
		foreach ($newCookie as $key =>$value) {

			//check if key is there
			$foundit = false;
			foreach ($finalCookie as $cookiekey => $cookievalue) {
			  if ( $key == $cookiekey ) 
			  		$foundit = true;
			}

			if (!$foundit)
			    $finalCookie[$key]= $value;

		}
	


		//echo '<pre>finalCookie'; var_dump( $finalCookie); echo '</pre>';

		//here we need to rewrite the cookie
		$cookietime = time() + (86400 * 365); // 1 year

		$finalCookie = json_encode($finalCookie);

		setcookie('loadUIcookie', $finalCookie, $cookietime, "/");


		//if things get crazy for any reason then we need to just delete all cookies 
		//maybe add to settings >

		//dirty short term hack deleted cookie
		//if(isset($_COOKIE['loadUIcookie'])) {
		//	setcookie('loadUIcookie', null, -1, "/");
    	//	unset($_COOKIE['loadUIcookie']);
		//}



		return true;
	}

	public static  function sortArrayByArray(Array $array, Array $orderArray) {
    
    $ordered = array();

    foreach($orderArray as $key) {
        if(array_key_exists($key,$array)) {
            $ordered[$key] = $array[$key];
            unset($array[$key]);
        }
    }
    return $ordered + $array;
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
