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

class LoadAvg
{
	public static $_settings; // storing standard settings and/or loaded modules settings
	public static $_classes; // storing loaded modules classes
	public static $_modules; // storing loaded modules
	public static $current_date; // current date
	
	// Periodas
	public static $period;
	public static $period_minDate;
	public static $period_maxDate;
	public static $settings_ini;
	public $timeStart = null, $timeFinish = null;


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
		
		date_default_timezone_set("UTC");
		self::$settings_ini = "settings.ini";
		
		$this->setSettings('general', 
			parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true)
		);

		date_default_timezone_set(self::$_settings->general['timezone']);

		self::$current_date = (isset($_GET['logdate']) && !empty($_GET['logdate'])) ? $_GET['logdate'] : date("Y-m-d");

		foreach ( self::$_settings->general['modules'] as $key => $value ) {
			if ( $value == "true" ) {
				try {
					require_once $key . DIRECTORY_SEPARATOR . 'class.' . $key . '.php';
					self::$_classes[$key] = new $key;
				} catch (Exception $e) {
					throw Exception( $e->getMessage() );
				}
			}
		}

		if (is_dir(APP_PATH . '/../lib/modules/')) {
			foreach (glob(APP_PATH . "/../lib/modules/*/*.php") as $filename) {
				$filename = explode(".", basename($filename));
				self::$_modules[$filename[1]] = strtolower($filename[1]);
			}
		}
	}

	/**
	 * is_dir_empty
	 *
	 * Checks if specified directory is empty or not.
	 *
	 * @param string $dir path to directory
	 */

	private function is_dir_empty($dir) {
		if (!is_readable($dir)) return NULL; 
		return (count(scandir($dir)) == 2);
	}

	/**
	 * createFirstLogs
	 *
	 * Creates first log files for every loaded modules after installation
	 *
	 */

	public function createFirstLogs()
	{
		if ( $this->is_dir_empty(APP_PATH . '/../' . self::$_settings->general['logs_dir']) ) {
			$loaded = self::$_settings->general['modules'];
			$logdir = APP_PATH . '/../' . self::$_settings->general['logs_dir'];

			// Check for each module we have loaded
			foreach ( $loaded as $module => $value ) {
				if ( $value == "false" ) continue;
				$moduleSettings = self::$_settings->$module;
				// Check if loaded module needs loggable capabilities
				if ( $moduleSettings['module']['logable'] == "true" ) {
					foreach ( $moduleSettings['logging']['args'] as $args) {
						$args = json_decode($args);
						$class = self::$_classes[$module];
						//$caller = sprintf($args->function, sprintf("'". $args->logfile . "'", date('Y-m-d')));
						$caller = $args->function;
						//$class->logfile = $logdir . sprintf($args->logfile, date('Y-m-d'));
						$class->logfile = $logdir . $args->logfile;
						$class->$caller(); 
					}
				}
			}

		}
	}

	/**
	 * checkWritePermissions
	 *
	 * Checks if specified file has write permissions.
	 *
	 * @param string $file path to file
	 */

	public function checkWritePermissions( $file )
	{
		if ( is_writable( $file ) )
			return true;
		else
			return false;
	}

	/**
	 * checkInstaller
	 *
	 * Checks if installer is removed or not
	 *
	 */

	public function checkInstaller() {
		if ( file_exists(dirname(APP_PATH) . "/public/install.php") )
			return false;
		else
			return true;
	}

	/**
	 * checkInstall
	 *
	 * Checks if is still installation progress and redirects if TRUE.
	 *
	 */

	public function checkInstall() {
		if ( file_exists(dirname(APP_PATH) . "/public/install.php") )
			header("Location: install.php");
	}

	/**
	 * checkWritePermissions
	 *
	 * Checks if specified file has write permissions.
	 *
	 * @param array $info array with info lines from the classes INI file
	 * @param array $variables variables to format lines
	 * @param string $class class name of module
	 * @return array $return formatted info lines
	 */

	public function parseInfo( $info, $variables, $class )
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
	 * write_php_ini
	 *
	 * Writes data into INI file
	 *
	 * @param array $array array with data to write into INI file.
	 * @param string $file filename to write.
	 */

	public function write_php_ini($array, $file)
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
				#echo '<pre>';var_dump($res);echo'</pre>';
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
	    if ($fp = fopen($file, 'w') ) {
	    	fwrite($fp, implode("\r\n", $res));
	    	fclose($fp);
	    }
	    //LoadAvg::safefilerewrite($file, implode("\r\n", $res));
	}
	
	/**
	 * safefilewrite
	 *
	 * Writes data to INI file and locks the file
	 *
	 * @param string $fileName filename
	 * @param array $dataToSave data to save to file
	 */

	private function safefilerewrite($fileName, $dataToSave)
	{    if ($fp = fopen($fileName, 'w'))
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
	    }

	}

	/**
	 * logIn
	 *
	 * User login, checks username and password from default settings to match.
	 *
	 * @param string $username the username
	 * @param string $password the password
	 */

	public function logIn( $username, $password) {
		if ( isset($username) && isset($password) ) {
			if ($username == LoadAvg::$_settings->general['username'] && md5($password) == LoadAvg::$_settings->general['password']) {
				$_SESSION['logged_in'] = true;
				
				if (isset(self::$_settings->general['checkforupdates'])) {
					$this->checkForUpdate();
				}
			}

		}
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
		// $http = new Zend_Http_Client( self::$_settings->general['api']['url'] );
		//var_dump(self::$_settings->general['api']['key']); exit;
		$url = self::$_settings->general['api']['url'];
		$json = array(
			'api_key'  => self::$_settings->general['api']['key'],
			'username' => self::$_settings->general['api']['username'],
			'server_id' => self::$_settings->general['api']['server'],
			'data'   => json_encode( $data )
		);
		$json = json_encode( $json );

		$options = array(
			CURLOPT_RETURNTRANSFER => true, // return web page
			CURLOPT_FOLLOWLOCATION => true, // follow redirects
			CURLOPT_USERAGENT => "BlueType API", // who am i
			CURLOPT_AUTOREFERER => true, // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
			CURLOPT_TIMEOUT => 120, // timeout on response
			CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
		);
		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt_array( $ch, $options );
		//execute post
		$result = curl_exec($ch); 
		$header = curl_getinfo( $ch );

		//close connection
		curl_close($ch);

		return $result;		
	}

	/**
	 * isLoggedIn
	 *
	 * Checks if the user is logged in and has SESSION started.
	 *
	 * @return boolean TRUE if is logged in and FALSE if not.
	 */

	public function isLoggedIn()
	{
		if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * logOut
	 *
	 * Logs out user and destroys SESSION data.
	 *
	 */

	public function logOut() { session_destroy(); }

	/**
	 * getNetworkInteraces
	 *
	 * Retrives network interfaces
	 *
	 * @return array $interfaces array of interfaces found on server
	 */

	public function getNetworkInterfaces()
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
		                $interface['name'] = (substr(trim($regex[1]), strlen(trim($regex[1]))-1, strlen(trim($regex[1]))) == ":") ? substr(trim($regex[1]), 0 , strlen(trim($regex[1]))-1) : $regex[1];

		                $interfaces[] = $interface;
		        }
		}

		return $interfaces;
	}

	/**
	 * setStartTime
	 *
	 * Sets startTime of page load
	 *
	 */

	public function setStartTime()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$this->timeStart = $time;
	}

	/**
	 * setFinishTime
	 *
	 * Sets finish time of page load
	 *
	 */

	public function setFinishTime()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$this->timeFinish = $time;
	}

	/**
	 * getPageLoadTime
	 *
	 * Returns page load time
	 *
	 * @return string $time page load time
	 */

	public function getPageLoadTime()
	{
		return round( ( $this->timeFinish - $this->timeStart ), 4 );
	}

	/**
	 * getDates
	 *
	 * Gets date range from logfiles to populate the select box from topbar
	 *
	 * @return array $return array with list of dates
	 */

	public function getDates()
	{
		$dates = array();
		foreach ( glob(dirname(__FILE__) . "/../logs/*.log") as $file ) {
			preg_match("/([0-9-]+)/", basename($file), $output_array);
			if ( isset( $output_array[0] ) && !empty( $output_array[0] ) )
				$dates[] = $output_array[0];
		}
		return array_unique($dates);
	}

	/**
	 * logUpdateCheck
	 *
	 * Logs to file every check for update(s).
	 *
	 */

	public function logUpdateCheck( $response )
	{
		$fh = fopen(dirname(__FILE__) . '/../logs/update.log', 'a+');
		$logLine = "Update check at " . date("Y-m-d H:i:s a") . " ---- Response: " . $response . PHP_EOL;
		if ( $fh ) {
-			fwrite($fh, $logLine);
			fclose($fh);
		}

	}

	/**
	 * checkForUpdate
	 *
	 * Checks for new versions of LoadAvg
	 *
	 */

	public function checkForUpdate()
	{
		if ( !isset($_SESSION['download_url'])) {
			if ( ini_get("allow_url_fopen") == 1) {
				#$response = file_get_contents("http://updates.loadavg.com/version.php?site_url=" . $_SERVER['SERVER_ADDR']  . "&ip=" . $_SERVER['SERVER_ADDR'] . "&version=" . self::$_settings->general['version'] . "&key=1");
				// $response = json_decode($response);
				$response = file_get_contents("http://54.229.242.17/loadavg-update/version.php?site_url=" . $_SERVER['SERVER_ADDR']  . "&ip=" . $_SERVER['SERVER_ADDR'] . "&version=" . self::$_settings->general['version'] . "&key=1");
				$this->logUpdateCheck( $response );
				//var_dump("http://54.229.242.17/loadavg-update/version.php?site_url=" . $_SERVER['SERVER_ADDR']  . "&ip=" . $_SERVER['SERVER_ADDR'] . "&version=" . self::$_settings->general['version'] . "&key=1");
				if ( $response > self::$_settings->general['version'] ) {
				 	$_SESSION['download_url'] = "http://updates.loadavg.com";
				}
			}
		}
	}
}
