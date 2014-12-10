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





class Processor extends LoadAvg
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
	 * logData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *
	 */

	/*
	 * format to be number procs, then proc/stat for each processor
	 */

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$timestamp = time();

		$core_nums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
		//echo 'PROCS: ' . $core_nums . "\n";

		$procStats = array();

		//get the processor stats for primary cpu
		if (!$this->getProcStats($procStats,0))
			return false; 

		//we just need the first 4 values of procStats to track cpu usage
		//$totalUsed = $procStats[0] + $procStats[1] + $procStats[2] + $procStats[3];

		////////////////////////////////////////////////////////////////
		//now start the logging
	    
	    //grab the logfile
		$logfile = sprintf($this->logfile, date('Y-m-d'));
		$separator = "_proc_latest";


		if ( $logfile && file_exists($logfile) )
			$elapsed = time() - filemtime($logfile);
		else
			$elapsed = 0;  //meaning new logfile

		//used to help calculate the difference as proc chart data is thruput not value based
		//this data is stored in _proc_latest

		// grab net latest location and figure out elapsed
		$mysqllatestElapsed = 0;
		$mysqlLatestLocation = dirname($logfile) . DIRECTORY_SEPARATOR . $separator;


		// basically if mysqllatestElapsed is within reasonable limits (logger interval + 20%) then its from the day
		// before rollover so we can use it to replace regular elapsed
		// which is 0 when there is anew log file
		$last = null;

		if (file_exists( $mysqlLatestLocation )) {
			
			$last = explode("|", file_get_contents(  $mysqlLatestLocation ) );

			if (  ( !isset($last[1]) || !$last[1]) ||  ($last[1] == null) || ($last[1] == "")   )
				$last[1] = $last[2] = $last[3] = $last[4] = 0;

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

	    	//echo 'LAST STORED : ' . $last[0] . '|' . $last[1] . '|' . $last[2] . '|' . $last[3] .  "\n";

			//figure out the difference as thats what we chart
			if (@$last && $elapsed) {

				$dif = array(); 

				$dif['user']  = $procStats[0] - $last[0]; 
				$dif['nice']  = $procStats[1] - $last[1]; 
				$dif['sys']   = $procStats[2] - $last[2]; 
				$dif['idle']  = $procStats[3] - $last[3]; 

				//store other usage as well now
				//or calculate in charts?

				$total = array_sum($dif); 

				$cpu = array(); 

				foreach($dif as $x=>$y) {

					$cpu[$x] = round($y / $total * 100, 2);

					if($cpu[$x]<0) 
						$cpu[$x] = 0;
				}

				//var_dump ($cpu);

				//vlaculate other usage and store this data
				//this is processess not accounted for in the idle variable
				$cpu['other'] =  round( (100 - ( $cpu['user'] + $cpu['nice'] + $cpu['sys'] + $cpu['idle'] )),2);

				if($cpu['other']<0) 
					$cpu['other'] = 0;

				$string = time() . "|" . $cpu['user'] . "|" . $cpu['nice']  . "|" . $cpu['sys']  . "|" . $cpu['idle'] . "|" . $cpu['other'] . "\n";

				//echo 'STRING:' . $string;

			} else {
				//if this is the first value in the set and there is no previous data then its null
				
				$lastlogdata = "|0.0|0.0|0.0|0.0|0.0";

				$string = time() . $lastlogdata . "\n" ;

			}


			//echo 'STRING:' . $string;

			//get out other usage as idle - (user+cpu+nice)
			//$otherUsage =  100 - ($cpu['idle'] + $cpu['user'] + $cpu['nice'] + $cpu['sys']); 

			//$testTotal = $cpu['user'] + $cpu['nice'] + $cpu['sys']  + $cpu['idle'] + $otherUsage;

			//echo 'TOTAL:' . $testTotal . "\n" ;

		//write out log data here
		$this->safefilerewrite($logfile,$string,"a",true);

		// write out last transfare and received bytes to latest
		$last_string = $procStats[0]."|".$procStats[1]."|".$procStats[2]."|".$procStats[3];

		$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . $separator;

		$this->safefilerewrite($fh,$last_string,"w",true);

		if ( $type == "api")
			return $string;
		else
			return true;	


	}

/*
	//this was added to main loadavg class

	public function getProcStats (array &$data, $theLine = 0) 
	{

        //we grab data from proc/stat in one pass as it changes as you read it
  		$stats = file('/proc/stat'); 

  		//if array is emoty we didnt work
		if($stats === array())
        	return false;

        //echo 'STATS:' . $stats[1];

        //grab cpu data
		$data = explode(" ", preg_replace("!cpu +!", "", $stats[$theLine])); 

       return true; 

	}
*/

	/**
	 * getData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @param string $switch with switch data to populate return array
	 * @return array $return data retrived from logfile
	 *
	 */

	//switch needs to pull chart data for selected processor
	//with default being summary data

	public function getUsageData( $logfileStatus, $switch ) 
	{

		//hard coded for testing to fir data value - 
		//$switch = 1;

		//chart should be 1+2+3+(4-1+2+3)
		/*
		$switch['user'] = 1
		$switch['nice'] = 2
		$switch['sys']  = 3
		$switch['idle'] = 4
		$switch['other'] = 5
		*/

		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$contents = null;
	
		$replaceDate = self::$current_date;
		
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


		// is this really faster than strlen ?
		if (isset($contents{1})) {
		//if ( strlen($contents) > 1 ) {

			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			$dataArray = $dataArrayOver = $dataArrayOver_2 = $dataRedline = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				//echo "24 hour";
				$timestamps = array();
			}
			/*
			 * build the chartArray array here and patch to check for downtime
			 */

			$chartArray = array();
			$this->getChartData ($chartArray, $contents);

			$totalchartArray = (int)count($chartArray);

			//used to limit display data from being sqewed by overloads
			$displayMode =	$settings['settings']['display_limiting'];


			//for ( $i = 0; $i < $totalchartArray; $i++) {	
			for ( $i = 0; $i < $totalchartArray; ++$i) {	

				$data = $chartArray[$i];
				
				// clean data for missing values
				$redline = ($this->checkRedline($data));

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
					$data[1]=0.0;

				//used to filter out redline data from usage data as it skews it
				//for us this means add 3 arrays togeather
				if (!$redline) {
					//switch = 2/3/4 if just showing one dataset
					$usage[$switch][] = $data[$switch];

					//switch= 1 / showing combination chart so usage is sum all usage!
					//$usage[$switch][] = $data[$switch];
					//$usage[$switch][] = $data[$switch];
				}




				$timedata = (int)$data[0];

				$time[$switch][$data[$switch]] = date("H:ia", $timedata);

				//for 24 hou charts
				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) 
					$timestamps[] = $data[0];



				//we have 3 datasets to plot
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", '". $data[1] ."']";
		
				$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", '". $data[2] ."']";
		
				$dataArrayOver_2[$data[0]] = "[". ($data[0]*1000) .", '". $data[3] ."']";

			}


			$processor_high = max($usage[$switch]);
			$processor_high_time = $time[$switch][$processor_high];

			$processor_low = min($usage[$switch]);
			$processor_low_time = $time[$switch][$processor_low];
		
			//$cpu_mean = (float)number_format(array_sum($usage[$switch]) / count($usage[$switch]), 3);
			$processor_mean = array_sum($usage[$switch]) / count($usage[$switch]) ;
			
			$processor_latest = $usage[$switch][count($usage[$switch])-1];

			if ($displayMode == 'true' )
			{
				$ymin = 0;
				$ymax = 100;
			} else {
				$ymin = $processor_low;
				$ymax = $processor_high;
			}

			/////////////////////////////////////////////////////////////
			//what exactly does this do ?
			//disabling it does nothing 

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				/*
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');

				//echo 'endtimne: ' . $endTime;

				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
				*/
			}

			$variables = array(
    	        'processor_high' => number_format($processor_high,3),
                'processor_high_time' => $processor_high_time,
                'processor_low' => number_format($processor_low,3),
                'processor_low_time' => $processor_low_time,
    	        'processor_mean' => number_format($processor_mean,3),
                'processor_latest' => number_format($processor_latest,3)
            );


			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if ( count($dataArrayOver) == 0 ) $dataArrayOver = null;
			if ( count($dataArrayOver_2) == 0 ) $dataArrayOver_2 = null;

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			if (!is_null($dataArrayOver_2)) ksort($dataArrayOver_2);


			$dataString[0] = "[" . implode(",", $dataArray) . "]";
			$dataString[1] = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			$dataString[2] = is_null($dataArrayOver_2) ? null : "[" . implode(",", $dataArrayOver_2) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'chart_avg' => 'avg',

				'ymin' => $ymin,
				'ymax' => $ymax,
				'mean' => $processor_mean,
				
				'dataset_1' => $dataString[0],
				'dataset_1_label' => 'User',

				'dataset_2' => $dataString[1],
				'dataset_2_label' => 'Nice',
				
				'dataset_3' => $dataString[2],
				'dataset_3_label' => 'System'
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
