<select name="data_type" id="data_type" onchange="SETUP_CUSTOM_ACC_EXPORT_OPEN.change_data_type(this);">
	<option value="">-- Select --</option>
	{foreach from=$data_type_list key=k item=items}
		<option value="{$items}" {if $form.data_type eq $items}selected{/if}>{$data_type_option.$items}</option>
	{/foreach}
</select>