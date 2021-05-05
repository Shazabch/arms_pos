{*
9/8/2017 10:55 AM Andy
- Change to use grn cost as average cost if found average cost or total pcs is negative.

10/2/2017 12:38 PM Andy
- Fixed if stock qty or total avg cost is negative before grn, take the grn cost to replace avg cost.

1/12/2018 6:03 PM Andy
- Enhanced cost calculation to check work order.

4/24/2018 10:00 AM Andy
- Added Foreign Currency feature.

8/29/2018 1:59 PM Andy
- Enhanced to calculate grn cost by multiply the grr tax percent.
*}

<h3>{$sku.description}</h3>
<h4>Costing method:
	{if $config.sku_use_avg_cost_as_last_cost}
		<u>Average Cost by Parent & Childs</u>
	{else}
		<u>Last Cost by Parent & Childs</u>
	{/if}
</h4>

<ul>
	<li> Stock Take cost replace Last Cost and Average Cost.</li>
	<li> If Stock Take din't define the cost, Last Cost and Average Cost will not be changed.</li>
	<li> GRN cost replace Last Cost and calculates Average Cost.</li>
	<li> Last Cost and Average Cost will then re-update to parent & child based on the Packing UOM Fraction.</li>
	{if $config.sku_use_avg_cost_as_last_cost}
		<li> You have configured to use <b style="color:blue;">Average Cost</b> as Standard Cost for all modules and reports.</li>
	{else}
		<li> You have configured to use <b style="color:blue;">Last Cost</b> as Standard Cost for all modules and reports.</li>
	{/if}
	{if $no_inventory eq 'yes'}
		<li> This sku is marked as "No Inventory", stock balance will always show as "N/A".</li>
	{/if}
	{if $config.foreign_currency}
		<li> {$LANG.BASE_CURRENCY_CONVERT_NOTICE}</li>
	{/if}
</ul>

<table width="100%" class="report_table">
	<tr class="header">
		<th>Date</th>
		<th>Type</th>
		<th>Last<br />Cost</th>
		<th>Average<br />Cost</th>
		<th>Qty B/F</th>
		<th>New Qty</th>
		<th>Balance (C/F)</th>
		<th>Link</th>
	</tr>

	{if $sessioninfo.branch_type ne 'franchise'}
		{assign var=has_opening_cost value=1}
		{* Master / Opening *}
		<tr>
			<td>-</td>
			<td>Masterfile / Opening</td>
			<td align="right">{$display_data.master.$sku_item_id.cost|number_format:$config.global_cost_decimal_points}</td>
			<td align="right">{$display_data.master.$sku_item_id.cost|number_format:$config.global_cost_decimal_points}</td>
			<td align="right">-</td>
			<td align="right">-</td>
			<td align="right">-</td>
			<td align="right">-</td>
		</tr>
	{/if}
	
	{* History *}
	{if $display_data}
		{foreach from=$display_data.date_list key=d item=date_data}
			{foreach from=$date_data key=entry_row item=entry}
				<tbody>
					<tr>
						<td>{$d}</td>
						<td>
							{if $entry.type eq 'stock_take'}
								Stock Take
							{elseif $entry.type eq 'grn'}
								GRN
							{elseif $entry.type eq 'work_order'}
								Work Order
							{else}
								{$entry.type}
							{/if}
							
							{if !$entry.action.item_list.$sku_item_id}
								&nbsp;(other sku)
							{/if}
							
							&nbsp;[<a href="javascript:void(toggle_cost_history_details('{$d}', '{$entry_row}'))">Details</a>]
						</td>
						
						{* By default only show result of AFTER *}
						{* Last Cost *}
						<td align="right" {if $entry.currency_code}class="converted_base_amt"{/if}>
							{$entry.after.item_list.$sku_item_id.cost|number_format:$config.global_cost_decimal_points}{if $entry.currency_code}*{/if}
						</td>
						
						{* Average Cost *}
						<td align="right" {if $entry.currency_code}class="converted_base_amt"{/if}>
							{$entry.after.item_list.$sku_item_id.avg_cost|number_format:$config.global_cost_decimal_points}{if $entry.currency_code}*{/if}
						</td>
						
						{* Qty B/F *}
						<td align="right">
							{if $no_inventory eq 'yes'}N/A
							{else}
								{$entry.before.item_list.$sku_item_id.qty|qty_nf}
							{/if}
						</td>
						
						{* IN *}
						<td align="right">{$entry.action.item_list.$sku_item_id.qty|qty_nf}</td>
						
						{* Balance (C/F) *}
						<td align="right">
							{if $no_inventory eq 'yes'}N/A
							{else}
							{$entry.after.item_list.$sku_item_id.qty|qty_nf}
							{/if}
						</td>
						
						{* Link *}
						<td>
							{if $entry.link}
								{if $entry.currency_code}
									<span title="Exchange Rate {$entry.currency_rate}">
										[{$entry.currency_code}]
										{$entry.link}
									</span>
								{else}
									{$entry.link}
								{/if}
								
							{else}
								-
							{/if}
						</td>
					</tr>
				</tbody>
				
				{* Details *}
				<tbody id="tbody_entry_row-{$d}-{$entry_row}" style="display:none;">
					<tr>
						<td colspan="8">
							<table width="100%" class="report_table small">
								<tr bgcolor="#dddddd">
									<th>ARMS Code / MCode / Art No<br />Description</th>
									<th>Qty<br />(Qty in PCS)</th>
									<th>Last Cost</th>
									<th>Total Value<br />by Last Cost</th>
									<th>AVG Cost</th>
									<th>Total Value<br /> by AVG Cost</th>
								</tr>
								
								{* BEFORE *}
								<tr bgcolor="#cccccc">
									<th colspan="6" align="left">Brought Forward</th>
								<tr>
								{foreach from=$entry.before.item_list key=sid item=r}
									<tr class="{if $entry.action.item_list.$sid}cost_history_changed_sku_row {/if}{if $sid eq $sku_item_id}highlight_row{/if}">
										<td>
											{$si_info_list.$sid.info.sku_item_code} / {$si_info_list.$sid.info.mcode|default:'-'} / {$si_info_list.$sid.info.artno|default:'-'}<br />
											{$si_info_list.$sid.info.description} {include file=details.uom.tpl uom=$si_info_list.$sid.info.packing_uom_code}
										</td>
										<td align="right">{$r.qty|qty_nf}
											{if $r.qty and $si_info_list.$sid.info.packing_uom_fraction ne 1}
												<br />({$r.qty*$si_info_list.$sid.info.packing_uom_fraction})
											{/if}
										</td>
										<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.avg_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_avg_cost|number_format:$config.global_cost_decimal_points}</td>
									</tr>
								{/foreach}
								<tr>
									<th align="right">Total by PCS</th>
									<th align="right">{$entry.before.total.qty|qty_nf}</th>
									<th align="right">{$entry.before.total.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
									<th align="right">{$entry.before.total.total_cost|number_format:$config.global_cost_decimal_points}</th>
									<th align="right">{$entry.before.total.avg_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
									<th align="right">{$entry.before.total.total_avg_cost|number_format:$config.global_cost_decimal_points}</th>
								</tr>
								
								{* ACTION *}
								<tr bgcolor="#cccccc">
									<th colspan="6" align="left">Changes :
										{if $entry.type eq 'stock_take'}
											Stock Take
										{elseif $entry.type eq 'grn'}
											GRN
										{/if}
									</th>
								<tr>
								{foreach from=$entry.action.item_list key=sid item=r}
									<tr class="cost_history_changed_sku_row {if $sid eq $sku_item_id}highlight_row{/if}">
										<td>
											{$si_info_list.$sid.info.sku_item_code} / {$si_info_list.$sid.info.mcode|default:'-'} / {$si_info_list.$sid.info.artno|default:'-'}<br />
											{$si_info_list.$sid.info.description} {include file=details.uom.tpl uom=$si_info_list.$sid.info.packing_uom_code}
										</td>
										<td align="right">{$r.qty|qty_nf}
											{if $r.qty and $si_info_list.$sid.info.packing_uom_fraction ne 1}
												<br />({$r.qty*$si_info_list.$sid.info.packing_uom_fraction})
											{/if}
										</td>
										<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.avg_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_avg_cost|number_format:$config.global_cost_decimal_points}</td>
									</tr>
								{/foreach}
								
								{* BALANCE *}
								<tr bgcolor="#cccccc">
									<th colspan="6" align="left">Balance Before Costing Update</th>
								<tr>
								{foreach from=$entry.balance.item_list key=sid item=r}
									<tr class="{if $entry.action.item_list.$sid}cost_history_changed_sku_row {/if}{if $sid eq $sku_item_id}highlight_row{/if}">
										<td>
											{$si_info_list.$sid.info.sku_item_code} / {$si_info_list.$sid.info.mcode|default:'-'} / {$si_info_list.$sid.info.artno|default:'-'}<br />
											{$si_info_list.$sid.info.description} {include file=details.uom.tpl uom=$si_info_list.$sid.info.packing_uom_code}
										</td>
										<td align="right">{$r.qty|qty_nf}
											{if $r.qty and $si_info_list.$sid.info.packing_uom_fraction ne 1}
												<br />({$r.qty*$si_info_list.$sid.info.packing_uom_fraction})
											{/if}
										</td>
										<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.avg_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_avg_cost|number_format:$config.global_cost_decimal_points}</td>
									</tr>
								{/foreach}
								<tr>
									<th align="right">Total by PCS</th>
									<th align="right">{$entry.balance.total.qty|qty_nf}</th>
									<th align="right">-</th>
									<th align="right">{$entry.balance.total.total_cost|number_format:$config.global_cost_decimal_points}</th>
									<th align="right">-</th>
									<th align="right">{$entry.balance.total.total_avg_cost|number_format:$config.global_cost_decimal_points}</th>
								</tr>
								
								{* AFTER *}
								<tr bgcolor="#cccccc">
									<th colspan="6" align="left">Balance After Costing Update</th>
								<tr>
								<tr>
									<td colspan="6">
										{if $entry.type eq 'stock_take'}											
											{if $entry.after.pcs_cost}
												<b>Total Stock Take Quantity with Cost:</b> {$entry.after.total_sc_pcs|qty_nf}<br />
												<b>Total Stock Take Cost:</b> {$entry.after.total_sc_cost|number_format:$config.global_cost_decimal_points}<br />
												<b>Cost in PCS:</b> {$entry.after.total_sc_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total_sc_pcs|qty_nf} = {$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}<br />
												<span style="color:red;">Stock Take cost replace Last Cost and Average Cost.
													Last Cost and Average Cost = <b>{$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}</b>
												</span>
											{else}
												Stock Take without cost changed
											{/if}
										{elseif $entry.type eq 'grn'}
											{if $entry.after.pcs_cost}
												<b>Total GRN Quantity:</b> {$entry.after.total_grn_pcs|qty_nf}<br />
												<b>Total GRN Cost:</b>
												{if $entry.currency_code}
													<span class="converted_base_amt">
														{$entry.after.total_grn_cost|number_format:$config.global_cost_decimal_points}*
													</span>
													[
														{$entry.currency_code} {$entry.ori_cost|number_format:$config.global_cost_decimal_points},
														Rate: {$entry.currency_rate}
													]
												{else}
													{$entry.after.total_grn_cost|number_format:$config.global_cost_decimal_points}
												{/if}
												
												{if $entry.after.total_grn_tax>0}
													<span >
													(
													Including Tax: {$entry.after.total_grn_tax|number_format:$config.global_cost_decimal_points}, 
													Before Tax: {$entry.after.total_grn_cost_before_tax|number_format:$config.global_cost_decimal_points}
													)
													</span>
												{/if}
												<br />
												<b>Cost in PCS:</b> {$entry.after.total_grn_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total_grn_pcs|qty_nf} = {$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}<br />
												<span style="color:red;">GRN cost replace Last Cost.
													Last Cost = <b>{$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}</b>
												</span><br />
												{if $entry.balance.total.old_avg_cost}
													<b>AVG Cost in PCS:</b> {$entry.balance.total.total_avg_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total.qty|qty_nf} = {$entry.balance.total.old_avg_cost|number_format:$config.global_cost_decimal_points}<br />
													{if $entry.before.total.qty le 0 or $entry.before.total.total_avg_cost le 0}
														Total PCS / AVG Cost before the Changes is negative, replace using GRN Cost.<br />
													{else}
														Total PCS / AVG Cost is Negative, Replace using GRN Cost.<br />
													{/if}
												{else}
													<b>New AVG Cost in PCS:</b> {$entry.balance.total.total_avg_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total.qty|qty_nf} = {$entry.after.total.avg_cost|number_format:$config.global_cost_decimal_points}<br />
												{/if}
												<span style="color:red;">
													AVG Cost = <b>{$entry.after.total.avg_cost|number_format:$config.global_cost_decimal_points}</b>
												</span>
											{/if}
										{elseif $entry.type eq 'work_order'}
											<b>Total Work Order Quantity:</b> {$entry.after.total_wo_pcs|qty_nf}<br />
											<b>Total Work Order Cost:</b> {$entry.after.total_wo_cost|number_format:$config.global_cost_decimal_points}<br />
											<b>Cost in PCS:</b> {$entry.after.total_wo_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total_wo_pcs|qty_nf} = {$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}<br />
											<span style="color:red;">Work Order cost replace Last Cost.
												Last Cost = <b>{$entry.after.pcs_cost|number_format:$config.global_cost_decimal_points}</b>
											</span><br />
											{if $entry.balance.total.old_avg_cost}
												<b>AVG Cost in PCS:</b> {$entry.balance.total.total_avg_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total.qty|qty_nf} = {$entry.balance.total.old_avg_cost|number_format:$config.global_cost_decimal_points}<br />
												{if $entry.before.total.qty le 0 or $entry.before.total.total_avg_cost le 0}
													Total PCS / AVG Cost before the Changes is negative, replace using Work Order Cost.<br />
												{else}
													Total PCS / AVG Cost is Negative, Replace using Work Order Cost.<br />
												{/if}
											{else}
												<b>New AVG Cost in PCS:</b> {$entry.balance.total.total_avg_cost|number_format:$config.global_cost_decimal_points} / {$entry.after.total.qty|qty_nf} = {$entry.after.total.avg_cost|number_format:$config.global_cost_decimal_points}<br />
											{/if}
											<span style="color:red;">
												AVG Cost = <b>{$entry.after.total.avg_cost|number_format:$config.global_cost_decimal_points}</b>
											</span>
										{/if}
									</td>
								</tr>
								{foreach from=$entry.after.item_list key=sid item=r}
									<tr class="{if $entry.action.item_list.$sid}cost_history_changed_sku_row {/if}{if $sid eq $sku_item_id}highlight_row{/if}">
										<td>
											{$si_info_list.$sid.info.sku_item_code} / {$si_info_list.$sid.info.mcode|default:'-'} / {$si_info_list.$sid.info.artno|default:'-'}<br />
											{$si_info_list.$sid.info.description} {include file=details.uom.tpl uom=$si_info_list.$sid.info.packing_uom_code}
										</td>
										<td align="right">{$r.qty|qty_nf}
											{if $r.qty and $si_info_list.$sid.info.packing_uom_fraction ne 1}
												<br />({$r.qty*$si_info_list.$sid.info.packing_uom_fraction})
											{/if}
										</td>
										<td align="right">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.avg_cost|number_format:$config.global_cost_decimal_points}</td>
										<td align="right">{$r.total_avg_cost|number_format:$config.global_cost_decimal_points}</td>
									</tr>
								{/foreach}
								<tr>
									<th align="right">Total by PCS</th>
									<th align="right">{$entry.after.total.qty|qty_nf}</th>
									<th align="right">{$entry.after.total.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
									<th align="right">{$entry.after.total.total_cost|number_format:$config.global_cost_decimal_points}</th>
									<th align="right">{$entry.after.total.avg_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
									<th align="right">{$entry.after.total.total_avg_cost|number_format:$config.global_cost_decimal_points}</th>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
			{/foreach}
		{/foreach}
	{else}
		{if !$has_opening_cost}
			<tr align="center">
				<td colspan="8">History not found</td>
			</tr>
		{/if}
	{/if}
</table>
