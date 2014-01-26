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

/* Application PATH */
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__) . '/app'));

// Add lib/ to include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_PATH . '/../lib'),
    realpath(APP_PATH . '/../lib/modules'),
    get_include_path(),
)));

