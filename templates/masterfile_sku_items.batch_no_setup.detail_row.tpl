{*10/7/2011 4:01:21 PM Justin- Added delete row to capture item ID.9/22/2014 11:43 AM Justin- Enhanced to have decimal points while adding batch no depending on SKU item settings.*}<tr bgcolor="#cfecec" onmouseout="this.bgColor='#cfecec';" onmouseover="this.bgColor='#e0ffff';" class="batch_items{$item.grn_item_id}" id="batch_items{$item.id}">	<td align="right">		<img src="/ui/del.png" onclick="del_row(this, {$item.id});" title="Delete" align=absmiddle border=0>		<input type="hidden" name="batch_id[{$item.id}]" class="batch_id" value="{$item.id}">		<input type="hidden" name="grn_item_id[{$item.id}]" value="{$item.grn_item_id}">	</td>	<td colspan="4">		<b>Batch No:</b> <input size=15 type="text" name="batch_no[{$item.id}]" value="{$item.batch_no}" id="batch_no{$item.id}">&nbsp;&nbsp;&nbsp;&nbsp;		<b>Expired Date:</b> <input size=10 type="text" name="expired_date[{$item.id}]" value="{$item.expired_date|ifzero:''}" id="expired_date{$item.id}">		<img align=absmiddle src="ui/calendar.gif" width=17 border=0 id="e_date{$item.id}" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;		<script>		    Calendar.setup({literal}{{/literal}		        inputField     :    "expired_date{$item.id}",     // id of the input field		        ifFormat       :    "%Y-%m-%d",      // format of the input field		        button         :    "e_date{$item.id}",  // trigger for the calendar (button ID)		        align          :    "Bl",           // alignment (defaults to "Bl")		        singleClick    :    true		        //onUpdate       :    load_data		    {literal}}{/literal});		</script>		<div style="margin-top:-1.6em;text-align:right;white-space:nowrap;">			<b>Qty:</b>		</div>	</td>	<td align="right">		<input size=5 type="text" class="r" name="batch_qty[{$item.id}]" value="{$item.qty}" id="batch_qty{$item.id}" onchange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}">	</td></tr>