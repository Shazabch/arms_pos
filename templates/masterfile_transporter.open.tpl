{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field
*}

<br />
<form method=post name=f_a onSubmit="return false;">
<input type=hidden name=a value="save">
<input type="hidden" name="id" value="{$form.id}" />

<table width="100%">
<tr>
	<td><b>Code</b></td>
	<td><input type="text" name="code" size="10" value="{$form.code}" onChange="this.value=this.value.toUpperCase();"/> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr>
<tr>
	<td nowrap><b>Company Name</b></td>
	<td><input type="text" name="company_name" size="30" value="{$form.company_name}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr>
<tr>
	<td><b>Description</b></td>
	<td>
	    <textarea name="description" rows="3" style="width:350px;">{$form.description}</textarea>
	</td>
</tr>

<tr>
	<td colspan="2" align="center"><br>
		<input type=button value="Save" id="btn_save" onclick="submit_form('save');" />
		<input type=button value="Close" onclick="submit_form('close');" />
	</td>
</tr>
</table>
</form>
