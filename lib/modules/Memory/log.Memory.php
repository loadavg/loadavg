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

class Memory extends Logger
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
		exec( "egrep 'MemTotal|MemFree|Buffers|Cached|SwapTotal|SwapFree' /proc/meminfo | awk -F' ' '{print $2}'", $sysmemory );

		/*
		  [0]=> string(9) "MemTotal:"
		  [1]=> string(8) "MemFree:"
		  [2]=> string(8) "Buffers:"
		  [3]=> string(7) "Cached:"
		  [4]=> string(11) "SwapCached:"
		  [5]=> string(10) "SwapTotal:"
		  [6]=> string(9) "SwapFree:"
		*/

		//calculate memory usage
		$totalmemory = $sysmemory[0];
		$freememory = $sysmemory[1];
		$bufferedmemory = $sysmemory[2];
		$cachedmemory = $sysmemory[3];

		$memory = $totalmemory - $freememory - $bufferedmemory - $cachedmemory;


		//calculate swap usage
		$swapcached = $sysmemory[4];
		$totalswap = $sysmemory[5];
		$freeswap = $sysmemory[6];

		$swap = $totalswap - ($freeswap + $swapcached);

	    $string = $timestamp . '|' . $memory . '|' . $swap . '|' . $totalmemory . "\n";

	    //echo 'DATA:'  . $string .  "\n" ;

		$filename = sprintf($this->logfile, date('Y-m-d'));
		LoadUtility::safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;
	}


}
