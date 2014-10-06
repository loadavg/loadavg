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
	/**
	 * __construct
	 *
	 * Class constructor, appends Module settings to default settings
	 *
	 */
	public function __construct()
	{
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini', true));
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

	public function getTotalMemory(  )
	{
		try {
								$Bytes = disk_total_space($drive);
								$totalBytes = dataSize($Bytes);
								return $totalBytes;

		} catch (Exception $e) {

		}
	}

	public function getTotalStorage( $drive )
	{
		try {
			if (is_dir($drive)) {

								$Bytes = disk_total_space($drive);
								$totalBytes = dataSize($Bytes);
								return $totalBytes;

			}
		} catch (Exception $e) {

		}
	}



//$percentBytes = $freeBytes ? round($freeBytes / $totalBytes, 2) * 100 : 0;

	public function getFreeStorage( $drive )
	{
		try {
			if (is_dir($drive)) {

								$total = disk_total_space($drive);
								$free = disk_free_space($drive);

								$freeBytes = dataSize($free);
								$percentFreeBytes =  $free ? round($free / $total, 2) * 100 : 0;
								
								return array($freeBytes, $percentFreeBytes);
			}
		} catch (Exception $e) {

		}
	}

	public function getUsedStorage( $drive )
	{
		try {
			if (is_dir($drive)) {
								$total = disk_total_space($drive);
								$free = disk_free_space($drive);

								$used = ($total - $free);
								$usedBytes = dataSize($used);
								
								$percentUsedBytes =  $used ? round($used / $total, 2) * 100 : 0;

								return array($usedBytes, $percentUsedBytes);

			}
		} catch (Exception $e) {

		}
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
			
		$Type=array("", "kilo", "mega", "giga", "tera");
		$counter=0;
		while($Bytes>=1024) {
							$Bytes/=1024;
							$counter++;
		}

		return("".$Bytes." ".$Type[$counter]."bytes");
		
	}

}

?>
