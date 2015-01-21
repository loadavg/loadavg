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

<!--
widget stytles can be found here but need cleaning up
http://demo.mosaicpro.biz/smashingadmin/php/index.php?lang=en&page=widgets
-->
<?php

//ps -Ao %cpu,%mem,user,time,comm,args | sort -r -k1 | head -n 30


//code form here
//https://github.com/pear/System_ProcWatch/blob/master/System/ProcWatch/Parser.php

//provblems with past part of data - command 1 - we truncate all data passed to command for some reason when parsing

$data = $process->fetchData('-Ao %cpu,%mem,pid,user,comm,args | sort -r -k1');

        $lines = explode("\n", trim($data));
        $heads = preg_split('/\s+/', strToLower(trim(array_shift($lines))));
        $count = count($heads) + 1;

        //echo 'Count: ' . $count ;

        $procs = array();


        foreach($lines as $i => $line){

        	//echo 'line ' . $line . '<br>';

            $parts = preg_split('/\s+/', trim($line), $count);
        
        	//deal with dual command headings here!
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

<div class="well lh70-style">
    <b>Process Data</b>
    <div class="pull-right">
	<?php echo $process->getData("uptime"); ?>
    </div>
</div>

<div class="innerAll">
	<div class="row-fluid">
		<div class="span12">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Process information</h4>
				</div>
				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Process Data</h4></div>

						<div class="widget-body">
						
							<?php
							$myNewArray = $process->arraySort($procs,'command0');

							foreach ($myNewArray as $value) {
								
								$numItems = 0;
								foreach ($value as $items) {
									$numItems++;
								}

								//skip rcuo - kernel threads
								//$pos = strpos($value[0]['command0'], "rcuo");

								//if ($pos !== false)
								//	continue;

								//skip all null data
								if ( ($value[0]['%cpu'] == 0) && ($value[0]['%mem'] == 0) )
									continue;

								//render data to screen
								echo '<strong>Process:</strong> ' . $value[0]['command0'];
								echo ' Running: ' . $numItems;
								echo ' ID: ' . $value[0]['pid'];
								echo ' User: ' . $value[0]['user'];
								echo ' Cpu: ' . $value[0]['%cpu'];
								echo ' Memory: ' . $value[0]['%mem'];
								echo '<br>';
								echo '<strong>Command:</strong> ' . $value[0]['command1'];
								echo '<br><br>';
							}
							?>
							<ul class="unstyled row-fluid">
								<li><strong  class="span4">Server:</strong><span class="span8">Some data</span></li>
							</ul>

						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>


	