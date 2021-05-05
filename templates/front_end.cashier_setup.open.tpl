{*
9/8/2011 11:34:13 AM Andy
- Add copy privilege feature.

10/6/2011 10:39:51 AM Andy
- Fix privilege toggle button should not appear in approval screen.
- Change button "Approved" to "Approve".

10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each cashier

09/14/2012 2:57 PM Kee Kee
- Add "Not Allow" into mprice type list

5/21/2013 4:26 PM Fithri
- update to allow user login barcode up to 16 chars max

6/21/2013 2:00 PM Andy
- Add control to limit user can only use discount by percent for item discount. (need config user_profile_show_item_discount_only_allow_percent).

10/29/2019 4:16 PM William
- Enhanced to block username and login id begin word same as config "reserve_login_id" value.
- Enhanced to add checking to other Required field.

11/4/2019 3:12 PM William
- Fix bug when config "user_profile_need_ic" not active, remove ic checking.
 - Fix bug when branch not HQ, remove location checking.
 
2/11/2020 11:48 AM William
- Enhanced to added new column "User Department".

06/30/2020 04:00 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

{literal}
<style>
tr.add_btm_line td, tr.add_btm_line th{
	border-bottom:1px solid #999;
} 
.err{
	color:red;
	font-weight: bold;
}
</style>
{/literal}

<script>

var is_tmp = int('{$form.is_tmp}');
var uid = int('{$form.id}');
var readonly = int('{$readonly}');
var phpself = '{$smarty.server.PHP_SELF}';
var reserve_login_id = '';
{if $reserve_val}
	reserve_login_id = {$reserve_val};
{/if}
var user_profile_need_ic = int('{$config.user_profile_need_ic}');
var branch_code = '{$BRANCH_CODE}';
{literal}
function checkallcol(bid, checked){
	var all_inp = $$('#tbody_privilege_list input[bid='+bid+']');
	for(var i=0; i<all_inp.length; i++){
		all_inp[i].checked = checked;
	}
}

function checkallrow(pv_code, checked){
	var all_inp = $$('#tbody_privilege_list input[pv_code='+pv_code+']');
	for(var i=0; i<all_inp.length; i++){
		all_inp[i].checked = checked;
	}
}

function submit_form(act){
	// check all required field
	//if(!check_required_field(document.f_a))	return false;
	
	if(!uid){
		if(document.f_a.u.value.trim() == ''){
			alert('Please key in Username.');
			return false;
		}else{
			if(reserve_login_id != ''){
				var u = document.f_a['u'].value.toLowerCase();
				for (var i = 0;i < reserve_login_id.length; i++) {
					if(u.startsWith(reserve_login_id[i].toLowerCase()) == true){
						alert('The username is not allow to start with "'+reserve_login_id[i]+'".');
						document.f_a['u'].value = '';
						document.f_a['u'].focus();
						return false;
					}
				}
			}
		}
	
		if(document.f_a.newpassword.value.trim() == '' || document.f_a.newpassword2.value.trim() == ''){
			alert('Please key in both password and confirmation password.');
			if(document.f_a.newpassword.value.trim()=='')	document.f_a.newpassword.focus();
			else	document.f_a.newpassword2.focus();
			return false;
		}
	}
	if(user_profile_need_ic){
		if(document.f_a.ic_no.value.trim() == ''){
			alert('Please key in IC No.');
			return false;
		}
	}
	
	if(branch_code == 'HQ'){
		if(document.f_a.default_branch_id.value.trim() == ''){
			alert('Please select location.');
			return false;
		}
	}
	
	if(document.f_a.position.value.trim() == ''){
		alert('Please key in Position.');
		return false;
	}
	
	if(document.f_a.l.value.trim() == ''){
		alert('Please key in Login ID.');
		return false;
	}else{
		if(reserve_login_id != ''){
			var l = document.f_a['l'].value.toLowerCase();
			for (var i = 0;i < reserve_login_id.length; i++) {
				if(l.startsWith(reserve_login_id[i].toLowerCase()) == true){
					alert('The Login ID is not allow to start with "'+reserve_login_id[i]+'".');
					document.f_a['l'].value = '';
					document.f_a['l'].focus();
					return false;
				}
			}
		}
	}
	
	if(document.f_a.email.value.trim() == ''){
		alert('Please key in email.');
		return false;
	}
	
	// check got change password
	if (document.f_a.newpassword.value != '' && document.f_a.newpassword.value != document.f_a.newpassword2.value){
		alert('Password does not match with confirmation password.');
		document.f_a.newpassword2.value = '';
		document.f_a.newpassword2.focus();
		return false;
	}
	
	if(act=='confirm_tmp_cashier'){
		if(!confirm('Are you sure?'))	return false;
	}
	
	document.f_a['a'].value = act;
	document.f_a.submit();
}

function delete_user(){
	if(!confirm('Are you sure to delete this cashier?'))	return false;
	
	document.f_a['a'].value = 'delete_tmp_user';
	document.f_a.submit();
}

function initial_form(){
	if(readonly){
		Form.disable(document.f_a);
	}
}

function submit_approval_cashier(act){
	if(act=='reject'){
		reason = prompt('Please key in reason') || '';
		if(!reason)	return false;
		
		document.f_approval['reject_reason'].value = reason;
	}
	document.f_approval['act'].value = act;
	
	if(!confirm('Are you sure?'))	return false;
	document.f_approval.submit();
}

function copy_privilege(){
	var uid = $('sel_copy_privilege').value;
	
	if(!uid)	return false;
	
	$('span_copy_privilege_loading').show();
	
	new Ajax.Request(phpself, {
		parameters:{
			a: 'ajax_copy_privilege',
			uid: uid
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';
			
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok']){ // success
                	// loop all privilege checkbox
            		$$('#tbody_privilege_list input.inp_pv_code').each(function(inp){
						$(inp).checked = false;
					});
					
					// got privilege data return
					if(ret['pv_list']){
						for(var bid in ret['pv_list']){
							var pv_list = ret['pv_list'][bid];
							var pv_code = '';
							for(var i=0; i<pv_list.length; i++){
								pv_code = pv_list[i];
								
								var inp = document.f_a['user_privilege['+bid+']['+pv_code+']'];
								if(!inp)	continue;
								
								inp.checked = true;
							}
						}
					}
					
					$('span_copy_privilege_loading').hide();        
                    return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

		    // prompt the error
		    alert(err_msg);
		    $('span_copy_privilege_loading').hide();
		}
	});
}

function check_cashier_setup_mprice_list(obj)
{
	var element = $$("li.cashier_setup_mprice_list");
	for(i=0;i<element.length;i++)
	{
		if(obj.checked)
		{
			$(element[i]).hide();
		}
		else{
			$(element[i]).show();
		}
	}
}
function uname_blur(u){
	lc(u);
}
{/literal}
</script>
<h1>Cashier Profile</h1>

<form style="display:none;" name="f_approval">
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="reject_reason" />
	<input type="hidden" name="act" />
	<input type="hidden" name="a" value="tmp_user_approval" />
</form>

{if $form.reject_reason}
	<div style="border:1px solid red;background-color:yellow;padding:5px;">
		<b>Rejected Reason:</b><br />
		{$form.reject_reason}
	</div><br />
{/if}

<form name="f_a" method="post" class="stdframe">
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="is_tmp" value="{$form.is_tmp}" />
	<input type="hidden" name="a" value="" />
	
	<h3>General Information</h3>
	
	{if $err}
		The following error(s) has occured:
		<ul class="err">
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
		</ul>
	{/if}
	
	<table>
		<tr>
			<td width="100"><b>Username</b></td>
			<td>
				{if $form.id>0}
					{$form.u}
				{else}
					<input type="text" name="u" value="{$form.u}" onblur="uname_blur(this)" maxlength="50" size="50" class="required" title="Username" />
					<img src="/ui/rq.gif" align="absmiddle" />
				{/if}
			</td>
		</tr>
		{if $config.user_profile_need_ic}
			<tr>
			    <td><b>IC No.</b></td>
			    <td>
					<input type="text" name="ic_no" size="50" maxlength="20" value="{$form.ic_no}" class="required" title="IC No."/>
					<img src="/ui/rq.gif" align="absmiddle" />
				</td>
			</tr>
		{/if}
		<tr>
			<td><b>Barcode</b></td>
			<td><input name="barcode" size="50" maxlength="16" value="{$form.barcode}" onBlur="uc(this)" /></td>
		</tr>
		<tr>
			<td><b>Full Name</b></td>
			<td><input name="fullname" size="50" maxlength="100" value="{$form.fullname}" onBlur="uc(this)" /></td>
		</tr>
		<tr>
			<td><b>Location</b></td>
			<td>
				{if $BRANCH_CODE eq 'HQ'}
					<select name="default_branch_id" class="required" title="Location">
						<option value="">-- Please Select --</option>
						{foreach from=$branches key=bid item=r}
							<option value="{$bid}" {if $form.default_branch_id eq $bid}selected {/if}>{$r.code}</option>
						{/foreach}
					</select>
					<img src="/ui/rq.gif" align="absmiddle" />
				{else}
					{$branches[$form.default_branch_id].code|default:$branches[$sessioninfo.branch_id].code}
				{/if}
			</td>
		</tr>
		<tr>
			<td><b>Position</b></td>
			<td>
				<input type="text" name="position" size="50" maxlength="100" value="{$form.position}" onBlur="uc(this)" class="required" title="Position" />
				<img src="/ui/rq.gif" align="absmiddle" />
			</td>
		</tr>
		
		<tr>
			<td><b>User Department</b></td>
			<td><input name="user_dept" size="50" maxlength="100" value="{$form.user_dept}" onBlur="uc(this)" title="User Department" type="text"/></td>
		</tr>
		<tr>
			<td><b>Login ID</b></td>
			<td>
				<input type="text" name="l" size="20" value="{$form.l}" class="required" title="Login ID"/>
				<img src="/ui/rq.gif" align="absmiddle" />
			</td>
		</tr>
		{if !$readonly}
			<tr>
				<td><b>Password</b></td>
				<td><input name="newpassword" type="password" size="20" /> (password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</td>
			</tr>
			<tr>
				<td><b>Retype Password</b></td>
				<td><input alt="Reconfirm password" name="newpassword2" type="password" size="20" /></td>
			</tr>
		{/if}
		<tr>
			<td><b>Email</b></td>
			<td>
				<input type="text" name="email" size="20" value="{$form.email}" onchange="lc(this)" class="required" title="Email" />
				<img src="/ui/rq.gif" align="absmiddle" />
			</td>
		</tr>
		<tr>
			<td><b>Discount Limit</b>
				[<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))">?</a>]
			</td>
			<td><input name="discount_limit" size="1" maxlength="3" value="{$form.discount_limit}" /> % 
				{if $config.user_profile_show_item_discount_only_allow_percent}
					&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="item_disc_only_allow_percent" {if $form.item_disc_only_allow_percent}checked {/if} value="1" />
					Force this user to only allow discount by percentage for Item Discount
				{/if}
			</td>
		</tr>
		<tr {if !$mprice_list}style="display:none;"{/if}>
			<td><b>Allow Mprice</b></td>
			<td>
			<ul style="list-style:none; margin:0; padding:0;">
			{assign var=mp value=$form.allow_mprice}
			<li style="float:left; padding-right:10px; margin:0;"><input type="checkbox" style="margin-left:0;" name="allow_mprice[not_allow]" onclick="check_cashier_setup_mprice_list(this)" {if $mp.not_allow}checked{/if} /> Not Allow</li>
			{foreach from=$mprice_list item=val}
				<li class="cashier_setup_mprice_list" {if $mp.not_allow}style="display:none;float:left; padding-right:10px; margin:0;"{else}style="float:left; padding-right:10px; margin:0;"{/if}><input type="checkbox" style="margin-left:0;" name="allow_mprice[{$val}]" {if $mp.$val && !$mp.not_allow}checked{/if} /> {$val}</li>
			{/foreach}
			</ul>
			</td>
		</tr>
	</table>
	
	<hr noshade size="1">
	
	{if !$readonly}
		<b style="color: #CE0000;">Copy template/cashier privilege: </b>
		<select id="sel_copy_privilege" onChange="copy_privilege();">
			<option value="">------------------</option>
			{foreach from=$templates_user key=tmp_uid item=r}
				<option value="{$tmp_uid}">{$r.u} {if $r.template}(Template){/if}</option>
			{/foreach}
		</select>
		<span id="span_copy_privilege_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading Privilege...</span>
		<br /><br />
	{/if}
	
	<!-- Privilege Table -->
	{assign var=user_privilege value=$form.user_privilege}
	<table border="0" cellspacing="0" cellpadding="4">
		<tbody>
			<tr class="add_btm_line">
				<td colspan="2"><h5>POS Privileges</h5></td>
				{foreach from=$branches key=bid item=b}
					{if $BRANCH_CODE eq 'HQ' || $BRANCH_CODE eq $b.code}
						<th width="50">
							{if !$readonly}
							<a href="javascript:void(checkallcol('{$bid}', true))"><img src="ui/checkall.gif" border="0" title="Check all" /></a><br>
							<a href="javascript:void(checkallcol('{$bid}', false))"><img src="ui/uncheckall.gif" border="0" title="Uncheck all" /></a><br>
							{/if}
							<label title="{$b.description}">{$b.code}</label>
						</th>
					{/if}
				{/foreach}
				<th align="left" width="100%">Description</th>
			</tr>
		</tbody>
		
		<tbody id="tbody_privilege_list">
		{foreach from=$privilege_list item=pv}
			<tr class="add_btm_line">
				<td>
					{if !$readonly}
					<a href="javascript:void(checkallrow('{$pv.code}', true))"><img src="ui/checkall.gif" border="0" title="Check all" /></a><br>
					<a href="javascript:void(checkallrow('{$pv.code}', false))"><img src="ui/uncheckall.gif" border="0" title="Uncheck all" /></a>
					{/if}
				</td>
				<th align="left"><label title="{$pv.description}">{$pv.code}</label></th>
				{foreach from=$branches key=bid item=b}
					{if $BRANCH_CODE eq 'HQ' || $BRANCH_CODE eq $b.code}
						<td style="border-bottom:1px solid #999" align="center">
						{if $pv.hq_only && $b.code ne 'HQ'}
						-
						{else}
						<input type="checkbox" name="user_privilege[{$bid}][{$pv.code}]" {if $user_privilege.$bid[$pv.code]}checked {/if} bid="{$bid}" pv_code="{$pv.code}" value="1" class="inp_pv_code" />
						{/if}
						</td>
					{/if}
				{/foreach}
				<td class="small">
					{$pv.description}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</form>
	
<p align="center">
	{if $form.is_tmp}	<!-- temporary data -->
		{if !$form.active}
			<input class="btn btn-success" type="button" value="Save as Draft" onclick="submit_form('save_tmp_cashier');" />
			{if $form.id}
			<input class="btn btn-error" type="button" value="Delete"  onclick="delete_user();" />
			{/if}
			<input class="btn btn-primary" type="button" value="Confirm"  onclick="submit_form('confirm_tmp_cashier');" />
		{else}
			{if $sessioninfo.level>=$approve_draft_cashier_lv}
				<input class="btn btn-error" type="button" value="Reject"  onclick="submit_approval_cashier('reject');" />
				<input class="btn btn-success" type="button" value="Approve"  onclick="submit_approval_cashier('approve');" />
			{/if}
		{/if}
	{else}
		<input class="btn btn-success" type="button" value="Save & Close" onclick="submit_form('save_cashier');" />
	{/if}
	
	<input class="btn btn-error type="button" value="Close" style="width:70px" onclick="document.location='{$smarty.server.PHP_SELF}'" />
</p>


<script>
initial_form();
</script>
{include file='footer.tpl'}