{*
29/04/2020 06:19 PM Sheila
- Modified layout to compatible with new UI.
*}
<div id="div_pw_disc_condition-{$row_num}-{$ddkey}" class="div_pw_disc_condition" style="background-color:#fff;padding:2px;border:1px solid black;" condition_row_num="{$ddkey}">
	{capture assign=element_name_extend}{$element_name_extend}[{$ddkey}]{/capture}
	
	<!-- Condition Rule -->
	<input type="hidden" name="disc_condition{$element_name_extend}[rule]" value="{$promo_disc_condition.rule}" />
	{if $promo_disc_condition.rule eq 'over_equal'}
		Over or Equal
	{else}
		{$promo_disc_condition.rule|capitalize} 
	{/if}
	<!-- Condition Value -->
	{if $promo_data.allowed_edit_condition_value}
		<input type="text" name="disc_condition{$element_name_extend}[condition_value]" value="{$promo_disc_condition.condition_value}" size="5" onChange="mi(this,1);" />
	{else}
		<input type="hidden" name="disc_condition{$element_name_extend}[condition_value]" value="{$promo_disc_condition.condition_value}" size="5" />
		{$promo_disc_condition.condition_value}
	{/if}
	
	<!-- Condition Type -->
	<input type="hidden" name="disc_condition{$element_name_extend}[condition_type]" value="{$promo_disc_condition.condition_type}" />
	{$promo_disc_condition.condition_type}
	
	<!-- Item Type & Item Value -->
	of 	
	<br />
	<input type="hidden" name="disc_condition{$element_name_extend}[item_type]" value="{$promo_disc_condition.item_type}" />
	<input type="hidden" name="disc_condition{$element_name_extend}[item_value]" value="{$promo_disc_condition.item_value}" />
	
	{if $promo_data.allowed_disc_condition_item_type}
		<table>
		{foreach from=$promo_data.allowed_disc_condition_item_type item=dtt}
			{if $dtt eq 'receipt'}
				<!-- Receipt Discount -->
				<tr>
					<td colspan="2"><b>Receipt Total Amount</b></td>
				</tr>
			{elseif $dtt eq 'sku'}
				<!-- SKU Discount -->
				{assign var=show_search_sku value=1}
				<tr>
					<td width="100"><b>SKU: </b></td>
					<td>
						{if $promo_data.allow_edit_condition_item_type}
							<input class="btn btn-primary" type="button" value="Edit Item" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_sku('{$row_num}','disc_condition', '{$ddkey}');" />
						{/if}
					</td>
				</tr>
			{elseif $dtt eq 'category'}
			    <!-- Category Discount -->
			    <tr>
					<td width="100"><b>Category:</b></td>
					<td>
						{if $promo_data.allow_edit_condition_item_type}
							<input class="btn btn-primary" type="button" value="Edit Category" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category','disc_condition', '{$ddkey}');" />
						{/if}
					</td>
				</tr>
			{elseif $dtt eq 'brand'}
			    <!-- Brand Discount -->
			    <tr>
			    	<td width="100"><b>Brand:</b></td>
					<td>
						{if $promo_data.allow_edit_condition_item_type}
							<input type="button" value="Edit Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'brand','disc_condition', '{$ddkey}');" />
						{/if}
					</td>
				</tr>
			{elseif $dtt eq 'category_brand'}
			    <!-- Category + Brand Discount -->
			    <tr>
					<td width="100"><b>Category: + Brand:</b></td>
					<td>
						{if $promo_data.allow_edit_condition_item_type}
							<input type="button" value="Edit Category + Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category_brand','disc_condition', '{$ddkey}');" />
						{/if}
					</td>
				</tr>
			{else}
				<tr>
					<td colspan="2">
			    		<img src="/ui/messages.gif" align="absmiddle" /> <b>Invalid Discount</b>
			    	</td>
			    </tr>
			{/if}
		{/foreach}
		</table>
	{else}
		{if $promo_disc_condition.item_type eq 'receipt'}
			<b>Receipt Total Amount</b>
		{elseif $promo_disc_condition.item_type eq 'sku'}
	        <b>SKU</b>
	        
	        {if $promo_data.allow_edit_condition_item_type}
	        	<input type="button" value="Edit Item" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_sku('{$row_num}', 'disc_condition', '{$ddkey}');" />
	        {/if}
        {elseif $promo_disc_condition.item_type eq 'brand'}
	        <b>Brand</b>
	        {if $promo_data.allow_edit_condition_item_type}
	        	<input type="button" value="Edit Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'brand', 'disc_condition', '{$ddkey}');" />
	        {/if}
        {elseif $promo_disc_condition.item_type eq 'category'}
	        <b>Category</b>
	        {if $promo_data.allow_edit_condition_item_type}
	        	<input type="button" value="Edit Category" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category', 'disc_condition', '{$ddkey}');" />
	        {/if}
        {elseif $promo_disc_condition.item_type eq 'category_brand'}
            <!-- Category + Brand Discount -->
			<b>Category + Brand:</b>
			{if $promo_data.allow_edit_condition_item_type}
				<input type="button" value="Edit Category + Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category_brand', 'disc_condition', '{$ddkey}');" />
			{/if}
		{else}
		    <b><img src="/ui/messages.gif" align="absmiddle" /> Unknown Type</b>
	    {/if}
	{/if}
	
	<div id="div_pw_disc_condition_item_info-{$row_num}-{$ddkey}" style="background-color:#ff0;padding:2px;border:2px inset black;{if !$promo_disc_condition.item_info}display:none;{/if}">
		{include file='promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.disc_condition.item_info.tpl'}
	</div>
</div>