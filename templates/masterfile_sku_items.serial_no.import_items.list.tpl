{*
5/20/2019 10:39 AM William
- Enhance "GRN" and "GRR" word to use report_prefix.
*}
<div class="div_content" align="center">
<div class="table-responsive">
	<table width="100%" class="dtl_items table mb-0 text-md-nowrap  table-hover">
		<thead class="bg-gray-100">
			<tr align="center">
				<th>{if $t ne 1}<input type="checkbox" id="check_all_inp" onclick="check_all_items();">{/if}</th>
				<th>{$master_title} No.</th>
				<th>GRR No</th>
				<th>GRR Doc</th>
				<th>Type</th>
				<th>Vendor</th>
				<th>Selling</th>
				<th>Amount</th>
				<th>Rcv Qty<br />(Pcs)</th>
				<th width="15">&nbsp;</th>
			</tr>
		</thead>
	
		<tbody class="fs-08" {if count($items)>15} style="width:650;height:250;overflow-y:auto;overflow-x:hidden;"{/if} id="dtl_items_row">
		{if count($items)>0}
			{assign var=import_items value=0}
			{foreach from=$items item=item}
				<tr class="dtl_items_row" height="25px">
					<td align="center">
						{assign var=grn_id value=$item.mid}
						{if (!$item.sn_import || $item.sn_import eq 'N;') && $t ne 1}
							<input type="checkbox" name="item_check[{$item.mid}]" mid="{$item.mid}" id="item_check[{$item.mid}]" class="grn_check" {if $item_check.$grn_id}checked{/if} value="1">
							{assign var=import_items value=1}
						{else}
							<img src="ui/view.png" title="Click to view S/N List from GRN" class="clickable" onclick="view_sn_list('{$item.mid}', '{$item.branch_id}')">
							<a href="javascript:void(show_print_dialog('{$item.mid}','{$item.branch_id}'))">
								<img border="0" title="Print this S/N Report" src="ui/print.png">
							</a>
						{/if}
					</td>
					<td>
					{if $master_title eq 'DO'}
						{if $item.do_no}
							{$item.do_no}
						{else}
							{$item.branch_prefix}{$item.id|string_format:"%05d"}(DD)
						{/if}
						<br>
						<font class="small" color=#009900>{$item.branch_prefix}{$item.id|string_format:"%05d"}(PD)</font>
					{else}
						{$item.report_prefix}{$item.mid|string_format:"%05d"}
					{/if}
					</td>
					<td nowrap>{$item.report_prefix}{$item.grr_id|string_format:"%05d"}{if !$config.use_grn_future}/{$item.grr_item_id}{/if}</td>
					<td>{$item.doc_no}</td>
					<td>{$item.type}</td>
					<td>{$item.vendor}</td>
					<td align=right>{$item.total_selling|number_format:2}</td>
					<td align=right>{$item.grn_amount|number_format:2}</td>
					<td class="r">{$item.qty|default:0|number_format}</td>
					<td>&nbsp;</td>
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="10" align="center">No data</td>
			</tr>
		{/if}
		</tbody>
	</table>
</div>
{if count($items) > 0 && $t ne 1 && $import_items}
<br />
	<div align="center">
		<input type="button" value="Import" onclick="ajax_add_item(this);">
		<input type="hidden" name="t" value="{$t}">
		<input type="hidden" name="search" value="{$search}">
	</div>
{/if}
</div>
