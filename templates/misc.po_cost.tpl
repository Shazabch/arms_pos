{include file=header.tpl}
<h1>{$PAGE_TITLE}</h1>
<form>
<input type=hidden name=a value="find">
<b>Find Multics Code</b> <input name=find value="{$smarty.request.find}"> <input type=submit value="Find">
</form>

{if $search}
<h3>Search Result</h3>
<table class=tb cellspacing=0 cellpadding=4>
<tr><th>Multics Code</th><th>Cost</th><th>Actual Unit Cost</th><th>Remark</th><th>ARMS Code</th><th>Description</th></tr>
{section loop=$search name=i}
<tr>
	<td>{$search[i][0]}</td>
	<td>{$search[i][1]}</td>
	<td>{$search[i][2]|default:"-"}</td>
	<td>{$search[i][3]|default:"-"}</td>
	<td>{$search[i][4].sku_item_code|default:"-"}</td>
	<td>{$search[i][4].description|default:"-"}</td>
</tr>
{/section}
</table>
{else}
<p>- no match -</p>
{/if}
{include file=footer.tpl}
<script>
document.forms[0].find.select();
document.forms[0].find.focus();
</script>
