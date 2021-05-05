{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}

{config_load file=site.conf}


<h5>{count var=$table} record{if count($table)>1}s{/if} <span id="span_refreshing"></span></h5>

<table class="sortable" id="trnsprt_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
<tr>
<th bgcolor={#TB_CORNER#} width=40>&nbsp;</th>
<th bgcolor={#TB_COLHEADER#}>Code</th>
<th bgcolor={#TB_COLHEADER#} nowrap>Company Name</th>
<th bgcolor={#TB_COLHEADER#}>Description</th>

<th bgcolor={#TB_COLHEADER#} nowrap>Added</th>
<th bgcolor={#TB_COLHEADER#} nowrap>Last Update</th>

</tr>
{foreach from=$table item=r}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
<td bgcolor={#TB_ROWHEADER#} nowrap>
	<a href="javascript:void(open('{$r.id}'))"><img src=ui/ed.png title="Edit" border=0></a>
	<a href="javascript:void(act('{$r.id}','{if $r.active}0{else}1{/if}'))">
    {if $r.active}
		<img src=ui/deact.png title="Deactivate" border=0>
	{else}
		<img src=ui/act.png title="Activate" border=0>
	{/if}
	</a>
</td>
<td>
<b>{$r.code}</b>{if !$r.active}<br><span class=small>(inactive)</span>{/if}
</td>
<td>{$r.company_name}</td>
<td>{$r.description}</td>

<td>{$r.added}</td>
<td>{$r.last_update}</td>
</tr>
{/foreach}
</table>

<script>
	ts_makeSortable($('trnsprt_tbl'));
</script>