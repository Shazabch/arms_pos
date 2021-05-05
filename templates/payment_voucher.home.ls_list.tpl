{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0 id=tbl_list>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>Log Sheet No</th>
	<th>Branch</th>
	<th>Status (Completed/Total)</th>
</tr>

{section name=i loop=$list}
<tr>
	<td align=left>
	<a href="?a=open_ls&ls_no={$list[i].log_sheet_no}&branch_id={$list[i].branch_id}">
	<img src=/ui/view.png border=0 title="View Log Sheet">
	</a>

	<img src=/ui/print.png border=0 title="Print Log Sheet" onclick="do_print_ls('{$list[i].log_sheet_no}')">

	</td>
	<td align=center>{$list[i].log_sheet_no}</td>
	
	<td align=center>{$list[i].branch}</td>
	
	<td align=center>
	{if $list[i].total_incomplete=='0'}
	<img src=/ui/approved.png border=0 title="View Voucher">
	{else}
	<img src=/ui/approved_grey.png border=0 title="View Voucher">
	{/if}
	( {$list[i].total_complete} / {$list[i].total_all} )
	</td>		
</tr>
{/section}
</table>
<script>
ts_makeSortable($('tbl_list'));
</script>
{/if}
