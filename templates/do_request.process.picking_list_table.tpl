{*
11/23/2009 1:05:06 PM Andy
- Add Reject Feature

10/13/2011 10:33:12 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
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



<table id="items_tbl" width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
<tr bgcolor=#ffee99>
	<th colspan="2">&nbsp;</th>
	<th>Process<br />By</th>
	<th>Items in list</th>
	<th>Current<br />Request<br />Qty</th>
	<th>Delivered<br />Qty</th>
	<th>DO<br />Qty</th>
	<th>Added</th>
	<th>Last Update</th>
</tr>
<tbody id="tbody_item_list">
	{foreach from=$picking_list item=r}
		<tr>
			<td width="70" nowrap>
				{if $sessioninfo.id eq $r.user_id}
					<a href="?a=open_picking_list&pid={$r.id}&branch_id={$r.branch_id}"><img src="/ui/ed.png" border="0" align="absmiddle" title="Edit" /></a>
					<img src="/ui/print.png" border="0" align="absmiddle" onClick="print_picking_list('{$r.id}');" title="Re-print" />
					<img src="/ui/icons/delete.png" border="0" align="absmiddle" class="clickable" title="Reject" onClick="reject_picking_list('{$r.id}');" />
				{else}
					<a href="?a=open_picking_list&pid={$r.id}&branch_id={$r.branch_id}"><img src="/ui/icons/page.png" border="0" align="absmiddle" title="View" /></a>
				{/if}
			</td>
			<td width="50">#{$r.id|string_format:'%05d'} &nbsp;</td>
			<td>{$r.u}</td>
			<td class="r">{$r.item_count|number_format}</td>
			<td class="r">{$r.request_qty|qty_nf}</td>
			<td class="r">{$r.total_do_qty|qty_nf}</td>
			<td class="r">{$r.do_qty|qty_nf}</td>
			<td>{$r.added}</td>
			<td>{$r.last_update}</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="9">No Item</td>
		</tr>
	{/foreach}
</tbody>
</table>
