<div id="div_category_matrix" style="padding:2px; {if $use_matrix ne 'yes'}display:none;{/if}">
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
					<input type="checkbox" name="category_matrix[{$size}][set_override]" value="1" title="NRR" onChange="category_matrix_override_changed('{$size}');" id="inp_category_matrix_override-{$size}" {if $cat_matrix.is_nnr.$size}checked{/if} {if !$is_edit}disabled {/if} />
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
						<input type="text" size="2" name="cat_matrix[min_qty][{$clr}][{$size}]" class="r category_matrix_min_qty-{$size}" value="{$cat_matrix.min_qty.$clr.$size}" id="inp-cat_matrix_value-min_qty-{$clr}-{$size}" {if $cat_matrix.is_nnr.$size}readonly{/if} {if !$is_edit}disabled {/if} onchange="mi(this);" />
					</td>
					<td align="center" nowrap>
						<input type="text" size="2" name="cat_matrix[max_qty][{$clr}][{$size}]" class="r" value="{$cat_matrix.max_qty.$clr.$size}" id="inp-cat_matrix_value-max_qty-{$clr}-{$size}" {if !$is_edit}disabled {/if} onchange="mi(this);" />
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>