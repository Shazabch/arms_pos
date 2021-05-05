{*
1/20/2011 12:16:11 PM Justin
- Added 2 fields which is Created and Approved.

1/14/2013 2:09 PM Justin
- Enhanced to show header of Voucher Value if found config "membership_use_voucher".

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

2/15/2017 3:29 PM Zhi Kai
- Change wording from 'User Current Date' to 'Use Current Date'.
*}

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul> 
{else}
    <table width=100% style="padding:5px; background-color:#fe9" class="input_no_border small body" border=0 cellspacing=1 cellpadding=1 id="tbl_items">
	<thead>
		<tr bgcolor=#ffffff>
			{if $smarty.request.t ne '3'}
		  		<th rowspan=2><input type="checkbox" onChange="toggle_all_status(this);"></th>
		  	{/if}
		  	<th rowspan=2>#</th>
			<th rowspan=2>Created By</th>
			<th rowspan=2>Available to</th>
			<th rowspan=2>Arms Code</th>
			<th rowspan=2>Desciption</th>
			<th rowspan=2>Cost</th>
			<th rowspan=2>Selling<br>Price</th>
			<th rowspan=2>Stock<br>Balance</th>
			<th rowspan=2>Point</th>
			<th rowspan=2>Cash</th>
			<th colspan=2>Valid</th>
			<th colspan="4">Receipt</th>
			{if $smarty.request.t eq '3'}
				<th rowspan=2>Cancelled By</th>
				<th rowspan=2>Cancelled Date</th>
			{/if}
			<th rowspan=2>Created By</th>
			{if $smarty.request.t ne '1'}
				<th rowspan=2>Approved By</th>
			{/if}
			{if $config.membership_use_voucher}
				<th rowspan=2>Voucher<br />Value</th>
			{/if}
		</tr>
		<tr bgcolor=#ffffff>
			<th>Date Start</th>
			<th>Date End</th>
			<th>Amount</th>
			<th>Date Start</th>
			<th>Date End</th>
			<th>Use Current Date</th>
		</tr>
	</thead>
	<tbody id="tbody_item_list">
		{foreach from=$redemption_items item=item name=fitem}
			{include file='membership.redemption_item_approval.list_row.tpl'}
			<script>items_row_count = int('{$smarty.foreach.fitem.iteration}');</script>
		{foreachelse}
			<tr id="tr_no_item" align="center">
				<td colspan="21"><p style="padding:10px;" class="large">-- No Item --</p></td>
			</tr>
		{/foreach}
	</tbody>
	    <tr id="tbl_footer">
		</tr>
	</table>
{/if}
