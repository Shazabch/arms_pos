{*
3/01/2021 5:17 PM Rayleen
- New Module "User EForm Application"

03/09/2021 4:17 PM Rayleen
- Add 'Address', 'Mobile Number', 'Photo', 'Resume' field in user form
- Split User Eform Application Form in two columns (User details and Login Credentials)
- Hide deafult header and change to Branch Logo and Address
- Fix display for mobile

3/18/2021 5:27 PM rayleen
- Fix display for iphone mobile view
*}
{include file=header.tpl no_menu_templates=1 }
{literal}
<style>
#top_nav_header{
	display: none;
}

@media (min-width: 802px){
	#panel1{
		width: 50%;
		float: left;
	}
	#panel2{
		width: 50%;
		float: right;
	}
}

@media only screen and (max-device-width: 801px) and  (min-device-width: 0px) {
	table{
		font-size: 22px;
	} 
	input, select, textarea  {
	    line-height: 2.6;
	    width: 90%;
	}
	#submitbtn{
		width: 100px;
	}
	#tbl_header, h1{
		font-size: 24px;
	}
	#panel1{
		width: 100% !important;
		float: none !important;
	}
	#panel2{
		width: 100% !important;
		float: none !important;
	}
}

</style>
{/literal}
<script type="text/javascript">

var reserve_login_id ='';
{if $reserve_val}
	var reserve_login_id = {$reserve_val};
{/if}

{literal}
function check_a()
{
    if (empty(document.f_a.username, 'You must enter a username'))
	{
		return false;
	}
	if(reserve_login_id != ''){
		var newuser = document.f_a['username'].value.toLowerCase();
		for (var i = 0;i < reserve_login_id.length; i++) {
			if(newuser.startsWith(reserve_login_id[i].toLowerCase()) == true){
				alert('The username is not allow to start with "'+reserve_login_id[i]+'".');
				document.f_a['username'].value = '';
				document.f_a['username'].focus();
				return false;
			}
		}
	}

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
	if (empty(document.f_a.login, 'You must enter a Login ID'))
	{
		return false;
	}
	if (empty(document.f_a.password, 'You must enter a password'))
	{
		return false;
	}
	if (empty(document.f_a.address, 'You must enter an address'))
	{
		return false;
	}
	if (empty(document.f_a.mobile_number, 'You must enter a mobile number'))
	{
		return false;
	}
	if (empty(document.f_a.email, 'You must enter a password'))
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

	document.f_a.submitbtn.disabled = true;
	return true;
}


function uname_blur(u)
{
	lc(u);
}
{/literal}
</script>

<div class="table-responsive">
	<div class="card mx-3">
		<div class="card-body">
			<table width="50%%" id="tbl_header">
				<tr>
					<td width="10%"><img style="max-width: 600px;max-height: 80px;" src="{get_logo_url}" height="80" hspace="5" vspace="5"></td>
					<td>
						<b>{$branch[0].address}</b><br>
						Tel: {$branch[0].phone_1}{if $branch[0].phone_2} / {$branch[0].phone_2}{/if}
						{if $branch[0].phone_3}
						<br>Fax: {$branch[0].phone_3}
						{/if}
						{if $config.enable_gst and $branch[0].gst_register_no}
							 &nbsp;&nbsp;&nbsp; GST No: {$branch[0].gst_register_no}
						{/if}
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">User EForm Application</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class=errmsg>
{if $errmsg.a}<ul>{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}</ul>{/if}
</div>
<div class="card mx-3 px-4 py-3">
	<div class="card-body">
		{if !$is_submitted}
<div class="stdframe" style="margin-bottom:20px;">
	<form method=post name=f_a onsubmit="return check_a()" enctype="multipart/form-data">
		<input type=hidden name=a value="add_eform_user">
		<div id="panel1">
			<div class="table-responsive">
				<table id="top_form" width="100%" style="float:left">
					<tr>
						<td width="30%"><label>Location</label></td>
						<td width="70%">
							{$branch[0].code}
							<input class="form-control" type="hidden" name="branch_id" value="{$branch[0].id}">
						</td>
					</tr>
					<tr >
						<td><label>Photo </label><br><small>(png,jpg,jpeg)</small></td>
						<td><input class="form-control" type="file" name=photo > </td>
					</tr>
					<tr >
						<td><label>Full Name<span class="text-danger"> *</span></label></td>
						<td><input class="form-control" name=fullname size=45 maxlength=100 onBlur="uc(this)" value="{$smarty.request.fullname}" > </td>
					</tr>
					{if $config.user_profile_need_ic}
						<tr >
							<td><label>IC No.<span class="text-danger">*</span></label></td>
							<td>
								<input class="form-control" name="ic_no" size="45" maxlength="20" value="{$smarty.request.ic_no}" />
							</td>
						</tr>
					{/if}
					<tr >
						<td><label>Address<span class="text-danger"> *</span></label></td>
						<td><input class="form-control" name=address size=45 maxlength=100 onBlur="uc(this)" value="{$smarty.request.address}" > </td>
					</tr>
					<tr >
						<td><label>Position<span class="text-danger"> *</span></label></td>
						<td><input class="form-control" name=position size=45 maxlength=100 onBlur="uc(this)" value="{$smarty.request.position}"> 
					</tr>
					<tr >
						<td><label>Mobile Number<span class="text-danger"> *</span></label></td>
						<td><input class="form-control" name=mobile_number size=45 maxlength=100 onBlur="uc(this)" value="{$smarty.request.mobile_number}"> 
					</tr>
					<tr >
						<td><label>Resume</label><br><small>(PDF)</small></td>
						<td><input class="form-control" type="file" name=resume> </td>
					</tr>
				</table>
			</div>
		</div>
		<div id="panel2">
			<b><em>Login Credentials</em></b>
		<div class="table-responsive ml-2" >
			<table id="top_form" width="100%" style="float:left">
				<tr >
					<td><label>Username<span class="text-danger"> *</span></label></td>
					<td><input class="form-control" name=username size=45 maxlength=50 value="{$smarty.request.username}" onBlur="uname_blur(this)">
						<small>only a-z, 0-9 and underscore '_' allowed, minimum {$MIN_USERNAME_LENGTH} characters</small></td>
				</tr>
				<tr>
					<td><label>Login ID<span class="text-danger"> *</span></label></td>
					<td><input class="form-control" name=login size=45 maxlength=16 value="{$smarty.request.login}"> <span id=v4></span></td>	
				</tr>
				<tr >
					<td><label>Email<span class="text-danger"> *</span></label></td>
					<td><input class="form-control" type="email" name=email size=45 maxlength=100 onBlur="lc(this)" value="{$smarty.request.email}"> <span id=v5></span></td>
				</tr>
				<tr>
					<td><label>Password<span class="text-danger"> *</span></label></td>
					<td><input class="form-control" name=password type=password size=45 > 
					<small>(password should consists of numbers and alphabates,with at least {$MIN_PASSWORD_LENGTH} character)</small>
				</tr>
				<tr>
					<td><label>Retype Password<span class="text-danger"> *</span></label></td>
					<td><input class="form-control" name=password2 type=password size=45>  <span id=v5></span></td>
				</tr>
			</table>
		</div>
		</div>
		<br style="clear: both;">
		<p><input class="btn btn-primary mt-3" name=submitbtn type=submit value="Submit" id="submitbtn"></p>
	</form>
	{if $qr_code}
	<table id="top_form" width="50%" style="float:right;">
		<tr>
			<td colspan="2"><b>QR Code</b></td>
		</tr>
		<tr>
			<td colspan="2">
				<img src="../thumb.php?img={$qr_code|urlencode}&h=100&w=100"/>
				<br>
				<a href="{$qr_content}" target="_blank">{$qr_content}</a>
			</td>
		</tr>
	</table>
	{/if}
	<br style="clear: both;">
</div>
{else}
<div class="stdframe" style="margin-bottom:20px;">
	<h4>User Successfully Added.</h4>
</div>
{/if}


	</div>
</div>
{include file=footer.tpl}
<script>
</script>
