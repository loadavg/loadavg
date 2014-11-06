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

ini_set('display_errors', 'Off');
error_reporting(E_ALL);


/* Find out where are we on the server*/

$script_path = realpath(basename(getenv("SCRIPT_NAME")));
$slash = explode('/', getenv("SCRIPT_NAME"));
$current_filename = $slash[count($slash) - 1]; 
$host_url = str_replace($current_filename, "", getenv("SCRIPT_NAME"));
$ROOT_PATH = dirname ($host_url);

//add trailing slash
if ( $ROOT_PATH != "/") $ROOT_PATH = $ROOT_PATH . "/";


/* Set script version */

$loadavg_version = "2.0";

defined('SCRIPT_VERSION') || define('SCRIPT_VERSION', $loadavg_version );

/* Set Application Globals */

defined('SCRIPT_ROOT') || define('SCRIPT_ROOT', $ROOT_PATH );

defined('HOME_PATH') || define('HOME_PATH', realpath(dirname(__FILE__) ));

/* Application PATH */
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) . '/app'));

// Add lib/ to include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_PATH . '/../lib'),
    realpath(APP_PATH . '/../lib/modules'),
    get_include_path(),
)));

