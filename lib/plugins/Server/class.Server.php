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

class Server extends LoadAvg
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
		self::$name = "Server";
		self::$icon = "fa-gears";

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
		$settings = LoadAvg::$_settings->$class;
		try {
			$results = array();
			exec($settings['cmd'][$cmd], $results, $res);
			$results = implode("<br />", $results);
			return $results;
		} catch (Exception $e) {

		}
	}

	/**
	 * getIcon
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
	 * dataSize
	 *
	 * A more readable way of viewing the returned float when polling disk size
	 *
	 * @param string $cmd command to execute for data
	 * @return array $results array of command execution results
	 *
	 */

	public function getTotalMemory(  )
	{
		try {
								$Bytes = disk_total_space($drive);
								$totalBytes = dataSize($Bytes);
								return $totalBytes;

		} catch (Exception $e) {

		}
	}

	public function getTotalStorage( $drive, $formatted = false )
	{
		try {
			if (is_dir($drive)) {

								$Bytes = disk_total_space($drive);

								if ($formatted)
									$totalBytes = $this->dataSize($Bytes);
								else
									$totalBytes = $Bytes;

								return $totalBytes;								
			}
		} catch (Exception $e) {

		}
	}

	public function getFreeStorage( $drive, $formatted = false )
	{
		try {
			if (is_dir($drive)) {

								$total = disk_total_space($drive);
								$free = disk_free_space($drive);

								$percentFreeBytes =  $free ? round($free / $total, 2) * 100 : 0;

								if ($formatted)
									$freeBytes = $this->dataSize($free);
								else
									$freeBytes = $free;

								return array( $freeBytes, $percentFreeBytes);
			}
		} catch (Exception $e) {

		}
	}

	public function getUsedStorage( $drive, $formatted = false )
	{
		try {
			if (is_dir($drive)) {

								$total = disk_total_space($drive);
								$free = disk_free_space($drive);
								$used = ($total - $free);

								$percentUsedBytes =  $used ? round($used / $total, 2) * 100 : 0;

								if ($formatted)
									$usedBytes = $this->dataSize($used);
								else
									$usedBytes = $used;

								return array(  $usedBytes, $percentUsedBytes );

			}
		} catch (Exception $e) {

		}
	}

	/*
	* move into plugin
	*/

	public function getPartitionData( )
	{
		try {

		   $df = array();

		   //careful as we are rounding down
		    exec("df -T -x tmpfs -x devtmpfs -P -B 1G",$df);
		    array_shift($df);
		 
		    $Stats = array();

		    foreach($df as $disks) {
		        $split = preg_split('/\s+/', $disks);
		        $Stats[] = array(
		                    'disk'      => $split[0],
		                    'mount'     => $split[6],
		                    'type'      => $split[1],
		                    'mb_total'  => $split[2],
		                    'mb_used'   => $split[3],
		                    'mb_free'   => $split[4],
		                    'percent'   => $split[5],
		                );
		    }

		    return $Stats;

		} catch (Exception $e) {

		}
	}

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
