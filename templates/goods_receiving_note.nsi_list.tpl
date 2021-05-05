{*
6/9/2020 11:34 AM William
 - Bug fixed to exclude inactive sku when GRN use scan barcode to add item.
 
6/11/2020 3:31 PM William
- Bug fixed, add checking for blocked grn when show multi sku result.
*}
<h2>Select item to add</h2>
{count var=$items} record(s) found :
<form name=f_s>
	<div style="height:310px; border:1px solid #ccc; overflow:auto;" id=div_sku>
		<ul style="list-style-type:none;margin:0;padding:0;">
		{section name=i loop=$items}
			{if $items[i].code ne $prev_code}
				<h4><b>SKU item(s) that Matched Code: {$items[i].code}</b></h4>
				{assign var=item_count value=1}
			{/if}
			<li style="pointer:cursor;display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id="li{$items[i].id}">
				<input type="radio" id="item_choice{$items[i].id}" {if $items[i].active eq '0' || $items[i].doc_is_blocked}disabled{/if} name="item_choice[{$items[i].code}]" class="radio_sid_list" value="{$items[i].id}">
				{$items[i].description}
				<font color=#009911>{if $items[i].artno}(Article: {$items[i].artno}){else}(MCode: {$items[i].mcode}){/if}</font>
				<font class=small>[<a href="javascript:void(window.open('masterfile_sku.php?a=view&id={$items[i].sku_id}'))">detail</a>]{if $show_varieties && $items[i].varieties>2}[<a href="javascript:toggle_vendor_sku({$items[i].sku_id},'{$items[i].id}')"><span id=xp{$items[i].id}>varieties</span></a>]{/if}</font>
				<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class=small>
				<font color=blue>ARMS Code:</font> {$items[i].sku_item_code}
				<font color=blue>Link Code:</font> {$items[i].link_code|default:"-"}
				{if $items[i].active eq '0'}<font style="color: red !important;">(Inactive SKU Item)</font>{/if}
				{if $items[i].doc_is_blocked}<font style="color: red !important;">(This SKU is currently blocked in GRN)</font>{/if}
				<input type=hidden id="list_sku_code{$items[i].id}" value="{$items[i].sku_item_code}" onclick="cancel()">
				<input type=hidden name="code[{$items[i].id}]" value="{$items[i].code}">
				<input type=hidden name="cost[{$items[i].id}]" value="{$items[i].cost}">
				<input type=hidden name="pcs[{$items[i].id}]" value="{$items[i].pcs}">
			</span>
			{assign var=prev_code value=$items[i].code}
			<!--{$item_count++}-->
		{/section}
		</ul>
		<input type=hidden id="tmp_grn_barcode" value="{$grn_barcode}">
	</div>
</form>
<div align=center>
	<br /><input type="button" value="Save" onclick="multi_add_sku_items(this, '{$is_recheck}')">
	<input type="button" value="Close" onclick="cancel()">
</div>
