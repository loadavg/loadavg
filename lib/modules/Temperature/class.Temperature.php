<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Temperature Module for LoadAvg
*
* @link https://github.com/loadavg/loadavg
* @author Knut Kohl
* @copyright 2016 Knut Kohl <github@knutkohl.de>
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/




class Temperature extends Charts
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
     * getTemperatureData
     *
     * Gets data from logfile, formats and parses it to pass it to the chart generating function
     *
     * @return array $return data retrived from logfile
     *
     */
    
    public function getTemperatureData(  )
    {
        $class = __CLASS__;
        $settings = LoadModules::$_settings->$class;

        //define some core variables here
        $dataArray = $dataArrayLabel = array();
        $dataRedline = $temperature = array();

        //display switch used to switch between view modes - data or percentage
        // true - show MB
        // false - show percentage
        $displayMode =    $settings['settings']['display_limiting'];    

        //define datasets
        $dataArrayLabel[0] = 'Temperature';

        /*
         * grab the log file data needed for the charts as array of strings
         * takes logfiles(s) and gives us back contents
         */        

        $contents = array();
        $logStatus = LoadUtility::parseLogFileData($this->logfile, $contents);

        /*
         * build the chartArray array here as array of arrays needed for charting
         * takes in contents and gives us back chartArray
         */

        $chartArray = array();

        //takes the log file and parses it into chartable data 
        if ($logStatus) {
            $this->getChartData ($chartArray, $contents,  false );
        }

        /*
         * now we loop through the dataset and build the chart
         * uses chartArray which contains the dataset to be charted
         */
        if (count($chartArray)) {

            // main loop to build the chart data
            foreach ($chartArray as $data) {

                if ($data == null) continue;
                
                $temperature[] = $data[1];

                $timedata = (int)$data[0];
                $time[$data[1]] = date("H:ia", $timedata);

                $temperatureCount[] = ($data[0]*1000);

                $dataArray[0][$data[0]] = "[". ($data[0]*1000) .", ". $data[1] ."]";
            }

//             echo '<pre>PRESETTINGS</pre>';
//             echo '<pre>';var_dump($temperature);echo'</pre>';

            /*
             * now we collect data used to build the chart legend 
             */
            $temperature_high = max($temperature);
            $temperature_low  = min($temperature); 
            $temperature_mean = array_sum($temperature) / count($temperature);

            //to scale charts
            $ymax = $temperature_high;
            $ymin = $temperature_low;

            $temperature_high_time = $time[max($temperature)];
            $temperature_low_time = $time[min($temperature)];

            $temperature_latest = (($temperature[count($temperature)-1]));

            $variables = array(
                'temperature_high' => number_format($temperature_high, 1),
                'temperature_high_time' => $temperature_high_time,
                'temperature_low' => number_format($temperature_low, 1),
                'temperature_low_time' => $temperature_low_time,
                'temperature_mean' => number_format($temperature_mean, 1),
                'temperature_latest' => number_format($temperature_latest, 1),
            );

            /*
             * all data to be charted is now cooalated into $return
             * and is returned to be charted
             * 
             */

            $return  = array();

            // get legend layout from ini file        
            $return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

            //parse, clean and sort data
            $depth=2; //number of datasets
            $this->buildChartDataset($dataArray,$depth);

            //build chart object            
            $return['chart'] = array(
                'chart_format' => 'line',
                'chart_avg' => 'avg',
                
                'ymin' => $ymin,
                'ymax' => $ymax,

                'mean' => $temperature_mean,

                'dataset'            => $dataArray,
                'dataset_labels'    => $dataArrayLabel,
            );

            return $return;    
            
        } else {

            return false;
        }
    }

    

}
