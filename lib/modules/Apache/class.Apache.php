<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Apache Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Apache extends LoadAvg
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
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini', true));
	}

	/**
	 * logApacheUsageData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *
	 */

	public function logApacheUsageData( $type = false )
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;


		$url = $settings['settings']['serverstatus'];
		//$url = "http://localhost/server-status";

		$parseUrl = $url . "/?auto";

		$locate = "CPULoad";

		$dataValue = $this->getApacheDataValue($parseUrl, $locate);

		if ($dataValue == null)
			$dataValue = 0;

	    $string = time() . '|' . $dataValue . "\n";

		if ( $type == "api" ) {
			return $string;
		} else {
        	$fh = fopen(sprintf($this->logfile, date('Y-m-d')), "a");
	        fwrite($fh, $string);
			fclose($fh); 
		}
	}


	/**
	 * getApacheDataValue
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $dataValue data retrived from mod_status
	 *
	 */

	public function getApacheDataValue($parseurl, $locate) 
	{


		$f = implode(file($parseurl."?dat=".time()),"");

		$active = explode("\n", $f );

		$dataValue = false;

		foreach ($active as $i => $value) {

			$pieces = explode(": ", $active[$i]);

			if ($pieces[0]==$locate) {
				$dataValue = $pieces[1];
			}

		}

		return($dataValue);
    }
    



	/**
	 * getApacheUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getApacheUsageData()
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		$contents = null;

		$replaceDate = self::$current_date;
		
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

		if ( strlen($contents) > 1 ) {

			$contents = explode("\n", $contents);
			$return = $usage = $args = array();

			//$swap = array();
			$usageCount = array();
			$dataArray = $dataArrayOver = array();

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps = array();

			for ( $i = 0; $i < count( $contents )-1; $i++) {
			
				$data = explode("|", $contents[$i]);

				// clean data for missing values
				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "") )
					$data[1]=0;

				$time[( $data[1]  )] = date("H:ia", $data[0]);
				$usage[] = ( $data[1]  );
			
				$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] ) ."]";
			
				//if ( isset($data[2]) )
				//	$dataArraySwap[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[2] / 1024 ) ."]";

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) $timestamps[] = $data[0];
			

				if ( (float) $data[1] > $settings['settings']['overload'])
					$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1]  ) ."]";
				
			}

			$mem_high= max($usage);
			$mem_high_time = $time[$mem_high];

			$mem_low = min($usage);
			$mem_low_time = $time[$mem_low];
		
			$mem_mean = array_sum($usage) / count($usage);
			$mem_latest = $usage[count($usage)-1];

			$ymin = $mem_low;
			$ymax = $mem_high;

			if ( LoadAvg::$_settings->general['chart_type'] == "24" ) {
				end($timestamps);
				$key = key($timestamps);
				$endTime = strtotime(LoadAvg::$current_date . ' 24:00:00');
				$lastTimeString = $timestamps[$key];
				$difference = ( $endTime - $lastTimeString );
				$loops = ( $difference / 300 );

				for ( $appendTime = 0; $appendTime <= $loops; $appendTime++ ) {
					$lastTimeString = $lastTimeString + 300;
					$dataArray[$lastTimeString] = "[". ($lastTimeString*1000) .", 0]";
				}
			}



			$variables = array(
				'mem_high' => number_format($mem_high,4),
				'mem_high_time' => $mem_high_time,
				'mem_low' => number_format($mem_low,4),
				'mem_low_time' => $mem_low_time,
				'mem_mean' => number_format($mem_mean,4),
				'mem_latest' => number_format($mem_latest,4),
			);



			//DEBUG HERE
			//print_r ($variables);
		
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);
			if (!is_null($dataArraySwap)) ksort($dataArraySwap);


			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";
			//print_r ($usageCount);

			$return['chart'] = array(
				'chart_format' => 'line',
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $mem_mean,
				'chart_data' => $dataString,
				'chart_data_over' => $dataOverString,
				//'swap_count' => $usageCount,
				'overload' => $settings['settings']['overload']
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
		$charts = $moduleSettings['chart'];
		$module = __CLASS__;
		$i = 0;
		foreach ( $charts['args'] as $chart ) {
			$chart = json_decode($chart);

			//grab the log file and date
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);
			
			if ( file_exists( $this->logfile )) {
				$i++;				

				$caller = $chart->function;
				$stuff = $this->$caller( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );
				
				include APP_PATH . '/views/chart.php';
			}
		}
	}
}
