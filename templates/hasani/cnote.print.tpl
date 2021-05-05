{if !$skip_header}
{include file='header.print.tpl'}

<style>

</style>

<script type="text/javascript">
var doc_no = '#{$form.id|string_format:"%05d"}';
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
<table width="100%" cellpadding="4" cellspacing="0" border="0">
	<tr>
		<td valign="top" align="center" class="xlarge" colspan="4" nowrap><h4>CREDIT NOTE</td>
	</tr>
	<tr>
		<td width="40%" valign="top" style="border:1px solid #000; padding:5px;">
			<h4>From</h4>
			<h4>{$branch.description}</h4>
			{$branch.address|nl2br}
			<br />
			GST	ID No.: {$branch.gst_register_no}
			<br />
			Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}
		</td>
		<td>&nbsp;</td>
		<td width="40%" valign="top" style="border:1px solid #000; padding:5px">
			<h4>Bill To</h4>
			<b>{$form.cust_name}</b><br>
			{$form.cust_address}<br>
			{if $form.cust_brn}
				BRN: {$form.cust_brn}
			{/if}
			<br />
		</td>
		<td align="right">
			<table class="xlarge">
				<tr height="22"><td nowrap>C/N No.:</td><td nowrap>{$form.cn_no}</td></tr>
				<tr height="22"><td nowrap>C/N Date:</td><td nowrap>{$form.cn_date}</td></tr>
				{if $form.return_type neq 'multiple_inv'}
					<tr height="22"><td nowrap>Invoice No.:</td><td nowrap>{$form.inv_no}</td></tr>
					<tr height="22"><td nowrap>Invoice Date:</td><td nowrap>{$form.inv_date}</td></tr>
				{/if}
				<tr bgcolor="#cccccc" height="22"><td colspan="2" align="center">{$page}</td></tr>
			</table>
		</td>
	</tr>
</table>

<br>
<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">
	<tr bgcolor="#cccccc">
		<th rowspan="2" width="20">No.</th>
		<th rowspan="2" width="50">MCode</th>
		<th rowspan="2" width="40%">Description</th>
		{if $form.return_type eq 'multiple_inv'}
			<th rowspan="2" width="40">Invoice No.</th>
			<th rowspan="2" width="40">Invoice Date</th>
		{/if}
		<th rowspan="2" width="20">UOM</th>
		<th rowspan="2" width="80">Price<br />({$config.arms_currency.symbol})</th>
		<th rowspan="2" width="80">Reason</th>
		<th colspan="2">Quantity</th>
		{if $form.is_under_gst}
			<th rowspan="2" width="80">Tax Rate<br />(%)</th>
			<th rowspan="2" width="80">Gross Amt</th>
			<th rowspan="2" width="80">GST Amt</th>
		{/if}
		<th rowspan="2" width="80">Total {if $form.is_under_gst}Incl GST{/if}<br />({$config.arms_currency.symbol})</th>
	</tr>
	<tr bgcolor="#cccccc">
		<th width="20">CTN</th>
		<th width="20">PCS</th>
	</tr>
	{assign var=t_page value=0}
	
	{foreach from=$items name=i item=r key=item_index}
		<!--{$t_page++}-->
		<tr  id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}" class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}" height="30">
			<td align=right>
				<!--{$line_no++}-->
				{if !$page_item_info.$item_index.not_item}
					{$r.item_no+1}.
				{else}
					&nbsp;
				{/if}
			</td>
			{if !$page_item_info.$item_index.not_item}<td>{$r.mcode}</td>{else}<td>&nbsp;</td>{/if}
			<td><div class="crop">{$r.description}</div></td>
			{if !$page_item_info.$item_index.not_item}
				{if $form.return_type eq 'multiple_inv'}
					<td>{$r.return_inv_no}</td>
					<td>{$r.return_inv_date}</td>
				{/if}
				<td>{$r.uom_code}</td>
				<td align="right">{if $form.is_under_gst}{$r.display_price|number_format:2}{else}{$r.price|number_format:2}{/if}</td>
				<td nowrap>{$r.reason|ucwords|default:"&nbsp;"}</td>
				<td align="right">{$r.ctn|ifzero:'&nbsp;'}</td>
				<td align="right">{$r.pcs}</td>
							
				{if $form.is_under_gst}
					<td align="right" nowrap>{$r.gst_code} @ {$r.gst_rate|number_format:2}</td>
					<td align="right">{$r.line_gross_amt|number_format:2}</td>
					<td align="right">{$r.line_gst_amt|number_format:2}</td>
				{/if}
				
				<td align="right">{$r.line_amt|number_format:2}</td>
			{else}
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{if $form.return_type eq 'multiple_inv'}
					<td>&nbsp;</td><td>&nbsp;</td>
				{/if}
				{if $form.is_under_gst}
					<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
				{/if}
				<td>&nbsp;</td>
			{/if}
		</tr>
	{/foreach}

	{repeat s=$t_page+1 e=$PAGE_SIZE name=rr}
	<tr height="30" class="no_border_bottom">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		{if $form.return_type eq 'multiple_inv'}
			<td>&nbsp;</td><td>&nbsp;</td>
		{/if}
		{if $form.is_under_gst}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		<td>&nbsp;</td>
	</tr>
	{/repeat}

	{if $is_last_page}
		{assign var=colspan value=8}
		
		{if $form.return_type eq 'multiple_inv'}
			{assign var=colspan value=$colspan+2}
		{/if}
		
		{if $form.is_under_gst}
			{assign var=colspan value=$colspan+1}
		{/if}

		{if $form.discount_amt gt 0}
		<tr>
			<td colspan="{$colspan}" rowspan="{$rowspan}" align="right"><b>Subtotal</b></td>
			{if $form.is_under_gst}
				<td align="right" class="total_row">{$form.sub_total_gross_amount|number_format:2}</td>
				<td align="right" class="total_row">{$form.sub_total_gst_amount|number_format:2}</td>
			{/if}

			<td align="right" class="total_row">{$form.sub_total_amount|number_format:2}</td>
		</tr>

		<tr>
			<td colspan="{$colspan}" rowspan="{$rowspan}" align="right"><b>Discount</b></td>
			{if $form.is_under_gst}
				<td align="right" class="total_row">{$form.gross_discount_amt|number_format:2}</td>
				<td align="right" class="total_row">{$form.gst_discount_amt|number_format:2}</td>
			{/if}

			<td align="right" class="total_row">{$form.discount_amt|number_format:2}</td>
		</tr>
		{/if}

		<tr>
			<td colspan="{$colspan}" rowspan="{$rowspan}" align="right"><b>Total</b></td>
			{if $form.is_under_gst}
				<td align="right" class="total_row">{$form.total_gross_amount|number_format:2}</td>
				<td align="right" class="total_row">{$form.total_gst_amount|number_format:2}</td>
			{/if}
			
			<td align="right" class="total_row">{$form.total_amount|number_format:2}</td>
		</tr>
	{/if}
</table>

{if $is_last_page}
	<div style="border:1px solid #000;">
		<b>Remark:</b><br />
		{$form.remark|default:'-'|nl2br}

		<br />
		<b>Adjustment Docs: </b>
		{foreach from=$form.adj_id_list item=adj_id name=fadj}
			{if !$smarty.foreach.fadj.first}, {/if}
			{$branch.report_prefix}{$adj_id|string_format:"%05d"}
		{/foreach}
		
	</div>
	
	<div width="100%">
		<div id="left_content" style="float:left; width:50%" align="center">
			<table>
			  <tr>
				  <td>
					  <br /><br /><br />
					  ____________________<br />
					  <b>Issued By</b><br />
					  <b>Name:</b>
				  </td>
			  </tr>
			</table>
		</div>
		
		<div id="right_content" style="float:left; width:50%" align="center">
			<table>
			  <tr>
				  <td>
					  <br /><br /><br />
					  ____________________<br />
					  <b>Authorised By</b><br />
					  <b>Name:</b>
				  </td>
			  </tr>
			</table>
		</div>
	</div>
	{*<div width="100%">
		<div id="left_content" style="float:left; width:50%">
			<table width="100%">
				<tr align="center">
					<td valign="bottom">
						<br /><br /><br />
						_________________<br />
						Issued By<br />
						<b>Name:</b>
					</td>
				</tr>
			</table>
		</div>
		
		<div id="right_content" style="float:left; width:50%">
			
		</div>
	</div>*}
	{*}
	<table width="100%">
		<tr style="vertical-align:center">
			<td width="50%" valign="bottom">
					<br /><br /><br />
					_________________<br />
					<span style="text-align:left">Issued By<span><br />
					<b>Name:</b>
			</td>
		
			<td width="50%" valign="bottom">
				<br /><br /><br />
				_________________<br />
				<b>Authorised By</b><br />
				<b>Name:</b>
			</td>
		</tr>
	</table>
	*}
{/if}

</div>
