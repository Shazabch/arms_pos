{*
5/11/2015 11:32 AM Andy
- Remove the column "Type".
*}

{if !$ci_list}
	No Data
{else}
	<table width="100%"class="report_table">
	    <tr class="header">
	        <th width="40" colspan="2">&nbsp;</th>
	        <th>Inv No</th>
	        <th>Date</th>
	        <th>Deliver To</th>
	    </tr>
	    {foreach from=$ci_list item=r name=f}
	        <tr>
	            <td width="20">{$smarty.foreach.f.iteration}.</td>
	            <td width="20"><input type="checkbox" checked name="ci_list[]" value="{$r.branch_id},{$r.id}" /></td>
	            <td align="center">{$r.ci_no}</td>
	            <td align="center">{$r.ci_date}</td>
	            <td>{$r.ci_branch_code} - {$r.ci_branch_desc}</td>
	        </tr>
	    {/foreach}
	</table>
<script>
{literal}
$('btn_start_multiple_print').disabled = false;
{/literal}
</script>
{/if}
