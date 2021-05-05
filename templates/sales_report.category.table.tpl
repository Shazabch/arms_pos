{*
9/17/2010 2:29:27 PM Andy
- Fix report to prevent user from triggle sub category or sku multiple time.

10/29/2010 4:19:02 PM Alex
- add show cost and show report gp privilege

11/29/2010 12:34:54 PM Andy
- Remove all fresh market items from this report.
- Show a new row of fresh market amount if "sales amount" is choose.

12/6/2010 2:21:49 PM Andy
- Change if item directly under category show "(Items directly under this category)" instead of category name, and cannot show sku details.

2/15/2011 10:00:24 AM Andy
- Reconstruct daily category sales report to show fresh market data.

6/16/2011 2:29:20 PM Andy
- Fix ajax show sub category got wrong contribute % when got fresh market sales.

9/8/2011 3:13:23 PM Andy
- Add show transaction count and buying power if show report by using top category or line.

4:55 PM 11/27/2014 Andy
- Enhance to show Service Charges and GST.
- Enhance the report to able to show by GST Amount or Sales Amount Included GST.

9/8/2011 3:13:23 PM William
- Enhance to add new column for "Discount" and "Gross Amount".

10/15/2020 9:35 AM William
- Added config checking "enable_tax" for tax.
*}

{assign var=curr_root_cat_id value=$curr_cat_info.id}

<!-- Normal selling, not fresh market -->
{if !$fresh_market_row_only}
	{foreach from=$cat_child_info.$curr_root_cat_id key=child_cat_id item=child_cat_info}
		{if $tb.$curr_root_cat_id.$child_cat_id.data }
			{if $normal_category_row_only}
				{assign var=row_total value=$tb_total.$curr_root_cat_id.data}
			{else}
				{assign var=row_total value=$tb_total.$curr_root_cat_id.total}
			{/if}
			{include file='sales_report.category.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$tb.$curr_root_cat_id.$child_cat_id.data cat_id=$child_cat_id cat_info=$child_cat_info row_total=$row_total}
		{/if}
	{/foreach}
{/if}

<!-- check whether got fresh market sales-->
{if !$normal_category_row_only and $config.enable_fresh_market_sku}
	<!-- generate fresh market html -->
	{capture assign=fresh_market_html}{strip}
		{foreach from=$cat_child_info.$curr_root_cat_id key=child_cat_id item=child_cat_info}
			{if $tb.$curr_root_cat_id.$child_cat_id.fm_data}
				{if $fresh_market_row_only}
					{assign var=row_total value=$tb_total.$curr_root_cat_id.fm_data}
				{else}
					{assign var=row_total value=$tb_total.$curr_root_cat_id.total}
				{/if}
				{include file='sales_report.category.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$tb.$curr_root_cat_id.$child_cat_id.fm_data cat_id=$child_cat_id cat_info=$child_cat_info row_total=$row_total is_fresh_market=1}
			{/if}
		{/foreach}
    {/strip}{/capture}
    
    <!-- print html if got fresh market sales-->
    {if $fresh_market_html}
        {if !$smarty.request.ajax}
            <!-- only show header if it is not call by ajax -->
	        {assign var=col_offset value=3}
	        {if $sessioninfo.show_cost}{assign var=col_offset value=$col_offset+1}{/if}
	        {if $sessioninfo.show_report_gp}{assign var=col_offset value=$col_offset+2}{/if}
	        <tr class="fm_data">
		        <td class="r">
		            {if !$no_header_footer}
		            	<img onclick="toggle_fm_row(this);" src="/ui/collapse.gif" />
		            {/if}
					Fresh Market
				</td>
		        {foreach from=$uq_cols key=dt item=d}
		        	<td>&nbsp;</td>
		        {/foreach}
		        <td>&nbsp;</td>
		        <td>&nbsp;</td>
		        
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td>&nbsp;</td>
					
					{* Amt Inc GST *}
					<td>&nbsp;</td>
				{/if}
		
				{if $show_tran_count}
					<td>&nbsp;</td><td>&nbsp;</td>	        
		        {/if}
				
		        {if $sessioninfo.show_cost}<td>&nbsp;</td>{/if}
		        {if $sessioninfo.show_report_gp}<td>&nbsp;</td><td>&nbsp;</td>{/if}
				<td></td>
				<td></td>
		        <td>&nbsp;</td>
		    </tr>
	    {/if}
	    {$fresh_market_html}
    {/if}
{/if}


