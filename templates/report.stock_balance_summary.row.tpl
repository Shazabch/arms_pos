{*
11/18/2014 10:22 AM Fithri
- Enhanced to allow user can use last GRN without select vendor.

12/9/2014 2:15 PM Andy
- Fix sku icon not show when show report by category.

7/13/2015 5:58 PM Andy
- Remove additional selling price column.

8/9/2017 10:21 AM Qiu Ying
- Enhanced to add sales value at opening and closing balance

10/16/2017 3:26 PM Justin
- Enhanced to all columns that showing both qty and value become showing either Qty or Cost base on "Show by Qty" or "Show by Cost" button.

10/26/2017 4:36 PM Justin
- Enhanced to show cost decimal points base on config set.

5/28/2018 11:43 AM Andy
- Bug fixed of logo shown on excel export.

7/5/2019 9:42 AM William
- Added new "Day Turnover" column to stock balance report.

5/27/2020 5:40 PM William
- Show '-' when the cost is 0 value.

8/3/2020 10:17 AM William
- Enhanced to show red color text for negative value.
*}

{foreach from=$table key=tablekey item=r}
	{assign var=k value=$r.key}
	<tr bgcolor="{if $bgcolor}#{$bgcolor}{else}{cycle values='#ffffff,#ffffcc'}{/if}" id="tr,{$r.tree_str}({$k})" alt="{$tree_lv}">
	    <td nowrap>
	        {if $smarty.request.show_by eq 'vendor'}{$vendor_info.$k.code} - {$vendor_info.$k.description}
	        {elseif $smarty.request.show_by eq 'branch'}{$branches.$k.code}
	        {else}
				<img src="/ui/pixel.gif" height="1" width="{$tree_lv*20}">
				{if $k ne $smarty.request.category_id}
				    {if $show_sku_img && $k>0 && $category_info.$k.level>1}
						{if !$no_header_footer}
							<img src='/ui/icons/table.png' onclick="load_sku('{$k}',this);" align="absmiddle" title="View SKU at Stock Balance Report" />
						{/if}
				    {/if}
				    {$category_info.$k.description}
				    {if $category_info.$k.got_child}
						{if !$no_header_footer}
							<img src="ui/expand.gif" align="absmiddle" title="Toggle Child Category" border="0" onClick="toggle_category_child('{$k}',this);" class="clickable img_expand" />
						{/if}
					{/if}
					{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
				{else}
				<span class="small">(Items direct under this category)</span>
				{if $r.changed}<sup class="not_up_to_date">*</sup>{/if}
				{/if}
			{/if}
	    </td>
		{if $got_opening_sc}
			<td class="r {if $r.sc_adj_from < 0}red{/if}">{$r.sc_adj_from|qty_nf}</td>
		{/if}
	    <td class="r {if $r.sb_from < 0}red{/if}">{$r.sb_from|qty_nf}</td>
        {if $sessioninfo.privilege.SHOW_COST}
			<td class="r {if $r.sb_from_val < 0}red{/if}">{$r.sb_from_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		<td class="r {if $r.sales_value_from < 0}red{/if}">{$r.sales_value_from|number_format:2}</td>
	    <td class="r">
			{if $form.type eq 'qty'}
				<font {if $total.grn < 0} color="red" {/if}>{$r.grn|qty_nf|ifzero}</font> {if $smarty.request.use_grn}{if $smarty.request.vendor_id}<font {if $total.grn_vendor_qty < 0} color="red" {/if}>({$r.grn_vendor_qty|qty_nf|ifzero})</font>{/if}{/if}
			{else}
				<font {if $total.grn_cost < 0} color="red" {/if}>{$r.grn_cost|number_format:$config.global_cost_decimal_points|ifzero}</font> {if $smarty.request.use_grn}{if $smarty.request.vendor_id}<font {if $total.grn_vendor_cost < 0} color="red" {/if}>({$r.grn_vendor_cost|number_format:$config.global_cost_decimal_points|ifzero})</font>{/if}{/if}
			{/if}
		</td>
	    <td class="r {if ($form.type eq 'qty' && $r.gra < 0) || ($form.type neq 'qty' && $r.gra_cost<0)}red{/if}">
			{if $form.type eq 'qty'}
				{$r.gra|qty_nf|ifzero}
			{else}
				{$r.gra_cost|number_format:$config.global_cost_decimal_points|ifzero}
			{/if}
		</td>
		<td class="r {if ($form.type eq 'qty' && $r.pos < 0) || ($form.type neq 'qty' && $r.pos_cost<0)}red{/if}">
			{if $form.type eq 'qty'}
				{$r.pos|qty_nf|ifzero}
			{else}
				{$r.pos_cost|number_format:$config.global_cost_decimal_points|ifzero}
			{/if}
		</td>
	    <td class="r {if ($form.type eq 'qty' && $r.do < 0) || ($form.type neq 'qty' && $r.do_cost<0)}red{/if}">
			{if $form.type eq 'qty'}
				{$r.do|qty_nf|ifzero}
			{else}
				{$r.do_cost|number_format:$config.global_cost_decimal_points|ifzero}
			{/if}
		</td>
		{if $got_range_sc}
			<td class="r {if ($form.type eq 'qty' && $r.sc_adj < 0) || ($form.type neq 'qty' && $r.sc_adj_cost<0)}red{/if}">
				{if $form.type eq 'qty'}
					{$r.sc_adj|qty_nf|ifzero}
				{else}
					{$r.sc_adj_cost|number_format:$config.global_cost_decimal_points|ifzero}
				{/if}
			</td>
		{/if}
	    <td class="r {if ($form.type eq 'qty' && $r.adj_in < 0) || ($form.type neq 'qty' && $r.adj_in_cost<0)}red{/if}">
			{if $form.type eq 'qty'}
				{$r.adj_in|qty_nf|ifzero}
			{else}
				{$r.adj_in_cost|number_format:$config.global_cost_decimal_points|ifzero}
			{/if}
		</td>
	    <td class="r {if ($form.type eq 'qty' && $r.adj_out < 0) || ($form.type neq 'qty' && $r.adj_out_cost<0)}red{/if}">
			{if $form.type eq 'qty'}
				{$r.adj_out|qty_nf|ifzero}
			{else}
				{$r.adj_out_cost|number_format:$config.global_cost_decimal_points|ifzero}
			{/if}
		</td>

	    {if $config.consignment_modules}
	        <td class="r {if ($form.type eq 'qty' && $r.cn_qty < 0) || ($form.type neq 'qty' && $r.cn_val<0)}red{/if}">
				{if $form.type eq 'qty'}
					{$r.cn_qty|qty_nf|ifzero}
				{else}
					{$r.cn_val|number_format:$config.global_cost_decimal_points|ifzero}
				{/if}
			</td>
	        <td class="r {if ($form.type eq 'qty' && $r.dn_qty < 0) || ($form.type neq 'qty' && $r.dn_val<0)}red{/if}">
				{if $form.type eq 'qty'}
					{$r.dn_qty|qty_nf|ifzero}
				{else}
					{$r.dn_val|number_format:$config.global_cost_decimal_points|ifzero}
				{/if}
			</td>
        {/if}
	    <td class="r {if $r.sb_to < 0}red{/if}">{$r.sb_to|qty_nf}{if $r.got_sc}<sup class="got_sc">*</sup>{/if}</td>
	    {if $sessioninfo.privilege.SHOW_COST}
	    	<td class="r {if $r.sb_to_val < 0}red{/if}">{$r.sb_to_val|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{/if}
		<td class="r {if $r.sales_value_to < 0}red{/if}">{$r.sales_value_to|number_format:2}</td>
		<td class="r {if $r.turnover_ratio < 0}red{/if}">{$r.turnover_ratio|number_format:2}</td>
		<td class="r {if $r.turnover_days < 0}red{/if}">{$r.turnover_days|number_format:2}</td>
	</tr>
{/foreach}
