{*

*}

{if $total_page >1}
	<div style="padding:2px;float:left;">
	Page
	<select onChange="page_change(this);">
		{section loop=$total_page name=s}
			<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
		{/section}
	</select>
	</div>
{/if}
	
<table class="sortable" id="adj_tbl" width="100%"" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
	<tr bgcolor="#ffee99">
		<th width="60">&nbsp;</th>
		<th>Adj No.</th>
		<th>Branch</th>
		<th>Date</th>
		<th>User</th>
		<th>Department</th>
		<th>Last Update</th>
	</tr>
<tbody id="tbody_adj">
{foreach from=$adj_list item=r}
	<tr>
	    <td nowrap>
            {if $r.approved}
				<a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
					<img src="/ui/approved.png" border="0" title="View" />
				</a>
				<a href="javascript:void(do_print('{$r.id}','{$r.branch_id}'))">
					<img src="ui/print.png" title="Print" border=0 />
				</a>
			{elseif $r.status==1}
				<a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
					<img src="/ui/view.png" border="0" title="View" />
				</a>
			{elseif $r.status==2}
			    {if $r.user_id==$sessioninfo.id and $r.branch_id eq $sessioninfo.branch_id}
			        <a href="?a=open&id={$r.id}&branch_id={$r.branch_id}">
						<img src="/ui/rejected.png" border="0" title="Open" />
					</a>
			    {else}
			        <a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
						<img src="/ui/rejected.png" border="0" title="view" />
					</a>
			    {/if}
			{elseif $r.status==3 or $r.status==4 or $r.status==5}
				<a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
					<img src="/ui/cancel.png" border="0" title="View" />
				</a>
			{else}
			    {if $r.branch_id eq $sessioninfo.branch_id}
					<a href="?a=open&id={$r.id}&branch_id={$r.branch_id}">
						<img src="/ui/ed.png" border="0" title="Open" />
					</a>
				{else}
				    <a href="?a=view&id={$r.id}&branch_id={$r.branch_id}">
						<img src="/ui/view.png" border="0" title="view" />
					</a>
				{/if}
			{/if}
		</td>
		<td>
			{$r.prefix}{$r.id|string_format:"%05d"}
			{if preg_match('/\d/',$r.approvals)}
				<div class="small">
					Approvals: <font color="#0000ff">{get_user_list list=$r.approvals aorder_id=$r.aorder_id}</font>
				</div>
			{/if}
		</td>
		<td>{$r.branch}</td>
		<td>{$r.adjustment_date}</td>
		<td>{$r.u}</td>
		<td>{$r.department|default:'-'}</td>
		<td>{$r.last_update}</td>
	</tr>
{foreachelse}
	<tr>
		<td colspan="7">- no record -</td>
	</tr>
{/foreach}
</tbody>
</table>

<script>
ts_makeSortable($('adj_tbl'));
</script>
