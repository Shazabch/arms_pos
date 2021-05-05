{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}

{config_load file=site.conf}

<div style="float:right;">
<h5>Total {$total_rows|number_format} record{if $total_rows>1}s{/if}</h5>
</div>
{if $total_page>1}
	<div style="padding:2px;float:left;">
	Page
	<select onChange="page_change(this);">
		{section loop=$total_page name=s}
			<option value="{$smarty.section.s.index}" {if $smarty.request.s eq $smarty.section.s.index}selected {/if}>{$smarty.section.s.iteration}</option>
		{/section}
	</select>
	</div>
{/if}
<span id="span_refreshing"></span>

<table class="sortable" id="sku_monitor_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
<tr>
	<th bgcolor="{#TB_CORNER#}"" width="40">&nbsp;</th>
	<th bgcolor="{#TB_COLHEADER#}">Group Name</th>
	<th bgcolor="{#TB_COLHEADER#}">Created By</th>
	<th bgcolor="{#TB_COLHEADER#}">Department</th>
	<th bgcolor="{#TB_COLHEADER#}">SKU in Group</th>
	<th bgcolor="{#TB_COLHEADER#}">Start Monitoring Date</th>
	<th bgcolor="{#TB_COLHEADER#}">Added</th>
	<th bgcolor="{#TB_COLHEADER#}">Last Update</th>
</tr>
{foreach from=$table item=r}
	<tr>
	    <td nowrap>
			<a href="javascript:void(open('{$r.id}'));"><img src="ui/ed.png" border="0" title="Edit" /></a>
			<a href="javascript:void(delete_group('{$r.id}'));"><img src="ui/icons/delete.png" border="0" title="Delete" /></a>
			<a href="javascript:void(view_batch('{$r.id}'));"><img src="ui/icons/application_view_list.png" border="0" title="View batch" /></a>
			<a href="javascript:void(regen_batch('{$r.id}'));"><img src="ui/icons/arrow_refresh.png" border="0" title="Regenerate batch group" id="img_regen_batch,{$r.id}" /></a>
		</td>
	    <td>{$r.group_name} {if $r.changed}<span class="not_update">*</span>{/if}</td>
	    <td>{$r.u}</td>
	    <td>{$r.dept_name|default:'-'}</td>
	    <td>{$r.sku_count|number_format}</td>
	    <td align="center">{$r.start_monitoring_date|default:'-'}</td>
	    <td align="center">{$r.added}</td>
	    <td align="right">{$r.last_update}</td>
	</tr>
{/foreach}
</table>

<script>
	ts_makeSortable($('sku_monitor_tbl'));
</script>