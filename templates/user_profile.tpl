{*
9/28/2007 3:01:54 PM yinsee
- remove "copy permission" for non-HQ
- remove "user level" for non-HQ 

10/1/2007 2:37:17 PM gary
- added user_activate right to only allow activate users. 

11/18/2009 4:05:41 PM edward
- add discount limit input box

11/10/2010 2:11:56 PM yinsee
- fix discount box html

12/10/2010 3:03:09 PM Andy
- Add NRIC field at user profile, must be enter and unique. (need config)

5/11/2011 4:32:55 PM Alex
- add a checkbox to check all other checkboxes

10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each user

3/29/2012 3:38:23 PM Justin
- Added to capture user phone no and receive notification by SMS (based on config "notification_send_sms").

5/2/2012 11:25:20 AM Andy
- Change title "Barcode" to "Login Barcode".
- Hide "Login Barcode" for consignment mode.

09/14/2012 11:52 AM Kee Kee
- Add "Not Allow" user mprice type

2/4/2013 4:27 PM Justin
- Enhanced to capture regions.

3/12/2013 5:21 PM Justin
- Enhanced to show * for those compulsory fields.

4/3/2013 5:15 PM Fithri
- fix bug where low-level user can update profile & change user level

5/21/2013 4:26 PM Fithri
- update to allow user login barcode up to 16 chars max

6/21/2013 2:00 PM Andy
- Add control to limit user can only use discount by percent for item discount. (need config user_profile_show_item_discount_only_allow_percent).

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/2/2014 4:03 PM Fithri
- add new checkbox 'Unbranded' in brands selection box

7/2/2015 3:52 PM Eric
- Hide Copy User Privilege when USERS_MNG permission disabled
- Check document.f_e is null do not init_chg

3/30/2016 10:06 AM Andy
- Fix user level will become guest if updated by other user.

05/23/2016 10:00 Edwin
- Add additional info at email

06/30/2016 16:30 Edwin
- Add lock and unlock user feature in user update.

05/08/2019 15:35 Liew
- when vendor and brand din't tick meaning allow all
- hide the vendor / brand list

1/30/2020 11:00 AM William
- Enhanced to add new column "user department".

07/08/2020 05:15 PM Sheila
- Updated button css

10/16/2020 2:32 PM William
- Enhanced to add fnb username.

3/02/2021 9:47 AM Rayleen
- Hide user activation, locking and restore button if user is added from eform application

3/09/2021 2:35 PM Rayleen
- Change URL of Eform "Close" button to redirect to the user profile
*}

{if $sessioninfo.privilege.USERS_MNG || $sessioninfo.privilege.USERS_ACTIVATE}
    {if !$user.template}
	<div class="card mx-3">
		<div class="card-body">
				<form class="form-horizontal">
					<div class="row">
						<div class="col-md-4">
							{if $BRANCH_CODE eq 'HQ'}
								{if $sessioninfo.privilege.USERS_MNG}
									<input type="hidden" name="a" value=c>
									<input type="hidden" name="user_id" value="{$user.id}">
									<label><b>Copy User Privilege</b></label>
									<small class="text-muted">Select template/user to copy from</small>
									<select class="form-control" name="template_id" onChange="copy_template(document.f_c)">
										<option value="0">----------</option>
										{section name=i loop=$templates}
											<option value="{$templates[i].id}">{$templates[i].u} {if $templates[i].template}(Template){/if}</option>
										{/section}
									</select>
								{/if}
               				 {/if}
						</div>
						<div class="col-md-4">
							<div class="form-group"  {if $eform_user}style="display:none;"{/if}> 
							
								{if !$user.active}
									<label><b>This user is currently <span class="text-danger">INACTIVE</span></b></label>
									<input class="form-control" onclick="run('a=k&user_id={$user.id}');" type=button value="Activate"> 
									<input class="form-control" onclick="run('a=r&user_id={$user.id}');" type=button value="Reset Password">
								{else}
								   <label><b>This user is currently ACTIVE</b></label>
								   <input class="form-control" onclick="run('a=d&user_id={$user.id}');" type=button value="Deactivate">
								{/if}
				
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group" {if $eform_user}style="display:none;"{/if}> 
								{if $user.locked}
									<label><b>This user is currently <span class="text-danger">LOCKED</span></b></label>
									<input class="form-control" onclick="run('a=j&user_id={$user.id}');" type=button value="Unlock">
								{else}
									<label><b>This user is UNLOCKED</b></label>
									<input class="form-control" onclick="run('a=l&user_id={$user.id}');" type=button value="Lock">
								{/if}
							</div>
						</div>
					</div>
				</form>
			<hr noshade size=1>
		{/if}
	{/if}
	
	{if $sessioninfo.privilege.USERS_MNG}
	{if !$user.template}
	<form method=post name=f_e onsubmit="return check_e(0)">
	{else}
<form method=post name=f_e onsubmit="return check_e(1)">
	{/if}
	<input type=hidden name=template value={$user.template}>
	<input type=hidden name=a value=m>
	<input type=hidden name=user_id value={$user.id}>
	<input type=hidden name=eform value={$eform_user}>
	
	{if !$user.template}
	
	<p align=center>
	<input class="btn btn-warning hide_eform" name=resetbtn type=reset value="Restore"  {if $eform_user}style="display:none;"{/if}>
	{if $eform_user}
		<input class="btn" type="button" onclick="document.location='users.application.php?a=return_profile&user_id={$eform_user}'" value="Close">
	{/if}
	<input class="btn btn-primary" name=submitbtn type=submit value="Update">
	</p>
	
	<div class=errmsg>
	{if $errmsg.a}
	<ul>{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}</ul>
	<script>
	var msg = '';
	{foreach item=m from=$errmsg.a}
	msg += '- {$m}\n';
	{/foreach}
	alert(msg);
	</script>
	{/if}
	</div>
	<form class="form-horizontal">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group row ml-1">
					<b><label>Last Login : </label></b>
					<p>&nbsp;{$user.lastlogin|date_format:"%e/%m/%y %H:%M:%S"}</p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group row ml-1">
					<b><label>User ID : </label></b>
					<p>&nbsp;{$user.id}</p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group row ml-1">
					<b><label>Username : </label></b>
					<p>&nbsp;{$user.u}</p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					{if $config.enable_suite_device}
						<label class="mt-3">Fnb Username</label>
						<input type="text" class="form-control" name="fnb_username" value="{$user.fnb_username}">
						<small class="text-muted">
							<span>(For Fnb Cashier use)</span>
						</small>
					{/if}
				</div>
			</div>
			{if $config.user_profile_need_ic}
			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">IC No.<span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="ic_no" value="{$user.ic_no}">
				</div>
			</div>
			{/if}
			{if !$config.consignment_modules}
			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">Login Barcode</label>
						<input type="text" class="form-control" name="ic_no" value="{$user.barcode}" onBlur="uc(this)">
						<small class="text-muted">
							<span>(For POS Counter use) only numeric)</span>
						</small>
				</div>
			</div>
			{/if}
			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">Full Name<span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="fullname" value="{$user.fullname}" onBlur="uc(this)">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">Position<span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="position" value="{$user.position}" onBlur="uc(this)">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">User Department</label>
						<input type="text" class="form-control" name="user_dept" value="{$user.user_dept}" onBlur="uc(this)">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
						<label class="mt-3">Location</label>
						<select class="form-control" name="default_branch_id" onchange="uname_blur(newuser)">
							{section name=i loop=$branches}
							<option value="{$branches[i].id}" {if $user.default_branch_id == $branches[i].id}selected{/if}>{$branches[i].code}</option>
							{/section}
						</select>
				</div>
			</div>
		</div>
			<div class="row">
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
						<input id="vendors_all_id" class="departments form-check-input" type="checkbox" onclick="toggle_all_check(this,'vendors','vendors')" {if not $user.vendors}checked{/if} > 
						<label class="form-check-label" for="vendors_all_id"> All </label>	
					</div> 		
				
					<div class="" id="vendors_id" {if not $user.vendors}style="display:none"{/if}>
						<div class="conatainer rounded bg-light p-3 mt-2 overflow-auto" style="height: 25vh;" >
							{section name=i loop=$vendors}
							{assign var=id value=`$vendors[i].id`}
							<input type=checkbox class="vendors p-4" name="vendors[{$vendors[i].id}]" {if $user.vendors.$id}checked {/if}> {$vendors[i].description}<br>
							{/section}
						</div>
							<small class="mt-1"><b>Note:</b> <span class="text-muted ">All vendors remain unticked will be considered have privilege on all vendors</span></small>
					</div>	
				</div>
				<!--vendors  section end -->

				<!--Brands Section start-->
				<div class="col-md-6">
					<label class="mt-3 tx-bold">Brands</label>
					<div class="form-check " id="brands_all_id">
						<input id="brands_all_id" class="departments form-check-input" type="checkbox" onclick="toggle_all_check(this,'brands','brands')" {if not $user.brands}checked{/if} > 
						<label class="form-check-label" for="brands_all_id"> All </label>	
					</div> 		
				
					<div class="" id="brands_id" {if not $user.brands}style="display:none"{/if}>
						<div class="conatainer rounded bg-light p-3 mt-2 overflow-auto" style="height: 25vh;" >
							<input type="checkbox" class="brands p-4" name="brands[0]" {if $user.brands[0]}checked {/if}> Unbranded<br>
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
			<div class="row">
				{if $config.consignment_modules && $config.masterfile_branch_region}
				<div class="col-md-6">
					<div class="form-group">
							<label class="mt-3"><b>Regions</b></label>
							<div id="regions">
								<div style="padding-bottom:10px;">
									<input class="form-control" type="checkbox" id="region_all_id" onclick="toggle_all_check(this,'regions','regions')">
									<label for="region_all_id"><b>All Regions</b></label>
									{foreach from=$config.masterfile_branch_region key=code item=r}
										<input class="form-control" type="checkbox" class="regions" name="regions[{$code}]" {if $user.regions.$code}checked {/if}> 
										<b>{$r.name}</b>
									{/foreach}
								</div>
							</div>
				
					</div>
				</div>
				{/if}
			</div>
			<div class="row">
				<div class="col-md-6">
					{if $BRANCH_CODE eq 'HQ' && $can_edit_level}
					<div class="form-group">
						<label class="mt-3">User Level</label>
						<select class="form-control" name="level">
							{foreach from=$user_level item=level key=n}
								<option value={$level} {if $user.level eq $level}selected{/if}>{$n}</option>
							{/foreach}
						</select>
					</div>
					{else}
					<input class="form-control" type="hidden" name="level" value="{$user.level}">
					
					{/if}
				</div>
				
				<div class="col-md-6">
					<div class="form-group">
						<label class="mt-3">Login ID <span class="text-danger"> *</span></label>
						<input class="form-control" name="newlogin "value="{$user.l}">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="mt-3">Password</label>
						<input class="form-control" name="newpassword" type="password" size=20>
						 <small class="text-muted">(password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</small>
					</div>
					
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="mt-3">Retype Password</label>
						<input class="form-control" alt="Reconfirm password" name="newpassword2" type="password">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
							<label class="mt-3">Email<span class="text-danger">*</span></label>
							<input class="form-control" name="newemail" size=20 value="{$user.email}" onchange="lc(this)">
							<small class="text-muted">(optional, but email is required for password reset)</small>
					</div>
				</div>
				<div class="col-md-6">
					<label class="mt-3">Discount Limits</label>
					<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))"><i class="fas fa-question"></i></a>
					<div class="input-group" > 
					<input name="disc_limit" class="form-control" maxlength="3" value="{$user.discount_limit}" >
						<div class="input-group-append">
							<div class="input-group-text">%</div>
						</div>
					</div>
					<div class="checkbox">
						<div class="custom-checkbox custom-control">
							<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" id="checkbox-1" name="item_disc_only_allow_percent" {if $user.item_disc_only_allow_percent}checked {/if} value="1">
							<label for="checkbox-1" class="custom-control-label mt-1 text-muted">
								<span class="tx-12">Force this user to only allow discount by percentage for Item Discount</span>
							</label>
						</div>
					</div>	
			</div>
			<div class="col-md-6">
				<div class="from-group" {if !$mprice_list}style="display:none;"{/if}>
		
						<label class="mt-3">Allow Mprice</label>
						<div class="row px-3 mt-3">
							<div class="checkbox mr-2">
								{assign var=mp value=$user.allow_mprice}
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
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="mt-3">Phone No</label>
	
						<input class="form-control" name="phone_1" maxlength="20" value="{$user.phone_1}" onchange="toggle_sms_notification();" />
						<small class="text-muted">(<b>eg:</b> 0123456789)</small>
						{if $config.notification_send_sms}
						<label>
							<input type="checkbox" name="sms_notification" {if $user.sms_notification}checked{/if} value="1" {if !$user.phone_1}disabled{/if} >
							Receive Notification by SMS
						</label>
						{/if}
					
				</div>
			</div>
		</div>

	</form>
	<hr noshade size=1>
	{/if}
		</div>
	</div>
       


	{include file=user_privilege_table.tpl}

		<div align="center" class="mb-3">
			<input class="btn btn-warning  hide_eform" name=resetbtn type=reset value="Restore" {if $eform_user}style="display:none;"{/if}>
			{if $eform_user}
				<input class="btn" type="button" onclick="document.location='users.application.php?a=return_profile&user_id={$eform_user}'" value="Close">
			{/if}
			<input class="btn btn-primary" name=submitbtn type=submit value="Update">
		</div>
	

</form>
	{/if}

<script>
if(document.f_e != null)
init_chg(document.f_e);

</script>
