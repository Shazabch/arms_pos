{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>MKT No.</th>
	<th>Title</th>
	<th>Create By</th>
	<th>User</th>
	<th>Due Date</th>
	<th>Offer Period</th>
	<th>Last Update</th>
</tr>
{section name=i loop=$list}
<tr>
	<td>
		{if $list[i].approved}
		<a href="?a=view&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}"><img src=/ui/approved.png border=0></a>
		{elseif $list[i].status==0 and ($list[i].user_id == 0 or $list[i].user_id == $sessioninfo.id)}
		<a href="?a=open&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}"><img src=/ui/ed.png border=0></a>
		{elseif $list[i].status==2}
		<a href="?a={if $list[i].user_id == $sessioninfo.id}open{else}view{/if}&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}"><img src=/ui/rejected.png border=0></a>
		{else}
		<a href="?a=view&id={$list[i].id}&branch_id={$branch_id|default:$sessioninfo.branch_id}"><img src=/ui/view.png border=0></a>
		{/if}
	</td>
	<td>{$list[i].id|string_format:"MKT%05d"}</td>
	<td nowrap>{$list[i].title}
		{if preg_match('/\d/',$list[i].approvals)}
			<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$list[i].approvals}</font></div>
		{/if}
	</td>
	<td align=center>{$list[i].create_u}</td>
	<td align=center>{$list[i].u|default:"<font color=red>-NEW-</font>"}</td>
	<td nowrap align=center {if $list[i].due}class=due{/if}>{$list[i].submit_due_date|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].offer_from|date_format:"%d/%m/%Y"} - {$list[i].offer_to|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
</tr>
{/section}
</table>
{/if}
