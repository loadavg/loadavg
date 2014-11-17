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

		$timestamp = time();

		$load = exec("cat /proc/loadavg | awk -F' ' '{print $1\"|\"$2\"|\"$3}'");
		$string = $timestamp . '|' . $load . "\n";

		//we can also add a switch to feed live data to server with no local logging
		//by just returning data
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		$this->safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;		


	}



	/**
	 * getData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @param string $switch with switch data to populate return array
	 * @return array $return data retrived from logfile
	 *
	 */

	public function getUsageData( $logfileStatus, $switch ) 
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


		// is this really faster than strlen ?
		if (isset($contents{1})) {
		//if ( strlen($contents) > 1 ) {

			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			$dataArray = $dataArrayOver = $dataArrayOver_2 = $dataRedline = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				//echo "24 hour";
				$timestamps = array();
			}
			/*
			 * build the chartArray array here and patch to check for downtime
			 */

			$chartArray = array();
			$this->getChartData ($chartArray, $contents);

			$totalchartArray = (int)count($chartArray);

			//used to limit display data from being sqewed by overloads
			$displayMode =	$settings['settings']['display_limiting'];


			//for ( $i = 0; $i < $totalchartArray; $i++) {	
			for ( $i = 0; $i < $totalchartArray; ++$i) {	

				$data = $chartArray[$i];
				
				// clean data for missing values
				$redline = ($this->checkRedline($data));

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
					$data[1]=0.0;

				//used to filter out redline data from usage data as it skews it
				if (!$redline)
					$usage[$switch][] = $data[$switch];

				$timedata = (int)$data[0];
				$time[$switch][$data[$switch]] = date("H:ia", $timedata);

				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";

				//for 24 hou charts
				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) 
					$timestamps[] = $data[0];
		
				if ( $data[$switch] > $settings['settings']['overload_1'] )
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";
		
				if ( $data[$switch] > $settings['settings']['overload_2'] )
					$dataArrayOver_2[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";

			}


			$cpu_high = max($usage[$switch]);
			$cpu_high_time = $time[$switch][$cpu_high];

			$cpu_low = min($usage[$switch]);
			$cpu_low_time = $time[$switch][$cpu_low];
		
			//$cpu_mean = (float)number_format(array_sum($usage[$switch]) / count($usage[$switch]), 3);
			$cpu_mean = array_sum($usage[$switch]) / count($usage[$switch]) ;
			
			$cpu_latest = $usage[$switch][count($usage[$switch])-1];

			if ($displayMode == 'true' )
			{
				$ymin = $cpu_low;
				$ymax = $settings['settings']['display_cutoff'];
			} else {
				$ymin = $cpu_low;
				$ymax = $cpu_high;
			}

			/////////////////////////////////////////////////////////////
			//what exactly does this do ?
			//disabling it does nothing 

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');

				//echo 'endtimne: ' . $endTime;

				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
			}

			$variables = array(
    	        'cpu_high' => number_format($cpu_high,3),
                'cpu_high_time' => $cpu_high_time,
                'cpu_low' => number_format($cpu_low,3),
                'cpu_low_time' => $cpu_low_time,
    	        'cpu_mean' => number_format($cpu_mean,3),
                'cpu_latest' => number_format($cpu_latest,3)
            );


			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if ( count($dataArrayOver) == 0 ) $dataArrayOver = null;
			if ( count($dataArrayOver_2) == 0 ) $dataArrayOver_2 = null;

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			if (!is_null($dataArrayOver_2)) ksort($dataArrayOver_2);


			$dataString[0] = "[" . implode(",", $dataArray) . "]";
			$dataString[1] = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			$dataString[2] = is_null($dataArrayOver_2) ? null : "[" . implode(",", $dataArrayOver_2) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'chart_avg' => 'avg',

				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $cpu_mean,
				
				'dataset_1' => $dataString[0],
				'dataset_1_label' => 'CPU Load',

				'dataset_2' => $dataString[1],
				'dataset_2_label' => 'Overload',
				
				'dataset_3' => $dataString[2],
				'dataset_3_label' => 'Secondary Overload'
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

	/*
	$stuff is array of:

		$info 
			$line -> array of legend items

		$chart -> 	chart data such as 
					ymin, ymax, chart settings and main chart data array
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
