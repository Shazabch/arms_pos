{include file='header.tpl' no_menu_templates=1}


<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MARKETING_PLAN_PROMOTION_ACTIVITY_MAIN_MODULE = {
	initialize: function(){
	    var completed_per = int($('#div_activity_completed_per').attr('per'));
		$('#div_activity_completed_per').progressbar({
			value: completed_per
		});
	}
};
{/literal}
</script>
{if !$promotion_activity}
	No Data
{else}
    <h4>Activity Informations</h4>

	<form name="f_promotion_activity" onSubmit="return false;">
	    <input type="hidden" name="marketing_plan_id" value="{$promotion_activity.sop_marketing_plan_id}" />
	    <input type="hidden" name="promotion_plan_id" value="{$promotion_activity.sop_promotion_plan_id}" />
	    <input type="hidden" name="activity_id" value="{$promotion_activity.id}" />

	    <table>
	        <tr>
	            <td width="100"><b>Title</b></td>
	            <td>{$promotion_activity.title}</td>
	        </tr>
	        <tr>
	            <td><b>Created by</b></td>
	            <td style="color:blue;">{$promotion_activity.created_by}</td>
	        </tr>
	        <tr>
	            <td><b>Date</b></td>
	            <td>
					{$promotion_activity.date_from|default:'-'}
					<b>to</b>
					{$promotion_activity.date_to|default:'-'}
				</td>
	        </tr>
	        <tr>
	            <td valign="top"><b>Remark</b></td>
	            <td>{$promotion_activity.remark|nl2br|default:'-'}</td>
	        </tr>
	        <tr>
	            <td><b>Budget</b></td>
	            <td>{$promotion_activity.budget|ifzero:'-'}</td>
	        </tr>
	        <tr>
	            <td><b>Active</b></td>
	            <td><img src="/ui/{if $promotion_activity.active}checked{else}unchecked{/if}.gif" /></td>
	        </tr>
	        <tr>
	            <td><b>Branch</b></td>
	            <td>
	                {$branches[$promotion_activity.created_branch_id].code}
	            </td>
	        </tr>
	        <tr>
	            <td valign="top"><b>Involved PIC</b></td>
	            <td>
	                {foreach from=$promotion_activity.pic_user_name_list item=u name=fp}
				        {$u.u}
				        {if !$smarty.foreach.fp.last}, {/if}
				    {/foreach}
	            </td>
	        </tr>
	        <tr>
	            <td><b>Completed %</b></td>
	            <td><div id="div_activity_completed_per" per="{$promotion_activity.completed_percent}" title="{$promotion_activity.completed_percent}%" style="height:8px;"></div></td>
	        </tr>
	    </table>
	</form>
{/if}

{include file='footer.tpl'}

<script>
{literal}
	$(function(){
        MARKETING_PLAN_PROMOTION_ACTIVITY_MAIN_MODULE.initialize(); // initial module
	});
{/literal}
</script>
