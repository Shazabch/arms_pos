{*
6/4/2013 10:41 AM Justin
- Bug fixed on pagination not working properly.

7/29/2013 4:55 PM Andy
- Fix Batch Price Change show wrong Approval sequence in Waiting for Approval list.

3/21/2015 2:33 PM Justin
- Enhanced to have cancel document feature.

05/26/2016 13:30 Edwin
- Change effective branches show format

2/21/2017 2:02 PM Justin
- Enhanced to allow user level "admin" can always edit any owner's documents.
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
<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
<tr bgcolor="#ffee99">
	<th>&nbsp;</th>
	<th>Doc No</th>
	<th>Effective<br />Branches</th>
	<th>Effective<br />Date</th>
	<th>Created by</th>
	<th>Last Updated</th>
</tr>

{foreach from=$fp_list key=fp_id item=fp name=future_price}
	<tr bgcolor="{cycle values=',#eeeeee'}">
		<td nowrap align="center">
			{if $fp.status eq '2'}<!-- Rejected -->
				<a href="masterfile_sku_items.future_price.php?a={if $fp.user_id eq $sessioninfo.id || $sessioninfo.level >= 9999}open{else}view{/if}&id={$fp.id}&branch_id={$fp.branch_id}"><img src="ui/rejected.png" title="Open this Future Change Price" border="0" /></a>
			{elseif $fp.status eq '4' || $fp.status eq '5'}
				<a href="masterfile_sku_items.future_price.php?a=view&id={$fp.id}&branch_id={$fp.branch_id}"><img src="ui/cancel.png" title="View this Future Change Price" border="0" /></a>
			{elseif $fp.status eq '1' || $sessioninfo.id ne $fp.user_id}
				<a href="masterfile_sku_items.future_price.php?a=view&id={$fp.id}&branch_id={$fp.branch_id}"><img src="ui/view.png" title="View this Future Change Price" border="0" /></a>
			{else}
				<a href="masterfile_sku_items.future_price.php?a=open&id={$fp.id}&branch_id={$fp.branch_id}"><img src="ui/ed.png" title="Open this Future Change Price" border="0" /></a>
			{/if}
			{if !$fp.expired && $fp.active eq 1 && ($fp.status eq 1 or !$fp.status)}
			<a href="javascript:void(print_report('{$fp.id}','{$fp.branch_id}'))"><img src="ui/print.png" title="Print Future Price" border=0></a>
			{/if}
			{if $fp.active & $fp.status eq 1 && $fp.approved && ($sessioninfo.id eq $fp.user_id || $sessioninfo.level >= 9999)}
				<a href="#" onclick="do_cancel('{$fp.id}', '{$fp.branch_id}');"><img src="ui/icons/delete.png" title="Cancel this Future Change Price" border="0" /></a>
			{/if}
		</td>
		<td>#{$fp.id|string_format:"%05d"}</td>
		<td>
			{foreach from=$fp.effective_branches key=bid item=r name=bl}
				{if $fp.date_by_branch}
					{$branches.$bid.code} ({$r.date} {$r.hour|string_format:"%02d"}:{$r.minute|string_format:"%02d"}) 
					{if !$smarty.foreach.bl.last}<br />{/if}
				{else}
					{$branches.$bid.code}
					{if !$smarty.foreach.bl.last}, {/if}
				{/if}
			{foreachelse}
				- None -
			{/foreach}
			{if preg_match('/\d/',$fp.approvals)}
				<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$fp.approvals aorder_id=$fp.approval_order_id}</font></div>
			{/if}
		</td>
		<td align="center">
			{if $fp.date_by_branch}
				-
			{else}
				{$fp.date} {$fp.hour|string_format:"%02d"}:{$fp.minute|string_format:"%02d"}:00
			{/if}
		</td>
		<td align="center">{$fp.username}</td>
		<td align="center">{$fp.last_update}</td>
	</tr>
{foreachelse}
	<tr>
		<td colspan="10" align="center">- no record -</td>
	</tr>
{/foreach}
</table>