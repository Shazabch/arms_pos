{*
5/29/2018 11:50AM HockLee
- new form: transporter vehicle

8/27/2018 4:00PM HockLee
- Added Transporter option.
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
<input type=hidden name=a value="save_vehicle">
<input type="hidden" name="id" value="{$form.id}" />

	<table width="100%">
		<tr>
			<td><b>Plate No.</b></td>
			<td><input type="text" name="plate_no" size="10" value="{$form.plate_no}" onChange="this.value=this.value.toUpperCase();"/> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Transporter</b></td>
			<td>
				<select name="transporter" class="width">
					<option value="0">Please select</option>
					{foreach from=$transporter item=transporter_item key=transporter_id}
						{if $transporter_item.active eq 1}
							<option value="{$transporter_id}" {if $form.transporter_id eq $transporter_id}selected{/if}>{$transporter_item.code}</option>
						{/if}

						{if $transporter_item.active eq 0 && $form.transporter_id eq $transporter_id}
							<option value="{$transporter_id}" {if $form.transporter_id eq $transporter_id}selected{/if}>{$transporter_item.code}</option>
						{/if}
					{/foreach}
				</select>
				<img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td><b>Type</b></td>
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
			</td>
		</tr>
		<tr>
			<td><b>Brand/Manufacturer</b></td>
			<td>
				<select name="brand" class="width">
					<option value="0">Please select</option>
					{foreach from=$brand item=brand_item key=brand_id}
						{if $brand_item.active eq 1}
							<option value="{$brand_id}" {if $form.brand_id eq $brand_id}selected{/if}>{$brand_item.name}</option>
						{/if}

						{if $brand_item.active eq 0 && $form.brand_id eq $brand_id}
							<option value="{$brand_id}" {if $form.brand_id eq $brand_id}selected{/if}>{$brand_item.name}</option>
						{/if}
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Route</b></td>
			<td>
				<select name="route" class="width">
					<option value="0">Please select</option>
					{foreach from=$route item=route_item key=route_id}
						{if $route_item.active eq 1}
							<option value="{$route_id}" {if $form.route_id eq $route_id}selected{/if}>{$route_item.name}</option>
						{/if}

						{if $route_item.active eq 0 && $form.route_id eq $route_id}
							<option value="{$route_id}" {if $form.route_id eq $route_id}selected{/if}>{$route_item.name}</option>
						{/if}
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Max Load</b></td>
			<td><input type="text" class="text-right" name="max_load" size="10" value="{$form.max_load}" /> kg</td>
		</tr>
		<tr>
			<td><b>Status</b></td>
			<td>
				<select name="status" class="width">
					<option value="0">Please select</option>
					{foreach from=$status item=status_item key=status_id}
						{if $status_item.active eq 1}
							<option value="{$status_id}" {if $form.status_id eq $status_id}selected{/if}>{$status_item.name}</option>
						{/if}

						{if $status_item.active eq 0 && $form.status_id eq $status_id}
							<option value="{$status_id}" {if $form.status_id eq $status_id}selected{/if}>{$status_item.name}</option>
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