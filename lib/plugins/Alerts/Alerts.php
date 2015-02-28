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

<?php

	//used for callbacks to main plugin to select timestamp to display
	//we are also passing back chart time

	$timeStamp = false;
	if (isset($_GET['timestamp'])) {
		$timeStamp = $_GET['timestamp'];
	}

	$chartTime = false;
	if (isset($_GET['charttime'])) {
		$chartTime = $_GET['charttime'];
	}

	//get plugin class
	$alerts = LoadPlugins::$_classes['Alerts'];

	//get the range of dates to be charted from the UI and 
	//set the date range to be charted in the plugin
	$range = $loadavg->getDateRange();
	$moduleName = 'Alerts';
	//$moduleName = __CLASS__;




    //echo '<pre>'; var_dump ($range); echo '</pre>'; 
    //echo '<pre>'; var_dump ($moduleName); echo '</pre>'; 
    //echo '<pre>'; var_dump ($moduleTemplate); echo '</pre>'; 


	//$alerts = LoadPlugins::$_classes['Alerts']; 
	//$chartData = $alerts->getUsageData();
    $moduleSettings = LoadPlugins::$_settings->$moduleName; // if module is enabled ... get his settings
	
	//$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file
	$chart = $moduleSettings['chart']['args'][0]; //contains args[] array from modules .ini file

	//data about chart to be rendered
	$chart = json_decode($chart);

	//get the log file NAME or names when there is a range and sets it in array chart->logfile
	//returns multiple files when multiple log files
	$logfile = $alerts->getLogFile( $chart->logfile, $range, $moduleName );

	//get actual datasets needed to send to template to render chart
	$chartData = $alerts->getChartRenderData(  $logfile );

	//echo '<pre>'; var_dump ($chartData); echo '</pre>'; 




?>


	<div class="well lh70-style">
	    <b>Alert Data</b>
	    <div class="pull-right">
	    </div>
	</div>

	<div class="innerAll">


	    <div id="accordion" class="accordion">	
		<?php
		    //render chart
		   // $loadModules->renderChart("Cpu", false, false, false, $callback, 770 );
		?>
		</div>

		<!--
		widget stytles can be found here but need cleaning up
		http://demo.mosaicpro.biz/smashingadmin/php/index.php?lang=en&page=widgets
		-->


		<div class="row-fluid">
			<div class="span12">
				<div class="widget widget-4">
					<div class="widget-head">
						<h4 class="heading">

							<?php
								
								$gotLog = false;

								if ($gotLog)
									$title = 'Running Processess at ' . date('H:i:s', $timeStamp);
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
		/*
		function cmp($a, $b)
		{
	  		return strcmp($a[1], $b[1]);
		}
		*/		

		//sorts alert data by key 1 into myNewArray
		$alertArray = $alerts->arraySort($chartData,1);


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


		//now we should sory myNewArray by totals but dont have them yet!!!
		//would be great if the arraySort did this as well, totaled up cpu and mem as it sorted...

		//$itemalertArray = $alerts->getTimeSlotAlert("Cpu","12:00 am", "01:00 am", $alertArray);
		//$itemalertArray = $alerts->getTimeSlotAlert("Cpu","01:00 am", "02:00 am", $alertArray);
		//$itemalertArray = $alerts->getTimeSlotAlert("Network","01:00 am", "02:00 am", $alertArray);

		?>

		<div id="separator" class="separator bottom"></div>




	<?php        

		//myNewArray - empty time based array of modules alerts
		//myNewArray["Cpu"] - module 1 ie cpu
		//myNewArray["Disk"] - module 2 ie disk
		$timeArray = $alerts->buildTimeArray($alertArray);

		$modules = LoadModules::$_modules; 
		$interfaces = LoadUtility::getNetworkInterfaces(); 
	?>

	<table class="table table-bordered table-primary table-striped table-vertical-center">

		<thead>
			<tr>
				<th style="width: 30px;" class="center">Time</th>

				<?php
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

		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		for ($i = 1; $i <= 24; $i++) {
		    $time = date('h:i:s a', $iTimestamp) . "\n<br />";
		?>

			<!-- Cart item -->
			<tr class="selectable" >
				<td class="center">
					<span class="label label-important"><?php echo $timeArray[$i]['time'];?></span>
				</td>


				<?php
				//render out modules data in table...

				$totalAlerts = 0;
		        foreach ($modules as $module => $moduleStatus) { 

		        	if ($moduleStatus=="true")
		        	{
						if ( isset ($timeArray[$i][$module]) && ($timeArray[$i][$module] > 0) )
						{
							?>
		        			<td class="center" data-toggle="modal" data-target="#myModal" data-date="<?php echo $module ?>" data-time="<?php echo $i ?>">
								<span class="label">
								<?php 
								echo $timeArray[$i][$module]; 
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
						<span class="label label-important"><?php echo $totalAlerts; ?></span>
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

	<script type="text/javascript">

	$(function(){
		$('#myModal').on('show', function(){ //subscribe to show method

		    var date = $(event.target).closest('td').data('date');
		    var time = $(event.target).closest('td').data('time');
			console.log(date);
			console.log(time);

		    $(this).find('.modal-body').html($('<b>Alert Module: ' + date  + '</b><br>' +
		    									'<b>Alert Time: ' + time  + '</b>'

		    								))
		});
	});

	</script>

	<!-- Modal -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
	  <div class="modal-dialog">
	    <div class="modal-content">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		    <h4 class="modal-title"><strong>Modal title</strong></h4>
		  </div>
		  <div class="modal-body">
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
