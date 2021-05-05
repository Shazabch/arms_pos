{*
10/7/2011 12:42:39 PM Andy
- Show branch description.

3/11/2015 5:37 PM Andy
- Enhanced to show (FOC) when item foc is tick or price is zero.
*}

<table width="100%" class="report_table">
	{assign var=rowspan value=1}
	{if $show_price_type}
		{assign var=rowspan value=$rowspan+1}
	{/if}
	<tr class="header">
		<th rowspan="{$rowspan}">Branch</th>
		<th rowspan="{$rowspan}">Selling Price</th>
		{if $show_price_type}
			<th rowspan="{$rowspan}">Price Type</th>
			<th colspan="{count var=$price_type_list}">Discount Rate</th>
		{/if}
	</tr>
	{if $show_price_type}
		<tr class="header">
			{foreach from=$price_type_list item=pt}
				<th>{$pt.code}</th>
			{/foreach}
		</tr>
	{/if}
	{foreach from=$b_info key=bid item=b}
		<tr>
			<td>{$b.code} - {$b.description}</td>
			<td class="r">{$b.selling_price|number_format:2}
				{if $b.selling_price eq 0 or $b.selling_price_foc}
						<i>(FOC)</i>
				{/if}
			</td>
			{if $show_price_type}
				<td align="center">{$b.price_type|default:'-'}</td>
				{foreach from=$price_type_list item=pt}
					<td class="r">{$b.disc_rate[$pt.code]|default:'-'}</td>
				{/foreach}
			{/if}
		</tr>
	{/foreach}
</table>