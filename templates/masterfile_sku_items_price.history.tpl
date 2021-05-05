{*
11/8/2018 4:10 PM Justin
- Enhanced to highlight the item row when it is called from SKU Change Price.
*}

{if !$history}
- no Selling Price change history - 
{else}
<table cellpadding=2 cellspacing=1 border=0 width=100%>
{if $qprice}
	<tr height=24 bgcolor=#ffee99>
		<th>Min Qty (&gt;=)</th><th>Price</th><th>User</th><th>More Info</th><th width=16>&nbsp;</th>
	</tr>
{else}
	<tr height=24 bgcolor=#ffee99>
		<th>Date/Time</th><th>Price</th><th>Price Type</th><th>User</th><th>More Info</th><th width=16>&nbsp;</th>
	</tr>
{/if}
<tbody id='history_id' style="height:255px;overflow:auto;">
{include file=masterfile_sku_items_price.history.detail.tpl}
</tbody>
</table>
{/if}
