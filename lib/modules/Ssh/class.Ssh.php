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

class Ssh extends Charts
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
	 * getChartLabel
	 *
	 * Gets label for chart based on mode
	 *
	 */
	
	public function getChartLabel( $switch  )
	{

		//mode specific data is set up here
		//1 == Accepted
		//2 == Failed
		//3 == Invalid

		$theLabel = "";
		switch ( $switch) {
			case 1: 	$theLabel = "Accepted";						
						break;

			case 2: 	$theLabel = "Failed";						
						break;

			case 3: 	$theLabel = "Invalid";						
						break;
		}

		return $theLabel;

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


	public function getUsageData( $switch = 1)
	{

		$class = __CLASS__;
		$settings = loadModules::$_settings->$class;

		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();

		//display switch used to switch between view modes - data or percentage
		$displayMode =	$settings['settings']['display_limiting'];

		//define datasets
		$dataArrayLabel[0] = $this->getChartLabel($switch); 
		$dataArrayLabel[1] = 'Failed';
		$dataArrayLabel[2] = 'Invalid User';

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

		if ($logStatus) {

			//takes the log file and parses it into chartable data 
			$this->getChartData ($chartArray, $contents );
			$sizeofChartArray = (int)count($chartArray);
		}


		/*
		 * now we loop through the dataset and build the chart
		 * uses chartArray which contains the dataset to be charted
		 */
		
		 if ( $sizeofChartArray > 0 ) {
			
			// main loop to build the chart data			
			for ( $i = 0; $i < $sizeofChartArray; ++$i) {		

				$data = $chartArray[$i];

				if ($data==null)
					continue;

				// clean data for missing values
				$redline = ($this->checkRedline($data));

				//used to filter out redline data from usage data as it skews it
				if (!$redline) {
					$usage[1][] = ( $data[1]  );
					$usage[2][] = ( $data[2]  );
					$usage[3][] = ( $data[3]  );
				}
			
				$timedata = (int)$data[0];
				$time[( $data[1]  )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				//dirty hack here as we arent using switch across the board
				//rather using switch mode 1 as a default
				if ($displayMode == "true") {

					// display data accepted
					$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[$switch]  ) ."]";
				

				} else {

					// display data accepted
					$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1]  ) ."]";
					
					// display data failed
					$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2]  ) ."]";
					
					// display data invalid user
					$dataArray[2][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[3]  ) ."]";					
				}
			}

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

			$ssh_accept = array_sum($usage[1]);			
			$ssh_failed = array_sum($usage[2]);
			$ssh_invalid = array_sum($usage[3]);

			//set zoom based on all data or some data
			if ($displayMode == "true") {
				$ssh_high = max($usage[$switch]);
			}
			else
				$ssh_high = max( (max($usage[1])) , (max($usage[2])), (max($usage[3])) );

			//really needs to be max across data 1, data 2 and data 3
			$ymax = $ssh_high;
			$ymin = 0;
			

			//need to really clean up this module!
			//as when no attemtps to access ssh logs time still has data ?

			//we can only do this if there is more than 1 in usage array?
			if (count ($time) > 1)
				$ssh_latest_login  = (    $time[   $usage[1][count($usage)-1]  ]    )    ;		
			else
				$ssh_latest_login  = (    $time[  $usage[1][1]  ]   )    ;		


			$variables = array(
				'ssh_accept' 	=> $ssh_accept,
				'ssh_failed' 	=> $ssh_failed,
				'ssh_invalid' 	=> $ssh_invalid,
				'ssh_latest_login' => $ssh_latest_login
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
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),

				'dataset_1' 	  => $dataArray[0],
				'dataset_1_label' => $dataArrayLabel[0],

				'dataset_2' 	  => $dataArray[1],
				'dataset_2_label' => $dataArrayLabel[1],

				'dataset_3' 	  => $dataArray[2],
				'dataset_3_label' => $dataArrayLabel[2],

				'overload' => $settings['settings']['overload'],

				'chart_avg' => 'stack',

				//really need to send sorted array with labels for stacking
				'variables' => $variables


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

	public function genChart($moduleSettings, $drawAvg = true )
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
			$dateRange = loadModules::$date_range;

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple log files
			$this->setLogFile($chart->logfile,  $dateRange, $module );


			// find out main function from module args that generates chart data
			// in this module its getUSageData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) 
				? $_GET[$moduleSettings['module']['url_args']] : '2' );

			//need to update for when more than 1 logfile ?
			//cant do file exists here
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

