<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main header file 
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/


/*
 *check is user has logged in and give errors
 */

$error = '';

if (isset($_POST['login'])  ) {
	
	if ( isset($_POST['username']) && isset($_POST['password']) ) {

		//echo "login with user and pass<br>";
		$loadavg->logIn( $_POST['username'], $_POST['password']);

		if ($loadavg->isLoggedIn()) 
			header("Location: index.php");

	 	else {

			if ( isset($_POST['username']) && isset($_POST['password']) ) {

				$error = "Incorrect credentials, please try again";
			}

			if (!isset($_POST['username']) || (!$_POST['username'])  ) 
				{ $error = "Username is mandatory"; }
			
			if (!isset($_POST['password']) || (!$_POST['password'])  ) 
				{ $error .= "<br>Password is mandatory"; }

		}
	}
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
<head>

	<title><?php echo 'Server ' . $settings['settings']['title'] . ' | LoadAvg ' . $settings['settings']['version']; ?></title>
	
	<!-- Meta -->
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	
	<!-- Bootstrap -->
	<link href="<?php echo SCRIPT_ROOT ?>public/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="<?php echo SCRIPT_ROOT ?>public/assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />

	<!-- Bootstrap Toggle Buttons Script -->
	<link rel="stylesheet" href="<?php echo SCRIPT_ROOT ?>public/assets/bootstrap/extend/bootstrap-toggle-buttons/static/stylesheets/bootstrap-toggle-buttons.css">
	
	<!-- JQueryUI v1.11.1 -->
	<link rel="stylesheet" href="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/jquery-ui-1.11.1.custom/jquery-ui.min.css" />

	<!-- Glyphicons -->
	<link rel="stylesheet" href="<?php echo SCRIPT_ROOT ?>public/assets/theme/css/font-awesome.min.css" />
	
	<!-- JQuery v1.11.1 -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/jquery-1.11.1.min.js"></script>

	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/jquery.cookie.js"></script>
	
	<!-- Modernizr -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/modernizr.custom.09032.js"></script>
	
	<!-- Theme -->
	<link rel="stylesheet" href="<?php echo SCRIPT_ROOT ?>public/assets/theme/css/style.css?<?php echo time(0); ?>" />
	
	<!-- LESS 2 CSS 
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/less-1.3.3.min.js"></script>
	-->

	<!--[if IE]><script type="text/javascript" src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/other/excanvas/excanvas.js"></script><![endif]-->

	<script type="text/javascript">
	<?php 
	$min = date('Y-m-d');
	$max = date('Y-m-d');
	if (isset($_GET['logdate']) && !empty($_GET['logdate']) && $_GET['logdate'] !== date('Y-m-d'))
	{
		$min = $_GET['logdate'];
		$max = $_GET['logdate'];
	}
	
	if (isset($_GET['minDate']) && !empty($_GET['minDate']) && isset($_GET['maxDate']) && !empty($_GET['maxDate']))
	{
		$min = $_GET['minDate'];
		$max = $_GET['maxDate'];
	}

	$min = strtotime($min);
	$max = strtotime($max);
	?>

	var today_min = <?php echo mktime(0, 0, 0, date("n", $min), date("j", $min), date("Y", $min))*1000; ?>;
	var today_max = <?php echo mktime(24, 0, 0, date("n", $max), date("j", $max), date("Y", $max))*1000; ?>;

	//fix for min range only (to current)

	//fix for 6 and 12 hours need to grab data from log file before

	//$nextWeek = time() + ( 24 * 60 * 60);
    // 24 hours; 60 mins; 60 secs

	//get current time if we want end time to be current time
	//today_max =  <?php echo (   time()  *1000); ?>;
	//today_min =  today_max - (3600 * 6 * 1000);

	</script>

	<!-- force a refreash every (logger = 5 default) minutes to update charts 
	     need to set up settings for this make it optional 
		 only do this if day is today as otherwise we dont need to refreash for ranges

		 problem with below is when you choose today from log menu then logdate is also set
		 need to fix that in form not here
	-->

	<?php
	if ( (!isset($_GET['minDate'])) || (!isset($_GET['maxDate'])) || (!isset($_GET['logdate'])) ) 
	{ 
		//if (    ($settings['settings']['title'] == "true") )
		if (   (isset($settings['settings']['autoreload']))  && ($settings['settings']['autoreload'] == "true") )
		{
		?>
		<meta http-equiv="refresh" content="300">
		<?php
		}
	}
	?>
	

</head>
<body>

<?php

		if ( LoadAvg::$period ) { echo 'PERIOD'; die; }

?>

	<!-- Start Content -->
	<div class="container fixed">
		
		<div class="navbar main hidden-print">
			
			<a href="index.php" class="appbrand"><img src="<?php echo SCRIPT_ROOT ?>public/assets/theme/images/loadavg_logo.png" style="float: left; margin-right: 5px;"><span>LoadAvg<span>Advanced Server Analytics</span></span></a>
			
			<?php if ($loadavg->isLoggedIn() || (isset($settings['settings']['allow_anyone']) && $settings['settings']['allow_anyone'] == "true")) { ?>

			<ul class="topnav pull-right">
				<li<?php if (isset($_GET['page']) && $_GET['page'] == '') : ?> class="active"<?php endif; ?>><a href="index.php"><i class="fa fa-bar-chart-o"></i> Charts</a></li>

				<li <?php if (isset($_GET['page']) && $_GET['page'] == 'server') : ?> class="active"<?php endif; ?>><a href="?page=server"><i class="fa fa-gears"></i> Server</a></li>
				


				<?php if ( $loadavg->isLoggedIn() ) { ?>

				<li class="account <?php if (isset($_GET['page']) && $_GET['page'] == 'settings') : ?> active<?php endif; ?>">
					<a data-toggle="dropdown" href="" class="logout"><span class="hidden-phone text">

					<?php echo (isset($settings['settings']['allow_anyone']) && $settings['settings']['allow_anyone'] == "true" ) ? 'Settings' : 'Settings' /* $settings['username']; */ ?></span> 
						<i class="fa fa-unlock-alt"></i></a>
					
					<ul class="dropdown-menu pull-right">
						<li><a href="?page=settings">System <i class="fa fa-cog pull-right"></i></a></li>
						<li><a href="?page=settingsmodules">Modules <i class="fa fa-cog pull-right"></i></a></li>
						<li><a href="?page=settingsapi">API <i class="fa fa-cog pull-right"></i></a></li>
						<li><a href="?page=settingslogger">Logger <i class="fa fa-cog pull-right"></i></a></li>
						<?php if ( $loadavg->isLoggedIn() ): ?>
						<li>
							<span>
								<a href="?page=logout" class="btn btn-default btn-small pull-right" style="padding: 2px 10px; background: #fff;" href="">Sign Out</a>
							</span>
						</li>
						<?php endif; ?>
					</ul>
				</li>

				
				<?php }

				else

				{ ?>

				<li><a href="?page=login"><i class="fa fa-question-circle"></i> Login</a></li>

				<?php } ?>

			</ul>
			<?php } ?>
		</div>
		
		<div id="wrapper">
		
		<div id="content">


