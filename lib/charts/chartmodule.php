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
	$tableStyle = ( isset( $chartData['chart']['chart_avg'] )     ) ? 'span8' : 'span9'; 

	//dirty hack
	if ($drawAvg == false)
		$tableStyle = 'span9';


	//used to hide data on mobile devices
	$hidden = false;
    if ( LoadAvg::$isMobile == true )
    	$hidden = true;

	//echo 'tableStyle ' . $tableStyle . '<br>';
?>

	<table id = "chartTable<?php echo strtok($moduleSettings['module']['name'], " "); ?>" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>	

	        <!--  Now we render the chart -->

	        <!-- this sections renders out chart left legend from .ini file -->
	        <?php if ( $drawLegend ) { ?>

	        <!--
			<td class="hidden-phone hidden-tablet" width="26%" align="right" style="padding-right: 15px;">
			-->
			
			<td class="<?php if ($hidden) echo 'hidden'; ?>" width="26%" align="right" style="padding-right: 15px;">
			
				<?php include( HOME_PATH . '/lib/charts/legend.php'); ?>
				
			</td>
			
			<?php }	?>

	        <!-- this sections renders main chart area -->
			<td id = "chartTd<?php echo strtok($moduleSettings['module']['name'], " "); ?>" width="65%" class="<?php echo $tableStyle ?> innerT" > 
				
				<?php 
				if ( $loadJavascript) { 
				?>
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
            <!--
            <td class="span1 hidden-phone hidden-tablet" style="height: 170px">
            -->

            
            <td class="span1 <?php if ($hidden) echo 'hidden'; ?>" width="10%">
           

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
