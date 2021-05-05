{*
REVISION HISTORY
==================
3/12/2008 10:59:42 AM gary
- initial the sku autocomplete.

9/15/2008 1:13:29 PM yinsee
- calculate cost in calc_total

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

8/20/2009 3:20:00 PM Andy
- add PO reset function

10/09/2009 10:00:40 AM edward
- remove test alert

12/23/2009 5:38:03 PM Andy
- Remove "Save & Close" and "Confirm" button at approved screen and replace it with "Close" button

5/31/2010 4:14:36 PM Alex
- add function upper_lower_limit()

6/3/2010 11:03:48 AM Andy
- Fix adjustment approval screen no show total bugs.

7/6/2010 10:12:30 AM Justin
- Added the checking function to disable/enable positive and negative fields base on the Adjustment Type.
- While loading a particular adjustment, added the disable feature to either positive or negative fields based on the Adjustment Type as if found the type is from config's set adjustment type.
- Enable both positive and negative fields whenever found user key in Adjustment Type manually.
- When adding a new SKU Item, created the similar function verify whether which field supposing to disable if the Adjustment Type belongs to config.

10/12/2010 10:10:25 AM Andy
- Disable the change branch feature once adjustment was saved.
- Fix wrong item row calculation bugs.
- Fix a few adjustment type bugs, which on/off wrong input box, cannot retain the selected adjustment type when add new item

7/27/2011 4:19:23 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config but not fixed by 2.

10/27/2011 4:12:32 PM Justin
- Added timer ID as hidden field.

11/9/2011 11:33:02 AM Andy
- Add need user to click continue before they can insert adjustment item.
- Add to block user to change adjustment branch after they click "continue".

8/14/2012 11:32 AM Justin
- Enhanced the entire scan item process to use ajax and JSON instead of XML.
- Enhanced to have GRN barcode scan function.
- Added a new ability that able to sense and add extra qty when matched existed items instead of showing error message.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

7/24/2013 11:16 AM Andy
- Enhance to check adjustment total cost for approval when confirm.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

1/28/2014 11:39 AM Justin
- Enhanced to have serial no feature.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

2/4/2015 2:25 PM Andy
- Fix when added item was duplicated, the addon qty should not appear null.
- Add periodically load dummy to avoid page logout.
- Enhance to can add attachment. (jpg/pdf/zip)

8/3/2015 11:01 AM Andy
- Add checking for $can_edit for some features.
- Fix adjustment cannot be view in approval view.

11/13/2015 9:00 AM Qiu Ying
- Add attachment file size limit 1 mb.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

10/27/2016 9:44 AM Andy
- Fixed load approval listing when got config adjustment_branch_selection.

2/3/2017 5:38 PM Andy
- Fixed to only show save and confirm button when it is editable.

3/20/2017 5:22 PM Andy
- Enhance when duplicate popup, positive value will always go to positive column, negative value will always go to negative column, no matter adjustment type.
- Enhance to always sum positive and negative value to calculate the latest adjust qty.

4/26/2017 9:46 AM Justin
- Bug fixed on javascript error while create new adjustment.

1/12/2018 3:11 PM Andy
- Enhanced to check work order when load adjustment.

6/11/2019 3:32 PM William
- Enhanced "Adjustment Type" cannot manually edit.

8/1/2019 2:31 PM William
- Fixed bug Save and comfirm button can make system generate multiple adjustment, when quickly click button multiple times.

9/3/2019 9:08 AM William
- Enhanced Attachment image can upload unlimited image.

6/23/2020 11:53 AM Sheila
- Updated button color

10/28/2020 2:15 PM William
- Enhanced to added "Add items by CSV" button.
*}
{if !$form.approval_screen}
{include file=header.tpl}
{else}
<hr noshade size=2>
{/if}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
#div_type_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div_type_list ul li:hover {
	background:#ff9;
}

#div_type_list ul li.current {
	background:#9ff;
}

#div_type_list:hover ul {
	visibility:visible;
}

.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
.csunday {
	color:#f00;
}
</style>
{/literal}
<script type="text/javascript">

// update autocompleter parameters when vendor_id or dept_id changed
var phpself = '{$smarty.server.PHP_SELF}';
var sku_autocomplete = undefined;
var adjustment_branch_selection = '{$config.adjustment_branch_selection|default:0}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var branch_id = '{$form.branch_id}';

{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}

var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

{literal}

/*
function reset_sku_autocomplete(){
	
	//var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(document.f_a.search_type)+"&dept_id="+document.f_a.department_id.value;
	
	var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(document.f_a.search_type);
	
	if (sku_autocomplete != undefined){
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
			$('btn_add_item').focus();
		}});
	}
	$('autocomplete_sku').focus();
}
*/

function init_calendar(){


	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});

}

function show_type_option(type){
	if($('div_type_list').style.display=='none'){
		Element.show('div_type_list');	
		Position.clone($('type_2'), $('div_type_list'), {setHeight: false, setWidth:false});
		//chklabel = $('issue_name').value;
		$$('#div_type_list li').each(function (obj,idx){
			if (obj.innerHTML == type){
				obj.className = 'current';
				obj.scrollToPosition;
			}
			else{
				obj.className = '';		
			}
		});	
	}
	else{
		Element.hide('div_type_list');		
	}
}

function do_select_type(obj, adj_type){
	var name =obj.title;	
	$('adjustment_type').value=name;

	disable_field_bytype(adj_type);

	Element.hide('div_type_list');		
}

function disable_field_bytype(adj_type){

	if(adj_type){
		var type = adj_type;
	
		if(type == '+'){ // if found the adjustment type contains positive sign
			var p = $('tbl_item').getElementsByClassName('p qty');
			var n = $('tbl_item').getElementsByClassName('n qty');
			
			// loop the following by setting up all the figure for negative fields become null and disable it.
			for(var i=0;i<n.length;i++){
				n[i].value = '';
				n[i].readOnly = true;
			}
	
			// loop the following by setting up all the figure for positive fields to enable it.
			for(var i=0;i<p.length;i++){
				p[i].readOnly = false;
			}
			$('is_config_adj_type').value = type;
		}else if(type == '-'){ // if found the adjustment type contains negative sign
			var p = $('tbl_item').getElementsByClassName('p qty');
			var n = $('tbl_item').getElementsByClassName('n qty');
	
			// loop the following by setting up all the figure for positive fields become null and disable it.
			for(var i=0;i<p.length;i++){
				p[i].value = '';
				p[i].readOnly = true;
			}
			
			// loop the following by setting up all the figure for negative fields to enable it.
			for(var i=0;i<n.length;i++){
				n[i].readOnly = false;
			}
			$('is_config_adj_type').value = type;
		}
	}else{	
		// enable all the fields from negative
		var n = $('tbl_item').getElementsByClassName('n qty');
		for(var i=0;i<n.length;i++){
			n[i].readOnly = false;
		}
			
		// enable all the fields from positive
		var p = $('tbl_item').getElementsByClassName('p qty');
		for(var i=0;i<p.length;i++){
			p[i].readOnly = false;
		}
		$('is_config_adj_type').value = ''; // all available
	}
	calc_total();
}
/*
function add_item(){
	if (int(document.f_a.sku_item_id.value)==0){
	    alert('No item selected');
	    return false;
	}
	var parms;
    parms = Form.serialize(document.f_a)+'&a=ajax_add_item';

	new Ajax.Request(
	    "adjustment.php",
	    {
			method:'post',
			parameters: parms,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
                var tb = $('adjust_items');
                var lbody;
				var xml = m.responseXML;
				
				if (!xml) { alert(m.responseText); return; }
				var records = xml.getElementsByTagName("record");
				$A(records).each(
				    function(r,idx){
					    var rowitem = tb.insertRow(-1);
					    rowitem.id = "titem"+xml_getData(r, "id").strip();
					    rowitem.innerHTML = xml_getData(r,'rowdata');
					    //add to focus the qty positive field after add_item
					    $('p_qty_'+(xml_getData(r, "id").strip())).focus();	
					}
				);
				reset_row();	
			},
		}
	);
}
*/
function reset_row(){
	var e = $('docs_items').getElementsByClassName('no');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^no_');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no_'+(i+1);
		}
	}
	//$('autocomplete_sku').select();
}


function add_autocomplete_extra(){
    reset_row();
}

function delete_item(id){
	var bstr = '';
	if(adjustment_branch_selection==1){
		bstr = '&bid='+document.f_a.default_branch_id.value;
	}
 	if (!confirm('Remove this SKU from Adjustment?')) return;
	ajax_request("adjustment.php",{
		method:'post',
		parameters: 'a=ajax_delete_row&id='+id+''+bstr,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            Element.remove('titem'+id);
            reset_row();
            calc_total();
			
			if($('sn_item'+id) != undefined){
				Element.remove('sn_item'+id); // remove if got S/N
				if($('sn_details').innerHTML.trim() == '') $('sn_title').hide();
			}
    	}
	});
}

function calc_total(rid){
	var total_qty_p=0;
	var total_qty_n=0;
	var total_cost=0;
	var total_selling = 0;
	var is_config_adj_type = $('is_config_adj_type').value;
	
	if (rid != undefined)
	{
		// update row
		// row total cost
		var sstr = '['+rid+']';
		var qty = float(round(float(document.f_a.elements['p_qty'+sstr].value) - float(document.f_a.elements['n_qty'+sstr].value), global_qty_decimal_points));
		document.f_a.elements['n_qty'+sstr].value = '';
		document.f_a.elements['p_qty'+sstr].value = '';
		
		if(qty>0){
			document.f_a.elements['p_qty'+sstr].value = qty;
			if(is_config_adj_type=="-")	document.f_a.elements['p_qty'+sstr].value = '';
		}else if(qty < 0){
			document.f_a.elements['n_qty'+sstr].value = qty*-1;
			if(is_config_adj_type=="+")	document.f_a.elements['n_qty'+sstr].value = '';
		}
		document.f_a.elements['cost'+sstr].value = round(qty * float(document.f_a.elements['unit_cost'+sstr].value),2);
		
		// row selling price
		document.f_a.elements['total_selling'+sstr].value = round(qty * float(document.f_a.elements['selling_price'+sstr].value),2);
	}
	
	// update total	
	var e = $('tbl_item').getElementsByClassName('qty');
	for(var i=0;i<e.length;i++){
	
		id_set = e[i].id;
		var id_get = id_set.split("_");

		if(id_get[2] != undefined){
			id_set = id_get[2];
		}else{
			id_set = id_get[1];	
		}
		
		qty = (float($('p_qty_'+id_set).value) - float($('n_qty_'+id_set).value));
		
	 	if (/^p_qty_/.test(e[i].id)){
			total_qty_p+=float(e[i].value);
			total_qty_p=float(round(total_qty_p, global_qty_decimal_points));
		}
	 	else if (/^n_qty_/.test(e[i].id)){
			total_qty_n+=float(e[i].value);
			total_qty_n=float(round(total_qty_n, global_qty_decimal_points));
		}
		else if (/^cost_/.test(e[i].id)){
			if($('p_qty_'+id_set).value != 0 || $('n_qty_'+id_set).value != 0){
				$('cost_'+id_set).value = round(qty * float($('unit_cost'+id_set).value),2);
				total_cost+=float(e[i].value);
				total_cost=float(round(total_cost, 2));
			}else{
				e[i].value = '';
			}
		}else if(/^total_selling_/.test(e[i].id)){
			if($('p_qty_'+id_set).value != 0 || $('n_qty_'+id_set).value != 0){
				$('total_selling_'+id_set).value = round(qty * float($('selling_price'+id_set).value),2);
				total_selling+=float(e[i].value);
				total_selling=float(round(total_selling,2));
			}else{
				e[i].value = '';
			}
		}
		
		// if found the following item having S/N
		if($('sn_item'+id_set) != undefined){
			// calculate the following branch qty from SN detail
			recalc_sn_used(id_set);
		}
	}

	$('total_qty_p').innerHTML=float(round(total_qty_p, global_qty_decimal_points));	
	$('total_qty_n').innerHTML=float(round(total_qty_n, global_qty_decimal_points));
	$('total_cost').innerHTML=round(total_cost,2);
	document.f_a['sheet_total_cost'].value = round(total_cost,2);
	
	$('total_selling').innerHTML=round(total_selling,2);
}


function check_date(obj){
	text=obj.value;
	if(text && isNumeric(text) && text.length=='6'){
		year=text.slice(0,2);
		month=text.slice(2,4);
		day=text.slice(4,6);
		year='20'+year;

		if(day<32 && month<13 && day>0 && month>0){
			obj.value=year+'-'+month+'-'+day;
		}
		else{
			alert('Invalid day/month format.');
			obj.value=date_now;
			obj.focus();
		}
		

		upper_lower_limit(obj);
		obj.focus();
	}

	

	else if (text && text.length=='10'){

		upper_lower_limit(obj);
		obj.focus();
	}

	else {
		alert('Please provide the valid format.');
		obj.value=date_now;
		obj.focus();
	}

}

function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) 
  	return false;
  return true;
}

function do_save(){
	// Disable all submit button
	enable_submit_button(false);
	
    if (check_login()) {
        document.f_a.target = "";
        document.f_a.a.value='save';
        if(check_a()){
			document.f_a.submit();
		}else{
			// Disable all submit button
			enable_submit_button(true);
		}
    }else{
		// Disable all submit button
		enable_submit_button(true);
	}
}

function enable_submit_button(is_enable){
	$$('#submitbtn input').each(function (obj,idx){
		obj.disabled = !is_enable;
	});	
}

function check_a(){
	if (empty(document.f_a.adjustment_date, "You must enter Adjustment Date")){
	    return false;
	}
	if (empty(document.f_a.adjustment_type, "You must enter Adjustment Type")){
	    return false;
	}
	return true;	
}

function do_confirm(){
    if (check_login()) {
        if (check_a() && confirm('Finalise Adjustment and submit for approval?')){
            document.f_a.a.value = "do_confirm";
            document.f_a.target = "";
            document.f_a.submit();
        }
    }
}

function do_delete(){
    if (check_login()) {
        document.f_a.target = "";
        document.f_a.reason.value = '';
        var p = prompt('Enter reason to Delete :');
        if (p.trim()=='' || p==null) return;
        document.f_a.reason.value = p;
        if (confirm('Delete this Adjustment?')){
            document.f_a.a.value = "delete";
            document.f_a.submit();
        }
    }
}

function change_branch(bid){
	var inputs = $('docs_items').getElementsBySelector('input.inp_selling_price');
	$('span_branch_change_loading').update(_loading_);
	
	var sku_item_id = [];
	
	for(var i=0; i<inputs.length; i++){
		sku_item_id.push(inputs[i].id.split(',')[1]);
	}
	
	ajax_request('adjustment.php',{
	    method: 'post',
		parameters: {
		    a: 'get_sku_selling_price',
		    branch_id: bid,
			'sku_item_id[]': sku_item_id
		},
		onComplete: function(e){
			$('span_branch_change_loading').update('');
			eval("var json = "+e.responseText);
			
			// update price
			for(var i=0; i<sku_item_id.length; i++){
			    // selling price
			    var sp = json[sku_item_id[i]]['selling_price'];
			    if(sp==undefined)   sp = 0;
				$('selling_price,'+sku_item_id[i]).value = sp;
				
				// stock balance
				var sb = json[sku_item_id[i]]['stock_balance'];
				if(sb==undefined)   sb = 0;
				$('stock_balance,'+sku_item_id[i]).value = sb;

				calc_total($('selling_price,'+sku_item_id[i]).title);
			}
		}
	});
}

function do_reset(){
    if (check_login()) {
        document.f_do_reset['reason'].value = '';
        var p = prompt('Enter reason to Reset :');
        if (p==null || p.trim()=='' ) return false;
        document.f_do_reset['reason'].value = p;

        if(!confirm('Are you sure to reset?'))  return false;

        document.f_do_reset.submit();
        return false;
    }
}

function refresh_tables(){
    if (check_login()) {
        document.f_a.a.value = "refresh";
        document.f_a.target = "";

        if(document.f_a['branch_id'].value>0){
            document.f_a.submit();
        }
    }
}

var active_search_box = "";
function add_grn_barcode_item(value){
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	$('grn_barcode').value='';
	do_ajax_add(Form.serialize(document.f_a)+'&a=ajax_add_grn_barcode_item&grn_barcode='+value);
	$('grn_barcode').focus();
}

function do_ajax_add(parms){
	ajax_request(phpself, {
		method:'post',
		parameters: parms,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			try{
				eval("var json = "+m.responseText);
			}catch(ex){
				alert(m.responseText);
				return;
			}

			for(var tr_key in json){
				if(json[tr_key]['error'] != undefined){
					alert(json[tr_key]['error']);
				}
			
				if(json[tr_key]['duplicate']){
					var item_id = json[tr_key]['existed_item_id'];
					var sku_item_code = json[tr_key]['existed_si_code'];
					var qty = json[tr_key]['qty'] || 0;
					var is_config_adj_type = json[tr_key]['is_config_adj_type'];
					var item_doc_allow_decimal = int(document.f_a['item_doc_allow_decimal['+item_id+']'].value);
					new_qty = float(prompt("Found existed SKU item '"+sku_item_code+"', please enter Qty:", qty));
					if(item_doc_allow_decimal){
						new_qty = round(new_qty, global_qty_decimal_points)
					}else{
						new_qty = int(new_qty);
					}
					
					/*if(is_config_adj_type == "-"){	// negative
						document.f_a['n_qty['+item_id+']'].value = float(document.f_a['n_qty['+item_id+']'].value) + float(new_qty);
					} 
					else if(is_config_adj_type =="+"){	// positive
						document.f_a['p_qty['+item_id+']'].value = float(document.f_a['p_qty['+item_id+']'].value) + float(new_qty);
					}else{
						if(new_qty > 0){
							document.f_a['p_qty['+item_id+']'].value = float(document.f_a['p_qty['+item_id+']'].value) + float(new_qty);
						}else{
							document.f_a['n_qty['+item_id+']'].value = float(document.f_a['n_qty['+item_id+']'].value) - float(new_qty);
						}
					}*/
					if(new_qty > 0){
						document.f_a['p_qty['+item_id+']'].value = float(document.f_a['p_qty['+item_id+']'].value) + float(new_qty);
					}else{
						document.f_a['n_qty['+item_id+']'].value = float(document.f_a['n_qty['+item_id+']'].value) - float(new_qty);
					}
					calc_total(item_id);
				}else new Insertion.Bottom($('docs_items'), json[tr_key]['rowdata']);
				
				if($('sn_details').innerHTML.trim() == '' && json[tr_key]['sn']){
	        		$('sn_dtl_icon').src = '/ui/collapse.gif';
	        		$('sn_title').show();
	        		$('sn_details').show();
	        	}
	    		if(json[tr_key]['sn']) new Insertion.Bottom($$('.sn_details').first(), json[tr_key]['sn']);
			}
		},	
		onComplete: function (m) {
			calc_total();
            reset_row();
			clear_autocomplete();
			sku_list.length = 0;
		},
	});
}

function toggle_sn(obj){
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		$('sn_details').show();
	}else{
		obj.src = '/ui/expand.gif';
		$('sn_details').hide();
	}
}

// recalculate the current SN used from qty
function recalc_sn_used(item_id){
	// calculate the total SN based in branch
	//var qty = $('row_qty'+item_id).innerHTML;
	var ttl_qty_used = 0;
	var ttl_bal_qty = 0;
	var sn_msg = '';

	var p_qty = document.f_a.elements['p_qty['+item_id+']'].value;
	var n_qty = document.f_a.elements['n_qty['+item_id+']'].value;

	var curr_qty = Math.abs(float(p_qty) - float(n_qty));

	//document.f_a.elements['b_sn_rcv_qty['+item_id+']'].value = curr_qty;
	var sn = document.f_a.elements['sn['+item_id+']'].value;
	var split_sn = sn.split('\n');
	var sn_count = 0;
	var sn_msg = ' ';

	for(var i=0; i<split_sn.length; i++){
		if(split_sn[i].trim() != "") sn_count++;
	}

	var bal_qty = float(curr_qty) - float(sn_count);

	if(curr_qty != bal_qty && bal_qty != 0){
		if(bal_qty >= 0) sn_msg = " ("+bal_qty+" qty remaining)";
		else sn_msg = " <b><font color=\"#ff0000\">(Over "+Math.abs(bal_qty)+" S/N)</font></b>";
	}
	
	sn_msg = round(curr_qty) + sn_msg;

	$('bal_qty_'+item_id).update(sn_msg);
	document.f_a.elements['sn_rcv_qty['+item_id+']'].value = curr_qty;
}

function do_proceed(form_name){
    if (check_login()) {
        if (check_a() && confirm('Are you sure?')){
            if(form_name == "f_a"){
                document.f_a.a.value = "do_confirm";
                document.f_a.target = "";
                document.f_a.skip_sn_error.value = 1;
                document.f_a.submit();
            }else if(form_name == "f_do_reset"){
                document.f_do_reset.a.value = "do_reset";
                document.f_do_reset.target = "";
                document.f_do_reset.skip_sn_error.value = 1;
                document.f_do_reset.submit();
            }
        }
    }
}

function adj_attachment_changed(){
    if (check_login()) {
        var filename = document.f_a['adj_attachment'].value;
        var file = document.f_a['adj_attachment'].files[0];
        if (!/\.jpg|\.jpeg|\.pdf|\.zip/i.test(filename))
        {
            alert("Selected file must be a valid JPEG image or PDF file or ZIP file");
            document.f_a['adj_attachment'].value = '';
            return;
        }else{
            if (file.size > 1048576){
                alert("File size too large. Maximum allowed size is 1MB.");
                document.f_a['adj_attachment'].value =  '';
                return;
            }
        }

        // show loading attachment
        $('span_adj_attachment_loading').show();
        document.f_a.target = "if_hidden";
        document.f_a.a.value='mark_adj_attachment';
        document.f_a.submit();
    }
}

function mark_adj_attachment_callback(adj_attachment_filename, tmp_adj_attachment_name){	
	document.f_a['adj_attachment'].value = '';
	var adj_attachment_list = $('adj_attachment_list');
	adj_attachment_list.insertAdjacentHTML('beforeend', '<span id="span_adj_attachment_details['+adj_attachment_filename+']"><span id="span_adj_attachment_name" class="link" title="Download" onClick="download_adj_attachment();">'+adj_attachment_filename+'</span><a href="javascript:void(cancel_adj_attachment(\''+adj_attachment_filename+'\'));"><img src=\"/ui/cancel.png\" align=\"absmiddle\" title=\"Cancel\" /></a><input type="hidden" name="adj_attachment_filename[]" value="'+adj_attachment_filename+'" /><input type="hidden" name="tmp_adj_attachment_name[]" value="'+tmp_adj_attachment_name+'" /></br></span>');
}

function upload_filename_deplicate(){
	document.f_a['adj_attachment'].value = '';
	alert('File name cannot duplicate.');
}

function if_hidden_loaded(){
	//console.log("if_hidden_loaded");
	// hide loading attachment
	if($('span_adj_attachment_loading') != undefined) $('span_adj_attachment_loading').hide();
}

function cancel_adj_attachment(name){
	var obj = $('span_adj_attachment_details['+name+']');
	if(obj){
		obj.remove();
	}
}

function show_upload_csv_popup(){
	if(document.f_a['adjustment_type'].value == ''){
		alert('Please select Adjustment Type before import adjustment item.');
		return false;
	}
	
	$('div_reload_csv_popup').update(_loading_);
	
	new Ajax.Request(phpself, {
		method: 'post',
		parameters: {
			'a': 'ajax_open_csv_popup',
			'branch_id': document.f_a['branch_id'].value,
			'id': document.f_a['id'].value,
			'timer_id': document.f_a['timer_id'].value,
			'is_config_adj_type': document.f_a['is_config_adj_type'].value
		},
		onComplete: function(msg){			    
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			$('div_reload_csv_popup').update('');
			
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['html']){ // success
					// Update html
					center_div($('div_upload_csv').show());
					$('div_upload_csv').update(ret['html']);
					curtain(true, 'curtain2');
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
}

download_adj_attachment = function(filename = null){
	if(filename == null){
		alert('Cannot download the attachment, as this document is not yet saved.');
		return;
	}
	
	// open new window to download
	window.open('adjustment.php?a=download_adj_attachment&branch_id='+branch_id+'&adj_id='+document.f_a['id'].value+'&adj_attachment_filename='+encodeURIComponent(filename));
}
</script>
{/literal}

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name="reason" value="{$reset_reason}">
<input type=hidden name="adjustment_date" value="{$form.adjustment_date}">
<input type=hidden name="skip_sn_error" value="0">
</form>

<div id=div_type_list style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid #000;margin: 0 0 0 0;height:200px;width:342px;overflow:auto;">
<ul id=tab>
{foreach item="type_item" from=$type_list}
	<li onclick="do_select_type(this, '{$type_item.adj_type}');" id="selected_type" title="{$type_item.name|upper}">{$type_item.name|upper}</li>
{/foreach}
</ul>
</div>

<h1>Adjustment {if $form.approved}({$form.report_prefix}{$form.id|string_format:"%05d"}){else}{if $form.id}(ID#{$form.id}){else}(New){/if}{/if}</h1>
<h3>Status:

{if $form.approved}
	Fully Approved
{elseif $form.status == 5}
	Deleted
{elseif $form.status == 4}
	Cancelled/Terminated
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 1}
	In Approval Cycle
{elseif $form.status == 0}
    Saved Adjustment
{/if}
</h3>

{if $form.wo_id}
<div class="stdframe" style="background-color:yellow;">
	<h4>You cannot edit the Adjustment created from Work Order.</h4>
	<h4>View Work Order: <a href="work_order.php?a=view&branch_id={$form.branch_id}&id={$form.wo_id}" target="_blank">{$form.wo_no}</a></h4>
</div>
<br />
{/if}

{if $form.cancelled_by}
<br>
<div class="stdframe" style="background:#fff">
<h4>Approval History</h4>
<p>
<img src=/ui/cancel.png border=0> {$form.cancelled} by {$form.user}<br>
Cancelled 
</p>
</div>
<br>
{/if}

{include file=approval_history.tpl}

<!-- S/N confirmation dialog -->
<div id="div_sn_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:auto;height:auto;display:none;border:2px solid #1569C7;background-color:#1569C7;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sn_dialog_header" style="border:2px ridge #1569C7;color:white;background-color:#1569C7;padding:2px;cursor:default;"><span style="float:left;" id="span_sn_dialog_header">S/N Confirmation</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sn_dialog_content" style="padding:2px;">
		{include file='adjustment.sn.confirmation.tpl'}
	</div>
</div>

{if $can_edit}
	<iframe width="500" height="500" style="display:none;" name="if_hidden" onload="if_hidden_loaded();"></iframe>
{/if}

<div id="div_upload_csv" class="curtain_popup" style="z-index:10000;width:auto;height:550px;display:none;max-height:550px;overflow:auto;min-width:550px;">
	{include file='adjustment.upload_csv.tpl'}
</div>

<form name="f_a" method="post" ENCTYPE="multipart/form-data">

{assign var=is_config_adj_type value=''}
{foreach item="type_item" from=$type_list}
	{if $form.adjustment_type == $type_item.name|upper}
		{assign var=is_config_adj_type value=$type_item.adj_type}
		<input type="hidden" name="is_config_adj_type" id="is_config_adj_type" value="{$type_item.adj_type}">
	{/if}
{/foreach}

{if !$is_config_adj_type}<input type="hidden" name="is_config_adj_type" id="is_config_adj_type" value="">{/if}

<input type=hidden name=a>
<input type=hidden name=reason value="">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name="timer_id" value="{$form.timer_id|default:$timer_id}" >
<input type=hidden name=approvals value={$form.approvals}>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type="hidden" name="sheet_total_cost" value="" />
<input type=hidden name="skip_sn_error" value="0">

<div class="stdframe" style="background:#fff;overflow:auto;">
<h4>General Information</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{else}
<div id=err></div>
{/if}

<table border=0 cellspacing=0 cellpadding=4>

<tr>
<th align=left width=120>Adjustment Date</th>
<td>
<input name="adjustment_date" id="added1" value="{$form.adjustment_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size=10  onchange="upper_lower_limit(this);"  maxlength=10  onclick="if(this.value)this.select();">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"> <span id="rq_img1"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
</td>
</tr>

<tr>
<th align=left width=120>Adjustment Type</th>
<td>
<input id="adjustment_type" name="adjustment_type" size=50 onchange="uc(this);" readOnly onkeyup="disable_field_bytype('');" value="{$form.adjustment_type}" maxlength="50">{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id))}{if $can_edit}<img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align=top onclick="show_type_option($('adjustment_type').value);">{/if}{/if}
 <span id="rq_img1"><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
<div id="type_2"></div>
</td>
</tr>

<tr>
<td><b>Department</b></td>
<td>
	<select name="dept_id" onchange="reset_sku_autocomplete();">
	{section name=i loop=$dept}
	<option value={$dept[i].id} {if $form.dept_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
	{/section}
	</select>
</td>
</tr>

<tr>
<td><b>Remark</b></td>
<td>
<textarea rows="2" cols="68" name=remark onchange="uc(this);">{$form.remark}</textarea>
</td>
</tr>

{assign var=need_refresh_button value=1}
{if $config.adjustment_branch_selection and $config.single_server_mode}
<tr>
    <td><b>Select Branch</b></td>
	<td>
	    <input type="hidden" name="open_branch_id" value="{if $smarty.request.open_branch_id}{$smarty.request.open_branch_id}{else}{$form.branch_id|default:$sessioninfo.branch_id}{/if}" />
	    <input type="hidden" name="default_branch_id" value="{$form.default_branch_id|default:$form.branch_id|default:$sessioninfo.branch_id}" />
	    {if !$form.branch_id}
	    <select name="branch_id" onChange="change_branch(this.value);">
	    	{foreach from=$branches key=bid item=b}
	    	    {if !$branch_group.have_group.$bid and $b.active}
	    	    	<option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
	    	    {/if}
	    	{/foreach}
	    	{foreach from=$branch_group.header key=bgid item=bg}
	    	    <optgroup label="{$bg.code}">
	    	        {foreach from=$branch_group.items.$bgid key=bid item=b}
	    	            {if $branches.$bid.active}
	    	            	<option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
	    	            {/if}
	    	        {/foreach}
	    	    </optgroup>
	    	{/foreach}
	    </select>
	    {else}
	    	<input type="hidden" name="branch_id" value="{$form.branch_id}">
	    	{assign var=need_refresh_button value=0}
	        {$branches[$form.branch_id].code} - {$branches[$form.branch_id].description}
		{/if} <span id="span_branch_change_loading"></span>
	</td>
</tr>
{else}
	<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
    <input type="hidden" name="open_branch_id" value="{if $smarty.request.open_branch_id}{$smarty.request.open_branch_id}{else}{$form.branch_id|default:$sessioninfo.branch_id}{/if}" />
	<input type="hidden" name="default_branch_id" value="{$form.default_branch_id|default:$form.branch_id|default:$sessioninfo.branch_id}" />
	
	{assign var=need_refresh_button value=0}
{/if}

{if !$need_refresh_button}
	<tr height="40" valign="top">
		<td><b>Attachment</b>
			[<a href="javascript:void(alert('Accept .jpg .pdf .zip\nAttachment maximum file size 1 MB'))">?</a>]
		</td>
		<td>
			<div id="adj_attachment_list">
			{foreach from=$form.adj_attachment_filename key=keys item=adj_attachment_filename}
				{if $adj_attachment_filename}
				<span id="span_adj_attachment_details[{$adj_attachment_filename}]" >
					<span id="span_adj_attachment_name" class="link" title="Download" onClick="download_adj_attachment('{$adj_attachment_filename}');">{$adj_attachment_filename}</span>
					{if $can_edit}
						<a href="javascript:void(cancel_adj_attachment('{$adj_attachment_filename}'));"><img src="/ui/cancel.png" align="absmiddle" title="Cancel" /></a>
					{/if}
					<input type="hidden" name="adj_attachment_filename[]" value="{$adj_attachment_filename}"/>
					<input type="hidden" name="tmp_adj_attachment_name[]" value="{$form.tmp_adj_attachment_name[$keys]}" />
					<br>
				</span>
				{/if}
			{/foreach}
			</div>
			<span id="span_adj_attachment_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Uploading...</span>
			{if $can_edit}
			<span id="span_adj_attachment_select">
				<input type="file" name="adj_attachment" onChange="adj_attachment_changed();" />
			</span>
			{/if}
			<span id="span_adj_attachment_error" style="{if !$adj_attachment_err_name}display:none;{/if}">
				<span id="span_adj_attachment_err_name" style="background:yellow;color:red;">The file size of {$adj_attachment_err_name} exceeds the limit allowed.</span>
			</span>
		</td>
	</tr>
{/if}

{if $can_edit && $form.branch_id}
<tr>
	<td><input type="button" value="Add items by CSV" onclick="show_upload_csv_popup();"></td>
	<td><span id="div_reload_csv_popup"></span></td>
</tr>
{/if}

<tr>
	<td>&nbsp;</td>
	<td>
		<div id=srefresh style="{if !$need_refresh_button}display:none;{/if}padding-top:10px;">
			<input id=refresh_btn type=button onclick="void(refresh_tables())" style="font-size:1.5em; color:#fff; background:#091" value="click here to continue">
			Please confirm <b>adjustment branch</b>, branch cannot be change after continue.
		</div>
	</td>
</tr>
</table>
</div>



<br>


{if $errm.item}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.item item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

<div style="{if $need_refresh_button}display:none;{/if}">

<div style="overflow:auto;">
<table width=100% id=tbl_item style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=1>
<thead class=small>
<tr height=24 bgcolor=#ffffff>
	<th rowspan=2>#</th>
	<th nowrap rowspan=2 width=120>ARMS Code</th>
	<th nowrap rowspan=2 width=150>Article / MCode</th>
	<th nowrap rowspan=2 width=40%>SKU Description</th>
	<th nowrap rowspan=2>Latest Stock<br />Balance</th>
	<th nowrap rowspan=2>Selling Price</th>
	<th nowrap rowspan=2 {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Unit Cost</th>
	<th nowrap colspan=2>Adjustment Qty</th>
	<th nowrap rowspan=2 {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Total Cost</th>
	<th nowrap rowspan=2>Total Selling</th>
</tr>
<tr bgcolor=#ffffff>
	<th nowrap>Positive (+) </th>
	<th nowrap>Negative (-) </th>
</tr>
</thead>

<tbody id="docs_items">
{foreach from=$adjust_items item=item name=fitem}
<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" id="titem{$item.id}" {if $smarty.request.highlight_item_id eq $item.sku_item_id}class=highlight_row{/if}>
{include file=adjustment.new.row.tpl}
</tr>
{/foreach}
</tbody>

<tfoot id="tbl_footer">
<tr height=24 bgcolor=#ffffff>
	<th colspan="{if !$sessioninfo.privilege.SHOW_COST}6{else}7{/if}" class="r">Total</th>
	<th class="r" id="total_qty_p"></th>
	<th class="r" id="total_qty_n"></th>
	<th class="r" id="total_cost" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}></th>
	<th class="r" id="total_selling"></th>
</tr>
</tfoot>

</table>
</div>

{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id))}
<div style="background:#ddd;border:1px solid #999;">
{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 allow_edit=1}
<hr />
{include file='scan_barcode_autocomplete.tpl' no_need_table=1}
<br />
<br />
</div>
{/if}
<br>

</div>

{assign var=have_sn value=0}
<span id="sn_title" {if (count($errm.sn) eq 0 && $item) or !$item}style="display:none;"{/if}><br /><h3>Serial No Details <img src="{if count($errm.sn) eq 0}/ui/expand.gif{else}/ui/collapse.gif{/if}" id="sn_dtl_icon" onclick="toggle_sn(this);" align=absmiddle></h3></span>
<div class="sn_details" id="sn_details" {if count($errm.sn) eq 0}style="display:none;"{/if}>
	{foreach from=$adjust_items item=item name=fitem}
		{if $item.serial_no || $item.have_sn}
			{include file="adjustment.sn.new.tpl"}
			{assign var=have_sn value=1}
		{/if}
	{/foreach}	
</div>

</form>

{if $form.approval_screen}
<form name="f_b" method=post>
<input type=hidden name=a value="approve">
<input type=hidden name=comment value="">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=branch_id value="{$form.branch_id}">
<input type=hidden name=approvals value={$form.approvals}>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type=hidden name=curr_date value="{$form.adjustment_date}">
{if $config.adjustment_branch_selection}
	<input type="hidden" name="original_approval_bid" value="{$smarty.request.original_approval_bid|default:$original_approval_bid}">
{/if}
{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}
</form>
{/if}

<p id=submitbtn align=center>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
	<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
	<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
	<input type=button value="Terminate" style="background-color:#900; color:#fff;" onclick="do_cancel()">
{else}
	{if $smarty.request.a eq 'open'}	
		{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id)) and !$form.approval_screen and !$form.approved}
			<input name=bsubmit class="btn btn-success" type=button value="Save & Close" onclick="do_save()" >
		{/if}

		{if (!$form.id || $form.status>0 || $form.approved) and !$form.approval_screen }
			<input class="btn btn-error" type=button value="Close" onclick="document.location='/adjustment.php'">
		{/if}

		{if $form.id and !$form.approved and !$form.status}
			<input class="btn btn-error" type=button value="Delete" onclick="do_delete()">
		{/if}

		{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id)) and !$form.approval_screen and !$form.approved}
			<input type=button class="btn btn-primary" value="Confirm" onclick="do_confirm()">
		{/if}
	{else}
		{if $form.approved and ($sessioninfo.level>=$config.doc_reset_level) and $form.module_type ne 'work_order'}
			<input type=button class="btn btn-error" value="Reset" onclick="do_reset();">
		{/if}
		
		<input type=button class="btn btn-error" value="Close" onclick="document.location='/adjustment.php'">
	{/if}
{/if}




</p>
{if !$form.approval_screen}
{include file=footer.tpl}
{/if}

<script>

{if !$form.approval_screen}
	{if $smarty.request.a ne 'open'}
		// view mode
		Form.disable(document.f_a);
		$('t_added1').hide();
	{elseif $form.status>0}
		{if $form.status eq '2' && $form.user_id==$sessioninfo.id}
			// edit rejected
			Form.enable(document.f_a);
		{else}
			Form.disable(document.f_a);
		{/if}
		$('t_added1').hide();
	{/if}
	init_calendar();
	//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku.
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
{else}
	Form.disable(document.f_a);
	$('t_added1').hide();
{/if}

{if $have_sn}
	$("sn_title").show();
{/if}

{if $sn_error}
	showdiv("div_sn_dialog");
	center_div("div_sn_dialog");
	new Draggable('div_sn_dialog');
	curtain(true);
{/if}

calc_total();

</script>

