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

	//get status of interface ebefore rendering
	$moduleCollapse = "accordion-body collapse in";
	$moduleCollapseStatus = "true";

	if ($cookies) {
		$this->getUIcookie($moduleCollapse, $moduleCollapseStatus, $module); 
	}
		
?>

<?php

	//loop through each interface as we may have multiple interfaces
	foreach (loadModules::$_settings->general['network_interface'] as $interface => $value) 
	{ 

		//skip disabled interfaces
		if (  !( isset(loadModules::$_settings->general['network_interface'][$interface]) 
			&& loadModules::$_settings->general['network_interface'][$interface] == "true" ) )
			continue;

		//we only have one cookie for all interfaces!!! need to fix this to have separate ones
		//$moduleCollapse = $moduleCollapseStatus  = "";
		//$this->getUIcookie($moduleCollapse, $moduleCollapseStatus, $module); 

?>

		<div id="accordion-<?php echo $module;?>" class="accordion-group" data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
			<div class="accordion-heading"> 
				<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $interface; ?>" >
				Network Interface: <?php echo $interface; ?>
				</a>
			</div>

			<div id="category<?php echo $interface; ?>" class="<?php echo $moduleCollapse;?>">
				<div class="accordion-inner">
					<?php
					//get data range we are looking at - need to do some validation in this routine
					//$dateRange = loadModules::$date_range;
					//$dateRange = $this->getDateRange();

					//check if function takes settings via GET url_args 
					$functionSettings =( (isset($moduleSettings['module']['url_args']) 
						&& isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

					//already passed over
        			//$class = self::$_classes[$module];

					/* draw charts for each subchart as per args will be Transmit and receive */
					$chartModules = 0;
					foreach ( $charts['args'] as $chart ) {
						$chartModules++;

						$chart = json_decode($chart);

						//get the log file NAME or names when there is a range
						//returns multiple files when multiple log files
						$class->setLogFile($chart->logfile,  $dateRange, $module, $interface );


						//get data needed to send to template to render chart
						$chartData = $class->getChartRenderData( $chart, $functionSettings, $module );			


						////////////////////////////////////////////////////////////////
						//net interfaces have differnt id's ?
						$chart->id = 'chart_network_' . $interface . '_' . $chart->type;

						include( HOME_PATH . '/lib/charts/chartmodule.php');

					} 
					?>
				</div> <!-- // Accordion inner end -->
			</div> <!-- // Accordion category end -->
		</div> <!-- // Accordion end -->

	<div class="separator bottom"></div>

<?php } ?>
