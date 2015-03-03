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

class Process extends LoadPlugins
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
		self::$name = "Process";
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
	 * fetchData
	 *
	 * Retrives data and returns it to caller
	 *
	 * @param string $ps_args args to execute for data
	 * @return array $results array of command execution results
	 *
	 */

    public function fetchProcessData($ps_args = '')
    {
        if (empty($ps_args)) {
            $ps_args = $this->_args;
        }
        putenv('COLUMNS=1000');

        return shell_exec("ps $ps_args");
    }




	/**
	 * parseProcessData
	 *
	 * process process data and returns it to caller
	 *
	 * @param string $ps_args args to execute for data
	 * @return array $results array of command execution results
	 *
	 */

    public function parseProcessData(&$lines)
    { 

	//ps -Ao %cpu,%mem,user,time,comm,args | sort -r -k1 | head -n 30

	//code form here
	//https://github.com/pear/System_ProcWatch/blob/master/System/ProcWatch/Parser.php

	//provblems with part of data - command1
	//we truncate all data passed to command for some reason when parsing


	    $heads = preg_split('/\s+/', strToLower(trim(array_shift($lines))));
	    
	    $count = count($heads) + 1;
	    //should be fixed to $count = 6;

	    $procs = array();

	    //error here!!!
	    if ($heads[5] != "command") {

		    echo '<pre>';

		    echo 'lines0:' . $lines[0] . "\n";
		    echo 'lines1:' . $lines[1] . "\n";
		    echo "\n";

	    	var_dump ($heads);
			echo '</pre>';

	    }

	    //fix for dual COMMAND columns in ps output makes sorting impossible
	    $heads[5] = $heads[5] . '0';


		//see debug in public function arraySort($input,$sortkey){

	    foreach($lines as $i => $line){

			//also bug when sorting with command0 check it out
	    	//if ($heads[5] != "command0")
	        //echo 'line :' . $line . "\n";

	        $parts = preg_split('/\s+/', trim($line), $count);
	    
	    	//deal with dual command title headings here in row 0
	    	//when creating keys for array
	        foreach ($heads as $j => $head) {

	            $procs[$i][$head] = str_replace('"', '\"', $parts[$j]);

	        }
	    }

	    return $procs;

}



	/**
	 * fetchProcessLogData
	 *
	 * read process data from log file and returns it to caller
	 *
	 * @param string $timestamp timestamp of log file we are looking for
	 * @return string $return the log file data for the plugin
	 *
	 */

    public function fetchProcessLogData($timestamp)
    { 

    	//$date = $date_range;

		$thismodule = __CLASS__;
		$moduleSettings = $this::$_settings->$thismodule;

		// Check if loaded module needs loggable capabilities
		if ( $moduleSettings['module']['logable'] == "false" ) 
			return false;
		//echo '<pre>'; var_dump ($moduleSettings); echo '</pre>';


		//grab the first args line only [0]
		$args = $moduleSettings['logging']['args'][0];
		$args = json_decode($args);

        //get log location and filename using timestamp
        //TODO this is using todays date - we need to use $date_range !!!
		$logdirname = sprintf($args->logdir, date('Y-m-d'));

		$filename = sprintf($args->logfile, $logdirname, $timestamp);
		$logfile = LOG_PATH . $filename;


		//grab data from file
		$result=@file_get_contents($logfile); 
		if ($result === false) 
		{ 
			//echo "MISSING LOG FILE : " . $logfile . "<br>";
		    return false; 
		} else { 
			//echo "GOT LOG FILE : " . $logfile . "<br>";	
		    return $result;
		} 


	}








    //sory dimensional array by key
    //http://stackoverflow.com/questions/2189626/group-a-multidimensional-array-by-a-particular-value
    
	public function arraySort($input,$sortkey){

	/*
	echo '<pre>';
	var_dump ($sortkey);
	var_dump ($input[0]);
	echo '</pre>';
	*/

	  foreach ($input as $key=>$val) 
	  	$output[$val[$sortkey]][]=$val;
	  
	  return $output;
	}




	/**
	 * dataSize
	 *
	 * A more readable way of viewing the returned float when polling disk size
	 *
	 * @param string $cmd command to execute for data
	 * @return array $results array of command execution results
	 *
	 */

	


	public function dataSize( $Bytes )
	{
		$Type=array("", "KB", "MB", "GB", "TB");
		$counter=0;
		while($Bytes>=1024) {
							$Bytes/=1024;
							$counter++;
		}

		return("". number_format($Bytes,2) ." ".$Type[$counter]);


	}


}

?>
