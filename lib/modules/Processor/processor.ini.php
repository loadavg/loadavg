; <?php exit(); __halt_compiler(); ?>
[module]
name = "CPU Usage"
description = "This module is used to display and log processor usage data."
status = "true"
has_settings = "true"
has_menu = "false"
url_args = "processor"
logable = "true"
[logging]
args[] = '{"logfile":"processor_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"processor_load","logfile":"processor_%s.log","function":"getUsageData", "chart_function":"processor_load", "label":"CPU Usage"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"processor_high_time|processor_high"}'
line[] = '{"format":"Low (%s): %s","args":"processor_low_time|processor_low"}'
line[] = '{"format":"Mean: %s","args":"processor_mean"}'
line[] = '{"format":"Latest: %s","args":"processor_latest"}'
[settings]
overload_1 = 80
overload_2 = 90
display_cutoff = 0.3
display_limiting = "true"