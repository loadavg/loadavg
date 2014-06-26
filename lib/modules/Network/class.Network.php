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

class Network extends LoadAvg
{
	public $logfile; // Stores the logfile name & path

	/**
	 * __construct
	 *
	 * Class constructor, appends Module settings to default settings
	 *
	 */
	public function __construct()
	{
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini', true));
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
		$settings = LoadAvg::$_settings->$class;

		foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) {
			$logfile = sprintf($this->logfile, date('Y-m-d'), $interface);

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
				$elapsed = time() - filemtime($logfile);
			else
				$elapsed = 0;

			if (file_exists( dirname($logfile) . DIRECTORY_SEPARATOR . '_net_latest_' . $interface)) {
				$last = explode("|", file_get_contents(dirname($logfile) . DIRECTORY_SEPARATOR . '_net_latest_' . $interface) );
			}

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
				
				$string = time() . "|" . $trans_rate . "|" . $recv_rate . "\n";
			} else {
				$string = time() . "|0.0|0.0\n";
			}

	      		if ( $type == "api") {
				return $string;
			} else {

				$fh = fopen($logfile, "a");
			    	fwrite($fh, $string);
				fclose($fh);


				$last_string = $trans."|".$recv;
				// Writing transfare and received bytes to file
				$fh = fopen(dirname($this->logfile) . DIRECTORY_SEPARATOR . "_net_latest_" . $interface, "w");
				fwrite($fh, $last_string);
				fclose($fh);
			}
		}
	}

	/**
	 * getTransferRateData
	 *
	 * Gets transfer data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	public function getTransferRateData()
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;
		$contents = null;

		$replaceDate = self::$current_date;
		
		if ( LoadAvg::$period ) {
			$dates = self::getDates();
			foreach ( $dates as $date ) {
				if ( $date >= self::$period_minDate && $date <= self::$period_maxDate ) {
					$this->logfile = str_replace($replaceDate, $date, $this->logfile);
					$replaceDate = $date;
					if ( file_exists( $this->logfile ) ) {
						$contents .= file_get_contents($this->logfile);
					}
				}
			}
		} else {
			$contents = @file_get_contents($this->logfile);
		}
		

		if ( strlen($contents) > 1 ) {

			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			//$dataArray = "[";
			$dataArray = $dataArrayOver = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			//$dataArrayOver = "[";
			for ( $i = 0; $i < count( $contents )-1; $i++) {
				$data = explode("|", $contents[$i]);

				// clean data for missing values
				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "") )
					$data[1]=0;
			
				$net_rate = $data[1];
				$time[$net_rate] = date("H:ia", $data[0]);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
			
				$rate[] = $net_rate;

				if ( $net_rate > $settings['settings']['threshold_transfer'] )
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";
			
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";
			}

			//$dataArray = substr($dataArray, 0, strlen($dataArray)-1);
			//$dataArrayOver = substr($dataArrayOver, 0, strlen($dataArrayOver)-1);
			//$dataArray .= "]";
			//$dataArrayOver .= "]";
		
			$net_high= max($rate);
			$net_high_time = $time[$net_high];

			$net_low = min($rate);
			$net_low_time = $time[$net_low];

			$net_latest = $rate[count($rate)-1];
			$net_mean = number_format(array_sum($rate) / count($rate), 2);

			$net_estimate = round($net_mean*60*60*24/1024);
		        if ($net_estimate >= 1024) {
        		        $net_estimate = round($net_estimate/1024,1);
                		$net_estimate_units = "GB";
		        } else {
        	        	$net_estimate_units = "MB";
	        	}

			$ymin = $net_low;
			$ymax = $net_high;
		
		
			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				//$dataArray = substr($dataArray, 0, strlen($dataArray)-1);
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');
				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";

					//$dataArray .= "[". ($lastTimeString*1000) .", 0],";
					//var_dump($lastTimeString . " #------# " . date("d-m-Y H:i", $lastTimeString));
				}
				//$dataArray = substr($dataArray, 0, strlen($dataArray)-1);
				//$dataArray .= "]";
			}




			$variables = array(
				'net_high' => $net_high,
				'net_high_time' => $net_high_time,
				'net_low' => $net_low,
				'net_low_time' => $net_low_time,
				'net_mean' => $net_mean,
				'net_latest' => $net_latest,
				'net_estimate' => $net_estimate,
				'net_estimate_units' => $net_estimate_units
			);
		
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);

			if (!is_null($dataArrayOver)) ksort($dataArrayOver);

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $net_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString
			);

			return $return;
		} else {
			return false;
		}

	}

	/**
	 * getReceiveRateData
	 *
	 * Gets receive data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	public function getReceiveRateData()
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$contents = null;

		$replaceDate = self::$current_date;
		
		if ( LoadAvg::$period ) {
			$dates = self::getDates();
			foreach ( $dates as $date ) {
				if ( $date >= self::$period_minDate && $date <= self::$period_maxDate ) {
					$this->logfile = str_replace($replaceDate, $date, $this->logfile);
					$replaceDate = $date;
					if ( file_exists( $this->logfile ) ) {
						$contents .= file_get_contents($this->logfile);
					}
				}
			}
		} else {
			$contents = @file_get_contents($this->logfile);
		}


		if ( strlen( $contents ) > 1 ) {
		
			$contents = explode("\n", $contents);
			$return = $usage = $args = array();
			$dataArray = $dataArrayOver = array();
		
			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			for ( $i = 0; $i < count( $contents )-1; $i++) {
				$data = explode("|", $contents[$i]);

				$net_rate = $data[2];
				$time[$net_rate] = date("H:ia", $data[0]);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
			
				$rate[] = $net_rate;

				if ( $net_rate > $settings['settings']['threshold_receive'] )
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";
			
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";
			}

			$net_high= max($rate);
			$net_high_time = $time[$net_high];

			$net_low = min($rate);
			$net_low_time = $time[$net_low];
		
			$net_latest = $rate[count($rate)-1];
			$net_mean = number_format(array_sum($rate) / count($rate), 2);
			$net_estimate = round($net_mean*60*60*24/1024);

        	if ($net_estimate >= 1024) {
            	$net_estimate = round($net_estimate/1024,1);
                $net_estimate_units = "GB";
	        } else {
    	        $net_estimate_units = "MB";
            }

			$ymin = $net_low;
			$ymax = $net_high;
/*
			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');
				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
			}
*/		
			$variables = array(
				'net_high' => $net_high,
				'net_high_time' => $net_high_time,
				'net_low' => $net_low,
				'net_low_time' => $net_low_time,
				'net_mean' => $net_mean,
				'net_latest' => $net_latest,
				'net_estimate' => $net_estimate,
				'net_estimate_units' => $net_estimate_units
			);
		
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $net_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString
			);

			return $return;
		} else {
			return false;
		}
	}

	/**
	 * genChart
	 *
	 * Function witch passes the data formatted for the chart view
	 *
	 * @param array @moduleSettings settings of the module
	 * @param string @logdir path to logfiles folder
	 *
	 */
	public function genChart($moduleSettings, $logdir)
	{

	//used for debugging
    //echo '<pre>';var_dump(self::$current_date);echo'</pre>';

		$charts = $moduleSettings['chart'];

		$module = __CLASS__;
		$i = 0;

		if ( file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'chart.php')) {
			include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'chart.php';
		} else {
			include APP_PATH . '/lib/views/chart.php';
		}		
	}
}
