{*
6/30/2017 3:42 PM Justin
- Enhanced to disable SKU not allowed to add when it is BOM Package SKU when using Add Parent & Child.
*}

<script>
{literal}
toggle_pc_add = function(ele){
	var status = ele.checked;
	
	$$('#tbl_pc_add input.pc_checkbox').each(function(chx){
		chx.checked = status;
	});
}
{/literal}
</script>

<div style="float:right"><img src="ui/closewin.png" onClick="default_curtain_clicked();" /></div>
<h2>List of Items</h2>
<ul style="color:#0000ff;">
	Note:<br />
	<li>BOM SKU [Package] cannot be added from Multi Add.</li>
</ul>
<form name="f_pc" id="f_pc">
<input type="hidden" name="id" value="{$smarty.request.do_id}">
<input type="hidden" name="do_type" value="{$smarty.request.do_type}" />

<table width="100%" id="tbl_pc_add">
<tr style="background:#fe9;">
	<th><input type="checkbox" id="inp_chx_all" onClick="toggle_pc_add(this);"></th>
	<th>ARMS Code</th>
	<th>Description</th>
	<th>Art. No</th>
	<th>Mcode</th>
	<th>Stock<br>Balance</th>
	<th>Selling Price</th>
	<th>Discount Code</th>
</tr>

<tbody>

{foreach from=$items key=sid item=r}
	<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
		<td><input type="checkbox" name="sid[]" value="{$sid}" {if $r.is_bom && $r.bom_type eq 'package'}disabled{else}class="pc_checkbox"{/if} sku_item_code="{$r.sku_item_code}" sku_description="{$r.description|escape:'html'}" {if $r.qty > 0}checked{/if} /></td>
		<td class="small">{$r.sku_item_code}</td>
		<td>
			{$r.description}
			{if $r.is_bom && $r.bom_type eq 'package'}
				<span style="color:#0000ff;">[BOM PACKAGE]</span>
			{/if}
		</td>
		<td class="small">{$r.artno|default:'-'}</td>
		<td class="small">{$r.mcode|default:'-'}</td>
		<td class="small" align="right">{$r.qty|default:'-'}</td>
		<td class="small" align="right">{$r.price|default:'-'}</td>
		<td class="small" align="center">{$r.discount_code|default:'-'}</td>
	</tr>
{/foreach}
</tbody>
</table>
</form>

<p align="center"><input type="button" value="Add" onClick="submit_parent_child(this);" /></p>
