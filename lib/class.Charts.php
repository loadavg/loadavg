<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main class for LoadAvg Chart Modules
*
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Charts extends LoadModules
{

	public  $logfile; // Stores the logfile name & path
	public  $logFileDepth; // Stores the data depth based on logger for parsing


	/**
	 * setDataDepth
	 *
	 * used to get depth of data being charted so we can clean it and get rid of erroroneus data
	 *
	 */

	public function setDataDepth( $moduleName )
	{

		$moduleSettings = LoadModules::$_settings->$moduleName; // if module is enabled ... get his settings

		$depth = 0;

		if ( LOGGER == "collectd" && (isset ($moduleSettings['collectd']['depth']))  ) {

			$depth = $moduleSettings['collectd']['depth']; 
		}
		else
		{
			$depth = $moduleSettings['module']['depth']; 
		}

		$this->logFileDepth = $depth;

		return $depth;

	}

	/**
	 * checkRedline
	 *
	 * checks for redline in data point sent over via charting modules
	 * and if it exists sets it to a null (0.0) data value for the chart
	 *
	 */

	function checkRedline (array &$data) 
	{

		$depth = $this->logFileDepth;


		// first check if its a readline and if it is clean data and set back to 0
		//change redline form -1 to RED 

		$redline = (isset($data[1]) && $data[1] == "-1" ? true : false);

		if ($redline) {

			//echo '<pre>PRE REDLINE '; print_r ($data); echo '</pre>';
			for ($x = 1; $x <= $depth; $x++) {
				$data[$x]=0.0;
			} 
			//echo '<pre>POST REDLINE '; print_r ($data); echo '</pre>';
			return true;
		}


		return false;
	}






	/**
	 * setLogFile
	 *
	 * sets log file name or multiple log file names for a range of dates depending on logger
	 * stores in global for chart object for easy access in a array to parse later on
	 *
	 */

	public function setLogFile( $moduleTemplate, $dateRange, $moduleName, $interface = null  )
	{
		//needs error checking here to return null arrays when no log file present
		$logString = null;

		//set depth of dataset for logfile
		$this->setDataDepth( $moduleName );

		//get the settings for the module
        $moduleSettings = LoadModules::$_settings->$moduleName; // if module is enabled ... get his settings
       

		if ( LOGGER == "loadavg" ) {
			
			//for loadavg
			//need to loop through range here
			$loop = 0;
			foreach ( $dateRange as $date ) {
					
				$thelogFile = LOG_PATH . sprintf($moduleTemplate, $date, $interface);

				if ( file_exists( $thelogFile )) {

					$logString[$loop][0] = $thelogFile;	
					$loop++;
				}
			}

		}

		if ( LOGGER == "collectd" ) {

        	//this is only if we are in collectd mode
			$collectdArgs = "";

			if (isset ($moduleSettings['collectd']) )
			{
				$collectdArgs = $moduleSettings['collectd']['args']; 
			} else {
				//we have no collectd support
				return null;
			}

			//parse data
			$collectd = json_decode($collectdArgs[0]);

			//note that this may change!!!
			//when changing dis mount points in df it changes... from dfboot to df-root
			$moduleTemplate = $collectd->name;


			//need to loop through options here as some log files are split up into multiple files
			$loop = 0;
			foreach ( $dateRange as $date ) {

				//grab all logs for day - only increment array if this loops works
				//hence foundData
				$loop2 = 0;
				$foundData = false;
				foreach ( $collectd->functions as $thedata ) {

					$moduleFunction = $thedata;

					$thelogFile = COLLECTD_PATH . $moduleTemplate . "/" . $moduleFunction . "-" . $date;
					
					//show the log files we have read in
					//echo 'LOG: ' . $logFile . '<br>';

					if ( file_exists( $thelogFile )) {
						$logString[$loop][$loop2] = $thelogFile;
						$foundData = true;
						$loop2++;
					}
				}

				//only increment here if above loop is successful!
				if  ($foundData) $loop++;
			}

		}			

		$this->logfile = $logString;

		//return $logString;

	}



	/*
	 * build the chart data array here and patch to check for downtime
	 * as current charts connect last point to next point
	 * so when we are down / offline for more than logging interval
	 * charts dont accuratly show downtime
	 *
	 * send over chartData and contents
	 * return data in chartData
	 * dataset lets you grab the last N hours of the chart if you want a subset
	   can be expanded on to get a subset with starttime / endtime 
	 */

	//parses contents
	//returns in chartData
	//depth is used for cleaning datapoints

	function getChartData (array &$chartData, array &$contents, $patchIt = true ) 
	{				

		$depth = $this->logFileDepth;
		//echo 'getChartData depth : ' . $depth;

		//select 6,12 or 24 hour charts
		$dataSet = LoadAvg::$_settings->general['settings']['chart_type'];
		//echo 'dataSet ' . $dataSet  .  '<br>';

		//delimiter is based on logger type 
		$delimiter = LoadUtility::getDelimiter();

		// this is based on logger interval of 5, 5 min = 300 aprox we add 100 to be safe
		//$interval = 360;  // 5 minutes is 300 seconds + slippage of 20% aprox 
		$interval = $this->getLoggerInterval();

		if (!$interval)
			$interval = 360; //default interval of 5 min
		else
			$interval = $interval * 1.2; //add 20% to interval for system lag

		$patch = $chartData = array();

		//trim the dataset if we are only reading 6 or 12 hours of info
		//revise for 6 and 12 hour charts
		if ( $dataSet == 6 || $dataSet == 12 )
		{
			$totalContents= (int)count( $contents );
			//echo 'TOTAL ' . $totalContents;

			//logger is every 5 min then $this->getLoggerInterval() / 60 = 5;
			//so 300 / 60 = 5 min; 60 / 5 = 12 datasets per hour
			$dataFrame = 60 / ($this->getLoggerInterval() / 60);  

			$dataNeeded = $dataFrame * $dataSet; 

			//TODO: only trim if there is more than we need...
			$contents = array_slice($contents, ($totalContents - $dataNeeded) );     
		}

		//contents is a array of strings for the dataset/logfile
		//now we explode each value in each line of the array into datapoints in chartData 
		//and send it back home as a array!

		//get size of array for parsing
		$totalContents= (int)count( $contents );

		//if there is only one item in data set then we just chart it and return
		if ($totalContents == 1) {

			$data = explode($delimiter, $contents[0]);
			LoadUtility::cleanDataPoint ($data, $depth ); 
			$chartData[0] = $data;

		} else {

			//subtract one from totalContents as arrays start at 0 not 1
			$numPatches = 0;

			for ( $i = 0; $i <= $totalContents-1; ++$i) {

				$data = explode($delimiter, $contents[$i]);
				LoadUtility::cleanDataPoint ($data, $depth ); 
				$chartData[$i] = $data;

				/*
				 * if there is more than one item in dataset then we can check for downtime between points
				 * and patch the dataset so they render ok
				 * this is becuase when rendering we connect lines in the chart (prev to next)
				 * and so downtime comes across as span not a null or 0
				 *
				 * check if difference is more than logging interval and patch for when server is offline
				 * we patch for time between last data (system went down) and next data (system came up)
				 * need to check if we need the nextData patch as well ie if system came up within 
				 * the next interval time
				 * 
				 * for local data we dont check the first value in the data set as if its there it means it was up
				 */
				if ($i > 0 && $i < $totalContents-1 ) {

					//dont do this for last value in dataset! as it will have no difference
					$nextData = explode($delimiter, $contents[$i+1]);
					
					//difference in timestamps
					$difference = $nextData[0] - $data[0];

					if ( $difference >= $interval ) {

						//echo 'patch difference:' . $difference;

						//patches are spans ie fall between datapoints so have start and end
						$patch[$numPatches] = array(  ($data[0]+$interval), "-1", $i);
						$patch[$numPatches+1] = array(  ($nextData[0]- ($interval/2)), "-1", $i);

						$numPatches += 2;
					}	
				}
			}

		}
		
		//if there are patches to be applied, we iterate through the patcharray and patch the dataset
		//by adding patch spans to it
		$totalPatch= (int)count( $patch );

		if ($totalPatch >0 && ($patchIt == true) ) {

			//echo "PATCHCOUNT: " . $totalPatch . "<br>";

			for ( $i = 0; $i < $totalPatch ; ++$i) {
					
					$patch_time = ( ($patch[$i][2]) + $i );
					
					$thepatch[0] = array ( $patch[$i][0] , $patch[$i][1]   );

					array_splice( $chartData, $patch_time, 0, $thepatch );
	        		//echo "PATCHED: " . $patch_time . " count: " . count( $chartData ) . "<br>";
			}
		}
		//echo "PATCHARRAYPATCHED: " . count( $chartData ) . "<br>";


	}


	/**
	 * buildChartDataset
	 *
	 * Takes array of chart data, sorts and prepares it for
	 * flot to render to screen
	 */

	public function buildChartDataset ( &$dataArray, $depth ) 
	{

		for ( $loop =0; $loop < $depth; $loop ++)
		{
			if ( !isset($dataArray[$loop]) || count($dataArray[$loop]) == 0) 
			{ 
				//null kills chartcore and doesnt send variables over to be charted even when empty.
				//$dataArray[$loop] = null; 
				$dataArray[$loop] = 0; 
			}
			else 
			{
				ksort($dataArray[$loop]);
				$dataArray[$loop] = "[" . implode(",", $dataArray[$loop]) . "]";
			}
		}
	}



	/**
	 * generateChart
	 *
	 * Function witch passes the data formatted for the chart view
	 *
	 * @param array @moduleSettings settings of the module
	 * @param string @logdir path to logfiles folder
	 *
	 */

	public function getChartRenderData( $chart, $functionSettings, $module )
	{

		// find out main function from module args that generates chart data
		// in this module its getUsageData above
		$caller = $chart->function;

		$logfileStatus = false;
		$chartData = false;

		if (!empty($this->logfile)) {

			$logfileStatus = true;

			//call modules main function and pass over functionSettings
			if ($functionSettings) {
				$chartData = $this->$caller(  $functionSettings );
			} else {
				$chartData = $this->$caller(  );
			}

		} 

		//if there is no logfile or error from the caller (stuff is false) 
		//then we just return a empty chart
		if ( !isset($chartData) || $chartData == false || $logfileStatus == false ) {

    		$moduleSettings = LoadModules::$_settings->$module; 

			$chartData = $this->parseInfo($moduleSettings['info']['line'], null, $module); 

			$chartData['chart'] = LoadUtility::getEmptyChart();
		}

		return $chartData;
	
	}


	/**
	 * generateTabbedChart
	 *
	 * USes the modules chart.php to render charts instead of genearic function
	 *
	 * @param array @moduleSettings settings of the module
	 * @param string @logdir path to logfiles folder
	 *
	 */	

	public function getChartTemplate($module )
	{

		//echo 'genrate tabbed chart';

        $moduleSettings = LoadModules::$_settings->$module; 

		$charts = $moduleSettings['chart'];

		$templateName = HOME_PATH . DIRECTORY_SEPARATOR . 'lib/modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'views/chart.php';

		//echo 'FILE : ' . $templateName . '<br>';

		return $templateName;	

	}





}
