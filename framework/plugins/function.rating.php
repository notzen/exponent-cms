<?php

##################################################
#
# Copyright (c) 2004-2006 OIC Group, Inc.
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

/**
 * Smarty plugin
 * @package Smarty-Plugins
 * @subpackage Function
 */

/**
 * Smarty {rating} function plugin
 *
 * Type:     function<br>
 * Name:     rating<br>
 * Purpose:  display a rating
 *
 * @param         $params
 * @param \Smarty $smarty
 * @return bool
 */
function smarty_function_rating($params,&$smarty) {
    global $user,$db;
    
    expCSS::pushToHead(array(
	    "unique"=>'ratings',
	    "link"=>PATH_RELATIVE."framework/modules/core/assets/css/ratings.css",
	    )
	);
	
    
    $params['subtype'] = isset($params['subtype'])?$params['subtype']:$params['content_type'];
    
    $total_rating = 0;
    if (!empty($params['record']->expRating[$params['subtype']])) {
        foreach ($params['record']->expRating[$params['subtype']] as $rating) {
            $total_rating = $total_rating + $rating->rating;
            if ($rating->poster==$user->id) {
                $myrate = $rating->rating;
            }
        }
        $rating_count = count($params['record']->expRating[$params['subtype']]);
        $total_average = number_format($total_rating/$rating_count,1);
    } else {
        $rating_count = 0;
        $total_average = 0;
    }
    $avg_percent = round($total_average*100/5)+1;
    $html = '
    <div class="star-rating">
        <div id="rating-total-'.$params['subtype'].'" class="star-stats">
            <strong>'.$params['label'].'</strong>
            <div id="user-rating-'.$params['subtype'].'" class="star-bar">
                <div id="star-average-'.$params['subtype'].'" class="star-average" style="width:'.$avg_percent.'%"></div>';
            if ($user->isLoggedIn()) {
        $html.='<div id="my-ratings-'.$params['subtype'].'" class="my-ratings">
                    <span rel="1" class="u-star st1'.($myrate>=1?" selected":"").'">
                        <span rel="2" class="u-star st2'.($myrate>=2?" selected":"").'">
                            <span rel="3" class="u-star st3'.($myrate>=3?" selected":"").'">
                                <span rel="4" class="u-star st4'.($myrate>=4?" selected":"").'">
                                    <span rel="5" class="u-star st5'.($myrate>=5?" selected":"").'">
                                    </span>
                                </span>
                            </span>
                        </span>
                    </span>
                </div>';
                
            }
        if ($rating_count) $html.='</div><em><span class="avg">'.$total_average.'</span> '.gt('avg. by').' <span class="raters">'.$rating_count.'</span> '.gt('people').'</em></div>';
        else $html .= '</div><em><span class="raters">'.gt('Be the first to rate this item.').'</em></div>';
        
        $rated = $db->selectValue('content_expRatings','expratings_id',"content_type='".$params['content_type']."' AND subtype='".$params['subtype']."' AND poster='".$user->id."'");
    $rated_val = $db->selectValue('expRatings','rating',"id='".$rated."' AND poster='".$user->id."'");
    $html .= '
        <div class="rating-form">
            <form id="ratingform-'.$params['subtype'].'" action="index.php" method="post">
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="controller" value="expRating" />
                <input type="hidden" name="content_type" value="'.$params['content_type'].'" />
                <input type="hidden" name="subtype" value="'.$params['subtype'].'" />
                <input type="hidden" name="content_id" value="'.$params['record']->id.'" />';

                $control = new radiogroupcontrol();
                // differentiate it from the old school forms
                $control->newschool = true;
                $control->cols = 0;
                $control->default = $rated_val;
                $control->items = array_combine(explode(',',"1,2,3,4,5"),explode(',',"1,2,3,4,5"));

                $html .= $control->toHTML('','rating');

            $html.='<button>Save Rating</button>
            </form>
        </div>
    </div>
    ';
    
    $content = "
    YUI(EXPONENT.YUI3_CONFIG).use('node','event','io', function(Y) {
        var myrating = '".$myrate."';
        var ratingcount = '".$rating_count."';
        var total_rating = '".$total_rating."';
        var total_average = '".$total_average."';
        var avg_percent = '".$avg_percent."';
        
        function update_totals(mynewrating) {
            if (myrating=='') {
                myrating = mynewrating;
                ratingcount = parseInt(ratingcount)+1
                Y.one('#rating-total-".$params['subtype']." .raters').setContent(ratingcount);
            }
            total_rating = (total_rating==0) ? parseInt(mynewrating) : total_rating-myrating+parseInt(mynewrating);
            total_average = total_rating/ratingcount;
            avg_percent = total_average*100/5;
            Y.one('#rating-total-".$params['subtype']." .avg').setContent(total_average.toFixed(1));
            Y.one('#star-average-".$params['subtype']."').setStyle('width',Math.round(avg_percent)+1+'%');
            myrating = mynewrating;
        }
        
        var iocfg = {
            method: 'POST',
            data: 'json=1&ajax_action=1',
            form: {
                id: 'ratingform-".$params['subtype']."',
                useDisabled: false
            }
        };
        function save_rating() {
            var url = EXPONENT.URL_FULL+'index.php';
            Y.io(url, iocfg);
            Y.on('io:success', onSuccess, this);
            Y.on('io:failure', onFailure, this);
        };
        function onSuccess(id,response,args) {

        };
        function onFailure(id,response,args) {
            alert('woops, something is broke...');
        };

        var myratings = Y.one('#my-ratings-".$params['subtype']."');

        // handles what happens when you click on the stars
        myratings.delegate(
        {
            'click' : function(e) {
                e.stopPropagation();
                e.container.all('.u-star').removeClass('selected');
                update_totals(e.target.getAttribute('rel'))
                e.target.addClass('selected').ancestors('.u-star').addClass('selected');
                var form = Y.one('#ratingform-".$params['subtype']."');
                form.one('[value='+myrating+']').set('checked','checked');
                save_rating();
                //form.submit();
            }
        },'.u-star');

        // handles what happens when you hover over the stars if you've not rated this item before
            myratings.on(
            {
                'mouseenter' : function(e) {
                    myratings.all('.u-star').removeClass('selected');
                },
                'mouseleave' : function(e) {
                    if (myrating!='') {
                        myratings.one('.u-star[rel='+myrating+']').addClass('selected').ancestors('.u-star').addClass('selected');
                    }
                }
            });

    });
    ";

    if ($user->isLoggedIn()) {
        expJavascript::pushToFoot(array(
            "unique"=>'ratings'.$params['subtype'],
            "yui3mods"=>"yui",
            "content"=>$content,
         ));
    }    

    echo $html;
}

?>
