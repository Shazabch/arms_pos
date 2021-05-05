<form name="f_promotion_plan_own_details" method="post" onSubmit="return false;">
	<input type="hidden" name="promotion_plan_id" value="{$promotion_plan_own_data.promotion_plan_id}" />
	<input type="hidden" name="a" value="save_promotion_plan_own_details" />

	<h4>General Informations</h4>
	<div class="stdframe ui-corner-all" style="background:#fff">
	    <table>
	        {if $promotion_plan_own_data.branch_id}
		        <!-- Edit -->
			    <tr>
			        <td><b>Branch</b></td>
			        <td>
						<span class="branch_own_label">{$branches[$promotion_plan_own_data.branch_id].code|default:'-'}</span>
                        <input type="hidden" name="branch_id" value="{$promotion_plan_own_data.branch_id}" />
					</td>
			    </tr>
		    {else}
		        <!-- New -->
		        <tr>
		            <td><b>Branch</b></td>
		            <td>
		                <select name="branch_id" class="required" title="Branch">
		                    <option value="">-- Please Select --</option>
		                    {foreach from=$promotion_plan_own_data.available_branches item=bid}
		                        <option value="{$bid}">{$branches.$bid.code}</option>
		                    {/foreach}
		                </select><img align="absbottom" title="Required Field" src="/ui/rq.gif" />
		            </td>
		        </tr>
		    {/if}
		    <tr>
		        <td><b>Date</b></td>
		        <td>
		            <input name="date_from" size="12" value="{$promotion_plan_own_data.date_from}" readonly title="Date From" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
					&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;
					<input name="date_to" size="12" value="{$promotion_plan_own_data.date_to}" readonly title="Date To" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		</table>
	</div>
</form>
