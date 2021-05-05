{*
8/10/2010 10:20:11 AM Andy
- Add "Open Price" and "Item Discount" Info.

10/21/2011 6:00:04 PM Andy
- Show receipt discount & mix and match discount in cashier performance/abnormal report.

1/12/2012 10:13:43 AM Justin
- Added Prune Count column.

3/1/2012 10:22:47 AM Andy
- Fix colspan bugs.

9/14/2018 5:51 PM Andy
- Enhanced to hide column "Variance" if got filter.
- Enhanced the amount column to show active transaction amount and cancelled transaction amount.

11/16/2018 4:06 PM Justin
- Enhanced to have Allow Cancelled Bill and Prune Bill, Allow and Count for Deleted Items.
- Bug fixed on the Allow countings where it sum up wrongly when filter or click on specific cashier.
*}

{literal}
<style>
.tbd{
	border-collapse:collapse;
}
.tbd tr{
    border-right:1px black solid;
	border-top:1px black solid;
}
.tbd th,.tbd td{
	border-left:1px black solid;
	border-bottom:1px black solid;
	padding-left:5px;
}
</style>

<script>

</script>
{/literal}

<h3 align="center">Cashier: {$cashier.name}</h3>
{if !$table}
No Data
{else}
<div class="div_content">
<table width="100%" class="tbd" cellpadding="2">
<tr class="header">
	<th rowspan="3">No.</th>
	<th rowspan="3">Date</th>
	{assign var=cols value=22}
	{if $got_mm_discount}{assign var=cols value=$cols+2}{/if}
	{if $show_variances}{assign var=cols value=$cols+1}{/if}
	<th colspan="{$cols}">Total</th>
	<th rowspan="3">AVG Tran. Time</th>
</tr>
<tr class="header">
	<th rowspan="2">Amount</th>
    <th rowspan="2">Tran.</th>
    <th colspan="2">Open Price</th>
    <th colspan="3">Item Discount</th>
    <th colspan="3">Receipt Discount</th>
    {if $got_mm_discount}
    	<th colspan="2">Mix & Match Discount</th>
    {/if}
    <th rowspan="2">Open Drawer</th>
    <th colspan="2">Cancelled Bill</th>
    <th colspan="2">Prune Bill</th>
    <th colspan="2">Delete Items</th>
    <th rowspan="2">Goods Return</th>
	{if $show_variances}
		<th rowspan="2">Variance</th>
	{/if}
    <th colspan="2">Member Sales</th>
    <th colspan="2">Non Member sales</th>
</tr>
<tr class="header">
	<!-- Open Price -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Item Discount -->
	<th>Allow</th>
	<th>Count</th>
	<th>Amount</th>
	
	<!-- Receipt Discount -->
	<th>Allow</th>
	<th>Count</th>
	<th>Amount</th>
	
	<!-- Cancel Bill -->
	<th>Allow</th>
	<th>Count</th>

	<!-- Prune Bill -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Delete Items -->
	<th>Allow</th>
	<th>Count</th>
	
	<!-- Mix and Match Discount -->
	{if $got_mm_discount}
		<th>Count</th>
		<th>Amount</th>
	{/if}
	
	<th>Tran.</th>
	<th>Amount</th>
	<th>Tran.</th>
	<th>Amount</th>
</tr>
	{foreach from=$table key=date item=r name=f}
	    <tr class="thover">
	        <td>{$smarty.foreach.f.iteration}</td>
	        <td>
	            {if $cashier.id eq $r.user_id}
					<a href="javascript:tran_details('{$r.user_id}','{$r.branch_id}','{$date}');">{$date}</a>
				{else}
					{$date}
				{/if}
			</td>
	        <td class="r" nowrap>
				{$r.amount|number_format:2}
				{if $r.cancelled_amount}
					<br />
					<span class="span_cancelled_amount small" title="Cancelled Amount: {$r.cancelled_amount|number_format:2}">C: {$r.cancelled_amount|number_format:2}</span>
				{/if}
			</td>
	        <td class="r">{$r.tran_count|number_format}</td>
	        
	        <!-- Open Price -->
	        <td class="r">{$r.allow_open_price|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.open_price|number_format}</td>
	        
	        <!-- Item Discount -->
	        <td class="r">{$r.allow_item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.item_discount_amt|number_format:2|ifzero:'-'}</td>
	        
	        <!-- Receipt Discount -->
	        <td class="r">{$r.allow_receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount|number_format|ifzero:'-'}</td>
	        <td class="r">{$r.receipt_discount_amt|number_format:2|ifzero:'-'}</td>
	        
	        <!-- Mix and Match Discount -->
			{if $got_mm_discount}
				<td class="r">{$r.mm_discount|number_format|ifzero:'-'}</td>
	        	<td class="r">{$r.mm_discount_amt|number_format:2|ifzero:'-'}</td>
			{/if}
	
			<!-- Open Drawer -->
	        <td class="r">{$r.drawer_open_count|number_format}</td>
			
			<!-- Cancel Bills -->
	        <td class="r">{$r.allow_cancelled_bill|number_format}</td>
	        <td class="r">{$r.cancelled_bill|number_format}</td>
			
			<!-- Prune Bills -->
	        <td class="r">{$r.allow_prune_bill|number_format}</td>
	        <td class="r">{$r.prune_bill|number_format}</td>
			
			<!-- Delete Items -->
	        <td class="r">{$r.allow_deleted_items|number_format}</td>
	        <td class="r">{$r.deleted_items|number_format}</td>
			
			<!-- Goods Return -->
	        <td class="r">{$r.total_goods_return|number_format}</td>
			{if $show_variances}
				<td class="r">{$r.variances|number_format:2}</td>
			{/if}
	        <td class="r">{$r.member_sells.qty|number_format}</td>
	        <td class="r">{$r.member_sells.amount|number_format:2}</td>
	        <td class="r">{$r.non_member_sells.qty|number_format}</td>
	        <td class="r">{$r.non_member_sells.amount|number_format:2}</td>
	        <td class="r" nowrap>
			{if $r.avg_tran_time_hour}{$r.avg_tran_time_hour} hours{/if}
			{if $r.avg_tran_time_min}{$r.avg_tran_time_min} mins{/if}
			{if $r.avg_tran_time_sec}{$r.avg_tran_time_sec} secs{/if}
			</td>
	    </tr>
	{/foreach}
</table>
</div>
{/if}
