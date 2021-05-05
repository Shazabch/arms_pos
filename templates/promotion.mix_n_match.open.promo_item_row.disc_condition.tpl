{*
4/6/2011 2:38:06 PM Andy
- Change "per" to "for".

11/11/2013 10:38 AM Andy
- Enhance to can select SKU Group as Discount Target and Condition Rule.

1/16/2014 5:58 PM Andy
- Add to capture brand description, sku group code/description and store in database for frontend counter use.

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.

7/15/2015 3:07 PM Andy
- Enhanced to show category/brand ID tooltips for category/brand description.
*}

<li class="is_condition_row" condition_row_num="{$condition_row_num|default:'tmp_row_num'}">
	{capture assign=element_name_extend}[{$group_id|default:'tmp_group_id'}][{$item_id|default:'tmp_item_id'}]{/capture}
	
	{if $allow_edit}
		<img src="/ui/icons/delete.png" title="Delete Condition" align="absmiddle" class="clickable" onClick="delete_disc_condition_row(this);" />
	{/if}
	<!-- Over or Every -->
	<select name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][rule]" {if $disabled}disabled {/if} onChange="MIX_MATCH_MAIN_MODULE.check_disc_condition_rule('{$item_id|default:'tmp_item_id'}');">
		<option value="over_equal" {if $promo_disc_condition.rule eq 'over_equal'}selected {/if}>
			Over or Equal &gt;=
		</option>
		<option value="over" {if $promo_disc_condition.rule eq 'over'}selected {/if}>
			Over &gt;
		</option>
		<option value="every" {if $promo_disc_condition.rule eq 'every'}selected {/if}>
			Every
		</option>
	</select>
	
	<!-- Amount or Qty -->
	<select name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][condition_type]"  {if $disabled}disabled {/if}>
	    <option value="amt" {if $promo_disc_condition.condition_type eq 'amt'}selected {/if}>
			Amount
		</option>
		<option value="qty" {if $promo_disc_condition.condition_type eq 'qty'}selected {/if}>
			Quantity
		</option>
	</select>

	of
	<!-- Value -->
	<input type="text" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][condition_value]" value="{$promo_disc_condition.condition_value}" size="5"  {if $disabled}disabled {/if} class="required" title="Condition Value" onChange="mf(this, 2, 1);" />
	
	for
	
	<!-- Type -->
	<input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][item_value]" value="{$promo_disc_condition.item_value}"  {if $disabled}disabled {/if} />
	{if $promo_disc_condition.item_type eq 'receipt' or $is_receipt_row}
	    <b>Receipt</b>
	    <input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][item_type]" value="receipt"  {if $disabled}disabled {/if} />
	{else}
	    <input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][item_type]" value="{$promo_disc_condition.item_type}"  {if $disabled}disabled {/if} />
	    {if $promo_disc_condition.item_type eq 'sku'}
	        <b>SKU</b>
	        {$promo_disc_condition.item_info.sku_item_code|default:'-'} /
			{$promo_disc_condition.item_info.artno|default:'-'} /
			{$promo_disc_condition.item_info.description|default:'-'}
			{if $promo_disc_condition.include_parent_child}
			 / Include Parent & Child
				<input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][include_parent_child]" value="1" {if $disabled}disabled {/if} />
			{/if}
        {elseif $promo_disc_condition.item_type eq 'brand'}
	        <b>Brand</b>
	        {$promo_disc_condition.item_info.description|default:'UN-BRANDED'}
	        
	        <input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][brand_description]" value="{$promo_disc_condition.item_info.description|escape}" {if $disabled}disabled {/if} />
        {elseif $promo_disc_condition.item_type eq 'category'}
	        <b>Category</b>
			<span title="{$promo_disc_condition.item_value}">
				{$promo_disc_condition.item_info.description|default:'UN-CATEGORIZE'}
			</span>
        {elseif $promo_disc_condition.item_type eq 'category_brand'}
            <!-- Category + Brand Discount -->
			<b>Category:</b>
			<span title="{$promo_disc_condition.item_info.cat_id}">
				{$promo_disc_condition.item_info.cat_desc|default:'UN-CATEGORIZE'}
			</span>
			<b>+</b>
			<b>Brand:</b>
			<span title="{$promo_disc_condition.item_info.brand_id}">
				{$promo_disc_condition.item_info.brand_desc|default:'UN-BRANDED'}
			</span>
			<input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][brand_description]" value="{$promo_disc_condition.item_info.brand_desc|escape}" {if $disabled}disabled {/if} />
		{elseif $promo_disc_condition.item_type eq 'sku_group'}
			{* SKU Group *}
			<b>SKU Group</b>
			{if $promo_disc_condition.item_info.code}
				{$promo_disc_condition.item_info.code} - 
			{/if}
			{$promo_disc_condition.item_info.description|default:'-'}
			
			<input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][sku_group_code]" value="{$promo_disc_condition.item_info.code|escape}" {if $disabled}disabled {/if} />
			<input type="hidden" name="disc_condition{$element_name_extend}[{$condition_row_num|default:'tmp_row_num'}][sku_group_description]" value="{$promo_disc_condition.item_info.description|escape}" {if $disabled}disabled {/if} />
		{else}
		    <b><img src="/ui/messages.gif" align="absmiddle" /> Unknown Type</b>
	    {/if}
        
	{/if}
</li>
