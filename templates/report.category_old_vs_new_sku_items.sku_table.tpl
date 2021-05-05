{*
5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.
*}

{if !$tb}
	No Data
{else}
	{if !$is_itemise_export}
		<button onclick="export_itemise_info('{$root_id}', '{$is_fresh_market}', '{$direct_under_cat}');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	{capture assign=report_header_html}
	    <tr>
			<th align="left">ARMS Code</th>
			<th align="left">Description</th>
			<th align="center">Item<br />Status</th>
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
		
		{foreach from=$tb key=id item=r name=sku_items}
		    {if $r.data}
				{assign var=curr_item_status value=$r.info.item_status}
				{if $prev_item_status && $prev_item_status != $curr_item_status}
					<tr>
					<th align="right" colspan="3">Sub Total</th>

					{if $fm_only and count($branch_id_list)==1}
						<td>&nbsp;</td>
					{/if}
					{foreach from=$uq_cols key=dt item=d}
						{assign var=fmt value="%0.2f"}
						{if $smarty.request.report_type eq 'qty'}
							{assign var=fmt value="qty"}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.qty}
						{elseif $smarty.request.report_type eq 'amt'}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.amt}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.amt-$tb_total.data.$dt.$prev_item_status.cost}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
							{assign var=fmt value="%0.2f%%"}
							{if $tb_total.data.$dt.$prev_item_status.amt eq 0}
								{assign var=val value=''}
							{else}
								{assign var=gp value=$tb_total.data.$dt.$prev_item_status.amt-$tb_total.data.$dt.$prev_item_status.cost}
								{assign var=val value=$gp/$tb_total.data.$dt.$prev_item_status.amt*100}
							{/if}
						{/if}
						<td class="small" align="right">{$val|value_format:$fmt}</td>
					{/foreach}
					
					<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.qty|value_format:'qty'}</td>
					<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.amt|value_format:'%0.2f'}</td>
					{if $sessioninfo.show_cost}
						<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.cost|value_format:'%0.2f'}</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=gp value=$tb_total.data.sub_total.$prev_item_status.amt-$tb_total.data.sub_total.$prev_item_status.cost}
						<td class="small" align="right">{$gp|value_format:"%0.2f"}</td>
						{if $tb_total.data.sub_total.$prev_item_status.amt>0}
							{assign var=gp_per value=$gp/$tb_total.data.sub_total.$prev_item_status.amt*100}
						{else}
							{assign var=gp_per value=0}
						{/if}
						<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
					{/if}
					</tr>
				{/if}
				<tr>
				    {if $tb_total.data.total.amt}
				    	{assign var=contribution_per value=$r.data.total.amt/$tb_total.data.total.amt*$root_per}
					{else}
					    {assign var=contribution_per value=0}
					{/if}

					<th align="left">{$r.info.sku_item_code}&nbsp;</th>
					<th nowrap align="left">{$r.info.description}&nbsp;</th>
					<th nowrap align="center">{$curr_item_status|capitalize}&nbsp;</th>
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
						{/if}

						{capture assign=tooltip}
							Qty:{$r.data.$dt.qty|value_format:'qty'}  /  Amt:{$r.data.$dt.amt|string_format:'%.2f'}  /  Cost:{$r.data.$dt.cost|string_format:'%.3f'}
						{/capture}
						<td class="small {if $fm_only and $r.data.$dt.cost_indicator and $r.data.$dt.cost_indicator ne 'fresh_market_cost' and $val}use_grn_cost{/if}" align="right" title='{$tooltip}' >{$val|value_format:$fmt}</td>
					{/foreach}

					<td class="small" align="right">{$r.data.total.qty|value_format:'qty'}</td>
					<td class="small" align="right">{$r.data.total.amt|value_format:'%0.2f':'-'}</td>
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
				{if $smarty.foreach.sku_items.last}
					<tr>
					<th align="right" colspan="3">Sub Total</th>

					{if $fm_only and count($branch_id_list)==1}
						<td>&nbsp;</td>
					{/if}
					{foreach from=$uq_cols key=dt item=d}
						{assign var=fmt value="%0.2f"}
						{if $smarty.request.report_type eq 'qty'}
							{assign var=fmt value="qty"}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.qty}
						{elseif $smarty.request.report_type eq 'amt'}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.amt}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
							{assign var=val value=$tb_total.data.$dt.$prev_item_status.amt-$tb_total.data.$dt.$prev_item_status.cost}
						{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
							{assign var=fmt value="%0.2f%%"}
							{if $tb_total.data.$dt.$prev_item_status.amt eq 0}
								{assign var=val value=''}
							{else}
								{assign var=gp value=$tb_total.data.$dt.$prev_item_status.amt-$tb_total.data.$dt.$prev_item_status.cost}
								{assign var=val value=$gp/$tb_total.data.$dt.$prev_item_status.amt*100}
							{/if}
						{/if}
						<td class="small" align="right">{$val|value_format:$fmt}</td>
					{/foreach}
					
					<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.qty|value_format:'qty'}</td>
					<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.amt|value_format:'%0.2f'}</td>
					{if $sessioninfo.show_cost}
						<td class="small" align="right">{$tb_total.data.sub_total.$prev_item_status.cost|value_format:'%0.2f'}</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=gp value=$tb_total.data.sub_total.$prev_item_status.amt-$tb_total.data.sub_total.$prev_item_status.cost}
						<td class="small" align="right">{$gp|value_format:"%0.2f"}</td>
						{if $tb_total.data.sub_total.$prev_item_status.amt>0}
							{assign var=gp_per value=$gp/$tb_total.data.sub_total.$prev_item_status.amt*100}
						{else}
							{assign var=gp_per value=0}
						{/if}
						<td class="small" align="right">{$gp_per|value_format:'%0.2f%%':'-'}</td>
					{/if}
					</tr>
				{/if}
			{/if}
			{assign var=prev_item_status value=$r.info.item_status}
		{/foreach}
		
		<tr class="sortbottom">
			<th align="right" colspan="3">Total</th>

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
				{/if}
				<td class="small" align="right">{$val|value_format:$fmt}</td>
			{/foreach}

			<td class="small" align="right">{$tb_total.data.total.qty|value_format:'qty'}</td>
			<td class="small" align="right">{$tb_total.data.total.amt|value_format:'%0.2f'}</td>
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
