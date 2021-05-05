{*
8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/19/2017 2:12 PM Khausalya
-Enhanced changes from RM to use config setting. 

5/8/2017 9:22 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
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
var doc_no = '{$form.do_receipt_no|string_format:"%010d"}';
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height="80" width="150" hspace="5" vspace="5">{else}&nbsp;{/if}</td>
	<td width="70%">
	<h2>{$from_branch.description}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
	<td width="100%" rowspan=2 align="right" valign="top">
	    <table width="100%" style="font-weight:bold;">
		<tr><td colspan="2">
			<div style="padding:4px; text-align:right;" align=center>
				<b>{$page}</b>
			</div>
			<br />
			<br />
			<br />
		</td></tr>
		<tr>
			<td colspan="2" class="xlarge">Official Receipt<br /></td>
		</tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Receipt No.: </td><td nowrap>{$form.do_receipt_no|string_format:"%010d"}</td></tr>
		{if !$config.do_printing_allow_hide_date or !$no_show_date}
			<tr height=22><td nowrap>DO Date: </td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		{/if}
		<tr height=22>
			<td nowrap>Received By: </td>
			{if $form.do_type eq 'open'}
				{assign var=rcv_by value='Cash Sales'}
			{elseif $form.do_type eq 'credit_sales'}
				{assign var=rcv_by value='Credit Sales'}
			{else}
				{assign var=rcv_by value=$form.do_type}
			{/if}
			<td nowrap>{$rcv_by|upper}</td>
		</tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr height=22><td nowrap>Receipt Reference: </td><td nowrap>{$form.checkout_remark|default:$form.remark|upper}</td></tr>
		<tr height=22><td nowrap>Total Receipt: </td><td nowrap>{$form.total_amount|default:0|number_format:2}</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By: </td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
	<div><b>&nbsp;&nbsp;Received From:</b></div>
	<table width="98%" cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		{if !$form.do_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px">
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>
        {elseif $form.do_type eq 'credit_sales'}
		    <td valign=top style="border:1px solid #000; padding:5px">
			<b>{$form.debtor_description}</b><br>
			{$form.debtor_address}<br>
			</td>
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<b>{$to_branch.description} </b><br>
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}
			{else}
				{$form.address_deliver_to|nl2br}
			{/if}
			<br>
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
			</td>
		{/if}

	</tr>
	</table>
	</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th width=5>#</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br>/MCode</th>
	<th width="90%">SKU Description</th>
	<th width=40>Price ({$config.arms_currency.symbol})</th>
	<th width=40>Qty<br />(Pcs)</th>
	<th width=80>Total Amount<br>({$config.arms_currency.symbol})</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>{$start_counter+$counter}.</td>
	<td align="center" nowrap>{$do_items[i].sku_item_code|default:'-'}</td>
	<td align="center" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{elseif $do_items[i].mcode <> ''}{$do_items[i].mcode|default:'&nbsp;'}{else}{$do_items[i].artno_mcode}{/if}</td>
	<td width="90%"><div class="crop">{$do_items[i].description}</div></td>
	<td align="right">{if $do_items[i].oi == ''}{$do_items[i].cost_price|number_format:2}{else}-{/if}</td>
	{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
	{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
	{assign var=row_amt value=$amt_ctn+$amt_pcs}
	{assign var=row_amt value=$row_amt|round2}
	{assign var=row_qty value=$do_items[i].ctn*$do_items[i].uom_fraction+$do_items[i].pcs}

	<td align="right">{$row_qty|qty_nf}</td>
	<td align="right">{$row_amt|number_format:2}</td>
	{assign var=total_qty value=$total_qty+$row_qty}
	{assign var=total_amt value=$total_amt+$row_amt}
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  <td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
    <th align="left" colspan="4">
		<div style="float:left;">
			{$config.arms_currency.name}: {convert_number number=$total_amt show_decimal=1} Only.
		</div>
		<div style="float:right;">
			E.&#38;.O.E.
		</div>
	</th>
    <th align="right">Total</th>
	<th align="right">{$total_qty|qty_nf}</th>
	<th align="right">{$total_amt|number_format:2}</th>
</tr>
{assign var=total_qty value=0}
{assign var=total_amt value=0}
{/if}

</table>

{if $is_lastpage}

<table width=100%>
<tr height=100>
	<td valign=bottom class=small>
		VIVA Privilege Sdn Bhd (Sunway)<br /><br /><br /><br />
		______________________________________<br />
		Authorised Signatory & Company Stamp<br />
	</td>
</tr>
</table>
{/if} 

</div>

