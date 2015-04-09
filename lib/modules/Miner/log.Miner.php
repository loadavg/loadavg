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



class Miner extends Logger
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

		$r = null;

		//grab server location and port
		$server = $settings['settings']['server'];
		$port = intval ($settings['settings']['port']);

		//echo 'server : ' . $server;
		//echo ' port : ' . $port;

		//grab data from API
		$r = $this->request('summary', $server, $port);
		
		/*
		echo print_r($r["SUMMARY"]["MHS 1m"], true)."\n";
		echo print_r($r["SUMMARY"]["MHS 5m"], true)."\n";
		echo print_r($r["SUMMARY"]["MHS 15m"], true)."\n";
		*/
		$load= $r["SUMMARY"]["MHS 1m"] . "|" . $r["SUMMARY"]["MHS 5m"] . "|" . $r["SUMMARY"]["MHS 15m"];


		//if we want fancy formatting in logs we can always format them like this
	 	//$number = number_format((float)$number, 2, '.', '');

		$string = $timestamp . '|' . $load . "\n";

		//we can also add a switch to feed live data to server with no local logging
		//by just returning data
		
		$filename = sprintf($this->logfile, date('Y-m-d'));

		LoadUtility::safefilerewrite($filename,$string,"a",true);

		//If alerts are enabled, check for alerts
		//note: $phpload dont work on 4.0 needs fixing above

		if (Alert::$alertStatus)
			$alertString = $this->checkAlerts($timestamp, $r, $settings);

		//Based on API mode return data if need be
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

	public function checkAlerts( $timestamp, $data, $settings )
	{

		//grab module name
		$module = __CLASS__;

		//for writing alert out
		$alert = null;

		//grab overloads
		$overload[1] = $settings['settings']['overload_1'];
		$overload[2] = $settings['settings']['overload_2'];

		//grab trigger
		$trigger = $data["SUMMARY"]["MHS 1m"];

		//var_dump($overload);
		//var_dump($data);

		//check overloads against data
		//testing load 5 min only here from data


		if ( $trigger <= $overload[1] )
		{
			$alert[0][0] = "Low hash";
			$alert[0][1] = (float)$overload[1];
			$alert[0][2] = $trigger;
		} 
		else if ( $trigger >= $overload[2] )
		{
			$alert[0][0] = "High hash";
			$alert[0][1] = (float)$overload[2];
			$alert[0][2] = $trigger;
		}

		if ( $alert != null )
		{	
			//need to build this out
			$string = $timestamp . '|' . $module . "|" . json_encode($alert) . "\n";

			//Logger::addAlert($string);
			Alert::addAlert($string);
		}
	}






#
# Sample Socket I/O to CGMiner API
#
function getsock($addr, $port)
{
 $socket = null;
 $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
 if ($socket === false || $socket === null)
 {
	$error = socket_strerror(socket_last_error());
	$msg = "socket create(TCP) failed";
	echo "ERR: $msg '$error'\n";
	return null;
 }

 $res = socket_connect($socket, $addr, $port);
 if ($res === false)
 {
	$error = socket_strerror(socket_last_error());
	$msg = "socket connect($addr,$port) failed";
	echo "ERR: $msg '$error'\n";
	socket_close($socket);
	return null;
 }
 return $socket;
}
#
# Slow ...
function readsockline($socket)
{
 $line = '';
 while (true)
 {
	$byte = socket_read($socket, 1);
	if ($byte === false || $byte === '')
		break;
	if ($byte === "\0")
		break;
	$line .= $byte;
 }
 return $line;
}
#
function request($cmd, $server, $port)
{

	//echo 'server : ' . $server;
	//echo ' port : ' . $port;

	//set up some defaults here
	if ($server == null)
		$server = '127.0.0.1';

	if ($port == null)
		$port = 4028;

 $socket = $this->getsock($server, $port);

 if ($socket != null)
 {
	socket_write($socket, $cmd, strlen($cmd));
	$line = $this->readsockline($socket);
	socket_close($socket);

	if (strlen($line) == 0)
	{
		echo "WARN: '$cmd' returned nothing\n";
		return $line;
	}

//	print "$cmd returned '$line'\n";

	if (substr($line,0,1) == '{')
		return json_decode($line, true);

	$data = array();

	$objs = explode('|', $line);
	foreach ($objs as $obj)
	{
		if (strlen($obj) > 0)
		{
			$items = explode(',', $obj);
			$item = $items[0];
			$id = explode('=', $items[0], 2);
			if (count($id) == 1 or !ctype_digit($id[1]))
				$name = $id[0];
			else
				$name = $id[0].$id[1];

			if (strlen($name) == 0)
				$name = 'null';

			if (isset($data[$name]))
			{
				$num = 1;
				while (isset($data[$name.$num]))
					$num++;
				$name .= $num;
			}

			$counter = 0;
			foreach ($items as $item)
			{
				$id = explode('=', $item, 2);
				if (count($id) == 2)
					$data[$name][$id[0]] = $id[1];
				else
					$data[$name][$counter] = $id[0];

				$counter++;
			}
		}
	}

	return $data;
 }

 return null;
}




}
