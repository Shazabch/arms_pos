<h2>Click item to add</h2>
{count var=$items} record(s) found :
<form name=f_s>
	<div style="height:310px; border:1px solid #ccc; overflow:auto;" id=div_sku>
		<ul style="list-style-type:none;margin:0;padding:0;">
		{section name=i loop=$items}
			<li style="pointer:cursor;display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id=li{$items[i].id} onclick="ajax_add_multiple_item('{$items[i].id}', '{$is_recheck}'); hidediv('sku_list');">
				{$items[i].description}
				<font color=#009911>{if $items[i].artno}(Article: {$items[i].artno}){else}(MCode: {$items[i].mcode}){/if}</font>
				<font class=small>[<a href="javascript:void(window.open('masterfile_sku.php?a=view&id={$items[i].sku_id}'))">detail</a>]{if $show_varieties && $items[i].varieties>2}[<a href="javascript:toggle_vendor_sku({$items[i].sku_id},'{$items[i].id}')"><span id=xp{$items[i].id}>varieties</span></a>]{/if}</font>
				<br>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<span class=small>
				<font color=blue>ARMS Code:</font> {$items[i].sku_item_code}
				<font color=blue>Link Code:</font> {$items[i].link_code|default:"-"}
				<input type=hidden id="list_sku_code{$items[i].id}" value="{$items[i].sku_item_code}" onclick="cancel()">
			</span>
		{/section}
		</ul>
		<input type=hidden id="tmp_grn_barcode" value="{$grn_barcode}">
		<input type=hidden id="tmp_cost" value="{$cost}">
		<input type=hidden id="tmp_qty" value="{$qty}">
	</div>
</form>
<div align=center>
	<br><input type=button value="Close" onclick="cancel()">
</div>
