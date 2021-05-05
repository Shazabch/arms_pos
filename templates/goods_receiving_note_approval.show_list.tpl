{*
7/27/2011 10:16:21 AM Justin
- Enhanced to have ctn and return ctn fields.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.

3/25/2015 2:33 PM Justin
- Enhanced to have between nett selling price or GST selling price while doing current vs suggested selling price.
*}

{if $doc_type eq '2'}
	{assign var=item_id value=$form.non_sku_items.code.$n}
	{assign var=item_desc value=$form.non_sku_items.description.$n}
	{assign var=item_cost value=$form.non_sku_items.cost.$n}
	{assign var=item_check value=$form.non_sku_items.i_c.$n}
	{assign var=item_code value=$form.non_sku_items.code.$n}
	{assign var=item_qty value=$form.non_sku_items.qty.$n}
{else}
	{assign var=item_id value=$item.id}
	{assign var=item_desc value=$item.description}
	{assign var=item_check value=$item.item_check}
	{assign var=item_cost value=$item.cost}
	{assign var=item_code value=$item.sku_item_code}
	{assign var=item_qty value=$item.pcs+$item.ctn*$item.uom_fraction}
{/if}

{* if $doc_type eq '4'}
	{assign var=item_id value=$form.non_sku_items.code.$n|default:$item.sku_item_code}
	{assign var=item_desc value=$form.non_sku_items.description.$n}
	{assign var=item_check value=$form.non_sku_items.i_c.$n}
	{assign var=item_code value=$form.non_sku_items.code.$n|default:$item.sku_item_code}
	{assign var=item_qty value=$form.non_sku_items.qty.$n}
{else}
	{assign var=item_id value=$item.id}
	{assign var=item_desc value=$item.description}
	{assign var=item_check value=$item.item_check}
	{assign var=item_code value=$item.sku_item_code}
	{assign var=item_qty value=$item.pcs+$item.ctn*$item.uom_fraction}
	<input type="hidden" name="{$doc_type}_description[{$item_id}]" title="{$item_id}" value="{$item_desc}">
{/if *}

{if $doc_type < 3}
	<td nowrap width="2%" align="center">
		{if $item_check eq '1' || $item.return_pcs > 0}
			{if $item_check}
				<img src="ui/checked.gif" style="vertical-align:top;" title="This item has been set into item returned">
			{/if}
			<input type="hidden" name="{$doc_type}_item_return[{$item_id}]" id="{$doc_type}_item_return{$item_id}" value="{$item_check}">
			<input type="hidden" name="{$doc_type}_sku_item_code[{$item_id}]" title="{$item_id}" value="{$item_code}">
			<input type="hidden" name="{$doc_type}_description[{$item_id}]" title="{$item_id}" value="{$item_desc}">
			<input type="hidden" name="{$doc_type}_ctn[{$item_id}]" value="{$item.ctn}">
			<input type="hidden" name="{$doc_type}_pcs[{$item_id}]" value="{$item.pcs}">
			<input type="hidden" name="{$doc_type}_return_ctn[{$item_id}]" value="{$item.return_ctn}">
			<input type="hidden" name="{$doc_type}_return_pcs[{$item_id}]" value="{$item.return_pcs}">
			<input type="hidden" name="{$doc_type}_cost[{$item_id}]" value="{$item_cost|default:0}">
		{/if}
	</td>
{/if}

<td nowrap width="2%" align="center">
	<span class="{$doc_type}_no" id="{$doc_type}_no_{$smarty.foreach.fitem.iteration}" title="No. {$smarty.foreach.fitem.iteration}"> 
		{$row_counter}.
	</span>
</td>

{assign var=q value=$item.ctn+$item.pcs/$item.uom_fraction}
{assign var=amt value=$q*$item_cost}
<input type="hidden" name="{$doc_type}_amt[{$item_id}]" value="{$amt}">

{if $doc_type ne '2'}
	<td>{$item.sku_item_code}/{if $item.mcode}<br />{$item.mcode|default:"-"}{else}-{/if}</td>
	<td align="center">{$item.artno|default:"-"}</td>
	<td>{$item.description}</td>
	<td align="center">{$item.uom_code|default:"EACH"}</td>
	{if $doc_type eq '0'}
		<td align="center">{$item.po_uom|default:"-"}</td>
		<td class="r" width="5%">{$item.po_order_ctn|qty_nf|ifzero:"-"}<br />{if $item.po_foc_ctn}F:{$item.po_foc_ctn|qty_nf}{else}-{/if}</td>
		<td class="r" width="5%">{$item.po_order_pcs|qty_nf|ifzero:"-"}<br />{if $item.po_foc_pcs}F:{$item.po_foc_pcs|qty_nf}{else}-{/if}</td>
		{assign var=po_order_qty value=$item.po_order_ctn*$item.uom_fraction+$item.po_order_pcs}
		{assign var=po_foc_qty value=$item.po_foc_ctn*$item.uom_fraction+$item.po_foc_pcs}
	{else}
		<td class="r" width="5%">{$item.cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
		{if $doc_type eq 1 && $form.is_under_gst}
			{assign var=gst_id value=$item.gst_id}
			{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
			{assign var=gst_amt value=$gst_amt|round:$config.global_cost_decimal_points}
			<td align="right" nowrap>
				{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
				({$gst_amt|number_format:$config.global_cost_decimal_points})
			</td>
		{/if}
	{/if}
	{if $doc_type ne '3'}
		<td class="r" width="5%"><div id="{$doc_type}_ctn{$item_id}" class="r">{$item.ctn|qty_nf}</div></td>
		<td class="r" width="5%"><div id="{$doc_type}_pcs{$item_id}" class="r">{$item.pcs|qty_nf}</div></td>
		{if $doc_type eq '0'}
			<td class="r" width="5%"><div id="{$doc_type}_return_ctn{$item_id}" class="r">{$item.return_ctn|qty_nf}</div></td>
			<td class="r" width="5%"><div id="{$doc_type}_return_pcs{$item_id}" class="r">{$item.return_pcs|qty_nf}</div></td>
			<td nowrap align="right">
				{assign var=qty_var value=$item_qty-$po_order_qty-$item.return_ctn*$item.uom_fraction-$item.return_pcs}
				{if $qty_var > 0}
					{assign var=foc_var value=$qty_var-$po_foc_qty}
					{if $foc_var > 0}
						{assign var=qty_var value=$foc_var}
						{assign var=foc_var value=0}
					{else}
						{assign var=qty_var value=0}
					{/if}
				{else}
					{assign var=foc_var value=$po_foc_qty*-1}
				{/if}
				<div id="{$doc_type}_qty_var{$item_id}" class="r">
					<span class={if $qty_var>0}pv{elseif $qty_var<0}nv{else}r{/if}>{if $qty_var}{$qty_var|qty_nf}{/if}</span><br />
					{if $foc_var}F:<span class={if $foc_var<0}nv{/if}>{$foc_var|qty_nf}</span>{/if}
					<br />
					{* if $qty_var>0 && $item_check ne '1' && $item.po_item_id ne '0' && $item.po_qty ne '0'}
						<img width="18" src="ui/lorry.png" style="vertical-align:top;" class="clickable" title="Add Return Row" onclick="ajax_add_variance_item('{$item.sku_item_id}', '{$qty_var}', '{$item_id}', '{$doc_type}')" align="absmiddle" alt="{$item_id}">
					{/if *}
				</div>
			</td>
			<td width="15%">
				<div class="crop" style="height:2em;">
					{$item.po_no} - 
					{if $item.item_group eq '0'}
						Undelivered item
					{elseif $item.item_group eq '1'}
						Matched with PO item
					{else}
						Matched with PO item's Parent
					{/if}
				</div>
			</td>
		{/if}
		{if $doc_type eq 1}
			<td nowrap align="right">{$amt|number_format:2}</td>
			{if $form.is_under_gst}
				{assign var=row_gst value=$gst_amt*$q}
				{assign var=row_gst value=$row_gst|round2}
				{assign var=row_gst_amt value=$amt+$row_gst}
				{assign var=row_gst_amt value=$row_gst_amt|round2}
				<td nowrap align="right">{$row_gst|number_format:2}</td>
				<td nowrap align="right">{$row_gst_amt|number_format:2}</td>
			{/if}
		{/if}
	{/if}
{else}
	<td>{$item_code}</td>
	<td>{$item_desc}</td>
	<td class="r">{$item_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
	<td class="r" width="5%">{$item_qty|qty_nf|ifzero:'-'}</td>
{/if}
{if $doc_type eq '3'}
	<td class="r">{$item.curr_selling_price|number_format:2}</td>
	{if $form.is_under_gst && $item.inclusive_tax eq 'yes'}
		{assign var=selling_price value=$item.gst_selling_price}
	{else}
		{assign var=selling_price value=$item.selling_price}
	{/if}
	<td class="r">{$selling_price|number_format:2}</td>
	{if $config.grn_check_selling_price}
		<td>{$item.reason}</td>
	{/if}
{/if}
