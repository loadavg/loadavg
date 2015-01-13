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

class Ssh extends Logger
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

	//need to save timestamp with offset
	//so we can add support for journalctl 

	//journalctl _COMM=sshd --since "10:00" --until "11:00"

	//journalctl _COMM=sshd --since "previous" --until "current"

	//nice!!

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = Logger::$_settings->$class;

		$sshdLogFile ['path'] =	$settings['settings']['log_location'];

        //log data variables
        $logData['invalid_user'] = 0;
        $logData['failed_pass'] = 0;
        $logData['accepted'] = 0;

	    //grab the logfile
		$logfile = sprintf($this->logfile, date('Y-m-d'));

		//check if log file exists and see time difference
		//stored in elapsed
		if ( $logfile && file_exists($logfile) )
			$elapsed = time() - filemtime($logfile);
		else
			$elapsed = 0;  //meaning new logfile

		//we need to read offset here
		//grab net latest location and figure out elapsed
		//zero out offset
        $sshdLogFile ['offset'] = 0;
        $sshdLogFile ['timestamp'] = 0;

		$sshlatestElapsed = 0;
		$sshLatestLocation = dirname($logfile) . DIRECTORY_SEPARATOR . '_ssh_latest';


		// basically if sshlatestElapsed is within reasonable limits (logger interval + 20%) 
		// then its from the day before rollover so we can use it to replace regular elapsed
		// which is 0 when there is a new log file

		if (file_exists( $sshLatestLocation )) {
			
			//if we want to add more data to return string we can use eplode below
			$last = explode("|", file_get_contents(  $sshLatestLocation ) );

			$sshdLogFile['offset'] = file_get_contents(  $sshLatestLocation );
			//$sshdLogFile['timestamp'] = file_get_contents(  $sshLatestLocation );

	    	//echo 'STORED OFFSET  : ' . $sshdLogFile['offset']   . "\n";

			$sshlatestElapsed =  ( time() - filemtime($sshLatestLocation));

			//if its a new logfile check to see if whats up with the interval
			if ($elapsed == 0) {

				//data needs to within the logging period limits to be accurate
				$interval = $this->getLoggerInterval();

				if (!$interval)
					$interval = 360;
				else
					$interval = $interval * 1.2;

				if ( $sshlatestElapsed <= $interval ) 
					$elapsed = $sshlatestElapsed;
			}
		}

        // Reset offset if file size has reduced (truncated)
        // means logs have been rotated!
        // TODO :
        // if logs have been rotated we need to look for data in old log file
        // and add to new log file
        // however need to read a .gz to do this as old logs are compressed and soted by date
        // ie secure-20140427.gz
        $fileSize = filesize($sshdLogFile['path']);

        if($fileSize < $sshdLogFile['offset']){
            $sshdLogFile['offset'] = 0;
        }

        //read log file and get log data
        if ( !$this->loadLogData( $sshdLogFile, $logData ) )
        	return false;

		//if we were able to get last data from mysql latest above
		//figure out the difference as thats what we chart
		if (@$sshdLogFile['offset'] && $elapsed) {

			if ($logData['accepted'] < 0) $logData['accepted'] = 0;

			if ($logData['failed_pass'] < 0) $logData['failed_pass'] = 0;

			if ($logData['invalid_user'] < 0) $logData['invalid_user'] = 0 ;

			$string = time() . "|" . $logData['accepted'] . "|" . $logData['failed_pass']  . "|" . $logData['invalid_user']       . "\n";

    		//echo 'DATA WRITE  : ' . $logData['accepted'] . '|' . $logData['failed_pass'] . '|' . $logData['invalid_user'] . "\n";

		} else {
			//if this is the first value in the set and there is no previous data then its null
			
			$lastlogdata = "|0|0|0";

			$string = time() . $lastlogdata . "\n" ;

		}

		//write out log data here
		$this->safefilerewrite($logfile,$string,"a",true);

		// write out filesize so we can pick up where we left off next time around
		$this->safefilerewrite($sshLatestLocation,$fileSize,"w",true);

		if ( $type == "api")
			return $string;
		else
			return true;

	}


	/**
	 * loadLogData
	 *
	 * Loads ssh log data from logfile, formats and parses it to pass it back
	 *
	 * @sshdLogFile log file location and settings
	 * @logData string that contains return data
	 * @return flag for success or fail
	 *
	 */

	function loadLogData( array &$sshdLogFile,  array&$logData )
	{

        // Open log file for reading
        $f = @fopen($sshdLogFile['path'],"r");
        if($f) {
            // Seek to last position we know
            fseek($f, $sshdLogFile['offset']);

            // Read new lines until end of file
            while(!feof($f)) {
                // Read line
                $line = @fgets($f,4096);

                if($line !== false) {

                    $line = trim($line);

                    // We check only lines with "sshd"
                    if(preg_match("/sshd/", $line)) {

                    	//failed passwords
                        if(preg_match("/Failed password/", $line)) 
                            $logData['failed_pass'] += 1;

                        //invalid users
                        if(preg_match("/Invalid user/", $line)) 
                            $logData['invalid_user'] += 1;

                        if(preg_match("/ROOT LOGIN REFUSED/", $line)) 
                            $logData['invalid_user'] += 1;

                        //accepted password issues
                        if(preg_match("/Accepted password/", $line)) 
                            $logData['accepted'] += 1;

                        if(preg_match("/Accepted publickey/", $line)) 
                            $logData['accepted'] += 1;

                        
                    }
                }
                // Sleep for 1 microsecond (so that we don't take all CPU resources 
                // and leave small part for other processes in case we need to parse a lot of data
                usleep(1);
            } 

            // Get current offset
            $currentOffset = ftell($f);

            //update the offest here
            if($sshdLogFile['offset'] != $currentOffset)
                $sshdLogFile['offset'] = $currentOffset;
          
            // Close file
            @fclose($f);

        } else { 

        	//cant open logfile - clean up and return
            @fclose($f);
            return false;
        }

        /*
        echo "\n";
        echo "------------------------------------- \n";
        echo 'INVALID USER:' . $logData['invalid_user'] . "\n" ;
        echo 'FAILED PASS :' . $logData['failed_pass'] . "\n" ;
        echo 'ACCEPTED    :' . $logData['accepted'] . "\n" ;
        echo 'OFFSET      :' . $sshdLogFile['offset'] . "\n" ;
        echo "------------------------------------- \n";
        echo "\n";
		*/

        return true;

	}




}

