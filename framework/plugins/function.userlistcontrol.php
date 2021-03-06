<?php

##################################################
#
# Copyright (c) 2007-2008 OIC Group, Inc.
# Written and Designed by Adam Kessler
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

/**
 * Smarty plugin
 * @package Smarty-Plugins
 * @subpackage Function
 */

/**
 * Smarty {userlistcontrol} function plugin
 *
 * Type:     function<br>
 * Name:     userlistcontrol<br>
 * Purpose:  display a list control of users
 *
 * @param         $params
 * @param \Smarty $smarty
 * @return bool
 */
function smarty_function_userlistcontrol($params,&$smarty) {
	echo '<script src="'.PATH_RELATIVE.'framework/core/subsystems/forms/controls/listbuildercontrol.js" language="javascript"></script>';

	global $db;
	$users = $db->selectObjects("user",null,"username");
	
	foreach ($users as $user) {
		$allusers[$user->id] = "$user->lastname, $user->firstname($user->username)";
	}
	
	$control = new listbuildercontrol(null, $allusers, 5);
	$name = isset($params['name']) ? $params['name'] : "userlist";
	echo $control->controlToHTML($name);
}

?>
