{*
*}
{foreach from=$table key=key item=item}
	{cycle assign=row_color values="#ffffff,#eeeeee"}
	<tbody {if $key}id="tbody_cat_row-{$key}"{/if}>
		<tr bgColor="{$row_color}">
			{if $smarty.request.view_type eq 1}
				<td>{$item.sku_item_code}</td>
				<td nowrap>{$item.description}</td>
				<td>{$item.mcode}</td>
			{else}
				{assign var=this_cat_info value=$cat_info_list.$key}
				<td rowspan="2">
					{if !$no_header_footer}
						<img src="/ui/pixel.gif" width="{$indent*20}" height="1" />
					{/if}
					
					{if $key eq $smarty.request.category_id}
						{if !$no_header_footer}
							<img src="/ui/pixel.gif" width="20" height="1" />
						{/if}
						Items directly under this "{$selected_cat_info.description|default:'-'}"
					{elseif $this_cat_info}
						{if !$no_header_footer}
							{if $this_cat_info.have_subcat}
								<img onClick="expand_sub('{$key}','{$indent+1}',this);" src="/ui/expand.gif" />
							{else}
								<img src="/ui/pixel.gif" width="20" height="1" />
							{/if}
						{/if}
						
						{if !$no_header_footer}
							<a href="javascript:void(show_sub('{$key}'))">
						{/if}
						
						{$this_cat_info.description|default:'-'}
						
						{if !$no_header_footer}
							</a>
						{/if}
					{else}
						Un-Categorized
					{/if}
				</td>
			{/if}

			<td class="r">{$item.refund|number_format:2}</td>
			<td class="r">{$item.count}</td>
			<td class="r">{$item.charges|number_format:2}</td>
			<td class="r">{$item.expired_count}</td>
		</tr>
	</tbody>
{/foreach}