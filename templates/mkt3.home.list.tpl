{if $total_mkt>1}
{$pagination}
{/if}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>Department</th>
	<th>Create By</th>
	<th>User</th>
	<th>Due Date</th>
	<th>Offer Period</th>
	<th>Last Update</th>
</tr>
{section name=i loop=$list}
{if $list[i].id}
{if $last_id ne $list[i].id}
{if $last_id > 0}</tbody>{/if}
{assign var=last_id value=$list[i].id}
<tr>
	<td colspan=8 bgcolor=#ffffee class=large style="border-bottom:2px solid #fe9;color:#c00;font-weight:bold;">
	{$list[i].id|string_format:"MKT%05d"} - {$list[i].title}
	</td>
</tr>
<tbody id=tb_{$list[i].id}>
{/if}
<tr>
	<td>
		{if $list[i].approved}
		<a href="?a=view&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}&dept_id={$list[i].dept_id}"><img src=/ui/approved.png border=0></a>
		{elseif $list[i].status==0 and ($list[i].user_id == 0 or $list[i].user_id == $sessioninfo.id)}
		<a href="?a=open&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}&dept_id={$list[i].dept_id}"><img src=/ui/ed.png border=0></a>
		{elseif $list[i].status==2}
		<a href="?a={if $list[i].user_id == $sessioninfo.id}open{else}view{/if}&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}&dept_id={$list[i].dept_id}"><img src=/ui/rejected.png border=0></a>
		{else}
		<a href="?a=view&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}&dept_id={$list[i].dept_id}"><img src=/ui/view.png border=0></a>
		{/if}
	</td>
	<td nowrap>{$list[i].dept}<!--{$list[i].dept_id}-->
	 		{if preg_match('/\d/',$list[i].approvals)}
			<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$list[i].approvals}</font></div>
		{/if}
		</td>
	<td align=center>{$list[i].create_u}</td>
	<td align=center>{$list[i].u|default:"<font color=red>-NEW-</font>"}</td>
	<td nowrap align=center {if $list[i].due}class=due{/if}>{$list[i].submit_due_date_2|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].offer_from|date_format:"%d/%m/%Y"} - {$list[i].offer_to|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
</tr>
{else}
{assign var=no_record value=1}
{/if}
{/section}
</tbody>
</table>
{if $no_record}
<p align=center>- No record submitted-</p>
{/if}
{/if}
