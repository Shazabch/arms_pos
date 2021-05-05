{*
2/1/2010 12:09:12 PM Andy
- remove PO No
- add discount row and sub total row, remark move to same row with them
- make it able to hide or show middle line

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

6/23/2011 11:04:12 AM Justin
- Modified the the Code No column to show Article No instead of ARMS Code.
- Added 3 lines between sub total, discount and total rows.
- Added printing of conversation from numeric into character for total amount.

6/28/2011 3:00:21 PM Justin
- Amended the Unit Price to show Cost Price instead of Selling Price.
- Removed the lines from total labels and leaves lines between qty and amount.
- Changed the label from "Discount" become "Sheet Discount" from total row.

7/15/2011 1:48:28 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/22/2011 11:58:53 AM Justin
- Modified to match correctly with the pre-fix layout.

4/3/2015 1:43 PM Justin
- Enhanced to set font size to smaller for artno.

4/10/2015 5:35 PM Justin
- Bug fixed Cost Price did not deduct percent from Bearing Percent.
- Enhanced to have Remark.

4/13/2015 3:07 PM Justin
- Enhanced to replace "CONSIGNMENT" to deliver branch's code.

4/27/2015 6:13 PM Justin
- Enhanced to show custom region of dollars description if it is deliver to oversea.
- Enhanced the cost price to use foreign cost price if have.
*}

{config_load file="site.conf"}
{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.ci_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}

.td_border_top{
	border-top:1px solid black !important;
}

.td_border_bottom{
	border-bottom:1px solid black !important;
}

.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}

body{
	//background-image:url('thumb.php?w=765&fit=1&img=templates/justkids/background-justkids.jpg');
	background-repeat: no-repeat;
	background-position: top left;
	width:750px;
}

.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}
{/literal}
</style>
<body onload="window.print()">
{/if}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0>
	<tr height="175px">
		<td width="27px"></td>
		<td></td>
		<td></td>
	</tr>
<!--tr>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Invoice No.</td><td nowrap>{$form.ci_no}</td></tr>
	    <tr height=22><td nowrap>Invoice Date</td><td nowrap>{$form.ci_date|date_format:"%d/%m/%Y"}</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr-->
	<tr>
		<td>&nbsp;</td>
		<td></td>
		<td>
			<table width=100% cellspacing="5" cellpadding="0" border=0 height="124px">
				<tr>
					<td valign="top" width="190px" style="padding:5px" class="small">
						<b><font class="large">{$to_branch.description}</font></b><br>
						{$to_branch.address|nl2br}<br>
						Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
						{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
					</td>
					<td width="17px">&nbsp;</td>
					<td width="115px" style="padding:5px">
						<table width="100%" height="100%">
							<tr height="36px"><td align="right">{$form.ci_date|date_format:"%d/%m/%Y"}</td></tr>
							<tr height="26px"><td align="right">{$to_branch.con_dept_name|default:'-'}</td></tr>
							<tr height="42px"><td align="right" valign="bottom" class="small">{$to_branch.code}</td></tr>
						</table>
					</td>
					<td width="15px">
					</td>
					<td width="108px" style="padding:5px">
						<table width="100%" height="100%">
							<tr height="36px"><td align="right">{$form.ci_no}</td></tr>
							<tr height="22px"><td>&nbsp;</td></tr>
							<tr height="42px"><td align="right">{$to_branch.con_terms|default:'-'}</td></tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="30px">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=1 width=100% class="small">
{assign var=counter value=0}
{section name=i loop=$ci_items}
<!-- {$counter++} -->
<tr>
	<td align="center" width="20px">&nbsp;</td>
	<td width="94px" nowrap>{$ci_items[i].artno}</td>
	<td width="238px" nowrap><div class="crop">{$ci_items[i].description}</div></td>
	<td align="right" width="53px">
		{assign var=qty value=$ci_items[i].uom_fraction*$ci_items[i].ctn+$ci_items[i].pcs}
		{$qty|default:'0'}
	</td>
	{if $form.exchange_rate > 0 && $ci_items[i].foreign_cost_price > 0 && $ci_items[i].foreign_cost_price ne $ci_items[i].cost_price}
		{assign var=cost_price value=$ci_items[i].foreign_cost_price}
	{else}
		{assign var=cost_price value=$ci_items[i].cost_price}
	{/if}
	
	{if $ci_items[i].gst_rate}
		{assign var=gst_cost value=$cost_price*$ci_items[i].gst_rate/100}
		{assign var=gst_cost value=$gst_cost|round2}
		{assign var=cost_price value=$cost_price+$gst_cost}
	{/if}
	
	{if $form.discount_selling_price_percent}
		{assign var=cost_deduct value=$cost_price*$form.discount_selling_price_percent/100}
		{assign var=cost_price value=$cost_price-$cost_deduct}
		{assign var=cost_price value=$cost_price|round:2}
	{/if}
	{if $form.discount_item_row_percent}
		{assign var=cost_deduct value=$cost_price*$form.discount_item_row_percent/100}
		{assign var=cost_price value=$cost_price-$cost_deduct}
		{assign var=cost_price value=$cost_price|round:2}
	{/if}
	<td align="right" width="58px">{$cost_price|number_format:2}</td>
	<td align="right" width="54px">{$ci_items[i].discount|default:'0'}%</td>
	{assign var=amt_ctn value=$cost_price*$ci_items[i].ctn}
	{assign var=amt_pcs value=$cost_price/$ci_items[i].uom_fraction*$ci_items[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}

	{if $form.show_per}
		{if $ci_items[i].disc_arr.0}
		    {assign var=discount_per value=$ci_items[i].disc_arr.0*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
		{if $ci_items[i].disc_arr.1}
		    {assign var=discount_per value=$ci_items[i].disc_arr.1*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
	{/if}
	<td align="right" width="64px">{$total_row|number_format:2}</td>
	<td width="3px"></td>
	{assign var=total value=$total+$total_row|round:2}
	{assign var=total_qty value=$total_qty+$qty}
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
	<tr height=15>
		<td width="20px">&nbsp;</td>
		<td width="94px">&nbsp;</td>
		<td width="238px">&nbsp;</td>
		<td width="53px">&nbsp;</td>
		<td width="58px">&nbsp;</td>
		<td width="54px">&nbsp;</td>
		<td width="64px">&nbsp;</td>
		<td width="3px">&nbsp;</td>
	</tr>
{/section}

{if $is_lastpage}
	<tr>
		<td>&nbsp;</td>
		<td align=right colspan="2" rowspan="3" nowrap>
			<table width="100%" border=0 cellspacing=0 cellpadding=1>
				<tr>
					<td rowspan="3">Remark: {$form.remark|default:'&nbsp;'}</td>
					<td align="right">Sub Total</td>
				</tr>
				<tr><td align="right">Sheet Discount ({$form.discount_percent|default:'0'}%)</td></tr>
				<tr><td align="right">Total</td></tr>
			</table>
		</td>
		<td class="td_border_top td_border_bottom">&nbsp;</td>
		<td class="td_border_top td_border_bottom">&nbsp;</td>
		<td class="td_border_top td_border_bottom">&nbsp;</td>
		<td align=right class="td_border_top td_border_bottom">{$total|number_format:2}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<!--td align=right colspan="2" nowrap>Sheet Discount ({$form.discount_percent|default:'0'}%)</td-->
		<td class="td_border_bottom">&nbsp;</td>
		<td class="td_border_bottom">&nbsp;</td>
		<td class="td_border_bottom">&nbsp;</td>
		<td align=right class="td_border_bottom">{$form.discount_amount|number_format:2}</td>
		<td>&nbsp;</td>
	</tr>
	{if $form.discount_amount}
		{assign var=discount_amt value=$form.discount_amount}
		{assign var=total value=$total-$discount_amt|round:2}
		{assign var=total value=$total|round:2}
	{/if}
	<tr>
		<td>&nbsp;</td>
		<!--td align="right" colspan="2">Total</td-->
		<td align=right class="td_border_bottom">{$total_qty}</td>
		<td class="td_border_bottom">&nbsp;</td>
		<td align=right class="td_border_bottom">&nbsp;</td>
		<td align=right class="td_border_bottom">{$total|number_format:2}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="right" colspan="6">(In {$to_branch.currency_description} {convert_number number=$total show_decimal=1})</td>
		<td>&nbsp;</td>
	</tr>
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}

</table>
</div>
