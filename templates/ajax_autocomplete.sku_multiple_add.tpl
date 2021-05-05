{*
9/2/2010 11:20:35 AM Andy
- Add show ARMS Code.
- Make multiple add can have alternative submit function.
*}

<script>
{literal}
toggle_multi_add = function(ele){
	var c = ele.checked;
	$$('#tbl_multi_add input.chx_sid_list').each(function(chx){
		chx.checked = c;
	});
}

	
{/literal}
</script>
{if !$items_list}
	No Match Found for:
	<br /><p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><i>{$search_str}</i></b></p>
{else}
<div style="height:80%;overflow-x:hidden;overflow-y:auto;">
<table width="100%" id="tbl_multi_add">
<tr style="background:#fe9;">
	<th><input type="checkbox" id="chx_toggle_multi_add" onChange="toggle_multi_add(this);"></th>
	<th>ARMS Code</th>
	<th>Description</th>
	<th>Art. No</th>
	<th>Mcode</th>
</tr>

{foreach from=$items_list item=r}
	{assign var=sid value=$r.id}
	<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
		<td><input type="checkbox" name="sid[]" class="chx_sid_list" value="{$sid}" /></td>
		<td>{$r.sku_item_code}</td>
		<td>{$r.description}</td>
		<td class="small">{$r.artno|default:'-'}</td>
		<td class="small">{$r.mcode|default:'-'}</td>
	</tr>
{/foreach}
</table>
</div>

<p align="center"><input type="button" value="Add" id="btn_submit_multiple_add" onClick="{$smarty.request.alt_submit_multi_add|default:'submit_multi_add(this);'}" /></p>
{/if}
