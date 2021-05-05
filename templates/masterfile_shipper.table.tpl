{*
5/29/2018 11:50AM HockLee
- new form: shipper
*}

{config_load file=site.conf}


<h5>{count var=$table} record{if count($table)>1}s{/if} <span id="span_refreshing"></span></h5>

<table class="sortable" id="trnsprt_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
<tr>
<th bgcolor={#TB_CORNER#} width=40>&nbsp;</th>
<th bgcolor={#TB_COLHEADER#}>Code</th>
<th bgcolor={#TB_COLHEADER#}>Company Name</th>
<th bgcolor={#TB_COLHEADER#}>Type</th>
<th bgcolor={#TB_COLHEADER#}>Contact Person</th>
<th bgcolor={#TB_COLHEADER#}>Phone #1</th>
<th bgcolor={#TB_COLHEADER#}>Contact Email</th>
<th bgcolor={#TB_COLHEADER#}>Added</th>
<th bgcolor={#TB_COLHEADER#}>Last Update</th>

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
<td>{$r.type}</td>
<td>{$r.contact_person}</td>
<td>{$r.phone_1}</td>
<td>{$r.contact_email}</td>
<td>{$r.added}</td>
<td>{$r.last_update}</td>
</tr>
{/foreach}
</table>

<script>
	ts_makeSortable($('trnsprt_tbl'));
</script>