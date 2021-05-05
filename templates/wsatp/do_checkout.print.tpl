{*
5/13/2014 4:31 PM Justin
- Added new column "Warranty Period".

1/22/2015 9:35 AM Justin
- Bug fixed on serial no cannot show in full if more than 3.
*}

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
table.tb2 {
    border-collapse: collapse;
    border-right:1px solid #000;
    border-bottom:1px solid #000;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.do_no}';
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
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>{else}&nbsp;{/if}</td>
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
		<div style="background:#000;padding:4px;color:#fff" align=center><b>DELIVERY ORDER</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Issued By</td><td nowrap>{$form.owner_fullname|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		{if $form.do_type eq 'transfer'}
			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
				<h4>From </h4>
				<b>{$from_branch.description}</b><br>
				{$from_branch.address|nl2br}<br>
				Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
				{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
			</td>
		{/if}
		
		{if $form.do_type eq 'open'}
			<td valign="top" width="100%" style="border:1px solid #000; padding:5px">
			<h4>Delivery Address</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address|nl2br}<br>
			</td>
		{elseif $form.do_type eq 'credit_sales'}
			<td valign=top style="border:1px solid #000; padding:5px">
			<h4>Delivery Address</h4>
			<b>{$form.debtor_description}</b><br>
			{$form.debtor_address|nl2br}<br>
			{if $form.debtor_cp}Attention: {$form.debtor_cp}<br>{/if}
			Tel: {$form.p1|default:"-"}{if $form.p2} / {$form.p2}{/if}
			{if $form.pfax}<br>Fax: {$form.pfax}{/if}
			</td>
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To</h4>
			<b>{$to_branch.description}</b><br>
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

<tr bgcolor=#cccccc class="top_line">
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 nowrap>ARMS Code</th>
	<th rowspan=2 width="90%">SKU Description</th>
	<th rowspan=2 width=40>UOM</th>
	<th rowspan=2>Warranty Period</th>
	<th nowrap colspan=2 width=80>Qty</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align=center>
		{if !$page_item_info.$item_index.not_item}
			{$r.item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align="center">{$r.sku_item_code|default:'&nbsp;'}</td>
	
	{if !$page_item_info.$item_index.not_item}
		<td width="90%"><div class="crop">{$r.description}</div></td>
		<td align="center">{$r.uom_code|default:"EACH"}</td>
		<td align="center" nowrap>{$r.warranty_period|default:"-"}</td>
		<td align="right">{$r.ctn|qty_nf}</td>
		<td align="right">{$r.pcs|qty_nf}</td>
	{else}
		<td width="90%">{$r.description}</td>
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
	
	{assign var=total_ctn value=$r.ctn+$total_ctn}
	{assign var=total_pcs value=$r.pcs+$total_pcs}
	
</tr>
{/foreach}


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
    <th align=right colspan="5" class="total_row">Total</th>
	<th align=right class="total_row">{$total_ctn|qty_nf}</th>
	<th align=right class="total_row">{$total_pcs|qty_nf}</th>
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>
<div style="height:5px;"></div>
<div style="border:1px solid #000;padding:1px;min-height:15px;">
	<table width="100%">
		<tr>
		    <td valign="top" nowrap><b>Remark: </b></td>
		    <td width="99%">{$form.remark}</td>
		</tr>
	</table>
</div>


<div style="border:1px solid #000;padding:1px;min-height:15px;">
    <table width="100%">
		<tr>
		    <td nowrap><b>Additional Remark</b></td>
		    <td width="99%">{$form.checkout_remark|default:"-"}</td>
		</tr>
	</table>
</div>

{if $is_lastpage}
	<br>
	<table width=100%>
	<tr><td class=small>
	Received above goods in good order and condition,<br />
	</td></tr>
	<tr height=120>

	<td valign=bottom class=small>
	_________________<br>
	Received By (Company Stamp & Signature)<br />
	Name:<br>
	Date:<br>
	</td>
	</tr>
	<tr>
	<tr><td>&nbsp;</td></tr>
	<td style="padding:4px; border: 1px solid;">
	i) Should the goods received is damaged, please feedback within 24hours.<br />
	ii) This is a computer generated document, no signature is required.<br />
	iii) Goods Sold Are Not Returnable Or Exchangeable.<br />
	iv) E. & O. E..
	</td>
	</tr>
	</table>
{else}
    <p align="center"><b>Continue Next Page</b></p>
{/if}
</div>
