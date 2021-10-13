{*
Revision History
================
4 Apr 2007 - yinsee
- check for $config.sku_application_require_multics to enable/disable multics code columns
- check for $config.sku_application_enable_variety to enable/disable adding more varieties when $items is not empty

6/7/2007 11:27:35 AM - yinsee
-  softline+outright only allow matrix only (config.sku_application_softline_outright_matrix_only)

7/20/2007 10:00:54 AM - yinsee
- removed OTS in check_allow_matrix()

7/25/2007 2:59:59 PM - yinsee
- disallow selecting discount rate with zero, except 'PWP' (currently, only ANEKA have 'PWP')

9/19/2007 1:32:03 PM -gary
- added only the last approval can appear the multics details for keyin.

10/1/2007 4:26:35 PM gary
- added function to reset the trade discount table to unchecked.

10/2/2007 3:24:20 PM yinsee
- no reset
- remove discount_value hidden field.

09.05.2008 18:16:30 Saw
- allow user to end package listing and continue new sku.

5/29/2008 12:09:29 PM yinsee
- "upon DN" change to "Upon SKU Application"

2/2/2009 11:03:00 AM Andy
- check $config['sku_always_show_trade_discount'] to show trade discount

12/29/2009 6:03:31 PM yinsee
- allow duplicate artno for matrix table if $config[sku_application_artno_allow_duplicate]

3/19/2010 5:25:25 PM Andy
- Automatically show receipt description if user is the last approval

6/2/2010 10:02:18 AM Alex
- add autocomplete_color_size function

8/13/2010 10:13:21 AM Andy
- Add can choose replacement item group when apply/edit sku.
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/19/2010 2:36:51 PM Andy
- Add config control to no inventory sku.

11/4/2010 12:34:40 PM Justin
- When changed to outright, hidden the Trade Discount and Trade Discount Table.
- Added the feature to hide Trade Discount Code when found config.

12/7/2010 5:01:09 PM Justin
- Removed the No Trade Discount radio button.
- Required user to select trade discount code whenever it is consignment type.
- Fixed the error generated every time load the page.

12/9/2010 3:24:22 PM Justin
- Removed the auto assign for default trade discount code that causing calculation errors.
- Fixed the calculation cost price bugs for matrix table.
- Added auto remove for all the price type percentages when sku type being changed into outright.
- Disabled the double calculation for recalculate cost price on both sku and matrix tables.
- Added the checking to disable/enable the cost price's field for both sku and matrix tables whenever sku type has been changed.

12/15/2010 5:12:56 PM Justin
- Fixed the bugs where keep alert required user to select trade discount code when sku type is outright.

12/16/2010 12:17:49 PM Justin
- Added the config check for enable/disable the cost price.

12/23/2010 12:55:45 PM Justin
- Fixed the bug not to replace cost price with selling price and disable cost price field when config_consignment_modules is on.

3/10/2011 4:43:37 PM Andy
- Add PO reorder qty min & max at edit/apply SKU.

4/26/2011 12:24:23 PM Justin
- Added scale type.
- Added new JS function to check fresh market that is "inherit" to category while display scale type for user to select.
- Amended the new JS function to always show scale type while it is "Yes" for fresh market.

5/19/2011 9:58:39 AM Alex
- include justkids.js if $config['ci_auto_gen_artno'] is on

5/19/2011 5:00:39 AM Justin
- Added Use Serial No column.

6/2/2011 6:34:20 PM Alex
- Remove auto change description when add new sku

6/3/2011 9:56:12 AM Justin
- Disabled the Scale Type field when no config set for enable fresh market.
- Modified certain function not to call out the checking for fresh market type when no config found.

9/12/2011 7:34:04 PM Alex
- add checking name artsize to avoid error=> check_artmcode()

9/15/2011 11:01:53 AM Justin
- Fixed the trade discount table is not update automatically when Category has been changed.

10/25/2011 11:46:32 AM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/8/2011 1:25:19 PM Andy
- Add checking to show local image if browser support FileReader.

11/10/2011 3:40:17 PM Andy
- Fix FileReader checking for browser compatible.

11/29/2011 4:16:32 PM Justin
- Added the checking to show hidden div while adding new photo.

12/12/2011 10:14:37 AM Andy
- Fix preview photo cannot show in firefox 2.

4/23/2012 4:44:12 PM Justin
- Added new feature to maintain PO reorder qty by branch.

5/7/2012 10:37:08 AM Andy
- Add "Category Discount (%)" and "Category Reward Point" can override by SKU.

7/3/2012 10:09:12 AM Justin
- Changed the field name of scale type.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

10/12/2012 4:56 PM Justin
- Enhanced to have date validation.

4/12/2013 2:24 PM Andy
- Fix empty dialog prompt when user click submit, fix it by user must wait artno/mcode validation finish first.
- Enhance the way how the javascript check the config.

07/12/2013 04:20 PM Justin
- Bug fixed on having wrong indication of have_sn, it should be 2=Yes instead of 1=Yes (pre-list).

8/29/2013 5:17 PM Fithri
- automatically strips out character that is not 0-9,a-Z,-_/ and space in artno field

9/13/2013 5:42 PM Justin
- Enhanced to allow user terminate Package Listing permanently by privilege.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

4/3/2014 2:28 PM Justin
- Enhanced to have "Notify Person" drown-down list for PO Reorder Qty.
- Enhanced to have PO Reorder by Child option.

6/3/2014 11:32 AM Justin
- Enhanced to have new ability that can upload images by PDF file.

6/9/2014 11:10 AM Justin
- Enhanced to accept to load "JPEG" image file.

8/21/2014 1:49 PM Justin 
- Enhanced to have Input, Output & Inclusive Taxes.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings.

9/25/2014 11:59 AM Justin
- Enhanced to show GST description from drop down list in full.

1/2/2015 4:43 PM Justin
- Enhanced to show GST inherit information.

1/23/2015 2:18 PM Andy
- Group Open Price and Allow Selling FOC into grouping named as Selling Price Settings.
- Enhance Open Price/Allow Selling FOC checking.
- Change the selling price must always >0 except is open price.

3/19/2015 5:58 PM Andy
- Fix wrong gp calculation.
- Change "Inclusive Tax" to "Selling Price Inclusive Tax".

10:51 AM 3/25/2015 Andy
- Enhance to immediately call calculate gst once item variety is added to the form.

3/26/2015 4:54 PM Andy
- Fix sku item edit cost by percentage bug.

4/10/2015 10:46 AM Andy
- Enhance the sku application matrix to copy cost when the next row cost is empty, if got data dont copy.

4/23/2015 10:28 AM Andy
- Fix the consignment cost gp calculation when change trade discount code.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

12/17/2015 1:01 PM DingRen
- add Allow Parent and Child duplicate MCode

1/9/2017 3:19 PM Andy
- Enhanced to only allow new customer to choose selling price inclusive tax = yes.
- Enhanced to not allow to edit selling price inclusive tax if it is already using 'inherit' or 'yes'.

4/25/2017 10:52 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long
- Bug fixed on "Serial No" caption is wrongly set to the middle.

9/11/2017 1:44 PM Justin
- Enhanced to have new feature "Use Matrix".
- Enhanced to allow user check/uncheck min qty by size from matrix.

2017-09-12 14:10 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/30/2017 10:41 AM Justin
- Enhanced to hide "Add Matrix" feature while config is turned on.

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

10/23/2018 3:08 PM Justin
- Enhanced the module to compatible with new SKU Type.
- Enhanced to load SKU Type list from database instead of hardcoded it.

5/28/2019 11:00 AM William
- Added new PO Reorder Qty "Moq".

8/15/2019 4:01 PM Andy
- Enhanced to no need check GST settings if no turn on GST.
- Enhanced to reduce ajax checking when changed category.

11/14/2019 4:16 PM William
- Enhanced to add "POS / Promotion" image to add and edit.

06/30/2020 11:17 AM Sheila
- Updated button css.

11/11/2020 4:35 PM Andy
- Added "Recommended Selling Price" (RSP) feature.

12/10/2020 12:34 PM Andy
- Fixed auto calculate RSP Discount bug.
*}
{include file=header.tpl}

{literal}
<style>
/* new added table cell */
#submitbtn[disabled]{
	background-color: grey !important;
	color: #000;
}

.input_matrix {
	font: bold 10px Arial;
}
.input_matrix td input {
	width:60px;
	font-size:10px;
	padding: 2px;
	
}
.input_matrix td input.ntp { /* price */
	background-color:#ff9;
}
.input_matrix td input[alt="header"] { /* header */
    background-color:#9f9;
	font-weight:bold;
}
.input_matrix td input.nte {    /* entry */
	background-color:#fff;
	color:#000;
	font-weight:bold;
	border:1px solid black;
}
.input_matrix td input.ntd { /* disabled */
	background-color:#eee;
	color:#eee;
	border:none;
}

.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}

.div_multi_select{
	border:1px solid grey;
	overflow:auto;
	padding: 2px;
	width:80%;
}

</style>
{/literal}

<script type="text/javascript">

var searched_id = 'findcat';
var total_item = -1;
var is_select = false;
var searching = false;
var search_ajax;
var new_open = true;
var config_sku_always_show_trade_discount = {if $config.sku_always_show_trade_discount}1{else}0{/if};
var config_consignment_modules = {if $config.consignment_modules}1{else}0{/if};
var config_enable_fresh_market_sku  = {if $config.enable_fresh_market_sku}1{else}0{/if};
var sku_artno_allow_specialchars = int('{$config.sku_artno_allow_specialchars}');
var sku_application_artno_allow_duplicate = int('{$config.sku_application_artno_allow_duplicate}');
var got_ci_auto_gen_artno = {if $config.ci_auto_gen_artno}1{else}0{/if};
var first_sku_id = '{$form.listing_fee_package_first_sku_id}';
var sku_count = '{$form.listing_fee_package_current}';
var gst_rate = 0;
var category_gst = 0;
var global_inclusive_tax = '{$global_gst_settings.inclusive_tax}';
var global_weight_decimal_points = '{$config.global_weight_decimal_points}';

{if $gst_settings}
var is_gst_active = true;
{else}
var is_gst_active = false;
{/if}

var phpself = '{$smarty.server.PHP_SELF}';

var gst_rate_list = [];
var gst_code_list = [];
{foreach from=$input_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
	gst_code_list['{$r.id}'] = '{$r.code}'
{/foreach}

{foreach from=$output_tax_list item=r}
    gst_rate_list['{$r.id}'] = '{$r.rate}'
	gst_code_list['{$r.id}'] = '{$r.code}'
{/foreach}

{literal}

var color_size_autocomplete = undefined;
var autocomplete_list = {};

function autocomplete_color_size(type,table,row,col,obj){

	var key = "_"+table+"_"+row+"_"+col;

	 if(!autocomplete_list[key]){

	    autocomplete_list[key] = new Ajax.Autocompleter("autocomplete_"+type+key, "div_autocomplete_"+type+"_choices"+key,"ajax_autocomplete.php?a=ajax_search_color_size",
		{
			paramName: type
		});
	}
}

function autocomplete_color_size_variety(type,table){

	var key = "_"+table;
	var check = type+table;


	 if(!autocomplete_list[check]){

	    autocomplete_list[check] = new Ajax.Autocompleter("autocomplete_"+type+key, "div_autocomplete_"+type+"_choices"+key,"ajax_autocomplete.php?a=ajax_search_color_size",
		{
			paramName: type
		});

	}
}

function check_allow_matrix()
{
{/literal}
{if $config.sku_application_softline_outright_matrix_only && !$config.sku_disable_add_matrix}
{literal}

    var v = document.f_a.sku_type.value;
	var ret = true;
	var have_matrix = false;
	var have_variety =false;
	// make sure there's no matrix table yet
	for(i=0;i<=total_item;i++)
	{
	    if (document.f_a.elements['item_type['+i+']'])
	    {
	        if (document.f_a.elements['item_type['+i+']'].value=='matrix')
	            have_matrix = true;
			else
			    have_variety = true;

		}
	}

	if ((v!='CONSIGN') && /^SOFTLINE/.test($('str_findcat').innerHTML))
	{
	    if ($('add_matrix')) showdiv('add_matrix');
	    if ($('add_variety')) hidediv('add_variety');

	    if (have_variety)
	    {
			alert('Please remove Variety items before submit.');
			return false;
		}
	}
	else
	{
	    if ($('add_matrix')) hidediv('add_matrix');
		if ($('add_variety')) showdiv('add_variety');

		if (have_matrix)
	    {
			alert('Please remove Matrix table items before submit.');
			return false;
		}
	}

{/literal}
{/if}
{literal}

	if(document.f_a.sku_type.value != "CONSIGN" || !document.f_a.sku_type.value){
		$('trade_discount_table').style.display = "none";
		if(!config_sku_always_show_trade_discount){
			$('trade_discount_type_table').style.display = "none";

			// set both of the radio buttons checked become false for trade discount and table
			document.f_a.trade_discount_type[0].checked = false;
			document.f_a.trade_discount_type[1].checked = false;
			
			if(document.f_a.default_trade_discount_code){
				for(var i=0; i<document.f_a.default_trade_discount_code.length; i++){
					document.f_a.default_trade_discount_code[i].checked = false;
					document.f_a.elements['trade_discount_table['+document.f_a.default_trade_discount_code[i].value+']'].value = '';
				}
			}

			for(var i=0; i<=total_item; i++){
				// unlock cost price for all SKU
				if(document.f_a['cost_price['+i+']'] != undefined) document.f_a['cost_price['+i+']'].readOnly = false;
				else if(document.f_a.elements["tbcost["+i+"][1]"]){
					// unlock cost price for all Matrix
					var r = 1;
				    while (document.f_a.elements["tbcost["+i+"]["+r+"]"] != undefined)
				    {
				        document.f_a.elements["tbcost["+i+"]["+r+"]"].readOnly = false;
						r++;
					}
				}
			}
		}
	}else{
		if(!config_sku_always_show_trade_discount){
			$('trade_discount_type_table').style.display = "";
			if (document.f_a.trade_discount_type!=undefined){
			    $('trade_discount_table').style.display = "";
			}

			for(var i=0; i<=total_item; i++){
				// lock cost price for all SKU
				if(document.f_a['cost_price['+i+']'] != undefined) document.f_a['cost_price['+i+']'].readOnly = true;
				else if(document.f_a.elements["tbcost["+i+"][1]"]){
					// lock cost price for all Matrix
					var r = 1;
				    while (document.f_a.elements["tbcost["+i+"]["+r+"]"] != undefined)
				    {
				        document.f_a.elements["tbcost["+i+"]["+r+"]"].readOnly = true;
						r++;
					}
				}
			}
		}else{
			$('trade_discount_table').style.display = "";
		}
	}

    check_is_last_approval();
	return true;
}


function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace("<span class=sh>", "");
	str.replace("</span>", "");

	// must select bottom-most category except softline
	if (str.indexOf('SOFTLINE')<0 && have_child!=0)
	{
		oid = document.f_a.category_id.value;
		document.f_a.category_id.value = '';
		$('str_findcat').innerHTML = 'You need to select a sub-category';
		obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
		show_child(oid);
		return;
	}

	$('str_findcat').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);

	check_allow_matrix();
	update_all_desc();
	//check_is_last_approval();
}

function hide_list()
{
	$(searched_id).style.display = 'none';
}

function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}

function add_matrix()
{
    total_item++;
    $('add_item_notify').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
    hidediv('add_item');

	var cat_id = document.f_a['category_id'].value;
	var sku_type = document.f_a['sku_type'].value;
	new Ajax.Updater(
		"new_items", "masterfile_sku_application.php",
	 	{
			method:'get',
	 	    parameters: 'a=ajax_add_matrix&n='+total_item+'&cat_id='+cat_id+'&sku_type='+sku_type,
	 	    insertion: Insertion.Bottom,
	 	    onComplete: function(){
                document.f_a['parent_child_duplicate_mcode'].value=1;
				form_added();
//				sel_brand();
	 	    },
	 	    evalScripts: true
		}
	);
}

function add_variety()
{
    total_item++;
    $('add_item_notify').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
    hidediv('add_item');

    var cat_id = document.f_a['category_id'].value;
	var sku_type = document.f_a['sku_type'].value;
	new Ajax.Updater(
		"new_items", "masterfile_sku_application.php",
	 	{
			method:'get',
	 	    parameters: 'a=ajax_add_form&n='+total_item+'&cat_id='+cat_id+'&sku_type='+sku_type,
	 	    insertion: Insertion.Bottom,
	 	    onComplete: function(){
				form_added();
//				sel_brand();
				additional_function();
				toggle_po_reorder_by_child();
				mst_gst_info_changed();
				calc_weight_kg();
	 	    },
			evalScripts: true
		}
	);
}

function copy_value(fieldname)
{
    document.f_a.elements[fieldname+"["+total_item+"]"].value = document.f_a.elements[fieldname+"["+(total_item-1)+"]"].value;
}

function cancel_item(id, item_id)
{
    if (!confirm('Are you sure?')) return;
    //$(id).style.display = 'none';
    if (item_id != undefined && item_id > 0)
    {
		ajax_request(
				"masterfile_sku_application.php",
				{
					method:'get',
					parameters: 'a=ajax_delete_variety&sku_item_id='+item_id,
					onComplete: function() {
						Effect.DropOut(id, {duration:0.5, afterFinish: function() { Element.remove(id); show_add_item();additional_function(); } });
					}
				}
		);
	}
	else
	{
		Effect.DropOut(id, {duration:0.5, afterFinish: function() { Element.remove(id); show_add_item();additional_function(); } });
	}
}

// check if we should show the add_item box
function show_add_item()
{
    {/literal}
	{if !$config.sku_application_enable_variety}
	// if variety is disabled, count the actual total items, if zero, show add_item box
	var n=0;
	for(var i=0;i<=total_item;i++)
	    if (document.f_a.elements['item_type['+i+']']) n++;
	//alert('total item: '+n);
	if (n==0) showdiv('add_item');
	{else}
	// if variety is enabled, always display add_item box
	showdiv('add_item');
	{/if}
	{literal}
}

// new item
function form_added()
{
 	$('add_item_notify').innerHTML = '';

	$('submitbtn').style.display='';
	if (total_item>0 && $('item['+(total_item-1)+']')) $('item['+(total_item-1)+']').className = "stdframe";
	$('item['+total_item+']').className = "stdframe active";
	if (document.f_a.elements["description0["+total_item+"]"])
		atom_update_fulldesc(total_item);
	else
	    document.f_a.elements["description["+total_item+"]"].value=document.f_a.brand_name.value+' '+$('autocomplete_category').value;

	// if discount table in used, disable new item's cost column
	if(document.f_a.sku_type.value == "CONSIGN" && !config_consignment_modules){
		if (document.f_a.elements["cost_price["+total_item+"]"]){
        	document.f_a.elements["cost_price["+total_item+"]"].readOnly = true;	
	        //document.f_a.elements["cost_price["+total_item+"]"].value = document.f_a.elements['trade_discount_table['+td_selected+']'].value + '%';
		}
	}
	
	if(is_gst_active){
		var item_type = document.f_a['item_type['+total_item+']'].value;
		// only variety need to calculate gst, matrix table will call the ajax grow table to recalculate
		if(item_type == 'variety'){
			calculate_gst(total_item);
		}
	}
	
	
	Effect.Appear('item['+total_item+']', {duration:.2, afterFinish: function() {window.scrollTo(0,$('item['+total_item+']').offsetTop); document.f_a.elements["artno["+total_item+"]"].focus()} });
 	init_click_hilite(document.f_a);
	show_add_item();
}

function percent_to_amount(selling,percent)
{
	if (percent.value.indexOf("%")>=0)
		percent.value = selling.value - (selling.value * float(percent.value) / 100);
	percent.value=round(percent.value,4);
}

function update_all_desc()
{
	var n;
	var v;

	if (new_open) return;

	for (n=0;n<=total_item;n++)
	{
	    if (document.f_a.elements["description0["+n+"]"])
	    {
	        // for variety table, reconcatenate the description
			//document.f_a.elements["description0["+n+"]"].value=v;
	    	atom_update_fulldesc(n);
	    }
	    else if (document.f_a.elements["description["+n+"]"])
	    {
	        // for matrix table, we only have description column
			document.f_a.elements["description["+n+"]"].value=document.f_a.brand_name.value + ' ' + $('autocomplete_category').value;
	    }
	}
}
function atom_update_gross(n)
{
	var sp = float(document.f_a.elements["selling_price["+n+"]"].value);
	if(is_gst_active){
		if($('span_gst_indicator_'+n).innerHTML=='Before'){
			sp = float(document.f_a.elements["gst_selling_price["+n+"]"].value);
		}
	}
    document.f_a.elements["gross["+n+"]"].value = round(sp - document.f_a.elements["cost_price["+n+"]"].value, 4);
	
	// gross profit percent
    var grossp = 0
    if(sp != 0){
		grossp = float(document.f_a.elements["gross["+n+"]"].value/sp)*100;
	}
	
	document.f_a.elements["grossp["+n+"]"].value = round(grossp,4);
	
	//calculate_gst(n);
}

function atom_update_fulldesc(n)
{
	if (new_open) return;

    // brand
	var newvalue = document.f_a.brand_name.value;

	// flavor
	if (document.f_a.elements["description4["+n+"]"].value != '')
	    newvalue += ' ' + document.f_a.elements["description4["+n+"]"].value;

	// category
	newvalue += ' ' + $('autocomplete_category').value;

	// size, weight, etc
	//var sz = '';

	if (document.f_a.elements["description1["+n+"]"].value != '')
	    newvalue += ' ' + document.f_a.elements["description1["+n+"]"].value;
	if (document.f_a.elements["description2["+n+"]"].value != '')
	    newvalue += ' ' + document.f_a.elements["description2["+n+"]"].value;
	if (document.f_a.elements["description3["+n+"]"].value != '')
	    newvalue += ' ' + document.f_a.elements["description3["+n+"]"].value;

	//sz = new String(sz).trim();
	//if (sz != '') newvalue += ' (' + sz + ')';

	if (document.f_a.elements["description5["+n+"]"].value != '')
	    newvalue += ' ' + document.f_a.elements["description5["+n+"]"].value;

    document.f_a.elements["description["+n+"]"].value=newvalue;

}

function check_jpg(obj,prev,opendiv)
{
	var v = obj.value;
	
	//alert(p);
    //prev = document.f_a.elements[p];
	prev.src = '';
	if (v == '')
	{
		prev.style.display = 'none';
		return;
	}

	nv = v;

	if (!/\.jpg|\.jpeg|\.pdf/i.test(nv))
	{
		prev.style.display = 'none';
		alert("Selected file must be a valid JPEG image or PDF file");
		v = '';
		return;
	}
	
	if (opendiv != '') $(opendiv).style.display = '';
    prev.style.display = '';
    
    if(typeof FileReader == 'undefined'){	// browser not support
		prev.src = "file:///" + v; 
	}else{
		if(/\.pdf/i.test(nv)){
			prev.src = "ui/pdf.jpg";
		}else{
			showLocalImage(obj, prev);
		}
	}
	var tmp_splt_id = obj.id.split("_");
	var item_no = tmp_splt_id[1];
	if($("div_uploaded_photo_"+item_no) != undefined) $("div_uploaded_photo_"+item_no).style.display = "";
	if($("uploaded_"+obj.id) != undefined) $("uploaded_"+obj.id).style.display = "";
	if($("del_"+prev.name) != undefined) $("del_"+prev.name).style.display = "";
	
	return;

}

//check promotion image exist or not
function check_promo_img_exist(obj, item_n){
	var promotion_img = $('promotion_img_'+item_n);
	var input_file = obj.value;
	if(/\.jpg|\.jpeg/i.test(promotion_img.src) || /\.jpg|\.jpeg/i.test(input_file)){
		alert("Please delete the old promotion image first.");
		event.preventDefault(); 
	}
}

//check image format
function check_promotion_img(obj, item_n){
	var v = obj.value;
	var promotion_img = $('promotion_img_'+item_n);
	var promotion_div = $('promotion_div_'+item_n);
	var promo_view = $('promo_view_'+item_n);
	
	//promotion image checking
	if(v.src == ''){
		promotion_img.hide();
		return;
	}
	if(!/\.jpg|\.jpeg/i.test(v)){
		promotion_div.hide();
		promotion_img.hide();
		obj.value = '';
		alert("Selected file must be a valid JPEG image or JPG file");
		return;
	}
	
	//show image if success
	promotion_div.show();
	promotion_img.show();
	promo_view.hide();
    if(typeof FileReader == 'undefined'){	// browser not support
		promotion_img.src = "file:///" + v; 
	}else{
		showLocalImage(obj, promotion_img);
	}
	promotion_img.onclick = function(){
		window.open(this.src,'','width=800,height=600');
	}
	return;
}

//remove promotion image
function delete_promotion_img(item_n){
	$('promotion_div_'+item_n).hide(); 
	$('promotion_photo_'+item_n).value = ''; 
	$('saved_promotion_photo_'+item_n).value = '';
	$('promotion_img_'+item_n).src='';
}

function check_a()
{
    if (check_login()) {
        if(config_sku_always_show_trade_discount==1){
            if(document.f_a.sku_type.value == "CONSIGN" && document.f_a.default_trade_discount_code.value==''){
                document.f_a.default_trade_discount_code.focus();
                alert('Please Select Discount Code');
                return false;
            }
        }

        if (!check_allow_matrix()) return false;
		
		var item = $$('input.receipt_desc');
		for(i=0;i<item.length;i++){
			var item_id = item[i].value;
			if(!check_receipt_desc_length(item_id))	return false;
		}

        if (!confirm('Press OK to submit the SKU Application')) return false;

        document.f_a.bsubmit.value = "Submitting ...";
        document.f_a.bsubmit.disabled = true;

        return true;
    }
    return false;
}

// listing fee selection changed
function sel_fee(v,f)
{
	$('lst_fee').style.display = 'none';
	$('in_kind').style.display = 'none';
	$('lst_package').style.display = 'none';
	$('lst_package2').style.display = 'none';

	if (v == 'Listing Fee')
	{
		$('lst_fee').style.display = '';
		if (f) document.f_a.listing_fee_amount.focus();
	}
	else if (v == 'In Kind')
	{
		$('in_kind').style.display = '';
		if (f) document.f_a.elements['listing_fee_inkind[item][0]'].focus();
	}
	else if (v == 'Package')
	{
	    $('lst_package').style.display = '';
	    if (f) document.f_a.listing_fee_package_amount.focus();
	}
	else if (v == 'Package2')
	{
	    $('lst_package2').style.display = '';
	    if (f) document.f_a.listing_fee_package_amount2.focus();
	}
	else
    	if (f) document.f_a.note.focus();
}

{/literal}
var td_selected = "{$form.default_trade_discount_code}";
{literal}

function update_td_selected(v,obj)
{
	if(v=='')   return false;
	td_selected=v;
	value=document.f_a.elements['trade_discount_table['+td_selected+']'].value;
	if(config_sku_always_show_trade_discount!=1){
		//for checking the discount value in php.
		if(td_selected == 'PWP' || value>0){
			recalculate_all_cost();
		}
		else{
			alert('Discount rate is 0, please select other discount type.');
			obj.checked=false;

			return;
		}
	}
}

function enable_all_cost()
{
	var n;
	var r;
	for (n=0;n<=total_item;n++)
	{
	    if (document.f_a.elements["cost_price["+n+"]"])
			document.f_a.elements["cost_price["+n+"]"].readOnly = false;
		else if (document.f_a.elements["tbcost["+n+"][1]"])
		{
		    r = 1;
		    while (document.f_a.elements["tbcost["+n+"]["+r+"]"] != undefined)
		    {
		        document.f_a.elements["tbcost["+n+"]["+r+"]"].readOnly = false;
				r++;
			}
		}
	}
}

function recalculate_all_cost(n)
{
	//console.log('recalculate_all_cost: '+n);

	var margin = 0;
	if(td_selected) margin = document.f_a.elements['trade_discount_table['+td_selected+']'].value;

	if (n != undefined)
	{
	    if (document.f_a.elements["cost_price["+n+"]"])
	    {
	    	if(!config_consignment_modules){
		        document.f_a.elements["cost_price["+n+"]"].readOnly = true;
				document.f_a.elements["cost_price["+n+"]"].value = margin + '%';
			}
			var inp_sp = document.f_a.elements['selling_price['+n+']'];
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before'){
					inp_sp = document.f_a.elements['gst_selling_price['+n+']'];
				}
			}
			percent_to_amount(inp_sp,document.f_a.elements["cost_price["+n+"]"]);
			atom_update_gross(n);
		}
		else if (document.f_a.elements["tbcost["+n+"][1]"])
		{
		    r = 1;
			var use_sp_gst = false;
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before')	use_sp_gst = true;
			}
		    while (document.f_a.elements["tbcost["+n+"]["+r+"]"] != undefined)
		    {
		    	if(!config_consignment_modules){
			        document.f_a.elements["tbcost["+n+"]["+r+"]"].readOnly = true;
					document.f_a.elements["tbcost["+n+"]["+r+"]"].value = margin + '%';
				}
				var inp_sp = use_sp_gst ? document.f_a.elements["tbprice_gst["+n+"]["+r+"]"] : document.f_a.elements["tbprice["+n+"]["+r+"]"];
				percent_to_amount(inp_sp,document.f_a.elements["tbcost["+n+"]["+r+"]"]);
				r++;
			} 
		}
		//console.log('finish recalculate_all_cost');
		return;
	}
	
	//console.log('recalculate_all_cost for all items');
//	alert('calc cost based on ' + td_selected);
	for (n=0;n<=total_item;n++)
	{
	    if (document.f_a.elements["cost_price["+n+"]"])
	    {
	    	if(!config_consignment_modules){
		        document.f_a.elements["cost_price["+n+"]"].readOnly = true;
				document.f_a.elements["cost_price["+n+"]"].value = margin + '%';
			}
			var inp_sp = document.f_a.elements['selling_price['+n+']'];
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before'){
					inp_sp = document.f_a.elements['gst_selling_price['+n+']'];
				}
			}
			percent_to_amount(inp_sp,document.f_a.elements["cost_price["+n+"]"]);
			atom_update_gross(n);
		}
		else if (document.f_a.elements["tbcost["+n+"][1]"])
		{
		    r = 1;
			var use_sp_gst = false;
			if(is_gst_active){
				if($('span_gst_indicator_'+n).innerHTML=='Before')	use_sp_gst = true;
			}
		    while (document.f_a.elements["tbcost["+n+"]["+r+"]"] != undefined)
		    {
		    	if(!config_consignment_modules){
			        document.f_a.elements["tbcost["+n+"]["+r+"]"].readOnly = true;
					document.f_a.elements["tbcost["+n+"]["+r+"]"].value = margin + '%';
				}
				var inp_sp = use_sp_gst ? document.f_a.elements["tbprice_gst["+n+"]["+r+"]"] : document.f_a.elements["tbprice["+n+"]["+r+"]"];
				percent_to_amount(inp_sp,document.f_a.elements["tbcost["+n+"]["+r+"]"]);
				r++;
			}
		}
	}
}

function td_sel(v)
{
 	if (v==1)
	{
	    $("trade_discount_table").style.display = '';
	    update_discount_table('brand', document.f_a.brand_id.value);
	    recalculate_all_cost();
	}
	else if (v==2)
	{
	    $("trade_discount_table").style.display = '';
	    update_discount_table('vendor', document.f_a.vendor_id.value);
	    recalculate_all_cost();
	}

	for(var i=0; i<document.f_a.default_trade_discount_code.length; i++){
		if(document.f_a.default_trade_discount_code[i].checked == true && document.f_a.elements['trade_discount_table['+document.f_a.default_trade_discount_code[i].value+']'].value == 0) document.f_a.default_trade_discount_code[i].checked = false;
	}
}

function update_discount_table(type, id)
{
	// clear TRADE_DISCOUNT table
{/literal}
{section name=c loop=$trade_discount_table}
	document.f_a.elements['trade_discount_table[{$trade_discount_table[c].code}]'].value = '';
{/section}
{literal}

	//clear all the checked in radio button.	
	/*var e = $('tbl_trade_discount').getElementsByClassName('trade');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^default_trade_discount_code');
	 	if (temp_1.test(e[i].id)){
			e[i].checked=false;
		}
	}*/
	
	if (id == 0) return;

    // retrieve
    ajax_request(
	'masterfile_sku_application.php',
	{
	    parameters: 'a=ajax_get_trade_discount_table&category_id='+document.f_a.category_id.value+'&'+type+'_id='+id,
	    method:'get',
	    evalScripts: true,
		onComplete: function(req, obj)
	    {
	        var formobj = document.f_a;
	        eval(req.responseText);
            //recalculate_all_cost();
		}
	});
}

function sel_department(){
	if(!document.f_a.category_id.value || document.f_a.sku_type.value != "CONSIGN" || !document.f_a.sku_type.value) return;
	else{
		var v = 0;
		for(var i=0; i<document.f_a.trade_discount_type.length; i++){
			if(document.f_a.trade_discount_type[i].checked == true) v = document.f_a.trade_discount_type[i].value;
		}

		if(v == 1){ // is brand
			if(!document.f_a.brand_id.value) return;
		}else if(v == 2){ // is vendor
			if(!document.f_a.vendor_id.value) return;
		}else return;

		td_sel(v);
	}
}

function sel_vendor()
{
    // update TRADE_DISCOUNT table
    var obj = document.f_a.vendor_id;

	if (document.f_a.trade_discount_type!=undefined && document.f_a.trade_discount_type[1].checked)
	{
	    update_discount_table('vendor', obj.value);
	}
}

function sel_brand()
{
	var obj = document.f_a.brand_id;

	if (obj.value == 0)
		document.f_a.brand_name.value = '';
	else
	    document.f_a.brand_name.value = document.f_a.brand.value;

	// update TRADE_DISCOUNT table
	if (document.f_a.trade_discount_type!=undefined && document.f_a.trade_discount_type[0].checked)
	{
		update_discount_table('brand', obj.value);
	}

	update_all_desc();
}

function enter_brand(obj)
{
    uc(obj);
	document.f_a.brand_name.value = obj.value;
	update_all_desc();
}

// expand the matrix table
function tb_expand(tbid,r,c)
{
	s = Form.serialize(document.f_a);
	s = s+'&a=ajax_grow_table&table='+tbid+'&add_row='+r+'&add_col='+c;

	new Ajax.Updater(
 		"matrix["+tbid+"]",
 		"masterfile_sku_application.php",
 		{
			method:'post',
			onComplete: function () {
				matrix_article_toggle(tbid, document.f_a.elements['own_article['+tbid+']'].checked);
				mst_gst_info_changed();
			},
		    parameters: s
		}
	);
}

// delete a col from the matrix table
function del_col(tbid,c)
{
	s = Form.serialize(document.f_a);
	s = s+'&a=ajax_grow_table&table='+tbid+'&del_col='+c;

	new Ajax.Updater(
 		"matrix["+tbid+"]",
 		"masterfile_sku_application.php",
 		{
			method:'post',
			onComplete: function () { matrix_article_toggle(tbid, document.f_a.elements['own_article['+tbid+']'].checked) },
		    parameters: s
		}
	);
}


// delete a row from the matrix table
function del_row(tbid,r)
{
	s = Form.serialize(document.f_a);
	s = s+'&a=ajax_grow_table&table='+tbid+'&del_row='+r;

	new Ajax.Updater(
 		"matrix["+tbid+"]",
 		"masterfile_sku_application.php",
 		{
			method:'post',
			onComplete: function () { matrix_article_toggle(tbid, document.f_a.elements['own_article['+tbid+']'].checked) },
		    parameters: s
		}
	);
}

// toggle the matrix table readonly stages when share/single mcode state changed
function matrix_article_toggle(n, v)
{

	if (v)
		$('item_article['+n+']').hide();
	else
	    $('item_article['+n+']').show();
	var cells = $('matrix['+n+']').getElementsByTagName('input');
	for (i=0;i<cells.length;i++)
	{
	    if (/^tbm*\[\d+\]/.test(cells[i].name) &&
			(!/^tbm*\[\d+\]\[\d+\]\[0\]$/.test(cells[i].name)
			&& !/^tbm*\[\d+\]\[0\]\[\d+\]$/.test(cells[i].name)))
		{
		    if(v)
			{
			    cells[i].className = 'nte';
				//cells[i].style.backgroundColor='';
				//cells[i].style.color='';
				//cells[i].style.borderWidth='1px';
				cells[i].disabled=false;
			}
			else
			{
				cells[i].className = 'ntd';
				/*cells[i].style.backgroundColor='#fff';
				cells[i].style.color='#fff';
				cells[i].style.borderWidth='0';*/
				cells[i].disabled=true;
			}
		}
	}
	
	calc_matrix_gst(n);
	toggle_gst_settings();
}

// add new inkind field
function add_inkind(obj,n)
{
	if (obj.alt != '') return;
    var s = obj.value;
	if (s=='') return;

	obj.alt='used';

	new Insertion.Bottom('in_kind', '<li> Item <input name="listing_fee_inkind[item]['+n+']" size=30 onblur="add_inkind(this,'+(n+1)+')">\nQty <input name="listing_fee_inkind[qty]['+n+']" size=5>\nUnit Cost <input name="listing_fee_inkind[cost]['+n+']" size=6 onchange="ctotal('+n+')">\nTotal Cost <input name="listing_fee_inkind[total_cost]['+n+']" size=6 onchange="cunit('+n+')"></li>\n');

}

function cunit(n)
{
	document.f_a.elements['listing_fee_inkind[cost]['+n+']'].value = round2(
		document.f_a.elements['listing_fee_inkind[total_cost]['+n+']'].value /
		document.f_a.elements['listing_fee_inkind[qty]['+n+']'].value);
}

function ctotal(n)
{
	document.f_a.elements['listing_fee_inkind[total_cost]['+n+']'].value = round2(
		document.f_a.elements['listing_fee_inkind[cost]['+n+']'].value *
		document.f_a.elements['listing_fee_inkind[qty]['+n+']'].value);
}

function enable_sheet_processing(str){
	$('submitbtn').disabled = true;
	$('span_sheet_processing').update(str).show();
}

function disable_sheet_processing(){
	$('submitbtn').disabled = false;
	$('span_sheet_processing').update('').hide();
}

function check_artmcode(obj, type, item_id)
{
	enable_sheet_processing(_loading_+' validating Art No/MCode. . .');
	
	if (sku_artno_allow_specialchars){
		obj.value = new String(obj.value).trim().toUpperCase();
	}else{
		obj.value = new String(obj.value).uczap();
	}

	if (obj.value == '') {
		disable_sheet_processing();	
		return;
	}

	if(!sku_application_artno_allow_duplicate || got_ci_auto_gen_artno){
		// check all mcode in page for same article/mcode, reject if found
		var x = document.f_a.getElementsByTagName('input');
		for (var i=0;i<x.length;i++)
		{
		    if (x[i] == obj || x[i].alt == 'header') continue; // skip self
		    if (x[i].type == 'text')
			{
				if ((type == 'artno' && (x[i].name.indexOf('artno') >= 0 || x[i].name.indexOf('tb[') >= 0)) || (type == 'mcode' && (x[i].name.indexOf('mcode') >= 0 || x[i].name.indexOf('tbm[') >= 0)))
				{
				    if (x[i].value == obj.value)
				    {
						alert('The Article No / Manufacturer\'s Code is already used in the current application.');
			           	obj.value = '';
						obj.focus();
						disable_sheet_processing();
						return;
					}
				}
			}
		}
	}

	// check database
	if (type == "artno"){
		if (/^artsize/.test(obj.name))
			var artno_mcode=(document.f_a['artno['+item_id+']'].value+" "+obj.value).trim();
		else
			var artno_mcode=obj.value.trim();
	}else
		var artno_mcode=(obj.value).trim();

    var parent_child_duplicate_mcode=document.f_a['parent_child_duplicate_mcode'].value;

	var s = 'a=ajax_check_artmcode&vendor_id='+ document.f_a.vendor_id.value + '&id='+ document.f_a.id.value + '&'+type+'=' +artno_mcode+'&brand_id='+ document.f_a.brand_id.value+'&category_id='+document.f_a.category_id.value+'&parent_child_duplicate_mcode='+parent_child_duplicate_mcode;
	ajax_request(
 		"masterfile_sku_application.php",
 		{
			method:'post',
			onComplete: function (m) {
				var msg = m.responseText.trim();
				
				if (msg != 'OK')
				{
					if(!msg)	msg = "Page failed to validate artno/mcode."
					alert("Error: "+msg);
					obj.value = '';
					obj.focus();
				}
				disable_sheet_processing();
			},
		    parameters: encodeURI(s)
		}
	);
}

function mcode_check()
{
	var vendor_id = document.f_a.vendor_id.value;
	if (vendor_id == '')
	{
	    alert("No vendor is selected.");
	    return;
	}
	if (empty(document.f_a.category_id, "You must enter Category.")){
		return false;
	}
	if (empty(document.f_a.brand_id, "You must enter Brand.")){
		return false;
	}		
	popwin = window.open('masterfile_sku_application.php?a=mcode_check&vendor_id='+vendor_id+'&category_id='+document.f_a.category_id.value+'&brand_id='+ document.f_a.brand_id.value, null, "width=400,height=500,status=no,scrollbars=yes,resizable=yes");
}

function check_is_last_approval(){
	var category_id = document.f_a['category_id'].value;
	var sku_type = document.f_a['sku_type'].value;
	
	if(!category_id)    toggle_receipt_desc(false);
	ajax_request(phpself, {
		parameters:{
			a: 'ajax_check_is_last_approval',
			cat_id: category_id,
			sku_type: sku_type
		},
		onSuccess: function(e){
			msg = trim(e.responseText);
			if(msg=='OK')   toggle_receipt_desc(true);
			else{
                toggle_receipt_desc(false);
			}
		},
		onComplete: function(e){
			msg = trim(e.responseText);
			if(msg && msg != 'OK' && msg !='NO'){
				toggle_receipt_desc(false);
				alert(msg);
			}
		}
	});
}

function toggle_receipt_desc(on){
	var all_tr = $$('#new_items tr.tr_receipt_desc');
	var all_inp = $$('#new_items input.inp_receipt_desc');
	
	for(var i=0; i<all_tr.length; i++){
		if(on){
            $(all_tr[i]).show();
            $(all_inp[i]).enable();
		}else{
            $(all_tr[i]).hide();
            $(all_inp[i]).disable();
		}
	}
}

function toggle_replacement_group(chx, item_n){
	var c = chx.checked;
	$('inp_ri_id_'+item_n).disabled = !c;
	$('inp_ri_group_name_'+item_n).disabled = !c;
}

function init_ri_autocomplete(item_n){
    new Ajax.Autocompleter("inp_ri_group_name_"+item_n, "autocomplete_replacement_group_choices_"+item_n, "ajax_autocomplete.php?a=ajax_search_replacement_item_group", {
        paramName: 'str',
        indicator: 'span_ri_loading_'+item_n,
		afterUpdateElement: function (obj, li) {
			$('inp_ri_id_'+item_n).value = li.title;
		}
	});
}

function check_fm_type(){
	/*var fm_type = document.f_a.is_fresh_market.value;
	if(fm_type != "inherit"){
		if(fm_type == "yes"){
			$("scale_type_area").style.display = ""; // always show the scale type
			document.f_a.scale_type.disabled = false;
		}else{
			$("scale_type_area").style.display = "none"; // do not need to check if it is not inherit (follow category)
			document.f_a.scale_type.disabled = true;
		}
		return;
	}
	new Ajax.Request('masterfile_sku.php',
		{
			method: 'get',
			parameters: 'a=ajax_check_fm_type&category_id='+document.f_a.category_id.value,
			onComplete: function(m) {
				if (m.responseText == 'yes'){
					$("scale_type_area").style.display = ""; // if found is market, then show
					document.f_a.scale_type.disabled = false;
				}else{
					if(m.responseText == 'no') $("scale_type_area").style.display = "none"; // it is not a fresh martket
					document.f_a.scale_type.disabled = true;
				}
			}
		}
	);*/
}

//fake function to avoid script error
additional_function = function(){};

function toggle_allow_selling_foc(item_n){
	var inp = document.f_a['allow_selling_foc['+item_n+']'];	// get the element
	
	if(!inp)	return false;	// element not found
	
	if(inp.checked){	// allow foc
		$('span_selling_foc-'+item_n).show();
		document.f_a['selling_foc['+item_n+']'].disabled = false;
		//check_selling_foc(item_n);
		check_selling_price_settings(inp, item_n);
	}else{	// not allow foc
		$('span_selling_foc-'+item_n).hide();
		document.f_a['selling_foc['+item_n+']'].disabled = true;
		//document.f_a['selling_price['+item_n+']'].readOnly = false;
	}
}

function check_selling_foc(item_n){
	var inp_selling_foc = document.f_a['selling_foc['+item_n+']']; // FOC checkbox
	var inp_selling_price = document.f_a['selling_price['+item_n+']'];	// selling price box
	
	var is_foc = inp_selling_foc.checked && (!inp_selling_foc.disabled);	// tick FOC and checkbox not disable
	
	/*inp_selling_price.readOnly = is_foc;
	if(is_foc){	// set selling price as FOC
		inp_selling_price.old_price = inp_selling_price.value;
		inp_selling_price.value = '0.00';
	}else{	// set no use FOC
		if(inp_selling_price.old_price){	// set back to use selling price before tick use FOC (if got)
			inp_selling_price.value = inp_selling_price.old_price;
		}
	}
	inp_selling_price.onchange();	// call onchange function*/
}

function cat_disc_inherit_changed(item_id){
	var sel;
	var div_disc_container;
	
	sel = document.f_a['cat_disc_inherit['+item_id+']'];
	div_disc_container = $('div_category_discount_container-member-'+item_id);
	
	
	if(sel.value == 'set'){
		$(div_disc_container).show();
	}else{
		$(div_disc_container).hide();
	}
}

function cat_disc_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v>100)	inp.value = '100.00';
		else if(v<=0){
			inp.value = 0;
		}
	}
}

function category_point_inherit_changed(item_id){
	var sel = document.f_a['category_point_inherit['+item_id+']'];
	var div_container = $('div_category_point_container-'+item_id);
	
	if(sel.value == 'set'){
		$(div_container).show();	
	}else{
		$(div_container).hide();
	}
}

function category_point_value_changed(inp){
	var v = inp.value.trim();
	
	if(v=='')	inp.value='';
	else{
		mf(inp,2);
		v = float(inp.value);
		if(v<=0){
			inp.value = 0;
		}
	}
}

function category_discount_branch_override_changed(item_id, bid){
	var c = $('inp_category_disc_override-'+item_id+"-"+bid).checked;
	$('item['+item_id+']').getElementsBySelector("input.inp_category_disc-"+item_id+"-"+bid).each(function(inp){
		inp.disabled = !c
	});
}

function category_point_branch_override_changed(item_id, bid){
	var c = $('inp_category_point_override-'+item_id+"-"+bid).checked;
	$('item['+item_id+']').getElementsBySelector("input.inp_category_point-"+item_id+"-"+bid).each(function(inp){
		inp.disabled = !c
	});
}

function toggle_prq_by_branch(obj){
	if(obj.checked == true){
		$('prq_by_branch').show();
	}else{
		$('prq_by_branch').hide();
	}
}

function date_validate(obj){
	if(validateTimestamp(obj.value)==false){
		alert("Invalid date format.");
		obj.value = "";
	}
}
function correct_artno(obj){
	var str1 = obj.value;
	if (!str1) return;
	var filtered_artno = '';
	for(var i=0; i<str1.length; i++){
		if ( !/[^a-zA-Z0-9-/ _]/.test(str1.charAt(i)) ) filtered_artno = filtered_artno+str1.charAt(i);
	}
	obj.value = filtered_artno;
}

function terminate_package_clicked(){
	if(!confirm("Are you sure want to terminate package?")) return false;
	
	document.location=phpself+'?a=terminate_listing&id='+first_sku_id+'&count='+sku_count;
}

function toggle_po_reorder_by_child(){
	if(document.f_a['po_reorder_by_child'] == undefined) return;
	
	if(document.f_a['po_reorder_by_child'].checked == true){
		$("div_po_reorder_qty").hide();
		$('new_items').getElementsByClassName("si_po_reorder_qty").each(function(inp){
			inp.disabled = false;
		});
		$('new_items').getElementsByClassName("tr_si_po_reorder_qty").each(function(tr){
			tr.show();
		});
	}else{
		$("div_po_reorder_qty").show();
		$('new_items').getElementsByClassName("si_po_reorder_qty").each(function(inp){
			inp.disabled = true;
		});
		$('new_items').getElementsByClassName("tr_si_po_reorder_qty").each(function(tr){
			tr.hide();
		});
	}
}

function load_category_GST(id) {

	var cid=int(id);

	ajax_request(phpself+'?a=ajax_load_category_GST&id='+cid, {
		method:'get',
		onSuccess: function(transport){
			category_gst = JSON.parse(transport.responseText);
			
			if(category_gst['input_tax_code'] != undefined && category_gst['input_tax_code'] != null) document.f_a['mst_input_tax'].options[0].text = "Inherit (Follow Category: "+category_gst['input_tax_code']+" ["+category_gst['input_tax_rate']+"%])";
			if(category_gst['output_tax_code'] != undefined && category_gst['output_tax_code'] != null) document.f_a['mst_output_tax'].options[0].text = "Inherit (Follow Category: "+category_gst['output_tax_code']+" ["+category_gst['output_tax_rate']+"%])";
			if(category_gst['inclusive_tax'] != undefined && category_gst['inclusive_tax'] != null) document.f_a['mst_inclusive_tax'].options[0].text = "Inherit (Follow Category: "+category_gst['inclusive_tax'].toUpperCase()+")";
		
			
			calculate_all_gst();
		}
	});
	
}

function calculate_all_gst(){
	//console.log('calculate_all_gst');
	// do looping for all items
	//console.log('item count: '+$('new_items').getElementsByClassName("input_artno").length);
	
	var input_artno_list = $$("#new_items input.input_artno");
	//input_artno.each(function(inp)
	for(var i=0; i<input_artno_list.length; i++)
	{
		//console.log('i: '+i);
		var inp = input_artno_list[i];
	
		var id = inp.readAttribute("item_id");
		//console.log('id: '+id);
		if (id != undefined) {
			var item_type = document.f_a['item_type['+id+']'].value;
			//console.log('item_type: '+item_type);
			
			if (item_type == 'variety'){
				//console.log('start to calculate_gst');
				calculate_gst(id);
				//console.log('finish calculate_gst');
			}else{
				//console.log('start to calc_matrix_gst');
				calc_matrix_gst(id);
				//console.log('finish calc_matrix_gst');
			}
		}
		//console.log('finish loop');
	}
	
	mst_gst_info_changed();
}

function calculate_gst(id, obj){
	//console.log('calculate_gst, id = '+id);
	if(is_gst_active){
		var output_tax = document.f_a['dtl_output_tax['+id+']'].value;
			
		// if found item is inherit to SKU, get output tax from SKU
		if(output_tax == -1) output_tax = document.f_a['mst_output_tax'].value;

		if(output_tax == -1 && document.f_a['category_id'].value > 0){ // found it is inherit to category
			gst_rate = float(gst_rate_list[category_gst['output_tax']]);
		}else{
			gst_rate = float(gst_rate_list[output_tax]);
		}
		
		var inclusive_tax = document.f_a['dtl_inclusive_tax['+id+']'].value;
		var item_type = document.f_a['item_type['+id+']'].value;
		
		// found it is inherit to SKU
		if(inclusive_tax == "inherit") inclusive_tax = document.f_a['mst_inclusive_tax'].value;
		
		// found SKU inherit to category
		if(inclusive_tax == "inherit") inclusive_tax = category_gst['inclusive_tax'];
		
		// update label for selling price before or after GST
		$("span_gst_indicator_"+id).update((inclusive_tax=='no')?"After":"Before");
		
		// update GST rate info
		//console.log('gst_rate = '+gst_rate);
		$('gst_perc_'+id).update(gst_rate);
		
		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "gst_selling_price["+id+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			document.f_a['selling_price['+id+']'].value = round(selling_price, 2);
		}else{
			var selling_price = float(document.f_a['selling_price['+id+']'].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			document.f_a['gst_selling_price['+id+']'].value=round(gst_selling_price,2);
		}
		
		var gp_selling_price = 0;
		if(inclusive_tax == 'yes'){
			gp_selling_price = document.f_a["gst_selling_price["+id+"]"].value;
		}else{
			gp_selling_price = document.f_a["selling_price["+id+"]"].value;
		}

		// gross profit amt
		document.f_a["gross["+id+"]"].value = round(gp_selling_price - document.f_a["cost_price["+id+"]"].value, 4);

		// gross profit percent
		var grossp = 0
		if(gp_selling_price != 0){
			grossp = float(document.f_a["gross["+id+"]"].value/gp_selling_price)*100;
		}
		
		document.f_a["grossp["+id+"]"].value = round(grossp,4);
		
		document.f_a['gst_amount['+id+']'].value = round(gst_amt, 2);
		
	}
	
	// recalculate gp
	if (document.f_a.sku_type.value == 'CONSIGN'){
		recalculate_all_cost(id, obj);
	} else {
		atom_update_gross(id);
	}
}

function calc_matrix_gst(id,rid,field_type) {
	//console.log('calc_matrix_gst');
	if(is_gst_active){
		//master sku
	  var mst_output_tax = document.f_a['mst_output_tax'].value;
	  var mst_inclusive_tax = document.f_a['mst_inclusive_tax'].value;

	  if (category_gst['output_tax'] && mst_output_tax==-1) mst_output_tax=gst_rate_list[category_gst['output_tax']]; // found it is inherit to category
	  else mst_output_tax=gst_rate_list[mst_output_tax];

	  if (category_gst['inclusive_tax'] && mst_inclusive_tax=='inherit') mst_inclusive_tax=category_gst['inclusive_tax']; // found it is inherit to category

	  var item_type = document.f_a['item_type['+id+']'].value;
	  var output_tax = document.f_a['dtl_output_tax['+id+']'].value;
	  var inclusive_tax = document.f_a['dtl_inclusive_tax['+id+']'].value;
	  if(output_tax == -1) gst_rate = float(mst_output_tax); // found it is inherit to master sku
	  else gst_rate = float(gst_rate_list[output_tax]);

	  if (inclusive_tax=='inherit') inclusive_tax=mst_inclusive_tax; // found it is inherit to master sku

	  $('span_gst_rate_'+id).update(gst_rate);

	  $('span_gst_indicator_'+id).update((inclusive_tax=='no')?"After":"Before");


	  if (rid!=undefined && field_type=='gst_price') {
		var gst_selling_price = float(document.f_a['tbprice_gst['+id+']['+rid+']'].value);

		if (inclusive_tax=='no') {
			var selling_price=(gst_selling_price*100)/(100+gst_rate);
			var gst_amt=float(selling_price) * gst_rate / 100;
		}
		else{
			var gst_amt=float(gst_selling_price) * gst_rate / 100;
			var selling_price=float(gst_selling_price+gst_amt);
		}

		document.f_a['tbgst['+id+']['+rid+']'].value=round(gst_amt,2);
		document.f_a['tbprice['+id+']['+rid+']'].value=round(selling_price,2);
	  }
	  else{
		if(document.f_a.elements["tbcost["+id+"][1]"]){
		  var rid = 1;

		  while (document.f_a.elements["tbprice["+id+"]["+rid+"]"] != undefined){
			var selling_price = float(document.f_a['tbprice['+id+']['+rid+']'].value);

			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}

			document.f_a['tbgst['+id+']['+rid+']'].value=round(gst_amt,2);
			document.f_a['tbprice_gst['+id+']['+rid+']'].value=round(gst_selling_price,2);

			rid++;
		  }
		}
	  }
	  //console.log('done calculate gst matrix');
	}
	
	if (document.f_a.sku_type.value == 'CONSIGN'){
		recalculate_all_cost(id);
	}
	//console.log('done calculate_gst');
}

function toggle_gst_settings(){
	$$('.gst_settings').each(function(ele,index){
		if(is_gst_active == 0) ele.hide();
		else{
			ele.show();
		} 
	});
}

function toggle_open_price(item_id){
	var inp = document.f_a["open_price["+item_id+"]"];
	if(inp.checked){
		// check all other sp settings
		check_selling_price_settings(inp, item_id);
	}
}

function check_selling_price_settings(chx_selected, item_id){
	$$('#ul_selling_price_settings-'+item_id+' input.chx_sp_settings').each(function(inp){
		if(inp != chx_selected){
			inp.checked = false;
			if(inp.onchange)	inp.onchange();
		}
	});
}

function cost_changed(inp, item_id){
	var inp_sp = document.f_a.elements['selling_price['+item_id+']'];
	if(is_gst_active){
		if($('span_gst_indicator_'+item_id).innerHTML=='Before'){
			inp_sp = document.f_a.elements['gst_selling_price['+item_id+']'];
		}
	}
			
	// convert percent to amount
	percent_to_amount(inp_sp, inp); 
	
	// update gross amt and %
	atom_update_gross(item_id);
}

function matrix_cost_changed(inp, item_id, row_id){
	var use_sp_gst = false;
	var next_row_id = row_id+1;
	if(document.f_a.elements['tbcost['+item_id+']['+next_row_id+']'] == undefined){
		next_row_id = 0;	// no more next row
	}
	if(is_gst_active){
		if($('span_gst_indicator_'+item_id).innerHTML=='Before')	use_sp_gst = true;
	}
	var inp_sp = use_sp_gst ? document.f_a.elements["tbprice_gst["+item_id+"]["+row_id+"]"] : document.f_a.elements["tbprice["+item_id+"]["+row_id+"]"];
			
	// convert percent to amount
	percent_to_amount(inp_sp, inp);
	
	// clone value to next row
	if (inp.value>0){
		if(next_row_id>0){
			if(float(document.f_a.elements['tbcost['+item_id+']['+next_row_id+']'].value)<=0){
				document.f_a.elements['tbcost['+item_id+']['+next_row_id+']'].value=inp.value;
			}
		}
	}
}

function matrix_changed(){
	var use_matrix = document.f_a['use_matrix'].value;
	if(use_matrix != "yes"){
		$('div_sku_matrix').hide();
	}else{
		$('div_sku_matrix').show();
	}
}

function sku_matrix_override_changed(size){
	var c = $('inp_sku_matrix_override-'+size).checked;
	$(document.f_a).getElementsByClassName("sku_matrix_min_qty-"+size).each(function(inp){
		inp.readOnly = c;
	});
}

function calc_weight_kg(item_id){
	obj = document.f_a['weight_kg['+0+']'];
	if(obj == undefined) return;
	
	var weight_ratio = obj.value;
	
	if(weight_ratio == 0) return;
	
	if(item_id == undefined){
		for (n=1;n<=total_item;n++){
			var opt = document.f_a["packing_uom_id["+n+"]"];
			if (opt != undefined){
				var fraction = opt.options[opt.selectedIndex].readAttribute("uom_fraction");
				var child_weight = round(weight_ratio * fraction, global_weight_decimal_points);
				
				document.f_a['weight_kg['+n+']'].value = child_weight;
			}
		}
	}else{
		var opt = document.f_a["packing_uom_id["+item_id+"]"];
		if (opt != undefined){
			var fraction = opt.options[opt.selectedIndex].readAttribute("uom_fraction");
			var child_weight = round(weight_ratio * fraction, global_weight_decimal_points);
			
			document.f_a['weight_kg['+item_id+']'].value = child_weight;
		}
	}
}

function prompt_rsp_notification(){
	var str = "RSP = Recommended Selling Price\n";
	str += "====================================\n";
	str += "- Selling Price will be calculated by using RSP - RSP Discount.\n";
	str += "- After turn on Use RSP, users still able to enter different RSP Discount and Selling Price at SKU Change Selling Price module.\n";
	str += "- But RSP can only have one in the Masterfile SKU."
	//str += "- The first time you turn on RSP, system will automatically update to the branches latest selling price if detected any branch latest selling price are different.\n";
	
	//str += "\nIMPORTANT: If the sku got change selling price history in any branch, you cannot untick RSP and also cannot edit RSP after turn on Use RSP."
	alert(str);
}

function item_selling_price_changed(item_id){
	var inp = document.f_a['selling_price['+item_id+']'];
	
	// round 2
	inp.value = round2(inp.value);
	
	var use_rsp = document.f_a['use_rsp['+item_id+']'].checked;
	if(use_rsp){
		var rsp_price = float(document.f_a['rsp_price['+item_id+']'].value);
		if(inp.value > rsp_price){
			alert('Your Selling Price ('+round2(inp.value)+') is more than RSP ('+round2(rsp_price)+')\nSelling Price will be auto adjust to '+round2(rsp_price));
			inp.value = round2(rsp_price);
		}
		
		// Calculate RSP
		calculate_rsp(item_id, 'rsp_discount');
	}
	
	// calculate gst
	calculate_gst(item_id, inp);
}

function use_rsp_changed(item_id){
	var use_rsp = document.f_a['use_rsp['+item_id+']'].checked;
	
	if(use_rsp){
		// Use RSP
		document.f_a['rsp_price['+item_id+']'].readOnly = false;
		document.f_a['rsp_discount['+item_id+']'].readOnly = false;
		
		// Calculate RSP
		calculate_rsp(item_id, 'selling_price');
	}else{
		// No Use RSP
		document.f_a['rsp_price['+item_id+']'].value = '0.00';
		document.f_a['rsp_discount['+item_id+']'].value = '';
		
		document.f_a['rsp_price['+item_id+']'].readOnly = true;
		document.f_a['rsp_discount['+item_id+']'].readOnly = true;
	}
	
}

function rsp_price_changed(item_id){
	var inp_rsp_price = document.f_a['rsp_price['+item_id+']'];
	inp_rsp_price.value = round2(inp_rsp_price.value);
	
	// Calculate RSP
	calculate_rsp(item_id, 'selling_price');
}

function rsp_discount_changed(item_id){
	var inp_rsp_discount = document.f_a['rsp_discount['+item_id+']'];
	var discount_pattern = validate_discount_format(inp_rsp_discount.value);
	
	if(inp_rsp_discount.value != '' && discount_pattern == ''){
		alert('Invalid Discount Pattern');
	}
	inp_rsp_discount.value = discount_pattern;
	
	// Calculate RSP
	calculate_rsp(item_id, 'selling_price');
}

function calculate_rsp(item_id, target_input){
	// RSP
	var rsp_price = float(document.f_a['rsp_price['+item_id+']'].value);
	var inp_rsp_discount = document.f_a['rsp_discount['+item_id+']'];
	var inp_selling_price = document.f_a['selling_price['+item_id+']'];
	
	if(target_input == 'selling_price'){
		// Get RSP Discount
		rsp_discount_amt = float(get_discount_amt(rsp_price, inp_rsp_discount.value));
		
		// Calculate Selling Price by using RSP - RSP Discount
		var selling_price = float(rsp_price - rsp_discount_amt);
		inp_selling_price.value = round2(selling_price);
	}else if(target_input == 'rsp_discount'){
		// Get Selling Price
		var selling_price = float(inp_selling_price.value);
		
		// Calculate RSP Discount by using RSP - Selling Price
		var rsp_discount = round(float(rsp_price - selling_price), 2);
		inp_rsp_discount.value = rsp_discount;
	}
}
</script>

{/literal}

{if $revise}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">SKU Application Revise (ID#{$form.id})</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="alert alert-primary mx-3 rounded">
	<ul style="list-style-type: none;">
		<li> Your New SKU Application was rejected. You can update and re-submit the revised application for approval.
		<li> Please check the Application Status box for rejecting reasons.
		</ul>
</div>
{else}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">SKU Application</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="alert alert-primary mx-3 rounded">
	<ul style="list-style-type: none;">
		<li> You can apply ONLY ONE SKU at one time. Product with different MCODE have to be applied as another SKU.
		<!--li> Product with multiple variety (eg: flavor) should be grouped into one SKU.
		<li> To add more than one varieties, click the <a href="#additem">Add another item</a> icon at bottom of page to insert additional item.-->
		</ul>
</div>
{/if}

<form action="{$smarty.server.PHP_SELF}" name="f_a" method=post ENCTYPE="multipart/form-data" onsubmit="return check_a()">
<input type=hidden name=a value="save">
<input type=hidden name=id value="{$form.id|default:0}">
<input type=hidden id="max_artno_id" name="max_artno" value="{$form.max_artno}">
<input type=hidden id="max_num_id" name="max_num" value="{$form.max_num|default:0}">

<div class="stdframe card mx-3" >
<!--- show approval log --->
<div class="card-body">
	{if $form.approval_history_items}
	<h4>Application Status</h4>
	<div style="background-color:#fff; border:1px dashed #00f; padding:5px;">
	{foreach from=$form.approval_history_items item=aitem}
	<div>
	<font class="small" color=##006600>{$aitem.timestamp} by {$aitem.u}</font><br>

	{if $aitem.status == 1}
	<img src=ui/checked.gif vspace=2 align=absmiddle> <b>{$aitem.log.general}</b>
	{elseif $aitem.status == 2}
	{foreach from=$aitem.log.general item=log key=log_k}
	{if $log ne 'Others'}
	<img src=ui/deact.png vspace=2 align=absmiddle> {$log}<br>
	{/if}
	{/foreach}
	{else}
	<img src=ui/del.png vspace=2 align=absmiddle> <b>{$aitem.log.general}</b>
	{/if}

	</div>
	{/foreach}
	</div>
{/if}

<h4>General Information</h4>
{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<div class="alert alert-danger mx-3 rounded"><li> {$e}</li></div>
{/foreach}
</ul></div></div>
{/if}
{if $form.listing_fee_package_first_sku_id == 0}
	{assign var=package_readonly value=""}
{else}
	{assign var=package_readonly value="readonly"}
{/if}
<table  border=0 cellpadding=2 cellspacing=1>
<tr>
	<b class="form-label">Category<span class="text-danger"> *</span></b>
	<div class="form-inline">
		<input class="form-control" id="sku_category_id" name="category_id" size=20 value="{$form.category_id}" onchange="" readonly>
		&nbsp;&nbsp;&nbsp;<input class="form-control" id="autocomplete_category" name="category" value="{$form.category|escape}" size=100>
	</div>
	<div  id="autocomplete_category_choices" class="autocomplete" style="width:500px !important"></div>
	
	<div id="str_findcat" class="small">{$form.cat_tree|default:"Type the category name to search..."}</div>
	
</tr>
<tr>
	<b class="form-label mt-2">SKU Type<span class="text-danger"> *</span></b>
	<select class="form-control" name=sku_type onchange="check_allow_matrix();">
	<option value="">-- Select --</option>
	{foreach from=$sku_type_list key=st_code item=st}
		<option value="{$st_code}" {if $form.sku_type eq $st_code}selected{/if}>{$st.description}</option>
	{/foreach}
	</select> 
	
</tr>

{if $config.enable_no_inventory_sku}
<!-- No Inventory-->
<tr>
	<b class="form-label">SKU Without Inventory</b>
	
	    <select class="form-control" name="no_inventory">
	        {foreach from=$inherit_options key=k item=val}
	            <option value="{$k}" {if $form.no_inventory eq $k}selected {/if}>{$val}</option>
	        {/foreach}
	    </select>

</tr>
{else}<input type="hidden" name="no_inventory" value="inherit" />
{/if}

{if $config.enable_fresh_market_sku}
<!-- Is Fresh Market SKU-->

	<b class="form-label">Is Fresh Market SKU</b>
	    <select class="form-control" name="is_fresh_market" onchange="check_fm_type(this);">
	        {foreach from=$inherit_options key=k item=val}
	            <option value="{$k}" {if $form.is_fresh_market eq $k}selected {/if}>{$val}</option>
	        {/foreach}
	    </select>&nbsp;&nbsp;&nbsp;&nbsp;

</tr>
{else}
	<input class="form-control" type="hidden" name="is_fresh_market" value="inherit" />
{/if}

{if !$config.consignment_modules}
	
	<b class="form-label">Scale Type</b>
			<select class="form-control" name="mst_scale_type">
				{foreach from=$scale_type_list key=st_value item=st_name}
					{if $st_value >= 0}
						<option value="{$st_value}" {if $form.scale_type eq $st_value}selected {/if}>{$st_name}</option>
					{/if}
				{/foreach}
			</select>
{/if}

<tr>
	<b class="form-label">Vendor<span class="text-danger"> *</span></b>
	<div class="form-inline">
		<input class="form-control" id="sku_vendor_id" name="vendor_id" type=hidden size=1 value="{$form.vendor_id}" onchange="" readonly>
	<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
	<div id="autocomplete_vendor_choices" class="autocomplete"></div>
	
	&nbsp;&nbsp;&nbsp;<input type="button" class="btn btn-primary" value="Verify Article/M-Code" onclick="mcode_check()">
	</div>
</tr>
<tr>
	<b class="form-label">Brand</b>
	<input class="form-control" name=brand_name type=hidden value="">
	<input class="form-control" name="brand_id" type=hidden value="{$form.brand_id}" readonly>
	<input class="form-control" id="autocomplete_brand" name="brand" value="{$form.brand}" >
	<div id="autocomplete_brand_choices" class="autocomplete"></div>
	{*<img src=ui/rq.gif align=absbottom title="Required Field">*}
</tr>
{if $config.sku_always_show_trade_discount}
<tr id="trade_discount_table">
	<b class="form-label">Trade Discount Code</b>
	    <select class="form-control" id="sku_default_trade_discount_code" name="default_trade_discount_code" onChange="update_td_selected(this.value,this);">
	    <option value="">-- Please Select --</option>
	    {section name=c loop=$trade_discount_table}
	        <option value="{$trade_discount_table[c].code}" {if $form.default_trade_discount_code eq $trade_discount_table[c].code}selected {/if}>{$trade_discount_table[c].code}</option>
		{/section}
		</select>
		<table style="display:none;">
		<tr>
		{section name=c loop=$trade_discount_table}
		{assign var=ccode value=`$trade_discount_table[c].code`}
		<th><input class="form-control" size=3 name="trade_discount_table[{$trade_discount_table[c].code}]" value="{$form.trade_discount_table.$ccode}" readonly></th>
		{/section}
		</tr>
	    </table>
</tr>
{else}
<tr id="trade_discount_type_table">
	<b class="form-label mt-2">Trade Discount</b>
	    <input type=radio name=trade_discount_type value=1 {if $form.trade_discount_type == 1}checked{/if} onclick="td_sel(this.value)"> use Brand Table
	    <input type=radio name=trade_discount_type value=2 {if $form.trade_discount_type == 2}checked{/if} onclick="td_sel(this.value)"> use Vendor Table
  
</tr>
<tr id="trade_discount_table" {if $form.trade_discount_type == 0}style="display:none"{/if}>
	<b class="form-label mt-2">Trade Discount Table</b>
	    <table id=tbl_trade_discount border=0 cellpadding=2 cellspacing=1>
		<tr>
		{section name=c loop=$trade_discount_table}
		<td>&nbsp;&nbsp;<input type=radio id="default_trade_discount_code" name=default_trade_discount_code onclick="update_td_selected(this.value,this);" value="{$trade_discount_table[c].code}" {if $form.default_trade_discount_code eq $trade_discount_table[c].code}checked{/if}>&nbsp;{$trade_discount_table[c].code}</td>
		{/section}
		</tr>
		<tr>
		{section name=c loop=$trade_discount_table}
		{assign var=ccode value=`$trade_discount_table[c].code`}
		<th><input size=3 class="trade_discount_table form-control" name="trade_discount_table[{$trade_discount_table[c].code}]" value="{$form.trade_discount_table.$ccode}" readonly></th>
		{/section}
		</tr>
	    </table>
	
</tr>
{/if}
<tr>
	<b class="form-label mt-2">Listing Fee</b>
	<select class="form-control" name=listing_fee_type onchange="sel_fee(this.value,1)">
	{if !$package_readonly}
		<option {if $form.listing_fee_type eq 'No Listing Fee'}selected{/if}>No Listing Fee</option>
		<option {if $form.listing_fee_type eq 'Listing Fee'}selected{/if}>Listing Fee</option>
		<option {if $form.listing_fee_type eq 'Package'}selected{/if} value="Package">Package (share Listing Fee for multiple SKU)</option>
		<option {if $form.listing_fee_type eq 'Package2'}selected{/if} value="Package2">Package (share Listing Fee for Variety)</option>
		<option {if $form.listing_fee_type eq 'In Kind'}selected{/if}>In Kind</option>
	{else}
	    <option value="Package">Package (item {$form.listing_fee_package_current} of {$form.listing_fee_package_count})</option>
	{/if}
	</select>
	<!-- fee -->
	<div id=lst_fee style="display:none;padding:2px 0px;">Amount ({$config.arms_currency.symbol}) <input name=listing_fee_amount size=5 value="{$form.listing_fee_amount}">
	<select class="form-control" name=listing_fee_when onchange="$('upon_dn').style.display = (this.value == 'Upon SKU Application' ? '' : 'none')">
	<option {if $form.listing_fee_when eq 'Upon SKU Application'}selected{/if}>Upon SKU Application</option>
	<option {if $form.listing_fee_when eq 'Upon PO'}selected{/if}>Upon PO</option>
	<option {if $form.listing_fee_when eq 'Upon Goods Received'}selected{/if}>Upon Goods Received</option>
	</select>
	<span id=upon_dn {if $form.listing_fee_when ne '' and $form.listing_fee_when ne 'Upon SKU Application'}style="display:none"{/if}>DN No: <input name=listing_fee_dn size=5 value="{$form.listing_fee_dn}"></span>
	</div>
	<!-- package sku -->
	<div id=lst_package style="display:none;padding:2px 0px;">
	Package Amount ({$config.arms_currency.symbol}) <input name=listing_fee_package_amount size=5 value="{$form.listing_fee_package_amount}" {$package_readonly}>
	{if $package_readonly}
	<input class="form-control" name=listing_fee_when2 value="{$form.listing_fee_when}" readonly>
	{else}
	<select class="form-control" name=listing_fee_when2 onchange="$('upon_dn2').style.display = (this.value == 'Upon SKU Application' ? '' : 'none')">
	<option {if $form.listing_fee_when eq 'Upon SKU Application'}selected{/if}>Upon SKU Application</option>
	<option {if $form.listing_fee_when eq 'Upon PO'}selected{/if}>Upon PO</option>
	<option {if $form.listing_fee_when eq 'Upon Goods Received'}selected{/if}>Upon Goods Received</option>
	</select>
	<span id=upon_dn2 {if $form.listing_fee_when ne '' and $form.listing_fee_when ne 'Upon SKU Application'}style="display:none"{/if}>DN No: <input name=listing_fee_dn2 size=5 value="{$form.listing_fee_dn}"></span>
	{/if}
	No. of SKU <input name=listing_fee_package_count size=5 value="{$form.listing_fee_package_count}" {$package_readonly}>
	{if $package_readonly}REF#<input name=listing_fee_package_first_sku_id size=3 value="{$form.listing_fee_package_first_sku_id}" readonly>{/if}
	</div>
 	<!-- package variety -->
	<div id=lst_package2 style="display:none;padding:2px 0px;">
	Package Amount ({$config.arms_currency.symbol}) <input name=listing_fee_package_amount2 size=5 value="{$form.listing_fee_package_amount}" {$package_readonly}>
	{if $package_readonly}
	<input name=listing_fee_when3 value="{$form.listing_fee_when}" readonly>
	{else}
	<select name=listing_fee_when3 onchange="$('upon_dn3').style.display = (this.value == 'Upon SKU Application' ? '' : 'none')">
	<option {if $form.listing_fee_when eq 'Upon SKU Application'}selected{/if}>Upon SKU Application</option>
	<option {if $form.listing_fee_when eq 'Upon PO'}selected{/if}>Upon PO</option>
	<option {if $form.listing_fee_when eq 'Upon Goods Received'}selected{/if}>Upon Goods Received</option>
	</select>
	<span id=upon_dn3 {if $form.listing_fee_when ne '' and $form.listing_fee_when ne 'Upon SKU Application'}style="display:none"{/if}>DN No: <input name=listing_fee_dn3 size=5 value="{$form.listing_fee_dn}"></span>
	{/if}
	No. of Variety <input name=listing_fee_package_count2 size=5 value="{$form.listing_fee_package_count}" {$package_readonly}>
	</div>
	<!-- inkind -->
	<ul id=in_kind style="display:none; padding:0;margin:0;"><br>
	{assign var=n value=0}
	{foreach name=f from=$form.listing_fee_inkind.item item=ft}
	{if $ft ne ''}
	<li> Item <input name="listing_fee_inkind[item][{$n}]" size=30 value="{$form.listing_fee_inkind.item[$smarty.foreach.f.index]}">
	Qty <input name="listing_fee_inkind[qty][{$n}]" size=5 value="{$form.listing_fee_inkind.qty[$smarty.foreach.f.index]}">
	Unit Cost <input name="listing_fee_inkind[cost][{$n}]" size=6 value="{$form.listing_fee_inkind.cost[$smarty.foreach.f.index]}" onchange="ctotal({$n})">
	Total Cost <input name="listing_fee_inkind[total_cost][{$n}]" size=6 value="{$form.listing_fee_inkind.total_cost[$smarty.foreach.f.index]}"  onchange="cunit({$n})"></li>
	<!-- {$n++} -->
	{/if}
	{/foreach}
	<li> Item <input name="listing_fee_inkind[item][{$n}]" size=30 onblur="add_inkind(this,{$n+1})">
	Qty <input name="listing_fee_inkind[qty][{$n}]" size=5>
	Unit Cost <input name="listing_fee_inkind[cost][{$n}]" size=6 onchange="ctotal({$n})">
	Total Cost <input name="listing_fee_inkind[total_cost][{$n}]" size=6 onchange="cunit({$n})"></li>
	</ul></td>
</tr>
<tr>
	<td><b class="form-label mt-2">Remark</b></td>
	<td><input class="form-control" name=remark size=50 value="New Application" readonly></td>
</tr>
<tr>
	<td><b class="form-label mt-2">Note</b></td>
	<td><textarea class="form-control" name=note rows=2 cols=40>{$form.note}</textarea></td>
</tr>
{if $config.sku_application_require_multics && $last_approval}
<tr>
	<td><b>{$config.link_code_name}</b></td>
	<td>
		<table class=body>
		<tr>
			<td>Department</td>
			<td>
			<input type="text" id="multics_dept" name="multics_dept" value="{$form.multics_dept}"/>
			<div id="multics_dept_choices" class="autocomplete"></div>
			</td>
			<td>Section</td>
			<td>
			<input type="text" id="multics_section" name="multics_section" value="{$form.multics_section}"/>
			<div id="multics_section_choices" class="autocomplete"></div>
			</td>
			<td>Category</td>
			<td>
			<input type="text" id="multics_category" name="multics_category" value="{$form.multics_category}"/>
			<div id="multics_category_choices" class="autocomplete"></div>
			</td>
		</tr>
		<tr>
			<b class="form-label">Brand</b>
		
			<input class="form-control" type="text" id="multics_brand" name="multics_brand" value="{$form.multics_brand}"/>
			<div id="multics_brand_choices" class="autocomplete"></div>
			
			<b class="form-label">Price Type</b>
			<select class="form-control" name=multics_pricetype>
			<option value="">Please Select</option>
			<option value="N1" {if $form.multics_pricetype eq "N1"}selected{/if}>N1</option>
			<option value="N2" {if $form.multics_pricetype eq "N2"}selected{/if}>N2</option>
			<option value="N3" {if $form.multics_pricetype eq "N3"}selected{/if}>N3</option>
			<option value="B1" {if $form.multics_pricetype eq "B1"}selected{/if}>B1</option>
			<option value="B2" {if $form.multics_pricetype eq "B2"}selected{/if}>B2</option>
			<option value="B3" {if $form.multics_pricetype eq "B3"}selected{/if}>B3</option>
			<option value="B5" {if $form.multics_pricetype eq "B5"}selected{/if}>B5</option>
			<option value="B6" {if $form.multics_pricetype eq "B6"}selected{/if}>B6</option>
			<option value="B7" {if $form.multics_pricetype eq "B7"}selected{/if}>B7</option>
			<option value="B8" {if $form.multics_pricetype eq "B8"}selected{/if}>B8</option>
			<option value="B9" {if $form.multics_pricetype eq "B9"}selected{/if}>B9</option>
			</select>
		
		</tr>
		</table>
	</td>
</tr>
{/if}
{if $config.enable_sn_bn}
	<tr>
		<td><b class="form-label">Use Serial No</b></td>
		<td>
			<select class="form-control" name="have_sn">
				<option value="0" {if $form.have_sn eq '0'}selected {/if}>No</option>
				<option value="1" {if $form.have_sn eq '1'}selected {/if}>Yes (Pre-list)</option>
				<option value="2" {if $form.have_sn eq '2'}selected {/if}>Yes</option>
			</select>
		</td>
	</tr>
{/if}
<tr >
	<div class="form-inline">
		<b class="form-label mt-2">PO Reorder Qty</b>
	&nbsp;&nbsp;<input type="checkbox" name="po_reorder_by_child" value="1" {if $form.po_reorder_by_child}checked{/if} onclick="toggle_po_reorder_by_child();" /> <b class="form-label mt-2">&nbsp;&nbsp;By Child</b>
	
	</div>
	<td>
		
		<div id="div_po_reorder_qty">
			Min: <input class="form-control" type="text" size="3" name="po_reorder_qty_min" value="{$form.po_reorder_qty_min}" />
			
			Max: <input class="form-control" type="text" size="3" name="po_reorder_qty_max" value="{$form.po_reorder_qty_max}" />
		
			MOQ<a href="javascript:void(alert('Minimum Order Quantity'))"><img src="/ui/icons/information.png" align="absmiddle" /></a> : 
			<input type="text" class="form-control" size="3" name="po_reorder_moq" value="{$form.po_reorder_moq}" />
		
			Notify Person 
			<select class="form-control" name="po_reorder_notify_user_id">
				<option value="" {if !$form.po_reorder_notify_user_id}selected{/if}>--</option>
				{foreach from=$po_reorder_users key=row item=r}
					<option value="{$r.id}" {if $form.po_reorder_notify_user_id eq $r.id}selected{/if}>{$r.u}</option>
				{/foreach}
			</select>
			{if !$config.consignment_modules}
				<span id="qty_setup">
					<input type="checkbox" name="po_reorder_qty_setup" value="1" {if $form.po_reorder_qty_setup}checked{/if} onclick="toggle_prq_by_branch(this);" /> Overwrite PO Reorder qty by Branch
				</span>
				<br />
				<br />
				<div class="div_multi_select" id="prq_by_branch" {if !$form.po_reorder_qty_setup}style="display:none;"{/if}>
					<table width="100%">
						{foreach from=$branch_list key=bid item=b}
							<tr>
								<td width="5%"><b>{$b.code}</b></td>
								<td width="1%">Min: </td>
								<td width="3%">
									<input class="form-control" type="text" size="3" name="po_reorder_qty_by_branch[min][{$bid}]" class="r" value="{$form.po_reorder_qty_by_branch.min.$bid}" />
								</td>
								<td width="1%">Max: </td>
								<td width="10%">
									<input class="form-control" type="text" size="3" name="po_reorder_qty_by_branch[max][{$bid}]" class="r" value="{$form.po_reorder_qty_by_branch.max.$bid}" />
								</td>
								<td width="1%">MOQ: </td>
								<td width="10%">
									<input class="form-control" type="text" size="3" name="po_reorder_qty_by_branch[moq][{$bid}]" class="r" value="{$form.po_reorder_qty_by_branch.moq.$bid}" />
								</td>
								<td width="10%">
									<select class="form-control" name="po_reorder_qty_by_branch[notify_user_id][{$bid}]">
										<option value="" {if !$form.po_reorder_qty_by_branch.notify_user_id.$bid}selected{/if}>--</option>
										{foreach from=$po_reorder_users key=row item=r}
											<option value="{$r.id}" {if $form.po_reorder_qty_by_branch.notify_user_id.$bid eq $r.id}selected{/if}>{$r.u}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
			{/if}
		</div>
	</td>
</tr>

{if $config.sku_non_returnable}
	<tr valign="top">
		<td nowrap><b class="form-label">Non-returnable</b> <a href="javascript:void(alert('Turn on this will not allow this group of SKU to return at GRA'))"><img src="/ui/icons/information.png" align="absmiddle" /></a></td>
		<td>
			<select class="form-control" name="group_non_returnable">
				<option value="1" {if $form.group_non_returnable eq 1}selected {/if}>Yes</option>
				<option value="0" {if $form.group_non_returnable eq 0}selected {/if}>No</option>
			</select>
		</td>
	</tr>
{/if}

<tr class="gst_settings">
	<td><b class="form-label mt-2">Input Tax</b></td>
	<td>
		<select class="form-control" name="mst_input_tax" onchange="mst_gst_info_changed();">
			<option value="-1" {if $form.mst_input_tax eq -1}selected{/if}>Inherit (Follow Category)</option>
			{foreach from=$input_tax_list key=rid item=r}
				<option value="{$r.id}" {if $form.mst_input_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>

<tr class="gst_settings">
	<td><b class="form-label mt-2">Output Tax</b></td>
	<td>
		<select class="form-control" name="mst_output_tax" onchange="calculate_all_gst();">
			<option value="-1" {if $form.mst_output_tax eq -1}selected{/if}>Inherit (Follow Category)</option>
			{foreach from=$output_tax_list key=rid item=r}
				<option data-rate="{$r.rate}" value="{$r.id}" {if $form.mst_output_tax eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}

		</select>
	</td>
</tr>

<tr class="tr_inclusive_tax" style="{if !$gst_settings or ($global_gst_settings.inclusive_tax eq 'yes' and (!isset($form.mst_inclusive_tax) or $form.mst_inclusive_tax eq 'inherit'))}display:none;{/if}">
	<td><b class="form-label mt-2">Selling Price Inclusive Tax</b></td>
	<td>
		<select class="form-control" name="mst_inclusive_tax" onchange="calculate_all_gst();">
			<option value="inherit" {if $form.mst_inclusive_tax eq "inherit"}selected {/if}>Inherit (Follow Category)</option>
			<option value="yes" {if $form.mst_inclusive_tax eq "yes"}selected {/if}>Yes</option>
			<option value="no" {if $form.mst_inclusive_tax eq "no"}selected {/if}>No</option>
		</select>
	</td>
</tr>
<tr valign="top">
    <td nowrap><b class="form-label mt-2">Allow Parent and Child duplicate MCode</b></td>
    <td>
        <select class="form-control " name="parent_child_duplicate_mcode">
            <option value="1" {if $form.parent_child_duplicate_mcode eq 1}selected {/if}>Yes</option>
            <option value="0" {if $form.parent_child_duplicate_mcode eq 0}selected {/if}>No</option>
        </select>
    </td>
</tr>

{if $config.enable_one_color_matrix_ibt}
	<tr>
		<td><b class="form-label mt-2">Use Matrix Settings</b></td>
		<td>
			<select class="form-control" name="use_matrix" onchange="matrix_changed();">
				{foreach from=$inherit_options key=k item=val}
					<option value="{$k}" {if ($matrix.use_matrix eq $k) || (!$matrix.use_matrix and $k eq 'no')}selected {/if}>{$val}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			{include file='masterfile_sku.ibt_matrix.tpl' is_edit=1 use_matrix=$matrix.use_matrix sku_matrix=$matrix.matrix}
		</td>
	</tr>
{/if}

</table>

</div>
</div>
<br>

<div id="new_items">
{include file="masterfile_sku_application.items.tpl"}
</div>

{if !$items || $config.sku_application_enable_variety}
<div>
<a name="additem"> </a>
<div class="card mx-3">
	<div class="card-body">
		<span id="add_item">
			<div class="row">
				<div class="col-md-6">
					<ul class="list-group">
						<li class="list-group-item list-group-item-action ">
							<span id="add_variety">
								<a id="add_new_sku" href="javascript:void(add_variety())"><img src=ui/new.png title="New" align=absmiddle border=0> Add New SKU</a> &nbsp; &nbsp;
							</span>
						</li>
					
				</div>
				<div class="col-md-6">
					<li class="list-group-item list-group-item-action">
						<span id="add_matrix">
							{if !$config.sku_disable_add_matrix}
								<a href="javascript:void(add_matrix())"><img src=ui/table_add.png title="New" align=absmiddle border=0> Add New Matrix</a>
							{/if}
							</span>
					</li>
			</ul>
				</div>
			</div>
		</span>
	</div>
<span id="add_item_notify"></span>
</div>
{/if}
<p align=center>
{if $package_readonly}
<input type="button" class="btn btn-primary" value="End Package Listing" class="btn btn-warning" onclick="document.location='{$smarty.server.PHP_SELF}?a=skip_listing';">
	{if $sessioninfo.privilege.MST_TERMINATE_PACKAGE}
	<input type="button" value="Terminate Package Listing" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="terminate_package_clicked();">
	{/if}
{/if}
<input class="btn btn-primary" id=submitbtn name=bsubmit type=submit value="Submit Application">
<br /><br />
<span id="span_sheet_processing" style="padding:2px;background:yellow;display:none;">Loading...</span>
</p>

</form>
{include file=footer.tpl}



<script>
sel_fee(document.f_a.listing_fee_type.value,0);
{if !$config.sku_always_show_trade_discount}
sel_brand();
sel_vendor();
{/if}

{literal}
toggle_gst_settings();
check_allow_matrix();
if (total_item<0) $('submitbtn').style.display='none';
init_click_hilite(document.f_a);

var category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category", {
	afterUpdateElement: function (obj,li)
	{
		this.defaultParams = '';
		var s = li.title.split(",");
		document.f_a.category_id.value = s[0];
		sel_category(obj,s[1]);
		if(config_enable_fresh_market_sku) check_fm_type();
		$('sku_category_id').onchange();
		if(!config_sku_always_show_trade_discount) sel_department();
		
		if(is_gst_active){
			load_category_GST(s[0]);
		}
	}});

new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand", { afterUpdateElement: function (obj, li) { document.f_a.brand_id.value = li.title; sel_brand(); }});

new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; sel_vendor();$('sku_vendor_id').onchange(); }});

{/literal}
{if $config.sku_application_require_multics && $last_approval}
{literal}
new Ajax.Autocompleter("multics_dept", "multics_dept_choices", "multics_autocomplete.php", {paramName: "dept", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_section", "multics_section_choices", "multics_autocomplete.php", {paramName: "sect", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_category", "multics_category_choices", "multics_autocomplete.php", {paramName: "cat", afterUpdateElement: function (obj, li) { obj.value = li.title }});

new Ajax.Autocompleter("multics_brand", "multics_brand_choices", "multics_autocomplete.php", {paramName: "brand", afterUpdateElement: function (obj, li) { obj.value = li.title }});
{/literal}
{/if}
{literal}
new_open = false;
toggle_po_reorder_by_child();

if(is_gst_active){
	setTimeout(function(){
		load_category_GST(document.f_a['category_id'].value);
	}, 500);
}

</script>
{/literal}
{if $config.ci_auto_gen_artno}
	<script type="text/javascript" src="{$config.ci_auto_gen_artno.js_path}"></script>
{/if}
