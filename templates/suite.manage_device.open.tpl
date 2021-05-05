{*
2/14/2019 9:42 AM Andy
- Change App Type from 'Any' to 'Others'.

4/12/2019 4:09 PM Justin
- Bug fixed Device Type chosen as "Price Checker" when saved the Device Type as "Others".

10/2/2019 10:40 AM Andy
- Fixed device type always displayed as "Others".

7/2/2020 3:19 PM Andy
- Added "Skip Dongle Checking" for Barcoder device.

9/28/2020 5:43 PM William
- Enhanced to hide Branc list when device type is "arms_fnb".

1/5/2021 11:36 AM William
- Enhanced to hide branch list when device type is 'pos'.
*}
<div>
	<form name="f_a" onSubmit="return false;">
		<input type="hidden" name="a" value="ajax_update_device" />
		<input type="hidden" name="guid" value="{$form.guid}" />
		
		<div style="height:85%;overflow-y:auto;" id="div_device_details">
			<table width="100%">
				{* Device Code *}
				<tr>
					<td width="100"><b>Device Code</b></td>
					<td colspan="3">
						<input type="text" name="device_code" value="{$form.device_code}" style="width:100px;" maxlength="20" class="required" title="Device Code" />
						<img src="ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
				
				{* Device Name *}
				<tr>
					<td width="100"><b>Device Name</b></td>
					<td colspan="3">
						<input type="text" name="device_name" value="{$form.device_name}" style="width:300px;" maxlength="200" class="required" title="Device Name" />
						<img src="ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
				
				{* Device Type *}
				<tr>
					<td width="100"><b>Device Type</b></td>
					<td colspan="3">
						{if $sessioninfo.id eq 1}
							<select name="device_type" onChange="DEVICE_SETUP_POPUP.check_device_type();">
								{foreach from=$device_type_list key=k item=v}
									<option value="{$k}" {if $form.device_type eq $k}selected {/if}>{$v}</option>
								{/foreach}
								<option value="" {if !$form.device_type}selected{/if}>Others</option>
							</select>
						{else}
							{$device_type_list[$form.device_type]|default:'Others'}
						{/if}
					</td>
				</tr>
				
				{* Special data *}
				<tr class="tr_special_data tr_special_data-barcoder" style="{if $form.device_type ne 'barcoder'}display:none;{/if}">
					<td width="100"><b>Skip Dongle Checking</b> 
						[<a href="javascript:void(alert('v2.0.0 and above'))">?</a>]
					</td>
					<td colspan="3">
						{if $sessioninfo.id eq 1}
							<input type="checkbox" name="skip_dongle_checking" value="1" {if $form.skip_dongle_checking}checked {/if}/> Yes
						{else}
							{if $form.skip_dongle_checking}Yes{else}No{/if}
						{/if}
					</td>
				</tr>
				
				{* Allowed Branches *}
				{if $BRANCH_CODE eq 'HQ'}
					<tr class="tr_branch_list" style="{if $form.device_type eq 'arms_fnb' || $form.device_type eq 'pos'}display: none{/if}">
						<td width="100"><b>Allowed Branches</b></td>
						<td colspan="3">
							<span>
								<input type="checkbox" value="1" onChange="DEVICE_SETUP_POPUP.toggle_allowed_branches();" id="inp_toggle_allowed_branches" /> All &nbsp;&nbsp;&nbsp;&nbsp;
							</span>
							{foreach from=$branches key=bid item=b}
								<span>
									<input type="checkbox" class="inp_allowed_branches" name="allowed_branches[{$bid}]" value="{$bid}" {if $form.allowed_branches.$bid}checked {/if} /> {$b.code} &nbsp;&nbsp;&nbsp;&nbsp;
								</span>
							{/foreach}
						</td>
					</tr>
				{/if}
				
				{* Access Code*}
				<td width="100"><b>Access Token</b></td>
				<td colspan="3">
					<input type="text" name="device_access_token" value="{$form.device_access_token}" style="width:100px;" maxlength="10" readonly class="required" title="Device Access Token" />
					{if $sessioninfo.id eq 1}
						<input type="button" value="Generate" onClick="DEVICE_SETUP_POPUP.generate_access_token_clicked();" style="width:100px;" />
						<img src="ui/rq.gif" align="absmiddle" />
					{/if}
				</td>
			</table>
		</div>
		
		<div style="height:20px;text-align:center" id="div_btn_update">
			<div style="position:absolute;padding-left:10px;" id="div_device_updating"></div>
			<input type="button" value="Save" onClick="DEVICE_SETUP_POPUP.save();" />
			<input type="button" value="Close" onClick="DEVICE_SETUP_POPUP.close();" />
		</div>
	</form>
</div>