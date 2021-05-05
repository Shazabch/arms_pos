{*
REVISION HISTORY
=================
3/17/2008 3:18:22 PM gary
- add title in each row amount. (fix the amount bug)

6/9/2008 12:27:15 PM yinsee
- add default:$item.pcs for $item.pcs_allocation.$bid

8/7/2009 3:03:59 PM Andy
- if $config.doc_uom_control equal true and master uom id not equal 1, lock uom selection

11/5/2009 4:50:58 PM Andy
- add invoice discount. per sheet and per item

6/17/2010 3:10:10 PM Justin
- Disabled any unrelated fields such as Stock Balance, Selling Price and etc if it is a Open Item.

7/20/2010 2:48:02 PM Justin
- Fixed the division errors while adding a empty or open items.

6/10/2011 10:54:11 AM Justin
- Added new feature for consignment customer of different currency fields.

6/20/2011 5:35:27 PM Alex
- add latest cost and price indicator column

8/5/2011 4:22:06 PM Andy
- Change DO Invoice Discount format.

8/19/2011 2:59:21 PM Justin
- Fixed the division by zero error message.

10/3/2011 5:55:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- Modified to resize the ctn and pcs fields when it's allow decimal points.

12/6/2011 10:08:32 AM Justin
- Added SA column.

1/16/2012 5:13:43 PM Justin
- Changed the word "None" become "--" for SA list.

2/8/2012 4:48:43 PM Justin
- Added new column "Master UOM" and renamed the current UOM become "DO UOM".

3/30/2012 11:10:43 AM Justin
- Added to set require to show confirmation when user about to leave the page.

4/20/2012 11:18:57 AM Alex
- add show packing uom code

4/25/2012 2:30:21 PM Alex
- change id to class for stock balance, price type and price to fix previous bugs of unable to change 2 items or above in a list 

7/23/2012 4:21 PM Andy
- Add checkbox for Credit Sales DO to able to ignore last credit sales cost and use cost from price indicator.

7/30/2012 10:16 AM Andy
- Fix credit sales DO cost bugs.

11/15/2012 5:59 PM Justin
- Bug fixed on after item price more than RM1k and user save it again, system will capture RM1 instead of RM1k.

3/1/2013 5:20 PM Justin
- Enhanced to enable/disable UOM field base on config set.

3/6/2013 11:19 AM Justin
- Bug fixed on system always show "EACH" on UOM even the DO items is having other fraction.

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

5/16/2013 9:53 AM Andy
- Make sku item id input have name.

5/22/2013 5:03 PM Justin
- Bug fixed on stock balance cannot be updated while deliver branch changed.

3/20/2014 1:30 PM Justin
- Enhanced to to show checklist qty & variance.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.
- Create a new JS class DO_MODULE and merge some JS.

6/19/2015 3:41 PM Andy
- Fix cost price rounding issue when no GST.

2/17/2016 10:37 AM Andy
- Fix invoice amount rounding issue on view screen.
- Fix DO amount rounding issue on view screen.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

3/29/2017 4:20 PM Justin
- Enhanced to allow user to key in extra qty instead of prompt error message while found item is existed.

5/29/2017 3:46 PM Justin
- Enhanced to have BOM function.

7/19/2017 10:28 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items
- Bug fixed on artno/mcode not show if the items is open item 

8/24/2018 5:38 PM Andy
- Enhanced Credit Sales DO to have Debtor Price feature.

3:06 PM 11/21/2018 Justin
- Enhanced to have new function to auto focus on ctn and pcs.

12/6/2018 5:37 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

3/14/2019 4:33 PM Andy
- Fixed sales agent should not allow to change at DO Checkout screen.

7/4/2019 5:52 PM William
change '--' when negative value to '-'.

8/28/2019 9:50 AM William
- Enhanced to added new column for new config "do_custom_column".

11/27/2019 3:03 PM William
- Enhance to show sku item photo when config "do_show_photo" is active.

06/22/2020 06:00 PM Sheila
- Fixed table boxes alignment and width.

03/09/2021 09:43 AM Ian
- Enhanced to add column rsp price & rsp discount when config "do_show_rsp" is active.

*}

{literal}
<style>
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}
</style>
{/literal}

{assign var=row_no value=$item_n|default:$smarty.foreach.fitem.iteration}

{if $form.do_type eq 'transfer' and $config.consignment_modules and $config.masterfile_branch_region and $config.consignment_multiple_currency and $form.exchange_rate>1}
	{assign var=is_currency_mode value=1}
	{assign var=exchange_rate value=$form.exchange_rate}
{/if}
<td align=center nowrap width=60 class="{if $item.bom_ref_num}td_bom_ref_num-{$item.bom_ref_num}{/if}" title="{$item.id}">
{if $form.create_type ne '3' && ($form.status<1 || $form.status eq '2') && !$form.approval_screen && !$readonly}
<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
{/if}
<span class="no" id="no_{$smarty.foreach.fitem.iteration}" nowrap>
{$smarty.foreach.fitem.iteration}.</span>
{if $do_type eq 'credit_sales'}
<a href="javascript:void(0)" onclick="show_context_menu(this, '{$item.sku_item_id}');"><img src="/ui/icons/bullet_arrow_down.png" border=0 align="absmiddle"></a>
{/if}

<input type="hidden" name="master_uom_id[{$item.id}]" value="{$item.master_uom_id}" class="inp_master_uom_id" />
</td>
{if $config.do_show_photo}
<td>
	{if $item.photo}
		<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" src="/thumb.php?w=100&h=100&cache=1&img={$item.photo|urlencode}" border="1" style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$item.photo}\'>')" title="View" />
	{else}
	- No Photo -
	{/if}
</td>
{/if}
{assign var=bid value=$form.do_branch_id|default:"0"}
{if $item.oi && !$readonly}
	<input type="hidden" name="oi_val[{$item.id}]" value="{$item.oi}">
	<td align=center></td>
	<td align="center" nowrap><input type="text" name="artno_mcode[{$item.id}]" value="{$item.artno_mcode}" /></td>
	<td>&nbsp;</td>
	{if $config.link_code_name && $config.docs_show_link_code}
		<td align=center></td>
	{/if}
	<td><input type="text" name="doi_description[{$item.id}]" size="50" value="{$item.description}" /></td>
	{if $config.do_show_rsp}
	<td></td><td></td>
	{/if}
{else}
<td align=center id="sku_item_code-{$item.id}">
	{if $item.is_first_bom}
		<font style="color:blue;">
			{$item.bom_parent_si_code}
		</font>
		<br />
	{/if}
	{$item.sku_item_code}
</td>
<td nowrap>
	{if $item.is_first_bom && $item.bom_parent_si_artno}
		<font style="color:blue;">
			{$item.bom_parent_si_artno}
		</font>
		<br />
	{/if}
	{if $item.oi}
		{$item.artno_mcode}
	{else}
		{$item.artno}
	{/if}
</td>

<td nowrap>
	{if $item.is_first_bom && $item.bom_parent_si_mcode}
		<font style="color:blue;">
			{$item.bom_parent_si_mcode}
		</font>
		<br />
	{/if}
	{if !$item.oi}
		{$item.mcode}
	{/if}
</td>

{if $config.link_code_name && $config.docs_show_link_code}
	<td id="item_link_code-{$item.id}">{$item.link_code}</th>
{/if}

<td>
	{if $item.is_first_bom}
		<font style="color:blue;">
			{$item.bom_parent_si_desc}
		</font>
		<br />
	{/if}
	{if $config.do_auto_split_by_price_type}
		<sup style="color:blue" class="span_price_type_{$item.sku_item_id}">{$item.price_type.$bid}</sup>
		<input type="hidden" name="price_type[{$item.id}][{$bid}]" class="inp_price_type_{$item.sku_item_id}" value="{$item.price_type.$bid}" title="{$item.id}" />
	{/if}
	{$item.description} {include file=details.uom.tpl uom=$item.master_uom_code}

	{if $config.do_show_rsp}
		{if $item.use_rsp}
			<td nowrap>{$item.rsp_price}</td>
			<td nowrap>{$item.rsp_discount}</td>	
		{else}
			<td nowrap>N/A</td>
			<td nowrap>N/A</td>
		{/if}				
	{/if}
	
	{if $item.bom_ref_num}
		<span style="color:grey;">
			[BOM PACKAGE]
		</span>
	{/if}
</td>

{/if}
{if $form.create_type==3}
	<td align=right>
	<input class="r cost uom" id=po_cost_{$item.id} name=po_cost[{$item.id}] value="{$item.po_cost|number_format:$config.global_cost_decimal_points:'.':''}" size=6 title="{$bid},{$item.id}" onchange="mf(this, {$config.global_cost_decimal_points}); positive_check(this);" {if $readonly}disabled{/if}>
	</td>
{/if}
	<td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_1_{$item.sku_item_id}" readonly name="stock_balance1[{$item.id}]" value="{$item.stock_balance1}" style="background:#ddd;">{/if}</td>	
	{if $do_type ne 'credit_sales' && $do_type ne 'open'}
        <td align=center>{if !$item.oi}<input size=5 class="small r stock_balance_2_{$item.sku_item_id}" readonly name="stock_balance2[{$item.id}]" value="{$item.stock_balance2}" style="background:#ddd;">{/if}</td>
	{/if}
    {if $config.show_parent_stock_balance}
        <td align=center>{if !$item.oi}<input size=5 class="small r parent_stock_balance_1_{$item.sku_item_id}" readonly name="parent_stock_balance1[{$item.id}]" value="{$item.parent_stock_balance1|default:'0'}" style="background:#ddd;">{/if}</td>	
        {if $do_type ne 'credit_sales' && $do_type ne 'open'}
        	<td align=center>{if !$item.oi}<input size=5 class="small r parent_stock_balance_2_{$item.sku_item_id}" readonly name="parent_stock_balance2[{$item.id}]" value="{$item.parent_stock_balance2|default:'0'}" style="background:#ddd;">{/if}</td>
        {/if}
    {/if}

{if $config.masterfile_enable_sa && $do_type ne 'transfer'}
	<td align="center">
		<select name="di_sa[{$item.id}]" onchange="check_sa(false);" class="di_sa" {if $readonly}disabled{/if}>
			<option value="">{if !$form.mst_sa}--{else}As Above{/if}</option>
			{foreach from=$sa_list name=i key=r item=sa}
				{assign var=sa_id value=$sa.id}
				<option value="{$sa_id}" {if $item.dtl_sa.$sa_id eq $sa_id}selected{/if}>{$sa.code}</option>
			{/foreach}
		</select>
	</td>
{/if}
	
	<!-- Latest Cost -->
	<td class="r" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}><input class='r' name='cost[{$item.id}]' style="background:#ddd;" size=6 value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}" readonly /></td>

	<!-- Price indicator -->
	{assign var=price_indicator value=$item.price_indicator}
	{assign var=price_indicate value=$item.price_indicate}
	{if $do_type eq 'credit_sales' && !$item.price_no_history && !$form.no_use_credit_sales_cost and !$item.price_indicator}
		{assign var=price_indicator value='Credit Sales DO'}
		{assign var=price_indicate value='Credit Sales DO'}
	{/if}
	<td align="center" class="pindicator" {if $do_type ne 'credit_sales' || !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
		<span>{$price_indicator}</span>
		<input type="hidden" name="i_price_indicate[{$item.id}]" value="{$price_indicate}">
	</td>

<!-- Price -->
<td align="right">
	{assign var=cost value=$item.cost_price|default:$item.grn_cost|default:$item.po_cost|default:$item.master_cost}
	
	{assign var=foreign_cost value=$item.foreign_cost_price|number_format:$config.global_cost_decimal_points:'.':''}
	
	{if $item.price_indicator eq 'Last DO' || $item.price_indicator eq 'Cost'} 
		{assign var=cost_decimal_points value=$config.global_cost_decimal_points}
	{else}
		{assign var=cost_decimal_points value=2}
	{/if}
	
	{assign var=display_cost_price value=$item.display_cost_price|round:4}
	{if $display_cost_price<=0}{assign var=display_cost_price value=$cost|round:4}{/if}
	{if $form.is_under_gst}
		<div style="white-space: nowrap;" class="small">
			<input type="checkbox" name="display_cost_price_is_inclusive[{$item.id}]" value="1" title="Ticked = Price is Inclusive Tax" 
			onChange="DO_MODULE.display_cost_price_is_inclusive_changed('{$item.id}');" 
			{if $item.display_cost_price_is_inclusive}checked {/if} />
			<span>
				<input size="8" name="display_cost_price[{$item.id}]" onChange="DO_MODULE.display_cost_price_changed('{$item.id}');"
				class="{if $item.price_no_history}price_no_history{/if}"
				{if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1' && $form.price_indicate ne '1'}readonly{/if}
				value="{$display_cost_price|number_format:4:'.':''}" />
			</span>
		</div>
	{/if}
	
	<div id="div_cost_price_info-{$item.id}" style="{if $form.is_under_gst && !$item.display_cost_price_is_inclusive}display:none;{/if}">
		<input class="r cost uom {if $item.price_no_history}price_no_history{/if}" id="cost_price_{$item.id}" name="cost_price[{$item.id}]" value="{if $form.is_under_gst}{$cost}{else}{$cost|number_format:$cost_decimal_points:'.':''}{/if}" 
		size="6" title="{$bid},{$item.id}" 
		{if $readonly}disabled{/if} {if $form.exchange_rate && $form.exchange_rate ne '1' && $form.price_indicate ne '1'}readonly{/if}
		type="{if $form.is_under_gst}hidden{else}text{/if}" onChange="DO_MODULE.cost_price_changed('{$item.id}', true);">
		<span id="span_cost_price_label-{$item.id}" class="small" style="color:blue;{if !$form.is_under_gst}display:none;{/if}">{$cost|number_format:4:'.':''}</span>
	</div>
	<input type="hidden" name="price_no_history[{$item.id}]" class="cls_pnh" id="price_no_history,{$item.sku_item_id}" value="{$item.price_no_history}" />
</td>

{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
	<td align=right class="foreign_cost_price" {if $hide_currency_field}style="display:none;"{/if}>
	<input class="r cost uom {if $item.price_no_history}price_no_history{/if}" id="foreign_cost_price_{$item.id}" name="foreign_cost_price[{$item.id}]" value="{$foreign_cost}" size=6 
	title="{$bid},{$item.id}" onchange="mf(this, {$config.global_cost_decimal_points}); positive_check(this); foreign_variable_recalc();" 
	{if $readonly}disabled{/if} {if $form.price_indicate eq '1'}readonly{/if}>
	</td>
{/if}

<td align="center">
<input name="master_uom[{$item.id}]" size=7 value="{$item.master_uom_code|default:'EACH'}" id="master_uom{$item.id}" readonly style="background:#ddd;">
</td>

{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
	{assign var=uom_fraction value=1}
	{assign var=uom_id value=1}
{else}
	{assign var=uom_fraction value=$item.uom_fraction}
	{assign var=uom_id value=$item.uom_id}
{/if}

<td align="center">
<input type="hidden" name="no_item" value="{$no_item}">
<input type="hidden" name="sku_item_code[{$item.id}]" id="sku_item_code{$item.id}" value="{$item.sku_item_code}">
<input type="hidden" name="uom_id[{$item.id}]" id="uom_id{$item.id}" value="{$uom_id|default:1}">
<input type="hidden" name="uom_fraction[{$item.id}]" class="uom" title="{$item.id}" id="uom_fraction{$item.id}" value="{$uom_fraction|default:1}">
<input type="hidden" class="sku_items_list" name="inp_sku_item_id[{$item.id}]" value="{$item.sku_item_id}">
<input type="hidden" name="inp_item_doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}">
<input type="hidden" name="bom_id[{$item.id}]" value="{$item.bom_id}">
<input type="hidden" name="bom_ref_num[{$item.id}]" value="{$item.bom_ref_num}">
<input type="hidden" name="bom_qty_ratio[{$item.id}]" value="{$item.bom_qty_ratio}">


<select name="sel_uom[{$item.id}]" id="sel_uom{$item.id}" onchange="DO_MODULE.item_uom_changed('{$item.id}');" {if $readonly || (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled{/if}>

{section name=i loop=$uom}
<option value="{$uom[i].id},{$uom[i].fraction}" {if $uom_id == $uom[i].id or ($uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
{/section}
</select>

{*if $config.doc_uom_control and $uom_fraction ne 1}
	<input type="hidden" name="sel_uom[{$item.id}]" value="{$uom_id|default:1}">
{/if*}
{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
	<input type="hidden" name="sel_uom[{$item.id}]" value="{$uom_id|default:1}">
{/if}
</td>


{if !$form.open_info.name}
<td align="right">{if !$item.oi}
<input name="selling_price[{$item.id}]" size=5 value="{$item.selling_price|default:'-'|number_format:2:'.':''}" class="r selling_price_{$item.sku_item_id}" title="{$item.id}" readonly style="background:#ddd;">
{/if}</td>
{/if}

{assign var=qty value=0}
{if $form.do_markup}
	{if $form.do_markup_arr.0}
	    {assign var=do_markup_per value=$form.do_markup_arr.0/100+1}
		{assign var=cost value=$cost*$do_markup_per}
		{assign var=foreign_cost value=$foreign_cost*$do_markup_per}
	{/if}
	{if $form.do_markup_arr.1}
	    {assign var=do_markup_per value=$form.do_markup_arr.1/100+1}
		{assign var=cost value=$cost*$do_markup_per}
		{assign var=foreign_cost value=$foreign_cost*$do_markup_per}
	{/if}
{/if}

{assign var=total_qty value=0}
{assign var=qty value=$qty+$item.ctn*$uom_fraction+$item.pcs}

{if $cost ne 0 && $uom_fraction ne 0}
	{assign var=$cost value=$cost/$uom_fraction}
{/if}
{if $foreign_cost ne 0 && $uom_fraction ne 0}
	{assign var=foreign_cost value=$foreign_cost/$uom_fraction}
{/if}

{* assign var=amount value=$cost*$qty *}

{assign var=tmp_amount value=$cost*$item.ctn}
{assign var=tmp_amount2 value=0}
{if $cost ne 0 && $uom_fraction ne 0}
	{assign var=tmp_amount2 value=$item.pcs/$uom_fraction*$cost}
{/if}

{assign var=gross_amount value=$tmp_amount+$tmp_amount2}
{assign var=gst_amt value=0}
{if $form.is_under_gst}
	{assign var=gst_amt value=$gross_amount*$item.gst_rate/100}
	{*{$gst_amt}<br/>*}
{/if}

{assign var=amount value=$gross_amount+$gst_amt}
{*{$amount}<br/>*}
{* assign var=foreign_amount value=$foreign_cost*$qty *}

{assign var=tmp_amount value=$foreign_cost*$item.ctn}
{assign var=tmp_amount2 value=0}
{if $foreign_cost ne 0 && $uom_fraction ne 0}
	{assign var=tmp_amount2 value=$item.pcs/$uom_fraction*$foreign_cost}
{/if}
{assign var=foreign_cost value=$tmp_amount+$tmp_amount2}

{assign var=amount value=$amount|round:2}
{assign var=foreign_amount value=$foreign_amount|round:2}
{$ttl_foreign_amount}
<!--input type=hidden name=do_item_id[{$item.id}] value="{$item.id}"-->

<td align=center nowrap>

	<table width="100%">
		<tr>
			<td align="center">
				<input style="width:40px" class="r uom inp_qty_ctn qty_fields" id="qty_ctn{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_ctn[{$item.id}]" {if $uom_fraction == 1 or $uom_id==1 or !$uom_id}disabled value="-"{else} value="{$item.ctn}"{/if} style="width:{if $item.doc_allow_decimal}40{else}30{/if}px;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); row_recalc({$item.id},{$bid});" {if $readonly}disabled{/if} onkeypress="return qty_keypressed(this, event);">
			</td>
			<td align="center">
				<input style="width:40px" class="r uom inp_qty_pcs qty_fields" id="qty_pcs{$item.id}_{$bid}" title="{$bid},{$item.id}" name="qty_pcs[{$item.id}]" style="width:{if $item.doc_allow_decimal}40{else}30{/if}px; background:#fc9;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); row_recalc({$item.id},{$bid}); {if $do_type eq 'transfer' and $config.do_use_rcv_pcs}copy_to_rcv(this);{/if}" value="{$item.pcs}" onkeypress="return qty_keypressed(this, event);" {if $readonly}disabled{/if}>
			</td>
		</tr>
	</table>
</td>

{if $do_type eq 'transfer' and $config.do_use_rcv_pcs}
	<td align="center">
		<input class="r uom" id="rcv_pcs{$item.id}" title="{$item.id}" name="rcv_pcs[{$item.id}]" style="width:{if $item.doc_allow_decimal}40{else}30{/if}px; " size=1 onchange="positive_check(this); row_recalc({$item.id},{$bid})" value="{$item.rcv_pcs}" {if $readonly}disabled{/if}>
	</td>
{/if}
<td align=right>
	<span id=row_qty{$item.id} class="uom" title=",{$item.id}">{$qty|default:0}</span>
</td>


{if $form.is_under_gst}
	{* GST Gross Amt *}
	<td class="r">
		<span id="span-gross_amt-{$item.id}">{$gross_amount|number_format:2}</span>
	</td>
	
	{* GST Selection *}
	<td align="center">
		<select name="item_gst[{$item.id}]" onchange="DO_MODULE.item_gst_changed('{$item.id}')" {if $readonly}disabled {/if}>
			{foreach from=$gst_list item=gst}
				<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}selected {/if}>
					{$gst.code} ({$gst.rate}%)
				</option>
			{/foreach}
		</select>
		<br />
		<span id="span-gst_amt-{$item.id}" class="small" style="color:blue;">
			{$item.line_gst_amt|number_format:2}
		</span>
		
		<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
		<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
		<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
	</td>
{/if}
<td align=right>
	<input type="hidden" name="line_gross_amt[{$item.id}]" value="{$item.line_gross_amt}"/>
	<input type="hidden" name="line_gst_amt[{$item.id}]" value="{$item.line_gst_amt}"/>
	<input type="hidden" name="line_amt[{$item.id}]" value="{$item.line_amt}"/>
	
	<span id="row_amount{$item.id}" class="uom row_amt" title=",{$item.id}">{$item.line_amt|default:0|number_format:2:".":""}</span>
</td>
{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
	<td align=right class="foreign_amt" {if $hide_currency_field}style="display:none;"{/if}>
		<span id="row_foreign_amount{$item.id}" class="uom row_foreign_amt" title=",{$item.id}">{$foreign_amount|default:0|number_format:2:".":""}</span>
	</td>
{/if}

{if $item.item_discount}
	{assign var=currency_multiply value=1}
	{if $is_currency_mode and $exchange_rate>0 and $form.price_indicate ne 1}
		{assign var=currency_multiply value=$currency_multiply*$exchange_rate}
	{/if}
	{get_discount_amt assign=discount_amt amt=$gross_amount discount_pattern=$item.item_discount currency_multiply=$currency_multiply}
	
	<!-- Foreign -->
	{assign var=currency_multiply value=1}
	{if $is_currency_mode and $exchange_rate>0 and $form.price_indicate eq 1}
		{assign var=currency_multiply value=$currency_multiply/$exchange_rate}
	{/if}
	{get_discount_amt assign=discount_foreign_amt amt=$foreign_amount discount_pattern=$item.item_discount currency_multiply=$currency_multiply}
{else}
    {assign var=discount_amt value=0}
{/if}

{*
{assign var=gross_inv_amt value=$gross_amount-$discount_amt}
{assign var=gross_inv_amt value=$gross_inv_amt}
{assign var=inv_gst_amt value=0}
{if $form.is_under_gst}
	{assign var=inv_gst_amt value=$gross_inv_amt*$item.gst_rate/100}
{/if}

{assign var=row_invoice_amt value=$gross_inv_amt+$inv_gst_amt}
*}

{assign var=row_invoice_foreign_amt value=$foreign_amount-$discount_foreign_amt}

{if $show_discount}
	<td class="r">
		<input name="item_discount[{$item.id}]"  {if $readonly}disabled {/if} class="r" id="inp_item_discount_{$item.id}" value="{$item.item_discount}" size="10" onChange="row_recalc({$item.id},{$bid});" />
		
		<input type="hidden" name="item_discount_amount[{$item.id}]" value="{$item.item_discount_amount}" />
		<input type="hidden" name="item_discount_amount2[{$item.id}]" value="{$item.item_discount_amount2}" />
	</td>
	
	{if $form.is_under_gst}
		{* Gross Invoice Amount *}
		<td class="r">
			<span id="span-gross_invoice_amt-{$item.id}">{$item.inv_line_gross_amt|number_format:2}</span>
		</td>
		
		{* Invoice GST *}
		<td class="r">
			<span id="span-invoice_gst_amt-{$item.id}">{$item.inv_line_gst_amt|number_format:2}</span>
		</td>
	{/if}
	
	<td class="r">
		<span id="span_row_invoice_amt_{$item.id}">{$item.inv_line_amt|number_format:2}</span>
	
		<input type="hidden" name="inv_line_gross_amt[{$item.id}]" value="{$item.inv_line_gross_amt}" />
		<input type="hidden" name="inv_line_gst_amt[{$item.id}]" value="{$item.inv_line_gst_amt}" />
		<input type="hidden" name="inv_line_amt[{$item.id}]" value="{$item.inv_line_amt}" />
		
		{* Amt 2 *}
		<input type="hidden" name="inv_line_gross_amt2[{$item.id}]" value="{$item.inv_line_gross_amt2}" />
		<input type="hidden" name="inv_line_gst_amt2[{$item.id}]" value="{$item.inv_line_gst_amt2}" />
		<input type="hidden" name="inv_line_amt2[{$item.id}]" value="{$item.inv_line_amt2}" />
		
		
	</td>
	<script>current_total_inv_amt+=float('{$item.inv_line_amt}');</script>
	{if $config.consignment_modules && is_array($config.consignment_multiple_currency) && $do_type eq 'transfer'}
		<td align="right" class="foreign_inv_amt" {if $hide_currency_field}style="display:none;"{/if}>
			<span id="span_row_foreign_invoice_amt_{$item.id}">{$row_invoice_foreign_amt|number_format:2}</span>
		</td>
	{/if}
{/if}
{if $config.do_custom_column}
	{foreach from=$config.do_custom_column key=col_key item=col}
		<td class="r"><input type="text" name="custom_col[{$item.id}][{$col_key}]" value="{$item.custom_col.$col_key}" {if $readonly}disabled {/if} /></td>
	{/foreach}
{/if}
{if $have_scan_items}
	<td class="r">{if isset($item.scan_qty)}{$item.scan_qty|qty_nf}{else}-{/if}</td>
	<td class="r {if $item.variance > 0}pv{elseif $item.variance<0}nv{/if}">
		{if isset($item.scan_qty)}
			{if $item.variance>0}+{elseif $item.variance<0}{/if}{$item.variance|qty_nf}
		{else}
			-
		{/if}
	</td>
{/if}

<script>
needCheckExit=true;
</script>