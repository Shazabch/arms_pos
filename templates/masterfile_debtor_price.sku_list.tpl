{$form.sku_matched|number_format} SKU Found. 

{if $form.total_page > 1}
	Go to Page
	<select class="form-control" id="sel_page" onChange="DEBTOR_PRICE.page_changed();">
		{section loop=$form.total_page name=s}
			<option value="{$smarty.section.s.index}" {if $smarty.section.s.index eq $smarty.request.p}selected {/if}>{$smarty.section.s.iteration}</option>
		{/section}
	</select>
{/if}
<br /><br />

<div >
	<div >
		<div class="table-responsive">
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr class="header">
						<th width="50">&nbsp;</th>
						<th width="100">ARMS Code</th>
						<th width="100">MCode<br />Art No<br />{$config.link_code_name}</th>
						<th>Description</th>
						<th>Price</th>
					</tr>
				</thead>
				{foreach from=$form.sku_list key=sid item=sku_data}
					<tbody class="fs-08">
						<tr>
							<td align="center">
								<img src="ui/ed.png" align="absmiddle" onClick="SKU_PRICE_DIALOG.open('{$sid}');" title="Change Price" />
							</td>
							<td>{$sku_data.info.sku_item_code}</td>
							<td>
								{$sku_data.info.mcode|default:'-'}<br />
								{$sku_data.info.artno|default:'-'}<br />
								{$sku_data.info.link_code|default:'-'}			
							</td>
							<td>{$sku_data.info.description}</td>
							<td>
								{if $sku_data.price_not_set}
									<i>-- Not Set --</i>
								{else}
									{foreach from=$sku_data.b_list key=bid item=b_data}
										{if $b_data.price > 0}
											<div class="div_debtor_price_container">
												<span class="span_debtor_branch">{$branch_list.$bid.code}</span>
												<span class="span_debtor_price">{$b_data.price|number_format:2}</span>
												<img src="ui/ed.png" align="absmiddle" onClick="SKU_PRICE_DIALOG.open('{$sid}', '{$bid}');" title="Change Price" />
												<img src="ui/icons/zoom.png" align="absmiddle" onClick="DEBTOR_PRICE.view_price_history('{$sid}', '{$bid}');" title="View Price History" />
											</div>
										{/if}
									{/foreach}
								{/if}
							</td>
						</tr>
					</tbody>
				{foreachelse}
					<tr>
						<td colspan="5">No SKU</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>