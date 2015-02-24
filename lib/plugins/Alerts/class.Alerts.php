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



}

?>
