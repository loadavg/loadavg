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

class Logger 
{

	public static $_settings; // storing standard settings and/or loaded modules settings
	public static $_classes; // storing loaded modules classes

	public static $_modules; // storing and managing modules

	public static $current_date; // current date

	public static $settings_ini;

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


	public function __construct()
	{

		date_default_timezone_set("UTC");
		self::$settings_ini = "settings.ini.php";

		$this->setSettings('general',
			parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true)
		);

		//get the date and timezone
		date_default_timezone_set(self::$_settings->general['settings']['timezone']);

		self::$current_date = (isset($_GET['logdate']) && !empty($_GET['logdate'])) ? $_GET['logdate'] : date("Y-m-d");


		//generate list of all modules
		$this->generateModuleList('modules');

		//load all charting modules that are enabled
		$this->loadModules('modules');

	}

	/**
	 * generatePluginList
	 *
	 * searches plugins directory for all plugins and adds them to list _plugins
	 *
	 */

	private function generateModuleList( $mode) {

		//loads in all modules names
		//so users can turn them on and off !

		if (is_dir(HOME_PATH . '/lib/' . $mode . '/')) {

			$searchpath = HOME_PATH . '/lib/' . $mode . '/*/class.*.php';

			foreach (glob($searchpath) as $filename) {
				$filename = explode(".", basename($filename));

				if ($mode == 'modules')
					self::$_modules[$filename[1]] = strtolower($filename[1]);

				//if ($mode == 'plugins')
				//	self::$_plugins[$filename[1]] = $filename[1];

			}
		}

	}




	/**
	 * loadModules
	 *
	 * load in modules by calling main scripts, will load moth core modules and plugins
	 *
	 * @param string $dir path to directory
	 */

	private function loadModules( $mode) {

		//loads modules code
		
		//first figure out if we are loading for dashboard or logger
		$class = 'log.';

		//if module is true in settings.ini file then we load it in 
		foreach ( self::$_settings->general[$mode] as $key => &$value ) {

			//echo 'VALUE: ' . $value . '   ' . 'KEY: ' . $key . '\n';

			if ( $value == "true" ) {
				try {
					$loadModule = $key . DIRECTORY_SEPARATOR . $class . $key . '.php';
					
					//echo 'loading:' . $loadModule;

					//this doesnt work as its defined as in the path... set in globals
					//maybe we should change this to not be relative ?
					require_once $loadModule;
					self::$_classes[$key] = new $key;

				} catch (Exception $e) {
					throw Exception( $e->getMessage() );
				}
			}

		}



	}

	/**
	 * getDates
	 *
	 * Gets date range from logfiles to populate the select box from topbar
	 * Also used to check for old log files in logger
	 *
	 * @return array $return array with list of dates
	 */

	//grabs dates of ALL log files to be safe but would be faster if it did just one module

	public static function getDates()
	{
		$dates = array();
		foreach ( glob( HOME_PATH . "/logs/*.log") as $file ) {

			//find files with number format only
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
	 * getLoggerInterval
	 *
	 * Gets the timing for the logger from system settings and returns it in seconds
	 *
	 */
	public  function getLoggerInterval( ) 
	{

		$interval = Logger::$_settings->general['settings']['logger_interval'];

		if  ( $interval ) {

			$interval = $interval * 60;
			return $interval;

		} else {

			//default is 5 minutes if no interval use that
			return 300;
		}

	}
	

	/**
	 * rotateLogFiles
	 *
	 * rotates the log files out by deleting old files older than @daystokeep
	 *
	 */
	public function rotateLogFiles ($logdir) 
	{
		
		$fromDate = strtotime("-". Logger::$_settings->general['settings']['daystokeep'] ." days 00:00:00");
		
		$dates = $this->getDates();

		foreach ( $dates as $date ) {
			$date = strtotime($date);
			if ($date < $fromDate) {
				$mask = $logdir . "*_" . date("Y-m-d", $date) . "*.log";

				//echo "MASK" . $mask .  "\n";
				array_map( 'unlink', glob( $mask ) );
			}
		}

}



/*
 * used to test if log files are being created by logger
 * needs better testing currently a bit of a hack
 * as we just test if the log directory is empty or not
 */

 	function testLogs( $mode = true)
	{

			$loadedModules = self::$_settings->general['modules'];
			$logdir = HOME_PATH . '/' . self::$_settings->general['settings']['logs_dir'];

			$test_worked = false;
			$test_nested = false;

			if ( LoadUtility::is_dir_empty($logdir))
				return false;

			// Check for each module we have loadedModules
			foreach ( $loadedModules as $module => $value ) {
				if ( $value == "false" ) continue;

				$moduleSettings = Logger::$_settings->$module;

				// Check if loadedModules module needs loggable capabilities
				if ( $moduleSettings['module']['logable'] == "true" ) {
					
					foreach ( $moduleSettings['logging']['args'] as $args) {
						
						$args = json_decode($args);
						$class = Logger::$_classes[$module];
						
						$caller = $args->function;

						//skip network interfaces as they have nested logs and work differently
						//later need to skip all nested logs as we check those below
						
						if ( $args->logfile == "network_%s_%s.log" )
						{
							$test_nested = true;
						}
						else
						{
							$filename = ( $logdir . sprintf($args->logfile, date('Y-m-d')) );

							if (file_exists($filename)) {
						    	$test_worked = true;
							}

							if ($mode == true)
								echo "Log: $filename Status: $test_worked  \n";		
						}
					}
				}
			}

			if ($test_nested == true) {

				//now do nested charts 
				foreach (Logger::$_settings->general['network_interface'] as $interface => $value) {

					if (  !( isset(Logger::$_settings->general['network_interface'][$interface]) 
						&& Logger::$_settings->general['network_interface'][$interface] == "true" ) )
						continue;

					$filename = ( $logdir . sprintf($args->logfile, date('Y-m-d') , $interface ) );
																				
					if (file_exists($filename)) {
				    	$test_worked = true;
					}

					if ($mode == true)
						echo "Log: $filename Status: $test_worked  \n";
				}
			}

			return $test_worked;
	}


	/**
	 * sendApiData
	 *
	 * If API is activated sends data to server
	 *
	 * @param array $data array of data to send to the server
	 * @return string $result message returned from the server
	 */

	public function sendApiData( $data ) {

		// for debugging
		//var_dump($data); //exit;
		//echo 'DEBUG: ' .  json_encode($data);

		$url = self::$_settings->general['api']['url'];

		$user_url = $url . '/users/';
		$server_url = $url . '/servers/';

		//validate API access here
		if ( self::$_settings->general['api']['server_token'] && self::$_settings->general['api']['key'] ) {		
		$ch =  curl_init($server_url . self::$_settings->general['api']['server_token'] . '/' . self::$_settings->general['api']['key'] . '/v');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$account_valid = curl_exec($ch);
		} else
			$account_valid = 'false';

		//get server id from server token
		if ( self::$_settings->general['api']['server_token'] ) {			
		$ch =  curl_init($server_url . self::$_settings->general['api']['server_token'] . '/t');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_exists = curl_exec($ch);
		} else
			$server_exists = 'false';

		//echo $server_url.json_decode($server_exists)->id.'/data';

		//validation needs to happen on the sever this is still insecure!
		//need to pass api token and server token over in data push

		if( $server_exists != 'false' && $account_valid != 'false' ) 
		{

			//file_put_contents("file.txt", json_encode($data)); test data
			$curl = curl_init();

			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $server_url.json_decode($server_exists)->id.'/data',
		    CURLOPT_USERAGENT => 'LoadAvg Client',
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => array(
		      'data' => json_encode($data),
		    )
			));

			// Send the request & save response to $resp
			$resp = curl_exec($curl);

			// Close request to clear up some resources
			curl_close($curl);

			//used for debugging to file
			//file_put_contents("file.txt",$resp);
			
			return true;
		}

		return null;
	}
	/**
	 * getProcStats
	 *
	 * parses /proc/stat and returns line $theLine
	 * move this out into utility functions later on
	 *
	 */

	public function getProcStats (array &$data, $theLine = 0) 
	{

        //we grab data from proc/stat in one pass as it changes as you read it
  		$stats = file('/proc/stat'); 

  		//if array is emoty we didnt work
		if($stats === array())
        	return false;

        //echo 'STATS:' . $stats[1];

        //grab cpu data
		$data = explode(" ", preg_replace("!cpu +!", "", $stats[$theLine])); 

       return true; 

	}


}
