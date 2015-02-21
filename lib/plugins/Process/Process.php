<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Server module interface
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
//open logged in
if ( $loadavg->isLoggedIn() )
{ 
?>

<?php

	//used for callbacks to main plugin to select timestamp to display
	//we are also passing back chart time

	$timeStamp = false;
	if (isset($_GET['timestamp'])) {
		$timeStamp = $_GET['timestamp'];
	}

	$chartTime = false;
	if (isset($_GET['charttime'])) {
		$chartTime = $_GET['charttime'];
	}

	//get plugin class
	$process = LoadPlugins::$_classes['Process'];

	//get the range of dates to be charted from the UI and 
	//set the date range to be charted in the plugin
	$range = $loadavg->getDateRange();
	$loadModules->setDateRange($range);

    //set url for callback
    //do this better - get current url and add timestamp to end
    $callback = 'public/index.php?page=Process&timestamp=';
    //$callback = 'public/index.php?page=Process&timestamp=%scharttime=%s';
?>


	<div class="well lh70-style">
	    <b>Process Data</b>
	    <div class="pull-right">
		<?php echo $process->getData("uptime"); ?>
	    </div>
	</div>

<div class="innerAll">

    <div id="accordion" class="accordion">	
	<?php
	    //render chart
	    $loadModules->renderChart("Cpu", false, false, false, $callback, 770 );
	?>
	</div>

	<!--
	widget stytles can be found here but need cleaning up
	http://demo.mosaicpro.biz/smashingadmin/php/index.php?lang=en&page=widgets
	-->

	<?php

	// get and parse process data here
	// to view on your system in console:
	// ps -Ao %cpu,%mem,pid,user,comm,args | sort -r -k1 | less

	//grab process data from log file for timeStamp
	$data = false;
	$gotLog = false;

	if ($timeStamp) {
		$data = $process->fetchProcessLogData($timeStamp);		
	}

	//grab process data from system (LIVE)
	if ( !$timeStamp || $data == false) {
		$data = $process->fetchProcessData('-Ao %cpu,%mem,pid,user,comm,args');
	}
	else
	{
		$gotLog = true;
	}

	//explode data into array
	$lines = explode("\n", trim($data));

	//parse out process data for display
    $procs = $process->parseProcessData($lines);

	?>


	<div class="row-fluid">
		<div class="span12">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">

						<?php
							if ($gotLog)
								$title = 'Running Processess at ' . date('H:i:s', $timeStamp);
							else
								$title = 'Running Processess (live)';

							echo $title . "\n";
						?>


					</h4>
				</div>
			</div>
		</div>
	</div>

	<div id="separator" class="separator bottom"></div>

    <div id="accordion" class="accordion">
						
	<?php

	//arraySort error here ?
	//Notice: Undefined index: command0 in /var/www/vhosts/load.loadavg.com/httpdocs/lib/plugins/Process/class.Process.php on line 117

	//echo '<pre>'; var_dump ($procs); echo '</pre>';



	//DIRTY HACK
	//sort by cpu column to start off with
	//really need to srt the groups after the arraySort below to be more acurate
	
	function cmp($a, $b)
	{
	    return strcmp($b["%cpu"], $a["%cpu"]);
	}
	usort($procs, "cmp");
	

	//sorts data by command key into myNewArray
	$myNewArray = $process->arraySort($procs,'command');

	//now we should sory myNewArray by totals but dont have them yet!!!
	//would be great if the arraySort did this as well, totaled up cpu and mem as it sorted...

	//gives each module a id in accordions
	$module = 0;

	//dont work as we get these at the end! hmm...
	$grandTotalProcesCpu = 0;
	$grandTotalProcesMem = 0;

	foreach ($myNewArray as $value) {
		
		$module++;

		//loop thorugh each group 
		$totalProcesCpu = $totalProcesMem = 0;
		$numProcs = 0;

		foreach ($value as $items) {
			$totalProcesCpu += $items['%cpu'];
			$totalProcesMem += $items['%mem'];			
			$numProcs++;
		}

		//increment grand totals
		$grandTotalProcesCpu += $totalProcesCpu;
		$grandTotalProcesMem += $totalProcesMem;

		//skip rcuo - kernel threads
		//$pos = strpos($value[0]['command0'], "rcuo");
		//if ($pos !== false)
		//	continue;

		//skip all null data
		if ( ($value[0]['%cpu'] == 0) && ($value[0]['%mem'] == 0) )
			continue;

		//override some values here to close accordians
		$moduleCollapse = "accordion-body collapse";
	    $moduleCollapseStatus = "false";

		//render data to screen
		?>

		<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
			
			<div class="accordion-heading"> 
				<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
					<?php
					echo '<strong>Process:</strong> ' . $value[0]['command'];
					echo ' Number Running: ' . $numProcs;
					echo "<span style='float:right;display:inline'>";
					echo ' Cpu: ' . $totalProcesCpu;
					echo ' Memory: ' . $totalProcesMem;
					echo "</span>";
					?>				
				</a>					
			</div>

			<div id="category<?php echo $module; ?>" class="<?php echo $moduleCollapse;?>">
				<div class="accordion-inner">
					<?php
						echo '<strong>Command:</strong> ' . $value[0]['command0'] . '<br>';

						foreach ($value as $items) {
							echo ' ID: ' . $items['pid'];
							echo ' User: ' . $items['user'];
							echo ' Cpu: ' . $items['%cpu'];
							echo ' Memory: ' . $items['%mem'];
							echo '<br>';
						}
					?>
				</div> <!-- // Accordion inner end -->
			</div> <!-- // Accordion category end -->

		</div> <!-- // Accordion inner stack end -->

		<?php
		}
		?>
	</div> <!-- // Accordion group end -->

	<div id="separator" class="separator bottom"></div>

	<div class="row-fluid">
		<div class="span12">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">

						<?php

							$title = 'Total CPU ' . $grandTotalProcesCpu . ' Total memory ' . $grandTotalProcesMem;

							echo $title . "\n";
						?>


					</h4>
				</div>
			</div>
		</div>
	</div>



</div> <!-- // inner all end -->


<?php 
} // close logged in 
else
{

	include( APP_PATH . '/views/login.php');

}
?>
