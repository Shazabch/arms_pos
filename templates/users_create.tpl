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

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Create Profile</h4>
			<span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $errmsg.a}<ul>
	{foreach item=m from=$errmsg.a}
	<div class="alert alert-danger mg-b-0 m-2" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$m}
	</div>
	{/foreach}
{/if}

{if $msg.a}
	{foreach item=m from=$msg.a}
	<div class="alert alert-info mg-b-0 m-2" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$m}
	</div>
	{/foreach}
{/if}

<!--Form Started here-->

<div class="card mx-3">
	<div class="card-body">
		<form class="from-horizontal" method="post" name="f_a" onsubmit="return check_a()">
			<div class="" >

				<div class="form-check">
					<input id="as_template" class="form-check-input" type="checkbox" name="template" value=1 {if $smarty.request.template}checked{/if} onClick="shide(this.checked)"> 
					<b><label for="as_template" class="form-check-label">Create as template</label></b>
				</div> 

				<div class="row">
					<div class="col-md-6">
						<label class="mt-3">username <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="newuser" value="{$smarty.request.newuser}" onBlur="uname_blur(this)">
						<small class="text-muted ">
							<span id="v1">only a-z, 0-9 and underscore '_' allowed, minimum {$MIN_USERNAME_LENGTH} characters</span>
						</small>
					</div>
					{if $config.enable_suite_device}
					<div class="col-md-6">
						<label class="mt-3">Fnb username</label>
						<input type="text" class="form-control" name="fnb_username" value="{$smarty.request.fnb_username}">
						<small class="text-muted">
							<span>(For Fnb Cashier use)</span>
						</small>
					</div>
					{/if}
					{if !$config.consignment_modules}
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Login Barcode</label>	
						<input type="text" class="form-control" name="ic_no" value="{$smarty.request.barcode}">
						<small class="text-muted">
							<span>(For POS Counter use)</span> <span id="v1">only numeric {if $MIN_BARCODE_LENGTH}, minimum {$MIN_BARCODE_LENGTH} digit{/if}</span>
						</small>
					</div>
					{/if}
					{if $config.user_profile_need_ic}
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">IC NO.<span class="text-danger">*</span></label>	
						<input type="text" class="form-control" name="ic_no" value="{$smarty.request.ic_no}">
					</div>
					{/if}
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Full name <span class="text-danger">*</span></label>	
						<input type="text" class="form-control" name="fullname" value="{$smarty.request.fullname}" onBlur="uc(this)">
					</div>
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Position <span class="text-danger">*</span></label>	
						<input type="text" class="form-control" name="position" value="{$smarty.request.position}" onBlur="uc(this)">
					</div>
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">User Department </label>	
						<input type="text" class="form-control" name="user_dept"  value="{$smarty.request.user_dept}" onBlur="uc(this)">
					</div>
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Location </label>
						<select class="form-control" name=default_branch_id onchange="uname_blur(newuser)">
							{section name=i loop=$branches}
							<option value={$branches[i].id} {if $smarty.request.default_branch_id == $branches[i].id}selected{/if}>{$branches[i].code}</option>
							{/section}
						</select>
					</div>	
				</div>
				<div class="row hide_by_temp">
					<div class="col">
						<label class="mt-3"><b>SKU Department</b></label>
						<div id="departments_id">
							<div class="form-check ml-3">
								<input id="dept_all_id" class="form-check-input" type="checkbox" onclick="toggle_all_check(this,'departments','departments')" > 
								<label class="form-check-label" for="dept_all_id"> All Departments </label>
								{assign var=root value=''}
								{section name=i loop=$departments}
								{if $root ne $departments[i].root}
								{assign var=root value=`$departments[i].root`}
							</div> 	
							<div id="root[{$departments[i].root_id}]" class="mt-3 container-fluid">
								<label class="form-label" ><b>{$root}</b></label>	
								<div class="row px-3">
									<div class="checkbox mr-2">
										<div class="custom-checkbox custom-control">
											<input type="checkbox" class="departments custom-control-input" id="dept_{$departments[i].root_id}_id" onclick="toggle_all_check(this,'departments','root_{$departments[i].root_id}')">
											<label for="dept_{$departments[i].root_id}_id" class="custom-control-label mt-1">All</label>
										</div>
									</div>
									{/if}
									{assign var=id value=`$departments[i].id`}
									<div class="checkbox mr-2">
										<div class="custom-checkbox custom-control">
											<input type="checkbox" class="departments root_{$departments[i].root_id} custom-control-input" id="dept{$departments[i].id}" name="departments[{$departments[i].id}]" {if $smarty.request.departments.$id}checked{/if}>
											<label for="dept{$departments[i].id}" class="custom-control-label mt-1">{$departments[i].description}</label>
										</div>
									</div>
									{/section}
								</div> 					
							</div>
						</div>
					</div>
				</div>
				<div class="row ">
					<!--Vendors section start-->
					<div class="col-md-6 hide-by-temp">
						<label class="mt-3 tx-bold">Vendors</label>
						<div class="form-check " id="vendors_all_id">
						<input id="vendors_all_id" class="departments form-check-input" type="checkbox" onclick="toggle_all_check(this,'vendors','vendors')" > 
						<label class="form-check-label" for="vendors_all_id"> All </label>	
						</div> 		
					
						<div class="" id="vendors_id">
							<div class="conatainer rounded bg-light p-3 mt-2 overflow-auto" style="height: 25vh;" >
								{section name=i loop=$vendors}
								{assign var=id value=`$vendors[i].id`}
								<input type=checkbox class="vendors p-4" name=vendors[{$vendors[i].id}] {if $smarty.request.vendors.$id}checked{/if}> {$vendors[i].description}<br>
								{/section}
							</div>
								<small class="mt-1"><b>Note:</b> <span class="text-muted ">All vendors remain unticked will be considered have privilege on all vendors</span></small>
						</div>	
					</div>
					<!--vendors  section end -->

					<!--Brands Section start-->
					<div class="col-md-6 hide-by-temp">
						<label class="mt-3 tx-bold">Brands</label>
						<div class="form-check " id="brands_all_id">
							<input id="brands_all_id" class="departments form-check-input" type="checkbox" onclick="toggle_all_check(this,'brands','brands')" > 
							<label class="form-check-label" for="brands_all_id"> All </label>	
						</div> 		
					
						<div class="" id="brands_id">
							<div class="conatainer rounded bg-light p-3 mt-2 overflow-auto" style="height: 25vh;" >
								<input type=checkbox class="brands p-4" name="brands[0]" {if $smarty.request.brands[0]}checked{/if}> Unbranded<br>
								{section name=i loop=$brands}
								{assign var=id value=`$brands[i].id`}
								<input type=checkbox class="brands p-4" name="brands[{$brands[i].id}]" {if $smarty.request.brands.$id}checked{/if}> {$brands[i].description}<br>
								{/section}
							</div>
								<small class="mt-1"><b>Note:</b> <span class="text-muted ">All brands remain unticked will be considered have privilege on all brands</span></small>
						</div>		
					</div>
					<!--Brands Section ends -->

				</div>
				<div class="row mt-3">
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">User Level </label>
						<select class="form-control" name="level" >
							{foreach from=$user_level item=level key=n}
							<option value="{if $smarty.request.level eq $level}selected{/if}" >{$n}</option>
							{/foreach}
						</select>
					</div>

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
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Login ID <span class="text-danger">*</span></label>	
						<input type="text" class="form-control" name="newlogin" value="{$smarty.request.newlogin}" onBlur="uc(this)">
					</div>

					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Password <span class="text-danger">*</span></label>	
						<input type="password" class="form-control" name="newpassword" value="{$smarty.request.newpassword}" onBlur="uc(this)">
						<small><span id="v2" class="text-muted">(password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</span></small>
					</div>
				
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Retype Password <span class="text-danger">*</span></label>	
						<input type="password" class="form-control" name="newpassword2" value="{$smarty.request.newpassword2}" onBlur="uc(this)">
					</div>
					
					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Email </label>	
						<input  class="form-control" name="newemail" value="{$smarty.request.newemail}" onBlur="lc(this)">			
					</div>

					<div class="col-md-6 hide_by_temp">
						<label class="mt-3">Discount Limits</label>
						<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))"><i class="fas fa-question"></i></a>
						<div class="input-group" > 
						<input name="disc_limit" class="form-control" maxlength="3" value="{$smarty.request.disc_limit}" >
							<div class="input-group-append">
								<div class="input-group-text">%</div>
							</div>
						</div>
						<div class="checkbox">
							<div class="custom-checkbox custom-control">
								<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-1" name="item_disc_only_allow_percent">
								<label for="checkbox-1" class="custom-control-label mt-1 text-muted">
									<span class="tx-12">Force this user to only allow discount by percentage for Item Discount</span>
								</label>
							</div>
						</div>					
					</div>
				</div>	
				<!--Allow mprice starts-->

				<div class="row mt-3 {if !$mprice_list}style="display:none"{/if}">
					<div class="col-md-6">
						<label class="mt-3"><b>Allow Mprice</b></label>
						<div class="row px-3">
							<div class="checkbox mr-2">
								{assign var=mp value=$smarty.request.allow_mprice}
								<div class="custom-checkbox custom-control">
									<input type="checkbox" id="checkbox-0" class="custom-control-input" name="allow_mprice[not_allow]"  onclick="check_user_profile_allow_mprice_list(this)" {if $mp.not_allow}checked{/if}>
									<label for="checkbox-0"  class="custom-control-label mt-1">Not Allow</label>
								</div>
							</div>
							{foreach from=$mprice_list item=val}
							<div  class="checkbox mr-2 user_profile_mprice_list" {if $mp.not_allow}style="display:none;"{else}style=""{/if} >
								<div class="custom-checkbox custom-control">
									<input type="checkbox" id="allow_mprice[{$val}]" class="custom-control-input" name="allow_mprice[{$val}]" {if $mp.$val}checked{/if}>
									<label for="allow_mprice[{$val}]" class="custom-control-label mt-1">{$val}</label>
								</div>
							</div>
							{/foreach}
						</div> 
					</div>
					<div class="col-md-6">
						<div class="ml-2">
							<label class="mt-3"><b>Privilege</b></label>
							<div class="row">
								<div class="col-md-4">
									<div class="checkbox">
										<div class="custom-checkbox custom-control">
											<input type="checkbox" class="custom-control-input" name="use_template" id="as_usetpl"  onClick="ctoggle(this.checked)">
											<label  class="custom-control-label"  for="as_usetpl">Use Template</label>
										</div>
									</div>
								</div>
								<div class="col-md-8">
									<select class="form-control select2" name="template_id" onChange="stoggle(this.value)">
									<option value="0">----------</option>
									{section name=i loop=$templates}
									<option value={$templates[i].id}>{$templates[i].u}</option>
									{/section}
								</select>
								</div>
							</div>
						</div>
					</div>
				</div>	
				<!--Mprice ends here-->
				
				{include file=user_privilege_table.tpl user_privilege=$smarty.request.user_privilege}
				
				<div class="row mt-2 text-center">
					<div class="col">
						<button class="btn btn-primary btn-block " name="submitbtn" value="Add"> Add</button>	
					</div>
				</div>
				
				{include file=footer.tpl}
				<script>
				shide($('as_template').checked);
				</script>

			</div>
		</form>
	</div>				
</div>
{/if}


