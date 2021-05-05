{*
5/17/2019 10:41 AM William
- Enhance "GRA" word to use report_prefix.
*}
{count var=$items} record(s) found :
<hr />
<div>
	<ul style="list-style-type:none;margin:0;padding:0;">
	{foreach from=$items key=r item=item}
		<li style="pointer:cursor;display:block;margin:0;padding:2px;" onmouseover="this.style.backgroundColor='#ff9'" onmouseout="this.style.backgroundColor=''">
			{$item.description}
			<input type="checkbox" name="dispose_item[{$item.id}]" value="{$item.branch_id}" class="disposal_item" />
			<font color=#009911>{$item.report_prefix}{$item.id|string_format:"%05d"} ({$item.bcode})</font>
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span class=small>
			<font color=blue>Vendor:</font> {$item.vd_code|default:"-"}
			<font color=blue>SKU Type:</font> {$item.sku_type|default:"-"}
			<font color=blue>Department:</font> {$item.dept_code|default:"-"}
		</span>
	{/foreach}
	</ul>
	<input type=hidden id="tmp_grn_barcode" value="{$grn_barcode}">
	<input type=hidden id="tmp_cost" value="{$cost}">
	<input type=hidden id="tmp_qty" value="{$qty}">
</div>
