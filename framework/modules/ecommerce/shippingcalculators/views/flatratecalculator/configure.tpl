{*
 * Copyright (c) 2004-2011 OIC Group, Inc.
 * Written and Designed by Adam Kessler
 *
 * This file is part of Exponent
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * GPL: http://www.gnu.org/licenses/gpl.txt
 *
 *}

<div id="authcfg" class="hide exp-skin-tabview">
    
    <div id="auth" class="yui-navset">
        <ul class="yui-nav">
        <li class="selected"><a href="#tab1"><em>General</em></a></li>
        </ul>            
        <div class="yui-content">
        <div id="tab1">
            {control type="text" name="rate" label="Flat Rate Shipping & Handling Charge" size=5 filter=money value=$calculator->configdata.rate}
            {control type="textarea" name="out_of_zone_message" label="Message to Out-of-Zone Buyers" size=15 value=$calculator->configdata.out_of_zone_message} 
        </div>
        </div>
    </div>
</div>
<div class="loadingdiv">Loading</div>

{script unique="editform" yui3mods=1}
{literal}
    YUI(EXPONENT.YUI3_CONFIG).use('node','yui2-tabview','yui2-element', function(Y) {
        var YAHOO=Y.YUI2;

        var tabView = new YAHOO.widget.TabView('auth');
        Y.one('#authcfg').removeClass('hide').next().remove();
    });
{/literal}
{/script}
