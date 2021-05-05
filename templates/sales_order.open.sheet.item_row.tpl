{*
8/10/2011 5:55:38 PM Andy
- Add total discount and row discount.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/10/2012 10:55:53 AM Andy
- Add show relationship between PO and SO.
- Add can show highlight row color.

4/20/2012 5:47:34 PM Alex
- add packing uom code after description

3/6/2013 4:57 PM Fithri
- when scan bom package item, split item into itemize
- if one of the item delete, all related bom items also delete
- if change one item qty, all other also change

4/6/2015 4:41 PM Andy
- Remove to store the GST Indicator.

3/4/2016 11:18 AM Andy
- Fix if user no privilege SHOW_COST will cause javascript error on change UOM.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

9/18/2018 3:56 PM Andy
- Someone edit "gross_amount" and "gst_amt" calculation.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

11/27/2019 3:22 PM William
- Enhance to show sku item photo when config "sales_order_show_photo" is active.

2/1/2021 10:01 AM William
- Enhance to add new column "remark" and "Reserve Qty". 

3/4/2021 
- Enhance to config and display out RSP and RSP Discount.
*}

{assign var=cost value=$item.cost_price}
{assign var=selling value=$item.selling_price}

{assign var=qty value=$item.ctn*$item.uom_fraction+$item.pcs}


{*assign var=gross_amount value=$selling/$item.uom_fraction*$qty*}
{assign var=gross_amount value=$item.line_gross_amt}

{*if $item.item_discount_amount}
	{assign var=gross_amount value=$gross_amount-$item.item_discount_amount}
{/if*}

{assign var=gst_amt value=0}
{if $form.is_under_gst}
	{*assign var=gst_amt value=$gross_amount*$item.gst_rate/100*}
	{*assign var=gst_amt value=$gst_amt|round2*}
	{assign var=gst_amt value=$item.line_gst_amt}
{/if}

{assign var=show_reserve_qty value=0}
{if (!$form.status && !$form.active && !$form.approved) || ($form.status eq '0' && $form.active eq '1' && $form.approved eq '0')}
	{assign var=show_reserve_qty value=1}
{/if}

{assign var=amount value=$gross_amount+$gst_amt}

<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="tr_item,{$item.id}" class="tr_item{if $smarty.request.highlight_item_id eq $item.sku_item_id or $smarty.request.highlight_soi_item_id eq $item.id} highlight_row{/if}">
    <td align=center nowrap width=50>
		{if !$readonly}
		<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item('{$item.id}')" align="absmiddle" alt="{$item.id}">
		{/if}
		<span class="no">{$smarty.foreach.f.iteration}.</span>
		
		<input type="hidden" name="do_qty[{$item.id}]" value="{$item.do_qty}" />
		<input type="hidden" name="item_sku_item_id[{$item.id}]" value="{$item.sku_item_id}" />
	</td>
	{if $config.sales_order_show_photo}
	<td nowrap>
		{if $item.photo}
			<img width="100" height="100" align="absmiddle" vspace="4" hspace="4" src="/thumb.php?w=100&h=100&cache=1&img={$item.photo|urlencode}" border="1" style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$item.photo}\'>')" title="View" />
		{else}
			- No Photo -
		{/if}
	</td>
	{/if}
	<td>{$item.sku_item_code|default:'&nbsp;'}</td>
	<td>{$item.artno|default:'&nbsp;'}</td>
	<td>{$item.mcode|default:'&nbsp;'}</td>
	<td>
		{$item.sku_description|default:'&nbsp;'} {include file=details.uom.tpl uom=$item.packing_uom_code}</br>
		<div>
			<span style="float: left;">Remark: </span><textarea name="item_remark[{$item.id}]">{$item.remark|escape}</textarea>
		</div>
	</td>
	{if $show_reserve_qty}
	<td align="center"><span>{$item.reserve_qty|escape}</span></td>
	{/if}
	<td align="center"><input size="6" class="small r" readonly name="stock_balance[{$item.id}]" id="stock_balance,{$item.id}" value="{$item.stock_balance}" style="background:#ddd;" sku_item_id="{$item.sku_item_id}" /></td>
	{if $config.show_parent_stock_balance}
		<td align="center"><input size="6" class="small r" readonly name="parent_stock_balance[{$item.id}]" id="parent_stock_balance,{$item.id}" value="{$item.parent_stock_balance|default:'0'}" style="background:#ddd;" /></td>
	{/if}
	
	{if $config.sales_order_show_rsp}
		{if $item.use_rsp}
			<td>{$item.rsp_price|number_format:2:".":""}</td>
			<td>{$item.rsp_discount}</td>
		{else}
			<td>N/A</td>
			<td>N/A</td>
		{/if}
	{/if}

	<td align="center" style="{if !$sessioninfo.privilege.SHOW_COST}display:none;{/if}">
		<input class="r cost" id="cost_price,{$item.id}" name="cost_price[{$item.id}]" value="{$cost|number_format:$config.global_cost_decimal_points:".":""}" size="8" onchange="row_recalc('{$item.id}');" readonly />
	</td>
	{if $config.show_parent_stock_balance}{assign var=colspan value=$colspan+1}{/if}
	<td align="center"><input class="r selling" id="selling_price,{$item.id}" name="selling_price[{$item.id}]" value="{$selling|number_format:2:".":""}" size="8" onchange="row_recalc('{$item.id}');" /></td>
	<td align="center">
	    <input type="hidden" name="uom_id[{$item.id}]" id="uom_id,{$item.id}" value="{$item.uom_id|default:1}" />
	    <input type="hidden" name="uom_fraction[{$item.id}]" id="uom_fraction,{$item.id}" value="{$item.uom_fraction|default:1}" />
	    <select name="sel_uom[{$item.id}]" onchange="uom_change(this.value,'{$item.id}');" {if $readonly or ($config.doc_uom_control)}disabled {/if}>
	        {foreach from=$uom key=uom_id item=u}
	            <option value="{$uom_id},{$u.fraction}" {if ($item.uom_id eq $uom_id) or (!$item.uom_id and $u.code eq 'EACH')}selected {/if}>{$u.code}</option>
	        {/foreach}
	    </select>
	</td>
	<td align="center" nowrap valign="center">
		<input class="r ctn" id="ctn,{$item.id}" name="ctn[{$item.id}]" {if $item.uom_fraction == 1 or $item.uom_id==1 or !$item.uom_id}disabled value="--"{else} value="{$item.ctn}"{/if} style="width:{if $item.doc_allow_decimal}45px{else}30px{/if};" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}row_recalc('{$item.id}')" {if $readonly}disabled {/if} />
	</td>
	<td align="center" nowrap valign="center">
		<input class="r pcs" id="pcs,{$item.id}" name="pcs[{$item.id}]" style="width:{if $item.doc_allow_decimal}45px{else}30px{/if}; background:#fc9;" size="{if $item.doc_allow_decimal}8{else}1{/if}" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}qty_changed(this);row_recalc('{$item.id}');" value="{$item.pcs}" {if $readonly}disabled {/if} doc_allow_decimal="{$item.doc_allow_decimal}" bom_ref_num="{$item.bom_ref_num}" bom_qty_ratio="{$item.bom_qty_ratio}" />
		<input type="hidden" name="bom_ref_num[{$item.id}]" value="{$item.bom_ref_num}" />
		<input type="hidden" name="bom_qty_ratio[{$item.id}]" value="{$item.bom_qty_ratio}" />
	</td>
	
	<!-- Row Total Qty -->
	<td align="right">
		<span id="row_qty,{$item.id}" class="row_qty">{$qty|qty_nf|default:0}</span>
	</td>
	
	<!-- Discount -->
	<td align="center">
		<input type="hidden" name="item_discount_amount[{$item.id}]" id="inp_item_discount_amount-{$item.id}" value="{$item.item_discount_amount}" />
		<input type="hidden" name="item_discount_amount2[{$item.id}]" id="inp_item_discount_amount2-{$item.id}" value="{$item.item_discount_amount2}" />
		
		<input type="text" name="item_discount[{$item.id}]" size="10" onChange="item_discount_changed('{$item.id}',this);" id="inp_item_discount-{$item.id}" value="{$item.item_discount}" />
	</td>

	{if $form.is_under_gst}
		{* GST Gross Amt *}
		<td class="r">
			<span id="span-gross_amt-{$item.id}" class="row_gross_amt">{$gross_amount|number_format:2}</span>
		</td>

		{* GST Selection *}
		<td align="center">
			<select name="item_gst[{$item.id}]" onchange="on_item_gst_changed(this, '{$item.id}')" {if $readonly}disabled {/if}>
				{foreach from=$gst_list item=gst}
					<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}selected {/if}>
						{$gst.code} ({$gst.rate}%)
					</option>
				{/foreach}
			</select>
		</td>

		{* GST Amount *}
		<td align="right">
			<span id="span-gst_amt-{$item.id}" class="row_gst_amt">
				{$gst_amt|number_format:2}
			</span>
		</td>
	{/if}

	{* Got Discount Amount 2 *}
	{* if $item.item_discount_amount2 > 0}
		{assign var=gross_amount value=$gross_amount-$item.item_discount_amount2}
		{if $form.is_under_gst}
			{assign var=gst_amt value=$gross_amount*$item.gst_rate/100}
			{assign var=gst_amt value=$gst_amt|round2}
		{/if}
		{assign var=amount value=$gross_amount+$gst_amt}
	{/if *}

	<!-- Row Total Amount-->
	<td align="right">
		<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
		<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
		<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />

		<input type="hidden" name="line_gross_amt[{$item.id}]" value="{$gross_amount}"/>
		<input type="hidden" name="line_gst_amt[{$item.id}]" value="{$gst_amt}"/>
		<input type="hidden" name="line_amt[{$item.id}]" value="{$amount}"/>

		<input type="hidden" name="line_gross_amt2[{$item.id}]" value="{$item.line_gross_amt2}"/>
		<input type="hidden" name="line_gst_amt2[{$item.id}]" value="{$item.line_gst_amt2}"/>
		<input type="hidden" name="line_amt2[{$item.id}]" value="{$item.line_amt2}"/>

		<span id="row_amount,{$item.id}" class="row_amt">{$amount|default:0|number_format:2:".":""}</span>
	</td>
	
	<!-- Delivered Qty -->
	{if $form.delivered}
	    <td align="right" class="delivered_qty">{$item.do_qty|qty_nf}</td>
	{/if}
	
	<!-- Generated to PO-->
	{if $form.approved}
		<td align="right">
			{if $item.pbid and $item.po_id}
				<a href="po.php?a=view&branch_id={$item.pbid}&id={$item.po_id}&highlight_po_item_id={$item.poi_id}" target="_blank">
					{$item.po_bcode}{$item.po_id|string_format:'%05d'}
				</a>
			{else}
				-
			{/if}
		</td>
	{/if}
</tr>
