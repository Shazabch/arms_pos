{*
7/19/2017 10:28 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items

10/27/2017 1:35 PM Justin
- Enhanced to have location column.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

11/12/2018 11:21 AM Andy
- Add column "Old Code".

6/18/2019 5:35 PM William
- Added new Vertical print format.
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
	<tr>
	{if $system_settings.logo_vertical eq 1}
		<td colspan="2" width="100%">
			<table width="100%" style="text-align: center;">
				<tr>
				{if !$config.do_print_hide_company_logo}
					<td><img src="{get_logo_url mod='do'}" style="max-width: 600px;max-height: 80px;" height="80" hspace="5" vspace="5"></td>
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
		<td rowspan=2 align=right>
			<table class="xlarge">
				<tr>
					<td colspan=2>
						<div style="background:#000;padding:4px;color:#fff" align=center><b>
						{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}
						DELIVERY ORDER<br>
						(Picking List)
						</b></div>
						<br>
					</td>
				</tr>
				<tr bgcolor="#cccccc" height=22><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
				{if !$config.do_printing_allow_hide_date or !$no_show_date}
					<tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
				{/if}
				<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
				{if $form.offline_id}
					<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
				{/if}
				<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
				<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
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
				Tel: {$form.debtor_phone|default:'-'}<br>
				Terms: {$form.debtor_term|default:'-'}<br>
				</td>
			{else}
				<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>Deliver To</h4>
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

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">
	<tr bgcolor="#cccccc">
		<th rowspan="2" width="5">&nbsp;</th>
		<th rowspan="2" nowrap>ARMS Code</th>
		<th rowspan="2" nowrap>Article<br>/MCode</th>
		<th rowspan="2" nowrap>{$config.link_code_name}</th>
		<th rowspan="2" width="90%">SKU Description</th>
		<th rowspan="2" width="40">Location</th>
		<th rowspan="2" width="40">UOM</th>
		<th colspan="2" nowrap width="80">DO Qty</th>
		<th rowspan="2" nowrap width="40">Qty</th>
	</tr>

	<tr bgcolor="#cccccc">
		<th nowrap width="40">Ctn</th>
		<th nowrap width="40">Pcs</th>
	</tr>
	{assign var=counter value=0}

	{foreach from=$do_items key=item_index item=r name=i}
		<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
			<td align="center" nowrap>
				
				{if !$page_item_info.$item_index.not_item}
					{$r.item_no+1}.
				{else}
					&nbsp;
				{/if}
			
			</td>
			<td align="center" nowrap>{$r.sku_item_code|default:'&nbsp;'}</td>
			
			<td align="center" nowrap>
				{if $r.oi}
					{$r.artno_mcode|default:'&nbsp;'}
				{else}
					{if $r.artno}{$r.artno|default:'&nbsp;'}{else}{$r.mcode|default:'&nbsp;'}{/if}
				{/if}
			</td>
			<td align="center" nowrap>{$r.link_code|default:'&nbsp;'}</td>
			<td width="90%">{if !$page_item_info.$item_index.no_crop}<div class="crop">{/if}{$r.description|default:'&nbsp;'}{if !$page_item_info.$item_index.no_crop}</div>{/if}</td>
			
			
			{if !$page_item_info.$item_index.not_item}
				<td align="center">{$r.location}</td>	
				<td align="center">{$r.uom_code|default:"EACH"}</td>	
				<td align="right">{$r.ctn|qty_nf}</td>
				<td align="right">{$r.pcs|qty_nf}</td>
				<td align="right">&nbsp;</td>

				{assign var=total_ctn value=$r.ctn+$total_ctn}
				{assign var=total_pcs value=$r.pcs+$total_pcs}
			{else}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		</tr>
	{/foreach}

	{section name=s start=0 loop=$extra_empty_row}
		<tr height=20 class="no_border_bottom {if $smarty.section.s.iteration eq $extra_empty_row and !$is_lastpage}td_btm_got_line{/if}">
			<td>&nbsp;</td>
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
	{/section}

	{if $is_lastpage}
		<tr class="total_row">
			<th align="right" colspan="7">Total</th>
			<th align="right">{$total_ctn|qty_nf}</th>
			<th align="right">{$total_pcs|qty_nf}</th>
			<th>&nbsp;</th>
		</tr>
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

	<table width="100%">
		<tr height="80">
			<td valign="bottom" class="small">
				_________________<br>
				Issued By<br>
				Name: {$form.owner_fullname}<br>
				Date:
			</td>

			<td valign="bottom" class="small">
				_________________<br>
				Received By<br>
				Name:<br>
				Date:
			</td>
		</tr>
	</table>
{/if}
<p align="center" class="small">** This document is for reference purpose only **</p>  

</div>

