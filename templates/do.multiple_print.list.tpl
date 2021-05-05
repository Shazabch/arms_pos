{*
4/20/2010 2:40:19 PM Andy
- Add search & print multiple DO
*}

{if !$do_list}
	No Data
{else}
	<input type="checkbox" name="print_do" checked /> Print DO
	{if $smarty.request.search_type eq 'do_no' or $smarty.request.search_type eq 'inv_no'}
	<input type="checkbox" name="print_invoice" checked /> Print Invoice
	{/if}

	<table width="100%"class="report_table">
	    <tr class="header">
	        <th width="40" colspan="2"<input type="checkbox" onChange="toggle_do_list_for_print(this);" checked></th>
	        <th>DO No</th>
	        <th>Inv No</th>
	        <th>Type</th>
	        <th>Date</th>
	        <th>Deliver To</th>
	    </tr>
	    {foreach from=$do_list item=r name=f}
	        <tr>
	            <td width="20">{$smarty.foreach.f.iteration}.</td>
	            <td width="20"><input type="checkbox" checked name="do_list[]" class="inp_do_list" value="{$r.branch_id},{$r.id}" /></td>
	            <td align="center">
					{if $r.is_draft}
					    {if $r.do_no}
					        {$r.do_no}<br />
					        <font class="small" color=#009900>{$r.temp_do_no} (DD)</font>
					    {else}
					        {$r.temp_do_no} (DD)
					    {/if}
					{elseif $r.is_proforma}
					    {if $r.do_no}
					        {$r.do_no}<br />
					        <font class="small" color=#009900>{$r.temp_do_no} (PD)</font>
						{else}
					    	{$r.temp_do_no} (PD)
					    {/if}
					{else}
                        {$r.do_no}
					{/if}
				</td>
	            <td align="center">{$r.inv_no|default:'-'}</td>
	            <td align="center">
					{if $r.do_type eq 'open'}Cash Sales
					{elseif $r.do_type eq 'credit_sales'}Credit Sales
					{else}Transfer{/if}
				</td>
	            <td align="center">{$r.do_date}</td>
	            <td>{$r.do_branch_code} - {$r.do_branch_desc}</td>
	        </tr>
	    {/foreach}
	</table>
<script>
{literal}
$('btn_start_multiple_print').disabled = false;
{/literal}
</script>
{/if}
