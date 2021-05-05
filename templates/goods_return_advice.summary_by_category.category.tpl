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

5/8/2018 1:16 PM Justin
- Enhanced to have foreign currency feature.
*}

<p><font class=small>Click on a sub-category for further detail. Click <img src=/ui/icons/table.png align=absmiddle>  to display SKU in the category.</font></p>

<table class="tb" id=_t cellspacing=0 cellpadding=2 border=0>
	<tr>
		<th rowspan=2 align=left>&nbsp;</th>
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
			<th>T.Qty</th>
			<th>T.Amt</th>
		{/section}
		<th>T.Qty</th>
		<th>T.Amt</th>
		{if $is_under_gst}
			<th>T.GST</th>
			<th>T.Amt<br />Incl. GST</th>
		{/if}
	</tr>
	{foreach from=$data key=id item=v}
		<tr>
			<th nowrap align=left>
				<img src="/ui/icons/table.png" align=absmiddle onclick="GRA_SUMMARY_BY_CATEGORY.show_sku('{$id}')" title="Show SKU">
				{if $v.have_subcat}
					<a href="javascript:void(GRA_SUMMARY_BY_CATEGORY.show_sub('{$id}'))">{$v.description}</a>
				{else}
				    {$v.description}
				{/if}
			</th>
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

{if $extotal}
	<tr class=sortbottom>
		<th align=right nowrap>Total of items not in ARMS SKU</th>
		{foreach from=$uq_cols key=dt item=dummy}
		    <td class=small align=right>{$extotal.$dt.extra_qty|qty_nf|ifzero:"&nbsp;"}</td>
	    	<td class=small align=right>{$extotal.$dt.extra_amt|number_format:2|ifzero:"&nbsp;"}</td>
		{/foreach}
		<td class=small align=right>{$tb_extotal.extra_qty|qty_nf|ifzero:"&nbsp;"}</td>
		<td class=small align=right>{$tb_extotal.extra_amt|number_format:2|ifzero:"&nbsp;"}</td>
		{if $is_under_gst}
			<td class=small align=right>{$tb_extotal.extra_gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td class=small align=right>{$tb_extotal.extra_gst_amt|number_format:2|ifzero:"&nbsp;"}</td>
		{/if}
	</tr>
{/if}

<tr class=sortbottom><th align=right>Total</td>
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
<br>
<div id=show_sku></div>
