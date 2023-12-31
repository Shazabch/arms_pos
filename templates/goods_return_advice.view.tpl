{*
Revision History
================
4/19/2007 yinsee
- add return_type column

9/21/2007 4:37:20 PM gary
- add DN info.

1/16/2008 5:49:18 PM yinsee
- add highlight row by $smarty.request.highlight_item_id

3/25/2008 3:43:18 PM gary
- lorry to vehicle

09.05.2008 14:37:04 Saw
- GRA Checkout add extra field for key in total CTN QTY RETURN, this field must compulsory.

5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST

8/4/2009 9:59:19 AM Andy
- add reset function

11/2/2010 2:20:08 PM Alex
- remove show cost privilege

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

5/23/2011 12:10:59 PM Justin
- Modified the grand total amount to round by default 2 decimal points instead of follow config set.

2/16/2012 4:11:26 PM Andy
- Add checking and hide "Cance","Confirm" and "Reset" button if not single server mode and different branch.
- Show branch code of GRA in notification title and viewing page.

4/23/2012 3:00:35 PM Alex
- add packing uom code

7/19/2012 5:05:34 PM Justin
- Added new status "disposed".

9/24/2012 6:00 PM Justin
- Added to show remark.

7/4/2013 5:58 PM Justin
- Enhanced the GRA status to show approval flow message.

07/19/2013 11:24 AM Justin
- Enhanced to show different info while config "gra_no_approval_flow" while is turned on.

4/18/2015 11:33 AM Justin
- Enhanced to show GST information.

5/8/2015 10:51 AM Justin
- Enhanced to show document date.

5/19/2015 1:09 PM Justin
- Bug fixed on total qty always show zero.

11/30/2015 9:43 PM DingRen
- direct load gst amount from gra item instead of recalculate

1/22/2016 11:20 AM Qiu Ying
- Show gst_selling_price and selling price

4/3/2017 11:23 AM Justin
- Enhanced to allow user to reset GRA if have privilege "GRA_ALLOW_USER_RESET".

4/21/2017 11:16 AM Khausalya
- Enhanced hanges from RM to use config setting.

6/15/2017 10:18 AM Andy
- Enhanced gra to able to highlight the row by sku_id.

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

9/4/2018 5:15 PM Justin
- Bug fixed on showing empty selling price when GRA is not under GST status.

12/6/2018 10:43 AM Justin
- Enhanced to have Rounding Adjust.

5/17/2019 9:49 AM William
- Enhance "GRA" word to use report_prefix.

06/24/2020 03:18 PM Sheila
- Updated button css
*}

{include file=header.tpl}

<script>
{literal}
function do_reset(){
    document.f_do_reset['reason'].value = '';
	var p = prompt('Enter reason to Reset :');
	if (p==null || p.trim()=='' ) return false;
	document.f_do_reset['reason'].value = p;

	if(!confirm('Are you sure to reset?'))  return false;

	document.f_do_reset.submit();
	return false;
}
{/literal}
</script>

{literal}
<style>
.gst_sp{
	color:blue;
	font-size:xx-small;
}
</style>
{/literal}

<form name="f_do_reset" method="post" style="display:none;" action="goods_return_advice.php">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
</form>


<h1>GRA ({$form.report_prefix}{$form.id|string_format:"%05d"})</h1>
<h3>Status:
{if $form.status == 0 && $form.approved == 1 && $form.returned == 1 && $form.type eq 'Return'}
	Completed
{elseif $form.status == 0 && $form.type eq 'Disposal'}
	Disposed
{elseif $form.returned == 0 && $form.status == 2 && $form.approved == 0}
	Waiting for Approval
{elseif $form.returned == 0 && $form.status == 0 && $form.approved == 1}
	Approved
{elseif $form.status == 1}
	Cancelled/Terminated
{else}
	Saved
{/if}
</h3>

{include file=approval_history.tpl}

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

<table border=0 cellspacing=0 cellpadding=4 width=100%>
<tr>
	<td><b>Branch</b></td>
	<td>{$form.branch_code}</td>
</tr>
<tr>
	<td width=100><b>Created By</b></td>
	<td>{$form.u}</td>
	<td width=100><b>Created On</b></td>
	<td>{$form.added|date_format:"%d/%m/%Y %I:%M%p"}</td>
	<td width=100><b>Amount</b></td>
	<td>{$form.amount|number_format:2}</td>
</tr>
<tr>
	<td><b>Vendor</b></td>
	<td>{$form.vendor}</td>
	<td><b>Department</b></td>
	<td>{$form.dept_code}</td>
	<td><b>Returned Date</b></td>
	<td>
	{if $form.returned}
	{$form.return_timestamp|date_format:"%d/%m/%Y %I:%M%p"}
	{else}-{/if}
	</td>
	
</tr>
<tr>
	<td><b>DN No.</b></td>
	<td>{$form.misc_info.dn_no|default:"-"}</td>
	<td><b>DN Date</b></td>
	<td>
	{if $form.misc_info.dn_date}
	{$form.misc_info.dn_date|date_format:"%d/%m/%Y"}
	{else}-	{/if}
	</td>
	<td><b>DN Amount</b></td>
	<td>{$form.misc_info.dn_amount|default:"-"}</td>
</tr>
<tr>
	<td><b>Vehicle No.</b></td>
	<td>{$form.transport}</td>
	<td><b>Driver Name</b></td>
	<td>{$form.misc_info.name}</td>
	<td><b>IC No.</b></td>
	<td>{$form.misc_info.nric}</td>
</tr>
<tr>
	<td><b>Return Ctn</b></td>
	<td>{$form.misc_info.return_ctn|default:'&nbsp;'}</td>
	{if $form.currency_code}
		<td><b>Currency</b></td>
		<td>{$form.currency_code}</td>
		<td><b>Exchange Rate</b></td>
		<td>{$form.currency_rate}</td>
	{/if}
</tr>
</table>
</div>

<br>

{if $form.items}
<table border=0 cellspacing=1 cellpadding=4 width=100% style="border:1px solid #000">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>ARMS Code</th>
	<th>Manufacturer Code</th>
	<th>Article No</th>
	{if $config.link_code_name}
		<th>{$config.link_code_name}</th>
	{/if}
	<th width=50%>SKU</th>
	<th>Return Type</th>
	<th nowrap>Inv / DO<br />No.</th>
	<th nowrap>Inv / DO<br />Date</th>
	<th>Qty (pcs)</th>
	<th>{if $form.sku_type eq 'CONSIGN'}Selling Price{else}Cost{/if}</th>
	{if $form.is_under_gst}
		<th>GST Code</th>
	{/if}
	<th>Amount ({$form.currency_code|default:$config.arms_currency.symbol})</th>
	{if $form.is_under_gst}
		<th>GST ({$form.currency_code|default:$config.arms_currency.symbol})</th>
		<th>Amount<br /> Incl. GST ({$form.currency_code|default:$config.arms_currency.symbol})</th>
	{/if}
	<th>Ret</th>
</tr>
<tbody>
{assign var=gra_items value=$form.items}
{assign var=qty value=0}
{section name=i loop=$gra_items}

{if $gra_items[i].grn_cost}
	{assign var=cost value=grn_cost}
{elseif $gra_items[i].po_cost}
	{assign var=cost value=po_cost}
{else}
	{assign var=cost value=master_cost}
{/if}

{assign var=qty value=$qty+$gra_items[i].qty}
{assign var=row_amt value=$gra_items[i].amount}
{if $form.is_under_gst}
{assign var=row_gst_amt value=$gra_items[i].amount_gst}
{assign var=row_gst value=$gra_items[i].gst}
{assign var=ttl_gst value=$ttl_gst+$row_gst}
{assign var=ttl_gst_amt value=$ttl_gst_amt+$row_gst_amt}
{/if}

{assign var=amt value=$amt+$row_amt}

{*assign var=amt value=$amt+$gra_items[i].qty*$gra_items[i].cost}
{assign var=amt value=$amt|round:$dp*}
<tr id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}" {if $smarty.request.highlight_item_id eq $gra_items[i].sku_item_id or (isset($smarty.request.highlight_sku_id) and $gra_items[i].sku_id eq $smarty.request.highlight_sku_id)}class=highlight_row{/if}>
    <td width=20 align=right>
    {$smarty.section.i.iteration}.
    </td>
	<td>{$gra_items[i].sku_item_code}</td>
	<td>{$gra_items[i].mcode|default:"&nbsp;"}</td>
	<td>{$gra_items[i].artno|default:"&nbsp;"}</td>
	{if $config.link_code_name}
	<td>{$gra_items[i].link_code|default:"&nbsp;"}</td>
	{/if}
	<td>{$gra_items[i].sku} {include file=details.uom.tpl uom=$gra_items[i].packing_uom_code}</td>
	<td>{$gra_items[i].return_type|default:"-"}</td>
	<td>{$gra_items[i].doc_no|default:"-"}</td>
	<td>{$gra_items[i].doc_date|ifzero:"&nbsp;"}</td>
	<td align=right>{$gra_items[i].qty|qty_nf}</td>
	<td align=right nowrap>
	{if $form.sku_type eq 'CONSIGN'}
		{if $form.is_under_gst}
			{if $gra_items[i].inclusive_tax eq 'yes'}
					{$gra_items[i].gst_selling_price|number_format:2}<br/>
					<span class="gst_sp">(Excl: {$gra_items[i].selling_price|number_format:4})<span>
			{else}
				{$gra_items[i].selling_price|number_format:2}<br/>
				<span class="gst_sp">(Incl: {$gra_items[i].gst_selling_price|number_format:2})<span>
			{/if}
		{else}
			{$gra_items[i].selling_price|number_format:2}
		{/if}
	{else}
		{$gra_items[i].cost|number_format:$dp}{/if}
	</td>
	{if $form.is_under_gst}
		<td align=right nowrap>{$gra_items[i].gst_code} ({$gra_items[i].gst_rate|default:'0'}%)</td>
	{/if}
	<td align=right>{$row_amt|number_format:2}</td>
	{if $form.is_under_gst}
		<td bgcolor="{$rowcolor2}" align="right">{$row_gst|number_format:2}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$row_gst_amt|number_format:2}</td>
	{/if}
	<td align=center>{if $gra_items[i].checkout}<img src=ui/approved.png>{else}<img src=ui/rejected.png><br>{$gra_items[i].reason}{/if}</td>
</tr>
{/section}
<tr bgcolor=#ffee99>
	<td {if $config.link_code_name}colspan=9{else}colspan=8{/if} align=right><b>Total</b></td>
	<td align=right>{$qty|qty_nf}</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
	{/if}
	<td align=right>{$amt|number_format:2}</td>
	{if $form.is_under_gst}
		<td align=right>{$ttl_gst|number_format:2}</td>
		<td align=right>{$ttl_gst_amt|number_format:2}</td>
	{/if}
	<td>&nbsp;</td>
</table>
<br>
{/if}

{assign var=total1_amt value=0}
{assign var=total1_qty value=0}
{if $new}
<h4>Items Not in ARMS SKU</h4>
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">
<tr bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>Item Code</th>
	<th width=50%>Description</th>
	<th nowrap>Return Type</th>
	<th nowrap>Inv / DO<br />No.</th>
	<th nowrap>Inv / DO<br />Date</th>
	<th>Qty (pcs)</th>
	<th>Cost</th>
	{if $form.is_under_gst}
		<th>GST Code</th>
	{/if}
	<th>Amount ({$form.currency_code|default:$config.arms_currency.symbol})</th>
	{if $form.is_under_gst}
		<th>GST ({$form.currency_code|default:$config.arms_currency.symbol})</th>
		<th>Amount<br /> Incl. GST ({$form.currency_code|default:$config.arms_currency.symbol})</th>
	{/if}
</tr>
{section name=i loop=$new}

{assign var=row_amt value=$new[i].qty*$new[i].cost}
{if $form.is_under_gst}
{math equation="x*((100+y)/100)" x=$row_amt y=$new[i].gst_rate assign=row_gst_amt}
{assign var=row_gst_amt value=$row_gst_amt|round2}
{assign var=row_amt value=$row_amt|round2}
{assign var=row_gst value=$row_gst_amt-$row_amt}

{assign var=ttl_gst1 value=$ttl_gst1+$row_gst}
{assign var=ttl_gst_amt1 value=$ttl_gst_amt1+$row_gst_amt}
{/if}

{assign var=total1_amt value=$total1_amt+$row_amt}
{assign var=total1_qty value=$new[i].qty+$total1_qty}
<tr id="tbrow_{$new[i].id}" bgcolor="{cycle values="#eeeeee,"}">
    <td width=6 align=right>
    {$n_c+$smarty.section.i.iteration}.
    </td>
	<td>{$new[i].code}</td>
	<td>{$new[i].description}</td>
	<td>{$new[i].reason|default:"-"}</td>
	<td>{$new[i].doc_no|default:"-"}</td>
	<td>{$new[i].doc_date|ifzero:"&nbsp;"}</td>
	<td align=right>{$new[i].qty|qty_nf}</td>
	<td align=right>{$new[i].cost|number_format:$dp}</td>
	{if $form.is_under_gst}
		<td align=right nowrap>{$new[i].gst_code} ({$new[i].gst_rate|default:'0'}%)</td>
	{/if}
	<td align=right>{$row_amt|number_format:2}</td>
	{if $form.is_under_gst}		
		<td bgcolor="{$rowcolor2}" align="right">{$row_gst|number_format:2}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$row_gst_amt|number_format:2}</td>
	{/if}
</tr>
{/section}
<tr>
	<td colspan=6 align=right>Total</td>
	<td align=right>{$total1_qty|qty_nf}</td>
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
	{/if}
	<td align=right>{$total1_amt|number_format:2}</td>
	{if $form.is_under_gst}
		<td align=right>{$ttl_gst1|number_format:2}</td>
		<td align=right>{$ttl_gst_amt1|number_format:2}</td>
	{/if}
</tr>
</table>
{/if}

{if $form.status == 0 && $form.approved == 1 && $form.returned == 1 && $form.type eq 'Return'}
	<br />
	<h4>Total Summary</h4>
	<table border="0" cellspacing="1" cellpadding="4" width="50%" style="border:1px solid #000">
		<tr bgcolor="#ffee99">
			<th>Sub Total ({$form.currency_code|default:$config.arms_currency.symbol})</th>
			<th>Rounding Adjust ({$form.currency_code|default:$config.arms_currency.symbol})</th>
			<th>Grand Total ({$form.currency_code|default:$config.arms_currency.symbol})</th>
		</tr>
		<tr>
			<td class="r">{$form.amount-$form.rounding_adjust|number_format:2}</td>
			<td class="r">{$form.rounding_adjust|number_format:2}</td>
			<td class="r">{$form.amount|number_format:2}</td>
		</tr>
	</table>
{/if}

<br>
<h4>Remark</h4>
<div style="border:1px solid #000;padding:5px;">{$form.remark2|default:"-"|nl2br}</div>

<br>
<h4>Additional Remark</h4>
<div style="border:1px solid #000;padding:5px;">{$form.remark|default:"-"|nl2br}</div>

<p align=center>
{if $form.returned and ($sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.GRA_ALLOW_USER_RESET) and ($config.single_server_mode || (!$config.single_server_mode and $form.branch_id eq $sessioninfo.branch_id))}
    <input class="btn btn-warning" type=button value="Reset" onclick="do_reset();">
{/if}
<input class="btn btn-error" type=button value="Close" onclick="close_window('{$smarty.server.PHP_SELF}')">
</p>
{include file=footer.tpl}