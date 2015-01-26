<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Charts module interface
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

//called by charting modules module to render chart! 
//in lib/modules/Cpu/class.Cpu.php for eg
//really shoudl be a function... instead of located here

?>

<?php
	//used to set the table width for charts when rendered with or without AVG column at right
	//need to move this over to CSS and loose php variables

	//echo 'DrawAvg ' . $dontDrawAvg ;
	$tableStyle = ( isset( $chartData['chart']['chart_avg'] )     ) ? 'span8' : 'span9'; 
	
	//dirty hack
	if ($dontDrawAvg == true)
		$tableStyle = 'span9';
?>

<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
		<div class="accordion-heading"> 
			<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
				<?php echo $chart->label; ?>				
			</a>
		</div>

		<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">
		<div class="accordion-inner">

		<?php 	include( HOME_PATH . '/lib/charts/chartmodule.php'); ?>

		</div> <!-- // Accordion inner end -->
	</div> <!-- // Accordion category end -->
</div> <!-- // Accordion end -->


<div id="separator" class="separator bottom"></div>
