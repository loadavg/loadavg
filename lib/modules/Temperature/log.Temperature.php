<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Temperature Module for LoadAvg
* 
* @link https://github.com/loadavg/loadavg
* @author Knut Kohl
* @copyright 2016 Knut Kohl <github@knutkohl.de>
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/




class Temperature extends Logger
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
	 * Retrives uptime data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *	 *
	 */

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = Logger::$_settings->$class;
				

		$temperature = exec("awk '{ printf \"%.1f\", \$1/1000 }' /sys/class/thermal/thermal_zone0/temp");

	    $string = time() . '|' . $temperature . "\n";
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		LoadUtility::safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;		
	}

}
