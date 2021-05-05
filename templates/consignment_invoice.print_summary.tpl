{*

4/20/2017 10:05 AM Khausalya
- Enhanced changes from RM to use config settings. 

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}


{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.ci_printing_no_item_line}
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
body{
  font-size:9pt;
}
.tb{
	border-left: none !important;
	font-size: 11pt;
}
.tb th,.tb td{
	border-right: none !important;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.ci_no}';
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
<div class="printarea">
<table width=100% cellspacing=0 cellpadding=0 border=0 style="font-size:11pt;">
<tr>
	<td width=100%>
		<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
		{$from_branch.address|nl2br}<br>
		{if $from_branch.gst_register_no}GST Registration No.: {$from_branch.gst_register_no}<br />{/if}
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
	</td>
	<td rowspan=2 align=right>
		<table class="large">
			<tr><td colspan=2>
				<div style="background:#000;padding:4px;color:#fff" align=center>
					<b>INVOICE SUMMARY</b>
				</div>
			</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Invoice No.</td><td nowrap>{$form.ci_no}</td></tr>
			<tr height=22><td nowrap>Invoice Date</td><td nowrap>{$form.ci_date|date_format:"%d/%m/%Y"}</td></tr>
			<tr height=22><td nowrap>Terms.</td><td nowrap>{$to_branch.con_terms|default:"--"}</td></tr>
			<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|upper|default:'&nbsp;'}</td></tr>
		</table>
	</td>
</tr>
<tr>
	<td>
	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>		
		{if !$form.ci_branch_id && $form.open_info.name}
			<td valign=top style="border:1px solid #000; padding:5px;padding-left:50px;">
			<h4>To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>		
		{else}
			<td valign=top width=50% style="border:1px solid #000; padding:5px;padding-left:10px;">
			<h4>Deliver To</h4>
			<b>{$to_branch.code} - {$to_branch.description}</b><br>
			{$to_branch.address|nl2br}<br>
			{if $to_branch.gst_register_no}GST Registration No.: {$to_branch.gst_register_no}<br />{/if}
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
			</td>
		{/if}

	</tr>
	</table>
</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">
	<th width="50">Price Type</th>
	
    <th nowrap width="40" align="right">Qty</th>
	{if $form.show_per}
		<th width="30">Disc (%)</th>
	{/if}
	
	{if $form.is_under_gst}
		<th width=80>Amount<br>({$config.arms_currency.symbol})</th>
		<th width="80">GST<br />({$config.arms_currency.symbol})</th>	
	{/if}
	
	<th width=80>Amount<br>{if $form.is_under_gst}Incl. GST<br />{/if}({$config.arms_currency.symbol})</th>
	
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		<th width="80">Amount<br />({$to_branch.currency_code})</th>
	{/if}
	
	{if $form.is_under_gst}
		<th width="20">&nbsp;</th>
	{/if}
	{foreach from=$ci_summary.items item=r name=fci}
		<tr class="no_border_bottom">
			<td>{$r.price_type}</td>
			<td align="right">{$r.pcs|qty_nf}</td>

			{if $form.show_per}
				
				<td align="right">{$r.discount|default:'0'}%</td>
				
			{/if}
			
			{if $form.is_under_gst}
				<td align="right">{$r.item_amt|number_format:2}</td>
				<td align="right">{$r.item_gst|number_format:2}</td>
			{/if}
			
			<td align="right">{$r.item_gst_amt|number_format:2}</td>
			
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
				<td align="right">{$r.item_foreign_gst_amt|number_format:3}</td>
			{/if}
			
			{if $form.is_under_gst}
				<td align="right">{$r.indicator_receipt}</td>
			{/if}
			
		</tr>
	{/foreach}
	
	<tr class="total_row no_border_bottom">
		<td align="right" nowrap>Sub Total</td>
		<td align="right">&nbsp;</td>
		
		{if $form.show_per}
			<td>&nbsp;</td>
		{/if}
		
		{if $form.is_under_gst}
			<td align="right">{$form.sub_total_gross_amt|number_format:2}</td>
			<td align="right">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2}</td>
		{/if}
		
		<td align="right">{$form.sub_total_amt|number_format:2}</td>
		
		{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
			<td align="right">{$form.sub_total_foreign_amt|number_format:3}</td>
		{/if}
		
		{if $form.is_under_gst}
			<td>&nbsp;</td>
		{/if}
	</tr>
	
	<tr class="no_border_bottom">
		<td align="right" nowrap>
			Disc ({$form.discount_percent|default:'0'}%)
		</td>
		<td>&nbsp;</td>
		{if $form.show_per}
			<td>&nbsp;</td>
		{/if}
		
		{if $form.is_under_gst}
			<td align="right">{$form.gross_discount_amount|number_format:2}</td> 
			<td align="right">{$form.sheet_gst_discount|number_format:2}</td> 
		{/if}
		
		<td align="right">{$form.discount_amount|number_format:2}</td>
		
		{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
			<td align="right">{$form.foreign_discount_amount|number_format:3}</td>
		{/if}
		
		{if $form.is_under_gst}
			<td>&nbsp;</td>
		{/if}
	</tr>
	
	<tr>
		<td align="right">Total</td>
		<td align=right>{$form.total_pcs|qty_nf}</td>
		{if $form.show_per}
			<td>&nbsp;</td>
		{/if}
		
		{if $form.is_under_gst}
			<td align=right>{$form.total_gross_amt|number_format:2}</td> 
			<td align=right>{$form.total_gst_amt|number_format:2}</td> 
		{/if}	
		
		<td align=right>{$form.total_amount|number_format:2}</td> 
		
		{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
			<td align="right">{$form.total_foreign_amount|number_format:3}</td>
		{/if}
		
		{if $form.is_under_gst}
			<td>&nbsp;</td>
		{/if}
	</tr>
</table>

{if $form.is_under_gst}
	<table width="30%">
		<tr>
			<td valign="top">
				{include file='consignment.gst_summary.tpl'}
			</td>
		</tr>
	</table>
{/if}

</div>