{*
*}
{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sortable table mb-0 text-md-nowrap  table-hover"  id="position_tbl"  width="100%">
				<thead class="bg-gray-100">
					<tr>
						<th bgcolor="{#TB_CORNER#}" width="3%">&nbsp;</th>
						<th bgcolor="{#TB_COLHEADER#}" width="10%">Code</th>
						<th bgcolor="{#TB_COLHEADER#}" width="57%">Description</th>
						<th bgcolor="{#TB_COLHEADER#}" width="10%">Added By</th>
						<th bgcolor="{#TB_COLHEADER#}" width="10%">Time Added</th>
						<th bgcolor="{#TB_COLHEADER#}" width="10%">Last Updated</th>
					</tr>
				</thead>
				{foreach from=$sa_position_list item=p}
					{assign var=position_id value=$p.id}
					<tbody class="fs-08">
						<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
							<td bgcolor="{#TB_ROWHEADER#}" nowrap>
								<a onclick="SALES_AGENT_POSITION_SETUP_MODULE.position_edit('{$position_id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
								<a onclick="SALES_AGENT_POSITION_SETUP_MODULE.position_activation('{$position_id}', {if $p.active}0);"><img src="ui/deact.png" title="Deactivate" border="0">{else}1);"><img src="ui/act.png" title="Activate" border="0">{/if}</a>
							</td>
							<td>{$p.code}{if !$p.active}<br><span class="small">(inactive)</span>{/if}</td>
							<td>{$p.description}</td>
							<td align="center">{$p.added_by}</td>
							<td align="center">{$p.added}</td>
							<td align="center">{$p.last_update}</td>
						</tr>
					</tbody>
				{foreachelse}
					<tr><td colspan="10" align="center">- No Data -</td></tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>
</div>

<script>
	ts_makeSortable($('position_tbl'));
</script>