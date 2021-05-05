{*
7/6/2011 5:18:49 PM Andy
- Fix un-category sales missing from report.
- Fix item direct under category cannot show sku details.

9/8/2011 3:13:23 PM Andy
- Add show transaction count and buying power if show report by using top category or line.

10/14/2011 11:53:11 AM Alex
- change qty use value_format:'qty'

12/8/2011 6:25:32 PM Justin
- Removed all values that contains "%" to prevent sorting bug.

4:55 PM 11/27/2014 Andy
- Enhance to show Service Charges and GST.
- Enhance the report to able to show by GST Amount or Sales Amount Included GST.

1/3/2018 9:22 AM Justin
- Bug fixed on the tooltips showing HTML tag.

6/7/2019 1:08 PM William
- Added two column Gross sales and Discount.

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

10/15/2020 9:37 AM William
- Change GST word to use Tax.
*}

<tbody id="tbody_{if $is_fresh_market}fm_row{else}cat_row{/if}-{$cat_id}" >
	<tr class="{if $is_fresh_market}is_fresh_market_row{/if}">
	    {if $row_total.total.amt}
	    	{assign var=contribution_per value=$row.total.amt/$row_total.total.amt*$root_per}
		{else}
		    {assign var=contribution_per value=0}
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
						<img onclick="expand_sub('{$cat_id}','{$smarty.request.indent+1}',this,'{$contribution_per}','{$is_fresh_market}');" src="/ui/expand.gif" />
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
				{assign var=val value=$row.$dt.qty}
			{elseif $smarty.request.report_type eq 'amt'}   <!-- show by amt-->
				{assign var=val value=$row.$dt.amt}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'} <!-- show by gp -->
				{assign var=val value=$row.$dt.amt-$row.$dt.cost}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'} <!-- show by gp pct -->
				{assign var=fmt value="%0.2f%"}
				{if $row.$dt.amt eq 0}
					{assign var=val value=''}
				{else}
				    {assign var=gp value=$row.$dt.amt-$row.$dt.cost}
				    {assign var=val value=$gp/$row.$dt.amt*100}
				{/if}
			{elseif $smarty.request.report_type eq 'gst_amt'}
				{assign var=val value=$row.$dt.tax_amount}
			{elseif $smarty.request.report_type eq 'amt_inc_gst'}
				{assign var=val value=$row.$dt.amt_inc_gst}
			{/if}

			{capture assign=tooltip}
				Qty:{$row.$dt.qty|qty_nf}  /  Amt:{$row.$dt.amt|string_format:'%.2f'}  /  Cost:{$row.$dt.cost|string_format:'%.3f'} / Tax:{$row.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$row.$dt.amt_inc_gst|string_format:'%.2f'} 
			{/capture}
			<td class="small" align="right" title='{$tooltip|escape:"html"}'>{$val|value_format:$fmt}</td>
		{/foreach}
		
		<td class="small" align="right">{$row.total.qty|value_format:'qty'}</td>
		<td class="small" align="right">{$row.total.gross_amt|value_format:'%0.2f':'-'}</td>
		<td class="small" align="right">{$row.total.discount|value_format:'%0.2f':'-'}</td>
		<td class="small" align="right">{$row.total.amt|value_format:'%0.2f':'-'}</td>
		
		{if $config.enable_gst || $config.enable_tax}
			{* GST *}
			<td class="small" align="right">{$row.total.tax_amount|value_format:'%0.2f':'-'}</td>
			
			{* Amt Inc GST *}
			<td class="small" align="right">{$row.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
		{/if}
		
		{if $show_tran_count}
			<td class="small" align="right">{$row.total.tran_count|value_format:'%d'}</td>
			{assign var=buying_power value=0}
			{if $row.total.tran_count}
				{assign var=buying_power value=$row.total.amt/$row.total.tran_count}
			{/if}
			<td class="small" align="right">{$buying_power|value_format:'%0.2f':'-'}</td>
		{/if}
		
		{if $sessioninfo.show_cost}
			<td class="small" align="right">{$row.total.cost|value_format:'%0.2f':'-'}</td>
		{/if}
		{if $sessioninfo.show_report_gp}
			{assign var=gp value=$row.total.amt-$row.total.cost}
			<td class="small" align="right">{$gp|value_format:'%0.2f':'-'}</td>

			{if $row.total.amt>0}
			    {assign var=gp_per value=$gp/$row.total.amt*100}
			{else}
			    {assign var=gp_per value=0}
			{/if}
			<td class="small" align="right">{$gp_per|value_format:'%0.2f%':'-'}</td>
		{/if}

		{if $row_total.total.amt}
			<td class="small" align="right">
				{$contribution_per|value_format:'%0.2f%':'-'}
			</td>
		{else}
            <td>&nbsp;</td>
		{/if}
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
			{include file='sales_report.category.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$use_row cat_id=$child_cat_id cat_info=$child_cat_info is_fresh_market=$is_fresh_market indent=$indent+1 row_total=$use_row_total}
		{/if}
	{/foreach}
{/if}
