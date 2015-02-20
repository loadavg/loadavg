<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Index - chart page interface
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

<script type="text/javascript">
    //used to display browser time
    
    //get the offset for the client timezone 
    var currentTime = new Date()

    var tz_offset = currentTime.getTimezoneOffset()/60;

    var hours = currentTime.getHours()
    var minutes = currentTime.getMinutes()
    var ampm = "";

    if (minutes < 10) minutes = "0" + minutes

    if(hours > 12) { hours = hours - 12; ampm = "pm"; }
        else ampm = "am";

    var browserTime = hours + ":" + minutes + " " + ampm;

</script>

<table class="well lh70 lh70-style" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tr>
        <td width="30%">

            <?php 
            
            //Let them know what log fiels they are seeing
            
            if    ( ( isset($_GET['minDate'])  && !empty($_GET['minDate']) ) &&         
                    ( isset($_GET['maxDate'])  && !empty($_GET['maxDate']) ) )
            {
                echo '<strong>Viewing</strong> ' . date("l, M. j", strtotime($_GET['minDate'])); 
                echo ' <strong>to</strong> ' . date("M. j", strtotime($_GET['maxDate'])); 
            }
            else if ( (isset($_GET['logdate'])) && !empty($_GET['logdate']) ) 
            {
                echo '<strong>Viewing </strong> ' . date("l, M. j", strtotime($_GET['logdate'])); 
            } 
            else 
            { 
                echo '<strong>Viewing </strong>' . date("l, M. j "); 
            }


            //print time zone and local time

            echo '<br>Server zone ' . LoadAvg::$_settings->general['settings']['timezone'];

            $chartTimezoneMode = LoadAvg::$_settings->general['settings']['timezonemode'];

            if ($chartTimezoneMode == "UTC") {
                $gmtimenow = time() - (int)substr(date('O'),0,3)*60*60; 
                $theTime = date("h:i a", $gmtimenow) . " UTC";
            }
            else if ($chartTimezoneMode == "Browser" || $chartTimezoneMode == "Override"  ) {
                $theTime = '<script type="text/javascript">document.write(browserTime);</script>';
            }

            echo  '<br>Local time ' . $theTime ;

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

        //get a list of all modules and their status (on or off)
        $loadedModules = LoadModules::$_modules; 
        //echo '<pre> system modules '; var_dump( $loadedModules); echo '</pre>';

        //now check for cookies - used to store layout sorting and open/close       
        $cookieList = false;

        $cookieList = $loadModules->getUIcookieSorting();
        //echo '<pre>Cookie list'; var_dump( $cookieList); echo '</pre>';

        //grab module settings and drop disabled modules here
        $chartList = null;

        if ($cookieList != false) {

            //so lets go with the cookieList
            $chartList = $cookieList;

            //but we need to test it out first make sire its ok ?
            //me thinks this shoudl be done in getUIcookieSorting ? not here ?
            //any issues getting cookie should just return false

            $cleanSettings = null;
            foreach ($loadedModules as $key =>$value) {

                if ($value=="true") {
                    $cleanSettings[$key]="true";
                }
            }

            //these should match really but if not dont we need to do something ?
            if (!LoadUtility::identical_values( $cleanSettings , $cookieList )) {

                echo '<pre>MISSMATCH</pre>';

                $loadModules->updateUIcookieSorting($loadedModules);
            }

        }
        else {
            $chartList = $loadedModules;
        }



        //for old broken cookies or issues with $chartList
        if ($chartList == null || !$chartList)
            $chartList = $loadedModules;

        //echo '<pre>Chart list'; var_dump( $chartList); echo '</pre>';

        //get the range of dates to be charted from the UI and 
        //set the date range to be charted in the modules
        $range = $loadavg->getDateRange();

        $loadModules->setDateRange($range);



        //now loop through the modules and draw them
        //now render the charts out

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