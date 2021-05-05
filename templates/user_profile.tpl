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
        <form method=post name=f_c>
            <table width=100% border=0>
                <tr>
                    {if $BRANCH_CODE eq 'HQ'}
                        {if $sessioninfo.privilege.USERS_MNG}
                            <td align=center width=50%>
                                <input type=hidden name=a value=c>
                                <input type=hidden name=user_id value={$user.id}>
                                <h5>Copy User Privilege</h5>
                                Select template/user to copy from<br>
                                <select name=template_id onChange="copy_template(document.f_c)">
                                    <option value=0>----------</option>
                                    {section name=i loop=$templates}
                                        <option value={$templates[i].id}>{$templates[i].u} {if $templates[i].template}(Template){/if}</option>
                                    {/section}
                                </select>
                            </td>
                        {/if}
                    {/if}
                    <td align=center width=25%>
                    	<div {if $eform_user}style="display:none;"{/if}>
                        <h5 ><font color=black>
                        {if !$user.active}
                            This user is currently <font color=#ff0000>INACTIVE</font><br><br><input onclick="run('a=k&user_id={$user.id}');" type=button value="Activate"> <input onclick="run('a=r&user_id={$user.id}');" type=button value="Reset Password">
                        {else}
                           This user is currently ACTIVE<br><br><input onclick="run('a=d&user_id={$user.id}');" type=button value="Deactivate">
                        {/if}
                        </font></h5>
                        <div>
                    </td>
                    <td align=center width=25%>
                    	<div {if $eform_user}style="display:none;"{/if}>
                        <h5><font color=black>
                        {if $user.locked}
                            This user is currently <font color=#ff0000>LOCKED</font><br><br><input onclick="run('a=j&user_id={$user.id}');" type=button value="Unlock">
                        {else}
                            This user is UNLOCKED<br><br><input onclick="run('a=l&user_id={$user.id}');" type=button value="Lock">
                        {/if}
                        </font></h5>
                    	</div>
                    </td>

                </tr>
            </table>
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
<table >
<tr>
<td width=100><b>Last Login</b></td><td>{$user.lastlogin|date_format:"%e/%m/%y %H:%M:%S"}</td>
</tr><tr>
<td><b>User ID</b></td><td>{$user.id}</td>
</tr><tr>
<td><b>Username</b></td><td>{$user.u}</td>
</tr>
{if $config.enable_suite_device}
<tr>
<td><b>Fnb Username</b></td><td><input name=fnb_username size=50 maxlength=100 value="{$user.fnb_username}"> (For Fnb Cashier use)</td>
</tr>
{/if}
{if $config.user_profile_need_ic}
	<tr>
	    <td><b>IC No.</b></td>
	    <td><input name="ic_no" size="50" maxlength="20" value="{$user.ic_no}" /> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
	</tr>
{/if}
{if !$config.consignment_modules}
<tr>
	<td><b>Login Barcode</b></td>
	<td>
		<input name=barcode size=50 maxlength=16 value="{$user.barcode}" onBlur="uc(this)"> (For POS Counter use) only numeric
	</td>
</tr>
{/if}
<tr>
<td><b>Full Name</b></td><td><input name=fullname size=50 maxlength=100 value="{$user.fullname}" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr><tr>
<td><b>Position</b></td><td><input name=position size=50 maxlength=100 value="{$user.position}" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr>
<td><b>User Department</b></td><td><input name=user_dept size=50 maxlength=100 value="{$user.user_dept}" onBlur="uc(this)"></td>
</tr>
<tr>
<td><b>Location</b></td><td>
<select name=default_branch_id onchange="uname_blur(newuser)">
{section name=i loop=$branches}
<option value={$branches[i].id} {if $user.default_branch_id == $branches[i].id}selected{/if}>{$branches[i].code}</option>
{/section}
</select>
</td>
</tr>
<tr>
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
	<span style="white-space: nowrap"><input type=checkbox id=dept{$departments[i].id} class="departments root_{$departments[i].root_id}" name=departments[{$departments[i].id}] {if $user.departments.$id}checked {/if}><label for=dept{$departments[i].id}>{$departments[i].description}</label></span>
	{/section}
	</div>
	</td>
</tr>
<tr>
	<td valign=top><b>Vendors</b></td>
	<td id="vendors_all_id">
		<input type=checkbox id="vendors_all_id" onclick="toggle_all_check(this,'vendors','vendors')" {if not $user.vendors}checked{/if}> 
		<label for="vendors_all_id">All</label><br>	
	</td>
</tr>
<tr>
	<td></td>
	<td id="vendors_id" {if not $user.vendors}style="display:none"{/if}>
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		{section name=i loop=$vendors}
		{assign var=id value=`$vendors[i].id`}
		<input type=checkbox class="vendors" name=vendors[{$vendors[i].id}] {if $user.vendors.$id}checked {/if}> {$vendors[i].description}<br>
		{/section}
		</div>
		<div style="float:left">
			&nbsp;&nbsp;<b>Note:</b> All vendors remain unticked will be considered have privilege on all vendors
		</div>
	</td>
</tr>
<tr>
	<td valign=top><b>Brands</b></td>
	<td id="brands_all_id">
		<input type=checkbox id="brands_all_id" onclick="toggle_all_check(this,'brands','brands')" {if not $user.brands}checked{/if}> 
		<label for="brands_all_id">All</label><br>	
	</td>
</tr>
<tr>
	<td></td>
	<td id="brands_id" {if not $user.brands}style="display:none"{/if}>
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		<input type=checkbox class="brands" name=brands[0] {if $user.brands[0]}checked {/if}> Unbranded<br>

		{section name=i loop=$brands}
		{assign var=id value=`$brands[i].id`}
		<input type=checkbox class="brands" name=brands[{$brands[i].id}] {if $user.brands.$id}checked {/if}> {$brands[i].description}<br>
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
				<input type="checkbox" class="regions" name="regions[{$code}]" {if $user.regions.$code}checked {/if}> 
				<b>{$r.name}</b>
			{/foreach}
		</div>
		</td>
	</tr>
{/if}

{if $BRANCH_CODE eq 'HQ' && $can_edit_level}
	<tr>
		<td><b>User Level</b></td>
		<td>
			<select name=level>
				{foreach from=$user_level item=level key=n}
					<option value={$level} {if $user.level eq $level}selected{/if}>{$n}</option>
				{/foreach}
			</select>
		</td>
	</tr>
{else}
	<input type="hidden" name="level" value="{$user.level}">
{/if}
<tr>
<td><b>Login ID</b></td><td><input name=newlogin size=20 value="{$user.l}"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr><tr>
<td><b>Password</b></td><td><input name=newpassword type=password size=20> (password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</td>
</tr><tr>
<td><b>Retype Password</b></td><td><input alt="Reconfirm password" name=newpassword2 type=password size=20></td>
</tr><tr>
<td><b>Email</b></td><td><input name=newemail size=20 value="{$user.email}" onchange="lc(this)"> (optional, but email is required for password reset)</td>
</tr>
<tr>
	<td>
		<b>Discount Limit</b>
		[<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))">?</a>]
	</td>
	<td>
		<input name=disc_limit size=1 maxlength="3" value="{$user.discount_limit}"> % 
		{if $config.user_profile_show_item_discount_only_allow_percent}
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="item_disc_only_allow_percent" {if $user.item_disc_only_allow_percent}checked {/if} value="1" />
			Force this user to only allow discount by percentage for Item Discount
		{/if}
	</td>
</tr>

<tr {if !$mprice_list}style="display:none;"{/if}>
<td><b>Allow Mprice</b></td>
<td>
	<ul style="list-style:none; margin:0; padding:0;">
	{assign var=mp value=$user.allow_mprice}
		<li style="float:left; padding-right:10px; margin:0;"><input type="checkbox" type="margin-left:0;" name="allow_mprice[not_allow]" onclick="check_user_profile_allow_mprice_list(this)" {if $mp.not_allow}checked{/if}> Not Allow</li>
		{foreach from=$mprice_list item=val}
		<li class="user_profile_mprice_list" {if $mp.not_allow}style="display:none;float:left; padding-right:10px; margin:0;"{else}style="float:left; padding-right:10px; margin:0;"{/if}  ><input type="checkbox" style="margin-left:0;" name="allow_mprice[{$val}]" {if $mp.$val}checked{/if} /> {$val}</li>
		{/foreach}
	</ul>
</td>
</tr><tr>
<td><b>Phone No</b></td>
<td>
	<input name="phone_1" size="20" maxlength="20" value="{$user.phone_1}" onchange="toggle_sms_notification();" />&nbsp;&nbsp;&nbsp;(<b>eg:</b> 0123456789)
	{if $config.notification_send_sms}<label><input type="checkbox" name="sms_notification" {if $user.sms_notification}checked{/if} value="1" {if !$user.phone_1}disabled{/if} >Receive Notification by SMS</label>{/if}&nbsp;
</td>
</tr>
</table>
<hr noshade size=1>
{/if}


{include file=user_privilege_table.tpl}

<p align=center>
<input class="btn btn-warning hide_eform" name=resetbtn type=reset value="Restore" {if $eform_user}style="display:none;"{/if}>
{if $eform_user}
	<input class="btn" type="button" onclick="document.location='users.application.php?a=return_profile&user_id={$eform_user}'" value="Close">
{/if}
<input class="btn btn-primary" name=submitbtn type=submit value="Update">
</p>
</form>
{/if}

<script>
if(document.f_e != null)
init_chg(document.f_e);

</script>
