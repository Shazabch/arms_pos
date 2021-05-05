{*
*}
<tbody id="tbody_{if $is_fresh_market}fm_row{else}cat_row{/if}-{$cat_id}" >
	<tr class="{if $is_fresh_market}is_fresh_market_row{/if}">
		{if $row_total.total.new.amt || $row_total.total.old.amt}
			{assign var=new_si_row_total value=$row_total.total.new.amt}
			{assign var=old_si_row_total value=$row_total.total.old.amt}
		{else}
			{assign var=new_si_row_total value=$row_total.total.amt}
			{assign var=old_si_row_total value=$row_total.total.amt}
		{/if}

	    {if $row_total.total.amt}
			{if $new_si_row_total}
				{assign var=new_si_contribution_per value=$row.new.total.amt/$new_si_row_total*$new_si_root_per}
			{/if}
			{if $old_si_row_total}
				{assign var=old_si_contribution_per value=$row.old.total.amt/$old_si_row_total*$old_si_root_per}
			{/if}
		{else}
		    {assign var=new_si_contribution_per value=0}
		    {assign var=old_si_contribution_per value=0}
		{/if}
		<th nowrap align="left">
			{if !$no_header_footer}
				<img src="/ui/icons/table.png" align="absmiddle" onclick="show_sku('{$cat_id|ifzero:$cat_info.root_id}', this, '{$is_fresh_market}', '{if !$cat_id and $cat_info.root_id}1{/if}');" title="Show SKU" />
				{if !$cat_id}
					<img src="/ui/pixel.gif" width="16" align="absmiddle" />
				{/if}
			{/if}
			{if $smarty.request.ajax || $indent || $smarty.request.indent}
				{assign var=indent value=$indent|default:$smarty.request.indent|default:0}
				<img src="/ui/pixel.gif" width="{$indent*16}" height="1" />
			{/if}
			
			{if $cat_data.have_subcat}
				{if !$no_header_footer}
					{if !$included_sub_cat}
						<img onclick="expand_sub('{$cat_id}','{$smarty.request.indent+1}',this,'{$new_si_contribution_per}','{$old_si_contribution_per}','{$is_fresh_market}');" src="/ui/expand.gif" />
					{/if}
					<a href="javascript:void(show_sub('{$cat_id}'))">
				{/if}
				{$cat_info.description}				
				{if !$no_header_footer} </a>{/if}
			{else}
			    {$cat_info.description|default:'Un-Category'}
			{/if}
			{if $is_fresh_market}<sup>FM</sup>{/if}
		</th>
		
		{foreach from=$uq_cols key=dt item=d}
		    {assign var=fmt value="%0.2f"}
			{if $smarty.request.report_type eq 'qty'}   <!-- show by qty-->
				{assign var=fmt value="qty"}
				{assign var=new_si_val value=$row.$dt.new.qty}
				{assign var=old_si_val value=$row.$dt.old.qty}
			{elseif $smarty.request.report_type eq 'amt'}   <!-- show by amt-->
				{assign var=new_si_val value=$row.$dt.new.amt}
				{assign var=old_si_val value=$row.$dt.old.amt}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'} <!-- show by gp -->
				{assign var=new_si_val value=$row.$dt.amt-$row.$dt.new.cost}
				{assign var=old_si_val value=$row.$dt.amt-$row.$dt.old.cost}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'} <!-- show by gp pct -->
				{assign var=fmt value="%0.2f%"}
				{if $row.$dt.new.amt eq 0 && $row.$dt.old.amt eq 0}
					{assign var=new_si_val value=''}
					{assign var=old_si_val value=''}
				{else}
					{if $row.$dt.new.amt ne 0}
						{assign var=new_si_gp value=$row.$dt.new.amt-$row.$dt.new.cost}
						{assign var=new_si_val value=$new_si_gp/$row.$dt.new.amt*100}
					{/if}
					{if $row.$dt.old.amt ne 0}
						{assign var=old_si_gp value=$row.$dt.old.amt-$row.$dt.old.cost}
						{assign var=old_si_val value=$old_si_gp/$row.$dt.old.amt*100}
					{/if}
				{/if}
			{/if}

			{capture assign=new_si_tooltip}
				Qty: {$row.$dt.new.qty|value_format:'qty'} |  /  Amt:{$row.$dt.new.amt|string_format:'%.2f'}  /  Cost: {$row.$dt.new.cost|string_format:'%.3f'}
			{/capture}
			{capture assign=old_si_tooltip}
				Qty: {$row.$dt.old.qty|value_format:'qty'} |  /  Amt:{$row.$dt.old.amt|string_format:'%.2f'}  /  Cost: {$row.$dt.old.cost|string_format:'%.3f'}
			{/capture}
			<td class="small" align="right" title='{$new_si_tooltip}'>
				<span title="{$new_si_tooltip}">{$new_si_val|value_format:$fmt|ifzero:'-'}</span>
				<div class="old_sku_items" title="{$old_si_tooltip}">{$old_si_val|value_format:$fmt|ifzero:'-'}</div>
			</td>
		{/foreach}
		
		<td class="small" align="right">
			{$row.new.total.qty|value_format:'qty':'-'}
			<div class="old_sku_items">{$row.old.total.qty|value_format:'qty':'-'}</div>
		</td>
		<td class="small" align="right">
			{$row.new.total.amt|value_format:'%0.2f':'-'}
			<div class="old_sku_items">{$row.old.total.amt|value_format:'%0.2f':'-'}</div>
		</td>
		
		{*if $show_tran_count}
			<td class="small" align="right">{$row.total.tran_count|value_format:'%d'}</td>
			{assign var=buying_power value=0}
			{if $row.total.tran_count}
				{assign var=buying_power value=$row.total.amt/$row.total.tran_count}
			{/if}
			<td class="small" align="right">{$buying_power|value_format:'%0.2f':'-'}</td>
		{/if*}
		
		{if $sessioninfo.show_cost}
			<td class="small" align="right">
				{$row.new.total.cost|value_format:'%0.2f':'-'}
				<div class="old_sku_items">{$row.old.total.cost|value_format:'%0.2f':'-'}</div>
			</td>
		{/if}
		{if $sessioninfo.show_report_gp}
			{assign var=new_si_gp value=$row.new.total.amt-$row.new.total.cost}
			{assign var=old_si_gp value=$row.old.total.amt-$row.old.total.cost}
			<td class="small" align="right">
				{$new_si_gp|value_format:'%0.2f':'-'}
				<div class="old_sku_items">{$old_si_gp|value_format:'%0.2f':'-'}</div>
			</td>

			{if $row.new.total.amt>0}
			    {assign var=new_si_gp_per value=$new_si_gp/$row.new.total.amt*100}
			{else}
			    {assign var=new_si_gp_per value=0}
			{/if}
			{if $row.old.total.amt>0}
			    {assign var=old_si_gp_per value=$old_si_gp/$row.old.total.amt*100}
			{else}
			    {assign var=old_si_gp_per value=0}
			{/if}
			<td class="small" align="right">
				{$new_si_gp_per|value_format:'%0.2f%':'-'}
				<div class="old_sku_items">{$old_si_gp_per|value_format:'%0.2f%':'-'}</div>
			</td>
		{/if}

		<td class="small" align="right">
			{$new_si_contribution_per|value_format:'%0.2f%':'-'}
			<div class="old_sku_items">{$old_si_contribution_per|value_format:'%0.2f%':'-'}</div>
		</td>
	</tr>
</tbody>

{if $included_sub_cat}
	<!-- show child -->
	{assign var=curr_cat_info value=$cat_info}
	{assign var=curr_root_cat_id value=$curr_cat_info.id}

	{foreach from=$cat_child_info.$curr_root_cat_id key=child_cat_id item=child_cat_info}
		{if $is_fresh_market}
			{assign var=use_row value=$tb.$curr_root_cat_id.$child_cat_id.fm_data}
			{assign var=use_row_total value=$tb_total.$curr_root_cat_id.fm_data}
		{else}
			{assign var=use_row value=$tb.$curr_root_cat_id.$child_cat_id.data}
			{assign var=use_row_total value=$tb_total.$curr_root_cat_id.data}
		{/if}
		
		{if $use_row}
			{include file='report.category_old_vs_new_sku_items.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$use_row cat_id=$child_cat_id cat_info=$child_cat_info is_fresh_market=$is_fresh_market indent=$indent+1 row_total=$use_row_total}
		{/if}
	{/foreach}
{/if}
