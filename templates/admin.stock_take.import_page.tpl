{*
8/19/2010 3:33:21 PM Alex
- Add SKU type filter

10/15/2010 11:03:14 AM Alex
- remove sku_type filter

11/4/2010 12:16:14 PM Alex
- remove button after click

2/16/2012 6:24:04 PM Alex
- add new options of auto add zero on same SKU parent
- add $config.stock_take_hide_auto_zero_sku_parent_option to close the non sku parent option

1/9/2013 5:53 PM Justin
- Enhanced to have new fill zero option "auto fill zero by categories".

6/10/2013 4:41 PM Justin
- Enhanced to have SKU Type filter option while action is selected.

07/17/2013 12:08 PM Justin
- Added vendor ajax autocomplete.
- Enhanced to have vendor filter for fill zero for selected category.

5/4/2017 14:06 Qiu Ying
- Enhanced to add note in Import/Reset Stock Take

11/13/2018 10:00 AM Justin
- Enhanced to have new fill zero option "auto fill zero by brand".

11/30/2018 5:44 PM Justin
- Bug fixed some wording issue.

06/25/2020 04:17 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var cat_array_list = new Array();
var vendor_array_list = new Array();
var brand_array_list = new Array();

function check_form(f){
	if(!f['stock_take_date']||f['stock_take_date'].value==''){
		alert('No Stock Date Selected.');
		return false;
	}
	
	if(f.name != "reset_stock_take" && document.import_stock_take['fill_zero_options'].value == "fill_zero_by_category_vendor"){
		document.import_stock_take['category_id_list'].value = cat_array_list;
		document.import_stock_take['vendor_id_list'].value = vendor_array_list;
		document.import_stock_take['brand_id_list'].value = brand_array_list;

		if(document.import_stock_take['category_id_list'].value=='' && document.import_stock_take['vendor_id_list'].value=='' && document.import_stock_take['brand_id_list'].value==''){
			alert('No Category, Vendor or Brand were selected.');
			return false;
		}

	}
	
	if(!confirm('Are you sure?'))  return false;
	
	//loading image replace button
	$('btn_import').update(_loading_);
	$('btn_reset').update(_loading_);
	
	return true;
}

function stock_branch_change(imported){
	var type = imported>0 ? 'reset' : 'import';
	var f = $('f_'+type);
	var bid = f['branch_id'].value;
	if(bid==''){    // no branch selected
		$(f['stock_take_date']).update('<option value="">-- No Data --</option>');
		$('btn_'+type).disabled = false;
	}else{
	    $('div_'+type+'_stock_take_date').update(_loading_);
	    $('btn_'+type).disabled = true;
		new Ajax.Updater('div_'+type+'_stock_take_date',phpself,{
			parameters:{
				a: 'check_available_stock_take_date',
				branch_id: bid,
				imported: imported
			},
			onComplete: function(e){
                $('btn_'+type).disabled = false;
			},
			evalScripts: true
		});
	}
}

// reset category autocomplete
function reset_category_autocomplete(){
	var selected_cat_id = 0;
	$('inp_search_cat_autocomplete').value = '';
	
	if(!this.cat_autocomplete){
		var params = $H({
			a: 'ajax_search_category',
			max_level: 10,
			no_findcat_expand: 1
		}).toQueryString();

		this.cat_autocomplete = new Ajax.Autocompleter("inp_search_cat_autocomplete", "div_search_cat_autocomplete_choices", 'ajax_autocomplete.php', {
			parameters: params,
			paramName: "category",
			indicator: 'span_cat_autocomplete_loading',
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");

				if (s[0]==''){
					obj.value='';
					return;
				}

				selected_cat_id = s[0];
				document.import_stock_take['category_id'].value = selected_cat_id;
				document.import_stock_take['category_desc'].value = obj.value;
			}
		});
	}
}

function check_options(obj){
	if(obj.value == "fill_zero_by_category_vendor"){
		$("fill_zero_by_category_vendor").show();
	}else{
		$("fill_zero_by_category_vendor").hide();
		cat_array_list = new Array();
		clear_cat_from_list();
	}
	
	if(obj.value != "no_fill") $("td_sku_type").show();
	else $("td_sku_type").hide();
}

function cat_add_autocomplete(){
	if(document.import_stock_take['category_id'].value){
		var cat_id = document.import_stock_take['category_id'].value;
		var cat_desc = document.import_stock_take['category_desc'].value;
		add_cat_to_list(cat_id, cat_desc);
	}
	cat_clear_autocomplete();
}

function add_cat_to_list(cat_id,lbl){
	if(cat_id!=''){
	    var new_option = document.createElement('option');
	    new_option.value = cat_id;
	    new_option.text = lbl; //$('autocomplete_sku').value;

		var obj = document.import_stock_take['category_list'];
		var found =false;

		for(var i=0; i<obj.length; i++){
			if(obj.options[i].value!=null){
				if(obj.options[i].value==cat_id){
					alert(lbl+' already in the list.');
					found=true;
					break;
				}
			}
		}

		if(!found){
		    try {
			    obj.add(new_option, null); // standards compliant; doesn't work in IE
			}
			catch(ex) {
			    obj.add(new_option); // IE only
			}
			//$('sku_code_list_2').value = $('sku_code_list_2').value+'|'+code;
			cat_array_list[cat_array_list.length] = cat_id;
			$('remove_cat').disabled=false;
			$('clear_cat').disabled=false;
		}
	}else{
		alert('Invalid input');
	}
}

function cat_clear_autocomplete(){
	document.import_stock_take['category_id'].value = '';
	document.import_stock_take['category_desc'].value = '';
	document.import_stock_take['search_cat_autocomplete'].value = '';
	$('span_cat_autocomplete_loading').style.display='none';
	document.import_stock_take['search_cat_autocomplete'].focus();
}

function remove_cat_from_list(){
    if(document.import_stock_take['category_list'].selectedIndex<0){
		alert('Please select a category from the list');
	}

	while(document.import_stock_take['category_list'].selectedIndex>=0){
        var selectedIndex = document.import_stock_take['category_list'].selectedIndex;
		document.import_stock_take['category_list'].remove(selectedIndex);
		cat_array_list.splice(selectedIndex, 1);
		if(document.import_stock_take['category_list'].length<=0){
		    $('remove_cat').disabled=true;
		    $('clear_cat').disabled=true;
		}
	}
}

function clear_cat_from_list(){
    while(document.import_stock_take['category_list'].length>0){
		document.import_stock_take['category_list'].remove(0);
	}
	$('remove_cat').disabled=true;
	$('clear_cat').disabled=true;
	cat_array_list = new Array();
}

function init_cat_array_list(cat_id){
    cat_array_list[cat_array_list.length] = cat_id;
    $('remove_cat').disabled=false;
	$('clear_cat').disabled=false;
}

// reset vendor autocomplete
function reset_vendor_autocomplete(){
	var selected_vendor_id = 0;
	$('inp_search_vendor_autocomplete').value = '';
	
	if(!this.vendor_autocomplete){
		var params = $H({
			a: 'ajax_search_vendor'
		}).toQueryString();

		this.vendor_autocomplete = new Ajax.Autocompleter("inp_search_vendor_autocomplete", "div_search_vendor_autocomplete_choices", 'ajax_autocomplete.php', {
			parameters: params,
			paramName: "vendor",
			indicator: 'span_vendor_autocomplete_loading',
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");

				if (s[0]==''){
					obj.value='';
					return;
				}

				selected_vendor_id = s[0];
				document.import_stock_take['vendor_id'].value = selected_vendor_id;
				document.import_stock_take['vendor_desc'].value = obj.value;
			}
		});
	}
}

function vendor_add_autocomplete(){
	if(document.import_stock_take['vendor_id'].value){
		var vd_id = document.import_stock_take['vendor_id'].value;
		var vd_desc = document.import_stock_take['vendor_desc'].value;
		add_vendor_to_list(vd_id, vd_desc);
	}
	vendor_clear_autocomplete();
}

function add_vendor_to_list(vd_id,lbl){
	if(vd_id!=''){
	    var new_option = document.createElement('option');
	    new_option.value = vd_id;
	    new_option.text = lbl; //$('autocomplete_sku').value;

		var obj = document.import_stock_take['vendor_list'];
		var found =false;

		for(var i=0; i<obj.length; i++){
			if(obj.options[i].value!=null){
				if(obj.options[i].value==vd_id){
					alert(lbl+' already in the list.');
					found=true;
					break;
				}
			}
		}

		if(!found){
		    try {
			    obj.add(new_option, null); // standards compliant; doesn't work in IE
			}
			catch(ex) {
			    obj.add(new_option); // IE only
			}
			//$('sku_code_list_2').value = $('sku_code_list_2').value+'|'+code;
			vendor_array_list[vendor_array_list.length] = vd_id;
			$('remove_vendor').disabled=false;
			$('clear_vendor').disabled=false;
		}
	}else{
		alert('Invalid input');
	}
}

function vendor_clear_autocomplete(){
	document.import_stock_take['vendor_id'].value = '';
	document.import_stock_take['vendor_desc'].value = '';
	document.import_stock_take['search_vendor_autocomplete'].value = '';
	$('span_vendor_autocomplete_loading').style.display='none';
	document.import_stock_take['search_vendor_autocomplete'].focus();
}

function remove_vendor_from_list(){
    if(document.import_stock_take['vendor_list'].selectedIndex<0){
		alert('Please select a vendor from the list');
	}

	while(document.import_stock_take['vendor_list'].selectedIndex>=0){
        var selectedIndex = document.import_stock_take['vendor_list'].selectedIndex;
		document.import_stock_take['vendor_list'].remove(selectedIndex);
		vendor_array_list.splice(selectedIndex, 1);
		if(document.import_stock_take['vendor_list'].length<=0){
		    $('remove_vendor').disabled=true;
		    $('clear_vendor').disabled=true;
		}
	}
}

function clear_vendor_from_list(){
    while(document.import_stock_take['vendor_list'].length>0){
		document.import_stock_take['vendor_list'].remove(0);
	}
	$('remove_vendor').disabled=true;
	$('clear_vendor').disabled=true;
	vendor_array_list = new Array();
}

function init_vendor_array_list(vd_id){
    vendor_array_list[vendor_array_list.length] = vd_id;
    $('remove_vendor').disabled=false;
	$('clear_vendor').disabled=false;
}

// reset brand autocomplete
function reset_brand_autocomplete(){
	var selected_brand_id = 0;
	$('inp_search_brand_autocomplete').value = '';
	
	if(!this.brand_autocomplete){
		var params = $H({
			a: 'ajax_search_brand'
		}).toQueryString();

		this.brand_autocomplete = new Ajax.Autocompleter("inp_search_brand_autocomplete", "div_search_brand_autocomplete_choices", 'ajax_autocomplete.php', {
			parameters: params,
			paramName: "brand",
			indicator: 'span_brand_autocomplete_loading',
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");

				if (s[0]==''){
					obj.value='';
					return;
				}

				selected_brand_id = s[0];
				document.import_stock_take['brand_id'].value = selected_brand_id;
				document.import_stock_take['brand_desc'].value = obj.value;
			}
		});
	}
}

function brand_add_autocomplete(){
	if(document.import_stock_take['brand_id'].value){
		var brd_id = document.import_stock_take['brand_id'].value;
		var brd_desc = document.import_stock_take['brand_desc'].value;
		add_brand_to_list(brd_id, brd_desc);
	}
	brand_clear_autocomplete();
}

function add_brand_to_list(brd_id,lbl){
	if(brd_id!=''){
	    var new_option = document.createElement('option');
	    new_option.value = brd_id;
	    new_option.text = lbl; //$('autocomplete_sku').value;

		var obj = document.import_stock_take['brand_list'];
		var found =false;

		for(var i=0; i<obj.length; i++){
			if(obj.options[i].value!=null){
				if(obj.options[i].value==brd_id){
					alert(lbl+' already in the list.');
					found=true;
					break;
				}
			}
		}

		if(!found){
		    try {
			    obj.add(new_option, null); // standards compliant; doesn't work in IE
			}
			catch(ex) {
			    obj.add(new_option); // IE only
			}
			//$('sku_code_list_2').value = $('sku_code_list_2').value+'|'+code;
			brand_array_list[brand_array_list.length] = brd_id;
			$('remove_brand').disabled=false;
			$('clear_brand').disabled=false;
		}
	}else{
		alert('Invalid input');
	}
}

function brand_clear_autocomplete(){
	document.import_stock_take['brand_id'].value = '';
	document.import_stock_take['brand_desc'].value = '';
	document.import_stock_take['search_brand_autocomplete'].value = '';
	$('span_brand_autocomplete_loading').style.display='none';
	document.import_stock_take['search_brand_autocomplete'].focus();
}

function remove_brand_from_list(){
    if(document.import_stock_take['brand_list'].selectedIndex<0){
		alert('Please select a brand from the list');
	}

	while(document.import_stock_take['brand_list'].selectedIndex>=0){
        var selectedIndex = document.import_stock_take['brand_list'].selectedIndex;
		document.import_stock_take['brand_list'].remove(selectedIndex);
		brand_array_list.splice(selectedIndex, 1);
		if(document.import_stock_take['brand_list'].length<=0){
		    $('remove_brand').disabled=true;
		    $('clear_brand').disabled=true;
		}
	}
}

function clear_brand_from_list(){
    while(document.import_stock_take['brand_list'].length>0){
		document.import_stock_take['brand_list'].remove(0);
	}
	$('remove_brand').disabled=true;
	$('clear_brand').disabled=true;
	brand_array_list = new Array();
}

function init_brand_array_list(brd_id){
    brand_array_list[brand_array_list.length] = brd_id;
    $('remove_brand').disabled=false;
	$('clear_brand').disabled=false;
}
/*
function stock_date_changed(imported){
	var type = imported>0 ? 'reset' : 'import';
	var f = $('f_'+type);
	var bid = f['branch_id'].value;
	var s_date = f['stock_take_date'].value;
	if(s_date==''){    // no branch selected
		$(f['sku_type']).update('<option value="">-- No Data --</option>');
		$('btn_'+type).disabled = false;
	}else{
	    $('div_'+type+'_stock_take_sku').update(_loading_);
	    $('btn_'+type).disabled = true;
	    
		new Ajax.Updater('div_'+type+'_stock_take_sku',phpself,{
			parameters:{
				a: 'check_available_stock_take_sku',
				branch_id: bid,
				date: s_date,
				imported: imported
			},
			onComplete: function(e){
                $('btn_'+type).disabled = false;
			},
			evalScripts: true
		});
	}
}
*/

{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<!-- Import -->
<div class="card mx-3">
	<div class="card-body">
		<fieldset>
			<legend><b>Import</b></legend>
			<form name="import_stock_take" id="f_import" onSubmit="return check_form(this);">
			<input type="hidden" name="a" value="import_stock_take" />
			<table>
				<tr>
					{if $BRANCH_CODE eq 'HQ'}
						<td><b class="form-label">Branch</b> 
							<select class="form-control" name="branch_id" onchange="stock_branch_change(0);">
						<option value="">-- Please Select --</option>
						{foreach from=$branch item=r}
							<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code} - {$r.description}</option>
						{/foreach}
						</select>&nbsp;&nbsp;&nbsp;</td>
					{else}
						<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
					{/if}
					<td>
						<div id="div_import_stock_take_date">
							{include file='admin.stock_take.import_page.stock_take_date.tpl' available_date=$date_data.import im_re=import type=0}
						</div>
					</td>
					<td>
						<span id="btn_import"><input type="submit" class="btn btn-primary ml-2" value="Import" /></span>
					</td>
				</tr>
				<tr>
					<td>
						<div class="form-inline">
							<b class="form-label">Action </b> [<a href="javascript:void(alert('
						1. No Auto Fill Zero\n
						- The system will import all Stock Take according to the selected branch and date only, without zerolise the rest of the non-scanned Stock Take items.\n\n
						2. Auto Add Zero for Same SKU Parent\n
						- The system auto zerolise the rest of the items under the same SKU family (Parent & Child) base on the items that scanned from Stock Take.\n\n
						3. Auto Add Zero for Non-scan Items\n
						- The system will import all Stock Take according to the selected branch and date, and then zerolise the rest of the non-scanned Stock Take items.\n\n
						4. Auto Add Zero for Selected Categories, Vendors & Brands\n
						- The system will import all Stock Take according to the selected branch and date, and then zerolise the rest of the non-scanned Stock Take items base on the selected categories, vendors and brands.\n
						'));">?</a>]
						&nbsp;&nbsp;<select class="form-control" name="fill_zero_options" onchange="check_options(this);">
							<option value="no_fill">No auto fill zero</option>
							{if !$config.stock_take_hide_auto_zero_sku_parent_option}<option value="fill_parent">Auto add zero for same SKU parent</option>{/if}
							<option value="fill_zero">Auto add zero for non-scan items</option>
							<option value="fill_zero_by_category_vendor">Auto add zero for selected Categories, Vendors & Brands</option>
						</select>
						</div>
						
						{*
						<input id="fill_zero_1" type="radio" name="fill_zero_options" value='no_fill' checked /><label for="fill_zero_1"><b>No auto fill zero</b></label> 
						<input id="fill_zero_2" type="radio" name="fill_zero_options" value='fill_parent' /><label for="fill_zero_2"><b>Auto add zero for same SKU parent</b></label> 
						<input id="fill_zero_3" type="radio" name="fill_zero_options" value='fill_zero' /><label for="fill_zero_3"><b>Auto add zero for non-scan items</b></label> 
						<input type="checkbox" name="fill_zero" /> <b>Set quantity to zero for items not in stock take</b>
						*}
					</td>
					<td id="td_sku_type" style="display:none;" colspan="{if BRANCH_CODE eq 'HQ'}2{else}1{/if}">
						<b>SKU Type:</b>
						<select name="sku_type">
							<option value="">All</option>
							{foreach from=$sku_type key=r item=st}
								<option value="{$st.code}">{$st.description}</option>
							{/foreach}
						</select>
					</td>
				</tr>
			</table>
				<div style="display:none;" id="fill_zero_by_category_vendor">
					<table>
						<tr>
							<td><b>Category (Max lv10)</b></td>
							<td>
								<input id="inp_search_cat_autocomplete" name="search_cat_autocomplete" style="font-size:14px;width:500px;" onclick="reset_category_autocomplete();" />
								<div id="div_search_cat_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
								<input type="button" value="Add" onclick="cat_add_autocomplete();" />
								<input type="hidden" name="category_id" value="" />
								<input type="hidden" name="category_desc" value="" />
								<span id="span_cat_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td colspan="{if BRANCH_CODE eq 'HQ'}3{else}2{/if}">
								<table>
									<tr>
										<td rowspan="5">
											<select multiple name="category_list" id="category_list" style="width:497px;height:100px;">
											{if $category}
												{foreach from=$category item=c}
													{if $c.sku_item_code ne ''}
														<option value="{$c.id}">{$c.description}</option>
														{*<script>init_cat_array_list('{$c.id}')</script>*}
													{/if}
												{/foreach}
											{/if}
											</select>
											<input type="hidden" name="category_id_list" value="" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td style="width:100px;"><input type="button" value="Remove" id="remove_cat" onClick="remove_cat_from_list();" disabled style="width:100px;"></td>
									</tr>
									<tr>
										<td><input type="button" value="Clear" id="clear_cat" onClick="clear_cat_from_list();" disabled style="width:100px;"></td>
									</tr>
									{foreach from=$category item=c}
										{if $c.sku_item_code ne ''}
											<script>init_cat_array_list('{$c.id}')</script>
										{/if}
									{/foreach}
			
									<tr><td>&nbsp;</td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>
							</td>
						</tr>
						<tr>
							<td><b>Vendor</b></td>
							<td>
								<input id="inp_search_vendor_autocomplete" name="search_vendor_autocomplete" style="font-size:14px;width:500px;" onclick="reset_vendor_autocomplete();" />
								<div id="div_search_vendor_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
								<input type="button" value="Add" onclick="vendor_add_autocomplete();" />
								<input type="hidden" name="vendor_id" value="" />
								<input type="hidden" name="vendor_desc" value="" />
								<span id="span_vendor_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td colspan="{if BRANCH_CODE eq 'HQ'}3{else}2{/if}">
								<table>
									<tr>
										<td rowspan="5">
											<select multiple name="vendor_list" id="vendor_list" style="width:497px;height:100px;">
											{if $vendors}
												{foreach from=$vendors item=v}
													{if $v.sku_item_code ne ''}
														<option value="{$v.id}">{$v.description}</option>
														{*<script>init_cat_array_list('{$c.id}')</script>*}
													{/if}
												{/foreach}
											{/if}
											</select>
											<input type="hidden" name="vendor_id_list" value="" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td style="width:100px;"><input type="button" value="Remove" id="remove_vendor" onClick="remove_vendor_from_list();" disabled style="width:100px;"></td>
									</tr>
									<tr>
										<td><input type="button" value="Clear" id="clear_vendor" onClick="clear_vendor_from_list();" disabled style="width:100px;"></td>
									</tr>
									{foreach from=$vendors item=v}
										{if $v.sku_item_code ne ''}
											<script>init_vendor_array_list('{$v.id}')</script>
										{/if}
									{/foreach}
			
									<tr><td>&nbsp;</td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>
							</td>
						</tr>
						<tr>
							<td><b>Brand</b></td>
							<td>
								<input id="inp_search_brand_autocomplete" name="search_brand_autocomplete" style="font-size:14px;width:500px;" onclick="reset_brand_autocomplete();" />
								<div id="div_search_brand_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
								<input type="button" value="Add" onclick="brand_add_autocomplete();" />
								<input type="hidden" name="brand_id" value="" />
								<input type="hidden" name="brand_desc" value="" />
								<span id="span_brand_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td colspan="{if BRANCH_CODE eq 'HQ'}3{else}2{/if}">
								<table>
									<tr>
										<td rowspan="5">
											<select multiple name="brand_list" id="brand_list" style="width:497px;height:100px;">
											{if $brands}
												{foreach from=$brands item=b}
													{if $b.sku_item_code ne ''}
														<option value="{$b.id}">{$b.description}</option>
														{*<script>init_cat_array_list('{$c.id}')</script>*}
													{/if}
												{/foreach}
											{/if}
											</select>
											<input type="hidden" name="brand_id_list" value="" />
										</td>
										<td></td>
									</tr>
									<tr>
										<td style="width:100px;"><input type="button" value="Remove" id="remove_brand" onClick="remove_brand_from_list();" disabled style="width:100px;"></td>
									</tr>
									<tr>
										<td><input type="button" value="Clear" id="clear_brand" onClick="clear_brand_from_list();" disabled style="width:100px;"></td>
									</tr>
									{foreach from=$brands item=b}
										{if $b.sku_item_code ne ''}
											<script>init_brand_array_list('{$b.id}')</script>
										{/if}
									{/foreach}
			
									<tr><td>&nbsp;</td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</form>
				{if $smarty.request.t eq 'import' and $smarty.request.msg}
					<span style="color:blue;">- {$smarty.request.msg}</span>
				{/if}
			</fieldset>
			
	</div>
</div>
<!-- Reset -->
<div class="card mx-3">
	<div class="card-body">
		<fieldset>
			<legend><b>Reset</b></legend>
			<form name="reset_stock_take" id="f_reset" onSubmit="return check_form(this);">
			<input type="hidden" name="a" value="reset_stock_take" />
			<table>
				<tr>
					{if $BRANCH_CODE eq 'HQ'}
						<td><b class="form-label">Branch</b>
						<select class="form-control" name="branch_id" onchange="stock_branch_change(1);">
						<option value="">-- Please Select --</option>
						{foreach from=$branch item=r}
							<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code} - {$r.description}</option>
						{/foreach}
						</select>&nbsp;&nbsp;&nbsp;</td>
					{else}
						<input class="form-control" type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
					{/if}
					<td>
						<div id="div_reset_stock_take_date" class="ml-3">
							{include file='admin.stock_take.import_page.stock_take_date.tpl' available_date=$date_data.reset im_re=reset type=1 }
						</div>
					</td>
					<td>&nbsp;&nbsp;&nbsp;
						<span id="btn_reset"><input class="btn btn-warning fs-08" type="submit" value="Reset"  /></span>
					</td>
				</tr>
			</table>
			</form>
				{if $smarty.request.t eq 'reset' and $smarty.request.msg}
					<span style="color:blue;">- {$smarty.request.msg}</span>
				{/if}
			</fieldset>
	</div>
</div>
{include file='footer.tpl'}
