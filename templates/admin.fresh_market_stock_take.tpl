{*
9/1/2010 6:02:21 PM Andy
- Add can direct add stock take item under selected list.
- Add "Multiple Add".
- Clone selected branch, date, location and shelf to "Add New Stock".

9/14/2010 2:00:50 PM Andy
- Add print stock count sheet when have config.

9/27/2011 12:55:45 PM Justin
- Modified the Ctn and Pcs round up to base on config set.
- Added new function to change the qty field to enable/disable decimal points key in base on sku item's doc_allow_decimal.

10/11/2011 6:15:00 PM Alex
- add qty checking in javascript

05/07/2020 5:34 PM Sheila
- Fixed table height

*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.sel_1{
	min-width:100px;
}
.calendar, .calendar table {
	z-index:100000;
}
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var stock_take_count_sheet = '{$config.stock_take_count_sheet}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	$('span_date_loading').update(_loading_);
	$('sel_date').update('');
	$('sel_loc').update('');
	$('sel_shelf').update('');
	$('inp_reload_stock_take_list').disabled = true;
	reset_range_box(true, true);
	new Ajax.Updater('sel_date', phpself+'?a=load_date_list&branch_id='+bid,{
		onComplete: function(e){
			$('span_date_loading').update('');
		}
	});
}

function date_changed(){
    var bid = document.f_a['branch_id'].value;
    var date = document.f_a['date'].value;
    $('span_loc_loading').update(_loading_);
    
    $('sel_loc').update('');
	$('sel_shelf').update('');
	$('inp_reload_stock_take_list').disabled = true;
	new Ajax.Updater('sel_loc', phpself+'?a=load_location_list&branch_id='+bid+'&date='+date,{
		onComplete: function(e){
			$('span_loc_loading').update('');
			$('sel_loc_from').update(e.responseText);
			$('sel_loc_to').update(e.responseText);
			reload_shelf_range();
		}
	});
}

function loc_changed(){
    var bid = document.f_a['branch_id'].value;
    var date = document.f_a['date'].value;
    var loc = document.f_a['location'].value;
    $('span_shelf_loading').update(_loading_);
    
	$('sel_shelf').update('');
	$('inp_reload_stock_take_list').disabled = true;
	new Ajax.Updater('sel_shelf', phpself+'?a=load_shelf_list&branch_id='+bid+'&date='+date+'&loc='+loc,{
		onComplete: function(e){
			$('span_shelf_loading').update('');
		}
	});
}

function shelf_changed(){
	$('div_stock_take_list').update(_loading_).show();
	$('inp_reload_stock_take_list').disabled = true;
    document.f_a['a'].value = 'ajax_load_stock_take_list';
    
	new Ajax.Updater('div_stock_take_list', phpself,{
	    method: 'post',
	    parameters: $(document.f_a).serialize(),
	    evalScripts: true,
		onComplete: function(e){
			$('inp_reload_stock_take_list').disabled = false;
		}
	});
}

function add(){
	var bid = document.f_a['branch_id'].value;
	var date = document.f_a['date'].value;
	var loc = document.f_a['location'].value;
	var shelf = document.f_a['shelf'].value;
	
//	curtain(true);
//	center_div($('div_stock_take').show());
	jQuery('#div_stock_take').modal('show');
	$('div_stock_take_content').update(_loading_);
	new Ajax.Updater('div_stock_take_content', phpself+'?a=ajax_open', {
		parameters: {
			'branch_id': bid,
			'date': date,
			'location': loc,
			'shelf': shelf
		},
		evalScripts: true
	});
}

function init_calendar(){
	Calendar.setup({
		inputField     :    "inp_stock_take_date",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "img_stock_take_date",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
	});
}

function add_autocomplete(){
    var type = getRadioValue(document.f_b.search_type);
    var sku_item_id = $('sku_item_id').value;
	var qty = $('inp_autocomplete_qty').value;

    if(type==5){    // handheld
        if(!sku_item_id){
			alert('Please search for sku first.');
			sku_handheld.ele_input.focus();
			return;
		}
	}
    
    if(!sku_item_id)    return false;   // check sku item id
    if(!document.f_b['location'].value.trim()){ // check location
		alert('Please enter location.');
		document.f_b['location'].focus();
		return;
	}
    if(!document.f_b['shelf'].value.trim()){ // check location
		alert('Please enter shelf.');
		document.f_b['shelf'].focus();
		return;
	}
	
    var qs = $H({'sid[]': [$('sku_item_id').value]}).toQueryString();
    var param_str = $(document.f_b).serialize()+'&'+qs;
    ajax_add_stock_take_item(type, param_str);
}

function ajax_add_stock_take_item(type, param_str, complete_callback){
   	new Ajax.Request(phpself, {
	    method: 'post',
		parameters: param_str+'&a=ajax_add_stock_take_item',
		onComplete: function(e){
			var msg = e.responseText.trim();

			try{    // try decode JSON string
                var sku_row = JSON.parse(msg);
                if(sku_row['html']){
                    new Insertion.Bottom($('tbody_ajax_added_item'), sku_row['html']);
	                if(type==5){    // handheld
				        sku_handheld.reset_status();
					}else{
				        clear_autocomplete();
					}
					if(complete_callback)	complete_callback(true);
				}
			}catch(ex){ // failed to decode, prompt the error
            	alert(msg);
            	if(complete_callback)	complete_callback(false);
			}
		}
	});
}

function reload_clicked(){
    shelf_changed();
}

function sku_type_changed(){
	if(!$('inp_reload_stock_take_list').disabled)   reload_clicked();
}

function reload_stock_take_list(){
    $('span_refreshing').update(_loading_);
    
	new Ajax.Updater('div_stock_take_list', phpself,{
	    method: 'post',
	    parameters: $(document.f_stock_take_list).serialize()+'&a=ajax_load_stock_take_list',
	    evalScripts: true,
		onComplete: function(e){
			
		}
	});
}

function delete_item(item_id){
	if(!confirm('Are you sure?'))   return false;
	var bid = document.f_stock_take_list['branch_id'].value;
	
	$('span_st_item_'+item_id).hide();
	$('span_st_item_loading_'+item_id).update(_loading_).show();
	new Ajax.Request(phpself, {
		parameters:{
            'a': 'ajax_delete_item',
			'branch_id': bid,
		    'item_id': item_id
		},
		onComplete: function(e){
		    var msg = e.responseText.trim();
		    if(msg=='OK'){
                $('tr_st_item_'+item_id).remove();
            	//$('span_stock_take_list_item_count').update(int($('span_stock_take_list_item_count').innerHTML)-1);
            	recalc_stock_take_list_item();
			}else{
			    $('span_st_item_loading_'+item_id).update('').hide();
                $('span_st_item_'+item_id).show();
                alert(msg);
			}
		}
	});
}

function swap(direction, item_id){
	var swap_tr
	if(direction=='up'){
		swap_tr = $('tr_st_item_'+item_id).previous('tr.tr_item');
	}else{
    	swap_tr = $('tr_st_item_'+item_id).next('tr.tr_item');
	}
	
	if(!swap_tr)    return; // nothing to swap
	
	var bid = document.f_stock_take_list['branch_id'].value;
	var swap_with_item_id = $(swap_tr).readAttribute('item_id');
	
	$('span_st_item_'+item_id).hide();
	$('span_st_item_loading_'+item_id).update(_loading_).show();
	$('span_st_item_'+swap_with_item_id).hide();
	$('span_st_item_loading_'+swap_with_item_id).update(_loading_).show();
	
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_swap_item',
			'branch_id': bid,
			'item_id': item_id,
			'swap_with_item_id': swap_with_item_id
		},
		onComplete: function(e){
            reload_stock_take_list();
		},
		evalScripts: true
	});
}

function submit_stock_take_list_form(act){
	if(!confirm('Are you sure?'))   return false;
	document.f_stock_take_list['a'].value = act;
	document.f_stock_take_list.submit();
}

function ajax_delete_added_item(bid, item_id){
	if(!confirm('Are you sure?'))   return false;
	var img = $('img_new_added_item_'+bid+'_'+item_id);
	if(img.src.indexOf('clock')>0)    return false;
	
	img.src = 'ui/clock.gif';
	new Ajax.Request(phpself, {
		parameters: {
			'a': 'ajax_delete_item',
			'branch_id': bid,
			'item_id': item_id
		},
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='OK'){
				$('tr_new_added_item_'+bid+'_'+item_id).remove();
			}else{
                img.src = 'ui/deact.png';
			}
		}
	});
}

function update_new_added_item(bid, item_id, ele){
	$('span_added_item_loading_'+bid+'_'+item_id).update('<img src="ui/clock.gif" align="absmiddle" />');
	var qty = ele.value;
	
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_update_item_qty',
			'branch_id': bid,
			'item_id': item_id,
			'qty': qty
		},
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg!='OK')   alert(msg);
			$('span_added_item_loading_'+bid+'_'+item_id).update('');
		}
	});
}

function print_stock_report(){
	document.f_stock_take_list['a'].value = 'print_report';
	document.f_stock_take_list.target = '_blank';
	document.f_stock_take_list.submit();
	document.f_stock_take_list.target = '';
}

function reset_range_box(reset_loc, reset_shelf){
	if(reset_loc){
		$('sel_loc_from').innerHTML = '';
		$('sel_loc_to').innerHTML = '';
	}
	if(reset_shelf){
		$('sel_shelf_from').innerHTML = '';
		$('sel_shelf_to').innerHTML = '';
	}
}

function reload_shelf_range(){
	var bid = document.f_a['branch_id'].value;
	var date = document.f_a['date'].value;
	var loc_from = $('sel_loc_from').value;
	var loc_to = $('sel_loc_to').value;
	
	$('td_loading_shelf_range').update(_loading_);
	
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_load_shelf_range',
			'branch_id': bid,
			'date': date,
			'loc_from': loc_from,
			'loc_to': loc_to
		},
		onComplete: function(e){
			var msg = e.responseText.trim();
			$('sel_shelf_from').update(msg);
			$('sel_shelf_to').update(msg);
			$('td_loading_shelf_range').update('');
		}
	});
}

function print_sheet(){
    if (stock_take_count_sheet){
		if (document.f_a['count_sheet'].value.trim()==''){
		    alert("Please enter stock count sheet no.");
			return;
		}
	}
		
	document.f_a['a'].value = 'print_sheet';
	document.f_a.target = '_blank';
	document.f_a.submit();
	document.f_a.target = '';
}

function show_possible_item(){
    var bid = document.f_b['branch_id'].value;
	var date = document.f_b['date'].value;
	var loc = document.f_b['location'].value.trim();
	var shelf = document.f_b['shelf'].value.trim();
	if(!loc){
	    alert('Please enter location');
        document.f_b['location'].focus();
        return false;
	}
	if(!shelf){
	    alert('Please enter shelf');
        document.f_b['shelf'].focus();
        return false;
	}
	center_div($('div_possible_items').show());
	
	
	$('div_possible_items_content').update(_loading_);
	new Ajax.Updater('div_possible_items_content',phpself, {
		parameters: $(document.f_b).serialize()+'&a=ajax_load_possible_item',
		evalScripts: true,
		onComplete: function(e){

		}
	});
}

function possible_items_windows_close(){
    $('div_possible_items').hide();
}

function reload_possible_items(){
	$('div_possible_item_list').update(_loading_);
	new Ajax.Updater('div_possible_item_list', phpself,{
		parameters: $(document.f_possible_item).serialize()
	});
}

function add_possible_items(){
	var all_chx = document.f_possible_item['sid[]'];    // get all possible items
	var got_check = false;
	for(var i=0; i<all_chx.length; i++){    // loop to check whether user got select at least one item
		if(all_chx[i].checked){ // got
			got_check = true;
			break;
		}
	}
	if(!got_check){
		alert('Please select an item');
		return false;
	}
	if(!confirm('Are you sure?'));
	$('btn_add_possible_item').disable().value = 'Adding...';
	var param_str = $(document.f_possible_item).serialize();
	ajax_add_stock_take_item('' , param_str, function(success){
		if(success) $('div_possible_items').hide();
		$('btn_add_possible_item').enable().value = 'Add';
	});
}

function handle_multiple_window_close(){
	$('div_multiple_add_popup').hide();
}

function submit_multi_add(){
	var bid = document.f_b['branch_id'].value;
	var date = document.f_b['date'].value;
	var loc = document.f_b['location'].value;
	var shelf = document.f_b['shelf'].value;
	
	if(!date){  // check date
		alert('Please select date.');
		return false;
	}
	if(!loc){   // check location
		alert('Please enter location.');
		document.f_b['location'].focus();
		return false;
	}
	if(!shelf){ // check location
		alert('Please enter shelf.');
		document.f_b['shelf'].focus();
		return false;
	}
	
	// get all selected sku
	var sid_list = [];
	$$('#tbl_multi_add input.chx_sid_list').each(function(inp){
		if($(inp).checked)  sid_list.push(inp.value);
	});
	if(sid_list.length<=0){ // no sku is selected to add
		alert('Please select at least one item.');
		return false;
	}
	
	// serialize parameters
	var param_str = $H({
		'branch_id': bid,
		'date': date,
		'location': loc,
		'shelf': shelf,
		'sid[]': sid_list
	}).toQueryString();
	
	$('btn_submit_multiple_add').value = 'Adding...';
	$('btn_submit_multiple_add').disabled = true;
	ajax_add_stock_take_item('' , param_str, function(success){
		if(success) handle_multiple_window_close();
		else    $('btn_submit_multiple_add').disabled = false;
	});
}

var stock_take_direct_add_sku_autocomplete = undefined;
function reset_stock_take_direct_add_sku(){
	var param_str = "a=ajax_search_sku&fresh_market_filter=yes&is_parent_only=1&type="+getRadioValue(document.f_stock_take_list['search_type']);
	if (stock_take_direct_add_sku_autocomplete != undefined){
	    stock_take_direct_add_sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		stock_take_direct_add_sku_autocomplete = new Ajax.Autocompleter("inp_autocomplete_sku", "div_autocomplete_sku_choices", "ajax_autocomplete.php", {
		parameters:param_str,
		paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_stock_take_list['inp_autocomplete_sku_item_id'].value =s[0];
			// document.f_a.sku_item_code.value = s[1];
		},
		indicator: 'span_stock_take_direct_add_sku_indicator'
		});
	}
	$('inp_autocomplete_sku').focus();
}

function recalc_stock_take_list_item(){
	var all_tr = $$('#tbody_stock_take_list tr');   // get all tr
	var c = 0;
	if(all_tr)  c = all_tr.length;
	$('span_stock_take_list_item_count').update(c); // update the tr count
	
	// reset the swap up/down arrow
	$$('#tbody_stock_take_list span.span_st_item').each(function(span, i){
		var a_swap_up = $(span).getElementsBySelector("a.a_swap_up")[0];
		var a_swap_down = $(span).getElementsBySelector("a.a_swap_down")[0];
		
		if(i==0)    a_swap_up.style.visibility = 'hidden';
		else    a_swap_up.style.visibility = 'visible';
		
		if(i>=(c-1))	a_swap_down.style.visibility = 'hidden';
		else    a_swap_down.style.visibility = 'visible';
	});
}

function add_stock_take_direct_add_item(){
	var sid = document.f_stock_take_list['inp_autocomplete_sku_item_id'].value;
	if(!sid)    return;
	$('span_stock_take_direct_add_sku_indicator').show();
	var sid_list = [sid];
	var qty_list =[0];
	add_stock_take_list_item(sid_list, qty_list);
}

function add_stock_take_list_item(sid_list, qty_list){
	var param_str = $H({
		'sid_list[]': sid_list,
		'qty_list[]': qty_list,
		'a': 'ajax_direct_add_stock_take_list_item'
	}).toQueryString();
	
	new Ajax.Request(phpself, {
	    method: 'post',
		parameters: $(document.f_stock_take_list).serialize()+'&'+param_str,
		onComplete: function(e){
		    var msg = e.responseText.trim();

			try{    // try decode JSON string
                var sku_row = JSON.parse(msg);
                if(sku_row['html']){
                    new Insertion.Bottom($('tbody_stock_take_list'), sku_row['html']);
                    recalc_stock_take_list_item();
				}
				// clear the input field
				if($('span_stock_take_direct_add_sku_indicator').style.display==''){
				    document.f_stock_take_list['inp_autocomplete_sku_item_id'].value = '';
				    $('inp_autocomplete_sku').value = '';
	                $('span_stock_take_direct_add_sku_indicator').hide();
				}
				if($('span_stock_take_direct_add_sku_handheld_indicator').style.display==''){
				    $('inp_stock_take_direct_add_handheld_sku').value = '';
				    $('inp_stock_take_direct_add_handheld_qty').value = '';
	                $('span_stock_take_direct_add_sku_handheld_indicator').hide();
	                $('inp_stock_take_direct_add_handheld_sku').focus();
				}
				if($('div_stock_take_direct_add_multiple').style.display==''){
					default_curtain_clicked();
				}
			}catch(ex){ // failed to decode, prompt the error
            	alert(msg);
            	if($('div_stock_take_direct_add_multiple').style.display==''){
					$('btn_submit_multiple_add').disabled = false;
				}
            	
			}
		}
	});
}

function search_stock_take_direct_add_handheld_item(){
	var v = $('inp_stock_take_direct_add_handheld_sku').value.trim();
	if(v=='')   return;
	
	$('span_stock_take_direct_add_sku_handheld_indicator').show();

	var param_str = $H({
		'a': 'ajax_search_sku_by_handheld',
		'fresh_market_filter': 'yes',
		'is_parent_only': 1,
		'value': v
	}).toQueryString();
	
	new Ajax.Request('ajax_autocomplete.php', {
		parameters: param_str,
		onComplete: function(e){
            var msg = e.responseText.trim();

			try{    // try decode JSON string
                var sku_row = JSON.parse(msg);
                if(sku_row['id']){
					$('inp_stock_take_direct_add_sid').value = sku_row['id'];   // store sku item id
					$('inp_stock_take_direct_add_handheld_sku').value = sku_row['description']; // change scanned code to description
					$('inp_stock_take_direct_add_handheld_qty').focus();    // move focus to qty
					$('inp_stock_take_direct_add_allow_decimal').value = sku_row['doc_allow_decimal'];   // store doc allow decimal indicator
					if(sku_row['doc_allow_decimal'] == 1){

						$('inp_stock_take_direct_add_handheld_qty').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
						$('inp_stock_take_direct_add_handheld_qty').value = float(round($('inp_stock_take_direct_add_handheld_qty').value, global_qty_decimal_points));
					}else{
						$('inp_stock_take_direct_add_handheld_qty').onchange = function(){ mi(this); };
						$('inp_stock_take_direct_add_handheld_qty').value = int($('inp_stock_take_direct_add_handheld_qty').value);
					}
				}else{
					alert('Unexpected Error.'); // should not get this error
				}
			}catch(ex){ // failed to decode, prompt the error
			    if(msg=='no')   alert('No match found.');
            	else	alert(msg); // should not get this error, if yes this should be sql error
			}
			$('span_stock_take_direct_add_sku_handheld_indicator').hide();
		}
	});
}

function add_stock_take_direct_add_handheld_item(){
    if ($('inp_stock_take_direct_add_allow_decimal').value == 1)
    	$('inp_stock_take_direct_add_handheld_qty').value = float(round($('inp_stock_take_direct_add_handheld_qty').value, global_qty_decimal_points)); 
    else
		$('inp_stock_take_direct_add_handheld_qty').value = int($('inp_stock_take_direct_add_handheld_qty').value);

	var sid = $('inp_stock_take_direct_add_sid').value;
        
	if(!sid){   // no sku item id
		alert('Please search sku first.');
		$('inp_stock_take_direct_add_handheld_sku').focus();
		return false;
	}
	
	var qty = $('inp_stock_take_direct_add_handheld_qty').value;
	if(qty<0){
		alert('Invalid qty');
		return false;
	}
	
	$('span_stock_take_direct_add_sku_handheld_indicator').show();
	var sid_list = [sid];
	var qty_list= [qty];
	add_stock_take_list_item(sid_list, qty_list);
}

function curtain_clicked(){
	$('div_stock_take_direct_add_multiple_content').update('');
	if($('div_multiple_add_popup_content')) $('div_multiple_add_popup_content').update('');
}

function show_stock_take_direct_add_multiple(){
	var v = $('inp_autocomplete_sku').value.trim();
	if(v=='')    return false;
	curtain(true);
	center_div($('div_stock_take_direct_add_multiple').show());
	$('div_stock_take_direct_add_multiple_content').update(_loading_);
	
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_stock_take_list.search_type)+'&hide_print=1&show_multiple=1';
	param_str += '&fresh_market_filter=yes';    // only get fresh market sku
	param_str += '&is_parent_only=1';   // only get parent sku
	param_str += '&alt_submit_multi_add=submit_multi_add2(this)';
	new Ajax.Updater('div_stock_take_direct_add_multiple_content','ajax_autocomplete.php?',{
		parameters: param_str+'&value='+v,
		evalScripts: true
	});
}

function submit_multi_add2(){
	// get all selected sku
	var sid_list = [];
	$$('#tbl_multi_add input.chx_sid_list').each(function(inp){
		if($(inp).checked)  sid_list.push(inp.value);
	});
	if(sid_list.length<=0){ // no sku is selected to add
		alert('Please select at least one item.');
		return false;
	}

	var qty_list= [0];  // just simply put a zero qty to avoid javascript error
	$('btn_submit_multiple_add').value = 'Adding...';
	$('btn_submit_multiple_add').disabled = true;
	add_stock_take_list_item(sid_list, qty_list);
}

function recalc_variance(id,qty,sb_qty){
	var sign="";
	var variance = float(round(qty - sb_qty, global_qty_decimal_points));
	if(variance > 0){
		sign = "+";
		$('var_'+id).className = "r positive";
	}else if(variance < 0){
		$('var_'+id).className = "r negative";
	}else $('var_'+id).className = "r";
	$('var_'+id).update(sign+variance);
}
{/literal}
</script>
<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>


<div class="modal" id="div_stock_take">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header bg-danger" id="div_stock_take_header">
                <h6 class="modal-title text-white">Stock Take Details</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
				<div style="clear:both;"></div>
			</div>
            <div class="modal-body">
                <div id="div_stock_take_content" style="padding:2px;"></div>
            </div>
        </div>
    </div>
</div>


<div id="div_possible_items" class="curtain_popup" style="position:absolute;z-index:10001;width:650px;height:515px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_possible_items_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Stock Take Possible Items</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="possible_items_windows_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_possible_items_content" style="padding:2px;"></div>
</div>

<!-- multiple add div -->
<div id="div_stock_take_direct_add_multiple" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_stock_take_direct_add_multiple_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_stock_take_direct_add_multiple_content" style="padding:2px;"></div>
</div>
<!-- end of multiple add div -->

<div class="alert alert-primary rounded mx-3 mt-2">
	<b>Tips</b>
<ul>
	<li> Put stock take date as '2010-06-01' if you want to see the sales data for '2010-05-31'.</li>
</ul>
</div>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a">
			<input type=hidden name="a" />
			
			{if !$can_select_branch}<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />{/if}
			
			<table>
				<tr>
					{if $can_select_branch}<td><b class="form-label">Branch</b></td>{/if}
					<td valign=top><b class="form-label">Date</b><span id="span_date_loading"></span></td>
					<td><b class="form-label">Location</b><span id="span_loc_loading"></span></td>
					<td><b class="form-label">Shelf</b><span id="span_shelf_loading"></span></td>
					<td></td>
				</tr>
				<tr>
					{if $can_select_branch}
						<td>
							<select name="branch_id" onchange="branch_changed()" size="10" class="sel_1 form-control">
								{foreach from=$branches item=r}
									<option value="{$r.id}" {if !$smarty.request.branch_id and $BRANCH_CODE eq $r.code}selected {else}{if $smarty.request.branch_id eq $r.id}selected {/if}{/if}>{$r.code}</option>
								{/foreach}
							</select>
						</td>
					{/if}
					<td>
						<select name="date" onChange="date_changed();" size="10"  class="sel_1 form-control" id="sel_date">
							{foreach from=$date item=r}
								<option value="{$r.d}" {if $smarty.request.date eq $r.d}selected {/if}>{$r.d}</option>
							{/foreach}
						</select>
					</td>
					<td>
						<select name="location" onChange="loc_changed();" size="10"  class="sel_1 form-control" id="sel_loc">
							{foreach from=$loc item=r}
								<option value="{$r.loc}" {if $smarty.request.location eq $r.loc}selected {/if}>{$r.loc}</option>
							{/foreach}
						</select>
					</td>
					<td>
						<select name="shelf" size="10" onChange="shelf_changed();" class="sel_1 form-control" id="sel_shelf">
							{foreach from=$shelf item=r}
								<option value="{$r.s}" {if $smarty.request.shelf eq $r.s}selected {/if}>{$r.s}</option>
							{/foreach}
						</select>
					</td>
					<td>
						<fieldset style="width: 300px;">
						<legend><b class="form-label ml-2">Select By Range</b></legend>
						<table>
							<tr>
								<td nowrap class="form-label mt-2 ml-2">Location From&nbsp;</td>
								<td>
									<select class="form-control" name="loc_from" onChange="reload_shelf_range();" id="sel_loc_from">
										{foreach from=$loc item=r}
											<option value="{$r.loc}" {if $smarty.request.location eq $r.loc}selected {/if}>{$r.loc|upper}</option>
										{/foreach}
									</select>
								</td>
								<td class="form-label mt-2">&nbsp;To&nbsp;</td>
								<td>
									<select class="form-control" name="loc_to" onChange="reload_shelf_range();" id="sel_loc_to">
										{foreach from=$loc item=r}
											<option value="{$r.loc}" {if $smarty.request.location eq $r.loc}selected {/if}>{$r.loc|upper}</option>
										{/foreach}
									</select>
								</td>
								<td id="td_loading_shelf_range" nowrap></td>
							</tr>
							<tr id="tr_shelf_range">
								<td class="form-label ml-2">Shelf From</td>
								<td>
									<select class="form-control" name="shelf_from" id="sel_shelf_from">
										{foreach from=$shelf2 item=r}
											<option value="{$r.s}" {if $smarty.request.shelf eq $r.s}selected {/if}>{$r.s}</option>
										{/foreach}
									</select>
								</td>
								<td class="form-label">&nbsp;To&nbsp;</td>
								<td>
									<select class="form-control" name="shelf_to" id="sel_shelf_to">
										{foreach from=$shelf2 item=r}
											<option value="{$r.s}" {if $smarty.request.shelf eq $r.s}selected {/if}>{$r.s}</option>
										{/foreach}
									</select>
								</td>
							</tr>
							<tr>
								<td class="form-label ml-2">Sku Type</td>
								<td>
									<select class="form-control" name="p_sku_type">
										<option value=''>All</option>
										{foreach from=$sku_type item=r}
											<option value='{$r.code}' {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
										{/foreach}
									</select>
								</td>
							</tr>
							{if $config.stock_take_count_sheet}
								<tr>
									<td nowrap class="form-label ml-2">Stock Count Sheet No.</td>
									<td><input class="form-control" name='count_sheet' type="text" maxlength="5" size="10" value='{$smarty.request.count_sheet}' onChange="miz(this);" /></td>
								</tr>
							{/if}
						</table>
						<br>
						<div class="form-inline ml-2">
							<input class="btn btn-primary" type=button value="Print Check List" onclick="print_sheet();">&nbsp;&nbsp;&nbsp;&nbsp;
						<div class="form-label"><input type="checkbox" name="print_with_qty" value=1> Print with quantity</div>
						</div>
					</fieldset>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<div class="form-inline">
							<input class="btn btn-primary" id="inp_reload_stock_take_list" type="button" value="Reload" {if !$smarty.request.shelf}disabled {/if} onClick="reload_clicked();" />
						&nbsp;&nbsp;&nbsp;
						<b class="form-label">SKU Type</b>
						&nbsp;<select class="form-control" name="sku_type" onChange="sku_type_changed();">
							<option value="">-- All --</option>
							{foreach from=$sku_type item=r}
								<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
							{/foreach}
						</select>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<br>
<div class="card mx-3">
	<div class="card-body"><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd New Stock</a> </div>
</div>
<br>

<div id="div_stock_take_list" class="stdframe" style="{if !$smarty.request.shelf}display:none;{/if}">
</div>

{include file='footer.tpl'}
<script>
    {if $smarty.request.shelf}
	    reload_clicked();
	{/if}
	{if $smarty.request.date}
	    reload_shelf_range();
	{/if}
	{if $smarty.request.msg}
		alert('{$smarty.request.msg}');
	{/if}
{literal}
new Draggable('div_stock_take',{ handle: 'div_stock_take_header'});
new Draggable('div_possible_items',{ handle: 'div_possible_items_header'});
new Draggable('div_stock_take_direct_add_multiple',{ handle: 'div_stock_take_direct_add_multiple_header'});
{/literal}
</script>
