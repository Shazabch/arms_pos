{*
2/24/2012 12:11:43 PM Justin
- Fixed the bugs where last S/N table is show after the footer
 
10/16/2013 4:02 PM Justin
- Enhanced to have new feature that can auto generate S/N (need config).

11/21/2013 5:10 PM Justin
- Enhanced to keep hidden for SKU ID.

3/23/2017 3:44 PM Justin
- Enhanced to have DO ID and Branch ID by keeping it as hidden.

5/23/2019 9:51 AM William
- Enhance "GRN" word to use report_prefix.
*}
{if count($sn_items)>0} 
	{foreach from=$sn_items item=sku_items key=grn_id name=grn}
		<div id="sn_item{$grn_id}">
		<fieldset width="100%">
		<legend><b><font size="2">{$ibt.$grn_id.report_prefix}{$grn_id|string_format:"%05d"}</font></b></legend>
		<input type="hidden" value="{$ibt.$grn_id.is_ibt}" name="ibt[{$grn_id}][is_ibt]">
		<input type="hidden" value="{$ibt.$grn_id.do_id}" name="ibt[{$grn_id}][do_id]">
		<input type="hidden" value="{$ibt.$grn_id.do_branch_id}" name="ibt[{$grn_id}][do_branch_id]">
		{foreach from=$sku_items item=sitem key=sid name=sku_item}
			{if $smarty.foreach.sku_item.iteration%2 ne 0}
				<table width="100%">
					<tr>
			{/if}
			<td width="50%" valign="top">
				<fieldset class="stdframe">
					<legend><b><font size="2">{$sitem.sku_item_code}</font></b></legend>
					<div class="div_sel">
					<table>
						{if count($errm.top.$grn_id.$sid) > 0}
							<div id="err"><div class="errmsg"><ul>
							{foreach from=$errm.top.$grn_id.$sid item=e}
							<li> {$e}
							{/foreach}
							</ul></div></div>
						{/if}
						<tr></tr>
						<tr valign="top">
							<td align="left" width="80%">
								<b>Description:</b> {$sitem.sku_description}<br /><br />
								<div id="bal_qty{$grn_id}_{$sid}">
									<b>Rcv Qty (Pcs):</b> {$sitem.qty}
									{if $sitem.ttl_sn != 0}
										{assign var=bal_qty value=$sitem.qty-$sitem.ttl_sn}
										{if $bal_qty < 0}
											<b><font color="#ff0000">(Over {$bal_qty|abs} S/N)</font></b>
										{else}
											({$bal_qty} Pcs remaining)
										{/if}
									{/if}
								</div>
								<input type="hidden" name="sku_item_id[{$grn_id}][{$sid}]" value="{$sitem.sku_item_id}">
								<input type="hidden" name="sku_id[{$grn_id}][{$sid}]" value="{$sitem.sku_id}">
								<input type="hidden" name="qty[{$grn_id}][{$sid}]" value="{$sitem.qty}">
								<input type="hidden" name="ttl_sn[{$grn_id}][{$sid}]" value="{$sitem.ttl_sn}">
							</td>
							<td width="20%" onchange="recalc_qty_used('{$grn_id}', '{$sid}');">
								<textarea name="sn[{$grn_id}][{$sid}]" cols="20" rows="15">{$sitem.sn}</textarea>
							</td>
							{if $config.masterfile_auto_generate_sn}
								<td>
									<input type="button" name="ag_sn_btn" value="Auto Generate" onclick="auto_generate_sn('{$sid}', '{$grn_id}');" />
								</td>
							{/if}
						</tr>
					</table>
					</div>
				</fieldset>
			</td>
			{if $smarty.foreach.sku_item.last && $smarty.foreach.sku_item.iteration%2 ne 0}
			<td width="50%"></td>
			{/if}
			{if $smarty.foreach.sku_item.iteration%2 eq 0 || $smarty.foreach.sku_item.last}
					</tr>
				</table>
			{/if}
		{/foreach}
		</fieldset>
		</div>
	{/foreach}
{/if}
