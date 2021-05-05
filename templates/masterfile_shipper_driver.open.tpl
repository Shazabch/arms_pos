{*
5/29/2018 11:50AM HockLee
- new form: transporter driver
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
<input type=hidden name=a value="save_driver">
<input type="hidden" name="id" value="{$form.id}" />

	<table width="100%">
		<tr>
			<td><b>Name</b></td>
			<td>
				<input type="text" name="name" size="20" value="{$form.name}"/> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>		
		<tr>
			<td><b>IC No.</b></td>
			<td>
				<input type="text" name="ic_no" size="20" value="{$form.ic_no}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"> eg: 800617-02-3859
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
				<input type="text" name="phone_1" size="30" value="{$form.phone_1}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Phone #2</b></td>
			<td>
				<input type="text" name="phone_2" size="30" value="{$form.phone_2}" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><b>Assign Vehicle (Optional)</b></td>
		</tr>
		<tr>
			<td><b>Plate No.</b></td>
			<td>
				<select name="vehicle" class="width">
					<option value="0">Please select</option>
					{foreach from=$vehicle item=vehicle_item key=vehicle_id}
						{if $vehicle_item.active eq 1}
							<option value="{$vehicle_id}" {if $form.vehicle_id eq $vehicle_id}selected{/if}>{$vehicle_item.plate_no}</option>
						{/if}

						{if $vehicle_item.active eq  0 && $form.vehicle_id eq $vehicle_id}
							<option value="{$vehicle_id}" {if $form.vehicle_id eq $vehicle_id}selected{/if}>{$vehicle_item.plate_no}</option>
						{/if}
					{/foreach}
				</select>
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