{*
10/29/2010 4:19:02 PM Alex
- add show cost and show report gp privilege

11/29/2010 12:57:24 PM Andy
- Remove all fresh market items from this report.
- Show a new row of fresh market amount if "sales amount" is choose.

12/6/2010 9:44:36 AM Andy
- Fix sku sorting function not working problem.

2/15/2011 11:49:52 AM Andy
- Reconstruct daily category sales report to show fresh market data.

7/6/2011 5:18:49 PM Andy
- Fix un-category sales missing from report.
- Fix item direct under category cannot show sku details.

10/14/2011 11:53:11 AM Alex
- change qty use value_format:'qty'

8/15/2012 11:30:43 AM Fithri
- Add 'Artno' column in Daily Category Sales Report for consignment customer

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

4:56 PM 11/27/2014 Andy
- Enhance to show Service Charges and GST.
- Enhance the report to able to show by GST Amount or Sales Amount Included GST.

8/16/2016 11:16 AM Andy
- Enhanced to show MCode in sku table.

9/20/2016 10:42 AM Qiu Ying
- Enhanced to show artno for daily category sales report

1/18/2017 10:40 AM Andy
- Fixed failed to Export DO Items.

1/3/2018 9:22 AM Justin
- Bug fixed on the tooltips showing HTML tag.

6/10/2019 4:39 PM William
- Added new column "Gross Amount" and "Discount". 

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

7/17/2020 10:40 AM William
- Remove character "%" on the row GP(%).

10/15/2020 9:39 AM William
- Change GST word to Tax.
*}

{if !$tb}
	No Data
{else}
	{if !$is_itemise_export}
		<button onclick="export_itemise_info('{$itemise_type}','{$root_id}', '{$is_fresh_market}', '{$direct_under_cat}');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	{capture assign=report_header_html}
	    <tr>
			<th align="left">ARMS Code</th>
			<th align="left">MCode</th>
			<th align="left">Artno</th>
			<th align="left">Description</th>
			{if $fm_only and count($branch_id_list)==1}
				<th>Last FM<br />Stock Take</th>
			{/if}
			
			{assign var=lasty value=0}
			{assign var=lastm value=0}
			{foreach from=$uq_cols key=dt item=d}
			    <th>
				{if $view_by_monthly}
					{if $lasty ne $d.y}
						<span class="small">{$d.y}</span><br />
						{assign var=lasty value=$d.y}
					{/if}
					{$d.m|str_month|truncate:3:''}
				{else}
					{if $lastm ne $d.m or $lasty ne $d.y}
					    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
					    {assign var=lastm value=$d.m}
						{assign var=lasty value=$d.y}
					{/if}
					{$d.d}
				{/if}
				</th>
			{/foreach}
			<th>Total<br />Qty</th>
			<th>Gross Amount</th>
			<th>Discount</th>
			<th>Amount</th>
			
			{if $config.enable_gst || $config.enable_tax}
				{* GST *}
				<th>Tax</th>
				
				{* Amt Inc GST *}
				<th>Amt Inc Tax</th>
			{/if}
			
			
			{if $sessioninfo.show_cost}
				<th>Cost</th>
			{/if}
			{if $sessioninfo.show_report_gp}
				<th>GP</th>
				<th>GP(%)</th>
			{/if}
			<th>Contrib<br />(%)</th>
		</tr>
	{/capture}
	
	{if $fm_only and count($branch_id_list)==1}
		<span class="use_grn_cost">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		GRN / Master Cost (not fresh market cost by stock take)
	{/if}
	
    <table class="sortable tb" cellspacing="0" cellpadding="2" border="0" id="tbl_sku_normal_items">
        {$report_header_html}
		
		{foreach from=$tb key=id item=r}
		    {if $r.data}
				<tr>
				    {if $tb_total.data.total.amt}
				    	{assign var=contribution_per value=$r.data.total.amt/$tb_total.data.total.amt*$root_per}
					{else}
					    {assign var=contribution_per value=0}
					{/if}

					<th align="left">{$r.info.sku_item_code}&nbsp;</th>
					<th align="left">{$r.info.mcode}&nbsp;</th>
					<th align="left">{$r.info.artno}&nbsp;</th>
					<th nowrap align="left">{$r.info.description}&nbsp;</th>
					{if $fm_only and count($branch_id_list)==1}
						<td align="center">{$r.info.last_fm_sc_date|default:'-'}</td>
					{/if}
					
					{foreach from=$uq_cols key=dt item=d}
					    {assign var=fmt value="%0.2f"}
						{if $smarty.request.report_type eq 'qty'}   <!-- show by qty-->
							{assign var=fmt value="qty"}
							{assign var=val value=$r.data.$dt.qty}
						{elseif $smarty.request.report_type eq 'amt'}   <!-- show by amt-->
							{assign var=val value=$r.data.$dt.amt}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'} <!-- show by gp -->
							{assign var=val value=$r.data.$dt.amt-$r.data.$dt.cost}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'} <!-- show by gp pct -->
							{assign var=fmt value="%0.2f"}
							{if $r.data.$dt.amt eq 0}
								{assign var=val value=''}
							{else}
							    {assign var=gp value=$r.data.$dt.amt-$r.data.$dt.cost}
							    {assign var=val value=$gp/$r.data.$dt.amt*100}
							{/if}
						{elseif $smarty.request.report_type eq 'gst_amt'}
							{assign var=val value=$r.data.$dt.tax_amount}
						{elseif $smarty.request.report_type eq 'amt_inc_gst'}
							{assign var=val value=$r.data.$dt.amt_inc_gst}
						{/if}

						{capture assign=tooltip}
							Qty:{$r.data.$dt.qty|qty_nf}  /  Amt:{$r.data.$dt.amt|string_format:'%.2f'}  /  Cost:{$r.data.$dt.cost|string_format:'%.3f'}  /  Tax:{$r.data.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$r.data.$dt.amt_inc_gst|string_format:'%.2f'}
						{/capture}
						<td class="small {if $fm_only and $r.data.$dt.cost_indicator and $r.data.$dt.cost_indicator ne 'fresh_market_cost' and $val}use_grn_cost{/if}" align="right" title='{$tooltip}' >{$val|value_format:$fmt}</td>
					{/foreach}
					<td class="small" align="right">{$r.data.total.qty|value_format:'qty'}</td>
					<td class="small" align="right">{$r.data.total.gross_amt|value_format:'%0.2f':'-'}</td>
					<td class="small" align="right">{$r.data.total.discount|value_format:'%0.2f':'-'}</td>
					<td class="small" align="right">{$r.data.total.amt|value_format:'%0.2f':'-'}</td>
					
					{if $config.enable_gst || $config.enable_tax}
						{* GST *}
						<td class="small" align="right">{$r.data.total.tax_amount|value_format:'%0.2f':'-'}</td>
						
						{* Amt Inc GST *}
						<td class="small" align="right">{$r.data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
					{/if}
					
					{if $sessioninfo.show_cost}
						<td class="small" align="right">{$r.data.total.cost|value_format:'%0.2f':'-'}</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=gp value=$r.data.total.amt-$r.data.total.cost}
						<td class="small" align="right">{$gp|value_format:'%0.2f':'-'}</td>

						{if $r.data.total.amt>0}
						    {assign var=gp_per value=$gp/$r.data.total.amt*100}
						{else}
						    {assign var=gp_per value=0}
						{/if}
						<td class="small" align="right">{$gp_per|value_format:'%0.2f':'-'}</td>
					{/if}

					{if $tb_total.data.total.amt}
						<td class="small" align="right">{$contribution_per|value_format:'%0.2f':'-'}</td>
					{else}
			            <td>&nbsp;</td>
					{/if}
				</tr>
			{/if}
		{/foreach}
		
		<tr class="sortbottom" style="background-color:#dcdcdc;">
			<td colspan="2">&nbsp;</td>
			<td>&nbsp;</td>
			<th align="right">Total</th>

			{if $fm_only and count($branch_id_list)==1}
				<td>&nbsp;</td>
			{/if}
			{foreach from=$uq_cols key=dt item=d}
				{assign var=fmt value="%0.2f"}
				{if $smarty.request.report_type eq 'qty'}
					{assign var=fmt value="qty"}
					{assign var=val value=$tb_total.data.$dt.qty}
				{elseif $smarty.request.report_type eq 'amt'}
					{assign var=val value=$tb_total.data.$dt.amt}
				{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
					{assign var=val value=$tb_total.data.$dt.amt-$tb_total.data.$dt.cost}
				{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
					{assign var=fmt value="%0.2f"}
					{if $tb_total.data.$dt.amt eq 0}
						{assign var=val value=''}
					{else}
					    {assign var=gp value=$tb_total.data.$dt.amt-$tb_total.data.$dt.cost}
						{assign var=val value=$gp/$tb_total.data.$dt.amt*100}
					{/if}
				{elseif $smarty.request.report_type eq 'gst_amt'}
					{assign var=val value=$tb_total.data.$dt.tax_amount}
				{elseif $smarty.request.report_type eq 'amt_inc_gst'}
					{assign var=val value=$tb_total.data.$dt.amt_inc_gst}
				{/if}
				
				{capture assign=tooltip}
					Qty:{$tb_total.data.$dt.qty|qty_nf}  /  Amt:{$tb_total.data.$dt.amt|string_format:'%.2f'}  /  Cost:{$tb_total.data.$dt.cost|string_format:'%.3f'}  /  Tax:{$tb_total.data.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$tb_total.data.$dt.amt_inc_gst|string_format:'%.2f'}
				{/capture}
						
				<td class="small" align="right" title='{$tooltip}'>{$val|value_format:$fmt}</td>
			{/foreach}
			<td class="small" align="right">{$tb_total.data.total.qty|value_format:'qty'}</td>
			<td class="small" align="right">{$tb_total.data.total.gross_amt|value_format:'%0.2f'}</td>
			<td class="small" align="right">{$tb_total.data.total.discount|value_format:'%0.2f'}</td>
			<td class="small" align="right">{$tb_total.data.total.amt|value_format:'%0.2f'}</td>
			
			{if $config.enable_gst || $config.enable_tax}
				{* GST *}
				<td class="small" align="right">{$tb_total.data.total.tax_amount|value_format:'%0.2f'}</td>
				
				{* Amt Inc GST *}
				<td class="small" align="right">{$tb_total.data.total.amt_inc_gst|value_format:'%0.2f'}</td>
			{/if}
			
			{if $sessioninfo.show_cost}
				<td class="small" align="right">{$tb_total.data.total.cost|value_format:'%0.2f'}</td>
			{/if}
			{if $sessioninfo.show_report_gp}
				{assign var=gp value=$tb_total.data.total.amt-$tb_total.data.total.cost}
				<td class="small" align="right">{$gp|value_format:"%0.2f"}</td>
				{if $tb_total.data.total.amt>0}
				    {assign var=gp_per value=$gp/$tb_total.data.total.amt*100}
				{else}
				    {assign var=gp_per value=0}
				{/if}
				<td class="small" align="right">{$gp_per|value_format:'%0.2f':'-'}</td>
			{/if}
		</tr>
	</table>
{/if}

<script>
{literal}
sortables_init();   // initial sortable table
{/literal}
</script>
