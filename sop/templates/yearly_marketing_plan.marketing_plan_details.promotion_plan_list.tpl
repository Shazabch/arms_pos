<table id="tbl_promotion_plan_list" width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead bgcolor="#ffee99">
		<tr>
		    <th colspan="2" rowspan="2">#</th>
		    <th rowspan="2" width="40">Calendar<br />Color</th>
		    <th rowspan="2" width="20">Active</th>
		    <th rowspan="2">Title</th>
		    <th colspan="2">Date</th>
		    <th rowspan="2">Description</th>
		    <th rowspan="2">Available Branch</th>
		    <th rowspan="2">Created by</th>
			<th rowspan="2">Last Update</th>
		</tr>
		<tr>
		    <th>From</th>
		    <th>To</th>
		</tr>
	</thead>
	<tbody id="tbody-promotion_plan_list">
		{foreach from=$promotion_plan_list item=promotion_plan name=fp}
		    {include file='yearly_marketing_plan.marketing_plan_details.promotion_plan_list.row.tpl' marketing_plan=$form}
		{/foreach}
	</tbody>
	<tfoot>
		<tr id="tr_promotion_no_data" style="{if $promotion_plan_list}display:none;{/if}">
	        <td colspan="11">** No Data **</td>
	    </tr>
    </tfoot>
</table>
