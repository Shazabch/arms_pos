{*
REVISION HISTORY
=================
2/22/2008 4:34:13 PM gary
- not allow to add FOC items for the same FOC items.
*}
<h5>Select items for Cost Sharing on the FOC item</h5>
<form name=f_b>
<input type=hidden name=sid[{$sheet_n}] value="{$sid}">
<div style="height:180px;overflow:auto">
{section name=i loop=$po_items}
{assign var=c value=`$po_items[i].id`}
<input type=hidden name=foc_items[{$po_items[i].id}] value="{$po_items[i].sku_item_id}">
<input name=sel_foc[{$po_items[i].id}] type="checkbox" {if $foc_sel[$c]}checked{/if}> 
{$po_items[i].description}
<br>
{/section}
</div>
<p align=center>
<input name=no_item type=checkbox> No Cost Sharing
<input type=button onclick="save_foc_item({$sheet_n},{$foc_item_id})" value=Save> 
<input type=button onclick="cancel_foc()" value=Cancel></p>
</form>
