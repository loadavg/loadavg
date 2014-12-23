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
		$settings = LoadAvg::$_settings->$class;

		//need to collect data from multiple interfaces here
		$apiString = "";

		foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) {

		//echo 'NET: ' . $interface . "\n" ;

		//skip disabled interfaces - should be and / or not and ?
			
		if (  !( isset(LoadAvg::$_settings->general['network_interface'][$interface]) 
			&& LoadAvg::$_settings->general['network_interface'][$interface] == "true" ) )
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
				$elapsed = time() - filemtime($logfile);
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

				$netlatestElapsed =  ( time() - filemtime($netLatestLocation));

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
				
				$string = time() . "|" . $trans_rate . "|" . $recv_rate . "\n";
			} else {
				//if this is the first value in the set and there is no previous data then its null
				
				$lastlogdata = "|0.0|0.0";

				$string = time() . $lastlogdata . "\n" ;

			}

				//write out log data here
				$this->safefilerewrite($logfile,$string,"a",true);

				// write out last transfare and received bytes to latest
				$last_string = $trans."|".$recv;
				$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . "_net_latest_" . $interface;

				$this->safefilerewrite($fh,$last_string,"w",true);

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
	 * getTransferRateData
	 *
	 * Gets transfer data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @mode int processing mode, 1 is Transfer and 2 is Receive 
	 * @return array $return data retrived from logfile
	 *
	 */


	public function getTransferRateData()
	{
		$return = $this->getData(1);

		return $return;
	}

	/**
	 * getTransferRateData
	 *
	 * Gets transfer data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @mode int processing mode, 1 is Transfer and 2 is Receive 
	 * @return array $return data retrived from logfile
	 *
	 */


	public function getReceiveRateData()
	{
		$return = $this->getData(2);

		return $return;
	}


	/**
	 * getData
	 *
	 * Gets transfer data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @mode int processing mode, 1 is Transfer and 2 is Receive 
	 * @return array $return data retrived from logfile
	 *
	 */

	public function getData( $mode = 1 )
	{

		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();

		//$dataArrayOver = $dataArrayOver_2 = array();
		//$dataArraySwap = array();

		//display switch used to switch between view modes 
		switch ( $mode) {
			case 1: 	$threshold = $settings['settings']['threshold_transfer'];		
				        $limiting = $settings['settings']['transfer_limiting'];				
				        $cutoff = $settings['settings']['transfer_cutoff'];			
				        $dataArrayLabel[0] = "Transmit";
				        $dataArrayLabel[1] = "Overload";
						break;

			case 2: 	$threshold = $settings['settings']['threshold_receive'];	
				        $limiting = $settings['settings']['receive_limiting'];
						$cutoff = $settings['settings']['receive_cutoff'];	
				        $dataArrayLabel[0] = "Receive";
				        $dataArrayLabel[1] = "Overload";											
						break;
		}
		$displayMode =	$limiting;


		/*
		 * grab the log file data needed for the charts as array of strings
		 * takes logfiles(s) and gives us back contents
		 */	

		$contents = array();
		$logStatus = LoadAvg::parseLogFileData($this->logfile, $contents);

		/*
		 * build the chartArray array here as array of arrays needed for charting
		 * takes in contents and gives us back chartArray
		 */

		$chartArray = array();
		$totalchartArray = 0;

		if ($logStatus) {

			//takes the log file and parses it into chartable data 
			$this->getChartData ($chartArray, $contents );
			$totalchartArray = (int)count($chartArray);
		}

		/*
		 * now we loop through the dataset and build the chart
		 * uses chartArray which contains the dataset to be charted
		 */

		if ( $totalchartArray > 0 ) {

			// main loop to build the chart data
			for ( $i = 0; $i < $totalchartArray; ++$i) {		
				$data = $chartArray[$i];

				//check for redline
				$redline = ($this->checkRedline($data));

				// clean data for missing values
				if (  (!$data[$mode]) ||  ($data[$mode] == null) || ($data[$mode] == "") || (int)$data[$mode] < 0)
					$data[$mode]=0;
			
				$net_rate = $data[$mode];

				$timedata = (int)$data[0];
				$time[$net_rate] = date("H:ia", $timedata);
			
				$rate[] = $net_rate;

				$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";

				if ( $net_rate > $threshold )
					$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", '". $net_rate ."']";
			
			}


			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

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

			if ($displayMode == 'true' ) {
				$ymin = 0;

				//$ymax = 16;
				$ymax =	(int)$cutoff;
			} else {
				$ymin = $net_low;
				$ymax = $net_high;
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

			 /*
			 * all data to be charted is now cooalated into $return
			 * and is returned to be charted
			 * 
			 */

			$return  = array();

			// get legend layout from ini file
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			//parse, clean and sort data
			$depth=3; //number of datasets
			$this->buildChartDataset($dataArray,$depth);

			//build chart object
			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $net_mean,

				'dataset_1' 	  => $dataArray[0],
				'dataset_1_label' => $dataArrayLabel[0],

				'dataset_2' 	  => $dataArray[1],
				'dataset_2_label' => $dataArrayLabel[1]
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
