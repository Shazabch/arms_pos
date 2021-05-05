{*
01/21/2016 10:13 AM Edwin
- Change popup save/edit, reload table by using ajax
- Network name not allow to edit except user_id = 1
- Add temporary counter with date from/to.

01/27/2016 14:33 Edwin
- Edit temporary counter settings authority changed to admin only.

03/24/2016 15:00 Edwin
- Bug fixed on allow counter settings saved wrongly when not login as admin

9/5/2016 17:34 Qiu Ying
- Hide "Return Policy Setting"

9/6/2016 11:00 Qiu Ying
- Counter Setup, set tick by default when add new counter (allow pos system to run, allow print receipt reference code)

10/27/2016 10:48 AM Qiu Ying
- Add "Digi SM-100" into Counter setup
- Add folder path for record "Digi SM-100" driver path

01/18/2017 9:45 AM Kee Kee
- Add "Block Goods Return" (Default No)

2017-09-18 15:17 PM Qiu Ying
- Bug fixed on calendar icon in temporary counter malfunction 

2017-10-09 16:17 PM Kee Kee
- Added new weight scale type "SM-320 with Tax"

2017-10-30 14:14 PM Kee Kee
- Added new weight scale type "SM-320 with Tax and Scale Type"

10/31/2018 5:27 PM Justin
- Bug fixed on calendar icon always appearing for everyone who can access this module.

4/19/2019 5:38 PM Justin
- Enhanced to have new settings "Self Checkout Counter" checkbox.

11/15/2019 10:48 AM Justin
- Enhanced to have new settings "Allow Time Attendance" checkbox.

8/13/2020 9:52 AM Andy
- Fixed spelling mistake.
- Fixed button style.

9/28/2020 6:30 PM William
- Enhanced to add new selection "Link with ARMS Fnb Suite Device".

11/9/2020 5:16 PM William
- Enhanced to change label "Link with ARMS Fnb Suite Device" to "Link with Suite Device".

02/16/2021 4:32 PM Rayleen
- Disable Self Checkout Counter checkbox if user is not Admin

3/16/2021 5:00 PM Shane
- Added POS Backend Mode setting.

4/16/2021 3:41 PM Shane
- Changed POS Backend Mode to checkbox and only editable by Admin.
*}
<script type="text/javascript">
var enable_suite_device = int('{$config.enable_suite_device}');
{literal}
submit_form = function(action) {
	if(action=='cancel'){
        if(!confirm('Exit without save?'))
			return false;
    default_curtain_clicked();
    return false;
	}
	else if(action=='save') {	
		if (document.f_b["id"].value == 0 && !document.f_b["network_name"].value.trim()) {
            alert('You must enter Network Name');
			return false;
        }
		var dateFrom = document.f_b["pos_settings[temporary_counter][date_from]"].value;
		//dateFrom = dateFrom.replace(/-/gi, "");
		
		var dateTo = document.f_b["pos_settings[temporary_counter][date_to]"].value;
		//dateTo = dateTo.replace(/-/gi, "");
		
		if ($('inp_temporary_counter_allow').checked) {
			if (!dateFrom || !dateTo || strtotime(dateFrom) > strtotime(dateTo)) {
				alert("Incorrect Temporary Counter Date.");
				return false;
			}
		}
		
		if (document.f_b["pos_settings[sync_weight]"].value == "SM-100" || document.f_b["pos_settings[sync_weight]"].value == "SM-320 with Tax" || document.f_b["pos_settings[sync_weight]"].value == "SM-320 with Tax and Scale Type"){
			if (!document.f_b["pos_settings[weight_scale_folderpath]"].value.trim()){
				alert("You must enter Folder Path");
				return false;
			}
			
			if (document.f_b["pos_settings[prefix]"].value.trim() == 0){
				alert("You must select a Barcode Type");
				return false;
			}
		}
	
		if(!confirm('Save changes?'))
			return false;
		$('btn_save').disable().value = 'Saving...';
		
		ajax_request(phpself, {
			parameters: document.f_b.serialize(),
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='Ok'){
					alert(msg);
					$('btn_save').enable().value = 'Save';
					return;
				}
				reload_table(true);
				alert('Save successfully.');
				default_curtain_clicked();
			}
		})
	}
}

check_counter_setup_list = function(obj) {
	var element = $$("div.counter_setup_mprice_list");
	
	for(i=0;i<element.length;i++)
		if(obj.checked)
		{
			$(element[i]).hide();
			$(element[i]).getElementsBySelector("input.inp_counter_setup_mprice_list").each(function(inp){
				inp.checked=false;
			});
		}
		else
			$(element[i]).show();
}

check_calendar = function(obj) {
	var element = $$("div.counter_temporary_date");
    if (obj.checked)
        $(element[0]).show();
	else 
		$(element[0]).hide();
}

change_weighing_scale = function(sel){
	var value = sel.value;
	if (value == "SM-100" || value == "SM-320 with Tax" || value == "SM-320 with Tax and Scale Type"){
		$("folderpath").show();
		$("prefix").show();
	}else{
		$("folderpath").hide();
		$("prefix").hide();
	}
}

check_device_fnb = function(){
	if(enable_suite_device){
		var suite_device_guid = document.f_b["suite_device_guid"].value;
		var tr_list = $$('#tb tr');
		var checkbox_allow_pos = document.f_b["pos_settings[allow_pos]"];
		if(suite_device_guid != ''){
			checkbox_allow_pos.checked = false;
			for(var i=0; i < tr_list.length; i++){
				if(!tr_list[i].classList.contains('tr_fnb_item')) tr_list[i].hide();
			}
		}else{
			for(var i=0; i < tr_list.length; i++){
				if(!tr_list[i].classList.contains('tr_fnb_item')) tr_list[i].show();
			}
		}
	}
}
{/literal}
</script>

<form method=post name=f_b>
    <input type=hidden name=a value="save">
    <input type=hidden name=id value="{$form.id}">
    <input type=hidden name=branch_id value="{$form.branch_id}">
    <table id="tb" >
    	<tr class="tr_fnb_item">
    		<td><b>Network Name</b></td>
			<td>
				<input onBlur="uc(this)" name="network_name" size="32" maxlength="32" value="{$form.network_name}" {if $form.id > 0 && $sessioninfo.id ne 1}readonly{/if} />
			</td>
    	</tr>
        <tr class="tr_fnb_item">
            <td><b>Location</b></td>
            <td>
                <input id="location" onBlur="uc(this)" name="location" size=32 maxlength=32 value="{$form.location}">
                <span id="location_load"></span>
                <div id="div_autocomplete_location" class="autocomplete" style="display:none;height:150px !important;width:200px !important;overflow:auto !important;z-index:100"></div>
            </td>
        </tr>
		{if $config.enable_suite_device}
		<tr class="tr_fnb_item">
			<td><b>Link with Suite Device</b></td>
			<td>
				<select name="suite_device_guid" onchange="check_device_fnb();" {if $sessioninfo.id neq 1}disabled{/if}>
					<option value="" {if !$form.suite_device_guid}selected{/if}>Not ARMS Fnb</option>
					{foreach from=$device_list key=k item=v}
						<option value="{$k}" {if $form.suite_device_guid eq $k}selected {/if}>{$v}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{/if}
        <tr>
            <td valign=top><b>POS Settings</b></td>
            <td><input type=checkbox name="pos_settings[allow_pos]" id=p1 value="1" {if $form.id}{if $form.pos_settings.allow_pos eq 1}checked{/if}{else}checked{/if}><label for=p1>Allow POS System to run</label><br></td>
        </tr>
        <tr>
            <td valign=top><b>Receipt Reference Code</b></td>
            <td><input type="checkbox" name="pos_settings[allow_print_receipt_reference_code]" id="r1" value="1" {if $form.id}{if $form.pos_settings.allow_print_receipt_reference_code eq 1}checked{/if}{else}checked{/if}><label for="r1">Allow print receipt reference code on receipt</label></td>
        </tr>
        <tr>
            <td valign="top"><b>Deposit Setting</b></td>
            <td><input type="checkbox" name="pos_settings[allow_do_deposit_payment]" id="d1" value="1" {if $form.pos_settings.allow_do_deposit_payment eq 1}checked{/if}><label for="d1">Allow to do deposit</label></td>
        </tr>
        <!--<tr>
            <td valign="top"><b>Return Policy Setting</b></td>
            <td><input type="checkbox" name="pos_settings[allow_do_return_policy]" id="d2" value="1" {if $form.pos_settings.allow_do_return_policy eq 1}checked{/if}><label for="d2">Allow to do return policy</label></td>
        </tr>-->
        <tr>
            <td valign="top"><b>Trade In Setting</b></td>
            <td><input type="checkbox" name="pos_settings[allow_do_trade_in]" id="d3" value="1" {if $form.pos_settings.allow_do_trade_in eq 1}checked{/if}><label for="d3">Allow to do trade in</label></td>
        </tr>
        <tr>
            <td valign="top"><b>Block Goods Return</b></td>
            <td><input type="checkbox" name="pos_settings[block_goods_return]" id="d1" value="1" {if $form.pos_settings.block_goods_return eq 1}checked{/if}><label for="d1">Not Allow to do Goods Return</label></td>
        </tr>
        {if $config.membership_control_counter_adjust_point}
        <tr>
            <td valign="top"><b>Adjust Member Point Setting</b></td>
            <td><input type=checkbox name="pos_settings[counter_allow_adjust_member_point]" id="d4" value="1" {if $form.pos_settings.counter_allow_adjust_member_point eq 1}checked{/if}><label for="d4">Allow to do adjust member point setting</label><br /></td>
        </tr>
        {/if}
        
        <tr>
            <td valign="top"><b>Hold Bill Slot</b></td>
            <td>
                <select name="pos_settings[hold_bill_slot]">
                    <option value=0 {if $form.pos_settings.hold_bill_slot eq 0}selected{/if}>0</option>
                    <option value=2 {if $form.pos_settings.hold_bill_slot eq 2}selected{/if}>2</option>
                    <option value=5 {if $form.pos_settings.hold_bill_slot eq 5}selected{/if}>5</option>
                    <option value=10 {if $form.pos_settings.hold_bill_slot eq 10}selected{/if}>10</option>
                    <option value=20 {if $form.pos_settings.hold_bill_slot eq 20}selected{/if}>20</option>
                </select>
            </td>
        </tr>
        <tr>
            <td valign=top><b>Membership Settings</b></td>
            <td>
                <input type=checkbox name="membership_settings[allow_membership]" id=m1 value="1" {if $form.membership_settings.allow_membership eq 1}checked{/if}> <label for=m1>Allow Membership Module to run</label><br>
                <input type=checkbox name="membership_settings[allow_application]" id=m2 value="1"{if $form.membership_settings.allow_application eq 1}checked{/if}> <label for=m2>Allow Membership Application</label><br>
				<input type=checkbox name="membership_settings[have_inventory]" id=m3 onClick="if(this.checked)hidediv('iv');else showdiv('iv');" value="1" {if $form.membership_settings.have_inventory eq 1}checked{/if}><label for=m3>Have Inventory</label><br>
				<div id=iv style="{if $form.membership_settings.have_inventory eq 1}display: none{/if}">Inventory Counter:
					<select name="membership_settings[inventory_counter_id]">
					{foreach from=$invCounter item=counterName}
						<option value="{$counterName.id}" {if $counterName.id eq $form.membership_settings.inventory_counter_id}selected{/if}>{$counterName.network_name}</option>
					{/foreach}
					</select>
				</div>
            </td>
        </tr>
        <tr {if !$mprice}style="display:none;"{/if}>
            <td valign="top"><b>Allow MPrice</b></td>
            <td>
				<input type="checkbox" name="mprice_settings[not_allow]" id="mp1" value="1" onclick="check_counter_setup_list(this)" {if $form.mprice_settings.not_allow}checked{/if}><label for="mp1">Not Allow</label><br />
				{assign var=i value=2}
				{foreach from=$mprice item=val}
					<div class="counter_setup_mprice_list" style="{if $form.mprice_settings.not_allow}display:none{/if}"><input class="inp_counter_setup_mprice_list" type="checkbox" name="mprice_settings[{$val}]" id="mp{$i}" value="1" {if $form.mprice_settings.$val eq 1}checked{/if}><label for="mp{$i}">{$val}</label><br/></div>
				{assign var=i value=$i+1}
				{/foreach}	
            </td>
        </tr>
        <tr>
            <td valign="top"><b>Sync to weight scale</b></td>
            <td>
                <select name="pos_settings[sync_weight]" onchange="change_weighing_scale(this)">
                    <option value="0" {if $form.pos_settings.sync_weight eq 0}selected{/if}>No</option>
                    <option value="BC11 800" {if $form.pos_settings.sync_weight eq 'BC11 800'}selected{/if}>BC11 800</option> {* Barcode No. ECO[23] = 99 *}
                    <option value="BC11 800 v2" {if $form.pos_settings.sync_weight eq 'BC11 800 v2'}selected{/if}>BC11 800 v2</option> {* Barcode No. ECO[23] = 01 *}
                    <option value="SM-100" {if $form.pos_settings.sync_weight eq 'SM-100'}selected{/if}>SM-100 / SM-320</option> {*  *}
                    <option value="SM-320 with Tax" {if $form.pos_settings.sync_weight eq 'SM-320 with Tax'}selected{/if}>SM-320 with Tax</option> {* Added new tax column *}
                    <option value="SM-320 with Tax and Scale Type" {if $form.pos_settings.sync_weight eq 'SM-320 with Tax and Scale Type'}selected{/if}>SM-320 with Tax and Scale Type</option> {* Added new tax column and scale type column*}
                </select>
            </td>
        </tr>
		{if $form.pos_settings.sync_weight eq "SM-100" || $form.pos_settings.sync_weight eq "SM-320 with Tax" || $form.pos_settings.sync_weight eq "SM-320 with Tax and Scale Type"}
			{assign var=is_sm value=1}
		{/if}
		<tr id="folderpath" {if !$is_sm}style="display:none"{/if}>
			<td valign="top"><b>Folder Path</b></td>
			<td>
				<input type="text" name="pos_settings[weight_scale_folderpath]" value="{if $is_sm}{$form.pos_settings.weight_scale_folderpath}{/if}" />
			</td>
		</tr>
		<tr id="prefix" {if !$is_sm}style="display:none"{/if}>
			<td valign="top"><b>Barcode Type</b></td>
			<td>
				<select name="pos_settings[prefix]">
					<option value="0" {if $form.pos_settings.prefix eq 0}selected{/if}>Please select</option>
                    <option value="barcode_unit_code_prefix" {if $form.pos_settings.prefix eq 'barcode_unit_code_prefix'}selected{/if}>Barcode Unit Code Prefix</option>
                    <option value="barcode_price_code_prefix" {if $form.pos_settings.prefix eq 'barcode_price_code_prefix'}selected{/if}>Barcode Price Code Prefix</option>
                    <option value="barcode_price_n_unit_code_prefix" {if $form.pos_settings.prefix eq 'barcode_price_n_unit_code_prefix'}selected{/if}>Barcode Unit Price & Unit Code Prefix</option>
                    <option value="barcode_total_price_n_unit_code_prefix" {if $form.pos_settings.prefix eq 'barcode_total_price_n_unit_code_prefix'}selected{/if}>Barcode Price & Unit Code Prefix</option>
                </select>
			</td>
		</tr>
        <tr>
            <td valign="top"><b>Temporary Counter</b></td>
            <td>
				<label>
					<input type="checkbox" id="inp_temporary_counter_allow" name="pos_settings[temporary_counter][allow]" value="1" onclick="check_calendar(this)" {if $form.pos_settings.temporary_counter.allow eq 1}checked{/if} {if $sessioninfo.id ne 1}disabled{/if}/>Yes
				</label>
				{if $sessioninfo.id ne 1}
					<input type="hidden" name="pos_settings[temporary_counter][allow]" value="{$form.pos_settings.temporary_counter.allow}">
				{/if}
				<div class="counter_temporary_date" style="{if !$form.pos_settings.temporary_counter.allow}display:none{/if}">
					<table>
						<tr>
					        <td>From</td>
							<td>
							    <input type="text" style="height:18px;" id="temp_start_date" name="pos_settings[temporary_counter][date_from]" size="8" value="{$form.pos_settings.temporary_counter.date_from}" readonly/>
								{if $sessioninfo.id eq 1}
									<img align="absbottom" src="ui/calendar.gif" id="b_temp_start_date" style="cursor: pointer;" title="Select Start Date" />
								{/if}
					        </td>
						</tr>
					    <tr>
					        <td>to</td>
					        <td>
					            <input type="text" style="height:18px;" id="temp_end_date" name="pos_settings[temporary_counter][date_to]" size="8" value="{$form.pos_settings.temporary_counter.date_to}" readonly/>
								{if $sessioninfo.id eq 1}
									<img align="absbottom" src="ui/calendar.gif" id="b_temp_end_date" style="cursor: pointer;" title="Select End Date"/>
								{/if}
					        </td>
					    </tr>
					</table>
				</div>
            </td>
        </tr>
		<tr>
            <td valign="top"><b>Self Checkout Counter</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="alert('- Available for ARMS POS V.198 or above.\n- Monitor from Counter is required to support 1024x768 resolution or above.');"/></td>
            <td><input type="checkbox" name="pos_settings[is_self_checkout]" id="d1" value="1" {if $form.pos_settings.is_self_checkout eq 1}checked{/if} {if $sessioninfo.id ne 1}disabled{/if}><label for="d1">Yes</label></td>
        </tr>
		<tr>
            <td valign="top"><b>Allow Time Attendance</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="alert('- Available for ARMS POS V.202 or above.');" /></td>
            <td><input type="checkbox" name="pos_settings[allow_time_attendance]" value="1" {if $form.pos_settings.allow_time_attendance eq 1}checked{/if}>Yes</td>
        </tr>

        {if $config.pos_settings_pos_backend_tab}
        <tr>
            <td valign="top"><b>POS Backend Mode</b></td>
            <td>
                <input type="checkbox" name="counter_pb_mode" value="1" {if $form.counter_pb_mode eq 1}checked{/if} {if $sessioninfo.id ne 1} disabled {/if}>Yes</td>
            </td>
        </tr>
        {/if}

        <tr style="position: absolute; top:90%; right:35%;" class="tbl_btn tr_fnb_item">
            <td align=center colspan=2 style="border: 0px !important"><br>
                <input type=button value="Save" id="btn_save" onclick="submit_form('save');">
				<input type=button value="Cancel" onclick="submit_form('cancel');">
            </td>
        </tr>
		
    </table>
</form>

<script>
check_device_fnb();
{if $readonly}
	Form.disable(document.f_b);
{else}
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
	location_autocomplete();
	{if $sessioninfo.id eq 1}activate_calendar();{/if}
{/if}
</script>