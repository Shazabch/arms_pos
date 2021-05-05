
<div style="position:absolute;right:17px;">
	<select id="sel_promotion_activity_branch">
		<option value="">-- Please Select Branch --</option>
		{foreach from=$promotion_plan.for_branch_id_list item=bid}
		    {if $YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $bid) or sop_check_privilege('SOP_YMP', $bid)}
		    	<option value="{$bid}" {if $bid eq $selected_branch_id}selected {/if}>{$branches.$bid.code}</option>
		    {/if}
		{/foreach}
	</select>
</div>

<table height="100%" width="100%">
	<tr>
	    <td width="30%">
			<div style="height:430px;overflow:auto;" class="stdframe" id="div_promotion_activity_left_container">
			    <ul class="ul_promotion_activity_tree"><li activity_id="0" class="li_activity_title"><div class="div_top_activity">
					{if $selected_branch_id}
					    {$branches.$selected_branch_id.code}
					{else}
					    Please Select Branch
					{/if}
				</div></li></ul>
			    <ul class="ul_promotion_activity_tree" id="ul_promotion_activity_tree-0" level="0">
					{foreach from=$promotion_activity_list item=pa}
                        {include file='yearly_marketing_plan.marketing_plan_details.activity_dialog.tree_row.tpl' promotion_activity=$pa}
					{/foreach}
			    </ul>
			</div>
		</td>
	    <td>
            <div style="height:430px;background:#fff;overflow-y:auto;" class="stdframe" id="div_activity_details"></div>
		</td>
	</tr>
	<tr height="20">
	    <td colspan="2">
	        <span id="span_activity_button_area">
	            {if $selected_branch_id and $allow_edit and ($YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $selected_branch_id)) and ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft')}
			        <button id="btn_add_new_activity">
						<img src="/ui/add.png" title="Add New Activity" align="absmiddle" border="0" /> Add New Activity
					</button>
					<button id="btn_refresh_activity_list">
						<img src="/ui/icons/arrow_refresh.png" title="Refresh Activity" align="absmiddle" border="0" /> Refresh Activity
					</button>
				{/if}
			</span>
	    </td>
	</tr>
</table>

<div style="display:none;">
	<ul>
		{include file='yearly_marketing_plan.marketing_plan_details.activity_dialog.tree_row.tpl'}
	</ul>
</div>
