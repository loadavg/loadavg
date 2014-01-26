<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network charts
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
<div class="well">
	<script type="text/javascript" src="assets/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
	<h4>Network</h4>
	<div class="widget widget-2 widget-tabs widget-tabs-2">
		<div class="widget-head">
			<ul id="network-tabs">
				<?php $i = 0; foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) { $i++;?>
				<li><a class="glyphicons cardio" href="#" data-target="#network_<?php echo $interface; ?>" data-toggle="tab"><i></i>Interface <?php echo $interface; ?></a></li>
				<?php } ?>				
			</ul>
		</div>
		<div class="widget-body">
			<div class="tab-content">
				<script>
				$(function() {
					$('#network-tabs [data-toggle="tab"]').on('click', function(){
						$(this).tab("show");
					});
					$('#network-tabs [data-toggle="tab"]').on('shown', function (e) {
						$($(this).attr('data-target')).find('[data-toggle="tab"]:first').tab("show");
					});
					$('.btn-group [data-toggle="tab"]').on('shown', function (e) {
						$('.btn-group [data-toggle="tab"]').removeClass('active');
						$(this).addClass('active');
					});
					setTimeout(function(){
						$($('#network-tabs [data-toggle="tab"]').get(0)).tab("show");
					}, 0);
				})
				</script>
				<?php $i = 0; foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) { $i++; ?>
				<div class="tab-pane" id="network_<?php echo $interface; ?>">
					<div class="clearfix" style="clear: both;"></div>
					<div class="tab-content">
						<?php
						$j = 0;
						foreach ( $charts['args'] as $chart ) {
							$j++;
							$chart = json_decode($chart);
							
							$chart->id = 'chart_network_' . $interface . '_' . $chart->type;
							//$chart->id = $chart->id . '_' . $interface;
							$this->logfile = $logdir . sprintf($chart->logfile, date('Y-m-d'), $interface);
							if ( file_exists( $this->logfile )) {
								$caller = $chart->function;
								$stuff = $this->$caller();
							?>
							<div class="tab-pane" id="network_<?php echo $interface; ?>_<?php echo $chart->type; ?>">
								<div class="row-fluid">
									<div class="span3 right">
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
										<div class="btn-group separator top">
											<button data-toggle="tab" data-target="#network_<?php echo $interface; ?>_transfer" class="btn btn-small btn-default">Transfer</button>
											<button data-toggle="tab" data-target="#network_<?php echo $interface; ?>_received" class="btn btn-small btn-default">Received</button>
										</div>
									</div>
									<div class="<?php echo ( @$stuff['chart']['mean'] ) ? 'span8' : 'span9'; ?>">
									
									<script type="text/javascript">
									(function () {
										<?php //if ( $j > 1) { ?>
										charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
										<?php //}?>
										var chart_data = {
											div_id: '<?php echo $chart->id; ?>',
											label: '<?php echo $chart->label; ?>',
											data: <?php echo $stuff['chart']['chart_data']; ?>,
											ymin: <?php echo $stuff['chart']['ymin']; ?>,
											ymax: <?php echo $stuff['chart']['ymax']; ?>
										},{
											label: 'Overload',
											data: <?php echo $stuff['chart']['chart_data_over']; ?>,
											bars: {
									            show: true,
									            barWidth: 1
									        }
										};

										$(function () {
											<?php if ( $j == 1) { ?>
											
											$('[data-target="#network_<?php echo $interface; ?>_<?php echo $chart->type; ?>"]').on('shown', function (e) {
												charts.<?php echo $chart->chart_function; ?>.setData(chart_data);
												charts.<?php echo $chart->chart_function; ?>.init(chart_data.div_id);
											});
											<?php } elseif ($j > 1) { ?>
											
											$('[data-target="#network_<?php echo $interface; ?>_<?php echo $chart->type; ?>"]').on('shown', function (e) {
												charts.<?php echo $chart->id; ?>.setData(chart_data);
												charts.<?php echo $chart->id; ?>.init(chart_data.div_id);
											});
											<?php } ?>

											<?php if ( @$stuff['chart']['mean'] ) { ?>
                                        // Separate chart for mean value display stacked bar chart
                                        var options =  {
                                                grid: {
                                                        show: true,
                                                        color: "#8ec657",
                                                        axisMargin: 0,
                                                        borderWidth: 1,
                                                        hoverable: true,
                                                        autoHighlight: true,
                                                        borderColor: "#DDD",
                                                        backgroundColor : "transparent"
                                                },
                                                series: {
                                                        bars: {
                                                                show: true, barWidth: 0.6,
                                                                fillColor: {colors:[{opacity: 1},{opacity: 1}]},
                                                                align: "center"
                                                        },
                                                        stack: 0
                                                },
                                                xaxis: {show: false, min: 1},
                                                yaxis:{show:true, max: <?php echo $stuff['chart']['ymax']; ?>, min: <?php echo $stuff['chart']['ymin']; ?>},
                                                legend: { show: false }
                                         };
                                				         $.plot($("#minmax_<?php echo $chart->id; ?>"),[[[1, <?php echo $stuff['chart']['mean']; ?>]]],options);
				                                		         <?php } ?>
											
										})
									})();
									</script>

									<div id="<?php echo $chart->id; ?>" style="height: 160px;"></div>
									
									</div>
									<?php if ( @$stuff['chart']['mean'] ) { ?>
							                <div class="span1 center">
							                        <div id="minmax_<?php echo $chart->id; ?>" style="width:40px;height:160px;"></div>
							                </div>
							                <?php } ?>
								<?php
								} else {
									?><div class="center strong">No logfile to dipslay data from</div><?php	
								}
								?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
