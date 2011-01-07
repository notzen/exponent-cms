{*
 * Copyright (c) 2007-2008 OIC Group, Inc.
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

<div class="module cart show-inline">
    {if $moduletitle}<h2>{$moduletitle}</h2>{/if}
    <div class="total">
        Total: <span class="carttotal">{currency_symbol}{$order->total|number_format:2}</span>
    </div>
    <ul>
        {foreach from=$items item=item}
            <li class="{cycle values="odd,even"}">
                <a href="{link controller=store action=show id=$item->product_id}">{if $item->product->expFile[0]->id}{img file_id=$item->product->expFile[0]->id square=55}{/if}{$item->products_name}</a>
                 {$item->quantity} @ <span class="price">{currency_symbol}{$item->products_price|number_format:2}</span>
                {br}
                <a href="{link action=removeItem id=$item->id}" class="removefromcart">Remove from cart</a>
                {clear}
            </li>
        {foreachelse}
            <li>You currently have no items in your cart</li>
        {/foreach}
    </ul>
</div>
