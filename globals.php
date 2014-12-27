<?php

/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Initialize globals
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

ini_set('display_errors', 'On');
error_reporting(E_ALL);


/* Find out where are we on the server*/

$script_path = realpath(basename(getenv("SCRIPT_NAME")));
$slash = explode('/', getenv("SCRIPT_NAME"));
$current_filename = $slash[count($slash) - 1]; 
$host_url = str_replace($current_filename, "", getenv("SCRIPT_NAME"));
$ROOT_PATH = dirname ($host_url);

//add trailing slash..
//need to check also if it dont end in a / ?
if ( $ROOT_PATH != "/") $ROOT_PATH = $ROOT_PATH . "/";


/* Set script version */

$loadavg_version = "2.1";

defined('SCRIPT_VERSION') || define('SCRIPT_VERSION', $loadavg_version );

/* Set Application Globals */

defined('SCRIPT_ROOT') || define('SCRIPT_ROOT', $ROOT_PATH );

defined('HOME_PATH') || define('HOME_PATH', realpath(dirname(__FILE__) ));

/* Application PATH */
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) . '/app'));

/* log PATH */
defined('LOGGER') || define('LOGGER',  'loadavg' );
defined('LOG_PATH') || define('LOG_PATH',  HOME_PATH . '/logs/' );

/* for collectd support in loadavg */
//defined('LOGGER') || define('LOGGER',  'collectd' );
defined('COLLECTD_PATH') || define('COLLECTD_PATH',  '/var/lib/collectd/csv/localhost/' );

// Add lib/ to include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_PATH . '/../lib'),
    realpath(APP_PATH . '/../lib/modules'),
    realpath(APP_PATH . '/../lib/plugins'),
    get_include_path(),
)));

