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

			<td  class="<?php echo ( isset( $stuff['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT">
				
				<!-- no $stuff means no log files mate -->


				<!-- no $stuff means no log data  -->
				<?php if ( $stuff  ) {

					//draw charts 
					include( HOME_PATH . '/lib/chartcore.php');
				
				} else { ?>
					<div class="alert alert-danger">No logfile data to generate charts from for module <?php echo $module; ?></div>
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
<!--
<div class="separator bottom"></div>
-->
<?php } ?>
