<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main footer file
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>		
			<?php if ($loadavg->isLoggedIn()) { ?>
			<div class="well lh70-style-top margin-none center footer">
				<a href="http://www.loadavg.com/">LoadAVG v <?php echo $settings['version']; ?></a> &copy;  <?php echo date("Y"); ?> Sputnik7 Ltd<br />
				For comments, suggestions &amp; to report bugs please <a href="http://www.loadavg.com/forums/">visit our forums</a><br />
				HTML graphs generated in <?php echo $page_load; ?> sec.					

				<?php if (!isset($_SESSION['support_loadavg'])) { ?>
				<div class="left pull-left">
					Like LoadAvg ? <a href="http://www.loadavg.com/donate/" title="Make a donation, support LoadAvg">Please donate</a>
				</div>

				<?php } ?>

				<?php if (isset($_SESSION['download_url'])) { ?>
				<div class="right pull-right">
					<!--
					Update available <a href="<?php echo $_SESSION['download_url']; ?>" title="Download the new version of LoadAvg">click to download</a>
					-->
					Update available <a href="http://www.loadavg.com/download/" title="Download the new version of LoadAvg">click to download</a>
				</div>
				<?php } ?>
			</div>
			<?php } ?>

		<!-- End Content -->
		</div>

		
		
		<!-- End Wrapper -->
		</div>
		
		
	</div>
	
	
	<!-- JQueryUI v1.9.2 -->
	<script src="assets/theme/scripts/plugins/system/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js"></script>

	<!-- Javascript for Period -->
	<script src="assets/theme/scripts/demo/period.js"></script>
	
	<!-- JQueryUI Touch Punch -->
	<!-- small hack that enables the use of touch events on sites using the jQuery UI user interface library -->
	<script src="assets/theme/scripts/plugins/system/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
	
	<!-- Colors -->
	<script>
	var primaryColor = '#4a8bc2';
	</script>
	
	<!--  Flot (Charts) JS -->
	<script src="assets/theme/scripts/plugins/charts/flot/jquery.flot.js" type="text/javascript"></script>
	<script src="assets/theme/scripts/plugins/charts/flot/jquery.flot.time.js" type="text/javascript"></script>
	<script src="assets/theme/scripts/plugins/charts/flot/plugins/tooltip/jquery.flot.tooltip.min.js"></script>

	<!-- Bootstrap Script -->
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>

	<!-- Bootstrap Toggle Buttons Script -->
	<script src="assets/bootstrap/extend/bootstrap-toggle-buttons/static/js/jquery.toggle.buttons.js"></script>

	<script>$(function () { $('.toggle-button').toggleButtons(); })</script>

	<!-- Common script -->
	<script src="assets/theme/scripts/demo/common.js"></script>
	
</body>
</html>
