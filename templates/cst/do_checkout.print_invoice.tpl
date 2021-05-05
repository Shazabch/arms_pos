{*
REVISION HISTORY
++++++++++++++++

10/5/2007 4:17:35 PM gary
- remove cost price column if have speacial config for it ($config[do_print_hide_cost]).

11/6/2007 4:25:18 PM gary
- change the invoice format. (remove subtotal and else....)

12/7/2007 2:43:52 PM gary
- printout follow the sequence from input.

3/31/2008 5:37:01 PM gary
- add ctn price column.

2008/6/23 15:50:55 yinsee
- add footer msg for GMARK ($config.invoice_footer)

7/1/2008 2:32:38 PM yinsee
- change 15 to $PAGE_SIZE
- add is_lastpage check to print footer

8/12/2008 12:29:53 PM
- crop 1em height for item description

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

11/10/2009 2:34:53 PM Andy
- edit print row, add invoice discount

11/12/2009 5:40:50 PM Andy
- layout edited, artile no and ctn price hide

11/18/2009 4:43:01 PM Andy
- remove the round2 in total_row

11/19/2009 5:42:40 PM Andy
- change invoice no print format

11/23/2009 10:27:09 AM Andy
- layout edit

12/24/2009 3:58:38 PM Andy
- Add DO No in invoice printing

1/12/2010 3:51:36 PM Andy
- Add config to manage item got line or not

1/15/2010 9:47:20 AM Andy
- Make description column to occupy as large space as it can

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/27/2011 5:40:09 PM Justin
- Amended the Deliver To address to take from DO when found user use Address Deliver To.

7/15/2011 1:23:54 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/9/2011 2:18:21 PM Andy
- Change all printing templates to compatible with new discount format.
- Add nowrap for artno for all DO printing templates.

10/25/2011 11:44:34 AM Justin
- Added rounding amount on last page when found config "do_enable_cash_sales_rounding" and it's cash sales.

11/29/2011 04:37:00 PM Andy
- Add to hide DO Date when found user got tick "Don't Show DO Date".

5/17/2013 11:43 AM Andy
- Enhance default printing format to compatible with additional description.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

3/2/2015 4:54 PM Andy
- Enhanced to compatible with GST Tax Invoice Format.

3/3/2015 1:57 PM Andy
- Fix to show do amount if no show invoice discount.

3/23/2015 10:28 AM Andy
- Total Gross Amt and Total GST Amt to align to right.

4/22/2015 5:02 PM Andy
- Fix serial number colspan on GST format.

5/12/2015 2:48 PM Andy
- Enhanced to show GST Summary.

11/17/2015 5:56 PM Andy
- Enhanced to check show invoice on gst summary.

04/01/2016 17:30 Edwin
- Added debtor telephone number and terms at credit sales print preview

3/30/2017 3:15 PM Justin
- Customised the report to fill into landscape and based on customer's request.

4/10/2017 2:02 PM Justin
- Bug fixed on rowspan having some issue while turn on config to use rounding.

4/25/2017 2:02 PM Khausalya
- Enhanced changes from RM to use config setting. 

4/27/2017 9:56 AM Justin
- Enhanced the signature according to customer's request.

5/19/2017 5:04 PM Justin
- Enhanced to have company no beside company name.

5/23/2017 3:55 PM Justin
- Enhanced to adjust the signature line to have tally with boxes.

6/5/2017 1:47 PM Justin
- Modified to change captions.

6/6/2017 1:44 PM Justin
- Bug fixed on the "Cash Bill" and "Credit Sales" cannot print properly while using dot matrix printer.

7/20/2017 16:11 Qiu Ying
- Bug fixed on artno/mcode not show if the items is open item

8/9/2017 11:16 AM Justin
- Bug fixed on amount column couldn't show out.

8/14/2017 4:50 PM Justin
- Enhanced to show Master UOM Code while found DO is using UOM code "EACH".

5/17/2018 9:00 AM KUAN YEH
- Modified address to Bill Address and Delivery Address by normal,branch or credit sales
- Add to two columns delivery address

6/12/2018 5:15 PM Justin
- Bug fixed on MCode and Artno title have placed wrongly.
- Bug fixed on showing wrong table layout at the Total amount section while DO is not under GST.

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption.
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

.gst_tbl_no_border tr,.gst_tbl_no_border th,.gst_tbl_no_border td{
	border:none !important;
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

{if $form.do_type eq 'transfer' and $config.do_transfer_have_discount}
	{assign var=show_invoice value=1}
{elseif $form.do_type eq 'open' and $config.do_cash_sales_have_discount}
    {assign var=show_invoice value=1}
{elseif $form.do_type eq 'credit_sales' and $config.do_credit_sales_have_discount}
    {assign var=show_invoice value=1}
{/if}

{assign var=currency_multiply value=1}
{if $form.do_type eq 'transfer' and $config.consignment_modules and $config.masterfile_branch_region and $config.consignment_multiple_currency and $form.exchange_rate>1}
	{assign var=is_currency_mode value=1}
	{assign var=exchange_rate value=$form.exchange_rate}
	
	{if $form.price_indicate ne 1}
		{assign var=currency_multiply value=$currency_multiply*$exchange_rate}
	{/if}
{/if}

<!-- print sheet -->
<div class="printarea">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
<tr>
	<td>
		{if !$config.do_print_hide_company_logo}
			{if $alt_logo_img}
			<img src={$alt_logo_img} height=70 hspace=5 vspace=5>
			{else}
			<img src="{get_logo_url mod='do'}" height=70 hspace=5 vspace=5>
			{/if}
		{else}
		&nbsp;
		{/if}
		</td>
		<td style="width:100%; vertical-align:top; padding-top:5px;">
		<h2>{$from_branch.description} ({$from_branch.company_no})</h2>
		{$from_branch.address}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
		{if $config.enable_gst and $from_branch.gst_register_no}
			 &nbsp;&nbsp;&nbsp; GST No: {$from_branch.gst_register_no}
		{/if}
	</td>
	<td rowspan=2 align=right>
	    <table class="large">
			<tr>
				<td colspan="2" style="font-size:10px; font-weight:bold; text-align:right;"><font>{$page}</font></td>
			</tr>
			<tr>
				<th colspan="2">
					{if $form.do_type ne "transfer"}
						<div style="padding:4px; font-size:14px; font-weight:bold;" align="center">
							{if $form.do_type eq 'open'}Cash Bill<br />{elseif $form.do_type eq 'credit_sales'}Credit Sales<br />{/if}
						</div>
					{/if}
					<div style="text-align:center; font-size:16px;">{if $form.is_under_gst}Tax {/if}Invoice</div>
					{if $copy_type ne 'normal'}<div style="text-align:right;">{$copy_type}</div>{/if}
				</th>
			</tr>
			<tr><td nowrap>INV NO.</td><td nowrap>{$form.inv_no}</td></tr>
			{if !$config.do_printing_allow_hide_date or !$no_show_date}
				<tr height=22><td nowrap>INV DATE</td><td nowrap>{$form.do_date|date_format:$config.dat_format}</td></tr>
			{/if}
			<tr><td nowrap>D/O NO.</td><td nowrap>{$form.do_no|default:"--"}</td></tr>
			<tr><td nowrap>P/O NO.</td><td nowrap>{$form.po_no|default:"--"}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>

	<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
	<tr>
		<td valign=top width=50% style="border:1px solid #000; padding:5px">
	    <h4>Bill to Address</h4>
	    	{if $form.do_type ne 'transfer'}
				{if $form.do_type eq 'credit_sales'}
					<b>{$form.debtor_description}</b>
					<br>
					{$form.debtor_address|nl2br}
					<br>
					Tel: {$form.debtor_phone|default:'-'} {* changed to - *}
					<br>
					Terms: {$form.debtor_term|default:'-'}
					<br>
				{else}
					<b>{$form.open_info.name}</b>
					<br>
					{$form.open_info.address|nl2br}
					<br>
				{/if}
			{else}
				<b>{$to_branch.description}</b>
				<br>
				{$to_branch.address|nl2br}
				<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}
				<br>Fax: {$to_branch.phone_3}
				{/if}
			{/if}
		
		</td>
		
			
		<td valign="top" width="50%" style="border:1px solid #000; padding:5px">
			<h4>Deliver to Address</h4>
			{if $form.do_type ne 'transfer'} 
				{if $form.do_type eq 'credit_sales'}
					<b>{$form.deliver_debtor_description|default:$form.debtor_description}</b>
					<br>
					{$form.debtor_deliver_address|nl2br}
					<br>
					Tel: {$form.deliver_debtor_phone|default:$form.debtor_phone|default:'-'}
					<br>
					Terms: {$form.deliver_debtor_term|default:$form.debtor_term|default:'-'}
					<br>
				{else}
					{if $form.use_address_deliver_to}
						<b>{$form.open_info.delivery_name|default:$form.open_info.name}</b>
						<br>
						{$form.open_info.delivery_address|default:$form.open_info.address|nl2br}
						<br>
					{else}
						<b>{$form.open_info.name}</b>
						<br>
						{$form.open_info.address|nl2br}
						<br>
					{/if}
				{/if}
			{else}
				<b>{$to_branch.description}</b>
				<br>
				{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}
				<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}
				<br>Fax: {$to_branch.phone_3}{/if}
			{/if}
		
		</td>
		
	</tr>
	</table>


</td>
</tr>

</table>

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb small">
<tr bgcolor="#cccccc">
	<th width="5">&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article No</th>
	<th nowrap>MCode</th>
	<th nowrap width="90%">SKU Description</th>
	<th nowrap>Price ({$config.arms_currency.symbol})</th>
	<th nowrap>Qty</th>
	<th nowrap >UOM</th>
	
	{if $show_invoice}
	    <th width="40">Disc</th>
	{/if}
	
	<th nowrap>Amt</th>
	{* GST *}
	{if $form.is_under_gst}
		<th nowrap>Gst Amt</th>
		<th nowrap>Total W/Gst</th>
		<th>Gst</th>
	{/if}
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
		{assign var=sku_item_code value=$r.sku_item_code}
		<td align="center">{$sku_item_code|default:'&nbsp;'}</td>
		<td align="center" nowrap>{if $r.oi}{$r.artno_mcode|default:'&nbsp;'}{else}{$r.artno|default:'&nbsp;'}{/if}</td>
		<td align="center" nowrap>{if !$r.oi}{$r.mcode|default:'&nbsp;'}{/if}</td>
		<td width="90%"><div {if !$page_item_info.$item_index.no_crop}class="crop"{/if}>{$r.description}</div></td>
		
		{if !$page_item_info.$item_index.not_item}
			{assign var=row_qty value=$r.ctn*$r.uom_fraction+$r.pcs}
			{assign var=cost_price value=$r.cost_price}
		
			{* DO Markup *}
			{if $form.do_markup_arr.0}
				{assign var=adjust_cost value=$form.do_markup_arr.0*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			{if $form.do_markup_arr.1}
				{assign var=adjust_cost value=$form.do_markup_arr.1*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			
			<td align="right">{$cost_price/$r.uom_fraction|number_format:$config.global_cost_decimal_points}</td>
			<td align="right">{$row_qty|qty_nf}</td>
			<td align="center">
				{if $r.uom_code && $r.uom_code ne 'EACH'}
					{$r.uom_code}
				{else}
					{$r.master_uom_code}
				{/if}
			</td>
			
			{if $show_invoice}
				<td align="right">{$r.item_discount|default:'-'}</td>
		    {/if}
			
			{if $show_invoice}
				<td align="right">{$r.inv_line_gross_amt|number_format:2}</td>
				{if $form.is_under_gst}
				<td align="right">{$r.inv_line_gst_amt|number_format:2}</td>
				<td align="right">{$r.inv_line_amt|number_format:2}</td>
				<td align="center">{$r.gst_code}</td>
				{/if}
			{else}
				<td align="right">{$r.line_gross_amt|number_format:2}</td>
				{if $form.is_under_gst}
				<td align="right">{$r.line_gst_amt|number_format:2}</td>
				<td align="right">{$r.line_amt|number_format:2}</td>
				<td align="center">{$r.gst_code}</td>
				{/if}
			{/if}
			
			{*assign var=total_ctn value=$r.ctn+$total_ctn}
			{assign var=total_pcs value=$r.pcs+$total_pcs}
			{assign var=total_qty value=$total_qty+$r.ctn*$r.uom_fraction+$r.pcs*}
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			{if $show_invoice}
				<td>&nbsp;</td>
			{/if}
			<td>&nbsp;</td>
			{if $form.is_under_gst}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			{/if}
		{/if}
		
	</tr>
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
	{if $show_invoice}
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
	{if $form.is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

{if $is_lastpage}
	{* Sub Total *}
	{assign var=cols value=2}
	{if $show_invoice}{assign var=cols value=$cols+1}{/if}
	
	{if $show_invoice and $form.discount}
		{* sub total *}
		<tr class="total_row">
			<td rowspan="3" colspan="6">
				<b>REMARK</b><br />
				{$form.remark|default:"-"|nl2br}<br /><br />

				{$form.checkout_remark|default:"-"|nl2br}<br /><br />
			</td>
		
			<th align="right" colspan="{$cols}">Sub Total</th>
			{if $show_invoice}
				<th align="right">{$form.inv_sub_total_gross_amt|number_format:2}</th>
				{if $form.is_under_gst}
					<th align="right">{$form.inv_sub_total_gst_amt|number_format:2}</th>
					<th align="right">{$form.sub_total_inv_amt|number_format:2}</th>
					<th>&nbsp;</th>
				{/if}
			{else}
				<th align="right">{$form.sub_total_gross_amt|number_format:2}</th>
				{if $form.is_under_gst}
					<th align="right">{$form.sub_total_gst_amt|number_format:2}</th>
					<th align="right">{$form.sub_total_amt|number_format:2}</th>
					<th>&nbsp;</th>
				{/if}
			{/if}
		</tr>
		
		{* Discount *}
		{assign var=cols value=2}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		
		{get_discount_amt assign=discount_amt amt=$total discount_pattern=$form.discount currency_multiply=$currency_multiply}
		{assign var=total value=$total-$discount_amt}
		<tr>
			<th align="right" colspan="{$cols}">Discount ({$form.discount|default:'-'})</th>
			<th align="right">{$form.inv_sheet_gross_discount_amt|number_format:2}</th>
			{if $form.is_under_gst}
				<th align="right">{$form.inv_sheet_gst_discount|number_format:2}</th>
				<th align="right">{$form.inv_sheet_discount_amt|number_format:2}</th>
				<th>&nbsp;</th>
			{/if}
	    </tr>
	{/if}

	{assign var=have_rounding value=0}
	{if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt}
		{* Total Before Round *}
		{assign var=cols value=2}
		{assign var=have_rounding value=1}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		
		{assign var=total_b4_rounded value=$form.total_inv_amt-$form.total_round_inv_amt}
		<tr class="">
			{if (!$show_invoice || !$form.discount)}
				<td rowspan="3" colspan="6">
					<b>REMARK</b><br />
					{$form.remark|default:"-"|nl2br}<br /><br />

					{$form.checkout_remark|default:"-"|nl2br}<br /><br />
				</td>
			{/if}
			<th align="right" colspan="{$cols}" nowrap>Total Before Round</th>
			<th>&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th align=right class="total_row">{$total_b4_rounded|number_format:2}</th>
				<th>&nbsp;</th>
			{/if}
		</tr>
	
		{* Rounding *}
		<tr class="">
			<th align=right colspan="{$cols}" class="total_row">Rounding</th>
			<th>&nbsp;</th>
			{if $form.is_under_gst}
				<th>&nbsp;</th>
				<th align=right class="total_row">{$form.total_round_inv_amt|number_format:2}</th>
				<th>&nbsp;</th>
			{/if}
		</tr>
	{/if}
	
	{* Total After Rounded *}
	<tr class="total_row">
		{assign var=cols value=2}
		{if $show_invoice}{assign var=cols value=$cols+1}{/if}
		{if (!$show_invoice || !$form.discount) && !$have_rounding}
			<td {if $form.is_under_gst}rowspan="3"{/if} colspan="6">
				<b>REMARK</b><br />
				{$form.remark|default:"-"|nl2br}<br /><br />

				{$form.checkout_remark|default:"-"|nl2br}<br /><br />
			</td>
		{/if}
		
		<th align="right" colspan="{$cols}" class="total_row" nowrap>Total {if $form.do_type eq 'open' && $config.do_enable_cash_sales_rounding && $form.total_round_inv_amt} After Rounded{/if}</th>
		{if $show_invoice}
			<th align="right">{$form.inv_total_gross_amt|number_format:2}</th>
			{if $form.is_under_gst}
				<th align="right">{$form.inv_total_gst_amt|number_format:2}</th>
				<th align=right class="total_row">{$form.total_inv_amt|number_format:2}</th>
				<th>&nbsp;</th>
			{/if}
		{else}
			<th align="right">{$form.do_total_gross_amt|number_format:2}</th>
			{if $form.is_under_gst}
				<th align="right">{$form.do_total_gst_amt|number_format:2}</th>
				<th align=right class="total_row">{$form.total_amount|number_format:2}</th>
				<th>&nbsp;</th>
			{/if}
		{/if}
	</tr>
	
	{if (!$show_invoice || !$form.discount) && !$have_rounding && $form.is_under_gst}
		{assign var=gst_cols value=6}
		{if $show_invoice}{assign var=gst_cols value=$gst_cols+1}{/if}
		<tr style="border-bottom:none; border-right:none;">
			<td colspan="{$gst_cols}" rowspan="4" align="center" style="border:none;">
				{if $form.is_under_gst}
					{if $show_invoice}
						{assign var=gst_sum_list value=$form.gst_summary.inv.gst}
					{else}
						{assign var=gst_sum_list value=$form.gst_summary.do.gst}
					{/if}
					<div style="border-color:none !important;">
					{include file='gst_summary.tpl' gst_summary_list=$gst_sum_list gst_tbl_no_border=1}
					</div>
				{/if}
			</td>
		</tr>
	{/if}
		
	{assign var=total value=0}
	{assign var=total_ctn value=0}
	{assign var=total_pcs value=0}
{/if}
</table>

{if $is_lastpage}
		<table class="small" style="border:0px; width:100%; border-top:0px;" cellpadding="0" cellspacing="0">
			<tr>
				<td width="70%">
					<ul style="list-style:none; padding:0; margin:0;">
						<li>If no remarks is made within two days, the above will be consider as correct. All Cheques should be crossed in favour of {$from_branch.description}.</li>
						<li>No goods can be returned for cash. Invoice not paid by due date will carry interest at 2% per month until settled.</li>
					</ul>
				</td>

				{if ($show_invoice and $form.discount) or $have_rounding}
					{if $form.is_under_gst}
						{if $show_invoice}
							{assign var=gst_sum_list value=$form.gst_summary.inv.gst}
						{else}
							{assign var=gst_sum_list value=$form.gst_summary.do.gst}
						{/if}
						<td align="center">
							{include file='gst_summary.tpl' gst_summary_list=$gst_sum_list}
						</td>
					{/if}
				{/if}
			</tr>
		</table>

	{if $config.invoice_footer}
		<p class=small>{$config.invoice_footer}</p>
	{else}
		<table width="100%">
			<tr>
				<td valign="top" class="small" width="30%">
					Received By
					<div style="padding-top:30px;">_________________________</div><br>
					<table>
						<tr>
							<td>Name:</td><td>__________________</td>
						</tr>
						<tr>
							<td>Date:</td>
							<td>
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
				</td>

				<td valign="top" class="small" width="30%">
					Transporter
					<div style="padding-top:30px;">_________________________</div><br>
					<table>
						<tr>
							<td>Name:</td><td>__________________</td>
						</tr>
						<tr>
							<td>Date:</td>
							<td>
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
				</td>

				<td valign="top" class="small" width="40%">
					For {$from_branch.description}
					<div style="padding-top:30px;">________________________</div><br>
					Authorised Signature
				</td>
			</tr>
		</table>
	{/if}
{/if}
</div>
