<tr id="tr_update_list_row-{$list_id}" class="tr_update_list_row" valign="top" bgcolor="#99CCFF">
	<td nowrap width="60">
		{* Delete *}
		<img src="/ui/cancel.png" title="Delete" class="clickable img_delete_separator" />

		{* Up *}
		<img src="/ui/icons/arrow_up.png" title="Move Up" class="clickable img_move_up" />
		
		{* Down *}
		<img src="/ui/icons/arrow_down.png" title="Move Down" class="clickable img_move_down" />
		
		{* Sequence *}
		<input type="hidden" name="update_list[{$list_id}][sequence]" value="{$list_info.sequence}" />
		<input type="hidden" name="update_list[{$list_id}][is_separator]" value="1" />
	</td>

	<td colspan="5" align="left" nowrap>
		<input type="text" name="update_list[{$list_id}][description]" class="separator_description" value="{$list_info.description}" size="100" />
	</td>
</tr>