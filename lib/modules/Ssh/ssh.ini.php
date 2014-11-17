; <?php exit(); __halt_compiler(); ?>
[module]
name = "SSH Usage"
description = "This module is used to display and log SSH usage data."
status = "true"
has_settings = "true"
has_menu = "false"
url_args = "ssh"
logable = "true"
[logging]
args[] = '{"logfile":"ssh_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"ssh_usage","logfile":"ssh_usage_%s.log","function":"getUsageData", "chart_function":"ssh_usage", "label":"SSH Logins"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"Accepted: %s","args":"ssh_accept"}'
line[] = '{"format":"Failed: %s","args":"ssh_failed"}'
line[] = '{"format":"Invalid: %s","args":"ssh_invalid"}'
line[] = '{"format":"Last login: (%s)","args":"ssh_latest_login"}'
[settings]
overload = 10
log_location = "/var/log/secure"
display_limiting = "true"