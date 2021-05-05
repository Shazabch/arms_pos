{*
8/26/2013 5:34 PM Justin
- Enhanced to allow user can pre-select payment type.
*}

{if $pp and !$pp_id}
	{assign var=pp_id value=$pp.id}
{/if}

<tr id="tr_pp_item-{$pp_id}" class="tr_pp_item">
	<td align="center">
		<input type="hidden" name="pp_id[{$pp_id}]" value="{$pp_id}" />
		
		<!-- Delete -->
		{if is_new_id($pp_id) or $pp_id eq '__TMP_PP_ID__'}
			<img src="/ui/del.png" align="absmiddle" class="clickable" onClick="POS_FORM.delete_pp_clicked('{$pp_id}');" />	
		{else}
			<input type="checkbox" name="pp_delete[{$pp_id}]" value="1" />
		{/if}
	</td>
	
	<!-- Type -->
	<td>
		<input id="pp_type_{$pp_id}" name="pp_type[{$pp_id}]" value="{$pp.type}"><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="POS_FORM.show_type_option($('pp_type_{$pp_id}').value, {$pp_id});">
		<div id="div_payment_type_list_{$pp_id}"></div>
		<input type="hidden" id="curr_pp_row_no_{$pp_id}" value="{$pp.type}" />
	</td>
	
	<!-- Remark -->
	<td>
		<input type="text" size="25" name="pp_remark[{$pp_id}]" value="{$pp.remark}" />
	</td>
	
	<!-- Amount -->
	<td>
		<input type="text" size="10" name="pp_amount[{$pp_id}]" value="{$pp.amount}" />
	</td>
</tr>