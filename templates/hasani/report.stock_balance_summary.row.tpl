{*
12/29/2009 10:16:26 AM Andy
- change to only show item details link if got category id

5/6/2010 2:23:01 PM Alex
- Add privilege for show cost

2/9/2011 2:38:09 PM Alex
- Change use same color depends on 1st level category

3/14/2011 6:07:18 PM Alex
- add CN and DN

8/4/2011 12:25:28 PM Alex
- add selling price for opeing, total on hand, closing

8/8/2011 10:03:05 AM Alex
- add number format 2 digit

8/11/2011 6:28:21 PM Alex
- Change number_format to num_format for qty

9/30/2011 5:54:41 PM Andy
- Change report to also show those SKU which got GRN between from/to date.
- Add GRN Qty,Cost to show additional qty/cost for selected vendor when use GRN.

10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

5/4/2012 1:59:23 PM Justin
- Added to show new info "stock check adjust qty" for opening balance and "stock take adjust qty and value" for range.

9/27/2012 2:05:00 PM Fithri
- stock balance summary - add can show by branch

12/17/2012 1:53:00 PM Fithri
- add item list
*}

{foreach from=$table item=r}
	{assign var=k value=$r.key}
	<tr bgcolor="{if $bgcolor}#{$bgcolor}{else}{cycle values='#ffffff,#ffffcc'}{/if}" id="tr,{$r.tree_str}({$k})" alt="{$tree_lv}">
	    <td>
	        {if $smarty.request.show_by eq 'vendor'}{$vendor_info.$k.code} - {$vendor_info.$k.description}
	        {elseif $smarty.request.show_by eq 'branch'}{$branches.$k.code}
	        {else}
				<img src="/ui/pixel.gif" height="1" width="{$tree_lv*20}">
				{if $k ne $smarty.request.category_id}
				    {if $show_sku_img && $k>0 && $r.level>1}
				    	<img src='/ui/icons/table.png' onclick="load_sku('{$k}',this);" align="absmiddle" title="View SKU at Stock Balance Report" />
				    {/if}
				    {$category_info.$k.description}
				    {if $category_info.$k.got_child}
					<img src="ui/expand.gif" align="absmiddle" title="Toggle Child Category" border="0" onClick="toggle_category_child('{$k}',this);" class="clickable img_expand" />
					{/if}
				{else}
				<span class="small">(Items direct under this category)</span>
				{/if}
			{/if}
	    </td>
		{if $got_opening_sc}
			<td class="r">{$r.sc_adj_from|qty_nf}</td>
		{/if}
	    <td class="r">{$r.sb_from|qty_nf}</td>
        {if $sessioninfo.privilege.SHOW_COST}
			<td class="r">{$r.sb_from_val|number_format:2}</td>
		{/if}
		{if $config.stock_balance_report_show_additional_selling}
			<td class="r">{$r.sb_from_selling|number_format:2}</td>
		{/if}
		
	    <td class="r">{$r.grn|qty_nf|ifzero} {if $smarty.request.use_grn}({$r.grn_vendor_qty|qty_nf|ifzero}){/if}</td>
        {if $sessioninfo.privilege.SHOW_COST}
	    	<td class="r">{$r.grn_cost|number_format:2|ifzero} {if $smarty.request.use_grn}({$r.grn_vendor_cost|number_format:2|ifzero}){/if}</td>
		{/if}
	    <td class="r">{$r.gra|qty_nf|ifzero}</td>
	    {if $sessioninfo.privilege.SHOW_COST}
			<td class="r">{$r.gra_cost|number_format:2|ifzero}</td>
		{/if}
		<td class="r">{$r.pos|qty_nf|ifzero}</td>
        {if $sessioninfo.privilege.SHOW_COST}
			<td class="r">{$r.pos_cost|number_format:2|ifzero}</td>
		{/if}
	    <td class="r">{$r.do|qty_nf|ifzero}</td>
        {if $sessioninfo.privilege.SHOW_COST}
		    <td class="r">{$r.do_cost|number_format:2|ifzero}</td>
		{/if}
		{if $got_range_sc}
			<td class="r">{$r.sc_adj|qty_nf|ifzero}</td>
			{if $sessioninfo.privilege.SHOW_COST}
				<td class="r">{$r.sc_adj_cost|number_format:2|ifzero}</td>
			{/if}
		{/if}
	    <td class="r">{$r.adj_in|qty_nf|ifzero}</td>
	    <td class="r">{$r.adj_out|qty_nf|ifzero}</td>

	    {if $config.consignment_modules}
	        <td class="r">{$r.cn_qty|qty_nf|ifzero}</td>
			{if $sessioninfo.privilege.SHOW_COST}
		        <td class="r">{$r.cn_val|number_format:2|ifzero}</td>
			{/if}
	        <td class="r">{$r.dn_qty|qty_nf|ifzero}</td>
			{if $sessioninfo.privilege.SHOW_COST}
	        	<td class="r">{$r.dn_val|number_format:2|ifzero}</td>
			{/if}
        {/if}
	    <td class="r">{$r.sb_to|qty_nf}{if $r.got_sc}<sup class="got_sc">*</sup>{/if}</td>
	    {if $sessioninfo.privilege.SHOW_COST}
	    	<td class="r">{$r.sb_to_val|number_format:2}</td>
		{/if}
		{if $config.stock_balance_report_show_additional_selling}
			<td class="r">{$r.sb_to_selling|number_format:2}</td>
		{/if}
	</tr>
{/foreach}
