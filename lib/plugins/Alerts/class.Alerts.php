<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main Server Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Alerts extends LoadPlugins
{


	public static $icon;
	public static $name;


	/**
	 * __construct
	 *
	 * Class constructor, appends Module settings to default settings
	 *
	 */

	
	public function __construct()
	{
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini.php', true));

		//set the plugin link and the icon for the menubar
		self::$name = "Alerts";
		self::$icon = "fa-file";

	}


	/**
	 * getPluginData
	 *
	 * Retrives icon and passes it back for UI - move into plugin class later on
	 *
	 * @return string $cmd command to execute for data
	 *
	 */
	public function getPluginData( )
	{

		$pluginData[0] = self::$name;
		$pluginData[1] = self::$icon;

		return $pluginData;
	}



	/**
	 * setLogFile
	 *
	 * sets log file name or multiple log file names for a range of dates depending on logger
	 * stores in global for chart object for easy access in a array to parse later on
	 *
	 */

	public function getLogFile( $moduleTemplate, $dateRange, $moduleName )
	{
		//needs error checking here to return null arrays when no log file present
		$logString = null;

		//set depth of dataset for logfile
		//$this->setDataDepth( $moduleName );

		//get the settings for the module
        $moduleSettings = LoadPlugins::$_settings->$moduleName; // if module is enabled ... get his settings
       
			
		//for loadavg
		//need to loop through range here
		$loop = 0;
		foreach ( $dateRange as $date ) {
				
			$thelogFile = LOG_PATH . sprintf($moduleTemplate, $date);

			if ( file_exists( $thelogFile )) {

				$logString[$loop][0] = $thelogFile;	
				$loop++;
			}
		}

		return $logString;

	}


	/**
	 * getChartRenderData
	 *
	 * Function which gets the raw chart data from the module
	 *
	 * @param array @chart settings of the chart
	 * @param array @functionSettings settings of the chart
	 * @param string @module module to look up
	 *
	 */



	public function getChartRenderData( $logfile )
	{

		// find out main function from module args that generates chart data
		// in this module its getUsageData above
		//$caller = $chart->function;

		$logfileStatus = false;
		$chartData = false;

		if (!empty($logfile)) {

			$logfileStatus = true;
			$chartData = $this->getUsageData( $logfile );

		} 


		//if there is no logfile or error from the caller (stuff is false) 
		//then we just return a empty chart
		if ( !isset($chartData) || $chartData == false ) {

			$chartData['chart'] = LoadUtility::getEmptyChart();
		}

		//echo '<pre>'; var_dump ($chartData); echo '</pre>'; 

		return $chartData;
	
	}

	/**
	 * getUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getUsageData( $logfile )
	{
		$class = __CLASS__;
		$settings = LoadPlugins::$_settings->$class;



		//define some core variables here
		$dataArray = $dataArrayLabel = array();
		$dataRedline = $usage = array();


		/*
		 * grab the log file data needed for the charts as array of strings
		 * takes logfiles(s) and gives us back contents
		 */		

		$contents = array();
		$logStatus = LoadUtility::parseLogFileData($logfile, $contents);

		//echo '<pre> :: '; var_dump ($contents); echo '</pre>'; 

		/*
		 * build the chartArray array here as array of arrays needed for charting
		 * takes in contents and gives us back chartArray
		 */

		$chartArray = array();
		$sizeofChartArray = 0;

		$chartData = array();

		//delimiter is based on logger type used to explode data
		$delimiter = LoadUtility::getDelimiter();
		$delimiter = "|";


		//takes the log file and parses it into chartable data 

		//echo '<pre> :: <br>'; 

		if ($logStatus) {

			//$this->getChartData ($chartArray, $contents,  false );

			$totalContents= (int)count( $contents );

			for ( $i = 0; $i < $totalContents; ++$i) {

				//grab the first dataset
				$data = explode($delimiter, $contents[$i]);

				//echo $data[0] . " " . $data[1] . " " . $data[2] . "<br>"; 

				$chartData[$i] = $data;
				//$chartData[$data[1]] = $data;
			
				//$chartValues[$i] = json_decode($data[2]);
				//var_dump ($chartValues[$i]); 

			}

		}
		
		return $chartData;

	}

    //sory dimensional array by key
    //http://stackoverflow.com/questions/2189626/group-a-multidimensional-array-by-a-particular-value
    
	public function arraySort($input,$sortkey){

	//echo '<pre>';
	//var_dump ($sortkey);
	//var_dump ($input[0]);
	//echo '</pre>';
	

	  foreach ($input as $key=>$val) 
	  	$output[$val[$sortkey]][]=$val;
	  
	  return $output;
	}



	/**
	 * buildtimeArray
	 *
	 * Function which gets the raw chart data from the module
	 *
	 * @param array @chart settings of the chart
	 * @param array @functionSettings settings of the chart
	 * @param string @module module to look up
	 *
	 */



	public function buildTimeArray( $dataArray )
	{

		//dataArray - array of modules alerts
		//dataArray["Cpu"] - module 1 ie cpu
		//dataArray["Disk"] - module 2 ie disk

		//really should merge this with alert array and then sort all
		//build time array and sort alerts into it for each module ?
		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		$timeArray = array();

		for ($i = 1; $i <= 24; $i++) {
		    $timeArray[$i]['time'] = date('h:i a', $iTimestamp) ;

			foreach ($dataArray as $value) {

		    	$timeArray[$i][$value[0][1]] = 0 ;
		    }

		    //swap this for 1/2 hour periods
		    //$iTimestamp += 1800;
		    $iTimestamp += 3600;
		}

		//loop through alert data and create time array
		foreach ($dataArray as $value) {
		
			//echo '<strong>Module:</strong> ' . $value[0][1] . '<br>';

			$module = $value[0][1];
			
			//all alerts for module by time
			foreach ($value as $items) {

				//get time of alert here
                $theAlertTime = date("h:i a", $items[0]);
				
				//echo '<strong>Alert:</strong> ' . $theAlertTime . '  ' ;

				for ($i = 1; $i <= 24; $i++) {

					if ($i == 1)
					{
						if ( strtotime($theAlertTime) == strtotime($timeArray[$i]['time']) )		{				
							//echo ' Found1: ' . $timeArray[$i]['time'] . '<br>';
							$timeArray[$i][$value[0][1]] += 1 ;
							break;
						}
					}

					if ($i > 1)
					{
						if ( strtotime($theAlertTime) >= strtotime($timeArray[$i]['time']) )
							continue;
						else {
							//echo ' Found2: ' . $timeArray[$i-1]['time'] . '<br>';
							$timeArray[$i-1][$value[0][1]] += 1 ;
							break;
						}
					}  
				}
			}
		}



		return $timeArray;
	
	}




	/**
	 * buildtimeArray
	 *
	 * Function which gets the raw chart data from the module
	 *
	 * @param array @chart settings of the chart
	 * @param array @functionSettings settings of the chart
	 * @param string @module module to look up
	 *
	 */

	//make alertArray global to class ? so we dont have to pass it around ?

	public function getTimeSlotAlert( $module, $startTime, $endTime, $alertArray )
	{
			echo "<pre>";


        //$theAlertTime = date("h:i a", $timeslot);

		//echo $timeslot . "<br>";


		//need to scrub the alertArray for all times that are in timeslot!!!
		//var_dump ($alertArray[$module]);


		foreach ($alertArray[$module] as $item) {

            $theTime = date("h:i a", $item[0]);

			$theTimeCheck = strtotime($theTime);

			if ( ($theTimeCheck >= strtotime($startTime)) && 
			     ($theTimeCheck < strtotime($endTime)) ) 
			{
				echo '<strong>Alerts:</strong> ' . $item[1];
				echo ' Time: ' . $theTime. " ";

				//alert!
	            $alertData = json_decode($item[2]);
				//var_dump ($alertData);

				if (isset($alertData[0][0]))
				{
				echo ' Alert: ' . $alertData[0][0];
				echo ' Trigger: ' . $alertData[0][1];
				echo ' Value: ' . $alertData[0][2];
				}

				if (isset($alertData[1][0]))
				{
				echo ' Alert: ' . $alertData[1][0];
				echo ' Trigger: ' . $alertData[1][1];
				echo ' Value: ' . $alertData[1][2];
				}

				echo '<br>';
			}
		//}
			}
		//}
			echo "</pre>";

	}


} // end of class
?>
