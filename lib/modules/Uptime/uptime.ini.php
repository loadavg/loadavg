; <?php exit(); __halt_compiler(); ?>
[module]
name = "Uptime"
description = "This module is used to display and log system uptime"
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
depth = 2
[logging]
args[] = '{"logfile":"uptime_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"uptime_usage","logfile":"uptime_usage_%s.log","function":"getUsageData", "chart_function":"uptime_usage", "label":"Uptime"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"uptime_high_time|uptime_high"}'
line[] = '{"format":"Low (%s): %s","args":"uptime_low_time|uptime_low"}'
line[] = '{"format":"Average: %s","args":"uptime_mean"}'
line[] = '{"format":"Latest: %s days","args":"uptime_latest"}'
[settings]
overload = 90
display_limiting = "false"
[collectd]
depth = 1
args[] = '{"name":"uptime","functions":["uptime"]}'