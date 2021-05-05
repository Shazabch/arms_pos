{include file='header.tpl'}

<script type="text/javascript">
{literal}
function reload_table() {
	
	$('div_table').innerHTML = '<img src=ui/clock.gif align=absmiddle /> Loading...';
	
	new Ajax.Updater('div_table', 'stucked_document_approvals.php', {
			parameters: 'a=reload_table&type='+document.search_form.type.value,
			evalScripts: true
	});
	
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<form name="search_form" id="search_form" onsubmit="return reload_table()">
<p>
<b>Document Type</b> :&nbsp;
	<select name="type">
		<option value="">All</option>
		{foreach from=$modules key=k item=m}
		{$m.label}
		<option value="{$k}" {if $smarty.request.m eq $k}selected{/if} >{$m.label|upper}</option>
		{/foreach}
	</select>
<input type=button value="Search" onclick="reload_table()" />
</p>
</form>

<div id="div_table" class="stdframe">
	{include file='stucked_document_approvals.table.tpl'}
</div>

{include file='footer.tpl'}
