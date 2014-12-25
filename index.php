<?php
// *************************************************************************
// This file is part of LoadAvg, the server monitoring & analytics platform
// http://www.loadavg.com
// Copyright (c) 2014  AGPL Karsten Becker <k.becker@sputnik7.com>
// *************************************************************************
//  
//  LoadAvg is free software: you may copy, redistribute
//  and/or modify it under the terms of the GNU Affero General Public License 
//  as published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//  
//  This file is distributed in the hope that it will be useful, but
//  WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  Affero General Public License for more details.
//  
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// *************************************************************************

/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Initialize Loadavg
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

/* read in globals */
require_once 'globals.php';

ob_start(); 

/* Initialize LoadAvg */ 
include 'class.LoadAvg.php';
$loadavg = new LoadAvg();

$settings = LoadAvg::$_settings->general;

/* Force https when in https mode */
if ( $settings['settings']['https'] == "true" && !isset($_SERVER["HTTPS"])) {
	header("Location: https://" . $_SERVER['SERVER_NAME'] .$_SERVER['REQUEST_URI']);
} else {
	header("Location: public/");
}

?>