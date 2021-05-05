{*
1/18/2013 4:49 PM Justin
- Enhanced to show department.
*}

{$pagination}
{assign var=nr_colspan value=8}
<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
<tr bgcolor="#ffee99">
	<th>&nbsp;</th>
	<th>GRN No</th>
	{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
		{assign var=nr_colspan value=$nr_colspan+1}
		<th>Branch</th>
	{/if}
	<th>GRR No</th>
	<th>GRR Doc</th>
	<th>Type</th>
	<th>Department</th>
	<th>Selling</th>
	<th>Amount</th>
	<th>Last Update</th>
</tr>

{section name=i loop=$grn_list}
<tr bgcolor="{cycle values=',#eeeeee'}">
	<td align="center" nowrap>
		{if $grn_list[i].active}
		{if $grn_list[i].status eq 0}
		{if $grn_list[i].user_id eq $vp_session.vp.link_user_id}
		<a href="{$smarty.server.PHP_SELF}?a=open&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}"><img src="ui/ed.png" title="Open this GRN" border=0></a>
		{else}
		<a href="{$smarty.server.PHP_SELF}?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target="_blank"><img src="ui/view.png" title="Open this GRN" border=0></a>
		{/if}
		{else}
		<a href="{$smarty.server.PHP_SELF}?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target="_blank"><img src="ui/approved.png" title="Open this GRN" border=0></a>
		{/if}
		{else}
		<a href="{$smarty.server.PHP_SELF}?a=view&id={$grn_list[i].id}&branch_id={$grn_list[i].branch_id}" target="_blank"><img src="ui/cancel.png" title="Open this GRN" border=0></a>
		{/if}

		{if $grn_list[i].active && (($grn_list[i].status==1 && $grn_list[i].approved) || $grn_list[i].status eq 0)}
		<a href="javascript:void(GRN.do_print({$grn_list[i].id},{$grn_list[i].branch_id}))"><img src="ui/print.png" title="Print this GRN Reports" border=0></a>
		{/if}
	</td>
	<td>GRN{$grn_list[i].id|string_format:"%05d"}</td>
	{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
		<td>{$grn_list[i].branch_code}</td>
	{/if}
	<td nowrap>GRR{$grn_list[i].grr_id|string_format:"%05d"}{if !$grn_list[i].is_future}/{$grn_list[i].grr_item_id}{/if}</td>
	<td align="center">{$grn_list[i].doc_no}</td>
	<td align="center">{$grn_list[i].type}</td>
	<td align="center">
		{assign var=department_id value=$grn_list[i].department_id}
		{$departments.$department_id.description|default:'-'}
	</td>
	<td align="right">{$grn_list[i].total_selling|number_format:2}</td>
	<td align="right">{$grn_list[i].amount|number_format:2}</td>
	<td align="center">{$grn_list[i].last_update}</td>
</tr>
{sectionelse}
<tr>
	<td colspan="{$nr_colspan}" align="center">- no record -</td>
</tr>
{/section}
</table>