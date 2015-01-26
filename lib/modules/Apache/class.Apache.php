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

class Apache extends Charts
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
	 * getApacheUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */

	public function getUsageData( )
	{
		$class = __CLASS__;
		$settings = loadModules::$_settings->$class;

		//define some core variables here
		$dataArray = null;
		$dataRedline = $usage = array();

		//display switch used to switch between view modes - data or percentage
		// true - show MB
		// false - show percentage
		$displayMode =	$settings['settings']['display_limiting'];	

		/*
		 * grab the log file data needed for the charts as array of strings
		 * takes logfiles(s) and gives us back contents
		 */

		$contents = array();
		$logStatus = LoadUtility::parseLogFileData($this->logfile, $contents);

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
				//usage is used to calculate view perspectives
				if (!$redline) 
					$usage[] = ( $data[1] );

				$timedata = (int)$data[0];
				$time[( $data[1]  )] = date("H:ia", $timedata); //////// remove long here to fix bug

				$usageCount[] = ($data[0]*1000);

				$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] ) ."]";

				if ( (float) $data[1] > $settings['settings']['overload'])
					$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1]  ) ."]";

			}

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

			$apache_high = max($usage);
			$apache_low  = min($usage); 
			$apache_mean = array_sum($usage) / count($usage);

			//to scale charts
			$ymax = $apache_high;
			$ymin = $apache_low;

			$apache_high_time = $time[max($usage)];
			$apache_low_time = $time[min($usage)];

			$apache_latest = ( ( $usage[count($usage)-1]  )    )    ;		

		
			$variables = array(
				'apache_high' => number_format($apache_high,4),
				'apache_high_time' => $apache_high_time,
				'apache_low' => number_format($apache_low,4),
				'apache_low_time' => $apache_low_time,
				'apache_mean' => number_format($apache_mean,4),
				'apache_latest' => number_format($apache_latest,4),
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
				'mean' => $apache_mean,

				'dataset_1' => $dataArray[0],
				'dataset_1_label' => 'CPU Usage',

				'dataset_2' => $dataArray[1],
				'dataset_2_label' => 'Overload',

				'overload' => $settings['settings']['overload']
			);

			return $return;	
			
		} else {

			return false;
		}
	}



	

	
}




