{if $shipping->pricelist|is_array == true}
<span class="shippingmethod{if $noShippingPrices} hide{/if}">
    <strong id="shipping-service"><img class="shippingmethodimg" src="{$shipping->calculator->icon}">
    <span id="shprceup">{$shipping->shippingmethod->option_title}{br} (${$shipping->shippingmethod->shipping_cost|number_format:2})</span></strong>
    
        {if $order->forced_shipping != true}
        <span class="bracket">
            {if $shipping->pricelist|@count > 1}
                <a id="shippingmethodoptionslink" class="ecom-link {$shpMthdOp.id} changeselection" href="#">Change Shipping Service Option</a>
            {else}
                <span>There are no options for {$shipping->shippingmethod->option_title}</span>
            {/if}
        </span>
        {/if}
</span>
<div style="clear:both"></div>
<div id="shippingmethodoptions" class="exp-dropmenu">
    <div class="hd"><span class="type-icon"></span>Choose a Shipping Service Option</div>
    <div class="bd">
        {form name="shpmthdopts" controller=shipping action=selectShippingOption}
            {control type=hidden id=option name=option value=$shipping->calculator->id}
            {foreach from=$shipping->pricelist item=option}
                {control type=hidden name="cost[`$option.id`]" value=$option.cost}
            {/foreach}
        {/form}
        <ul>
            {foreach from=$shipping->pricelist item=option}
                {if $option.id == $shipping->shippingmethod->option}{assign var=selected value=true}{else}{assign var=selected value=false}{/if}
                <li><a rel="{$option.id}" href="#" class="shpmthdopswtch{if $shpMthdOp.id == $option.id} current{/if}">{$option.title} (${$option.cost|number_format:2})</a></li>
            {/foreach}
        </ul>
    </div>
</div>
{else}
<div id="shipping-error" class="error">
    {$shipping->pricelist}
</div>
{/if}
