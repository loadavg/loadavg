; <?php exit(); __halt_compiler(); ?>
[module]
name = "Memory Usage"
description = "This module is used to display and log memory usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"memory_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"memory_usage","logfile":"memory_usage_%s.log","function":"getUsageData", "chart_function":"memory_usage", "label":"Memory Usage"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"mem_high_time|mem_high"}'
line[] = '{"format":"Low (%s): %s","args":"mem_low_time|mem_low"}'
line[] = '{"format":"Mean: %s","args":"mem_mean"}'
line[] = '{"format":"Swap: %s","args":"mem_swap"}'
line[] = '{"format":"Total : %s MB","args":"mem_total"}'
line[] = '{"format":"Latest: %s MB","args":"mem_latest"}'
[settings]
overload = 90
display_limiting = "false"