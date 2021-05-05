{*
12/20/2012 3:18 PM Andy
- Add checking to duplicated branch bonus other %.
*}

<tr id="tr_branch_bonus_row_breakdown-{$bid}-{$y}-{$m}-{$row_no}-{$type_row_no}" class="tr_branch_bonus_row_breakdown">
	{assign var=row_value value=$bonus_per_by_type.value}
	
	<td>
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.deleie_tr_branch_bonus_row_breakdown('{$bid}', '{$y}', '{$m}', '{$row_no}', '{$type_row_no}')" />
	</td>
	<td nowrap>
		{$bonus_per_by_type.type}
		<input type="hidden" name="sales_bonus_by_step[{$bid}][{$y}][{$m}][{$row_no}][bonus_per_by_type][{$type_row_no}][type]" value="{$bonus_per_by_type.type}" />
		
		<img src="/ui/icons/exclamation.png" title="Duplicated Entry" style="display:none;" align="absmiddle" id="img_branch_bonus_row_breakdown_duplicated_entry-{$bid}-{$y}-{$m}-{$row_no}-{$type_row_no}" class="img_branch_bonus_row_breakdown_duplicated_entry" />
	</td>
	<td>
		<input type="hidden" size="3" name="sales_bonus_by_step[{$bid}][{$y}][{$m}][{$row_no}][bonus_per_by_type][{$type_row_no}][value]" value="{$row_value}" />
		
		{if $bonus_per_by_type.type eq 'SKU'}
			{$si_info.$row_value.sku_item_code}/{$si_info.$row_value.mcode|default:'-'}/{$si_info.$row_value.artno|default:'-'}
		{else}
			{$cat_info.$row_value.description}
		{/if}
	</td>
	<td align="center">
		<input type="text" size="3" name="sales_bonus_by_step[{$bid}][{$y}][{$m}][{$row_no}][bonus_per_by_type][{$type_row_no}][per]" value="{$bonus_per_by_type.per|number_format:2:".":""|ifzero:''}" onChange="mfz(this);" class="required" title="Breakdown Bonus %" />
	</td>
</tr>