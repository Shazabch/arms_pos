{*
8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/22/2011 4:42:59 PM Justin
- Added some hidden fields as for enhancements used.
- deleted some of the unused checking codes.

2/28/2012 3:45:32 PM Justin
- Added to show similar contents as PO when having IBT DO.

4/20/2012 5:40:56 PM Alex
- add packing uom code after description

7/13/2012 4:49:34 PM Justin
- Enhanced to have UOM control by config and packing uom fraction.

7/19/2012 6:37:34 PM Justin
- Bug fixed that while found packing uom fraction > 1, system still allow user to choose UOM.

8/15/2012 11:57 AM Justin
- Enhanced to set the item back to undelivered PO item when delete item.

9/5/2012 11:20 AM Justin
- Enhanced to disable UOM selection while found config "doc_disable_edit_uom".

10/18/2012 4:25 PM Justin
- Enhanced to do checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.
- Enhanced when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhanced when user delete one of the bom package sku, all related sku will be delete at the same time.

2/25/2013 11:36 AM Justin
- Bug fixed on system does not capture disable/enable the UOM dropdown list correctly base on config set.

4/15/2013 11:36 AM Justin
- Added new hidden field "po available_qty".

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

6/20/2014 5:00 PM Justin
- Bug fixed on item not in ARMS with special characters will not able to delete.

3/25/2015 2:24 PM Justin
- Bug fixed on the current vs suggested selling price placed in the wrong place and comparing the wrong values.

02/19/2016 13:44 Edwin
- Bug fixed on remark prompt incorrectly when two identical items are present
- Load PO items based on PO items id.

1/12/2017 4:14 PM Andy
- Enhanced to use branch_is_under_gst to check gst selling price.

5/16/2017 10:01 AM Justin
- Enhanced to show po qty base on user privilege "GRN_SHOW_PO_QTY" when SKU Manage stage.
- Bug fixed on description from item not in ARMS will stored as empty af if having special character.

5/19/2017 9:16 AM Justin
- Enhanced to reduce the SKU description textfield size for SKU not in ARMS.
- Enhanced to maintain gst information for SKU not in ARMS.

6/22/2017 2:01 PM Justin
- Enhanced to have new feature that can skip existed SKU items while calling out the multi add menu.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.
*}
{assign var=item_id value=0}
{assign var=item_desc value=0}
{assign var=item_check value=0}
{assign var=item_code value=0}

{if (!$config.doc_allow_edit_uom && $item.packing_uom_fraction ne 1) || $config.doc_disable_edit_uom}
	{assign var=uom_fraction value=1}
	{assign var=uom_id value=1}
{else}
	{assign var=uom_fraction value=$item.uom_fraction}
	{assign var=uom_id value=$item.uom_id}
{/if}

{if $doc_type eq '4'}
	{assign var=item_id value=$form.non_sku_items.code.$n|default:$item.sku_item_code}
	{assign var=item_desc value=$form.non_sku_items.description.$n}
	{assign var=item_check value=$form.non_sku_items.i_c.$n}
	{assign var=item_code value=$form.non_sku_items.code.$n|default:$item.sku_item_code}
	{assign var=item_qty value=$form.non_sku_items.qty.$n}
{else}
	{assign var=item_id value=$item.id}
	{assign var=item_desc value=$item.description}
	{assign var=item_check value=$item.item_check}
	{assign var=item_code value=$item.sku_item_code}
	{assign var=item_qty value=$item.pcs}
	<input type="hidden" name="{$doc_type}_description[{$item_id}]" title="{$item_id}" value="{$item_desc}">
{/if}

<input type="hidden" name="{$doc_type}_sku_item_code[{$item_id}]" title="{$item_id},{$item_code},{$item.link_code},{$item.artno},{$item.mcode}" class="{$doc_type}_sku_item_code" value="{$item_code|escape:'html'}">

{if ($doc_type eq '3' || $doc_type eq '4') && $smarty.request.action}
	<td nowrap width="2%" align="center">
	{if (($grr.type eq 'PO' || $grr.is_ibt_do) && $doc_type eq '3' && !$grr.allow_grn_without_po) || ($confirmed && ($doc_type eq '3' || $doc_type eq '4'))}
		{if ($grr.type eq 'PO' || $grr.is_ibt_do) && $doc_type eq '3' && !$grr.allow_grn_without_po}
			{assign var=item_check value=1}
		{/if}
		{if $item_check}
			<img src="ui/checked.gif" style="vertical-align:top;" title="This item has set into item returned.">
		{/if}
		<input type="hidden" name="{$doc_type}_item_return[{$item_id}]" id="{$doc_type}_item_return{$item_id}" value="{$item_check}">
	{else}
		<input type="checkbox" name="{$doc_type}_item_return[{$item_id}]" id="{$doc_type}_item_return{$item_id}" value="{$item_check}" {if $item_check eq '1'}checked{/if} class="{$doc_type}_item_return" title="Return {$item_code}" sku_item_id="{$item_id}" onchange="check_return('{$doc_type}', '{$item_id}')">
	{/if}
	</td>
{/if}
<td nowrap width="2%" align="right">
	{if $sessioninfo.privilege.GRN_SIV_DIV && $doc_type eq '4' && $smarty.request.action eq 'verify' && !$confirmed}
		<img src="ui/refresh.png" style="vertical-align:top;" class="clickable" onclick="ajax_recheck_nsi('{$item_code}');" align="absmiddle" title="Recheck {$item_code}">
	{/if}
	{if $doc_type > 0 && $doc_type < 5 && $smarty.request.action eq 'edit'}
		<img src="ui/cancel.png" style="vertical-align:top;" class="clickable" title="Delete Row" onclick="delete_item('{$item_id|escape:'javascript'}', '{$doc_type}', '')" align="absmiddle" alt="{$item_id}">
	{/if}
	<span class="{$doc_type}_no" id="{$doc_type}_no_{$smarty.foreach.fitem.iteration}" title="No. {$smarty.foreach.fitem.iteration}"> 
		{$smarty.foreach.fitem.iteration}.
	</span>
</td>
<input type="hidden" name="{$doc_type}_item_group[{$item_id}]" value="{$item.item_group}">
{if $doc_type ne '4'}
	{if $grr.type eq 'PO' || $grr.is_ibt_do}
		<input type="hidden" name="{$doc_type}_po_cost[{$item_id}]" value="{$item.po_cost|number_format:$config.global_cost_decimal_points:".":""}">
		<input type="hidden" id="{$doc_type}_po_item_id[{$item_id}]" name="{$doc_type}_po_item_id[{$item_id}]" value="{$item.po_item_id}">
	{/if}
	<input type="hidden" name="{$doc_type}_uom_id[{$item_id}]" value="{$uom_id|default:1}">
	<input type="hidden" name="{$doc_type}_uom_fraction[{$item_id}]" value="{$uom_fraction|default:1}">
	<input type="hidden" name="{$doc_type}_sku_item_id[{$item_id}]" title="{$item_id}" value="{$item.sku_item_id}" class="{$doc_type}_sku_item_id">
	<input type="hidden" name="{$doc_type}_sku_id[{$item_id}]" value="{$item.sku_id}">
	<input type="hidden" name="{$doc_type}_selling_uom_id[{$item_id}]" value="1">
	<input type="hidden" name="{$doc_type}_selling_uomf[{$item_id}]" value="1">
	<input type="hidden" name="{$doc_type}_master_uom_id[{$item_id}]" value="{$item.master_uom_id}" />
	<input type="hidden" name="{$doc_type}_master_uom_fraction[{$item_id}]" value="{$item.packing_uom_fraction}" />
	<input type="hidden" name="{$doc_type}_selling_price[{$item_id}]" value="{$item.selling_price|number_format:2:'.':''}">
	<input type="hidden" name="{$doc_type}_curr_selling_price[{$item_id}]" value="{$item.curr_selling_price|number_format:2}">	
	<input type="hidden" name="{$doc_type}_from_isi[{$item_id}]" value="{$item.from_isi|default:0}">
	<input type="hidden" name="{$doc_type}_item_seq[{$item_id}]" value="{$item.item_seq|default:0}">
	<input type="hidden" name="{$doc_type}_doc_allow_decimal[{$item_id}]" value="{$item.doc_allow_decimal|default:0}">
	<input type="hidden" name="{$doc_type}_bom_ref_num[{$item_id}]" value="{$item.bom_ref_num|default:0}" class="bom_ref_num_grp_{$item.bom_ref_num|default:0}" item_id="{$item_id}" doc_type="{$doc_type}">
	<input type="hidden" name="{$doc_type}_bom_qty_ratio[{$item_id}]" value="{$item.bom_qty_ratio|default:0}">
	<input type="hidden" name="{$doc_type}_available_po_qty[{$item_id}]" value="{$item.available_po_qty|default:0}">
	{if $form.is_under_gst}
		<input type="hidden" name="{$doc_type}_gst_id[{$item_id}]" value="{$item.gst_id}">
		<input type="hidden" name="{$doc_type}_gst_code[{$item_id}]" value="{$item.gst_code}">
		<input type="hidden" name="{$doc_type}_gst_rate[{$item_id}]" value="{$item.gst_rate}">
	{/if}
	{if $form.branch_is_under_gst}
		<input type="hidden" name="{$doc_type}_selling_gst_id[{$item_id}]" value="{$item.selling_gst_id}">
		<input type="hidden" name="{$doc_type}_selling_gst_code[{$item_id}]" value="{$item.selling_gst_code}">
		<input type="hidden" name="{$doc_type}_selling_gst_rate[{$item_id}]" value="{$item.selling_gst_rate}">
		<input type="hidden" name="{$doc_type}_gst_selling_price[{$item_id}]" value="{$item.gst_selling_price}">
	{/if}

	<td>{$item.sku_item_code}</td>
	<td align="center">{$item.artno|default:"-"}</td>
	<td align="center">{$item.mcode|default:"-"}</td>
	{if $config.link_code_name && $config.docs_show_link_code}
		<td nowrap>{$item.link_code}</td>
	{/if}
	<td>{$item.description} {if $item.bom_ref_num > 0}<font color="grey">(BOM Package)</font>{/if} {include file=details.uom.tpl uom=$item.packing_uom_code}</td>
	{if $doc_type eq '3'}
		<td><input id="{$doc_type}_cost{$item_id}" name="{$doc_type}_cost[{$item_id}]" size="7" value="{$item.cost|number_format:$config.global_cost_decimal_points:".":""}" onclick="clear0(this)" onchange="mf(this, {$config.global_cost_decimal_points}); positive_check(this); recalc_row('{$item_id}', '{$doc_type}');" class="r" {if $confirmed || $smarty.request.action eq 'verify'}readonly{/if}></td>
	{else}
		<input type="hidden" name="{$doc_type}_cost[{$item_id}]" value="{$item.cost|number_format:$config.global_cost_decimal_points:".":""}">
	{/if}
	{assign var=qty value=$item.ctn*$uom_fraction+$item.pcs}
	{assign var=q value=$item.ctn+$item.pcs/$uom_fraction}
	{assign var=amt value=$q*$item.cost}
	<input type="hidden" name="{$doc_type}_amt[{$item.id}]" value="{$amt}">
	{if $doc_type ne '0'}
		{if $doc_type ne '5'}
			<td>
				{if !$confirmed && $smarty.request.action ne 'verify'}
					<select onchange="sel_uom('{$item.id}', this.value, '{$doc_type}');" id="sel_uom{$item.id}" {if (!$config.doc_allow_edit_uom && $item.packing_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled {/if}>
						{section name=i loop=$uom}
							<option value="{$uom[i].id},{$uom[i].fraction}" {if $uom[i].id == $uom_id or ($uom[i].code eq 'EACH' && !$uom_id)}selected{/if}>{$uom[i].code}</option>
						{/section}
					</select>
				{else}
					{$item.uom_code}
				{/if}
			</td>
			
			{if $smarty.request.action eq 'verify' && $doc_type eq '1' && $sessioninfo.privilege.GRN_SHOW_PO_QTY}
				<td class="r">{$item.po_ctn|qty_nf|default:0}</td>
				<td class="r">{$item.po_pcs|qty_nf|default:0}</td>
			{/if}
			
			<td class="r">
				<input type="text" id="{$doc_type}_ctn{$item_id}" name="{$doc_type}_ctn[{$item_id}]" {if $item.doc_allow_decimal}size="8"{else}size="5"{/if} value="{$item.ctn}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); recalc_row('{$item_id}', '{$doc_type}', '{$is_pi}');" class="r" {if $uom_fraction <= 1}disabled{elseif $confirmed || $smarty.request.action eq 'verify'}readonly{/if}>
			</td>
			<td class="r">
				<input type="text" id="{$doc_type}_pcs{$item_id}" name="{$doc_type}_pcs[{$item_id}]" {if $item.doc_allow_decimal}size="8"{else}size="5"{/if} value="{$item.pcs}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); recalc_row('{$item_id}', '{$doc_type}', '{$is_pi}');" class="r" {if $confirmed || $smarty.request.action eq 'verify'}readonly{/if}>
			</td>
			{if $doc_type eq '1' && $smarty.request.action eq 'verify'}
				<td class="r">
					<input type="text" id="{$doc_type}_return_ctn{$item_id}" name="{$doc_type}_return_ctn[{$item_id}]" {if $item.doc_allow_decimal}size="8"{else}size="5"{/if} value="{$item.return_ctn}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); recalc_row('{$item_id}', '{$doc_type}', '{$is_pi}', this);" class="r" {if $confirmed || $smarty.request.action eq 'edit'}readonly{elseif $uom_fraction <= 1}disabled{/if}>
				</td>
				<td class="r">
					<input type="text" id="{$doc_type}_return_pcs{$item_id}" name="{$doc_type}_return_pcs[{$item_id}]" {if $item.doc_allow_decimal}size="8"{else}size="5"{/if} value="{$item.return_pcs}" onclick="clear0(this)" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if} positive_check(this); recalc_row('{$item_id}', '{$doc_type}', '{$is_pi}', this);" class="r" {if $confirmed || $smarty.request.action eq 'edit'}readonly{/if}>
				</td>
			{else}
				<input type="hidden" id="{$doc_type}_return_ctn{$item_id}" name="{$doc_type}_return_ctn[{$item_id}]" value="{$item.return_ctn}">
				<input type="hidden" id="{$doc_type}_return_pcs{$item_id}" name="{$doc_type}_return_pcs[{$item_id}]" value="{$item.return_pcs}">
			{/if}
		{else}
			<td align="center">{$item.po_no}</td>
			<td align="center">{$item.po_date}</td>
			{if $form.branch_is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{else}
				{assign var=selling_price value=$item.selling_price}
			{/if}
			<td class="r">{$item.curr_selling_price|number_format:2}</td>
			<td class="r">{$selling_price|number_format:2}</td>
			{if $config.grn_check_selling_price}
				<td>
					<input type="text" id="{$doc_type}_reason{$item_id}" name="{$doc_type}_reason[{$item_id}]" size="50" value="{$item.reason}" {if $div3_id}readonly{/if}>
				</td>
			{/if}
		{/if}
	{/if}
	{if ($grr.type eq 'PO' || $grr.is_ibt_do) && $doc_type < 3}
		{if $doc_type ne '0'}
			<td nowrap align="left">
				{assign var=qty_var value=$item.ctn*$uom_fraction+$item.pcs-$item.return_ctn*$uom_fraction-$item.return_pcs-$item.po_qty}
				<input type="hidden" name="{$doc_type}_qty_var[{$item_id}]" title="{$item_id}" value="{$qty_var}">
				{if !$is_pi}
					<div id="{$doc_type}_qty_var{$item_id}" class="r">
						<span class={if $qty_var>0}pv{elseif $qty_var<0}nv{else}r{/if}>{if $qty_var>0}Over{elseif $qty_var<0}Short{/if}</span>
						{* if $qty_var>0 && $item_check ne '1' && $item.po_item_id ne '0' && $item.po_qty ne '0' && !$cu_id}
							<img width="18" src="ui/lorry.png" style="vertical-align:top;" class="clickable" title="Add Return Row" onclick="ajax_add_variance_item('{$item.sku_item_id}', '{$qty_var}', '{$item.id|default:item_id}', '{$doc_type}')" align="absmiddle" alt="{$item_id}">
						{/if *}
					</div>
				{/if}
			</td>
		{else}
			<input type="hidden" id="{$doc_type}_po_ctn{$item_id}" name="{$doc_type}_po_ctn[{$item_id}]" value="{$item.po_ctn|default:0}">
			<input type="hidden" id="{$doc_type}_po_pcs{$item_id}" name="{$doc_type}_po_pcs[{$item_id}]" value="{$item.po_pcs|default:0}">
			<td class="r">{$item.po_ctn|qty_nf|default:0}</td>
			<td class="r">{$item.po_pcs|qty_nf|default:0}</td>
		{/if}
		<input type="hidden" id="{$doc_type}_po_qty{$item_id}" name="{$doc_type}_po_qty[{$item_id}]" size="5" value="{$item.po_qty|default:0}" class="r" readonly>
	{/if}
{else}
	<td>
		{$item_code}
	</td>
	<td><input type="text" id="{$doc_type}_description{$item_id}" name="{$doc_type}_description[{$item_id}]" size="200" value="{$item_desc|escape:'html'}" onclick="clear0(this)" onchange="uc(this);" {if $confirmed || $smarty.request.action eq 'verify'}readonly{/if}></td>
	<td><input type="text" id="{$doc_type}_cost{$item_id}" name="{$doc_type}_cost[{$item_id}]" size="7" value="{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points:".":""}" onclick="clear0(this)" onchange="mf(this, {$config.global_cost_decimal_points});" class="r" {if $confirmed || $smarty.request.action eq 'verify'}readonly{/if}></td>
	<td class="r" width="5%">
		<input type="text" id="{$doc_type}_pcs{$item_id}" name="{$doc_type}_pcs[{$item_id}]" size="5" value="{$item_qty}" onclick="clear0(this)" onchange="mi(this); positive_check(this); recalc_row('{$item_id}', '{$doc_type}');" class="r" {if $confirmed || $smarty.request.action eq 'verify'}readonly{/if}>
	</td>
	{if $form.is_under_gst}
		<input type="hidden" name="{$doc_type}_gst_id[{$item_id}]" value="{$form.non_sku_items.gst_id.$n}">
		<input type="hidden" name="{$doc_type}_gst_code[{$item_id}]" value="{$form.non_sku_items.gst_code.$n}">
		<input type="hidden" name="{$doc_type}_gst_rate[{$item_id}]" value="{$form.non_sku_items.gst_rate.$n}">
		<input type="hidden" name="{$doc_type}_doc_no[{$item_id}]" value="{$form.non_sku_items.doc_no.$n}">
		<input type="hidden" name="{$doc_type}_doc_date[{$item_id}]" value="{$form.non_sku_items.doc_date.$n}">
	{/if}
{/if}
