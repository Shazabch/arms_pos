{*
1/17/2013 4:10 PM Justin
- Bug fixed on system showing number format error.

4/21/2017 2:34 PM Khausalya
- Enhanced changes from RM to use config setting. 
*}

<tr id="tr_co_item-{$co_row_num}" class="tr_co_item">
	{if !$noedit && !$is_print}
		<td>
			<img src="ui/del.png" title="Delete" align="absmiddle" class="clickable" onClick="CO2_FORM.delete_row_clicked('{$co_row_num}');" />
			<img src="ui/icons/arrow_up.png" title="Move Up" align="absmiddle" class="clickable img_swap_up" id="img_swap_up-{$co_row_num}" onClick="CO2_FORM.swap_row('up','{$co_row_num}');" />
			<img src="ui/icons/arrow_down.png" title="Move Down" align="absmiddle" class="clickable img_swap_down" id="img_swap_down-{$co_row_num}" onClick="CO2_FORM.swap_row('down', '{$co_row_num}');" />
		</td>
	{/if}
	
	<!-- Date (As Per Pos) -->
	<td align="center" nowrap>
		{if !$noedit && !$is_print}
			<input type="text" name="date_as_pos[{$co_row_num}]" value="{$co_item.date_as_pos}" size="10" id="inp_date_as_pos-{$co_row_num}" />
			<img align="absbottom" src="ui/calendar.gif" id="img_date_as_pos-{$co_row_num}" style="cursor: pointer;" title="Select Date" />
		{else}
			{$co_item.date_as_pos|default:'&nbsp;'}
		{/if}
	</td>
	
	<!-- Collection No -->
	<td align="center">
		{if !$noedit && !$is_print}
			<input type="text" name="collection_no[{$co_row_num}]" value="{$co_item.collection_no}" maxlength="100" />
		{else}
			{$co_item.collection_no|default:'&nbsp;'}
		{/if}
	</td>
	
	<!-- Amount ({$config.arms_currency.symbol}) -->
	<td align="right">
		{if !$noedit && !$is_print}
			<input type="text" name="row_amt[{$co_row_num}]" value="{$co_item.row_amt|round2|number_format:2:".":""}" onChange="CO2_FORM.amount_changed('{$co_row_num}');" style="text-align:right;" size="10" />
		{else}
			{$co_item.row_amt|default:0|number_format:2|ifzero:''}
		{/if}
	</td>
	
	{if $is_print}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
