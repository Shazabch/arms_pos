<tr id="tr_package_items-{$item_guid}" item_guid="{$item_guid}" class="tr_package_items">
	{if $can_edit}
		<td nowrap>
			<input type="hidden" name="package_items[{$item_guid}][item_guid]" value="{$item_guid}" />
			
			{* Delete *}
			<img src="ui/del.png" title="Delete" class="clickable" onClick="MEMBERSHIP_PACKAGE.remove_item('{$item_guid}');" />
			
			{* Up *}
			<img src="/ui/icons/arrow_up.png" title="Move Up" class="clickable img_move_up" onClick="MEMBERSHIP_PACKAGE.move_item('{$item_guid}', 'up');" />
			
			{* Down *}
			<img src="/ui/icons/arrow_down.png" title="Move Down" class="clickable img_move_down" onClick="MEMBERSHIP_PACKAGE.move_item('{$item_guid}', 'down');" />
		</td>
	{/if}
	
	{* Title *}
	<td>
		<input  type="text" name="package_items[{$item_guid}][title]" style="width:200px;" maxlength="100" value="{$item.title|escape:html}" title="Item Title" class="required form-control" />
	</td>
	
	{* Description *}
	<td>
		<input type="text" name="package_items[{$item_guid}][description]" style="width:100%;" maxlength="200" value="{$item.description|escape:html}" title="Item Description" class="required form-control" />
	</td>
	
	{* Remark *}
	<td>
		<textarea class="form-control" name="package_items[{$item_guid}][remark]" rows="2" cols="50">{$item.remark|escape:html}</textarea>
	</td>
	
	{* Entry Needed *}
	<td align="center">
		<input type="text" name="package_items[{$item_guid}][entry_need]" style="width:80px;text-align:right;" value="{$item.entry_need|ifzero:''}" onChange="mi(this, 1, 1);" title="Item Entry Needed" class="required form-control" />
	</td>
	
	{* Max Redeem *}
	<td align="center">
		<input class="form-control" type="text" name="package_items[{$item_guid}][max_redeem]" style="width:80px;text-align:right;" value="{$item.max_redeem|ifzero:''}" onChange="mi(this, 1, 1);" />
	</td>
</tr>