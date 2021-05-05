{*
2/8/2012 2:14:53 PM Andy
- Remove color/size by row, change to show by matrix table.
- Add "Average" column.
- Add "Qty Matrix Table".

4/9/2012 5:19:23 PM Justin
- Modified to show category name instead of showing "category" when sales under the root category.
*}

{foreach from=$data key=next_cat_id item=r}
	{assign var=this_cat_info value=$cat_info_list.$next_cat_id}
	
	{cycle assign=row_color values="#ffffff,#eeeeee"}
	<tbody {if $next_cat_id}id="tbody_cat_row-{$next_cat_id}"{/if}>
		<tr bgColor="{$row_color}">
			<td rowspan="2">
				{if !$no_header_footer}
					<img src="/ui/pixel.gif" width="{$indent*20}" height="1" />
				{/if}
				
				{if $next_cat_id eq $smarty.request.category_id}
					{if !$no_header_footer}
						<img src="/ui/pixel.gif" width="20" height="1" />
					{/if}
					Items directly under this "{$selected_cat_info.description|default:'-'}"
				{elseif $this_cat_info}
					{if !$no_header_footer}
						{if $this_cat_info.have_subcat}
							<img onClick="expand_sub('{$next_cat_id}','{$indent+1}',this);" src="/ui/expand.gif" />
						{else}
							<img src="/ui/pixel.gif" width="20" height="1" />
						{/if}
					{/if}
					
					{if !$no_header_footer}
						<a href="javascript:void(show_sub('{$next_cat_id}'))">
					{/if}
					
					{$this_cat_info.description|default:'-'}
					
					{if !$no_header_footer}
						</a>
					{/if}
				{else}
					Un-Categorized
				{/if}
			</td>
		
			<td rowspan="2" class="r">{$r.qty|qty_nf}</td>
			<td rowspan="2" class="r">{$r.amount|number_format:2}</td>
			{if $sessioninfo.show_cost}
				<td rowspan="2" class="r">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
			{/if}
			{if $sessioninfo.privilege.SHOW_REPORT_GP}
				<td rowspan="2" class="r">{$r.gp|number_format:2}</td>
				<td rowspan="2" class="r">{$r.gp_per|number_format:2}%</td>
			{/if}
			
			<!-- Average -->
			<td rowspan="2" class="r">{$r.avg_amt|number_format:2}</td>
			{if $sessioninfo.show_cost}
				<td rowspan="2" class="r">{$r.avg_cost|number_format:$config.global_cost_decimal_points}</td>
			{/if}
			{if $sessioninfo.privilege.SHOW_REPORT_GP}
				<td rowspan="2" class="r">{$r.avg_gp_per|number_format:2}%</td>
			{/if}
			
			<!-- Sales Trend -->
			<td class="r">{$r.sales_trend.qty.1|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.3|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.6|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.12|qty_nf:".":""|ifzero}</td>
			<td rowspan="2">
				<table class="report_table">
					<tr class="header">
						<th class="color_header">&nbsp;</th>
						{foreach from=$colors item=clr}
							{if $r.by_color_size.$clr}
								<th class="color_header">{$clr}</th>
							{/if}
						{/foreach}
						{if $r.by_color_size.NOTSET}
							<th class="color_header">Not Set</th>
						{/if}
					</tr>
					{foreach from=$sizes item=sz}
						<tr>
							{if $r.size_list.$sz}
								<td>{$sz}</td>
								{foreach from=$colors item=clr}
									{if $r.by_color_size.$clr}
										<td class="r">{$r.by_color_size.$clr.$sz.qty|qty_nf}</td>
									{/if}
								{/foreach}
								{if $r.by_color_size.NOTSET}
									<td class="r">{$r.by_color_size.NOTSET.$sz.qty|qty_nf}</td>
								{/if}
							{/if}
						</tr>
					{/foreach}
					{if $r.size_list.NOTSET}
						<td>Not Set</td>
						{foreach from=$colors item=clr}
							{if $r.by_color_size.$clr}
								<td class="r">{$r.by_color_size.$clr.NOTSET.qty|qty_nf}</td>
							{/if}
						{/foreach}
						{if $r.by_color_size.NOTSET}
							<td class="r">{$r.by_color_size.NOTSET.NOTSET.qty|qty_nf}</td>
						{/if}
					{/if}
				</table>
			</td>
		</tr>
		<tr bgColor="{$row_color}">
			<td class="r">{$r.sales_trend.qty.1|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.3/3|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.6/6|qty_nf:".":""|ifzero}</td>
			<td class="r">{$r.sales_trend.qty.12/12|qty_nf:".":""|ifzero}</td>
		</tr>
	</tbody>
{/foreach}