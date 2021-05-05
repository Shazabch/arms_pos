<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<thead bgcolor="#ffee99">
	    <tr>
	        <th colspan="2" width="80">#</th>
	        <th>Year</th>
	        <th>Total Events</th>
	        <th>Created by</th>
	        <th>Last Update</th>
	    </tr>
	</thead>
	<tbody class="tbody_container">
	    {foreach from=$festival_list item=festival name=f}
	        {include file='masterfile_festival_date.list.row.tpl'}
	    {/foreach}
	</tbody>
	{if !$festival_list}
		<tr>
	        <td colspan="5">** No data **</td>
	    </tr>
    {/if}
</table>
