{*
4/2/2010 10:51:59 AM Andy
- Printing remove vertical line

7/23/2010 11:19:02 AM Andy
- Fix Consignment Invoice when multiple print will not show the printing by user name.

9/3/2010 10:54:55 AM Andy
- Add print branch code for deliver to branch.

11/12/2010 5:11:50 PM Justin
- Rounded up the amount become 2 decimal points during sum up total.

11/22/2010 2:49:11 PM Justin
- Fixed the rounding problem that is not workable from previous version.

5/9/2011 6:42:09 PM Andy
- Change printing font size bigger.

7/15/2011 11:36:41 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

9/18/2012 3:31 PM Drkoay
- add discount_selling_price_percent and discount_item_row_percent for calculate item price

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

3/31/2015 6:23 PM Justin
- Enhanced report to meet customer report format.

4/3/2015 5:54 PM Justin
- Enhanced to have GST summary and report changes.

10/22/2015 10:39 AM DingRen
- fix discount_selling_price_percent calculation error

4/26/2017 8:06 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/8/2017 9:39 AM Khausalya
- Enhanced changes from Ringgit Malaysia to use config setting. 

8/28/2018 11:56 AM Justin
- Enhanced to show/hide Tax Invoice caption.

10/8/2018 4:06 PM Andy
- Remove the word "CONSIGNMENT" for non-gst invoice.
*}

{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.ci_printing_no_item_line}
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

.td_top_got_line{
    border-top:1px solid black !important;
}
{/literal}
{/if}

{literal}
body{
  font-size:9pt;
}
.tb{
	font-size: 11pt;
}
.tb th,.tb td{
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

{*
{if $form.type eq 'lost' or $form.type eq 'over'}
  {assign var=show_per value=1}
{/if}
*}

<!-- print sheet -->
<div class=printarea>
<table width="100%" cellspacing=0 cellpadding=0 border=0 style="font-size:11pt; padding-bottom:14px;">
<tr height="175px">
	<td>&nbsp;</td>
</tr>
<tr>
	<th align="center" class="large">{if $form.is_under_gst}TAX {/if}INVOICE</th>
</tr>
<tr class="small">
	<td>
		<table width=100% cellpadding=0 border=0>
		<tr>
			<td valign=top width=45% style="border:1px solid #000; padding:5px;padding-left:10px;">
				<h4>Bill To</h4>
				<b><font class="large">{$to_branch.description}</font></b><br>
				{$to_branch.address|nl2br}<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				{if $config.enable_gst && $to_branch.gst_register_no}<br />GST No: {$to_branch.gst_register_no}{/if}
				
			</td>
			<td valign=top width=25% style="border:1px solid #000; padding:5px;padding-left:10px;">
				<table class="large" width="100%">
					<tr><td nowrap>Date: {$form.ci_date|date_format:"%d/%m/%Y"}</td></tr>
					<tr><td nowrap>Department: {$to_branch.con_dept_name|default:'-'}</td></tr>
					<tr><td>Your/Our Purchase Order No.: CONSIGNMENT</td></tr>
				</table>
			</td>
			<td valign=top width=25% style="border:1px solid #000; padding:5px;padding-left:10px;">
				<table class="large" width="100%">
					<tr><td nowrap>Invoice No.: {$form.ci_no}</td></tr>
					<tr><td nowrap>Our D/O No.:</td></tr>
					<tr><td nowrap>Terms: {$to_branch.con_terms|default:'-'}</td></tr>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc class="small">
	<th>&nbsp;</th>
	<th>Code No.</th>
	<th width="50%">Description</th>
    <th width=80>U/Price<br />({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
    <th width=80>Qty</th>
	<th width=80>Disc<br>(%)</th>
	<th width=80>Amount<br>({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
	{if $form.is_under_gst}
		<th width=80>GST<br>({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
		<th width=80>Amount<br />Include<br />GST ({$to_branch.currency_code|default:$config.arms_currency.symbol})</th>
	{/if}
</tr>

{assign var=counter value=0}
{if $form.exchange_rate}
	{assign var=exchange_rate value=$form.exchange_rate}
{else}
	{assign var=exchange_rate value=1}
{/if}
{section name=i loop=$ci_items}
<!-- {$counter++} -->
<tr class="no_border_bottom{if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td align=center>{$start_counter+$counter}.</td>
	<td nowrap>{$ci_items[i].artno}</td>
	<td nowrap><div class="crop">{if $form.is_under_gst}{$ci_items[i].indicator_receipt} {/if}{$ci_items[i].description}</div></td>
	{assign var=cost_price value=$ci_items[i].cost_price}
	{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
		{assign var=cost_price value=$ci_items[i].foreign_cost_price}
	{/if}
	
	{*if $ci_items[i].gst_rate}
		{assign var=gst_cost value=$cost_price*$ci_items[i].gst_rate/100}
		{assign var=gst_cost value=$gst_cost|round2}
		{assign var=cost_price value=$cost_price+$gst_cost}
	{/if*}
	
	{if $form.discount_selling_price_percent}
		{assign var=disc_arr value="+"|explode:$form.discount_selling_price_percent}
		{foreach from=$disc_arr item=disc}
			{assign var=cost_deduct value=$cost_price*$disc/100}
			{assign var=cost_price value=$cost_price-$cost_deduct}
			{assign var=cost_price value=$cost_price}
		{/foreach}
	{/if}

	{if $form.discount_item_row_percent}
		{assign var=cost_deduct value=$cost_price*$form.discount_item_row_percent/100}
		{assign var=cost_price value=$cost_price-$cost_deduct}
		{assign var=cost_price value=$cost_price|round:2}
	{/if}
	
	<td align="right">{$cost_price|number_format:2}</td>
	<td align="right">
		{assign var=qty value=$ci_items[i].uom_fraction*$ci_items[i].ctn+$ci_items[i].pcs}
		{$qty|default:'0'}
	</td>
	<td align="right">{$ci_items[i].discount|default:'0'}%</td>
	{assign var=amt_ctn value=$cost_price*$ci_items[i].ctn}
	{assign var=amt_pcs value=$cost_price/$ci_items[i].uom_fraction*$ci_items[i].pcs}
	{assign var=total_row value=$amt_ctn+$amt_pcs}

	{if $form.show_per}
		{if $ci_items[i].disc_arr.0}
		    {assign var=discount_per value=$ci_items[i].disc_arr.0*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
		{if $ci_items[i].disc_arr.1}
		    {assign var=discount_per value=$ci_items[i].disc_arr.1*0.01}
			{assign var=discount_amount value=$total_row*$discount_per}
			{assign var=total_row value=$total_row-$discount_amount}
		{/if}
	{/if}

	<td align="right">{$total_row|number_format:2}</td>
	{if $form.is_under_gst}
		{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
			<td align="right">{$ci_items[i].item_foreign_gst|number_format:2}</td>
			<td align="right">{$ci_items[i].item_foreign_gst_amt|number_format:2}</td>
			{assign var=total_gst value=$total_gst+$ci_items[i].item_foreign_gst}
			{assign var=total_gst_amt value=$total_gst_amt+$ci_items[i].item_foreign_gst_amt}
		{else}
			<td align="right">{$ci_items[i].item_gst|number_format:2}</td>
			<td align="right">{$ci_items[i].item_gst_amt|number_format:2}</td>
			{assign var=total_gst value=$total_gst+$ci_items[i].item_gst}
			{assign var=total_gst_amt value=$total_gst_amt+$ci_items[i].item_gst_amt}
		{/if}
	{/if}

	{assign var=total value=$total+$total_row|round:2}
	{assign var=total_qty value=$total_qty+$qty}
</tr>
{/section}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="{if $s2 ne $PAGE_SIZE}no_border_bottom{/if}{if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
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
	{/if}
</tr>
{/section}



{if $is_lastpage}
	<tr>
		<td colspan="3" rowspan="3" class="td_top_got_line">
			Remark: {$form.remark|default:'&nbsp;'}<br />
			{if $gst_summary_list}
				{include file='consignment.gst_summary.tpl'}
			{else}
				&nbsp;
			{/if}
		</td>
		<td align="right" nowrap class="td_top_got_line"><b>Sub Total</b></td>
		<td align=right class="td_top_got_line">{$total_qty}</td>
		<td align="right" colspan=2" class="td_top_got_line">{$total|number_format:2}</td>
		{if $form.is_under_gst}
			<td align="right" class="td_top_got_line">{$total_gst|number_format:2}</td>
			<td align="right" class="td_top_got_line">{$total_gst_amt|number_format:2}</td>
		{/if}
	</tr>
	<tr>
		<td align="right" nowrap>
			<b>Disc ({$form.discount_percent|default:'0'}%)</b>
		</td>
		<td align="right" colspan="3">
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
				{$form.gross_foreign_discount_amount|ifzero:$form.discount_amount|number_format:2}
			{else}
				{$form.gross_discount_amount|ifzero:$form.discount_amount|number_format:2}
			{/if}
		</td>
		{if $form.is_under_gst}
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
				<td align=right>{$form.sheet_foreign_gst_discount|number_format:2}</td> 
				<td align=right>{$form.foreign_discount_amount|number_format:2}</td>
			{else}
				<td align=right>{$form.sheet_gst_discount|number_format:2}</td> 
				<td align=right>{$form.discount_amount|number_format:2}</td>
			{/if}
		{/if}
	</tr>
	<tr>
		<td align="right"><b>Total</b></td>
		<td align=right>{$total_qty}</td>
		<td align=right colspan="2">
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
				{$form.total_foreign_gross_amt|ifzero:$form.total_amount|number_format:2}
			{else}
				{$form.total_gross_amt|ifzero:$form.total_amount|number_format:2}
			{/if}
		</td> 
		{assign var=wording_amt value=$form.total_gross_amt|ifzero:$form.total_amount}
		{if $form.is_under_gst}
			{if $to_branch.currency_code && $to_branch.currency_code ne $config.arms_currency.symbol}
				<td align=right>{$form.total_foreign_gst_amt|number_format:2}</td> 
				<td align=right>{$form.total_foreign_amount|number_format:2}</td> 
				{assign var=wording_amt value=$form.total_foreign_amount}
			{else}
				<td align=right>{$form.total_gst_amt|number_format:2}</td> 
				<td align=right>{$form.total_amount|number_format:2}</td> 
				{assign var=wording_amt value=$form.total_amount}
			{/if}
		{/if}	
	</tr>
	<tr>
		{assign var=colspan value=7}
		{if $form.is_under_gst}
			{assign var=colspan value=$colspan+2}
		{/if}
		<th align="right" colspan="{$colspan}">(In {$to_branch.currency_description|default:$config.arms_currency.name} {convert_number number=$wording_amt show_decimal=1})</th>
	</tr>
{assign var=total value=0}
{assign var=total_gst value=0}
{assign var=total_gst_amt value=0}
{/if}

</table>

{if $is_lastpage}

<br />

<table width=100% class="large">
	<tr>
		<td valign=bottom height="25">
		<b>Issued by</b> ______________________________
		</td>
		<td align="right">
			For: <b>BODYKIDS FASHION WEAR SDN. BHD.</b>
		</td>
	</tr>
	<tr>
		<td valign=bottom height="25">
			<b>Checked by</b> ____________________________
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign=bottom height="25">
			<b>Approved by</b> ___________________________
		</td>
		<td align="right" valign=bottom>
			_____________________________________
		</td>
	</tr>
</table>
{/if}

</div>
