{*
3/13/2019 3:31 PM Andy
- Added "Print DO (Group by same Color)".

6/18/2019 2:35 PM William
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
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
<tr>
{if $system_settings.logo_vertical eq 1}
	<td colspan="2" width="100%">
		<table width="100%" style="text-align: center;">
			<tr>
			{if !$config.do_print_hide_company_logo}
				{if $alt_logo_img}
				<td><img src={$alt_logo_img} height=80 hspace=5 vspace=5></td>
				{else}
				<td><img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5></td>
				{/if}
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
			{if $form.is_under_gst && $form.checkout}
			<tr>
				<td><h1 align="center">Tax Invoice</h1></td>
			</tr>
			{/if}
		</table>
	</td>
{else}
	<td>
		{if !$config.do_print_hide_company_logo}
			{if $alt_logo_img}
			<img src={$alt_logo_img} height=80 hspace=5 vspace=5>
			{else}
			<img src="{get_logo_url mod='do'}" height=80 hspace=5 vspace=5>
			{/if}
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
		
		
		{if $form.is_under_gst && $form.checkout}
			<h1 align="center">Tax Invoice</h1>
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
					{if $is_draft}
						(DRAFT)
					{elseif $is_proforma}
						(Proforma)
					{elseif !$form.checkout}
						(Pre-Checkout)
					{/if}
					</b>
				</div><br />
			</td>
		</tr>
		
	    <tr bgcolor="#cccccc" height=22>
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
			<tr height=22><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		{/if}
		<tr height=22><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		{if $form.offline_id}
			<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
		{/if}
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:"%d/%m/%Y"}</td></tr-->
		<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
		<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</tr>
	  	</table>
	</td>
</tr>
<br>
<tr>
<td colspan="2">
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
						{$to_branch.address|nl2br}
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

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="small">
<tr bgcolor="#cccccc" class="total_row td_btm_got_line">
	<th rowspan="2" width="5">&nbsp;</th>
	<th rowspan="2" nowrap>ARMS Code</th>
	<th rowspan="2" nowrap>Article<br>/MCode</th>
	<th rowspan="2" nowrap>Color</th>
	<th rowspan="2" width="90%">SKU Description</th>
	<th rowspan="2" width="40">UOM</th>
	<th nowrap colspan="2" width=80>Qty</th>
</tr>

<tr bgcolor="#cccccc" class="td_btm_got_line">
	<th nowrap width="40">Ctn</th>
	<th nowrap width="40">Pcs</th>
</tr>

{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td align=center>
			{if !$page_item_info.$item_index.not_item}
				{$r.item_no+1}.
			{else}
				&nbsp;
			{/if}
		</td>
		<td align="center">{$r.parent_info.sku_item_code|default:'&nbsp;'}</td>
		<td align="center" nowrap>
			{if $r.oi}	
				{$r.artno_mcode|default:'&nbsp;'}
			{else}
				{if $r.parent_info.artno}{$r.parent_info.artno|default:'&nbsp;'}{else}{$r.parent_info.mcode|default:'&nbsp;'}{/if}
			{/if}
		</td>
		<td align="center" nowrap>{$r.color|default:'N/A'}</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.parent_info.description}</div></td>
		
		
		{if !$page_item_info.$item_index.not_item}
			<td align=center>{$r.uom_code|default:"EACH"}</td>
			<td align="right">{$r.ctn|qty_nf}</td>
			<td align="right">{$r.pcs|qty_nf}</td>
			
			{assign var=total_ctn value=$r.ctn+$total_ctn}
			{assign var=total_pcs value=$r.pcs+$total_pcs}
			{assign var=total_qty value=$total_qty+$r.ctn*$r.uom_fraction+$r.pcs}
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	</tr>
	
	{* Size and Color *}
	{if !$page_item_info.$item_index.not_item}
		<tr>
			<td>&nbsp;</td>
			{assign var=colspan value=6}
			{if $form.is_under_gst}
				{assign var=colspan value=$colspan+3}
			{/if}
			<td colspan="{$colspan}">
				<table>
					{* Size *}
					<tr>
						{foreach from=$r.size_list key=item_size item=item_ctn_pcs}
							<td style="padding-right:10px;">{$item_size|default:'N/A'}</td>
						{/foreach}
					</tr>
					{* Qty *}
					<tr>
						{foreach from=$r.size_list key=item_size item=item_ctn_pcs}
							<td>
								{if $item_ctn_pcs.ctn}
									{$item_ctn_pcs.ctn|qty_nf}:
								{/if}
								{$item_ctn_pcs.pcs|qty_nf}
							</td>
						{/foreach}
					</tr>
				</table>
			</td>
		</tr>
	{/if}
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
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	{assign var=colspan value=7}
	{if $form.is_under_gst}
		{assign var=colspan value=$colspan+3}
	{/if}
	<td colspan="{$colspan}">
		<table>
			{* Size *}
			<tr>
				<td>&nbsp;</td>
			</tr>
			{* Qty *}
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
{/section}

{if $is_lastpage}

		
	{* Total *}
	<tr class="total_row">
		{assign var=cols value=6}
		
		<th align="right" colspan="{$cols}" class="total_row">Total</th>
		<th align="right" class="total_row">{$total_ctn|qty_nf}</th>
		<th align="right" class="total_row">{$total_pcs|qty_nf}</th>
	</tr>
		
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</table>

</div>
