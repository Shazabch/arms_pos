{*
10/17/2011 3:37:49 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/19/2017 9:28 AM Khausalya 
- Enhanced changes from RM to use config setting. 

*}
<tfoot>
	<tr class="tr_item_row">
  	<td colspan="8" class="r"><b>Total</b></td>
  	{assign var=sid value='total'}
  	{assign var=row_holding_cost value=0}
  	{assign var=row_actual_profit_grp_amt value=0}
  	{foreach from=$date_label key=date_key item=dk}
              {capture assign=tooltips_prefix}All branches \ Total \ {$dk.y}-{$months[$dk.m]} \{/capture}
              {assign var=repeated_sku value=''}
			
          	{assign var=col_class value="col_`$dk.y`_`$dk.m`"}
          	{assign var=dk_m value=$dk.m}
          	
          	<!-- In (Qty) -->
          	<td class="r in_qty" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Opening Qty">{$total.$sid.opening.$date_key.qty|qty_nf}</td>
          	<td class="r in_qty" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} In Qty">{$total.$sid.grn.$date_key.qty|qty_nf}</td>
          	{assign var=total_in value=$total.$sid.opening.$date_key.qty+$total.$sid.grn.$date_key.qty}
          	<td class="r in_qty" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Opening Qty">{$total_in|qty_nf}</td>
          	
          	<!-- Sales (Qty) -->
          	<td class="r sales_qty" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales Qty">{$total.$sid.pos.$date_key.qty|qty_nf}</td>
          	{if $total_in}
          	    {assign var=sales_per value=$total.$sid.pos.$date_key.qty/$total_in*100}
          	{else}
          	    {assign var=sales_per value=0}
          	{/if}
          	<td class="r sales_qty" col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>
          	
          	<!-- IBT (Qty) -->
          	<td class="r ibt_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} DO Qty">{$total.$sid.do.$date_key.qty|qty_nf}</td>
          	<td class="r ibt_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj Qty">{$total.$sid.ibt_adj.$date_key.qty|qty_nf}</td>
          	{if $total_in}
          	    {assign var=ibt_adj_per value=$total.$sid.ibt_adj.$date_key.qty/$total_in*100}
          	{else}
          	    {assign var=ibt_adj_per value=0}
          	{/if}
          	<td class="r ibt_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

          	<!-- GRA (Qty) -->
          	<td class="r gra_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA Qty">{$total.$sid.gra.$date_key.qty|qty_nf}</td>
          	{if $total_in}
          	    {assign var=gra_per value=$total.$sid.gra.$date_key.qty/$total_in*100}
          	{else}
          	    {assign var=gra_per value=0}
          	{/if}
          	<td class="r gra_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

          	<!-- Total Out (Qty) -->
          	{assign var=total_out value=$total.$sid.pos.$date_key.qty+$total.$sid.do.$date_key.qty+$total.$sid.gra.$date_key.qty}
          	<td class="r total_out_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
          	{if $total_in}
          	    {assign var=total_out_per value=$total_out/$total_in*100}
          	{else}
          	    {assign var=total_out_per value=0}
          	{/if}
          	<td class="r total_out_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

          	<!-- Adj (Qty) -->
          	{assign var=adj value=$total.$sid.adj.$date_key.qty+$total.$sid.stock_check_adj.$date_key.qty}
          	<td class="r adj_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
          	{if $total_in}
          	    {assign var=adj_per value=$adj/$total_in*100}
          	{else}
          	    {assign var=adj_per value=0}
          	{/if}
          	<td class="r adj_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

          	<!-- Balance (Qty) -->
			{assign var=balance_qty value=$total_in-$total_out}
			<td class="r balance_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
			{if $total_in}
          	    {assign var=balance_per value=$balance_qty/$total_in*100}
          	{else}
          	    {assign var=balance_per value=0}
          	{/if}
          	<td class="r balance_qty " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

          	<!-- Total Sales (Amt) -->
          	<td class="r sales_amt " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Cost">{$total.$sid.pos.$date_key.cost|number_format:2}</td>
          	<td class="r sales_amt " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Total Selling Amount">{$total.$sid.pos.$date_key.amt|number_format:2}</td>
          	{assign var=profit_amt value=$total.$sid.pos.$date_key.amt-$total.$sid.pos.$date_key.cost}
          	<td class="r sales_amt " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
          	{if $total.$sid.pos.$date_key.amt}
          	    {assign var=pos_per value=$profit_amt/$total.$sid.pos.$date_key.amt*100}
          	{else}
          	    {assign var=pos_per value=0}
          	{/if}
          	<td class="r sales_amt " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

          	<!-- Holding Cost -->  	
          	{assign var=holding_cost value=$total_hoding_cost.$dk_m}
          	{assign var=row_holding_cost value=$row_holding_cost+$holding_cost}
          	<td class="r holding_cost " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
          	{if $profit_amt}
                    {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
          	{else}
          	    {assign var=holding_cost_per value=0}
          	{/if}
          	<td class="r holding_cost " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

          	<!-- Actual Profit -->
          	{assign var=actual_profit value=$profit_amt-$holding_cost}
          	<td class="r actual_profit " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
          	{if $total.$sid.pos.$date_key.amt}
          	    {assign var=actual_profit_per1 value=$actual_profit/$total.$sid.pos.$date_key.amt*100}
          	{else}
          	    {assign var=actual_profit_per1 value=0}
          	{/if}
          	<td class="r actual_profit " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
			{if $total.$sid.pos.$date_key.amt}
          	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
          	    {assign var=actual_profit_per2 value=$total.$sid.pos.$date_key.amt-$actual_profit_per2-$holding_cost}
          	    {assign var=actual_profit_per2 value=$actual_profit_per2/$total.$sid.pos.$date_key.amt*100}
          	{else}
          	    {assign var=actual_profit_per2 value=0}
          	{/if}
          	
          	{assign var=actual_profit_grp_amt value=$total_actual_profit_grp_amt.$dk_m}
          	{assign var=row_actual_profit_grp_amt value=$row_actual_profit_grp_amt+$actual_profit_grp_amt}
          	<td class="r actual_profit " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
          	
          	<td class="r actual_profit " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

          	<!-- Average Per Unit (Amt) -->
          	{if $total.$sid.pos.$date_key.qty}
          	    {assign var=avg_cost value=$total.$sid.pos.$date_key.cost/$total.$sid.pos.$date_key.qty}
          	    {assign var=avg_amt value=$total.$sid.pos.$date_key.amt/$total.$sid.pos.$date_key.qty}
          	{else}
          	    {assign var=avg_cost value=0}
          	    {assign var=avg_amt value=0}
          	{/if}
          	<td class="r avg " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
          	<td class="r avg " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

          	<!-- Variance (Qty & Amt) -->
          	<!-- Mark up -->
          	<td class="r markup " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Qty">{$total.$sid.variances.$date_key.markup_qty|qty_nf}</td>
          	<td class="r markup " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Up Amount">{$total.$sid.variances.$date_key.markup_amt|number_format:2}</td>
          	<!-- Mark down -->
          	<td class="r markdown " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Qty">{$total.$sid.variances.$date_key.markdown_qty|qty_nf}</td>
          	<td class="r markdown " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Mark Down Amount">{$total.$sid.variances.$date_key.markdown_amt|number_format:2}</td>
          	<!-- discount -->
          	<td class="r discount " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Qty">{$total.$sid.variances.$date_key.disc_qty|qty_nf}</td>
          	<td class="r discount " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Discount Amount">{$total.$sid.variances.$date_key.disc_amt|number_format:2}</td>
          	<!-- Variance Amt -->
          	{assign var=variance_amt value=$total.$sid.variances.$date_key.markup_amt-$total.$sid.variances.$date_key.markdown_amt-$total.$sid.variances.$date_key.disc_amt}
          	<td class="r variance_amt " col_class="{$col_class}" style="display:none;" title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>
      {/foreach}
      
      <!-- Total -->
      {capture assign=tooltips_prefix}All branches \ {$sku_item.sku_item_code} \ {$sku_item.description|escape} \ Total \{/capture}
      
    	<!-- In (Qty) -->
    	<td class="r in_qty col_total" title="{$tooltips_prefix} Opening Qty">{$total.$sid.opening.total.qty|qty_nf}</td>
    	<td class="r in_qty col_total" title="{$tooltips_prefix} In Qty">{$total.$sid.grn.total.qty|qty_nf}</td>
    	{assign var=total_in value=$total.$sid.opening.total.qty+$total.$sid.grn.total.qty}
    	<td class="r in_qty col_total" title="{$tooltips_prefix} Total In">{$total_in|qty_nf}</td>

    	<!-- Sales (Qty) -->
    	<td class="r sales_qty col_total"  title="{$tooltips_prefix} Sales Qty">{$total.$sid.pos.total.qty|qty_nf}</td>
    	{if $total_in}
    	    {assign var=sales_per value=$total.$sid.pos.total.qty/$total_in*100}
    	{else}
    	    {assign var=sales_per value=0}
    	{/if}
    	<td class="r sales_qty col_total"  title="{$tooltips_prefix} Sales %">{$sales_per|number_format:2}</td>

    	<!-- IBT (Qty) -->
    	<td class="r ibt_qty col_total"  title="{$tooltips_prefix} DO Qty">{$total.$sid.do.total.qty|qty_nf}</td>
    	<td class="r ibt_qty col_total"  title="{$tooltips_prefix} IBT Adj Qty">{$total.$sid.ibt_adj.total.qty|qty_nf}</td>
    	{if $total_in}
    	    {assign var=ibt_adj_per value=$total.$sid.ibt_adj.total.qty/$total_in*100}
    	{else}
    	    {assign var=ibt_adj_per value=0}
    	{/if}
    	<td class="r ibt_qty col_total" title="{$tooltips_prefix} IBT Adj %">{$ibt_adj_per|number_format:2}</td>

    	<!-- GRA (Qty) -->
    	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA Qty">{$total.$sid.gra.total.qty|qty_nf}</td>
    	{if $total_in}
    	    {assign var=gra_per value=$total.$sid.gra.total.qty/$total_in*100}
    	{else}
    	    {assign var=gra_per value=0}
    	{/if}
    	<td class="r gra_qty col_total" title="{$tooltips_prefix} GRA %">{$gra_per|num_format:2}</td>

    	<!-- Total Out (Qty) -->
    	{assign var=total_out value=$total.$sid.pos.total.qty+$total.$sid.do.total.qty+$total.$sid.gra.total.qty}
    	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out Qty">{$total_out|qty_nf}</td>
    	{if $total_in}
    	    {assign var=total_out_per value=$total_out/$total_in*100}
    	{else}
    	    {assign var=total_out_per value=0}
    	{/if}
    	<td class="r total_out_qty col_total" title="{$tooltips_prefix} Total Out %">{$total_out_per|number_format:2}</td>

    	<!-- Adj (Qty) -->
    	{assign var=adj value=$total.$sid.adj.total.qty+$total.$sid.stock_check_adj.total.qty}
    	<td class="r adj_qty col_total" title="{$tooltips_prefix} Stock Take & Adj Qty">{$adj|qty_nf}</td>
    	{if $total_in}
    	    {assign var=adj_per value=$adj/$total_in*100}
    	{else}
    	    {assign var=adj_per value=0}
    	{/if}
    	<td class="r adj_qty col_total"  title="{$tooltips_prefix} Stock Take & Adj %">{$adj_per|number_format:2}</td>

    	<!-- Balance (Qty) -->
		{assign var=balance_qty value=$total_in-$total_out}
		<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock Qty">{$balance_qty|qty_nf}</td>
		{if $total_in}
    	    {assign var=balance_per value=$balance_qty/$total_in*100}
    	{else}
    	    {assign var=balance_per value=0}
    	{/if}
    	<td class="r balance_qty col_total" title="{$tooltips_prefix} Closing Stock %">{$balance_per|number_format:2}</td>

    	<!-- Total Sales (Amt) -->
    	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Cost">{$total.$sid.pos.total.cost|number_format:2}</td>
    	<td class="r sales_amt col_total" title="{$tooltips_prefix} Total Selling Amount">{$total.$sid.pos.total.amt|number_format:2}</td>
    	{assign var=profit_amt value=$total.$sid.pos.total.amt-$total.$sid.pos.total.cost}
    	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit Amount">{$profit_amt|number_format:2}</td>
    	{if $total.$sid.pos.total.amt}
    	    {assign var=pos_per value=$profit_amt/$total.$sid.pos.total.amt*100}
    	{else}
    	    {assign var=pos_per value=0}
    	{/if}
    	<td class="r sales_amt col_total" title="{$tooltips_prefix} Profit %">{$pos_per|number_format:2}</td>

    	<!-- Holding Cost -->
    	{assign var=holding_cost value=$row_holding_cost}
    	<td class="r holding_cost col_total"  title="{$tooltips_prefix} Holding Cost">{$holding_cost|number_format:2}</td>
    	{if $profit_amt}
            {assign var=holding_cost_per value=$holding_cost/$profit_amt*100}
    	{else}
    	    {assign var=holding_cost_per value=0}
    	{/if}
    	<td class="r holding_cost col_total" title="{$tooltips_prefix} Holding Cost %">{$holding_cost_per|number_format:2}</td>

    	<!-- Actual Profit -->
    	{assign var=actual_profit value=$profit_amt-$holding_cost}
    	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Amount">{$actual_profit|number_format:2}</td>
    	{if $total.$sid.pos.total.amt}
    	    {assign var=actual_profit_per1 value=$actual_profit/$total.$sid.pos.total.amt*100}
    	{else}
    	    {assign var=actual_profit_per1 value=0}
    	{/if}
    	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit %">{$actual_profit_per1|number_format:2}</td>
		{if $total.$sid.pos.total.amt}
    	    {assign var=actual_profit_per2 value=$total_out*$sku_item.hq_cost}
    	    {assign var=actual_profit_per2 value=$total.$sid.pos.total.amt-$actual_profit_per2-$holding_cost}
    	    {assign var=actual_profit_per2 value=$actual_profit_per2/$total.$sid.pos.total.amt*100}
    	{else}
    	    {assign var=actual_profit_per2 value=0}
    	{/if}
    
		{assign var=actual_profit_grp_amt value=$row_actual_profit_grp_amt}
    	<td class="r actual_profit col_total" title="{$tooltips_prefix} Actual Profit Group Amount">{$actual_profit_grp_amt|number_format:2}</td>
      	
    	<td class="r actual_profit col_total"  title="{$tooltips_prefix} Actual Profit Group %">{$actual_profit_per2|number_format:2}</td>

    	<!-- Average Per Unit (Amt) -->
    	{if $total.$sid.pos.total.qty}
    	    {assign var=avg_cost value=$total.$sid.pos.total.cost/$total.$sid.pos.total.qty}
    	    {assign var=avg_amt value=$total.$sid.pos.total.amt/$total.$sid.pos.total.qty}
    	{else}
    	    {assign var=avg_cost value=0}
    	    {assign var=avg_amt value=0}
    	{/if}
    	<td class="r avg col_total" title="{$tooltips_prefix} Average Selling Cost">{$avg_cost|number_format:2}</td>
    	<td class="r avg col_total"  title="{$tooltips_prefix} Average Selling Amount">{$avg_amt|number_format:2}</td>

    	<!-- Variance (Qty & Amt) -->
    	<!-- Mark up -->
    	<td class="r markup col_total"  title="{$tooltips_prefix} Mark Up Qty">{$total.$sid.variances.total.markup_qty|qty_nf}</td>
    	<td class="r markup col_total"  title="{$tooltips_prefix} Mark Up Amount">{$total.$sid.variances.total.markup_amt|number_format:2}</td>
    	<!-- Mark down -->
    	<td class="r markdown col_total"  title="{$tooltips_prefix} Mark Down Qty">{$total.$sid.variances.total.markdown_qty|qty_nf}</td>
    	<td class="r markdown col_total"  title="{$tooltips_prefix} Mark Down Amount">{$total.$sid.variances.total.markdown_amt|number_format:2}</td>
    	<!-- discount -->
    	<td class="r discount col_total"  title="{$tooltips_prefix} Discount Qty">{$total.$sid.variances.total.disc_qty|qty_nf}</td>
    	<td class="r discount col_total"  title="{$tooltips_prefix} Discount Amount">{$total.$sid.variances.total.disc_amt|number_format:2}</td>
    	<!-- Variance Amt -->
    	{assign var=variance_amt value=$total.$sid.variances.total.markup_amt-$total.$sid.variances.total.markdown_amt-$total.$sid.variances.total.disc_amt}
    	<td class="r variance_amt col_total"  title="{$tooltips_prefix} Variances Amount">{$variance_amt|number_format:2}</td>

    	<!-- Branch Summary -->
    	<!-- Opening Balance & In Stock -->
    	{assign var=open_bal value=$total_open_bal}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Opening Balance & In Stock">{$open_bal|number_format:2}</td>
    	<!-- Sales amount -->
    	{assign var=sales_amt value=$total.$sid.pos.total.amt}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales amount">{$sales_amt|number_format:2}</td>
<!-- Sales Cost -->
    	{assign var=sales_cost value=$total.$sid.pos.total.cost}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales Cost">{$sales_cost|number_format:2}</td>
    	<!-- Profit Amount -->
    	{assign var=profit_amt value=$sales_amt-$sales_cost}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Amount">{$profit_amt|number_format:2}</td>
    	<!-- Profit Percent -->
    	{if $sales_amt}
    		{assign var=profit_per value=$profit_amt/$sales_amt*100}
    	{else}
    	    {assign var=profit_per value=0}
    	{/if}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Profit Percent">{$profit_per|number_format:2}</td>
    	<!-- Closing Stock (Amt) -->
    	{assign var=close_bal value=$open_bal-$sales_cost}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Closing Stock (Amt)">{$close_bal|number_format:2}</td>
    	<!-- % sold on QTY -->
    	{if $total_in}
    		{assign var=sold_qty_per value=$total.$sid.pos.total.qty/$total_in*100}
    	{else}
    	    {assign var=sold_qty_per value=0}
    	{/if}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: % sold on QTY">{$sold_qty_per|number_format:2}</td>
    	<!-- Profit & Sales Weighted average -->
    	{assign var=weight_avg value=$profit_per*$sold_qty_per}
    	<!-- td class="r branch_summary">{$weight_avg|number_format:2}</td -->
    	
    	<!-- Sales - Purchase (Amt) -->
    	{assign var=earn value=$total_earn}
    	<td class="r branch_summary" title="{$tooltips_prefix} Branch Summary: Sales - Purchase (Amt)">{$earn|number_format:2}</td>

		<!-- Summary -->
		<!-- Selling -->
		{assign var=summary_selling value=$total.$sid.pos.total.amt}
		<td class="r summary" title="{$tooltips_prefix} Summary: Selling">{$summary_selling|number_format:2}</td>
		
		<!-- Cost Branch -->
		{assign var=summary_cost_branch value=$total.$sid.pos.total.cost}
		<td class="r summary" title="{$tooltips_prefix} Summary: Cost Branch">{$summary_cost_branch|number_format:2}</td>
		
		<!-- Cost HQ -->
		{assign var=summary_cost_hq value=$total_summary_cost_hq.$dk_m}
		<td class="r summary" title="{$tooltips_prefix} Summary: Cost HQ">{$summary_cost_hq|number_format:2}</td>
		
		<!-- Profit ({$config.arms_currency.symbol}) Branch -->
		{assign var=summary_profit_rm_branch value=$summary_selling-$summary_cost_branch}
		<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) Branch">{$summary_profit_rm_branch|number_format:2}</td>
		
		<!-- Profit ({$config.arms_currency.symbol}) HQ-->
		{assign var=summary_profit_rm_hq value=$summary_selling-$summary_cost_hq}
		<td class="r summary" title="{$tooltips_prefix} Summary: Profit ({$config.arms_currency.symbol}) HQ">{$summary_profit_rm_hq|number_format:2}</td>
		
		<!-- Profit % Branch-->
		{assign var=summary_profit_per_branch value=0}
		{if $summary_selling}
			{assign var=summary_profit_per_branch value=$summary_cost_branch/$summary_selling*100}
		{/if}
		{assign var=temp_val value=100}
		{assign var=summary_profit_per_branch value=$temp_val-$summary_profit_per_branch}
		<td class="r summary" title="{$tooltips_prefix} Summary: Profit % Branch">{$summary_profit_per_branch|number_format:2}</td>
		
		<!-- Profit % HQ -->
		{assign var=summary_profit_per_hq value=0}
		{if $summary_selling}
			{assign var=summary_profit_per_hq value=$summary_cost_hq/$summary_selling*100}
		{/if}
		{assign var=temp_val value=100}
		{assign var=summary_profit_per_hq value=$temp_val-$summary_profit_per_hq}
		<td class="r summary" title="{$tooltips_prefix} Summary: Profit % HQ">{$summary_profit_per_hq|number_format:2}</td>
		
		<!-- Holding Cost -->
		{assign var=summary_holding_cost value=$holding_cost}
		<td class="r summary" title="{$tooltips_prefix} Summary: Holding Cost">{$summary_holding_cost|number_format:2}</td>
		
		<!-- Total Cost Branch -->
		{assign var=summary_total_cost_branch value=$summary_cost_branch+$summary_holding_cost}
		<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost Branch">{$summary_total_cost_branch|number_format:2}</td>
		
		<!-- Total Cost HQ -->
		{assign var=summary_total_cost_hq value=$summary_cost_hq+$summary_holding_cost}
		<td class="r summary" title="{$tooltips_prefix} Summary: Total Cost HQ">{$summary_total_cost_hq|number_format:2}</td>
		
		<!-- Actual Profit ({$config.arms_currency.symbol}) Branch -->
		{assign var=summary_act_profit_rm_branch value=$summary_selling-$summary_total_cost_branch}
		<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) Branch">{$summary_act_profit_rm_branch|number_format:2}</td>
		
		<!-- Actual Profit ({$config.arms_currency.symbol}) HQ -->
		{assign var=summary_act_profit_rm_hq value=$summary_selling-$summary_total_cost_hq}
		<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit ({$config.arms_currency.symbol}) HQ">{$summary_act_profit_rm_hq|number_format:2}</td>
		
		<!-- Actual Profit % Branch -->
		{assign var=summary_act_profit_per_branch value=0}
		{if $summary_selling}
			{assign var=summary_act_profit_per_branch value=$summary_total_cost_branch/$summary_selling*100}
		{/if}
		{assign var=temp_val value=100}
		{assign var=summary_act_profit_per_branch value=$temp_val-$summary_act_profit_per_branch}
		<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % Branch">{$summary_act_profit_per_branch|number_format:2}</td>
		
		<!-- Actual Profit % HQ -->
		{assign var=summary_act_profit_per_hq value=0}
		{if $summary_selling}
			{assign var=summary_act_profit_per_hq value=$summary_total_cost_hq/$summary_selling*100}
		{/if}
		{assign var=temp_val value=100}
		{assign var=summary_act_profit_per_hq value=$temp_val-$summary_act_profit_per_hq}
		<td class="r summary" title="{$tooltips_prefix} Summary: Actual Profit % HQ">{$summary_act_profit_per_hq|number_format:2}</td>

        <!-- Proposal -->
    	<td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
    	<td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        <td class="col_proposal">&nbsp;</td>
        
        <!-- SP1 -->
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        <td class="col_sp1">&nbsp;</td>
        
        <!-- SP2 -->
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
        <td class="col_sp2">&nbsp;</td>
    </tr>
</tfoot>