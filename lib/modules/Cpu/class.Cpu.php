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

		$load = null;

		//use the php function if its there
		if (!function_exists('sys_getloadavg')) {
		   		$load = exec("cat /proc/loadavg | awk -F' ' '{print $1\"|\"$2\"|\"$3}'");
		} else {
			$phpload=sys_getloadavg();
			$load=$phpload[0] . "|" . $phpload[1] . "|" . $phpload[2];
		}

		//if we want fancy formatting in logs we can always format them like this
	 	//$number = number_format((float)$number, 2, '.', '');

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

	public function getUsageData( $switch ) 
	{

		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		//define some core variables here
		$dataArray = $dataRedline = $usage = array();
		$dataArrayOver = $dataArrayOver_2 = array();

		//display switch used to switch between view modes - data or percentage
		$displayMode =	$settings['settings']['display_limiting'];	

		/*
		 * grab the log file data needed for the charts as array of strings
		 * takes logfiles(s) and gives us back contents
		 */

		$contents = array();
		$logStatus = $this->parseLogFileData($this->logfile, $contents);

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
				
				// clean data for missing values
				$redline = ($this->checkRedline($data));

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
					$data[1]=0.0;

				//used to filter out redline data from usage data as it skews it
				if (!$redline)
					$usage[$switch][] = $data[$switch];

				//time data
				$timedata = (int)$data[0];
				$time[$switch][$data[$switch]] = date("H:ia", $timedata);

				//chart arrays
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";
		
				if ( $data[$switch] >= $settings['settings']['overload_1'] )
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";
		
				if ( $data[$switch] >= $settings['settings']['overload_2'] )
					$dataArrayOver_2[$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";

			}

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */
		
			$cpu_high = max($usage[$switch]);
			$cpu_high_time = $time[$switch][$cpu_high];

			$cpu_low = min($usage[$switch]);
			$cpu_low_time = $time[$switch][$cpu_low];
		
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
		

			$variables = array(
    	        'cpu_high' => number_format((double)$cpu_high,3),
                'cpu_high_time' => $cpu_high_time,
                'cpu_low' => number_format($cpu_low,3),
                'cpu_low_time' => $cpu_low_time,
    	        'cpu_mean' => number_format($cpu_mean,3),
                'cpu_latest' => number_format($cpu_latest,3)
            );

			/*
			 * all data to be charted is now cooalated into $return
			 * and is returned to be charted
			 * 
			 */

			$return  = array();

			// get legend layout from ini file
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
				'chart_format' 	  => 'line',
				'chart_avg' 	  => 'avg',

				'ymin' 			  => $ymin,
				'ymax' 			  => $ymax,
				'mean' 			  => $cpu_mean,
				
				'dataset_1' 	  => $dataString[0],
				'dataset_1_label' => 'CPU Load',

				'dataset_2' 	  => $dataString[1],
				'dataset_2_label' => 'Overload',
				
				'dataset_3' 	  => $dataString[2],
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
	$chartData is array of:

		$info 
			$line -> array of legend items

		$chart -> 	chart data such as 
					ymin, ymax, chart settings and main chart data array
	*/

	public function genChart($moduleSettings)
	{

		//get chart settings for module
		$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file
		$module = __CLASS__;

		//this loop is for modules that have multiple charts in them - like mysql and network
		$i = 0;
		foreach ( $charts['args'] as $chart ) {

			$chart = json_decode($chart);

			//grab the log file for current date (current date can be overriden to show other dates)
			//problem when overiding dates with new format ? why ?
			//$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);

			//get data range we are looking at - need to do some validation in this routine
			$dateRange = $this->getDateRange();

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple files make up a log file
			$this->logfile = $this->getLogFile($chart->logfile,  $dateRange, $module );


			// find out main function from module args that generates chart data
			// in this module its getUSageData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) 
				? $_GET[$moduleSettings['module']['url_args']] : '2' );

			//need to update for when more than 1 logfile ?
			if (!empty($this->logfile)) {

			//if ( file_exists( $this->logfile[0][0] )) {
				$i++;				
				$logfileStatus = true;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$chartData = $this->$caller( $functionSettings );
				} else {
					$chartData = $this->$caller(  );
				}

			} else {
				//no log file so draw empty charts
				$i++;				
				$logfileStatus = false;
			}

			//now draw chart to screen
			include APP_PATH . '/views/chart.php';
		}
	}

}
