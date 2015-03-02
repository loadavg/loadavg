<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Server module interface
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
//open logged in
if ( $loadavg->isLoggedIn() )
{ 
?>


	<!-- need to automate this include for all plugins js code -->
	<script src="<?php echo SCRIPT_ROOT ?>lib/plugins/Alerts/alerts.js" type="text/javascript"></script>

	<?php

	//get plugin class
	$alerts = LoadPlugins::$_classes['Alerts'];

	//get the range of dates to be charted from the UI and 
	//set the date range to be charted in the plugin
	$range = $loadavg->getDateRange();

	$moduleName = 'Alerts';
	//$moduleName = __CLASS__;


    $moduleSettings = LoadPlugins::$_settings->$moduleName; // if module is enabled ... get his settings
	
	$chart = $moduleSettings['chart']['args'][0]; //contains args[] array from modules .ini file

	//data about chart to be rendered
	$chart = json_decode($chart);

	//get the log file NAME or names when there is a range and sets it in array chart->logfile
	//returns multiple files when multiple log files
	$logfile = $alerts->getLogFile( $chart->logfile, $range, $moduleName );

	//get actual datasets needed to send to template to render chart
	$chartData = $alerts->getChartRenderData(  $logfile );

	//
	// technically we can render out the alert data now as chart of all alerts
	//

	//sorts alert data by key 1 into myNewArray
	$alertArray = $alerts->arraySort($chartData,1);
	?>


	<script type="text/javascript">
	//we need to pass alertArray over to javascript code for modals
	var alertData = [];
	alertData = <?php print(json_encode($alertArray)); ?>;
	</script>

	<div class="well lh70-style">
	    <b>Alert Data</b>
	    <div class="pull-right">
	    </div>
	</div>

	<div class="innerAll">


	    <div id="accordion" class="accordion">	
		<?php
		    //if we want to render a chart - dont have one!
	        //$loadModules->renderChart("Uptime", false, false, false, false, 770 );
		?>
		</div>

		<div class="row-fluid">
			<div class="span12">
				<div class="widget widget-4">
					<div class="widget-head">
						<h4 class="heading">

							<?php
								
								$gotLog = false;

								if ($gotLog)
									$title = 'Alerts at ' . date('H:i:s', $timeStamp);
								else
									$title = 'Alerts (today)';

								echo $title . "\n";
							
							?>

						</h4>
					</div>
				</div>
			</div>
		</div>

		<div id="separator" class="separator bottom"></div>

	    <div id="accordion" class="accordion">

		<?php

		//lets see whats there
		foreach ($alertArray as $value) {
			
			$module++;

			//override some values here to close accordians
			$moduleCollapse = "accordion-body collapse";
		    $moduleCollapseStatus = "false";
			//render data to screen
			?>

			<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
				
				<div class="accordion-heading"> 
					<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
						<?php
						echo '<strong>Alerts:</strong> ' . $value[0][1];
						?>				
					</a>					
				</div>

				<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">
					<div class="accordion-inner">
						<?php
							//echo '<strong>Command:</strong> ' . $value[0][1] . '<br>';

							//echo "<pre>"; var_dump($value); echo "</pre>"; 
							foreach ($value as $items) {

				                $theTime = date("h:i a", $items[0]);

				                $alertData = json_decode($items[2]);

								echo ' Time: ' . $theTime;

								if (isset($alertData[0][0]))
								{
								echo ' Alert: ' . $alertData[0][0];
								echo ' Trigger: ' . $alertData[0][1];
								echo ' Value: ' . $alertData[0][2];
								}

								if (isset($alertData[1][0]))
								{
								echo ' Alert: ' . $alertData[1][0];
								echo ' Trigger: ' . $alertData[1][1];
								echo ' Value: ' . $alertData[1][2];
								}

								echo '<br>';
							}
						?>
					</div> <!-- // Accordion inner end -->
				</div> <!-- // Accordion category end -->

			</div> <!-- // Accordion inner stack end -->

			<?php
			}
			?>
		</div> <!-- // Accordion group end -->

		<div id="separator" class="separator bottom"></div>



		<?php        

		//myNewArray - empty time based array of modules alerts
		//myNewArray["Cpu"] - module 1 ie cpu
		//myNewArray["Disk"] - module 2 ie disk
		$timeArray = $alerts->buildTimeArray($alertArray);

		$modules = LoadModules::$_modules; 
		//$interfaces = LoadUtility::getNetworkInterfaces(); 
		?>

	<table class="table table-bordered table-primary table-striped table-vertical-center">

		<thead>
			<tr>
				<th style="width: 30px;" class="center">Time</th>

				<?php
				//render out column headings here
		        foreach ($modules as $module => $moduleStatus) { 

		        	if ($moduleStatus=="true")
		        		echo "<th style='width: 10%;'>" .  $module  . "</th>";

		        }
		        ?>
				<th style="width: 10%;" class="center">Alerts</th>

			</tr>
		</thead>
		<tbody>

		<?php

		/*
        if ($chartTimezoneMode == "UTC") {
            $gmtimenow = time() - (int)substr(date('O'),0,3)*60*60; 
            $theTime = date("h:i a", $gmtimenow) . " UTC";
        }		
        */

        //render out left column time headings
        //need to get these from javascript really to match up
		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));


		for ($i = 1; $i <= 24; $i++) {
		    $time = date('h:i:s a', $iTimestamp) . "\n<br />";
		?>

			<!-- Cart item -->
			<tr class="selectable" >
				<td class="center">
					<span class="label label-info"><?php echo $timeArray[$i]['time'];?></span>
				</td>


				<?php
				//render out modules data in table form...

				//out of 12 samples per hour this is 50% threshold
				$warning_threshold = 6;

				$totalAlerts = 0;
		        foreach ($modules as $module => $moduleStatus) { 

		        	if ($moduleStatus=="true")
		        	{
						if ( isset ($timeArray[$i][$module]) && ($timeArray[$i][$module] > 0) )
						{
							$value = $timeArray[$i][$module];
							?>
		        			<td class="center" data-toggle="modal" data-target="#myModal" data-module="<?php echo $module ?>" data-time="<?php echo $timeArray[$i]['timestamp'] ?>">

		        			<?php 
		        			if ($value>=$warning_threshold)
		        			{ ?>
								<span class="label label-warning">
							<?php } else { ?>
								<span class="label label-success">
							<?php } ?>

								<?php 
								echo $value; 
								$totalAlerts += $timeArray[$i][$module]; 
								?>
								</span>
							</td>
							<?php
						}
						else
						{
						?>
						<td class="center"></td>
						<?php
						}
					}		
		        }
				?>

				<td>
					<?php  //for totals... move me
					if ( $totalAlerts > 0) 
					{
					?>
						<span class="label label-info"><?php echo $totalAlerts; ?></span>
					<?php
					}
					?>
				</td>
			</tr>
		
		<?php
		    $iTimestamp += 3600;
		}
		?>


						
		</tbody>
	</table>


	<!-- Modal -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
	  <div class="modal-dialog">
	    <div class="modal-content">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		    <h4 class="modal-title"><strong>Modal title</strong></h4>
		  </div>
		  <div class="modal-body">
		  1<br>2<br>3<br>4<br>
		  </div>
		  <div class="modal-footer">
		    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		  </div>
	    </div>
	  </div>
	</div>

	<div class="separator bottom"></div>
	

	</div> <!-- // inner all end -->


	<?php 
	} // close logged in 
	else
	{
		include( APP_PATH . '/views/login.php');
	}
?>
