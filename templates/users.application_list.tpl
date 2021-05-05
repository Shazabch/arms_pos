{*
3/03/2021 3:20 PM Rayleen
- New Module "User EForm Application"
- Only users with USERS_EFORM_APPROVAL can update a application
- Add checking if user can approve application

3/09/2021 5:16 PM Rayleen
-Add new column 'Apply Time'

04/26/2021 5:53 PM Rayleen
- Add "activated_by" column in user application list
*}
<table class=sortable id=po_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>User Name</th>
	<th>Login ID</th>
	<th>Branch</th>
	<th>Template</th>
	<th>Full Name</th>
	<th>Email</th>
	<th>Position</th>
	<th>Apply Time</th>
	<th>Activated By</th>
</tr>
{section name=i loop=$application_list}
	<tr bgcolor={cycle values=",#eeeeee"}>
		<td align="center">
			<a href="users.application.php?a=show_profile&user_id={$application_list[i].user_id}">
			{if $status eq 0 and $sessioninfo.privilege.USERS_EFORM_APPROVAL and $can_approve_user }
				<img src="ui/ed.png" title="Update this User" border=0></a>
			{else}
				<img src="ui/view.png" title="View this User" border=0></a>
			{/if}
		</td>
		<td nowrap> {$application_list[i].username}</td>
		<td nowrap> {$application_list[i].login_id}</td>
		<td nowrap> {$application_list[i].code}</td>
		<td nowrap> {$application_list[i].template}</td>
		<td nowrap> {$application_list[i].fullname}</td>
		<td nowrap> {$application_list[i].email}</td>
		<td nowrap> {$application_list[i].position}</td>
		<td nowrap> {$application_list[i].added}</td>
		<td nowrap> {$application_list[i].activated_by}</td>
	</tr>
{sectionelse}
<tr>
	<td colspan="{$nr_colspan}" align="center">- no record -</td>
</tr>
{/section}
</table>

