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
?>

<?php

	//get status of interface ebefore rendering
	$moduleCollapse = "accordion-body collapse in";
	$moduleCollapseStatus = "true";

	if ($cookies) {
		$this->getUIcookie($moduleCollapse, $moduleCollapseStatus, $module); 
	}

?>

<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
	<div class="accordion-heading"> 		
		<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
			<?php echo $moduleSettings['module']['name']; //$chart->label; ?>				
		</a>
	</div>
	<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">
		<div class="accordion-inner">

			<?php

			//show or skip last chart
			$showqueries = $moduleSettings['settings']['show_queries'];

			$chartModules = 0;
			foreach ( $charts['args'] as $chart ) {
				$chartModules++;

				//this is to switch between differet chart modes
				//if set to show queries skip charts 1 and 2
				if ( ( ( $showqueries == "true" ) && ( $chartModules == 1 || $chartModules == 2) ) 
					|| ( $showqueries == "false" && $chartModules == 3) )
					continue;

				$chart = json_decode($chart);

				//get the log file NAME or names when there is a range
				//returns multiple files when multiple log files
				$class->setLogFile($chart->logfile,  $dateRange, $module );

				//get data needed to send to template to render chart
				$chartData = $class->getChartRenderData( $chart, $functionSettings, $module );

				include( HOME_PATH . '/lib/charts/chartmodule.php'); 

			
				} ?>
			
		</div> <!-- // Accordion inner end -->
	</div> <!-- // Accordion category end -->
</div> <!-- // Accordion end -->

<div class="separator bottom"></div>

