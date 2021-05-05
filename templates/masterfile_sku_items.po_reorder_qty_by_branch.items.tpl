{*
12/16/2019 10:42 AM William
- Added new Moq Qty to PO Reorder Qty by Branch.
*}


<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<!-- Header -->
	<tr height="24" bgcolor="#ffee99">
		<th rowspan="2">ARMS Code</th>
		<th rowspan="2">Description</th>
		
		<!-- Normal Change Price Method: by branch -->
		{foreach from=$branches item=b}
			<th colspan="4" title="{$b.code} - {$b.description}">{$b.code}</th>
		{/foreach}
	</tr>
		
	<tr height="24" bgcolor="#ffee99">
		{foreach from=$branches item=b}
			<th title="Min Qty for {$b.code}">Min Qty</th>
			<th title="Max Qty for {$b.code}">Max Qty</th>
			<th title="Moq Qty for {$b.code}">Moq Qty</th>
			<th title="Notify Person">Notify Person</th>
		{/foreach}
	</tr>
	
	{foreach from=$si_info key=sid item=si}
		<tr>
			<td>
				{$si.sku_item_code}
				<input type="hidden" name="si_code[{$sid}]" value="{$si.sku_item_code}" />
			</td>
			<td>{$si.description}</td>
			{foreach from=$branches key=bid item=b}
				<td align="center">
					<input type="text" name="min_qty[{$sid}][{$bid}]" class="r" value="{$items.$sid.$bid.min_qty|qty_nf}" onchange="{if $si.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" onfocus="this.select()" size="5" id="min_qty-{$sid}-{$bid}" />
					<input type="hidden" name="item_id[{$sid}][{$bid}]" value="{$items.$sid.$bid.sku_item_id}" />
				</td>
				<td align="center"><input type="text" name="max_qty[{$sid}][{$bid}]" class="r" value="{$items.$sid.$bid.max_qty|qty_nf}" onchange="{if $si.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" onfocus="this.select()" size="5" id="max_qty-{$sid}-{$bid}" /> </td>
				<td align="center"> 
				<input type="text" name="moq_qty[{$sid}][{$bid}]" class="r" value="{$items.$sid.$bid.moq_qty|qty_nf}" onchange="{if $si.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}" onfocus="this.select()" size="5" id="moq_qty-{$sid}-{$bid}" /> 
				</td>
				<td>
					<select name="notify_user_id[{$sid}][{$bid}]">
						<option value="" {if !$items.$sid.$bid.notify_user_id}selected{/if}>--</option>
						{foreach from=$users key=uid item=r}
							<option value="{$r.id}" {if $items.$sid.$bid.notify_user_id eq $uid}selected{/if}>{$r.u}</option>
						{/foreach}
					</select>
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
<div id="price_btn"><input type="button" name="save_btn" value="Save" onclick="PO_REORDER_QTY_BY_BRANCH_MODULE.save();"><span id="loading_area"></span></div>
