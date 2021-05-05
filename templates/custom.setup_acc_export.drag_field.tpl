{*
8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in an abnormal way when containing special characters
*}

<table cellspacing="5" cellpadding="4" width="100%" id="tblRight">
	<tr>
		<td><h4 id="h_data_field">Data Fields</h4></td>
	</tr>
	<tr>
		<td valign="top">
			<p>Drag a field into the table on your left</p>
			<ul class="ul_dragable" id="ul_dragable_list_cash_sales" style="padding:2px;width:100%">
				{foreach from=$row_format_field key=k item=items}
					<li id="li_report_option-{$items}" class="li_report_option li_report_option_area-master" 
					title="{$data_field[$items].field_desc|escape:'html'}" field_active="{$data_field[$items].field_active|escape:'html'}" 
					field_cancel="{$data_field[$items].field_cancel|escape:'html'}" field_value="{$data_field[$items].field_value|escape:'html'}" 
					field_desc="{$data_field[$items].field_desc|escape:'html'}" field_label_type="{$data_field[$items].field_label_type|escape:'html'}" 
					label="{$data_field[$items].title|escape:'html'}">{$data_field[$items].title|escape:'html'} [<a id="opener" href="javascript:void(SETUP_CUSTOM_ACC_EXPORT_OPEN.view_info('{$data_field[$items].title|escape:'javascript'}','{$data_field[$items].field_desc|escape:'javascript'}'))">?</a>]</li>
				{/foreach}
			</ul>
		</td>
	</tr>
</table>