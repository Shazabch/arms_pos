
<form name="f_ph" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save">
	<input type="hidden" name="id" value="{$form.id}" />

	<br />
	<table width="100%">
		{* Code *}
		<tr>
			<td><b>Code</b></td>
			<td colspan="2">
				<input type="text" name="code" size="10" maxlength="10" value="{$form.code}" class="required" title="Code" onChange="this.value=this.value.toUpperCase();" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			</td>
		</tr>
		
		{* Description *}
		<tr>
			<td><b>Description</b></td>
			<td colspan="2">
				<input type="text" name="description" size="50" maxlength="100" value="{$form.description}" class="required" title="Description" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field" />
			</td>
		</tr>

	</table>
</form>

<p align="center" id="p_action">
	<input type="button" value="Save" onClick="PH_DIALOG.save_clicked();" id="btn_save" />
	<input type="button" value="Close" onClick="PH_DIALOG.close();" />
</p>