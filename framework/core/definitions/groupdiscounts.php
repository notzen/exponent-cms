<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
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

if (!defined('EXPONENT')) exit('');

return array(
	'id'=>array(
		DB_FIELD_TYPE=>DB_DEF_ID,
        DB_PRIMARY=>true,
        DB_INCREMENT=>true),
	'group_id'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'discounts_id'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'dont_allow_other_discounts'=>array(
        DB_FIELD_TYPE=>DB_DEF_BOOLEAN),
    'rank'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
);

?>
