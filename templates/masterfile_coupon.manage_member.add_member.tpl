
<div class="stdframe" style="height:370px;overflow-y:auto;">
	<form name="f_member" onSubmit="return false;" method="post" enctype="multipart/form-data" target="if_member">
		<input type="hidden" name="a" value="ajax_add_member" />
		<input type="hidden" name="coupon_code" value="{$coupon_items.coupon_code}" />
		
		<fieldset>
			<legend><h4>Import by Manually Key in</h4></legend>
			<b>Key in member NRIC or Card No, separate by "," or new line (e.g: 900100123, 900100124)</b>
			<textarea name="add_member_list" style="width: 100%;height:100px;"></textarea><br />
			<div id="div_add_info_manual"></div>
			<input type="button" value="Add" class="btn_add_member" onClick="ADD_MEMBER_DIALOG.add_member_clicked('manual');" />
			
			{if $config.membership_mobile_settings and $config.enable_push_notification}
				<input type="checkbox" name="enable_push_notification_manual" value="1" checked /> Send Push Notification to member
			{/if}
		</fieldset>
		
		<fieldset>
			<legend><h4>Import by CSV</h4></legend>
			<input type="file" name="member_file" /><br />
			[<a href="?a=download_import_coupon_member">Download Sample</a>] Format: (NRIC / Card No)
			<br /><br />
			<div id="div_add_info_csv"></div>
			<input type="button" value="Add" class="btn_add_member" onClick="ADD_MEMBER_DIALOG.add_member_clicked('csv');" />
			{if $config.membership_mobile_settings and $config.enable_push_notification}
				<input type="checkbox" name="enable_push_notification_csv" value="1" checked /> Send Push Notification to member
			{/if}
		</fieldset>
	</form>
</div>

	
<div>
	<div id="div_action_loading" style="position:fixed;"></div>
	<p  align="center" id="p_action_btn">
	<input type="button" value="Close" onClick="ADD_MEMBER_DIALOG.close();" />
	</p>
</div>