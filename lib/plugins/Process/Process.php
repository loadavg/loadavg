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


<?php $process = LoadPlugins::$_classes['Process']; ?>

<div class="well lh70-style">
    <b>Process Data</b>
    <div class="pull-right">
	<?php echo $process->getData("uptime"); ?>
    </div>
</div>

<div class="innerAll">




    <div id="accordion" class="accordion">	
	<?php
	        //get the range of dates to be charted from the UI and 
	        //set the date range to be charted in the modules
	        $range = $loadavg->getDateRange();
	        $loadModules->setDateRange($range);

	        //render chart
	        $loadModules->renderSingleChart("Cpu", false);

	?>
	</div>

<!--
widget stytles can be found here but need cleaning up
http://demo.mosaicpro.biz/smashingadmin/php/index.php?lang=en&page=widgets
-->
<?php

//ps -Ao %cpu,%mem,user,time,comm,args | sort -r -k1 | head -n 30


//code form here
//https://github.com/pear/System_ProcWatch/blob/master/System/ProcWatch/Parser.php

//provblems with part of data - command1
//we truncate all data passed to command for some reason when parsing

//also bug when sorting with command0 check it out

// view 
// ps -Ao %cpu,%mem,pid,user,comm,args | sort -r -k1 | less

$data = $process->fetchData('-Ao %cpu,%mem,pid,user,comm,args | sort -r -k1');

        $lines = explode("\n", trim($data));
        $heads = preg_split('/\s+/', strToLower(trim(array_shift($lines))));
        $count = count($heads) + 1;

        $procs = array();

        //var_dump ($heads);
		//see debug in public function arraySort($input,$sortkey){

        foreach($lines as $i => $line){

            $parts = preg_split('/\s+/', trim($line), $count);
        
        	//deal with dual command title headings here in row 0
        	//when creating keys for array
        	$command = 0;
            foreach ($heads as $j => $head) {

	            	if ($head == 'command') {
	            		$head = $head . $command;
	            		$command++;
	            	}
            	
                $procs[$i][$head] = str_replace('"', '\"', $parts[$j]);

            }


        }


?>


	<div class="row-fluid">
		<div class="span12">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Running Processess</h4>
				</div>
			</div>
		</div>
	</div>

	<div id="separator" class="separator bottom"></div>

    <div id="accordion" class="accordion">
						
	<?php
	$myNewArray = $process->arraySort($procs,'command0');

	//gives each module a id in accordions
	$module = 0;

	foreach ($myNewArray as $value) {
		
		$module++;

		$totalProcesCpu = $totalProcesMem = 0;
		$numProcs = 0;

		foreach ($value as $items) {
			$totalProcesCpu += $items['%cpu'];
			$totalProcesMem += $items['%mem'];
			$numProcs++;
		}

		//skip rcuo - kernel threads
		//$pos = strpos($value[0]['command0'], "rcuo");
		//if ($pos !== false)
		//	continue;

		//skip all null data
		if ( ($value[0]['%cpu'] == 0) && ($value[0]['%mem'] == 0) )
			continue;


		/*
		  //status data used from common.js 
		  if ($value == "open") {
			$moduleCollapse = "accordion-body collapse in";
		    $moduleCollapseStatus = "true";

		  if ($value == "closed") {
			$moduleCollapse = "accordion-body collapse";
		    $moduleCollapseStatus = "false";
		*/

		$moduleCollapse = "accordion-body collapse";
	    $moduleCollapseStatus = "false";

		//render data to screen
		?>

		<div id="accordion-<?php echo $module;?>" class="accordion-group"   data-collapse-closed="<?php echo $module;?>" cookie-closed=<?php echo $moduleCollapseStatus; ?> >
			
			<div class="accordion-heading"> 
				<a class="accordion-toggle" data-toggle="collapse"  href="#category<?php echo $module; ?>" >
					<?php
					echo '<strong>Process:</strong> ' . $value[0]['command0'];
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
						echo '<strong>Command:</strong> ' . $value[0]['command1'] . '<br>';

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

</div> <!-- // inner all end -->


	