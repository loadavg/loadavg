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





class Processor extends Logger
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
		$settings = Logger::$_settings->$class;

		$timestamp = time();

		$core_nums = trim(exec("grep -P '^processor' /proc/cpuinfo|wc -l"));

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
		LoadUtility::safefilerewrite($logfile,$string,"a",true);

		// write out last transfare and received bytes to latest
		$last_string = $procStats[0]."|".$procStats[1]."|".$procStats[2]."|".$procStats[3];

		$fh = dirname($this->logfile) . DIRECTORY_SEPARATOR . $separator;

		LoadUtility::safefilerewrite($fh,$last_string,"w",true);

		if ( $type == "api")
			return $string;
		else
			return true;	


	}


}
