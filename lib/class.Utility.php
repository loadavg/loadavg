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

	public static function loadExtensions( $mode, &$settings, &$classes) {

		//loads modules code
		$class = 'class.';

		//if module is true in settings.ini file then we load it in 
		foreach ( $settings->general[$mode] as $key => &$value ) {

			if ( $value == "true" ) {
				try {
					$loadModule = $key . DIRECTORY_SEPARATOR . $class . $key . '.php';
					
					//echo 'loading:' . $loadModule;

					//this doesnt work as its defined as in the path... set in globals
					//maybe we should change this to not be relative ?
					require_once $loadModule;
					$classes[$key] = new $key;

				} catch (Exception $e) {
					throw Exception( $e->getMessage() );
				}
			}

		}

	}

	/**
	 * generatePluginList
	 *
	 * searches plugins directory for all plugins and adds them to list _plugins
	 *
	 */

	public static function  generateExtensionList( $mode, &$dataStore) {

		//loads in all modules names
		//so users can turn them on and off !

		if (is_dir(HOME_PATH . '/lib/' . $mode . '/')) {

			$searchpath = HOME_PATH . '/lib/' . $mode . '/*/class.*.php';

			foreach (glob($searchpath) as $filename) {
				$filename = explode(".", basename($filename));

				if ($mode == 'modules')
					$dataStore[$filename[1]] = strtolower($filename[1]);

				if ($mode == 'plugins')
					$dataStore[$filename[1]] = $filename[1];

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
	
}
