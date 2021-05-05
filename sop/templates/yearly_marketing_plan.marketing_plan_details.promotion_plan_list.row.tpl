<tr id="tr_promotion_plan-{$promotion_plan.id}" class="is_id_row">
	<td width="20"><span class="row_no">{$smarty.foreach.fp.iteration}</span>.</td>
	<td width="80" nowrap>
	    {if !$approval_screen}
			{if $allow_edit and $YMP_HQ_EDIT}
			    {if ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft') and $YMP_HQ_EDIT}
				    <img src="/ui/ed.png" title="Edit" border="0" align="absmiddle" class="open_promotion_plan clickable" />
				    {if ($marketing_plan.label eq 'draft') and $YMP_HQ_EDIT}
				    <img src="/ui/icons/delete.png" title="Delete" border="0" align="absmiddle" class="delete_promotion_plan clickable" />
				    {/if}
			    {/if}
			{/if}
			
			{if $allow_edit}
			    <img src="/ui/icons/table_edit.png" align="absmiddle" title="Open activity management" class="open_activity_dialog clickable" />
				
			{/if}
			{if $allow_edit and ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft')}
				<img src="/ui/foc.png" align="absmiddle" title="Add branch own info" class="add_alternative_changes clickable" />
			{/if}
		{/if}
	</td>
	<td>
	    <div class="{if ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft')}colorSelector{/if}" id="div_calendar_color-{$promotion_plan.id}" default_color="{$promotion_plan.calendar_color}" style="margin:0;padding:0;">
   			<div title="{$promotion_plan.calendar_color}" style="background-color:{$promotion_plan.calendar_color}">&nbsp;</div>
		</div>
	</td>
	<td class="c">
		<input type="checkbox" title="Active/Deactive" {if $promotion_plan.active}checked {/if} id="chx_promotion_active-{$promotion_plan.id}" {if ($marketing_plan.label ne 'draft' and $marketing_plan.label ne 'approved') or !$allow_edit or !$YMP_HQ_EDIT}disabled {/if} />
	</td>
	<td td_type="title">{$promotion_plan.title|default:'-'}</td>
	<td>{$promotion_plan.date_from|default:'-'}</td>
	<td>{$promotion_plan.date_to|default:'-'}</td>
	<td>{$promotion_plan.description|nl2br|default:'-'}</td>
	<td>
	    <span class="small">
	    {foreach from=$promotion_plan.for_branch_id_list item=bid name=fb}
	        <span id="span_promotion_branch-{$promotion_plan.id}-{$bid}" class="{if $promotion_plan.branch_own_info.$bid}red_strike{/if}">{$branches.$bid.code}</span>
	        {if !$smarty.foreach.fb.last}, {/if}
	    {/foreach}
	    </span>
	</td>
	<td>{$promotion_plan.added|default:'-'} <span class="small">({$promotion_plan.created_by|default:'-'})</span></td>
	<td>{$promotion_plan.last_update|default:'-'}</td>
</tr>

{if $promotion_plan.branch_own_info and !$no_branch_data_row}
	{foreach from=$promotion_plan.branch_own_info item=promotion_plan_b}
	    {include file='yearly_marketing_plan.marketing_plan_details.promotion_plan_list.row.branch_data.tpl'}
	{/foreach}
{/if}
