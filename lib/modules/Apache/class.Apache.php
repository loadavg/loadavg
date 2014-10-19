<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Apache Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Apache extends LoadAvg
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
	 * logApacheUsageData
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


		$url = $settings['settings']['serverstatus'];
		//$url = "http://localhost/server-status";

		$parseUrl = $url . "/?auto";

		$locate = "CPULoad";

		$dataValue = $this->getApacheDataValue($parseUrl, $locate);

		if ($dataValue == null)
			$dataValue = 0;

	    $string = time() . '|' . $dataValue . "\n";

		if ( $type == "api") {
			return $string;
		} else {
			$filename = sprintf($this->logfile, date('Y-m-d'));
			$this->safefilerewrite($filename,$string,"a",true);
		}
	}


	/**
	 * getApacheDataValue
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $dataValue data retrived from mod_status
	 *
	 */

	public function getApacheDataValue($parseurl, $locate) 
	{


		$f = implode(file($parseurl."?dat=".time()),"");

		$active = explode("\n", $f );

		$dataValue = false;

		foreach ($active as $i => $value) {

			$pieces = explode(": ", $active[$i]);

			if ($pieces[0]==$locate) {
				$dataValue = $pieces[1];
			}

		}

		return($dataValue);
    }
    



	/**
	 * getApacheUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getApacheUsageData()
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

			//$swap = array();
			$usageCount = array();
			$dataArray = $dataArrayOver = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			$chartArray = array();

			$this->getChartData ($chartArray, $contents);

			$totalchartArray = (int)count($chartArray);

			for ( $i = 0; $i < $totalchartArray-1; ++$i) {
			
				$data = $chartArray[$i];

				// clean data for missing values
				$redline = ($data[1] == "-1" ? true : false);

				// clean data for missing values
				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")|| ($data[1] == "-1")  )
					$data[1]=0.0;

				//used to filter out redline data from usage data as it skews it
				//usage is used to calculate view perspectives
				if (!$redline)
					$usage[] = ( $data[1]  );


				$time[( $data[1]  )] = date("H:ia", $data[0]);
			
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] ) ."]";
			
				//if ( isset($data[2]) )
				//	$dataArraySwap[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2] / 1024 ) ."]";

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
			

				if ( (float) $data[1] > $settings['settings']['overload'])
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1]  ) ."]";
				
			}

			$apache_high= max($usage);
			$apache_high_time = $time[$apache_high];

			$apache_low = min($usage);
			$apache_low_time = $time[$apache_low];
		
			$apache_mean = array_sum($usage) / count($usage);
			$apache_latest = $usage[count($usage)-1];

			$ymin = $apache_low;
			$ymax = $apache_high;

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



			$variables = array(
				'apache_high' => number_format($apache_high,4),
				'apache_high_time' => $apache_high_time,
				'apache_low' => number_format($apache_low,4),
				'apache_low_time' => $apache_low_time,
				'apache_mean' => number_format($apache_mean,4),
				'apache_latest' => number_format($apache_latest,4),
			);

			//DEBUG HERE
			//print_r ($variables);
		
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			//if (!is_null($dataArraySwap)) ksort($dataArraySwap);


			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			//print_r ($usageCount);

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $apache_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString,
				//'swap_count' => $usageCount,
				'overload' => $settings['settings']['overload']
			);

			return $return;	
		} else {
			//means there was no chart data sent over to chart
			//so just trturn legend and null data back over

			//can we create a null dataString ?
			$dataString =   "[[0, '0.01']]";

			//return false;
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => 0,
				'ymax' => 1,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => 0,
				'chart_data' => $dataString,
				'chart_data_over' => null,
				'overload' => false
			);

			return $return;
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

			if ( file_exists( $this->logfile )) {
				$i++;				
				$no_logfile = false;

				//check if function takes settings via GET url_args 
				$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$stuff = $this->$caller( $functionSettings );
				} else {
					$stuff = $this->$caller( );
				}

			} else {
				$i++;				
				$no_logfile = true;
				$stuff = $this->$caller( );
			}
			
			//now draw chart to screen
			include APP_PATH . '/views/chart.php';
		}
	}
	
}




