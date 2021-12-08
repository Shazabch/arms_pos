{*
2/14/2019 9:42 AM Andy
- Change App Type from 'Any' to 'Others'.
*}
{config_load file=site.conf}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=" table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100" style="height: 30px;">
					<tr>
						<th  width="60">&nbsp;</th>
						<th>Device Code</th>
						<th>Device Name</th>
						<th>Type</th>
						{if BRANCH_CODE eq 'HQ'}
							<th>Allowed Branches</th>
						{/if}
						<th>Active Status</th>
						<th>Pair Status</th>
					</tr>
				</thead>
				
				{foreach from=$device_list key=device_guid item=r}
					<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
						<td>
							<a href="javascript:void(DEVICE_SETUP.edit_device('{$r.guid}'))"><img src="ui/ed.png" title="Edit" border="0" /></a>
							
							{if $sessioninfo.id eq 1}
								<a href="javascript:void(DEVICE_SETUP.toggle_active('{$r.guid}'))">
									<img {if $r.active}src="ui/deact.png" title="Deactivate"{else}src="ui/act.png" title="Activate"{/if} border="0" id="img_device_active-{$device_guid}" />
								</a>
							{/if}
							{* if $sessioninfo.privilege.COUNTER_ALLOW_UNSET_STATUS && $counters[i].cst_id}
								<a href="javascript:void(unset_counter_status({$counters[i].id}))">
									<img src="ui/icons/computer_delete.png" title="Delete Counter Status" border="0">
								</a>
							{/if *}
						</td>
						<td>{$r.device_code}</td>
						<td>{$r.device_name}</td>
						<td>{$device_type_list[$r.device_type]|default:'Others'}</td>
						{if BRANCH_CODE eq 'HQ'}
							<td>
								{foreach from=$r.allowed_branches item=allowed_bid name=fb}
									{if $branches.$allowed_bid}
										{if !$smarty.foreach.fb.first}, {/if}
										{$branches.$allowed_bid.code}
									{/if}
								{/foreach}
							</td>
						{/if}
						<td align="center">{if $r.active}Active{else}Inactive{/if}</td>
						<td align="center">
							<span id="span_paired_status-{$device_guid}">
								{if $r.paired}
									Paired
									<img src="ui/icons/cross.png" title="Un-Pair" align="absmiddle" class="clickable" onClick="DEVICE_SETUP.unpair_device_clicked('{$device_guid}');" />
								{else}
									-
								{/if}
							</span>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>