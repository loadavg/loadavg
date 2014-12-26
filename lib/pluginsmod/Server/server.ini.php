; <?php exit(); __halt_compiler(); ?>
[module]
name = "Network Usage"
description = "This module is used to display and log server data."
status = true
has_settings = false
has_menu = false
logable = false

[cmd]
hostname = "hostname -d"
nodename = "uname -n"
os = "uname -o"
kernel = "uname -sr"
kernel_version = "uname -v"
hardware_name = "uname -m"
hardware_platform = "uname -i"
proc_type = "uname -p"
proc_count = "cat /proc/cpuinfo | grep processor | wc -l"
proc_model = "cat /proc/cpuinfo | grep 'model name' | awk -F':' '{print $2}'"
mem_usage = "free -m"
mem_total = "free -m | grep Mem | awk -F' ' '{print $2}'"
mem_used = "free -m | grep Mem | awk -F' ' '{print $3}'"
mem_free = "free -m | grep Mem | awk -F' ' '{print $4}'"
mem_shared = "free -m | grep Mem | awk -F' ' '{print $5}'"
mem_buffers = "free -m | grep Mem | awk -F' ' '{print $6}'"
mem_cached = "free -m | grep Mem | awk -F' ' '{print $7}'"
swap_total = "free -m | grep Swap | awk -F' ' '{print $2}'"
swap_used = "free -m | grep Swap | awk -F' ' '{print $3}'"
swap_free = "free -m | grep Swap | awk -F' ' '{print $4}'"
network_name = "ifconfig -a | sed -n 's/^\([^ ]\+\).*/\1/p' | grep -Fvx -e lo: -e lo"
network_ip = "ifconfig | grep 'inet addr:'| grep -v ':127\.0\.0\.1 ' | cut -d: -f2 | awk '{ print $1}'"
hdd_usage = "df -h"
uptime = "uptime"


