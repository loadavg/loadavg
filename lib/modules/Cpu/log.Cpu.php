<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Hardware/CPU Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/



class Cpu extends Logger
{

	/**
	 * __construct
	 *
	 * Class constructor, appends Module settings to default settings
	 *
	 */
	public function __construct()
	{
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini.php', true));
	}

	/**
	 * logData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *
	 */

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = Logger::$_settings->$class;

		$timestamp = time();

		$load = null;

		//use the php function if its there
		if (!function_exists('sys_getloadavg')) {
		   		$load = exec("cat /proc/loadavg | awk -F' ' '{print $1\"|\"$2\"|\"$3}'");
		} else {
			$phpload=sys_getloadavg();
			$load=$phpload[0] . "|" . $phpload[1] . "|" . $phpload[2];
		}

		//if we want fancy formatting in logs we can always format them like this
	 	//$number = number_format((float)$number, 2, '.', '');

		$string = $timestamp . '|' . $load . "\n";

		//we can also add a switch to feed live data to server with no local logging
		//by just returning data
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		//$this->safefilerewrite($filename,$string,"a",true);
		LoadUtility::safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;		


	}




}
