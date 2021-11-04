{*
2/17/2017 4:10 PM Justin
- Bug fixed on commission activate/deactivate feature.
*}

{$pagination}
<div>
<div class="table-responsive">
	<table class="sortable table mb-0 text-md-nowrap  table-hover" id="sac_tbl" width="100%" style="padding:2px">
		<thead class="bg-gray-100">
			<tr >
				<th width="10%">&nbsp;</th>
				<th width="10%">Commission No.</th>
				<th width="50%">Title</th>
				<th width="10%">Owner</th>
				<th width="10%">Created On</th>
				<th width="10%">Last Update</th>
			</tr>
		</thead>
		{foreach from=$sac_list item=sac}
			<tbody class="fs-08">
				<tr bgcolor="{cycle values=',#eeeeee'}" id="commission_{$sac.id}_{$sac.branch_id}">
					<td align="center" nowrap>
						<a href="{$smarty.server.PHP_SELF}?a=open_commission&id={$sac.id}&branch_id={$sac.branch_id}"><img src="ui/ed.png" title="Edit this Commission" border="0"></a>
						<a onclick="ajax_toggle_commission_status('{$sac.id}', '{$sac.branch_id}');"><img src="{if $sac.active}ui/deact.png{else}ui/act.png{/if}" id="img_sac_status_{$sac.id}_{$sac.branch_id}" title="{if $sac.active}Deactivate{else}Activate{/if} this Commission" border="0" style="cursor:pointer;"></a>
						<span class="small" id="span_sac_inactive_{$sac.id}_{$sac.branch_id}" {if $t > 0 || $sac.active}style="display:none;"{/if} align="center"><br />(inactive)</span>
					</td>
					<td align="center">#{$sac.id|string_format:"%05d"}</td>
					<td nowrap>{$sac.title}</td>
					<td align="center">{$sac.username}</td>
					<td align="center">{$sac.added}</td>
					<td align="center">{$sac.last_update}</td>
				</tr>
			</tbody>
		{foreachelse}
			<tr>
				<td colspan="5" align="center">- no record -</td>
			</tr>
		{/foreach}
	</table>
</div>
</div>

<script>
	ts_makeSortable($('sac_tbl'));
</script>
