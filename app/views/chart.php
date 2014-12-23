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

?>

<?php

	//if there is no logfile or error from the caller (stuff is false) 
	//then we just build empty charts
	if ( !isset($chartData) || $chartData == false || $logfileStatus == false ) {

		$chartData = $this->parseInfo($moduleSettings['info']['line'], null, $module); // module was __CLASS__
		$chartData['chart'] = $this->getEmptyChart();
	}
	
	//read status of accordions from cookies so we can paint screen accordingly
	$moduleCollapse = $moduleCollapseStatus =  "";
	
	$this->getUIcookie($moduleCollapse, $moduleCollapseStatus, $module); 


?>

<div class="accordion" id="accordion<?php echo $module;?>"  data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
  	<div class="accordion-group">

	<?php if ( $chart ) {    // what happens if not chart here ??? ?>

		<div class="accordion-heading"> 
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion<?php echo $module; ?>" href="#category<?php echo $module; ?>">
				<strong><?php echo $chart->label; ?></strong>				
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

			<!-- used to change  if we have the Avg chart on right or not -->
			<td class="<?php echo ( isset( $chartData['chart']['chart_avg'] ) ) ? 'span8' : 'span9'; ?> innerT"> 
				
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
			if ( isset($chartData['chart']['chart_avg']) ) {  

				//$chartMode = $chartData['chart']['chart_avg'];

				$chartMode = (isset($chartData['chart']['chart_avg']) ? $chartData['chart']['chart_avg'] : null);

				switch ( $chartMode) {

					case "avg": 	include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;

					case "stack": 	include( HOME_PATH . '/lib/charts/chartstack.php');				
									break;

					default: 		include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;				}

			} 
			?> 

		</tr>
	</table>

	<?php } // closes main if chart at top ?>

		</div> <!-- // Accordion inner end -->

		</div> <!-- // Accordion category end -->

	</div> <!-- // Accordion group end -->
	
</div> <!-- // Accordion end -->

<div class="separator bottom"></div>
