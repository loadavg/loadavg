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

			}

		}
		
		return $chartData;

	}

    //

	/**
	 * arraySort
	 *
	 * sory dimensional array by key
	 * http://stackoverflow.com/questions/2189626/group-a-multidimensional-array-by-a-particular-value
	 *
	 * @param array @input array to be sorted
	 * @param string @sortkey key to sort by
	 * @param array @output array to be returned
	 *
	 */    
	public function arraySort($input,$sortkey){
		
		//this is only set when there is no data for osme reason
		//for when charts return null chart object 
		if ( !isset($input['chart']['dataset_labels'][0]) ) {
			foreach ($input as $key=>$val) 
		  		$output[$val[$sortkey]][]=$val;
		}

		return $output;
	}


	/**
	 * buildChartArray
	 *
	 * Function which builds the chart array used to render alert charts
	 *
	 * @param array @dataArray sorted alert data sent over to build array with
	 * @param array @timeArray array that is returned
	 *
	 */
	public function buildChartArray( $dataArray )
	{

//echo '<pre>';
//var_dump ($dataArray);

		//get todays time at 00:00
		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		//create time array using 24 hour loop
		$chartArray = array();
		for ($i = 1; $i <= 24; $i++) {

		    $chartArray[$i]['time'] = date('h:i a', $iTimestamp) ;
		    $chartArray[$i]['timeStamp'] =  $iTimestamp ;

		    //null core values for modules
		    /*
			foreach ($dataArray as $value) {
				var_dump ($value[0]);

		    	//$chartArray[$i][$value[0][1]] = 0 ;
		    }
*/
		    //3600 is a hour, swap this for 1/2 hour periods to 1800;
		    $iTimestamp += 3600;
		}




		//loop through dataArray alert data and create time array

		//loop thtough each module that has a alert
		foreach ($dataArray as $value) {
		
			//get module name
			$module = $value[0][1];
			
			//all alerts for module by time
			foreach ($value as $items) {

				//get time of alert here
                $theAlertTime = strtotime (date("h:i a", $items[0]));

				for ($i = 1; $i <= 24; $i++) {

					if ($i == 1)
					{
						if ( $theAlertTime == strtotime($chartArray[$i]['time']) ) {

							if (isset($value[0][1]))
							{
								//if key doesnt exist we have to create it or we get a error
								if (!isset( $chartArray[$i]['module'][$module] ))
									$chartArray[$i]['module'][$module] = 0 ;
	
									$chartArray[$i]['module'][$module] += 1 ;
							}
							//break was in if isset above note!
							break;
						
						}
					}

					if ($i > 1)
					{
						if ( $theAlertTime >= strtotime($chartArray[$i]['time']) )
							continue;
						else {
							if (isset($value[0][1]))
							{
								//if key doesnt exist we have to create it or we get a error
								if (!isset( $chartArray[$i-1]['module'][$module] ))
									$chartArray[$i-1]['module'][$module] = 0;
									

								$chartArray[$i-1]['module'][$module] += 1 ;
							}
							//break was in if isset above note!
							break;
						}
					}  
				}
			}
		}


//var_dump ($chartArray);
//echo '</pre>';

		return $chartArray;
	
	}


} // end of class
?>
