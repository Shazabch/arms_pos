{*
4/26/2017 11:04 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}
<span class="badge badge-pill badge-info p-2">
	{$items_details.total_item|number_format} {$LNG.ITEMS}, {$items_details.total_ctn|number_format} {$LNG.CTN}, {$items_details.total_pcs|number_format} {$LNG.PCS}, {$config.arms_currency.symbol}{$items_details.ttl_amount|number_format:2}
</span>
