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

class Mysql extends LoadAvg
{
	public $logfile; // Stores the logfile name & path

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


		//get database settings
		//need some error checking here ie  return if they are empty
		$mysqlserver =	$settings['settings']['mysqlserver'];
		$mysqluser =	$settings['settings']['mysqluser'];
		$mysqlpassword =	$settings['settings']['mysqlpassword'];

		//test database connection
		$connection = mysqli_connect($mysqlserver,$mysqluser,$mysqlpassword); 

		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			return false;
		} 

		$query1 = mysqli_query($connection, "SHOW GLOBAL STATUS LIKE 'Bytes_received'") ;

		$query2 = mysqli_query($connection, "SHOW GLOBAL STATUS LIKE 'Bytes_sent'") ;

		$query3 = mysqli_query($connection, "SHOW GLOBAL STATUS LIKE 'Queries'") ;


		//write the results
		$row = mysqli_fetch_array($query1);
			$bytesReceived = $row[1];

		$row = mysqli_fetch_array($query2);
			$bytesSent = $row[1];

		$row = mysqli_fetch_array($query3);
			$queries = $row[1];

		//free up querys and connections
		mysqli_free_result($query1);
		mysqli_free_result($query2);
		mysqli_free_result($query3);
		mysqli_close($connection);

		//for debugging	    
	    //echo 'DATA READ   : ' . $bytesReceived . '|' . $bytesSent . '|' . $queries . "\n";

	    //grab the logfile
		$logfile = sprintf($this->logfile, date('Y-m-d'));


		if ( $logfile && file_exists($logfile) )
			$elapsed = time() - filemtime($logfile);
		else
			$elapsed = 0;  //meaning new logfile

		//used to help calculate the difference as mysql charts is thruput not value based
		//this data is stored in _mysql_latest

		// grab net latest location and figure out elapsed
		$mysqllatestElapsed = 0;
		$mysqlLatestLocation = dirname($logfile) . DIRECTORY_SEPARATOR . '_mysql_latest';


		// basically if mysqllatestElapsed is within reasonable limits (logger interval + 20%) then its from the day
		// before rollover so we can use it to replace regular elapsed
		// which is 0 when there is anew log file
		$last = null;

		if (file_exists( $mysqlLatestLocation )) {
			
			$last = explode("|", file_get_contents(  $mysqlLatestLocation ) );

			if (  ( !isset($last[1]) || !$last[1]) ||  ($last[1] == null) || ($last[1] == "")   )
				$last[1]=0;

			if (  ( !isset($last[2]) || !$last[2]) ||  ($last[2] == null) || ($last[2] == "")   )
				$last[2]=0;

			$mysqllatestElapsed =  ( time() - filemtime($mysqlLatestLocation));

			//if its a new logfile check to see if there is previous netlatest data
			if ($elapsed == 0) {

				//data needs to within the logging period limits to be accurate
				$interval = $this->getLoggerInterval();

				if (!$interval)
					$interval = 360;
				else
					$interval = $interval * 1.2;

				if ( $mysqllatestElapsed <= $interval ) 
					$elapsed = $mysqllatestElapsed;
			}
		}

	    	//echo 'LAST STORED : ' . $last[0] . '|' . $last[1] . '|' . $last[2] . "\n";

			//if we were able to get last data from mysql latest above
			//figure out the difference as thats what we chart
			if (@$last && $elapsed) {

				$recv_diff = ($bytesReceived - $last[0]) ;
				if ($recv_diff < 0) $recv_diff = 0;

				$sent_diff = ($bytesSent - $last[1]) ;
				if ($sent_diff < 0) $sent_diff = 0;

				//$queries_diff = ($queries - $last[2]) - 4;  // we are the 4 queries! remove to be accurate really
				$queries_diff = ($queries - $last[2]) ;  
				if ($queries_diff < 0) $queries_diff = 0 ;

				$string = time() . "|" . $recv_diff . "|" . $sent_diff  . "|" . $queries_diff       . "\n";

	    		//echo 'DATA WRITE  : ' . $recv_diff . '|' . $sent_diff . '|' . $queries_diff . "\n";

			} else {
				//if this is the first value in the set and there is no previous data then its null
				
				$lastlogdata = "|0.0|0.0|0.0";

				$string = time() . $lastlogdata . "\n" ;

			}

		//write out log data here
		$this->safefilerewrite($logfile,$string,"a",true);

		// write out last transfare and received bytes to latest
		$last_string = $bytesReceived."|".$bytesSent."|".$queries;

		$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . "_mysql_latest";

		$this->safefilerewrite($fh,$last_string,"w",true);

		if ( $type == "api")
			return $string;
		else
			return true;

	}


	/**
	 * getData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getData( $logfileStatus, $useData = 1)
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$contents = null;

		$replaceDate = self::$current_date;


		//mode specific data is set up here
		//1 == Transmit
		//2 == Receive
		//3 == Queries

		$theLabel = "";
		switch ( $useData) {
			case 1: 	$theLabel = "Transmit";						
						break;

			case 2: 	$theLabel = "Receive";						
						break;

			case 3: 	$theLabel = "Queries";						
						break;
		}

		if ($logfileStatus == false ) {
		
			if ( LoadAvg::$period ) {
				$dates = self::getDates();
				foreach ( $dates as $date ) {
					if ( $date >= self::$period_minDate && $date <= self::$period_maxDate ) {
						$this->logfile = str_replace($replaceDate, $date, $this->logfile);
						$replaceDate = $date;
						if ( file_exists( $this->logfile ) )
							$contents .= file_get_contents($this->logfile);
					}
				}
			} else {
				$contents = file_get_contents($this->logfile);
			}

		} else {

			$contents = 0;
		}

		if ( strlen($contents) > 1 ) {
			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			$swap = array();
			$usageCount = array();
			$dataArray = $dataArrayOver = $dataArraySwap = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			$chartArray = array();

			$this->getChartData ($chartArray, $contents);

			$totalchartArray = (int)count($chartArray);

			//$displayMode =	$settings['settings']['display_limiting'];

			for ( $i = 0; $i < $totalchartArray; ++$i) {				
				$data = $chartArray[$i];

				//check for redline
				$redline = ($this->checkRedline($data));

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
					$data[1]=0.0;

				//when showing send and receive its bytes to MB
				//when showing queries, mode 3, its 1 to 1
				if ($useData == 3)
					$divisor = 1;
				else
					$divisor = 1024;

				//used to filter out redline data from usage data as it skews it
				if (!$redline) {
					$usage[] = ( $data[$useData] / $divisor );
				}

				$timedata = (int)$data[0];
				$time[( $data[$useData] / $divisor )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) 
					$timestamps[] = $data[0];

				// received
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[$useData] / $divisor ) ."]";

			}
			
			$mysql_high = max($usage);
			$mysql_low  = min($usage); 
			$mysql_mean = array_sum($usage) / count($usage);

			$ymax = $mysql_high;
			$ymin = $mysql_low;			

			$mysql_high_time = $time[max($usage)];
			$mysql_low_time = $time[min($usage)];
			$mysql_latest = ( ( $usage[count($usage)-1]  )  )    ;		


			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');
				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++ ) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
			}
		
			// values used to draw the legend
			$variables = array(
				'mysql_high' => number_format($mysql_high,0),
				'mysql_high_time' => $mysql_high_time,
				'mysql_low' => number_format($mysql_low,0),
				'mysql_low_time' => $mysql_low_time,
				'mysql_mean' => number_format($mysql_mean,0),
				'mysql_latest' => number_format($mysql_latest,0),
			);
		
			// get legend layout from ini file
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);

			// dataString is cleaned data used to draw the chart
			// dataOverString is if we are in overload

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mysql_mean,
				'avg' => "stack",
				'dataset_1' => $dataString,
				'dataset_1_label' => $theLabel,

				'dataset_2' => $dataOverString,
				'dataset_2_label' => 'Overload',

				'overload' => $settings['settings']['overload']
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
	
	public function getTransferData( $logfileStatus )
	{
		$returnStatus = $this->getData( $logfileStatus, 1 );
		
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
	
	public function getReceiveData( $logfileStatus)
	{
		$returnStatus = $this->getData( $logfileStatus, 2 );
		
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
	
	public function getQueryData( $logfileStatus)
	{
		$returnStatus = $this->getData( $logfileStatus, 3 );
		
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
	public function genChart($moduleSettings, $logdir)
	{

	//used for debugging
    //echo '<pre>';var_dump(self::$current_date);echo'</pre>';

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
