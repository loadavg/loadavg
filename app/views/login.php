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

<!--
HTML 5 remember me ?
http://devzone.co.in/remember-functionality-signinlogin-form-using-html5-jquery/

use a salt and a auth cookie to make more secure
http://stackoverflow.com/questions/15194663/encrypt-and-decrypt-md5
salt needs to be created at install time and stored in settings.ini
-->

<?php

//see if we remember them and if so load values
$loaduser = $remember = false;

if (isset($_COOKIE['loadremember'])) 
	$remember = true;

if (isset($_COOKIE['loaduser'])) 
	$loaduser = $_COOKIE['loaduser'];

?>

<div id="login">



	<form class="form-signin" method="post" action="">
		<input type="hidden" name="login" value="1">
		<div class="widget widget-4">
			<div class="widget-head">
				<h4 class="heading">Restricted area</h4>
			</div>
		</div>

	<?php
	if ($banned == false) {
	?>
		<h3 class="form-signin-heading"><i class="fa fa-unlock-alt"></i> Please sign in</h3>
		<div class="uniformjs">

			<input type="text" name="username" class="input-block-level text" placeholder="Email or Username"
			<?php if($remember && $loaduser) echo 'value='. $loaduser; ?>> 
			
			<input type="password" name="password" class="input-block-level password" placeholder="Password"> 
			
			<label class="checkbox">
				<div class="checker" id="uniform-undefined">
				<span>
				<input type="checkbox" name = "remember-me" value="remember-me" style="opacity: 100;" 
				<?php if($remember)  echo 'checked="checked"'; else  echo '';?>>
				</span>
				</div>Remember me
			</label>

		</div>

		<button class="btn btn-large btn-primary" type="submit">Sign in</button>

	<?php
	} else {
	?>
		<h3 class="form-signin-heading"><i class="fa fa-unlock-alt"></i> Your IP has been banned</h3>
	<?php
	}


	?>
	</form>

		<!--
			really should have a error console that we create here but draw to in the footer for proper 
			error reporting after the script has run
		-->
		<center>
			<?php if (strlen($error)>0) { echo  $error; }?>
		</center>


</div>

