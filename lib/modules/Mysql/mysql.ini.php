; <?php exit(); __halt_compiler(); ?>
[module]
name = "Mysql Usage"
description = "This module is used to display and log mysql usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"mysql_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"type":"Transmit","id":"mysql_transfer","logfile":"mysql_usage_%s.log","function":"getTransferData", "chart_function":"mysql_usage", "label":"Transmit"}'
args[] = '{"type":"Receive","id":"mysql_receive","logfile":"mysql_usage_%s.log","function":"getReceiveData", "chart_function":"mysql_usage", "label":"Receive"}'
args[] = '{"type":"Queries","id":"mysql_queries","logfile":"mysql_usage_%s.log","function":"getQueryData", "chart_function":"mysql_usage", "label":"Queries"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"mysql_high_time|mysql_high"}'
line[] = '{"format":"Low (%s): %s","args":"mysql_low_time|mysql_low"}'
line[] = '{"format":"Mean: %s","args":"mysql_mean"}'
line[] = '{"format":"Latest: %s MB","args":"mysql_latest"}'
[settings]
overload = 2048
mysqlserver = "localhost"
mysqluser = "root"
mysqlpassword = "root"
show_queries = "true"
