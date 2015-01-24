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

		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>	

	        <!--  Now we render the chart -->

	        <!-- this sections renders out chart left legend from .ini file -->

			<td width="26%" align="right" style="padding-right: 15px;">
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
			</td>

	        <!-- this sections renders main chart area -->

			<td class="<?php echo $tableStyle ?> innerT"> 
				

	       		<!-- $i is passed over by calling function in module and is used to track multiple modules in chart
	       		     more than 1 in i means multiple charts in the segment so we include js files just once
	       		-->
				<?php 
				//$i is never used unless in group charts!
				//and all group chart modules have thier own version of this file it seems
				echo ' and $i is ' . $i;

				if ( $i == 1) { ?>
				<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
				<?php }	

				//draw chart
				include( HOME_PATH . '/lib/charts/chartcore.php');
				?>

			</td>

			<?php 
	        // Now draw separate chart for mean value display stacked bar chart
	        // cool as we can also do pie charts etc using different flags
			if ( isset($chartData['chart']['chart_avg']) && ($dontDrawAvg == false)  ) {  

				//$chartMode = $chartData['chart']['chart_avg'];
				$chartMode = (isset($chartData['chart']['chart_avg']) ? $chartData['chart']['chart_avg'] : null);

            ?> <td class="span1 hidden-phone" style="height: 170px">
            <?php
				switch ( $chartMode) {

					case "avg": 	include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;

					case "stack": 	include( HOME_PATH . '/lib/charts/chartstack.php');				
									break;

					default: 		include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;				
				}
			?> </td> <?php
			} 
			?> 
			</tr>
		</table>

		</div> <!-- // Accordion inner end -->
	</div> <!-- // Accordion category end -->
</div> <!-- // Accordion end -->


<div id="separator" class="separator bottom"></div>
