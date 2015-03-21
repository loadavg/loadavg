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

	</script>



	<div class="well lh70-style">
	    <b>Alert Data</b>
	    <div class="pull-right">
	    </div>
	</div>

	<div class="innerAll">

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

		//chartArray - time based array populated with modules alerts used to crate charts
		//and to source modal click data from 
		$chartArray = $alerts->buildChartArray($alertArray);

		//get list of all moudles for table
		$modules = LoadModules::$_modules; 

		?>

		<script type="text/javascript">

		//we need to pass alertArray over to javascript code for modals
		var alertData = [];
		alertData = <?php print(json_encode($alertArray)); ?>;

		//we need to pass chartModules over to javascript code for chart
		var chartModules = [];
		chartModules = <?php print(json_encode($modules)); ?>;

		//we need to pass chartArray over to javascript code for chart
		var chartArray = [];
		chartArray = <?php print(json_encode($chartArray)); ?>;
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
