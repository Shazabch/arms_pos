{include file="header.tpl"}

<h1>{$PAGE_TITLE} History</h1>

{if $smarty.request.msg}
	<p>
		{$smarty.request.msg|htmlentities}
	</p>
	
{/if}

<table width="100%" class="report_table">
	<tr class="header">
		<th>&nbsp;</th>
		<th>ID</th>
		<th>Batch List</th>
		<th>Total Member Points Used</th>
		<th>Timestamp</th>
	</tr>
	
	{foreach from=$his_list item=r}
		<tr>
			<td width="100">
				<a href="?a=open_his&branch_id={$r.branch_id}&his_id={$r.id}">
					<img src="/ui/view.png" align="absmiddle" border="0" title="View Settings" />
				</a>
				<a href="masterfile_voucher.auto_redemption.generate.php?a=cancel_his_from_list&branch_id={$r.branch_id}&his_id={$r.id}" onclick="return confirm('Are you sure? This will revert back the points to member');">
					<img src="/ui/rejected.png" align="absmiddle" border="0" title="Cancel and revert points back to member" />
				</a>
			</td>
			<td align="center">{$r.id}</td>
			<td>
				{foreach from=$r.batch_list item=batch_no name=fbatch}
					{if !$smarty.foreach.fbatch.first}, {/if}
					<a href="masterfile_voucher.php?batch_no={$batch_no}" target="_blank">
						{$batch_no}
					</a>					
				{/foreach}
			</td>
			<td class="r">
				{$r.total_points_used|number_format}
			</td>
			<td align="center">
				{$r.added}
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">No Data</td>
		</tr>
	{/foreach}
</table>
{include file="footer.tpl"}