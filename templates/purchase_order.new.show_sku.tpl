{*
Revision History
================
4/20/07 4:32:22 PM yinsee
- show selling and cost from vendor_sku_history

11/30/2007 3:06:11 PM gary
- list out related sku.
*}
{if !$hideheader}
<h2>Select Items to add</h2>
{count var=$items} record(s) found:
<form name=f_s>
<input type=hidden name=related_sku value="{$related_sku}">
<div style="height:310px; border:1px solid #ccc; overflow:auto;">
{/if}
{if !$hideheader}
<ul style="list-style-type:none;margin:0;padding:0;">
{else}
<ul style="list-style-type:none;margin:0 0 0 20;padding:0;" id=ul{$sku_id}>
{/if}
{section name=i loop=$items}
<li style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id=li{$items[i].id}> <input id=cb{$items[i].id} name=sel[{$items[i].id}] type=checkbox>
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
{if !$hideheader}
<li> - no SKU found for this vendor -
{else}
<li> - no varieties -
{/if}
{/section}
</ul>
{if !$hideheader}
</div>
</form>
<div align=center>
<input type=button value="Add" onclick="do_vendor_sku()">
<input type=button value="Close" onclick="cancel_vendor_sku()">
</div>
{/if}
