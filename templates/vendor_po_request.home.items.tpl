{*
06.05.2008 17:41:16 Saw
- remove "category" column

07.05.2008 10:30:47
- Change the Title (Balance) to (Balance (pcs))

9/7/2009 11:20:35 AM yinsee
- balance from stock-balance if connect from outside

11/4/2010 12:51:00 PM Justin
- Added replacement of Cost column with Order Price whenever found config on.
- Added a FOC column.
- Set the remark textfield stick to center.

11/11/2010 3:23:56 PM Justin
- Modified the field header from "Order Price" become "PO Cost Price".

6/16/2011 4:59:52 PM Andy
- Add "Cost Indicate" for PO create by vendor, if got config "po_vendor_use_order_price", cost indicate will be "VENDOR".

12/30/2015 11:56 AM Kee Kee
- Added ID in tr tag for check price.

2/13/2017 2:01 PM Andy
- Change loop from section to foreach.

8/9/2017 5:26 PM Andy
- Fixed the last cost should be cost price from vendor_sku_history.

11/2/2017 5:42 PM Justin
- Enhanced to lock UOM similar to PO's checking.
*}

<tr style="border:1px solid #999; padding:5px; background-color:#fe9">
<th width=20>&nbsp;</th>
<th nowrap>ARMS Code</th>
<th nowrap>ArtNo</th>
<th nowrap>Mcode</th>
<th width="50%" nowrap>Description</th>
<th>{if $config.po_vendor_use_order_price}PO Cost<br />Price{else}Last Cost{/if}</th>
<th>Balance<br>(pcs)</th>
{if $show_system_balance}
<th>30D<br>Sales</th>
<th>90D<br>Sales</th>
{/if}
<th width="5%">UOM</th>
<th>Qty</th>
<th>FOC (Pcs)</th>
<th>Remark</th>
</tr>

{foreach from=$items key=sid item=r name=fitem}
<input type="hidden" name="dept_id[{$r.sku_item_id}]" value="{$r.dept_id}">
<input type="hidden" name="selling_price[{$r.sku_item_id}]" value="{$r.sell_price_1|default:$r.sell_price_2}">
<input type="hidden" name="artno_mcode[{$r.sku_item_id}]" value="{$r.artno|default:$r.mcode}">

<tr id="{$sid}" {if $used_items.used_items.$sid==$sid}class="used small"{/if} class="small" style="background:{cycle name=f1 values="#e7e7e7,#fff"}">
<td align=right>{$smarty.foreach.fitem.iteration}.</td>
<td>{$r.sku_item_code}</td>
<td>{$r.artno|default:"&nbsp;"}</td>
<td>{$r.mcode|default:"&nbsp;"}</td>
<td>{$r.description}</td>
<td align=center>
	{if $config.po_vendor_use_order_price}
		<input id="order_price_{$r.sku_item_id}" name="order_price[{$sid}]" class="r op" onchange="this.value=round(this.value,2);" size=8 value="{$temp.order_price.$sid|ifzero:''}">
	{else}
		{$r.cost_price|number_format:2}
	{/if}
</td>

{if $show_system_balance}
<td align=center>
<input name="balance[{$r.sku_item_id}]" class="r" size=8 readonly value="{$r.stock}">
</td>
<td align=center>
{$r.l30d_pos}
</td>
<td align=center>
{$r.l90d_pos}
</td>
{else}
<td align=center>
<input name="balance[{$sid}]" class="r" size=8 onchange="mi(this);" value="{$temp.balance.$sid}">
</td>
{/if}

{if (!$config.doc_allow_edit_uom && $r.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}
	{assign var=uom_id value=$defaultUOM.id}
	{assign var=uom_code value=$defaultUOM.code}
	{assign var=uom_fraction value=$defaultUOM.fraction}
{elseif $temp.uom_code.$sid}
	{assign var=uom_id value=$temp.uom_id.$sid}
	{assign var=uom_code value=$temp.uom_code.$sid}
	{assign var=uom_fraction value=$temp.uom_fraction.$sid}
{elseif $r.uom_code_2}
	{assign var=uom_id value=$r.uom_id_2}
	{assign var=uom_code value=$r.uom_code_2}
	{assign var=uom_fraction value=$r.uom_fraction_2}
{elseif $r.uom_code_1}
	{assign var=uom_id value=$r.uom_id_1}
	{assign var=uom_code value=$r.uom_code_1}
	{assign var=uom_fraction value=$r.uom_fraction_1}
{else}
	{assign var=uom_id value=$defaultUOM.id}
	{assign var=uom_code value=$defaultUOM.code}
	{assign var=uom_fraction value=$defaultUOM.fraction}
{/if}
{if (!$config.doc_allow_edit_uom && $r.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}
	<td id="td_uom_{$sid}" valign="center"><span id="label_uom_{$sid}">&nbsp;{$uom_code}</span></td>
{else}
	<td id="td_uom_{$sid}" nowrap onclick="do_select_uom('{$sid}')" valign="center">
		<table border="0" class="uom_table" width="100%">
			<tr>
				<td><span id="label_uom_{$sid}">{$uom_code}</span></td>
				<td align="right"><img align="right" src="/ui/option_button.jpg" /></td>
			</tr>
		</table>
	</td>
{/if}

<td align="center">
<input id="qty_{$sid}" name="qty[{$sid}]" {if $config.po_vendor_use_order_price} class="r total op" {else} class="r total" {/if} size=8 onchange="mi(this);recalculate('{$sid}');" value="{$temp.qty.$sid}">
</td>
<td align="center">
<input id="foc_{$sid}" name="foc[{$sid}]" class="r total" size=8 onchange="mi(this);recalculate('{$sid}');" value="{$temp.foc.$sid}">
</td>
<td align="center"><input name="remark[{$sid}]" size="20" value="{$temp.remark.$sid}"></td>

<input type="hidden" id="uom_{$sid}" name="uom_id[{$sid}]" value="{$uom_id}">

<input type="hidden" id="uom_fraction_{$sid}" name="uom_fraction[{$sid}]" value="{$uom_fraction}">

<input type="hidden" id="price_{$sid}" name="price[{$sid}]" value="{$r.cost_price|number_format:2:".":""}">

<input type="hidden" id="cost_indicate_{$sid}" name="cost_indicate[{$sid}]" value="{$r.cost_indicate}" />

</tr>

{/foreach}
<tr class="sortbottom" style="border:1px solid #999; padding:5px; background-color:#fe9">
<th colspan={if $show_system_balance}10{else}8{/if} class="r">Total Qty : </th>
<th id="total_qty" class="r">&nbsp;</th>
<th id="total_foc" class="r">&nbsp;</th>
<th class="r">&nbsp;</th>
</tr>
