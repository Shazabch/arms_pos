{*
9/12/2014 5:20 PM Justin
- Enhanced to update status of GST settings when check/uncheck GST registration checkbox.

9/24/2014 3:08 PM Justin
- Enhanced to use different ways to store GST Settings.
- Removed "Service Charge (%)" and place it into Branch Masterfile.

9/26/2014 10:17 AM Justin
- Enhanced to have "Selling Price Rounding Condition".

10/14/2014 10:23 AM Justin
- Enhanced not to disable the form when activate/deactivate GST settings.
- Bug fixed on settings that is using checkbox will not save when uncheck it.

10/16/2014 2:06 PM Justin
- Enhanced to checked by default for "Discount After GST Selling Price" for first time save the form.

10/17/2014 10:33AM dingren
- add Service Charge Calculate After GST

10/23/2014 11:41AM dingren
- hide Deposit GST Type

10/29/2014 05:55PM dingren
- hide Receipt Prefix No

11/4/2014 2:14 PM Justin
- Enhanced to have cost price discount before/after GST.
- Enhanced selling price discount before/after GST maintain by using dropdown list.

11/6/2014 4:44PM Ding Ren
- Remove Goods Return Reason Settings

11/25/2014 11:17 AM Justin
- Added new option "Skip GST validations".

12/29/2014 5:45 PM Justin
- Changed the wording "Skip GST Validations" into "Enable GST Feature Now".
- Enhanced to preset the title list of Preset Special Exemption Remark Field to be the same with Preset Tax Invoice Remark Title.
- Enhanced Special Exemption GST Type to only show GST with 0% rate only.
- Added new field "Inclusive Tax".

01/27/2015 10:30 AM Dingren
- enable back deposite GST Type

01/04/2015 11:41 AM Dingren
- Change Selling Price Discount default to after GST

2/4/2015 4:10 PM Andy
- Add Export GST Type. (for consignment mode only)

2/16/2015 3:59 PM Andy
- Add a notice to let user know if they change the "Inclusive Tax", system will take longer time to update.

03/06/2015 3:23 PM dingren
- Add Enable GST with 0% before start date

03/12/2015 3:23 PM dingren
- Add Calculate Tax for F.O.C item (POS only)

03/16/2015 2:14 PM dingren
- Hide Calculate Tax for F.O.C item (POS only)

3/16/2015 5:45 PM Justin
- Enhanced to remove the "Please Select" for "Service Charge", "Deposit GST Type" and "Special Exemption GST Type".

3/30/2015 10:12 AM Justin
- Enhanced to change caption from "Inclusive Tax" into "Selling Price Inclusive Tax".
- Enhanced to have Member Card Inclusive Tax and Member Card Service Charge Type fields.

3/31/2015 5:49 PM Andy
- Enhanced to have Designated Areas GST.

6/25/2015 4:04 PM Eric
- Enhanced Branch should only can view Masterfile GST setting and unable to edit

07/09/2015 5:52PM dingren
- add global input tax and global output tax

9/13/2016 4:20 PM Andy
- Enhanced all gst type default should be empty and user must select it then only can save.
- Enhanced the validation to block user to save if found got gst type is not selected.

10/31/2016 10:11 AM Andy
- Enhanced to only allow user to select selling price discount after gst, but will not affect old settings.

11/30/2016 4:20 PM Andy
- Enhanced to always have default tax invoice remark value. (Name, Address, BRN, GST Reg No)

1/9/2017 2:47 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.

2/15/2017 9:47 AM Qiu Ying
- Enhanced to have Security Deposit GST Type for those deposit without items

8/17/2017 2:35 PM Justin
- Enhanced to remove the options "Enable GST with 0% before start date".

10/31/2017 11:38 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.

11/10/2017 1:54 PM Justin
- Bug fixed on wording "Clause" instead of "Claus".

6/23/2020 11:53 AM Sheila
- Updated button alignment

*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}

.td_label_top{
	padding-top:3;
	vertical-align:top;
}

input[readonly]{
	background-color: #dcdcdc;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_code = '{$BRANCH_CODE}';

{literal}
var GST_SETTINGS_MODULE = {
	form_element: undefined,
	initialize : function(){
		
		this.form_element = document.f_a;

		// event when user click "save"
		$('active_chkbox').observe('click', function(){
            GST_SETTINGS_MODULE.toggle_form();
		});

		/*$('skip_gst_validate').observe('change', function(){
			if(this.value == '1'){
				$('force_zero_rate_before_start_date').removeAttribute('disabled');
			}
			else{
				$('force_zero_rate_before_start_date').setAttribute('disabled', 'disabled');
			}
		});*/
		
		// pre-add row for Preset Special Exemption Remark Field
		var pserf_data = $('pserf_tbl').getElementsByClassName('pserf_title');
		if(pserf_data.length == 0) this.pserf_add_row();

		// pre-add row for Preset Tax Invoice Remark Title if no record
		var ptirt_data = $('ptirt_tbl').getElementsByClassName('ptirt_title');
		if(ptirt_data.length == 0) this.ptirt_add_row();

		if(branch_code == "HQ"){
			// event when user click "save"
			$('save_btn').observe('click', function(){
	            GST_SETTINGS_MODULE.validate();
			});
		}else{
			this.disable_form();
		}

		// pre-add row for Goods Return Reason Settings if no record
		//var grrs_data = $('grrs_tbl').getElementsByClassName('grrs_code');
		//if(grrs_data.length == 0) this.grrs_add_row();
		
		//if($('active_chkbox').checked == false) Form.disable(document.f_a);
	},
	validate : function(){
		var passed = check_required_field(this.form_element);
		if(!passed)	return false;

		this.save();
	},
	save : function(){
		/*this.form_element = document.f_a;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'update'
		};
		prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert("Save successfully.");
						document.f_a['id'].value = ret['id'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				
				// prompt the error
			    if(err_msg) alert("You have encountered below errors:\n"+err_msg);
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});*/
		if(!confirm('Are you sure all the settings are correct?'))	return false;
		
		document.f_a.submit();
	},
	
	perc_check : function(obj){
		mf(obj);
		
		if(obj.value < 0 || obj.value > 100){
			obj.value = 0;
		}
	},
	
	/*grrs_add_row : function(){
		var new_tr = $('temp_grrs_row').cloneNode(true).innerHTML;

		new Insertion.Bottom($('reason_settings'), new_tr);
	},

	grrs_remove_row : function(obj){
		if(obj == undefined) return;

		Element.remove(obj.parentNode.parentNode);
	},*/
	
	toggle_form : function(){
		if($('active_chkbox').checked == false){
			if(!confirm("Are you sure want to away from GST registered?")){
				$('active_chkbox').checked = true;
				return;
			}
			//Form.disable(this.form_element);
			this.form_element.active.value = 0;
		}else{
			//Form.enable(this.form_element);
			this.form_element.active.value = 1;
		}
		
		/*var prm = {
			a: 'ajax_toggle_active',
			id: this.form_element.id.value,
			status: this.form_element.active.value
		};

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				
				// prompt the error
				if(err_msg) alert("You have encountered below errors:\n"+err_msg);
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});*/
	},

	pserf_add_row : function(){
		var new_tr = $('temp_pserf_row').cloneNode(true).innerHTML;

		new Insertion.Bottom($('exemption_remark_settings'), new_tr);
	},

	pserf_remove_row : function(obj){
		if(obj == undefined) return;

		Element.remove(obj.parentNode.parentNode);
	},
	
	ptirt_add_row : function(){
		var new_tr = $('temp_ptirt_row').cloneNode(true).innerHTML;

		new Insertion.Bottom($('tax_invoice_remark_settings'), new_tr);
	},

	ptirt_remove_row : function(obj){
		if(obj == undefined) return;

		Element.remove(obj.parentNode.parentNode);
	},

	disable_form : function(){
		disable_sub_ele(this.form_element, false);
	}
}

</script>
{/literal}

<table style="display:none;">
	{*tbody id="temp_grrs_row" class="temp_grrs_row">
		<tr>
			<td><img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.grrs_remove_row(this);" class="clickable" title="Delete this row" /></td>
			<td><input type="text" name="grr_settings[code][]" class="grrs_code"></td>
			<td><input type="text" name="grr_settings[description][]" size="60"></td>
		</tr>
	</tbody>*}
	<tbody id="temp_pserf_row" class="temp_pserf_row">
		<tr>
			<td><img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.pserf_remove_row(this);" class="clickable" title="Delete this row" /></td>
			<td><input type="text" name="exemption_remark_field[title][]" class="pserf_code"></td>
		</tr>
	</tbody>
	<tbody id="temp_ptirt_row" class="temp_ptirt_row">
		<tr>
			<td><img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.ptirt_remove_row(this);" class="clickable" title="Delete this row" /></td>
			<td><input type="text" name="tax_invoice_remark[title][]" class="ptirt_code"></td>
		</tr>
	</tbody>
</table>

<h1>{$PAGE_TITLE}</h1>


{if $err}
<div id="err"><div class="errmsg"><ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{if $smarty.request.save}
<img src="ui/approved.png" title="Saved GST information" border="0"> <b>Saved GST information.</b><br /><br />
{/if}

<div id="udiv" class="stdframe">

<input type="checkbox" id="active_chkbox" value="1" {if $form.active}checked{/if} {if $BRANCH_CODE ne "HQ"}disabled{/if} /> <b>This company is under GST registered</b>
<form method="post" name="f_a" onSubmit="return GST_SETTINGS_MODULE.validate();" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="update">
	<input type="hidden" name="active" value="{$form.active}">
	<table id="gst_settings">
		<tr>
			{*<td><b>Receipt Prefix No.</b></td>
			<td><input type="text" onBlur="uc(this)" name="receipt_prefix_no" value="{$form.receipt_prefix_no}" size="20" maxlength="40"> <font color="red" size="+2">*</font></td>*}
			<td><b>Selling Price Discount</b></td>
			<td colspan="3">
				<select name="disc_after_sp">
					<option value="1" {if $form.disc_after_sp == 1}selected{/if}>After GST</option>
					{if isset($form.disc_after_sp) && $form.disc_after_sp == 0}
						<option value="0" {if isset($form.disc_after_sp) && $form.disc_after_sp == 0}selected{/if}>Before GST</option>
					{/if}
				</select>
				<span><font color="blue">
					* POS only
				</font></span>
			</td>
		</tr>
		{*
		<tr>
			<td><b>Calculate Tax for F.O.C item</b></td>
			<td colspan="3">
				<select name="cal_tax_foc_item">
					<option value="0" {if $form.cal_tax_foc_item == 0}selected{/if}>No</option>
					<option value="1" {if $form.cal_tax_foc_item == 1}selected{/if}>Yes</option>
				</select>
				<span><font color="blue">
					* POS only
				</font></span>
			</td>
		</tr>
		*}
		<tr>
			<td><b>Selling Price Rounding Condition</b></td>
			<td>
				<select name="sp_rounding_condition">
					<option value="" {if !$form.sp_rounding_condition}selected{/if}>--</option>
					{foreach from=$sp_rc_list key=r item=val}
						<option value="{$val}" {if $form.sp_rounding_condition eq $val}selected{/if}>{$val}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Enable GST Feature Now</b></td>
			<td colspan="3">
				<select name="skip_gst_validate" id="skip_gst_validate">
					<option value="1" {if $form.skip_gst_validate}selected{/if}>Yes</option>
					<option value="0" {if !$form.skip_gst_validate}selected{/if}>No</option>
				</select>
				<span><font color="blue">
					* This will skip all validations such as Global GST Date, Masterfile Branch, Vendor, DO/PO Date and etc.
				</font></span>
			</td>
			<!--td><b>Cost Price Discount</b></td>
			<td>
				<select name="disc_after_cp">
					<option value="0" {if !$form.disc_after_sp}selected{/if}>Before GST</option>
					<option value="1" {if $form.disc_after_sp}selected{/if}>After GST</option>
				</select>
			</td-->
		</tr>
		{*<tr>
			<td><b>Enable GST with 0% before start date</b></td>
			<td colspan="3">
				<select id="force_zero_rate_before_start_date" name="force_zero_rate_before_start_date" {if !$form.skip_gst_validate}disabled{/if}>
					<option value="0" {if !$form.force_zero_rate_before_start_date}selected{/if}>No</option>
					<option value="1" {if $form.force_zero_rate_before_start_date}selected{/if}>Yes</option>
				</select>
				<span><font color="blue">
					* This will force all gst rate into 0% for POS, DO/PO, Sales Order and etc. (Except Category, SKU item and Change Price)
				</font></span>
			</td>
		</tr>*}
		<tr>
			<td><b>Selling Price Inclusive Tax</b></td>
			<td colspan="3">
				<select name="inclusive_tax">
					<option value="yes" {if $form.inclusive_tax eq 'yes'}selected{/if}>Yes</option>
					{if isset($form.inclusive_tax) && $form.inclusive_tax == 'no'}
						<option value="no" {if $form.inclusive_tax eq 'no'}selected{/if}>No</option>
					{/if}
				</select>
				<span><font color="blue">
					* This will applies to all categories (LINE) which is using inherit, changing this will cause system to take longer time to update.
				</font></span>
			</td>
			<!--td><b>Cost Price Discount</b></td>
			<td>
				<select name="disc_after_cp">
					<option value="0" {if !$form.disc_after_sp}selected{/if}>Before GST</option>
					<option value="1" {if $form.disc_after_sp}selected{/if}>After GST</option>
				</select>
			</td-->
		</tr>
		<tr>
			<td><b>Global Input Tax</b></td>
			<td colspan="3">
				<select name="global_input_tax" class="required" title="Global Input Tax">
					<option value="">-- Please Select --</option>
					{foreach from=$supply_gst_list key=row item=r}
						<option value="{$r.id}" {if $form.global_input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
		<tr>
			<td><b>Global Output Tax</b></td>
			<td colspan="3">
				<select name="global_output_tax" class="required" title="Global Output Tax">
					<option value="">-- Please Select --</option>
					{foreach from=$gst_list key=row item=r}
						<option value="{$r.id}" {if $form.global_output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
		<tr>
			<!--td><b>Service Charge</b></td>
			<td><input type="text" name="service_charge" value="{$form.service_charge}" size="5" class="r" maxlength="3" onchange="GST_SETTINGS_MODULE.perc_check(this);">&nbsp;% <font color="red" size="+2">*</font></td-->
			<td><b>Service Charge GST Type</b></td>
			<td colspan="3">
				<select name="service_charge_type" class="required" title="Service Charge GST Type">
					<option value="">-- Please Select --</option>
					<!--option value="" {if !$form.service_charge_type}selected{/if}>Please Select</option-->
					{foreach from=$gst_list key=row item=r}
						<option value="{$r.id}" {if $form.service_charge_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>

		</tr>
		<tr>
			<td><b>Deposit GST Type</b></td>
			<td>
				<select name="deposit_type" class="required" title="Deposit GST Type">
					<option value="">-- Please Select --</option>
					<!--option value="" {if !$form.deposit_type}selected{/if}>Please Select</option-->
					{foreach from=$gst_list key=row item=r}
						<option value="{$r.id}" {if $form.deposit_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
		<tr>
			<td><b>Security Deposit GST Type</b></td>
			<td>
				<select name="security_deposit_type" class="required" title="Security Deposit GST Type">
					<option value="">-- Please Select --</option>
					<!--option value="" {if !$form.deposit_type}selected{/if}>Please Select</option-->
					{foreach from=$gst_list key=row item=r}
						<option value="{$r.id}" {if $form.security_deposit_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
		<tr>
			<td><b>Special Exemption GST Type</b></td>
			<td colspan="3">
				<select name="special_exemption_type" class="required" title="Special Exemption GST Type">
					<option value="" {if !$form.special_exemption_type}selected{/if}>-- Please Select --</option>
					{foreach from=$exempted_gst_list key=row item=r}
						<option value="{$r.id}" {if $form.special_exemption_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
		<tr>
			<td class="td_label_top"><b>Special Exemption Relief Clause Remark</b></td>
			<td colspan="3">
				<textarea name="special_exemption_relief_claus_remark" cols="50" rows="4" class="required"  title="Special Exemption Relief Clause Remark">{$form.special_exemption_relief_claus_remark}</textarea>
			</td>
		</tr>
		{if $config.consignment_modules}
			<tr>
				<td><b>Export GST Type</b></td>
				<td colspan="3">
					<select name="export_gst_type" class="required" title="Export GST Type">
						<option value="" {if !$form.export_gst_type}selected{/if}>-- Please Select --</option>
						{foreach from=$gst_list key=row item=r}
							<option value="{$r.id}" {if $form.export_gst_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
						{/foreach}
					</select>
					<font color="red" size="+2">*</font>
				</td>
			</tr>
			<tr>
				<td><b>Designated Areas GST Type</b></td>
				<td colspan="3">
					<select name="designated_gst_type" class="required" title="Designated Areas GST Type">
						<option value="" {if !$form.designated_gst_type}selected{/if}>-- Please Select --</option>
						{foreach from=$gst_list key=row item=r}
							<option value="{$r.id}" {if $form.designated_gst_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
						{/foreach}
					</select>
					<font color="red" size="+2">*</font>
				</td>
			</tr>
		{/if}
		<tr>
			<td class="td_label_top"><b>Preset Special<br />Exemption Remark Field</b></td>
			<td valign="top">
				<fieldset>
					<table width="100%" id="pserf_tbl">
						<tr class="header">
							<td width="3%">&nbsp;</td>
							<td width="27%"><b>Title</b></td>
						</tr>
						<tbody id="exemption_remark_settings">
						{foreach from=$form.exemption_remark_field.title key=rid item=pserf_title}
							<tr>
								<td>{if $BRANCH_CODE eq "HQ"}<img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.pserf_remove_row(this);" class="clickable" title="Delete this row" />{/if}</td>
								<td><input type="text" name="exemption_remark_field[title][]" value="{$pserf_title}" class="pserf_title"></td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<input type="button" value="Add Row" onclick="GST_SETTINGS_MODULE.pserf_add_row(this);" />
				</fieldset>
			</td>
			<td class="td_label_top"><b>Preset Tax Invoice Remark Title</b>
				<br />
				<span style="color:blue;">
					{foreach from=$taxInvoiceDefaultRemarkList item=v name=f}
						{if !$smarty.foreach.f.first}, {/if}
						{$v}
					{/foreach}
					<br />are always required.
				</span>
			</td>
			<td valign="top">
				<fieldset>
					<table width="100%" id="ptirt_tbl">
						<tr class="header">
							<td width="3%">&nbsp;</td>
							<td width="27%"><b>Title</b></td>
						</tr>
						<tbody id="tax_invoice_remark_settings">
						{foreach from=$form.tax_invoice_remark.title key=rid item=ptirt_title}
							{assign var=remark_can_edit value=1}
							{if in_array($ptirt_title, $taxInvoiceDefaultRemarkList)}
								{assign var=remark_can_edit value=0}
							{/if}
							<tr>
								<td>
									{if $BRANCH_CODE eq "HQ" and $remark_can_edit}
										<img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.ptirt_remove_row(this);" class="clickable" title="Delete this row" />
									{/if}
								</td>
								<td><input type="text" name="tax_invoice_remark[title][]" value="{$ptirt_title}" class="ptirt_title" {if !$remark_can_edit}readonly{/if} ></td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<input type="button" value="Add Row" onclick="GST_SETTINGS_MODULE.ptirt_add_row(this);" />
				</fieldset>
			</td>
		</tr>
		{*<tr>
			<td class="td_label_top"><b>Goods Return<br />Reason Settings</b></td>
			<td colspan="3">
				<fieldset>
					<table width="100%" id="grrs_tbl">
						<tr class="header">
							<td width="3%">&nbsp;</td>
							<td width="27%"><b>Code</b></td>
							<td width="70%"><b>Description</b></td>
						</tr>
						<tbody id="reason_settings">
						{foreach from=$form.grr_settings.code key=rid item=grrs_code}
							<tr>
								<td><img src="/ui/closewin.png" align="absmiddle" onClick="GST_SETTINGS_MODULE.grrs_remove_row(this);" class="clickable" title="Delete this row" /></td>
								<td><input type="text" name="grr_settings[code][]" value="{$grrs_code}" class="grrs_code"></td>
								<td><input type="text" name="grr_settings[description][]" value="{$form.grr_settings.description.$rid}" size="60"></td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<input type="button" value="Add Row" onclick="GST_SETTINGS_MODULE.grrs_add_row(this);" />
				</fieldset>
			</td>
		</tr>*}
		<tr>
			<td><b>Member Card Inclusive Tax</b></td>
			<td colspan="3">
				<select name="membership_inclusive_tax">
					<option value="yes" {if $form.membership_inclusive_tax eq 'yes'}selected{/if}>Yes</option>
					<option value="no" {if $form.membership_inclusive_tax eq 'no'}selected{/if}>No</option>
				</select>
				<span><font color="blue">
					* This will applies to all membership counters.
				</font></span>
			</td>
		</tr>
		<tr>
			<td><b>Member Card Service Charge Type</b></td>
			<td colspan="3">
				<select name="membership_gst_type" class="required" title="Member Card Service Charge Type">
					<option value="">-- Please Select --</option>
					{foreach from=$gst_list key=row item=r}
						<option value="{$r.id}" {if $form.membership_gst_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
				<font color="red" size="+2">*</font>
			</td>
		</tr>
	</table>
	<!-- bottom -->
	{if $BRANCH_CODE eq "HQ"}
	<div align="center">
		<input type="button" value="Save" id="save_btn"> 
	</div>
	{/if}
</form>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
GST_SETTINGS_MODULE.initialize();
</script>

{include file=footer.tpl}
