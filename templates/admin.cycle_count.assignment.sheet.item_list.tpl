<table class="report_table" id="tbl_item_list" width="100%" style="background-color: #fff;">
	<tr class="header">
		<th width="40">No.</th>
		<th>ARMS Code</th>
		<th>MCode</th>
		<th>Art No</th>
		<th>{$config.link_code_name}</th>
		<th>Description</th>
		
		{if $form.wip}
			<th width="100">Backend Qty</th>
			<th width="100">Mobile App Qty</th>
			<th width="100">Total Qty</th>
			<th width="150">First Stock Take Time</th>
			
			{if $form.completed}
				<th width="100">POS Qty [<a href="javascript:void(CC_SHEET.show_pos_qty_notify());">?</a>]</th>
				<th width="100">Calculated Stock Take Qty [<a href="javascript:void(CC_SHEET.show_st_qty_notify());">?</a>]</th>
				<th width="100">System Stock</th>
				<th width="100">Variance</th>
			{/if}
		{/if}
	</tr>
	
	{foreach from=$item_list key=item_id item=r}
		<tr id="tr_item-{$item_id}" class="tr_item {if $smarty.request.highlight_sku_item_id eq $r.sku_item_id}highlight_row{/if}">
			<td>{$item_id}.
				<input type="hidden" class="inp_sku_item_id inp_sku_item_id-{$r.sku_item_id}" id="inp_sku_item_id-{$item_id}" value="{$r.sku_item_id}" />
				<input type="hidden" class="inp_doc_allow_decimal" id="inp_doc_allow_decimal-{$item_id}" value="{$r.doc_allow_decimal}" />
			</td>
			<td>{$r.sku_item_code}</td>
			<td>{$r.mcode|default:'-'}</td>
			<td>{$r.artno|default:'-'}</td>
			<td>{$r.link_code|default:'-'}</td>
			<td nowrap>{$r.description|default:'-'}</td>
			
			{if $form.wip}
				{* Backend Qty *}
				<td align="right">
					<div style="text-align:center;{if !$can_edit}display:none;{/if}">
						<input type="text" class="inp_backend_qty" id="inp_backend_qty-{$item_id}" value="{$r.backend_qty}" onChange="CC_SHEET.backend_qty_changed('{$item_id}');" />
					</div>
					{if !$can_edit}
						{$r.backend_qty}
					{/if}
				</td>
				
				{* Mobile App Qty *}
				<td align="right">
					<input type="hidden" class="inp_app_qty" id="inp_app_qty-{$item_id}" value="{$r.app_qty}" />
					<span id="span_app_qty-{$item_id}">{$r.app_qty}</span>
				</td>
				
				{assign var=row_qty value=""}
				{if $r.backend_qty === 0 || $r.backend_qty > 0}
					{assign var=row_qty value=$row_qty+$r.backend_qty}
				{/if}
				{if $r.app_qty === 0 || $r.app_qty > 0}
					{assign var=row_qty value=$row_qty+$r.app_qty}
				{/if}
				
				{* Total Qty *}
				<td class="td_total_qty" align="right"><span id="span_total_qty-{$item_id}">{$row_qty}</span></td>
				
				{* First Stock Take Time *}
				<td align="center"><span id="span_st_time-{$item_id}">{$r.st_time|ifzero:'&nbsp;'}</span></td>
				
				{if $form.completed}
					{* POS Qty *}
					<td align="right"><span id="span_pos_qty-{$item_id}">{$r.pos_qty|default:'0'}</span></td>
					
					{* Calculated Stock Take Qty *}
					<td align="right" class="td_calculated_st_qty"><span id="span_calculated_st_qty-{$item_id}">{$r.calculated_st_qty}</span></td>
					
					{* System Stock *}
					<td align="right">{$r.stock_balance}</td>
					
					{* Variance *}
					<td align="right" class="{if $r.st_variance > 0}positive_value{elseif $r.st_variance < 0}negative_value{/if}">{$r.st_variance}</td>
				{/if}
			{/if}
		</tr>
	{/foreach}
</table>