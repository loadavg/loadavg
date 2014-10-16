<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Charts module interface
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">
	<?php if ( $chart ) { ?>
	<div class="widget-head"><h4 class="heading"><?php echo $chart->label; ?></h4></div>
	<div class="widget-body collapse in" style="height: auto;">
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>	

			<!-- check to see if therre is log data for the chart first -->
			<?php
			if ( isset( $no_logfile ) && $no_logfile ) {
				?>
				<td><div class="alert alert-danger">No logfile to display data from</div></td>
			<?php } else { ?>

	        <!-- Now we render the chart -->

			<td width="26%" align="right" style="padding-right: 15px;">
				<ul class="unstyled">
					<?php
					foreach ($stuff['info']['line'] as $line) {
						switch ($line['type']) {
							case "file":
								echo '<li>'; include $line['file']; echo '</li>';
								break;
							case "line":
								echo '<li>' . $line['formatted_line'] . '</li>';
						}
					}
					?>
				</ul>
			</td>
			<td class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT">
				<?php if ( $i == 1) { 

					//print_r ($stuff);
					//echo 'mean:'; print_r ($stuff['chart']['mean']); echo '<br>';
					//echo 'min :'; print_r ($stuff['chart']['ymin']); echo '<br>';
					//echo 'max :'; print_r ($stuff['chart']['ymax']); echo '<br>';

					?>

				<!-- parse_ini_file(APP_PATH . '/config/' . self::$settings_ini, true) -->
				
				<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
				

				<?php }	?>
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


					// temp code to figure out whats going on here

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



						//used for secondary overlaods
						<?php if ( isset($stuff['chart']['chart_data_over']) ) { ?>
							var d2 = {
								label: 'Overload',
								data: <?php echo $stuff['chart']['chart_data_over']; ?>
							};
							chart_data.push(d2);
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


						//used for secondary overlaods
						<?php if ( isset($stuff['chart']['chart_data_over_2']) ) { ?>
							var d3 = {
								label: 'Secondary Overload',
								data: <?php echo $stuff['chart']['chart_data_over_2']; ?>
							};
							chart_data.push(d3);
						<?php } ?>


					<?php } ?>

	                // draw chart

					$(function () {
						<?php if ( $i == 1) { ?>
						charts.<?php echo $chart->chart_function; ?>.setData(chart_data);
						charts.<?php echo $chart->chart_function; ?>.init('<?php echo $chart->id; ?>');
						<?php } elseif ($i > 1) { ?>
						charts.<?php echo $chart->id; ?>.setData(chart_data);
						charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');
						<?php } ?>

	                    // Now draw separate chart for mean value display stacked bar chart
	                    
	                    // Dirty hack as mean = 0 is breaking charts 
	                    // when apache has no log data in log file or log valueas are all set to zero
	                    // only works if MEAN is set in charts ini file
	                    // cool as we can also do pie charts etc using different flags

						<?php 
							//if ( isset($stuff['chart']['mean']) ) {  
							if (   (  isset($stuff['chart']['mean']) )  &&  ( $stuff['chart']['mean'] != 0   )       ) {  

						?>

	                    var options =  {
	                        grid: {
                                show: true,
                                color: "#efefef",
                                axisMargin: 0,
                                borderWidth: 1,
                                hoverable: true,
                                autoHighlight: true,
                                borderColor: "#797979",
                                backgroundColor : "#353535"
	                        },
                            series: {
    	                        bars: {
            		                show: true,
                                    fillColor: {colors:[{opacity: 1},{opacity: 1}]},
                                    align: "center"
                                },
                                color: "#26ADE4",
                                stack: 0
                            },
                            width: 0.5,
                            xaxis: {
                            	show: false, 
                            	min: 1
                            },
                            yaxis: {
                            	show: false, 
                            	max: <?php echo $stuff['chart']['ymax']; ?>, 
                            	min: <?php echo $stuff['chart']['ymin'];?>, 
                            	reserveSpace: false, 
                            	labelWidth: 15
                            },
                            legend: { 
                            	show: false 
                            },
							tooltip: true,

							tooltipOpts: {

								content: function(label, xval, yval, flotItem) {
									return "Avg " + parseFloat(yval).toFixed(4);
						    	},

								shifts: {
									x: 20,
									y: -20
								},
								defaultTheme: false
							}

	                     };
	                     
	                     $("#minmax_<?php echo $chart->id; ?>").width(35).height(140);
	                     $.plot($("#minmax_<?php echo $chart->id; ?>"),[[[1, <?php echo $stuff['chart']['mean']; ?>]]],options);

	                     <?php } ?>

					})
				})();
				</script>
					


<?php
/*
					echo 'have_swap :'; echo $have_swap; echo '<br>';
					echo 'have over :'; echo $have_over; echo '<br>';
*/
?>

				<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR" style="right: 22px;"></div>
				<div class="clearfix"></div>
				<div id="<?php echo $chart->id; ?>" style="height: 160px;" class="chart-holder"></div>

			</td>
			<?php if ( @$stuff['chart']['mean'] ) { ?>
            <td class="span1 hidden-phone" style="height: 170px">
                <div id="minmax_<?php echo $chart->id; ?>" style="width:35px;height:140px;top: 18px;right: 5px;"></div>
                <div style="position: relative; top: 13px;font-size: 11px;left: 3px;">Avg</div>
        	</td>
            <?php } ?>
            <?php } ?>
		</tr>
	</table>
		<?php } ?>
	</div> <!-- // Accordion end -->
</div> <!-- // Accordion group -->

<div class="separator bottom"></div>
