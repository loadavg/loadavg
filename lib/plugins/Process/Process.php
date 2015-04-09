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

	//set module name here
	$moduleName = 'Process';

	//grab the subheader 
    $showCalendar = false;

    include( APP_PATH . '/layout/subheader.php');

?>

	<!-- need to automate this include for all plugins js code -->
	<script src="<?php echo SCRIPT_ROOT ?>lib/plugins/Process/process.js" type="text/javascript"></script>
<?php

	//get plugin class
	$process = LoadPlugins::$_classes[$moduleName];

	// if module is enabled ... get his settings
    $pluginSettings = LoadPlugins::$_settings->$moduleName; 

	//get the range of dates to be charted from the UI and 
	//set the date range to be charted in the plugin
	$range = $loadavg->getDateRange();

	//where is loadModules from ???
	//$loadModules->setDateRange($range);
	LoadModules::setDateRange($range);

	//check if we are pulling todays date or other date from drop down menu
	$gotLogDate = false;
	$logDate = date('Y-m-d');

	if ( (isset($_GET['logdate'])) && (!empty($_GET['logdate'])) ) {                             

		$logDate = $_GET['logdate'];

		if ( $logDate != date('Y-m-d') )
			$gotLogDate = true;
	}

?>


	<div class="innerAll">
	    <div id="accordion" class="accordion">	
		<?php

		//used for callbacks to main plugin to select timestamp to display
		$timeStamp = false;
		if (isset($_GET['timestamp'])) 
			$timeStamp = $_GET['timestamp'];

	    //set url for callback - see end of chartcore.php in lib.charts			
		//get plugin url for callbacks - needs cleaning up
		$host_url = LoadUtility::get_module_url();

		//if we have a log file selected override callback
		if ($gotLogDate)
	    	$callback =  $host_url . 'page=Process&logdate=' . $logDate . '&timestamp=';
		else	
	    	$callback =  $host_url . 'page=Process&timestamp=';

	    //render chart 
		
		//get plugin settings for chart to display
		$chartToShow = $pluginSettings['settings']['display_chart']; //contains args[] array from modules .ini file
		//echo 'showing ' . $showChart;

	    //LoadModules::renderChart("Cpu", false, false, false, $callback, 770 );
	    LoadModules::renderChart($chartToShow, false, false, false, $callback, 770 );
		?>
		</div>

		<?php
		// get and parse process data here
		// to view on your system in console:
		// ps -Ao %cpu,%mem,pid,user,comm,args | sort -r -k1 | less

		//grab process data from log file for timeStamp
		$data = false;
		$gotLog = false;

		if ($timeStamp) {
			//echo '<pre>'; 'got timestamp checking for data';
			$data = $process->fetchProcessLogData($timeStamp, $logDate);		
			//var_dump ($data);
			//echo '</pre>';
		}

		//if no log data then we grab process data from system (LIVE)
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
									$title = 'Running Processess at ' . date('H:i:s', $timeStamp) . ' on ' . date('l, M. j ', $timeStamp);
								else {

									if ($gotLogDate)
										$title = 'No Log Data showing Running Processess (live)';
									else
										$title = 'Running Processess (live)';

								}

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
