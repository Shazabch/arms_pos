{*
5/20/2015 9:54 AM Andy
- Add consignment invoice summary custom printing template for cmaree.

11:24 AM 5/20/2015 Andy
- Enhanced the style for template.

4/20/2017 10:46 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/22/2017 1:47 PM Justin
- Enhanced company address to use from Masterfile Branch instead of HARDCODED it.

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption base on document's GST status.
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

.header_tbl td{
    border:1px solid black;
	padding:3px;
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
<div style="text-align:center;" class="small">
	<img src="cutemaree/cutemaree.jpg" height="70" /><br />
	<b>(Co No.: {$from_branch.company_no}{if $from_branch.gst_register_no}, GST Registration No.: {$from_branch.gst_register_no}{/if})</b><br />
	{$from_branch.address}<br />
	Tel: {$from_branch.phone_1|default:'-'}, {$from_branch.phone_2|default:'-'} &nbsp;&nbsp;&nbsp; Fax: {$from_branch.phone_3|default:'-'} &nbsp;&nbsp;&nbsp; E-mail: {$from_branch.contact_email}<br />
	<h2>{if $form.is_under_gst}TAX {/if}INVOICE</h2>
</div>

<table class="header_tbl" cellspacing="10px" cellpadding="4" width=100%>
	<tr>
	    <!-- Bill To-->
	    <td width="50%" class="small">
	        	<h3>&nbsp;&nbsp;&nbsp;Bill To</h3>
	        	<b>{$to_branch.description}</b><br>
				{$to_branch.address|nl2br}<br>
				{if $to_branch.gst_register_no}GST Registration No.: {$to_branch.gst_register_no}<br />{/if}
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
	    </td>
	    <td width="30%" border="1" class="small">
	            Date: <b>{$form.ci_date|date_format:"%d/%m/%Y"}</b><br /><br />
	            Department: <b>{$to_branch.con_dept_name|default:'-'}</b><br /><br />
	            Your/Our Purchase<br />
	            Order No: <b>CONSIGNMENT</b>
	    </td>
	    <td width="20%">
	        Invoice No: <b>{$form.ci_no}</b><br />
	        
	        Terms: <b>{$to_branch.con_terms|default:'-'}</b><br />
	        {$page}
	    </td>
	</tr>
</table>
<br>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">
	<tr bgcolor="#cccccc">
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
	</tr>
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
	
	<tr bgcolor="#cccccc">
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

<table width=100%>
	<tr height=80>

	{if $form.is_under_gst}
		<td valign=top style="padding-right:10px;" width="30%">
			{include file='consignment.gst_summary.tpl'}
		</td>
	{/if}
	
	<td valign=bottom class=small>
	_________________<br>
	Issued By<br>
	Name: {$sessioninfo.fullname}<br>
	Date:
	</td>

	<td valign=bottom class=small>
	_________________<br>
	Approved By<br>
	Name:<br>
	Date:
	</td>
	</tr>
</table>
	
</div>