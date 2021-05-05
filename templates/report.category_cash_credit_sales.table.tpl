{*
10/14/2011 3:20:26 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5/27/2015 11:13 AM Justin
- Enhanced to can show GST amount.

5/27/2015 10:30 AM Justin
- Enhanced to allow user can filter and show report by GST amount.

10/15/2020 8:18 PM William
- Enhanced to add new tax checking.
*}

{foreach from=$tb key=id item=r}
	<tbody id="r{$id}">
		<tr>
		    {if $tb_total.data.total.amt}
		    	{assign var=contribution_per value=$r.total.amt/$tb_total.data.total.amt*$root_per}
			{else}
			    {assign var=contribution_per value=0}
			{/if}

			<th nowrap align="left">
			{if !$no_header_footer}
			    {if $id or $root_id}
					<img src="/ui/icons/table.png" align="absmiddle" onclick="show_sku('{$id|default:$root_id}', this);" title="Show SKU" />
			    {else}
					<img src="/ui/pixel.gif" width="16" align="absmiddle" />
			    {/if}
			{/if}
			{if $smarty.request.ajax}
				<img src="/ui/pixel.gif" width="{$smarty.request.indent*16}" height="1">
			{/if}
			{if $r.have_subcat}
				{if !$no_header_footer}
					<img onclick="expand_sub('{$id}','{$smarty.request.indent+1}',this,'{$contribution_per}');" src="/ui/expand.gif" />
					<a href="javascript:void(show_sub('{$id}'))">
				{/if}
				{$r.description}
				{if !$no_header_footer} </a>{/if}

			{else}
			    {$r.description}
			{/if}
			</th>

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
				<td class="small" align="right" title='{$tooltip}'>{$val|value_format:$fmt}</td>
			{/foreach}

			<td class="small" align="right">{$r.total.qty|value_format:'qty'}</td>
			<td class="small" align="right">{$r.total.amt|value_format:'%0.2f':'-'}</td>
			
			{if $config.enable_gst || $config.enable_tax}
				{* GST *}
				<td class="small" align="right">{$r.total.tax_amount|value_format:'%0.2f'}</td>
				
				{* Amt Inc GST *}
				<td class="small" align="right">{$r.total.amt_inc_gst|value_format:'%0.2f'}</td>
			{/if}
			
			{if $sessioninfo.show_report_gp}
				<td class="small" align="right">{$r.total.cost|value_format:'%0.2f':'-'}</td>
				{assign var=gp value=$r.total.amt-$r.total.cost}
				<td class="small" align="right">{$gp|value_format:'%0.2f':'-'}</td>

				{if $r.total.amt>0}
				    {assign var=gp_per value=$gp/$r.total.amt*100}
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
	</tbody>
{/foreach}
