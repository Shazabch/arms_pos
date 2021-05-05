{*
3/2/2021 3:44 PM Andy
- Enhanced Work Order to can transfer by Weight to Pcs.
*}

{if !$woList}
	<p align="center"> &nbsp; * No Data Found *</p>
	
{else}
	{if $total_page >1}
		<div style="padding:2px;float:left;">
		Page
		<select onChange="WORK_ORDER.page_change();" id="sel_page">
			{section loop=$total_page name=s}
				<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
			{/section}
		</select>
		</div>
	{/if}
	
	<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
			<th width="100">WO No</th>
			<th>Department</th>
			<th>Transfer Type</th>
			<th width="100">Adjustment Date</th>
			<th width="50">Qty Out</th>
			<th width="50">Qty In</th>			
			<th>Created By</th>
			<th>Last Update</th>
			<th>Adjustment Docs</th>
		</tr>
		
		{foreach from=$woList item=wo}
			<tr bgcolor="{cycle values='#ffffff,#eeeeee'}">
				{assign var=url_edit value="`$smarty.server.PHP_SELF`?a=open&branch_id=`$wo.branch_id`&id=`$wo.id`"}
				{assign var=url_view value="`$smarty.server.PHP_SELF`?a=view&branch_id=`$wo.branch_id`&id=`$wo.id`"}
				{assign var=can_edit value=0}
				
				{if $wo.active == 1 and !$wo.completed and $wo.branch_id eq $sessioninfo.branch_id}
					{if $wo.status eq 0 and $sessioninfo.privilege.ADJ_WORK_ORDER_OUT}
						{assign var=can_edit value=1}
					{/if}
					{if $wo.status eq 1 and $sessioninfo.privilege.ADJ_WORK_ORDER_IN}
						{assign var=can_edit value=1}
					{/if}
				{/if}
				
				<td align="center">
					{if $can_edit}
						<a href="{$url_edit}"><img src="ui/ed.png" title="Open / Edit" border="0" /></a>
					{else}
						<a href="{$url_view}"><img src="ui/view.png" title="View" border="0" /></a>
					{/if}
				</td>
				
				<td>{$wo.wo_no}</td>
				<td>{$wo.dept_desc}</td>
				<td align="center">{$transfer_type_list[$wo.transfer_type]}</td>
				<td align="center">{$wo.adj_date}</td>
				<td align="right">{$wo.out_total_qty|qty_nf}</td>
				<td align="right">{$wo.in_total_actual_qty|qty_nf}</td>
				<td align="center">{$wo.created_u|default:'-'}</td>
				<td align="center">{$wo.last_update|default:'-'}</td>
				<td align="center">
					{if $wo.adj_id}
						<a href="adjustment.php?a=view&branch_id={$wo.branch_id}&id={$wo.adj_id}" target="_blank">ID#{$wo.adj_id}</a>
					{else}
						&nbsp;
					{/if}
				</td>
				
			</tr>
		{/foreach}
	</table>
{/if}