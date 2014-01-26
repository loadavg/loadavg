<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Network charts Accordian
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
<div class="well ">
	<h4>Network</h4>
	
	<script type="text/javascript" src="assets/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
	
	<div class="accordion" id="accordion">
		
		<?php 
		$i = 0; 
		foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) { 
			$i++; 
		?>
		<script>
		$(function() {
			$('#collapse-<?php echo $i; ?> [data-toggle="collapse"]').on('click', function(){
				$(this).tab("show");
			});
			$('#collapse-<?php echo $i; ?> [data-toggle="collapse"]').on('shown', function (e) {
				$($(this).attr('data-target')).find('[data-toggle="tab"]:first').tab("show");
			});
			$('.btn-group [data-toggle="tab"]').on('shown', function (e) {
				$('.btn-group [data-toggle="tab"]').removeClass('active');
				$(this).addClass('active');
			});
			setTimeout(function(){
				$($('#collapse-<?php echo $i; ?> [data-toggle="tab"]').get(0)).tab("show");
			}, 0);
		})
		</script>

		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $i; ?>">Interface: <?php echo $interface; ?></a>
			</div>
			<div id="collapse-<?php echo $i; ?>" class="accordion-body collapse <?php if ($i == 1) { ?>in <?php } ?>">
				<div class="tab-content ">
					<?php
					$j = 0;
					foreach ( $charts['args'] as $chart ) {
						$j++;
						$chart = json_decode($chart);
					
						$chart->id = 'chart_network_' . $interface . '_' . $chart->type;
						//$chart->id = $chart->id . '_' . $interface;
						$this->logfile = $logdir . sprintf($chart->logfile, date('Y-m-d'), $interface);
						?>
					<div class="tab-pane" id="network_<?php echo $interface; ?>_<?php echo $chart->type; ?>">
						<div class="row-fluid">
							<?php
							$caller = $chart->function;
							$stuff = $this->$caller();
							?>
							<div class="span3 right">
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
								<div class="btn-group separator top">
									<button data-toggle="tab" data-target="#network_<?php echo $interface; ?>_transfer" class="btn btn-small btn-default">Transfer</button>
									<button data-toggle="tab" data-target="#network_<?php echo $interface; ?>_received" class="btn btn-small btn-default">Received</button>
								</div>
							</div>
							<div class="<?php echo ( @$stuff['chart']['mean'] ) ? 'span8' : 'span9'; ?>">
								<?php if ( $stuff ) { ?>
								<script type="text/javascript">
								(function () {
									charts.<?php echo $chart->id; ?> = $.extend({}, charts.<?php echo $chart->chart_function; ?>);
									
									var d1 = {
										label: '<?php echo $chart->label; ?>',
										data: <?php echo $stuff['chart']['chart_data']; ?>,
										ymin: <?php echo $stuff['chart']['ymin']; ?>,
										ymax: <?php echo $stuff['chart']['ymax']; ?>
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
										$('[data-target="#network_<?php echo $interface; ?>_<?php echo $chart->type; ?>"]').on('shown', function (e) {
											charts.<?php echo $chart->id; ?>.setData(chart_data);
											charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');
										});
										
										<?php if ( @$stuff['chart']['mean'] ) { ?>
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
	                                                stack: 0,
                                                    color: "#8ec657"
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

								<div id="<?php echo $chart->id; ?>_legend" class="pull-right innerLR"></div>
								<div class="clearfix"></div>
								<div id="<?php echo $chart->id; ?>" style="height: 160px;"></div>

								<?php } else { ?>
								<div class="alert alert-danger">No logfile to display data from, for <?php echo ucwords($chart->type); ?> data interface <?php echo $interface; ?></div>
								<?php } ?>
							</div> <!-- End of span / Chart div-->
							<?php if ( $stuff && $stuff['chart']['mean'] ) { ?>
			                <div class="span1 center hidden-phone">
			                    <div id="minmax_<?php echo $chart->id; ?>" style="width:40px;height:160px;"></div>
			                </div>
			                <?php } ?>
			            </div> <!-- End of row-fluid -->
					</div> <!-- End of tab-pane -->
					<?php
					} // End of foreach (tab-pane)
					?>
				</div> <!-- End of tab-content -->
			</div> <!-- End of collapse-* -->
		</div> <!-- End of accordeion-group -->
		<?php
		} // End of Accordion Group
		?>
	</div> <!-- End of accordion -->
</div>

