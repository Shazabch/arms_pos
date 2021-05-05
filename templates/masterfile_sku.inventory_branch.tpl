{*
REVISION HISTORY
================
11/19/2007 2:51:57 PM gary
- add do and adjustment stock.

1/13/2009 12:12:42 PM yinsee
- add Excel export

3/31/2010 10:33:57 AM Andy
- Change to if data equal to zero, don't show the row
- Fix empty link bugs

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

7/8/2010 4:41:04 PM Andy
- Show Un-finalized POS in SKU item inventory

7/9/2010 3:39:30 PM Andy
- Fix consignment module show all pos under un-finalized bugs, if consignment module will not have unfinalized pos.

7/16/2010 3:25:35 PM Andy
- SKU Inventory change to will not show stock balance for "SKU without inventory".

8/19/2010 3:02:28 PM Andy
- Add config control to no inventory sku.

3/18/2011 3:08:00 PM Alex
- remove checking on 0 quantity

7/20/2011 11:11:42 AM Justin
- Added print SKU Item Inventory.

10/17/2011 10:06:02 AM Alex
- Modified the Ctn and Pcs round up to base on config set.
- Modified the round up for cost to base on config.

6/27/2013 3:41 PM Justin
- Enhanced as if the adjust +1 and -1 on same date, should show 0 and clickable to show the adj.

7/8/2013 4:22 PM Fithri
- pos (nf) column, do not show "0" when no data

7/23/2013 2:58 PM Fithri
- pos column, do not show "0" when no data

1/28/2014 2:01 PM Justin
- Enhanced to check privilege only show cost.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

4/12/2016 2:43 PM Andy
- Fix view inventory by parent will calculate wrong when got stock check.

5/6/2016 10:38 AM Andy
- Highlight the stock check and items without stock check header.

5/27/2016 11:12 AM Andy
- Change the wording. 

2/22/2017 9:35 AM Justin
- Bug fixed on Balance column couldn't show out while export excel.

4/10/2017 11:17 AM Qiu Ying
- Enhanced to add IBT GRN in Parent SKU Inventory & SKU Item Inventory

4/19/2018 11:26 AM Justin
- Enhanced to make more clearer explaination for DO (nC).
*}
{config_load file=common.conf}

{if $smarty.request.is_print}
	{include file='header.print.tpl'}
{/if}

<h2>{$sku.description}</h2>
{if !$smarty.request.is_print && !$smarty.request.output_excel}
<button onclick="document.location='{$smarty.server.REQUEST_URI}&output_excel=1'">{#OUTPUT_EXCEL#}</button>
<button onclick="window.open('{$smarty.server.REQUEST_URI}&is_print=1');"><img align="absmiddle" src="/ui/print.png"> Print</button>
{/if}
<p>
* nA = GRA/GRN/Ajustment not yet approved<br>
* nC = DO has been approved but not yet checkout<br>
{if !$config.consignment_modules}
* nF = POS not yet finalise<br>
{/if}
* Est = Estimated<br>
{if !$form.is_parent_inventory}
	* all quantity displayed are in EACH uom.
{/if}
</p>
<table class="tb" border="0" cellpadding="4" cellspacing="0" width="100%">
<tr bgcolor=#ffee99>
{assign var=rows value=2}
<th rowspan="{$rows}">Date</th>
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

{foreach from=$data item=d key=dt}
{*if $d.stock_check ne ''*}
{if isset($d.stock_check)}
<tr bgcolor=#ffee99>
<th align=left>{$dt}</th>
{assign var=cols value=5}
{if $config.consignment_modules}
	{assign var=cols value=$cols+3}
	{assign var=cols2 value=6}
{else}
    {assign var=cols value=$cols+1}
    {assign var=cols2 value=5}
{/if}
<td colspan="{$cols}"><b>Stock Check</b>
	{if !$form.is_parent_inventory}
		{if $sessioninfo.privilege.SHOW_COST}(Cost: {$d.stock_check_cost|number_format:$config.global_cost_decimal_points}){/if}
	{else}
		{if !$smarty.request.is_print and !$smarty.request.output_excel}
			<span class="small">[<a href="javascript:void(toggle_sc_details('{$dt}'))">Details</a>]</span>
		{/if}
		<br />
		
		<div id="div_sc_details-{$dt}" style="{if !$smarty.request.is_print and !$smarty.request.output_excel}display:none;{/if}">
			{foreach from=$d.stock_check_by_item key=sid item=sc name=fsc}
				{$sc.sku_item_code}: {$sc.qty|qty_nf}{if !$smarty.foreach.fsc.last},<br />{/if}
			{/foreach}
			
			{if $d.un_stock_check_by_item}
				<br /><b>Others item without stock check</b><br />
				{foreach from=$d.un_stock_check_by_item key=sid item=usc name=ufsc}
					{$form.item_list.$sid.sku_item_code}: {$usc.qty|qty_nf}{if !$smarty.foreach.ufsc.last},<br />{/if}
				{/foreach}
			{/if}
		</div>
	{/if}
</td>
<td align=right>{$d.stock_check|qty_nf}</td>
<td colspan="{$cols2}">&nbsp;</td>
<td align=right>
    {if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}N/A{else}
		{$d.stock_check+$balance2|qty_nf}
	{/if}
</td>
</tr>
{assign var=balance value=$d.stock_check}
{/if}

	{* assign var=balance value=$balance+$d.grn-$d.pos-$d.gra-$d.do+$d.adjustment+$d.cn-$d.dn}
	{assign var=balance2 value=$balance2+$d.grn2-$d.pos2-$d.gra2-$d.do2+$d.adjustment2+$d.cn2-$d.dn2 *}
	<tr>
	<th align=left>{$dt|default:'-'}</th>
	{if !$smarty.request.output_excel}
		<td>
		    {if isset($d.grn)}
				<a href="javascript:void(inventory_find('grn','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}',0))">{$d.grn|qty_nf}</a>
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
		    {if isset($d.grn_ibt)}
				<a href="javascript:void(inventory_find('grn','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}',1))">{$d.grn_ibt|qty_nf}</a>
			{else}
				&nbsp;
			{/if}
		</td>
		<td>{$d.pos|qty_nf|ifzero}</td>
		<td>
		    {if isset($d.gra)}
				<a href="javascript:void(inventory_find('gra','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.gra|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		<td>
		    {if isset($d.do)}
				<a href="javascript:void(inventory_find('do','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.do|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		<td>
		    {if isset($d.adjustment)}
				<a href="javascript:void(inventory_find('adj','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.adjustment|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		{if $config.consignment_modules}
			<td>
			    {if isset($d.cn)}
					<a href="javascript:void(inventory_find('cn','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.cn|qty_nf}</a>
			    {else}
					&nbsp;
				{/if}
			</td>
			<td>
			    {if isset($d.dn)}
					<a href="javascript:void(inventory_find('dn','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.dn|qty_nf}</a>
			    {else}
					&nbsp;
				{/if}
			</td>
		{/if}
		<td align=right>{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}N/A{else}{$d.bal|qty_nf}{/if}</td>
		<td>
		    {if isset($d.grn2)}
				<a href="javascript:void(inventory_find('grn2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.grn2|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		{if !$config.consignment_modules}<td>{$d.pos2|qty_nf|ifzero}</td>{/if}
		<td>
		    {if isset($d.gra2)}
				<a href="javascript:void(inventory_find('gra2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.gra2|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		<td>
		    {if isset($d.do2)}
				<a href="javascript:void(inventory_find('do2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.do2|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		<td>
		    {if isset($d.adjustment2)}
				<a href="javascript:void(inventory_find('adj2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.adjustment2|qty_nf}</a>
		    {else}
				&nbsp;
			{/if}
		</td>
		{if $config.consignment_modules}
			<td>
			    {if isset($d.cn2)}
					<a href="javascript:void(inventory_find('cn2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.cn2|qty_nf}</a>
			    {else}
					&nbsp;
				{/if}
			</td>
			<td>
			    {if isset($d.dn2)}
					<a href="javascript:void(inventory_find('dn2','{$form.type}',{$sku.sku_id},{$sku.id},'{$dt}','{$smarty.request.branch_id}'))">{$d.dn2|qty_nf}</a>
			    {else}
					&nbsp;
				{/if}
			</td>
		{/if}
	{else}
		<td>
			{if isset($d.grn)}
				{$d.grn|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.grn_ibt)}
				{$d.grn_ibt|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.pos)}
				{$d.pos|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.gra)}
				{$d.gra|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.do)}
				{$d.do|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.adjustment)}
				{$d.adjustment|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		{if $config.consignment_modules}
		    <td>
				{if isset($d.cn)}
					{$d.cn|qty_nf}
				{else}
					&nbsp;
				{/if}
			</td>
			<td>
				{if isset($d.dn)}
					{$d.dn|qty_nf}
				{else}
					&nbsp;
				{/if}
			</td>
		{/if}
		<td align=right>{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}N/A{else}{$d.bal|qty_nf}{/if}</td>
		<td>
			{if isset($d.grn2)}
				{$d.grn2|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		{if !$config.consignment_modules}
			<td>
				{if isset($d.pos2)}
					{$d.pos2|qty_nf}
				{else}
					&nbsp;
				{/if}
			</td>
		{/if}
		<td>
			{if isset($d.gra2)}
				{$d.gra2|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.do2)}
				{$d.do2|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		<td>
			{if isset($d.adjustment2)}
				{$d.adjustment2|qty_nf}
			{else}
				&nbsp;
			{/if}
		</td>
		{if $config.consignment_modules}
		    <td>
				{if isset($d.cn2)}
					{$d.cn2|qty_nf}
				{else}
					&nbsp;
				{/if}
			</td>
			<td>
				{if isset($d.dn2)}
					{$d.dn2|qty_nf}
				{else}
					&nbsp;
				{/if}
			</td>
		{/if}
	{/if}
	<td align=right>{if $config.enable_no_inventory_sku and $no_inventory eq 'yes'}N/A{else}{$d.bal2|qty_nf}{/if}</td>
	</td>
	</tr>
{* /if *}
{/foreach}
</table>

{if $smarty.request.is_print}
<script>
{literal}
	window.print();
{/literal}
</script>
{/if}