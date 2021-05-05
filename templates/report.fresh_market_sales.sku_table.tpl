{*
2/11/2011 3:58:11 PM Andy
- Add color for those sales without stock take.
*}
{if !$sku_items_data}
	** No Data **
{else}
    {capture assign=report_header_html}
	    <tr>
			<th align="left">ARMS Code</th>
			<th align="left">Description</th>

			{assign var=lasty value=0}
			{assign var=lastm value=0}
			{foreach from=$date_cols key=dt item=d}
			    <th valign="bottom">
					{if $lastm ne $d.m or $lasty ne $d.y}
					    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
					    {assign var=lastm value=$d.m}
						{assign var=lasty value=$d.y}
					{/if}
					{$d.d}
				</th>
			{/foreach}
			<th>Amount</th>
			{if $sessioninfo.show_cost}
				<th>Cost</th>
			{/if}
			{if $sessioninfo.show_report_gp}
				<th>GP</th>
				<th>GP(%)</th>
			{/if}
			<th>Contrib<br>(%)</th>
		</tr>
	{/capture}
	
	<table class="sortable tb" id="tbl_sku_items" cellspacing="0" cellpadding="2" border="0">
		{$report_header_html}
		
		{foreach from=$sku_items_data key=sid item=r}
		    <tr>
		        <td><b>{$r.sku_item_code}</b></td>
				<td nowrap><b>{$r.description}&nbsp;</b></td>

				{assign var=row_amt value=0}
				{assign var=row_cost value=0}
				{foreach from=$date_cols key=dt item=d}
				    {assign var=row_amt value=$row_amt+$r.pos.$dt.amt|round2}
				    {assign var=row_cost value=$row_cost+$r.pos.$dt.cost|round2}
				    <td align="right" class="{if isset($r.pos.$dt) and !$r.pos.$dt.got_sc}col_no_sc{/if} {if $r.pos.$dt.amt<0}negative{/if}">{$r.pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/foreach}
				
				<td class="r {if $row_amt<0}negative{/if}">{$row_amt|number_format:2|ifzero:'&nbsp;'}</td>
				
				<!-- Cost -->
				{if $sessioninfo.show_cost}
					<td class="r {if $row_cost<0}negative{/if}">{$row_cost|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				
				{if $sessioninfo.show_report_gp}
					<!-- GP -->
				    {assign var=row_gp value=$row_amt-$row_cost}
				    <td class="r {if $row_gp<0}negative{/if}">{$row_gp|number_format:2|ifzero:'&nbsp;'}</td>
				    
				    <!-- GP %-->
				    {assign var=row_gp_per value=0}
					{if $row_amt}
					    {assign var=row_gp_per value=$row_gp/$row_amt*100}
					{/if}
					<td class="r {if $row_gp_per<0}negative{/if}">{$row_gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
				{/if}
				
				<!-- calculate contribute % -->
			    {assign var=contrib_per value=0}
				{if $sku_items_total_data.total.pos.total.amt}
				    {assign var=contrib_per value=$row_amt/$sku_items_total_data.total.pos.total.amt*100}
				{/if}
				<td class="r">{$contrib_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
		    </tr>
		{/foreach}
		
		<tfoot>
		    <tr class="sortbottom">
		        <td colspan="2" class="r"><b>Total</b></td>
		        
		        {assign var=row_amt value=0}
				{assign var=row_cost value=0}
		        {foreach from=$date_cols key=dt item=d}
		            <td class="r {if $col_data<0}negative{/if}">{$sku_items_total_data.total.pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
		            
		            <!-- amt -->
		            {assign var=row_amt value=$row_amt+$sku_items_total_data.total.pos.$dt.amt|round2}
				    
				    <!-- cost -->
				    {assign var=row_cost value=$row_cost+$sku_items_total_data.total.pos.$dt.cost|round2}
		        {/foreach}
		        
		        <td class="r {if $row_amt<0}negative{/if}">{$row_amt|number_format:2|ifzero:'&nbsp;'}</td>
		        
		        <!-- Cost -->
				{if $sessioninfo.show_cost}
				    <td class="r {if $row_cost<0}negative{/if}">{$row_cost|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				
				{if $sessioninfo.show_report_gp}
					<!-- GP -->
				    {assign var=row_gp value=$row_amt-$row_cost}
				    <td class="r {if $row_gp<0}negative{/if}">{$row_gp|num_format:2|ifzero:'&nbsp;'}</td>

				    <!-- GP %-->
				    {assign var=row_gp_per value=0}
					{if $row_amt}
					    {assign var=row_gp_per value=$row_gp/$row_amt*100}
					{/if}
					<td class="r {if $row_gp_per<0}negative{/if}">{$row_gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
				{/if}
		    </tr>
		</tfoot>
	</table>
{/if}
<script>
{literal}
	sortables_init();   // initial sortable table
{/literal}
</script>
