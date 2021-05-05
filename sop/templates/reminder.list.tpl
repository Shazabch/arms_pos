<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead class="theader">
		<tr>
		    <th colspan="2" width="60" rowspan="2">#</th>
		    <th rowspan="2">Active</th>
		    <th rowspan="2">Title</th>
		    <th colspan="2">Date</th>
		    <th rowspan="2">References Task</th>
		    <th rowspan="2">Task Title</th>
		    <th rowspan="2">Added</th>
		    <th rowspan="2">Last Update</th>
		</tr>
		<tr>
		    <th>From</th>
		    <th>To</th>
		</tr>
	</thead>
	<tbody class="tbody_container" id="tbody_reminder_list">
	    {foreach from=$reminder_list item=reminder name=fr}
	        {include file='reminder.list.row.tpl'}
	    {/foreach}
	</tbody>
	<tfoot>
	    <tr class="tr_no_data" style="{if $reminder_list}display:none;{/if}">
	        <td colspan="9">** No Data **</td>
	    </tr>
	</tfoot>
</table>
