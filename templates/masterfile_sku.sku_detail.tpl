{*
REVISION HISTORY
================
11/19/2007 4:17:29 PM gary
- modify the layout.

11/23/2007 4:40:27 PM gary
- modify the layout of moving sku div.

11/4/2009 6:17:42 PM yinsee
- set to_sku_id as {$to_sku_id}
*}

<form method=post name=f_sku>
<input type=hidden name=a value="update_move_sku">
<input type=hidden name=to_sku_id value="{$to_sku_id}">
<input type=hidden name=sku_item_id value="{$request.sku_item_id}">
<p>
<h3>Change Parent SKU</h3>
Click confirm if this is the correct SKU you want to move to. Below are the items currently in the target SKU.
<h5>Category : {$form.cat_tree}</h5> 
</p>
<div style="height:330px;overflow:auto;">
<table border=0 class="tb" cellpadding=4 cellspacing=0 align=center width=100%>
<tr bgcolor=#ffee99>
<th>SKU Item</th>
<th>Description</th>
<th>Packing UOM</th>
</tr>
{section name=i loop=$items}
<tr bgcolor="{cycle name=r1 values=",#eeeeee"}">
<td>{$items[i].sku_item_code}</td>
<td>{$items[i].description|default:"&nbsp;"}</td>
<td>{$items[i].uom|default:'EACH'}</td>
</tr>
{/section}
</table>
</div>

<p align=center>
<input id=btn_c type=submit onclick="do_confirm();" value="Confirm">
<input type=button onclick="curtain_clicked();" value="Cancel">
</p>

</form>
<script>
$('btn_c').focus();
</script>
