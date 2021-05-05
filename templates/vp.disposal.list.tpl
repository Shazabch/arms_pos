{*

2/4/2013 10:22 AM Fithri
- Add adjustment type column

*}

{$pagination}
<table class=sortable id=adj_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
<th>&nbsp;</th>
<th>No.</th>
<th>Date</th>
<th>Department</th>
<th>Type</th>
<th>Last Update</th>
</tr>
{section name=i loop=$list}
<tr bgcolor={cycle values=",#eeeeee"}>
<td nowrap>
{if $list[i].approved==1 && $list[i].active==1}
<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
<img src=/ui/approved.png border=0 title="View this Ajustment">
</a>
{elseif ($list[i].status==1 || $list[i].status==3)}
<a href="?a=view&id={$list[i].id}&branch_id={$list[i].branch_id}">
<img src=/ui/view.png border=0 title="View this Ajustment">
</a>
<a href="javascript:DSP.do_print({$list[i].id},{$list[i].branch_id})">
<img src=/ui/print.png border=0 title="Print this Ajustment">
</a>
{else}
<a href="?a=open&id={$list[i].id}&branch_id={$list[i].branch_id}">
<img src=/ui/ed.png border=0 title="Open this Ajustment">
</a>
{/if}
</td>
<td>
{$list[i].prefix}{$list[i].id|string_format:"%05d"}
{if preg_match('/\d/',$list[i].approvals)}
<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$list[i].approvals aorder_id=$list[i].aorder_id}</font></div>
{/if}
</td>
<td>{$list[i].adjustment_date}</td>
<td>{$list[i].department}</td>
<td>{$list[i].adjustment_type}</td>
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
