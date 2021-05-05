{*
REVISION HISTORY
++++++++++++++++

10/5/2007 4:17:35 PM gary
- remove cost price column if have speacial config for it ($config[do_print_hide_cost]).

12/7/2007 2:43:52 PM gary
- printout follow the sequence from input.

1/29/2008 11:27:54 AM yinsee
- gary added line for each rows (why no put comment?)
- add selling price

3/31/2008 5:37:01 PM gary
- add ctn price column.

7/1/2008 2:32:38 PM yinsee
- change 15 to $PAGE_SIZE
- add is_lastpage check to print footer

8/12/2008 12:28:50 PM yinsee
- auto crop at 1em for description column

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

5/10/2010 3:08:39 PM Andy
- Add DO Markup.

7/5/2010 10:18:21 AM Andy
- Change to show mcode first, if no mcode only show artno.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 1:33:04 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/10/2011 3:04:29 PM Andy
- Add nowrap for artno for all DO printing templates.

10/7/2011 11:25:19 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

12/6/2011 10:04:11 AM Andy
- Fix wrong deliver address.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/28/2015 2:10 PM Qiu Ying
- SKU Additional Description should show in document printing
*}

{config_load file="site.conf"}
{if !$skip_header}
{include file='header.print.tpl'}
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
	{$from_branch.address|nl2br}
	<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}DELIVERY ORDER</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:"%d/%m/%Y"}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>From </h4>
		<b>{$from_branch.description}</b><br>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
		</td>
		
		{if !$form.do_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>		
        {elseif $form.do_type eq 'credit_sales'}
		    <td valign=top style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.debtor_description}</b><br>
			{$form.debtor_address}<br>
			</td>
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>Deliver To</h4>
			<b>{$to_branch.description}</b><br>
			
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}<br>
			{else}
				{$form.address_deliver_to|nl2br}<br>
			{/if}
			
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
	<th rowspan=2 width=50>ARMS Code</th>
	<th rowspan=2 width=50>Article /<br>MCode</th>
	<th rowspan=2>SKU Description</th>
	<th rowspan=2 width=40>Selling<br>(RM)</th>	
	{if !$config.do_print_hide_cost}
		<th rowspan=2 width=40>Price<br>(RM)</th>
	{/if}
	<th rowspan=2 width=40>UOM</th>
	{if !$config.do_print_hide_cost}
		<th rowspan=2 width=40>Ctn Price<br>(RM)</th>
	{/if}
	<th nowrap colspan=2 width=80>Qty</th>
	{if !$config.do_print_hide_cost}
	<th rowspan=2 width=80>Total Amount<br>(RM)</th>
	{/if}
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom">
	<td align=center>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td align=center>{$do_items[i].sku_item_code|default:'&nbsp;'}</td>
	<td nowrap>{if $do_items[i].mcode <> ''}{$do_items[i].mcode}{else}{$do_items[i].artno|default:'&nbsp;'}{/if}</td>
	<td width="90%"><div {if !$page_item_info[i].no_crop}class="crop"{/if}>{$do_items[i].description}</div></td>
	
	{if !$page_item_info[i].not_item}
		<td align="right">{$do_items[i].selling_price|number_format:2}</td>
		{assign var=cost value=$do_items[i].cost_price}
		{if $form.do_markup}
			{assign var=do_markup_per value=$form.do_markup/100+1}
			{assign var=cost value=$cost*$do_markup_per}
		{/if}
		
		{if !$config.do_print_hide_cost}
			<td align="right">{$cost/$do_items[i].uom_fraction|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td align=center>{$do_items[i].uom_code|default:"EACH"}</td>
		{if !$config.do_print_hide_cost}
			<td align="right">
			{if $do_items[i].uom_fraction>1}
				{$cost|number_format:$config.global_cost_decimal_points}
			{else}
				-
			{/if}
			</td>
		{/if}
		<td align="right">{$do_items[i].ctn|qty_nf}</td>
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$cost*$do_items[i].ctn}
		{assign var=amt_pcs value=$cost/$do_items[i].uom_fraction*$do_items[i].pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}
		{if !$config.do_print_hide_cost}
		<td align="right">{$total_row|number_format:2}</td>
		{/if}
		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		{if !$config.do_print_hide_cost}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
	
</tr>
{/section}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20 class="no_border_bottom">
{if !$config.do_print_hide_cost}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
{/if}
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

{if $is_lastpage}
<tr class="total_row">
{if !$config.do_print_hide_cost}
	<th align=right colspan=8 class="total_row">Total</th>
{else}
	<th align=right colspan=6 class="total_row">Total</th>
{/if}
	<th align=right class="total_row">{$total_ctn|qty_nf}</th>
	<th align=right class="total_row">{$total_pcs|qty_nf}</th>
{if !$config.do_print_hide_cost}
	<th align=right class="total_row">{$total|number_format:2}</th>
{/if}
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>

<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>
<br>
<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr bgcolor=#cccccc>
	<th width=80>&nbsp;</th>
	<th>Name</th>
	<th>Signature</th>
	<th>Date</th>
	<th>Time</th>
</tr>
<tr height=50>
	<td><b>Issued By</b></td>
	<td align=center>{$form.owner_fullname}</td>
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
</table>
{/if}
</div>
