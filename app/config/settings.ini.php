; <?php exit(); __halt_compiler(); ?>
version = "2.0"
extensions_dir = "modules/"
logs_dir = "logs/"
title = "LoadAvg"
timezone = "America/New_York"
daystokeep = "30"
allow_anyone = "false"
chart_type = "24"
username = ""
password = ""
https = "false"
checkforupdates = "false"
apiserver = "false"
logger_interval = '5'
rememberme_interval = '5'
ban_ip = "true"
[api]
url = ""
key = ""
server_token = ""
[network_interface]
[modules]
Cpu = "true"
Memory = "true"
Disk = "true"
Apache = "false"
Mysql = "false"
Network = "true"
Server = "true"
Ssh = "false"
