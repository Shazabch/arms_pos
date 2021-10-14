{include file=header.tpl}
{*
 11/3/2009 5:39:11 PM Andy
 - Add Rejected Tab, add revert & cancel function
 
 11/23/2009 4:49:35 PM Andy
 - add sales trend information
 
6/22/2011 11:03:38 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

6/24/2011 10:52:44 AM Andy
- Add loading indicator when sku autocomplete is loading.

10/13/2011 11:35:46 AM Justin
- Modified the Ctn and Pcs round up to base on config set.
- Added new function to change the qty and stock balance fields to enable/disable decimal points key in base on sku item's doc_allow_decimal.

11/25/2011 12:57:53 PM Andy
- Add show Ctn#1 and Ctn#2 at DO Request if found config.do_request_show_ctn_1_2

6/13/2012 11:39:23 AM Justin
 - Added to show only HQ selection while logged on branch was a franchise.
 
12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".

12/12/2012 2:32 PM Andy
- Add auto fill in expect delivery date if found got config "do_request_default_expected_delivery_date_extend_day".
- Change expect delivery date not to clear if got config "do_request_default_expected_delivery_date_extend_day".
- Add checking to config "do_request_block_same_item_same_expect_delivery_date" and not allow user to choose the date over the maximum date.

12/24/2012 11:45 AM Andy
- DO Request can add by sku group.

1/17/2013 5:02 PM Justin
- Enhanced to show "add DO Request by SKU Group" option when found customer using vendor portal.

2/19/2013 11:45 AM Justin
- Modified the qty round up to base on config set.

3/5/2013 12:48 PM Justin
- Enhanced to have validation for expected delivery date that disallow user to choose previous date base on config set.

3/5/2013 4:29 PM Andy
- Fix sorting cannot be maintain after delete item.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

03/24/2016 09:30 Edwin
- Modified the label from "Request from Branch" to "Choose Supply Branch"

7/6/2017 12:07 PM Andy
- Enhanced to able to highlight DO Request Item by SKU_ITEM_ID.

3/21/2019 3:10 PM Andy
- Enhanced to show DO Request SKU Photo.
- Added "Advanced Add" feature.

5/28/2019 2:46 PM Justin
- Enhanced to have remove items by selection.

04/22/2020 11:52 AM Sheila
- Modified layout to compatible with new UI.

12/14/2020 1:24 PM Rayleen
- Add function to view/hide other lines after click 'Show More/Less' link

12/22/2020 5:46 PM Rayleen
- Add function to view/hide other SKU details after click 'Show More Info/Hide Info' link
*}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#tbody_item_list tr:hover{
	background-color:#ffa;
}
td.editable{
	background-color:yellow;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var do_request_show_ctn_1_2 = int('{$config.do_request_show_ctn_1_2}');
var do_request_no_expected_delivery_date = int('{$config.do_request_no_expected_delivery_date}');
var do_request_default_expected_delivery_date_extend_day = int('{$config.do_request_default_expected_delivery_date_extend_day}');
var do_request_maximum_expect_delivery_date_day = int('{$config.do_request_maximum_expect_delivery_date_day}');
var do_request_expected_delivery_date_times = '{$do_request_expected_delivery_date_times}';
var do_request_expected_delivery_date_days = '{$do_request_expected_delivery_date_days}';
var highlight_sku_item_id = '{$smarty.request.highlight_sku_item_id}';
var curr_branch_id = '{$sessioninfo.branch_id}';

{literal}
var sku_autocomplete = undefined;
var is_escape = false;
var last_obj;
var search_str = '';
var allowed_max_expect_do_date;
var allowed_min_expect_do_date;
var default_sort_by = 1;
var default_sort_order = 'asc';

if(do_request_maximum_expect_delivery_date_day>0){
	allowed_max_expect_do_date = new Date();
	allowed_max_expect_do_date.setTime(allowed_max_expect_do_date.getTime() + 3600000*24*do_request_maximum_expect_delivery_date_day);
}

if(do_request_expected_delivery_date_times && do_request_expected_delivery_date_days){
	var curr_date = new Date();
	var curr_year = curr_date.getFullYear();
	var curr_mth = curr_date.getMonth()+1;
	var curr_day = curr_date.getDate();
	var edd_time_splt = do_request_expected_delivery_date_times.split(":");
	var expired_expected_delivery_date = new Date(curr_year, curr_mth-1, curr_day, edd_time_splt[0], edd_time_splt[1], 0);

	//expired_expected_delivery_date.setTime(expired_expected_delivery_date.getTime() + 3600000 * do_request_expected_delivery_date_times);
	if(curr_date.getTime() > expired_expected_delivery_date.getTime()){
		allowed_min_expect_do_date = new Date(curr_year, curr_mth-1, int(curr_day)+int(do_request_expected_delivery_date_days), 0, 0, 0);
	}else{
		allowed_min_expect_do_date = new Date(curr_year, curr_mth-1, curr_day+1, 0, 0, 0);
	}
}
		
function reset_sku_autocomplete()
{
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
	  
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		indicator: 'span_autocomplete_loading',
    afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
		    
			document.f_a['sku_item_id'].value =s[0];
			document.f_a['sku_item_code'].value = s[1];

			var doc_allow_decimal = document.f_a.elements['inp_dad,'+s[0]].value;
			if(doc_allow_decimal == 1){
				document.f_a.request_qty.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };

				document.f_a.stock_balance.onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
			}else{
				document.f_a.request_qty.onchange = function(){ mi(this); };
				document.f_a.stock_balance.onchange = function(){ mi(this); };
			}

			if (s[0]>0)
			{
				$('autocomplete_sku').disabled = true;
				$('span_loading_item_info').update(_loading_);
				new Ajax.Request(phpself+'?a=ajax_get_item_info&ajax=1&sku_item_id='+s[0],
					{
						onComplete:function(msg){
							eval("var json="+msg.responseText);
						    document.f_a['stock_balance'].value = float(round(json['sb']['qty'], global_qty_decimal_points));
						    $('div_item_details').update(json['item_details']);
							$('autocomplete_sku').disabled = false;
							$('span_loading_item_info').update('');
						}
					});
				document.f_a['request_qty'].focus();
			}
			else{
				clear_autocomplete();
			}
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete()
{
	document.f_a['sku_item_id'].value = '';
	document.f_a['sku_item_code'].value = '';
	document.f_a['request_qty'].value = '';
	document.f_a['stock_balance'].value = '';
	//document.f_a['uom_id'].value = 1;
	document.f_a['comment'].value = '';
	
	if(!do_request_no_expected_delivery_date){
		if(document.f_a['inp_expect_do_date']){
			if(!do_request_default_expected_delivery_date_extend_day){
				document.f_a['inp_expect_do_date'].value = '';
			}
		}
	}
	
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}

function list_sel(selected){
	if(selected==4){
		var tmp_search_str = $('inp_item_search').value.trim();
		
		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
	}
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}	
	
	var all_tab = $$('.tab .a_tab');
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');
	
	if($('sort_by') != undefined){
		var sort_by = $('sort_by').value;
		var sort_order = $('sort_order').value;
		
		default_sort_by = sort_by;
		default_sort_order = sort_order;
	}else{
		var sort_by = default_sort_by; // mark to sort by "Last Update"
		var sort_order = default_sort_order;
	}
	
	var params = {
		search_str: search_str,
		sort_by: sort_by,
		sort_order: sort_order
	}
	if(highlight_sku_item_id){
		params['highlight_sku_item_id'] = highlight_sku_item_id;
		highlight_sku_item_id = '';
	}
	
	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters: params,
		onComplete: function(msg){
			
		},
		evalScripts: true
	});
}

function add_item(ele){
	if(document.f_a['request_branch_id'].value==''){
		alert('Please select request branch.');
		document.f_a['request_branch_id'].focus();
		return;
	}
	if(document.f_a['sku_item_id'].value==''){
		alert('Please search and select an item.');
		return;
	}
	if(document.f_a['request_qty'].value==''||document.f_a['request_qty'].value<=0){
		alert('Please key in request qty.');
		document.f_a['request_qty'].select();
		return;
	}
	
	ele.disabled = true;
	$('span_adding_item').update(_loading_);
	
	new Ajax.Request(phpself+'?a=ajax_add_item&ajax=1',{
			parameters: document.f_a.serialize(),
			onComplete: function(msg){
				if(msg.responseText=='OK'){
					if(tab_num==1)	list_sel(1);
					clear_autocomplete();
				}else{
					alert(msg.responseText);
				}
				ele.disabled = false;
				$('span_adding_item').update('');
			}
	});
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function remove_item(id,branch_id){
	if(!confirm('Click OK to delete')){
		return;
	}
	//var parent_tr = $(ele).parentNode.parentNode;
	//$(parent_tr).setStyle({'color':'grey'})
	
	var remove_id_list = [];
	if(!id){ // means it was rejected for more than one selection
		// get all input element
		var all_chx = $$('#items_tbl input.inp_item');
		
		if(all_chx.length<=0)	return;
		
		// disable all input to prevent user edit
		for(var i=0; i<all_chx.length; i++){
			if($(all_chx[i]).checked == true) remove_id_list.push($(all_chx[i]).value);
		}
	}else remove_id_list.push(id); // only one being rejected
	
	if(remove_id_list.length == 0){
		alert("No item were selected for remove.");
		return;
	}
	
	$('items_list').update(_loading_);
	new Ajax.Request(phpself+'?a=ajax_remove_item&ajax=1',{
		parameters:{
			'item_id_list[]': remove_id_list,
			branch_id: branch_id,
			curr_branch_id: curr_branch_id
		},
		onComplete: function(msg){
			if(msg.responseText!='OK')	alert(msg.responseText);			
			list_sel();
		}
	});
}

function do_edit(obj){
	is_escape = false;
	last_obj = obj;
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	$('edit_popup').show();
	$('edit_text').select();
	$('edit_text').focus();
}

function checkKey(event){
    if (event == undefined) event = window.event;
	
	if(event.keyCode==13){  // enter
		save();
	}else if(event.keyCode==27){    // escape
	    remove_focus();
	}
	event.stopPropagation();
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(4);
	}
}

function remove_focus(){
	is_escape = true;
    document.f_a.sku_item_id.focus();
}

function save(){
	var old_value = int(last_obj.innerHTML);
	var new_value = int($('edit_text').value);
	
	if(new_value<=0){
		//alert('Invalid Qty');
		$('edit_popup').hide();
		remove_focus();
		return;
	}
	
	$('edit_popup').hide();
	
    
	if(is_escape)   return;
	
	if(old_value!=new_value){
	    var data = last_obj.id.split(',');
	    var data_type = data[0];
	    var id = data[1];
		var branch_id = data[2];
		

		last_obj.update('Saving..');
		var obj = last_obj;
		new Ajax.Request(phpself+'?a=ajax_update_request_qty&ajax=1',{
			method: 'post',
			parameters:{
				id: id,
				branch_id: branch_id,
				qty: new_value
			},
			onComplete: function(msg){
				if(msg.responseText=='OK'){
					$(obj).update(new_value);
				}else{
					alert(msg.responseText);
					$(obj).update(old_value);
				}
				
				// update ctn 1 and ctn 2 Qty
				if(do_request_show_ctn_1_2)	update_ctn1_ctn2(branch_id, id);
			}
		});
	}
	
	remove_focus();
}

function cancel_item(id,branch_id){
  if(!confirm('Are you sure?')) return false;
  
  $('items_list').update(_loading_);
	new Ajax.Request(phpself+'?a=ajax_cancel_item&ajax=1',{
		parameters:{
			id: id,
			branch_id: branch_id
		},
		onComplete: function(msg){
			if(msg.responseText!='OK')	alert(msg.responseText);			
			list_sel();
		}
	});
}

function revert_item(id,branch_id, current_qty){
  var new_qty = prompt("Please enter new request qty", current_qty);
  new_qty = int(new_qty);
  new_qty = float(round(new_qty, global_qty_decimal_points));
  if(new_qty<=0)  return;
  
  if(!confirm('Are you sure to request this item again? (New Request Qty: '+new_qty+')')) return false;
  
  $('items_list').update(_loading_);
	new Ajax.Request(phpself+'?a=ajax_revert_item&ajax=1',{
		parameters:{
			id: id,
			branch_id: branch_id,
			new_qty: new_qty
		},
		onComplete: function(msg){
			if(msg.responseText!='OK')	alert(msg.responseText);			
			list_sel();
		}
	});
}

function update_ctn1_ctn2(branch_id, id){
	var request_qty = float($('request_qty,'+id+','+branch_id).innerHTML);
	var packing_uom_fraction = float($('inp_packing_uom_fraction-'+branch_id+'-'+id).value);
	var ctn_1_fraction = float($('inp_ctn_1_fraction-'+branch_id+'-'+id).value);
	var ctn_2_fraction = float($('inp_ctn_2_fraction-'+branch_id+'-'+id).value);
	
	// ctn 1
	if(ctn_1_fraction>1 && $('span_ctn_1_qty-'+branch_id+'-'+id)){
		$('span_ctn_1_qty-'+branch_id+'-'+id).update('('+float(round(request_qty*packing_uom_fraction/ctn_1_fraction, global_qty_decimal_points))+')');
	}
	
	// ctn 2
	if(ctn_2_fraction>1 && $('span_ctn_2_qty-'+branch_id+'-'+id)){
		$('span_ctn_2_qty-'+branch_id+'-'+id).update('('+float(round(request_qty*packing_uom_fraction/ctn_2_fraction, global_qty_decimal_points))+')');
	}
}

function init_calendar(){
	// expected delivery date
	if(!do_request_no_expected_delivery_date){
		Calendar.setup({
			inputField     :    "inp_expect_do_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_expect_do_date",
			align          :    "Bl",
			singleClick    :    true,
			dateStatusFunc :    function (date) { // disable those date <= today
								if(allowed_max_expect_do_date || allowed_min_expect_do_date){
									var val = false;
									if((date.getTime() > allowed_max_expect_do_date.getTime())){
										val = true;
									}

									if(allowed_min_expect_do_date && date.getTime() < allowed_min_expect_do_date.getTime()){
										val = true;
									}
									return val;
								}
								return false;	// always allow
                            }
		});
	}
}

function expect_do_date_changed(){
	var v = document.f_a['expect_do_date'].value.trim();
	if(!v)	return false;
	
	var tmp = v.split('-');
	var y = int(tmp[0]);
	var m = int(tmp[1]);
	var d = int(tmp[2]);
	
	if(y>2000 && m >=1 && m <=12 && d>=1 && d<=31){
		var d = new Date(y, m-1, d);
		if(!(d.getTime() < allowed_max_expect_do_date.getTime())){
			alert('Expected Delivery Date cannot over '+allowed_max_expect_do_date.getFullYear()+'-'+(allowed_max_expect_do_date.getMonth()+1)+'-'+allowed_max_expect_do_date.getDate());
			document.f_a['expect_do_date'].value = '';
			return false;
		}
		
		if(allowed_min_expect_do_date && !(d.getTime() >= allowed_min_expect_do_date.getTime())){
			alert('Expected Delivery Date cannot earlier than '+allowed_min_expect_do_date.getFullYear()+'-'+(allowed_min_expect_do_date.getMonth()+1)+'-'+allowed_min_expect_do_date.getDate());
			document.f_a['expect_do_date'].value = '';
			return false;
		}
	}
}

function toggle_all_item_selected(ele){
	var c = ele.checked;
	if(c) var s = 1;
	else var s =0;
	
	var id_list = [];
	
	// get all input element
	var all_chx = $$('#items_tbl input.inp_item');
	
	if(all_chx.length<=0)	return;
	
	// disable all input to prevent user edit
	for(var i=0; i<all_chx.length; i++){
		if(ele.checked == true) $(all_chx[i]).checked = true;
		else $(all_chx[i]).checked = false;
	}
}

function show_more_description(){
	var is_show = $('show_more_line').value;
	if(is_show==0){
		$$('.adesc').each(
            function(s) {
                s.hide();
            }
        );
		$('show_more').update('Show More');
		var is_show = $('show_more_line').value = 1;
	}else{
		$$('.adesc').each(
            function(s) {
                s.show();
            }
        );
		$('show_more').update('Show Less');
		var is_show = $('show_more_line').value = 0;
	}
}

function show_more_info(){
	var is_show = $('show_more_item_info').value;
	if(is_show==0){
		$('other_info').hide();
		$('show_more_info').update('Show More Info');
		var is_show = $('show_more_item_info').value = 1;
	}else{
		$('other_info').show();
		$('show_more_info').update('Hide Info');
		var is_show = $('show_more_item_info').value = 0;
	}
}
{/literal}
</script>

{include file='shared_sku_photo.script.tpl'}

<!-- Special Div -->
<div id="edit_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id="edit_text" size=5 onblur="save()" onKeyPress="checkKey(event)" style="text-align:right;">
</div>

<!-- End of Special Div-->
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="alert alert-primary mx-3">
	<ul>
		<li> You can only search the products under your allowed departments.</li>
		<li> Items added to Request are auto-saved.</li>
		{if $config.enable_vendor_portal}
			<li> You can also <a href="?a=add_by_sku_group_main" target="_blank">add DO Request by SKU Group</a>.</li>
		{/if}
	</ul>
</div>

<div style="text-align:right;"><a href="?a=advanced_add_main">Advanced Search &gt;&gt;</a></div>
<div class="card mx-3">
	<div class="card-body">
		<div id=tbl_1 class=stdframe >
			<!-- sku search -->
			<form name="f_a" onSubmit="return false;">
			<input name="sku_item_id" size=3 type=hidden>
			<input name="sku_item_code" size=13 type=hidden>
			{*<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />*}
			
			<b class="form-label">Choose Supply Branch<span class="text-danger"> *</span></b>
			<select class="form-control" name="request_branch_id">
				<option value="">-- Please Select --</option>
				{foreach from=$branch item=b}
					{if $b.code ne $BRANCH_CODE && ($sessioninfo.branch_type ne "franchise" || $sessioninfo.branch_type eq "franchise" && $b.id eq 1)}
					<option value="{$b.id}" {if $b.code eq 'HQ'}selected {/if}>{$b.code} - {$b.description}</option>
					{/if}
				{/foreach}
			</select>
			
			<table class="tl" >
			<tr><b class="form-label mt-2">Search SKU<span class="text-danger" title="required field"> *</span></b>
			<input class="form-control" id="autocomplete_sku" name="sku" size=50 onclick="this.select();" style="font-size:14px;width:500px;"> <span></span>
			<span id="span_loading_item_info"></span>
			<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div></td></tr>
			<tr><td>&nbsp;</td><td colspan=6>
			<div class="fs-08">
				<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
			</div>
			</td></tr>
			</table>
			
			<div id="div_item_details"></div>
			<br>
		<div class="row">
			<div class="col-md-3">
				<b class="form-label">Request Qty<span class="text-danger" title="Required Field"> *</span></b>
			 <input class="form-control" type="text" name="request_qty" size="3" onchange="mi(this);" /> <span>
		
			</div>
			{*<div class="col-md-3">
				<b class="form-label">UOM</b>
			<select class="form-control" name="uom_id">
				{foreach from=$uom item="curr_uom"}
				<option value={$curr_uom.id} {if $curr_uom.code eq 'EACH'}selected {/if}>{$curr_uom.code}
				</option>
				{/foreach}
			</select>
			</div>*}
			<div class="col-md-3">
				<b class="form-label">Stock Balance</b>
				 <input class="form-control" size=3 name="stock_balance" onchange="mi(this);">
			</div>
			
			<div class="col-md-3">
				<b class="form-label">Remarks</b> 
				<input class="form-control"  size=32 maxlength="30" name="comment">
			</div>
			
			{if !$config.do_request_no_expected_delivery_date}
				<div class="col-md-3">
					<b class="form-label">Expected Delivery Date</b>
				<div class="form-inline">
					<input class="form-control" name="expect_do_date" id="inp_expect_do_date" size="12" maxlength="10" value="{$default_expect_min_do_date|default:$default_expect_do_date}" onChange="expect_do_date_changed();" />
				&nbsp;&nbsp;&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date" style="cursor: pointer;" title="Select Date" />
				</div>
				</div>
			{/if}
		</div>
			
			<p>
				<input class="btn btn-primary mt-2" type="button" value="Add to DO Request" onClick="add_item(this);" />
				<span id="span_adding_item"></span>
			</p>
			</form>
			</div>
	</div>
</div>
<br />


<div class=tab style="white-space:nowrap;">
		<div class="row mx-3 mb-3">
		<div class="col">
			<a href="javascript:void(list_sel(1))" id=lst1 class="a_tab btn btn-outline-primary btn-rounded">Saved Items</a>
		<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab btn btn-outline-primary btn-rounded">Processing</a>
		<a href="javascript:void(list_sel(5))" id=lst5 class="a_tab btn btn-outline-primary btn-rounded">Rejected</a>
		<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab btn btn-outline-primary btn-rounded">Completed</a>
		
		</div>
		<div class="col">
			<a class="a_tab" id=lst4> 
				<div class="form-inline">
					<b class="form-label">Find Items </b>
				&nbsp;&nbsp;<input class="form-control" id="inp_item_search" onKeyPress="search_input_keypress(event);" /> 
				&nbsp;&nbsp;<input type="button" class="btn btn-primary" value="Go" onClick="list_sel(4);" />
				</div>
			</a>
		</div>
		
</div>
</div>
<div id="items_list" >
</div>

<script type="text/javascript">
reset_sku_autocomplete();
init_calendar();
list_sel();
</script>
{include file=footer.tpl}

