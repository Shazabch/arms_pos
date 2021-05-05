{*
4/26/2018 4:22 PM Justin
- Enhanced to have foreign currency feature.

5/24/2019 2:04 PM William
- Enhance "GRN" word to use report_prefix.
*} 

<ul style="list-style: none;">
	{foreach from=$vendor_item name=v key=vid item=r}
		<li style="margin:0;">
			<input type="radio" {if $smarty.foreach.v.first}checked{/if} name="vendor_id" value="{$vid}" id="vendor_id_{$vid}" onclick="gst_changed({$vid});"> {$r.vendor} ({if !$grn_list.$vid}{$r.source}, Cost:{$r.cost_price}, {/if}ARMS Code:{$r.sku_item_code} , Artno:{$r.artno})<br />
			<b>SKU Type:</b> <input readonly name="sku_type" value="{$r.sku_type}">
			{if $grn_list.$vid}
				&nbsp; &nbsp; <b>Invoice / DO No.</b>:
				<select name="doc_no_sel[{$vid}]" id="doc_no_sel_{$vid}" onchange="gst_changed({$vid});">
					{foreach from=$grn_list.$vid name=g key=grn_id item=grn}
						<option value="{$grn.doc_no}" doc_date="{$grn.doc_date}" cost_price="{$grn.cost_price}" gst_id="{$grn.gst_id}" currency_code="{$grn.currency_code}">{$grn.type} - {$grn.doc_no} ({$grn.report_prefix}{$grn_id|string_format:"%05d"}, Cost Price: {if $grn.currency_code}{$grn.currency_code} {/if}{$grn.cost_price|number_format:$config.global_cost_decimal_points})</option>
					{/foreach}
				</select>
			{/if}
			
			<input type="hidden" name="grn_id_{$vid}" value="{$r.ref_id}" />
			<input type="hidden" name="branch_id_{$vid}" value="{$r.branch_id}" />
			<input type="hidden" name="mst_gst_id_{$vid}" id="mst_gst_id_{$vid}" value="{$gst_info.id}" />
			<input type="hidden" name="mst_cost_price_{$vid}" id="mst_cost_price_{$vid}" value="{$r.cost_price}" />
		</li>
		{assign var=sku_type value=$r.sku_type}
		{if $smarty.foreach.v.first}
			{assign var=first_vid value=$vid}
		{/if}
	{/foreach}
</ul>

<script>
{if $sku_type eq 'CONSIGN'}
	Element.hide('tr_cost');
{else}
	Element.show('tr_cost');
{/if}

gst_changed('{$first_vid}');
</script>