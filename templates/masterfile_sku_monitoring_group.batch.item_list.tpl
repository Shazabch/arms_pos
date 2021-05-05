{config_load file=site.conf}

{if !$item_list && !$batch_list}-- No Data --{else}
<div style="height:295px;">
{if $item_list}
{if count($item_list)>14}{assign var=show_scroll value=1}{/if}
<table width="100%">
	<tr>
	    <th bgcolor="{#TB_COLHEADER#}" width="10">&nbsp;</th>
	    <th bgcolor="{#TB_COLHEADER#}">ARMS Code</th>
	    <th bgcolor="{#TB_COLHEADER#}">Artno</th>
	    <th bgcolor="{#TB_COLHEADER#}">Description</th>
	    {if $show_scroll}<th width="13">&nbsp;</th>{/if}
	</tr>
	<tbody style="{if $show_scroll}height:270px;overflow-y:auto;overflow-x:hidden;{/if}">
	{foreach from=$item_list item=r name=f}
	    <tr>
	        <td>{$smarty.foreach.f.iteration}.</td>
	        <td class="r">{$r.sku_item_code}</td>
	        <td class="r">{$r.artno}</td>
			<td class="r">{$r.description|default:'-'}</td>
	    </tr>
	{/foreach}
	</tbody>
</table>
{elseif $batch_list}
{if count($batch_list)>14}{assign var=show_scroll value=1}{/if}
<table width="100%">
	<tr>
	    <th bgcolor="{#TB_COLHEADER#}" width="10">&nbsp;</th>
	    <th bgcolor="{#TB_COLHEADER#}">Year</th>
	    <th bgcolor="{#TB_COLHEADER#}">Month</th>
	    {if $show_scroll}<th width="13">&nbsp;</th>{/if}
	</tr>
	<tbody style="{if $show_scroll}height:270px;overflow-y:auto;overflow-x:hidden;{/if}">
	{foreach from=$batch_list item=r name=f}
	    <tr>
	        <td>{$smarty.foreach.f.iteration}.</td>
	        <td class="r">{$r.year}</td>
	        <td class="r">{$r.month}</td>
	    </tr>
	{/foreach}
	</tbody>
</table>
{/if}
</div>
<div align="center"><input type="button" value="Close" onClick="$('div_group_batch_items_details').hide();" /></div>
{/if}
