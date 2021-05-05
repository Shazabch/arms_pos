<form name="f_condition_type" onSubmit="return false" method="post">
	<!-- SKU -->
	{include file='sku_items_autocomplete.tpl' parent_form='document.f_condition_type'}
	<br />
	<table width="100%">
	    <tr>
	        <td colspan="2"><b><u>Category & Brand Combination</u></b></td>
	    </tr>

	    <!-- Category -->
	    <tr>
	        <td><b>Category (Max lv10)</b></td>
	        <td>
	            <input id="inp_search_cat_autocomplete" name="search_cat_autocomplete" style="font-size:14px;width:500px;" />
	            <div id="div_search_cat_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	        </td>
	    </tr>
	    
	    <!-- Brand -->
	    <tr>
	        <td><b>Brand</b></td>
	        <td>
	            <input id="inp_search_brand_autocomplete" name="search_brand_autocomplete" style="font-size:14px;width:500px;" />
	            <div id="div_search_brand_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	        </td>
	    </tr>
	    
	    <tbody id="tbody_condition_additional_filter">
		    <tr>
		    	<td colspan="2"><b><u>Additional Filter</u></b></td>
		    </tr>
		    
		    <!-- SKU Type-->
		    <tr>
		    	<td><b>SKU Type</b></td>
		    	<td>
		    		<select name="cst" onchange="SA_COMMISSION_CONDITION_DIALOG.check_sku_type(this);">
		    			<option value="">-- All --</option>
		    			{foreach from=$master_sku_type item=mst}
		    				<option value="{$mst.code}">{$mst.code}</option>
		    			{/foreach}
		    		</select>
		    	</td>
		    </tr>
		    
		    <!-- Price Type (allow to select multiple) -->
			<tr id="condition_price_type">
				<td valign="top"><b>Price Type</b></td>
				<td>
					{assign var=mpt_count value=1}
					<input type="checkbox" style="margin-left:0;" name="cpt_toggle" id="cpt_toggle"> All 
					{foreach from=$master_price_type item=mpt}
						{if $mpt_count%6 eq 0}<br />{/if}
						{assign var=mpt_id value=$mpt.id}
						<input type="checkbox" style="margin-left:0;" name="cpt[{$mpt.code}]" class="price_type_list" /> {$mpt.code}
						<!--{$mpt_count++}-->
					{/foreach}
				</td>
			</tr>

		    <!-- Vendor (should able to use Last GRN) -->
			<tr>
				<td><b>Vendor</b></td>
				<td>
					<input id="inp_search_vendor_autocomplete" name="search_vendor_autocomplete" style="font-size:14px;width:500px;" />
					<div id="div_search_vendor_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				</td>
			</tr>
		</tbody>

	    <tr>
	        <td colspan="2" align="center">
	            <br /><input type="button" value="Add Combination" id="inp_add_cat_brand_autocomplete" />
			</td>
	    </tr>
	</table>
	<span id="span_sac_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
</form>
