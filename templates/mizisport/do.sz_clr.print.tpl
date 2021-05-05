{*
1/20/2012 11:52:43 AM Justin
- Removed some of the CSS that is no longer use.
- Removed the width=100% for main table.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}
<!-- this is the print-out for approved but non-checkout DO -->
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
<div class="printarea nobreak">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
<tr>
	<td>{if !$config.do_print_hide_company_logo}<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">{else}&nbsp;{/if}</td>
	<td width="100%" class="large">
	<h2>{$from_branch.description}</h2>
	{$from_branch.address|nl2br}<br>
	Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
	{if $from_branch.phone_3}
	&nbsp;&nbsp; Fax: {$from_branch.phone_3}
	{/if}
	</td>
	<td rowspan="2" align="right">
	    <table class="xlarge">
		<tr><td colspan="2">
<div style="background:#000;padding:4px;color:#fff" align="center"><b>
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
		<br>
		</td></tr>
		<tr bgcolor="#cccccc" height="22"><td nowrap>DO No.</td><td nowrap>{$form.do_no}</td></tr>
	    <tr height="22"><td nowrap>DO Date</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
		<tr height="22"><td nowrap>PO No.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
		<!--tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr-->
		<tr bgcolor="#cccccc" height="22"><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan=2 class="large">
	<table width="100%" cellspacing="5" cellpadding="0" border="0" height="120px">
	<tr>
		{if !$form.do_branch_id && $form.open_info.name}
			<td valign="top" style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.open_info.name}</b><br>
			{$form.open_info.address}<br>
			</td>
        {elseif $form.do_type eq 'credit_sales'}
		    <td valign="top" style="border:1px solid #000; padding:5px">
			<h4>To</h4>
			<b>{$form.debtor_description}</b><br>
			{$form.debtor_address}<br>
			</td>
		{else}
			<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
			<h5>Deliver To: {$to_branch.description}</h5>
			{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
				{$to_branch.address|nl2br}
			{else}
				{$form.address_deliver_to|nl2br}
			{/if}<br>
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3} &nbsp;&nbsp;&nbsp;&nbsp;Fax: {$to_branch.phone_3}{/if}
			</td>
		{/if}

	</tr>
	</table>
</td>
</tr>
</table>

<br>

{foreach from=$sz_clr_items key=sku_id item=il}
	<h4>{$il.sku_item_code} - {$il.description}</h4>
	<table border="0" cellspacing="0" cellpadding="4" class="tb small">
	<tr>
		<th>&nbsp;</th>
		{foreach from=$il.color key=clr_list item=clr}
			<th>{$clr}</th>
		{/foreach}
		<th>Total</th>
	</tr>
	{foreach from=$il.size key=sz item=clr_list name=sz_area}
		<tr class="td_btm_got_line" align="right">
			<th align="left">{$sz}</th>
			{foreach from=$il.color key=clr item=color name=clr_area}
				<td class="r" nowrap>
					{assign var=have_item value=0}
					{foreach from=$il.uom_list key=uom_code item=code name=uom_area}
						{if $il.list.$sz.$clr.$uom_code.ctn || $il.list.$sz.$clr.$uom_code.pcs}
							{if $have_item}<br />{/if}
							{if $il.list.$sz.$clr.$uom_code.ctn}
								{$il.list.$sz.$clr.$uom_code.ctn|qty_nf} {$uom_code} / 
							{/if}
							{$il.list.$sz.$clr.$uom_code.pcs|qty_nf} PCS
							{assign var=have_item value=1}
						{/if}
					{/foreach}
					{if !$have_item}0 PCS{/if}
				</td>
				{if $smarty.foreach.clr_area.last}
					<td>
						{assign var=have_item value=0}
						{foreach from=$il.uom_list key=uom_code item=code name=uom_area}
							{if $sz_clr_items.$sku_id.$sz.$uom_code.ctn || $sz_clr_items.$sku_id.$sz.$uom_code.pcs}
								{if $have_item}<br />{/if}
								{if $sz_clr_items.$sku_id.$sz.$uom_code.ctn}
									{$sz_clr_items.$sku_id.$sz.$uom_code.ctn|qty_nf} {$uom_code} / 
								{/if}
								{$sz_clr_items.$sku_id.$sz.$uom_code.pcs|qty_nf} PCS
								{assign var=have_item value=1}
							{/if}
						{/foreach}
						{if !$have_item}0 PCS{/if}
					</td>
				{/if}
			{/foreach}
		</tr>
		{if $smarty.foreach.sz_area.last}
			<tr align="right">
				<th align="left">Total</th>
				{foreach from=$il.color key=clr_list item=clr}
					<td>
						{assign var=have_item value=0}
						{foreach from=$il.uom_list key=uom_code item=code name=uom_area}
							{if $sz_clr_items.$sku_id.$clr.$uom_code.ctn || $sz_clr_items.$sku_id.$clr.$uom_code.pcs}
								{if $have_item}<br />{/if}
								{if $sz_clr_items.$sku_id.$clr.$uom_code.ctn}
									{$sz_clr_items.$sku_id.$clr.$uom_code.ctn|qty_nf} {$uom_code} / 
								{/if}
								{$sz_clr_items.$sku_id.$clr.$uom_code.pcs|qty_nf} PCS
								{assign var=have_item value=1}
							{/if}
						{/foreach}
						{if !$have_item}0 PCS{/if}
					</td>
				{/foreach}
				<th>
					{assign var=have_item value=0}
					{foreach from=$il.uom_list key=uom_code item=code name=uom_area}
						{if $il.$uom_code.total.ctn || $il.$uom_code.total.pcs}
							{if $have_item}<br />{/if}
							{if $il.$uom_code.total.ctn}
								{$il.$uom_code.total.ctn|qty_nf} {$uom_code} / 
							{/if}
							{$il.$uom_code.total.pcs|qty_nf} PCS
							{assign var=have_item value=1}
						{/if}
					{/foreach}
					{if !$have_item}0 PCS{/if}
				</th>
			</tr>
		{/if}
	{/foreach}
	</table>
	<br />
{/foreach}
</div>
</body>
