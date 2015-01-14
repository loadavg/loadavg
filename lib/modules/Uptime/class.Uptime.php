<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Swap Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/




class Uptime extends Charts
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
	 * getDiskUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getUsageData(  )
	{
		$class = __CLASS__;
		$settings = LoadModules::$_settings->$class;

		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();

		//display switch used to switch between view modes - data or percentage
		// true - show MB
		// false - show percentage
		$displayMode =	$settings['settings']['display_limiting'];	

		//define datasets
		$dataArrayLabel[0] = 'Uptime';

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
		$sizeofChartArray = 0;

		//takes the log file and parses it into chartable data 
		if ($logStatus) {

			$this->getChartData ($chartArray, $contents,  false );
			$sizeofChartArray = (int)count($chartArray);
		}

		/*
		 * now we loop through the dataset and build the chart
		 * uses chartArray which contains the dataset to be charted
		 */

		if ( $sizeofChartArray > 0 ) {

			//get the size of the disk we are charting - need to calculate percentages
			//$diskSize = $this->getDiskSize($chartArray, $sizeofChartArray);


			// main loop to build the chart data
			for ( $i = 0; $i < $sizeofChartArray; ++$i) {	
				
				$data = $chartArray[$i];

				if ($data == null)
					continue;

				// clean data for missing values and check for redline
				$redline = ($this->checkRedline($data));

				//we skip all redline data for this module
				$redline = false;
				
				//usage is used to calculate view perspectives
				//check for when first data is 0 here 
				if (!$redline) 
					$usage[] = ( $data[1] / 86400);
					

				$timedata = (int)$data[0];
				$time[( $data[1] / 86400 )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				// display data using MB
				$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 86400 ) ."]";

			
			}


			//echo '<pre>PRESETTINGS</pre>';
			//echo '<pre>';var_dump($usage);echo'</pre>';

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */


			$uptime_high = max($usage);
			$uptime_low  = min($usage); 
			$uptime_mean = array_sum($usage) / count($usage);

			//to scale charts
			$ymax = $uptime_high;
			$ymin = $uptime_low;

			$uptime_high_time = $time[max($usage)];
			$uptime_low_time = $time[min($usage)];

			$uptime_latest = ( ( $usage[count($usage)-1]  )    )    ;		

		
			$variables = array(
				'uptime_high' => number_format($uptime_high,4),
				'uptime_high_time' => $uptime_high_time,
				'uptime_low' => number_format($uptime_low,4),
				'uptime_low_time' => $uptime_low_time,
				'uptime_mean' => number_format($uptime_mean,4),
				'uptime_latest' => number_format($uptime_latest,4),
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
			$depth=2; //number of datasets
			$this->buildChartDataset($dataArray,$depth);

			//build chart object			
			$return['chart'] = array(
				'chart_format' => 'line',
				'chart_avg' => 'avg',
				
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $uptime_mean,

				'dataset_1' 	  => $dataArray[0],
				'dataset_1_label' => $dataArrayLabel[0],
				
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

			//get data range we are looking at - need to do some validation in this routine
			//$dateRange = $this->getDateRange();
			$dateRange = loadModules::$date_range;

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple log files
			$this->setLogFile($chart->logfile,  $dateRange, $module );

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

			if (!empty($this->logfile)) {

				$i++;				
				$logfileStatus = true;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$chartData = $this->$caller(  $functionSettings );
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
