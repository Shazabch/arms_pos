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

11/12/2009 5:40:50 PM Andy
- layout edited, artile no and ctn price hide

11/23/2009 10:27:09 AM Andy
- layout edit

11/24/2009 5:10:45 PM Andy
- change cash sales to cash bill

12/14/2009 5:58:48 PM Andy
- column title changed, "Selling" change to "RSP"

1/12/2010 3:51:36 PM Andy
- Add config to manage item got line or not

1/15/2010 9:47:20 AM Andy
- Make description column to occupy as large space as it can

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 1:23:27 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/10/2011 2:58:25 PM Andy
- Add nowrap for artno for all DO printing templates.

11/29/2011 04:37:00 PM Andy
- Add to hide DO Date when found user got tick "Don't Show DO Date".

5/17/2013 11:43 AM Andy
- Enhance default printing format to compatible with additional description.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

4/14/2015 2:43 PM Andy
- Enhanced to show GST ID.

12/28/2015 2:10 PM Qiu Ying
- SKU Additional Description should show in document printing

04/01/2016 15:15 Edwin
- Removed unnecessary characters in table header's title

04/01/2016 17:30 Edwin
- Added debtor telephone number and terms at credit sales print preview

4/19/2017 1:23 PM Khausalya 
- Enhanced changes from RM to use config setting. 

7/19/2017 11:05 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items
- Bug fixed on artno/mcode not show if the items is open item

12/6/2017 5:03 PM Justin
- Modified to change "From" become "Bill to Address" and "To" become "Deliver to Address".
- Enhanced to show "Bill to Address" and "Deliver to Address" according to user settings from DO module.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/22/2018 10:26 AM Justin
- Enhanced to show Old Code instead of ARMS Code when config is turned on.

12/27/2018 4:02 PM Justin
- Enhanced to print barcode on top of the document number.

1/18/2019 9:11 AM Justin
- Bug fixed on the slash "/" from barcode will causing the wrong result from scanner.

6/14/2019 5:17 PM William
- Added new Vertical print format.

9/15/2020 2:49 PM William
- Enhanced to add "Shipment Method" and "Tracking Code" to DO Printing.

12/15/2020 11:40 AM William
- Enhanced to check SKU Code Column when option is enable.

3/19/2021 12:02 PM Andy
- Fixed the colspan in row total is incorrect when no hide RSP.
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
{if $system_settings.logo_vertical eq 1}
	<td colspan="2" width="100%">
		<table width="100%" style="text-align: center;">
			<tr>
			{if !$config.do_print_hide_company_logo}
				<td><img src="{get_logo_url mod='do'}" style="max-width: 600px;max-height: 80px;" hspace="5" vspace="5"></td>
			{else}
				<td>&nbsp;</td>
			{/if}
			</tr>
			{if $system_settings.verticle_logo_no_company_name neq 1}
			<tr>
				<td><h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2></td>
			</tr>
			{/if}
			<tr>
				<td>{$from_branch.address}</td>
			</tr>
			<tr>
				<td>
				Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
				{if $from_branch.phone_3}
				&nbsp;&nbsp; Fax: {$from_branch.phone_3}
				{/if}
				{if $config.enable_gst and $from_branch.gst_register_no}
					 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
				{/if}
				</td>
			</tr>
			<tr>
				{if $form.is_under_gst && $form.checkout}
					<td><h1 align="center">Tax Invoice</h1></td>
				{/if}
			</tr>
		</table>
	</td>
{else}
	<td>
	{if !$config.do_print_hide_company_logo}
		<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">
	{else}
	&nbsp;
	{/if}
	</td>
	<td width=100%>
	<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
{/if}
	<td rowspan=2 align="right">
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align="center"><b>
{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}DELIVERY ORDER</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height="22">
			<td nowrap>DO No.</td>
			<td {if $form.approved eq 1 && $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
				{if $form.approved eq 1 && $config.print_document_barcode}
					<span class="barcode3of9" style="padding:0;">
						*{$form.do_no|replace:'/':'/O'}*
					</span>
				{/if}
				
				<div {if $form.approved eq 1 && $config.print_document_barcode}style="margin-top:-5px;"{/if}>
					{$form.do_no}
				</div>
			</td>
		</tr>
		{if !$config.do_printing_allow_hide_date or !$no_show_date}
			<tr height="22"><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		{/if}
		<tr height="22"><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{if $form.offline_id}
			<tr height="22" bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height="22"><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height="22"><td colspan="2" align="center">{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width="100%" cellspacing="5" cellpadding="0" border="0" height="120px">
		<tr>
			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
				<h4>Bill to Address</h4>
				{if $form.do_type ne 'transfer'}
					{if $form.do_type eq 'credit_sales'}
						<b>{$form.debtor_description}</b><br>
						{$form.debtor_address|nl2br}<br>
						Tel: {$form.debtor_phone|default:'-'}<br>
						Terms: {$form.debtor_term|default:'-'}<br>
					{else}
						<b>{$form.open_info.name}</b><br>
						{$form.open_info.address|nl2br}<br />
					{/if}
				{else}
					<b>{$to_branch.description}</b><br>
					{$to_branch.address|nl2br}<br>
					Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
					{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{/if}
			</td>
			
			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
				<h4>Deliver to Address</h4>
				{if $form.do_type ne 'transfer'}
					{if $form.do_type eq 'credit_sales'}
						<b>{$form.deliver_debtor_description|default:$form.debtor_description}</b><br>
						{$form.debtor_deliver_address|nl2br}<br>
						Tel: {$form.deliver_debtor_phone|default:$form.debtor_phone|default:'-'}<br>
						Terms: {$form.deliver_debtor_term|default:$form.debtor_term|default:'-'}<br>
					{else}
						{if $form.use_address_deliver_to}
							<b>{$form.open_info.delivery_name|default:$form.open_info.name}</b><br>
							{$form.open_info.delivery_address|default:$form.open_info.address|nl2br}<br />
						{else}
							<b>{$form.open_info.name}</b><br>
							{$form.open_info.address|nl2br}<br />
						{/if}
					{/if}
				{else}
					<b>{$to_branch.description}</b><br>
					{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
						{if $to_branch.deliver_to}
							{$to_branch.deliver_to|nl2br}
						{else}
							{$to_branch.address|nl2br}
						{/if}
					{else}
						{$form.address_deliver_to|nl2br}
					{/if}
					<br>
					Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
					{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{/if}
			</td>
		</tr>
	</table>
</td>
</tr>
</table>

<br>
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc class="top_line">
	<th rowspan=2 width=5>&nbsp;</th>
	{if $smarty.request.print_col.sku_item_code}
		<th rowspan=2 nowrap>ARMS Code</th>
	{/if}
	{if $smarty.request.print_col.mcode}
		<th rowspan=2 nowrap>MCode</th>
	{/if}
	{if $smarty.request.print_col.artno}
		<th rowspan=2 nowrap>Article</th>
	{/if}
	{if $smarty.request.print_col.link_code}
		<th rowspan=2 nowrap>{$config.link_code_name|default:'Old Code'}</th>
	{/if}
	<th rowspan=2 width="90%">SKU Description</th>
	{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>({$config.arms_currency.symbol})</th>{/if}

	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
</tr>

<tr bgcolor=#cccccc>
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}

<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align=center>
	
		{if !$page_item_info.$item_index.not_item}
			{$r.item_no+1}.
		{else}
			&nbsp;
		{/if}
	
	</td>
	{if $smarty.request.print_col.sku_item_code}
		<td align="center">{$r.sku_item_code|default:'&nbsp;'}</td>
	{/if}
	{if $smarty.request.print_col.mcode}
		<td align="center">{$r.mcode|default:'&nbsp;'}</td>
	{/if}
	{if $smarty.request.print_col.artno}
		<td align="center">{$r.artno|default:'&nbsp;'}</td>
	{/if}
	{if $smarty.request.print_col.link_code}
		<td align="center">{$r.link_code|default:'&nbsp;'}</td>
	{/if}
	<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
	
	{if !$page_item_info.$item_index.not_item}
		{if !$hide_RSP}<td align="right">{$r.selling_price|number_format:2}</td>{/if}
		<td align=center>{$r.uom_code|default:"EACH"}</td>
		<td align="right">{$r.ctn|qty_nf}</td>
		<td align="right">{$r.pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$r.cost_price*$r.ctn}
		{assign var=amt_pcs value=$r.cost_price/$r.uom_fraction*$r.pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs|round2}

		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$r.ctn+$total_ctn}
		{assign var=total_pcs value=$r.pcs+$total_pcs}
	{else}
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	{/if}
</tr>
{/foreach}


{section name=s start=0 loop=$extra_empty_row}
<tr height=20 class="no_border_bottom {if $smarty.section.s.iteration eq $extra_empty_row and !$is_lastpage}td_btm_got_line{/if}">
	{if $smarty.request.print_col.sku_item_code}<td>&nbsp;</td>{/if}
	{if $smarty.request.print_col.mcode}<td>&nbsp;</td>{/if}
	{if $smarty.request.print_col.artno}<td>&nbsp;</td>{/if}
	{if $smarty.request.print_col.link_code}<td>&nbsp;</td>{/if}
	{if !$hide_RSP}<td>&nbsp;</td>{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{assign var=colspan value=3}
{if $smarty.request.print_col.sku_item_code}{assign var=colspan value=$colspan+1}{/if}
{if $smarty.request.print_col.mcode}{assign var=colspan value=$colspan+1}{/if}
{if $smarty.request.print_col.artno}{assign var=colspan value=$colspan+1}{/if}
{if $smarty.request.print_col.link_code}{assign var=colspan value=$colspan+1}{/if}
{if !$hide_RSP}{assign var=colspan value=$colspan+1}{/if}
{if $is_lastpage}
<tr class="total_row">
{*
{if !$config.do_print_hide_cost}
	<th align=right colspan=6 class="total_row">Total</th>
{else}
	<th align=right colspan=4 class="total_row">Total</th>
{/if}
*}
    <th align=right colspan={$colspan} class="total_row">Total</th>
	<th align=right class="total_row">{$total_ctn|qty_nf}</th>
	<th align=right class="total_row">{$total_pcs|qty_nf}</th>
{*
{if !$config.do_print_hide_cost}
	<th align=right class="total_row">{$total|number_format:2}</th>
{/if}
*}
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
<br>
<table width="100%">
	<tr>
		<td width="50%"><b>Remark</b></td>
		<td width="50%"><b>Additional Remark</b></td>
	</tr>
	<tr>
		<td>
			<div style="border:1px solid #000;padding:5px;height:60px;">
			{$form.remark|default:"-"|nl2br}
			</div>
		</td>
		
		<td>
			<div style="border:1px solid #000;padding:5px;height:60px;">
			{$form.checkout_remark|default:"-"|nl2br}
			</div>
		</td>
	</tr>
	{if $form.shipment_method || $form.tracking_code}
	<tr>
		<td><b>Shipping Details</b></td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%"  style="border:1px solid #000;padding:5px;">
				<tr>
					<td style="width: 50%;"><b>Shipment Method:</b> {$form.shipment_method}</td>
					<td style="width: 50%;"><b>Tracking Code:</b> {$form.tracking_code}</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
</table>
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
	<td align=center>{$smarty.now|date_format:$config.dat_format}</td>
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
