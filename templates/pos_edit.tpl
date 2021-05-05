{*
10/15/2012 9:26 AM Andy
- Add import pos items by csv.
- Add a button to clear added pos items.
- Add can swap new pos items row.
- Add can auto numbering item id at pos items list.
- Add to ask confirmation when user going to leave page while is under editing mode.
- Add when edit/remove pos items row will recalculate total qty and amount.
- Add can tick/un-tick all pos items to delete.

8/26/2013 5:34 PM Justin
- Enhanced to allow user can pre-select payment type.
- Enhanced to round total qty to 3 decimal points.
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#div_payment_type_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div_payment_type_list ul li:hover {
	background:#ff9;
}

#div_payment_type_list ul li.current {
	background:#9ff;
}

#div_payment_type_list:hover ul {
	visibility:visible;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

var request_cid = '{$smarty.request.counter_id}';
var request_date = '{$smarty.request.date}';
var request_receipt = '{$smarty.request.receipt_no}';
var needCheckExit = false;

{literal}

var RECEIPT_SEARCH = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
		    inputField     :    "inp_date",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
		
		if(request_cid && request_date && request_receipt){
			this.load_receipt_clicked();
		}
	},
	// function to validate form before submit
	check_form: function(need_check_receipt){
		if(!this.f['counter_id'].value){
			alert('Please select counter.');
			return false;
		}
		
		if(this.f['date'].value.trim()==''){
			alert('Invalid Date');
			return false;
		}
		
		if(need_check_receipt){
			if(this.f['receipt_no'].value.trim()==''){
				alert('Please key in receipt no.');
				return false;
			}
		}
		
		return true;
	},
	// function when user click create new receipt
	new_receipt_clicked: function(){
		if(!this.check_form())	return false;
		
		this.f['is_new_receipt'].value = 1;
		this.load_receipt();
	},
	// function when user click load receipt
	load_receipt_clicked: function(){
		if(!this.check_form(true))	return false;
		
		this.load_receipt();
	},
	load_receipt: function(){
		$('div_pos_content').update(_loading_);
		
		var params = $(this.f).serialize();
		this.f['is_new_receipt'].value = 0;
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){ 
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_pos_content').update(ret['html']);
	                    POS_FORM.initialize();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    //alert(err_msg);
			    $('div_pos_content').update(err_msg);
			}
		});
	}
};

var POS_FORM = {
	f: undefined,
	initialize: function(){
		this.f = document.f_b;
		
		// once the form is load, prompt user if they are going to leave the page
		needCheckExit = true;
		
		this.pi_row_changed();
	},
	// function when user click add new pos item
	add_pos_items_clicked: function(){
		var new_pi_id = (new Date()).getTime();

		var new_tr = $('tr_pi_item-__TMP_PI_ID__').cloneNode(true);
		
		new_tr.id = "tr_pi_item-"+new_pi_id;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_PI_ID__/g, new_pi_id);
		$(new_tr).update(new_html);
		
		$('tbody_pi_list').appendChild(new_tr);
		
		this.pi_row_changed();
	},
	// function when user click add pos payment
	add_pos_paymnent_clicked: function(){
		var new_pp_id = (new Date()).getTime();

		var new_tr = $('tr_pp_item-__TMP_PP_ID__').cloneNode(true);
		
		new_tr.id = "tr_pp_item-"+new_pp_id;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_PP_ID__/g, new_pp_id);
		$(new_tr).update(new_html);
		
		$('tbody_pp_list').appendChild(new_tr);
	},
	// function when user click add mix and match
	add_mix_n_match_clicked: function(){
		var new_mm_id = (new Date()).getTime();

		var new_tr = $('tr_mm_item-__TMP_MM_ID__').cloneNode(true);
		
		new_tr.id = "tr_mm_item-"+new_mm_id;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_MM_ID__/g, new_mm_id);
		$(new_tr).update(new_html);
		
		$('tbody_mm_list').appendChild(new_tr);
	},
	// function when user checked delete pos items
	delete_pi_checked: function(pi_id){
		if(!pi_id)	return false;
		
		this.pi_row_changed();
	},
	// function when user click delete pos item row
	delete_pi_clicked: function(pi_id){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_pi_item-'+pi_id).remove();
		
		this.pi_row_changed();
	},
	// function when user click delete pos payment row
	delete_pp_clicked: function(pp_id){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_pp_item-'+pp_id).remove();
	},
	// function to validate form before submit
	check_form: function(){
		if(!check_required_field(this.f))	return false;
		
		return true;
	},
	// function when user click save pos
	save_pos_clicked: function(){
		if(!this.check_form())	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('btn_save_pos').disabled = true;
		$('btn_save_pos').value = 'Saving. . .';
		
		var params = $(this.f).serialize();
		
		var cid = this.f['counter_id'].value;
		var d = this.f['date'].value;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('btn_save_pos').disabled = false;
				$('btn_save_pos').value = 'Save';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['receipt_no']){ // success
	                	alert('POS Updated');
	                	needCheckExit = false;
	      				window.location = 'pos_edit.php?counter_id='+cid+'&date='+d+'&receipt_no='+ret['receipt_no'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			    
			}
		});
	},
	// function to get all pos item row
	get_all_pi_row: function(){
		return $$('#tbody_pi_list tr.tr_pi_item');
	},
	// function to get pos item id by element
	get_pi_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain pi id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_pi_item')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var pi_id = parent_ele.id.split('-')[1];
		return pi_id;
	},
	// function when user click auto numbering item id
	renum_item_id_clicked: function(id){
		//alert('sort sort');
		var ctr = 1;
		$$('#tbody_pi_list input.inp_pi_item_id').each(function (obj,idx){
			obj.value = ctr;
			ctr++;
		});
	},
	barcode_clicked: function(id){
	
		var barcode = $('pi_barcode-'+id).value;
		var params = 'a=process_barcode&barcode='+barcode;
		if (barcode == '') {
			alert('Please enter barcode');
			return;
		}
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){ 
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
					if(ret['err'])	{
						alert(ret['err']);
						return;
					}
					else {
						if (ret['qty_pcs']) $('pi_qty-'+id).value = ret['qty_pcs'];
						if (ret['sku_item_code']) $('pi_sku_item_code-'+id).value = ret['sku_item_code'];
						if (ret['selling_price']) $('pi_price-'+id).value = ret['selling_price'];
						POS_FORM.pi_row_changed(); //re-calculate
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
					alert(err_msg);
				}
			}
		});
		
		return false;
	},
	// function to check all imgage swap up/down
	check_pi_swap_img_visibility: function(){
		// get all pos item row list
		var pi_list = this.get_all_pi_row();
		
		// loop row
		is_first_swappable_row = true;
		for(var i = 0; i<pi_list.length; i++){
			// get row id
			var pi_id = this.get_pi_id_by_ele(pi_list[i]);
			
			var img_pi_swap_up = $('img_pi_swap_up-'+pi_id);
			var img_pi_swap_down = $('img_pi_swap_down-'+pi_id);
			
			var hidden_up = '';
			var hidden_down = '';
			
			if(!this.is_new_pi(pi_id)){
				hidden_up = 'hidden';
				hidden_down = 'hidden';
			}else{
				if(is_first_swappable_row){
					hidden_up = 'hidden';
					is_first_swappable_row = false;
				}
				if(i == pi_list.length-1){
					hidden_down = 'hidden';
				}
			}
			
			
			
			img_pi_swap_up.style.visibility = hidden_up;
			img_pi_swap_down.style.visibility = hidden_down;
		}
	},
	// function to check whether this pi_id is new row
	is_new_pi: function(pi_id){
		return this.f['pi_delete['+pi_id+']'] ? false : true;
	},
	// function to call when add/swap/remove row
	pi_row_changed: function(){
		// check swap img
		this.check_pi_swap_img_visibility();
		
		// recalculate total
		this.recalc_total();
	},
	// function when user click swap pi row
	swap_pi_row: function(direction, pi_id){
		var swap_tr = undefined;
		var tr_pi_item = $('tr_pi_item-'+pi_id);
		
		if(direction=='up'){
			swap_tr = tr_pi_item.previous('tr.tr_pi_item');
		}else{
	    	swap_tr = tr_pi_item.next('tr.tr_pi_item');
		}
		
		if(!swap_tr)    return; // nothing to swap
		
		swap_ele(tr_pi_item, swap_tr);
		
		this.pi_row_changed();
	},
	// function when user click clear pos items list
	clear_pi_list: function(){
		if(!confirm('Are you sure?\nThis will only remove newly added item.'))	return false;
		
		// get all pos item row list
		var pi_list = this.get_all_pi_row();
		
		for(var i = 0; i<pi_list.length; i++){
			// get row id
			var pi_id = this.get_pi_id_by_ele(pi_list[i]);
			
			if(this.is_new_pi(pi_id)){
				$('tr_pi_item-'+pi_id).remove();
			}
		}
		
		this.pi_row_changed();
	},
	// function to recalculate total
	recalc_total: function(){
		// get all pos item row list
		var pi_list = this.get_all_pi_row();
		var total_pi_qty = 0;
		var total_amt = 0;
		
		for(var i = 0; i<pi_list.length; i++){
			// get row id
			var pi_id = this.get_pi_id_by_ele(pi_list[i]);
			
			if(!this.is_new_pi(pi_id)){	// is existing pos items
				if(this.f['pi_delete['+pi_id+']'] && this.f['pi_delete['+pi_id+']'].checked)	continue;	// skip delete row
			}
			total_pi_qty += float(this.f['pi_qty['+pi_id+']'].value);
			
			var amt = float(round(float(this.f['pi_price['+pi_id+']'].value)-float(this.f['pi_discount['+pi_id+']'].value),2));
			total_amt += amt;
		}
		
		$('span_total_pi_qty').update(round(total_pi_qty, 3));
		$('span_total_amt').update(round(total_amt,2));
	},
	// function when pos items qty changed
	pi_qty_changed: function(pi_id){
		this.pi_row_changed();
	},
	// function when pos items price changed
	pi_price_changed: function(pi_id){
		this.pi_row_changed();
	},
	// function when pos items discount changed
	pi_discount_changed: function(pi_id){
		this.pi_row_changed();
	},
	// function when user import pos items by csv
	import_pi_by_csv: function(){
		if(this.f['pi_csv'].value.indexOf('.csv')<0){
			alert('Please select a csv file to import.');
			return false;
		}
		
		var old_a = this.f['a'].value;
		
		this.f['a'].value = 'import_pi_by_csv';
		this.f.target = 'ifimport';
		
		this.f.submit();
		
		this.f['a'].value = old_a;
		this.f.target = '';
	},
	// callback function after import pos items by csv
	import_pi_by_csv_callback: function(msg){
		var THIS = this;
		var str = msg.trim();
		var ret = {};
	    var err_msg = '';
		
	    try{
            ret = JSON.parse(str); // try decode json object
            if(ret['ok']){ // success
            	if(ret['html']){
            		new Insertion.Bottom('tbody_pi_list', ret['html']);
            		THIS.pi_row_changed();
            	}else{
            		alert('There is no row to add');
            		return false;
            	}
                return;
			}else{  // save failed
				if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				else    err_msg = str;
			}
		}catch(ex){ // failed to decode json, it is plain text response
			err_msg = str;
		}

	    // prompt the error
	    alert(err_msg);
	},
	// function when user toggle delete all pos items
	pi_all_delete_changed: function(){
		var c = $('chx_toggle_pi_all_delete').checked;
		
		// get all pos item row list
		var pi_list = this.get_all_pi_row();
		
		for(var i = 0; i<pi_list.length; i++){
			// get row id
			var pi_id = this.get_pi_id_by_ele(pi_list[i]);
			
			if(this.is_new_pi(pi_id))	return;
			
			this.f['pi_delete['+pi_id+']'].checked = c;
		}
	},
	
	do_select_type: function(obj, pt){
		var name = pt;

		var id = $("curr_pp_id").value;
		this.f['pp_type['+id+']'].value = name;
		var curr_row_no = obj.readAttribute('pt_row');
		$("curr_pp_row_no_"+id).value = curr_row_no;

		Element.hide('div_payment_type_list');
	},
	
	show_type_option: function(type, id){
		//alert(type);
		//alert(id);
		if($('div_payment_type_list').style.display=='none'){
			Element.show('div_payment_type_list');
			
			$("curr_pp_id").value = id;
			Position.clone($('div_payment_type_list_'+id), $('div_payment_type_list'), {setHeight: false, setWidth:false});
			//chklabel = $('issue_name').value;
			$$('#div_payment_type_list li').each(function (obj,idx){
				var row_no = obj.readAttribute('pt_row');
				var curr_row_no = $("curr_pp_row_no_"+id).value;
				if (row_no == curr_row_no){
					obj.className = 'current';
					obj.scrollToPosition;
				}
				else{
					obj.className = '';		
				}
			});	
		}
		else{
			Element.hide('div_payment_type_list');
		}
	}
}

function confirmExit(e) {
	if(needCheckExit){
		return 'Are You sure you want to leave at this time? Any un-saved data will be lost.';
	}
	
}
window.onbeforeunload=confirmExit;


function import_pi_by_csv_callback(msg){
	POS_FORM.import_pi_by_csv_callback(msg);
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<!-- payment type menu -->
<div id="div_payment_type_list" style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid #000;margin: 0 0 0 0;height:200px;width:342px;overflow:auto;">
<ul id="tab">
{foreach item=pt from=$payment_type key=pt_row}
	<li onclick="POS_FORM.do_select_type(this, '{$pt|escape:'javascript'}');" id="selected_type" pt_row="{$pt}">{$pt}</li>
{/foreach}
</ul>
</div>
<input type="hidden" id="curr_pp_id" value="" />

<!-- End of Reject type menu -->

<form name="f_a" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_load_receipt" />
	<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	<input type="hidden" name="is_new_receipt" />
	
	<b>Branch: </b>
	{$BRANCH_CODE}
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Counter: </b>
	<select name="counter_id">
		<option value="">-- Please Select --</option>
		{foreach from=$counter_list key=cid item=r}
			<option value="{$cid}" {if $smarty.request.counter_id eq $cid}selected {/if}>{$r.network_name}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Date: </b>
	<input type="text" name="date" id="inp_date" size="12" value="{$smarty.request.date|default:$smarty.now|date_format:"%Y-%m-%d"}" class="required" title="Date" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" />
	&nbsp;&nbsp;&nbsp;&nbsp;	
			
	<b>Receipt No: </b>
	<input type="text" name="receipt_no" size="5" value="{$smarty.request.receipt_no}" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Load Receipt" onClick="RECEIPT_SEARCH.load_receipt_clicked();" />
	<input type="button" value="New Receipt" onClick="RECEIPT_SEARCH.new_receipt_clicked();" />
</form>

<br />

<div id="div_pos_content">
</div>

<iframe style="visibility:hidden;width:1px;height:1px;" name="ifimport"></iframe>

<script type="text/javascript">RECEIPT_SEARCH.initialize();</script>

{include file="footer.tpl"}
