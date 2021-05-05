{if !$sheet_list}
	No Data
{else}
	<table width="100%"class="report_table">
	    <tr class="header">
	        <th width="40" colspan="2">&nbsp;</th>
	        <th>Inv No</th>
	        <th>Date</th>
	        <th>Deliver To</th>
	    </tr>
	    {foreach from=$sheet_list item=r name=f}
	        <tr>
	            <td width="20">{$smarty.foreach.f.iteration}.</td>
	            <td width="20"><input type="checkbox" checked name="sheet_list[]" value="{$r.branch_id},{$r.id}" /></td>
	            <td align="center">{$r.inv_no}</td>
	            <td align="center">{$r.date}</td>
	            <td>{$r.to_branch_code} - {$r.to_branch_desc}</td>
	        </tr>
	    {/foreach}
	</table>
<script>
{literal}
$('btn_start_multiple_print').disabled = false;
{/literal}
</script>
{/if}
