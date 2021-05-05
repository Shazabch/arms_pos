{*
4/25/2017 10:13 AM Khausalya
- Enhanced changes from RM to use config setting. 

6/15/2017 9:22 AM Qiu Ying
- Bug fixed on qty values are listed with currency symbols
*}
{foreach from=$saci_date_list.$sac_id item=date_item_id key=date_from name=saci_date_list}
	<table width="100%" border="0" style="border:1px solid #000">
		<tbody>
			<tr>
				<td colspan="4">
					<b>Date From: </b>{$date_from|date_format:'%Y-%m-%d'}
				</td>
			</tr>
			<tr>
				<th bgcolor="#CCCCCC" width="6%">&nbsp;</th>
				<th bgcolor="#CCCCCC" width="34%">Condition</th>
				<th bgcolor="#CCCCCC" width="34%">Additional Filter</th>
				<th bgcolor="#CCCCCC" width="26%">Commission Method</th>
			</tr>
			{foreach from=$sac_items.$sac_id.$date_item_id item=saci name=saci_list}
				<tr valign="top" {if $is_hidden || $saci.is_deleted}style="display:none;"{/if}>
					<td bgcolor="#DDDDDD" align="center">
						{$smarty.foreach.saci_list.iteration}.
					</td>
					<td bgcolor="#DDDDDD">
						{if $saci.sku_item_id}
							<b>SKU: </b>{$saci.sku_item_code} / {$saci.artno} / {$saci.description}
						{else}
							{if $saci.cat_desc}
								<b>Category:</b> {$saci.cat_desc}
							{/if}
							{if $saci.brand_desc}
								{if $saci.cat_desc}<br />+<br />{/if}
								<b>Brand:</b> {$saci.brand_desc}
							{/if}
						{/if}
					</td>
					<td bgcolor="#DDDDDD">
						{if $saci.sku_type}
							<b>SKU Type: </b>
							{$saci.sku_type}
						{/if}
						{if is_array($saci.price_type)}
							{if $saci.sku_type}<br />+<br />{/if}
							<b>Price Type: </b>
							{foreach from=$saci.price_type item=pt key=pt_code name=pt_list}
								{$pt_code|trim}{if !$smarty.foreach.pt_list.last},{/if}
							{/foreach}
						{/if}
						{if $saci.vendor_desc}
							{if $saci.sku_type || is_array($saci.price_type)}<br />+<br />{/if}
							<b>Vendor: </b>
							{$saci.vendor_desc}
						{/if}
					</td>
					<td bgcolor="#DDDDDD" nowrap>
						<b>Type: </b>{$saci.commission_method}
						{if $saci.commission_method eq 'Flat'}
							<br /><b>Value: </b>{$saci.commission_value|default:0}
						{/if}
						<span {if $saci.commission_method eq "Flat"}style="display:none;"{/if}>
							<table width="100%">
								<tr>
									<th bgcolor="#CACACA" width="5%">&nbsp;</th>
									<th bgcolor="#B6B6B6" width="65%">Range</th>
									<th bgcolor="#B6B6B6" width="30%">Value</th>
								</tr>
								<tbody>
									{assign var=range_count value=0}
									{if $saci.commission_method ne "Flat" && $saci.commission_value}
										{if $saci.commission_method eq "Sales Range"}
											{assign var=amt_label value=$config.arms_currency.symbol}
										{else}
											{assign var=amt_label value=""}
										{/if}
										{foreach from=$saci.commission_value item=cv key=r name=cv_list}
											<tr>
												<td bgcolor="#CACACA">
													{$smarty.foreach.cv_list.iteration}.
												</td>
												<td bgcolor="#CACACA">
													{if $cv.range_from > 0 && $cv.range_to > 0}
														Between {$amt_label}{$cv.range_from} - {$amt_label}{$cv.range_to}
													{elseif $cv.range_from > 0 && $cv.range_to eq 0}
														Start from {$amt_label}{$cv.range_from}
													{elseif $cv.range_from eq 0 && $cv.range_to > 0}
														At most {$amt_label}{$cv.range_to}
													{/if}
												</td>
												<td align="right" bgcolor="#CACACA">{$cv.value}</td>
											</tr>
											{if $smarty.foreach.cv_list.last}{assign var=range_count value=$r}{/if}
										{foreachelse}
											<tr>
												<td colspan="3" bgcolor="#CACACA" align="center">No Record</td>
											</tr>
										{/foreach}
									{else}
										<tr>
											<td colspan="3" bgcolor="#CACACA" align="center">No Record</td>
										</tr>
									{/if}
								</tbody>
							</table>
						</span>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="4" align="center" bgcolor="{#TB_CORNER#}">No Data</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	{if !$smarty.foreach.saci_date_list.last}<br />{/if}
{/foreach}
