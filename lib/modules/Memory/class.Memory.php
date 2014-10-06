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

class Memory extends LoadAvg
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
		$settings = LoadAvg::$_settings->$class;

		exec("free -o | grep Mem | awk -F' ' '{print $3}'", $memory);
		//exec("free -o | grep Mem | awk -F' ' '{print $3 - $6 - $7}'", $memory);

		$memory = implode(chr(26), $memory);

		//exec("free -o | grep Mem | awk -F' ' '{print $3}'", $swap);
		exec("free -o | grep Swap | awk -F' ' '{print $3}'", $swap);

		$swap = implode(chr(26), $swap);

		exec("free -o | grep Mem | awk -F' ' '{print $2}'", $totalmemory);
		//exec("free -o | grep Mem | awk -F' ' '{print $3 - $6 - $7}'", $memory);

		$totalmemory = implode(chr(26), $totalmemory);

	    $string = time() . '|' . $memory . '|' . $swap . '|' . $totalmemory . "\n";

		if ( $type == "api" ) {
			return $string;
		} else {
        	$fh = fopen(sprintf($this->logfile, date('Y-m-d')), "a");
	        fwrite($fh, $string);
			fclose($fh); 
		}
	}

	/**
	 * getMemoryUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getMemoryUsageData()
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

			$swap = array();
			$usageCount = array();
			$dataArray = $dataArrayOver = $dataArraySwap = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			$chartArray = array();

			$this->getChartData ($chartArray, $contents);

			$totalchartArray = (int)count($chartArray);

			for ( $i = 0; $i < $totalchartArray; $i++) {				
				$data = $chartArray[$i];

				// clean data for missing values
				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "") )
					$data[1]=0;
				
				$time[( $data[1] / 1024 )] = date("H:ia", $data[0]);
				$usage[] = ( $data[1] / 1024 );
			
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";
			
				// sort out swap data here
				if ( isset($data[2]) )
					$dataArraySwap[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2] / 1024 ) ."]";

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
			
				if ( isset($data[2]) ) $swap[] = ( $data[2] / 1024 );
			
				// check overload in percentage 
				$percentage_used =  ( $data[1] / $data[3] ) * 100;

				if ( $percentage_used > $settings['settings']['overload'])  {
					//echo $percentage_used; die;
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";
				}

			}

			//echo $percentage_used; die;

			end($swap);


			$swapKey = key($swap);
			$swap = $swap[$swapKey];


			$mem_high= max($usage);
			$mem_high_time = $time[$mem_high];

			$mem_low = min($usage);
			$mem_low_time = $time[$mem_low];
		
			$mem_mean = array_sum($usage) / count($usage);
			$mem_latest = $usage[count($usage)-1];

			$mem_total = $usage[count($usage)-1];

			// normalize data if we are swappoing
			// issue is swap is a single value not over time

			if  ( $swap > 1 ) {
				$ymin = $swap/2;
				$ymax = $mem_high*1.05;
			}
			else {
				$ymin = $mem_low;
				$ymax = $mem_high;
			}

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');
				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++ ) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
			}
		
			// values used to draw the legend
			$variables = array(
				'mem_high' => number_format($mem_high,1),
				'mem_high_time' => $mem_high_time,
				'mem_low' => number_format($mem_low,1),
				'mem_low_time' => $mem_low_time,
				'mem_mean' => number_format($mem_mean,1),
				'mem_latest' => number_format($mem_latest,1),
				'mem_total' => number_format($mem_total,1),
				'mem_swap' => number_format($swap,1),
			);

			//print_r ($variables);
		
			// get legend layout from ini file
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			if (!is_null($dataArraySwap)) ksort($dataArraySwap);


			// dataString is cleaned data used to draw the chart
			// dataSwapString is the swap usage
			// dataOverString is if we are in overload

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			$dataSwapString = is_null($dataArraySwap) ? null : "[" . implode(",", $dataArraySwap) . "]";

			//echo "<br><br>";
			//print_r ($swap);
			//echo "<br><br>";

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mem_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString,
				'chart_data_swap' => $dataSwapString,
				'swap' => $swap,
				'swap_count' => $usageCount,
				'overload' => $settings['settings']['overload']
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

			//grab the log file and date
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);
			
			if ( file_exists( $this->logfile )) {
				$i++;				
				// $this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);
				$caller = $chart->function;
				$stuff = $this->$caller( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );
				
				include APP_PATH . '/views/chart.php';
			}
		}
	}
}
