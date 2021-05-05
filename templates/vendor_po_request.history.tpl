{*
3/14/2013 3:05 PM Justin
- Enhanced to have new feature of deactivate access code.
- Enhanced to show valid period.

6/22/2017 14:30 Qiu Ying
- Enhanced to select multiple department in vendor PO access
*}

<br>
{if $row}
<h2>Login Tickets for {$smarty.request.vendor}</h2>
<table class=tb width=100% cellpadding=4 cellspacing=0 border=0 id="tbl_history">
	<tr bgcolor="#ffee99">
		<th>&nbsp;</th>
		<th>Access Code</th>
		<th>Owner</th>
		<th>Create By</th>
		<th>Department</th>
		{if $BRANCH_CODE eq 'HQ'}
		<th>Branch</th>
		{/if}
		<th>No. of PO</th>
		<th>Active</th>
		<th>IP</th>
		<th>Last Accessed</th>
		<th>Created</th>
		<th>Valid Until</th>
	</tr>
	
	{foreach item=row_data from=$row}
	<tr>
		<td align="center">
			{if $row_data.active}
				<img src="ui/cancel.png" style="vertical-align:top;" class="clickable" title="Deactivate {$row_data.ac}" onclick="deactivate_ac('{$row_data.ac}', '{$row_data.branch_id}', this);" align="absmiddle" alt="{$item_id}">
			{else}
				&nbsp;
			{/if}
		</td>
		<td align="center">{$row_data.ac}</td>
		<td align="center">{$row_data.u}</td>
		<td align="center">{$row_data.u_create}</td>
		<td align="left" width="40%">
			{if $row_data.d_id}
				{assign var=upper_str value=""}
				{assign var=lower_str value=""}
				{foreach name=loop_dept from=$row_data.d_id item=d_name}
					{assign var=dept_name value=$d_name}
					{if $smarty.foreach.loop_dept.iteration < 5}
						{if $smarty.foreach.loop_dept.iteration neq 4 && $smarty.foreach.loop_dept.iteration neq $row_data.d_id|@count}
							{assign var=dept_name value="$dept_name, "}
						{/if}
						{assign var=upper_str value=$upper_str$dept_name}
					{else}
						{if $smarty.foreach.loop_dept.iteration neq $row_data.d_id|@count}
							{assign var=dept_name value="$dept_name, "}
						{/if}
						{assign var=lower_str value=$lower_str$dept_name}
					{/if}
				{/foreach}
				<span>{$upper_str}</span>{if $lower_str neq ""}<span id="div_view_more_{$row_data.ac}" style="display:none;">, {$lower_str}</span>
					&nbsp;[<a id="view_type_{$row_data.ac}" href="javascript:void(toggle_view_more('{$row_data.ac}'))">show more</a>]
				{/if}
			{else}
				{$row_data.dept}
			{/if}
		</td>
		{if $BRANCH_CODE eq 'HQ'}
		<td align="center">{$row_data.branch_code}</td>
		{/if}
		<td align="right">{$row_data.po_count|ifzero:"&nbsp;"}</td>
		<td align="center"><font color="{if $row_data.active}green{else}red{/if}">{if $row_data.active}Yes{else}<font color="red">No{/if}</font></td>
		<td align="center">{$row_data.access_ip|default:"&nbsp;"}</td>
		<td align="center">{if $row_data.added == $row_data.last_update}-{else}{$row_data.last_update}{/if}</td>
		<td align="center">{$row_data.added}</td>
		<td align="center">{$row_data.valid_period}</td>
	</tr>
	{/foreach}
</table>
{else}
- no tickets -
{/if}
