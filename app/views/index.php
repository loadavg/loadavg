<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Index - main chart page interface
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>

<?php 
if (    (   $loadavg->isLoggedIn() 
        || ( isset($settings['settings']['allow_anyone']) && $settings['settings']['allow_anyone'] == "true" ) )
     && ( $banned == false ) 
    ) 
{ 

    //grab the subheader 
    $showCalendar = true;

    include( APP_PATH . '/layout/subheader.php');

    } 
    ?>

    <!--
        We render all the chart modules out here
    -->

    <div class="innerAll">

        <div id="accordion" class="accordion">

        <?php
        //for debuggin show internal list of all modules and their status (on or off)
        //echo '<pre> system modules '; var_dump( LoadModules::$_modules); echo '</pre>';

        //first check to see if list is stored in cookies
        //used to store layout sorting / render order       
        $cookieList = false;
        $cookieList = $loadModules->getUIcookieSorting();
        //echo '<pre>Cookie list'; var_dump( $chartList); echo '</pre>';


        //for no cookie, old broken cookies or issues with cookies
        //we use internal list of loaded Modules 
        $chartList = false;

        if ($cookieList == false)
            $chartList = LoadModules::$_modules; 
        else
            $chartList = $cookieList;
        //echo '<pre> chartList  '; var_dump( $chartList); echo '</pre>';


        //get the  range of dates to be charted from the UI and 
        $range = $loadavg->getDateRange();

        //set the date range to be charted in the modules
        $loadModules->setDateRange($range);

        // now render the charts out
        // loop through the chartlist of modules and draw them

        foreach ( $chartList as $module => $value ) { // looping through all the modules in the settings.ini file
            
            //echo 'module: ' . $module . ' value: ' . $value . "\n" ;

            // if modules is disabled then move on
            // cookie sorting will be all true even though values are open/close
            if ( $value == "false" )
                continue; 

            //make sure module loaded before using it
            if (isset(LoadModules::$_settings->$module))
                $moduleSettings = LoadModules::$_settings->$module; // if module is enabled ... get his settings
            else
                continue;

            //now draw the chart
            if ( $moduleSettings['module']['logable'] == "true" ) { // if module has loggable enabled it has a chart
                
                $loadModules->renderChart ( $module, true );

            }
        }



        ?>

    </div>    

    <?php
    echo '<center><strong>Server Time Zone</strong> ' . LoadAvg::$_settings->general['settings']['timezone'] . ' | <strong>Local Time</strong> ' . $theTime . '</center>';
    ?>

    </div>


    <!-- include javascript helper code for charts module -->
    <script src="<?php echo SCRIPT_ROOT ?>lib/charts/charts.js"></script>
