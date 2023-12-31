{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

5/20/2019 9:06 AM William
- Enhance "GRN" and "GRR" word to use report_prefix.
*}

{if $total_page >1}
<div style="padding:2px;float:left;">
Page
<select onChange="page_change(this);">
	{section loop=$total_page name=s}
		<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
	{/section}
</select>
</div>
{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="sortable" id="batch_no_tbl" width=100% >
				<thead class="bg-gray-100">
					<tr >
						<th></th>
						<th>GRN No</th>
						<th>GRR No</th>
						<th>GRR Doc</th>
						<th>Type</th>
						<th>Vendor</th>
						<th>Selling</th>
						<th>Amount</th>
						<th>Last Update</th>
						<th>Print</th>
					</tr>
				</thead>
				
				{section name=i loop=$items}
				<tbody class="fs-08">
					<tr bgcolor={cycle values=",#eeeeee"}>
						<td align="center">
							<a href="masterfile_sku_items.batch_no_setup.php?a=edit&id={$items[i].id}&branch_id={$items[i].branch_id}&p={$smarty.request.p}&t={$smarty.request.t}"><img src="{if $smarty.request.t eq '1'}ui/ed.png{else}ui/view.png{/if}" title="Open this GRN" border=0></a>
							{if $smarty.request.t eq '2'}
								<a href="javascript:void(do_print({$items[i].id},{$items[i].branch_id}))"><img src="ui/print.png" title="Print this Summary by SKU Item Report" border="0"></a>
							{/if}
						</td>
						<td>{$items[i].report_prefix}{$items[i].id|string_format:"%05d"}</td>
						<td nowrap>{$items[i].report_prefix}{$items[i].grr_id|string_format:"%05d"}/{$items[i].grr_item_id}</td>
						<td>{$items[i].doc_no}</td>
						<td>{$items[i].type}</td>
						<td>{$items[i].vendor}
						</td>
						<td align=right>{$items[i].total_selling|number_format:2}</td>
						<td align=right>{$items[i].amount|number_format:2}</td>
						<td align=right>{$items[i].last_update}</td>
						<td align=center>{$items[i].print_counter}</td>
					</tr>
				</tbody>
				{sectionelse}
				<tr>
					<td colspan="10" align="center">- no record -</td>
				</tr>
				{/section}
				</table>
		</div>
	</div>
</div>

<script>
	ts_makeSortable($('batch_no_tbl'));
</script>