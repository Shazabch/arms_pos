{*
5/29/2018 11:50AM HockLee
- new form: shipper
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
<input type=hidden name=a value="save">
<input type="hidden" name="id" value="{$form.id}" />

	<table width="100%">
		<tr>
			<td><b>Transporter Type</b></td>
			<td>
				<select name="type" class="width">
					<option value="0">Please select</option>
					{foreach from=$type item=type_item key=type_id}
						{if $type_item.active eq 1}					
							<option value="{$type_id}" {if $form.type_id eq $type_id}selected{/if}>{$type_item.name}</option>
						{/if}

						{if $type_item.active eq 0 && $form.type_id eq $type_id}									
							<option value="{$type_id}" {if $form.type_id eq $type_id}selected{/if}>{$type_item.name}</option>				
						{/if}		
					{/foreach}
				</select>
				<img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Code</b></td>
			<td>
				<input type="text" name="code" size="10" value="{$form.code}" onChange="this.value=this.value.toUpperCase();"/> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Company Name</b></td>
			<td>
				<input type="text" name="company_name" size="30" value="{$form.company_name}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Address</b></td>
			<td>
			    <textarea name="address" rows="3" style="width:350px;">{$form.address}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Phone #1</b></td>
			<td>
				<input type="text" name="phone_1" size="30" value="{$form.phone_1}" /><img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Phone #2</b></td>
			<td>
				<input type="text" name="phone_2" size="30" value="{$form.phone_2}" />
			</td>
		</tr>
		<tr>
			<td><b>Fax No.</b></td>
			<td>
				<input type="text" name="fax" size="30" value="{$form.fax}" />
			</td>
		</tr>
		<tr>
			<td><b>Contact Person</b></td>
			<td>
				<input type="text" name="contact_person" size="30" value="{$form.contact_person}" /><img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Contact Email</b></td>
			<td>
				<input type="text" name="contact_email" size="30" value="{$form.contact_email}" />
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
