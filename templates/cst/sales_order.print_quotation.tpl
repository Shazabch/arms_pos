{*
4/27/2017 9:56 AM Justin
- Enhanced the signature according to customer's request.

5/19/2017 5:04 PM Justin
- Enhanced to have company no beside company name.

7/24/2017 1:15 PM Justin
- Enhanced to remove the "Remark" title.
- Bug fixed on discount column shows wrongly.
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

{literal}

{/literal}
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
<div class="printarea" width="100%">
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tr>
			<td><img src="{get_logo_url}" height="80" hspace="5" vspace="5"></td>
			<td width="100%">
				<h2>{$from_branch.description} ({$from_branch.company_no})</h2>
				{$from_branch.address|nl2br}<br />
				Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
				{if $from_branch.phone_3}
				&nbsp;&nbsp; Fax: {$from_branch.phone_3}
				{/if}
				{if $config.enable_gst and $from_branch.gst_register_no}
					 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
				{/if}
			</td>
			<td rowspan="2" align="right">
				<table class="large">
					<tr><td colspan="2" style="font-size:10px; font-weight:bold; text-align:right;"><font>{$page}</font></td></tr>
					<tr>
						<td colspan="2" align="right">
							<h1>Quotation</h1>
						</td>
					</tr>
					<tr>
						<td nowrap width="100px">NO.</td>
						<td nowrap>{$form.order_no}</td>
					</tr>
					<tr>
						<td nowrap width="100px">DATE.</td>
						<td nowrap>{$form.order_date|date_format:$config.dat_format}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%" cellspacing="5" cellpadding="0" border="0">
				<tr>
					<td valign="top" style="padding:5px">
						To<br />
						<b>{$debtor[$form.debtor_id].description}</b><br />
						{$debtor[$form.debtor_id].address}<br />
						TEL: {$debtor[$form.debtor_id].phone_1}<br />
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">
		<tr bgcolor="#cccccc">
			<th width="5" nowrap>&nbsp;</th>
			<th nowrap>ARMS Code</th>
			<th nowrap>M Code</th>
			<th nowrap>Article Code</th>
			<th width="60%" nowrap>Description</th>
			<th nowrap>Price</th>
			<th nowrap>QTY</th>
			<th nowrap>UOM</th>
			<th nowrap>Disc</th>
			{if $form.is_under_gst}
				<th nowrap>Amt</th>
				<th nowrap>GST Amt</th>
			{/if}
			<th nowrap>Total{if $form.is_under_gst} W/GST{/if}</th>
			{if $form.is_under_gst}
				<th nowrap>GST</th>
			{/if}
		</tr>
		{assign var=counter value=0}
		{section name=i loop=$items}
		<!-- {$counter++} -->
		<tr class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
			{if !$page_item_info[i].not_item}
				{assign var=row_amt value=$items[i].line_gross_amt+$items[i].line_gst_amt}
				<td align="center" nowrap>{$items[i].item_no+1}.</td>
				<td align="center" nowrap>{$items[i].sku_item_code}</td>
				<td align="center" nowrap>{$items[i].mcode|default:'&nbsp;'}</td>
				<td align="center" nowrap>{$items[i].artno|default:'&nbsp;'}</td>
			{else}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			<td width="60%"><div class="crop">{$items[i].sku_description}</div></td>
			{if !$page_item_info[i].not_item}
				{assign var=row_qty value=$items[i].ctn*$items[i].uom_fraction+$items[i].pcs}
				<td align="right">{$items[i].selling_price|number_format:2}</td>
				<td align="right">{$row_qty|qty_nf}</td>
				<td align=center>{$items[i].uom_code|default:"EACH"}</td>
				<!-- Discount -->
				<td align="right">{$items[i].item_discount|default:'-'}</td>
				{if $form.is_under_gst}
					<td align="right">{$items[i].line_gross_amt|number_format:2}</td>
					<td align="right">{$items[i].line_gst_amt|number_format:2}</td>
				{/if}
				<!-- Total Amount -->
				<td align="right">{$row_amt|number_format:2}</td>
				{if $form.is_under_gst}
					<td align="center">{$items[i].gst_code}</td>
				{/if}
			{else}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				{if $form.is_under_gst}
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				{/if}
				<td>&nbsp;</td>
			{/if}
		</tr>
		{/section}
		
		{assign var=s2 value=$counter}
		{section name=s start=$counter loop=$PAGE_SIZE}
		<!-- filler -->
		{assign var=s2 value=$s2+1}
		<tr height="20" class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
			<td>&nbsp;</td>
		</tr>
		{/section}

		{if $is_lastpage}
			{assign var=sub_total_amt value=$form.total_amount+$form.sheet_discount_amount}
			{assign var=colspan1 value=3}
			{assign var=rowspan1 value=1}
			{if $form.sheet_discount_amount}
				{assign var=rowspan1 value=$rowspan1+2}
			{/if}
			<tr class="total_row">
				<td colspan="6" rowspan="3">
					<b>REMARK</b><br />
					{$form.remark|default:"-"|nl2br}<br /><br />
				</td>
				
				<!-- Sub Total -->
				<th align="right" colspan="{$colspan1}" class="total_row" nowrap>{if $form.sheet_discount_amount}Sub{/if}Total</th>
				{if $form.is_under_gst}
					{assign var="total_gross_amount" value=$sub_total_amt-$form.total_gst_amt-$form.sheet_gst_discount}
					<th align="right">{$total_gross_amount|number_format:2}</th>
					<th align="right">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2}</th>
				{/if}
				<th align="right">{$sub_total_amt|number_format:2}</th>
				{if $form.is_under_gst}
					<th>&nbsp;</th>
				{/if}
				
			</tr>
			{if $form.sheet_discount_amount}
			<!-- Sheet Discount -->
			<tr>
				<th align="right" colspan="{$colspan1}" >Disc</th>
				{if $form.is_under_gst}
					{math assign="sheet_discount_gross_amount" equation="x-y" x=$form.sheet_discount_amount y=$form.sheet_gst_discount}
					<th align="right">{$sheet_discount_gross_amount|number_format:2}</th>
					<th align="right">{$form.sheet_gst_discount|number_format:2}</th>
				{/if}
				<th align="right">{$form.sheet_discount_amount|number_format:2}</th>
				{if $form.is_under_gst}
					<th>&nbsp;</th>
				{/if}
			</tr>
			
			<!-- Total -->
			<tr>
				<th align="right" colspan="{$colspan1}" >Total</th>
				
				{if $form.is_under_gst}
					<th align="right">{$form.total_gross_amt|number_format:2}</th>
					<th align="right">{$form.total_gst_amt|number_format:2}</th>
				{/if}
				
				<th align="right">{$form.total_amount|number_format:2}</th>
				{if $form.is_under_gst}
					<th>&nbsp;</th>
				{/if}
			</tr>
			{else}
				<tr border="0"></tr>
				<tr border="0"></tr>
			{/if}
		{/if}
	</table>
	
	{if $is_lastpage}
		<table width="100%" class="small">
		<tr>
			<td width="85%" valign="top">
				<ul style="list-style:none; padding:0; margin:0;">
					<li>We wish to thank you for having good faith in our company and giving us the opportunity for a submission of our quotation.</li>
					<li>Please find the above of financial breakdown for your kind consideration.</li>
					<li>We hope that you find our quotation satisfactory and we look forward to providing our services for your corporation.</li>
					<li>Please do not hesitate to contact us should you required any further information.</li>
					<li>This quotation only valid within 10 day start from valid date. After valid date, this quotation will follow a latest price.</li>
					<li>Thank you.</li>
				</ul>
			</td>
			<td valign="bottom">
				<br /><br /><br /><br />
				_____________________________<br />
				Authorised Signature(s)<br /><br />
				NAME:{$sessioninfo.fullname}<br /><br />
				Date:
				&nbsp;
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
				-
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
				-
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
				<span style="font-size:15px; padding-left:6px; border: 1px solid;">&nbsp;</span>
			</td>
		</tr>
		</table>
	{/if}
</div>
