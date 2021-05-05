{*
11/2/2010 1:05:22 PM Alex
- remove show cost privilege checking

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

4/21/2015 3:11 PM Justin
- Enhanced to have GST information.

07/12/2016 13:30 Edwin
- Changed to new module
*}
{if $tb}
	<h4>SKU under category {$category_name} (with sales)</h4>
	<table class="tb" id=_t cellspacing=0 cellpadding=2 border=0>
		<tr>
			<th rowspan=2 align=left>ARMS Code</th>
			<th rowspan=2 align=left>Description</th>
			{foreach from=$uq_cols key=dt item=d}
				<th valign=bottom colspan="2">
					{if $d.m eq 0 && $d.y eq 0}
						<span class=small>Not Returned</span><br />
					{else}
						{if $lastm ne $d.m || $lasty ne $d.y}
							<span class=small>{$d.m}/{$d.y}</span><br />
							{assign var=lastm value=$d.m}
							{assign var=lasty value=$d.y}
						{/if}
						{$d.d}
					{/if}
				</th>
			{/foreach}
			{if $is_under_gst}
				{assign var=colspan value="4"}
			{else}
				{assign var=colspan value="2"}
			{/if}
			<th colspan="{$colspan}">Total</th>
		</tr>
		<tr>
			{section name=i loop=$uq_cols}
				<th>Qty</th>
				<th>T.Amt</th>
			{/section}
			<th>Qty</th>
			<th>T.Amt</th>
			{if $is_under_gst}
				<th>T.GST</th>
				<th>T.Amt<br />Incl. GST</th>
			{/if}
		</tr>
		{foreach from=$tb key=id item=v}
			<tr>
				<th align=left>{$id|ifzero:"&nbsp;"}</th>
				<th nowrap align=left>{$v.description}&nbsp;</th>
			{foreach from=$uq_cols key=dt item=dummy}
				<td class=small align=right>{$v.$dt.qty|qty_nf|ifzero:"&nbsp;"}</td>
				<td class=small align=right>{$v.$dt.amt|number_format:2|ifzero:"&nbsp;"}</td>
			{/foreach}
			<td class=small align=right>{$v.total.qty|qty_nf|ifzero:"&nbsp;"}</td>
			<td class=small align=right>{$v.total.amt|number_format:2|ifzero:"&nbsp;"}</td>
			{if $is_under_gst}
				<td class=small align=right>{$v.total.gst|number_format:2|ifzero:"&nbsp;"}</td>
				<td class=small align=right>{$v.total.gst_amt|number_format:2|ifzero:"&nbsp;"}</td>
			{/if}
			</tr>
		{/foreach}
	
	<tr class=sortbottom>
		<td>&nbsp;</td>
		<th align="right">Total</th>
		{foreach from=$uq_cols key=dt item=dummy}
			<td class=small align=right>{$total_day.$dt.qty|qty_nf|ifzero:"&nbsp;"}</td>
			<td class=small align=right>{$total_day.$dt.amt|number_format:2|ifzero:"&nbsp;"}</td>
		{/foreach}
		<td class=small align=right>{$final_total.qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td class=small align=right>{$final_total.amt|number_format:2|ifzero:"&nbsp;"}</td>
		{if $is_under_gst}
			<td class=small align=right>{$final_total.gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td class=small align=right>{$final_total.gst_amt|number_format:2|ifzero:"&nbsp;"}</td>
		{/if}
	</tr>
	</table>
{else}
	<ul><li>No data</li></ul>
{/if}