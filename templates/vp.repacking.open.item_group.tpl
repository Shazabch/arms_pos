<div id="div_item_group-{$group_id}" class="div_item_group stdframe" style="margin-bottom:20px;">
	<div style="float:right">
		{if $can_edit}
			<img src="ui/del.png" class="clickable" onClick="REPACKING_FORM.remove_item_group_clicked('{$group_id}');" title="Delete Group" />
		{/if}
	</div>
	
	<h3>Lose Item ( - )</h3>
	
	<table width="100%" class="report_table" style="background-color:#fff;">
		<tr class="header">
			<th width="20">&nbsp;</th>
			<th width="100">ARMS Code</th>
			<th width="80">Art No.</th>
			<th width="80">MCode</th>
			<th>Description</th>
			<th width="50">Cost</th>
			<th width="50">Qty</th>
			<th width="50">Total Cost</th>
		</tr>
		<tbody id="tbody_lose_item_list-{$group_id}">
			{foreach from=$item_list.lose item=item name=fli}
				{include file="vp.repacking.open.item_group.lose_item_row.tpl" row_id=$smarty.foreach.fli.iteration}
			{/foreach}
		</tbody>
		<tfoot>
			<tr class="header">
				<th colspan="7" align="right">Total</th>
				<th>
					<span id="span_lose_total_cost-{$group_id}">-</span>
					<input type="hidden" id="inp_lose_total_cost-{$group_id}" />
				</th>
			</tr>
		</tfoot>		
	</table>
	
	{if $can_edit}
		<button onClick="REPACKING_FORM.add_lose_item_clicked('{$group_id}');" id="btn_add_new_lose_item-{$group_id}">+</button>
		<span id="span_add_new_lose_item_loading-{$group_id}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	{/if}
	
	<h3>Pack Item ( + )</h3>
	<ul>
		<li> You can only have 1 pack item.</li>
	</ul>
	<table width="100%" class="report_table" style="background-color:#fff;">
		<tr class="header">
			<th width="20">&nbsp;</th>
			<th width="100">ARMS Code</th>
			<th width="80">Art No.</th>
			<th width="80">MCode</th>
			<th>Description</th>
			<th width="50">Misc Cost</th>
			<th width="50">Calculated Cost [<a href="javascript:void(alert(' (Total cost from all Lose Item + Misc Cost) / Pack Item Qty'))">?</a>]</th>
			<th width="50">Qty</th>
			<th width="50">Total Cost</th>
		</tr>
		<tbody id="tbody_pack_item_list-{$group_id}">
			{foreach from=$item_list.pack item=item name=fpi}
				{include file="vp.repacking.open.item_group.pack_item_row.tpl" row_id=$smarty.foreach.fpi.iteration}
			{/foreach}
		</tbody>
	</table>
	
	{if $can_edit}
		<button onClick="REPACKING_FORM.add_pack_item_clicked('{$group_id}');" id="btn_add_new_pack_item-{$group_id}">+</button>
		<span id="span_add_new_pack_item_loading-{$group_id}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	{/if}
</div>
