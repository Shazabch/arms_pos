{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}

.td_label_top{
	padding-top:3;
	vertical-align:top;
}

.div_multi_select{
	border:1px solid grey;
	overflow:auto;
	overflow-x:hidden;
	display: inline-block;
	padding: 2px;
}

.calendar{
	z-Index: 100000 !important;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var curr_branch_code = '{$BRANCH_CODE}';
</script>

<h1>Consignment Price Wizard</h1>

<form>
Branch
<select name="branch_id">
{foreach from=$branch_list item=b}	
<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected{/if}>{$b.code} - {$b.description}</option>
{/foreach}
</select>
<input type="hidden" name="a" value="show">
<button value="Show" onclick="form.a.value='show_price';form.submit();">Show</button>
<button value="Show" onclick="form.a.value='download_price';form.submit();">Download CSV</button>
</form>

{if !$nofooter}
{include file=footer.tpl}
{/if}