{*
5/29/2014 4:11 PM Justin
- Bug fixed on Generated GRA ID showing empty list.

5/27/2019 11.29 AM William
- Enhance "GRA" word to use report_prefix.
*}

{include file='header.tpl'}

<h1>Generate GRA Result</h1>

{if $warning}
	<h4>Some data has been skip due to belows reason</h4>
	<ul>
		{foreach from=$warning item=r}
			<li>{$r}</li>
		{/foreach}
	</ul>
{/if}

{if $gra_data.id_list}
	<h4>Generated GRA ID</h4>
	<ul>
		{foreach from=$gra_data.id_list item=id}
			<li> <a href="/goods_return_advice.php?a=open&id={$id}" target="_blank">{$report_prefix}{$id|string_format:'%05d'}</a></li>
		{/foreach}
	</ul>
{/if}

<a href="/goods_return_advice.php">Click here to go back to GRA Listing</a>
{include file='footer.tpl'}