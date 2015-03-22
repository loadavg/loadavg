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

class Swap extends Logger
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
	 * logMemoryUsageData
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

		/* 
			grab this data directly from /proc/meminfo in a single call
			egrep --color 'Mem|Cache|Swap' /proc/meminfo
		*/
		
		//pulling Cached here gives us both Cached and SwapCached
		exec( "egrep 'SwapCached|SwapTotal|SwapFree' /proc/meminfo | awk -F' ' '{print $2}'", $sysmemory );

		/*
		  [0]=> string(11) "SwapCached:"
		  [1]=> string(10) "SwapTotal:"
		  [2]=> string(9) "SwapFree:"
		*/

		//calculate swap usage
		$swapcached = $sysmemory[0];
		$swaptotal = $sysmemory[1];
		$swapfree = $sysmemory[2];

		$swapused = $swaptotal - ($swapfree + $swapcached);

	    $string = $timestamp . '|' . $swapcached . '|' . $swaptotal . '|' . $swapfree . '|' . $swapused . "\n";

	    //echo 'DATA:'  . $string .  "\n" ;

		$filename = sprintf($this->logfile, date('Y-m-d'));
		LoadUtility::safefilerewrite($filename,$string,"a",true);

		//If alerts are enabled, check for alerts
		if (Alert::$alertStatus)
			$this->checkAlerts($timestamp, $swapused, $swaptotal, $settings);
		
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

		//echo 'memory: ' . $data1 . "\n";
		//echo 'totalmemory: ' . $data2 . "\n";

		//check overloads against data using percentage for disk
		$percentage = ( $data1 / $data2 ) *100;


		//echo 'perc: ' . $percentage . "\n";
		//echo 'overload: ' . $overload[1] . "\n";

		if ( $percentage > $overload[1] )
		{
			$alert[0][0] = "swap";
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
