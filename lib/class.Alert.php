<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Alert class
*
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Alert 
{

	public   static $alertDataArray;
	public   static $alertStatus;
	
	public function __construct()
	{

		$alertDataArray = array();
		$alertStatus = false;
	}


	/*
	 * setStatus - object knows if its active or not!
	 */

	public  function setStatus( $status )
	{

		self::$alertStatus = $status;

	}

	/*
	 * setStatus - object knows if its active or not!
	 */

	public  function getStatus( )
	{

		return self::$alertStatus;

	}


	/**
	 * initializeAlerts - initialize alert object
	 *
	 */

	public  function initializeAlerts( )
	{

		if (isset(self::$alertDataArray))
			unset(self::$alertDataArray);

		self::$alertDataArray = array();

	}

	/**
	 * addAlert - adds a alert to the alert cue
	 * can be called statically once que has ben created
	 *
	 */

	public  static function addAlert( $alert)
	{

		//echo 'adding alert' . $alert;
		if ( $alert && $alert != null )
			self::$alertDataArray[] = $alert;

	}

	/**
	 * viewAlerts - shows whats in the cue
	 *
	 */

	public  function viewAlerts( )
	{

		foreach ( self::$alertDataArray as $alert ) {

			echo  ' alert: ' . $alert;

		}
	}

	/**
	 * writeAlerts - writes alerts out to log file
	 *
	 */

	public  function writeAlerts( )
	{
		//hard coded for the moment
		if ( isset (self::$alertDataArray) && is_array(self::$alertDataArray) )
		{
			$filename =  LOG_PATH . "events_" . date('Y-m-d') . ".log";
		
			LoadUtility::safefilerewrite($filename,self::$alertDataArray,"a",true);

			return true;
		}

		return false;
	}


	
}
