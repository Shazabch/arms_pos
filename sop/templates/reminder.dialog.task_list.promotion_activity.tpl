<form name="f_reminder_task_list" method="post" onSubmit="return false;">
	{if !$promotion_activity_list}
		** No Available Activity **
	{else}
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="report_table">
		    <thead class="theader">
		        <tr>
		            <th>&nbsp;</th>
		            <th>Promotion</th>
		            <th>Activity</th>
				</tr>
		    </thead>
		    <tbody class="tbody_container">
		        {foreach from=$promotion_activity_list item=promotion_activity}
		            <tr>
		                <td>
							<input type="radio" name="promotion_activity" class="task_list" {if is_array($selected_promotion_activity)}{if $selected_promotion_activity.ref_id eq $promotion_activity.activity_reference_id}checked {/if}{/if}/>
							
							<input type="hidden" class="task_ref_table" value="sop_marketing_plan_promotion_activity" />
							<input type="hidden" class="task_name" value="{$promotion_activity.mar_promo_title|escape} > {$promotion_activity.mar_act_title|escape}" />
							<input type="hidden" class="ref_id" value="{$promotion_activity.activity_reference_id}" />
							
							<span class="span_all_ref_info">
							    <input type="hidden" name="ref_info[promotion_title]" value="{$promotion_activity.mar_promo_title|escape}" />
							    <input type="hidden" name="ref_info[activity_title]" value="{$promotion_activity.mar_act_title|escape}" />
							    <input type="hidden" name="ref_info[activity_created_branch_id]" value="{$promotion_activity.activity_created_branch_id}" />
							    <input type="hidden" name="ref_info[activity_id]" value="{$promotion_activity.activity_id}" />
							</span>
						</td>
		                <td>{$promotion_activity.mar_promo_title|default:'-'}</td>
		                <td>{$promotion_activity.mar_act_title|default:'-'}</td>
		            </tr>
		        {/foreach}
		    </tbody>
		</table>
	{/if}
</form>
