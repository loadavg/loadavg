; <?php exit(); __halt_compiler(); ?>
[module]
name = "Disk Usage"
description = "This module is used to display and log disk usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"disk_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"disk_usage","logfile":"disk_usage_%s.log","function":"getUsageData", "chart_function":"disk_usage", "label":"Disk Usage"}'
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
drive = "/"
display_limiting = "false"