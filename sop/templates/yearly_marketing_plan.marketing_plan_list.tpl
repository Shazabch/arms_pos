{if $total_page >1}
	<div style="padding:2px;float:right;">
		Page
		<select class="sel_page_dropdown">
			{section loop=$total_page name=s}
				<option value="{$smarty.section.s.iteration}" {if $smarty.request.p eq $smarty.section.s.iteration}selected {/if}>
					{$smarty.section.s.iteration}
				</option>
			{/section}
		</select>
	</div>
{/if}

<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead bgcolor="#ffee99">
	    <tr>
	        <th rowspan="2" colspan="2">#</th>
	        <th rowspan="2">Title</th>
	        <th rowspan="2">Year</th>
	        <th colspan="2">Date</th>
	        <th rowspan="2">Remark</th>
	        <th rowspan="2">Created by</th>
	        <th rowspan="2">Last Update</th>
	    </tr>
	    <tr>
	        <th>From</th>
	        <th>To</th>
	    </tr>
	</thead>
	<tbody class="tbody_container">
	    {foreach from=$marketing_plan_list item=marketing_plan name=f}
	        {include file='yearly_marketing_plan.marketing_plan_list.row.tpl'}
	    {/foreach}
	</tbody>
	<tfoot>
	    <tr class="tr_marketing_plan_no_data" style="{if $marketing_plan_list}display:none;{/if}">
	        <td colspan="9">** No data **</td>
	    </tr>
	</tfoot>
</table>

{*<button class="btn_view_calendar" {if !$marketing_plan_list}disabled {/if} selected_year="{$smarty.request.year}">
	<img src="/ui/icons/calendar.png" title="View calendar" align="absmiddle" /> View calendar
</button>*}
