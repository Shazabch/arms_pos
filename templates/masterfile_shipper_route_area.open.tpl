{*
5/29/2018 11:50 AM HockLee
- new form: transporter route area
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
<input type=hidden name=a value="save_route_area">
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="original_route_id" value="{$form.route_id}" />
<input type="hidden" name="original_sequence" value="{$form.sequence}" />
<input type="hidden" name="validation" value="{if $new_sequence eq 1}1{else}0{/if}" />
	<table width="100%">
		<tr>
			<td><b>Route Name</b></td>
			<td>
				{if $show eq 1}
					{$form.route_name}
				{else}
					<select name="route" class="width">
						<option value="0">Please select</option>
							{foreach from=$route item=name key=route_id}
								<option value="{$route_id}" {if $form.route_id eq $route_id}selected{/if}>{$name}</option>
							{/foreach}
					</select>
				{/if}
			</td>
		</tr>
		<tr>
			<td><b>Area</b></td>
			<td>
				{if $show eq 1}
					{$form.area}
				{else}
					<select name="area" class="width">
						<option value="0">Please select</option>
							{foreach from=$area item=name key=area_id}
								<option value="{$area_id}">{$name}</option>
							{/foreach}
					</select>
				{/if}
			</td>
		</tr>
		<tr>
			<td><b>Sequence</b></td>
			<td>
				<input type="number" name="sequence" size="2" min="1" max="30" value="{if $new_sequence eq 1}1{else}{$form.sequence}{/if}" /> max 30				
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