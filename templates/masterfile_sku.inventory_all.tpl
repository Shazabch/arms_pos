{*
REVISION HISTORY
================
11/19/2007 2:51:57 PM gary
- add do and adjustment stock.

1/13/2009 12:12:42 PM yinsee
- add Excel export

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

7/8/2010 4:41:04 PM Andy
- Show Un-finalized POS in SKU item inventory

7/9/2010 3:39:30 PM Andy
- Fix consignment module show all pos under un-finalized bugs, if consignment module will not have unfinalized pos.

7/16/2010 3:25:35 PM Andy
- SKU Inventory change to will not show stock balance for "SKU without inventory".

8/19/2010 3:02:39 PM Andy
- Add config control to no inventory sku.

10/17/2011 10:42:51 AM Alex
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

11/25/2011 5:00:06 PM Alex
- add show inactive branch

1/28/2014 2:01 PM Justin
- Enhanced to check privilege only show cost.

5/5/2016 5:06 PM Andy
- Change load all branch inventory to get from sku_items_cost, no more load all details.

5/6/2016 10:24 AM Andy
- Change "Last Calculation Timestamp" to "Last Update".

5/27/2016 11:12 AM Andy
- Change the wording. 
- Enhanced view all branch inventory to all recently 1 year of data, or data after stock check.

05/31/2016 11:30 Edwin
- Hide "display one year data" if $config.sku_inventory_all_show_full_data is enabled.

4/10/2017 11:17 AM Qiu Ying
- Enhanced to add IBT GRN in Parent SKU Inventory & SKU Item Inventory

4/19/2018 11:26 AM Justin
- Enhanced to make more clearer explaination for DO (nC).

05/13/2020 6:45PM Sheila
- Updated css for links in the table

06/26/2020 03:35 Sheila
- Updated button css
*}
{config_load file=common.conf}
<h2>{$sku.description}</h2>
{if !$smarty.request.output_excel}<button class="btn btn-primary" onclick="document.location='{$smarty.server.REQUEST_URI}&output_excel=1'">{#OUTPUT_EXCEL#}</button>{/if}

<p>
		{if !$config.sku_inventory_all_show_full_data}
		* Displaying recently 1 year of data. (From {$min_date} to {$smarty.now|date_format:"%Y-%m-%d"})<br />
			&nbsp;&nbsp;&nbsp;&nbsp;{/if}* If the branch got stock check, displaying the data after the stock check.<br />
		* nA = GRA/GRN/Ajustment not yet approved.<br>
		* nC = DO has been approved but not yet checkout.<br>
		{if !$config.consignment_modules}
			* nF = POS not yet finalise.<br />
		{/if}
		* Est = Estimated<br />
		{if !$form.is_parent_inventory}
			* all quantity displayed are in EACH uom.<br />
		{/if}
	
</p>

<table class="tb sku_inventory_tbl" border="0" cellpadding="4" cellspacing="0">
<tr bgcolor="#ffee99">
{assign var=rows value=2}
	<th rowspan="{$rows}">Branch</th>
	<th rowspan="{$rows}">Last Stock Check Date</th>
	<th rowspan="{$rows}">Opening Stock Qty</th>
	<th colspan="2">GRN</th>
	<th rowspan="{$rows}">POS</th>
	<th rowspan="{$rows}">GRA</th>
	<th rowspan="{$rows}">DO</th>
	<th rowspan="{$rows}">ADJ</th>
	{if $config.consignment_modules}
		<th rowspan="{$rows}">CN</th>
		<th rowspan="{$rows}">DN</th>
	{/if}
	<th rowspan="{$rows}">Balance</th>
	<th rowspan="{$rows}">GRN (nA)</th>
	{if !$config.consignment_modules}<th rowspan="{$rows}">POS (nF)</th>{/if}
	<th rowspan="{$rows}">GRA (nA)</th>
	<th rowspan="{$rows}">DO (nC)</th>
	<th rowspan="{$rows}">ADJ (nA)</th>
	{if $config.consignment_modules}
		<th rowspan="{$rows}">CN (nA)</th>
		<th rowspan="{$rows}">DN (nA)</th>
	{/if}
	<th rowspan="{$rows}">Balance (Est)</th>
</tr>

<tr bgcolor="#ffee99">
	<th>Vendor</th>
	<th>IBT</th>
</tr>

{foreach from=$branch item=b}
	{assign var=bid value=$b.id}
	{if !($config.sku_listing_hide_zero_balance and $data.branch_data.$bid.qty eq 0)}
	<tr>
		{if !$smarty.request.output_excel}
			<th align="left" class="branch_link"><a href="javascript:void(show_inventory({if $smarty.request.sku_id}'sku_id',{$smarty.request.sku_id}{else}'sku_item_id',{$smarty.request.sku_item_id}{/if},'{$bid}'))" title="{$b.description}">{$b.code}</a>{if !$b.active}<br />(Inactive){/if}</th>
		{else}
			<th align="left">{$b.code}{if !$b.active}<br />(Inactive){/if}</th>
		{/if}
		
		{* Stock Check Date *}
		<td align="center">{$data.branch_data.$bid.last_sc_date|ifzero:'-'}</td>
		
		{* Opening Stock Qty *}
		<td align="right">{$data.branch_data.$bid.open_qty|qty_nf|ifzero:'0'}</td>
		
		{* GRN *}
		<td align="right">{$data.branch_data.$bid.grn|qty_nf|ifzero:'&nbsp;'}</td>
		<td align="right">{$data.branch_data.$bid.grn_ibt|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* POS *}
		<td align="right">{$data.branch_data.$bid.pos|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* GRA *}
		<td align="right">{$data.branch_data.$bid.gra|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* DO *}
		<td align="right">{$data.branch_data.$bid.do|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* Adjustment *}
		<td align="right">{$data.branch_data.$bid.adjustment|qty_nf|ifzero:'&nbsp;'}</td>
		
		{if $config.consignment_modules}
			{* CN *}
			<td align="right">{$data.branch_data.$bid.cn|qty_nf|ifzero:'&nbsp;'}</td>
			
			{* DN *}
			<td align="right">{$data.branch_data.$bid.dn|qty_nf|ifzero:'&nbsp;'}</td>
		{/if}
		
		{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}
			<td align="right">N/A</td>
		{else}
			<td align="right">{$data.branch_data.$bid.qty|qty_nf}</td>
		{/if}
		
		{* GRN 2 *}
		<td align="right">{$data.branch_data.$bid.grn2|qty_nf|ifzero:'&nbsp;'}</td>
		
		{if !$config.consignment_modules}
			{* POS 2 *}
			<td align="right">{$data.branch_data.$bid.pos2|qty_nf|ifzero:'&nbsp;'}</td>
		{/if}
		
		{* GRA 2 *}
		<td align="right">{$data.branch_data.$bid.gra2|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* DO 2 *}
		<td align="right">{$data.branch_data.$bid.do2|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* Adjustment 2 *}
		<td align="right">{$data.branch_data.$bid.adjustment2|qty_nf|ifzero:'&nbsp;'}</td>
		
		{if $config.consignment_modules}
			{* CN 2 *}
			<td align="right">{$data.branch_data.$bid.cn2|qty_nf|ifzero:'&nbsp;'}</td>
			
			{* DN 2 *}
			<td align="right">{$data.branch_data.$bid.dn2|qty_nf|ifzero:'&nbsp;'}</td>
		{/if}
		
		{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}
			<td align="right">N/A</td>
		{else}
			<td align="right">{$data.branch_data.$bid.qty2|qty_nf}</td>
		{/if}
	</tr>
	{/if}
{/foreach}

<tr bgcolor="#ffee99">
	<th align="left">Total</th>
	<th align="center">-</th>
	<th align="right">-</th>
	
	{* GRN *}
	<td align="right">{$data.total.grn|qty_nf|ifzero:'&nbsp;'}</td>
	<td align="right">{$data.total.grn_ibt|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* POS *}
	<td align="right">{$data.total.pos|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* GRA *}
	<td align="right">{$data.total.gra|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* DO *}
	<td align="right">{$data.total.do|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* Adjustment *}
	<td align="right">{$data.total.adjustment|qty_nf|ifzero:'&nbsp;'}</td>
	
	{if $config.consignment_modules}
		{* CN *}
		<td align="right">{$data.total.cn|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* DN *}
		<td align="right">{$data.total.dn|qty_nf|ifzero:'&nbsp;'}</td>
	{/if}
	
	{* Balance *}
	{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}
			<td align="right">N/A</td>
	{else}
		<td align="right">{$data.total.qty|qty_nf|ifzero:'&nbsp;'}</td>
	{/if}
	
	{* GRN 2 *}
	<td align="right">{$data.total.grn2|qty_nf|ifzero:'&nbsp;'}</td>
	
	{if !$config.consignment_modules}
		{* POS 2 *}
		<td align="right">{$data.total.pos2|qty_nf|ifzero:'&nbsp;'}</td>
	{/if}
	
	{* GRA 2 *}
	<td align="right">{$data.total.gra2|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* DO 2 *}
	<td align="right">{$data.total.do2|qty_nf|ifzero:'&nbsp;'}</td>
	
	{* Adjustment 2 *}
	<td align="right">{$data.total.adjustment2|qty_nf|ifzero:'&nbsp;'}</td>
	
	{if $config.consignment_modules}
		{* CN *}
		<td align="right">{$data.total.cn2|qty_nf|ifzero:'&nbsp;'}</td>
		
		{* DN *}
		<td align="right">{$data.total.dn2|qty_nf|ifzero:'&nbsp;'}</td>
	{/if}
	
	{* Balance 2 *}
	{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}
			<td align="right">N/A</td>
	{else}
		<td align="right">{$data.total.qty2|qty_nf|ifzero:'&nbsp;'}</td>
	{/if}
</tr>
</table>
