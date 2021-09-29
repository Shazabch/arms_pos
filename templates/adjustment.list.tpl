{*
1/12/2010 6:01:31 PM Andy
- Add nowrap for adjustment icon

8/16/2011 11:17:21 AM Justin
- Fixed the bugs while listing Adjustment.

8/22/2011 2:51:32 PM Justin
- Fixed the bugs where always show rejected item while view from draft adjustment.

05/05/2016 17:25 Edwin
- Added new table column "Remark" at Adjustment.

2/3/2017 5:38 PM Andy
- Fixed HQ not allow to edit adjustment when no config adjustment_branch_selection and single_server_mode.

1/12/2018 3:11 PM Andy
- Enhanced to check work order when load adjustment.

10/28/2020 2:13 PM William
- Enhanced to add "export adjustment item" icon.
*}

{$pagination}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sortable report_table table mb-0 text-md-nowrap  table-hover" id="adj_tbl">
				<thead class="bg-gray-100 fs-08" style="height: 30px;">
					<tr>
						<th>&nbsp;</th>
						<th>Adj No.</th>
						<th>Branch</th>
						<th>Date</th>
						<th>User</th>
						<th>Department</th>
						<th>Adjustment Type</th>
						<th>Remark</th>
						<th>Last Update</th>
						</tr>
				</thead>
				{section name=i loop=$list}
			<tbody class="fs-08">
				<tr bgcolor={cycle values=",#eeeeee"}>
					<td nowrap>
					{if $list[i].approved==1 && $list[i].active==1}
					<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/approved.png border=0 title="View this Ajustment">
					</a>
					<a href="javascript:void(do_print({$list[i].id},{$list[i].branch_id}))">
					<img src="ui/print.png" title="Print this Adjustment" border=0>
					</a>
					<a href="javascript:void(export_adjustment_item({$list[i].id},{$list[i].branch_id}))">
					<img src="ui/icons/page_excel.png" title="Export Adjustment Item" border=0>
					</a>
					{elseif ($list[i].status==1 || $list[i].status==3) && $list[i].approved==0 && $list[i].active==1}
					<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/view.png border=0 title="View this Ajustment">
					</a>
					{elseif $list[i].status==2 && $list[i].approved==0 && $list[i].active==1}
					<a href="?a={if $list[i].user_id == $sessioninfo.id and ((BRANCH_CODE eq 'HQ' and $config.adjustment_branch_selection and $config.single_server_mode) or $sessioninfo.branch_id eq $list[i].branch_id)}open{else}view{/if}&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/rejected.png border=0 title="Open This Ajustment">
					</a>
					{elseif $list[i].active==0 || $list[i].status==3 || $list[i].status==4 || $list[i].status==5}
					<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
					<img src=/ui/cancel.png border=0 title="View this Ajustment">
					</a>
					{else}
						{if ((BRANCH_CODE eq 'HQ' and $config.adjustment_branch_selection and $config.single_server_mode) or $sessioninfo.branch_id eq $list[i].branch_id) and $list[i].module_type ne 'work_order'}
							<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
								<i class="fas fa-edit text-primary" title="Open this Ajustment"></i>
								
							</a>
						{else}
							<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
								<img src="/ui/view.png" border="0" title="View this Ajustment">
							</a>
						{/if}
					{/if}
					</td>
					<td>
					{$list[i].prefix}{$list[i].id|string_format:"%05d"}
					{if preg_match('/\d/',$list[i].approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$list[i].approvals aorder_id=$list[i].aorder_id}</font></div>
					{/if}
					</td>
					<td>{$list[i].branch}</td>
					<td>{$list[i].adjustment_date}</td>
					<td>{$list[i].u}</td>
					<td>{$list[i].department}</td>
					<td>{$list[i].adjustment_type}</td>
					<td>{$list[i].remark|nl2br}</td>
					<td>{$list[i].last_update}</td>
					</tr>
			</tbody>
				{sectionelse}
			<tbody class="fs-08">
				<tr>
					<td colspan=5>- no record -</td>
				</tr>
			</tbody>
				{/section}
				</table>
		</div>
		
	</div>
</div>
<script>
ts_makeSortable($('adj_tbl'));
</script>
