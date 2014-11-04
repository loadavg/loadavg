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

<!-- loop through each interface -->

<?php

$i = 0; 

foreach (LoadAvg::$_settings->general['network_interface'] as $interface => $value) { 

	$i++;

	//skip disabled interfaces
	if (  !( isset(LoadAvg::$_settings->general['network_interface'][$interface]) 
		&& LoadAvg::$_settings->general['network_interface'][$interface] == "true" ) )
		continue;

?>

<!-- draw charts for each interface -->

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">
	<div class="widget-head"><h4 class="heading"><strong>Network</strong> Interface: <?php echo $interface; ?></h4></div>
	<div class="widget-body collapse in" style="height: auto;">
		<?php
		$j = 0;

		/* draw charts for each subchart as per args will be Transmit and receive */

		foreach ( $charts['args'] as $chart ) {
			$j++;
			$chart = json_decode($chart);
		
			//echo '<pre>CHART</pre>';
			//echo '<pre>';var_dump($chart);echo'</pre>';
			//echo $chart->type;

			// note that this will probably need to be fixed for PERIODS
			$this->logfile = $logdir . sprintf($chart->logfile, self::$current_date, $interface);

			if ( file_exists( $this->logfile )) {
				$logfileStatus = false;
			} else {				
				$logfileStatus = true;
			}

			$chart->id = 'chart_network_' . $interface . '_' . $chart->type;

			$caller = $chart->function;
			$stuff = $this->$caller();

			?>

		<!-- <div class="row-fluid"> -->
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
			<?php

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

			<!-- used to change  if we have the Avg chart on right or not -->
			<td class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT"> 
				
	       		<!-- $i is passed over by calling function in module and is used to track multiple modules in chart
	       		     more than 1 in i means multiple charts in the segment so we include js files just once
	       		-->
				<?php if ( $i == 1) { ?>
				<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
				<?php }	

				//draw chart
				include( HOME_PATH . '/lib/charts/chartcore.php');
				?>

			</td>

			<?php 
	        // Now draw separate chart for mean value display stacked bar chart
	        // cool as we can also do pie charts etc using different flags
			if ( isset($stuff['chart']['mean']) ) {  

				include( HOME_PATH . '/lib/charts/chartavg.php');
			} 
			?> 

			</tr>
		</table>
		<?php } ?>
	</div> <!-- // Accordion end -->
	
</div> <!-- // Accordion group -->
<!--
<div class="separator bottom"></div>
-->
<?php } ?>
