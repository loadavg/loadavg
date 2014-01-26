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
}
