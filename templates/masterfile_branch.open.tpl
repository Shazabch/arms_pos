{*
3/26/2012 3:03:25 PM Andy
- Reconstruct module structure to use ajax update instead of IRS.

3/27/2012 11:10:54 AM Andy
- Add new setting "Can change price when in-active".

5/16/2012 4:00:34 PM Justin
- Added new option "Transaction End Date" and appear for user to maintain while in consignment mode.

6/15/2012 3:27:37 PM Justin
- Added new fields "Type" and "Debtor" for branch that is franchise.

9/11/2012 5:01:00 PM fithri
- Branch masterfile - Add extra info

9/25/2012 2:08:00 PM Fithri
- when add new branch, all the per-branch settings (eg: selling price, block po, discount %, point) can copy from other branch

5/21/2013 11:54 AM Justin
- Enhanced to hide Copy Settings section.

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

8/8/2014 12:08 PM Justin
- Enhanced to have GST Registration & Start Date.

11/14/2014 11:22 AM Justin
- Enhanced to have config check for GST info.
- Bug fixed on show calendar errror while GST is disabled.

12/10/2014 1:22 PM Justin
- Enhanced to disallow user to change Branch Code & Prefix when editing HQ information.

3/26/2015 10:26 AM Justin
- Enhanced to have "Export Type" for consignment customers.

3/31/2015 5:48 PM Andy
- Enhanced to have another Export type "Designated Areas".

4/13/2015 3:48 PM Andy
- Fix if no key in GST Registration Number, the GST start date will always empty.

4/14/2015 11:19 AM Ding Ren
- add account code
- add account_receivable_code
- add account_receivable_name
- add account_payable_code
- add account_payable_name

5/25/2015 4:03 PM Justin
- Enhanced to allow user to control if wants to change price when change region.

9/7/2016 4:27 PM Andy
- Change branch code limit from 10 to 15.

11/02/2016 13:46 PM Kee Kee
- Change "Account Receivable" to "Sales Account"
- Change "Account Payable" to "Purchase Account"

3/24/2017 10:45 AM Justin
- Enhanced to add notes that system can only accepts JPG/JPEG for logo upload with maximum 5mb file size.

4/28/2017 15:54 Qiu Ying
- Enhanced to add timezone field and set default to "Asia/Kuala_Lumpur"

3/30/2018 4:13PM HockLee
- Add new input Integration Code

5/31/2019 5:14 PM Andy
- Change "Integration Code" to become standard feature.

6/7/2019 9:44 AM Andy
- Remove Integration Code alpha numeric code checking.

6/18/2019 1:31 PM William
- Added new checkbox "Logo is Vertical Size" for branch logo.
- Added new checkbox "No Need Show Company Name When Logo Is Vertical" for hide company name.

11/25/2019 2:54 PM William
- Added new "operation_time", "longitude", and "latitude" column.
- Rename Branch Logo to Branch Document Logo.

12/17/2019 4:02 PM William
- Enhanced to change the max length of "Registration No" to 30.

01/05/2021 5:23 PM Rayleen
- Add new field - "Warehouse Number" and "Warehouse Name"
*}
<script type="text/javascript">
{literal}
function integration_code_changed(){
	var int_code = document.f_b['integration_code'];
	var str = document.f_b['integration_code'].value.trim();
	int_code.value = int_code.value.regex(/\s/g, '');	// replace all whitespace
	//int_code.value = int_code.value.regex(/[&\]/\\#,+()_$`@^~%.'[":*?<>|?!;:=~{}-]/g, '');	// replace special character

	if(str){	// got integration code
		if(str.length > 10){	// min 10 char
			alert('Integration code cannot more than 10 characters');
			document.f_b['integration_code'].focus();
			return false;
		}
		
		/*if(!str.match(/^[a-z0-9]+$/i)){
			alert('Integration code only allow alphabet and number.');
			document.f_b['integration_code'].focus();
			return false;
		}*/
	}
}
function display_hide_company_logo(){
	var is_vertical_logo = $('is_vertical_logo');
	var hide_company_name = $('hide_company_name');
	
	if(is_vertical_logo.checked == true){
		hide_company_name.show();
	}else{
		hide_company_name.hide();
		
	}
}
{/literal}
</script>

<form name="f_b" method="post" target="if1" enctype="multipart/form-data" onSubmit="return false;">
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	
	<input type="hidden" name="a" value="ajax_update_branch" />
	<input type="hidden" name="id" value="{$form.id}" />

<table>
	<tr>
		<!-- col #1 -->
		<td valign=top>
			<table>
			<tr>
				<td><b>Code</b></td>
				<td>
					<input onBlur="uc(this)" {if !$allow_add_branch || $form.code eq 'HQ'}readonly {/if} name="code" size="15" maxlength="15" value="{$form.code}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
			</tr>
			<tr>
				<td><b>Report Prefix</b></td>
				<td>
					<input onBlur="uc(this)" {if !$allow_add_branch || $form.code eq 'HQ'}readonly {/if} name="report_prefix" size="5" maxlength="5" value="{$form.report_prefix}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
				</td>
			</tr>
			<tr>
				<td><b>Company Name</b></td>
				<td><input name="description" size="30" value="{$form.description}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr>
			<tr id="hide_company_name" {if $form.is_vertical_logo neq 1}style="display:none;" {/if}>
				<td colspan="2">
					<span><input type="checkbox" name="vertical_logo_no_company_name" value="1" {if $form.vertical_logo_no_company_name eq 1}checked{/if} /><b>No Need Show Company Name When Logo Is Vertical</b></span>
				</td>
			</tr>
			<tr>
				<td><b>Registration No.</b></td>
				<td><input onBlur="uc(this)" name="company_no" maxlength="30" size="20" value="{$form.company_no}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr>
			<tr>
				<td valign="top"><b>Phone #1</b></td>
				<td><input name="phone_1" size="20" value="{$form.phone_1}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr>
			<tr>
				<td valign="top"><b>Phone #2</b></td>
				<td><input name="phone_2" size="20" value="{$form.phone_2}" /></td>
			</tr>
			<tr>
				<td valign="top"><b>Phone #3</b></td>
				<td><input name="phone_3" size="20" value="{$form.phone_3}" /></td>
			</tr>
			<tr>
				<td valign="top"><b>Contact Person</b></td>
				<td><input onBlur="uc(this)" name="contact_person" size="30" value="{$form.contact_person}" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr>
			<tr>
				<td valign="top"><b>Contact Email</b></td>
				<td><input onBlur="lc(this)" name="contact_email" size="20" value="{$form.contact_email}" /></td>
			</tr>
			<tr>
				<td valign="top"><b>Counter Limit</b></td>
				<td>
					<input type="text" name="counter_limit" size="5" {if $sessioninfo.id ne 1}readonly {/if} value="{$form.counter_limit}" />
				</td>
			</tr>
			{if $config.consignment_modules}
				<tr>
					<td valign="top"><b>Department</b></td>
					<td>
						<input onBlur="lc(this)" name="con_dept_name" size="30" maxlength="100" value="{$form.con_dept_name}" />
					</td>
				</tr>
				<tr>
					<td valign="top"><b>Terms</b></td>
					<td>
						<input onBlur="lc(this)" name="con_terms" size="10" maxlength="10" value="{$form.con_terms}" />
					</td>
				</tr>
				<tr>
					<td valign="top"><b>Consignment Lost Invoice Discount</b></td>
					<td>
						<input onBlur="check_lost_inv_disc(this);" name="con_lost_ci_discount" size="5" value="{$form.con_lost_ci_discount}" /> %
					</td>
				</tr>
				<tr>
					<td valign="top"><b>Allow edit selling price in Consingment Invoice</b></td>
					<td>
						<input type="checkbox" name="ci_allow_edit_selling_price" value="1" {if $form.ci_allow_edit_selling_price}checked {/if} />
					</td>
				</tr>
				<tr>
					<td valign="top"><b>Monthly report settings</b></td>
					<td>
						<b>Sort by</b>
						<select name="con_sort_by" id="sort_by_id">
							<option value="artno" {if $form.con_sort_by eq 'artno'}selected {/if}>Artno</option>
							<option value="super" {if $form.con_sort_by eq 'super'}selected {/if}>Supermarket Code, Artno</option>
							<option value="price" {if $form.con_sort_by eq 'price'}selected {/if}>Price, Artno</option>
						</select>&nbsp;&nbsp;
						<br>
						<input id="group_category_id" type="checkbox" name="con_group_category" value="1" {if $form.con_group_category}checked {/if} />
						<label for="group_category_id"><b>Group by category</b></label>&nbsp;&nbsp;<br>
						
						<input id="split_artno_id" type="checkbox" name="con_split_artno" value="1" {if $form.con_split_artno}checked {/if} />
						<label for="split_artno_id"><b>Split artno</b></label>&nbsp;&nbsp;		
					</td>
				</tr>
			{/if}
			{if $config.masterfile_branch_region and (!$form.id or $form.id>1)}
				<tr id="tr_region_row">
					<td valign="top"><b>Region</b></td>
					<td>
						<input type="hidden" name="old_region" value="{$form.region}" />
						<select name="region">
							<option value="">--</option>
							{foreach from=$config.masterfile_branch_region key=region_code item=region}
								<option value="{$region_code}" {if $form.region eq $region_code}selected {/if}>{$region.name}</option>
							{/foreach}
						</select>
						{if $config.sku_use_region_price}
							<span>
								<input type="checkbox" name="force_update_rprice" value="1" checked /> <b>Force to change price <a href="javascript:void(alert('All selling price for items under this region will update base on new region you choose.\nItems from existing region will update to master selling price if found did not update from new region.'));">[?]</a></b>
							</span>
						{/if}
					</td>
				</tr>
			{/if}
			
			<tr>
				<td valign="top"><b>Timezone </b>(<a href="#" onClick="alert('This feature only available at counter live v192 or above (counter BETA v341 or above).');">?</a>)</td>
				<td>
					<select name="timezone">
						<option value="">-- Please Select --</option>
						{foreach from=$timezone key=k item=tz}
							<option value="{$k}" {if $form.timezone eq $k}selected {/if}>{$tz}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		
			{if $config.consignment_modules and $config.enable_consignment_transport_note and $config.enable_transporter_masterfile}
				<tr>
					<td align="top"><b>Transporter</b></td>
					<td>
						<select name="transporter_id">
							<option value="0">-- None --</option>
							{foreach from=$transporters key=tid item=r}
								<option value="{$tid}" {if $form.transporter_id eq $tid}selected {/if}>{$r.code} - {$r.company_name}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			{/if}
		
			<tr>
				<td><b>Can change price when in-active</b></td>
				<td>
					<select name="inactive_change_price">
						<option value="0">No</option>
						<option value="1" {if $form.inactive_change_price}selected {/if}>YES</option>
					</select>
				</td>
			</tr>
			{if $config.consignment_modules}
				<tr>
					<td><b>Transaction End Date</b></td>
					<td>
						<b>Date</b> <input size="10" type="text" name="trans_end_date" value="{$form.trans_end_date}" id="trans_end_date">
						<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Transaction End Date">
					</td>
				</tr>
			{/if}
			{if $config.masterfile_use_branch_type}
				<tr>
					<td valign="top"><b>Branch's Type</b></td>
					<td>
						<select name="type" onchange="check_branch_type(this);">
							<option value="branch" {if $form.type eq 'branch'}selected {/if}>Own Branch</option>
							<option value="franchise" {if $form.type eq 'franchise'}selected {/if}>Franchise</option>
						</select>
					</td>
				</tr>
				<tr id="debtor" {if $form.type ne "franchise"}style="display:none;"{/if}>
					<td valign="top"><b>Debtor</b></td>
					<td>
						<select name="debtor_id" {if $form.type ne "franchise"}disabled{/if} style="width:206px;">
							{foreach from=$debtors key=r item=i}
								<option value="{$i.id}" {if $form.debtor_id eq $i.id}selected {/if}>{$i.code} - {$i.description}</option>
							{/foreach}
						</select>
					</td>
				</tr>								
			{/if}

			<tr>
				<td valign="top"><b>Integration Code</b></td>
				<td><input name="integration_code" size="20" value="{$form.integration_code}" onChange="integration_code_changed();" maxlength="10" /></td>
			</tr>

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
				{if $config.consignment_modules}
					<tr>
						<td><b>Export Type</b></td>
						<td>
							<select name="is_export">
								<option value="0" {if !$form.is_export}selected {/if}>Local</option>
								<option value="1" {if $form.is_export == 1}selected {/if}>Foreign</option>
								<option value="2" {if $form.is_export == 2}selected {/if}>Designated Areas</option>
							</select>
						</td>
					</tr>
				{/if}
				<tr>
					<td><b>Account Code (Vendor)</b></td>
					<td>
						<input type="text" name="account_code" value="{$form.account_code}" />
					</td>
				</tr>
				<tr>
					<td><b>Account Code (Debtor)</b></td>
					<td>
						<input type="text" name="account_code_debtor" value="{$form.account_code_debtor}" />
					</td>
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
								<tr>
									<td><b>Purchase Account Code</b></td>
									<td><input type="text" name="account_payable_code" value="{$form.account_payable_code}"/></td>
									<td><b>Purchase Account Name</b></td>
									<td><input type="text" name="account_payable_name" value="{$form.account_payable_name}"/></td>
								</tr>
							</table>
						</fieldset>
					</td>
				</tr>
			{/if}
				<tr>
					<td valign=top><b>Branch Operation Time</b></td>
					<td><textarea name="operation_time" rows="3" cols="30">{$form.operation_time}</textarea></td>
				</tr>
				<tr>
					<td><b>Longitude </b>(<a href="/ui/getlocation/getlocation.png" target="_blank">?</a>)</td>
					<td>
						<input type="text" name="longitude" value="{$form.longitude}" />
					</td>
				</tr>
				<tr>
					<td><b>Latitude </b>(<a href="/ui/getlocation/getlocation.png" target="_blank">?</a>)</td>
					<td>
						<input type="text" name="latitude" value="{$form.latitude}" />
					</td>
				</tr>
				<tr>
					<td><b>Warehouse Number</td>
					<td>
						<input type="text" name="warehouse_number" value="{$form.warehouse_number}" />
					</td>
				</tr>
				<tr>
					<td><b>Warehouse Name</b></td>
					<td>
						<input type="text" name="warehouse_name" value="{$form.warehouse_name}" />
					</td>
				</tr>		
			</table>
		</td>

		<!-- col #2 -->
		<td valign=top >
			<b>Address</b> <img src="ui/rq.gif" align="absbottom" title="Required Field"><br />
			<textarea name="address" rows="5" cols="40">{$form.address}</textarea><br />
	
			<b>Deliver To</b><br /> 
			<textarea name="deliver_to" rows="5" cols="40">{$form.deliver_to}</textarea><br /> 	
			
			<br />
			<label><input name="use_branch_logo" type="checkbox" {if $form.logo}checked{/if}/><b>Branch Document Logo</b></label>
			(<a href="#" onClick="alert('This logo will be used in documents for this branch');">?</a>)&nbsp;&nbsp;
			<input type="file" name="logo" onchange="upload_check();" /><br />
			<div style="color:#0000ff; font-weight:bold;">
				<ul>
					<li>Please ensure the file is a valid image file (JPG/JPEG).</li>
					<li>Uploaded logo will replace existing one.</li>
					<li>Image File Size is limited to a maximum of 5MB only.</li>
				</ul>
			</div><br />
			<div id="branch_logo" align="center" width="100%" style="background-color:#EBEBEB;padding:3px;">
				{if $form.logo}
				<img width="100" height="100" src="{$form.logo}?{$smarty.now}" onclick="show_full_logo();" />
				{else}
				<h2 style="color:grey;">Not set</h2>
				{/if}
			</div>
			<div><input onchange="display_hide_company_logo()" id="is_vertical_logo" type="checkbox" name="is_vertical_logo" value="1" {if $form.is_vertical_logo eq 1}checked{/if} /><b>Logo is Vertical Size</b></div>
		</td>
	</tr>
</table>

{if $config.branch_extra_info}
<b>Extra Info</b><br />
<div style="border:2px solid black; height:120px;overflow:auto;">
<table width="100%">
{assign var=rows_count value=1}
<tr>
{foreach from=$config.branch_extra_info item=ei key=eik}
	<td><b>{$ei.description}</b></td>
	<td><input type="{$ei.input_type}" name="{$eik}" {if $ei.input_size}size="{$ei.input_size}"{/if} {if $form_extra.$eik}value="{$form_extra.$eik}"{/if} />&nbsp;&nbsp;&nbsp;</td>
{if $rows_count eq 2}
{*if 1*}
</tr>
{assign var=rows_count value=0}
{/if}
{assign var=rows_count value=$rows_count+1}
{/foreach}
</table>
</div>
{/if}

{*if !$form.id}
	<h3>New Branch Settings</h3>
	<div style="height:120px;border:2px solid black;overflow:auto;">
		<table>
			<tr>
				<td><b>Copy Selling Prices from branch</b></td>
				<td>
					<select name="copy_selling_price">
					<option value="">--</option>
					{foreach from=$branches key=brkey item=br}
					<option value="{$br.id}">{$br.code}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Copy Block PO from branch</b></td>
				<td>
					<select name="copy_block_po">
					<option value="">--</option>
					{foreach from=$branches key=brkey item=br}
					<option value="{$br.id}">{$br.code}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Copy POS Settings from branch</b></td>
				<td>
					<select name="copy_pos_settings">
					<option value="">--</option>
					{foreach from=$branches key=brkey item=br}
					<option value="{$br.id}">{$br.code}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Copy Member Discount from branch</b></td>
				<td>
					<select name="copy_discount">
					<option value="">--</option>
					{foreach from=$branches key=brkey item=br}
					<option value="{$br.id}">{$br.code}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Copy Member Points from branch</b></td>
				<td>
					<select name="copy_point">
					<option value="">--</option>
					{foreach from=$branches key=brkey item=br}
					<option value="{$br.id}">{$br.code}</option>
					{/foreach}
					</select>
				</td>
			</tr>
		</table>
	</div>
{/if*}

	<br />
	<div align="center" id="div_branch_btn_area">
		<input type="button" value="Confirm" onClick="update_branch();" />  
		<input type="button" value="Close" onclick="hidediv('div_branch');" />
		<div id="div_branch_processing_msg"></div>
	</div>
</form>

<div class="ndiv" id="logo_full" style="position:fixed;display:none;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div class="small" style="position:absolute; right:3; text-align:right;top:2px;">
					<a href="javascript:void(hidediv('logo_full'))"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
				</div>
				<div style="margin-top:20px;">
					<img src="{$form.logo}?{$smarty.now}" />
				</div>
			</div>
		</div>
	</div>
</div>

<iframe name="if1" height="0" width="0" tabindex="-1" style="display:none;"></iframe>

<script>
	{if $config.consignment_modules}
		{literal}
		Calendar.setup({
			inputField     :    "trans_end_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_added1",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
		{/literal}
	{/if}
	
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
</script>
