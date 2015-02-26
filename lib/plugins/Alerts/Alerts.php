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

		function cmp($a, $b)
		{
	  		return strcmp($a[1], $b[1]);
		}
		

		//sorts data by key 1 into myNewArray
		$myNewArray = $alerts->arraySort($chartData,1);



		//now we should sory myNewArray by totals but dont have them yet!!!
		//would be great if the arraySort did this as well, totaled up cpu and mem as it sorted...

		//gives each module a id in accordions
		$module = 0;

		//echo "<pre>"; var_dump($myNewArray); echo "</pre>"; 

		//myNewArray - array of modules alerts
		//myNewArray[1] - module 1 ie cpu
		//myNewArray[2] - module 2 ie network

		foreach ($myNewArray as $value) {
			
			$module++;

			//override some values here to close accordians
			$moduleCollapse = "accordion-body collapse";
		    $moduleCollapseStatus = "false";

			//$moduleCollapse = "accordion-body collapse in";
		    //$moduleCollapseStatus = "true";

			//render data to screen
			?>

			<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
				
				<div class="accordion-heading"> 
					<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
						<?php
						echo '<strong>Process:</strong> ' . $value[0][1];
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

	



	<!--
	<table class="table table-white table-bordered table-vertical-center table-pricing">
	-->


	<table class="table table-bordered table-primary table-striped table-vertical-center">

		<thead>
			<tr>
				<th style="width: 40px;" class="center">Time</th>
				<th style="width: 40px;" class="center">Alerts</th>
				<th style="width: 10%;">CPU</th>
				<th style="width: 10%;">Proc</th>
				<th style="width: 10%;">Swap</th>
				<th style="width: 10%;">Memory</th>
				<th style="width: 10%;">Disk</th>
				<th style="width: 10%;">Network</th>
				<th style="width: 10%;">Uptime</th> <!-- 50px -->
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


/*
		$iTimestamp  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));

		for ($i = 1; $i <= 24; $i++) {
		    //$time = date('H:i:s', $iTimestamp) . "\n<br />";
		    $time = date('h:i:s a', $iTimestamp) . "\n<br />";
*/		


		    if (null==true) {
		?>

		<!-- Cart item -->
		<tr class="selectable">
			<!--
			<td class="center"><span class="label label-important">12:00 AM</span></td>
			-->
			<td class="center"><span class="label label-important"><?php echo $time   ?></span></td>
			<td>
				<span class="label">3-4 Years</span>
			</td>
			<td class="center">1</td>
			<td class="center">100</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
		</tr>
		<!-- // Cart item END -->
					<!-- Cart item -->
		<tr class="selectable">
			<td class="center"></td>
			<td>
				<span class="label">3-4 Years</span>
			</td>
			<td class="center">1</td>
			<td class="center">100</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
			<td class="center">119</td>
		</tr>


		<?php
		    $iTimestamp += 3600;
		}
		?>


						
		</tbody>
	</table>

	<div class="separator bottom"></div>
	







	</div> <!-- // inner all end -->


	<?php 
	} // close logged in 
	else
	{
		include( APP_PATH . '/views/login.php');
	}
?>
