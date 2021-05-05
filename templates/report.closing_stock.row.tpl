{*
*}

{foreach from=$table key=tablekey item=r}
	{assign var=k value=$r.key}
	<tr bgcolor="{if $bgcolor}#{$bgcolor}{else}{cycle values='#ffffff,#ffffcc'}{/if}" id="tr,{$r.tree_str}({$k})" alt="{$tree_lv}">
	    <td nowrap>
	        {if $smarty.request.show_by eq 'vendor'}{$vendor_info.$k.code} - {$vendor_info.$k.description}
	        {elseif $smarty.request.show_by eq 'branch'}{$branches.$k.code}
	        {else}
				<img src="/ui/pixel.gif" height="1" width="{$tree_lv*20}">
				{if $k ne $smarty.request.category_id}
				    {if $show_sku_img && $k>0 && $category_info.$k.level>1}
				    	<img src='/ui/icons/table.png' onclick="load_sku('{$k}',this);" align="absmiddle" title="View SKU at Closing Stock Report" />
				    {/if}
				    {$category_info.$k.description}
				    {if $category_info.$k.got_child}
					<img src="ui/expand.gif" align="absmiddle" title="Toggle Child Category" border="0" onClick="toggle_category_child('{$k}',this);" class="clickable img_expand" />
					{/if}
					{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
				{else}
				<span class="small">(Items direct under this category)</span>
				{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
				{/if}
			{/if}
	    </td>
		{if $got_closing_sc}
			<td class="r">{$r.sc_adj_to|qty_nf}</td>
		{/if}
	    <td class="r">{$r.sb_to|qty_nf}{if $r.got_sc}<sup class="got_sc">*</sup>{/if}</td>
	    {if $sessioninfo.privilege.SHOW_COST}
	    	<td class="r">{$r.sb_to_val|number_format:$config.global_cost_decimal_points}</td>
		{/if}
		<td class="r">{$r.sales_value_to|number_format:2}</td>
	</tr>
{/foreach}
