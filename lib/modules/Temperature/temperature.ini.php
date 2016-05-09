; <?php exit(); __halt_compiler(); ?>
[module]
name = "Temperature"
description = "This module is used to display and log system core temperature"
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
depth = 1
[logging]
args[] = '{"logfile":"temperature_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"temperature","logfile":"temperature_%s.log","function":"getTemperatureData", "chart_function":"temperature", "label":"Temperature"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s °C","args":"temperature_high_time|temperature_high"}'
line[] = '{"format":"Low (%s): %s °C","args":"temperature_low_time|temperature_low"}'
line[] = '{"format":"Average: %s °C","args":"temperature_mean"}'
line[] = '{"format":"Latest: %s °C","args":"temperature_latest"}'
[settings]
temperature_device = "/sys/class/thermal/thermal_zone0/temp"