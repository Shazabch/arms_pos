{*
REVISION HISTORY
++++++++++++++++

10/5/2007 4:17:35 PM gary
- remove cost price column if have speacial config for it ($config[do_print_hide_cost]).

11/6/2007 4:25:18 PM gary
- change the invoice format. (remove subtotal and else....)
*}

{config_load file="site.conf"}
{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">
<style>
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
<body onload="window.print()">
{/if}

<!-- print sheet -->
<div class=printarea>
<div style="float:right">{$page}</div>
<br>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5></td>
	<td width=100%>
	<h2>{$from_branch.description}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>INVOICE</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:"%d/%m/%Y"}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0>
	<tr>
		<td valign=top style="border:1px solid #000; padding:5px">
		<h4>From </h4>
		<b>{$from_branch.description}</b><br>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
		</td>

		<td valign=top style="border:1px solid #000; padding:5px">
		<h4>Deliver To</h4>
		<b>{$to_branch.description}</b><br>
		{$to_branch.address|nl2br}<br>
		Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
		{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
		</td>

	</tr>
	</table>
</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width=50>ARMS Code</th>
	<th rowspan=2 width=50>Article /<br>MCode</th>
	<th rowspan=2>SKU Description</th>
	<th rowspan=2 width=40>Cost Price<br>(RM)</th>
	<th rowspan=2 width=40>Purchase<br>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th rowspan=2 width=80>Total Amount<br>(RM)</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}

{foreach from=$do_items item=e key=dept}
{assign var=total_dept value=0}
{assign var=total_dept_ctn value=0}
{assign var=total_dept_pcs value=0}

	{section name=i loop=$do_items.$dept}
	<!-- {$counter++} -->
<tr bgcolor="{cycle values="#eeeeee,"}">
	<td align=center>{$counter}.</td>
	<td align=center>{$do_items.$dept[i].sku_item_code}</td>
	<td>{$do_items.$dept[i].artno_mcode|default:"-"}</td>
	<td>{$do_items.$dept[i].description|truncate:50}</td>
	{assign var=p_markup value=$markup+100}
	{assign var=p_markup value=$p_markup/100}
	{assign var=cost_price value=`$p_markup*$do_items.$dept[i].cost_price`}
	<td align="right">{$cost_price|number_format:3}</td>
	<td align=center>{$do_items.$dept[i].uom_code|default:"EACH"}</td>
	<td align="right">{$do_items.$dept[i].ctn}</td>
	<td align="right">{$do_items.$dept[i].pcs}</td>
	
	{assign var=amt_ctn value=$cost_price*$do_items.$dept[i].ctn}
	{assign var=amt_pcs value=$cost_price/$do_items.$dept[i].uom_fraction*$do_items.$dept[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}

	<td align="right">{$total_row|number_format:2}</td>

	{assign var=total value=$total+$total_row}
	{assign var=total_ctn value=$do_items.$dept[i].ctn+$total_ctn}
	{assign var=total_pcs value=$do_items.$dept[i].pcs+$total_pcs}
	
	{assign var=total_dept value=$total_dept+$total_row}
	{assign var=total_dept_ctn value=$do_items.$dept[i].ctn+$total_dept_ctn}
	{assign var=total_dept_pcs value=$do_items.$dept[i].pcs+$total_dept_pcs}
</tr>
	{/section}
{/foreach}

{repeat s=$counter+1 e=15}
<!-- filler -->
<tr height=20>
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
{/repeat}

<tr>
	<th align=right colspan=6>Total</th>
	<td align=right>{$total_ctn}</td>
	<td align=right>{$total_pcs}</td>
	<td align=right>{$total|number_format:2}</td>
</tr>
</table>

<br>

<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:80px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>
<br>

<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$sessioninfo.fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Approved By<br>
Name:<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>

</div>
