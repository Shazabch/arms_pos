{*
4/16/2019 3:00 PM Andy
- Added Cash Sales Integration.

5/13/2019 5:41 PM Andy
- Added Cash Sales Deposit Product Code.

5/17/2019 4:48 PM Andy
- Added Account Receivable Integration.

06/25/2020 10:44 AM Sheila
- Updated button css
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var ARMS_ACC_SETTINGS = {
	f: undefined,
	initialise: function(){
		Calendar.setup({
			inputField     :    "inp_integration_start_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_integration_start_date",
			align          :    "Bl",
			singleClick    :    true,
		});
		
		this.f = document.f_a;
	},
	// function when user change use own settings
	use_own_settings_changed: function(){
		var c = int(this.f['data[acc_settings][other][use_own_settings]'].value);
		
		if(c){
			$('div_form_data').show();
		}else{
			$('div_form_data').hide();
		}
	},
	// function when user click on button save
	save_clicked: function(){
		
		this.save_data();
	},
	// function to validate form data
	validate_data: function(){
		var need_check_acc_settings_list = true;
		if(this.f['data[acc_settings][other][use_own_settings]']){
			var use_own_settings = int(this.f['data[acc_settings][other][use_own_settings]'].value);
			if(!use_own_settings){
				need_check_acc_settings_list = false;
			}
		}
		
		if(need_check_acc_settings_list){
			// check all required fields
			if(!check_required_field(this.f))	return false;
		}
		
		
		return true;
	},
	// core function to save data
	save_data: function(){
		// check form
		if(!this.validate_data())	return false;
		
		var params = $(this.f).serialize();
		var btn_save = $('btn_save');
		btn_save.value = 'Saving...';
		btn_save.disabled = true;
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    
				btn_save.value = 'Save';
				btn_save.disabled = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Save successfully!');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_settings" />
	
	{if $BRANCH_CODE != 'HQ'}
	  <b>Use own branch settings</b> <select name="data[acc_settings][other][use_own_settings]" onchange="ARMS_ACC_SETTINGS.use_own_settings_changed()">
		<option value="0" {if !$data.acc_settings.other.use_own_settings}selected{/if}>No</option>
		<option value="1" {if $data.acc_settings.other.use_own_settings}selected{/if}>Yes</option>
	  </select>
	  <br/>
	{/if}

	<div id="div_form_data" {if $BRANCH_CODE!='HQ' && !$data.use_own_settings}style="display:none;"{/if}>
		<h4>Purchase / Sales Account Settings</h4>
		<table class="report_table">
		  <tr class="header">
			<th>Key</th>
			<th>Description</th>
			<th>Account Code</th>
			<th>Account Name</th>
			<th>&nbsp;</th>
		  </tr>
		  <tbody id="normalAcc">
			{foreach from=$acc_settings_list key=acc_type item=r}
				<tr>
					<td>{$acc_type}</td>
					<td>{$r.name}</td>
					<td>
						<input type="text" name="data[acc_settings][normal][{$acc_type}][account_code]" value="{$data.acc_settings.normal.$acc_type.account_code}" class="required" title="{$r.name}" />
					</td>
					<td>
						<input type="text" name="data[acc_settings][normal][{$acc_type}][account_name]" value="{$data.acc_settings.normal.$acc_type.account_name|default:$r.default_acc_name}"  class="required" title="{$r.name}" />
					</td>
					<td><img src="ui/rq.gif" /></td>
				</tr>
			{/foreach}
		  </tbody>
		 </table>
		 
		<br />
		<h4>Payment Type Settings</h4>
		<table class="report_table">
			<tr class="header">
				<th>Payment Type</th>
				<th>Description</th>
				<th>Account Code</th>
				<th>Account Name</th>
			</tr>
			
			{foreach from=$payment_type_list key=ptype item=r}
				<tr>
					<td>{$ptype}</td>
					<td>{$r.desc}</td>
					<td>
						<input type="text" name="data[acc_settings][payment][{$ptype}][account_code]" value="{$data.acc_settings.payment.$ptype.account_code}" title="{$r.desc}" />
					</td>
					<td>
						<input type="text" name="data[acc_settings][payment][{$ptype}][account_name]" value="{$data.acc_settings.payment.$ptype.account_name|default:$r.desc}" title="{$r.desc}" />
					</td>
				</tr>
			{/foreach}
		</table>
		
		 {if $config.enable_gst}
			<br />
			<h4>GST Account Settings</h4>
			<table class="report_table">
				<tr class="header">
					<th>Tax Code</th>
					<th>Tax Rate</th>
					<th>Account Code</th>
					<th>Account Name</th>
				</tr>
			  
			  {* Purchase *}
			<tr class="header">
				<th colspan="4">Purchase</th>
			</tr>
			{foreach from=$gst_list key=gst_id item=r}
				{if $r.type eq 'purchase'}
					<tr>
						<td>{$r.code}</td>
						<td>{$r.rate}%</td>
						<td>
							<input type="text" name="data[gst_settings][{$gst_id}][account_code]" value="{$data.gst_settings.$gst_id.account_code}"/>
						</td>
						<td>
							<input type="text" name="data[gst_settings][{$gst_id}][account_name]" value="{$data.gst_settings.$gst_id.account_name|default:'GST Input Tax'}"/>
						</td>
					</tr>
				{/if}
			{/foreach}
			  
			{* Supply *}
			<tr class="header">
				<th colspan="4">Supply</th>
			</tr>
			{foreach from=$gst_list key=gst_id item=r}
				{if $r.type eq 'supply'}
					<tr>
						<td>{$r.code}</td>
						<td>{$r.rate}%</td>
						<td>
							<input type="text" name="data[gst_settings][{$gst_id}][account_code]" value="{$data.gst_settings.$gst_id.account_code}"/>
						</td>
						<td>
							<input type="text" name="data[gst_settings][{$gst_id}][account_name]" value="{$data.gst_settings.$gst_id.account_name|default:'GST Output Tax'}"/>
						</td>
					</tr>
				{/if}
			{/foreach}
			</table>
		 {/if}
		 
		<br />
		<h4>Other Settings</h4>
		<table class="report_table">
			<tr class="header">
				<th>Description</th>
				<th>Value</th>
				<th>&nbsp;</th>
			</tr>
			
			{* Integration Start Date *}
			<tr>
				<td>Integration Start Date</td>
				<td>
					<input type="text" name="data[acc_settings][other][integration_start_date]" id="inp_integration_start_date" size="12" maxlength="10" value="{$data.acc_settings.other.integration_start_date|default:$smarty.now|date_format:'%Y-%m-%d'}" class="required" title="Integration Start Date" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_integration_start_date" style="cursor: pointer;" title="Select Date" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Standard AP Tax Code *}
			<tr>
				<td>Standard AP Tax Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][standard_ap_tax_code]" value="{$data.acc_settings.other.standard_ap_tax_code|default:'TX-S'}" class="required" title="Standard AP Tax Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Cash Sales Standard Product Code *}
			<tr>
				<td>Cash Sales Standard Product Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][cash_sales_standard_product_code]" value="{$data.acc_settings.other.cash_sales_standard_product_code|default:'STDItem001'}" class="required" title="Cash Sales Standard Product Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Cash Sales Rounding Product Code *}
			<tr>
				<td>Cash Sales Rounding Product Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][cash_sales_rounding_product_code]" value="{$data.acc_settings.other.cash_sales_rounding_product_code|default:'RADJ001'}" class="required" title="Cash Sales Rounding Product Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Cash Sales Deposit Product Code *}
			<tr>
				<td>Cash Sales Deposit Product Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][cash_sales_deposit_product_code]" value="{$data.acc_settings.other.cash_sales_deposit_product_code|default:'DPS001'}" class="required" title="Cash Sales Deposit Product Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Account Receivable Standard Product Code *}
			<tr>
				<td>Account Receivable Standard Product Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][ar_standard_product_code]" value="{$data.acc_settings.other.ar_standard_product_code|default:'STDItem001'}" class="required" title="Account Receivable Standard Product Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
			
			{* Account Receivable Rounding Product Code *}
			<tr>
				<td>Account Receivable Rounding Product Code</td>
				<td>
					<input type="text" name="data[acc_settings][other][ar_rounding_product_code]" value="{$data.acc_settings.other.ar_rounding_product_code|default:'RADJ001'}" class="required" title="Account Receivable Rounding Product Code" />
				</td>
				<td><img src="ui/rq.gif" /></td>
			</tr>
		</table>
	</div>
	
	<br />
	<input class="btn btn-success" type="button" value="Save" onClick="ARMS_ACC_SETTINGS.save_clicked();" id="btn_save" />
</form>

<script>ARMS_ACC_SETTINGS.initialise();</script>

{include file='footer.tpl'}