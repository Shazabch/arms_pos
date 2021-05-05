{*
6/24/2011 11:59:27 AM Andy
- Make report stock take qty can show decimal.

10/13/2011 4:51:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

5/9/2012 2:21:43 PM Justin
- Simplified the report construction.
- Modified to be able to show different output base on user filter.
- Added to show grand total.

11/19/2013 3:11 PM Justin
- Enhanced to change the wording from "Mark On" to "GP(%)".

4/19/2017 11.02 AM Khausalya 
- Enhanced changes from RM to use config setting. 

11/15/2017 5:18 PM Justin
- Enhanced to show cost decimal points base on config set.
- Bug fixed on cost and selling showed on screen is incorrect.

5/9/2019 5:43 PM William
- Bug fixed on detail table broken.
- Added new "Group Type" filter.
- Enhanced view type detail and summary can filter by "Group Type".

12/03/2020 09:30 AM Rayleen
-Add column  link/old code after Art No
- Rename and arrange columns to Arms Code, MCode, Art No, {$config.link_code_name}, Description, etc
- Change Total/Grand Total column Colspan
*}

{if $smarty.request.group_by_item}
	<table width="100%" class="tb" cellspacing="0" cellpadding="4">
		<tr class="thd">
			<th>Item #</th>
			<th>ARMS Code</th>
			<th>MCode</th>
			<th>Art No</th>
			<th>{$config.link_code_name}</th>
			<th>Description</th>
			<th>Qty<br>(Pcs)</th>
			{if $sessioninfo.privilege.SHOW_COST}
				<th>Unit Cost<br>({$config.arms_currency.symbol})</th>
				<th>Total Cost<br>({$config.arms_currency.symbol})</th>
			{/if}
			<th>Unit Selling<br>({$config.arms_currency.symbol})</th>
			<th>Total Selling<br>({$config.arms_currency.symbol})</th>
			<th>GP(%)</th>
		</tr>

		{foreach from=$table item=cost_list key=sid}
			{foreach from=$cost_list item=selling_list key=cost}
				{foreach from=$selling_list item=r key=selling}
					{assign var=total_qty value=$total_qty+$r.total_qty}
					{assign var=total_cost value=$total_cost+$r.total_cost}
					{assign var=total_sell value=$total_sell+$r.total_retail}
					<!--{$n++}-->
					<tr>
						<td>{$n}</td>
						<td>{$r.sku_item_code}</td>
						<td>{$r.mcode|default:"&nbsp;"}</td>
						<td>{$r.artno|default:"&nbsp;"}</td>
						<td>{$r.link_code|default:"&nbsp;"}</td>
						<td>{$r.sku|default:"<font color='red'>-item not in ARMS-</font>"}</td>
						<td align="right">{$r.total_qty|qty_nf}</td>
						{if $sessioninfo.privilege.SHOW_COST}
							<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
							<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						<td align="right">{$r.selling|number_format:2}</td>
						<td align="right">{$r.total_retail|number_format:2}</td>
						<td align="right">{$r.mark_on|number_format:2}</td>
					</tr>
				{/foreach}
			{/foreach}
		{/foreach}

		<tr class="thd">
			<th colspan="6" align="right">Grand Total</td>
			<td align="right">{$total_qty|qty_nf}</td>
			{if $sessioninfo.privilege.SHOW_COST}
				<td>&nbsp;</td>
				<td align="right">{$total_cost|number_format:$config.global_cost_decimal_points}</td>
			{/if}
			<td>&nbsp;</td>
			<td align="right">{$total_sell|number_format:2}</td>
			{assign var=p value=$total_sell-$total_cost}
			{if $p > 0}
				{assign var=ttl_mark_on value=$p/$total_sell*100}
			{else}
				{assign var=ttl_mark_on value=0}
			{/if}
			<td align="right">{$ttl_mark_on|number_format:2}</td>
		</tr>
	</table>
{else}
	{if $smarty.request.group_type eq 'bydepartment'}


		
		{foreach from=$table item=dept_list key=date}
			
				{foreach from=$dept_list item=row key=dept}
					{assign var=n value=0}
					{assign var=total_qty value=0}
					{assign var=total_cost value=0}
					{assign var=total_sell value=0}
					<table width="100%" class="tb" cellspacing="0" cellpadding="4">
						<tr>
							<th style="background-color:#fe9;width: 275px;"  >Department</th><td>{$dept|default:"<font color='red'>-uncategorized-</font>"}</td>
						</tr>
					</table>
					<br />
					<table width="100%" class="tb" cellspacing="0" cellpadding="4">
						<tr class="thd">
							<th>Item #</th>
							<th>ARMS Code</th>
							<th>MCode</th>
							<th>Art No</th>
							<th>{$config.link_code_name}</th>
							<th>Description</th>
							<th>Qty<br>(Pcs)</th>
							{if $sessioninfo.privilege.SHOW_COST}
								<th>Unit Cost<br>({$config.arms_currency.symbol})</th>
								<th>Total Cost<br>({$config.arms_currency.symbol})</th>
							{/if}
							<th>Unit Selling<br>({$config.arms_currency.symbol})</th>
							<th>Total Selling<br>({$config.arms_currency.symbol})</th>
							<th>GP(%)</th>
						</tr>

						{foreach from=$row item=r key=dump}
							{assign var=total_qty value=$total_qty+$r.total_qty}
							{assign var=total_cost value=$total_cost+$r.total_cost}
							{assign var=total_sell value=$total_sell+$r.total_retail}
							{assign var=grand_total_qty value=$grand_total_qty+$r.total_qty}
							{assign var=grand_total_cost value=$grand_total_cost+$r.total_cost}
							{assign var=grand_total_sell value=$grand_total_sell+$r.total_retail}
							<!--{$n++}-->
							<tr>
								<td>{$n}</td>
								<td>{$r.sku_item_code}</td>
								<td>{$r.mcode|default:"&nbsp;"}</td>
								<td>{$r.artno|default:"&nbsp;"}</td>
								<td>{$r.link_code|default:"&nbsp;"}</td>
								<td>{$r.sku|default:"<font color='red'>-item not in ARMS-</font>"}</td>
								<td align="right">{$r.total_qty|qty_nf}</td>
								{if $sessioninfo.privilege.SHOW_COST}
									<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
									<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
								{/if}
								<td align="right">{$r.selling|number_format:2}</td>
								<td align="right">{$r.total_retail|number_format:2}</td>
								<td align="right">{$r.mark_on|number_format:2}</td>
							</tr>
						{/foreach}

						<tr class=thd>
							<th colspan=6 align="right">Total</td>
							<td align="right">{$total_qty|qty_nf}</td>
							{if $sessioninfo.privilege.SHOW_COST}
								<td>&nbsp;</td>
								<td align="right">{$total_cost|number_format:$config.global_cost_decimal_points}</td>
							{/if}
							<td>&nbsp;</td>
							<td align="right">{$total_sell|number_format:2}</td>
							{assign var=p value=$total_sell-$total_cost}
							{if $p > 0}
								{assign var=ttl_mark_on value=$p/$total_sell*100}
							{else}
								{assign var=ttl_mark_on value=0}
							{/if}
							<td align="right">{$ttl_mark_on|number_format:2}</td>
						</tr>
					</table>
					<br />
				{/foreach}
				
		{/foreach}

	
	
	{else}
	

		{foreach from=$table item=date_list key=date}

				{foreach from=$date_list item=row key=shelf_no}
	

						{assign var=n value=0}
						{assign var=total_qty value=0}
						{assign var=total_cost value=0}
						{assign var=total_sell value=0}
						<table width="100%" class="tb" cellspacing="0" cellpadding="4">
							<tr>
								<th style="background-color:#fe9;width: 275px;">Shelf No</th><td>{$shelf_no}</td>								
							</tr>
						</table>
						<br />
						<table width="100%" class="tb" cellspacing="0" cellpadding="4">
							<tr class="thd">
								<th>Item #</th>
								<th>ARMS Code</th>
								<th>MCode</th>
								<th>Art No</th>
								<th>{$config.link_code_name}</th>
								<th>Description</th>
								<th>Qty<br>(Pcs)</th>
								{if $sessioninfo.privilege.SHOW_COST}
									<th>Unit Cost<br>({$config.arms_currency.symbol})</th>
									<th>Total Cost<br>({$config.arms_currency.symbol})</th>
								{/if}
								<th>Unit Selling<br>({$config.arms_currency.symbol})</th>
								<th>Total Selling<br>({$config.arms_currency.symbol})</th>
								<th>GP(%)</th>
							</tr>

							{foreach from=$row item=r key=dump}
								{assign var=total_qty value=$total_qty+$r.total_qty}
								{assign var=total_cost value=$total_cost+$r.total_cost}
								{assign var=total_sell value=$total_sell+$r.total_retail}
								{assign var=grand_total_qty value=$grand_total_qty+$r.total_qty}
								{assign var=grand_total_cost value=$grand_total_cost+$r.total_cost}
								{assign var=grand_total_sell value=$grand_total_sell+$r.total_retail}
								<!--{$n++}-->
								<tr>
									<td>{$n}</td>
									<td>{$r.sku_item_code}</td>
									<td>{$r.mcode|default:"&nbsp;"}</td>
									<td>{$r.artno|default:"&nbsp;"}</td>
									<td>{$r.link_code|default:"&nbsp;"}</td>
									<td>{$r.sku|default:"<font color='red'>-item not in ARMS-</font>"}</td>
									<td align="right">{$r.total_qty|qty_nf}</td>
									{if $sessioninfo.privilege.SHOW_COST}
										<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
									{/if}
									<td align="right">{$r.selling|number_format:2}</td>
									<td align="right">{$r.total_retail|number_format:2}</td>
									<td align="right">{$r.mark_on|number_format:2}</td>
								</tr>
							{/foreach}

							<tr class=thd>
								<th colspan=6 align="right">Total</td>
								<td align="right">{$total_qty|qty_nf}</td>
								{if $sessioninfo.privilege.SHOW_COST}
									<td>&nbsp;</td>
									<td align="right">{$total_cost|number_format:$config.global_cost_decimal_points}</td>
								{/if}
								<td>&nbsp;</td>
								<td align="right">{$total_sell|number_format:2}</td>
								{assign var=p value=$total_sell-$total_cost}
								{if $p > 0}
									{assign var=ttl_mark_on value=$p/$total_sell*100}
								{else}
									{assign var=ttl_mark_on value=0}
								{/if}
								<td align="right">{$ttl_mark_on|number_format:2}</td>
							</tr>
						</table>
						<br />
					
				{/foreach}
			
		{/foreach}
	
	{/if}
	
	<br />
	<table width="100%" class="tb" cellspacing="0" cellpadding="4">
		<tr class="thd">
			<th>&nbsp;</th>
			<th>Total Qty (Pcs)</th>
			<th>Total Cost ({$config.arms_currency.symbol})</th>
			<th>Total Selling ({$config.arms_currency.symbol})</th>
		</tr>
		<tr align="right">
			<th>Grand Total</th>
			<td>{$grand_total_qty|number_format:2}</td>
			<td>{$grand_total_cost|number_format:$config.global_cost_decimal_points}</td>
			<td>{$grand_total_sell|number_format:2}</td>
		</tr>
	</table>
{/if}
