
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

/*
 * $chartModules is passed over by calling function in module and is used to track multiple modules in chart
 * more than 1 in chartModules means multiple charts in the segment so we include js files just once
 * and make calls to functions that need to be loaded or already loaded here
 */
?>

<?php


//var_dump( $chartData['chart']['dataset'] ) ;
//var_dump( $chartData['chart']['dataset_labels'] ) ;

?>

	<!--
	Fist function here builds the chart data object to send over to be charted
	-->

	<script type="text/javascript">
	(function () {
	
		<?php if ( $chartModules > 1) { ?>
		charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
		<?php }?>

			//core chart data here
			//chart_Data[0] to carry core chart info
			//chart_Data[1]+ to carry charing info
			var chart_data = new Array();
			var chart_info = new Array();

			//primary dataset includes ymin and ymax
			<?php 
			if ( isset($chartData['chart']['dataset'][0])  ) { ?>
				 chart_info = {

					//first dataset carries chart info as well
					//need to change this
					ymin: <?php echo $chartData['chart']['ymin']; ?>,
					ymax: <?php echo $chartData['chart']['ymax']; ?>
					<?php if ($width) echo ', chartwidth: ' . $width .',';  ?>
					<?php if ($height) echo 'chartheight: ' . $height;  ?>
					//defualts are chartwidth: 530, chartheight: 160
				};
			<?php } ?>

			//used for primary dataset
			<?php 
			if ( isset($chartData['chart']['dataset'][0])  ) { ?>
				 chart_data[0] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][0]; ?>',
					data: <?php echo $chartData['chart']['dataset'][0]; ?>
				};
			<?php } ?>


			//used for primary overload
			<?php 
			if (    isset($chartData['chart']['dataset'][1])   ) {  
			?>
				 chart_data[1] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][1]; ?>',
					data: <?php echo $chartData['chart']['dataset'][1]; ?>
				};
			<?php } ?>

			//used for secondary overloads
			<?php 
			if ( isset($chartData['chart']['dataset'][2])  ) { 	
			?>
				 chart_data[2] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][2]; ?>',
					data: <?php echo $chartData['chart']['dataset'][2]; ?>
				};
			<?php } ?>

			//used for swap in memory moudle
			<?php 
			if ( isset($chartData['chart']['dataset'][3])  ) { 
			?>
				 chart_data[3] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][3]; ?>',
					data: <?php echo $chartData['chart']['dataset'][3]; ?>
				};
			<?php } ?> 

			<?php 
			if ( isset($chartData['chart']['dataset'][4])  ) { 
			?>
				 chart_data[4] = {
					label: '<?php echo $chartData['chart']['dataset_labels'][4]; ?>',
					data: <?php echo $chartData['chart']['dataset'][4]; ?>
				};
			<?php } ?> 

			//great for debugging! sends entire array to console for inspection
			//console.info(chart_data); 

			//
			// This function calls the charts flot javascript code to render out chart
			//

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

				elseif ($chartModules > 1) 

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
