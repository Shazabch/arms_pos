{*
6/11/2007 1:33:19 PM (gary) 
- added cost call from grn->po->master 

6/12/2007 12:27:04 PM (gary)
- added popup cost history for each row
 
7/10/2007 2:25:18 PM gary
- to calculate the FOC and get correct total selling (line 155)
 
7/24/2007 10:41:26 AM yinsee
- hide ARTNO_MCODE column
 
9/13/2007 11:58:35 AM yinsee
- replace selling uom with "EACH" (fixed, cannot change) 
 
10/23/2007 4:37:51 PM gary
- add cost indicate column. 

11/28/2007 11:08:57 AM gary
- selling price UOM allow to change.

1/8/2008 11:12:34 AM gary
- config the selling price set as readonly or can modify.

2/19/2008 6:40:01 PM gary
- if selling price readonly , fix the selling UOM.

2/22/2008 4:34:58 PM gary
- not allow to add FOC items for the same FOC items.
*}
{strip}
{assign var=item_id value=$item.id}
<td nowrap>
<input type=hidden name="is_foc[{$sheet_n}][{$item.id}]" id=is_foc{$item.id} value="{$item.is_foc}">
<span id=count{$item.id}>
{if $item_n ne ''}
{$item_n}.
{else}
{$smarty.foreach.fitem.iteration}.
{/if}
</span>
</td>

<td nowrap>
	<img src=ui/remove16.png class=clickable title="Delete Row" onclick="delete_item({$item.id})" align=absmiddle>
	{if !$item.remark}<img src=ui/note16.png id=snote{$item.id} title="Add Remark" class="clickable" onclick="showdiv('note{$item.id}');this.style.display='none';$('rem{$item.id}').focus();" align=absmiddle>{/if}
	{if !$item.remark2}<img src=ui/inote16.png id=snote2{$item.id} title="Add Internal Remark" class="clickable" onclick="showdiv('note2{$item.id}');this.style.display='none';$('rem2{$item.id}').focus();" align=absmiddle>{/if}
	{if $item.is_foc}<img src=ui/foc.png id="focicon{$item.id}" class=clickable title="Edit FOC costing" onclick="edit_foc({$sheet_n},{$item.id},{$item.sku_item_id})">{/if}
	<img src=/ui/table_multiple.png title="History" onclick="get_price_history_row(this,{$item.sku_item_id})" align=absmiddle>
</td>
{/strip}

<td {if $item.is_foc}style="color:#090"{/if} nowrap>
	<input name="artno_mcode[{$sheet_n}][{$item.id}]" value="{$item.artno_mcode}" type=hidden>
	{$item.artno_mcode}
</td>

<td {if $item.is_foc}style="color:#090"{/if}>
	{if $item.is_foc}<sup id="foc_id{$item.id}" style="color:#f00">{$item.foc_id}</sup>{/if}
	<span title="{$item.sku_item_code}">{$item.description}</span>
	<sup style="color:#f00" id="foc_annotation{$item.id}">{$foc_annotations.$item_id}</sup>
	<!-- supplier remark -->
	<div id=note{$item.id} style="white-space:nowrap;{if !$item.remark}display:none{/if}">
	<img src=ui/note16.png align=absmiddle>&nbsp;<input style="font:10px Arial;width:150px;" id=rem{$item.id} name="item_remark[{$sheet_n}][{$item.id}]" value="{$item.remark|escape}" onblur="if(this.value==''){literal}{{/literal}hidediv('note{$item.id}');showdiv('snote{$item.id}'){literal}}{/literal}" onclick="this.select()" maxlength=100>
	</div>
	<!-- internal remark -->
	<div id=note2{$item.id} style="white-space:nowrap;{if !$item.remark2}display:none{/if}">
	<img src=ui/inote16.png align=absmiddle>&nbsp;<input style="font:10px Arial;width:150px;" id=rem2{$item.id} name="item_remark2[{$sheet_n}][{$item.id}]" value="{$item.remark2|escape}" onblur="if(this.value==''){literal}{{/literal}hidediv('note2{$item.id}');showdiv('snote2{$item.id}'){literal}}{/literal}" onclick="this.select()" maxlength=100>
	</div>
</td>
{if !$form.deliver_to}
<td>
	<input id="sp{$item.id}" name="selling_price[{$sheet_n}][{$item.id}]" size=4 value="{$item.selling_price|number_format:2:".":""}" onchange="this.value=round2(this.value);row_recalc({$item.id})" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if}>
</td>
{/if}
<td>
	<!--
	//hide it to active the selling UOM
	<input type=hidden name=selling_uom_id[{$sheet_n}][{$item.id}] value="{$item.selling_uom_id}">
	<input type=hidden id=suomf{$item.id} name=selling_uom_fraction[{$sheet_n}][{$item.id}] value="{$item.selling_uom_fraction|default:1}">
	EACH
	-->
	<input type=hidden name=selling_uom_id[{$sheet_n}][{$item.id}] value="{$item.selling_uom_id}">
	<input type=hidden id=suomf{$item.id} name=selling_uom_fraction[{$sheet_n}][{$item.id}] value="{$item.selling_uom_fraction|default:1}">
	
	{if $config.po_selling_price_readonly}
		EACH
	{else}
		<select name=selling_uom[{$sheet_n}][{$item.id}] id="suom{$item.id}" onchange="uom_change(this.value,'selling','{$sheet_n}','{$item.id}');row_recalc({$item.id})">
		{section name=i loop=$uom}
		<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.selling_uom_id == $uom[i].id or ($item.selling_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
		{/section}
		</select>	
	{/if}

</td>
<td>
	<input type=hidden name=order_uom_id[{$sheet_n}][{$item.id}] value="{$item.order_uom_id}">
	<input type=hidden id=ouomf{$item.id} name=order_uom_fraction[{$sheet_n}][{$item.id}] value="{$item.order_uom_fraction|default:1}">
	<select name=order_uom[{$sheet_n}][{$item.id}] id="ouom{$item.id}" onchange="uom_change(this.value,'order','{$sheet_n}','{$item.id}');row_recalc({$item.id})">
	{section name=i loop=$uom}
	<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.order_uom_id == $uom[i].id or ($item.order_uom_id==0 and $uom[i].code eq 'EACH')}selected{/if}>{$uom[i].code}</option>
	{/section}
	</select>
</td>
<td>
	<input id="op{$item.id}" name="order_price[{$sheet_n}][{$item.id}]" size=4 value="{$item.order_price|number_format:3:".":""}" onchange="this.value=round(this.value,3);row_recalc({$item.id})" style="{if $item.is_foc};background:#ff0;color:#f00;{/if}" onclick="clear0(this)">
</td>

<td align=center>
{$item.cost_indicate|default:"-"}
<input type=hidden name=cost_indicate[{$sheet_n}][{$item.id}] value="{$item.cost_indicate}">
</td>

{if $form.po_option eq '1'}
<td>
	<input id="rp{$item.id}" name="resell_price[{$sheet_n}][{$item.id}]" size=4 value="{$item.resell_price|number_format:3:".":""}" onchange="this.value=round(this.value,3);row_recalc({$item.id})" onclick="clear0(this)">
</td>
{/if}
{if $form.deliver_to}
{section name=i loop=$branch}
{if in_array($branch[i].id,$form.deliver_to)}
{assign var=bid value=`$branch[i].id`}
<td align=right nowrap valign=top>
{if !$item.is_foc}
	<input id="q{$item.id}[{$bid}]" name="qty_allocation[{$sheet_n}][{$item.id}][{$bid}]" {if $item.order_uom_fraction == 1}disabled value="--"{else}value="{$item.qty_allocation[$bid]}"{/if} style="width:30px;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)">
	<input id="ql{$item.id}[{$bid}]" name="qty_loose_allocation[{$sheet_n}][{$item.id}][{$bid}]" style="width:30px; background:#fc9;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)" value="{$item.qty_loose_allocation[$bid]}">
{else}
    <input id="q{$item.id}[{$bid}]" name="qty_allocation[{$sheet_n}][{$item.id}][{$bid}]" value=0 type=hidden>
	<input id="ql{$item.id}[{$bid}]" name="qty_loose_allocation[{$sheet_n}][{$item.id}][{$bid}]" value=0 type=hidden>
{/if}
	<div align=center>
	S.P <input id="sp{$item.id}[{$bid}]" name="selling_price_allocation[{$sheet_n}][{$item.id}][{$bid}]" value="{$item.selling_price_allocation[$bid]|default:$item.selling_price|number_format:2:".":""}" size=4 onchange="this.value=round(this.value,2);row_recalc({$item.id})" onclick="clear0(this)" {if $config.po_selling_price_readonly}readonly style="background-color:#ccc;"{/if} style="width:40px;">
	</div>
	<span id="br_sp[{$bid}][{$item.id}]" style="display:none">{$item.br_sp[$bid]}</span>
	<span id="br_cp[{$bid}][{$item.id}]" style="display:none">{$item.br_cp[$bid]}</span>
</td>
<td align=right nowrap valign=top>
	<input id="f{$item.id}[{$bid}]" name="foc_allocation[{$sheet_n}][{$item.id}][{$bid}]" {if $item.order_uom_fraction == 1}disabled value="--"{else}value="{$item.foc_allocation[$bid]}"{/if} style="width:25px;background:#ff0;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)">
	<input id="fl{$item.id}[{$bid}]" name="foc_loose_allocation[{$sheet_n}][{$item.id}][{$bid}]" style="width:25px;background:#f90;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)" value="{$item.foc_loose_allocation[$bid]}">
{if $form.is_request}
<div style="padding-top:3px;" align=center>Bal: <font color=blue>{$item.balance[$bid]}</font></div>
{/if}
</td>
{/if}
{/section}
<td align=right>
	<span id=qty{$item.id}>{$item.qty|default:0}</span>
	<span style="display:none" id=ctn{$item.id}>{$item.ctn|default:0}</span>
</td>
<td align=right>
	<span id=foc{$item.id}>{$item.foc|default:0}</span>
</td>
{else}
{* single branch *}
<td valign=top align=right nowrap>
{if !$item.is_foc}
	<input id="q{$item.id}" name=qty[{$sheet_n}][{$item.id}] {if $item.order_uom_fraction == 1}disabled value="--"{else}value="{$item.qty}"{/if} size=1 onchange="row_recalc({$item.id})" style="width:30px;" onclick="clear0(this)">
	
	<input id="ql{$item.id}" name="qty_loose[{$sheet_n}][{$item.id}]" style="width:30px; background:#fc9;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)" value="{$item.qty_loose}">
	
	<span id=qty{$item.id} style="display:none">{$item.qty*$item.order_uom_fraction+$item.qty_loose}</span>
{else}
    <input id="q{$item.id}" name=qty[{$sheet_n}][{$item.id}] value="0" type=hidden>
    <input id="ql{$item.id}" name=qty_loose[{$sheet_n}][{$item.id}] value="0" type=hidden>
    <span id=qty{$item.id} style="display:none">0</span>
{/if}
{if $form.is_request}
<div style="padding-top:3px;" align=center>Bal: <font color=blue>{$item.balance}</font></div>
{/if}
</td>
<td valign=top align=right nowrap>
	<input id="f{$item.id}" name=foc[{$sheet_n}][{$item.id}] {if $item.order_uom_fraction == 1}disabled value="--"{else}value="{$item.foc}"{/if} size=1 onchange="row_recalc({$item.id})" style="width:25px;background:#ff0;" onclick="clear0(this)">
	<input id="fl{$item.id}" name="foc_loose[{$sheet_n}][{$item.id}]" style="width:25px; background:#f90;" size=1 onchange="row_recalc({$item.id})" onclick="clear0(this)" value="{$item.foc_loose}">
	<span id=foc{$item.id} style="display:none">{$item.foc*$item.order_uom_fraction+$item.foc_loose}</span>
	<span id=ctn{$item.id} style="display:none">{$item.qty+$item.foc}</span>
</td>
{/if}

<td align=right><span id=gamount{$item.id}>{if $item.is_foc}FOC{else}{$item.gamount|number_format:2}{/if}</span></td>
<td>
{if !$item.is_foc}
<input id=tax{$item.id} name=tax[{$sheet_n}][{$item.id}] value="{$item.tax}" size=3 onchange="row_recalc({$item.id})" onclick="clear0(this)">
{else}
<input type=hidden id=tax{$item.id} name=tax[{$sheet_n}][{$item.id}] value=0>
{/if}
</td>

<td>
{if !$item.is_foc}
<input id=disc{$item.id} name=discount[{$sheet_n}][{$item.id}] value="{$item.discount}" size=5 onchange="row_recalc({$item.id})" onclick="clear0(this)">
<div class="small" style="color:#00f" id=disc_amount{$item.id}>
{if $item.disc_amount>0 && strstr($item.discount,'%')}
{$item.disc_amount|number_format:2}
{/if}
</div>
{else}
<input type=hidden id=disc{$item.id} name=discount[{$sheet_n}][{$item.id}] value="">
<!-- Added by gary to calculate the FOC and get correct total selling-->
<div class="small" style="color:#00f" id=disc_amount{$item.id}>
{if $item.disc_amount>0 && strstr($item.discount,'%')}
{$item.disc_amount|number_format:2}
{/if}
</div>
{/if}
</td>
{assign var=c2 value=$item.foc+$item.qty}
<td align=right><span id=amount{$item.id}>{if $item.is_foc}FOC{else}{$item.amount|number_format:2}<br><font color=blue>{$item.amount/$c2|number_format:3}</font>{/if}</span></td>
{if $item.is_foc}
{assign var=total_profit value=$item.total_selling}
{else}
{assign var=total_profit value=$item.total_selling-$item.amount}
{/if}
<td align=right><span id=total_sell{$item.id}>{$item.total_selling|number_format:2}</span></td>
<td align=right><span id=total_profit{$item.id} style=";{if $item.is_foc}color:#090;{elseif $total_profit<=0}color:#f00{/if}">{$total_profit|number_format:2}</span></td>
<td align=right><span id=total_margin{$item.id} style=";{if $item.is_foc}color:#090;{elseif $total_profit<=0}color:#f00{/if}">{if $item.total_selling<=0}-{else}{$total_profit/$item.total_selling*100|number_format:2}%{/if}</span></td>
