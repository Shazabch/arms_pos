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
<table class=sortable id=adj_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
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
{section name=i loop=$list}
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
			<img src=/ui/ed.png border=0 title="Open this Ajustment">
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
{sectionelse}
<tr>
	<td colspan=5>- no record -</td>
</tr>
{/section}
</table>

<script>
ts_makeSortable($('adj_tbl'));
</script>
