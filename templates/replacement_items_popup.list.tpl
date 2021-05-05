{if !$replacement_items}No Replacement Item Found
{else}
	<form name="f_ri" onSubmit="return false;">
		<div style="height:85%;border:1px solid black;background:#fcfcfc;overflow-x:hidden;overflow-y:auto;">
		    <table width="100%" class="report_table">
		        <tr class="header">
		            <th>&nbsp;</th>
		            <th>ARMS Code</th>
		            <th>Artno / MCode</th>
		            <th>Description</th>
		            <th>Stock Balance</th>
		            <th>Selling</th>
					{if $sessioninfo.privilege.SHOW_COST}
		            	<th>Cost</th>
		            {/if}
		        </tr>
		        {foreach from=$replacement_items item=r}
		            <tr>
		                <td>
							{if $settings.can_confirm_item}
								<input type="radio" name="sku_item_id" value="{$r.sku_item_id}" />
							{/if}
						</td>
		                <td>
							{if $settings.can_click_item_row}
							    <a href="javascript:void(replacement_item.item_code_clicked('{$r.sku_item_id}','{$r.sku_item_code}'));">
									{$r.sku_item_code}
								</a>
							{else}
                                {$r.sku_item_code}
							{/if}
						</td>
		                <td>{$r.artno_mcode|default:'-'}</td>
		                <td>{$r.description|default:'-'}</td>
		                <td class="r">{$r.qty|num_format:2}</td>
		                <td class="r">{$r.selling_price|number_format:2}</td>
		                {if $sessioninfo.privilege.SHOW_COST}
		                    <td class="r">{$r.cost|number_format:2}</td>
		                {/if}
		            </tr>
		        {/foreach}
		    </table>
		</div>
		<div align="center">
		    {if $settings.can_confirm_item}
				<input type="button" value="Confirm" style="width:80px;" onClick="replacement_item.confirm_selected_item();" />
			{/if}
			<input type="button" value="Close" style="width:80px;" onClick="replacement_item.popup_close();" />
		</div>
	</form>
{/if}
