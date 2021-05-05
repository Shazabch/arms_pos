{*
10/25/2019 10:26 AM Andy
- Rename to word from "Entry" to "Credit".
*}

{if !$package_list}
	<p align="center"> &nbsp; * No Data Found *</p>
{else}
    {if $total_page >1}
		<div style="padding:2px;float:left;">
			Page
			<select id="sel_page" onChange="CC_ASSGN_LIST.list_sel();">
				{section loop=$total_page name=s}
					<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
				{/section}
			</select>
		</div>
	{/if}

	<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="100">&nbsp;</th>
			<th width="60">Created Branch</th>
			<th width="100">Document No</th>
			<th width="60">Title</th>
			<th width="80">Valid From</th>
			<th width="80">Valid To</th>
			<th>Allowed Branches</th>
			<th width="80">Total Credit Earned</th>
			<th width="80">Created By</th>
			<th width="120">Last Update</th>
		</tr>
		
		{foreach from=$package_list item=r}
		    <tr bgcolor='{cycle values=",#eeeeee"}'>
				<td>
					{assign var=can_edit value=0}
					{if $r.active}
						{if ($r.status eq 0) and $r.branch_id eq $sessioninfo.branch_id}
							{assign var=can_edit value=1}
						{/if}
					{else}
					
					{/if}
					
					{if $can_edit}
						<a href="membership.package.setup.php?a=open&package_unique_id={$r.unique_id}"><img src="ui/ed.png" title="Edit" border="0" /></a>
					{else}
						<a href="membership.package.setup.php?a=view&package_unique_id={$r.unique_id}"><img src="ui/view.png" title="View" border="0" /></a>
					{/if}
				</td>
				
				<td>{$r.bcode}</td>
				<td>{$r.doc_no}</td>
				<td>{$r.title}</td>
				<td>{$r.valid_from}</td>
				<td>{$r.valid_to}</td>
				
				<td>
					{foreach from=$r.allowed_branches item=tmp_bid name=fb}
						{if !$smarty.foreach.fb.first}, {/if}
						{$branches_list.$tmp_bid.code}
					{/foreach}
				</td>
				<td align="right">{$r.total_entry_earn|number_format}</td>
				<td align="center">{$r.owner_u}</td>
				<td align="center">{$r.last_update}</td>
			</tr>
		{/foreach}
	</table>
{/if}