{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}

{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="table-responsive">
	<table class="sortable table mb-0 text-md-nowrap  table-hover" id="branch_group_tbl" width="100%">
		<thead class="fs-09">
			<tr>
				<th bgcolor={#TB_CORNER#} width=40>&nbsp;</th>
				<th bgcolor={#TB_COLHEADER#}>Code</th>
				<th bgcolor={#TB_COLHEADER#}>Description</th>
				<th bgcolor={#TB_COLHEADER#}>Numbers of Branch</th>
				</tr>
		</thead>
		
		{foreach from=$table item=r}
		<tbody class="fs-08">
			<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
				<td bgcolor={#TB_ROWHEADER#} nowrap>
				<a href="javascript:open('{$r.id}');"><img src=ui/ed.png title="Edit" border=0></a>
				<a href="javascript:deleteGroup('{$r.id}');"><img src=ui/deact.png title="Delete" border=0></a>
				</td>
				<td nowrap>{$r.code}</td>
				<td nowrap>{$r.description}</td>
				<td>{$r.item_count|default:'0'}</td>
				</tr>
		</tbody>
		{/foreach}
		
		</table>
</div>
</div>

<script>
	ts_makeSortable($('branch_group_tbl'));
</script>