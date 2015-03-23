<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Subheader - used to add date selector to pages
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>


<script type="text/javascript">
    //used to display browser time
    
    //get the offset for the client timezone 
    var currentTime = new Date();

    //why doest this pull getItemTime from common.js ?
    //var browserTime = getItemTime(currentTime);

    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();
    var ampm = "";

    if (minutes < 10) 
        minutes = "0" + minutes;

    if(hours > 12) { hours = hours - 12; ampm = "pm"; }
        else ampm = "am";

    var browserTime = hours + ":" + minutes + " " + ampm;

    //get the timezone offset
    var tz_offset = currentTime.getTimezoneOffset()/60;

	/*
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
	 console.log ("we are mobile");
	} else
	{
	 console.log ("we are not mobile");	
	}
	*/
</script>



<table class="well lh70 lh70-style" width="100%" border="0" cellspacing="1" cellpadding="3">
    <tr>
        <td width="30%">

            <?php 

            echo '<strong>Server</strong> ' . LoadAvg::$_settings->general['settings']['title'] . "<br>";

            //figure out the time first
            $chartTimezoneMode = LoadAvg::$_settings->general['settings']['timezonemode'];

            if ($chartTimezoneMode == "UTC") {
                $gmtimenow = time() - (int)substr(date('O'),0,3)*60*60; 
                $theTime = date("h:i a", $gmtimenow) . " UTC";
            }
            else if ($chartTimezoneMode == "Browser" || $chartTimezoneMode == "Override"  ) {
                $theTime = '<script type="text/javascript">document.write(browserTime);</script>';
            }


            //Now let them know what data they are seeing
            
            if    ( ( isset($_GET['minDate'])  && !empty($_GET['minDate']) ) &&         
                    ( isset($_GET['maxDate'])  && !empty($_GET['maxDate']) ) )
            {
                echo '<strong>Viewing</strong> ' . date("l, M. j", strtotime($_GET['minDate'])); 
                echo ' <strong>to</strong> ' . date("M. j", strtotime($_GET['maxDate'])); 
            }
            else if ( (isset($_GET['logdate'])) && !empty($_GET['logdate']) ) 
            {
                echo '<strong>Viewing </strong> ' . date("l, M. j", strtotime($_GET['logdate'])); 
                //echo date("l, M. j", strtotime($_GET['logdate'])); 
            } 
            else 
            { 
                //echo '<strong>Viewing </strong>' . date("l, M. j "); 
                echo date("l, M. j ") . " | " . $theTime; 
            }

            ?>

        </td>

        <td width="70%" align="right">

			<script type="text/javascript">
			// a dirty hack for our form/drop down menu as we need to hack the url for plugins
			//as plugins have a ? in the url and a form submit overwrites the ? with the base url
			function myfunction() {

				//first we get the url
				//var url = window.location.href;
				var url = document.URL;
				var lastPart = url.substr(url.lastIndexOf('/') + 1);


				//this means that page is in the url so we are on a page
				//if we are on a page we need to add the plugin name back to the url here
				if ( lastPart.indexOf("page=") > -1 ) {

					console.log('Page is in url!');

					//get the modules
					var loadModules = [];
					loadModules = <?php print(json_encode(LoadPlugins::$_plugins)); ?>;
					console.log('loadModules', loadModules);


					for ( key in loadModules) {

						if ( lastPart.indexOf(key) > -1 ) {

							//we need to insert page if we are on a plugin here!!!
							var myin = document.createElement("input"); 
							myin.type='hidden'; 
							myin.name='page'; 
							myin.value=key; 

							//this would append at the end... 
							//document.getElementById('form_id').appendChild(myin); 
							//we insert at begining for eg index.php?page=alerts...
							document.getElementById('form_id').insertBefore(myin,document.getElementById('form_id').firstChild);
							break;
						}
					}
				}

				//alert(url);

				document.getElementById("form_id").action = "index.php"; // Setting form action to "success.php" page
				document.getElementById("form_id").submit(); // Submitting form

			}
			</script>

			<form id = "form_id" onsubmit="this.action=get_action();" method="get" class="margin-none form-horizontal">

                <!-- Periods -->
                <div class="control-group margin-none">

                <?php
				if ( isset($showCalendar)  && ($showCalendar==true) )
				{
				?>

                    <label class="control-label"><b>Period:</b></label>
                    <div class="controls">

                        <input type="hidden" id="minDateValue" value="<?php echo date("Y-m-d", strtotime("-". LoadAvg::$_settings->general['settings']['daystokeep'] ." days 00:00:00")); ?>">
                        <input type="hidden" id="maxDateValue" value="<?php echo date("Y-m-d"); ?>">
  
                        <input type="text" id="minDate" name="minDate" value="<?php echo (isset($_GET['minDate']) && !empty($_GET['minDate'])) ? $_GET['minDate'] : ''; ?>" placeholder="Period from" style="width: 70px;height: 18px;">
                        -
                        <input type="text" id="maxDate" name="maxDate" value="<?php echo (isset($_GET['minDate']) && !empty($_GET['maxDate'])) ? $_GET['maxDate'] : ''; ?>" placeholder="Period to" style="width: 70px;height: 18px;">
                <?php
				}
				?>

                        <b class="innerL">Log file:</b>
                        <select name="logdate" id="logdate" style="width: 110px;height: 28px;">
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

                        <input type="hidden" id="page" value="Alert">

                        </select>
                        <input onclick="myfunction()" type="button" value="View" class="btn btn-primary" />
                    </div>
                </div>
                <!-- End of Periods -->
            </form>

        </td>

    </tr>
</table>


