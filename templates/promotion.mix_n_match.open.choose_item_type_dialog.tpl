{*
5/4/2011 4:58:35 PM Andy
- Add discount target filter. (sku type, price type, price range)

10/10/2011 5:24:51 PM Andy
- Make mix and match category can search until level 10.

11/7/2013 2:58 PM Andy
- Add can choose sku group.

1/6/2014 3:06 PM Andy
- Allow the mix and match promotion to set by sku group.

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.
*}

<form name="f_choose_item_type" onSubmit="return false" method="post">
	<!-- SKU -->
	<table>
		<tr>
			<th>Search SKU</th>
			<td>
			    <input name="disc_target_type" type="hidden" />
				<input name="disc_target_value" type="hidden" />
				<input id="inp_disc_target_autocomplete" name="disc_target_autocomplete" size="30" onclick="this.select();" style="font-size:14px;width:400px;" />

				<input type="button" value="Add" id="inp_add_disc_target_autocomplete" />
				<div id="div_disc_target_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
			    <input type="radio" name="search_type" value="mcode" checked /> MCode &amp; {$config.link_code_name}
				<input type="radio" name="search_type" value="sku_item_code" /> ARMS Code
				<input type="radio" name="search_type" value="artno" /> Art.No
				<input type="radio" name="search_type" value="sku_descripton" /> Description
				{*
				<br />
				<input type="radio" name="search_type" value="category" /> Category (Max lv10)
				<input type="radio" name="search_type" value="brand" /> Brand
				*}
			</td>
		</tr>
		<tr style="display:none;">
			<td>&nbsp;</td>
			<td>
			    <input type="checkbox" name="include_parent_child" value="0" /> Include Parent & Child
			</td>
		</tr>
	</table>
	
	
	{* SKU Group *}
	<hr />
	<table>
		<tr>
			<td><b>SKU Group</b> [<a href="javascript:void(alert('Only available in BETA v214 and above.'))">?</a>]</td>
			<td>
				<select name="sku_group_id" id="sel_choose_sku_group_id">
					<option value="">-- Please Select --</option>
					{foreach from=$master_sku_group_list item=sku_grp}
						<option value="{$sku_grp.id2}">{if $sku_grp.code}{$sku_grp.code} - {/if}{$sku_grp.description}</option>
					{/foreach}
				</select>
				<input type="button" value="Add" id="inp_choose_sku_group_id" />
			</td>
		</tr>	
	</table>
	
	
	{* Category & Brand *}
	<hr />
	<table width="100%">
	    <tr>
	        <td colspan="2"><b><u>Category & Brand Combination</u></b></td>
	    </tr>
	    
	    <!-- Category -->
	    <tr>
	        <td><b>Category (Max lv10)</b></td>
	        <td>
	            <input id="inp_search_cat_autocomplete" name="search_cat_autocomplete" size="30" style="font-size:14px;width:400px;" />
	            <div id="div_search_cat_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
	        </td>
	    </tr>
	    
	    <!-- Brand -->
	    <tr>
	        <td><b>Brand</b></td>
	        <td>
	            <input id="inp_search_brand_autocomplete" name="search_brand_autocomplete" size="30" style="font-size:14px;width:400px;" />
	            <div id="div_search_brand_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
	        </td>
	    </tr>
	    
	    <tbody id="tbody_choose_item_type_additional_filter">
		    <tr>
		    	<td colspan="2"><b><u>Additional Filter</u></b></td>
		    </tr>
		    
		    <!-- SKU Type-->
		    <tr>
		    	<td><b>SKU Type</b></td>
		    	<td>
		    		<select name="disc_target_sku_type">
		    			<option value="">-- All --</option>
		    			{foreach from=$master_sku_type item=mst}
		    				<option value="{$mst.code}">{$mst.code}</option>
		    			{/foreach}
		    		</select>
		    	</td>
		    </tr>
		    
		    <!-- Price Type -->
		    <tr>
		    	<td><b>Price Type</b></td>
		    	<td>
		    		<select name="disc_target_price_type">
		    			<option value="">-- All --</option>
		    			{foreach from=$master_price_type item=mpt}
		    				<option value="{$mpt.code}">{$mpt.code}</option>
		    			{/foreach}
		    		</select>
		    	</td>
		    </tr>
		    
		    <!-- Price Range -->
		    <tr>
		    	<td><b>Price Range</b></td>
		    	<td>
		    		From
		    		<input type="text" size="5" name="disc_target_price_range_from" onChange="mf(this, 2, 1);" />
		    		to
		    		<input type="text" size="5" name="disc_target_price_range_to" onChange="mf(this, 2, 1);" />
		    	</td>
		    </tr>
		</tbody>
				    
	    <tr>
	        <td>&nbsp;</td>
	        <td>
	            <input type="button" value="Add Combination" id="inp_add_cat_brand_autocomplete" />
			</td>
	    </tr>
	</table>
		<span id="span_disc_target_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
</form>
