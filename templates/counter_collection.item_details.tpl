{*
///////// use by following files ///////////
- counter_collection2.php
- counter_collection3.php
- masterfile_sku.php

////////// Changes Log //////////////
10/8/2010 5:03:07 PM
- pos_report.cashier_performance.php

3/28/2011 5:07:28 PM Justin
- Added extra info for Receipt Detail.

3/29/2011 3:54:44 PM Justin
- Added membership no info.

4/29/2011 5:39:05 PM Alex
- add show return item, item discount and open discount user 

6/10/2011 10:59:18 AM Andy
- Add print transaction details.

6/21/2011 6:03:55 PM Alex
- add payment type approve by which user

7/12/2011 2:43:04 PM Andy
- Fix when printing page no need to show scroll bar. 

7/15/2011 11:20:45 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/14/2011 4:20:15 PM Andy
- Add mix and match promotion info at receipt details.

12/20/2011 9:56:51 AM Andy
- Add show "Receipt Remark" at Receipt Item Details.
- Add show "Item Remark" at Receipt Item Details.

2/17/2012 9:46:43 AM Justin
- Added Deposit table.

9/3/2012 11:47 AM Fithri
- Item details - show barcode

7/3/2013 4:39 PM Andy
- Show cancelled status in transaction details.

8/20/2013 10:15 AM Andy
- Fix deposit item selling price cannot show *.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

11/27/2014 5:02 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.
- Enhance to display a better layout for receipt item details.
- Add to show the deleted item in receipt item details.

4/21/2015 5:42 PM Andy
- Enhanced to have view receipt mode.
- Enhanced to have goods return receipt link.
- Enhanced to show item GST indicator.

5/5/2015 3:32 PM Andy
- Enhanced to show branch code in transaction details header.

03/24/2016 17:45 Edwin
- Enchanced on showing Receipt Reference Number in tables and details pop out

05/20/2016 14:30 Edwin
- Enhanced on show or hide "Inc. GST" wording base on pos's gst status.

10/27/2016 11:13 AM Qiu Ying
- Bug fixed on receipt reference number does not match with actual receipt print

1/9/2017 13:30 Qiu Ying
- Bug fixed on print empty item details from GST Credit Note Report

3/2/2017 3:00 PM Justin
- Enhanced to show deposit list and clickable.

4/19/2017 2:35 PM Khausalya 
- Enhanced changes from RM to use config setting.

11/29/2017 11:35 AM Andy
- Enhanced to show Sales Agent Code and Name when view transaction details.

6/20/2018 3:49 PM Justin
- Enhanced to show the payment type capture for "Currency Adjust" instead of "Currency_Adjust".

10/18/2018 5:28 PM Justin
- Enhanced to use pre-generated sales agent data instead of the original one to show the Sales Agent list.

4/17/2020 11:07 AM Andy
- Moved "Print" button to stick at bottom right corner.

10/12/2020 6:00 PM William
- Added new tax checking.
- Change GST Word to Tax.
*}

{if $is_print}
	{include file='header.print.tpl'}
{/if}

{literal}
<style>
.hightlight {
	color:red;
}
.deleted_item_row{
	background-color: #fcc;
	color:red;	
}

.deleted_item_row .strike{
	text-decoration: line-through;
}
</style>
{/literal}
<h1>({$pos.branch_code}) Receipt Detail: {$items[0].network_name|default:$deposit_info.network_name} / {$items[0].cashier_name|default:$deposit_info.cashier_name} / #{receipt_no_prefix_format branch_id=$items[0].branch_id counter_id=$items[0].counter_id receipt_no=$items[0].receipt_no|default:$deposit_info.receipt_no} - {$items[0].pos_time|default:$deposit_info.pos_time|date_format:'%Y-%m-%d %I:%M%p'} </br>Receipt Ref. Num: {$items[0].receipt_ref_no|default:$deposit_info.receipt_ref_no}</h1>
<div style="{if !$is_print}overflow:auto;height:350px;{/if}">

{if $pos.cancel_status}<h4>(Receipt Cancelled)</h4>{/if}
{if $pos.member_no}<h4>Membership No.: {$pos.member_no}</h4>{/if}
{if $pos.sa_data}
	<h4>
		Sales Agent: 
		{foreach from=$pos.sa_data key=sa_id item=sa_info name=fsa}
			{$sa_info.code} - {$sa_info.name}
			{if !$smarty.foreach.fsa.last}, {/if}
		{/foreach}		
	</h4>

{/if}

{if $items}
<table class="tb" width=100% cellpadding=4 cellspacing=0 border=0>
<tr class=header style="background:#fe9">
	<th rowspan=2>ARMS Code</th>
	<th rowspan=2>MCode</th>
	<th rowspan=2>Barcode</th>
	<th rowspan=2>Description</th>
	<th rowspan=2>Qty</th>
	<th colspan=3>Price</th>
	{if $pos.is_gst || $pos.is_tax_registered}
		<th rowspan="2">Tax</th>
	{/if}
</tr>
<tr class=header style="background:#fe9">
	<th>Actual</th>
	<th>Discount</th>
	<th>Selling ({$config.arms_currency.symbol})</th>
</tr>

{assign var=total_amount value=0}
{foreach from=$items item=item}
<tr {if $sku_code eq $item.sku_item_code or $smarty.request.highlight_item_id eq $item.item_id}class="hightlight"{/if}>
	<td>{$item.sku_item_code|default:'-'}</td>
	<td>{$item.mcode|default:'-'}</td>
	<td>{$item.barcode|default:'-'}</td>
	<td>{$item.description|default:'-'}
		{if $item.open_price_user || $item.item_discount_user || $item.return_user || $item.trade_in_by || $item.writeoff_by || $item.verify_code_by || $item.item_sa}
		<span class="small">
			{if $item.open_price_user}<br>(Open price by: <font color="blue">{$item.open_price_user}</font>)	{/if}
			{if $item.item_discount_user}<br>(Item discount by: <font color="blue">{$item.item_discount_user}</font>)	{/if}
			{if $item.return_user}<br>(Return by: <font color="blue">{$item.return_user}</font>){/if}
			{if $item.pos_goods_return}
				<br />&lt;<a href="/counter_collection.php?a=view_tran_details&branch_id={$item.pos_goods_return.branch_id}&date={$item.pos_goods_return.return_date}&counter_id={$item.pos_goods_return.return_counter_id}&pos_id={$item.pos_goods_return.return_pos_id}&highlight_item_id={$item.pos_goods_return.return_item_id}" target="_blank">Return Receipt</a>&gt;
			{/if}
			{if $item.trade_in_by}<br>(Trade In by: <font color="blue">{$item.trade_in_by_u}</font>){/if}
			{if $item.writeoff_by}<br>(Write-Off by: <font color="blue">{$item.writeoff_by_u}</font>){/if}
			{if $item.verify_code_by}<br>(Verified by: <font color="blue">{$item.verify_code_by_u}</font>){/if}
			{if $item.item_sa}
				<br />
				<font color="blue">
					Sales Agent: 
					{foreach from=$item.item_sa item=sa_id name=fsa}
						{$pos.sa_data.$sa_id.code} - {$pos.sa_data.$sa_id.name}
						{if !$smarty.foreach.fsa.last}, {/if}
					{/foreach}
				</font>
			{/if}
		</span>
		{/if}
		{if $item.remark}<br>Remark: <i>{$item.remark}</i>{/if}
	</td>
	{capture assign=fields}{$smarty.request.counter_id},'{$smarty.request.date}',{$smarty.request.pos_id},{$smarty.request.branch_id},{$item.id},this{/capture}
	<td {if $editable}onclick="editable({$fields},'qty')"{/if} class=editable>{$item.qty}</td>
	<td {if $editable}onclick="editable({$fields},'price')"{/if} class=editable align=right>{$item.price|number_format:2}</td>
	<td {if $editable}onclick="editable({$fields},'discount')"{/if} class=editable align=right>{$item.discount|number_format:2}{if $item.discount_str}<br /><span class="small">(<font color="blue">{$item.discount_str|number_format:2}%</font>)</span>{/if}</td>
	<td align=right>{$item.price-$item.discount|number_format:2}</td>
	{assign var=total_amount value=$total_amount+$item.price-$item.discount}
	
	{if $pos.is_gst || $pos.is_tax_registered}
		<td align="center">{$item.tax_indicator}</td>
	{/if}
</tr>
{/foreach}

{if $receipt_info.deleted_item_list}
	{foreach from=$receipt_info.deleted_item_list item=r}
		<tr class="deleted_item_row">
			<td class="strike">{$r.sku_item_code|default:'-'}</td>
			<td class="strike">{$r.mcode|default:'-'}</td>
			<td class="strike">{$r.barcode|default:'-'}</td>
			<td>
				<span  class="strike">{$r.description|default:'-'}</span>
				<br /><span style="color:blue;" class="small">Deleted by: {$r.delete_by_u}</span>
			</td>
			<td class="strike">{$r.qty|default:0}</td>
			<td class="strike" align="right">{$r.price|number_format:2}</td>
			<td class="strike" align="right">-</td>
			<td class="strike" align="right">{$r.price|number_format:2}</td>
		</tr>
	{/foreach}
{/if}

<tr style="background:#ffc">
	<td colspan="7">{if $receipt_info.total_amt != $receipt_info.sub_total_amt}Sub{/if} Total{if $pos.is_gst || $pos.is_tax_registered} Inc. Tax{/if}</td>
	<td align="right">{$receipt_info.sub_total_amt|number_format:2}</td>
</tr>


{if $receipt_info.discount_list}
	{foreach from=$receipt_info.discount_list item=discount_row}
		<tr>
			<td colspan="7" align="right"><b>{$discount_row.type}</b></td>
			<td align="right">{$discount_row.amount*-1|number_format:2}</td>
		</tr>
	{/foreach}
{/if}

{if $pos.service_charges}
	<td colspan="7" align="right"><b>Service Charge{if $pos.is_gst || $pos.is_tax_registered} Inc. Tax{/if}</b></td>
	<td align="right">{$pos.service_charges|number_format:2}</td>
{/if}

{if $receipt_info.rounding}
	<tr>
		<td colspan="7" align="right"><b>{$receipt_info.rounding.type}</b></td>
		<td align="right">{$receipt_info.rounding.amount|number_format:2}</td>
	</tr>
{/if}

{if $receipt_info.total_amt != $receipt_info.sub_total_amt}
	<tr style="background:#ffc">
		<td colspan="7">Total{if $pos.is_gst || $pos.is_tax_registered} Inc. Tax{/if}</td>
		<td align="right">{$receipt_info.total_amt|number_format:2}</td>
	</tr>
{/if}



{if $payment}
<!-- payment types -->
{foreach from=$payment item=p}
{assign var=pt value=$p.type}
<tr>
<td colspan=7 align=right>
	<b>
	{if $p.approved_by}<span class="small"><font color="blue">[Approve by {$p.approved_by}]</font></span>{/if} 
	{if $pos_config.payment_type.$pt eq "Deposit" || $pt eq "Deposit"}
		{foreach from=$dc_list item=dc name=dc_items}
			{if $smarty.foreach.dc_items.first}[{/if}
			<a href="/counter_collection.php?a=view_tran_details&branch_id={$dc.branch_id}&date={$dc.date}&counter_id={$dc.counter_id}&pos_id={$dc.pos_id}" target="_blank">{$dc.receipt_ref_no}</a>
			{if !$smarty.foreach.dc_items.last}
			,
			{else}
			]
			{/if}
		{/foreach}
	{/if}
	{if $pt eq "Foreign_Adjust"}{assign var=pt value="Foreign Adjust"}{/if}
	{$pos_config.payment_type_label.$pt|default:$pt} {if $p.remark}({$p.remark}){/if}</b></td><td align=right>{$p.amount|number_format:2}
	<br>
</tr>
{/foreach}
<tr>
<td colspan=7 align=right><b>Change</b></td><td align=right>{$amount_change|number_format:2}<br>
</tr>
{/if}
</table>
{/if}

<!-- Mix and Match Usage -->
{if $pos_mix_match_usage_list}
	<br />
	
	<h3>Mix & Match Discount Promotion</h3>
	<table class="tb" cellpadding="4" cellspacing="0" border="0">
		<thead>
			<tr style="background:#fe9">
				<th>Receipt Description</th>
				<th>More Info</th>
				<th>Total Amount</th>
			</tr>
		</thead>
		{foreach from=$pos_mix_match_usage_list item=r}
			<tr>
				<td>{$r.remark}</td>
				<td>
					{capture assign=more_info_html}{strip}
					{if $r.more_info.barcode}
						<div>
							{$r.more_info.barcode}
						</div>
					{/if}
					
					{if $r.more_info.qty>1 or $r.more_info.alt_price_label}
						<div>
							{if $r.more_info.unit_price}
								{$r.more_info.unit_price|number_format:2}
							{elseif $r.more_info.alt_price_label}
								{$r.more_info.alt_price_label}
							{else}
								FOC
							{/if}
							&nbsp; x {$r.more_info.qty|num_format:2}
						</div>
					{/if}
					
					{if is_array($r.more_info.barcode_list) and $r.more_info.barcode_list}
						<div>
							{foreach from=$r.more_info.barcode_list key=barcode item=bc name=fbc}
								<span style="white-space:nowrap;">
									{if $bc.qty > 1}
										{$bc.qty|num_format:2} x
									{/if}
									{$barcode}
									{if $bc.price}
										 : {$bc.price|number_format:2}
									{/if}
									
								</span>
								
								{if !$smarty.foreach.fbc.last}<br />{/if}
							{/foreach}
						</div>
					{/if}
					{/strip}{/capture}
					{$more_info_html|default:'-'}
				</td>
				<td align="right">{$r.amount|number_format:2|ifzero:'FOC'}</td>
			</tr>
		{/foreach}
	</table>
{/if}

{if $is_deposit}
	<h4>Deposit Information</h4>
	<b>Total Deposit Amount {if $cancel_deposit_info}Returned{else}Received{/if}: </b>{$config.arms_currency.symbol}{$deposit_info.deposit_amount|number_format:2}

	{if $cancel_deposit_info}
		<br /><br />
		<h4>Cancelled Receipt Detail: {$cancel_deposit_info.network_name} / {$cancel_deposit_info.cashier_name} / #{receipt_no_prefix_format branch_id=$cancel_deposit_info.branch_id counter_id=$cancel_deposit_info.counter_id receipt_no=$cancel_deposit_info.receipt_no} - {$cancel_deposit_info.pos_time|date_format:'%Y-%m-%d %I:%M%p'}</br>Receipt Ref. Num: {$cancel_deposit_info.cancel_receipt_ref_no}</h4>
	{/if}

	{if count($deposit_items) > 0}
		<table class="tb" cellpadding="4" cellspacing="0" border="0">
			<thead>
				<tr style="background:#fe9">
					<th>SKU Item Code</th>
					<th>Receipt Description</th>
					<th>UOM Code</th>
					<th>Selling Price</th>
					<th>Qty</th>
				</tr>
			</thead>
			{foreach from=$deposit_items key=r item=item}
				<tr>
					<td>{$item.sku_item_code}</td>
					<td>{$item.description}</td>
					<td>{$item.uom_code}</td>
					<td align="right">
						{if $item.selling_price eq '*'}
							*				
						{else}
							{$item.selling_price|number_format:2}
						{/if}
					</td>
					<td align="right">{$item.qty}</td>
				</tr>
			{/foreach}
		</table>
	{elseif count($deposit_items) eq 0 && ($deposit_info.type eq "RECEIVED" || $deposit_info.type eq "USED")}
		<br /><br />* This deposit is yet to confirm items purchased
	{/if}
{/if}

<!-- Receipt Remark -->
{if $pos.receipt_remark}
	<br>
	<h4>Receipt Remark</h4>
	<table cellpadding="4" cellspacing="0" border="0">
		{foreach from=$pos.receipt_remark item=rmk}
			<tr>
				<td><b>{$rmk.title}</b></td>
				<td width="10">:</td>
				<td>{$rmk.value}</td>
			</tr>
		{/foreach}
	</table>
{/if}

{* GST *}
{if $gst_summary}
	<br />
	<h4>Tax Summary</h4>
	<table class="tb" cellpadding="4" cellspacing="0" border="0">
		<thead>
			<tr style="background:#fe9">
				<th>Tax Code</th>
				<th>Rate (%)</th>
				<th>Amount</th>
				<th>Tax</th>
			</tr>
		</thead>
		{foreach from=$gst_summary item=gs}
			<tr>
				<td>{$gs.tax_indicator}</td>
				<td>{$gs.tax_rate}</td>
				<td class="r">{$gs.before_tax_price|number_format:2}</td>
				<td class="r">{$gs.tax_amount|number_format:2}</td>
			</tr>
		{/foreach}
	</table>
{/if}


</div>

{if !$is_print or $is_view_only}
	<p align="right">
		<button onClick="window.open('/counter_collection.php?a=print_tran_details{if $smarty.request.branch_id}&branch_id={$smarty.request.branch_id}{elseif $receipt_info.branch_id}&branch_id={$receipt_info.branch_id}{/if}{if $smarty.request.date}&date={$smarty.request.date}{elseif $receipt_info.date}&date={$receipt_info.date}{/if}{if $smarty.request.counter_id}&counter_id={$smarty.request.counter_id}{elseif $receipt_info.counter_id}&counter_id={$receipt_info.counter_id}{/if}{if $smarty.request.pos_id}&pos_id={$smarty.request.pos_id}{elseif $receipt_info.pos_id}&pos_id={$receipt_info.pos_id}{/if}')">
			<img src="/ui/print.png" align="absmiddle" />
			Print
		</button>
	</p>
{/if}

{if $is_print && !$is_view_only}
<script>
{literal}
	window.print();
{/literal}
</script>
{/if}
