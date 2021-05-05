{*
11/5/2008 5:15:16 PM yinsee
- wrong PP number (mistaken taken from po_items)

4/26/2010 3:53:33 PM Andy
- Fix a bugs which make PO No cannot not show.

4/28/2010 5:06:38 PM Andy
- Fix invalid deliver & cancel date bugs.
- Fix show wrong actual & proforma PO bugs.
- Fix wrong PO amount for actual & proforma PO bugs.
- Fix PO show all department will get wrong total selling bugs if have draft or proforma PO.

6/24/2010 5:06:38 PM Justin
- Added the display of different list for GRR Document No when use GRN Future.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

12/11/2014 3:40 PM Justin
- Enhanced to have GST information.

4/19/2017 10:51 AM Khausalya
- Enhanced changes from RM to use config setting. 

4/20/2018 2:19 PM Andy
- Added Foreign Currency feature.

7/31/2019 11:18 AM William
- Enhanced status text auto change when "status" selection change to "Actual PO".
- Added new Delivered GRN column.

02/17/2021 4:01 PM Rayleen
- Remove bgcolor in table row  if it is an export file
*}
<div class="noscreen">
<h3>
Branch : {section name=i loop=$branch}
	{if $smarty.request.branch_id eq $branch[i].id}{$branch[i].code}{/if}
{/section}&nbsp;&nbsp;&nbsp;
Department : {section name=i loop=$dept}
{if $smarty.request.department_id eq $dept[i].id}{$dept[i].description}{/if}
{/section}&nbsp;&nbsp;&nbsp;
GRN Date : {$smarty.request.from} - {$smarty.request.to}
</h3>
</div>

{if !$po}
** no data **
{else}

{if $config.foreign_currency}* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}{/if}
{assign var=nr_colspan value=6}
<table id="tbl_po" class="report_table">
<tr class="header">
	<th>&nbsp;</th>
	<th>PO</th>
	<th>Branch</th>
	<th>PO Date</th>
	<th>Vendor Code</th>
	{if $config.enable_vendor_account_id}
		<th>Account ID</th>
		{assign var=nr_colspan value=$nr_colspan+1}
	{/if}
	<th>Vendor</th>
	<th>Total Selling
		{if $config.enable_gst}<br />(Exclude GST){/if}
	</th>
	{if $got_currency_code}
		<th>PO Amount<br />(Foreign Currency)</th>
		<th>Exchange Rate</th>
	{/if}
	<th>
		PO Amount
		{if $got_currency_code}<br />({$config.arms_currency.code}){/if}
	</th>
	{if $is_under_gst}
		<th>Total GST</th>
	{/if}
	<th>GP (%)</th>
	<th>PO Status</th>
	<th>Delivery Date</th>
	<th>Cancel Date</th>
	<th>GRR Date</th>
	<th>Delivered GRN</th>
</tr>
{assign var=ttotal_sell value=0}
{assign var=ttotal_amt value=0}

{foreach from=$po item=item name=i}
	<tr {if !$is_export} bgcolor="{cycle values='#ffffff,#eeeeee'}" {/if}>
		<td>{$smarty.foreach.i.iteration}.</td>
		<td>
		{if $item.status==0}
			{$item.report_prefix}{$item.po_id|string_format:"%05d"}(DP)
		{elseif $item.approved}
			{$item.po_no}
		{else}
			{$item.report_prefix}{$item.po_id|string_format:"%05d"}(PP)
		{/if}
		</td>
		<td>{$item.po_branch|default:$item.branch}</td>
		<td>{$item.po_date}</td>
		<td>{$item.vendor_code}</td>
		{if $config.enable_vendor_account_id}
			<td>{$item.account_id}</td>
		{/if}
		<td>{$item.vendor}</td>
		<td align="right">{$item.total_selling_amt|number_format:2}</td>

		{if $got_currency_code}
			{if $item.currency_code}
				<td align="right">
					{$item.currency_code} {$item.po_amount|number_format:2}
				</td>
				<td align="right">{$item.currency_rate}</td>
			{else}
				<td align="right">-</td>
				<td align="right">-</td>
			{/if}
		{/if}
		
		<td align="right" {if $item.currency_code}class="converted_base_amt"{/if}>
			{$item.base_po_amount|number_format:2}{if $item.currency_code}*{/if}
		</td>
		
		{if $is_under_gst}
			<td align="right">{$item.po_gst_amount|number_format:2}</td>
		{/if}

		{if $item.total_selling_amt}
		{assign var=gp value=$item.total_selling_amt-$item.base_po_amount}
		{assign var=gp value=$gp/$item.total_selling_amt*100}
		{/if}
		<td align="right">{$gp|number_format:2}</td>
		<td>
		{if $item.approved==0 and $item.status==0}
		Draft
		{elseif $item.approved==1}
		Actual PO
		{elseif $item.approved==0 and $item.status==1}
		Proforma
		{elseif $item.status eq 2}
		Rejected
		{/if}
		{if $item.delivered == 1}
		(<font color="green">Delivered</font>)
		{/if}
		{if $item.expired}
		(<font color="red">{$item.expired}</font>)
		{/if}
		</td>
		<td align=right>
		{$item.delivery_date}
		</td>
		<td align=right>
		{$item.cancel_date}
		</td>
		<td align=right>{$item.rcv_date}</td>
		<td align="right">
		{if !$item.delivered_grn_list}-{else}
			{foreach from=$item.delivered_grn_list item=delivered_grn name=fdg}
				<a href="goods_receiving_note.php?a=view&id={$delivered_grn.grn_id}&branch_id={$delivered_grn.branch_id}" target="_blank">
					{$delivered_grn.report_prefix}{$delivered_grn.grn_id|string_format:"%05d"}
				</a>
				{if !$smarty.foreach.fdg.last}, {/if}
			{/foreach}
		{/if}
		</td>
		{assign var=gp value=0}
	</tr>
	{assign var=ttotal_sell value=$ttotal_sell+$item.total_selling_amt}
	{assign var=ttotal_amt value=$ttotal_amt+$item.base_po_amount}
	{if $is_under_gst}
		{assign var=total_gst value=$total_gst+$item.po_gst_amount}
	{/if}
{/foreach}

<tr class="header">
	<td class="noborder" colspan="{$nr_colspan}"><b>Total</b></th>
	<td align="right">{$ttotal_sell|number_format:2}</td>

	{if $got_currency_code}
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	{/if}
	
	<td align="right" {if $got_currency_code}class="converted_base_amt"{/if}>
		{$ttotal_amt|number_format:2}{if $got_currency_code}*{/if}
	</td>
	{if $is_under_gst}
		<td align="right">{$total_gst|number_format:2}</td>
	{/if}

	<td colspan="6">&nbsp;</td>
</tr>

</table>
<script>
//ts_makeSortable($('tbl_po'));
</script>
{/if}{**}
