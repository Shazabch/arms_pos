{*4/10/2015 11:03 AM Justin- Bug fixed on Vendor Name does not appear.4/14/2015 5:59 PM Justin- Enhanced to change report layout.- Enhanced to show document no and GST price indicator.4/15/2015 10:38 AM Justin- Bug fixed on print empty rows table cannot show out.4/18/2015 4:18 PM Justin- Enhanced report to include extra GRA information.4/22/2015 9:51 AM Justin- Modified to change the price indicator to use GST code.4/28/2015 5:54 PM Justin- Enhanced to remove Invoice No. and Date from header.- Enhanced to take off the Rounding Adjustment.- Enhanced to have Invoice Date by itemise.7/15/2015 9:30 AM Justin- Enhanced item amount to show in decimal points base on global cost decimal points settings from config when printing GRA's DN.11/30/2015 9:43 PM DingRen- change item gross amount to 2 decimal12/24/2015 9:55 AM Qiu Ying- SKU Additional Description should show in document printing1/13/2016 10:20 AM Edwin- Change vendor address to follow alternative branch contact.4/21/2017 4:22 PM Khausalya- Enhanced changes from RM to use config setting. 3/7/2018 1:50 PM HockLee- Added "Prepared By" and "Received By" at footer.5/2/2018 4:16 PM Justin- Enhanced to have foreign currency feature.9/14/2018 2:48 PM Justin- Bug fixed on showing wrong unit price when printing D/N from Consignment GRA.12/27/2018 10:45 AM Justin- Enhanced to print barcode on top of the document number.5/16/2019 5:52 PM William- Enhance "GRA" word to use report_prefix.*}{if !$skip_header}{include file='header.print.tpl'}<style>{if $config.gra_printing_no_item_line}{literal}.no_border_bottom td{	border-bottom:none !important;}.total_row td, .total_row th{    border-top: 1px solid #000;}.td_btm_got_line td,.td_btm_got_line th{    border-bottom:1px solid black !important;}{/literal}{/if}</style><script type="text/javascript">var doc_no = '#{$form.id|string_format:"%05d"}';{literal}function start_print(){	document.title = doc_no;	window.print();}{/literal}</script><body onload="start_print();">{/if}<!-- print sheet --><div class="printarea"><table width="100%" cellpadding="4" cellspacing="0" border="0">	<tr>		<td valign="top" align="center" class="xlarge" colspan="4" nowrap><h4>DEBIT NOTE</td>	</tr>	<tr>		<td width="40%" valign="top" style="border:1px solid #000; padding:5px;">			<h4>From</h4>			<h4>{$branch.description}</h4>			{$branch.address|nl2br}			<br />			GST	ID No.: {$branch.gst_register_no}			<br />			Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if}		</td>		<td>&nbsp;</td>		<td width="40%" valign="top" style="border:1px solid #000; padding:5px">			<h4>Bill To</h4>			<b>{$vendor.description}</b><br>			{if $branch_vendor.address}				{$branch_vendor.address}<br>			{else}				{$vendor.address}<br>			{/if}			{if $form.is_under_gst}				GST	ID No.: {$vendor.gst_register_no}			{/if}			<br />		</td>		<td align="right">			<table class="xlarge">				<tr height=22>					<td nowrap>D/N No.:</td>					<td {if $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>						{if $config.print_document_barcode}							<span class="barcode3of9" style="padding:0;">								*{$form.dn_no}*							</span>						{/if}												<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>							{$form.dn_no}						</div>					</td>				</tr>				<tr height=22><td nowrap>D/N Date:</td><td nowrap>{$form.dn_date}</td></tr>				<tr height=22><td nowrap>{$form.ref_table|default:"Document"|strtoupper} No:</td><td nowrap>{$branch.report_prefix}{$form.ref_id|string_format:"%05d"}</td></tr>				<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>			</table>		</td>	</tr></table><br><table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">	<tr  bgcolor=#cccccc>		<th>No.</th>		<th width=40%>Description</th>		<th nowrap>Inv / DO<br />No.</th>		<th nowrap>Inv / DO<br />Date.</th>		<th>Reason</th>		{if $form.is_under_gst}			<th>Tax Rate<br />(%)</th>		{/if}		<th>Quantity<br />(PCS)</th>		<th>Unit Price<br />({$form.currency_code|default:$config.arms_currency.symbol})</th>		<th>Total<br />({$form.currency_code|default:$config.arms_currency.symbol})</th>	</tr>	{assign var=t_page value=0}	{assign var=ttl_qty value=0}	{assign var=ttl_amt value=0}	{foreach from=$items name=i item=r key=item_index}		<!--{$t_page++}-->		<tr  id="tbrow_{$gra_items[i].id}" bgcolor="{cycle values="#eeeeee,"}" class="no_border_bottom {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">			<td align=right>				<!--{$line_no++}-->				{if !$page_item_info.$item_index.not_item}					{$r.item_no+1}.				{else}					&nbsp;{*{$start_counter+$smarty.foreach.i.iteration}.*}				{/if}			</td>			<td>{$r.description}</td>			{if !$page_item_info.$item_index.not_item}				<td>{$r.doc_no|ucwords|default:"&nbsp;"}</td>				<td>{$r.doc_date|ifzero:"&nbsp;"}</td>				<td>{$r.reason|ucwords|default:"&nbsp;"}</td>				{if $form.is_under_gst}					<td align="right">{$r.gst_code} @ {$r.gst_rate|number_format:2}</td>				{/if}				<td align="right">{$r.qty|qty_nf}</td>				<td align="right">					{if $form.ref_table eq 'gra' && $form.sku_type eq 'CONSIGN'}						{$r.selling_price|number_format:2}					{else}						{$r.cost|number_format:$config.global_cost_decimal_points}					{/if}				</td>				<td align="right">{$r.item_gross_amount|number_format:2}</td>			{else}				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>				{if $form.is_under_gst}					<td>&nbsp;</td>				{/if}				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>			{/if}		</tr>	{/foreach}	{repeat s=$t_page+1 e=$PAGE_SIZE name=rr}	<tr height=30 class="no_border_bottom">		<td>&nbsp;</td>		<td>&nbsp;</td>		<td>&nbsp;</td>		<td>&nbsp;</td>		<td>&nbsp;</td>		{if $form.is_under_gst}			<td>&nbsp;</td>		{/if}		<td>&nbsp;</td>		<td>&nbsp;</td>		<td>&nbsp;</td>	</tr>	{/repeat}	{if $is_last_page}		{assign var=colspan value=5}		{assign var=rowspan value=1}		{if $form.is_under_gst}			{assign var=colspan value=$colspan+1}			{assign var=rowspan value=$rowspan+2}		{/if}		<tr>			<td valign="top" colspan="{$colspan}" rowspan="{$rowspan}"><b>Remark: </b>{$form.remark|nl2br}</td>			<th align="right" colspan="2">				{if $form.is_under_gst}					Amount Excluding Tax				{else}					Total				{/if}			</th>			<td align="right" class="total_row">{$form.total_gross_amount|number_format:2}</td>		</tr>		{if $form.is_under_gst}			<tr>				<th align="right" colspan="2">Total GST</th>				<td align="right" class="total_row">{$form.total_gst_amount|number_format:2}</td>			</tr>			<!--tr>				<th align="right" colspan="2">Rounding Adjustment</th>				<td align="right" class="total_row">{$form.rounding_adjustment|number_format:2}</td>			</tr-->			<tr>				<th align="right" colspan="2">Amount Including Tax</th>				<td align="right" class="total_row">{$form.total_amount|number_format:2}</td>			</tr>		{/if}	{/if}</table><br><table width="100%"><tr height="80"><td valign="bottom">_________________<br>Prepared By<br>Name:<br>Date:</td><td valign="bottom">_________________<br>Received By<br>Name:<br>Date:</td></tr></table></div>