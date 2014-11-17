; <?php exit(); __halt_compiler(); ?>
[module]
name = "CPU Usage"
description = "This module is used to display and log cpu usage data."
status = "true"
has_settings = "true"
has_menu = "false"
url_args = "load"
logable = "true"
[logging]
args[] = '{"logfile":"cpu_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"cpu_load","logfile":"cpu_%s.log","function":"getUsageData", "chart_function":"cpu_load", "label":"CPU Load"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"cpu_high_time|cpu_high"}'
line[] = '{"format":"Low (%s): %s","args":"cpu_low_time|cpu_low"}'
line[] = '{"format":"Mean: %s","args":"cpu_mean"}'
line[] = '{"format":"Latest: %s","args":"cpu_latest"}'
[settings]
overload_1 = 0.2
overload_2 = 0.3
display_cutoff = 0.3
display_limiting = "false"
