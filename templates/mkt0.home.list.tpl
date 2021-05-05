{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>MKT No.</th>
	<th>Title</th>
	<th>User</th>
	<th>Offer Period</th>
	<th>Last Update</th>
</tr>
{section name=i loop=$list}
<tr>
	<td>
		{if $list[i].active==0}
		<a href="?a=view&id={$list[i].id}&p_dae={$list[i].last_update}"><img src="ui/cancel.png" border=0></a>
		{elseif $list[i].status==0}
		<a href="?a=open&id={$list[i].id}&p_dae={$list[i].last_update}"><img src=/ui/ed.png border=0></a>
		{else}
		<a href="?a=view&id={$list[i].id}&p_dae={$list[i].last_update}"><img src=/ui/view.png border=0></a>
		{/if}
	</td>
	<td>{$list[i].id|string_format:"MKT%05d"}</td>
	<td nowrap>{$list[i].title}</td>
	<td align=center>{$list[i].u}</td>
	<td nowrap align=center>{$list[i].offer_from|date_format:"%d/%m/%Y"} - {$list[i].offer_to|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
</tr>
{/section}
</table>
{/if}
