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

<?php $server = LoadAvg::$_classes['Server']; ?>

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
								<li><strong  class="span4">Hostname:</strong><span class="span8"><?php echo $server->getData('hostname'); ?></span></li>
								<li><strong  class="span4">Nodename:</strong><span class="span8"><?php echo $server->getData('nodename'); ?></span></li>
							</ul>
						</div>
					</div>
					

					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Kernel Information</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
                            <li><strong  class="span4">Operating System:</strong><span class="span8"><?php echo $server->getData('os'); ?></span></li>
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
					<div class="well">
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
                            <li><strong class="span4">Hardware name:</strong><span class="span8"><?php echo $server->getData('hardware_name'); ?></span></li>
                            <li><strong class="span4">Hardware platform:</strong><span class="span8"><?php echo $server->getData('hardware_platform'); ?></span></li>
                        </ul>
						</div>
					</div>

					<div class="widget ">
						<div class="widget-head"><h4 class="heading">Processor(s)</h4></div>
						<div class="widget-body">
							<ul class="unstyled row-fluid">
	                            <li><strong class="span4">Processor type:</strong><span class="span8"><?php echo $server->getData('proc_type'); ?></span></li>
	                            <li><strong class="span4">Total processor(s):</strong><span class="span8"><?php echo $server->getData('proc_count'); ?></span></li>
                                    <li><strong class="span4">Processor model:</strong><span class="span8"><?php echo $server->getData('proc_model'); ?></span></li>
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
					<pre><?php echo $server->getData('hdd_usage'); ?></pre>
				</div>
			</div>
		</div>
	</div>
</div>
