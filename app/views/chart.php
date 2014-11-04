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
			'chart_data' => "[[0, '0.00']]"
		);
	
	}

?>

<!--
accordion widgets are built using this code here 
NEED HELP setting up remember status of accordions on page reload using cookies
using code to manage accordion state is in common.js
-->

<div class="widget" data-toggle="collapse-widget" data-collapse-closed="false" data-target="#accordion<?php echo $module; ?>">

	<?php if ( $chart ) { ?>
	<div class="widget-head"><h4 class="heading"><?php echo $chart->label; ?></h4></div>
	<div class="widget-body in collapse" style="height: auto;">

		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>	

	        <!--  Now we render the chart -->

	        <!-- this sections renders out chart left legend from .ini file -->

			<td width="26%" align="right" style="padding-right: 15px;">
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
			</td>

	        <!-- this sections renders main chart area -->

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

	<?php } // closes main if chart at top ?>

	</div> <!-- // Accordion end -->
</div> <!-- // Accordion group -->

<div class="separator bottom"></div>
