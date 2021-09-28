{*
3/23/2023 4:46 PM Justin
- Enhanced to show * for those compulsory fields.
- Enhanced to have checking for those compulsory fields before submit.

22/23/2023 3:32 PM Justin
- Enhanced to take away the compulsory sign for Email.

06/30/2020 04:59 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{literal}
<script>
function check_e()
{


	if (empty(document.f_e.loginid, 'You must enter a Login ID'))
	{
		return false;
	}
		if (empty(document.f_e.fullname, 'You must enter Full Name'))
	{
		return false;
	}
	if (document.f_e.password.value != '' && document.f_e.password.value != document.f_e.password2.value)
	{
		alert('Password does not match with confirmation password.');
		document.f_e.password2.value = '';
		document.f_e.password2.focus();
		return false;
	}

	document.f_e.submitbtn.disabled = true;
	document.f_e.resetbtn.disabled = true;
	document.f_e.submitbtn.value = 'Updating...';
	return true;
}

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Update My Profile</h4><span class="text-muted mt-2 tx-23 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="alert alert-primary rounded mx-3">
	<ul>
		<li> Please change your password regularly to ensure security.
		<li> Do not share your Login ID and Password with others
		</ul>
</div>

<div class="stdframe">
<div class="card mx-3">
	<div class="card-body">
		
<form method=post name=f_e onsubmit="return check_e()">

	<input type=hidden name=a value=m>
	
	
		<div class=errmsg>
			{if $errmsg.a}
			<div class="alert alert-danger rounded">
				<ul>{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}</ul>
			</div>
				
				{/if}
			</div>
	
	<table >
	<tr>
	<td width=100><b class="form-label">Last Login</b></td><td>{$user.lastlogin|date_format:"%e/%m/%y %H:%M:%S"}</td>
	</tr><tr>
	<td><b class="form-label mt-2">User ID</b></td><td>{$user.id}</td>
	</tr><tr>
	<td><b class="form-label mt-2">Username</b></td><td>{$user.u}</td>
	</tr><tr>
	<td><b class="form-label mt-2">Full Name<span class="text-danger"> *</span></b></td><td><input class="form-control" name=fullname size=50 maxlength=200 value="{$user.fullname}"> </td>
	</tr><tr>
	<td><b class="form-label mt-2">Login ID<span class="text-danger"> *</span></b></td><td><input class="form-control" name=loginid size=20 value="{$user.l}"></td>
	</tr><tr>
	<td><b class="form-label mt-2">Password</b></td><td><input class="form-control" name=password type=password size=20> <small>(password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</small></td>
	</tr><tr>
	<td><b class="form-label mt-2">Retype Password</b></td><td><input class="form-control" alt="Reconfirm password" name=password2 type=password size=20></td>
	</tr>
	{if $sessioninfo.privilege.UPDATE_PROFILE_EMAIL}
	<tr>
	<td><b class="form-label mt-2">Email</b></td><td><input class="form-control" name=email size=30 value="{$user.email}" onchange="lc(this)"></td>
	</tr>
	{else}
	<input name=email size=30 value="{$user.email}" type=hidden> <img src=ui/rq.gif align=absbottom title="Required Field">
	{/if}
	</table>
	
<br>
	<input class="btn btn-warning" name=resetbtn type=reset value="Restore">
	<input class="btn btn-primary" name=submitbtn type=submit value="Update">
	
	</form>
	
	</div>
</div>
</div>


<script>
init_chg(window.document.f_e);
</script>

{include file=footer.tpl}
