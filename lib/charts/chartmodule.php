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

	$tableStyle = ( isset( $chartData['chart']['chart_avg'] )     ) ? 'span8' : 'span9'; 

	//dirty hack
	if ($drawAvg == false)
		$tableStyle = 'span9';

	//echo 'tableStyle ' . $tableStyle . '<br>';
?>

	<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>	

	        <!--  Now we render the chart -->

	        <!-- this sections renders out chart left legend from .ini file -->
			<td width="26%" align="right" style="padding-right: 15px;">

				<?php include( HOME_PATH . '/lib/charts/legend.php'); ?>
				
			</td>

	        <!-- this sections renders main chart area -->
			<td class="<?php echo $tableStyle ?> innerT"> 
				
	       		<!-- $chartModules is passed over by calling function in module and is used to track multiple modules in chart
	       		     more than 1 in chartModules means multiple charts in the segment so we include js files just once
	       		-->
				<?php 
				if ( $chartModules == 1) { ?>
				<script type="text/javascript" src= "<?php echo SCRIPT_ROOT; ?>lib/modules/<?php echo $module; ?>/<?php echo strtolower($module); ?>.js"></script>
				<?php }	

				//draw chart
				include( HOME_PATH . '/lib/charts/chartcore.php');
				?>

			</td>

			<?php 
	        // Now draw separate chart for mean value display stacked bar chart
	        // cool as we can also do pie charts etc using different flags
			if ( isset($chartData['chart']['chart_avg']) && ($drawAvg == true)  ) {  

				//$chartMode = $chartData['chart']['chart_avg'];
				$chartMode = (isset($chartData['chart']['chart_avg']) ? $chartData['chart']['chart_avg'] : null);

            ?> 
            <td class="span1 hidden-phone" style="height: 170px">
            <?php
				switch ( $chartMode) {

					case "avg": 	include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;

					case "stack": 	include( HOME_PATH . '/lib/charts/chartstack.php');				
									break;

					default: 		include( HOME_PATH . '/lib/charts/chartavg.php');				
									break;				
				}
			?> 
			</td> 
			<?php
			} 
			?> 
			
		</tr>
	</table>
