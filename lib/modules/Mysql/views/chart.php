<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network charts derived from views/chart.php
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

<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>

<!-- draw charts for each interface -->

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">
	<div class="widget-head"><h4 class="heading"><strong>MySql Usage</strong></h4></div>
	<div class="widget-body collapse in" style="height: auto;">
		<?php
		$j = 0;

		//show or skip last chart
		$showqueries = $moduleSettings['settings']['show_queries'];

		/* draw charts for each subchart as per args will be Transmit and receive */

		foreach ( $charts['args'] as $chart ) {
			$j++;

			//this is to skip the 3rd chart which is queries
			//bit of a hack could be made nicer
			if ( $showqueries == "false" && $j == 3)
					continue;

			$chart = json_decode($chart);

			// note that this will probably need to be fixed for PERIODS
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date);

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

			if ( file_exists( $this->logfile )) {
				$i++;				
				$logfileStatus = false;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$stuff = $this->$caller( $logfileStatus, $functionSettings );
				} else {
					$stuff = $this->$caller( $logfileStatus );
				}

			} else {
				//no log file so draw empty charts
				$i++;				
				$logfileStatus = true;
			}

			?>

		<!-- <div class="row-fluid"> -->
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
			<!-- <div class="span3 right"> -->
			<td width="26%" align="right" style="padding-right: 15px">
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
			<!-- </div> -->
			</td>

			<td  class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT">
				
				<!-- no $stuff means no log files mate -->

				<?php if ( $stuff ) { ?>
				<script type="text/javascript">
				(function () {
					charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
					
					var d1 = {
						label: '<?php echo $chart->label; ?>',
						data: <?php echo $stuff['chart']['chart_data']; ?>,
						ymin: '<?php echo $stuff["chart"]["ymin"]; ?>',
						ymax: '<?php echo $stuff["chart"]["ymax"]; ?>'
					};

					<?php if ( !isset( $stuff['chart']['chart_data_over'] ) || $stuff['chart']['chart_data_over'] == null ) { ?>

					var chart_data = d1;
					
					<?php } elseif (strlen($stuff['chart']['chart_data_over']) > 1) { ?>
						
					var chart_data = new Array();
					
					var d2 = {
						label: 'Overload',
						data: <?php echo $stuff['chart']['chart_data_over']; ?>
					};
					chart_data.push(d1);
					chart_data.push(d2);
					<?php } ?>

					$(function () {




							charts.<?php echo $chart->id; ?>.setData(chart_data);

							<?php if ($logfileStatus == true) { 
								$errorMessage = 'No logfile data to generate charts for module ' . $module . ' check your logger';
								?>
								charts.<?php echo $chart->id; ?>.setLabel("<?php echo $errorMessage; ?>");
							<?php } ?>


							charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');

						
							<?php 
								if ( isset($stuff['chart']['mean']) ) {  
    						?>

                            // Separate chart for mean value display stacked bar chart
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
                                        show: true, barWidth: 0.6,
                                        fillColor: {colors:[{opacity: 1},{opacity: 1}]},
                                        align: "center"
                                    },
                                    color: "#26ADE4",
                                    stack: 0,
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
				<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR" style="right: 23px;"></div>
				<div class="clearfix"></div>
				<div id="<?php echo $chart->id; ?>" style="height: 160px;"></div>

				<?php } else { ?>
				<div class="alert alert-danger">No logfile to display data from, for <?php echo ucwords($chart->type); ?> data interface <?php echo $interface; ?></div>
				<?php } ?>

			<!-- </div> -->
			</td>
			<?php if ( isset($stuff['chart']['mean']) ) { ?>
            <!-- <div class="span1 hidden-phone"> -->
            <td class="span1 hidden-phone" style="height: 170px">
                <div id="minmax_<?php echo $chart->id; ?>" style="width:35px;height:140px;top: 18px;right: 5px;"></div>
                <div style="position: relative; top: 13px;font-size: 11px;">Avg</div>
            <!-- </div> -->
        	</td>
            <?php } ?>
		<!-- </div> -->
			</tr>
		</table>
		<?php } ?>
	</div> <!-- // Accordion end -->
	
</div> <!-- // Accordion group -->

<div class="separator bottom"></div>

