<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
# Written and Designed by James Hunt
#
# This file is part of Exponent
#
# Exponent is free software; you can redistribute
# it and/or modify it under the terms of the GNU
# General Public License as published by the Free
# Software Foundation; either version 2 of the
# License, or (at your option) any later version.
#
# GPL: http://www.gnu.org/licenses/gpl.txt
#
##################################################
/** @define "BASE" "." */

/**
 * The file that initializes everything
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Adam Kessler <adam@oicgroup.net>
 * @version 2.0.0
 */

// Initialize the exponent environment
require_once('exponent_bootstrap.php');

// Initialize the MVC framework - for objects we need loaded now
require_once(BASE.'framework/core/expFramework.php');

// Initialize the Sessions subsystem
expSession::initialize();

// Initialize the Theme subsystem
expTheme::initialize();

// Initialize the language subsystem
expLang::loadLang();

// Initialize the Database subsystem
$db = expDatabase::connect(DB_USER,DB_PASS,DB_HOST.':'.DB_PORT,DB_NAME);

// Initialize the Modules subsystem & Create the list of available/active controllers
$available_controllers = expModules::initializeControllers();  //original position
//$available_controllers = array();
//$available_controllers = initializeControllers();
//foreach ($db->selectObjects('modstate',1) as $mod) {
//	if (!empty($mod->path)) $available_controllers[$mod->module] = $mod->path;  //FIXME test location
//}

// Initialize the History (Flow) subsystem.
$history = new expHistory(); //<--This is the new flow subsystem

// Initialize the javascript subsystem
if (expJavascript::inAjaxAction()) set_error_handler('handleErrors');

// Validate the session and populate the $user variable
if ($db->havedb) {
	$user = new user();
	expSession::validate();
}

/* exdoc
 * The flag to use a mobile theme variation.
 */
if (!defined('MOBILE')) {
	if (defined('FORCE_MOBILE') && FORCE_MOBILE && $user->isAdmin()) {
		define('MOBILE',true);
	} else {
		define('MOBILE',expTheme::is_mobile());
	}
}

// Initialize permissions variables
$exponent_permissions_r = expSession::get("permissions");

// initialize the expRouter
$router = new expRouter();

// Initialize the navigation hierarchy
if ($db->havedb)
	$sections = expCore::initializeNavigation();

/**
 * dumps the passed variable to screen, but only if in development mode
 * @param  $var the variable to dump
 * @param bool $halt if set to true will halt execution
 * @return void
 */
function eDebug($var, $halt=false){
	if (DEVELOPMENT) {
		echo "<xmp>";
		print_r($var);
		echo "</xmp>";
		
		if ($halt) die();
	}
}

/**
 * dumps the passed variable to a log, but only if in development mode
 * @param  $var the variable to log
 * @param string $type the type of entry to record
 * @param string $path the pathname for the log file
 * @param string $minlevel
 * @return void
 */
function eLog($var, $type='', $path='', $minlevel='0') {
	if($type == '') { $type = "INFO"; }
	if($path == '') { $path = BASE . 'tmp/exponent.log'; }
	if (DEVELOPMENT >= $minlevel) {
		if (is_writable ($path) || !file_exists($path)) {
			if (!$log = fopen ($path, "ab")) {
				eDebug(gt("Error opening log file for writing."));
			} else {
				if (fwrite ($log, $type . ": " . $var . "\r\n") === FALSE) {
					eDebug(gt("Error writing to log file")." (".$path.").");
				}
				fclose ($log);
			}
		} else {
			eDebug(gt("Log file"." (".$path)." ".gt("not writable."));
		}
	}
}

?>