{*
1/6/2014 5:16 PM Justin
- Enhanced to have separator.
*}

<tr id="tr_update_list_row-{$list_id}" class="tr_update_list_row" valign="top">

	<td nowrap width="60">
		{* Delete *}
		<img src="/ui/cancel.png" title="Delete" class="clickable img_delete" />

		{* Up *}
		<img src="/ui/icons/arrow_up.png" title="Move Up" class="clickable img_move_up" />
		
		{* Down *}
		<img src="/ui/icons/arrow_down.png" title="Move Down" class="clickable img_move_down" />
		
		{* Sequence *}
		<input type="hidden" name="update_list[{$list_id}][sequence]" class="sequence" value="{$list_info.sequence}" />
	</td>
	
	{* Changes Log *}
	<td>
		<span style="color:blue;">Title:</span>
		<input type="text" name="update_list[{$list_id}][title]" value="{$list_info.title}" style="width:100%;" class="inp_title" />
		<br />
		<span style="color:blue;">Changes Log:</span><br />
		<textarea name="update_list[{$list_id}][changes_log]" class="txt_changes_log" style="width:100%;">{$list_info.changes_log}</textarea>
		<br />
		<span style="color:blue;">Extras:</span><br />
		<textarea name="update_list[{$list_id}][extras]" class="txt_extras" style="width:100%;">{$list_info.extras}</textarea>
	</td>
	
	{* Status *}
	<td align="center">
		<select name="update_list[{$list_id}][status]" class="sel_status">
			{foreach from=$status_list key=k item=v}
				<option value="{$k}" {if $list_info.status eq $k}selected {/if}>{$v}</option>
			{/foreach}
		</select>
	</td>
	
	{* Received *}
	<td align="center">
		<input type="text" name="update_list[{$list_id}][receive_date]" size="10" value="{$list_info.receive_date}" class="inp_receive_date" />
	</td>
	
	{* PIC *}
	<td align="center">
		<input type="text" name="update_list[{$list_id}][username]" size="10" value="{$list_info.username}" class="inp_username" />
	</td>
	
	{* Files *}
	<td>
		<span class="link span_edit_file_list"><img src="/ui/ed.png" align="absmiddle" /> Edit</span> |
		<a href="?a=view_update_history&id={$list_id}" target="_blank"><img src="/ui/icons/application_view_list.png" align="absmiddle" /> Update History</a>
		
		{* File table *}
		<div id="div_file_table-{$list_id}">
			{include file="ARMS_UPDATER.list_row.file_table.tpl"}
		</div>
	</td>
</tr>