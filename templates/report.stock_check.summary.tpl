{*
5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST

6/24/2011 11:59:27 AM Andy
- Make report stock take qty can show decimal.

10/13/2011 4:51:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/19/2017 10:57 AM Khausalya
- Enhanced changes from RM to use config setting. 

11/15/2017 5:18 PM Justin
- Enhanced to show cost decimal points base on config set.
- Bug fixed on cost and selling showed on screen is incorrect.

5/9/2019 5:43 PM William
- Enhanced view type summary can filter by "Group Type".
*}

{if $smarty.request.group_type eq 'bydepartment'}
<div class="card mx-3">
	<div class="card-body">
		<table class="tb table mb-0 text-md-nowrap  table-hover">
			<thead class="bg-gray-100">
				<tr class=thd>
					<th>Department</th>
					<th>Total Qty<br>(Pcs)</th>
					{if $sessioninfo.privilege.SHOW_COST}
						<th>Total Cost<br>({$config.arms_currency.symbol})</th>
					{/if}
					<th>Total Selling<br>({$config.arms_currency.symbol})</th>
					<th>On %</th>
					</tr>
			</thead>
			{foreach name=i from=$table item=r}
			{assign var=total_qty value=$total_qty+$r.qty}
			{assign var=total_cost value=$total_cost+$r.tcost}
			{assign var=total_sell value=$total_sell+$r.tsell}
			<tbody class="fs-08">
				<tr class={cycle values="r0,r1"}>
					<td>{$r.dept|default:"<font color=red>-uncategorized-</font>"}</td>
					<td align=right>{$r.qty|qty_nf}</td>
					{if $sessioninfo.privilege.SHOW_COST}
						<td align=right>{$r.tcost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
					<td align=right>{$r.tsell|number_format:2}</td>
					{assign var=p value=$r.tsell-$r.tcost}
					<td align=right>{if $r.tsell ne '0'}{$p/$r.tsell*100|number_format:2}{else}{$p*100|number_format:2}{/if}</td>
				</tr>
			</tbody>
			{/foreach}
			<tr class=thd>
				<th colspan=1 align=right>Total</td>
				<td align=right>{$total_qty|qty_nf}</td>
				{if $sessioninfo.privilege.SHOW_COST}
					<td align=right>{$total_cost|number_format:$config.global_cost_decimal_points}</td>
				{/if}
				<td align=right>{$total_sell|number_format:2}</td>
				{assign var=p value=$total_sell-$total_cost}
				<td align=right>{if $total_sell ne '0'}{$p/$total_sell*100|number_format:2}{else}{$p*100|number_format:2}{/if}</td>
			</tr>
			</table>
	</div>
</div>
{else}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="tb table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr class=thd>
						<th>Shelf No</th>
						<th>Total Qty<br>(Pcs)</th>
						{if $sessioninfo.privilege.SHOW_COST}
							<th>Total Cost<br>({$config.arms_currency.symbol})</th>
						{/if}
						<th>Total Selling<br>({$config.arms_currency.symbol})</th>
						<th>On %</th>
						</tr>
				</thead>
				{foreach name=i from=$table item=r}
				{assign var=total_qty value=$total_qty+$r.qty}
				{assign var=total_cost value=$total_cost+$r.tcost}
				{assign var=total_sell value=$total_sell+$r.tsell}
				
				<tbody class="fs-08">
					<tr class={cycle values="r0,r1"}>
						<td>{$r.shelf_no}</td>
						<td align=right>{$r.qty|qty_nf}</td>
						{if $sessioninfo.privilege.SHOW_COST}
							<td align=right>{$r.tcost|number_format:$config.global_cost_decimal_points}</td>
						{/if}
						<td align=right>{$r.tsell|number_format:2}</td>
						{assign var=p value=$r.tsell-$r.tcost}
						<td align=right>{if $r.tsell ne '0'}{$p/$r.tsell*100|number_format:2}{else}{$p*100|number_format:2}{/if}</td>
					</tr>
				</tbody>
				{/foreach}
				<tr class=thd>
					<th colspan=1 align=right>Total</td>
					<td align=right>{$total_qty|qty_nf}</td>
					{if $sessioninfo.privilege.SHOW_COST}
						<td align=right>{$total_cost|number_format:$config.global_cost_decimal_points}</td>
					{/if}
					<td align=right>{$total_sell|number_format:2}</td>
					{assign var=p value=$total_sell-$total_cost}
					<td align=right>{if $total_sell ne '0'}{$p/$total_sell*100|number_format:2}{else}{$p*100|number_format:2}{/if}</td>
				</tr>
				</table>
		</div>
	</div>
</div>
{/if}
