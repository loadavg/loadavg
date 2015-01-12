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

class Mysql extends Logger
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




	
}
