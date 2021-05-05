<form name="f_promotion_plan" method="post" onSubmit="return false;">
    <input type="hidden" name="marketing_plan_id" value="{$promotion_plan.marketing_plan_id}" />
	<input type="hidden" name="promotion_plan_id" value="{$promotion_plan.id}" />
	<input type="hidden" name="a" value="save_promotion_plan" />
	
	<h4>General Informations</h4>
	<div class="stdframe ui-corner-all" style="background:#fff">
	    <table>
		    <tr>
		        <td width="100"><b>Title</b></td>
		        <td>
		            <input type="text" name="title" value="{$promotion_plan.title}" maxlength="100" size="50" title="Promotion Plan Title" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		    <tr>
		        <td><b>Date</b></td>
		        <td>
		            <input name="date_from" size="12" value="{$promotion_plan.date_from}" readonly title="Date From" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
					&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;
					<input name="date_to" size="12" value="{$promotion_plan.date_to}" readonly title="Date To" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif">
		        </td>
		    </tr>
		    <tr>
		        <td valign="top"><b>Description</b></td>
		        <td><textarea name="description" cols="50" rows="4">{$promotion_plan.description}</textarea></td>
		    </tr>
		    
		    <tr style="{if $BRANCH_CODE ne 'HQ'}display:none; {/if}">
		        <td valign="top"><b>Branch</b></td>
		        <td>
		            <div style="width:100%;max-height:300px;border:1px solid black;overflow-y:auto;" class="ui-corner-all">
		            <ul style="list-style:none;">
						<li><input type="checkbox" name="toggle_all_branches" /> <b>All</b></li>
						<li style="background: #cfcfcf;">
							<input type="checkbox" name="for_branch_id_list[]" value="1" onClick="return false;" checked /> HQ
						</li>
		                {foreach from=$branches key=bid item=b}
		                    {if $bid>1}
			                    <li><input type="checkbox" name="for_branch_id_list[]" value="{$bid}" {if is_array($promotion_plan.for_branch_id_list) and in_array($bid, $promotion_plan.for_branch_id_list)}checked {/if}  />
		                    	    {$b.code} - {$b.description}
								</li>
							{/if}
		                {/foreach}
		            </ul>
		            </div>
		        </td>
		    </tr>
		</table>
	</div>
</form>
