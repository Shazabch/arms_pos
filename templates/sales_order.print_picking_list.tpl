{*
10/10/2019 9:49 AM William
- Add new print option "Picking List" for sales order.
*}

{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.sales_order_printing_no_item_line}
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
</style>

<script type="text/javascript">
var doc_no = '{$form.order_no}';
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
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
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
					SALES ORDER<br>
					(Picking List)
					</b></div>
					<br>
				</td>
			</tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Order No.</td><td nowrap>{$form.order_no}</td></tr>
			<tr height=22><td nowrap>Order Date</td><td nowrap>{$form.order_date|date_format:$config.dat_format}</td></tr>
			<tr height=22><td nowrap>Order No.</td><td nowrap>{$form.order_no|default:"--"}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
		</table>
	</td>
	</tr>
	<tr>
		<td colspan="2">
		<table width="100%" cellspacing=5 cellpadding=0 border=0 height="120px">
			<tr>
				<td valign="top" width=50% style="border:1px solid #000; padding:5px">
					<h4>Bill to Address</h4>
					<b>{$debtor[$form.debtor_id].description}</b><br>
					{$debtor[$form.debtor_id].address|nl2br}<br>
					Tel: {$debtor[$form.debtor_id].phone_1|default:'-'}<br>
					Terms: {$debtor[$form.debtor_id].term|default:'-'}<br>
				</td>

				<td valign=top style="border:1px solid #000; padding:5px">
					<h4>Deliver to Address</h4>
					{if $debtor[$form.debtor_id].delivery_address}
						<b>{$debtor[$form.debtor_id].description}</b><br>
						{$debtor[$form.debtor_id].delivery_address|nl2br}<br>
						Tel: {$debtor[$form.debtor_id].phone_1|default:'-'}<br>
						Terms: {$debtor[$form.debtor_id].term|default:'-'}
					{else}
						<b>{$debtor[$form.debtor_id].description}</b><br>
						{$debtor[$form.debtor_id].address|nl2br}<br>
						Tel: {$debtor[$form.debtor_id].phone_1|default:'-'}<br>
						Terms: {$debtor[$form.debtor_id].term|default:'-'}
					{/if}
				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>

<br>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">
	<tr bgcolor="#cccccc">
		<th rowspan="2" width="5">&nbsp;</th>
		<th rowspan="2" nowrap>ARMS Code</th>
		<th rowspan="2" nowrap>Article<br>/MCode</th>
		<th rowspan="2" nowrap>{$config.link_code_name}</th>
		<th rowspan="2" width="90%">SKU Description</th>
		<th rowspan="2" width="40">Location</th>
		<th rowspan="2" width="40">UOM</th>
		<th colspan="2" nowrap width="80">Order Qty</th>
		<th rowspan="2" nowrap width="40">Qty</th>
	</tr>

	<tr bgcolor="#cccccc">
		<th nowrap width="40">Ctn</th>
		<th nowrap width="40">Pcs</th>
	</tr>
	
	{assign var=counter value=0}
	{foreach from=$items key=item_index item=r name=i}
	<!-- {$counter++} -->
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
				{if $r.artno}
					{$r.artno|default:'&nbsp;'}
				{else}
					{$r.mcode|default:'&nbsp;'}
				{/if}
			</td>
			<td align="center" nowrap>{$r.link_code|default:'&nbsp;'}</td>
			<td width="90%"><div class="crop">{$r.sku_description|default:'&nbsp;'}</div></td>
			
			
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
	
	{assign var=s2 value=$counter}
	{section name=s start=0 loop=$extra_empty_row}
	{assign var=s2 value=$s2+1}
		<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
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
				Name: {$sessioninfo.fullname}<br>
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