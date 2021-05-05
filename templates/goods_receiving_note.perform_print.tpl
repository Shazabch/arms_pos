{*
REVISION HISTORY
===================
1/11/2008 2:32:31 PM gary
- fix the negative qty for total balance qty.

1/30/2008 5:47:55 PM gary
- the profit just get from current grn.

2/1/2008 1:43:40 PM gary
- set print with max items per page.

2/4/2008 10:49:09 AM gary
- fix the uom bug in po qty and po foc.

11/3/2009 10:49:35 AM Andy
- Add "(Not Verified)" to indicate it is "Saved GRN".

11/24/2009 6:00:51 PM Andy
- column "Balance" change to "GRN Balance" and add the legend for it.

1/12/2010 3:51:36 PM Andy
- Add config to manage item got line or not

8/2/2010 10:37:58 AM Andy
- Change grn performance report to if zero rcv qty will not calculate GP%.

10/26/2010 3:47:29 PM Justin
- Added Sub total for every page instead of total.
- Set the footer content only print out on last page.
- Fixed the sum up bugs for total cost.
- Modified the printing empty row to based on config.
- Add Terms, Fast and Prompt Payment Terms on header.

12/13/2010 3:48:11 PM Justin
- Added Document No to show PO No when found config['use_grn_future'].

2/16/2011 4:27:19 PM Andy
- Modify remark. (add DO into remark)

7/12/2011 1:11:40 PM Justin
- Fixed the rounding error for total amount.

7/15/2011 1:41:43 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/23/2012 12:19:43 PM Justin
- Replaced the prefix "PO" with dynamic document type.
- Fixed the devision by zero bugs.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.
- Bug fixed on GRN profit (%) from Summary is wrongly calculated.

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

02/29/2016 14:45 Edwin
- Bug fixed on GRN GP calculation error
- Enhanced on appearance of gst selling info and calculation based on branch's gst status

03/14/2016 15:45 Edwin
- Bug fixed on GRN Performance Report and Report Total layout

3/14/2016 3:25 PM Andy
- Fix number format issue.

4/20/2017 1:39 PM Khausalya 
- Enhanced changes from RM to use config setting. 

6/16/2017 11:15 AM Justin
- Bug fixed on gst amount got different with Account Verification.

4/20/2018 10:06 AM Justin
- Enhanced to show foreign currency if found GRR using it.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/22/2018 10:26 AM Justin
- Enhanced to show Old Code instead of ARMS Code when config is turned on.

12/17/2018 3:06 PM Justin
- Enhanced to print barcode on top of the document number.

5/22/2019 3:56 PM William
- Enhance "GRN","GRR" word to use report_prefix.
*}
{if !$skip_header}
{include file='header.print.tpl'}

<style>
{if $config.grn_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}
</style>

<script type="text/javascript">
var doc_no = '{$branch.report_prefix}{$grn.id|string_format:"%05d"}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

{if !$smarty.request.noprint}<body onload="start_print();">{/if}
{/if}

<!-- print sheet -->
<div class=printarea>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
		<td align=center>
		<b>GOODS RECEIVING 
			{if !$is_gra}
				NOTE<br />PERFORMANCE REPORT<br />
				{if $grn.status eq 0}
					(Not Verified)
				{/if}
			{else}
				NOTE<br />
				(VENDOR COPY)
			{/if}
		</b>
	</td>
</tr>
<tr bgcolor=#cccccc>
	<td align=center><b>{$page}</b></td>
</tr>
</table>
<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
<td><b>GRN No</b></td>
<td {if $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
	{if $config.print_document_barcode}
		<span class="barcode3of9" style="padding:0;">
			*{$grn.id|string_format:"%05d"}*
		</span>
	{/if}
	
	<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
		{$branch.report_prefix}{$grn.id|string_format:"%05d"}
	</div>
</td>
<td><b>GRN Date</b></td><td>{$grn.added|date_format:"%d/%m/%Y"}</td>
<td><b>GRN By</b></td><td>{$grn.u}</td>
<td><b>Printed By</b></td><td>{$sessioninfo.u}</td>
</tr><tr>
<td><b>GRR No</b></td><td>{$branch.report_prefix}{$grn.grr_id|string_format:"%05d"}/{$grn.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:"%d/%m/%Y"}</td>

<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:"%d/%m/%Y"}</td>
<td><b>Received By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Department</b></td><td colspan=3>{$grn.department|default:$grr.department}</td>
</tr><tr>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
<td><b>Document Type.</b></td><td><font color=blue>{$grr.type}</font></td>
<td><b>Document No.</b></td><td><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td><b>Partial Delivery</b></td><td>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</td>
{else}
<td colspan=2>&nbsp;</td>
{/if}
</tr>
<tr>
<td><b>Terms</b></td><td>{$grr.term|default:"&nbsp;"}</td>
<td><b>Fast Payment Term ({$grr.fast_payment_discount|default:0}%)</b></td><td>{$grr.fast_payment_term|default:"&nbsp;"}</td>
<td><b>Prompt Payment Term ({$grr.prompt_payment_discount|default:0}%)</b></td><td {if !$grn.offline_id}colspan="3"{/if}>{$grr.prompt_payment_term|default:"&nbsp;"}</td>
{if $grn.offline_id}
	<td><b>Offline ID</b></td><td>#{$grn.offline_id|string_format:"%05d"}</td>
{/if}
</tr>
{if $grr.currency_code}
	<tr>
		<td><b>Currency Code</b></td>
		<td {if !$grr.currency_code}colspan="9"{/if}>
			{if !$grr.currency_code}
				Base Currency
			{else}
				{$grr.currency_code}
			{/if}
		</td>
		{if $grr.currency_code}
			<td><b>Exchange Rate</b></td><td colspan="7">{$grr.currency_rate|default:"1"}</td>
		{/if}
	</tr>
{/if}
</table>

<br>
{if !$is_gra}
	<div class="small">
	* The selling price is based on date {$grn.price_date|date_format:"%d/%m/%Y"}.<br />
	* GRN Balance is Rcv Qty minus total sales and DO from {$grr.rcv_date|date_format:"%d/%m/%Y"} to {$smarty.now|date_format:"%d/%m/%Y"}.<br />
	{if $grr.currency_code}
		* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}
	{/if}
	</div>
{/if}

<table class="box small tb" width=100% cellpadding=2 cellspacing=0 border=0>
<tr class="topline botline" bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2 width='50'>
		{if $config.replace_docs_arms_code_with_link_code}
			{$config.link_code_name|default:'Old Code'}
		{else}
			ARMS Code
		{/if}/<br>
		Mcode
	</th>
	<th rowspan=2 width='50'>Artno</th>
	<!--<th rowspan=2 width='50'>Mcode</th>-->
	<th rowspan=2>Description</th>
	<th rowspan=2 width='15'>Rcv Qty</th>
	{if !$is_gra}
		{assign var=colspan value=2}
		{if $grn.branch_is_under_gst}
			{assign var=colspan value=$colspan+2}
		{/if}
		<th colspan="{$colspan}" width='40'>* S.Price({$config.arms_currency.symbol})</th>
	{/if}
	{assign var=colspan value=2}
	{if $is_gra && $grn.is_under_gst}
		{assign var=colspan value=$colspan+3}
	{/if}
	<th colspan="{$colspan}" width='40'>Cost ({$grr.currency_code|default:$config.arms_currency.symbol})</th>
	{if !$is_gra}
		<th colspan=3 width='40'>{if $grr.type eq 'PO' || !$grr.is_ibt_do}PO{else}DO{/if} Details</th>
		<th rowspan=2 width='20'>GP (%)</th>
		<th colspan=2 width='35'>GRN Balance</th>
	{/if}
</tr>

<tr class="botline" bgcolor=#cccccc>
{if !$is_gra}
	<th>Unit</th>
	{if $grn.branch_is_under_gst}
		<th>Unit<br />Incl. GST</th>
	{/if}
	<th>Total</th>
	{if $grn.branch_is_under_gst}
		<th>Total<br />Incl. GST</th>
	{/if}
{/if}

<th>Unit</th>
{if $is_gra && $grn.is_under_gst}
	<th>GST Code</th>
{/if}
<th>Total</th>
{if $is_gra && $grn.is_under_gst}
	<th>GST</th>
	<th>Total Incl.<br />GST</th>
{/if}

{if !$is_gra}
	<th>Qty</th>
	<th>FOC</th>
	<th>Cost ({$grr.currency_code|default:$config.arms_currency.symbol})</th>

	<th>Qty</th>
	<th>Amount</th>
{/if}
</tr>

<tbody id=tbditems>
{assign var=n value=0}

{foreach name=i from=$grn_items item=item key=iid}
<!-- {$n++} -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	{if !$page_item_info.$iid.not_item}
		<td>{$item.item_no+1}</td>{*<td>{$start_counter+$n}.</td>*}
		{assign var=sku_item_code value=`$item.sku_item_code`}
		<td>
			{if $config.replace_docs_arms_code_with_link_code}
				{$item.link_code|default:'&nbsp;'}
			{else}
				{$sku_item_code}
			{/if}
			{if $item.mcode<>''}<br>{$item.mcode|default:"-"}{/if}
		</td>
		<td align=center>{$item.artno|default:"-"}</td>
		<!--<td align=center>{$item.mcode|default:"-"}</td>-->
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	<td><div class="crop" style="height:2em">{$item.description}</div></td>
	
	{if !$page_item_info.$iid.not_item}
		{assign var=qty value=$item.qty|round:$config.global_qty_decimal_points}
		<td align=right>{$qty|qty_nf}</td>
		
		{if !$is_gra}
			{if $item.grn_price}
				{assign var=price value=`$item.grn_price`}
			{else}
				{assign var=price value=`$item.master_price`}	
			{/if}
			<td align=right>{$price|number_format:2}</td>
			{if $grn.branch_is_under_gst}
				{assign var=t_gst_price value=`$item.gst_selling_price*$qty`}
				<td align=right>{$item.gst_selling_price|number_format:2}</td>
			{/if}
			{assign var=t_price value=`$price*$qty`}
			<td align=right>{$t_price|number_format:2}</td>
			{if $grn.branch_is_under_gst}
				<td align=right>{$t_gst_price|number_format:2}</td>
			{/if}
		{/if}
		
		{assign var=cost value=$item.grn_cost|round:$config.global_cost_decimal_points}
		<td align=right>{$cost|number_format:$config.global_cost_decimal_points}</td>
		{if $is_gra && $grn.is_under_gst}
			{assign var=gst_amt value=$cost*$item.gst_rate/100}
			{assign var=row_gst value=$gst_amt*$qty}
			{assign var=row_gst value=$row_gst|round2}
			<td align=right>
				{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
				{$gst_amt|number_format:$config.global_cost_decimal_points}
			</td>
		{/if}
		{assign var=t_cost value=`$cost*$qty`}
		<td align=right>{$t_cost|number_format:2}</td>
		{if $is_gra && $grn.is_under_gst}
			{assign var=row_gst_cost value=$t_cost+$row_gst}
			{assign var=row_gst_cost value=$row_gst_cost|round2}
			{assign var=total_gst value=$total_gst+$row_gst}
			{assign var=total_gst_cost value=$total_gst_cost+$row_gst_cost}
			<td align=right>{$row_gst|number_format:2}</td>
			<td align=right>{$row_gst_cost|number_format:2}</td>
		{/if}
		
		{if !$is_gra}
			<td align=right>{$item.po_qty-$item.po_foc|qty_nf}</td>
			<td align=right>{$item.po_foc|qty_nf}</td>
			<td align=right>{$item.po_cost|number_format:$config.global_cost_decimal_points}</td>
			
			{if $qty}
				{if $form.branch_is_under_gst && $item.inclusive_tax eq "yes"}
					{assign var=gp_price value=$item.gst_selling_price*$qty}
				{else}
					{assign var=gp_price value=$price*$qty}
				{/if}
				
				{math assign=item_profit equation="round(selling - (cost * currency_rate),2)" selling=$gp_price cost=$t_cost currency_rate=$grr.currency_rate|default:1}
				
				{*assign var=s_c value=`$gp_price-$cost`*}
				{assign var=gp value=`$item_profit/$gp_price*100`}
			{else}
				{assign var=gp value=0}
			{/if}
			<td align=right>{$gp|number_format:2}{if $grr.currency_code}*{/if}</td>

			{assign var=minus_qty value=$pos_qty.$sku_item_code.sold_qty+$do_qty.$sku_item_code.qty|round:$config.global_qty_decimal_points}
			{assign var=balance value=`$qty-$minus_qty`}
			{if $balance<0}
			<!-- if all grn qty sold out, so the total sold out qty is total grn qty.-->
			{assign var=minus_qty value=`$qty`}
			{assign var=balance value=0}
			{/if}
			<td align=right>{$balance|qty_nf}</td>

			{assign var=amount value=`$balance*$cost`}	
			<td align=right>{$amount|number_format:2}</td>
		{/if}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $is_gra && $grn.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		{if !$is_gra}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{if $grn.branch_is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		{/if}
	{/if}
</tr>
	{if !$page_item_info.$iid.not_item}
		{assign var=sold_price value=`$minus_qty*$price`}
		{assign var=sold_cost value=`$minus_qty*$cost`}
		
		{assign var=total_sold_sell value=$total_sold_sell+$sold_price}
		{assign var=total_sold_cost value=$total_sold_cost+$sold_cost}
		
		
		{assign var=total_qty value=$total_qty+$qty}
		{assign var=total_sell value=$total_sell+$t_price|round:2}
		{assign var=total_gst_sell value=$total_gst_sell+$t_gst_price|round:2}
		{assign var=total_cost value=$total_cost+$t_cost|round:2}
		{assign var=total_bal_qty value=$total_bal_qty+$balance}
		{assign var=total_bal value=$total_bal+$amount|round:2}
	{/if}
{/foreach}

{assign var=s2 value=$n}
{section name=s start=$n loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<!-- filler -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<!--<td>&nbsp;</td>-->
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $is_gra && $grn.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	{if !$is_gra}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $grn.branch_is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{/if}
</tr>
{/section}

{* repeat s=$n+1 e=15}
<!-- filler -->
<tr height=30 bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<!--<td>&nbsp;</td>-->
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/repeat *}
</tbody>

<tr class="topline" height=30 bgcolor=#cccccc>
<td colspan=4 align=right><b>{if !$is_last_page}Sub {/if}Total</b></td>
<td align=right><b>{$total_qty|qty_nf}</b></td>
{if !$is_gra}
	{if $grn.branch_is_under_gst}
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	<td align=right><b>{$total_sell|number_format:2}</b></td>
	{if $grn.branch_is_under_gst}
		<td align=right><b>{$total_gst_sell|number_format:2}</b></td>
	{/if}
{else}
	{if $grn.is_under_gst}
		<td>&nbsp;</td>
	{/if}
{/if}
<td>&nbsp;</td>
<td align=right><b>{$total_cost|number_format:2}</b></td>
{if $is_gra && $grn.is_under_gst}
	<td align=right><b>{$total_gst|number_format:2}</b></td>
	<td align=right><b>{$total_gst_cost|number_format:2}</b></td>
{/if}
{*assign var=t_s_c value=$total_sell-$total_cost*}
{math assign=item_profit equation="round(selling - (cost * currency_rate),2)" selling=$total_sell cost=$total_cost currency_rate=$grr.currency_rate|default:1}
{if $item_profit>0}
	{assign var=total_gp value=`$item_profit/$total_sell*100`}
{else}
	{assign var=total_gp value=0}
{/if}
{if !$is_gra}
	<td colspan=3>&nbsp;</td>
	<td align=right><b>{$total_gp|number_format:2}{if $grr.currency_code}*{/if}</b></td>

	<td align=right><b>{$total_bal_qty|qty_nf}</b></td>
	<td align=right><b>{$total_bal|number_format:2}</b></td>
{/if}
</tr>

</table>
{if $is_last_page}
	<br />
	{if !$is_gra}
		<table class="box small" width=100% cellpadding=2 cellspacing=0 border=0>
		<tr class="topline" height=30 bgcolor=#cccccc>
		<td colspan=8 align=right>&nbsp;</td>
		<th colspan=5>GRN Profit</th>
		<th colspan=3>GRN Balance</th>
		</tr>

		<tr class="topline" height=30 bgcolor=#cccccc>
		<th colspan=8 align=right rowspan=2><b>GRN Status Summary<br> As at {$smarty.now|date_format:"%d/%m/%Y"}</b></th>
		<th><b>Qty Sold</b></th>
		<th><b>S.Price</b></th>
		<th><b>Cost</b></th>
		<th><b>Profit</b></th>
		<th><b>%</b></th>
		<th><b>Qty</b></th>
		<th><b>Amount</b></th>
		<th><b>%</b></th>
		</tr>

		<tr class="topline" height=30 bgcolor=#cccccc>
		{assign var=qty_sold value=`$total_qty-$total_bal_qty`}
		<td align=right><b>{$qty_sold|qty_nf}</b></td>
		<td align=right><b>{$total_sold_sell|number_format:2}</b></td>
		<td align=right><b>{$total_sold_cost|number_format:$config.global_cost_decimal_points}</b></td>
		<td align=right><b>{$total_sold_sell-$total_sold_cost|number_format:2}</b></td>
		{assign var=profit_percentage value=`$total_sold_sell-$total_sold_cost`}
		{if $profit_percentage>0}
			{assign var=profit_percentage value=`$profit_percentage/$total_sold_sell*100`}
		{else}
			{assign var=profit_percentage value=0}
		{/if}
		<td align=right><b>{$profit_percentage|number_format:2}</b></td>

		<td align=right><b>{$total_bal_qty|qty_nf}</b></td>
		<td align=right><b>{$total_bal|number_format:2}</b></td>
		{assign var=qty_percentage value=`$total_bal_qty/$total_qty*100`}
		<td align=right><b>{$qty_percentage|number_format:2}</b></td>
		</tr>
		</table>
	{else}
		<br />
		<div style="border:2px solid #000; padding:1px;">
		<br />
		<br />
		<table width="70%">
			<tr>
				<td width="30%" valign="bottom" class="small">
					_________________<br />
					Issued By<br />
					Name: ({$sessioninfo.u})<br />
					NRIC:
				</td>
			
				<td width="30%" valign="bottom" class="small">
					_________________<br />
					Confirmed By<br />
					Name: (Transporter)<br />
					NRIC:
				</td>

				<td width="40%" valign="bottom" class="small">
					_________________<br />
					Checked By (Internal Use)<br />
					Name: (Store Inventory Supervisor)<br />
					NRIC:
				</td>
			</tr>
		</table>
		</div>
	{/if}
{/if}

{include file=report_footer.landscape.tpl}
</div>
