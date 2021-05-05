<div id="sn_item{$item.id}">
<br />
<fieldset class="stdframe" style="width:99%;">
	<legend><b><font size="2">{$item.sku_item_code}</font></b></legend>
	<div class="div_sel">
	<table>
		{assign var=iid value=$item.id}
		{if count($errm.sn.$iid) > 0}
			<div id="err"><div class="errmsg"><ul>
			{foreach from=$errm.sn.$iid item=err_msg key=row}
					<li valign="bottom"> {$err_msg}
			{/foreach}
			</ul></div></div>
		{/if}
		<tr></tr>
		<tr valign="top">
			<td align="left" nowrap>
				<b>Description:</b> {$item.description}<br /><br />
				<b>Qty (Pcs):</b> 
				<span id="bal_qty_{$item.id}">
				{assign var=qty value=$item.qty|abs}

				{$qty|default:0}
				{if $item.ttl_sn ne 0}
					{assign var=bal_qty value=$qty-$item.ttl_sn}
					{if $bal_qty < 0}
						<b><font color="#ff0000">(Over {$bal_qty} S/N)</font></b>
					{elseif $bal_qty > 0}
						({$bal_qty} qty remaining)
					{/if}
				{/if}
				</span>
				<input type="hidden" name="sn_item_id[{$item.id}]" value="{$item.id}">
				<input type="hidden" name="sn_sku_item_id[{$item.id}]" value="{$item.sku_item_id}">
				<input type="hidden" name="sn_rcv_qty[{$item.id}]" value="{$qty|abs|default:0}">
				<input type="hidden" name="ttl_sn[{$item.id}]" value="{$item.ttl_sn}">
			</td>
			<td width="30" >
				<textarea name="sn[{$item.id}]" cols="20" rows="15" onchange="recalc_sn_used('{$item.id}');">{$item.serial_no}</textarea>
			</td>
		</tr>
	</table>
	</div>
</fieldset>
</div>
