{if $promo_disc_condition.item_type eq 'sku'}
	<b>SKU</b>
	{$promo_disc_condition.item_info.sku_item_code|default:'-'} /
	{$promo_disc_condition.item_info.artno|default:'-'} /
	{$promo_disc_condition.item_info.description|default:'-'}
{elseif $promo_disc_condition.item_type eq 'brand'}
	<b>Brand</b>
	{$promo_disc_condition.item_info.description|default:'UN-BRANDED'}
{elseif $promo_disc_condition.item_type eq 'category'}
	<b>Category</b>
	{$promo_disc_condition.item_info.description|default:'UN-CATEGORIZE'}
{elseif $promo_disc_condition.item_type eq 'category_brand'}
	  <!-- Category + Brand Discount -->
	<b>Category:</b>
	{$promo_disc_condition.item_info.cat_desc|default:'UN-CATEGORIZE'}
	<b>+</b>
	<b>Brand:</b>
	{$promo_disc_condition.item_info.brand_desc|default:'UN-BRANDED'}
{else}
	<b><img src="/ui/messages.gif" align="absmiddle" /> Unknown Type</b>
{/if}