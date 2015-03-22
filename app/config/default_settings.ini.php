; <?php exit(); __halt_compiler(); ?>
[settings]
version = 2.2
extensions_dir = "modules/"
logs_dir = "/logs/"
title = "LoadAvg"
clienttimezone = "America/New_York"
timezonemode = "Browser"
timezone = "America/New_York"
daystokeep = 30
allow_anyone = "false"
chart_type = 24
username = ""
password = ""
https = "false"
checkforupdates = "true"
apiserver = "false"
logger_interval = 5
rememberme_interval = 5
ban_ip = "true"
autoreload = "false"
logalerts = "true"
[api]
url = ""
key = ""
server_token = ""
[network_interface]
eno16777736 = "true"
lo = "false"
[modules]
Cpu = "true"
Memory = "true"
Disk = "true"
Apache = "false"
Mysql = "false"
Network = "true"
Processor = "false"
Ssh = "false"
Swap = "false"
Uptime = "true"
[plugins]
Alerts = "true"
Process = "false"
Server = "true"