{*
2/21/2018 5:35 PM Andy
- Enhanced to show another purple tick for files with svn committed (php7)
*}
<table width="100%" class="tb tbl_file_table" cellpadding="0" cellspacing="0" id="tbl_file_table-{$list_id}">
	<tr>		
		<th width="10">&nbsp;</th>

		<th width="40">
			{* Delete *}
			
			{* Check for Upload *}
			<input type="checkbox" onChange="ARMS_UPDATER.toggle_file_table_upload_list('{$list_id}');" id="chx_toggle_file_need_upload-{$list_id}" />
		</th>
		
		{* File Name *}
		<th>File name</th>
	</tr>
	
	<tbody>
		{foreach from=$list_info.file_list item=file_info name=ffl}
			<tr id="tr_file_row-{$file_info.id}" class="tr_file_row">
				
				<td align="right">
					{$smarty.foreach.ffl.iteration}
				</td>
				
				
				<td nowrap>
					{* Delete *}
					<img src="ui/cancel.png" onClick="ARMS_UPDATER.delete_file_row('{$file_info.id}');" align="absmiddle" />
					
					{* Check for Upload *}
					<input type="checkbox" name="update_list[{$list_id}][file_list][{$file_info.id}][need_upload]" value="{$file_info.id}" class="chx_file_need_upload-{$list_id} chx_file_need_upload" onChange="ARMS_UPDATER.need_upload_changed('{$file_info.id}');" />
					
					
				</td>
				
				{* File Name *}
				<td>
					<span class="is_svn" style="{if $file_info.is_svn eq 1}{else}display:none;{/if}"><img src="ui/notify_sku_approve.png" align="absmiddle" title="SVN Committed" /></span>
					<span class="is_svn_php7" style="{if $file_info.is_svn_php7 eq 1}{else}display:none;{/if}"><img src="ui/notify_sku_approve.png" align="absmiddle" title="SVN PHP7 Committed" class="img_mod1" /></span>
					{$file_info.filename}
					<input type="hidden" id="inp_filename-{$file_info.id}" value="{$file_info.filename}" />

				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

<ul class="errmsg ul_file_errmsg" id="ul_file_errmsg-{$list_id}"></ul>
