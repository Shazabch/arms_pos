{*
10/12/2011 2:09:54 PM Alex
- add qty_nf to control quantity decimal

1/11/2013 5:21 PM Justin
- Enhanced to show voucher codes list.
- Bug fixed on cannot show voucher table each time returned from errors.
*}

{assign var=row_key value="`$item.id`_`$item.branch_id`"}
<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="item_{$row_key}">
	<td align="center" nowrap>
		{if !$config.membership_redemption_use_enhanced}
			{$smarty.foreach.f.iteration}
		{else}
			<img src=ui/cancel.png style="vertical-align:top;" class=clickable title="Delete Row" onclick="delete_item('{$row_key}')" align=absmiddle alt="{$item_id}">
		{/if}
	</td>
    <td>{$item.sku_item_code}</td>
    <td>
		{$item.description}
		{if $item.is_voucher}
		<table width="25%" id="voucher_item_table_{$row_key}" style="{if !$items.$row_key.voucher && !$items.$row_key.qty}display:none;{/if} border: 1px solid black;">
			<tr bgcolor="#E0D28A">
				<th>#</th>
				<th>Voucher Code</th>
			</tr>
			<tbody id="voucher_item_list_{$row_key}">
				{foreach from=$items.$row_key.voucher_code key=row item=val}
					<tr id="voucher_item_row_{$row_key}_{$row}">
						<td id="voucher_row_no_{$row_key}_{$row}">{$row+1}</td>
						<td align="center"><input type="text" name="voucher_code[{$row_key}][{$row}]" value="{$val}" /></td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		{/if}
	</td>
    <td class="r">{$item.selling_price|number_format:2}</td>
    <td align="center">{$item.valid_date_from}</td>
    <td align="center">{$item.valid_date_to}</td>
    <td class="r">{$item.point|number_format}</td>
    <td class="r">{$item.receipt_amount|number_format:2}</td>
    <td class="r">{$item.cash|number_format:2}</td>
    <td>
		<input size="5" name="qty[{$row_key}]" class="inp_qty" style="text-align:right;" id="qty,{$row_key}" onFocus="set_selected_item(this);" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if};recalc_all();{if $item.is_voucher && $item.voucher_value}assign_voucher_row('{$row_key}');{/if}" value="{$items.$row_key.qty|qty_nf}" />
		<input type="hidden" class="item_id" id="item_id,{$row_key}" value="{$item.id}" />
		<input type="hidden" id="sku_item_code,{$row_key}" value="{$item.sku_item_code}" />
		<input type="hidden" id="condition_pt,{$row_key}" value="{$item.point}" />
		<input type="hidden" id="condition_cash,{$row_key}" value="{$item.cash}" />
		<input type="hidden" id="condition_receipt,{$row_key}" value="{$item.receipt_amount}" />
		<input type="hidden" id="prev_qty,{$row_key}" name="prev_qty[{$row_key}]" value="{$items.$row_key.qty}" />
		<input type="hidden" id="voucher_value,{$row_key}" name="voucher_value[{$row_key}]" value="{$item.voucher_value|default:0}" />
	</td>
    {if $item.receipt_amount}
        <td><input size="5" name="receipt_no[{$row_key}]" id="receipt_no,{$row_key}" value="{$items.$row_key.receipt_no}" /></td>
		<td><input size="8" readonly name="receipt_date[{$row_key}]" id="receipt_date,{$row_key}" value="{$items.$row_key.receipt_date}" /></td>
		<td>
			<input size="5" name="counter_no[{$row_key}]" id="counter_no,{$row_key}" value="{$items.$row_key.counter_no}" />
			<input type="hidden" class="item_need_receipt" value="{$row_key}" />
		</td>
		<script>
		    Calendar.setup({literal}{{/literal}
		        inputField     :    "receipt_date,{$row_key}",     // id of the input field
		        ifFormat       :    "%Y-%m-%d",      // format of the input field
		        button         :    "receipt_date,{$row_key}",  // trigger for the calendar (button ID)
		        align          :    "Bl",           // alignment (defaults to "Bl")
		        singleClick    :    true
				//,
		        //onUpdate       :    load_data
		    {literal}}{/literal});
		</script>
    {else}
        <td></td><td></td><td></td>
    {/if}
</tr>
