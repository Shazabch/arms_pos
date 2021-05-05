{*
3/13/2013 4:46 PM Justin
- Enhanced to show * for those compulsory fields.
- Enhanced to have checking for those compulsory fields before submit.

12/13/2013 3:31 PM Justin
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

<h1>Update My Profile</h1>
<ul>
<li> Please change your password regularly to ensure security.
<li> Do not share your Login ID and Password with others
</ul>

<div class="stdframe">

<form method=post name=f_e onsubmit="return check_e()">

<input type=hidden name=a value=m>

<div class=errmsg>
{if $errmsg.a}<ul>{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}</ul>{/if}
</div>
<table >
<tr>
<td width=100><b>Last Login</b></td><td>{$user.lastlogin|date_format:"%e/%m/%y %H:%M:%S"}</td>
</tr><tr>
<td><b>User ID</b></td><td>{$user.id}</td>
</tr><tr>
<td><b>Username</b></td><td>{$user.u}</td>
</tr><tr>
<td><b>Full Name</b></td><td><input name=fullname size=50 maxlength=100 value="{$user.fullname}"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr><tr>
<td><b>Login ID</b></td><td><input name=loginid size=20 value="{$user.l}"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr><tr>
<td><b>Password</b></td><td><input name=password type=password size=20> (password should consists of numbers and alphabates, with at least {$MIN_PASSWORD_LENGTH} character)</td>
</tr><tr>
<td><b>Retype Password</b></td><td><input alt="Reconfirm password" name=password2 type=password size=20></td>
</tr>
{if $sessioninfo.privilege.UPDATE_PROFILE_EMAIL}
<tr>
<td><b>Email</b></td><td><input name=email size=30 value="{$user.email}" onchange="lc(this)"></td>
</tr>
{else}
<input name=email size=30 value="{$user.email}" type=hidden> <img src=ui/rq.gif align=absbottom title="Required Field">
{/if}
</table>

<br>

<p align=center>
<input class="btn btn-warning" name=resetbtn type=reset value="Restore">
<input class="btn btn-primary" name=submitbtn type=submit value="Update">
</p>
</form>

</div>


<script>
init_chg(window.document.f_e);
</script>

{include file=footer.tpl}
