{if !$repacking_list}
	<p>* No Data *</p>
{else}
	{if $total_page >1}
		<div style="padding:2px;float:left;">
		Page
		<select onChange="REPACKING_LIST.page_change(this);">
			{section loop=$total_page name=s}
				<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.page_num eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
			{/section}
		</select>
		</div>
		<br style="clear:both" />
	{/if}
	
	<table width="100%" class="report_table">
		<tr class="header">
			<th>&nbsp;</th>
			<th>ID</th>
			<th>Date</th>
			<th>Department</th>
			<th>Remark</th>
			<th>Added</th>
			<th>Last Update</th>
		</tr>
		
		{foreach from=$repacking_list item=r}
			<tr>
				<td>
					{if $r.active and $r.status eq 0 and $r.approved eq 0}
						<a href="?a=open&branch_id={$r.branch_id}&id={$r.id}"><img src="ui/ed.png" title="Edit" /></a>
					{else}
						<a href="?a=view&branch_id={$r.branch_id}&id={$r.id}"><img src="ui/view.png" title="View" /></a>
						<a href="?a=print_repacking&branch_id={$r.branch_id}&id={$r.id}" target="_blank">
							<img src="/ui/print.png" border="0" title="Print" />
						</a>
					{/if}
				</td>
				<td>#{$r.id}</td>
				<td>{$r.repacking_date}</td>
				<td>{$r.dept_desc|default:'-'}</td>
				<td>{$r.remark|nl2br|default:'-'}</td>
				<td>{$r.added|default:'-'}</td>
				<td>{$r.last_update|default:'-'}</td>
			</tr>
		{/foreach}
	</table>
{/if}
