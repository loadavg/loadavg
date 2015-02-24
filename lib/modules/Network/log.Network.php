<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Network extends Logger
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

		//need to collect data from multiple interfaces here
		$apiString = "";

		foreach (Logger::$_settings->general['network_interface'] as $interface => $value) {

		//echo 'NET: ' . $interface . "\n" ;

		//skip disabled interfaces - should be and / or not and ?
			
		if (  !( isset(Logger::$_settings->general['network_interface'][$interface]) 
			&& Logger::$_settings->general['network_interface'][$interface] == "true" ) )
			continue;

			$logfile = sprintf($this->logfile, date('Y-m-d'), $interface);

			//echo 'PROCESSING:' . $logfile . "\n";

			$netdev = file_get_contents('/proc/net/dev');
			$pattern = "/^.*\b($interface)\b.*$/mi";
			preg_match($pattern, $netdev, $hits);

			$venet = '';

			if(isset($hits[0]))
			{
				$venet = trim($hits[0]);
				$venet = preg_replace("/ {1,99}/", " ", $venet);
				$venet = trim(str_replace("$interface:","",$venet));
			}

			$parts = explode(" ",$venet);
			$recv = isset($parts[0]) ? $parts[0] : '';
			$trans = isset($parts[8]) ? $parts[8] : '';

			// $recv = exec("cat /proc/net/dev | grep ".$interface." | awk -F' ' '{print $2}'");
			// $trans = exec("cat /proc/net/dev | grep ".$interface." | awk -F' ' '{print $10}'");

			if ( $logfile && file_exists($logfile) )
				$elapsed = $timestamp - filemtime($logfile);
			else
				$elapsed = 0;  //meaning new logfile

			//used to help calculate the difference as network is thruput not value based
			//so is based on the difference between thruput before the current run
			//this data is stored in _net_latest_elapsed_

			// grab net latest location and elapsed
			$netlatestElapsed = 0;
			$netLatestLocation = dirname($logfile) . DIRECTORY_SEPARATOR . '_net_latest_' . $interface;

			// basically if netlatest elapsed is within reasonable limits (logger interval + 20%) then its from the day
			// before rollover so we can use it to replace regular elapsed
			// which is 0 when there is anew log file
			if (file_exists( $netLatestLocation )) {
				
				$last = explode("|", file_get_contents(  $netLatestLocation ) );

				$netlatestElapsed =  ( $timestamp - filemtime($netLatestLocation));

				//if its a new logfile check to see if there is previous netlatest data
				if ($elapsed == 0) {

					//data needs to within the logging period limits to be accurate
					$interval = $this->getLoggerInterval();

					if (!$interval)
						$interval = 360;
					else
						$interval = $interval * 1.2;

					if ( $netlatestElapsed <= $interval ) 
						$elapsed = $netlatestElapsed;
				}
			}

			//if we were able to get last data from net latest above
			if (@$last && $elapsed) {
				$trans_diff = ($trans - $last[0]) / 1024;
				if ($trans_diff < 0) {
					$trans_diff = (4294967296 + $trans - $last[0]) / 1024;
				}
				$trans_rate = round(($trans_diff/$elapsed),2);
				$recv_diff = ($recv - $last[1]) / 1024;
				if ($recv_diff < 0) {
					$recv_diff = (4294967296 + $recv - $last[1]) / 1024;
				}
				$recv_rate = round(($recv_diff/$elapsed),2);
				
				$string = $timestamp . "|" . $trans_rate . "|" . $recv_rate . "\n";
			} else {
				//if this is the first value in the set and there is no previous data then its null
				
				$lastlogdata = "|0.0|0.0";

				$string = $timestamp . $lastlogdata . "\n" ;

			}

				//write out log data here
				LoadUtility::safefilerewrite($logfile,$string,"a",true);

				// write out last transfare and received bytes to latest
				$last_string = $trans."|".$recv;
				$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . "_net_latest_" . $interface;

				LoadUtility::safefilerewrite($fh,$last_string,"w",true);


				//If alerts are enabled, check for alerts
				if (@$last && $elapsed) {
					if (ALERTS)
						$this->checkAlerts($timestamp, $trans_rate, $recv_rate, $settings);
				}

				//figure out how to send back data for multiple interfaces here
				//echo "STRING:" . $string;

				$apiString[$interface] = $string;
				/*
				if ($apiString == "")
				else
					$apiString += "|" . $string;
				*/
		}

		if ( $type == "api")
			return $apiString;
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
		$overload[1] = $settings['settings']['overload_transfer'];
		$overload[2] = $settings['settings']['overload_receive'];

		//echo 'memory: ' . $data1 . "\n";
		//echo 'totalmemory: ' . $data2 . "\n";

		$trackVal = 0;

		if ( $data1 > $overload[1] )
		{
			$alert[$trackVal][0] = "transfer";
			$alert[$trackVal][1] = (float)$overload[1];
			$alert[$trackVal][2] = $data1;
			$trackVal++;
		}

		if ( $data2 > $overload[2] )
		{
			$alert[$trackVal][0] = "receive";
			$alert[$trackVal][1] = (float)$overload[2];
			$alert[$trackVal][2] = $data2;
		}


		if ( $alert != null )
		{
			//var_dump($alert);
			//var_dump(json_encode($alert));		

			//build file name
			$filename =  LOG_PATH . "events_" . date('Y-m-d') . ".log";
			
			//need to build this out
			$string = $timestamp . '|' . $module . "|" . json_encode($alert) . "\n";

			LoadUtility::safefilerewrite($filename,$string,"a",true);
		}
	}


}
