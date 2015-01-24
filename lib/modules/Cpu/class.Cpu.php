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



class Cpu extends Charts
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
		$settings = LoadModules::$_settings->$class;

		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();

		//display switch used to switch between view modes - data or percentage
		//switches between fixed and fitted view modes
		$displayMode =	$settings['settings']['display_limiting'];	

		//define datasets
		$dataArrayLabel[0] = 'CPU Load';
		$dataArrayLabel[1] = 'Overload';
		$dataArrayLabel[2] = 'Secondary Overload';

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
				if (!$redline)
					$usage[$switch][] = $data[$switch];

				//time data
				$timedata = (int)$data[0];
				$time[$switch][$data[$switch]] = date("H:ia", $timedata);

				//chart arrays
				$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";
		
				if ( $data[$switch] >= $settings['settings']['overload_1'] )
					$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";
		
				if ( $data[$switch] >= $settings['settings']['overload_2'] )
					$dataArray[2][$data[0]] = "[". ($data[0]*1000) .", '". $data[$switch] ."']";

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

			//parse, clean and sort dataArray to necesary depth
			$depth=3; //number of datasets
			$this->buildChartDataset($dataArray,$depth);

			$return  = array();

			// get legend layout from ini file
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			//build chart object
			$return['chart'] = array(
				'chart_format' 	  => 'line',
				'chart_avg' 	  => 'avg',

				'ymin' 			  => $ymin,
				'ymax' 			  => $ymax,
				'mean' 			  => $cpu_mean,
				
				'dataset_1' 	  => $dataArray[0],
				'dataset_1_label' => $dataArrayLabel[0],

				'dataset_2' 	  => $dataArray[1],
				'dataset_2_label' => $dataArrayLabel[1],
				
				'dataset_3' 	  => $dataArray[2],
				'dataset_3_label' => $dataArrayLabel[2]
			);

			return $return;

		} else {

			return false;
		}
	}


}
