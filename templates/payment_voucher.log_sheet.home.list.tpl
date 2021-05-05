{$pagination}
{if !$list}
<p align=center>- No record -</p>
{else}
<table width=100% cellpadding=4 cellspacing=1 border=0 id=tbl_list>
<tr bgcolor=#ffee99 height=24>
	<th>&nbsp;</th>
	<th>Log Sheet No.</th>
	<th>Branch</th>
	<th width=10%>Total Voucher</th>
</tr>

{section name=i loop=$list}
<tr>
	<td align=left width=100>
	{if $list[i].log_sheet_status eq '3'}
	<a href="?a=view&ls_no={$list[i].log_sheet_no}&p={$list[i].log_sheet_page}&branch_id={$list[i].branch_id}">
	<img src=/ui/view.png border=0 title="View Log Sheet">
	</a>
	<img src=/ui/print.png border=0 title="Print Log Sheet Page" onclick="do_print('{$list[i].log_sheet_no}','{$list[i].log_sheet_page}','{$list[i].voucher_branch_id}');">	
	{else}
	<a href="?a=open&ls_no={$list[i].log_sheet_no}&branch_id={$list[i].branch_id}">
	<img src=/ui/ed.png border=0 title="Edit Log Sheet">
	</a>
	{/if}
	</td>	
	<td align=center>{$list[i].log_sheet_no}{if $list[i].log_sheet_page}/{$list[i].log_sheet_page}{/if}</td>
	<td align=center>{$list[i].branch}</td>
	<td align=center>{$list[i].total_voucher}</td>
</tr>
{/section}
</table>
<script>
ts_makeSortable($('tbl_list'));
</script>
{/if}
