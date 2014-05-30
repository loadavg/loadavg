<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Login module interface
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>

<div id="login">
	<form class="form-signin" method="post" action="">
		<input type="hidden" name="login" value="1">
		<div class="widget widget-4">
			<div class="widget-head">
				<h4 class="heading">Restricted area</h4>
			</div>
		</div>
		<h3 class="form-signin-heading"><i class="fa fa-unlock-alt"></i> Please sign in</h3>
		<div class="uniformjs">
			<input type="text" name="username" class="input-block-level text" placeholder="Email address"> 
			<input type="password" name="password" class="input-block-level password" placeholder="Password"> 
			<label class="checkbox"><div class="checker" id="uniform-undefined">
				<span><input type="checkbox" value="remember-me" style="opacity: 0;"></span></div>Remember me
			</label>
		</div>
		<button class="btn btn-large btn-primary" type="submit">Sign in</button>
	</form>

	<center>
				<a href="http://www.loadavg.com/">LoadAVG v <?php echo $settings['version']; ?></a> &copy;  <?php echo date("Y"); ?> Sputnik7 Ltd<br />
				For comments and suggestions please <a href="http://www.loadavg.com/forums/">visit our forums</a><br />
	</center>
</div>