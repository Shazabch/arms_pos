{*
4/19/2021 3:49 PM edward au
- change Last Synced to Last Setup
*}
{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="table mb-0 text-md-nowrap  table-hover" width="100%">
				<thead class="bg-gray-100">
					<tr>
						<th rowspan="2" bgcolor="{#TB_COLHEADER#}">Counter Name</th>
						<th rowspan="2" bgcolor="{#TB_COLHEADER#}">POS Image Server</th>
						<th rowspan="2" colspan="2" bgcolor="{#TB_COLHEADER#}">HQ Server</th>
						<th colspan="4" bgcolor="{#TB_COLHEADER#}">Sync Server</th>
						<th rowspan="2" bgcolor="{#TB_COLHEADER#}">Last Setup</th>
					</tr>
					<tr bgcolor="{#TB_COLHEADER#}">
						<th colspan="2">Masterfile</th>
						<th colspan="2">Sales</th>
					</tr>
				</thead>
				{foreach from=$cc_list item=cc}
					<tbody class="fs-08">
						<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
							<td>{$cc.network_name}</td>
							<td>{$cc.pos_image_server}</td>
							<td width="5%" nowrap>
								{* Main Server - Header *}
								{foreach from=$ss_header_list name=sshl key=rn item=desc}
									<b>{$desc}:</b>
									{if !$smarty.foreach.sshl.last}<br />{/if}
								{/foreach}
							</td>
							<td>
								{* Main Server - Data*}
								{foreach from=$ss_header_list name=sshl key=rn item=desc}
									{$cc.hq_server.$rn}
									{if !$smarty.foreach.sshl.last}<br />{/if}
								{/foreach}
							</td>
							{if $cc.masterfile_sync_server}
								<td width="5%" nowrap>
									{* Sync Server - Masterfile Header *}
									{foreach from=$ss_header_list name=sshl key=rn item=desc}
										<b>{$desc}:</b>
										{if !$smarty.foreach.sshl.last}<br />{/if}
									{/foreach}
								</td>
								<td>
									{* Sync Server - Masterfile Data*}
									{foreach from=$ss_header_list name=sshl key=rn item=desc}
										{$cc.sync_server.$rn}
										{if !$smarty.foreach.sshl.last}<br />{/if}
									{/foreach}
								</td>
							{else}
								<td colspan="2" style="font-weight:bold; text-align:center;" nowrap>&nbsp;</td>
							{/if}
							{if $cc.sales_sync_server}
								<td width="5%" nowrap>
									{* Sync Server - Sales Header *}
									{foreach from=$ss_header_list name=sshl key=rn item=desc}
										<b>{$desc}:</b>
										{if !$smarty.foreach.sshl.last}<br />{/if}
									{/foreach}
								</td>
								<td>
									{* Sync Server - Sales Data*}
									{foreach from=$ss_header_list name=sshl key=rn item=desc}
										{$cc.sync_server_up_sales.$rn}
										{if !$smarty.foreach.sshl.last}<br />{/if}
									{/foreach}
								</td>
							{else}
								<td colspan="2" style="font-weight:bold; text-align:center;" nowrap>&nbsp;</td>
							{/if}
							<td align="center">{$cc.last_update}</td>
						</tr>
					</tbody>
				{foreachelse}
					<tr><td colspan="9" align="center">- No Data -</td></tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>
</div>