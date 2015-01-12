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
		$settings = LoadAvg::$_settings->$class;
				
		$drive = $settings['settings']['drive'];
		
		if (is_dir($drive)) {
				
			$spaceBytes = disk_total_space($drive);
			$freeBytes = disk_free_space($drive);

			$usedBytes = $spaceBytes - $freeBytes;
						
			//$freeBytes = dataSize($Bytes);
			//$percentBytes = $freeBytes ? round($freeBytes / $totalBytes, 2) * 100 : 0;
		}

	    $string = time() . '|' . $usedBytes  . '|' . $spaceBytes . "\n";
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		$this->safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;		
	}




}
