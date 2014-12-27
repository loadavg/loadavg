; <?php exit(); __halt_compiler(); ?>
[module]
name = "Swap Usage"
description = "This module is used to display and log swap file usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"swap_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"swap_usage","logfile":"swap_usage_%s.log","function":"getUsageData", "chart_function":"swap_usage", "label":"Swap Usage"}'
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