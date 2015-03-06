<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Alerts plugin interface
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

	//sorts alert data by key 1 into alertArray
	//alertArray["Cpu"] - module 1 ie cpu
	//alertArray["Disk"] - module 2 ie disk
	$alertArray = $alerts->arraySort($chartData,1);
	//$alertArray = $chartData;

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
	        //$loadModules->setDateRange($range);
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

		<?php        

		//chartArray - time based array populated with modules alerts used to render chart
		$chartArray = $alerts->buildChartArray($alertArray);

		//echo '<pre>'; var_dump ($chartArray); echo '</pre>';

		//get list of all moudles for table
		$modules = LoadModules::$_modules; 

		?>

	<table class="table table-bordered table-primary table-striped table-vertical-center">

		<thead>
			<tr>
				<th class="center">Time</th>

				<?php
				//render out column headings here
		        foreach ($modules as $module => $moduleStatus) { 
		        	if ($moduleStatus=="true")
		        		echo "<th style='width: 10%;'>" .  $module  . "</th>";

		        } ?>
				<th style="width: 10%;" class="center">Alerts</th>

			</tr>
		</thead>

		<tbody>
		<?php
        //render out left column time headings
        //need to get these from javascript really to match up
		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		//loop through hours in table rows
		for ($i = 1; $i <= 24; $i++) {
		?>

			<!-- Cart item -->
			<tr class="selectable" >
				<td class="center">
					<span class="label label-info"><?php echo $chartArray[$i]['time'];?></span>
				</td>


				<?php
				//render out modules data in table form...

				//out of 12 samples per hour 6 is 50% threshold for table values
				$warning_threshold = 6;

				//alert themes here
				//http://stackoverflow.com/questions/21725557/additional-twitter-bootstrap-label-button-alert-badge-colors
				
				$totalAlerts = 0;
		        foreach ($modules as $module => $moduleStatus) { 

		        	if ($moduleStatus=="true")
		        	{
						if ( isset ($chartArray[$i][$module]) && ($chartArray[$i][$module] > 0) )
						{
							$value = $chartArray[$i][$module];
							?>
		        			<td class="center" data-toggle="modal" data-target="#myModal" data-module="<?php echo $module ?>" data-time="<?php echo $chartArray[$i]['timestamp'] ?>">

		        			<?php 
		        			if ($value>=$warning_threshold) 
		        				{ ?> <span class="label label-warning"> <?php } 
							else 
								{ ?> <span class="label label-success"><?php } 

								echo $value; 
								$totalAlerts += $chartArray[$i][$module]; 

								?>
								</span>
							</td>
							<?php
						}
						else { ?>
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
