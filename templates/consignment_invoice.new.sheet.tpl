{*
2/12/2010 5:19:20 PM Andy
- Add new consignment type: consignment over invoice

7/14/2010 2:53:53 PM Andy
- Add settings for consignment invoice.
- Able to control whether use item discount or not.
- Able to control whether split invoice by price type or not when confirm.

6/7/2011 4:00:53 PM Justin
- Added span for currency code area.

12/15/2011 2:12:43 PM Justin
- Added span for selling price for currency code.

1/21/2015 10:46 AM Justin
- Enhanced to have GST calculation.

5/13/2015 5:48 PM Andy
- Change the ajax add item to use json instead of xml.
*}
{config_load file="site.conf"}

{if $form.deliver_branch && !$form.ci_branch_id}
<!-- ci item table -->
<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border small body" cellspacing=1 cellpadding=1>

<!--START HEADER-->
<thead>
<tr bgcolor=#ffffff>
	<th rowspan=2 width=20>#</th>
	<th nowrap rowspan=2 width=100>ARMS Code</th>
	<th nowrap rowspan=2 width=80>Article /<br>MCode</th>
	<th nowrap rowspan=2>SKU Description</th>
	{if $form.create_type==3}
	<th rowspan=2  width=60>PO Cost<br>(RM)</th>
	{/if}
	<th rowspan=2  width=60>Latest Stock<br>Balance</th>
	<th rowspan=2  width=60>Price<br>(RM)</th>
	<th rowspan=2 width=80>UOM</th>
	<th nowrap colspan={count multi=1 var=$form.deliver_branch}>Qty</th>
	<th rowspan=2 width=60>Total<br>Qty</th>
	<th rowspan=2 width=60>Total Amount<BR>(RM)</th>
	{if $form.is_under_gst}
		<th rowspan="2">GST Code</th>
		<th rowspan="2">GST Amount</th>
		<th rowspan="2">Total Amount<br />Include GST<br />(RM)</th>
	{/if}
</tr>
<tr bgcolor=#ffffff>

{if $form.deliver_branch}
{section name=i loop=$branch}
{if $branch[i].id eq $form.deliver_branch}
	<th nowrap>{$branch[i].code}<br>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> <span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span></th>
{/if}
{/section}
{/if}
</tr>
</thead>
<!--END TABLE HEADER -->


<!--START TABLE ITEMS-->
<tbody id="ci_items">
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}

{foreach from=$ci_items item=item name=fitem}
	{section name=i loop=$branch}
	{assign var=bid value=`$branch[i].id`}
	{assign var=total_ctn value=$total_ctn+$item.ctn.$bid}
	{assign var=total_pcs value=$total_pcs+$item.pcs.$bid}
	{/section}

{include file=consignment_invoice.new.ci_row.single_branch.tpl}

{/foreach}
</tbody>
<!--END TABLE ITEMS-->


<!-- START TABLE FOOTER-->
<tfoot>
<!-- total -->
<tr bgcolor=#ffffff class=normal height=24 id="total">
<td colspan={count var=$form.deliver_branch multi=1 offset=6} nowrap align=right>
<b>TOTAL</b>
</td>

<td width=80><b>
T.Ctn : <span id=t_ctn>{$form.total_ctn|default:$total_ctn}</span><br>
T.Pcs : <span id=t_pcs>{$form.total_pcs|default:$total_pcs}</span>
</b></td>


<th align=right id=display_total_amount class="uom">
{$form.total_amount|default:0|number_format:2:".":""}
</th>

<input type=hidden id=total_ctn name=total_ctn value="{$form.total_ctn|default:$total_ctn}">
<input type=hidden id=total_pcs name=total_pcs value="{$form.total_pcs|default:$total_pcs}">

<input id="total_amount" name="total_amount" type=hidden value="{$form.total_amount|number_format:2:".":""}">

</tr>

</tfoot>
<!-- END TABLE FOOTER-->
</table>
</div>

<!--######################################################################################-->
<!--######################################################################################-->

{else}

<!-- ci item table -->
<table width=100% style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=1>

<!--START HEADER-->
<thead class=small>
<tr bgcolor=#ffffff>
	<th rowspan=2 width=20>#</th>
	<th nowrap rowspan=2 width=100>ARMS Code</th>
	<th nowrap rowspan=2 width=80>Article /<br>MCode</th>
	<th nowrap rowspan=2>SKU Description</th>
	{* if $form.create_type==3}
	<th rowspan=2  width=60>PO Cost<br>(RM)</th>
	{/if *}
	<th rowspan=2  width=60>Latest Stock<br>Balance</th>
	<th rowspan=2 width=60>Price<br>(RM)</th>
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th rowspan=2 width=60 id="foreign_price">Price<br />(<span id="span_p_currency_code"></span>)</th>
	{/if}
	<th rowspan=2 width=80>UOM</th>
	{*if !$form.open_info.name*}
	<th rowspan=2 width=60>Selling<br>Price<br>(<span id="span_sp_currency_code">RM</span>)</th>
	{*/if*}
	<th nowrap>Qty</th>
	<th rowspan=2 width=60>Total<br>Qty</th>
	<th rowspan=2 width=60>Discount</th>
	<th rowspan=2 width=60>Total Selling<br>(<span id="span_sp_amt_currency_code">RM</span>)</th>
	<th rowspan=2 width=60>Total Amount<br>(RM)</th>
	{if $form.is_under_gst}
		<th rowspan="2">GST Code</th>
		<th rowspan="2">GST Amount</th>
		<th rowspan="2">Total Amount<br />Include GST<br />(RM)</th>
	{/if}
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th rowspan=2 width=60 id="foreign_ttl_amt">Total Amount<br />(<span id="span_amt_currency_code"></span>)</th>
	{/if}
</tr>
<tr bgcolor=#ffffff>
<th align=center>
	<span style="border:1px solid #ccc;background:#fff">&nbsp;Ctn&nbsp;</span> 
	<span style="border:1px solid #ccc;background:#fc9">&nbsp;Pcs&nbsp;</span>
</th>
</tr>
</thead>
<!--END TABLE HEADER -->


<!--START TABLE ITEMS-->
<tbody id="ci_items">
{assign var=total_ctn value=0}
{assign var=total_pcs value=0}

{foreach from=$ci_items item=item name=fitem}
	{assign var=total_ctn value=$total_ctn+$item.ctn}
	{assign var=total_pcs value=$total_pcs+$item.pcs}

	{include file=consignment_invoice.new.ci_row.single_branch.tpl}

{/foreach}
</tbody>
<!--END TABLE ITEMS-->


<!-- START TABLE FOOTER-->
<tfoot>

{if $form.create_type==3}
{assign var=colspan value=10}
{else}
{assign var=colspan value=9}
{/if}
{if !$form.open_info.name}
{assign var=colspan value=$colspan+1}
{/if}

<!-- Sub Total -->
<tr bgcolor=#ffffff class=normal height=24>
	{assign var=sb_col value=$colspan+1}
	<input type="hidden" name="colspan_length" value="{$sb_col}">
    <td colspan="{$sb_col}" nowrap align=right id="td_sub_total"><b>Sub Total</b></td>
    <th align=right id="td_sub_total_selling">{$form.total_selling|number_format:2:".":""}</th>
	<th class="r" id="td_sub_total_amount">{$total_gross_amount|number_format:2:".":""}</th>
	{if $form.is_under_gst}
	  {assign var="total_gross_amount" value=$sub_total_amt-$form.total_gst_amt-$form.sheet_gst_discount}
	  <th></th>
	  <th class="r" id="td_sub_total_gst">{$form.total_gst_amt+$form.sheet_gst_discount|number_format:2:".":""}</th>
	  <th align=right id="td_sub_total_gst_amount">{$form.sub_total_amt|number_format:2:".":""}</th>
	{/if}
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th align=right id="td_sub_total_foreign_amount">{$form.sub_total_foreign_amt|number_format:2:".":""}</th>
	{/if}
</tr>
<!-- Sub Total -->

<!-- Discount -->
<tr bgcolor=#ffffff class=normal id="tr_discount_amount" style="display:none;height:24px;">
    <td colspan="{$colspan+1}" nowrap align=right id="td_discount"><b>Discount (<span id="span_branch_discount_per">{$form.discount_percent|ifzero:''}</span>%)</b></td>
    <th align=right id="td_display_discount_amount" colspan="2">{$form.discount_amount*-1|default:0|number_format:2:".":""}</th>
	{if $form.is_under_gst}
		{math assign="sheet_discount_gross_amount" equation="(x-y)*-1" x=$form.discount_amount|default:0 y=$form.sheet_gst_discount|default:0}
		<th></th>
		<th class="r" id="td_sheet_discount_gst">{$form.sheet_gst_discount*-1|number_format:2:".":""}</th>
		<th class="r" id="td_sheet_discount_gst_amount">{$sheet_discount_gross_amount|number_format:2:".":""}</th>
	{/if}
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th align=right id="td_display_foreign_discount_amount">
			{$form.discount_foreign_amount*-1|default:0|number_format:2:".":""}
		</th>
	{/if}
</tr>
<!-- End of Discount -->
<!-- total -->
<tr bgcolor=#ffffff class=normal height=24 id="total">

<td colspan="{$colspan}" nowrap align=right id="td_total"><b>TOTAL</b></td>

<td width=80><b>
T.Ctn : <span id=t_ctn>{$form.total_ctn|default:$total_ctn}</span><br>
T.Pcs : <span id=t_pcs>{$form.total_pcs|default:$total_pcs}</span>
</b></td>

<th align=right id=display_total_amount class="uom" colspan="2">
	{$form.total_amount|default:0|number_format:2:".":""}
</th>

{if $form.is_under_gst}
  <th></th>
  <th class="r" id="td_total_gst">{$form.total_gst_amt|number_format:2:".":""}</th>
  <th class="r" id="td_total_gst_amt">{$form.total_gross_amt|number_format:2:".":""}</th>
{/if}

{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
	<th align="right" id="display_total_foreign_amount" class="uom total_foreign_amount">
		{$form.total_foreign_amount|number_format:2:".":""}
	</th>
{/if}
<input type=hidden id=total_ctn name=total_ctn value="{$form.total_ctn|default:$total_ctn}">
<input type=hidden id=total_pcs name=total_pcs value="{$form.total_pcs|default:$total_pcs}">

<input id="total_amount" name="total_amount" type=hidden value="{$form.total_amount|number_format:2:".":""}">
<input id="total_foreign_amount" name="total_foreign_amount" type="hidden" value="{$form.total_foreign_amount}">
<input id="sub_total_amt" name="sub_total_amt" type=hidden value="{$form.sub_total_amt|number_format:2:".":""}">
<input id="sub_total_foreign_amt" name="sub_total_foreign_amt" type=hidden value="{$form.sub_total_foreign_amt|number_format:2:".":""}">
<input id="total_selling" name="total_selling" type=hidden value="{$form.total_selling|number_format:2:".":""}">

<input id="gross_discount_amount" name="gross_discount_amount" type=hidden value="{$form.gross_discount_amount|number_format:2:".":""}">
<input id="input_discount_amount" name="discount_amount" type=hidden value="{$form.discount_amount|number_format:2:".":""}">
<input id="input_foreign_discount_amount" name="foreign_discount_amount" type=hidden value="{$form.foreign_discount_amount|number_format:2:".":""}">

<input name="sub_total_gross_amt" type="hidden" value="{$form.sub_total_gross_amt}" />
<input name="total_gross_amt" type="hidden" value="{$form.total_gross_amt}" />
<input name="sheet_gst_discount" type="hidden" value="{$form.sheet_gst_discount}" />
<input name="total_gst_amt" type="hidden" value="{$form.total_gst_amt}" />

<input name="sub_total_foreign_gross_amt" type="hidden" value="{$form.sub_total_foreign_gross_amt}" />
<input name="total_foreign_gross_amt" type="hidden" value="{$form.total_foreign_gross_amt}" />
<input name="sheet_foreign_gst_discount" type="hidden" value="{$form.sheet_foreign_gst_discount}" />
<input name="total_foreign_gst_amt" type="hidden" value="{$form.total_foreign_gst_amt}" />
<input name="gross_foreign_discount_amount" type="hidden" value="{$form.gross_foreign_discount_amount|number_format:2:".":""}">
</tr>

</tfoot>
<!-- END TABLE FOOTER-->
</table>
</div>
{/if}
 
