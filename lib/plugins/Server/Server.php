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

<?php $server = LoadPlugins::$_classes['Server']; ?>

<!--
widget stytles can be found here but need cleaning up
http://demo.mosaicpro.biz/smashingadmin/php/index.php?lang=en&page=widgets
-->

<div class="well lh70-style">
    <b>Server Data</b>
    <div class="pull-right">
	<?php echo $server->getData("uptime"); ?>
    </div>
</div>

<div class="innerAll">
	<!--
	<h3>Server Data</h3>
	-->
	

    <div id="accordion" class="accordion">	
	<?php
	        //get the range of dates to be charted from the UI and 
	        //set the date range to be charted in the modules
	        $range = $loadavg->getDateRange();
	        $loadModules->setDateRange($range);

	        //render chart
	        $loadModules->renderChart("Uptime", false, false, false, 770 );

	?>
	</div>

	<div id="separator" class="separator bottom"></div>

	<div class="row-fluid">
		<div class="span6">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Server information</h4>
				</div>

				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Server Data</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
								<li><strong  class="span4">Server:</strong><span class="span8"><?php echo $settings['settings']['title']; ?></span></li>
								<li><strong  class="span4">Hostname:</strong><span class="span8"><?php echo $server->getData('hostname'); ?></span></li>
								<li><strong  class="span4">Nodename:</strong><span class="span8"><?php echo $server->getData('nodename'); ?></span></li>
							</ul>
						</div>
					</div>
					

					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Kernel Information</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
                            <li><strong  class="span4">OS:</strong><span class="span8"><?php echo $server->getData('os'); ?></span></li>
                            <li><strong  class="span4">Kernel:</strong><span class="span8"><?php echo $server->getData('kernel'); ?></span></li>
							<li><strong class="span4">Version:</strong><span class="span8"><?php echo $server->getData('kernel_version'); ?></span></li>
                        </ul>	
						</div>
					</div>
					
				</div>
			</div>

			<div class="separator bottom"></div>



			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Memory Usage</h4>
				</div>

				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Memory</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
							<li><strong class="span4">Total:</strong><span class="span8"><?php echo $server->getData('mem_total'); ?> MB</span></li>
							<li><strong class="span4">Used:</strong><span class="span8"><?php echo $server->getData('mem_used'); ?> MB</span></li>
							<li><strong class="span4">Free:</strong><span class="span8"><?php echo $server->getData('mem_free'); ?> MB</span></li>
							<li><strong class="span4">Shared:</strong><span class="span8"><?php echo $server->getData('mem_shared'); ?> MB</span></li>
							<li><strong class="span4">Buffers:</strong><span class="span8"><?php echo $server->getData('mem_buffers'); ?> MB</span></li>
							<li><strong class="span4">Cached:</strong><span class="span8"><?php echo $server->getData('mem_cached'); ?> MB</span></li>
							</ul>
						</div>
					</div>
					

					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Swap</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
							<li><strong class="span4">Total:</strong><span class="span8"><?php echo $server->getData('swap_total'); ?> MB</span></li>
							<li><strong class="span4">Used:</strong><span class="span8"><?php echo $server->getData('swap_used'); ?> MB</span></li>
							<li><strong class="span4">Free:</strong><span class="span8"><?php echo $server->getData('swap_free'); ?> MB</span></li>
                        </ul>	
						</div>
					</div>

			<div class="separator bottom"></div>

			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Networking</h4>
				</div>
				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Interfaces</h4></div>
						<div class="widget-body">
								<ul class="unstyled row-fluid">
                            <li><strong class="span4">Interface:</strong><span class="span8"><?php echo $server->getData('network_name'); ?></span></li>
                            <li><strong class="span4">IP:</strong><span class="span8"><?php echo $server->getData('network_ip'); ?></span></li>
                        </ul>
						</div>
					</div>

				</div>
			</div>

				</div>
			</div>

		</div>


		<div class="span6">
			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Hardware</h4>
				</div>
				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Hardware</h4></div>
						<div class="widget-body">
								<ul class="unstyled row-fluid">
                            <li><strong class="span4">Type:</strong><span class="span8"><?php echo $server->getData('hardware_name'); ?></span></li>
                            <li><strong class="span4">Platform:</strong><span class="span8"><?php echo $server->getData('hardware_platform'); ?></span></li>
                        </ul>
						</div>
					</div>

					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Processor(s)</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
	                            <li><strong class="span4">Type:</strong><span class="span8"><?php echo $server->getData('proc_type'); ?></span></li>
	                            <li><strong class="span4">Number:</strong><span class="span8"><?php echo $server->getData('proc_count'); ?></span></li>
	                            <li><strong class="span4">Details:</strong><span class="span8"><?php echo $server->getData('proc_model'); ?></span></li>

	                        </ul>
						</div>
					</div>
				</div>
			</div>



			<div class="separator bottom"></div>

			<div class="widget widget-4">
				<div class="widget-head">
					<h4 class="heading">Disk usage</h4>
				</div>
				<div class="widget-body">
					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Primary Storage</h4></div>
						<div class="widget-body">

						<?php
						
						//this is how we get a loaded modules settings data
						//$settings = LoadPlugins::$_settings->Disk;
						//$drive = $settings['settings']['drive'];
						$drive = "/";

						//really need to do this 
						//if (is_dir($drive)) {				
						//}

						$totalBytes =  $server->getTotalStorage( $drive, true );
						$freeData   =  $server->getFreeStorage( $drive, true );
						$usedData   =  $server->getUsedStorage( $drive, true );

						?>
							
						<ul class="unstyled row-fluid">
	                        <li><strong class="span4">Total Space:</strong><span class="span8"><?php echo $totalBytes; ?></span></li>

	                        <li><strong class="span4">Used Space:</strong><span class="span8"><?php echo $usedData[0]; ?></span></li>
	                        <li><strong class="span4">Free Space:</strong><span class="span8"><?php echo $freeData[0]; ?></span></li>

	                        <li><strong class="span4">Free %:</strong><span class="span8"><?php echo $freeData[1]; ?> %</span></li>
	                        <li><strong class="span4">Used %:</strong><span class="span8"><?php echo $usedData[1]; ?> %</span></li>
	                   </ul>
					</div>
					</div>
					</div>

				<div class="separator bottom"></div>





				<div class="widget-head">
					<h4 class="heading">Disk Partitions</h4>
				</div>

				<div class="widget-body">
					<div class="widget ">
						<!--
						<div class="widget-head"><h4 class="heading">Interfaces</h4></div>
					-->
						<div class="widget-body">					
					<?php 
					//old way
					//echo $server->getData('hdd_usage'); 

					//$Stats = $server->getPartitionData( $server->getData('partitions') );
					$Stats = $server->getPartitionData( );

		            foreach($Stats as $row){
					?>
						<ul class="unstyled row-fluid">

	<li><strong class="span4">Disk:</strong><span class="span8"><?php echo $row['disk']; ?></span></li>
	<li><strong class="span4">Mount:</strong><span class="span8"><?php echo $row['mount']; ?></span></li>
	<li><strong class="span4">Type:</strong><span class="span8"><?php echo $row['type']; ?></span></li>
	<li><strong class="span4">Total:</strong><span class="span8"><?php echo (int)$row['mb_total']; ?></span></li>
	<li><strong class="span4">Used:</strong><span class="span8"><?php echo (int)$row['mb_used']; ?></span></li>
	<li><strong class="span4">Free:</strong><span class="span8"><?php echo (int)$row['mb_free']; ?></span></li>
	<li><strong class="span4">Percent:</strong><span class="span8"><?php echo (int)$row['percent']; ?></span></li>

						</ul>
					<?php		 

		            }    

					?>

				</div>
				</div>
				</div>

			</div>
		</div>
	</div>
</div>

<?php 
} // close logged in 
else
{

	include( APP_PATH . '/views/login.php');

}
?>