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

<?php

	//if there is no logfile or error from the caller (stuff is false) 
	//then we just build empty charts
	if ( !isset($stuff) || $stuff == false || $logfileStatus == true ) {

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
			'dataset_1_label' => "No Data",
			'dataset_1' => "[[0, '0.00']]"
		);
	
	}

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

			//this is to switch between differet chart modes
			//we can clean this up later on

			//if set to show queries skip charts 1 and 2
			if ( ( $showqueries == "true" ) && ( $j == 1 || $j == 2) )
				continue;

			//if set to not show queries skip chart 3
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

<div class="separator bottom"></div>

