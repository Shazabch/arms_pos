{*
1/20/2012 11:52:43 AM Justin
- Removed some of the CSS that is no longer use.
- Removed the width=100% for main table.
*}

{config_load file="site.conf"}
{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.po_printing_no_item_line}
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
td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
{/literal}
</style>

<body onload="window.print()">
{/if}
<!-- loop for each sheets -->
<div class="printarea nobreak">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='po'}" height="80" hspace="5" vspace="5" /></td>
	<td width=100%>
	<h2>{$billto.description}</h2>
	{$billto.address|nl2br}<br>
	Tel: {$billto.phone_1}{if $billto.phone_2} / {$billto.phone_2}{/if}
	&nbsp;&nbsp; Fax: {$billto.phone_3}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Purchase Order</b></div>
{if $form.status==0}
<div class="xsmall">This draft PO is for internal use only, not valid for any purchase use.</div>
{elseif !$form.approved}
<div class="xsmall">This Proforma PO is not valid for delivery, official PO will be issued to supersede this document after approval.</div>
<!--div class="xsmall">This Proforma PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div-->
{/if}
		<br>
		</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>PO No.</td><td nowrap>{$form.po_no}</td></tr>
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}
		
		<tr bgcolor="#eeeeee"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>
		{if !$config.po_set_max_items}
		<tr bgcolor="#eeeeee"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table cellspacing=5 cellpadding=0 border=0>
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Vendor</h4>
		<b>{$vendor.description}</b><br>
		{$vendor.address|nl2br}<br>
		Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
		{if $vendor.phone_3}<br>Fax: {$vendor.phone_3}{/if}
		</td>

		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Deliver To</h4>
		<b>{$deliver.description}</b><br>
		{$deliver.address|nl2br}<br>
		Tel: {$deliver.phone_1|default:"-"}{if $deliver.phone_2} / {$deliver.phone_2}{/if}
		{if $deliver.phone_3}<br>Fax: {$deliver.phone_3}{/if}
		</td>

	</tr>
	</table>
</td>
</tr>
</table>

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
{if $print.branch_copy}
	<div class="printarea nobreak">
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
<tr>
	<td><img src="{get_logo_url mod='po'}" height="80" hspace="5" vspace="5" /></td>
	<td width=100%>
	<h2>{$billto.description}</h2>
	{$billto.address|nl2br}<br>
	Tel: {$billto.phone_1}{if $billto.phone_2} / {$billto.phone_2}{/if}
	&nbsp;&nbsp; Fax: {$billto.phone_3}
	</td>
	<td rowspan=2 align=right>
	    <table class="xlarge">
		<tr><td colspan=2>
<div style="background:#000;padding:2px;color:#fff" align=center><b>{if $form.status==0}Draft{elseif !$form.approved}Proforma{/if} Purchase Order</b></div>
{if $form.status==0}
<div class="xsmall">This draft PO is for internal use only, not valid for any purchase use.</div>
{elseif !$form.approved}
<div class="xsmall">This Proforma PO is not valid for delivery, official PO will be issued to supersede this document after approval.</div>
<!--div class="xsmall">This Proforma PO is waiting for approval, no delivery will be accepted until this PO is fully approved by the authority.</div-->
{/if}
		<br>
		</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>PO No.</td><td nowrap>{$form.po_no}</td></tr>
		{if $config.po_show_terms}<tr><td nowrap>Payment Terms</td><td nowrap>{$form.term|default:"-"} Days</td></tr>{/if}
	    <tr><td nowrap>Department</td><td nowrap>{$form.department}</td></tr>
		<tr><td nowrap>Ordered By</td><td nowrap>{$form.fullname}</td></tr>
		<tr><td nowrap>PO Date</td><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>
		
		<!--add vendor payment terms-->
		{if $form.payment_term}
		<tr>
		<td nowrap>Payment Term</td>
		<td nowrap>{$form.payment_term}</td>
		</tr>	
		{/if}
		
		<tr bgcolor="#eeeeee"><td nowrap>Delivery Date</td><td nowrap>{$form.delivery_date}</td></tr>
		<tr bgcolor="#eeeeee"><td nowrap>Cancellation Date</td><td nowrap>{$form.cancel_date}</td></tr>
		{if !$config.po_set_max_items}
		<tr bgcolor="#eeeeee"><td nowrap>Page</td><td nowrap>{$page}</td></tr>
		{/if}
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	<table cellspacing=5 cellpadding=0 border=0>
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Vendor</h4>
		<b>{$vendor.description}</b><br>
		{$vendor.address|nl2br}<br>
		Tel: {$vendor.phone_1|default:"-"}{if $vendor.phone_2} / {$vendor.phone_2}{/if}
		{if $vendor.phone_3}<br>Fax: {$vendor.phone_3}{/if}
		</td>

		<td valign=top width=50% style="border:1px solid #000; padding:5px">
		<h4>Deliver To</h4>
		<b>{$deliver.description}</b><br>
		{$deliver.address|nl2br}<br>
		Tel: {$deliver.phone_1|default:"-"}{if $deliver.phone_2} / {$deliver.phone_2}{/if}
		{if $deliver.phone_3}<br>Fax: {$deliver.phone_3}{/if}
		</td>

	</tr>
	</table>
</td>
</tr>
</table>

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
	<br />
	<div align="right"><h1>Internal Copy</h1></div>
</div>
{/if}
</body>