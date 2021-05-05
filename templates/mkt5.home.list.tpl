{if $total_mkt>1}
{$pagination}
{/if}
{if !$list}
<p align=center>- No record -</p>
{else}
<div class=small style="text-align:right;padding:4px;">
<img src=/ui/unchecked.gif align=absmiddle> MKT5 Not created
<img src=/ui/approved_grey.png align=absmiddle> MKT5 Not confirmed
<img src=/ui/approved.png align=absmiddle> MKT5 Confirmed
</div>
<table width=100% cellpadding=4 cellspacing=1 border=0>
<tr bgcolor=#ffee99>
	<th rowspan=2>&nbsp;</th>
	<th rowspan=2>Department</th>
	<th colspan={count var=$branches}>Participating Branch</th>
	<th rowspan=2>User</th>
	<th rowspan=2>Offer Period</th>
	<th rowspan=2>Last Update</th>
</tr>
<tr bgcolor=#ffee99>
	{foreach from=$branches item=branch}
	<th class=small>{$branch.code}</th>
	{/foreach}
</tr>
{section name=i loop=$list}
<tr class=hd>
	<td>&nbsp;</td>
	<td><a href=/mkt_status.php?a=view&id={$list[i].id}>{$list[i].id|string_format:"MKT%05d"}</a></td>
	<td colspan={count var=$branches}><a href=/mkt_status.php?a=view&id={$list[i].id}>{$list[i].title}</a></td>
	<td align=center>{$list[i].u}</td>
	<td nowrap align=center>{$list[i].offer_from|date_format:"%d/%m/%Y"} - {$list[i].offer_to|date_format:"%d/%m/%Y"}</td>
	<td nowrap align=center>{$list[i].last_update}</td>
</tr>
{foreach from=$list[i].complete key=dept item=complete}
<tr>
	<td>
	{if $complete}
		<a href="/mkt_status.php?a=open_by_dept&id={$list[i].id}&dept_id={$list[i].submitted.$dept.id}">
		{if $list[i].check.$dept.mda_approved}
			<img src=/ui/approved.png border=0></a>
	    {elseif $list[i].check.$dept.mda_status eq '2'}
			<img src=/ui/rejected.png border=0 title="Rejected">
		{elseif $list[i].check.$dept.mda_status}
			<img src=/ui/view.png border=0 title="Viewing Approval"></a>
		{else}
			<img src=/ui/ed.png border=0 title="Waiting For Approval"></a>
		{/if}
	{else}
		<img src=/ui/approved_grey.png border=0 title="Incomplete">
	{/if}
	</td>
	<td>{$dept}
	{if preg_match('/\d/',$list[i].app.approvals)}
	<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$list[i].app.approvals}</font></div>
	{/if}
	</td>
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $list[i].branches.$br}
	<td align=center>
	    {if $list[i].submitted.$dept.$br}
	    <img src=/ui/approved.png>
	    {elseif $list[i].submitted.$dept.$br eq '0' or !$list[i].submitted.$dept.$br}
	    <img src=/ui/approved_grey.png>
	    {else}
		&nbsp;
	    {/if}
	</td>
	{else}
	<td class=d45>
 	&nbsp;
	</td>
	{/if}
	{/foreach}
 	<td colspan=3>
<div class=small style="color:#060">Select a Publish Date to view/edit</div>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $list[i].publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">
{assign var=dt value=$list[i].publish_dates[$x]}
{if $BRANCH_CODE eq 'HQ'}
<a href="?a={if $list[i].mkt5_status eq '1'}view{else}open{/if}&id={$list[i].id}&dept_id={$list[i].submitted.$dept.id}&date_id={$list[i].publish_dates[$x]}">{/if}{$list[i].publish_dates[$x]}</a>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>

</tr>
{/foreach}
{/section}
</table>
{/if}
