{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0>
<tr bgcolor=#ffee99 height=24>
	<th colspan=3>&nbsp;</th>
	<th>User</th>
	<th>Due Date</th>
	<th>Offer Period</th>
	<th>Last Update</th>
</tr>
{section name=i loop=$list}
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
	<a href="?a=view&mkt0_id={$list[i].id}&user_id={$sessioninfo.id}"><img src=/ui/view.png border=0></a>
	</td>
	<td nowrap>{$list[i].dept}<!--{$list[i].dept_id}--></td>
	<td align=center>{$list[i].create_u}</td>
	<td align=center>{$list[i].u|default:"<font color=red>-NEW-</font>"}</td>
	<td nowrap align=center {if $list[i].due}class=due{/if}>{$list[i].submit_due_date_4|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].offer_from|date_format:"%d/%m/%Y"} - {$list[i].offer_to|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
</tr>
{/section}
</tbody>
</table>
{/if}
