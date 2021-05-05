{*
2/8/2017 1:36 PM Andy
- Added "As per date" to show report generation time.
- Added Report Title if found is export.

4/5/2017 8:00 AM Qiu Ying
- Enhanced to separate MCode, ArtNo, Old Code into own column when export

5/4/2017 9:24 AM Justin
- Enhanced to have split out MCode, Artno and Link Code checkbox.

5/8/2017 4:40 PM Justin
- Enhanced to add page total for stock balance and stock value.

5/18/2017 9:35 AM Justin
- Bug fixed page total stock value.
- Bug fixed on showing additional column while login to sub branch.
- Bug fixed on page total stock balance.

11/7/2018 2:21 PM Andy
- Enhanced to have "Grand Total".
- Fixed total cost wrong if group by sku.

12/21/2020 4:54 PM William
- Enhanced "Multi Branch Stock Balance" report move branch column to top row when no multi row.
*}

{if $is_export}
	<h2>{$report_title}</h2>
{/if}

<h3>
	Page {$data.curr_page+1} / {$data.total_page}
	<br />
	As per date: {$data.report_time|date_format:"%Y-%m-%d %H:%M"}
</h3>

{assign var=split_field value=$smarty.request.split_field}
<table class="report_table" width="100%">
	<tr class="header">
		<th>No.</th>
		<th>ARMS Code</th>
		{if $is_export || $split_field}
			<th>MCode</th>
			<th>ArtNo</th>
			<th>{$config.link_code_name}</th>
		{else}
			<th>MCode<br />ArtNo<br />{$config.link_code_name}</th>
		{/if}
		<th>Description</th>
		<th>UOM</th>
		<th>{if $BRANCH_CODE eq 'HQ'}Total {/if}Stock Balance</th>
		<th>
			{if $BRANCH_CODE eq 'HQ'}
				HQ
			{/if}
			Last Cost
		</th>
		<th>
			Stock Value
			{if $BRANCH_CODE eq 'HQ'}<br />(Using HQ Last Cost){/if}
		</th>
		{if $BRANCH_CODE eq 'HQ'}
			{if $branch_line_count > 1}
			<th colspan="{$data.branch_per_line}">Stock Balance By Branch</th>
			{else}
				{foreach from=$data.branch_id_list_by_line key=line_no item=bid_list name=f_b_list}
					{foreach from=$bid_list item=bid name=f_bcode}
						<th colspan="1">{$branches.$bid.code}</th>
					{/foreach}
				{/foreach}
			{/if}
		{/if}
	</tr>
	
	{assign var=item_no value=$data.start_item_no}
	{foreach from=$data.data.by_item key=id item=stock_info}
		{assign var=item_no value=$item_no+1}
		<tr>
			<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$item_no}</td>
			<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.sku_item_code}</td>
			{if $is_export || $split_field}
				<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.mcode|default:'-'}</td>
				<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.artno|default:'-'}</td>
				<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.link_code|default:'-'}</td>
			{else}
				<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">
					{$data.si_info.$id.mcode|default:'-'}<br />
					{$data.si_info.$id.artno|default:'-'}<br />
					{$data.si_info.$id.link_code|default:'-'}
				</td>
			{/if}
			<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.description|default:'-'}</td>
			<td rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}">{$data.si_info.$id.uom_code|default:'-'}</td>
			
			{* Total Stock Balance *}
			<td  rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}" align="right">{$stock_info.total.qty|qty_nf}</td>
			
			{* Last Cost *}
			<td  rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}" align="right">{$data.si_info.$id.last_cost|number_format:$config.global_cost_decimal_points}</td>
			
			{* Stock Value *}
			<td  rowspan="{if $branch_line_count > 1}{$data.item_row_use}{else}1{/if}" align="right">
				{$stock_info.total.cost|number_format:$config.global_cost_decimal_points}
			</td>
			
			{* Stock Balance By Branch *}
			{if $BRANCH_CODE eq 'HQ'}
				{if $branch_line_count > 1}
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
					
					{* Branch Stock *}
					<tr>
					{foreach from=$bid_list item=bid}
						<td align="right">{$stock_info.stock_by_branch.$bid.qty}</td>
					{/foreach}
					{if $smarty.foreach.f_bcode.iteration < $data.branch_per_line}
						{section loop=$data.branch_per_line name=s_bcode start=$smarty.foreach.f_bcode.iteration}
							<td>&nbsp;</td>
						{/section}
					{/if}
					</tr>
				{/foreach}
				{else}
					{* Branch Stock *}
					
					{foreach from=$bid_list item=bid}
						<td align="right">{$stock_info.stock_by_branch.$bid.qty}</td>
					{/foreach}
					{if $smarty.foreach.f_bcode.iteration < $data.branch_per_line}
						{section loop=$data.branch_per_line name=s_bcode start=$smarty.foreach.f_bcode.iteration}
							<td>&nbsp;</td>
						{/section}
					{/if}
				{/if}
			{/if}
		</tr>
	{/foreach}
	<tr class="header">
		{assign var=cols value=5}
		{if $is_export || $split_field}
			{assign var=cols value=$cols+2}
		{/if}
		<td colspan="{$cols}" align="right"><b>Page Total</b></td>
		<td align="right">{$data.page_total.qty|qty_nf}</td>
		<td>&nbsp;</td>
		<td align="right">{$data.page_total.cost|number_format:$config.global_cost_decimal_points}</td>
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
			<td class="col_header" align="center"><b>Stock Balance</b></th>
			<td align="right">{$data.grand_total.qty|qty_nf}</td>
			
		</tr>
		
		<tr>
			<td class="col_header" align="center">
				<b>
					Stock Value
					{if $BRANCH_CODE eq 'HQ'}<br />(Using HQ Last Cost){/if}
				</b>
			</td>
			<td align="right">{$data.grand_total.cost|qty_nf}</td>
		</tr>
	</table>
{/if}