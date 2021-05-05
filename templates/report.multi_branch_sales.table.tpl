{*
9/22/2020 5:21 PM William
- Enhanced to show grand total.
*}
{if $is_export}
	<h2>{$report_title}</h2>
{/if}

<h3>
	Page {$data.curr_page+1} / {$data.total_page}
</h3>

<table class="report_table" width="100%">
	<tr class="header">
		<th>No.</th>
		<th>ARMS Code</th>
		{if $is_export}
			<th>MCode</th>
			<th>ArtNo</th>
			<th>{$config.link_code_name}</th>
		{else}
			<th>MCode<br />ArtNo<br />{$config.link_code_name}</th>
		{/if}
		<th>Description</th>
		<th>UOM</th>
		<th>{if $BRANCH_CODE eq 'HQ'}Total {/if}Sales Quantity</th>
		<th>{if $BRANCH_CODE eq 'HQ'}Total {/if}Sales Amount</th>
		{if $BRANCH_CODE eq 'HQ'}
		<th colspan="{$data.branch_per_line}">Sales By Branch<br />(Quantity/Amount)</th>
		{/if}
	</tr>
	
	{assign var=item_no value=$data.start_item_no}
	{foreach from=$data.data.by_item key=id item=stock_info}
		{assign var=item_no value=$item_no+1}
		<tr>
			<td rowspan="{$data.item_row_use}">{$item_no}</td>
			<td rowspan="{$data.item_row_use}">{$data.si_info.$id.sku_item_code}</td>
			{if $is_export}
				<td rowspan="{$data.item_row_use}">{$data.si_info.$id.mcode|default:'-'}</td>
				<td rowspan="{$data.item_row_use}">{$data.si_info.$id.artno|default:'-'}</td>
				<td rowspan="{$data.item_row_use}">{$data.si_info.$id.link_code|default:'-'}</td>
			{else}
				<td rowspan="{$data.item_row_use}">
					{$data.si_info.$id.mcode|default:'-'}<br />
					{$data.si_info.$id.artno|default:'-'}<br />
					{$data.si_info.$id.link_code|default:'-'}
				</td>
			{/if}
			<td rowspan="{$data.item_row_use}">{$data.si_info.$id.description|default:'-'}</td>
			<td rowspan="{$data.item_row_use}">{$data.si_info.$id.uom_code|default:'-'}</td>
			
			{* Total Sales Quantity *}
			<td  rowspan="{$data.item_row_use}" align="right">{$data.si_info.$id.total_qty|qty_nf}</td>
			
			{* Total Sales Amount *}
			<td  rowspan="{$data.item_row_use}" align="right">{$data.si_info.$id.total_sales|number_format:$config.global_cost_decimal_points}</td>
			
			{* Sales By Branch *}
			{if $BRANCH_CODE eq 'HQ'}
				{foreach from=$data.branch_id_list_by_line key=line_no item=bid_list name=f_b_list}
					{if !$smarty.foreach.f_b_list.first}<tr>{/if}
					
					{* Branch Code *}
					{foreach from=$bid_list item=bid name=f_bcode}
						<td class="td_branch_code" title="{$branches.$bid.description|escape:html}">{$branches.$bid.code}</td>
					{/foreach}
					{if $smarty.foreach.f_bcode.iteration < $data.branch_per_line}
						{section loop=$data.branch_per_line name=s_bcode start=$smarty.foreach.f_bcode.iteration}
							<td class="td_branch_code">&nbsp;</td>
						{/section}
					{/if}
					</tr>
					
					{* Branch Sales *}
					<tr>
					{foreach from=$bid_list item=bid}
						<td align="right">
							{$stock_info.sales_by_branch.$bid.qty|qty_nf}
							<br />
							{$stock_info.sales_by_branch.$bid.sales|number_format:$config.global_cost_decimal_points}
						</td>
					{/foreach}
					{if $smarty.foreach.f_bcode.iteration < $data.branch_per_line}
						{section loop=$data.branch_per_line name=s_bcode start=$smarty.foreach.f_bcode.iteration}
							<td>&nbsp;</td>
						{/section}
					{/if}
					</tr>
				{/foreach}
			{/if}
		</tr>
	{/foreach}
	<tr class="header">
		{assign var=cols value=5}
		{if $is_export || $split_field}
			{assign var=cols value=$cols+2}
		{/if}
		<td colspan="{$cols}" align="right"><b>Page Total</b></td>
		<td align="right">{$data.page_total.total_qty|qty_nf}</td>
		<td align="right">{$data.page_total.total_sales|number_format:$config.global_cost_decimal_points}</td>
		{if $BRANCH_CODE eq 'HQ'}
			<td colspan="{$data.branch_per_line}">&nbsp;</td>
		{/if}
	</tr>
</table>

{if $display_grand_total}
	<br />
	<h2>Grand Total</h2>
	<table class="report_table">
		<tr>
			<td class="col_header" align="left"><b>Sales Quantity</b></th>
			<td align="right">{$data.grand_total.total_qty|qty_nf}</td>
		</tr>
		<tr>
			<td class="col_header" align="left"><b>Sales Amount</b></td>
			<td align="right">{$data.grand_total.total_sales|number_format:$config.global_cost_decimal_points}</td>
		</tr>
	</table>
{/if}