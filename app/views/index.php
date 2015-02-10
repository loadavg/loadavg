<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Index page interface
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
?>

<table class="well lh70 lh70-style" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tr>
        <td width="30%">
            <b>Today</b> - <?php echo date("l, M. j h:i a", (time())); ?>  <!--  need to add log file dates here when overriden   -->



            <?php if ( (isset($_GET['logdate'])) && !empty($_GET['logdate']) ) 
            {
            echo '<br>Viewing ' . date("l, M. j", strtotime($_GET['logdate'])); 
            } else {
            ?> 
            <br>Server <?php echo LoadAvg::$_settings->general['settings']['timezone']; ?>
            <br>Client <?php echo date("e", (time())); 
            }
            ?>  
            
        </td>
        <td width="70%" align="right">
            <form action="" method="get" class="margin-none form-horizontal">
                <!-- Periods -->
                <div class="control-group margin-none">
                    <label class="control-label"><b>Period:</b></label>
                    <div class="controls">
                        <input type="hidden" id="minDateValue" value="<?php echo date("Y-m-d", strtotime("-". LoadAvg::$_settings->general['settings']['daystokeep'] ." days 00:00:00")); ?>">
                        <input type="hidden" id="maxDateValue" value="<?php echo date("Y-m-d"); ?>">
                        
                        <input type="text" id="minDate" name="minDate" value="<?php echo (isset($_GET['minDate']) && !empty($_GET['minDate'])) ? $_GET['minDate'] : ''; ?>" placeholder="Period from" style="width: 70px;height: 18px;">
                        -
                        <input type="text" id="maxDate" name="maxDate" value="<?php echo (isset($_GET['minDate']) && !empty($_GET['maxDate'])) ? $_GET['maxDate'] : ''; ?>" placeholder="Period to" style="width: 70px;height: 18px;">


                        <b class="innerL">Log file:</b>
                        <select name="logdate" onchange="this.submit()" style="width: 110px;height: 28px;">
                        <?php

                        $dates = LoadAvg::getDates();

                        $date_counter = 1;

                        $totalDates = (int)count($dates);

                        foreach ( $dates as $date ) {

                            if (  ($date_counter !=  $totalDates) )

                            {
                                ?><option<?php echo ((isset($_GET['logdate']) && !empty($_GET['logdate']) && $_GET['logdate'] == $date) || (!isset($_GET['logdate']) && $date == date('Y-m-d'))) ? ' selected="selected"' : ''; ?> value="<?php echo $date; ?>"><?php echo $date; ?></option><?php
                            }
                            else
                            {
                                //last date is todays date add for easy access
                                ?><option<?php echo ((isset($_GET['logdate']) && !empty($_GET['logdate']) && $_GET['logdate'] == $date) || (!isset($_GET['logdate']) && $date == date('Y-m-d'))) ? ' selected="selected"' : ''; ?> value="<?php echo $date; ?>"><?php echo 'Today'; ?></option><?php                                
                            }

                            $date_counter++;
                        }

                        ?>
                        </select>
                        <input type="submit" value="View" class="btn btn-primary" />
                    </div>
                </div>
                <!-- End of Periods -->
            </form>
        </td>
    </tr>
</table>
<?php 
} 
?>

    <!--
        We render all the chart modules here
    -->

    <div class="innerAll">

    <div id="accordion" class="accordion">



        <?php

        $loadedModules = LoadModules::$_settings->general['modules']; 

        //this has become one hell of a mess need to revisit and clean up cookie code
        //as system stores module status 
        //but cookies storie if moudle is there or not
        
        //echo '<pre> system activated '; var_dump( $loadedModules); echo '</pre>';

        $cookieStatus = false;
        $cookieList;
        $cookieStatus = $loadModules->getUIcookieSorting($cookieList);

        //grab module settings and drop disabled modules here
        if ($cookieStatus) {

            $cleanSettings = null;
            foreach ($loadedModules as $key =>$value) {

                if ($value=="true") {
                    $cleanSettings[$key]="true";
                }
            }

            //these should match really
            if (!LoadUtility::identical_values( $cleanSettings , $cookieList )) {
                $loadModules->updateUIcookieSorting($loadedModules);
            }

        }
       //echo '<pre>'; var_dump( $cookieList); echo '</pre>';

        //now loop through the modules and draw them
        $moduleNumber = 0;
        $chartList = null;

        if ($cookieStatus)
            $chartList = $cookieList;
        else
            $chartList = $loadedModules;

        //for old broken cookies or issues with $cookieList
        if ($chartList == null || !$chartList)
            $chartList = $loadedModules;

        //echo '<pre> live settings'; var_dump( $chartList); echo '</pre>';

        //get the range of dates to be charted from the UI and 
        $range = $loadavg->getDateRange();

        //set the date range to be charted in the modules
        $loadModules->setDateRange($range);


        //now render the charts out
        //$loadModules->renderCharts($chartList, true);

        foreach ( $chartList as $module => $value ) { // looping through all the modules in the settings.ini file
            
            //echo 'module: ' . $module . 'value: ' . $value ;

            if ( $value === "false" ) continue; // if modules is disabled ... moving on.

            //fix for issues with cookies
            if (!isset(LoadModules::$_settings->$module))
                continue;

            $moduleSettings = LoadModules::$_settings->$module; // if module is enabled ... get his settings
            
            if ( $moduleSettings['module']['logable'] == "true" ) { // if module has loggable enabled it has a chart
                
        
                $loadModules->renderChart ( $module, true );

            }
        }



        ?>

    </div>    
    </div>