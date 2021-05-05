{*
11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 1:17:40 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/10/2011 3:01:03 PM Andy
- Add nowrap for artno for all DO printing templates.

10/7/2011 11:25:19 AM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point

4/4/2012 10:54:00 AM Andy
- Change email address.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

4/14/2015 2:43 PM Andy
- Enhanced to show GST ID.

12/28/2015 1:10 PM Qiu Ying
- SKU Additional Description should show in document printing

5/25/2016 2:23 PM Andy
- Enhanced to show title as "CONSIGNMENT DELIVERY ORDER".
- Enhanced to show row amount and total row.

4/20/2017 10:39 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/22/2017 1:47 PM Justin
- Enhanced company address to use from Masterfile Branch instead of HARDCODED it.
*}
<!-- this is the print-out for approved and checkout DO , share template for cutemaree -->
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

.header_tbl td{
    border:1px solid black;
	padding:3px;
}
{/literal}
</style>
<script type="text/javascript">

var doc_no = '{$form.do_no}';
if (doc_no == '') doc_no = '{$form.prefix}{$form.id|string_format:"%05d"}(DD)';

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
<div style="text-align:center;" class="small">
	<img src="/cutemaree/cutemaree.jpg" height="70" /><br />
	<b>(Co No.. {$from_branch.company_no})</b><br />
	{$from_branch.address}<br />
	Tel: {$from_branch.phone_1|default:'-'}, {$from_branch.phone_2|default:'-'} &nbsp;&nbsp;&nbsp; Fax: {$from_branch.phone_3|default:'-'} &nbsp;&nbsp;&nbsp; E-mail: {$from_branch.contact_email}
	{if $config.enable_gst and $from_branch.gst_register_no}
		 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
	{/if}<br />
	<h2>CONSIGNMENT DELIVERY ORDER</h2>
</div>

<table class="header_tbl" cellspacing="10px" cellpadding="4" width=100%>
	<tr>
	    <!-- Bill To-->
	    <td width="50%" class="small">
	        	<h3>&nbsp;&nbsp;&nbsp;Bill To</h3>
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
	    <td width="30%" border="1" class="small">
	            Date: <b>{$form.do_date|date_format:"%d/%m/%Y"}</b><br /><br />
	            Department: <b>{$to_branch.con_dept_name|default:'-'}</b><br /><br />
	            Your/Our Purchase<br />
	            Order No: <b>CONSIGNMENT</b>
	    </td>
	    <td width="20%">
	        D/O No: <b>{$form.do_no}</b><br /><br />
	        Terms: <b>{$to_branch.con_terms|default:'-'}</b><br />
	        {$page}
	    </td>
	</tr>
</table>
<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th rowspan=2 width=5>&nbsp;</th>
	<th rowspan=2 width=100>Article</th>
	<th rowspan=2>SKU Description</th>
	<th rowspan=2 width=40>Selling<br>({$config.arms_currency.symbol})</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
	<th rowspan="2" width="80">Amount</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr>
	<td align=center>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	<td nowrap>{$do_items[i].artno|default:"-"}</td>
	<td width="90%">{if !$page_item_info[i].no_crop}<div class="crop">{/if}{$do_items[i].description}{if !$page_item_info[i].no_crop}</div>{/if}</td>
	
	{if !$page_item_info[i].not_item}
		<td align="right">{$do_items[i].cost_price|number_format:2}</td>
		<td align=center>{$do_items[i].uom_code|default:"EACH"}</td>
		<td align="right">{$do_items[i].ctn|qty_nf}</td>
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		<td align="right">{$do_items[i].inv_line_amt|number_format:2}</td>
		
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{repeat s=$counter+1 e=$PAGE_SIZE}
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
</tr>
{/repeat}

{if $is_lastpage}
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="right">Total</td>
		<td align="right">{$total_ctn|qty_nf}</td>
		<td align="right">{$total_pcs|qty_nf}</td>
		<td align="right">{$form.total_inv_amt|number_format:2}</td>
	</tr>
{/if}
</table>

{if $is_lastpage}
<br>
<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>
	{if $form.checkout}
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
		</table>
	{else}
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
		Checked By<br>
		Name:<br>
		Date:
		</td>

		<td valign=bottom class=small>
		_________________<br>
		Received By<br>
		Name:<br>
		Date:
		</td>
		<td valign=bottom class=small>
		    <u>Checkout Info</u><br />
			Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.name|default:'&nbsp;'}<br>
			IC No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.nric|default:'&nbsp;'}<br>
			Lorry No. : {$form.checkout_info.lorry_no|default:'&nbsp;'}<br>
		</td>
		</tr>
		</table>

		<!--p align=center class=small>** This document is for reference purpose only **</p-->
	{/if}
{/if}
</div>
