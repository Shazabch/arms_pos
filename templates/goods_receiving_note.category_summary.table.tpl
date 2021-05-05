{*
4/24/2018 1:52 PM Justin
- Enhanced to show foreign currency.
*}

{foreach from=$tb key=id item=r}
	<tbody id="r{$id}">
		<tr>
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
					<!--img onclick="expand_sub('{$id}','{$smarty.request.indent+1}',this,'{$contribution_per}');" src="/ui/expand.gif" /-->
					<a href="javascript:void(show_sub('{$id}'))">
				{/if}
				{$r.description}
				{if !$no_header_footer} </a>{/if}

			{else}
			    {$r.description}
			{/if}
			</th>

			{foreach from=$uq_cols key=dt item=d}
				{capture assign=tooltip}
					Qty:{$r.data.$dt.qty|qty_nf}  /  Selling:{$r.data.$dt.sell|string_format:'%.2f'}  /  Cost:{$r.data.$dt.cost|string_format:'%.2f'}
				{/capture}
				<td class="small" align="right" title='{$tooltip}'>{$r.data.$dt.sell|number_format:2}</td>
				{if $sessioninfo.show_cost}
					<td class="small {if $r.data.$dt.have_fc}converted_base_amt{/if}" align="right" title="{$tooltip}">{$r.data.$dt.cost|number_format:2}{if $r.data.$dt.have_fc}*{/if}</td>
				{/if}
			{/foreach}

			<td class="small" align="right">{$r.total.sell|number_format:2}</td>
			{if $is_under_gst}
				{assign var=row_gst value=$r.total.gst_sell-$r.total.sell}
				<td class="small" align="right">{$row_gst|number_format:2}</td>
				<td class="small" align="right">{$r.total.gst_sell|number_format:2}</td>
			{/if}
			{if $sessioninfo.show_cost}
				<td class="small {if $r.total.have_fc}converted_base_amt{/if}" align="right">{$r.total.cost|number_format:2}{if $r.total.have_fc}*{/if}</td>
			{/if}
			{if $sessioninfo.show_report_gp}
				{assign var=gp value=$r.total.sell-$r.total.cost}
				<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp|number_format:2}</td>

				{if $r.total.sell>0}
				    {assign var=gp_per value=$gp/$r.total.sell*100}
				{else}
				    {assign var=gp_per value=0}
				{/if}
				<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp_per|number_format:2}</td>
			{/if}
		</tr>
	</tbody>
{/foreach}
