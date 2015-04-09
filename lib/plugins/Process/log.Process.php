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




class Process extends Logger
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
	 * logDiskUsageData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *	 *
	 */

	public function logData( $type = false )
	{

		$class = __CLASS__;
		$settings = Logger::$_settings->$class;

		$timestamp = time();

		//get process data here	
		$ps_args = '-Ao %cpu,%mem,pid,user,comm,args';

        putenv('COLUMNS=1000');
        $string = shell_exec("ps $ps_args");

		//$string = explode("\n", trim ($processData));

        //get log location
		$logdirname = sprintf($this->logdir, date('Y-m-d'));
		$logpath = LOG_PATH . $logdirname;

		//echo "LOG_PATH : " . $logpath . " \n";

		$filename = sprintf($this->logfile, $logdirname, $timestamp);
		//echo "FILENAME : " . $filename . " \n";


		//great log folder if it doesnt exist
		if (!file_exists($logpath)) {
		    mkdir( $logpath, 0777 );
		}

		//read me on encoding array to disk instead
		//https://www.safaribooksonline.com/library/view/php-cookbook/1565926811/ch05s08.html

		//write out process data to file
		LoadUtility::safefilerewrite($filename,$string,"w",true);

		if ( $type == "api")
			return $string;
		else
			return true;		
	}




}

//compress files


//file_put_contents ($filename, $data)

//file_put_contents ("compress.zlib:///myphp/test.txt.gz", $data)

//file_get_contents ("compress.zlib:///myphp/test.txt.gz" 

/*
or

gzopen(filename, mode))
gzwrite
gzclose


and

$lines = gzfile ($filename);

	foreach ($lines as $line)
		echo line;
*/