## About LoadAvg

LoadAvg is a powerful and free way to manage load, memory and resource usage on linux servers, cloud computers and virtual machines.

http://www.loadavg.com

## How To Install The LoadAvg App

You will need to install LoadAvg in a location where you can access it directly over your web browser. There are three ways to do this, one is inside your webroot, the other is as a subdomain and you can also use a Apache hardcoded link.

### Dependence:

This application requires a web server to be installed (currently tested on Apache V2.x however lightppd or nginx should work) and PHP 5.0 or greater to be installed and accessable via your web server.

### Installation:

 - Clone loadavg on your server, in the loaction you wish to access it
   `# git clone https://github.com/loadavg/loadavg.git`
 
 - Enter the loadavg  source folder
   `# cd loadavg`
 
 - Run the configure script
   `# sudo ./configure`

 - Navigate to the location of the installation in your web browser

 - Complete the online installation
 
 - Delete the install script for security
 
 - Add the logger entry to your crontab

### Usage:

- Navigate to the location of the installation in your web browser

## Extra Info

This application is distributed WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
[GNU AFFERO GENERAL PUBLIC LICENSE](LICENSE) for more details.

Your feedback is appreciated, for help and assistance or to get involved please visit our forums.

http://www.loadavg.com/forum/
