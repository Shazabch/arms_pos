{*
2/28/2011 11:18:35 AM Andy
- Add "from qty".
- Move column "Remark" to last column.
- Add receipt description.

3/21/2011 5:24:30 PM Andy
- Move all overlap promotion item script to new template.

4/29/2011 5:35:36 PM Andy
- Add "discount by qty" for each mix and match promotion row.
- Add "Bundled Price", discount qty must more than 1.
- Add "Special FOC".
- Add discount target filter. (sku type, price type, price range)

7/26/2011 11:47:04 AM Andy
- Change discount qty : 'all items' can use 'qty from'.

3/2/2012 2:48:18 PM Andy
- Add category discount by SKU info at overlap promotion info.

11/11/2013 10:38 AM Andy
- Enhance to can select SKU Group as Discount Target and Condition Rule.

1/16/2014 5:58 PM Andy
- Add to capture brand description, sku group code/description and store in database for frontend counter use.

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price

2/22/2017 11:27 AM Justin
- Bug fixed on receipt description couldn't display special symbol.
*}

{if $promo_item}
	{assign var=group_id value=$promo_item.group_id}
	{assign var=item_id value=$promo_item.id}
	{capture assign=element_name_extend}[{$group_id}][{$item_id}]{/capture}
	
	<!-- allow discount by type -->
	{assign var=allow_fixed_price value=1}
	{assign var=allow_bundled_price value=1}
	{assign var=allow_foc value=1}
	{assign var=allow_discount_amt value=1}
	{assign var=allow_discount_per value=1}
	
	<!-- allow discount by value-->
	{assign var=allow_disc_by_value value=1}
	
	<!-- allow discount by qty -->
	{assign var=allow_all_items value=1}
	{assign var=allow_group_total value=1}
	{assign var=allow_opt_1 value=1}
	
	<!-- others -->
	{assign var=allow_qty_from value=1}
	{assign var=allow_disc_limit value=1}
	{assign var=allow_loop_limit value=1}
	
	<!-- check discount target -->
	{if $promo_item.disc_target_type eq 'receipt'} <!-- Receipt -->
		{assign var=allow_fixed_price value=0}
		{assign var=allow_bundled_price value=0}
		{assign var=allow_foc value=0}
		
		{assign var=allow_qty_from value=0}
		{assign var=allow_disc_limit value=0}
	{elseif $promo_item.disc_target_type eq 'special_foc'}	<!-- Special FOC -->
		{assign var=allow_fixed_price value=0}
		{assign var=allow_bundled_price value=0}
		{assign var=allow_discount_amt value=0}
		{assign var=allow_discount_per value=0}
	
		{assign var=allow_all_items value=0}
		{assign var=allow_group_total value=0}
		{assign var=allow_disc_by_value value=0}
		
		{assign var=allow_qty_from value=0}
	{/if}
	
	<!-- check discount by type -->
	{if $promo_item.disc_by_type eq 'foc'}	<!-- FOC -->
		{assign var=allow_all_items value=0}
		{assign var=allow_group_total value=0}
		{assign var=allow_disc_by_value value=0}
	{elseif $promo_item.disc_by_type eq 'bundled_price'} <!-- Bundled Price-->
		{assign var=allow_all_items value=0}
		{assign var=allow_group_total value=0}
		{assign var=allow_opt_1 value=0}
	{elseif $promo_item.disc_by_type eq 'fixed_price'} <!-- Fixed Price -->
		{assign var=allow_group_total value=0}
	{/if}
	
	<!-- check discount by qty -->
	{if $promo_item.disc_by_qty eq $all_items_val} <!-- All Items -->
		{assign var=allow_disc_limit value=0}
	{elseif $promo_item.disc_by_qty eq $group_total_val} <!-- Group Total -->
		{assign var=allow_qty_from value=0}
		{assign var=allow_disc_limit value=0}
	{/if}
	
	<!-- check got condition evert or not -->
	{if !$promo_item.got_every}
		{assign var=allow_loop_limit value=0}
	{/if}
	
	<tr id="tr_promo_item_row-{$item_id}" class="promo_item_row {if $smarty.request.highlight_promo_item_id eq $item_id}highlight_row{/if}">
	    <td width="20" nowrap>
			<span class="row_no">{$smarty.foreach.fg.iteration}</span>.
		</td>
		<td width="60" nowrap>
		    &nbsp;
		    {if ($form.status<1 || $form.status eq '2') && !$form.approval_screen && $allow_edit}
            	<img src="/ui/icons/cross.png" class="clickable" title="Delete Row" onClick="delete_discount_item('{$item_id}');" align="absmiddle" />
                <img src="/ui/icons/arrow_up.png" align="absmiddle" class="clickable img_move_sequence_up" title="Move sequence up" style="{if $smarty.foreach.fg.first}visibility:hidden;{/if}" onClick="move_sequence_up('{$item_id}');" />
            	<img src="/ui/icons/arrow_down.png" align="absmiddle" class="clickable img_move_sequence_down" title="Move sequence down" style="{if $smarty.foreach.fg.last}visibility:hidden;{/if}" onClick="move_sequence_down('{$item_id}');" />
            {/if}
		</td>
		<!-- Discount Target -->
		<td>
		    <input type="hidden" name="disc_target_type{$element_name_extend}" value="{$promo_item.disc_target_type}" />
		    <input type="hidden" name="disc_target_value{$element_name_extend}" value="{$promo_item.disc_target_value}" />
			{if $promo_item.disc_target_type eq 'receipt'}
			    <!-- Receipt Discount -->
				<b>Receipt</b>
			{elseif $promo_item.disc_target_type eq 'special_foc'}
				<!-- Special FOC -->
				<b>Special FOC:</b>
				{$promo_item.disc_target_info.description}
				<input type="hidden" name="disc_target_info{$element_name_extend}[description]" value="{$promo_item.disc_target_info.description}" />
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
					Include Parent & Child<br>
				{/if}
				<!-- Price Type: {$promo_item.disc_target_info.price_type|default:'-'}<br> -->
				<input type="hidden" name="disc_target_info{$element_name_extend}[selling_price]" value="{$promo_item.disc_target_info.selling_price}" />
				<input type="hidden" name="disc_target_info{$element_name_extend}[cost_price]" value="{$promo_item.disc_target_info.cost_price}" />
				<input type="hidden" name="disc_target_info{$element_name_extend}[price_type]" value="{$promo_item.disc_target_info.price_type}" />
				<input type="hidden" name="disc_target_info{$element_name_extend}[include_parent_child]" value="{$promo_item.disc_target_info.include_parent_child}" />
			{elseif $promo_item.disc_target_type eq 'brand'}
			    <!-- Brand Discount -->
				<b>Brand:</b>
				{$promo_item.item_info.description|default:'UN-BRANDED'}
				
				<input type="hidden" name="disc_target_info{$element_name_extend}[brand_description]" value="{$promo_item.item_info.description|escape}" />
            {elseif $promo_item.disc_target_type eq 'category'}
			    <!-- Category Discount -->
				<b>Category:</b>
				{$promo_item.item_info.description|default:'UN-CATEGORIZE'}
            {elseif $promo_item.disc_target_type eq 'category_brand'}
			    <!-- Category + Brand Discount -->
				<b>Category:</b>
				{$promo_item.item_info.cat_desc|default:'UN-CATEGORIZE'}
				<br />+<br />
				<b>Brand:</b>
				{$promo_item.item_info.brand_desc|default:'UN-BRANDED'}
				
				<input type="hidden" name="disc_target_info{$element_name_extend}[brand_description]" value="{$promo_item.item_info.brand_desc|escape}" />
			{elseif $promo_item.disc_target_type eq 'sku_group'}
				{* SKU Group *}
				<b>SKU Group: </b>
				{if $promo_item.item_info.code}
					{$promo_item.item_info.code} - 
				{/if}
				{$promo_item.item_info.description|default:'-'}
				
				<input type="hidden" name="disc_target_info{$element_name_extend}[sku_group_code]" value="{$promo_item.item_info.code|escape}" />
				<input type="hidden" name="disc_target_info{$element_name_extend}[sku_group_description]" value="{$promo_item.item_info.description|escape}" />
			{else}
			    <img src="/ui/messages.gif" align="absmiddle" /> <b>Invalid Discount</b>
			{/if}
			
			<!-- SKU Type -->
			<input type="hidden" name="disc_target_sku_type{$element_name_extend}" value="{$promo_item.disc_target_sku_type}" />
			{if $promo_item.disc_target_sku_type}
				<br /><nobr><b>SKU Type:</b> {$promo_item.disc_target_sku_type}</nobr>
			{/if}
			
			<!-- Price Type -->
			<input type="hidden" name="disc_target_price_type{$element_name_extend}" value="{$promo_item.disc_target_price_type}" />
			{if $promo_item.disc_target_price_type}
				<br /><nobr><b>Price Type:</b> {$promo_item.disc_target_price_type}</nobr>
			{/if}
			
			<input type="hidden" name="disc_target_price_range_from{$element_name_extend}" value="{$promo_item.disc_target_price_range_from}" />
			<input type="hidden" name="disc_target_price_range_to{$element_name_extend}" value="{$promo_item.disc_target_price_range_to}" />
			
			<!-- Price Range -->
			{if $promo_item.disc_target_price_range_from>0 || $promo_item.disc_target_price_range_to>0}
				<br />
				<nobr>
					<b>Price:</b>
				{if $promo_item.disc_target_price_range_from > 0 and !$promo_item.disc_target_price_range_to}
					more than {$promo_item.disc_target_price_range_from|number_format:2}
				{elseif !$promo_item.disc_target_price_range_from and $promo_item.disc_target_price_range_to > 0}
					less than {$promo_item.disc_target_price_range_to|number_format:2}
				{else}
					from 
					{$promo_item.disc_target_price_range_from|number_format:2}
					to
					{$promo_item.disc_target_price_range_to|number_format:2}
				{/if}
				</nobr>
			{/if}
		</td>
		<!-- Condition -->
		<td nowrap>
		    <ul style="list-style:none;" id="ul_disc_condition-{$item_id}">
		        {if $promo_item.disc_condition}
                    {foreach from=$promo_item.disc_condition key=crn item=dc}
			            {include file='promotion.mix_n_match.open.promo_item_row.disc_condition.tpl' promo_disc_condition=$dc condition_row_num=$crn}
			        {/foreach}
				{else}
					<!-- default will put a row for receipt amt -->
				    {include file='promotion.mix_n_match.open.promo_item_row.disc_condition.tpl' condition_row_num=1 is_receipt_row=1}
				{/if}
		    </ul>
		    {if $allow_edit}
		        <button onClick="add_disc_condition('{$item_id}');">
					<img src="/ui/findcat_expand.png" align="absmiddle" />
					Search Condition Item
				</button>
				<button onClick="add_disc_condition_by_receipt('{$item_id}');">
	                <img src="/ui/inote16.png" align="absmiddle" />
	                Add Condition by Receipt
                </button>
			{/if}
		</td>
		
		<!-- Discount -->
		<td nowrap>
		    By
			<select name="disc_by_type{$element_name_extend}" onChange="check_disc_by_type('{$item_id}');">
			    {foreach from=$discount_by_type key=k item=t}
			        {if ($k eq 'fixed_price' and !$allow_fixed_price)
					 or ($k eq 'foc' and !$allow_foc) 
					 or ($k eq 'bundled_price' and !$allow_bundled_price)
					 or ($k eq 'amt' and !$allow_discount_amt)
					 or ($k eq 'per' and !$allow_discount_per)}
			        
			        {else}
			        	<option value="{$k}" {if $promo_item.disc_by_type eq $k}selected {/if}>{$t}</option>
			        {/if}
			    {/foreach}
			</select>
			<span style="{if !$allow_disc_by_value}display:none;{/if}" id="span_disc_by_value-{$item_id}">
				<input type="text" size="5" name="disc_by_value{$element_name_extend}" value="{$promo_item.disc_by_value}" class="" title="Discount by value" onChange="mf(this, 2, 1);" />
			</span>
			
			<br />
			<div style="{if $promo_item.disc_target_type eq 'receipt'}display:none;{/if}">
				Discount Qty:
				<select name="disc_by_qty{$element_name_extend}" onChange="MIX_MATCH_MAIN_MODULE.discount_qty_changed('{$item_id}');" onClick="MIX_MATCH_MAIN_MODULE.check_enter_customize_discount_qty('{$item_id}');" title="Double Click to key in the qty you want">
					
					
					{foreach from=$disc_by_qty_type key=dbq_k item=dbq}
						<option value="{$dbq_k}" {if $promo_item.disc_by_qty eq $dbq_k}selected {/if} style="{if ($dbq_k eq $all_items_val and !$allow_all_items) or ($dbq_k eq $group_total_val and !$allow_group_total)}display:none;{/if}">{$dbq}</option>
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
			</div>
			{if $is_under_gst}
				<div id="div_disc_by_bundled_price_inclusive_tax{$element_name_extend}" style="{if $promo_item.disc_by_type neq 'bundled_price'}display:none;{/if}" >
					Selling Price Inclusive Tax: 
					<select name="disc_by_inclusive_tax{$element_name_extend}">
						{if !is_new_id($form.id) and !$promo_item.disc_by_inclusive_tax}
							<option value ="Not Set" selected>Not Set</option>
						{/if}
						<option value ="yes" {if $promo_item.disc_by_inclusive_tax eq 'yes'}selected{/if}>Yes</option>
						<option value ="no" {if $promo_item.disc_by_inclusive_tax eq 'no'}selected{/if}>No</option>
					</select>
				</div>
			{/if}
		</td>

		<!-- Limit -->
		<td align="center">
		    <table>
		        <tr id="tr_qty_from-{$item_id}" style="{if !$allow_qty_from}display:none;{/if}">
		            <td nowrap>Qty From</td>
		    		<td>
						<input type="text" size="3" name="qty_from{$element_name_extend}" value="{$promo_item.qty_from}" onChange="miz(this);" />
					</td>
		        </tr>
		    	<tr id="tr_disc_limit-{$item_id}" style="{if !$allow_disc_limit}display:none;{/if}">
		    	    <td>Qty Limit</td>
		    	    <td>
						<input type="text" size="3" name="disc_limit{$element_name_extend}" value="{$promo_item.disc_limit}" onChange="miz(this);check_item_limit('{$item_id}');" id="inp_item_limit-{$item_id}" />
			    	</td>
			    </tr>
			    <tr id="tr_loop_limit-{$item_id}" style="{if !$allow_loop_limit}display:none;{/if}" title="'Loop Limit' is use to limit how many 'every' condition can loop">
			    	<td>Loop Limit</td>
			    	<td>
			    		<input type="text" size="3" name="loop_limit{$element_name_extend}" value="{$promo_item.loop_limit}" onChange="miz(this);" id="inp_loop_limit-{$item_id}" />
			    	</td>
			    </tr>
			</table>
		</td>
		
		<!-- Receipt Description -->
		<td align="center">
		    <input type="text" maxlength="35" size="20" name="receipt_description{$element_name_extend}" class="required" title="Receipt Description" value="{$promo_item.receipt_description|escape}" />
		</td>
		{*<!-- Remark -->
		<td>
		    {strip}
		    {if $allow_edit}<textarea name="item_remark{$element_name_extend}" cols="40" rows="4">{/if}
			{$promo_item.item_remark}
			{if $allow_edit}</textarea>{/if}
			{/strip}
		</td>*}
	</tr>
	
	{include file='promotion.overlap_promo_row.tpl' overlap_pi_discount=$promo_item.overlap_pi.discount overlap_pi_mix=$promo_item.overlap_pi.mix_n_match promo_item_id=$item_id overlap_mprice=$promo_item.overlap_pi.mprice overlap_qprice=$promo_item.overlap_pi.qprice overlap_category_disc=$promo_item.overlap_pi.category_disc overlap_category_disc_by_sku=$promo_item.overlap_pi.category_disc_by_sku}
{/if}
