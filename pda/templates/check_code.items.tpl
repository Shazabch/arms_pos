{*
2/27/2013 3:20 PM Fithri
- show stock balance - deduct unfinalized qty (stock with un-finalize sales)

6/17/2013 4:14 PM Justin
- Enhanced show selling price in bigger font size.
- Modified to make all selling price by branch to align by row.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

4/26/2017 11:02 AM Khausalya
- Enhanced changes from RM to use config setting. 

11/06/2020 9:55 AM Sheila
- Fixed table css
*}

{assign var=n value=1}
{if $msg}
	{$msg}
{/if}	
{foreach from=$items item=item max=50}

<div class="item">
<div class="block">
{$n++}. <em><b><font color="#ff0000">{$item.description}</font></b></em><br>
ARMS Code: <em>{$item.sku_item_code}</em><br>
{$config.link_code_name}: <em>{$item.link_code}</em><br>
Artno/MCode: <em>{$item.artno|default:"-"}/{$item.mcode|default:"-"}</em><br>
Vendor: <em>{$item.vendor}</em><br>
Brand: <em>{$item.brand}</em><br>
SKU Type: <em>{$item.sku_type}</em><br>
Selling Price: <em><font size="3">{$config.arms_currency.symbol}{$item.selling_price|number_format:2}</font></em><br />
{if $item.price}
	{foreach from=$item.price key=branch item=price}
	&nbsp; <em><font size="2"><span class="br">{$branch}</span> - <font size="3">{$config.arms_currency.symbol}{$price.price|number_format:2}</font></em>
	<br />
	{/foreach}
{/if}
{if $config.check_code_show_balance}
Location:<em>{$item.location}</em><br>
Stock Balance:<em>{$item.qty}</em><br>
Unfinalised Stock Balance:<em>{$item.unfinalize_qty}</em><br>
{/if}
{if $config.enable_replacement_items and $item.ri_id}
Replacement Item Group: <a href="javascript:void(show_replacement_items('{$item.id}'));">{$item.ri_group_name|default:'-'}</a>
{/if}
{if $item.batch_items}
	Batch No: <em>{$item.batch_no}</em> &nbsp;&nbsp;&nbsp;&nbsp; Expired Date: <em>{$item.expired_date}</em>
	{foreach from=$item.batch_items key=branch item=batch_item name=bn_list}
		{if $branch ne $BRANCH_CODE}&nbsp; <em><span class=br>{$branch}</span></em>{/if}
	{/foreach}
{/if}
</div>

<br style="clear:both">
</div>
{/foreach}
