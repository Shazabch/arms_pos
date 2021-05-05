
<input type="hidden" name="list_id" value="{$list_info.id}" />

<textarea name="file_list" style="width:100%;height:200px;">{foreach from=$list_info.file_list item=r}{strip}
		{$r.filename}{/strip}
{strip}	
{/strip}{/foreach}</textarea>
