<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead class="bg-gray-100">
	    <tr>
	        <th colspan="2" width="100">#</th>
	        <th>Branch</th>
	        <th>Type</th>
	        <th>Department</th>
	        <th>SKU Type</th>
	        <th>Order</th>
	        <th>Approvers</th>
	        <th>Notify Users</th>
	    </tr>
	</thead>
	<tbody class="tbody_container">
	    {foreach from=$approval_flow_list item=approval_flow name=fa}
	        <tr class="tr_approval_flow_row" approval_flow_id="{$approval_flow.id}">
	            <td width="20">{$smarty.foreach.fa.iteration}.</td>
	            <td width="80" nowrap>
					<img src="/ui/{if $approval_flow.active}deact.png{else}act.png{/if}" title="Activate/Deactivate" border="0" class="clickable img_act_approval_flow" />
					<img src="/ui/ed.png" title="Edit" border="0" class="clickable img_edit_approval_flow" />
				</td>
	            <td>{$branches[$approval_flow.branch_id].code|default:'-'}</td>
	            <td>{$flow_type[$approval_flow.type].label|default:'-'}</td>
	            <td>{$approval_flow.cat_description|default:'-'}</td>
	            <td>{$approval_flow.sku_type|default:'-'}</td>
	            <td>{$approval_order[$approval_flow.aorder].description|default:'-'}</td>
	            <td>{get_user_list list=$approval_flow.approvals aorder_id=$approval_flow.aorder}</td>
	            <td>{get_user_list list=$approval_flow.notify_users delimeter=', '}</td>
	        </tr>
		{foreachelse}
		    <tr class="tr_approval_flow_no_data" style="{if $approval_flow_list}display:none;{/if}">
		        <td colspan="9">** No Data **</td>
		    </tr>
	    {/foreach}
	</tbody>
</table>
