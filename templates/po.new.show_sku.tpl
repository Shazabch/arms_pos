{*
2/10/2017 5:10 PM Andy
- Change loop from section to foreach.

05/15/2020 10:22 PM Sheila
- Updated button color
- Added padding to button
*}

{if !$hideheader}
<h2>Select Items to add</h2>
{count var=$items} record(s) found :
<form name=f_s>
<input type=hidden name=related_sku value="{$related_sku}">
<div style="height:310px; border:1px solid #ccc; overflow:auto;" id=div_sku>
{/if}
{if !$hideheader}
<ul style="list-style-type:none;margin:0;padding:0;">
{else}
<ul style="list-style-type:none;margin:0 0 0 20;padding:0;" id=ul{$sku_id}>
{/if}
{foreach from=$items key=sid item=r}
<li style="display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''" id="li{$r.id}"> <input id="cb{$r.id}" name="sel[{$r.id}]" type="checkbox" />
{$r.description}
<font color=#009911>{if $r.artno}(Article: {$r.artno}){else}(MCode: {$r.mcode}){/if}</font>
<font class=small>[<a href="javascript:void(window.open('masterfile_sku.php?a=view&id={$r.sku_id}'))">detail</a>]{if $show_varieties && $r.varieties>2}[<a href="javascript:toggle_vendor_sku({$r.sku_id},'{$r.id}')"><span id="xp{$r.id}">varieties</span></a>]{/if}</font>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;
<span class=small>
<font color=blue>ARMS Code:</font> {$r.sku_item_code}
<font color=blue>Selling:</font> {$r.selling_price|number_format:2}
<font color=blue>Cost:</font> {$r.cost_price|number_format:2}
</span>
{foreachelse}
{if !$hideheader}
<li> - no SKU found for this vendor -
{else}
<li> - no varieties -
{/if}
{/foreach}
</ul>
{if !$hideheader}
</div>
</form>
<div align=center class="btn_padding">
<input class="btn btn-primary" type=button value="Add" onclick="do_vendor_sku()">
<input class="btn btn-warning" type=button value="Close" onclick="cancel_vendor_sku()">
</div>
{/if}
