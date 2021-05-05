{*
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
			{include file='report.category_old_vs_new_sku_items.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$tb.$curr_root_cat_id.$child_cat_id.data cat_id=$child_cat_id cat_info=$child_cat_info row_total=$row_total}
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
				{include file='report.category_old_vs_new_sku_items.table.row.tpl' cat_data=$tb.$curr_root_cat_id.$child_cat_id row=$tb.$curr_root_cat_id.$child_cat_id.fm_data cat_id=$child_cat_id cat_info=$child_cat_info row_total=$row_total is_fresh_market=1}
			{/if}
		{/foreach}
    {/strip}{/capture}
    
    <!-- print html if got fresh market sales-->
    {if $fresh_market_html}
        {if !$smarty.request.ajax}
            <!-- only show header if it is not call by ajax -->
	        {assign var=col_offset value=3}
	        {*if $sessioninfo.show_cost}{assign var=col_offset value=$col_offset+1}{/if*}
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
		        {*if $show_tran_count}
					<td>&nbsp;</td><td>&nbsp;</td>	        
		        {/if*}
		        {if $sessioninfo.show_cost}<td>&nbsp;</td>{/if}
		        {if $sessioninfo.show_report_gp}<td>&nbsp;</td><td>&nbsp;</td>{/if}
		        <td>&nbsp;</td>
		    </tr>
	    {/if}
	    {$fresh_market_html}
    {/if}
{/if}


