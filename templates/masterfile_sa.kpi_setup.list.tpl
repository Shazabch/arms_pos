{*
*}
{config_load file=site.conf}
<div id="udiv" class="stdframe">
{assign var=colspan value=10}
<div class="table-responsive">
	<table  class="sortable table mb-0 text-md-nowrap  table-hover"  id="kpi_tbl"  width="100%">
		<thead class="bg-gray-100">
			<tr>
				<th bgcolor="{#TB_CORNER#}" width="3%">&nbsp;</th>
				{if $show_position}
					<th bgcolor="{#TB_COLHEADER#}" width="15%">Position</th>
					{assign var=colspan value=$colspan+1}
				{/if}
				<th bgcolor="{#TB_COLHEADER#}" width="15%">Description</th>
				<th bgcolor="{#TB_COLHEADER#}" width="40%">Additional Description</th>
				<th bgcolor="{#TB_COLHEADER#}" width="7%">Scores</th>
				<th bgcolor="{#TB_COLHEADER#}" width="7%">Added By</th>
				<th bgcolor="{#TB_COLHEADER#}" width="7%">Time Added</th>
				<th bgcolor="{#TB_COLHEADER#}" width="7%">Last Updated</th>
			</tr>
		</thead>
		{foreach from=$sa_kpi_list item=kpi}
			{assign var=sa_kpi_id value=$kpi.id}
			<tbody class="fs-08">
				<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
					<td bgcolor="{#TB_ROWHEADER#}" nowrap>
						<a onclick="SALES_AGENT_KPI_MODULE.kpi_edit('{$sa_kpi_id}', '{$kpi.position_id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
						<a onclick="SALES_AGENT_KPI_MODULE.kpi_activation('{$sa_kpi_id}', '{$kpi.position_id}', {if $kpi.active}0);"><img src="ui/deact.png" title="Deactivate" border="0">{else}1);"><img src="ui/act.png" title="Activate" border="0">{/if}</a>
					</td>
					{if $show_position}
						<td>{$kpi.position_desc}</td>
					{/if}
					<td>{$kpi.description}{if !$kpi.active}<br><span class="small">(inactive)</span>{/if}</td>
					<td>{$kpi.additional_description}</td>
					<td class="r">{$kpi.scores|number_format:2}</td>
					<td align="center">{$kpi.added_by}</td>
					<td align="center">{$kpi.added}</td>
					<td align="center">{$kpi.last_update}</td>
				</tr>
			</tbody>
		{foreachelse}
			<tr><td colspan="{$colspan}" align="center">- No Data -</td></tr>
		{/foreach}
	</table>
</div>
</div>

<script>
	ts_makeSortable($('kpi_tbl'));
</script>