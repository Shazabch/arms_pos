{*
4/4/2012 10:01:55 AM Alex
- moving onclick function into javascript void()

3/11/2015 5:37 PM Andy
- Enhanced to show (FOC) when item foc is tick or price is zero.

11/9/2018 10:31 AM Justin
- Enhanced to highlight the item row when it is called from SKU Change Price.
*}

{if $qprice}
	{*for qprice only*}
	{foreach name=i from=$history key=timestamp item=mpu}
	<tr	style="background: #ffddaa;" {if $smarty.foreach.i.iteration >3} class="hidden" {/if} >
		<td colspan=4>{$timestamp}</td>
	</tr>
	    {foreach from=$mpu key=min_qty item=pu}
	        {foreach from=$pu key=price item=r}
			<tr {if $smarty.foreach.i.iteration > 3} class="childhidden" {/if}>
				<td align="center">{$min_qty}</td>
				<td class="r">{$price|number_format:2}</td>
				<td>{$r.user|default:"<font color=green>System</font>"}</td>
				<td>
					{if $r.fp_id && $r.fpi_id && $r.fp_branch_id}
						{assign var=highlight_sku_item_id value=0}
						{if $r.fpi_id eq $r.sku_item_id}
							{assign var=highlight_sku_item_id value=$r.sku_item_id}
						{/if}					
					
						Batch Price Change #<a href="masterfile_sku_items.future_price.php?a=view&id={$r.fp_id}&branch_id={$r.fp_branch_id}&{if highlight_sku_item_id>0}highlight_sku_item_id={$highlight_sku_item_id}{else}highlight_item_id={$r.fpi_id}{/if}" target="_blank">{$r.fp_id|string_format:"%05d"}</a>
					{/if}
				</td>
			</tr>
		    {/foreach}
	    {/foreach}
	{/foreach}
	<div id='show_result_id' class="bottom"><a href="javascript:void(show_more_result());">Show more results</a></div>
{else}
	{*for mprice only*}
	{section name=i loop=$history}
	<tr>
		<td>{$history[i].added}</td>
		<td class="r">
			{$history[i].price|number_format:2}
			{if $history[i].price eq '0' or $history[i].selling_price_foc}
				<br />
				<i>(FOC)</i>
			{/if}
		</td>
		<td align="center">{$history[i].trade_discount_code}</td>
		<td>{$history[i].user|default:"<font color=green>System</font>"}</td>
		<td>
			{if $history[i].fp_id && $history[i].fpi_id && $history[i].fp_branch_id}
				{assign var=highlight_sku_item_id value=0}
				{if $history[i].fpi_id eq $history[i].sku_item_id}
					{assign var=highlight_sku_item_id value=$history[i].sku_item_id}
				{/if}
				Batch Price Change #<a href="masterfile_sku_items.future_price.php?a=view&id={$history[i].fp_id}&branch_id={$history[i].fp_branch_id}&{if highlight_sku_item_id>0}highlight_sku_item_id={$highlight_sku_item_id}{else}highlight_item_id={$history[i].fpi_id}{/if}" target="_blank">{$history[i].fp_id|string_format:"%05d"}</a>
			{/if}
		</td>
	</tr>
	{/section}
{/if}
