{*
12/19/2012 5:05 PM Andy
- Add checking to duplicated branch profit other %.
*}

<tr id="tr_branch_profit_row_breakdown-{$bid}-{$row_no}-{$type_row_no}" class="tr_branch_profit_row_breakdown">
	{assign var=row_value value=$profit_per_by_type.value}
	
	<td>
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.deleie_tr_branch_profit_row_breakdown('{$bid}', '{$row_no}', '{$type_row_no}')" />
	</td>
	<td nowrap>
		{$profit_per_by_type.type}
		<input type="hidden" name="sales_report_profit_by_date[{$bid}][{$row_no}][profit_per_by_type][{$type_row_no}][type]" value="{$profit_per_by_type.type}" />
		
		<img src="/ui/icons/exclamation.png" title="Duplicated Entry" style="display:none;" align="absmiddle" id="img_branch_profit_row_breakdown_duplicated_entry-{$bid}-{$row_no}-{$type_row_no}" class="img_branch_profit_row_breakdown_duplicated_entry" />
	</td>
	<td>
		<input type="hidden" size="3" name="sales_report_profit_by_date[{$bid}][{$row_no}][profit_per_by_type][{$type_row_no}][value]" value="{$row_value}" />
		
		{if $profit_per_by_type.type eq 'SKU'}
			{$si_info.$row_value.sku_item_code}/{$si_info.$row_value.mcode|default:'-'}/{$si_info.$row_value.artno|default:'-'}
		{else}
			{$cat_info.$row_value.description}
		{/if}
	</td>
	<td align="center">
		<input type="text" size="3" name="sales_report_profit_by_date[{$bid}][{$row_no}][profit_per_by_type][{$type_row_no}][per]" value="{$profit_per_by_type.per|default:0|number_format:2:".":""|ifzero:''}" onChange="mfz(this);" class="required" title="Breakdown Report Profit %" />
	</td>
</tr>