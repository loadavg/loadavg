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





class Cpu extends LoadAvg
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

		$timestamp = time();

		$load = exec("cat /proc/loadavg | awk -F' ' '{print $1\"|\"$2\"|\"$3}'");
		$string = $timestamp . '|' . $load . "\n";

		// need to clean data here 
		// need to insert a 0 in array if timestamp > previous timestamp + 5min

		// get last timestamp in log file
		/*
		$logfile = null;	

		$logfile = sprintf( sprintf($this->logfile, date('Y-m-d') ) );

		echo "logfile:" .  $logfile ;

		$contents = file_get_contents($logfile);
		$contents = explode("\n", $contents);

		echo "contents:" .  count( $contents ) ;
		*/

		//this allows us to feed live data to server with no local logging

		if ( $type == "api") {
			return $string;
		} else {
		     $fh = fopen(sprintf($this->logfile, date('Y-m-d')), "a");
			fwrite($fh, $string);
			fclose($fh); 
		}


	}

	/**
	 * getData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @param string $witch with witch data to populate return array
	 * @return array $return data retrived from logfile
	 *
	 */

	public function getData( $witch )
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
					if ( file_exists( $this->logfile ) )
						$contents .= file_get_contents($this->logfile);
				}
			}
		} else {
			$contents = file_get_contents($this->logfile);
		}


		if ( strlen($contents) > 1 ) {




			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			$dataArray = $dataArrayOver = $dataArrayOver_2 = $dataRedline = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			/*
			 * build the chartArray array here and patch to check for downtime
			 */

			$chartArray = array();
			$this->getChartData ($chartArray, $contents);

			for ( $i = 0; $i < count( $chartArray ); $i++) {				
				$data = $chartArray[$i];

				// clean data for missing values
				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "") )
					$data[1]=0;

				$time[$witch][$data[$witch]] = date("H:ia", $data[0]);

				//this is used for cpu only to switch between 1 min 5 min and 15 min load
				$usage[$witch][] = $data[$witch];

				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $data[$witch] ."']";

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
		
				if ( $data[$witch] > $settings['settings']['overload_1'] )
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $data[$witch] ."']";
		
				if ( $data[$witch] > $settings['settings']['overload_2'] )
					$dataArrayOver_2[$data[0]] = "[". ($data[0]*1000) .", '". $data[$witch] ."']";

			}
		
			$cpu_high = max($usage[$witch]);
			$cpu_high_time = $time[$witch][$cpu_high];

			$cpu_low = min($usage[$witch]);
			$cpu_low_time = $time[$witch][$cpu_low];
		
			$cpu_mean = number_format(array_sum($usage[$witch]) / count($usage[$witch]), 2);
			$cpu_latest = $usage[$witch][count($usage[$witch])-1];

			$ymin = $cpu_low;
			$ymax = $cpu_high;

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

			$variables = array(
    	        'cpu_high' => $cpu_high,
                'cpu_high_time' => $cpu_high_time,
                'cpu_low' => $cpu_low,
            	'cpu_low_time' => $cpu_low_time,
    	        'cpu_mean' => $cpu_mean,
                'cpu_latest' => $cpu_latest
            );

			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if ( count($dataArrayOver) == 0 ) $dataArrayOver = null;
			if ( count($dataArrayOver_2) == 0 ) $dataArrayOver_2 = null;

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			if (!is_null($dataArrayOver_2)) ksort($dataArrayOver_2);

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			$dataOverString_2 = is_null($dataArrayOver_2) ? null : "[" . implode(",", $dataArrayOver_2) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $cpu_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString,
				'chart_data_over_2' => $dataOverString_2
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
		$charts = $moduleSettings['chart'];
		$module = __CLASS__;
		$i = 0;
		foreach ( $charts['args'] as $chart ) {
			$chart = json_decode($chart);			
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);
			
			if ( file_exists( $this->logfile )) {
				$i++;
				$caller = $chart->function;
				$stuff = $this->$caller( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );
				$no_logfile = false;
			} else {
				$no_logfile = true;
			}
			include APP_PATH . '/views/chart.php';
		}
	}
}
