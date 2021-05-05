{*
7/26/2011 12:55:46 PM Andy
- Fix promotion wizard (Start discount on Y Qty onwards), (Step Promotion Discount).
- Change discount qty : 'all items' can use 'qty from'.

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price
*}

{capture assign=element_name_extend}[{$row_num}]{/capture}

{assign var=allow_disc_by_value value=1}
{assign var=allow_disc_by_qty value=1}
{assign var=allow_qty_from value=1}
{assign var=allow_disc_limit value=1}
{assign var=allow_opt_1 value=1}

<!-- Discount Target Type -->
{if $promo_item.disc_target_type eq 'receipt'}
	{assign var=allow_disc_by_qty value=0}
{/if}

<!-- Discount by Target -->
{if $promo_item.disc_by_type eq 'foc'}
	{assign var=allow_disc_by_value value=0}
{/if}

<!-- Discount by Qty -->
{if $promo_item.disc_by_qty eq $group_total_val}
	{assign var=allow_qty_from value=0}
	{assign var=allow_disc_limit value=0}
{/if}
{if $promo_item.disc_by_qty eq $all_items_val}
	{assign var=allow_disc_limit value=0}
{/if}

<div id="div_pw_disc_target_item-{$row_num}" class="div_pw_disc_target_item">
	{if $promo_data.allow_delete_discount_target and $row_num>1}
		<div style="float:right;margin-top:10px;">
			<img src="/ui/del.png" class="clickable" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.delete_disc_target('{$row_num}');" />
		</div>
	{/if}
	<h3 align="center" style="background-color:#bfbfbf;">Condition 
		<span class="span_pw_disc_target_item_no">{$smarty.foreach.f_pw_item.iteration}</span>
	</h3>
	
	<!--  Some hidden temporary value -->
	<input type="hidden" name="allow_edit_condition_item_type{$element_name_extend}" value="{$promo_data.allow_edit_condition_item_type}" />
	
	<!-- Receipt limit -->
	{if $promo_data.receipt_limit}
		<input type="hidden" name="receipt_limit{$element_name_extend}" value="{$promo_data.receipt_limit}" />
	{/if}
	
	<div id="div_pw_disc_target_all_info-{$row_num}" style="{if $row_num > 1}display:none;{/if}">
		<!-- Discount Target Type-->
		<h4>Discount Target: (What you want to discount?)</h4>
		<input type="hidden" name="disc_target_type{$element_name_extend}" value="{$promo_item.disc_target_type}" />
		<input type="hidden" name="disc_target_value{$element_name_extend}" value="{$promo_item.disc_target_value}" />
		
		{if $promo_data.allowed_disc_target_type}
			<table>
			{foreach from=$promo_data.allowed_disc_target_type item=dtt}
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
							<input type="button" value="Edit Item" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_sku('{$row_num}');" />
						</td>
					</tr>
				{elseif $dtt eq 'category'}
				    <!-- Category Discount -->
				    <tr>
						<td width="100"><b>Category:</b></td>
						<td>
							<input type="button" value="Edit Category" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category');" />
						</td>
					</tr>
				{elseif $dtt eq 'brand'}
				    <!-- Brand Discount -->
				    <tr>
				    	<td width="100"><b>Brand:</b></td>
						<td>
							<input type="button" value="Edit Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'brand');" />
						</td>
					</tr>
				{elseif $dtt eq 'category_brand'}
				    <!-- Category + Brand Discount -->
				    <tr>
						<td width="100"><b>Category: + Brand:</b></td>
						<td>
							<input type="button" value="Edit Category + Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category_brand');" />
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
			{if $promo_item.disc_target_type eq 'receipt'}
				<!-- Receipt Discount -->
				<b>Receipt Total Amount</b>
			{elseif $promo_item.disc_target_type eq 'special_foc'}
				<!-- Special FOC -->
				<b>Special FOC:</b>
			{elseif $promo_item.disc_target_type eq 'sku'}
				<!-- SKU Discount -->
				{assign var=show_search_sku value=1}
				<b>SKU: </b>
				<input type="button" value="Edit Item" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_sku('{$row_num}');" />
			{elseif $promo_item.disc_target_type eq 'category'}
			    <!-- Category Discount -->
				<b>Category:</b>
				<input type="button" value="Edit Category" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category');" />
			{elseif $promo_item.disc_target_type eq 'brand'}
			    <!-- Brand Discount -->
				<b>Brand:</b>
				<input type="button" value="Edit Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'brand');" />
			{elseif $promo_item.disc_target_type eq 'category_brand'}
			    <!-- Category + Brand Discount -->
				<b>Category: + Brand:</b>
				<input type="button" value="Edit Category + Brand" onClick="MIX_MATCH_MAIN_WIZARD_DIALOG.show_search_cat_brand('{$row_num}', 'category_brand');" />
			{else}
			    <img src="/ui/messages.gif" align="absmiddle" /> <b>Invalid Discount</b>
			{/if}
		{/if}
		
		<!-- Discount Target Info -->
		<div id="div_pw_disc_target_info-{$row_num}" style="background-color:#cfc;padding:2px;border:2px inset black;{if !$promo_item.item_info && !$promo_item.disc_target_info}display:none;{/if}">
			{include file='promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.disc_target_info.tpl'}
		</div>
	</div>
	
	<!-- Discount By Type -->
	<h4>Discount By: (How you discount it?)</h4>
	<b>By</b>
	{if $promo_data.allowed_disc_by_type}
		<select name="disc_by_type{$element_name_extend}">
			{foreach from=$promo_data.allowed_disc_by_type item=t}
				<option value="{$t}" {if $t eq $promo_item.disc_by_type}selected {/if}>{$discount_by_type[$t]}</option>
			{/foreach}
		</select>
	{else}
		<input type="hidden" name="disc_by_type{$element_name_extend}" value="{$promo_item.disc_by_type}" />
		<b>{$discount_by_type[$promo_item.disc_by_type]}</b>
	{/if}
	
	<!-- Discount by Value -->
	<span id="span_pw_disc_by_value_container-{$row_num}" style="{if !$allow_disc_by_value}display:none;{/if}">
		<input type="text" size="5" name="disc_by_value{$element_name_extend}" value="{$promo_item.disc_by_value}" title="Discount by value" onChange="mf(this, 2, 1);" />
	</span>
	
	<div style="{if !$allow_disc_by_qty}display:none;{/if}">
		<h4>Discount Qty (How Many Item to be discount for each qualification?)</h4>
		<!-- Discount by Qty-->
		<b>Discount Qty:</b> 
		{if $promo_data.allowed_disc_by_qty}
			<select name="disc_by_qty{$element_name_extend}">
				{foreach from=$disc_by_qty_type key=dbq_k item=dbq}
					<option value="{$dbq_k}" {if $promo_item.disc_by_qty eq $dbq_k}selected {/if} style="{if $promo_data.allowed_disc_by_qty.not_allowed.$dbq_k}display:none;{/if}">{$dbq}</option>
				{/foreach}
				<option value="1" {if $promo_item.disc_by_qty eq 1 or !$promo_item.disc_by_qty}selected {/if} style="{if !$allow_opt_1}display:none;{/if}">1</option>
				{section loop=9 name=dbs}
					{assign var=s_val value=$smarty.section.dbs.iteration+1}
					<option value="{$s_val}" {if $promo_item.disc_by_qty eq $s_val}selected {/if}>{$s_val}</option>
				{/section}
				<!-- more than 10 is customize qty -->
				{if $promo_item.disc_by_qty>10}
					<option value="{$promo_item.disc_by_qty}" selected>{$promo_item.disc_by_qty}</option>
				{/if}
			</select>
		{else}
			<input type="hidden" name="disc_by_qty{$element_name_extend}" value="{$promo_item.disc_by_qty}" />
			{if $promo_item.disc_by_qty < 0}
				{$disc_by_qty_type[$promo_item.disc_by_qty]}
			{else}
				{$promo_item.disc_by_qty}
			{/if}
		{/if}
		
		<span id="span_pw_qty_from-{$row_num}" style="{if !$allow_qty_from}display:none;{/if}">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Start Discount at Qty:</b>
			{if $promo_data.allowed_qty_from}
				<input type="text" size="5" name="qty_from{$element_name_extend}" value="{$promo_item.qty_from}" onChange="mi(this, 1);" />
			{else}
				{$promo_item.qty_from|default:'-'}
				<input type="hidden" name="qty_from{$element_name_extend}" value="{$promo_item.qty_from}" />
			{/if}
		</span>
		
		<span id="span_pw_disc_limit-{$row_num}" style="{if !$allow_disc_limit}display:none;{/if}">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Total how many qty can discount in one receipt:</b>
			{if $promo_data.allowed_disc_limit}
				<input type="text" size="5" name="disc_limit{$element_name_extend}" value="{$promo_item.disc_limit}" onChange="mi(this, 1);" />
			{else}
				{$promo_item.disc_limit|default:'No Limit'}
				<input type="hidden" name="disc_limit{$element_name_extend}" value="{$promo_item.disc_limit}" />
			{/if}
		</span>
	</div>
	{if $is_under_gst}
		<div style="{if !$allow_disc_by_qty}display:none;{/if}">
			<h4>Selling Price Inclusive Tax</h4>
			<b>Selling Price Inclusive Tax:</b>
			<select name="disc_by_inclusive_tax{$element_name_extend}">
				<option value ="yes" {if $disc_by_inclusive_tax eq 'yes'}selected{/if}>Yes</option>
				<option value="no" {if $disc_by_inclusive_tax eq 'no'}selected{/if}>No</option>
			</select>
		</div>
	{/if}
	<!-- Discount Condition -->
	<h4>Discount Qualifier: (What is the condition to active this discount?)</h4>
	<div id="div_pw_disc_condition-{$row_num}">
		{foreach from=$promo_item.disc_condition key=ddkey item=promo_disc_condition}
			{include file='promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.disc_condition.tpl'}
		{/foreach}
	</div>
</div>