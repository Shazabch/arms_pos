{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}

{config_load file=site.conf}

<div style="float:right;">
<h5>Total {count var=$table} record{if count($table)>1}s{/if}</h5>
</div>

<span id="span_refreshing"></span>
<table class="sortable" id="bank_ints_tbl" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<th bgcolor="{#TB_CORNER#}"" width="40">&nbsp;</th>
		<th bgcolor="{#TB_COLHEADER#}">Start from</th>
		<th bgcolor="{#TB_COLHEADER#}">Interest Rate</th>
		<th bgcolor="{#TB_COLHEADER#}">Added</th>
		<th bgcolor="{#TB_COLHEADER#}">Last Update</th>
	</tr>
	{foreach from=$table item=r}
	    <tr>
		    <td nowrap>
				<a href="javascript:void(open('{$r.id}'));"><img src="ui/ed.png" border="0" title="Edit" /></a>
				<a href="javascript:void(delete_interest_rate('{$r.id}'));"><img src="ui/icons/delete.png" border="0" title="Delete" /></a>
			</td>
			<td>{$months[$r.month]} {$r.year}</td>
			<td class="r">{$r.interest_rate|num_format:3}%</td>
			<td align="center">{$r.added}</td>
	    	<td align="center">{$r.last_update}</td>
		</tr>
	{/foreach}
</table>

<script>
	ts_makeSortable($('bank_ints_tbl'));
</script>
