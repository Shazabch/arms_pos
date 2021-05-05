{*
7/16/2009 3:24:30 PM Andy
- add stock balance column

8/7/2009 3:03:59 PM Andy
- if $config.doc_uom_control equal true and master uom id not equal 1, lock uom selection

11/5/2009 4:57:14 PM Andy
- add invoice discount. per sheet and per item

11/20/2009 12:53:48 PM Andy
- Change price type color

6/17/2010 3:10:10 PM Justin
- Disabled any unrelated fields such as Stock Balance, Selling Price and etc if it is a Open Item.

6/22/2010 12:25:06 PM Justin
- Changed the display style not to display Description and Article / MCode while the status is equal to reading purpose only for example DO Checkout module.
- Hidden the "Selling Price" while it is Open Item for all DO modules.

7/20/2010 4:16:09 PM 2:48:02 PM Justin
- Fixed the division errors while adding a empty or open items.

6/20/2011 5:34:02 PM Alex
- add latest cost and price indicator column

8/5/2011 4:21:59 PM Andy
- Change DO Invoice Discount format.

8/19/2011 2:59:21 PM Justin
- Fixed the division by zero error message.

10/3/2011 5:55:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- Modified to resize the ctn and pcs fields when it's allow decimal points.

2/8/2012 4:48:43 PM Justin
- Added new column "Master UOM" and renamed the current UOM become "DO UOM".

3/30/2012 11:10:43 AM Justin
- Added to set require to show confirmation when user about to leave the page.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

4/25/2012 2:30:21 PM Alex
- change id to class for stock balance, price type and price to fix previous bugs of unable to change 2 items or above in a list 

3/1/2013 5:20 PM Justin
- Enhanced to enable/disable UOM field base on config set.

3/6/2013 11:19 AM Justin
- Bug fixed on system always show "EACH" on UOM even the DO items is having other fraction.

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

5/16/2013 9:53 AM Andy
- Make sku item id input have name.

5/22/2014 10:33 AM Justin
- Bug fixed on PO Cost always zero.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.
- Create a new JS class DO_MODULE and merge some JS.

6/19/2015 3:41 PM Andy
- Fix cost price rounding issue when no GST.

7/29/2015 10:51 AM Joo Chia
- Add in open branch qty form button.
- Assign id for column to allow to access from branch qty form.

2/17/2016 10:37 AM Andy
- Fix invoice amount rounding issue on view screen.
- Fix DO amount rounding issue on view screen.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

5/29/2017 3:46 PM Justin
- Enhanced to have BOM function.

7/19/2017 10:28 AM Qiu Ying
- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items

3:06 PM 11/21/2018 Justin
- Enhanced to have new function to auto focus on ctn and pcs.

12/6/2018 5:37 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

8/28/2019 9:53 AM William
- Enhanced to added new column for new config "do_custom_column".

11/27/2019 3:25 PM William
- Enhance to show sku item photo when config "do_show_photo" is active.

06/23/2020 04:46 Sheila
- Fixed table boxes alignment and width.

03/09/2021 09:43 AM Ian
- Enhanced to add column rsp price & rsp discount when config "do_show_rsp" is active.
*}

{assign var=row_no value=$item_n|default:$smarty.foreach.fitem.iteration}

<td align=center nowrap width=50 class="{if $item.bom_ref_num}td_bom_ref_num-{$item.bom_ref_num}{/if}" title="{$item.id}">
{if $form.create_type ne '3' && ($form.status<1 || $form.status eq '2') && !$form.approval_screen && !$readonly}
	<img src="ui/remove16.png" class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle alt="{$item.id}">
{/if}
{if !$readonly && ($form.status<1 || $form.status eq '2') and $form.do_type eq 'transfer'}
	<img src="ui/icons/application_view_columns.png" class="clickable" title="Open Branch Qty Form" onclick="DO_MODULE.open_branch_qty_form('{$item.id}');" align="absmiddle" />
{/if}

<span class="no" id="no_{$smarty.foreach.fitem.iteration}">
{$smarty.foreach.fitem.iteration}.</span>

<input type="hidden" name="master_uom_id[{$item.id}]" value="{$item.master_uom_id}" />
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
{if $item.oi && !$readonly}
	<input type="hidden" name="oi_val[{$item.id}]" value="{$item.oi}">
	<td align=center></td>
	<td nowrap><input type="text" name="artno_mcode[{$item.id}]" value="{$item.artno_mcode}" /></td>
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
	<td nowrap id="artno_mcode-{$item.id}">
		{if $item.is_first_bom}
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
	<td nowrap id="mcode-{$item.id}">
		{if $item.is_first_bom}
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
	<td id="item_desc-{$item.id}">
		{if $item.is_first_bom}
			<font style="color:blue;">
				{$item.bom_parent_si_desc}
			</font>
			<br />
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
	<input class="r cost uom" id=po_cost_{$item.id} name=po_cost[{$item.id}] value="{$item.po_cost|number_format:$config.global_cost_decimal_points:'.':''}" size=6 title="{$bid},{$item.id}" onchange="mf(this, {$config.global_cost_decimal_points});" {if $readonly}disabled{/if}>
	</td>
{/if}
<td align=center>
{if !$item.oi}
	<input size=5 class="small r" readonly name="stock_balance1[{$item.id}]" id="stock_balance,1,{$item.sku_item_id}" value="{$item.stock_balance1}" style="background:#ddd;">
{/if}
</td>
{if $config.show_parent_stock_balance}
    <td align=center>
		{if !$item.oi}
			<input size=5 class="small r" readonly name="parent_stock_balance1[{$item.id}]" id="parent_stock_balance,1,{$item.sku_item_id}" value="{$item.parent_stock_balance1|default:'0'}" style="background:#ddd;">
		{/if}
    </td>
{/if}
<td class="r" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
	{if !$item.oi}
	<input class='r' name='cost[{$item.id}]' style="background:#ddd;" size=6 value="{$item.cost|number_format:$config.global_cost_decimal_points:'.':''}" readonly />
	{/if}
</td>

<td align="center" {if !$item.oi}class="pindicator"{/if} {if $do_type ne 'credit_sales' || !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
	<span>{if !$item.price_no_history && $do_type eq 'credit_sales'}
			Credit Sales DO
		  {else}{$item.price_indicator}{/if}
	</span>
	<input type="hidden" name="i_price_indicate[{$item.id}]" value="{if !$item.price_no_history && $do_type eq 'credit_sales'}Credit Sales DO{else}{$item.price_indicate}{/if}">
</td>

	
<td align="right">
	{if $item.price_indicator eq 'Cost'}
		{assign var=cost_decimal_points value=$config.global_cost_decimal_points}
	{else}
		{assign var=cost_decimal_points value=2}
	{/if}

	{assign var=cost value=$item.cost_price}

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
		type="{if $form.is_under_gst}hidden{else}text{/if}" onchange="DO_MODULE.cost_price_changed('{$item.id}', true);">
		<span id="span_cost_price_label-{$item.id}" style="color:blue; {if !$form.is_under_gst}display:none;{/if}">{$cost|number_format:4:'.':''}</span>
	</div>

</td>

<td align=center>
<input name="master_uom[{$item.id}]" size=7 value="{$item.master_uom_code|default:'EACH'}" id="master_uom{$item.id}" readonly style="background:#ddd;">
</td>

{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
	{assign var=uom_fraction value=1}
	{assign var=uom_id value=1}
{else}
	{assign var=uom_fraction value=$item.uom_fraction}
	{assign var=uom_id value=$item.uom_id}
{/if}

<td align=center>
<input type="hidden" name="no_item" value="{$no_item}">
<input type="hidden" name="sku_item_code[{$item.id}]" id="sku_item_code{$item.id}" value="{$item.sku_item_code}">
<input type="hidden" name="uom_id[{$item.id}]" id="uom_id{$item.id}" value="{$uom_id|default:1}">
<input type="hidden" name="uom_fraction[{$item.id}]" class="uom" title="{$item.id}" id="uom_fraction{$item.id}" value="{$uom_fraction|default:1}">
<input type="hidden" class="sku_items_list" name="inp_sku_item_id[{$item.id}]" value="{$item.sku_item_id}">
<input type="hidden" name="inp_item_doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}">
<input type="hidden" name="bom_id[{$item.id}]" value="{$item.bom_id}">
<input type="hidden" name="bom_ref_num[{$item.id}]" value="{$item.bom_ref_num}">
<input type="hidden" name="bom_qty_ratio[{$item.id}]" value="{$item.bom_qty_ratio}">

<select name="sel_uom[{$item.id}]" id="sel_uom{$item.id}" onchange="DO_MODULE.item_uom_changed('{$item.id}');" 
{if $readonly || (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled {/if}>
{section name=i loop=$uom}
<option value="{$uom[i].id},{$uom[i].fraction}" {if $uom_id == $uom[i].id or ($uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
{/section}
</select>

{*if $config.doc_uom_control and $item.master_uom_id ne 1}
	<input type="hidden" name="sel_uom[{$item.id}]" value="{$uom_id|default:1}">
{/if*}
{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
	<input type="hidden" name="sel_uom[{$item.id}]" value="{$uom_id|default:1}">
{/if}
</td>

{assign var=qty value=0}
{assign var=foc value=0}
{assign var=total_qty value=0}
{assign var=total_foc value=0}

<!-- do markup percent -->
{if $form.do_markup}
	{if $form.do_markup_arr.0}
	    {assign var=do_markup_per value=$form.do_markup_arr.0/100+1}
		{assign var=cost value=$cost*$do_markup_per}
	{/if}
	{if $form.do_markup_arr.1}
	    {assign var=do_markup_per value=$form.do_markup_arr.1/100+1}
		{assign var=cost value=$cost*$do_markup_per}
	{/if}
{/if}

{if $form.deliver_branch}
{assign var=row_ctn value=0}
{assign var=row_pcs value=0}
{assign var=do_branch_id_count value=0}

{section name=i loop=$branch}
	{if in_array($branch[i].id,$form.deliver_branch)}
		{assign var=do_branch_id_count value=$do_branch_id_count+1}
		{assign var=bid value=`$branch[i].id`}
		{assign var=b_name value=`$branch[i].report_prefix`}

		{assign var=qty value=$qty+$item.ctn_allocation.$bid*$uom_fraction+$item.pcs_allocation.$bid}
		
		{assign var=row_ctn value=$row_ctn+$item.ctn_allocation.$bid}
		{assign var=row_pcs value=$row_pcs+$item.pcs_allocation.$bid}
		
		{if $cost != 0 && $uom_fraction != 0}
			{assign var=amount value=$cost/$uom_fraction}
		{/if}
             
		<td align=center nowrap valign=top>

			<table width="100%">
				<tr>
					<td align="center">
					{*<input class="tbl_col_ctn" style="background:#fff;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" value="Ctn" disabled="">*}

						<input id="qty_ctn{$item.id}_{$bid}" title="{$bid},{$item.id}" class="r uom inp_qty_ctn qty_fields" name="qty_ctn[{$item.id}][{$bid}]" {if $uom_fraction == 1 or $uom_id==1 or !$uom_id}disabled value="--"{else} value="{$item.ctn_allocation.$bid}"{/if} style="width:{if $item.doc_allow_decimal}40{else}40{/if}px; font-size: 10px;padding-right: 2px;cursor: context-menu" size=3 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); row_recalc({$item.id},{$bid});" onkeypress="return qty_keypressed(this, event);">

					</td>
					<td align="center">
					{*<input class="tbl_col_ctn" style="background:#fc9;border:1px solid #ccc;width: 40px;text-align:center;font-size: 11px;font-weight: bold;padding-right: 2px;cursor: context-menu;" class="tbl_col_pcs" value="Pcs" disabled="">*}

					<input id="qty_pcs{$item.id}_{$bid}" title="{$bid},{$item.id}" class="r uom inp_qty_pcs qty_fields" name="qty_pcs[{$item.id}][{$bid}]" style="width:{if $item.doc_allow_decimal}40{else}40{/if}px; background:#fc9; font-size: 10px;padding-right: 2px;cursor: context-menu" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); row_recalc({$item.id},{$bid});" value="{$item.pcs_allocation.$bid}" onkeypress="return qty_keypressed(this, event);">
					</td>
				</tr>
			</table>
            <table>
                {if !$form.open_info.name && !$item.oi}
                    <tr><td align=center>S.P</td>
                        <td align=center>
                            {if $item.price_type.$bid and $config.do_auto_split_by_price_type}<sup style="color:blue">({$item.price_type.$bid})</sup>{/if}
                            <input name="selling_price[{$item.id}][{$bid}]" value="{if $item.selling_price_allocation.$bid}{$item.selling_price_allocation.$bid}{else}-{/if}" class="r" readonly style="background:#ddd;" size=6>
                            <input type="hidden" name="price_type[{$item.id}][{$bid}]" value="{$item.price_type.$bid}" />
                    </td></tr>
                {/if}
                {if !$item.oi}
                    <tr><td align=center>Stock</td>
                        <td align=center>
                            <input id="stock_balance2,{$item.id},{$bid}"  title="{$bid},{$item.id}" class="r" name="stock_balance2_allocation[{$item.id}][{$bid}]" readonly style="background:#ddd;" size=6 value="{$item.stock_balance2_allocation.$bid}">
                    </td></tr>
                {/if}
                {if !$item.oi && $config.show_parent_stock_balance}
                    <tr><td align=center>Parent Stock</td>
                        <td align=center>
                            <input id="parent_stock_balance2,{$item.id},{$bid}"  title="parent,{$bid},{$item.id}" class="r" name="parent_stock_balance2_allocation[{$item.id}][{$bid}]" readonly style="background:#ddd;" size=6 value="{$item.parent_stock_balance2_allocation.$bid|default:'0'}">
                    </td></tr>
                {/if}
            </table>
		</td>
	{/if}
{/section}

{assign var=tmp_amount value=$cost*$row_ctn}
{assign var=tmp_amount2 value=0}
{if $uom_fraction ne 0 && $cost ne 0}
	{assign var=tmp_amount2 value=$row_pcs/$uom_fraction*$cost}
{/if}

{assign var=gross_amount value=$tmp_amount+$tmp_amount2}
{assign var=gst_amt value=0}
{if $form.is_under_gst}
	{assign var=gst_amt value=$gross_amount*$item.gst_rate/100}
	{*{$gst_amt}<br/>*}
{/if}

{assign var=amount value=$gross_amount+$gst_amt}
{*{$amount}<br/>*}

{assign var=amount value=$amount|round2}
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
		<span id="span-gst_amt-{$item.id}" style="color:blue;">
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
	
	<span id=row_amount{$item.id} class="uom row_amt" title=",{$item.id}">{$item.line_amt|default:0|number_format:2:".":""}</span>
</td>

{if $item.item_discount>0}
	{get_discount_amt assign=discount_amt amt=$gross_amount discount_pattern=$item.item_discount discount_by_value_multiply=$do_branch_id_count}
{else}
    {assign var=discount_amt value=0}
{/if}

{*
{assign var=gross_inv_amt value=$gross_amount-$discount_amt}
{assign var=gross_inv_amt value=$gross_inv_amt|round2}
{assign var=inv_gst_amt value=0}
{if $form.is_under_gst}
	{assign var=inv_gst_amt value=$gross_inv_amt*$item.gst_rate/100}
{/if}

{assign var=row_invoice_amt value=$gross_inv_amt+$inv_gst_amt}
*}

{if $show_discount}
	<td class="r">
		<input name="item_discount[{$item.id}]" class="r" {if $readonly}disabled {/if} id="inp_item_discount_{$item.id}" value="{$item.item_discount}" size="10" onChange="row_recalc({$item.id},{$bid});" />
		
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
{/if}
{/if}
{if $config.do_custom_column}
	{foreach from=$config.do_custom_column key=col_key item=col}
		<td class="r"><input type="text" name="custom_col[{$item.id}][{$col_key}]" value="{$item.custom_col.$col_key}" /></td>
	{/foreach}
{/if}
<script>
needCheckExit=true;
</script>