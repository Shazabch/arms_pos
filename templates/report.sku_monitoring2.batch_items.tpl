{*
9/14/2010 3:03:28 PM Andy
- Adjust column width alignment.
*}

{config_load file=site.conf}
{if count($item_list)>14}{assign var=show_scroll value=1}{/if}
<table width="100%">
	<tr>
	    <th bgcolor="{#TB_COLHEADER#}" width="10">&nbsp;</th>
	    <th bgcolor="{#TB_COLHEADER#}">ARMS Code</th>
	    <th bgcolor="{#TB_COLHEADER#}">Artno</th>
	    <th bgcolor="{#TB_COLHEADER#}">Description</th>
	    {if $show_scroll}<th width="15">&nbsp;</th>{/if}
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
