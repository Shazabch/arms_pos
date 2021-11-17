{*
12/4/2020 1:42 PM Shane
- Added Created At Branch
*}

{$pagination}
<div class="card mx-3">
	<div class="card-body">
		<table class="sortable table mb-0 text-md-nowrap  table-hover" id=announcement_tbl width=100% >
			<thead class="bg-gray-100" style="height: 30px;">
				<tr >
					<th>&nbsp;</th>
					<th>Announcement ID</th>
					<th>Description</th>
					<th>From</th>
					<th>To</th>
					<th>Branch</th>
					<th>Last Update</th>
					<th>Created By</th>
					<th>Created At Branch</th>
				</tr>
			</thead>
			
			{section name=i loop=$announcement_list}
				{assign var=phpfile value="front_end.announcement.php"}
			<tbody class="fs-08">
				<tr bgcolor={cycle values=",#eeeeee"}>
					<td nowrap>
						{if $announcement_list[i].status==1 && $announcement_list[i].branch_id==$sessioninfo.branch_id}
							<a href="{$phpfile}?a=open&id={$announcement_list[i].id}&branch_id={$announcement_list[i].branch_id}"><img src="ui/ed.png" title="Open this promotion" border=0></a>
						{elseif $announcement_list[i].status==2 || $announcement_list[i].status==4}
							<a href="{$phpfile}?a=view&id={$announcement_list[i].id}&branch_id={$announcement_list[i].branch_id}" target="_blank"><img src="ui/cancel.png" title="Open this promotion" border=0></a>
						{else}
							<a href="{$phpfile}?a=view&id={$announcement_list[i].id}&branch_id={$announcement_list[i].branch_id}" target="_blank"><img src="ui/approved.png" title="Open this promotion" border=0></a>
						{/if}
						
					</td>
					<td align=center>{$announcement_list[i].id}</td>
					<td>{$announcement_list[i].title}</td>
					<td>{$announcement_list[i].date_from} {$announcement_list[i].time_from}</td>
					<td>{$announcement_list[i].date_to} {$announcement_list[i].time_to}</td>
					<td>{$announcement_list[i].announcement_branch_id}</td>
					<td>{$announcement_list[i].last_update}</td>
					<td>{$announcement_list[i].u}</td>
					<td>{$announcement_list[i].created_at_branch}</td>
				</tr>
			</tbody>
			{sectionelse}
			<tr>
				<td colspan=6>- no record -</td>
			</tr>
			{/section}
			</table>
	</div>
</div>
<script>
ts_makeSortable($('announcement_tbl'));
</script>
