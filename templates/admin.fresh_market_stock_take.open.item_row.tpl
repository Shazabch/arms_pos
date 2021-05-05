{*
6/8/2011 10:51:55 AM Andy
- Add artno column at stock take.

9/28/2011 10:55:45 AM Justin
- Modified the Ctn and Pcs round up to base on config set.
*}

<tr id="tr_new_added_item_{$item.branch_id}_{$item.id}">
	<td>
		<a href="javascript:void(ajax_delete_added_item('{$item.branch_id}', '{$item.id}'));">
			<img src="ui/deact.png" border="0" title="Delete" id="img_new_added_item_{$item.branch_id}_{$item.id}" />
		</a>
	</td>
	<td>{$item.sku_item_code|default:'-'}</td>
	<td>{$item.artno|default:'-'}</td>
	<td>{$item.description|default:'-'}</td>
	<td>{$item.uom_code}</td>
	<td class="r">
	    <span id="span_added_item_loading_{$item.branch_id}_{$item.id}"></span>
		<input type="text" style="width:80px;text-align:right;" value="{$item.qty|qty_nf}" onChange="{if $item.doc_allow_decimal}this.value=float(round(this.value, {$config.global_qty_decimal_points})){else}mi(this){/if};update_new_added_item('{$item.branch_id}', '{$item.id}', this);" />
	</td>
</tr>
