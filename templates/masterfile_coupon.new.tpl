{*
11/28/2013 2:41 PM Justin
- Bug fixed on SKU item still exists in SKU item list even it is deleted.

8/19/2016 9:49 AM Andy
- Rename "Min Qty" to "Min Purchase Qty".

8/27/2019 1:54 PM Andy
- Added Discount by Percentage.
- Added Minimum Receipt Amount.
- Enhanced to able to view coupon details while it is already activated.

9/27/2019 11:06 AM Andy
- Enhanced to prevent users to click save coupon multiple time while the process is running.

10/4/2019 9:27 AM Andy
- Added alert notification for Discount By, Min Purchase Amount, Min Receipt Amount and Membership Condition.

11/28/2019 4:43 PM Andy
- Added feature to control coupon only show for mobile registered member since day X to day Y or need profile info.

2/11/2020 5:58 PM Andy
- Added new coupon feature "Referral Program".
*}

<div style="padding:10;">
	<h3>Coupon Details ({$action|default:"New"})</h3>
	<form name="f_a" method="post">
		<input type="hidden" name="id" id="id" value="{$form.id}">
		<input type="hidden" name="branch_id" id="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}">
		<input type="hidden" name="is_print" id="is_print" value="{$form.is_print|default:0}">
		<input type="hidden" name="active" value="{$form.active|default:0}">

		<table border="0">
			<tr>
				<td>&nbsp;</td>
				<td><b>Valid Date</b></td>
				<td><b>Date Start</b></td>
				<td colspan="2">
					<input type="text" name="valid_from" value="{$form.valid_from}" id="inp_valid_from" readonly="1" size="12" onchange="COUPON_DIALOG.calculate_date_end();"  />
					{if !$form.active}
						<img align="absmiddle" src="ui/calendar.gif" id="img_valid_from" style="cursor: pointer;" title="Select Date"/>&nbsp;&nbsp;
					{/if}
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td width="70px">
					<select name="rdo_end" id="rdo_end_id" onchange="COUPON_DIALOG.calculate_date_end(); COUPON_DIALOG.toggle_date_type(this);">
						<option value="valid_to">Date End</option>
						<option value="valid_duration">Duration</option>
					</select>
				</td>
				<td id="date_end_id" colspan="2">
					<input type="text" name="valid_to" value="{$form.valid_to}" id="inp_valid_to" readonly="1" size="12" />
					{if !$form.active}
						<img align="absmiddle" src="ui/calendar.gif" id="img_valid_to" style="cursor: pointer;" title="Select Date"/>&nbsp;&nbsp;
					{/if}
				</td>
				<td id="date_duration_id" colspan="2">
					<select name="valid_duration" id="inp_valid_duration" onchange="COUPON_DIALOG.calculate_date_end();">
						{section name=mon loop=13 start=1}
							<option value="{$smarty.section.mon.index}">{$smarty.section.mon.index}</option>
						{/section}
					</select>
					<b>(Months)</b>
				</td>
			</tr>
			<tr id="date_duration_id2">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>Date End</b></td>
				<td id="time_to_id2" colspan="2">
					<input id="show_date_end" readonly="1" size="12">&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><b>Valid Time</b></td>
				<td><b>Time Start</b></td>
				<td colspan="2">
					<input type="text" name="valid_time_from" value="{$form.valid_time_from|default:'00:00'}" id="inp_time_from" size="6" maxlength="5" /> (hh:mm)
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>Time End</b></td>
				<td colspan="2">
					<input type="text" name="valid_time_to" value="{$form.valid_time_to|default:'23:59'}" id="inp_time_to" size="6" maxlength="5" /> (hh:mm)
				</td>
			</tr>
			
			<tr>
				<td>&nbsp;</td>
				<td><b>Code</b></td>
				<td nowrap>
					<input id="code" name="code" type="text" style="width:90px;" maxlength="7" value="{$form.code}" onchange="mi(this);">
					{if !$form.id}<input type="button" value="Auto" onClick="COUPON_MAIN.auto_get_new_code();" id="btn_get_new_code" />{/if}
					<img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
				
				{* Discount By *}
				<td><b>Discount By [<a href="javascript:void(COUPON_MAIN.alert_discount_by_notification())">?</a>]</b></td>
				<td>
					<select name="discount_by">
						<option value="amt" {if !$form.discount_by or $form.discount_by eq 'amt'}selected {/if}>Amount</option>
						<option value="per" {if $form.discount_by eq 'per'}selected {/if}>Percentage</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><b>Min Purchase Qty [<a href="javascript:void(COUPON_MAIN.alert_min_qty_notification())">?</a>]</b></td>
				<td>
					<input id="min_qty" name="min_qty" type="text" size="5" maxlength="7" value="{$form.min_qty}" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points}));" style="text-align:right;" />
				</td>
				
				{* Minimum Purchase Amount *}
				<td><b>Min Purchase Amount [<a href="javascript:void(COUPON_MAIN.alert_min_amt_notification())">?</a>]</b></td>
				<td colspan="2">
					<input id="min_amt" name="min_amt" type="text" size="5" maxlength="7" value="{$form.min_amt}" onchange="this.value=float(round(this.value, 2));"  style="text-align:right;" />
				</td>
			</tr>
			
			<tr>
				<td colspan="3">&nbsp;</td>
				
				{* Minimum Receipt Amount *}
				<td><b>Min Receipt Amount [<a href="javascript:void(COUPON_MAIN.alert_min_receipt_amt_notification())">?</a>]</b></td>
				<td colspan="2">
					<input id="min_receipt_amt" name="min_receipt_amt" type="text" size="5" maxlength="7" value="{$form.min_receipt_amt}" onchange="this.value=float(round(this.value, 2));"  style="text-align:right;" />
				</td>
			</tr>
			
			<tr>
				<td colspan="5">
					<fieldset>
					<legend>Items Condition</legend>
						<table>
							<tr>
								<td><input type="radio" id="setting_1" name="setting" value="dept_bran_vd" {if !$group_item}checked{/if} onclick="COUPON_DIALOG.setting_changed(this.value);" checked></td>
								<td><b>Department</b></td>
								<td colspan=2>
									<select name="dept_id" id="dept_id">
										<option value="0" selected>All</option>
										{foreach from=$dept item=d}
										<option value="{$d.id}" {if $form.dept_id eq $d.id}selected{/if}>{$d.description}</option>
										{/foreach}
									</select>
									<input type="hidden" name="department" id="department" value="All">
								</td>
							</tr>
							<tr id="brand">
								<td>&nbsp;</td>
								<td><label><b>Brand</b></label></td>
								<td colspan="2">
									<input type="hidden" id="brand_id" name="brand_id" size="1" value="{$form.brand_id}">
									<input id="autocomplete_brand" name="brand" value="{$form.brand_desc}" size="50">&nbsp;&nbsp;&nbsp;&nbsp;
									<div id="autocomplete_brand_choices" style="display:none;" class="autocomplete"></div>
								</td>
							</tr>
							<tr id="vendor">
								<td>&nbsp;</td>
								<td><label><b>Vendor</b></label></td>
								<td colspan="2">
									<input type="hidden" id="vendor_id" name="vendor_id" size="1" value="{$form.vendor_id}">
									<input id="autocomplete_vendor"  name="vendor" value="{$form.vendor_desc}" size=50>&nbsp;&nbsp;&nbsp;&nbsp;
									<div id="autocomplete_vendor_choices" style="display:none;" class="autocomplete"></div>
								</td>
							</tr>	
							<tr id="sku_items" style="vertical-align:top;">
								<td style="padding-top:9;"><input type="radio" id="setting_2" name="setting" value="sku_items" {if $group_item}checked{/if} onclick="COUPON_DIALOG.setting_changed(this.value);"></td>
								<td style="padding-top:9;"><label for="setting_3"><b>SKU Items</b></label></td>
								<td colspan="2" id="td_sku_items" style="display:none;">
									{include file="sku_items_autocomplete_multiple_add2.tpl"}
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			
			{* Membership Condition *}
			<tr>
				<td colspan="3" valign="top">
					<fieldset>
						<legend>Membership Condition [<a href="javascript:void(COUPON_MAIN.alert_member_condition_notification())">?</a>]</legend>
						<table id="tbl_member_limit_type">
							{* No Limit *}
							<tr>
								<td>
									<input type="radio" class="inp_member_limit_type" name="member_limit_type" value="" {if !$form.member_limit_type}checked {/if} onChange="COUPON_DIALOG.member_limit_type_changed();" />
								</td>
								<td><b>Member & Non-Member</b></td>
							</tr>
							
							{* All Member Only *}
							<tr>
								<td>
									<input type="radio" class="inp_member_limit_type" name="member_limit_type" value="all_member" {if $form.member_limit_type eq 'all_member'}checked {/if} onChange="COUPON_DIALOG.member_limit_type_changed();" />
								</td>
								<td><b>Member Only</b></td>
							</tr>
							
							{* Selected Member Type *}
							<tr>
								<td>
									<input type="radio" class="inp_member_limit_type" name="member_limit_type" value="member_type" {if $form.member_limit_type eq 'member_type'}checked {/if} onChange="COUPON_DIALOG.member_limit_type_changed();" />
								</td>
								<td><b>Selected Member Type Only</b></td>
							</tr>
							
							{* Selected Member *}
							<tr>
								<td>
									<input type="radio" class="inp_member_limit_type" name="member_limit_type" value="selected_member" {if $form.member_limit_type eq 'selected_member'}checked {/if} onChange="COUPON_DIALOG.member_limit_type_changed();" />
								</td>
								<td><b>Selected Member Only</b></td>
							</tr>
							
							{* Referral Program *}
							<tr>
								<td>
									<input type="radio" class="inp_member_limit_type" name="member_limit_type" value="referral_program" {if $form.member_limit_type eq 'referral_program'}checked {/if} onChange="COUPON_DIALOG.member_limit_type_changed();" />
								</td>
								<td><b>Referral Program</b></td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td colspan="2" style="height:100%;width:250px;">
					<div class="stdframe" style="height:100%;padding:2px;" id="div_member_limit_info">
						<div class="div_member_limit_info" id="div_member_limit_info-default" style="color:blue;{if $form.member_limit_type}display:none;{/if}">
							Allow for All Member and Non-Member.
						</div>
						
						<div class="div_member_limit_info" id="div_member_limit_info-all_member" style="color:blue;{if $form.member_limit_type ne 'all_member'}display:none;{/if}">
							Including All Member Type.
						</div>
						
						<div class="div_member_limit_info" id="div_member_limit_info-member_type" style="{if $form.member_limit_type ne 'member_type'}display:none;{/if}">
							<span style="color:blue;">Please Select Member Type</span>
							<ul style="list-style:none;">
								{foreach from=$config.membership_type key=mtype item=m_desc}
									{assign var=mtype_key value=$mtype|default:$m_desc}
									<li> <input type="checkbox" class="chx_member_type" name="member_limit_info[member_type][{$mtype_key}]" value="1" {if $form.member_limit_info.member_type.$mtype_key}checked {/if} />{$m_desc}</li>
								{/foreach}
							</ul>
						</div>
						
						<div class="div_member_limit_info" id="div_member_limit_info-selected_member" style="color:blue;{if $form.member_limit_type ne 'selected_member'}display:none;{/if}">
							Member Card No will be able to configure after coupon is print.
						</div>
						
						<div class="div_member_limit_info" id="div_member_limit_info-referral_program" style="{if $form.member_limit_type ne 'referral_program'}display:none;{/if}">
							<span style="color:blue;">Member will be entitled after key in Referral Code in Mobile App</span><br />
							<table>
								<tr>
									<td>
										Referrer [<a href="javascript:void(alert('Referrer = The member who give other people their Referral Code'))">?</a>] get
										<input type="text" style="width:50px;" name="referrer_coupon_get" value="{$form.referrer_coupon_get|default:1}" onChange="this.value=round(this.value);" /> 
										Coupon Per 
										<input type="text" style="width:50px;" name="referrer_count_need" value="{$form.referrer_count_need|default:1}" onChange="this.value=round(this.value);" /> 
										Referral.
									</td>
								</tr>
								
								<tr>
									<td>
										Referee [<a href="javascript:void(alert('Referee = The member who key in Referral Code'))">?</a>] get
										<input type="text" style="width:50px;" name="referee_coupon_get" value="{$form.referee_coupon_get}" onChange="this.value=round(this.value);" /> 
										Coupon after key in Referral Code.
									</td>
								</tr>
								
								<tr>
									<td>
										Required to key in Referral Code in 
										<input type="text" style="width:50px;" name="referee_day_limit" value="{$form.referee_day_limit|ifzero:''}" onChange="this.value=round(this.value);" placeholder="No Limit" />
										Days after registration.
									</td>
								</tr>
							</table>
						</div>
						
						<div id="div_member_options" style="{if !$form.member_limit_type}display:none;{/if}">
							<table>
								{* Use Limit *}
								<tr>
									<td nowrap>
										Use Limit [<a href="javascript:void(alert('Limit member to maximum can use how many time this coupon.'))">?</a>] 
									</td>
									<td>
										<input type="text" style="width:50px;" name="member_limit_count" value="{$form.member_limit_count}" onChange="this.value=round(this.value);" />
									</td>
								</tr>
								
								{* Available within xx day *}
								<tr>
									<td colspan="2" nowrap>
										Only available once registered in Mobile App since Day
										<input type="text" style="width:50px;" name="member_limit_mobile_day_start" value="{$form.member_limit_mobile_day_start|ifzero:''}" onChange="miz(this);" />
										to Day
										<input type="text" style="width:50px;" name="member_limit_mobile_day_end" value="{$form.member_limit_mobile_day_end|ifzero:''}" onChange="miz(this);" />
									</td>
								</tr>
								
								{* Profile Required *}
								<tr>
									<td colspan="2" nowrap>
										Membership Profile Required fields<br />
										<ul style="list-style:none;">											
											{foreach from=$member_limit_profile_info_list key=k item=v}
												<li> <input type="checkbox" name="member_limit_profile_info[{$k}]" value="1" {if $form.member_limit_profile_info.$k}checked {/if}/> {$v}</li>
											{/foreach}
										</ul>
									</td>
								</tr>
							</table>
						</div>
						
						
						
					</div>
				</td>
			</tr>
		</table>
	</form>
	<p align="center" id="p_save_coupon">
		{if $form.active}
			<input type="button" value="Close" onclick="COUPON_DIALOG.curtain_clicked();">
		{else}
			<input type="button" value="Save Coupon" onclick="COUPON_DIALOG.do_save();">
			<input type="button" value="Cancel" onclick="COUPON_DIALOG.curtain_clicked();">
		{/if}
	</p>
</div>

{*
<script>
COUPON_DIALOG.initialize();
</script>
*}
