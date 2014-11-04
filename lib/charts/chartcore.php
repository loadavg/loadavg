
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
			label: '<?php echo $chart->label; ?>',
			data: <?php echo $stuff['chart']['chart_data']; ?>,
			ymin: <?php echo $stuff['chart']['ymin']; ?>,
			ymax: <?php echo $stuff['chart']['ymax']; ?>
		};

		<?php //have_over means we have a primary overload
		if ( !isset( $stuff['chart']['chart_data_over'] ) || $stuff['chart']['chart_data_over'] == null ) 
			$have_over = false;
		else
			$have_over = true;
		

		if ( !isset( $stuff['chart']['chart_data_swap'] ) || $stuff['chart']['chart_data_swap'] == null ) 
			$have_swap = false;
		else
			$have_swap = true;		
		?>

		// we work when there is overload and no swap
		// but die when there is swap and no overloard!

		//if ( !isset( $stuff['chart']['chart_data_over'] ) || $stuff['chart']['chart_data_over'] == null ) { ?>
		<?php
		if ( !$have_over && !$have_swap  ) { 
		//if ( !$have_over  ) { 
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
			<?php if ( isset($stuff['chart']['chart_data_over']) ) { ?>
				var d2 = {
					label: 'Overload',
					data: <?php echo $stuff['chart']['chart_data_over']; ?>
				};
				chart_data.push(d2);
			<?php } ?>

			//used for secondary overlaods
			<?php if ( isset($stuff['chart']['chart_data_over_2']) ) { ?>
				var d3 = {
					label: 'Secondary Overload',
					data: <?php echo $stuff['chart']['chart_data_over_2']; ?>
				};
				chart_data.push(d3);
			<?php } ?>

			// new swap code
			<?php 
			if ( isset($stuff['chart']['chart_data_swap']) ) { ?>
				var d3 = {
					label: 'Swap',
					data: <?php echo $stuff['chart']['chart_data_swap']; ?>
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





