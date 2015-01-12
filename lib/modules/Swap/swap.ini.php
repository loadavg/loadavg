; <?php exit(); __halt_compiler(); ?>
[module]
name = "Swap Usage"
description = "This module is used to display and log swap file usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
depth = 4
[logging]
args[] = '{"logfile":"swap_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"swap_usage","logfile":"swap_usage_%s.log","function":"getUsageData", "chart_function":"swap_usage", "label":"Swap Usage"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"mem_high_time|mem_high"}'
line[] = '{"format":"Low (%s): %s","args":"mem_low_time|mem_low"}'
line[] = '{"format":"Mean: %s","args":"mem_mean"}'
line[] = '{"format":"Total : %s MB","args":"mem_total"}'
line[] = '{"format":"Latest: %s MB","args":"mem_latest"}'
[settings]
overload = 90
display_limiting = "true"
[collectd]
depth = 3
args[] = '{"name":"swap","functions":["swap-cached","swap-free","swap-used"]}'