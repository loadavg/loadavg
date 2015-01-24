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
	 * getData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $cmd command to execute for data
	 * @return array $results array of command execution results
	 *
	 */

	public function getData( $cmd )
	{
		$class = __CLASS__;
		$settings = LoadPlugins::$_settings->$class;
		try {
			$results = array();
			exec($settings['cmd'][$cmd], $results, $res);
			$results = implode("<br />", $results);
			return $results;
		} catch (Exception $e) {

		}
	}

	/**
	 * fetchData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $cmd command to execute for data
	 * @return array $results array of command execution results
	 *
	 */

    public function fetchData($ps_args = '')
    {
        if (empty($ps_args)) {
            $ps_args = $this->_args;
        }
        putenv('COLUMNS=1000');
        return shell_exec("ps $ps_args");
    }

    //sory dimensional array by key
    //http://stackoverflow.com/questions/2189626/group-a-multidimensional-array-by-a-particular-value
    
	public function arraySort($input,$sortkey){

	var_dump ($sortkey);
	var_dump ($input[0]);

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
