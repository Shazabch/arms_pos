{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

5/27/2015 10:30 AM Justin
- Enhanced to allow user can filter and show report by GST amount.

9/29/2016 17:44 Qiu Ying
- Enhanced to add Art No for Daily Category from Cash/Credit Sales Report

10/16/2020 10:54 AM William
- Enhanced to add tax checking.
*}

{if !$tb}
	No Data
{else}
	{if !$is_itemise_export}
		<button onclick="export_itemise_info('{$root_id}');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	{capture assign=report_header_html}
	    <tr>
			<th align="left">ARMS Code</th>
			<th align="left">Art No</th>
			<th align="left">Description</th>
			{if $fm_only and count($branch_id_list)==1}
				<th>Last FM<br />Stock Take</th>
			{/if}
			
			{assign var=lasty value=0}
			{assign var=lastm value=0}
			{foreach from=$uq_cols key=dt item=d}
			    <th valign="bottom">
				{if $view_by_monthly}
					{if $lasty ne $d.y}
						<span class="small">{$d.y}</span><br />
						{assign var=lasty value=$d.y}
					{/if}
					{$d.m|str_month|truncate:3:''}
					</th>
				{else}
					{if $lastm ne $d.m or $lasty ne $d.y}
					    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
					    {assign var=lastm value=$d.m}
						{assign var=lasty value=$d.y}
					{/if}
					{$d.d}
					</th>
				{/if}
			{/foreach}
			<th>Total<br />Qty</th>
			<th>Amount</th>
			
			{if $config.enable_gst || $config.enable_tax}
				<th>Tax</th>
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
					<th nowrap align="left">{$r.info.artno}&nbsp;</th>
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
							{assign var=fmt value="%0.2f%%"}
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
							Qty:{$r.data.$dt.qty|value_format:'qty'}  /  Amt:{$r.data.$dt.amt|string_format:'%.2f'}  /  Cost:{$r.data.$dt.cost|string_format:'%.3f'}
						{/capture}
						<td class="small {if $fm_only and $r.data.$dt.cost_indicator and $r.data.$dt.cost_indicator ne 'fresh_market_cost' and $val}use_grn_cost{/if}" align="right" title='{$tooltip}' >{$val|value_format:$fmt}</td>
					{/foreach}

					<td class="small" align="right">{$r.data.total.qty|value_format:'qty'}</td>
					<td class="small" align="right">{$r.data.total.amt|value_format:'%0.2f':'-'}</td>
					
					{if $config.enable_gst || $config.enable_tax}
						{* GST *}
						<td class="small" align="right">{$r.data.total.tax_amount|value_format:'%0.2f'}</td>
						
						{* Amt Inc GST *}
						<td class="small" align="right">{$r.data.total.amt_inc_gst|value_format:'%0.2f'}</td>
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
						<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
					{/if}

					{if $tb_total.data.total.amt}
						<td class="small" align="right">{$contribution_per|value_format:'%0.2f%%':'-'}</td>
					{else}
			            <td>&nbsp;</td>
					{/if}
				</tr>
			{/if}
		{/foreach}
		
		<tr class="sortbottom">
			<td>&nbsp;</td><td>&nbsp;</td><th align="right">Total</th>

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
					{assign var=fmt value="%0.2f%%"}
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
				<td class="small" align="right">{$val|value_format:$fmt}</td>
			{/foreach}

			<td class="small" align="right">{$tb_total.data.total.qty|value_format:'qty'}</td>
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
				<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
			{/if}
		</tr>
	</table>
{/if}

<script>
{literal}
sortables_init();   // initial sortable table
{/literal}
</script>
