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
	//set module name here
	$moduleName = 'Alerts';

	//grab the subheader 
    $showCalendar = false;

    include( APP_PATH . '/layout/subheader.php');

?>


	<!-- need to automate this include for all plugins js code -->
	<script src="<?php echo SCRIPT_ROOT ?>lib/plugins/Alerts/alerts.js" type="text/javascript"></script>

	<?php

	//get plugin class
	$alerts = LoadPlugins::$_classes[$moduleName];

	//get the range of dates to be charted from the UI and 
	//set the date range to be charted in the plugin
	$range = $loadavg->getDateRange();

	//check if we are pulling todays date or other date from drop down menu
	$gotLogDate = false;
	$logDate = date('Y-m-d');

	if ( (isset($_GET['logdate'])) && (!empty($_GET['logdate'])) ) {                             

		$logDate = $_GET['logdate'];

		if ( $logDate != date('Y-m-d') )
			$gotLogDate = true;
	}


	//echo '<pre>'; var_dump($range); echo '</pre>';

	// if module is enabled ... get his settings
    $pluginSettings = LoadPlugins::$_settings->$moduleName; 
	
	//get plugin settings
	$pluginData = $pluginSettings['chart']['args'][0]; //contains args[] array from modules .ini file

	//data about chart to be rendered
	$pluginData = json_decode($pluginData);


	//get the log file name for decoding
	$logfile = $alerts->getLogFile( $pluginData->logfile, $range, $moduleName );


	//get actual datasets needed to send to template to render chart from the logfile
	$chartData = $alerts->getChartRenderData(  $logfile );


	/*
	 * technically we can render out the alert data now as chart of all alerts
	 */

	//sorts alert data by key 1 into alertData
	//alertData["Cpu"] - module 1 ie cpu
	//alertData["Disk"] - module 2 ie disk
	$alertData = $alerts->arraySort($chartData,1);

	//echo '<pre>'; var_dump ($alertData); echo '</pre>'; 


	?>


	<div class="innerAll">

		<div class="row-fluid">
			<div class="span12">
				<div class="widget widget-4">
					<div class="widget-head">
						<h4 class="heading">
							<?php
								if ($gotLogDate)
									$title = 'Alerts On ' . $logDate;
								else
									$title = 'Alerts Today ' . $logDate;

								echo $title . "\n";
							?>
						</h4>
					</div>
				</div>
			</div>
		</div>

		<div id="separator" class="separator bottom"></div>

		<?php        

		//dataArray - time based array populated with modules alerts used to render charts
		$dataArray = $alerts->buildAlertArray($alertData, $logDate );

		//need list of all moudles for table in order to render table
		$modules = LoadModules::$_modules; 

		?>

		<script type="text/javascript">

		//we need to pass chartModules over to javascript code for chart
		var chartModules = [];
		chartModules = <?php print(json_encode($modules)); ?>;

		//we need to pass raw alertData over to javascript code for modal popups
		var alertData = [];
		alertData = <?php print(json_encode($alertData)); ?>;

		//time values here are todays date only!!!
		//we need to pass dataArray over to javascript code used to render the chart
		var dataArray = [];
		dataArray = <?php print(json_encode($dataArray)); ?>;
		</script>


		<div class="separator bottom"></div>
	


		<!-- build and render alerts table -->

		<table id="data-table" class="table table-bordered table-primary table-striped table-vertical-center"></table>
	


		<!-- Create Modal for alerts table  -->

		<div class="modal hide fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
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


	</div> <!-- // inner all end -->

	<?php 
	} // close logged in 
	else
	{
		include( APP_PATH . '/views/login.php');
	}
?>
