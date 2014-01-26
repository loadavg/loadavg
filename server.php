<?php

/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* API server demonstration script
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

mysql_connect("dbhost","dbuser","dbpassword");
mysql_select_db("loadavg");
$trigger = null;

if (isset($_GET['trigger'])) {
	switch ( $_GET['trigger'] ) {
		case 'generate': $trigger = "generate"; break;
		case 'view': $trigger = "view"; break;
	}
}

$howmany = 50;
?>
<html>
<head>
	<title>LoadAvg 2.0 API Demo</title>
</head>
<body>
	<a href="?trigger=generate">Generate new credentials</a> | <a href="?trigger=view">View last <?php echo $howmany; ?> entries in database</a>

	<table>
		<tr>
			<td><h2>
			<?php
				switch ( $trigger ) {
					case 'generate':
						echo "Generate new credentials";
						break;
					case 'view':
						echo "View last ".$howmany." entries in database";
						break;
				}
			?>
			</h2></td>
		</tr>
		<tr>
			<td>
				<?php
				switch ( $trigger ) {
					case 'generate': 
						?>
						<form action="" method="post">
							<input type="submit" value="Generate">
						</form>
						<?php
						if (isset($_POST)) {
							echo "Generating...";
							$password = md5(time());
							$username = 'loadavg_' . uniqid();
							$generate = mysql_query("INSERT INTO users (`username`,`password`,`date_added`) VALUES('{$username}','{$password}',NOW());");
							$user_id = mysql_insert_id();
							$generate_server = mysql_query("INSERT INTO api_servers (`user_id`,`time_created`) VALUE('{$user_id}', NOW());");
							$server_id = mysql_insert_id();
							$api_key = strtoupper(md5($username));
							$generate_api_key = mysql_query("INSERT INTO api_keys (`user_id`,`server_id`,`api_key`,`time_created`) VALUES('{$user_id}','{$server_id}','{$api_key}',NOW());");
							?>
							<table width="100%" cellspacing="1" cellpadding="3">
								<tr>
									<td>Username</td>
									<td>API Key</td>
									<td>Server ID</td>
								</tr>
								<tr>
									<td><?php echo $username; ?></td>
									<td><?php echo $api_key; ?></td>
									<td align="center"><?php echo $server_id; ?></td>
								</tr>
							</table>
							<?php
						}
						break;
					case 'view':
						?>
						<table width="100%" cellspacing="1" cellpadding="3" border="0">
							<tr>
								<td>#</td>
								<td>Date</td>
								<td>User</td>
								<td>Server</td>
								<td>Data</td>
							</tr>
							<?php
							$entries = mysql_query("SELECT * FROM `api_data` INNER JOIN `users` ON api_data.user_id = users.id ORDER by api_data.id DESC LIMIT 0, " . $howmany);
							$i = 0;
							while ( $row = mysql_fetch_object( $entries ) ) {
								$i++;
							?>
							<tr>
								<td><?php echo $i; ?></td>
								<td><?php echo $row->date;?></td>
								<td><?php echo $row->username; ?></td>
								<td align="center"><?php echo $row->server_id; ?></td>
								<td><?php echo $row->data; ?></td>
							</tr>
							<?php } ?>
						</table>
						<?php
						break;
				}
				?>
			</td>
		</tr>
	</table>
</body>
</html>