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

<?php

	//if there is no logfile or error from the caller (stuff is false) 
	//then we just build empty charts
	if ($stuff == false || $logfileStatus == true ) {

		$stuff = $this->parseInfo($moduleSettings['info']['line'], null, $module); // module was __CLASS__

		////////////////////////////////////////////////////////////////////////////
		//this data can be created in charts.php really if $datastring is null ?
		//or add a flag to the array for chartdata here...
		$stuff['chart'] = array(
			'chart_format' => 'line',
			'ymin' => 0,
			'ymax' => 1,
			'xmin' => date("Y/m/d 00:00:01"),
			'xmax' => date("Y/m/d 23:59:59"),
			'mean' => 0,
			'chart_data' => "[[0, '0.00']]"
		);
	
	}

?>

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">


	<?php if ( $chart ) { ?>
	<div class="widget-head"><h4 class="heading"><?php echo $chart->label; ?></h4></div>
	<div class="widget-body collapse in" style="height: auto;">
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>	

	        <!-- 
	          Now we render the chart 
	      	-->

	        <!-- this sections renders out chart left legend from .ini file -->

			<td width="26%" align="right" style="padding-right: 15px;">
				<?php if ( $stuff ) { ?>				
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
				<?php } ?>				
			</td>

	        <!-- this sections renders main chart area -->

			<!-- used to change wisth if we have the Avg chart on right or not -->
			<td class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT"> 
				
       		<!-- $i is passed over by calling function in module and is used to track multiple modules in chart
       		     more than 1 in i means multiple charts in the segment so we include  js files just once
       		-->
			<?php if ( $i == 1) { ?>
			<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
			<?php }	?>


				<!-- no $stuff means no log data  -->
				<?php if ( $stuff  ) {  ?>

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


		                // render the chart using the chart.js data
		                // until we can figure out how to render error message on top of chart we override the label  :)

						$(function () {
							<?php if ( $i == 1) { ?>
							charts.<?php echo $chart->chart_function; ?>.setData(chart_data);
							<?php if ($logfileStatus == true) { 
								$errorMessage = 'No logfile data to generate charts for module ' . $module . ' check your logger';
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


		                    // Now draw separate chart for mean value display stacked bar chart
		                    // cool as we can also do pie charts etc using different flags

							<?php 
								if ( isset($stuff['chart']['mean']) ) {   
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


					<?php if ($logfileStatus) { // need to implement this method for overlay errors on charts ?>
					<!--
					
					NOTE: Enable canvas: true, in chart js options
					<script type="text/javascript">
					var c=document.getElementsByTagName("canvas")[0];
					var canvas=c.getContext("2d");
					var cx = c.width / 2;
					var text="Flot chart title";
					canvas.font="bold 20px sans-serif";
					canvas.textAlign = 'center';
					canvas.fillText(text,cx,75);	
					</script>

					<div class="alert alert-danger">No logfile data to generate charts from for module <?php echo $module; ?></div>
					-->
					<?php } ?>

				<?php } else { ?>
					<div class="alert alert-danger">No logfile data to generate charts from for module <?php echo $module; ?></div>
				<?php } ?>

			</td>

			<?php if ( isset($stuff['chart']['mean']) ) { ?>
            <td class="span1 hidden-phone" style="height: 170px">
                <div id="minmax_<?php echo $chart->id; ?>" style="width:35px;height:140px;top: 18px;right: 5px;"></div>
                <div style="position: relative; top: 13px;font-size: 11px;left: 3px;">Avg</div>
        	</td>

            <?php } // closes main if stuff at top ?> 

		</tr>
	</table>



	<?php } // closes main if chart at top ?>



	</div> <!-- // Accordion end -->
</div> <!-- // Accordion group -->

<div class="separator bottom"></div>
