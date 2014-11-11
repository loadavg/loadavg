
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

		var d1 = {
			//label: '<?php echo $chart->label; ?>',
			label: '<?php echo $stuff['chart']['dataset_1_label']; ?>',
			data: <?php echo $stuff['chart']['dataset_1']; ?>,
			ymin: <?php echo $stuff['chart']['ymin']; ?>,
			ymax: <?php echo $stuff['chart']['ymax']; ?>
		};

		<?php 


		//we either have over or we have swap - need to fix this really
		//as swap is over in charting engine
		//swap should be just aanother dataset rendered differently ?
		//but we have scenarios when there is dataset 1 and 2 or 1 and 2 and 3 but not 1 and 3 ?

		//have_over means we have a primary overload
		//chart_data_over
		if ( !isset( $stuff['chart']['dataset_2'] ) || $stuff['chart']['dataset_2'] == null ) 
			$have_over = false;
		else
			$have_over = true;
		

		if ( !isset( $stuff['chart']['dataset_4'] ) || $stuff['chart']['dataset_4'] == null ) 
			$have_swap = false;
		else
			$have_swap = true;		
		?>

		// we work when there is overload and no swap
		// but die when there is swap and no overloard!

		<?php
		if ( !$have_over && !$have_swap  ) { 
			?>

			var chart_data = d1;
		
		<?php } 

		//elseif (strlen($stuff['chart']['chart_data_over']) > 1) { 
		else { 
			?>
			
			// load core chart data here
			var chart_data = new Array();
			chart_data.push(d1);

			//used for primary overload
			<?php if ( isset($stuff['chart']['dataset_2']) ) { ?>

				var d2 = {
					label: '<?php echo $stuff['chart']['dataset_2_label']; ?>',
					data: <?php echo $stuff['chart']['dataset_2']; ?>
				};
				chart_data.push(d2);
			
			<?php } ?>

			//used for secondary overlaods
			<?php if ( isset($stuff['chart']['dataset_3']) ) { ?>
				var d3 = {
					label: '<?php echo $stuff['chart']['dataset_3_label']; ?>',
					data: <?php echo $stuff['chart']['dataset_3']; ?>
				};
				chart_data.push(d3);
			<?php } ?>

			//d3 is shareds! we need to have a data type instead ?
			//or have d4 for swap
			// new swap code
			<?php 
			if ( isset($stuff['chart']['dataset_4']) ) { ?>
				var d3 = {
					label: '<?php echo $stuff['chart']['dataset_4_label']; ?>',
					data: <?php echo $stuff['chart']['dataset_4']; ?>
				};
				chart_data.push(d3);
			<?php } 
			?>

		<?php } ?>


        // render the chart using the chart.js data
        // for error message until we can figure out how to render error message 
        // on top of blank chart we override the label  :)

		$(function () {
			<?php if ( $i == 1) { ?>
			charts.<?php echo $chart->chart_function; ?>.setData(chart_data);
			<?php if ($logfileStatus == true) { 
				$errorMessage = 'No logfile data to generate charts for module ' . $module;
				?>
				charts.<?php echo $chart->chart_function; ?>.setLabel("<?php echo $errorMessage; ?>");
			<?php } ?>

			charts.<?php echo $chart->chart_function; ?>.init('<?php echo $chart->id; ?>');

			<?php } elseif ($i > 1) { ?>

			charts.<?php echo $chart->id; ?>.setData(chart_data);							
			<?php if ($logfileStatus == true) { 
				$errorMessage = 'No logfile data to generate charts for module ' . $module . ' check your logger';
				?>
				charts.<?php echo $chart->chart_function; ?>.setLabel("<?php echo $errorMessage; ?>");
			<?php } ?>

			charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');
			<?php } ?>



		})
	})();
	</script>


	<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR" style="right: 22px;"></div>
	<div class="clearfix"></div>
	<div id="<?php echo $chart->id; ?>" style="height: 160px;" class="chart-holder"></div>





