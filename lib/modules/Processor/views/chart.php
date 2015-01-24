<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* MySQL charts derived from views/chart.php
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

/*
args[] = '{"id":"processor_load","logfile":"processor_%s.log","function":"getUsageData", "chart_function":"processor_load", "label":"Procssor Usage"}'
*/
?>



<?php

	if ( !isset($chartData) || $chartData == false || $logfileStatus == false ) {

		$chartData = $this->parseInfo($moduleSettings['info']['line'], null, $module); // module was __CLASS__
		$chartData['chart'] = $this->getEmptyChart();
	}

	$moduleCollapse = $moduleCollapseStatus = $modulePosition = "";

	$this->getUIcookie($moduleCollapse, $moduleCollapseStatus, $module); 

?>


<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>

<!-- draw charts for each interface 

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false">
	

	<div class="widget-head"><h4 class="heading"><strong>Processor Usage</strong></h4></div>
	<div class="widget-body collapse in" style="height: auto;">



<div class="accordion" id="accordion<?php echo $module;?>"  data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
-->
<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
  	
		<div class="accordion-heading"> 

			<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
				Processor Usage				
			</a>
		</div>

		<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">

		<div class="accordion-inner">

		<?php
		$j = 0;

		//show or skip last chart
		//$showqueries = null;
		//$showqueries = $moduleSettings['settings']['show_queries'];

		/* draw charts for each subchart as per args will be Transmit and receive */

		// showqueries = true show summary chart
		// showqueries = false show all charts

		foreach ( $charts['args'] as $chart ) 
		{
			$j++;

			//this is to switch between differet chart modes
			//if set to show queries skip charts 1 and 2
			//if ( ( ( $showqueries == "true" ) && ( $j == 1 || $j == 2) ) || ( $showqueries == "false" && $j == 3) )
			//	continue;

			$chart = json_decode($chart);

			//get data range we are looking at - need to do some validation in this routine
			$dateRange = loadModules::$date_range;
			//$dateRange = $this->getDateRange();

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple log files
			$this->setLogFile($chart->logfile,  $dateRange, $module );

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

			if (!empty($this->logfile)) {
			//if ( file_exists( $this->logfile[0][0] )) {
				$i++;				
				$logfileStatus = true;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$chartData = $this->$caller( $functionSettings );
				} else {
					$chartData = $this->$caller(  );
				}

			} else {

				//no log file so draw empty charts
				$i++;				
				$logfileStatus = false;

			}

			?>


		<?php

			//if there is no logfile or error from the caller (chartData is false) 
			//then we just build empty charts
			if ( !isset($chartData) || $chartData == false || $logfileStatus == false ) {

				$chartData = $this->parseInfo($moduleSettings['info']['line'], null, $module); // module was __CLASS__

				$chartData['chart'] = $this->getEmptyChart();
			}

		?>


		<!-- <div class="row-fluid"> -->
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<!-- <div class="span3 right"> -->
				<td width="26%" align="right" style="padding-right: 15px">
					<?php if ( $chartData ) { ?>
					<ul class="unstyled">
						<?php
						foreach ($chartData['info']['line'] as $line) {
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
				<td class="<?php echo ( isset( $chartData['chart']['mean'] ) ) ? 'span8' : 'span9'; ?> innerT"> 
					
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
				if ( isset($chartData['chart']['mean']) ) {  
            		?> <td class="span1 hidden-phone" style="height: 170px">

					<?php include( HOME_PATH . '/lib/charts/chartavg.php');
										?> </td> <?php
				} 
				?> 

			</tr>
		</table>

		<?php } ?>

		</div> <!-- // Accordion inner end -->

		</div> <!-- // Accordion category end -->
	
</div> <!-- // Accordion end -->

<div id="separator" class="separator bottom"></div>

