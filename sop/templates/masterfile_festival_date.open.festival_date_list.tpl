<table id="tbl_festival_date_list" width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead bgcolor="#ffee99">
		<tr>
		    <th colspan="2" rowspan="2">#</th>
		    <th rowspan="2" width="40">Calendar<br />Color</th>
		    <th rowspan="2" width="20">Active</th>
		    <th rowspan="2">Title</th>
		    <th colspan="2">Date</th>
		    <th rowspan="2">Created by</th>
			<th rowspan="2">Last Update</th>
		</tr>
		<tr>
		    <th>From</th>
		    <th>To</th>
		</tr>
	</thead>
	<tbody id="tbody_festival_date_list">
		{foreach from=$festival_date_list item=festival_date name=ffd}
		    {include file='masterfile_festival_date.open.festival_date_list.row.tpl' festival_date=$festival_date festival_sheet=$form}
		{/foreach}
	</tbody>
	<tfoot>
		<tr id="tr_festival_date_no_data" style="{if $festival_date_list}display:none;{/if}">
	        <td colspan="9">** No Data **</td>
	    </tr>
    </tfoot>
</table>
