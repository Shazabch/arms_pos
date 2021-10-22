
<form name="f_leave" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save">
	<input type="hidden" name="id" value="{$form.id}" />

	<br />
	<table width="100%">
		{* Code *}
		<tr>
			<td><b class="form-label">Code<span class="text-danger" title="Required Field" > *</span></b></td>
			<td colspan="2">
				<input clas type="text" name="code" value="{$form.code}" class="required form-control" title="Code" onChange="this.value=this.value.toUpperCase();" />
			
			</td>
		</tr>
		
		{* Description *}
		<tr>
			<td><b class="form-label">Description<span class="text-danger"title="Required Field" > *</span></b></td>
			<td colspan="2">
				<input type="text" name="description"  value="{$form.description}" class="required form-control" title="Description" />
				
			</td>
		</tr>

	</table>
</form>

<p align="center" id="p_action">
	<input type="button" class="btn btn-primary" value="Save" onClick="LEAVE_DIALOG.save_clicked();" id="btn_save" />
	<input type="button" value="Close" class="btn btn-danger" onClick="LEAVE_DIALOG.close();" />
</p>