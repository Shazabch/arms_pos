{if $mm and !$mm_id}
	{assign var=mm_id value=$mm.id}
{/if}

<tr id="tr_mm_item-{$mm_id}" class="tr_mm_item">
	<td align="center">
		<input type="hidden" name="mm_id[{$mm_id}]" value="{$mm_id}" />
		
		<!-- Delete -->
		{if is_new_id($mm_id) or $mm_id eq '__TMP_MM_ID__'}
			<img src="/ui/del.png" align="absmiddle" class="clickable" onClick="POS_FORM.delete_mm_clicked('{$mm_id}');" />	
		{else}
			<input type="checkbox" name="mm_delete[{$mm_id}]" value="1" />
		{/if}
	</td>
	
	<!-- Type -->
	<td>
		<input type="text" size="50" name="mm_remark[{$mm_id}]" value="{$mm.remark}" />
	</td>
	
	<!-- Remark -->
	<td>
		{*<input type="text" size="25" name="mm_more_info[{$mm_id}]" value="{$mm.more_info}" />*}
		{capture assign=more_info_html}{strip}
		{if $mm.more_info.barcode}
			<div>
				{$mm.more_info.barcode}
			</div>
		{/if}
		
		{if $mm.more_info.qty>1 or $mm.more_info.alt_price_label}
			<div>
				{if $mm.more_info.unit_price}
					{$mm.more_info.unit_price|number_format:2}
				{elseif $mm.more_info.alt_price_label}
					{$mm.more_info.alt_price_label}
				{else}
					FOC
				{/if}
				&nbsp; x {$mm.more_info.qty|num_format:2}
			</div>
		{/if}
		
		{if is_array($mm.more_info.barcode_list) and $mm.more_info.barcode_list}
			<div>
				{foreach from=$mm.more_info.barcode_list key=barcode item=bc name=fbc}
					<span style="white-space:nowrap;">
						{if $bc.qty > 1}
							{$bc.qty|num_format:2} x
						{/if}
						{$barcode}
						{if $bc.price}
							 : {$bc.price|number_format:2}
						{/if}
						
					</span>
					
					{if !$smarty.foreach.fbc.last}<br />{/if}
				{/foreach}
			</div>
		{/if}
		{/strip}{/capture}
		{$more_info_html|default:'-'}
	</td>
	
	<!-- Amount -->
	<td>
		<input type="text" size="10" name="mm_amount[{$mm_id}]" value="{$mm.amount}" />
	</td>
</tr>