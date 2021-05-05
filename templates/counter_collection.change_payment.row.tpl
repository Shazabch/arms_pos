{*
8/17/2011 5:53:53 PM Andy
- Fix counter collection adjustment add coupon not working.

2/5/2013 3:18 PM Fithri
- add adjusted payment receipt can revert to old payment type

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

06/09/2016 14:30 Edwin
- Reconstruct on payment type

7/13/2018 4:14 PM Justin
- Enhanced to show actual label for "Currency Adjust".
*}

<tr class="tr_receipt_row">
	<td>{receipt_no_prefix_format branch_id=0 counter_id=0 receipt_no=$receipt_array.$pos_id|default:$receipt_no}
		<img src="/ui/add.png" align="absmiddle" onclick="add_payment_type('{$pos_id}');">
	</td>
	<td>
		<table id="paytype_table[{$pos_id}]" cellspacing="0" border="0">
			{foreach name=j from=$items item=item}
				{include file=counter_collection.change_payment.row2.tpl}
				{assign var=ppindex value=$smarty.foreach.j.iteration}
			{/foreach}
			<input type="hidden" name="paytype_index[{$pos_id}]" value="{$ppindex}">
			<input type="hidden" name="add_payment_index[{$pos_id}]" value="{$ppindex}">
		</table>
	</td>
	<td valign="top">
		<table cellspacing="0" border="0">
		{foreach from=$oitems.$pos_id item=item}
			<tr>
				<td style="border:0px;">
					{assign var=pt value=$item.type}
					{if $pt eq 'Currency_Adjust'}
						Currency Adjust
					{else}
						{$pos_config.payment_type_label.$pt|default:$pt}
					{/if}
				</td>
				<td style="border:0px;">{$item.payment_amount|number_format:2}</td>
			</tr>
		{/foreach}
		</table>
		{if $oitems.$pos_id}
			<input type="button" value="Revert to original" onclick="ajax_revert_to_original({$item.id},this);" />
		{/if}
	</td>
</tr>
