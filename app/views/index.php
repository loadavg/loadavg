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

<?php if ($loadavg->isLoggedIn()) { 

//if (!isset($_GET['page'])) {
//    $_SET['page'] = '';
//}
    ?>

<table class="well lh70 lh70-style" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tr>
        <td width="30%">
            <b>Today</b> - <?php echo date("l, M. j H:i a", (time()-300)); ?>  <!--  need to add log file dates here when overriden   -->



            <?php if ( (isset($_GET['logdate'])) && !empty($_GET['logdate']) ) 
            {
            echo '<br>Viewing ' .  $_GET['logdate'];
            } else {
            ?> 
            <br>Zone <?php echo date("(e)", (time()-300)); 
            }
            ?>  
            
        </td>
        <td width="70%" align="right">
            <form action="" method="get" class="margin-none form-horizontal">
                <!-- Periods -->
                <div class="control-group margin-none">
                    <label class="control-label"><b>Period:</b></label>
                    <div class="controls">
                        <input type="hidden" id="minDateValue" value="<?php echo date("Y-m-d", strtotime("-". LoadAvg::$_settings->general['daystokeep'] ." days 00:00:00")); ?>">
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

<div class="innerAll">
    <?php
    foreach ( $loaded as $module => $value ) { // looping through all the modules in the settings.ini file
        if ( $value === "false" ) continue; // if modules is disabled ... moving on.

        $moduleSettings = LoadAvg::$_settings->$module; // if module is enabled ... get his settings
        
        if ( $moduleSettings['module']['logable'] == "true" ) { // if module has loggable enabled it has a chart
            
            $class = LoadAvg::$_classes[$module];

            $i = 0;

            if (isset($moduleSettings['module']['tabbed']) && $moduleSettings['module']['tabbed'] == "true") {
                if ($i == 1) break;

                //echo 'NESTEDCHARTS:';
                $class->genChart( $moduleSettings, $logdir );
                $i++; //will this ever be hit ? as i = 1 breaks things
            } else {
                //echo 'CORECHART:';
                $class->genChart( $moduleSettings, $logdir );
            }
        }
    }
    ?>
</div>