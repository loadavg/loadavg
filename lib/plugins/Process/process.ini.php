; <?php exit(); __halt_compiler(); ?>
[module]
name = "Process module"
description = "This module is used to display and log server data."
status = true
has_settings = false
has_menu = false
logable = true
[logging]
args[] = '{"logfile":"process_usage_%s.log","function":"logData"}'
[cmd]
uptime = "uptime"



