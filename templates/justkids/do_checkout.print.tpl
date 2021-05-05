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

6/23/2011 11:04:12 AM Justin
- Changed font size like DO.No for item list
- Amended the report to fix each SKU to print in one row.
- Amended and recorded with/without margin to print more rows.
- Add description before signature (N.B. Buyer must be to check the good at at he of delivery for no compliants will be entertained there - after)
- Added the label of print (NEXT PAGE...) before signature when it is not last page.

6/28/2011 3:19:12 PM Justin
- Removed the "From" address.

7/15/2011 1:51:21 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/19/2011 2:46:43 PM Justin
- Resized the font from DO item list.
- Modified the Fax row from Deliver To column to have same row as Tel No.

8/22/2011 4:59:32 PM Justin
- Reduced the font size for header.

10/4/2011 4:59:19 PM Andy
- Reduce the delivery branch and address font.

10/7/2011 2:11:22 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

4/12/2012 1:21:59 PM Andy
- Reduce "from branch" description font size.
- Add "company no" after "from branch" description. 

10/01/2012 2:23 PM Drkoay
- Remove table line
- Remove ARMS code
- Change last page footer sign box
- do_checkout_print_item_per_last_page change to 22

10/8/2012 4:56 PM Justin
- Enhanced the font size of the report to ties up with letter print format.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

3/31/2015 12:04 PM Justin
- Modified to change the label "DO" into "Consignment Note".

4/14/2015 2:43 PM Andy
- Enhanced to show GST ID.

12/28/2015 2:10 PM Qiu Ying
- SKU Additional Description should show in document printing
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

table.tb  {
  border:none;
}

table.tb td {
  border:none;
}

table.tb th {
  border:none;
}
table.tb2 {
    border-collapse: collapse;
    border-right:1px solid #000;
    border-bottom:1px solid #000;
}

body {
	font-size: 12px;
}

.ssmall {
	font-size: 90%;
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="ssmall">
<tr>
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>{else}&nbsp;{/if}</td>
	<td width=100% class="large">
	<h3>{$from_branch.description} {if $from_branch.company_no}<span class="xsmall">({$from_branch.company_no})</span>{/if}</h3>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 <br />GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}CONSIGNMENT NOTE</b></div>
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{*<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr>*}
		{*<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>*}
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px" class="large">
	<tr>		
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
			<h5>Deliver To: {$to_branch.description}</h5>
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}
			{else}
				{$form.address_deliver_to|nl2br}
			{/if}
			<br>
			Tel: <u>{$to_branch.phone_1|default:"-"}</u>{if $to_branch.phone_2} / <u>{$to_branch.phone_2}</u>{/if}
			{if $to_branch.phone_3} &nbsp;&nbsp;&nbsp;&nbsp;Fax: <u>{$to_branch.phone_3}</u>{/if}
			</td>
		{/if}

	</tr>
	</table>
</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb ssmall">

<tr bgcolor=#cccccc class="top_line td_btm_got_line">
	<th rowspan=2 width=5>&nbsp;</th>
	{*<th rowspan=2 nowrap>ARMS Code</th>*}
	<th rowspan=2 nowrap width="110">Article/MCode</th>
	<th rowspan=2 width="90%">SKU Description</th>
	<th rowspan=2 width=40>{*RSP*}Selling<br>(RM)</th>
	<th rowspan=2 width=40>UOM</th>
	<th nowrap colspan=2 width=80>Qty</th>
</tr>

<tr bgcolor=#cccccc class="td_btm_got_line">
	<th nowrap width=40>Ctn</th>
	<th nowrap width=40>Pcs</th>
</tr>
{assign var=counter value=0}

{section name=i loop=$do_items}
<!-- {$counter++} -->
<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align="center" nowrap>
		{if !$page_item_info[i].not_item}
			{$do_items[i].item_no+1}.
		{else}
			&nbsp;
		{/if}
	</td>
	{*<td align="center" nowrap>{$do_items[i].sku_item_code}</td>*}
	<td align="center" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{else}{$do_items[i].mcode|default:'&nbsp;'}{/if}</td>
	<td width="90%"><div {if !$page_item_info[i].no_crop}class="crop"{/if}>{$do_items[i].description}</div></td>
	
	{if !$page_item_info[i].not_item}
		<td align="right">{$do_items[i].selling_price|number_format:2}</td>
		{*
		{if !$config.do_print_hide_cost}
			<td align="right">{$do_items[i].cost_price/$do_items[i].uom_fraction|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		*}
		<td align=center>{$do_items[i].uom_code|default:"EACH"}</td>
		{*}
		{if !$config.do_print_hide_cost}
			<td align="right">
			{if $do_items[i].uom_fraction>1}
				{$do_items[i].cost_price|number_format:$config.global_cost_decimal_points}
			{else}
				-
			{/if}
			</td>
		{/if}
		*}
		<td align="right">{$do_items[i].ctn|qty_nf}</td>
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		
		{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
		{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs|round2}
		{*
		{if !$config.do_print_hide_cost}
		<td align="right">{$total_row|number_format:2}</td>
		{/if}
		*}
		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
  <td>&nbsp;</td>
	<td>&nbsp;</td>
	{*<td>&nbsp;</td>*}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
{*
{if !$config.do_print_hide_cost}
	<th align=right colspan=6 class="total_row">Total</th>
{else}
	<th align=right colspan=4 class="total_row">Total</th>
{/if}
*}
    <th align=right colspan=5 class="total_row">Total</th>
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

{if !$is_lastpage}
<span class="small">
N.B: Buyer must be to check the good at the time of delivery for no compliants will be entertained there - after
<br />NEXT PAGE...
</span>
{/if}

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
<br />
<span class="small">
N.B: Buyer must be to check the good at the time of delivery for no compliants will be entertained there - after
</span>
<br /><br />
{*<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
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
</table>*}
<p class="small">&nbsp;</p>
<table width=100%>
<tr height=80>

<td valign=bottom class="ssmall">
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class="ssmall">
_________________<br>
Approved By<br>
Name:<br>
Date:
</td>

<td valign=bottom class="ssmall">
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>
{/if}
</div>
