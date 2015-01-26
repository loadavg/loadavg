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


?>

<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
	<div class="accordion-heading"> 
		<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
			Processor Usage				
		</a>
	</div>
	<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">
		<div class="accordion-inner">

			<?php

			//get data range we are looking at - need to do some validation in this routine
			$dateRange = loadModules::$date_range;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) 
				&& isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );


			$chartModules = 0;
			foreach ( $charts['args'] as $chart ) {
				$chartModules++;

				$chart = json_decode($chart);

				//get the log file NAME or names when there is a range
				//returns multiple files when multiple log files
				$this->setLogFile($chart->logfile,  $dateRange, $module );

				//get data needed to send to template to render chart
				$chartData = $this->getChartRenderData( $chart, $functionSettings, $module );

				?>

				<?php	include( HOME_PATH . '/lib/charts/chartmodule.php'); ?>

			<?php } ?>

			</div> <!-- // Accordion inner end -->
		</div> <!-- // Accordion category end -->
	
</div> <!-- // Accordion end -->

<div id="separator" class="separator bottom"></div>

