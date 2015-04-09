; <?php exit(); __halt_compiler(); ?>
[module]
name = "Alerts module"
description = "This module is used to display and track alerts."
status = true
has_settings = false
has_menu = false
logable = "false"
hasownlogdir = "false"
[chart]
args[] = '{"id":"event_usage","logfile":"events_%s.log","function":"getUsageData", "chart_function":"event_usage", "label":"Events"}'
[cmd]
uptime = "uptime"
[settings]
overload_1 = 10
enable_logging = "false"