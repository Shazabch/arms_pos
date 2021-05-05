{if !$is_header}
<tr id="row_details_{$bid}_{$item.mst_cid}_{$level}_{$item.prev_cat}" class="row_details_{$bid}_{$item.mst_cid|default:$item.category_id}_{$level}_{$item.prev_cat}" bgcolor="{if $level eq 2}#cfafaf;{elseif $level eq 3}#daaaaa;{/if}">
{/if}
	<td align="center">
		{if $is_header}
			{$branches.$bid.code}
		{else}
			&nbsp;
		{/if}
	</td>
	<td>
		{section name=i loop=$level start=1}
			&nbsp;&nbsp;&nbsp;&nbsp;
		{/section}
		{if $level < 3 && $level > 1 || ($level eq 1 && $item.category_id)}
			<img src="/ui/expand.gif" onclick="show_details('{$item.category_id|default:0}', '{$bid}', '{$level+1}', '{$item.mst_cid}', '{$item.prev_cat}', this);" align=absmiddle>
		{/if}
		{$item.description}
	</td>
	<td align="right">{$item.amount|default:0|number_format:2}</td>
	<td align="right">{$item.sb_amount|default:0|number_format:2}</td>
	<td align="right">
		{if $item.amount && $item.sb_amount}
			{assign var=sell_thru_perc value=$item.amount/$item.sb_amount*100}
			{$sell_thru_perc|number_format:2}%
		{else}
			0.00%
		{/if}
	</td>
{if !$is_header}
</tr>
{/if}
