{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.
*}
<tr id="tr-pi_item_row-{$pi.counter_id}-{$pi.pos_id}-{$pi.pos_items_id}" class="pi_item_row">
	{if $allow_edit}
		<td>
			<span class="span_act">
				{if $pi.writeoff_by}
					<span class="link" onClick="TRADE_IN_WRITEOFF_MODULE.undo_writeoff(this);">
						<img src="/ui/icons/arrow_rotate_clockwise.png" align="absmiddle" title="Revert" border="0" /> Revert
					</span>
				{else}
					<span class="link" onClick="TRADE_IN_WRITEOFF_MODULE.set_writeoff(this);">
						<img src="/ui/icons/bin.png" align="absmiddle" title="Write-Off" border="0" /> Write-Off
					</span>
				{/if}
			</span>
			<span class="span_loading" style="display:none;background:yellow;padding:2px;">
				<img src="/ui/clock.gif" align="absmiddle" /> Loading...
			</span>
		</td>
	{/if}
	<td class="{if $pi.writeoff_by}col_writeoff{elseif $pi.sku_item_id}col_verified{/if}">
		{if $pi.writeoff_by}
			Write-Off
		{else}
			{if $pi.sku_item_id}
				Verified
			{else}
				New
			{/if}
		{/if}
	</td>
	<td>{receipt_no_prefix_format branch_id=$pi.branch_id counter_id=$pi.counter_id receipt_no=$pi.receipt_no}</td>

	<!-- Trade in info -->
	<td>{$pi.barcode|default:'-'}</td>
	<td>{$pi.sku_description|default:'-'}</td>
	<td>{$pi.more_info.trade_in.serial_no|default:'-'}</td>
	<td align="right">{$pi.qty}</td>
	<td align="right">{$pi.price|number_format:2}</td>
	<td>{$pi.trade_in_by_u|default:'-'}</td>
	<td class="{if $pi.writeoff_by}col_writeoff{/if}">{$pi.writeoff_by_u|default:'-'}</td>
	<td class="{if $pi.writeoff_by}col_writeoff{/if}">{$pi.writeoff_timestamp|ifzero:'-'}</td>

	<!-- verify info -->
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.sku_item_code|default:'-'}</td>
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.mcode|default:'-'}</td>
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.link_code|default:'-'}</td>
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.description|default:'-'}</td>
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.verify_code_by_u|default:'-'}</td>
	<td class="{if $pi.sku_item_id}col_verified{/if}">{$pi.verify_timestamp|ifzero:'-'}</td>
</tr>