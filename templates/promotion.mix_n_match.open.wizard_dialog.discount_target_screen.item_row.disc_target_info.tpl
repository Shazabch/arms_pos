{*
3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.
*}

{capture assign=element_name_extend}[{$row_num|default:'tmp_row_num'}]{/capture}

{if $promo_item.disc_target_type eq 'special_foc'}
	<input type="text" name="disc_target_info{$element_name_extend}[description]" value="{$promo_item.disc_target_info.description}" style="width:200px;" />
{elseif $promo_item.disc_target_type eq 'sku'}
	<!-- SKU Discount -->
	<b>SKU:</b>
	{$promo_item.item_info.sku_item_code|default:'-'} /
	{$promo_item.item_info.artno|default:'-'} /
	{$promo_item.item_info.description|default:'-'}
	<br>
	Selling: {$promo_item.disc_target_info.selling_price|default:'0'|number_format:2}<br>
	Cost: {$promo_item.disc_target_info.cost_price|default:'0'|number_format:2}<br>
	{if $promo_item.disc_target_info.include_parent_child}
	Include Parent & Child
		<input type="hidden" name="disc_target_info{$element_name_extend}[include_parent_child]" value="{$promo_item.disc_target_info.include_parent_child}" />
	{/if}
	<!-- Price Type: {$promo_item.disc_target_info.price_type|default:'-'}<br> -->
	<input type="hidden" name="disc_target_info{$element_name_extend}[selling_price]" value="{$promo_item.disc_target_info.selling_price}" />
	<input type="hidden" name="disc_target_info{$element_name_extend}[cost_price]" value="{$promo_item.disc_target_info.cost_price}" />
	<input type="hidden" name="disc_target_info{$element_name_extend}[price_type]" value="{$promo_item.disc_target_info.price_type}" />
{elseif $promo_item.disc_target_type eq 'category'}
	<!-- Category Discount -->
	<b>Category:</b>
	{$promo_item.item_info.description|default:'UN-CATEGORIZE'}
{elseif $promo_item.disc_target_type eq 'brand'}
	<!-- Brand Discount -->
	<b>Brand:</b>
	{$promo_item.item_info.description|default:'UN-BRANDED'}
{elseif $promo_item.disc_target_type eq 'category_brand'}
    <!-- Category + Brand Discount -->
	<b>Category:</b>
	{$promo_item.item_info.cat_desc|default:'UN-CATEGORIZE'}
	<br />+<br />
	<b>Brand:</b>
	{$promo_item.item_info.brand_desc|default:'UN-BRANDED'}
{else}
    <img src="/ui/messages.gif" align="absmiddle" /> <b>Invalid Discount</b>
{/if}