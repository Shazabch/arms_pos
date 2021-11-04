{*
11/14/2011 11:16:43 AM Justin
- Added gender column.

12/5/2011 11:18:43 AM Justin
- Temporary hidden the field "Last Purchase Branch" for further enhancement.
- Disabled the sorting feature for Last Renew Branch column.

2/10/2012 5:03:43 PM Justin
- Enabled back "Last Purchase Branch" column and its sorting feature.

8/1/2012 5:59 PM Justin
- Enhanced to have update control by config.

9/13/2012 3:59 PM Justin
- Enhanced to mark * for points while point is not up to date.
- Enhanced to have a link to allow user click and mark member points recalculate when user has privilege.

9/25/2012 12:50 PM Justin
- Enhanced to only allow user recalculate points while is system admin.

9/27/2012 3:45 PM Justin
- Bug fixed of the indicator of points up to date is wrong.

2:32 PM 9/12/2013 Justin
- Modified the capture from "Apply Branch to "Issue Branch".

7/31/2019 2:25 PM Andy
- Added Module "Member Mobile App Details".

10/9/2019 3:55 PM Andy
- Added Module "Membership Package Info".

12/5/2019 4:48 PM Andy
- Enhanced to allow users to select which fields to display / print / export.
*}

{assign var=export_field value=$smarty.request.export_field}

<div class="table-responsive">
	<table width="100%" class=" table mb-0 text-md-nowrap  table-hover" >
		<thead class="bg-gray-100">
			<tr >
				<th >&nbsp;</th>
				<th>No</th>
				
				{if $export_field.card_no}<th onClick="sort_reloadTable('card_no','membership_listing');" class="clickable">Card No. {darrow col='card_no' grp='membership_listing'}</th>{/if}
				{if $export_field.nric}<th onClick="sort_reloadTable('nric','membership_listing');" class="clickable">NRIC {darrow col='nric' grp='membership_listing'}</th>{/if}
				{if $export_field.name}<th onClick="sort_reloadTable('name','membership_listing');" class="clickable">Name {darrow col='name' grp='membership_listing'}</th>{/if}
				{if $export_field.member_type}<th onClick="sort_reloadTable('member_type','membership_listing');" class="clickable">{$available_field.member_type.label} {darrow col='member_type' grp='membership_listing'}</th>{/if}
				{if $export_field.designation}<th onClick="sort_reloadTable('designation','membership_listing');" class="clickable">{$available_field.designation.label} {darrow col='designation' grp='membership_listing'}</th>{/if}
				{if $export_field.gender}<th onClick="sort_reloadTable('gender','membership_listing');" class="clickable">Gender {darrow col='gender' grp='membership_listing'}</th>{/if}
				{if $export_field.dob}<th onClick="sort_reloadTable('dob','membership_listing');" class="clickable">{$available_field.dob.label} {darrow col='dob' grp='membership_listing'}</th>{/if}
				{if $export_field.marital_status}<th onClick="sort_reloadTable('marital_status','membership_listing');" class="clickable">{$available_field.marital_status.label} {darrow col='dob' grp='membership_listing'}</th>{/if}
				{if $export_field.race}<th onClick="sort_reloadTable('race','membership_listing');" class="clickable">Race {darrow col='race' grp='membership_listing'}</th>{/if}
				{if $export_field.national}<th onClick="sort_reloadTable('national','membership_listing');" class="clickable">National {darrow col='national' grp='membership_listing'}</th>{/if}
				{if $export_field.education_level}<th onClick="sort_reloadTable('education_level','membership_listing');" class="clickable">{$available_field.education_level.label} {darrow col='education_level' grp='membership_listing'}</th>{/if}
				{if $export_field.preferred_lang}<th onClick="sort_reloadTable('preferred_lang','membership_listing');" class="clickable">{$available_field.preferred_lang.label} {darrow col='preferred_lang' grp='membership_listing'}</th>{/if}
				{if $export_field.address}<th onClick="sort_reloadTable('address','membership_listing');" class="clickable">{$available_field.address.label} {darrow col='address' grp='membership_listing'}</th>{/if}
				{if $export_field.postcode}<th onClick="sort_reloadTable('postcode','membership_listing');" class="clickable">{$available_field.postcode.label} {darrow col='postcode' grp='membership_listing'}</th>{/if}
				{if $export_field.city}<th onClick="sort_reloadTable('city','membership_listing');" class="clickable">{$available_field.city.label} {darrow col='city' grp='membership_listing'}</th>{/if}
				{if $export_field.state}<th onClick="sort_reloadTable('state','membership_listing');" class="clickable">{$available_field.state.label} {darrow col='state' grp='membership_listing'}</th>{/if}
				{if $export_field.phone_1}<th onClick="sort_reloadTable('phone_1','membership_listing');" class="clickable">{$available_field.phone_1.label} {darrow col='phone_1' grp='membership_listing'}</th>{/if}
				{if $export_field.phone_2}<th onClick="sort_reloadTable('phone_2','membership_listing');" class="clickable">{$available_field.phone_2.label} {darrow col='phone_2' grp='membership_listing'}</th>{/if}
				{if $export_field.phone_3}<th onClick="sort_reloadTable('phone_3','membership_listing');" class="clickable">{$available_field.phone_3.label} {darrow col='phone_3' grp='membership_listing'}</th>{/if}
				{if $export_field.email}<th onClick="sort_reloadTable('email','membership_listing');" class="clickable">{$available_field.email.label} {darrow col='email' grp='membership_listing'}</th>{/if}
				{if $export_field.apply_branch}<th onClick="sort_reloadTable('apply_branch_code','membership_listing');" class="clickable">{$available_field.apply_branch.label} {darrow col='apply_branch_code' grp='membership_listing'}</th>{/if}
				{if $export_field.last_renew_branch}<th onClick="sort_reloadTable('last_renew_branch','membership_listing');" class="clickable">{$available_field.last_renew_branch.label} {darrow col='last_renew_branch' grp='membership_listing'}</th>{/if}
				{if $export_field.last_purchase_branch}<th onClick="sort_reloadTable('lp_branch_code','membership_listing');" class="clickable">{$available_field.last_purchase_branch.label} {darrow col='lp_branch_code' grp='membership_listing'}</th>{/if}
				{if $export_field.points}<th onClick="sort_reloadTable('points','membership_listing');" class="clickable">Points {darrow col='points' grp='membership_listing'}</th>{/if}
				{if $export_field.points_update}<th onClick="sort_reloadTable('points_update','membership_listing');" class="clickable">Points Update {darrow col='points_update' grp='membership_listing'}</th>{/if}
				{if $export_field.issue_date}<th onClick="sort_reloadTable('issue_date','membership_listing');" class="clickable">Issue Date {darrow col='issue_date' grp='membership_listing'}</th>{/if}
				{if $export_field.next_expiry_date}<th onClick="sort_reloadTable('next_expiry_date','membership_listing');" class="clickable">Expiry Date {darrow col='next_expiry_date' grp='membership_listing'}</th>{/if}
				{if $export_field.terminated_date}<th onClick="sort_reloadTable('terminated_date','membership_listing');" class="clickable">Terminated Date {darrow col='terminated_date' grp='membership_listing'}</th>{/if}
				{if $export_field.blocked_date}<th onClick="sort_reloadTable('blocked_date','membership_listing');" class="clickable">Blocked Date {darrow col='blocked_date' grp='membership_listing'}</th>{/if}
				{if $export_field.verified_by}<th onClick="sort_reloadTable('u.u','membership_listing');" class="clickable">Verified By {darrow col='u.u' grp='membership_listing'}</th>{/if}
				
				{if $export_field.occupation}<th onClick="sort_reloadTable('occupation','membership_listing');" class="clickable">{$available_field.occupation.label} {darrow col='occupation' grp='membership_listing'}</th>{/if}
				{if $export_field.income}<th onClick="sort_reloadTable('income','membership_listing');" class="clickable">{$available_field.income.label} {darrow col='income' grp='membership_listing'}</th>{/if}
				{if $export_field.mobile_registered_time}<th onClick="sort_reloadTable('mobile_registered_time','membership_listing');" class="clickable">{$available_field.mobile_registered_time.label} {darrow col='mobile_registered_time' grp='membership_listing'}</th>{/if}
				{if $export_field.newspaper}<th onClick="sort_reloadTable('newspaper','membership_listing');" class="clickable">{$available_field.newspaper.label} {darrow col='newspaper' grp='membership_listing'}</th>{/if}
				{if $export_field.parent_nric}<th onClick="sort_reloadTable('parent_nric','membership_listing');" class="clickable">{$available_field.parent_nric.label} {darrow col='parent_nric' grp='membership_listing'}</th>{/if}
			</tr>
		</thead>
		{foreach name=i from=$members item=member}
		<tr bgcolor={cycle values="#ffffff","#eeeeee"} >
			<td nowrap style="width: 100px;">
				{if $sessioninfo.privilege.MEMBERSHIP_EDIT or $sessioninfo.privilege.MEMBERSHIP_ADD}
					{if $config.membership_update_control && $BRANCH_CODE ne 'HQ' && $sessioninfo.branch_id ne $member.apply_branch_id}
						<a href="membership.php?t=view&a=i&nric={$member.nric}" target="_BLANK"><img src="/ui/view.png" border=0 title="View Information"></a>
					{else}
						<a href="membership.php?t=update&a=i&nric={$member.nric}" target="_BLANK"><img src="/ui/ed.png" border=0 title="Update Information"></a>
						{if $sessioninfo.level >= 9999}
							<a onclick="set_changed_member_points('{$member.nric}');" style="cursor:pointer;"><img src="/ui/icons/arrow_refresh.png" border=0 title="Recalculate Member Point for {$member.name}"></a>
						{/if}
						
					{/if}
				{/if}
				<a href="membership.php?t=history&a=i&nric={$member.nric}" target="_BLANK"><img src="/ui/icons/star.png" border=0 title="Check Point & History"></a>
				
				{if $config.membership_mobile_settings and (!$config.membership_update_control or $BRANCH_CODE eq 'HQ' or ($config.membership_update_control && $sessioninfo.branch_id eq $member.apply_branch_id)) and ($sessioninfo.privilege.MEMBERSHIP_EDIT)}
					<a href="membership.mobile_details.php?nric={$member.nric|urlencode}" target="_BLANK">
						<img src="ui/icons/ipod_cast.png" title="Member Mobile App Info" />
					</a>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.details.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_REDEEM)}
					<a href="membership.package.details.php?nric={$member.nric|urlencode}" target="_BLANK">
						<img src="ui/icons/package.png" title="Membership Package Info" />
					</a>
				{/if}
			</td>
			<td>{$smarty.foreach.i.iteration+$start_counter}.</td>
			{if $export_field.card_no}<td>{$member.card_no}</td>{/if}
			{if $export_field.nric}<td>{$member.nric}</td>{/if}
			{if $export_field.name}<td>{$member.name}</td>{/if}
			{if $export_field.member_type}<td align="center">{$config.membership_type[$member.member_type]|default:'&nbsp;'}</td>{/if}
			{if $export_field.designation}<td align="center">{$member.designation|default:'&nbsp;'}</td>{/if}
			{if $export_field.gender}<td align="center">{$member.gender}</td>{/if}
			{if $export_field.dob}<td align="center">{$member.dob|date_format:$config.dat_format|default:"-"}</td>{/if}
			{if $export_field.marital_status}<td align="center">{if $form.marital_status eq 1}Married{else}Single{/if}</td>{/if}
			{if $export_field.race}<td align="center">{$member.race}</td>{/if}
			{if $export_field.national}<td align="center">{$member.national}</td>{/if}
			{if $export_field.education_level}<td align="center">{$member.education_level|default:'&nbsp;'}</td>{/if}
			{if $export_field.preferred_lang}<td align="center">{$member.preferred_lang|default:'&nbsp;'}</td>{/if}
			{if $export_field.address}<td>{$member.address|default:'&nbsp;'}</td>{/if}
			{if $export_field.postcode}<td>{$member.postcode|default:'&nbsp;'}</td>{/if}
			{if $export_field.city}<td>{$member.city|default:'&nbsp;'}</td>{/if}
			{if $export_field.state}<td>{$member.state|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_1}<td>{$member.phone_1|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_2}<td>{$member.phone_2|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_3}<td>{$member.phone_3|default:'&nbsp;'}</td>{/if}
			{if $export_field.email}<td>{$member.email|default:'&nbsp;'}</td>{/if}
			{if $export_field.apply_branch}<td align="center">{$member.apply_branch_code|default:'&nbsp;'}</td>{/if}
			{if $export_field.last_renew_branch}<td align="center">{$member.last_renew_branch|default:'&nbsp;'}</td>{/if}
			{if $export_field.last_purchase_branch}<td align="center">{$member.lp_branch_code|default:'&nbsp;'}</td>{/if}
			{if $export_field.points}		
				<td align="right" nowrap>
					{$member.points}
					<span id="span_points_{$member.nric}" style="color:red; font-size:16px; font-weight:bold;{if !$member.points_changed} display:none;{/if}">*</span>
				</td>
			{/if}
			{if $export_field.points_update}<td align="center">{if $member.points_update>0}{$member.points_update|date_format:$config.dat_format}{else}-{/if}</td>{/if}
			{if $export_field.issue_date}<td align="center">{if $member.issue_date>0}{$member.issue_date|date_format:$config.dat_format}{else}-{/if}</td>{/if}
			{if $export_field.next_expiry_date}<td align="center" {if $member.next_expiry_date>0}id="clr_in_red"{/if}>{if $member.next_expiry_date>0}{$member.next_expiry_date|date_format:$config.dat_format}{else}-{/if}</td>{/if}
			{if $export_field.terminated_date}<td align="center" {if $member.terminated_date>0}id="clr_in_red"{/if}>{if $member.terminated_date>0}{$member.terminated_date|date_format:$config.dat_format}{else}-{/if}</td>{/if}
			{if $export_field.blocked_date}<td align="center" {if $member.blocked_date>0}id="clr_in_red"{/if}>{if $member.blocked_date>0}{$member.blocked_date|date_format:$config.dat_format}{else}-{/if}</td>{/if}
			{if $export_field.verified_by}<td align="center">{$member.u|default:"-"}</td>{/if}
			{if $export_field.occupation}<td align="center">{$member.occupation|default:"-"}</td>{/if}
			{if $export_field.income}<td align="center">{$member.income|default:"-"}</td>{/if}
			{if $export_field.mobile_registered_time}<td align="center" nowrap>{if $member.mobile_registered_time>0}{$member.mobile_registered_time|default:"-"}{else}-{/if}</td>{/if}
			{if $export_field.newspaper}
				<td align="center">
					{foreach from=$member.newspaper key=newspaper item=dummy}
						{$newspaper}
					{/foreach}
				</td>
			{/if}
			{if $export_field.parent_nric}<td align="center">{$member.parent_nric|default:"-"}</td>{/if}
			
		</tr>
		{/foreach}
	</table>
	
</div>