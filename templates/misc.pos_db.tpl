{include file=header.tpl}
<h3>POS DB</h3>
<ul>
{foreach from=$file item=f}
<li><a href="{$f.url}" title="Modified Date: {$f.timestamp}">{$f.file}</a></li>
{/foreach}
