{*
4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

4/24/2018 3:12 PM Justin
- Enhanced to show foreign currency.
*}

{if !$tb}
	No Data
{else}
	{*if !$is_itemise_export}
		<button onclick="export_itemise_info('{$root_id}');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if*}
	{capture assign=report_header_html}
	    <tr>
			<th align="left" rowspan="2">ARMS Code</th>
			<th align="left" rowspan="2">Description</th>
			
			{assign var=lasty value=0}
			{assign var=lastm value=0}
			{foreach from=$uq_cols key=dt item=d}
			    <th valign="bottom" colspan="2">
					{if $lastm ne $d.m or $lasty ne $d.y}
						<span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
						{assign var=lastm value=$d.m}
						{assign var=lasty value=$d.y}
					{/if}
					{$d.d}
				</th>
			{/foreach}
			<th rowspan="2">T.Sell</th>
			{if $is_under_gst}
				<th rowspan="2">T.GST</th>
				<th rowspan="2">T.Sell<br />Inc. GST</th>
			{/if}
			{if $sessioninfo.show_cost}
				<th rowspan="2">T.Cost</th>
			{/if}
			{if $sessioninfo.show_report_gp}
				<th rowspan="2">GP</th>
				<th rowspan="2">GP(%)</th>
			{/if}
		</tr>
		<tr>
			{foreach from=$uq_cols key=dt item=d}
				<th>T.Sell</th>
				{if $sessioninfo.show_cost}
					<th>T.Cost</th>
				{/if}
			{/foreach}
		</tr>
	{/capture}
	
    <table class="sortable tb" cellspacing="0" cellpadding="2" border="0" id="tbl_sku_normal_items">
        {$report_header_html}
		
		{foreach from=$tb key=id item=r}
		    {if $r.data}
				<tr>
					<th align="left">{$r.info.sku_item_code}&nbsp;</th>
					<th nowrap align="left">{$r.info.description}&nbsp;</th>
					
					{foreach from=$uq_cols key=dt item=d}
						{capture assign=tooltip}
							Qty:{$r.data.$dt.qty|qty_nf}  /  Selling:{$r.data.$dt.sell|string_format:'%.2f'}  /  Cost:{$r.data.$dt.cost|string_format:'%.2f'}
						{/capture}
						<td class="small" align="right" title='{$tooltip}' >{$r.data.$dt.sell|number_format:2}</td>
						{if $sessioninfo.show_cost}
							<td class="small {if $r.data.$dt.have_fc}converted_base_amt{/if}" align="right" title='{$tooltip}' >{$r.data.$dt.cost|number_format:2}{if $r.data.$dt.have_fc}*{/if}</td>
						{/if}
					{/foreach}

					<td class="small" align="right">{$r.data.total.sell|number_format:2}</td>
					{if $is_under_gst}
						{assign var=row_gst value=$r.total.gst_sell-$r.total.sell}
						<td class="small" align="right">{$row_gst|number_format:2}</td>
						<td class="small" align="right">{$r.total.gst_sell|number_format:2}</td>
					{/if}
					{if $sessioninfo.show_cost}
						<td class="small {if $r.data.total.have_fc}converted_base_amt{/if}" align="right">{$r.data.total.cost|number_format:2}{if $r.data.total.have_fc}*{/if}</td>
					{/if}
					{if $sessioninfo.show_report_gp}
						{assign var=gp value=$r.data.total.sell-$r.data.total.cost}
						<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp|number_format:2}</td>

						{if $r.data.total.sell>0}
						    {assign var=gp_per value=$gp/$r.data.total.sell*100}
						{else}
						    {assign var=gp_per value=0}
						{/if}
						<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp_per|number_format:2}</td>
					{/if}
				</tr>
			{/if}
		{/foreach}
		
		<tr class="sortbottom">
			<td>&nbsp;</td><th align="right">Total</th>
			{foreach from=$uq_cols key=dt item=d}
				<td class="small" align="right">{$tb_total.data.$dt.sell|number_format:2}</td>
				{if $sessioninfo.show_cost}
					<td class="small {if $tb_total.data.$dt.have_fc}converted_base_amt{/if}" align="right">{$tb_total.data.$dt.cost|number_format:2}{if $tb_total.data.$dt.have_fc}*{/if}</td>
				{/if}
			{/foreach}

			<td class="small" align="right">{$tb_total.data.total.sell|number_format:2}</td>
			{if $is_under_gst}
				{assign var=ttl_gst value=$tb_total.data.total.gst_sell-$tb_total.data.total.sell}
				<td class="small" align="right">{$ttl_gst|number_format:2}</td>
				<td class="small" align="right">{$tb_total.data.total.gst_sell|number_format:2}</td>
			{/if}
			{if $sessioninfo.show_cost}
				<td class="small {if $tb_total.data.total.have_fc}converted_base_amt{/if}" align="right">{$tb_total.data.total.cost|number_format:2}{if $tb_total.data.total.have_fc}*{/if}</td>
			{/if}
			{if $sessioninfo.show_report_gp}
				{assign var=gp value=$tb_total.data.total.sell-$tb_total.data.total.cost}
				<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp|number_format:2}</td>
				{if $tb_total.data.total.sell>0}
				    {assign var=gp_per value=$gp/$tb_total.data.total.sell*100}
				{else}
				    {assign var=gp_per value=0}
				{/if}
				<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp_per|number_format:2}</td>
			{/if}
		</tr>
	</table>
{/if}

<script>
{literal}
sortables_init();   // initial sortable table
{/literal}
</script>
