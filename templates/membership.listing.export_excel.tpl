{*
5/27/2010 4:32:15 PM Andy
- Add phone 3 at export excel.

8/11/2010 11:53:23 AM Justin
- Added a DOB column.

6/29/2011 12:31:13 PM Andy
- Add can choose field to export excel.

8/17/2011 2:10:34 PM Justin
- Added address and email address export fields.

9/6/2011 5:41:52 PM Andy
- Fix DOB column cannot show in export excel.

11/9/2011 6:22:43 PM Justin
- Added to show new filter "Age".

11/14/2011 12:05:43 PM Justin
- Added to show new filter "Gender".
- Re-aligned the colspan.

6/26/2012 2:43:13 PM Justin
- Added all fields into the list.

7/30/2012 4:19:21 PM Justin
- Enhanced to include new extra info from config.

9/18/2012 4:20 PM Justin
- Changed the wording "Designation" into "Title".

12/5/2019 4:48 PM Andy
- Enhanced to allow users to select which fields to display / print / export.
*}

<div style="text-align:right">Page {$page} of {$totalpage}</div>
<table width="100%" cellspacing="0" cellpadding="4" class="tb" border="0">
	<tr>
		<td><b>Branch</b></td><td>{$print_params.branch}</td>
	    <td><b>Race</b></td><td>{$print_params.race}</td>
	    <td><b>City</b></td><td>{$print_params.city}</td>
	    <td><b>State</b></td><td>{$print_params.state}</td>
	    <td><b>Date Filter</b></td>
		<td>
			{if !$print_params.date_filter}--{else}
				{$print_params.date_filter}
			    {if $print_params.date_from and $print_params.date_to}
			    	Between {$print_params.date_from} - {$print_params.date_to}
			    {elseif $print_params.date_from}
			        Start from {$print_params.date_from}
                {elseif $print_params.date_to}
			        End at {$print_params.date_to}
				{/if}
			{/if}
		</td>
	</tr>
	<tr>
	    <td><b>Search by</b></td>
	    <td>
			{if !$print_params.search_type}--{else}
			    {$print_params.search_type} contains '{$print_params.search_value}'
			{/if}
		</td>
		<td><b>Terminated</b></td><td>{$print_params.terminated}</td>
		<td><b>Blocked</b></td><td>{$print_params.blocked}</td>
		<td><b>Verified</b></td><td>{$print_params.verified}</td>
		<td><b>Expiry</b></td><td>{$print_params.expiry}</td>
	</tr>
	{if $print_params.point_from or $print_params.point_to or $print_params.gender or $print_params.age_from or $print_params.age_to}
	    <tr>
			{assign var=colspan value=9}
	        {if $print_params.point_from or $print_params.point_to}
				<td><b>Points</b></td>
				<td {if !$print_params.gender && !$print_params.age_from && !$print_params.age_to}colspan="{$colspan}"{/if}>
					{if $print_params.point_from and $print_params.point_to}
						Between {$print_params.point_from} - {$print_params.point_to}
					{elseif $print_params.point_from}
						Start from {$print_params.point_from}
					{elseif $print_params.point_to}
						At most {$print_params.point_to}
					{/if}
				</td>
				{assign var=colspan value=$colspan-2}
			{/if}
			{if $print_params.gender}
				<td><b>Gender</b></td>
				<td {if !$print_params.age_from && !$print_params.age_to}colspan="{$colspan}"{/if}>{$print_params.gender}</td>
				{assign var=colspan value=$colspan-2}
			{/if}
		    {if $print_params.age_from or $print_params.age_to}
				<td><b>Age</b></td>
				<td colspan="{$colspan}">
					{if $print_params.age_from and $print_params.age_to}
						Between {$print_params.age_from} - {$print_params.age_to}
					{elseif $print_params.age_from}
						Start from {$print_params.age_from}
					{elseif $print_params.age_to}
						At most {$print_params.age_to}
					{/if}
				</td>
			{/if}
	    </tr>
	{/if}
</table>

<br />

<table width="100%" cellspacing="0" cellpadding="4" class="tb" border="0">
	<tr bgcolor="#cccccc">
	    <th width="20">&nbsp;</th>
	    {if $export_field.card_no}<th>Card No</th>{/if}
	    {if $export_field.nric}<th>NRIC</th>{/if}
	    {if $export_field.name}<th>Name</th>{/if}
	    {if $export_field.member_type}<th>Member<br />Type</th>{/if}
	    {if $export_field.designation}<th>Designation</th>{/if}
	    {if $export_field.gender}<th width="10">Gender</th>{/if}
	    {if $export_field.dob}<th>DOB</th>{/if}
	    {if $export_field.marital_status}<th>Marital<br />Status</th>{/if}
	    {if $export_field.race}<th>Race</th>{/if}
	    {if $export_field.national}<th>National</th>{/if}
	    {if $export_field.education_level}<th>Education<br />Level</th>{/if}
	    {if $export_field.preferred_lang}<th>Preferred<br />Language</th>{/if}
	    {if $export_field.address}<th>Address</th>{/if}
		{if $export_field.postcode}<th>{$available_field.postcode.label}</th>{/if}
		{if $export_field.city}<th>{$available_field.city.label}</th>{/if}
		{if $export_field.state}<th>{$available_field.state.label}</th>{/if}
	    {if $export_field.phone_1}<th>{$available_field.phone_1.label}</th>{/if}
		{if $export_field.phone_2}<th>{$available_field.phone_2.label}</th>{/if}
		{if $export_field.phone_3}<th>{$available_field.phone_3.label}</th>{/if}
	    {if $export_field.email}<th>Email</th>{/if}
		{if $export_field.apply_branch}<td>{$available_field.apply_branch.label}</td>{/if}
		{if $export_field.last_renew_branch}<td>{$available_field.last_renew_branch.label}</td>{/if}
		{if $export_field.last_purchase_branch}<td>{$available_field.last_purchase_branch.label}</td>{/if}
	    {if $export_field.points}<th>Points</th>{/if}
		{if $export_field.points_update}<th>Points<br />Update</th>{/if}
	    {if $export_field.issue_date}<th>Issue<br />Date</th>{/if}
	    {if $export_field.next_expiry_date}<th>Expiry<br />Date</th>{/if}
	    {if $export_field.terminated_date}<th>{$available_field.terminated_date.label}</th>{/if}
	    {if $export_field.blocked_date}<th>{$available_field.blocked_date.label}</th>{/if}
	    {if $export_field.verified_by}<th>{$available_field.verified_by.label}</th>{/if}
	    {if $export_field.occupation}<th>occupation</th>{/if}
	    {if $export_field.income}<th>Income</th>{/if}
	    {if $export_field.mobile_registered_time}<th>{$available_field.mobile_registered_time.label}</th>{/if}
		
	    {* if $export_field.newspaper}<th>Newspaper</th>{/if *}
	    {* if $export_field.recruit_by}<th>Recruit<br />By</th>{/if *}
	    {if $export_field.parent_nric}<th>Parent<br />Nric</th>{/if}
	    {* if $export_field.radio_station}<th>Radio<br />Station</th>{/if *}
	    {* if $export_field.credit_card}<th>Credit<br />Card</th>{/if *}
		{* if $config.membership_extra_info}
			{foreach from=$config.membership_extra_info key=col item=info}
				{if $export_field.$col}<th>{$info.description|replace:" ":"<br />"}</th>{/if}
			{/foreach}
		{/if *}
	</tr>
	{assign var=counter value=0}
	{foreach from=$items item=r name=i}
	{assign var=counter value=$counter+1}
	    <tr height="40">
	        <td align="left">{$start_counter++}.</td>
			{if $export_field.card_no}<td>{$r.card_no|default:'&nbsp;'}</td>{/if}
            {if $export_field.nric}<td>{$r.nric|default:'&nbsp;'}</td>{/if}
			{if $export_field.name}<td>{$r.name|default:'&nbsp;'}</td>{/if}
			{if $export_field.member_type}<td>{$config.membership_type[$r.member_type]|default:'&nbsp;'}</td>{/if}
			{if $export_field.designation}<td>{$r.designation|default:'&nbsp;'}</td>{/if}
			{if $export_field.gender}<td>{$r.gender|default:'&nbsp;'}</td>{/if}
			{if $export_field.dob}<td>{if $r.dob>0}{$r.dob|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.marital_status}<td>{if $r.marital_status eq 1}Married{else}Single{/if}</td>{/if}
			{if $export_field.race}<td>{$r.race|default:'&nbsp;'}</td>{/if}
			{if $export_field.national}<td>{$r.national|default:'&nbsp;'}</td>{/if}
			{if $export_field.education_level}<td>{$r.education_level|default:'&nbsp;'}</td>{/if}
			{if $export_field.preferred_lang}<td>{$r.preferred_lang|default:'&nbsp;'}</td>{/if}
			{if $export_field.address}<td>{$r.address|default:'&nbsp;'}</td>{/if}
			{if $export_field.postcode}<td>{$r.postcode|default:'&nbsp;'}</td>{/if}
			{if $export_field.city}<td>{$r.city|default:'&nbsp;'}</td>{/if}
			{if $export_field.state}<td>{$r.state|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_1}<td>{$r.phone_1|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_2}<td>{$r.phone_2|default:'&nbsp;'}</td>{/if}
			{if $export_field.phone_3}<td>{$r.phone_3|default:'&nbsp;'}</td>{/if}
			{if $export_field.email}<td>{$r.email|default:'&nbsp;'}</td>{/if}
			{if $export_field.apply_branch}<td>{$r.apply_branch_code|default:'&nbsp;'}</td>{/if}
			{if $export_field.last_renew_branch}<td>{$r.last_renew_branch|default:'&nbsp;'}</td>{/if}
			{if $export_field.last_purchase_branch}<td>{$r.lp_branch_code|default:'&nbsp;'}</td>{/if}
			{if $export_field.points}<td align="right">{$r.points|default:'&nbsp;'}</td>{/if}
			{if $export_field.points_update}<td>{if $r.points_update>0}{$r.points_update|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.issue_date}<td>{if $r.issue_date>0}{$r.issue_date|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.next_expiry_date}<td>{if $r.next_expiry_date>0}{$r.next_expiry_date|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.terminated_date}<td>{if $r.terminated_date>0}{$r.terminated_date|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.blocked_date}<td>{if $r.blocked_date>0}{$r.blocked_date|date_format:$config.dat_format|default:'&nbsp;'}{else}&nbsp;{/if}</td>{/if}
			{if $export_field.verified_by}<td>{$r.u|default:'&nbsp;'}</td>{/if}
			{if $export_field.occupation}<td>{$r.occupation|default:'&nbsp;'}</td>{/if}
			{if $export_field.income}<td>{$r.income|default:'&nbsp;'}</td>{/if}
			{if $export_field.mobile_registered_time}<td>{if $r.mobile_registered_time>0}{$r.mobile_registered_time|default:"-"}{else}&nbsp;{/if}</td>{/if}
			
			{* if $export_field.newspaper}<td>{$r.newspaper|default:'&nbsp;'}</td>{/if *}
			{* if $export_field.recruit_by}<td>{$r.recruit_by|default:'&nbsp;'}</td>{/if *}
			{if $export_field.parent_nric}<td>{$r.parent_nric|default:'&nbsp;'}</td>{/if}
			{* if $export_field.radio_station}<td>{$r.radio_station|default:'&nbsp;'}</td>{/if *}
			{* if $export_field.credit_card}<td>{$r.credit_card|default:'&nbsp;'}</td>{/if *}
			{* if $config.membership_extra_info}
				{foreach from=$config.membership_extra_info key=col item=info}
					{if $export_field.$col}<td>{$r.$col|default:'&nbsp;'}</td>{/if}
				{/foreach}
			{/if *}
	    </tr>
	{/foreach}
</table>
