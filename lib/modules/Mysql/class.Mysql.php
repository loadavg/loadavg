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

class Mysql extends LoadAvg
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

		//connect to the database
		@mysql_connect ("localhost","root","vision7") 
			or die ('Cannot connect to MySQL: ' . mysql_error());

		//need to move over to this way
		//$connection = mysqli_connect('localhost', 'root', 'vision7');


		$query1 = mysql_query("SHOW GLOBAL STATUS LIKE 'Bytes_received'") or die ('Query is invalid: ' . mysql_error());
		$query2 = mysql_query("SHOW GLOBAL STATUS LIKE 'Bytes_sent'") or die ('Query is invalid: ' . mysql_error());
		$query3 = mysql_query("SHOW GLOBAL STATUS LIKE 'Queries'") or die ('Query is invalid: ' . mysql_error());

		//write the results
		$row = mysql_fetch_array($query1);
			$bytesReceived = $row[1];

		$row = mysql_fetch_array($query2);
			$bytesSent = $row[1];

		$row = mysql_fetch_array($query3);
			$queries = $row[1];


	    $string = time() . '|' . $bytesReceived . '|' . $bytesSent . '|' . $queries . "\n";

		$logfile = sprintf($this->logfile, date('Y-m-d'));


			$recv = $bytesReceived;
			$trans = $bytesSent;



			if ( $logfile && file_exists($logfile) )
				$elapsed = time() - filemtime($logfile);
			else
				$elapsed = 0;  //meaning new logfile

			//used to help calculate the difference as mysql is thruput not value based
			//so is based on the difference between thruput before the current run
			//this data is stored in _mysql_latest

			// grab net latest location and elapsed
			$mysqllatestElapsed = 0;
			$mysqlLatestLocation = dirname($logfile) . DIRECTORY_SEPARATOR . '_mysql_latest';


			// basically if netlatest elapsed is within reasonable limits (logger interval + 20%) then its from the day
			// before rollover so we can use it to replace regular elapsed
			// which is 0 when there is anew log file
			if (file_exists( $mysqlLatestLocation )) {
				
				$last = explode("|", file_get_contents(  $mysqlLatestLocation ) );

				$mysqllatestElapsed =  ( time() - filemtime($mysqlLatestLocation));

				//if its a new logfile check to see if there is previous netlatest data
				if ($elapsed == 0) {

					//data needs to within the logging period limits to be accurate
					$interval = $this->getLoggerInterval();

					if (!$interval)
						$interval = 360;
					else
						$interval = $interval * 1.2;

					if ( $mysqllatestElapsed <= $interval ) 
						$elapsed = $mysqllatestElapsed;
				}
			}

			//if we were able to get last data from mysql latest above
			//not sure if these are the right dividers
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
				

				$queries_diff = ($queries - $last[2]);
				if ($queries_diff < 0) {
					$queries_diff = (4294967296 + $queries - $last[2]) ;
				}
				$queries_rate = round(($queries_diff/$elapsed),2);


				$string = time() . "|" . $trans_rate . "|" . $recv_rate  . "|" . $queries_rate       . "\n";
			} else {
				//if this is the first value in the set and there is no previous data then its null
				
				$lastlogdata = "|0.0|0.0|0.0";

				$string = time() . $lastlogdata . "\n" ;

			}


		if ( $type == "api") {
			return $string;
		} else {
				//write out log data here
				$this->safefilerewrite($logfile,$string,"a",true);

				// write out last transfare and received bytes to latest
				$last_string = $trans."|".$recv."|".$queries;
				$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . "_mysql_latest";

				$this->safefilerewrite($fh,$last_string,"w",true);
		}
	}

	/**
	 * getUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getUsageData( $logfileStatus)
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$contents = null;

		$replaceDate = self::$current_date;
		
		if ($logfileStatus == false ) {
		
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

		} else {

			$contents = 0;
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

			//need to get disk size in order to process data properly
			//is it better before loop or in loop
			//what happens if you resize disk on the fly ? in loop would be better
			$memorySize = 0;

			//need to start logging total memory
			//what happens if this is -1 ???
			$memorySize = $chartArray[$totalchartArray-1][3] / 1024;

			// get from settings here for module
			// true - show MB
			// false - show percentage
				
			//data[0] = time
			//data[1] = mem used
			//data[2] = swap
			//data[3] = total mem

			$displayMode =	$settings['settings']['display_limiting'];

			for ( $i = 0; $i < $totalchartArray; ++$i) {				
				$data = $chartArray[$i];

				// clean data for missing values
				$redline = ($data[1] == "-1" ? true : false);

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "") || ($data[1] == "-1")  )
					$data[1]=0.0;

				if (  ($data[2] == "-1")  ) 
					$data[2]=0.0;

				if (  ($data[3] == "-1")  )
					$data[3]=0.0;

				//used to filter out redline data from usage data as it skews it
				if (!$redline) {
					$usage[] = ( $data[1] / 1024 );
				}
			

				$time[( $data[1] / 1024 )] = date("H:ia", $data[0]);

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) 
					$timestamps[] = $data[0];

					// display data using MB
					$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";


				if ( (float) $data[1] > $settings['settings']['overload'])
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";

					//swapping
					//if ( isset($data[2])  ) {
					//	$dataArraySwap[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2] / 1024 ) ."]";
					//	$swap[] = ( $data[2] / 1024 );
					//}

			}
			
			$mem_high = max($usage);
			$mem_low  = min($usage); 
			$mem_mean = array_sum($usage) / count($usage);

			$ymax = $mem_high;
			$ymin = $mem_low;			

			$mem_high_time = $time[max($usage)];
			$mem_low_time = $time[min($usage)];
			$mem_latest = ( ( $usage[count($usage)-1]  )  )    ;		


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
				'mem_high' => number_format($mem_high,2),
				'mem_high_time' => $mem_high_time,
				'mem_low' => number_format($mem_low,2),
				'mem_low_time' => $mem_low_time,
				'mem_mean' => number_format($mem_mean,2),
				'mem_latest' => number_format($mem_latest,2),
				//'mem_total' => number_format($mem_total,2),
				//'mem_swap' => number_format($swap,2),
			);
		
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

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mem_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString,
				'chart_data_swap' => $dataSwapString,				// how is it used
				//'swap' => $swap,									// how is it used
				//'swap_count' => $usageCount,						// how is it used
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
		$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file

		$module = __CLASS__;
		$i = 0;
		foreach ( $charts['args'] as $chart ) {
			$chart = json_decode($chart);

			//grab the log file for current date (current date can be overriden to show other dates)
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

			if ( file_exists( $this->logfile )) {
				$i++;				
				$logfileStatus = false;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$stuff = $this->$caller( $logfileStatus, $functionSettings );
				} else {
					$stuff = $this->$caller( $logfileStatus );
				}

			} else {
				//no log file so draw empty charts
				$i++;				
				$logfileStatus = true;
			}

			//now draw chart to screen
			include APP_PATH . '/views/chart.php';
		}
	}
}
