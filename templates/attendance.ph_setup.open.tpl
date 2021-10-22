
<form name="f_ph" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save">
	<input type="hidden" name="id" value="{$form.id}" />

	<br />
	<table width="100%">
		{* Code *}
		<tr>
			<td><b class="form-label ml-3">Code<span class="text-danger" title="Required Field"> *</span></b></td>
			<td colspan="2">
				<input  type="text" name="code"  value="{$form.code}" class="required form-control" title="Code" onChange="this.value=this.value.toUpperCase();" />
				
			</td>
		</tr>
		
		{* Description *}
		<tr>
			<td><b class="form-label mt-2 ml-3">Description<span class="text-danger" title="Required Field"> *</span></b></td>
			<td colspan="2">
				<input type="text" name="description"  value="{$form.description}" class="required form-control mt-2 " title="Description" />
				
			</td>
		</tr>

	</table>
</form>

<p align="center" id="p_action">
	<input type="button" class="btn btn-primary" value="Save" onClick="PH_DIALOG.save_clicked();" id="btn_save" />
	<input type="button"  class="btn btn-danger" value="Close" onClick="PH_DIALOG.close();" />
</p>