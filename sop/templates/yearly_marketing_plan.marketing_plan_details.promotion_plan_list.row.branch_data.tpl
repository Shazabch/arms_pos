<tr id="tr_promotion_plan_branch_details-{$promotion_plan_b.promotion_plan_id}-{$promotion_plan_b.branch_id}" class="is_id_row">
	<td width="20">&nbsp;</td>
	<td width="80" nowrap class="r">
        {if !$approval_screen}
			{if $allow_edit}
			    {if ($YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $promotion_plan_b.branch_id)) and ($marketing_plan.label eq 'draft' or $marketing_plan.label eq 'approved')}
			        <img src="/ui/foc.png" title="Edit" border="0" align="absmiddle" class="edit_promotion_plan_branch_details clickable" />
				    <img src="/ui/del.png" title="Delete" border="0" align="absmiddle" class="delete_promotion_plan_branch_details clickable" />
			    {/if}
			{/if}
		{/if}
	</td>
	<td nowrap>
	    <span class="branch_own_label">
	    	{$branches[$promotion_plan_b.branch_id].code|default:'-'}
	    </span>
	</td>
	<td class="c">
		<input type="checkbox" title="Active/Deactive" {if $promotion_plan_b.active}checked {/if} id="chx_promotion_active_branch_details-{$promotion_plan.id}-{$promotion_plan_b.branch_id}" {if ($YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $promotion_plan_b.branch_id)) and ($marketing_plan.label eq 'draft' or $marketing_plan.label eq 'approved')}{else}disabled {/if} />
	</td>
	<td>{*{$promotion_plan_b.title|default:'-'}*}-</td>
	<td>
	    <span class="{if $promotion_plan_b.date_from ne $promotion_plan.date_from}date_diff{/if}">
			{$promotion_plan_b.date_from|default:'-'}
		</span>
	</td>
	<td>
	    <span class="{if $promotion_plan_b.date_to ne $promotion_plan.date_to}date_diff{/if}">
			{$promotion_plan_b.date_to|default:'-'}
		</span>
	</td>
	<td>{*{$promotion_plan_b.description|nl2br|default:'-'}*}-</td>
	<td>-</td>
	<td>{$promotion_plan_b.added|default:'-'} <span class="small">({$promotion_plan_b.created_by_user_u|default:'-'})</span></td>
	<td>{$promotion_plan_b.last_update|default:'-'} <span class="small">({$promotion_plan_b.last_update_by_user_u|default:'-'})</span></td>
</tr>
