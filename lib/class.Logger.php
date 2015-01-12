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

class Logger extends LoadAvg
{

	public  $logfile; // Stores the logfile name & path
	public  $logFileDepth; // Stores the data depth based on logger for parsing

	/**
	 * checkRedline
	 *
	 * checks for redline in data point sent over via charting modules
	 * and if it exists sets it to a null (0.0) data value for the chart
	 *
	 */




	/**
	 * getDelimiter
	 *
	 * Returns delimiter used for parsing log files
	 *
	 * LOGGER is globla defined in globals.php
	 */

	public function getDelimiter ( ) 
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




}
