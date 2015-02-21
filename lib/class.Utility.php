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



class LoadUtility
{


	/**
	 * is_dir_empty
	 *
	 * Checks if specified directory is empty or not.
	 *
	 * @param string $dir path to directory
	 */

	public static function is_dir_empty($dir) {
		if (!is_readable($dir)) return NULL;
		return (count(scandir($dir)) == 2);
	}

	
	/**
	 * loadExtensions
	 *
	 * load in modules by calling  scripts, will load both core chart modules and plugins
	 *
	 * @param string $dir path to directory
	 */

	public static function loadExtensions( $mode, &$settings, &$classes, &$modules, $logger = false) {
		

		//loads modules code
		if ($logger)
				$class = 'log.';
		else
				$class = 'class.';

		$previous = null;

		if (DEBUG) echo "<pre>Loading " . $mode . "<br>";

		//if module is true in settings.ini file then we load it in 
		foreach ( $settings->general[$mode] as $key => &$value ) {

			if ( $value == "true" ) {
				try {
					$loadModule = $key . DIRECTORY_SEPARATOR . $class . $key . '.php';
					
					//if (LOGDEBUG) echo 'Loading module ' . $loadModule . "\n";

					//this doesnt work as its defined as in the path... set in globals
					//maybe we should change this to not be relative ?
					//if ( file_exists ( $loadModule ))
					//{

					if ( LoadUtility::fileExists ( $loadModule ))
					{
						//echo "Module File Exists \n";

						require_once $loadModule;

						$classes[$key] = new $key;

						$modules[$key] = $settings->general[$mode][$key];
					} else {
						//echo "Module File Doesnt Exists \n";						
					}
					//$settings->general[$mode]["Cpu"]

				} catch (Exception $e) {
					throw Exception( $e->getMessage() );
				}

				$previous = $key;
			}

		}
		if (DEBUG) echo "</pre>";

	}

	/**
	 * generatePluginList
	 *
	 * searches plugins directory for all plugins and adds them to list _plugins
	 *
	 */

	public static function  generateExtensionList( $mode, &$dataStore) {

		//loads in all modules names
		//sets status to false (off)

		//first vaidate extension directory
		if (is_dir(HOME_PATH . '/lib/' . $mode . '/')) {

			//set template to search for extensions in/with
			$searchpath = HOME_PATH . '/lib/' . $mode . '/*/class.*.php';

			//search searchpath for extensions
			foreach (glob($searchpath) as $filename) {

				$filename = explode(".", basename($filename));

				if ($mode == 'modules' || $mode == 'plugins' ) {
					$dataStore[$filename[1]] = "false";
				}
			}
		}

		//var_dump ($dataStore);
	}


	/**
	 * fileExists
	 *
	 * checks if a file exists using the include path list set in globals.php
	 *
	 */
	public static  function fileExists($file) {
	    if(function_exists('stream_resolve_include_path'))
	        return stream_resolve_include_path($file);
	    else {
	        $include_path = explode(PATH_SEPARATOR, get_include_path());
	        foreach($include_path as $path)
	            if(file_exists($path.DS.$file))
	                return true;
	        return false;
	    }
	}

	/**
	 * getSettings
	 *
	 * loads settings data for a plugin or module and returns.
	 *
	 * @param string $module name of modiule to load
	 * @param string $mode type - modules or plugins
	 */
	public static function getSettings( $module, $mode = "plugins" ) {

		$moduleLocation = HOME_PATH . '/lib/' . $mode .  '/' . $module . '/'  ; 

		$moduleSettings = ( parse_ini_file( $moduleLocation  . strtolower( $module ) . '.ini.php', true) );

		return $moduleSettings;

	}


	/**
	 * checkWritePermissions
	 *
	 * Checks if specified file has write permissions.
	 *
	 * @param string $file path to file
	 */

	public static function checkWritePermissions( $file )
	{
		if ( is_writable( $file ) )
			return true;
		else
			return false;
	}







	/**
	 * write_php_ini
	 *
	 * Writes data into INI file
	 *
	 * @param array $array array with data to write into INI file.
	 * @param string $file filename to write.
	 */

	public static function write_php_ini($array, $file)
	{
	    $res = array();
		$bval = null;
	    foreach($array as $key => $val)
	    {
	        if(is_array($val))
	        {
	            $res[] = "[$key]";
	            foreach($val as $skey => $sval) {
			if (is_array($sval)) {
				for ($i = 0; $i < count($sval); $i++) {
					$res[] = $skey . '[] = \'' . $sval[$i] . '\'';
				}
			} else {
	        	    	if (strpos($sval, ";") === 0)
		            		$res[] = $sval;
		            	else
	            			$res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
			}
	            }
	        }
	        else {
	        	if (strpos($val, ";") === 0)
	        		$res[] = $val;
	        	else
	        		$res[] = "$key = ".(is_numeric($val) ? $val : (strstr($val, '{') !== false) ? '\''.$val.'\'' : '"'.$val.'"');
	        }
	    }

	    //we should use this instead
	    //LoadAvg::safefilerewrite($file, implode("\r\n", $res));

	    //security header here
	    $header = "; <?php exit(); __halt_compiler(); ?>\n";

	    if ($fp = fopen($file, 'w') ) {
	    	fwrite($fp, $header);	    	
	    	fwrite($fp, implode("\r\n", $res));
	    	fclose($fp);
	    }
	}

	//modified to not clean numeric values
	/*
	function write_php_ini($array, $file)
	{
	    $res = array();
	    foreach($array as $key => $val)
	    {
	        if(is_array($val))
	        {
	            $res[] = "[$key]";

	            //foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
	            foreach($val as $skey => $sval) 
	            	$res[] = "$skey = ".'"'.$sval.'"';
	        }
	        //else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
	        else $res[] = "$key = ".'"'.$val.'"';
	    }
	    safefilerewrite($file, implode("\r\n", $res));
	}
	*/



	public static function write_module_ini($newsettings, $module_name)
	{

		$module_config_file = HOME_PATH . '/lib/modules/' . $module_name . '/' . strtolower( $module_name ) . '.ini.php';

		//$this->write_php_ini($newsettings, $module_config_file);
		self::write_php_ini($newsettings, $module_config_file);

	}

	/**
	 * safefilewrite
	 *
	 * Writes data to INI file and locks the file
	 *
	 * @param string $fileName filename
	 * @param array $dataToSave data to save to file
	 */

	public static function safefilerewrite($fileName, $dataToSave, $mode = "w", $logs = false )
	{    

		//if file is new and is a logfile then we need to make it chmod 777
		//or we have issues between flies create using app and ones using cron
		//cron gives root permissions and app gives appache permissions
		$exists = file_exists ( $fileName );

		if ($fp = fopen($fileName, $mode))
	    {
	        $startTime = microtime();
	        do
	        {
	        	$canWrite = flock($fp, LOCK_EX);
	        	// If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
	        	if(!$canWrite) usleep(round(rand(0, 100)*1000));
	        } while ((!$canWrite)and((microtime()-$startTime) < 1000));

	        //file was locked so now we can store information
	        if ($canWrite)
	        {
	        	fwrite($fp, $dataToSave);
	            //flock($fp, LOCK_UN);
	        }

	        fclose($fp);

	        //if its a new log file fix permissions
	        if (!$exists && $logs==true ) {
	        	//echo "fix logs";
				chmod($fileName, 0777);
			}

	        return true;
	    }
	    else
	    {
	    	return false;
	    }

	}


	/**
	 * ini_merge
	 *
	 * used in settings modules to merge changes inot settings files
	 * may be depreciated now in exchange for array_replace
	 *
	 * @param string $config_ini config file array
	 * @param string $custom_ini data config file array to merge with
	 */

	 public static function ini_merge ($config_ini, $custom_ini) 
	 {
	 	foreach ($custom_ini AS $k => $v):
	    	if (is_array($v)):
	      		$config_ini[$k] = self::ini_merge($config_ini[$k], $custom_ini[$k]);
	    	else:
	      		$config_ini[$k] = $v;
	    	endif;
	  	endforeach;
	 
	 	return $config_ini;
	 }

	/**
	 * get_module_url
	 *
	 */

	 public static function get_module_url () 
	 {

		$absolute_url = LoadUtility::full_url($_SERVER);
		echo 'absolute_url ' . $absolute_url . "<br>";

		//ge thepage root
		$slash = explode('?', $absolute_url);

		$current_filename = $slash[count($slash) - 1]; 

		$host_url = str_replace($current_filename, "", $absolute_url);
		echo 'host_url ' . $host_url . "<br>";

		 return $host_url;
	 }




	public static  function url_origin($s, $use_forwarded_host=false)
	{
	    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	    $sp = strtolower($s['SERVER_PROTOCOL']);
	    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	    $port = $s['SERVER_PORT'];
	    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
	    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
	    return $protocol . '://' . $host;
	}

	public static function full_url($s, $use_forwarded_host=false)
	{
	    return LoadUtility::url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
	}







	/**
	 * getNetworkInteraces
	 *
	 * Retrives network interfaces
	 *
	 * @return array $interfaces array of interfaces found on server
	 */

	public static function getNetworkInterfaces()
	{
		// $interfaces = exec("/sbin/ifconfig | grep -oP '^[a-zA-Z0-9]*' | paste -d'|' -s");
		//$interfaces = exec('/sbin/ifconfig | expand | cut -c1-8 | sort | uniq -u | awk -F: \'{print $1;}\' | tr "\\n" "|" | tr -d \' \' | sed \'s/|*$//g\'');

		exec("/sbin/ifconfig", $content);
		$interfaces = array();

		#foreach (preg_split("/\n\n/", $content) as $int) {
		foreach ( $content as $int ) {
		    preg_match("/^(.*)\s+(flags|Link)/ims", $int, $regex);

		        if (!empty($regex)) {
		                $interface = array();
		                //$interface['name'] = $regex[1];

		                //added a trim to the return value as on centos 6.5 we had whitespace
		                $interface['name'] =  trim ( (substr(trim($regex[1]), strlen(trim($regex[1]))-1, strlen(trim($regex[1]))) == ":") ? substr(trim($regex[1]), 0 , strlen(trim($regex[1]))-1) : $regex[1] );

		                //echo ':' . $interface['name'] . ':';

		                $interfaces[] = $interface;
		        }
		}

		return $interfaces;
	}


	/**
	 * parseLogFileData
	 *
	 * returns data from inside log file in array
	 * if $data is array then grabs multiple files of data and
	 * uses array_merge when log files are across multiple days / ranges
	 *
	 */
	public static function parseLogFileData( $data, &$newDataArray  )
	{

		//do some checks first
		if ( !$data || $data == null || !isset($data) )
			return false;

		//loop through all data files and add them up here		
		//data is a array of log files to parse, the depth being used for multiple days
		//for eg when we have a date range
		//log file data is then read from disk and parsed into newDataArray

		$contents = "";
		$loop = 0;

	   	//used to show log files that are being parsed
	    //var_dump($data);

		foreach ($data as $dataKey => $logFileArray) {
	   
	   		//now grab data from disk
			$contents = LoadUtility::getLogFileDataFromDisk($logFileArray);

			//merge results sequentially when more than one file is read in
			$newDataArray = array_merge($newDataArray, $contents);
		}

			//echo '<pre>';
			//print_r ($newDataArray);
			//echo '</pre>';

		//TODO: what if getLogFileDataFromDisk was false ? need to return false here
		return true;

	}

	/**
	 * getLogFileDataFromDisk
	 *
	 * $logFileArray is a array of log files to parse 
	 * for a simple individual log file its a array of 1
	 * for more complex log files that are split across separate files its a array of > 1
	 *
	 */

	public static function getLogFileDataFromDisk( $logFileArray  )
	{

		//first we need to loop through log file and build mycontents array which is newline exploded 
		//array of data sets from each log file read from disk!

		$arraysize = 0;
		foreach ($logFileArray as $dataKey => $thelogFile) {

			if ( file_exists( $thelogFile )) {

				$mycontents[$arraysize] = file_get_contents($thelogFile);
				$mycontents[$arraysize] = explode("\n", $mycontents[$arraysize]);

				//used just for collectd to clean top of datasets where descriptions are
				if (LOGGER == "collectd"){
					array_shift($mycontents[$arraysize]);
				}

				//if last value is null or empty or ???? delete it
				if ( end($mycontents[$arraysize]) == null || end($mycontents[$arraysize]) == "" )
					array_pop($mycontents[$arraysize]);

				$arraysize++;
			}
		}

		//if its just a single log file we can return it now
		//otherwise parse it and then return it
		if ($arraysize == 1) {
			return $mycontents[0];
		} else {

			$finaldata = LoadUtility::parseComplexLogFiles( $mycontents, $arraysize  );
			return $finaldata;		
		}
	}


	/**
	 * parseComplexLogFiles
	 *
	 * when dealing with complex log files ie log data split across multiple files
	 * we need to read in all parts, parse to arrays and then merge them togeather
	 * into a single array as loadavg charts work with a single array of log data only!
	 * currently a bit of a mission! 
	 *
	 */

	public static function parseComplexLogFiles( $mycontents, $arraysize  )
	{

		//fist we have to loop through each data set in each log file 
		//as per the depth of the array (number of files) parse it and then
		//stitch it back up into the newDataArray

		//now we loop through multiple mycontents array break out data values
		$thenewarray = array();

		//delimiter is based on logger type 
		$delimiter = LoadUtility::getDelimiter();

		//main loop is number of datasets to me merged togeather
		for ($dataloop = 0; $dataloop < $arraysize; $dataloop++) {
		$finaldata = "";

			//this builds the array 
			$loop = 0;
			foreach ($mycontents[$dataloop] as &$value) {

				$thedata = explode($delimiter , $value);

				//for first data set grab timestamp
				if ($dataloop==0)
					$thenewarray[0][$loop] = isset($thedata[0]) ? $thedata[0] : null;
				
				//all other data sets its the 2nd value
				$thenewarray[$dataloop+1][$loop] = isset($thedata[1]) ? $thedata[1] : null;

			    $loop++;
			}
			unset($value); 

		} 

		//now rebuild data into $thenewarray as a single array -  stitch it back up
		$loop = 0;
		foreach ($thenewarray[0] as &$value) {

			$dataString = "";
			for ($dataloop = 0; $dataloop <= $arraysize; $dataloop++) {
				$dataString .= $thenewarray[$dataloop][$loop] . ",";
			}
			
			//need to kill the last "," here as its not needed ?
			$dataString = substr($dataString, 0, -1);
			$finaldata[$loop] = $dataString;

		    $loop++;
		}
		unset($value); 

		return $finaldata;		
		
	}

	/**
	 * cleanDataPoint
	 *
	 * cleans chart data points
	 *
	 */

	//TODO needs optimizing as its called for EVERY data point!

	public static function cleanDataPoint (array &$data, $depth = 3 ) 
	{
		//now clean data item for bad data meaning missing a depth value
		//we can put other rules in here...

		for ($x = 1; $x <= $depth; $x++) {

			//first see if all points are set according to depth of array	
			//one bad element kills datapoint
			if ( !isset($data[$x]) ) {
				$data = null;	
				return false;				
			}

			// now check for missing data and if missing data zero out missing data...
			else if ( ($data[$x] == null) || ($data[$x] == "") ) {
				
				$data[$x]=0.0;	
			}
		} 

		return true;
	}


	/**
	 * identical_values
	 *
	 * Returns data used to chart a empty chart for when there is no chart data
	 *
	 * @param array $emptyChart array with empty chart data
	 */
	public static function identical_values( $arrayA , $arrayB ) {

	    sort( $arrayA );
	    sort( $arrayB );

	    return $arrayA == $arrayB;
	} 


	/**
	 * endswith
	 *
	 * Returns data used to chart a empty chart for when there is no chart data
	 *
	 * @param array $emptyChart array with empty chart data
	 */
	public static function endswith($string, $test) {

	    $strlen = strlen($string);
	    $testlen = strlen($test);
	    
	    if ($testlen > $strlen) 
	    	return false;
	    
	    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
	}


	/**
	 * getEmptyChart
	 *
	 * Returns data used to chart a empty chart for when there is no chart data
	 *
	 * @param array $emptyChart array with empty chart data
	 */
	public static function getEmptyChart( )
	{

		$labels[0]="No Data";
		$data[0]="[[0, '0.00']]";

		$emptyChart = array(
			'chart_format' => 'line',
			'chart_avg' => 'avg',
			'ymin' => 0,
			'ymax' => 1,
			'xmin' => date("Y/m/d 00:00:01"),
			'xmax' => date("Y/m/d 23:59:59"),
			'mean' => 0,
			'dataset_labels' => $labels,
			'dataset' => $data
		);

		return $emptyChart;
	}


/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/
	public static function get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}


	/**
	 * getDelimiter
	 *
	 * Returns delimiter used for parsing log files
	 *
	 * LOGGER is globla defined in globals.php
	 */
	public static function getDelimiter ( ) 
	{
		$delimiter = "";
		switch ( LOGGER ) {

			case "collectd": 	$delimiter = ",";				
								break;

			case "loadavg": 	$delimiter = "|";				
								break;

			default: 			$delimiter = "|";				
								break;				
		}

		return $delimiter;

	}


	/**
	 * getTimezones
	 *
	 * Get the list of all possible timezones
	 *
	 */

	public static function getTimezones()
	{

		$_timezones = array();

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
				$_timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
			}
		}

		return $_timezones;

	}


}
