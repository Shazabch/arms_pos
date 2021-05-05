{*

2/4/2013 10:06 AM Fithri
- Bugfix : Print Disposal Report doesnt show the quantity

2/4/2013 4:03 PM Fithri
- Bugfix : Fix total amount to display with proper decimal point

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
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
	<td><img src="{get_logo_url}" height=80 hspace=5 vspace=5></td>
	<td width=100% class="small">
	<h2>{$form.b_description} {if $form.b_company_no}({$form.b_company_no}){/if}</h2>
	{$form.b_address|nl2br}<br>
	Tel: {$form.b_phone_1}{if $form.b_phone_2} / {$form.b_phone_2}{/if}
	{if $form.b_phone_3}
	&nbsp;&nbsp; Fax: {$form.b_phone_3}
	{/if}
	</td>
	<td rowspan="2" align=right>
	    <table>
			<tr height=22><td colspan=2 style="background:#000;padding:4px;color:#fff" align=center><b>Disposal</b></td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Disposal No.</td><td nowrap>{$form.report_prefix}{$form.id|string_format:"%05d"}</td></tr>
		    <tr height=22><td nowrap>Disposal Date</td><td nowrap>{$form.adjustment_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr>
			<tr height=22><td nowrap>Vendor Code</td><td nowrap>{$vp_session.code}</td></tr>
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

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th>&nbsp;</th>
	<th>ARMS Code</th>
	<th>Art No</th>
	<th>MCode</th>
	<th width=40%>SKU Description</th>
	<th>Qty</th>
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
		{assign var=qty value=$adjust_items[i].qty|round:$qty_dp}
		<td align=right>{assign var=qty value=$qty|abs}{if $qty>0}{$qty|number_format:$qty_dp}{assign var=p_qty value=$p_qty+$qty}{else}&nbsp;{$qty}{/if}</td>	
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
</tr>
{/repeat}

<tr bgcolor=#cccccc align=right class="total_row">
	<th colspan=5>Total</th>
	<th>{$p_qty|number_format:$qty_dp}</th>
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
