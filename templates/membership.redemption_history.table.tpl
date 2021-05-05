{*
3/2/2010 10:00:19 AM Andy
- Change to only allow user with privilege to cancel redemption

8/18/2010 10:40:12 AM Justin
- Added the approval information on bottom of NRIC field.
- Modified View hyperlink to include new variables for verification purpose.

8/27/2010 9:10:25 AM Justin
- Added Cash Needed column.
- Changed Cancel By column to display when tab 4 and 5.
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
	<th width="100" nowrap>&nbsp;</th>
	<th width="100">Redemption No</th>
	<th>Card No</th>
	<th>NRIC</th>
	<th>Qty Redempt</th>
	<th>Points Used</th>
	<th>Cash Needed</th>
	<th>Created By</th>
	<th>Date</th>
	<th>Print Count</th>
	{if $smarty.request.t eq 4 or $smarty.request.t eq 5}
	    <th width="100">Cancel By</th>
	{/if}
	<th>last_update</th>
</tr>
<tbody id="tbody_item_list">
{foreach from=$items item=r}
    {cycle values=",#eeeeee" assign=tr_color}
	<tr bgcolor="{$tr_color}" class="data_row">
	    <td align="center">
			<a href="?a=view&t={$smarty.request.t}&id={$r.id}&branch_id={$r.branch_id}{if $smarty.request.do_verify}&do_verify={$smarty.request.do_verify}{/if}{if $search_str}&search_str={$search_str}{/if}"><img src="/ui/icons/page.png" border="0" title="View" /></a>
			{if $r.status eq 0}
				<a href="javascript:void(print_slip('{$r.id}','{$r.branch_id}'));"><img src="/ui/icons/printer.png" border="0" title="Print Slip" /></a>
				{if $sessioninfo.privilege.MEMBERSHIP_CANCEL_RE && $r.verified eq 1}
				<a href="javascript:void(cancel_redemption('{$r.id}','{$r.branch_id}'));">
					<img src="/ui/icons/delete.png" border="0" title="Cancel Redemption" />
				</a>
				{/if}
			{/if}
		</td>
		<td>
			{$r.redemption_no}
			<input type="hidden" value="{$r.redemption_no}" id="rdmpt_code_{$r.branch_id}_{$r.id}">
		</td>
		<td>{$r.card_no}</td>
		<td>
			{$r.nric}
			{if preg_match('/\d/',$r.approvals) && $smarty.request.t eq 1 || preg_match('/\d/',$r.approvals) && $smarty.request.t eq 2}
				<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$r.approvals aorder_id=$r.approval_order_id}</font></div>
			{/if}
		</td>
		<td width="50" align="right">{$r.total_qty|number_format}</td>
		<td width="100" align="right">{$r.total_pt_need|number_format}</td>
		<td width="100" align="right">{$r.total_cash_need|number_format:2}</td>
		<td width="100" align="center">{$r.u}</td>
		<td width="100" align="center">{$r.date}</td>
		<td width="50" align="right">{$r.print_count|number_format}</td>
		{if $smarty.request.t eq 4 or $smarty.request.t eq 5}
		    <td align="center">{$r.cancel_by_u}</td>
		{/if}
		<td width="150" align="center">{$r.last_update}</td>
	</tr>
{foreachelse}
	<tr>
	    <td colspan="10">No Data</td>
	</tr>
{/foreach}
</tbody>
</table>
