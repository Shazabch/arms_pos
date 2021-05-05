<form name="f_r" onSubmit="return false;" method="post">
	<input type="hidden" name="a" value="update_report_group" />
	<h2>Report Group Setting</h2>
	<br>
	{foreach from=$report_group_edit_list item=rg}
	<div class="report_group_edit_list">
		<span style="float:left"><input type="text" id="report_group_setting_val_list[]" name="report_group_setting_val[{$rg}]" value="{$rg|htmlentities}" /></span>
		<span style="float:right;cursor:pointer;" onClick="REPORT_BUILDER.delete_report_group_clicked(this.parentNode);" title="Delete" ><img src="/ui/del.png" /></span>
	</div>
	{/foreach}
	<p align="center">
		<input type="button" id="btn_save" value="Save" onClick="REPORT_BUILDER.update_report_group_clicked();" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
		<input type="button" onClick="curtain_clicked();" value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;">
	</p>
</form>