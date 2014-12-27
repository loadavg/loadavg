; <?php exit(); __halt_compiler(); ?>
[module]
name = "Uptime"
description = "This module is used to display and log system uptime"
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"uptime_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"uptime_usage","logfile":"uptime_usage_%s.log","function":"getUsageData", "chart_function":"uptime_usage", "label":"Uptime"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"disk_high_time|disk_high"}'
line[] = '{"format":"Low (%s): %s","args":"disk_low_time|disk_low"}'
line[] = '{"format":"Average: %s","args":"disk_mean"}'
line[] = '{"format":"Size : %s MB","args":"disk_total"}'
line[] = '{"format":"Latest: %s MB","args":"disk_latest"}'
line[] = '{"format":"Free: %s MB","args":"disk_free"}'
[settings]
overload = 90
display_limiting = "false"
[collectd]
args[] = '{"name":"df-root","functions":["df_complex-free","df_complex-reserved","df_complex-used"]}'