; <?php exit(); __halt_compiler(); ?>
[settings]
version = 2.1
extensions_dir = "modules/"
logs_dir = "logs/"
title = "localhost.localdomain"
clienttimezone = "America/Los_Angeles"
timezone = "America/New_York"
daystokeep = 30
allow_anyone = "false"
chart_type = 24
username = "loadavg"
password = "d0c09fdd73c933fcd0443ed04740e042"
https = "false"
checkforupdates = "true"
apiserver = "false"
logger_interval = 5
rememberme_interval = 5
ban_ip = "true"
autoreload = "true"
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
Processor = "false"
Ssh = "false"
Swap = "false"
Uptime = "true"
[plugins]
Server = "true"
Process = "true"