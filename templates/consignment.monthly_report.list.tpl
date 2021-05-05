{if !$sheet_list}
	<p> &nbsp; * No {$sheet_name|lower|capitalize} Found *</p>
{else}
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
	<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
	        <th>Branch</th>
	        <th>Year</th>
	        <th>Month</th>
	        <th>Added</th>
	        <th>Last Update</th>
		</tr>
		{foreach from=$sheet_list item=r}
		    <tr bgcolor="{cycle values=",#eeeeee"}">
		        <td>
					{if $r.status eq 0}
						<a href="{$smarty.server.PHP_SELF}?a=load_table&branch={$r.branch_id}&year={$r.year}&month={$r.month}"><img src="ui/ed.png" title="Edit" border="0" /></a>
					{else}
					    <a href="{$smarty.server.PHP_SELF}?a=load_table&branch={$r.branch_id}&year={$r.year}&month={$r.month}"><img src="ui/view.png" title="View" border="0" /></a>
					{/if}
				</td>
		        <td>{$r.branch_code} - {$r.branch_desc}</td>
		        <td class="r">{$r.year}</td>
		        <td class="r">{$r.month}</td>
		        <td align="center">{$r.added|ifzero:'-'}</td>
		        <td align="center">{$r.last_update|ifzero:'-'}</td>
		    </tr>
		{/foreach}
	</table>
{/if}
