{*
2017-09-07 14:07 PM Qiu Ying
- Enhanced to have default DO Size & Color Print Template

10/20/2017 2:21 PM Andy
- Fixed pre-checkout checking.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

12/28/2018 10:25 AM Justin
- Enhanced to print barcode on top of the document number.

1/16/2019 9:11 AM Justin
- Bug fixed on the slash "/" from barcode will causing the wrong result from scanner.

6/19/2019 10:55 AM William
- Added new Vertical print format.
*}
<!-- this is the print-out for approved but non-checkout DO -->
{config_load file="site.conf"}
{if !$skip_header}
{include file='header.print.tpl'}

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
{if $err}
	{$err}
{else}
	<div class="printarea nobreak">
	<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
	<tr>
{if $system_settings.logo_vertical eq 1}
		<td colspan="2"  width="100%">
		<table width="100%" style="text-align: center;">
			<tr>
			{if !$config.do_print_hide_company_logo}
				<td><img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5" style="max-width: 600px;max-height: 80px;"></td>
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
			<tr><td colspan=2>
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
			</b></div>
			<br>
			</td></tr>
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
			<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
			</table>
		</td>
	</tr>
	<br>
	<tr>
		<td colspan="2" width=100%>
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
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
				Tel: {$form.debtor_phone|default:'-'}<br>
				Terms: {$form.debtor_term|default:'-'}<br>
				</td>
			{else}
				<td valign=top width=100% style="border:1px solid #000; padding:5px">
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
	{assign var=max_size_count value=0}
	{assign var=col_type value="color"}
	{assign var=row_type value="size"}
	
	{if $config.enable_one_color_matrix_ibt}
		{assign var=col_type value="size"}
		{assign var=row_type value="color"}
	{/if}
	
	{foreach from=$sz_clr_items key=sku_id item=il name=sku_item_list}
		{if $max_size_count < $il.$col_type|@count}
			{assign var=max_size_count value=$il.$col_type|@count}
		{/if}
	{/foreach}
	
	<table width="100%" border="0" cellspacing="0" cellpadding="4" class="tb small">
		<tr>
			<th width="5">No.</th>
			<th nowrap>Article No/MCode</th>
			<th width="70%">Description</th>
			<th nowrap colspan="{$max_size_count+1}">{if $config.enable_one_color_matrix_ibt}Color/Size{else}Size/Color{/if}</th>
			<th nowrap>Total Pcs</th>
		</tr>
		{foreach from=$sz_clr_items key=sku_id item=il name=sku_item_list}
			{assign var=color_count value=$il.$row_type|@count}
			<tr>
				<td rowspan="{$color_count+1}">{$il.item_no}.</td>
				<td rowspan="{$color_count+1}">{if $il.artno}{$il.artno}{else}{$il.mcode}{/if}</td>
				<td rowspan="{$color_count+1}">{$il.description}</td>
				<td>&nbsp;</td>
				{foreach from=$il.$col_type key=sz_list item=sz}
					<td align="center">{$sz}</td>
				{/foreach}
				{if $max_size_count > $il.$col_type|@count}
					{assign var=tmp_size value=$il.$col_type|@count}
					{section name=sec start=0 loop=$max_size_count-$tmp_size step=1}
						<td>&nbsp;</td>
					{/section}
				{/if}
				
				<td rowspan="{$color_count+1}" align="right">{$il.total_pcs|qty_nf}</td>
			</tr>
			{foreach from=$il.$row_type key=clr_list item=clr name=clr_area}
				<tr>
					<td align="center">{$clr}</td>
					{foreach from=$il.$col_type key=sz item=size name=sz_area}
						<td nowrap align="right">
							{foreach from=$il.uom_list key=uom_code item=code name=uom_area}
								{if $il.list.$sz.$clr.$uom_code.ctn || $il.list.$sz.$clr.$uom_code.pcs}
									{$il.list.$sz.$clr.$uom_code.ctn_pcs|qty_nf}
								{/if}
							{/foreach}
						</td>
						{if $smarty.foreach.sz_area.last}
							{if $max_size_count > $il.$col_type|@count}
								{section name=sec start=0 loop=$max_size_count-$tmp_size step=1}
									<td>&nbsp;</td>
								{/section}
							{/if}
						{/if}
					{/foreach}
				</tr>
			{/foreach}
		{/foreach}
	</table>
	</div>
{/if}
</body>