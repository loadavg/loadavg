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

class Mysql extends Charts
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
	 * @return the label for the mode 
	 *
	 */
	
	public function getChartLabel( $switch  )
	{

		//mode specific data is set up here
		//1 == Transmit
		//2 == Receive
		//3 == Queries

		$theLabel = "";
		switch ( $switch) {
			case 1: 	$theLabel = "Transmit";						
						break;

			case 2: 	$theLabel = "Receive";						
						break;

			case 3: 	$theLabel = "Queries";						
						break;
		}

		return $theLabel;

	}


	/**
	 * getData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getData( $switch = 1)
	{

		$class = __CLASS__;
		$settings = loadModules::$_settings->$class;

		//define some core variables here
		$dataArray = $dataRedline = $usage = array();
		$dataArraySwap = array();

		//display switch used to switch between view modes - data or percentage
		//$displayMode =	$settings['settings']['display_limiting'];	

		//mode specific data is set up here
		$dataArrayLabel[0] = $this->getChartLabel($switch);

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

				//check for redline
				$redline = ($this->checkRedline($data));

				//when showing send and receive its bytes to MB
				//when showing queries, mode 3, its 1 to 1
				if ($switch == 3)
					$divisor = 1;
				else
					$divisor = 1024;

				//used to filter out redline data from usage data as it skews it
				if (!$redline) {
					$usage[] = ( $data[$switch] / $divisor );
				}

				$timedata = (int)$data[0];
				$time[( $data[$switch] / $divisor )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				// received
				$dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". ( $data[$switch] / $divisor ) ."]";

			}

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

			$mysql_high = max($usage);
			$mysql_low  = min($usage); 
			$mysql_mean = array_sum($usage) / count($usage);

			$ymax = $mysql_high;
			$ymin = $mysql_low;			

			$mysql_high_time = $time[max($usage)];
			$mysql_low_time = $time[min($usage)];
			$mysql_latest = ( ( $usage[count($usage)-1]  )  )    ;		

		
			// values used to draw the legend
			$variables = array(
				'mysql_high' => number_format($mysql_high,0),
				'mysql_high_time' => $mysql_high_time,
				'mysql_low' => number_format($mysql_low,0),
				'mysql_low_time' => $mysql_low_time,
				'mysql_mean' => number_format($mysql_mean,0),
				'mysql_latest' => number_format($mysql_latest,0),
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
			$depth=1; //number of datasets
			$this->buildChartDataset($dataArray,$depth);

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mysql_mean,
				'avg' => "stack",

				'dataset_1' 	  => $dataArray[0],
				'dataset_1_label' => $dataArrayLabel[0]

				//'overload' => $settings['settings']['overload']
			);

			return $return;	
		} else {

			return false;	
		}
	}


	/**
	 * getTransferData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getTransferData(  )
	{
		$returnStatus = $this->getData(  1 );
		
		return $returnStatus;	
	}


	/**
	 * getTransferData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getReceiveData( )
	{
		$returnStatus = $this->getData(  2 );
		
		return $returnStatus;			
	}

	/**
	 * getTransferData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getQueryData( )
	{
		$returnStatus = $this->getData( 3 );
		
		return $returnStatus;			
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
	public function genChart($module, $drawAvg = true )
	{

	//used for debugging
    //echo '<pre>';var_dump(self::$current_date);echo'</pre>';
        $moduleSettings = LoadModules::$_settings->$module; 

		$charts = $moduleSettings['chart'];

		$module = __CLASS__;
		$i = 0;

		if ( file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'chart.php')) {
			include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'chart.php';
		} else {
			include APP_PATH . '/lib/views/chart.php';
		}		
	}
}
