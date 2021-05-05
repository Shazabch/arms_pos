{*
9/15/2008 2:03:22 PM yinsee
- tweak printing, add cost total column

1/9/2009 11:05:47 AM yinsee
- remove the triple-description test (last time forgot to remove!!)

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

1/13/2010 4:56:56 PM Andy
- Add config to manage item got line or not

6/18/2010 5:13:53 PM Andy
- Fix adjustment printing if remark too long will make alignment run.

3/9/2011 2:03:52 PM Justin
- Added new column selling price.
- Added the checking not enable/disable the cost and selling price columns.

7/15/2011 1:15:22 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

7/27/2011 4:19:23 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config but not fixed by 2.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.adj_printing_no_item_line}
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

{literal}
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
<script type="text/javascript">

var doc_no = '{$form.report_prefix}{$form.id|string_format:"%05d"}';

{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr valign=top>
	<td><img src="{get_logo_url mod='adjustment'}" height=80 hspace=5 vspace=5></td>
	<td width=100% class="small">
	<h2>{$form.b_description}</h2>
	{$form.b_address|nl2br}<br />
	Tel: {$form.b_phone_1}{if $form.b_phone_2} / {$form.b_phone_2}{/if}
	{if $form.b_phone_3}
	&nbsp;&nbsp; Fax: {$form.b_phone_3}
	{/if}
	</td>
	<td rowspan="2" align=right>
	    <table>
			<tr height=22><td colspan=2 style="background:#000;padding:4px;color:#fff" align=center><b>Adjustment</b></td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Adj No.</td><td nowrap>{$form.report_prefix}{$form.id|string_format:"%05d"}</td></tr>
		    <tr height=22><td nowrap>Adj Date</td><td nowrap>{$form.adjustment_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<table class="normal" border=0 cellspacing=0 cellpadding=1>
			<tr>
			<th align=left nowrap>Department : </th>
			<td align=left width=10%>{$form.dept}</td>
			<th align=left nowrap>Adjustment Type : </th>
			<td align=left width=30%>{$form.adjustment_type}</td>
			{if $form.remark}
			<th align=left nowrap>Remark : </th>
			<td align=left width=30%>{$form.remark}</td>
			{/if}
		</tr>
		</table>
	</td>
</tr>
</table>
<br />
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>ARMS Code</th>
	<th rowspan=2>Art No</th>
	<th rowspan=2>MCode</th>
	<th rowspan=2 width=50%>SKU Description</th>
	{if $cost_enable}
		<th rowspan=2>Unit<br />Cost</th>
	{/if}
	{if $sp_enable}
		<th rowspan=2>Unit<br />Selling<br />Price</th>
	{/if}
	<th colspan=2>Qty</th>
	{if $cost_enable}
		<th rowspan=2>Cost<br />(RM)</th>
	{/if}
	{if $sp_enable}
		<th rowspan=2>Selling<br />Price<br />(RM)</th>
	{/if}
</tr>

<tr bgcolor=#cccccc>
	<th>Positive</th>
	<th>Negative</th>
</tr>

{assign var=counter value=0}

{section name=i loop=$adjust_items}
	{if $adjust_items[i].doc_allow_decimal}
		{assign var=qty_dp value=$config.global_qty_decimal_points}
	{else}
		{assign var=qty_dp value=2}
	{/if}
<!-- {$counter++} -->
<tr class="no_border_bottom">
	<td align=center>{$start_counter+$counter}.</td>
	<td nowrap>{$adjust_items[i].sku_item_code|default:"&nbsp;"}</td>
	<td nowrap>{$adjust_items[i].artno|default:"&nbsp;"}</td>
  <td>{$adjust_items[i].mcode|default:'&nbsp;'}</td>
	<td><div class="crop">{$adjust_items[i].description}</div></td>
	{if $cost_enable}
		<td align=right>
			{$adjust_items[i].cost|number_format:$config.global_cost_decimal_points}
		</td>
	{/if}
	{if $sp_enable}
		<td align=right>
			{$adjust_items[i].selling_price|number_format:2}
		</td>
	{/if}
	{assign var=qty value=$adjust_items[i].qty|round:$qty_dp}
	<td align=right>{if $qty>0}{$qty|number_format:$qty_dp}{assign var=p_qty value=$p_qty+$qty}{else}&nbsp;{/if}</td>	
	<td align=right>{if $qty<0}{$qty|abs|number_format:$qty_dp}{assign var=n_qty value=$n_qty+$qty}{else}&nbsp;{/if}</td>	
	{if $cost_enable}
		<td align=right>
			{assign var=cost_amt value=$qty*$adjust_items[i].cost}
			{$cost_amt|number_format:2}
			{assign var=totalcost value=$totalcost+$cost_amt|round:2}
		</td>
	{/if}
	{if $sp_enable}
		<td align=right>
			{assign var=selling_amt value=$qty*$adjust_items[i].selling_price}
			{$selling_amt|number_format:2}
			{assign var=totalsp value=$totalsp+$selling_amt|round:2}
		</td>
	{/if}
</tr>
{/section}

{repeat s=$counter+1 e=$item_per_page}
<!-- filler -->
<tr height=20 class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $cost_enable}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	{if $sp_enable}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/repeat}

<tr bgcolor=#cccccc align=right class="total_row">
	{assign var=colspan value=5}
	{if $cost_enable}
		{assign var=colspan value=$colspan+1}
	{/if}
	{if $sp_enable}
		{assign var=colspan value=$colspan+1}
	{/if}
	<th colspan="{$colspan}">Total</th>
	<th>{$p_qty|default:0|qty_nf}</th>
	<th>{$n_qty|default:0|abs|qty_nf}</th>
	{if $cost_enable}
		<th>{$totalcost|number_format:2}</th>
	{/if}
	{if $sp_enable}
		<th>{$totalsp|number_format:2}</th>
	{/if}
</tr>


</table>

<!--table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr bgcolor=#cccccc>
	<th width=80>&nbsp;</th>
	<th>Name</th>
	<th>Signature</th>
	<th>Date</th>
	<th>Time</th>
</tr>
<tr height=50>
	<td><b>Issued By</b></td>
	<td align=center>{$sessioninfo.fullname}</td>
	<td>&nbsp;</td>
	<td align=center>{$smarty.now|date_format:"%d/%m/%Y"}</td>
	<td align=center>{$smarty.now|date_format:"%H:%M:%S"}</td>
</tr>
<tr height=50>
	<td><b>Checking By</b></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr height=50>
	<td><b>Received By</b></td>
	<td valign=top>

	Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.name|default:'&nbsp;'}<br>
	IC No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.nric|default:'&nbsp;'}<br>
	Lorry No. : {$form.checkout_info.lorry_no|default:'&nbsp;'}<br>

	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table-->

</div>
