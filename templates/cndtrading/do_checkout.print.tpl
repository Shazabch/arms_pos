{*

*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
	<style>
	{if $config.do_printing_no_item_line}
		{literal}
		.no_border_bottom td{ border-bottom:none !important; }
		.total_row td, .total_row th{ border-top: 1px solid #000; }
		.td_btm_got_line td,.td_btm_got_line th{ border-bottom:1px solid black !important; }
		{/literal}
	{/if}

	{literal}
	.hd { background-color:#ddd; }
	.rw { background-color:#fff; }
	.rw2{ background-color:#eee; }
	.ft { background-color:#eee; }
	table.tb2 {
	    border-collapse: collapse;
	    border-right:1px solid #000;
	    border-bottom:1px solid #000;
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
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
	<tr>
		<td>
		{if !$config.do_print_hide_company_logo}
			<img src="{get_logo_url mod='do'}" height="80" hspace="5" vspace="5">
		{else}
			&nbsp;
		{/if}
		</td>
		<td width=100%>
			<h2>{$from_branch.description}</h2>
			{$from_branch.address|nl2br}<br>
			Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}
				&nbsp;&nbsp; Fax: {$from_branch.phone_3}
			{/if}
			{if $config.enable_gst and $from_branch.gst_register_no}
				 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
			{/if}
		</td>
		<td rowspan=2 align=right>
		    <table class="xlarge">
				<tr><td colspan=2>
					<div style="background:#000;padding:4px;color:#fff" align=center><b>
					{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}DELIVERY ORDER</b></div>
					<br>
				</td></tr>
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
		<td colspan=2>
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
						<b>{$to_branch.description}</b><br>
						{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
							{$to_branch.address|nl2br}
						{else}
							{$form.address_deliver_to|nl2br}
						{/if}<br>
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
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">
	<tr bgcolor=#cccccc class="top_line">
		<th rowspan=2 width=5>&nbsp;</th>
		<th rowspan=2 nowrap>Article<br>/MCode</th>
		<th rowspan=2 width="90%">SKU Description</th>
		{if !$hide_RSP}<th rowspan=2 width=40>RSP<br>(RM)</th>{/if}
		<th rowspan=2 width=40>UOM</th>
		<th nowrap colspan=2 width=80>Qty</th>
	</tr>
	<tr bgcolor=#cccccc>
		<th nowrap width=40>Ctn</th>
		<th nowrap width=40>Pcs</th>
	</tr>
	{assign var=counter value=0}

	{foreach from=$do_items key=item_index item=r name=i}
		<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}" height=30>
			<td align=center>
				{if !$page_item_info.$item_index.not_item}
					{$r.item_no+1}.
				{else}
					&nbsp;
				{/if}
			</td>
			<td align="center" nowrap>{if !$page_item_info.$item_index.not_item}{$r.artno|default:'-'}<br>{$r.mcode|default:'-'}{/if}</td>
			<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
	
			{if !$page_item_info.$item_index.not_item}
				{if !$hide_RSP}<td align="right">{$r.selling_price|number_format:2}</td>{/if}
				<td align=center>{$r.uom_code|default:"EACH"}</td>
				<td align="right">{$r.ctn|qty_nf}</td>
				<td align="right">{$r.pcs|qty_nf}</td>
			
				{assign var=amt_ctn value=$r.cost_price*$r.ctn}
				{assign var=amt_pcs value=$r.cost_price/$r.uom_fraction*$r.pcs}
				{assign var=total_row value=$amt_ctn+$amt_pcs|round2}
				{assign var=total_row value=$total_row|round2}
				{assign var=total value=$total+$total_row}
				{assign var=total_ctn value=$r.ctn+$total_ctn}
				{assign var=total_pcs value=$r.pcs+$total_pcs}
			{else}
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			{/if}
		</tr>
	{/foreach}

	{section name=s start=0 loop=$extra_empty_row}
		<tr height=30 class="no_border_bottom {if $smarty.section.s.iteration eq $extra_empty_row and !$is_lastpage}td_btm_got_line{/if}">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{if !$hide_RSP}<td>&nbsp;</td>{/if}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	{/section}

	{if $is_lastpage}
		<tr class="total_row">
			<th align=right colspan={if !$hide_RSP}5{else}4{/if} class="total_row">Total</th>
			<th align=right class="total_row">{$total_ctn|qty_nf}</th>
			<th align=right class="total_row">{$total_pcs|qty_nf}</th>
		</tr>
		{assign var=total value=0}
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
	<b>Additional Remark</b>
	<div style="border:1px solid #000;padding:5px;height:20px;">
		{$form.checkout_remark|default:"-"|nl2br}
	</div>
	<br>
	<table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
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
	</table>
{/if}
</div>