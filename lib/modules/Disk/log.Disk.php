<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Memory Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/




class Disk extends Logger
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
	 * logDiskUsageData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *	 *
	 */

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = Logger::$_settings->$class;
				
		$timestamp = time();

		$drive = $settings['settings']['drive'];
		
		if (is_dir($drive)) {
				
			$spaceBytes = disk_total_space($drive);
			$freeBytes = disk_free_space($drive);

			$usedBytes = $spaceBytes - $freeBytes;
						
			//$freeBytes = dataSize($Bytes);
			//$percentBytes = $freeBytes ? round($freeBytes / $totalBytes, 2) * 100 : 0;
		}

	    $string = $timestamp . '|' . $usedBytes  . '|' . $spaceBytes . "\n";
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		LoadUtility::safefilerewrite($filename,$string,"a",true);

		//If alerts are enabled, check for alerts
		if (ALERTS)
			$this->checkAlerts($timestamp, $usedBytes, $spaceBytes, $settings);

		if ( $type == "api")
			return $string;
		else
			return true;		
	}

	/**
	 * checkAlerts
	 *
	 * Check if we hit a alert and act on it here
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *
	 */

	public function checkAlerts( $timestamp, $data1, $data2, $settings )
	{

		//grab module name
		$module = __CLASS__;

		//for writing alert out
		$alert = null;

		//grab overloads
		$overload[1] = $settings['settings']['overload_1'];


		//check overloads against data using percentage for disk
		$percentage = ( $data1 / $data2 ) *100;

		//echo 'perc: ' . $percentage ;
		//echo ' overload: ' . $overload[1] ;

		if ( $percentage > $overload[1] )
		{
			$alert[0][0] = "storage";
			$alert[0][1] = (float)$overload[1];
			$alert[0][2] = $percentage;
		}


		if ( $alert != null )
		{	
			//need to build this out
			$string = $timestamp . '|' . $module . "|" . json_encode($alert) . "\n";

			Alert::addAlert($string);
		}
	}


}
