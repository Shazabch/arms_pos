<div id="div_sku_matrix" style="padding:2px; {if $use_matrix ne 'yes'}display:none;{/if}">
	<table class="report_table">
		<tr class="header">
			<th rowspan="3">Color</th>
			{capture name="size_count"}
				{count var=$size_list}
			{/capture}
			<th colspan="{$smarty.capture.size_count*2}">Size</th>
		</tr>
		
		<!-- size list -->
		<tr class="header">
			{foreach from=$size_list key=arr1 item=size name=s}
				<th colspan="2">{$size}</th>
			{/foreach}
		</tr>

		<tr class="header">
			{foreach from=$size_list key=arr1 item=size name=s}
				<th>
					Min Qty
					<input type="checkbox" name="sku_matrix[{$size}][set_override]" value="1" title="NRR" onChange="sku_matrix_override_changed('{$size}');" id="inp_sku_matrix_override-{$size}" {if $sku_matrix.is_nnr.$size}checked{/if} {if !$is_edit}disabled {/if} />
				</th>
				<th>Max Qty</th>
			{/foreach}
		</tr>
		
		<!-- color list -->
		{foreach from=$clr_list key=arr1 item=clr name=c}
			<tr>
				<td><b>{$clr}</b></td>
				{foreach from=$size_list key=arr2 item=size name=s}
					<td align="center" nowrap>
						<input type="text" size="2" name="sku_matrix[min_qty][{$clr}][{$size}]" class="r sku_matrix_min_qty-{$size}" value="{$sku_matrix.min_qty.$clr.$size}" id="inp-sku_matrix_value-min_qty-{$clr}-{$size}" {if $sku_matrix.is_nnr.$size}readonly{/if} {if !$is_edit}disabled {/if} onchange="mi(this);" />
					</td>
					<td align="center" nowrap>
						<input type="text" size="2" name="sku_matrix[max_qty][{$clr}][{$size}]" class="r" value="{$sku_matrix.max_qty.$clr.$size}" id="inp-sku_matrix_value-max_qty-{$clr}-{$size}" {if !$is_edit}disabled {/if} onchange="mi(this);" />
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>