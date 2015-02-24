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
		self::$settings_ini = "settings.ini.php";

		$this->setSettings('general',
			parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true)
		);


		//generate list of all modules
		LoadUtility::generateExtensionList( 'modules', self::$_modules );

		//load all charting modules that are enabled
		LoadUtility::loadExtensions( 'modules', self::$_settings, self::$_classes, self::$_modules);


		//echo '<pre>'; var_dump(self::$_modules); echo '</pre>';

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
		LoadUtility::generateExtensionList( 'modules', self::$_modules );

		//load all charting modules that are enabled
		LoadUtility::loadExtensions( 'modules', self::$_settings, self::$_classes, self::$_modules);
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
	 * renderSingleChart
	 *
	 * draws a chart to the screen
	 * @param string $module is the module to draw
	 * @param bool $drawAvg will draw the averages bar if true
	 */
	public function renderChart ( 	$module, 
									$drawAvg = true, 
									$drawLegend = true, 
									$cookies = true,
									$callback = false,
									$width = false, $height = false )
	{

        if (!isset(LoadModules::$_settings->$module))
            return false;
                        
        // if module is enabled ... get his settings
        $moduleSettings = self::$_settings->$module; 

        //get the class so we can call functions
        $class = self::$_classes[$module];

		//get data range we are looking at - need to do some validation in this routine
		$dateRange = self::$date_range;

        //get data for chart/s to be rendered
		$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file

		//var_dump($charts);
		
		//see if there is a callback - used to trigger onclick events in chart
		$chartCallback = $callback;

		//check if chart has dynamic functions
		$functionSettings =( (isset($moduleSettings['module']['url_args']) 
			&& isset($_GET[$moduleSettings['module']['url_args']])) 
			? $_GET[$moduleSettings['module']['url_args']] : '2' );


		/*
		 * tabbed chart modules have multiple charts within them
		 */
        if (isset($moduleSettings['module']['tabbed']) 
        	&& $moduleSettings['module']['tabbed'] == "true") 
        {

			$templateName = HOME_PATH 	. DIRECTORY_SEPARATOR . 'lib/modules' 
										. DIRECTORY_SEPARATOR . $module 
										. DIRECTORY_SEPARATOR . 'views/chart.php';


            //uses the modules views/chart code
            //move this code in here next
           //$templateName = $class->getChartTemplate( $module );

       		//not sure if we need this as no template means it breaks
			if ( file_exists( $templateName )) 
				include $templateName;
			else 
				return false;
			

        } else {

        	//single level chart data only
        	//should add this to chartData ?
        	//$chart = $charts['args'][0];
			//$chart = json_decode($chart);

			//now call template to draw chart to screen
			include HOME_PATH . '/lib/charts/chart.php';

        }

        return true;

    }




	/**
	 * getUIcookie
	 *
	 * used to get status of accordions - collapsed or visable from the loadUI cookie
	 * using code to manage accordion state is in common.js
	 *
	 */

	public function getUIaccordionCookie ( &$data1,  &$data2, $module) 
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

		
	public  function getUIcookieSorting () 
	{

		//first grab the cookie
		$cookieArray = null;
		$cookieArray = $this->getModuleChartUICookie();

		//if no cookie then return
		if ($cookieArray == false || $cookieArray == null || !$cookieArray  )
			return false;

        //checks if cookie is there but its empty ie no values
        if (  array_keys($cookieArray) == range(0, count($cookieArray) - 1)  ) {

        //echo '<pre>cookie issues </pre>';

		/*
        //code that tests cookies against whats active to look for errors
		//do we need this ? if bad cookie maybe just delete it ?

        $loadedModules = LoadModules::$_modules; 

        if ($chartList != false) {

            $cleanSettings = null;
            foreach ($loadedModules as $key =>$value) {

                if ($value=="true") {
                    $cleanSettings[$key]="true";
                }
            }
    
            //echo '<pre> chartList  '; var_dump( $chartList); echo '</pre>';
            //echo '<pre> cleanSettings  '; var_dump( $cleanSettings); echo '</pre>';

            //sorts and then compares arrays
            //these should match really but if not dont we need to do something ?

            if (!LoadUtility::identical_values( $cleanSettings , $chartList )) {

                $loadModules->updateUIcookieSorting($loadedModules);
                $chartList = $loadedModules;

            }

        }
	    */ 

            return false;
        } 

		//all is good return cookie;
		return $cookieArray;

	}

	//gets the loadUIcookie if its set 
	//returns value in &$cookie
	//if not returns false

	public function getModuleChartUICookie () 
	{
		//if cookie exist greb it here
		//if not we return default values above
		if (isset($_COOKIE["loadUIcookie"]))
			$cookie = $_COOKIE["loadUIcookie"];
		else
			return false;
		
		$cookie = stripslashes($cookie);
		$cookie = json_decode($cookie, true);

		return $cookie;

	}


	//updates cookies according to new module settings
	//for when modules are turned on or off in settings

	public function updateUIcookieSorting ($moduleSettings) 
	{


		//first check if there is a cookie there if there is none 
		//return to caller with false

		$currentCookie = false;
		$currentCookie = $this->getModuleChartUICookie ();

		echo '<pre>CookieData '; var_dump( $currentCookie); echo '</pre>';

		if ($currentCookie == false)
		{
			return false;
		}


		//echo '<pre>Updating Cookie</pre>';

		//now parse moduleSettings sent over and drop all false values 
		//then replace true values with open/close status
		//as cookies only store active modules and status - gives us newCookie
		$newCookie = null;
		foreach ($moduleSettings as $key =>$value) {

			if ($value=="true") {

				if (isset($currentCookie[$key]))
					$newCookie[$key]=$currentCookie[$key];
				else
					$newCookie[$key]="open";

			}

		}

		//echo '<pre>newCookie'; var_dump( $newCookie); echo '</pre>';

		/*
		 * now we need to sort the new cookie based on original cookie
		 */

		//first compare old cookie against new cookie...
		$sortedCookie = null;

		foreach ($currentCookie as $key =>$value) {

			//check if key is already in cookies
			foreach ($newCookie as $cookiekey => $cookievalue) {

			  if ( $key == $cookiekey ) {
			    $sortedCookie[$key]= $cookievalue;
			  }

			}
		}

		//now go thorugh the new cookie and see if we left anything out after comparison
		//(ie new modules) and add it to the end
		foreach ($newCookie as $key =>$value) {

			//check if key is there
			$foundit = false;
			foreach ($sortedCookie as $cookiekey => $cookievalue) {
			  if ( $key == $cookiekey ) 
			  		$foundit = true;
			}

			if (!$foundit)
			    $sortedCookie[$key]= $value;

		}

		//echo '<pre>sortedCookie '; var_dump( $sortedCookie); echo '</pre>';

		$this->saveUICookie($sortedCookie);

		return true;
	}


	public static  function saveUICookie($cookie) {

		//here we need to rewrite the cookie
		$cookietime = time() + (86400 * 365); // 1 year
		$finalCookie = json_encode($cookie);

		setcookie('loadUIcookie', $finalCookie, $cookietime, "/");

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

	

}
