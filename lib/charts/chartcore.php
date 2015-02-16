
<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Charting core for LoadAvg included by charts.php
* used in main charts and override modules
*
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>

<?php

	//hardcoded for now - used for div height at end of page
	$chartHeight = 160;


	//global timezone data - can be overriden per chart later on
	$chartTimezoneMode = LoadAvg::$_settings->general['settings']['timezonemode'];
	$chartTimezone = LoadAvg::$_settings->general['settings']['clienttimezone'];

	//echo 'chartModules : ' . $chartModules;
	//var_dump ($chartData['chart']);

/*
 * $chartModules is passed over by calling function in module and is used to track multiple modules in chart
 * more than 1 in chartModules means multiple charts in the segment so we include js files just once
 * and make calls to functions that need to be loaded or already loaded here
 */
?>

	<!--
	Fist function here builds the chart data object to send over to be charted
	-->

	<script type="text/javascript">
	(function () {
	
		//if greater than 1 then extend chart id with chart function
		<?php if ( $chartModules > 1) { ?>
		charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
		<?php }?>

			//core chart data here
			var chart_data = new Array();
			var chart_info = new Array();

			//chart_info has all core chart data
			<?php 
			if ( isset($chartData['chart']['dataset'][0])  ) { ?>
				 chart_info = {

					ymin: <?php echo $chartData['chart']['ymin']; ?>,
					ymax: <?php echo $chartData['chart']['ymax']; ?>,

					timezonemode: "<?php echo $chartTimezoneMode; ?>",
					timezone: "<?php echo $chartTimezone; ?>"
					
					<?php if ($width) echo ', chartwidth: ' . $width .',';  ?>
					<?php if ($height) echo 'chartheight: ' . $height;  ?>
					//defualts are chartwidth: 530, chartheight: 160
				};
			<?php } ?>

			<?php 

			//loop through sets in chartData and if they are viable
			//send them over to be charted via chart_Data
			
			foreach ( $chartData['chart']['dataset'] as $dataKey => $dataSet ) { ?>
				chart_data[<?php echo $dataKey; ?>] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][$dataKey]; ?>',
					data: <?php echo $dataSet; ?>
				};
			<?php 
			}


			/*	is foreach above faster than the loop ? seems so
			for ( $dataLoop = 0; $dataLoop <= 4; ++$dataLoop ) {

				if ( isset($chartData['chart']['dataset'][$dataLoop])  ) { ?>
					 chart_data[<?php echo $dataLoop; ?>] = {
						label: '<?php echo $chartData['chart']['dataset_labels'][$dataLoop]; ?>',
						data: <?php echo $chartData['chart']['dataset'][$dataLoop]; ?>
					};
				<?php } 
			} */
			?>

			//great for debugging! sends entire array to console for inspection
			//console.info(chart_data); 

			// This function calls the charts flot javascript code to render out chart

			$(function () {

				<?php 
				//used to set things up for differrent chart views ie 6 / 12 hour charts
				$chartType = LoadAvg::$_settings->general['settings']['chart_type'];
				$changeRange = false;	

				if ( $chartType == "6" || $chartType == "12" ) {

					$rangeHours = $chartType; //we can change this
					$rangeMax =  (   time()  *1000); 
					$rangeMin =  $rangeMax - (3600 * $rangeHours * 1000); //3600 seconds per hour
					//$chartWidth = 60*3*1000;
					$changeRange = true;
				} 
				?>

				/*
				 * first time we use a charts code we have to initialize it using the charts function
				 * other calls can just use the charts id
				 */

				<?php if ( $chartModules == 1) 
				{ ?>

					//send chart data over first	
					charts.<?php echo $chart->chart_function; ?>.setData(chart_data,chart_info);

					//code to override the date and time for zooming in
					//has issues if start time is before start of series mon
					<?php if ($changeRange == true) { ?>
					charts.<?php echo $chart->chart_function; ?>.setRange(  <?php echo $rangeMin; ?> , <?php echo $rangeMax; ?> , <?php echo ($chartType/2); ?>  );
					<?php } ?>
				
					//initalize chart here
					charts.<?php echo $chart->chart_function; ?>.init('<?php echo $chart->id; ?>');


				<?php 
				} 

				//elseif ($chartModules > 1) 
				else

				{ ?>
					//send chart data over first	
					charts.<?php echo $chart->id; ?>.setData(chart_data,chart_info);		

					//code to override the date and time for zooming in
					//has issues if start time is before start of series mon
					<?php if ($changeRange == true) { ?>
					charts.<?php echo $chart->chart_function; ?>.setRange(  <?php echo $rangeMin; ?> , <?php echo $rangeMax; ?> , <?php echo ($chartType/2); ?>  );
					<?php } ?>

					//initalize chart here
					charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');

				<?php } ?>

			})

		})();

	</script>

	<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR" style="right: 22px;"></div>
	<div class="clearfix"></div>
	<div id="<?php echo $chart->id; ?>" style="height: <?php echo $chartHeight;?>px;" class="chart-holder"></div>

	<script>
	/*
	//OnClick Code
	//used to add a redirect to url on-click on chart!
	//see if we can move into module .js code
	
	$("#<?php echo $chart->id; ?>").bind('plotclick', function ( event, pos, item ) {
	 
	 if(item) {

	 	console.log(item);
	 	console.log(item.series.label);
	 	console.log(item.dataIndex);
	 	console.log(item.datapoint);

	 	//window.open("/index.php","_self");
	 }
	 
	 //http://www.benknowscode.com/2013/02/adding-interaction-to-flot-graphs_7028.html
	 
	});
	*/
	</script>

