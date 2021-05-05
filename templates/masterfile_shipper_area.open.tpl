{*
5/29/2018 11:50 AM HockLee
- new template: area
*}
{literal}
<style>
.text-right{
	text-align: right;
}

.width{
	width: 150px;
}
</style>
{/literal}

<br />
<form method=post name=f_a onSubmit="return false;">
<input type=hidden name=a value="save_area">
<input type="hidden" name="id" value="{$form.id}" />

	<table width="100%">
		<tr>
			<td><b>Area Name</b></td>
			<td>
				<input type="text" name="name" size="20" value="{$form.name}"/> <img src="ui/rq.gif" align="absbottom" title="Required Field">
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