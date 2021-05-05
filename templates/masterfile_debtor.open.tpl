{*
5/14/2010 6:07:18 PM Alex
- Add area

4/1/2013 4:03 PM Andy
- Allow to generate login ticket for debtor if got config "enable_debtor_portal".

5/13/2013 10:11 AM Andy
- Add default mprice type for debtor.

5/27/2013 2:14 PM Andy
- Change the debtor key to be editable from masterfile and at least 10 characters.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

1/26/2015 5:58 PM Andy
- Add Special Exemption for Debtor.

2/3/2015 3:57 PM Andy
- Change the word "Special Exemption" to "GST Special Exemption".

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

11/1/2016 11:41 AM Andy
- Enhanced to have debtor gst start date and gst registration number.

11/2/2016 13:12 PM Kee Kee
- Change "Account Receivable" to "Sales Account"

12/1/2017 11:33 AM Justin
- Changed the "Address" become "Address (Bill)".
- Enhanced to have "Address (Deliver)".

3/30/2018 4:13PM HockLee
- Add new input Integration Code.

8/24/2018 2:07 PM Andy
- Enhanced to have Debtor Price selection.

5/3/2019 10:05 AM Liew
- Add default Sales Agent

12/17/2019 4:02 PM William
- Enhanced to change the max length of "company No" to 30.
*}

<script type="text/javascript">
{literal}
submit_form = function(action){
	if(action=='close'){
        if(!confirm('Are you sure?'))   return false;
        default_curtain_clicked();
        return false;
	}else if(action=='save'){
		if(!document.f_a['code'].value.trim()){
			alert('Please enter code.');
			return false;
		}
		
		if(document.f_a['login_ticket']){
			var str = document.f_a['login_ticket'].value.trim();
			if(str){	// got debtor key
				if(str.length<10){	// min 10 char
					alert('Debtor Key must have minimum 10 characters');
					document.f_a['login_ticket'].focus();
					return false;
				}
				
				if(!str.match(/^[a-z0-9]+$/i)){
					alert('Debtor Key only allow alphabet and number.');
					document.f_a['login_ticket'].focus();
					return false;
				}
			}
		}

		if(document.f_a['integration_code']){
			var str = document.f_a['integration_code'].value.trim();
			if(str){	// got integration code
				if(str.length > 10){	// min 10 char
					alert('Integration code cannot more than 10 characters');
					document.f_a['integration_code'].focus();
					return false;
				}
				
				if(!str.match(/^[a-z0-9]+$/i)){
					alert('Integration code only allow alphabet and number.');
					document.f_a['integration_code'].focus();
					return false;
				}
			}
		}

		if(!confirm('Are you sure?'))   return false;
        
        $('btn_save').disable().value = 'Saving...';
        
        ajax_request(phpself,{
			parameters: document.f_a.serialize(),
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='OK'){
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




{/literal}
</script>
<br />
<form method="post" name="f_a" onSubmit="return false;">
<input type="hidden" name="a" value="save">
<input type="hidden" name="id" value="{$form.id}" />

<table width="100%">
<tr>
	<td><b>Code</b> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
	<td><input type="text" name="code" size="10" value="{$form.code}" onChange="this.value=this.value.toUpperCase();"/></td>
	<td><b>Term</b></td>
	<td><input type="text" name="term" size="4" value="{$form.term}" onChange="miz(this);" /></td>
</tr>
<tr>
	<td nowrap><b>Company No.</b></td>
	<td><input type="text" name="company_no" maxlength="30" size="20" value="{$form.company_no}" /></td>
	<td><b>Credit Limit</b></td>
	<td><input type="text" name="credit_limit" size="4" value="{$form.credit_limit}" /></td>
</tr>
<tr>
	<td><b>Description</b></td>
	<td colspan="3">
	    <textarea name="description" rows="3" style="width:350px;">{$form.description}</textarea>
	</td>
</tr>
<tr>
	<td valign="top"><b>Address (Bill)</b></td>
	<td colspan="3"><textarea name="address" rows="5" style="width:350px;">{$form.address}</textarea></td>
</tr>
<tr>
	<td valign="top"><b>Address (Deliver) [<a class="gst_info" href="javascript:void(alert('- it is optional and can be configured to use at the printout of Credit & Cash Sales DO.\n- system will pickup Address (Bill) as Delivery address if left empty.'));">?</a>]</b></td>
	<td colspan="3"><textarea name="delivery_address" rows="5" style="width:350px;">{$form.delivery_address}</textarea></td>
</tr>
<tr>
	<td><b>Contact Person</b></td>
	<td><input onBlur="uc(this)" name="contact_person" size="20" value="{$form.contact_person}"></td>
	<td><b>Contact Email</b></td>
	<td><input onBlur="lc(this)" name="contact_email" size="20" value="{$form.contact_email}"></td>
</tr>
<tr>
	<td><b>Phone #1</b></td>
	<td><input name="phone_1" size="20" value="{$form.phone_1}"></td>
	<td><b>Phone #2</b></td>
	<td><input name="phone_2" size="20" value="{$form.phone_2}"></td>
</tr>
<tr>
	<td valign="top"><b>Fax</b></td>
	<td><input name="phone_3" size="20" value="{$form.phone_3}"></td>
	<td valign="top"><b>Area</b></td>
	<td><input name="area" size="20" value="{$form.area}" id="inp_area" />
	<span id="span_loading_area"></span>
     <div id="div_autocomplete_area_choices" class="autocomplete" style="display:none;height:150px !important;width:200px !important;overflow:auto !important;z-index:100"></div>

</tr>

<tr>
	<td><b>Default MPrice Type</b> [<a href="javascript:void(alert('Use for Sales Order and Delivery Order'))">?</a>]</td>
	<td colspan="3">
		<select name="debtor_mprice_type">
			<option value="">-- None --</option>
			{foreach from=$config.sku_multiple_selling_price item=mprice_type}
				<option value="{$mprice_type}" {if $mprice_type eq $form.debtor_mprice_type}selected {/if}>{$mprice_type}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="use_debtor_price" value="1" {if $form.use_debtor_price}checked {/if} /> <span style="color:blue;">Use Debtor Price (if have)</span>
	</td>
</tr>

<tr>
	<td><b>Default Sales Agent</b> [<a href="javascript:void(alert('Use for Credit Sales Delivery Order'))">?</a>]</td>
	<td colspan="3">
		<select name="debtor_sales_agent">
		<option value="">-- None --</option>
		{foreach from=$sales_agent item=sa}
			<option value="{$sa.id}" {if $sa.id eq $form.sa_id}selected{/if}>{$sa.code} - {$sa.name}</option>
		{/foreach}
		</select>
	</td>
</tr>

{if $config.enable_debtor_portal}
	<tr>
		<td><b>Debtor Key</b></td>
		<td colspan="3">
			<input type="text" name="login_ticket" value="{$form.login_ticket}" size="15" maxlength="20" onChange="login_ticket_changed();" />
			<input type="button" value="Generate" onClick="generate_debtor_ticket();" />
			<input type="button" value="Clear" onClick="clear_debtor_ticket();" />
			<br />
			<span class="small" style="color:blue;">(Alphabet and number only)</span>
		</td>
	</tr>
{/if}

{if $config.enable_reorder_integration}
	<tr>
		<td><b>Integration Code</b></td>
		<td colspan="3">
			<input type="text" name="integration_code" value="{$form.integration_code}" size="15" maxlength="20" onChange="integration_code_changed();" />
			<br />
		</td>
	</tr>
{/if}

{if $config.enable_gst}
	<tr>
		<td><b>GST Registration Number</b></td>
		<td><input name="gst_register_no" size="20" value="{$form.gst_register_no}" onChange="gst_reg_no_changed();" /></td>
	</tr>
	<tr>
		<td><b>GST Start Date</b></td>
		<td>
			<input size="10" type="text" name="gst_start_date" value="{$form.gst_start_date|ifzero:''}" id="gst_start_date" readonly  onChange="gst_reg_date_changed();" />
			<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select GST Start Date">
		</td>
	</tr>
	<tr>
		<td><b>GST Special Exemption</b></td>
		<td><input type="checkbox" name="special_exemption" {if $form.special_exemption}checked{/if} value="1" /></td>
	</tr>
	<tr>
		<td colspan="4">
			<fieldset>
				<legend><b>GL Code</b></legend>
				<table>
					<tr>
						<td><b>Sales Account Code</b></td>
						<td><input type="text" name="account_receivable_code" value="{$form.account_receivable_code}"/></td>
						<td><b>Sales Account Name</b></td>
						<td><input type="text" name="account_receivable_name" value="{$form.account_receivable_name}"/></td>
					</tr>
				</table>
			</fieldset>
		</td>
	</tr>
{/if}

<tr>
	<td colspan="4" align="center"><br>
		<input type="button" value="Save" id="btn_save" onclick="submit_form('save');" />
		<input type="button" value="Close" onclick="submit_form('close');" />
	</td>
</tr>
</table>
</form>

<script>
{if $readonly}
	Form.disable(document.f_a);
{else}
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
	reset_area_autocomplete();
	
	{if $config.enable_gst}
		{literal}
			Calendar.setup({
				inputField     :    "gst_start_date",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "t_added2",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true
			});
		{/literal}
	{/if}
{/if}

</script>
