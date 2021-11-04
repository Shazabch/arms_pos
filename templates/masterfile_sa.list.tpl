{*
8/18/2017 5:01 PM Andy
- Enhanced to able to filter by code, name and status.

10/22/2019 3:05 PM Andy
- Added Sales Agent Photo.

10/22/2019 3:05 PM Andy
- Enhanced to show Position.
*}
{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sortable"  id="sa_tbl"  width="100%">
				<div class="thead bg-gray-100">
					<tr>
						<th bgcolor="{#TB_CORNER#}" width="4%">&nbsp;</th>
						<th bgcolor="{#TB_COLHEADER#}" width="5%">Code</th>
						<th bgcolor="{#TB_COLHEADER#}" width="15%">Name</th>
						<th bgcolor="{#TB_COLHEADER#}" width="5%">Company Code</th>
						<th bgcolor="{#TB_COLHEADER#}" width="17%">Company Name</th>
						<th bgcolor="{#TB_COLHEADER#}" width="7%">Email</th>
						<th bgcolor="{#TB_COLHEADER#}" width="8%">Phone No</th>
						<th bgcolor="{#TB_COLHEADER#}" width="5%">Ticket No</th>
						<th bgcolor="{#TB_COLHEADER#}" width="9%">Ticket Valid Until</th>
						<th bgcolor="{#TB_COLHEADER#}" width="9%">Position</th>
						<th bgcolor="{#TB_COLHEADER#}" width="25%">Commission</th>
					</tr>
				</div>
				{foreach from=$sa_list item=sa}
					{assign var=sa_id value=$sa.id}
					<tbody class="fs-08">
						<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
							<td bgcolor="{#TB_ROWHEADER#}" nowrap>
								<a onclick="SALES_AGENT_MODULE.sa_edit('{$sa_id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
								<a onclick="SALES_AGENT_MODULE.sa_activation('{$sa_id}', {if $sa.active}0);"><img src="ui/deact.png" title="Deactivate" border="0">{else}1);"><img src="ui/act.png" title="Activate" border="0">{/if}</a>
								<a onclick="SALES_AGENT_MODULE.sa_ticket_activation('{$sa_id}', {if $sa.ticket_no && !$sa.ticket_expired}0, 0);"><img src="ui/icons/key_delete.png" title="Deactivate" border="0">{else}1, 0);"><img src="ui/icons/key_add.png" title="Generate Ticket" border="0">{/if}</a>
								<a onclick="SA_COMMISSION_MODULE.commission_table_appear('{$sa_id}');"><img src="ui/icons/calculator_edit.png" title="View & Edit Commission" border="0"></a>
								<a onclick="SALES_AGENT_MODULE.sales_target_table_appear('{$sa_id}');"><img src="ui/icons/page_edit.png" title="View & Edit Sales Target" border="0"></a>
								<input type="hidden" id="list_sa_code_{$sa_id}" value="{$sa.code}">
								
								<a href="javascript:void(SALES_AGENT_PHOTO_DIALOG.open('{$sa_id}'));">
									<img src="ui/icons/image.png" title="Sales Agent Photo" border="0" />
								</a>
							</td>
							<td><b>{$sa.code}</b>{if !$sa.active}<br><span class="small">(inactive)</span>{/if}</td>
							<td>{$sa.name}</td>
							<td>{$sa.company_code}</td>
							<td>{$sa.company_name}</td>
							<td>{$sa.email}</td>
							<td>{$sa.phone_1}</td>
							<td align="center">{if !$sa.ticket_expired}{$sa.ticket_no}{else}&nbsp;{/if}</td>
							<td align="center">
								{if $sa.ticket_valid_before > 0 && !$sa.ticket_expired}
									{$sa.ticket_valid_before}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>
								{if $sa.position_id}
									{$sa.position_code} - {$sa.position_desc}
								{else}
									-
								{/if}
							</td>
							<td>
								{foreach from=$commission_list.$sa_id item=sac name=sac_list}
									<a onclick="SA_COMMISSION_MODULE.commission_items_table_appear({$sac.sac_id}, {$sac.branch_id});" title="Click to view Commission Items">{$sac.branch_code} - {$sac.title}</a>
									{if !$smarty.foreach.sac_list.last}<br />{/if}
								{/foreach}
							</td>
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
	ts_makeSortable($('sa_tbl'));
</script>