
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


	<script type="text/javascript">
	(function () {
	
		<?php if ( $i > 1) { ?>
		charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
		<?php }?>

			//core chart data here
			// load core chart data here
			var chart_data = new Array();

			//we can chart up to 4 data sets now need to move this to a array as well
			var d1 = d2 = d3 = d4 = null;

			//primary dataset includes ymin and ymax - should separate this
			<?php 
			if ( isset($chartData['chart']['dataset_1']) ) { ?>
				 //console.log('data 1');
				 d1 = {
					label: '<?php echo $chartData['chart']['dataset_1_label']; ?>',
					data: <?php echo $chartData['chart']['dataset_1']; ?>,
					ymin: <?php echo $chartData['chart']['ymin']; ?>,
					ymax: <?php echo $chartData['chart']['ymax']; ?>
				};
				chart_data.push(d1);
			<?php } ?>

			//used for primary overload
			<?php 
			//if (    ( isset($chartData['chart']['dataset_2']))    ) {  
			if (    isset($chartData['chart']['dataset_2'])  ) {  ?>
				 d2 = {
					label: '<?php echo $chartData['chart']['dataset_2_label']; ?>',
					data: <?php echo $chartData['chart']['dataset_2']; ?>
				};
				chart_data.push(d2);
			<?php } ?>

			//used for secondary overlaods
			<?php if ( isset($chartData['chart']['dataset_3']) ) { 	?>
				 d3 = {
					label: '<?php echo $chartData['chart']['dataset_3_label']; ?>',
					data: <?php echo $chartData['chart']['dataset_3']; ?>
				};
				chart_data.push(d3);
			<?php } ?>

			//d3 is shareds! we need to have d4 for swap moving ahead
			// new swap code
			<?php 
			if ( isset($chartData['chart']['dataset_4']) ) { ?>
				 d4 = {
					label: '<?php echo $chartData['chart']['dataset_4_label']; ?>',
					data: <?php echo $chartData['chart']['dataset_4']; ?>
				};
				chart_data.push(d4);
			<?php } ?>

			//great for debugging!
			//sends entire array to console for inspection
			///console.info(chart_data);


			// CLEAN ME UP PLEASE!!!

	        // render the chart using the chart.js data
	        // for error message until we can figure out how to render error message 
	        // on top of blank chart we override the label  :)

			$(function () {


				<?php 
				$chartType = LoadAvg::$_settings->general['settings']['chart_type'];
				$changeRange = false;				
				//uses current time needs to use time of last log file entry ?
				if ( $chartType == "6" || $chartType == "12" ) {

					$rangeHours = $chartType; //we can change this
					$rangeMax =  (   time()  *1000); 
					$rangeMin =  $rangeMax - (3600 * $rangeHours * 1000); //3600 seconds per hour
					//$chartWidth = 60*3*1000;
					$changeRange = true;
				} 
				?>

				//not sure whats going on below but when i == 1 we do
				// $chart->chart_function

				//and when i > 1 we do
				// $chart->id

				//tied to this line at start of the loop for when i > 1
				// $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);


				<?php if ( $i == 1) 
				{ ?>

					//send chart data over first	
					charts.<?php echo $chart->chart_function; ?>.setData(chart_data);

					//code to override the date and time for zooming in
					//has issues if start time is before start of series mon
					<?php if ($changeRange == true) { ?>
					charts.<?php echo $chart->chart_function; ?>.setRange(  <?php echo $rangeMin; ?> , <?php echo $rangeMax; ?> , <?php echo ($chartType/2); ?>  );
					<?php } ?>

					//if set to true it means we have no log file to read from
					//so we send over error message
					<?php 
					if ($logfileStatus == true) { 
						
						$errorMessage = 'No logfile data to generate charts for module ' . $module . ' check your logger';
						?>
						charts.<?php echo $chart->chart_function; ?>.setLabel("<?php echo $errorMessage; ?>");
					<?php } ?>

					//initalize chart here
					charts.<?php echo $chart->chart_function; ?>.init('<?php echo $chart->id; ?>');


				<?php 
				} 

				elseif ($i > 1) 

				{ ?>
					//send chart data over first	
					charts.<?php echo $chart->id; ?>.setData(chart_data);		

					//code to override the date and time for zooming in
					//has issues if start time is before start of series mon
					<?php if ($changeRange == true) { ?>
					charts.<?php echo $chart->chart_function; ?>.setRange(  <?php echo $rangeMin; ?> , <?php echo $rangeMax; ?> , <?php echo ($chartType/2); ?>  );
					<?php } ?>


					<?php 
					if ($logfileStatus == true) { 

						$errorMessage = 'No logfile data to generate charts for module ' . $module . ' check your logger';
						?>
						charts.<?php echo $chart->chart_function; ?>.setLabel("<?php echo $errorMessage; ?>");
					<?php } ?>

					//initalize chart here
					charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');

				<?php } ?>

			})

		})();

	//console.log('end');

	</script>


	<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR" style="right: 22px;"></div>
	<div class="clearfix"></div>
	<div id="<?php echo $chart->id; ?>" style="height: 160px;" class="chart-holder"></div>





