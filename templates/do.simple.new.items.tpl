{*
*}
<td align="center" nowrap width="50" class="{if $item.bom_ref_num}td_bom_ref_num-{$item.bom_ref_num}{/if}" title="{$item_id}">
	{if ($form.status<1 || $form.status eq '2') && !$form.approval_screen && !$readonly}
		<img src="ui/remove16.png" class="clickable" title="Delete Row" onclick="DO_PREPARATION_MODULE.delete_item({$item_id})" align="absmiddle" alt="{$item_id}">
	{/if}

	<span class="no" id="no_{$smarty.foreach.fitem.iteration}">
	{$smarty.foreach.fitem.iteration}.</span>
</td>
<td align="center" id="sku_item_code-{$item_id}">
	{if $item.is_first_bom}
		<font style="color:blue;">
			{$item.bom_parent_si_code}
		</font>
		<br />
	{/if}
	{$item.sku_item_code}
</td>
<td nowrap id="artno_mcode-{$item_id}">
	{if $item.is_first_bom}
		<font style="color:blue;">
			{$item.bom_parent_si_artno_mcode}
		</font>
		<br />
	{/if}
	{$item.artno}
</td>
<td nowrap>
	{$item.mcode}
</td>
<td id="item_desc-{$item_id}">
	{if $item.is_first_bom}
		<font style="color:blue;">
			{$item.bom_parent_si_desc}
		</font>
		<br />
	{/if}
	{$item.description} {include file=details.uom.tpl uom=$item.master_uom_code}
	{if $item.bom_ref_num}
		<span style="color:grey;">
			[BOM PACKAGE]
		</span>
	{/if}
</td>

<td align="center">
	{if ((!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom) && !$readonly}
		{assign var=uom_fraction value=1}
		{assign var=uom_id value=1}
	{else}
		{assign var=uom_fraction value=$item.uom_fraction}
		{assign var=uom_id value=$item.uom_id}
	{/if}
	<select name="sel_uom[{$item_id}]" id="sel_uom{$item_id}" onchange="DO_PREPARATION_MODULE.item_uom_changed('{$item_id}');" {if $readonly || (!$config.doc_allow_edit_uom && $item.master_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled{/if}>

		{foreach from=$uom name=i item=u}
			<option value="{$u.id},{$u.fraction}" {if $uom_id == $u.id or ($uom_id==0 and $u.code eq 'EACH')}selected{/if}>{$u.code}</option>
		{/foreach}
	</select>
</td>

{assign var=qty value=0}

{if $form.deliver_branch}
	{assign var=row_ctn value=0}
	{assign var=row_pcs value=0}
	{assign var=do_branch_id_count value=0}

	{foreach from=$branch name=i item=b}
		{if in_array($b.id,$form.deliver_branch)}
			{assign var=do_branch_id_count value=$do_branch_id_count+1}
			{assign var=bid value=`$b.id`}
			{assign var=b_name value=`$b.report_prefix`}

			{assign var=qty value=$qty+$item.ctn_allocation.$bid*$uom_fraction+$item.pcs_allocation.$bid}
			
			{assign var=row_ctn value=$row_ctn+$item.ctn_allocation.$bid}
			{assign var=row_pcs value=$row_pcs+$item.pcs_allocation.$bid}
				 
			<td align="center" nowrap valign="top">
				<input id="qty_ctn{$item_id}_{$bid}" title="{$bid},{$item_id}" class="r uom inp_qty_ctn" name="qty_ctn[{$item_id}][{$bid}]" {if $uom_fraction == 1 or $uom_id==1 or !$uom_id}disabled value="--"{else} value="{$item.ctn_allocation.$bid}"{/if} style="width:40px;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); DO_PREPARATION_MODULE.calc_all_items();">
				<input id="qty_pcs{$item_id}_{$bid}" title="{$bid},{$item_id}" class="r uom inp_qty_pcs" name="qty_pcs[{$item_id}][{$bid}]" style="width:40px; background:#fc9;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); DO_PREPARATION_MODULE.calc_all_items();" value="{$item.pcs_allocation.$bid}">
			</td>
		{/if}
	{/foreach}
{else}
	{if $do_type eq "transfer"}
		{assign var=bid value=$form.do_branch_id|default:"0"}
	{/if}
	<td align="center" nowrap valign="top">
		<input class="r uom inp_qty_ctn" id="qty_ctn{$item_id}_{$bid}" title="{$bid},{$item_id}" name="qty_ctn[{$item_id}]" {if $uom_fraction == 1 or $uom_id==1 or !$uom_id}disabled value="-"{else} value="{$item.ctn}"{/if} style="width:40px;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); DO_PREPARATION_MODULE.calc_all_items();" {if $readonly}disabled{/if}>

		<input class="r uom inp_qty_pcs" id="qty_pcs{$item_id}_{$bid}" title="{$bid},{$item_id}" name="qty_pcs[{$item_id}]" style="width:40px; background:#fc9;" size=1 onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if}; positive_check(this); DO_MODULE.qty_changed(this); DO_PREPARATION_MODULE.calc_all_items();" value="{$item.pcs}" {if $readonly}disabled{/if}>
	</td>
{/if}

<td align="right">
	<input type="hidden" name="sku_item_code[{$item_id}]" id="sku_item_code{$item_id}" value="{$item.sku_item_code}">
	<input type="hidden" name="sku_id[{$item_id}]" id="sku_id{$item_id}" value="{$item.sku_id}">
	<input type="hidden" name="uom_id[{$item_id}]" id="uom_id{$item_id}" value="{$uom_id|default:1}">
	<input type="hidden" name="uom_fraction[{$item_id}]" class="uom_fraction" title="{$item_id}" id="uom_fraction{$item_id}" value="{$uom_fraction|default:1}">
	<input type="hidden" class="sku_items_list" name="inp_sku_item_id[{$item_id}]" value="{$item.sku_item_id}" title=",{$item_id}">
	<input type="hidden" name="inp_sku_id[{$item_id}]" value="{$item.sku_id}" title=",{$item_id}">
	<input type="hidden" name="inp_item_doc_allow_decimal[{$item_id}]" value="{$item.doc_allow_decimal}">
	<input type="hidden" name="is_first_bom[{$item_id}]" value="{$item.is_first_bom}">
	<input type="hidden" name="bom_id[{$item_id}]" value="{$item.bom_id}">
	<input type="hidden" name="bom_ref_num[{$item_id}]" value="{$item.bom_ref_num}">
	<input type="hidden" name="bom_qty_ratio[{$item_id}]" value="{$item.bom_qty_ratio}">
	<input type="hidden" name="item_real_id[{$item_id}]" value="{$item.id}">
	<input type="hidden" name="artno_mcode[{$item_id}]" value="{$item.artno_mcode|escape:'html'}">
	<span id="row_qty{$item_id}" class="uom" title=",{$item_id}">{$qty|default:0}</span>
</td>