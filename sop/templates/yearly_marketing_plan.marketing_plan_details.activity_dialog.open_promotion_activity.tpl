
{if !$promotion_activity}
	No Data
{else}
    <h4>Activity Informations</h4>
    	
	<form name="f_promotion_activity" onSubmit="return false;">
	    <input type="hidden" name="marketing_plan_id" value="{$marketing_plan.id}" />
	    <input type="hidden" name="promotion_plan_id" value="{$promotion_activity.promotion_plan_id}" />
	    <input type="hidden" name="branch_id" value="{$promotion_activity.branch_id}" />
	    <input type="hidden" name="activity_id" value="{$promotion_activity.id}" />
	    <input type="hidden" name="a" value="save_promotion_activity" />
	    <input type="hidden" name="can_assign_pic" value="{if $marketing_plan.label eq 'approved'}1{/if}" />
	    
	    <table width="100%">
	        <tr>
	            <td width="100"><b>Title</b></td>
	            <td><input type="text" name="title" maxlength="100" value="{$promotion_activity.title}" style="width:300px;" class="required" title="Title" /><img align="absbottom" title="Required Field" src="/ui/rq.gif"></td>
	        </tr>
	        <tr>
	            <td><b>Created by</b></td>
	            <td style="color:blue;">{$promotion_activity.created_by}</td>
	        </tr>
	        <tr>
	            <td><b>Date</b></td>
	            <td>
     				<input type="text" name="date_from" value="{$promotion_activity.date_from|default:'-'}" title="Date From" size="10" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif" />
					<b>to</b>
     				<input type="text" name="date_to" value="{$promotion_activity.date_to|default:'-'}" title="Date To" size="10" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif" />
				</td>
	        </tr>
	        <tr>
	            <td valign="top"><b>Remark</b></td>
	            <td><textarea name="remark" style="width:300px;">{$promotion_activity.remark}</textarea></td>
	        </tr>
	        <tr>
	            <td><b>Budget</b></td>
	            <td><input type="text" name="budget" value="{$promotion_activity.budget}" size="10" /></td>
	        </tr>
	        <tr>
	            <td><b>Active</b></td>
	            <td><input type="checkbox" name="active" value="1" {if $promotion_activity.active}checked {/if} /></td>
	        </tr>
	        <tr>
	            <td valign="top"><b>PIC</b></td>
	            <td>
	                <p>
						<b>Search user: </b>
						<input type="text" name="inp_autocomplete_input" size="20" />
						<input type="button" name="btn_autocomplete_add" value="Add" />
					</p>
					<div id="div_autocomplete_user_list" style="height:100px;overflow-x:hidden;overflow-y:auto;border:2px inset black;">
					</div>
	            </td>
	        </tr>
	    </table>
	    <p class="c" id="p_promotion_activity_button_area">
	        {if $allow_edit and ($YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $promotion_activity.branch_id)) and ($marketing_plan.label eq 'approved' or $marketing_plan.label eq 'draft')}
		        <button id="btn_save_promotion_activity"><img src="/ui/icons/disk.png" title="Save" align="absmiddle" /> Save</button>
		        <button id="btn_delete_promotion_activity"><img src="/ui/icons/delete.png" title="Save" align="absmiddle" /> Delete</button>
	        {/if}
	    </p>
	    {foreach from=$promotion_activity.pic_user_id_list item=uid}
	        <script>ACTIVITY_DIALOG_MODULE.add_current_pic_list('{$uid}');</script>
	    {/foreach}
	</form>
{/if}
