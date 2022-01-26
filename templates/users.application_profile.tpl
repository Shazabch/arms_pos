{*
3/02/2021 9:47 AM Rayleen
- New Module "User EForm Application"

03/09/2021 2:37 PM Rayleen
- Add Department, Remarks and template field in the User Profile
*}
{include file=header.tpl}
{literal}
<style>
.u-status{
	font-size: 14px;
}
</style>
{/literal}
<script type="text/javascript">

{literal}
	function user_action(status)
	{
		var text = 'APPROVE';
		if(status==2){
			text = 'REJECT';
		}
		if(!confirm("Are you sure want to "+text+" this user?")) return;

		document.f_a.status.value = status;
		document.f_a.submit();
	}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>User EForm Application Profile</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class=errmsg>
<div class="alert alert-danger rounded">
	
		{if $errmsg.a}
		<ul>
			<li><span style="font-size: 13px;">This User can no longer be approved, please reject this application.</span></li>
			{foreach item=m from=$errmsg.a}<li>{$m}{/foreach}
		</ul>
		{/if}
		
</div>
</div>
<div class="card mx-3 card-body">
	<div class="stdframe" style="margin-bottom:20px;">
		<form name=f_a method=post >
			<input type=hidden name=a value=update_user>
			<input type=hidden name=status value=0>
			<input type=hidden name=user_id value={$user.id}>
			<div style="width: 50%">
				<table cellspacing="4" cellpadding="3"  {if $user.status eq 0}width="85%"{else}width="58%"{/if}>
					{if $user.photo}
					<tr>
						<td><img src="{$user.photo}" alt="" width="100px;"></td>
						<td></td>
					</tr>
					{/if}
					{if $user.status eq 1}
						{assign var=status value=Approved}
						<tr>
							<td class="u-status" style="color:#5D842E !important;"><b>Status</b></td>
							<td class="u-status" style="color:#5D842E !important;"><b>APPROVED</b></td>
						</tr>
					{/if}
					{if $user.status eq 2}
						{assign var=status value=Rejected}
						<tr>
							<td class="u-status" style="color:red !important;"><b>Status</b></td>
							<td class="u-status" style="color:red !important;"><b>REJECTED</b></td>
						</tr>
					{/if}
					{if $status }
					<tr>
						<td class="u-status"><b>{$status} Date</b></td>
						<td class="u-status"><b>{$user.approved_date}</b></td>
					</tr>
					<tr>
						<td><b>{$status} By</b></td>
						<td>{$approved_by}</td>
					</tr>
					{/if}
					{if $user.status eq 3}
						<tr>
							<td class="u-status" style="color:#5D842E !important;"><b>Status</b></td>
							<td class="u-status" style="color:#5D842E !important;"><b>ACTIVATED</b></td>
						</tr>
						<tr>
							<td><b>Approved Date</b></td>
							<td>{$user.approved_date}</td>
						</tr>
					{/if}
					<tr>
						<td><b>Date Added</b></td>
						<td>{$user.added}</td>
					</tr>
					<tr>
						<td><b>Location</b></td>
						<td>{$user.code}</td>
					</tr>
					{if $user.status eq 0}
					<tr>
						<td><b>Template</b></td>
						<td>
							<select class="form-control" name="template" id="template">
								<option value="">----</option>
								{section name=i loop=$templates}
								<option value={$templates[i].id} {if $smarty.request.template == $templates[i].id}selected{/if}>{$templates[i].u}</option>
								{/section}
							</select>
						</td>
					</tr>	
					<tr>
						<td><b>User Department</b></td>
						<td><input class="form-control" type="text" name="department" size="35"></td>
					</tr>	
					<tr>
						<td><b>Remarks</b></td>
						<td><textarea class="form-control" name="remarks" id="" cols="28" rows="3"></textarea></td>
					</tr>	
					{else}
						<tr>
							<td><b>Template</b></td>
							<td>{$user.template_code|default:'-'}</td>
						</tr>
						<tr>
							<td><b>Remarks</b></td>
							<td>{$user.remarks|default:'-'}</td>
						</tr>
					{/if}
				</table>
			</div>
			<br style="clear: both;">
			<div>
				<div style="float: left; width: 50%">
					<b><em>User Details</em></b>
					<table cellspacing="4" cellpadding="3" {if $user.status eq 0}width="50%"{else}width="60%"{/if}>
						<tr>
							<td><b>Full Name</b></td>
							<td>{$user.fullname}</td>
						</tr>
						{if $config.user_profile_need_ic}
							<tr>
								<td><b>IC No.</b></td>
								<td>{$user.ic_no}</td>
							</tr>
						{/if}
						<tr>
							<td><b>Address</b></td>
							<td>{$user.address}</td>
						</tr>	
						<tr>
							<td><b>Position</b></td>
							<td>{$user.position}</td>
						</tr>	
						<tr>
							<td><b>Mobile Number</b></td>
							<td>{$user.mobile_number}</td>
						</tr>
						{if $user.department}
						<tr>
							<td><b>Department</b></td>
							<td>{$user.department}</td>
						</tr>
						{/if}
						{if $user.resume}
						<tr>
							<td><b>Resume</b></td>
							<td><a type="button" href="{$user.resume}" target="_blank" class="btn btn-success"> View</a></td>
						</tr>
						{/if}
					</table>
				</div>
	
				<div  style="float: right; width: 50%">
					<b><em>Login Credentials</em></b>
					<table cellspacing="4" cellpadding="3"  width="50%">
						<tr>
							<td><b>Username</b></td>
							<td>{$user.username}</td>
						</tr>
						<tr>
							<td><b>Login ID</b></td>
							<td>{$user.login_id}</td>
						</tr>	
						<tr>
							<td><b>Email</b></td>
							<td>{$user.email}</td>
						</tr>	
					</table>
				</div>
			</div>
			<br style="clear: both;">
	
			<p align=center>
				{if !$approved_by and $sessioninfo.privilege.USERS_EFORM_APPROVAL and $can_approve_user}
					<input class="btn btn-primary" name=approve onclick="user_action(1)" type=button value="Approve">
					<input class="btn btn-danger" name=reject onclick="user_action(2)" type=button value="Reject">
				{else}
					<input class="btn btn-danger" type="button" onclick="document.location='users.application.php?a=application_list'" value="Close">
				{/if}
			</p>
		</form>
	</div>
</div>


{include file=footer.tpl}
<script>

</script>
