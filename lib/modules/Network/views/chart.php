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

<!-- not sure why we do this dynamically as this is only for network module -->

<!--
<script type="text/javascript" src="lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
-->

<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>

<!-- loop through each interface -->

<?php
$i = 0; 
foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) { 
	$i++;
?>

<!-- draw charts for each interface -->

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">
	<div class="widget-head">
		<h4 class="heading"><strong>Network</strong> Interface: <?php echo $interface; ?></h4>
	</div>
	<div class="widget-body collapse in" style="height: auto;">
		<?php
		$j = 0;
		foreach ( $charts['args'] as $chart ) {
			$j++;
			$chart = json_decode($chart);
		

			// note that this will probably need to be fixed for PERIODS
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date, $interface);

			//this was old code here
			//$this->logfile = $logdir . sprintf($chart->logfile, date('Y-m-d'), $interface);

			$chart->id = 'chart_network_' . $interface . '_' . $chart->type;

			?>
		<!-- <div class="row-fluid"> -->
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
			<?php
			$caller = $chart->function;
			$stuff = $this->$caller();
			?>
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
			<!-- <div class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?>"> -->
			<td  class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT">
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
						// $('[data-target="#network_<?php echo $interface; ?>_<?php echo $chart->type; ?>"]').on('shown', function (e) {
							charts.<?php echo $chart->id; ?>.setData(chart_data);
							charts.<?php echo $chart->id; ?>.init('<?php echo $chart->id; ?>');
						// });
						
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
                                yaxis:{show:false, max: '<?php echo $stuff["chart"]["ymax"]; ?>', min: '<?php echo $stuff["chart"]["ymin"]; ?>'},
                                legend: { show: false }
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
			<?php if ( @$stuff['chart']['mean'] ) { ?>
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
<!--
<div class="separator bottom"></div>
-->
<?php } ?>
