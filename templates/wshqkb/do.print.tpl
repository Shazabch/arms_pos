{*
1/15/2010 9:47:20 AM Andy
- Make description column to occupy as large space as it can

1/19/2010 3:44:59 PM Andy
- Remove RSP Column

1/20/2010 4:17:38 PM Andy
- make top have 4 cm space, reduce space for signature, remove from_branch info, let bottom have some free space around 2cm
- remove logo

1/26/2010 3:36:15 PM Andy
- reduce to branch column space, show debtor tel and fax, also add nl2br for debtor address.  

5/18/2010 1:30:01 PM Andy
- qty & price swap
- don want ctn
- artno add nowrap

5/19/2010 11:23:27 AM Andy
- cash sales DO Printing add sales person name
- credit sales DO Printing add sales person name & debtor term

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 3:27:35 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/7/2011 3:42:59 PM Alex
- add $config.global_cost_decimal_points & qty_nf control cost and qty decimal point 

2/14/2012 6:26:43 PM Justin
- Added to show S/A info and column.

5/9/2012 5:32:43 PM Justin
- Fixed bug of system shows wrong Sales Agent.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/28/2015 1:10 PM Qiu Ying
- SKU Additional Description should show in document printing
*}
<!-- this is the print-out for approved but non-checkout DO -->
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
<div style="height:4cm;">&nbsp;</div>
<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
	 <td valign=top style="border:1px solid #000; padding:5px;width:45%;">
	  {if !$form.do_branch_id && $form.open_info.name}	
  		<h4>To</h4>
  		<b>{$form.open_info.name}</b><br>
  		{$form.open_info.address|nl2br}<br>
    {elseif $form.do_type eq 'credit_sales'}
  		<h4>To</h4>
  		<b>{$form.debtor_description}</b><br>
  		{$form.debtor_address|nl2br}<br>
  		Tel: {$form.p1|default:"-"}{if $form.p2} / {$form.p2}{/if}
  		{if $form.pfax}<br>Fax: {$form.pfax}{/if}
  	{else}
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
  	{/if}
	</td>
	<td valign=top style="border:0px solid #000; padding:5px;width:45%;">
	    {if $form.do_type eq 'open' or $form.do_type eq 'credit_sales'}
			{if $config.masterfile_enable_sa}
				{if $form.mst_sa}
					<h4><b>Sales Agent: </b><br />
						<span style="font-weight:normal;">
							{foreach from=$form.mst_sa name=i key=r item=sa_id}
								{$sa_list.$sa_id.code} - {$sa_list.$sa_id.name}<br />
							{/foreach}
						</span>
					</h4>
				{/if}
			{else}
				<h4><b>Sales Person: </b><span style="font-weight:normal;">{$form.sales_person_name}</span></h4>
			{/if}
	    {/if}
	    {if $form.do_type eq 'credit_sales'}
   			<b>Term: </b>{$form.debtor_term} Days
	    {/if}
	</td>
	<td align=right>
	    <table class="" width="100%">
		<tr><td colspan=2>
<div style="background:#000;padding:4px;color:#fff" align=center><b>
	{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}
DELIVERY ORDER<br>
		{if $is_draft}
			(DRAFT)
		{elseif $is_proforma}
			(Proforma)
		{else}
			(Pre-Checkout)
		{/if}
		</b></div>
		</td></tr>
		<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">
<tr bgcolor=#cccccc>
	<th width=5>&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br>/MCode</th>
	<th width="90%">SKU Description</th>
	{if $config.masterfile_enable_sa && $dtl_have_sa}
		<th nowrap>Sales Agent</th>
	{/if}
	<th nowrap width="80">Qty</th>
	<th>UOM</th>
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
	<td align="center" nowrap>{$do_items[i].sku_item_code}</td>
	<td align="center" nowrap>{if $do_items[i].artno <> ''}{$do_items[i].artno}{else}{$do_items[i].mcode|default:'&nbsp;'}{/if}</td>
	<td width="90%">{if !$page_item_info[i].no_crop}<div class="crop">{/if}{$do_items[i].description}{if !$page_item_info[i].no_crop}</div>{/if}</td>
	
	{if !$page_item_info[i].not_item}
		{if $config.masterfile_enable_sa && $dtl_have_sa}
			<td align="center">
				{if $do_items[i].dtl_sa}
					{foreach from=$do_items[i].dtl_sa name=i key=r item=sa}
						{assign var=sa_id value=$sa.id}
						{$sa_list.$sa_id.code}
					{/foreach}
				{else}
					&nbsp;
				{/if}
			</td>
		{/if}
		<td align="right">{$do_items[i].pcs|qty_nf}</td>
		<td>{$do_items[i].uom_code}</td>
		{assign var=amt_ctn value=$do_items[i].cost_price*$do_items[i].ctn}
		{assign var=amt_pcs value=$do_items[i].cost_price/$do_items[i].uom_fraction*$do_items[i].pcs}
		{assign var=total_row value=$amt_ctn+$amt_pcs}
		{assign var=total_row value=$total_row|round2}
		{assign var=total value=$total+$total_row}
		{assign var=total_ctn value=$do_items[i].ctn+$total_ctn}
		{assign var=total_pcs value=$do_items[i].pcs+$total_pcs}
	{else}
		{if $config.masterfile_enable_sa && $dtl_have_sa}
			<td>&nbsp;</td>
		{/if}
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
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	{if $config.masterfile_enable_sa && $dtl_have_sa}
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row">
	{assign var=colspan value=4}
	{if $config.masterfile_enable_sa && $dtl_have_sa}
		{assign var=colspan value=$colspan+1}
	{/if}
  	<th align=right colspan="{$colspan}">Total</th>
	<th align=right>{$total_pcs|qty_nf}</th>
	<td>&nbsp;</td>
</tr>
{assign var=total value=0}
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}
{/if}

</table>

{if $is_lastpage}
{*<b>Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.remark|default:"-"|nl2br}
</div>*}
<b>Additional Remark</b>
<div style="border:1px solid #000;padding:5px;height:20px;">
{$form.checkout_remark|default:"-"|nl2br}
</div>

{*<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
Date:
</td>

<td valign=bottom class=small>
_________________<br>
Received By<br>
Name:<br>
Date:
</td>
</tr>
</table>*}


<table width=100%>
<tr height=80>

<td valign=bottom class=small>
_________________<br>
Issued By<br>
Name: {$form.owner_fullname}<br>
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

{/if}
<p align=center class=small>** This document is for reference purpose only **</p>  

</div>
