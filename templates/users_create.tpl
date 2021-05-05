{*
11/10/2010 2:13:40 PM yinsee
- add discount limit

12/10/2010 3:03:09 PM Andy
- Add NRIC field at user profile, must be enter and unique. (need config)
- Fix when add new user if system found user submitted got wrong data and redirect back, some data will not bring back and dissapear.

3/30/2011 10:55:09 AM Alex
- hide other input form except user name when tick create as template

5/11/2011 4:32:45 PM Alex
- add function toggle_all_check()
- add a checkbox to check all other checkboxes

10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each user

5/2/2012 11:25:04 AM Andy
- Change title "Barcode" to "Login Barcode".
- Remove "Required Star" for "Login Barcode".
- Hide "Login Barcode" for consignment mode.
- Add bold for title "Allow Mprice".

2/4/2013 4:27 PM Justin
- Enhanced to capture regions.

3/12/2013 5:21 PM Justin
- Enhanced to have checking for those compulsory fields before submit.

5/21/2013 4:26 PM Fithri
- update to allow user login barcode up to 16 chars max

6/21/2013 2:00 PM Andy
- Add control to limit user can only use discount by percent for item discount. (need config user_profile_show_item_discount_only_allow_percent).

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

11/28/2013 1:50 PM Andy
- Remove auto assign email.

10/2/2014 4:03 PM Fithri
- add new checkbox 'Unbranded' in brands selection box

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

09-Mar-2016 11:25 Edwin
- Bug fixed on missing discount limit value when fail

3/24/2016 2:55 PM Andy
- Change password hint.

8/18/2016 11:22 AM Andy
- Update username description to include 0-9 is allowed.

05/08/2019 15:35 Liew
- when vendor and brand din't tick meaning allow all
- hide the vendor / brand list

10/29/2019 2:41 PM William
- Enhanced to block username and login id begin word same as config "reserve_login_id" value.

1/30/2020 11:00 AM William
- Enhanced to add new column "user department".

6/23/2020 10:30 AM Sheila
- Updated button css

10/5/2020 12:49 PM William
- Added new "Fnb Username" textbox.

10/23/2020 4:10 PM William
- Bug fixed fnb username cannot work when create user.
*}
{include file=header.tpl}
<script type="text/javascript">
//get config "reserve_login_id" array value
var reserve_login_id ='';
{if $reserve_val}
	var reserve_login_id = {$reserve_val};
{/if}
{literal}
function check_a()
{
	if (check_login()) {
        if (empty(document.f_a.newuser, 'You must enter a username'))
		{
			return false;
		}
		if(reserve_login_id != ''){
			var newuser = document.f_a['newuser'].value.toLowerCase();
			for (var i = 0;i < reserve_login_id.length; i++) {
				if(newuser.startsWith(reserve_login_id[i].toLowerCase()) == true){
					alert('The username is not allow to start with "'+reserve_login_id[i]+'".');
					document.f_a['newuser'].value = '';
					document.f_a['newuser'].focus();
					return false;
				}
			}
		}
		if (!document.f_a.template.checked)
		{
			if (empty(document.f_a.ic_no, 'You must enter IC No'))
			{
				return false;
			}
			if (empty(document.f_a.fullname, 'You must enter Full Name'))
			{
				return false;
			}
			if (empty(document.f_a.position, 'You must enter Position'))
			{
				return false;
			}
			if (empty(document.f_a.newlogin, 'You must enter a Login ID'))
			{
				return false;
			}
			if (empty(document.f_a.newpassword, 'You must enter a password'))
			{
				return false;
			}
			if (document.f_a.newpassword.value != document.f_a.newpassword2.value)
			{
				alert('Password does not match with confirmation password.');
				document.f_a.newpassword2.value = '';
				document.f_a.newpassword2.focus();
				return false;
			}
			/*
			if (empty(document.f_a.newemail, 'You must enter an email'))
			{
				return false;
			}
			*/

			// if got nric field
			if(document.f_a['ic_no']){
				if(document.f_a['ic_no'].value.trim()==''){
					alert('Please enter IC');
					document.f_a['ic_no'].focus();
					return false;
				}
			}
			if(reserve_login_id != ''){
				var login_id = document.f_a['newlogin'].value.toLowerCase();
				for (var n = 0;n < reserve_login_id.length; n++) {
					if(login_id.startsWith(reserve_login_id[n].toLowerCase()) == true){
						alert('The Login ID is not allow to start with "'+reserve_login_id[n]+'".');
						document.f_a['newlogin'].value = '';
						document.f_a['newlogin'].focus();
						return false;
					}
				}
			}
		}

		document.f_a.submitbtn.disabled = true;
		return true;
	}
	return false;
}

function checkallrow(p, r, v)
{
	var x = document.f_a.getElementsByTagName('input');
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type.indexOf('checkbox') >= 0 && x[i].name.indexOf(p + '[') == 0 && x[i].name.indexOf('[' + r + ']') > p.length+2)
		{
			x[i].checked = v;
		}
	}
}

function checkallcol(p, c, v)
{
	var x = document.f_a.getElementsByTagName('input');
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type.indexOf('checkbox') >= 0 && x[i].name.indexOf(p + '[' + c + ']') >= 0)
		{
			x[i].checked = v;
		}
	}
}

function shide(v)
{
	if (v)
	{
	    $$('#top_form .hide_by_temp').each(function(ele,index){
			$(ele).hide();
		});

		document.getElementById('v1').style.visibility = 'hidden';
/*		document.getElementById('v2').style.visibility = 'hidden';
		document.getElementById('v3').style.visibility = 'hidden';
		document.getElementById('v4').style.visibility = 'hidden';
*/
	}
	else
	{
	    $$('#top_form .hide_by_temp').each(function(ele,index){
			$(ele).show();
		});

		document.getElementById('v1').style.visibility = 'visible';
/*		document.getElementById('v2').style.visibility = 'visible';
		document.getElementById('v3').style.visibility = 'visible';
		document.getElementById('v4').style.visibility = 'visible';
*/
	}
}

function stoggle(v)
{
	document.f_a.use_template.checked=(v!=0);

	if (v==0)
		document.getElementById('custompriv').style.display='';
	else
		document.getElementById('custompriv').style.display='none';
}


function ctoggle(v)
{
	stoggle(v);

}

var branch_email_suffx = ['', '_bl', '_dg', '_gr', '_kg'];
function uname_blur(u)
{
	lc(u);
}

// Clone Selected Column
function clone_selected_col()
{
	sc_bid = document.getElementById('sc').value;
	dc_bid = document.getElementById('dc').value;

	if (sc_bid == dc_bid)
	{
		alert("Can't Clone From Same Source To Same Destination Branch");
	}
	else
	{
		if (confirm("Clone selected branch privileges?")) 
		{							
			var sc_value = document.f_a.getElementsByClassName("inp_priv-" + sc_bid);
			var dc_value = document.f_a.getElementsByClassName("inp_priv-" + dc_bid);
			
			for (var j = 0; j < dc_value.length; j++)
			{
				dc_value[j].checked = false;
			}
			
			for (var i = 0, len=sc_value.length; i < len; i++)
			{				
				// Get Source Privilege Code
				var priv_code = sc_value[i].getAttribute("priv_code");
				// Get Target Input
				var target_inp = document.f_a['user_privilege['+dc_bid+']['+priv_code+']'];
				
				if (target_inp){
					target_inp.checked = sc_value[i].checked;
				}
			}
		}
	}
}

function toggle_all_check(obj, type, class_name){
	if (type=="departments"){
		$$("#departments_id ."+class_name).each(function (ele,index){
			ele.checked=obj.checked;
		});

	}else if (type=="vendors"){
		$$("#vendors_id ."+class_name).each(function (ele,index){
			ele.checked=false;
		});

		// if Vendors All Is Checked	
		$("vendors_id").style.display = (obj.checked) ? "none" : "";	
	}else if (type=="brands"){
		$$("#brands_id ."+class_name).each(function (ele,index){
			ele.checked=false;
		});
		
		// if Brands All Is Checked
		$("brands_id").style.display = (obj.checked) ? "none" : "";				
	}else if (type=="regions"){
		$$("#regions ."+class_name).each(function (ele,index){
			ele.checked=obj.checked;
		});
	}
}
{/literal}
</script>

{* <p>You currently have {$active_count} active user. The system currently allow up to {$MAX_ACTIVE_USER} active users</p> *}

{if $show_add_user}
<h1>Create Profile</h1>
<div class=errmsg>
{if $errmsg.a}<ul>{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}</ul>{/if}
{if $msg.a}<ul class=msg>{foreach item=m from=$msg.a}<li>{$m}{/foreach}</ul>{/if}
</div>
<div class="stdframe" style="margin-bottom:20px;">
<form method=post name=f_a onsubmit="return check_a()">
<input type=hidden name=a value="a">
<table id="top_form">
<tr>
	<td colspan=2><input id=as_template type=checkbox name=template value=1 {if $smarty.request.template}checked{/if} onClick="shide(this.checked)"> <b><label for="as_template">Create as template</label></b></td>
</tr>
<tr>
	<td><b>Username</b></td>
	<td><input name=newuser size=20 maxlength=50 value="{$smarty.request.newuser}" onBlur="uname_blur(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"><span id=v1>only a-z, 0-9 and underscore '_' allowed, minimum {$MIN_USERNAME_LENGTH} characters</span></td>
</tr>
{if $config.enable_suite_device}
<tr>
	<td><b>Fnb Username</b></td>
	<td><input name="fnb_username" size=20 maxlength=50 value="{$smarty.request.fnb_username}"> (For Fnb Cashier use)</td>
</tr>
{/if}
{if $config.user_profile_need_ic}
	<tr class="hide_by_temp">
	    <td><b>IC No.</b></td>
	    <td>
			<input name="ic_no" size="50" maxlength="20" value="{$smarty.request.ic_no}" />
            <img src="ui/rq.gif" align="absbottom" title="Required Field" />
		</td>
	</tr>
{/if}

{if !$config.consignment_modules}
<tr class="hide_by_temp">
	<td><b>Login Barcode</b></td>
	<td><input name=barcode size=26 maxlength=16 value="{$smarty.request.barcode}"> (For POS Counter use) <span id=v1>only numeric {if $MIN_BARCODE_LENGTH}, minimum {$MIN_BARCODE_LENGTH} digit{/if}</span></td>
</tr>
{/if}
<tr class="hide_by_temp">
	<td><b>Full Name</b></td>
	<td><input name=fullname size=50 maxlength=100 value="{$smarty.request.fullname}" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Position</b></td>
	<td><input name=position size=50 maxlength=100 value="{$smarty.request.position}" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>User Department</b></td>
	<td><input name=user_dept size=50 maxlength=100 value="{$smarty.request.user_dept}" onBlur="uc(this)"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Location</b></td>
	<td>
		<select name=default_branch_id onchange="uname_blur(newuser)">
		{section name=i loop=$branches}
		<option value={$branches[i].id} {if $smarty.request.default_branch_id == $branches[i].id}selected{/if}>{$branches[i].code}</option>
		{/section}
		</select>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>SKU Department</b></td>
	<td id="departments_id">
	<div style="padding-bottom:10px;">
	<input type=checkbox id="dept_all_id" onclick="toggle_all_check(this,'departments','departments')">
	<label for="dept_all_id"><b>All departments</b></label>

	{assign var=root value=''}
	{section name=i loop=$departments}
	{if $root ne $departments[i].root}
	{assign var=root value=`$departments[i].root`}
	</div>
	<div id="root[{$departments[i].root_id}]" style="padding-bottom:10px;">
	<b>{$root}</b><br>
	<input type=checkbox id="dept_{$departments[i].root_id}_id" class="departments" onclick="toggle_all_check(this,'departments','root_{$departments[i].root_id}')"><label for="dept_{$departments[i].root_id}_id">All</label>
	{/if}
	{assign var=id value=`$departments[i].id`}
	<span style="white-space: nowrap"><input type=checkbox id=dept{$departments[i].id} class="departments root_{$departments[i].root_id}" name=departments[{$departments[i].id}] {if $smarty.request.departments.$id}checked{/if}><label for=dept{$departments[i].id}>{$departments[i].description}</label></span>
	{/section}
	</div>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>Vendors</b></td>
	<td id="vendors_all_id">
		<input type=checkbox id="vendors_all_id" onclick="toggle_all_check(this,'vendors','vendors')"> 
		<label for="vendors_all_id">All</label><br>	
	</td>
</tr>
<tr class="hide_by_temp">
	<td></td>
	<td id="vendors_id">
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		{section name=i loop=$vendors}
		{assign var=id value=`$vendors[i].id`}
		<input type=checkbox class="vendors" name=vendors[{$vendors[i].id}] {if $smarty.request.vendors.$id}checked{/if}> {$vendors[i].description}<br>
		{/section}
		</div>
		<div style="float:left">
			&nbsp;&nbsp;<b>Note:</b> All vendors remain unticked will be considered have privilege on all vendors
		</div>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>Brands</b></td>
	<td id="brands_all_id">
		<input type=checkbox id="brands_all_id" onclick="toggle_all_check(this,'brands','brands')"> 
		<label for="brands_all_id">All</label><br>	
	</td>
</tr>
<tr class="hide_by_temp">
	<td></td>
	<td id="brands_id">
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		<input type=checkbox class="brands" name=brands[0] {if $smarty.request.brands[0]}checked{/if}> Unbranded<br>

		{section name=i loop=$brands}
		{assign var=id value=`$brands[i].id`}
		<input type=checkbox class="brands" name=brands[{$brands[i].id}] {if $smarty.request.brands.$id}checked{/if}> {$brands[i].description}<br>
		{/section}
		</div>
		<div style="float:left">
			&nbsp;&nbsp;<b>Note:</b> All brands remain unticked will be considered have privilege on all brands
		</div>
	</td>
</tr>
{if $config.consignment_modules && $config.masterfile_branch_region}
	<tr>
		<td valign=top><b>Regions</b></td>
		<td id="regions">
		<div style="padding-bottom:10px;">
			<input type="checkbox" id="region_all_id" onclick="toggle_all_check(this,'regions','regions')">
			<label for="region_all_id"><b>All Regions</b></label>
			{foreach from=$config.masterfile_branch_region key=code item=r}
				<input type="checkbox" class="regions" name="regions[{$code}]" {if $smarty.request.regions.$code}checked {/if}> 
				<b>{$r.name}</b>
			{/foreach}
		</div>
		</td>
	</tr>
{/if}
<tr class="hide_by_temp">
	<td><b>User Level</b></td><td>
	<select name=level>
	{foreach from=$user_level item=level key=n}
	<option value={$level} {if $smarty.request.level eq $level}selected{/if}>{$n}</option>
	{/foreach}
	</select>
	</td>
</tr>
<tr class="hide_by_temp">
	<td><b>Login ID</b></td>
	<td><input name=newlogin size=20 maxlength=16 value="{$smarty.request.newlogin}"> <span id=v4><img src=ui/rq.gif align=absbottom title="Required Field"></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Password</b></td>
	<td><input name=newpassword type=password size=20 value="{$smarty.request.newpassword}"> <span id=v2><img src=ui/rq.gif align=absbottom title="Required Field"> (password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Retype Password</b></td>
	<td><input name=newpassword2 type=password size=20 value="{$smarty.request.newpassword2}">  <span id=v5><img src=ui/rq.gif align=absbottom title="Required Field"></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Email</b></td>
	<td><input name=newemail size=20 value="{$smarty.request.newemail}" onBlur="lc(this)"><span id=v3></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Discount Limit</b>
		[<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))">?</a>]
	</td>
	<td>
		<input name=disc_limit size=1 maxlength="3" value="{$smarty.request.disc_limit}"> % 
		{if $config.user_profile_show_item_discount_only_allow_percent}
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="item_disc_only_allow_percent" {if $user.item_disc_only_allow_percent}checked {/if} value="1" />
			Force this user to only allow discount by percentage for Item Discount
		{/if}
	</td>
</tr>
<tr {if !$mprice_list}style="display:none"{/if}>
<td><b>Allow Mprice</b></td>
<td>
	<ul style="list-style:none; margin:0; padding:0;">	
		{assign var=mp value=$smarty.request.allow_mprice}
		<li style="float:left; padding-right:10px; margin:0;"><input type="checkbox" type="margin-left:0;" name="allow_mprice[not_allow]" onclick="check_user_profile_allow_mprice_list(this)" {if $mp.not_allow}checked{/if}> Not Allow</li>
		{foreach from=$mprice_list item=val}
		<li class="user_profile_mprice_list" {if $mp.not_allow}style="display:none;float:left; padding-right:10px; margin:0;"{else}style="float:left; padding-right:10px; margin:0;"{/if}  ><input type="checkbox" style="margin-left:0;" name="allow_mprice[{$val}]" {if $mp.$val}checked{/if} /> {$val}</li>
		{/foreach}
	
	</ul>
</td>
</tr>
<tr>
	<td><b>Privilege</b></td>
	<td>
		<input type=checkbox id=as_usetpl name=use_template onClick="ctoggle(this.checked)"> <label for="as_usetpl">Use Template</label> &nbsp;&nbsp;&nbsp;
		<select name=template_id onChange="stoggle(this.value)">
		<option value=0>----------</option>
		{section name=i loop=$templates}
		<option value={$templates[i].id}>{$templates[i].u}</option>
		{/section}
		</select>
	</td>
</tr>
</table>
<table>

<div id=custompriv style="padding:10px;width:100%;overflow:auto;">
{include file=user_privilege_table.tpl user_privilege=$smarty.request.user_privilege}
</div>

<p align=center><input class="btn btn-primary" name=submitbtn type=submit value="Add"></p>
</form>
</div>
{/if}

<div style="visibility:hidden"><iframe name=_irs width=1 height=1 frameborder=0></iframe></div>

{include file=footer.tpl}
<script>
shide($('as_template').checked);
</script>
