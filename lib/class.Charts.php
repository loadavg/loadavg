<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main controller class for LoadAvg 2.0
*
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Charts extends LoadAvg
{


	/**
	 * checkRedline
	 *
	 * checks for redline in data point sent over via charting modules
	 * and if it exists sets it to a null (0.0) data value for the chart
	 *
	 */


	function checkRedline (array &$data, $depth = 3 ) 
	{
		// check if its a readline and if so clean data and set back to 0
		// gotta be a better way of doing this really but it works

		$redline = ($data[1] == "-1" ? true : false);

		if ($redline) {

			//echo '<pre>PRE REDLINE '; print_r ($data); echo '</pre>';
			for ($x = 1; $x <= $depth; $x++) {
				$data[$x]=0.0;
			} 
			//echo '<pre>POST REDLINE '; print_r ($data); echo '</pre>';

		}

		return $redline;
	}



	/**
	 * getLogFile
	 *
	 * gets log file name or multiple log file names for a range of dates depending on logger
	 * and retunrs to module in a array to parse later on
	 *
	 */

	public function getLogFile( $moduleTemplate, $dateRange, $moduleName, $interface = null  )
	{
		//needs error checking here to return null arrays when no log file present
		$logString = null;

		//get the settings for the module
        $moduleSettings = LoadAvg::$_settings->$moduleName; // if module is enabled ... get his settings
       

		if ( LOGGER == "loadavg" ) {
			
			//for loadavg
			//need to loop through range here
			$loop = 0;
			foreach ( $dateRange as $date ) {
					
				$logFile = LOG_PATH . sprintf($moduleTemplate, $date, $interface);

				if ( file_exists( $logFile )) {

					$logString[$loop][0] = $logFile;	
					$loop++;
				}
			}

			return $logString;

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

					$logFile = COLLECTD_PATH . $moduleTemplate . "/" . $moduleFunction . "-" . $date;
					
					//show the log files we have read in
					//echo 'LOG: ' . $logFile . '<br>';

					if ( file_exists( $logFile )) {
						$logString[$loop][$loop2] = $logFile;
						$foundData = true;
						$loop2++;
					}
				}

				//only increment here if above loop is successful!
				if  ($foundData) $loop++;
			}

			return $logString;
		}			
	}

	/**
	 * parseLogFileData
	 *
	 * returns data from inside log file in array
	 * if $data is array then grabs multiple files of data and
	 * uses array_merge when log files are across multiple days / ranges
	 *
	 */

	public function parseLogFileData( $data, &$newDataArray  )
	{

		//do some checks first
		if ( !$data || $data == null || !isset($data) )
			return false;

		//loop through all data files and add them up here		
		//data is a array of log files to parse, the depth being used for multiple days
		//for eg when we have a date range
		//log file data is then read from disk and parsed into newDataArray

		$contents = "";
		$loop = 0;

	   	//used to show log files that are being parsed
	    //var_dump($data);

		foreach ($data as $dataKey => $logFileArray) {
	   
	   		//now grab data from disk
			$contents = $this->getLogFileDataFromDisk($logFileArray);

			//merge results sequentially when more than one file is read in
			$newDataArray = array_merge($newDataArray, $contents);
		}

			//echo '<pre>';
			//print_r ($newDataArray);
			//echo '</pre>';

		//TODO: what if getLogFileDataFromDisk was false ? need to return false here
		return true;

	}


	/**
	 * getLogFileDataFromDisk
	 *
	 * $logFileArray is a array of log files to parse 
	 * for a simple individual log file its a array of 1
	 * for more complex log files that are split across separate files its a array of > 1
	 *
	 */

	public function getLogFileDataFromDisk( $logFileArray  )
	{

		//first we need to loop through log file and build mycontents array which is newline exploded 
		//array of data sets from each log file read from disk!

		$arraysize = 0;
		foreach ($logFileArray as $dataKey => $logFile) {

			if ( file_exists( $logFile )) {

				$mycontents[$arraysize] = file_get_contents($logFile);
				$mycontents[$arraysize] = explode("\n", $mycontents[$arraysize]);

				//used just for collectd to clean top of datasets where descriptions are
				if (LOGGER == "collectd"){
					array_shift($mycontents[$arraysize]);
				}

				//if last value is null or empty or ???? delete it
				if ( end($mycontents[$arraysize]) == null || end($mycontents[$arraysize]) == "" )
					array_pop($mycontents[$arraysize]);

				$arraysize++;
			}
		}

		//if its just a single log file we can return it now
		//otherwise parse it and then return it
		if ($arraysize == 1) {
			return $mycontents[0];
		} else {

			$finaldata = $this->parseComplexLogFiles( $mycontents, $arraysize  );
			return $finaldata;		
		}
	}

	/**
	 * parseComplexLogFiles
	 *
	 * when dealing with complex log files ie log data split across multiple files
	 * we need to read in all parts, parse to arrays and then merge them togeather
	 * into a single array as loadavg charts work with a single array of log data only!
	 * currently a bit of a mission! 
	 *
	 */

	public function parseComplexLogFiles( $mycontents, $arraysize  )
	{

		//fist we have to loop through each data set in each log file 
		//as per the depth of the array (number of files) parse it and then
		//stitch it back up into the newDataArray

		//now we loop through multiple mycontents array break out data values
		$thenewarray = array();

		//delimiter is based on logger type 
		$delimiter = $this->getDelimiter();

		//main loop is number of datasets to me merged togeather
		for ($dataloop = 0; $dataloop < $arraysize; $dataloop++) {
		$finaldata = "";

			//this builds the array 
			$loop = 0;
			foreach ($mycontents[$dataloop] as &$value) {

				$thedata = explode($delimiter , $value);

				//for first data set grab timestamp
				if ($dataloop==0)
					$thenewarray[0][$loop] = isset($thedata[0]) ? $thedata[0] : null;
				
				//all other data sets its the 2nd value
				$thenewarray[$dataloop+1][$loop] = isset($thedata[1]) ? $thedata[1] : null;

			    $loop++;
			}
			unset($value); 

		} 

		//now rebuild data into $thenewarray as a single array -  stitch it back up
		$loop = 0;
		foreach ($thenewarray[0] as &$value) {

			$dataString = "";
			for ($dataloop = 0; $dataloop <= $arraysize; $dataloop++) {
				$dataString .= $thenewarray[$dataloop][$loop] . ",";
			}
			
			//need to kill the last "," here as its not needed ?
			$dataString = substr($dataString, 0, -1);
			$finaldata[$loop] = $dataString;

		    $loop++;
		}
		unset($value); 

		return $finaldata;		
		
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

	function getChartData (array &$chartData, array &$contents ) 
	{				


		//select 6,12 or 24 hour charts
		$dataSet = LoadAvg::$_settings->general['settings']['chart_type'];
		//echo 'dataSet ' . $dataSet  .  '<br>';

		//delimiter is based on logger type 
		$delimiter = $this->getDelimiter();

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
			$chartData[0] = $data;

		} else {

			//subtract one from totalContents as arrays start at 0 not 1
			$numPatches = 0;

			for ( $i = 0; $i <= $totalContents-1; ++$i) {

				$data = explode($delimiter, $contents[$i]);
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
		//echo "PATCHCOUNT: " . $totalPatch . "<br>";

		if ($totalPatch >0) {

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
	 * Takes array of filan chart data, sorts and prepares it for
	 * flot to render to screen
	 */

	public function buildChartDataset ( &$dataArray, $depth ) 
	{

		for ( $loop =0; $loop < $depth; $loop ++)
		{
			if ( !isset($dataArray[$loop]) || count($dataArray[$loop]) == 0) 
			{ 
				$dataArray[$loop] = null; 
			}
			else 
			{
				ksort($dataArray[$loop]);
				$dataArray[$loop] = "[" . implode(",", $dataArray[$loop]) . "]";
			}
		}
	}


	/**
	 * getDelimiter
	 *
	 * Returns delimiter used for parsing log files
	 *
	 * LOGGER is globla defined in globals.php
	 */

	public function getDelimiter ( ) 
	{
		$delimiter = "";
		switch ( LOGGER ) {

			case "collectd": 	$delimiter = ",";				
								break;

			case "loadavg": 	$delimiter = "|";				
								break;

			default: 			$delimiter = "|";				
								break;				
		}

		return $delimiter;

	}


	/**
	 * getEmptyChart
	 *
	 * Returns data used to chart a empty chart for when there is no chart data
	 *
	 * @param array $emptyChart array with empty chart data
	 */

	public function getEmptyChart( )
	{
		$emptyChart = array(
			'chart_format' => 'line',
			'chart_avg' => 'avg',
			'ymin' => 0,
			'ymax' => 1,
			'xmin' => date("Y/m/d 00:00:01"),
			'xmax' => date("Y/m/d 23:59:59"),
			'mean' => 0,
			'dataset_1_label' => "No Data",
			'dataset_1' => "[[0, '0.00']]"
		);

		return $emptyChart;
	}


}