{*
12/9/2014 11:58 AM Andy
- Move the old stock balance summary to custom report for smarthq.
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
					{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
				{else}
				<span class="small">(Items direct under this category)</span>
				{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
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
	    <td class="r">{$r.adj_in_cost|number_format:2|ifzero}</td>
	    <td class="r">{$r.adj_out|qty_nf|ifzero}</td>
	    <td class="r">{$r.adj_out_cost|number_format:2|ifzero}</td>

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
