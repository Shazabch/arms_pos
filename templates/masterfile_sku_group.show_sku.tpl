{*
7/20/2011 9:40:21 AM Justin
- Added hidden field for Art No and MCode info purpose.
*}
<div style="text-align:right;padding:5px;"><img src="/ui/closewin.png" onclick="Element.hide('sku_table')"></div>

<h2>Select Items to add</h2>
{count var=$items} record(s) found:
<form name=f_s>
<input type=hidden name=related_sku value="{$related_sku}">
<div style="height:290px; border:1px solid #ccc; overflow:auto;">

<ul style="list-style-type:none;margin:0;padding:0;" id="item_list">

{section name=i loop=$items}
<li style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id=li{$items[i].id}> <input id="{$items[i].sku_item_code}" name="{$items[i].description}" type="checkbox" class="sku_item" artno="{$items[i].artno}" mcode="{$items[i].mcode}">
<input id="cb_ajax_sku_{$items[i].sku_item_code}" value="{$items[i].id}" type="hidden" artno="{$items[i].artno}" mcode="{$items[i].mcode}">
{$items[i].description}
<font color=#009911>{if $items[i].artno}(Article: {$items[i].artno}){else}(MCode: {$items[i].mcode}){/if}</font>
<font class=small>[<a href="javascript:void(window.open('masterfile_sku.php?a=view&id={$items[i].sku_id}'))">detail</a>]{if $show_varieties && $items[i].varieties>2}[<a href="javascript:toggle_vendor_sku({$items[i].sku_id},'{$items[i].id}')"><span id=xp{$items[i].id}>varieties</span></a>]{/if}</font>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;
<span class=small>
<font color=blue>ARMS Code:</font> {$items[i].sku_item_code}
<font color=blue>Selling:</font> {$items[i].selling_price|number_format:2}
<font color=blue>Cost:</font> {$items[i].cost_price|number_format:2}
</span>
{sectionelse}

<li> - no SKU found for this vendor -

{/section}
</ul>

</div>
</form>
<div align=center>
<input type=button value="Add" onclick="add_vendor_sku();">
<input type=button value="Close" onclick="$('sku_table').hide();">
</div>

