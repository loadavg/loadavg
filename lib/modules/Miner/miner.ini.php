; <?php exit(); __halt_compiler(); ?>
[module]
name = "Miner Usage"
description = "This module is used to display and log mining usage data."
status = "true"
has_settings = "true"
has_menu = "false"
url_args = "load"
logable = "true"
depth = 3
[logging]
args[] = '{"logfile":"miner_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"miner_load","logfile":"miner_%s.log","function":"getUsageData", "chart_function":"miner_load", "label":"Miner Load"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"cpu_high_time|cpu_high"}'
line[] = '{"format":"Low (%s): %s","args":"cpu_low_time|cpu_low"}'
line[] = '{"format":"Mean: %s","args":"cpu_mean"}'
line[] = '{"format":"Latest: %s","args":"cpu_latest"}'
[settings]
server = "127.0.0.1"
port = 4028
overload_1 = 3700
overload_2 = 4000
display_cutoff = 4500
display_limiting = "false"