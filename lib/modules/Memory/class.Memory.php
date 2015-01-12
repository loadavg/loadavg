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

class Memory extends Charts
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

		$timestamp = time();

		/* 
			grab this data directly from /proc/meminfo in a single call
			egrep --color 'Mem|Cache|Swap' /proc/meminfo
		*/
		
		//pulling Cached here gives us both Cached and SwapCached
		exec( "egrep 'MemTotal|MemFree|Buffers|Cached|SwapTotal|SwapFree' /proc/meminfo | awk -F' ' '{print $2}'", $sysmemory );

		/*
		  [0]=> string(9) "MemTotal:"
		  [1]=> string(8) "MemFree:"
		  [2]=> string(8) "Buffers:"
		  [3]=> string(7) "Cached:"
		  [4]=> string(11) "SwapCached:"
		  [5]=> string(10) "SwapTotal:"
		  [6]=> string(9) "SwapFree:"
		*/

		//calculate memory usage
		$totalmemory = $sysmemory[0];
		$freememory = $sysmemory[1];
		$bufferedmemory = $sysmemory[2];
		$cachedmemory = $sysmemory[3];

		$memory = $totalmemory - $freememory - $bufferedmemory - $cachedmemory;


		//calculate swap usage
		$swapcached = $sysmemory[4];
		$totalswap = $sysmemory[5];
		$freeswap = $sysmemory[6];

		$swap = $totalswap - ($freeswap + $swapcached);

	    $string = $timestamp . '|' . $memory . '|' . $swap . '|' . $totalmemory . "\n";

	    //echo 'DATA:'  . $string .  "\n" ;

		$filename = sprintf($this->logfile, date('Y-m-d'));
		$this->safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;
	}

	/**
	 * getDiskSize
	 *
	 * Gets size of disk based on logger and offsets
	 *
	 * @return the disk size
	 *
	 */
	
	public function getMemorySize( $chartArray, $sizeofChartArray  )
	{

			//need to get memory size in order to process data properly
			//is it better before loop or in loop
			//what happens if you resize disk on the fly ? in loop would be better
			$memorySize = 0;

			//map the collectd disk size to our disk size here
			//subtract 1 from size of array as a array first value is 0 but gives count of 1
			if ( LOGGER == "collectd")
			{	
				$memorySize = ( $chartArray[$sizeofChartArray-1][1] + 
								$chartArray[$sizeofChartArray-1][2] + 
								$chartArray[$sizeofChartArray-1][3] ) / 1024;
			} else {

				$memorySize = $chartArray[$sizeofChartArray-1][3] / 1024;
			}

			return $memorySize;

	}

	/**
	 * reMapData
	 *
	 * remap data based on loogger
	 *
	 * @data sent over by caller
	 * @return none
	 *
	 */
	
	public function reMapData( &$data )
	{
		if ( LOGGER == "collectd")
		{

			$dmemory =  $data[1] + $data[2] + $data[3]; 

			$dtotalmemory = $data[1] + $data[2] + $data[3] + $data[4];

			$data[1] = $dmemory;
			$data[2] = 0;  //used for swap in loadavgd... not used in collectd
			$data[3] = $dtotalmemory;
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
	
	public function getUsageData( )
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();
		$swap = array();
		
		//display switch used to switch between view modes - data or percentage
		// true - show MB
		// false - show percentage
		$displayMode =	$settings['settings']['display_limiting'];	

		//define datasets
		$dataArrayLabel[0] = 'Memory Usage';
		$dataArrayLabel[1] = 'Overload';
		$dataArrayLabel[2] = 'Swap';

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

			//get the size of memory we are charting
			$memorySize = $this->getMemorySize($chartArray, $sizeofChartArray);

			// main loop to build the chart data
			for ( $i = 0; $i < $sizeofChartArray; ++$i) {				
				
				$data = $chartArray[$i];

				if ($data == null)
					continue;

				//check for redline
				$redline = ($this->checkRedline($data));

				//remap data if it needs mapping based on different loggers
				$this->reMapData($data);

				//if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
				//	$data[1]=0.0;

				//used to filter out redline data from usage data as it skews it
				if (!$redline) {
					$usage[] = ( $data[1] / 1024 );
					$percentage_used =  ( $data[1] / $data[3] ) * 100; // DIV 0 REDLINE
				} else {
					$percentage_used = 0;
				}
			
				$timedata = (int)$data[0];
				$time[( $data[1] / 1024 )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				if ($displayMode == 'true' ) {
					// display data using MB
					$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";

					if ( $percentage_used > $settings['settings']['overload'] )
						$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1024 ) ."]";

					//swapping
					if ( isset($data[2])  ) {						
						$dataArray[2][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2] / 1024 ) ."]";
						$swap[] = ( $data[2] / 1024 );

					}

				} else {
					// display data using percentage
					$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". $percentage_used ."]";

					if ( $percentage_used > $settings['settings']['overload'])
						$dataArray[1][$data[0]] = "[". ($data[0]*1000) .", ". $percentage_used ."]";

					//swapping
					if ( isset($data[2])  ) {

						if (!$redline) 
							$swap_percentage = ( ($data[2] / $data[3])  * 100); // DIV 0 REDLINE
						else
							$swap_percentage = 0;
						
						$dataArray[2][$data[0]] = "[". ($data[0]*1000) .", ". $swap_percentage ."]";
						$swap[] = $swap_percentage;
					}
				}
			}

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

			//echo $percentage_used; die;
			end($swap);
			$swapKey = key($swap);
			$swap = $swap[$swapKey];

			if ($displayMode == 'true' )
			{
				$mem_high = max($usage);
				$mem_low  = min($usage); 
				$mem_mean = array_sum($usage) / count($usage);

				if  ( $swap > 1 ) {
					$ymax = $mem_high*1.05;
					$ymin = $swap/2;
				}
				else {
					$ymax = $mem_high;
					$ymin = $mem_low;
				}

			} else {

				$mem_high=   ( max($usage) / $memorySize ) * 100 ;				
				$mem_low =   ( min($usage) / $memorySize ) * 100 ;
				$mem_mean =  ( (array_sum($usage) / count($usage)) / $memorySize ) * 100 ;

				//these are the min and max values used when drawing the charts
				//can be used to zoom into datasets
				$ymin = 1;
				$ymax = 100;

			}

			$mem_high_time = $time[max($usage)];
			$mem_low_time = $time[min($usage)];
			$mem_latest = ( ( $usage[count($usage)-1]  )  )    ;		

			//TODO need to get total memory here
			//as memory can change dynamically in todays world!

			$mem_total = $memorySize;
			$mem_free = $mem_total - $mem_latest;

		
			// values used to draw the legend
			$variables = array(
				'mem_high' => number_format($mem_high,2),
				'mem_high_time' => $mem_high_time,
				'mem_low' => number_format($mem_low,2),
				'mem_low_time' => $mem_low_time,
				'mem_mean' => number_format($mem_mean,2),
				'mem_latest' => number_format($mem_latest,2),
				'mem_total' => number_format($mem_total,2),
				'mem_swap' => number_format($swap,2),
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
				'chart_avg' => 'avg',

				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mem_mean,

				'dataset_1' 	  => $dataArray[0],  
				'dataset_1_label' => $dataArrayLabel[0],

				'dataset_2' 	  => $dataArray[1],
				'dataset_2_label' => $dataArrayLabel[1],
				
				'dataset_3' 	  => $dataArray[2],				// how is it used
				'dataset_3_label' => $dataArrayLabel[2],
				
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


	public function genChart($moduleSettings)
	{

		//get chart settings for module
		$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file

		$module = __CLASS__;

		//this loop is for modules that have multiple charts in them - like mysql and network
		$i = 0;
		foreach ( $charts['args'] as $chart ) {
			$chart = json_decode($chart);

			//get data range we are looking at - need to do some validation in this routine
			$dateRange = $this->getDateRange();

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple log files
			$this->setLogFile($chart->logfile,  $dateRange, $module );

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) 
				? $_GET[$moduleSettings['module']['url_args']] : '2' );

			//need to update for when more than 1 logfile ?
			//cant do file exists here
			if (!empty($this->logfile)) {

				$i++;				
				$logfileStatus = true;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$chartData = $this->$caller( $functionSettings );
				} else {
					$chartData = $this->$caller( );
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
