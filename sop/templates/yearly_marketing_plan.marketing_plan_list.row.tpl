<tr id="tr_marketing_plan-{$marketing_plan.id}">
	<td>{$smarty.foreach.f.iteration+$start_count}.</td>
	<td nowrap>
	    {if $marketing_plan.status eq 0 and $marketing_plan.approved eq 0}
			{if $allow_edit and $YMP_HQ_EDIT}
			    <a href="javascript:void(0);" class="open_marketing_plan" marketing_plan_id="{$marketing_plan.id}">
			        <img src="/ui/ed.png" title="Edit" border="0" />
				</a>
			
				<a href="javascript:void(0);" class="delete_marketing_plan" marketing_plan_id="{$marketing_plan.id}">
					<img src="/ui/icons/delete.png" title="Delete" border="0" />
				</a>
			{/if}
		{/if}
		<a href="?a=open_marketing_plan_details&id={$marketing_plan.id}">
		    <img src="/ui/icons/application_side_tree.png" title="Manage Promotion Plan" border="0" />
		</a>
	</td>
	<td>
		{$marketing_plan.title|default:'-'}
		{if preg_match('/\d/',$marketing_plan.approvals)}
			<div class="small">Approvals: <font color="#0000ff">
				{get_user_list list=$marketing_plan.approvals aorder_id=$marketing_plan.approval_order_id}
			</font></div>
		{/if}
	</td>
	<td>{$marketing_plan.year|default:'-'}</td>
	<td>{$marketing_plan.date_from|default:'-'}</td>
	<td>{$marketing_plan.date_to|default:'-'}</td>
	<td>{$marketing_plan.remark|default:'-'|nl2br}</td>
	<td>{$marketing_plan.added|default:'-'} <span class="small">({$marketing_plan.username|default:'-'})</span></td>
	<td>{$marketing_plan.last_update|default:'-'} <span class="small">({$marketing_plan.last_update_by_username|default:'-'})</span></td>
</tr>
