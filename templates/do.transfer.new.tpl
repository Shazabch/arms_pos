{*
REVISON HISTORY
+++++++++++++++
7/16/2009 3:23:32 PM Andy
- Add branch stock balance

8/12/2009 12:32:28 PM Andy
- Module modify, now all default DO is type 'trasnfer'

10/30/2009 4:24:00 PM yinsee
- add missing delivery-to list new new DO (why??)

11/5/2009 4:50:25 PM Andy
- add invoice discount. per sheet and per item

11/10/2009 10:23:04 AM Andy
- Hide "Close" button, user must click on "Save & Close"

11/11/2009 3:33:19 PM edward
- add show status

11/16/2009 10:22:17 AM Andy
- fix no invoice javascript error

11/19/2009 10:38:22 AM Andy
- check create from PO if option is 3, let user to select multiple branch
- add split legend before the button

11/25/2009 9:42:34 AM Andy
- Fix "DO create from PO" system does not auto select delivery branch if branches count less than 10

2/22/2010 12:09:40 PM Andy
- Block user to save the DO while price changing is in process
- Fix price indicator bugs

5/10/2010 3:09:25 PM Andy
- Add DO Markup.
- Fix DO stock balance show "undefined" bugs.
- Fix if got invoice discount but no item will have javascript error.

5/17/2010 1:50:22 PM Andy
- Add DO auto split by price type can automatically insert DO Discount base on branch trade discount. (need config)
- DO Markup can now be use as DO Discount as well.


5/31/2010 4:16:54 PM Alex
- add function upper_lower_limit()

6/17/2010 3:10:10 PM Justin
- Added a new button called "Add Open Item" to allow user to execute to create a Open Item under DO Cash Sales while editing/adding a particular DO.
- Added config for DO - $config['do_add_open_item']. This config will enable/disable the Open Item feature on DO.
- Added checking function to check incoming transaction either is SKU or Open Item in JS.

6/22/2010 12:25:06 PM Justin
- Added a button to call out Matrix table.
- Added Matrix table to add multiple SKU Item at a one time.
- Added pop up screen for the Matrix table.

8/13/2010 10:56:17 AM Andy
- Transfer, Cash Sales and Credit Sales DO can use replacement item. (Need Config)
- "Add Open Item" button change color and move to right.

1/12/2011 5:36:28 PM Justin
- Added a new parameter to be sent to ajax search sku to retrieve info of batch no.

1/26/2011 4:00:54 PM Justin
- Added serial number enhancements.
- Added new include of js file (do.js) to use same function between transfer, cash and credit sales modules.
- Enhanced the ajax call and insertion for single row insert by using JSON.

3/9/2011 5:07:51 PM Justin
- Added the checking of pcs and ctn that cannot exceeds 99,999.

5/27/2011 5:30:19 PM Justin
- Added checkbox and textarea onto DO Transfer.
  * both of these fields available while found it is deliver to only one branch.
  * system will pre-load one set of address and put in JS for Deliver To where saved from Masterfile Branch and auto place on Address Deliver To whenever user change the branch.
- Added new function to hidden or unhidden the Address Deliver To while user check/uncheck the Use Address Deliver To.
- Both of these fields visible while it is only deliver to one branch.

6/8/2011 4:47:40 PM  Justin
- Added Exchange Rate field.
  * Required config "consignment_multiple_currency".
  * load from Consignment Forex.
  * All exchange is place on array in JS and replace current one whenever user select different branches.
  * Added an ability to show/hide foreign price/amount fields base on branch currency code as if it is not roreign currency code.
- Added new function to monitor and maintain all foreign cost/amount base on the price indicate and exchange rate change by user.

6/22/2011 11:01:28 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

6/23/2011 10:19:01 AM Alex
- add span and class nowrap for options

7/5/2011 1:41:39 PM Justin
- Modified the Deliver To can be use by default without need of consignment modules.

8/5/2011 4:17:40 PM Andy
- Change DO Invoice Discount format.

8/19/2011 2:59:21 PM Justin
- Added clear search SKU engine feature.

9/19/2011 3:49:34 PM Andy
- Add show item error.

10/3/2011 5:55:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/21/2011 3:07:33 PM Justin
- Fixed the bugs where system cannot re-calculate the Foreign Amount.

2/29/2012 11:18:05 AM Alex
- add options for scan GRN Barcode

3/30/2012 11:39:32 AM Justin
- Added new feature to prompt a confirmation when user about to leave the page.

4/6/2012 4:47:48 PM Alex
- add user notification list on change_user_list() and change_user_list_process()

4/25/2012 6:08:11 PM Alex
- edit change_branch_id() function to change price type while change date

4/30/2012 5:31:29 PM Alex
- disable the certain element in form while javascript enable whole form

5/8/2012 4:47:23 PM Justin
- Fixed bugs of where system did not show error message while user intends to deliver to same branch.

7/23/2012 4:21 PM Andy
- Add transfer DO when change deliver branch will prompt user whether need to reload cost price.

7/26/2012 11:32 AM Andy
- Add will check user department privilege when add search/add item (need config)

7/27/2012 5:27 PM Andy
- Fix price indicator bug if user have no cost privilege.

8/7/2012 5:56 PM Justin
- Enhanced to show error message when found error during scan barcode.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

6/23/2014 5:23 PM Justin
- Enhanced to have new feature that can add Serial No by range.

7/9/2014 11:33 AM Justin
- Enhanced to have new feature that can change customer info from S/N Details at once from new menu.
- Enhanced to have new ability that can skip S/N data for DO Transfer by branch(need config).

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

2/25/2015 9:49 AM Justin
- Bug fixed on consignment customer not able change deliver from while create new DO.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.
- Create a new JS class DO_MODULE and merge some JS.

5/8/2015 2:32 PM Andy
- Enhanced to put the default value for do_type.
- Change the discount label to always same as discount format.
- Remove the foreign sheet discount label.

6/4/2015 11:17 AM Andy
- Fix foreign calculation.

6/22/2015 4:14 PM Andy
- Fix interbranch GST bugs.

7/30/2015 2:27 PM Joo Chia
- Add in feature check/uncheck branch by branch group or all branch.
- Fix if no branch selected, hide continue button.

11/17/2015 6:03 PM Andy
- Enhanced to capture branch group array into js.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

2/16/2016 3:40 PM Andy
- Enhance the row calculate function to can skip calculate all.

3/8/2016 2:23 PM Qiu Ying
- Fix Deliver From become enabled = true
- Fix if Deliver To no selected, then hide new sheet
- Put added timestamp above DO Date
- Fix get latest is_under_gst value

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.
- Bug fixed on stock balance calculate wrong when changed branch on "Deliver To".

9/14/2016 10:58 AM Andy
- Fixed price indicator cannot tick selling price as default.

2/27/2017 9:14 AM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

3/29/2017 4:20 PM Justin
- Enhanced to allow user to key in extra qty instead of prompt error message while found item is existed.
- Enhanced to have new privilege checking for user to reset DO.

4/7/2017 6:09 PM Justin
- Enhanced to have checking for invalid and negative quantity, prompt error message to the user.

4/20/2017 9:20 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/29/2017 3:44 PM Justin
- Enhanced to update DO items to have BOM information.

10/11/2018 2:52 PM Andy
- Fixed if GST Settings is inactive but users change the price indicator, system will become have gst back.

11/22/2018 10:53 AM Justin
- Updated do.js to version 1.

5/30/2019 11:45 PM Andy
- Enhanced to show Related DO.

5/31/2019 10:18 AM Andy
- Enhanced to show branch code in Related DO link.

04/20/2020 06:02 PM Sheila
- Modified layout to compatible with new UI.

*}

{assign var=time_value value=1000000000}
{assign var=do_type value='transfer'}

{if $form.open_info || $form.deliver_branch || $form.do_branch_id}
	{assign var=have_select_delivery value=1}
{else}
	{assign var=have_select_delivery value=0}
{/if}

{if !$form.approval_screen}
	{include file=header.tpl}
{else}
	<hr noshade size=2>
{/if}

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="js/do.js?v=1"></script>

<style>
.sh{
    background-color:#ff9;
}

.stdframe.active{
 	background-color:#fea;
	border: 1px solid #f93;
}

td.xc{
	border-bottom: 1px dashed #aaa;
}

.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}

.addbutton{
	background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;
}

input.inp_qform_bg{
	background-color:#CAFF70;
	color:#000;
	border: 2px outset grey;
}

input.inp_qform_bg_selected{
	border: 2px inset grey !important;
}

tr.tr_qform_branch_row_highlight{
	background-color:#CAFF70;
}
</style>
{/literal}

{assign var=show_discount value=$config.do_transfer_have_discount}
<script type="text/javascript">
{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

var active_search_box = 'ajax_autocomplete';
var do_use_rcv_pcs = int('{$config.do_use_rcv_pcs}');
var current_branch_code = '{$BRANCH_CODE}';
var do_auto_split_by_price_type = int('{$config.do_auto_split_by_price_type}');
var consignment_modules = int('{$config.consignment_modules}');
var masterfile_branch_region = '{$config.masterfile_branch_region}';
var consignment_multiple_currency = '{$config.consignment_multiple_currency}';
var show_discount = int('{$show_discount}');
var current_total_inv_amt = 0;
var do_type = '{$smarty.request.do_type|default:$form.do_type}';
var create_type = '{$form.create_type}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var needCheckExit = true;
var must_check_dept = int('{$config.do_must_check_dept}');
var do_transfer_skip_sn = '{$config.do_transfer_skip_sn}';
var show_parent_stock_balance = int('{$config.show_parent_stock_balance}');
var currency_symbol = '{$config.arms_currency.symbol}';
var branch_id_code = [];
var branch_deliver_to = [];
var branch_exchange_rate = [];
var branch_currency_code = [];
var sku_bom_additional_type = int('{$config.sku_bom_additional_type}');

{foreach from=$all_branch item=b}
    branch_id_code['{$b.id}'] = '{$b.code}'
	
	{if $config.consignment_modules && $config.masterfile_branch_region}
		{if $form.do_branch_id eq $b.id}
			{assign var=deliver_to value=$form.address_deliver_to}
		{else}
			{assign var=deliver_to value=$b.deliver_to}
		{/if}
		branch_deliver_to['{$b.id}'] = "{$deliver_to|escape:javascript}";
		
		{if is_array($config.consignment_multiple_currency)}
			{if $form.do_branch_id eq $b.id && $form.exchange_rate && $form.exchange_rate ne '1'}
				{assign var=exchange_rate value=$form.exchange_rate}
			{else}
				{assign var=exchange_rate value=$b.exchange_rate}
			{/if}
			branch_currency_code['{$b.id}'] = "{$b.currency_code|escape:javascript}";
			branch_exchange_rate['{$b.id}'] = "{$exchange_rate|escape:javascript}";
		{/if}
	{/if}
{/foreach}

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var temp_is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');

var branches_group_items = [];
{foreach from=$branches_group.items key=bgid item=branch_list}
	if(!branches_group_items['{$bgid}'])	branches_group_items['{$bgid}'] = [];
	{foreach from=$branch_list key=bid item=b}
		branches_group_items['{$bgid}'].push('{$bid}');
	{/foreach}
{/foreach}

{literal}
function do_save(){
	document.f_a.a.value='save';
	document.f_a.target = "";
	if(check_a() && chk_branch() && chk_open_info()){
	
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		ajax_request('do.php',{
			method: 'post',
			parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
					needCheckExit = false;
					document.f_a.submit();
					return;
				}
				else {
					Element.hide('wait_popup');
					curtain(false,'curtain2');
					alert(e.responseText.trim());
					return;
				}
			}
		});
	
	}
}

function chk_branch(){
	if($('delivery_branches').style.display!='none'){
        if(document.f_a['branch_id'].value==''){
			alert('Please Select Branch Deliver From');
			return false;
		}else if(document.f_a['do_branch_id']!=undefined){
		    if(document.f_a['do_branch_id'].value==''){
				alert('Please Select Branch Deliver To');
				return false;
			}else if(document.f_a['branch_id'].value==document.f_a['do_branch_id'].value){
                alert('Cannot Delivery to Same Branch');
				return false;
			}
		}else if($('div_multi_branch_selected')){
			var b = document.f_a['deliver_branch[]'];
			
			var got_gst = -1;
			var got_check = 0;
			for(var i=0; i<b.length; i++){
				if(b[i].checked == true && b[i].value==document.f_a['branch_id'].value){
					alert('Cannot Delivery to Same Branch');
					return false;
				}
				
				if(b[i].checked && enable_gst && !consignment_modules){
					var got_gst_interbranch = int($(b[i]).readAttribute('got_gst_interbranch'));
					if(got_gst >= 0 && got_gst != got_gst_interbranch){
						alert('Cannot mixed GST and NON-GST branch');
						return false;
					}
					got_gst = got_gst_interbranch;
				}
				
				if(b[i].checked){
					got_check++;
				}
			}
			
			if (got_check <= 0){
				return false;
			}
		}
	}
	return true;
}

function do_confirm(){
	if(check_a() && chk_branch() && chk_open_info() && chk_rcv_qty()){
		if (confirm('Finalise DO and submit for approval?')){
		
			center_div('wait_popup');
			curtain(true,'curtain2');
			Element.show('wait_popup');
			
			ajax_request('do.php',{
				method: 'post',
				parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
				onComplete: function(e){
					if (e.responseText.trim() == 'OK') {
						needCheckExit = false;
						document.f_a.a.value = "confirm";
						document.f_a.target = "";
						document.f_a.submit();
						return;
					}
					else {
						Element.hide('wait_popup');
						curtain(false,'curtain2');
						alert(e.responseText.trim());
						return;
					}
				}
			});
		}	
	}
}

function chk_rcv_qty(){
	if(do_use_rcv_pcs){
        var total_qty = $('total_qty').value;
		var total_rcv = $('total_rcv').value;

		if(total_qty!=total_rcv){
			alert('Total Qty not match with Total Rcv Qty.');
			return false;
		}
	}
	
	return true;
}

function check_a(){
	if (empty(document.f_a.do_date, "You must enter DO Date")){
	    return false;
	}
	return true;
}

function ajax_add(parms){
	if(masterfile_branch_region && consignment_multiple_currency) parms += "&currency_code="+branch_currency_code[document.f_a['do_branch_id'].value];

	// remove the highlight class
	var td_bom_ref_num_list = $$('#do_items tr.highlight_row');
	for(var i=0; i<td_bom_ref_num_list.length; i++){
		var tmp_tr_ele = td_bom_ref_num_list[i];
		$(tmp_tr_ele).removeClassName('highlight_row');
	}
	
	ajax_request("do.php",{
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
				if(json[tr_key]['item_existed'] == 1 && json[tr_key]['item_id'] > 0){
					var item_id = json[tr_key]['item_id'];
					if(json[tr_key]['bom_ref_num'] > 0){ // it is duplicated and match with bom package items
						var bom_ref_num = json[tr_key]['bom_ref_num'];
						alert("The following BOM package is existed, please refer to highlighted area.");
						
						var td_bom_ref_num_list = $$('#do_items td.td_bom_ref_num-'+bom_ref_num);
						
						for(var i=0; i<td_bom_ref_num_list.length; i++){
							var tmp_tr_ele = td_bom_ref_num_list[i].parentNode;
							$(tmp_tr_ele).addClassName('highlight_row');
						}
						return;
					}else if(document.f_a['do_branch_id']!=undefined){ // it is single branch
						var si_code = document.f_a['sku_item_code['+item_id+']'].value;
						var extra_qty = prompt("SKU item '"+si_code+"' existed, please enter extra qty:\t\nNOTE: Negative qty is not allowed.");

						if(extra_qty != null && !isNumeric(extra_qty)){
							alert("Invalid qty.");
							return;
						}

						if(document.f_a['inp_item_doc_allow_decimal['+item_id+']'].value != 0){
							extra_qty = float(round(extra_qty, global_qty_decimal_points));
						}else extra_qty = float(round(extra_qty, 0));
						
						if(extra_qty > 0) document.f_a['qty_pcs['+item_id+']'].value = float(document.f_a['qty_pcs['+item_id+']'].value) + float(extra_qty);
						else if(extra_qty < 0) alert("Negative qty is not allowed.");
					}else{ // it is multi branch
						$('inp_item_id').value = item_id;
						DO_MODULE.open_branch_qty_form(item_id);
					}
					return;
				}
			
				if(json[tr_key]['error'] != undefined){
					alert(json[tr_key]['error']);
				}

				if(json[tr_key]['bn_notify'] != undefined){
					if(!confirm(json[tr_key]['bn_notify'])) break;
				}

	    		if(json[tr_key]['sn']){
					new Insertion.Bottom($$('.sn_details').first(), json[tr_key]['sn']);
					toggle_sn_details();
	        	}
	    		new Insertion.Bottom($('do_items'),json[tr_key]['rowdata']);
			}
		},
		onComplete: function (m) {
			calc_all_items();
            reset_row();
		},
	});
}

function add_grn_barcode_item(value){
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	$('grn_barcode').value='';
	ajax_add(Form.serialize(document.f_a)+'&a=ajax_add_grn_barcode_item&grn_barcode='+value);
	active_search_box = 'grn_barocde';
}

function add_item(oi, bid){
	if (int(document.f_a.sku_item_id.value)==0 && !oi){
	    alert('No item selected');
		$('autocomplete_sku').value = '';
	    return false;
	}
	
	if(!chk_branch()){
		return false;
	}

	ajax_add(Form.serialize(document.f_a)+'&a=ajax_add_item&show_discount='+show_discount+'&oi='+oi+'&bid='+bid);	
	active_search_box = 'ajax_autocomplete';
	clear_autocomplete();

}

function add_size_color(){
    if (int(document.f_a.sku_item_id.value)==0){
		alert('No item selected');
		$('autocomplete_sku').value = '';
	    return false;
    }
	$('color_size_matrix').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	curtain(true);
	showdiv('color_size_matrix');
	center_div('color_size_matrix');

	new Ajax.Updater('color_size_matrix','ajax_sku_popups.php',{
		    method:'post',
		    parameters: Form.serialize(document.f_a)+'&a=ajax_color_size_matrix&type=do',
		    evalScripts: true
	});
}

function cancel_matrix(){
	curtain(false);
	hidediv('color_size_matrix');
	hidediv('load_color_size_matrix');
}

function do_matrix(){
	hidediv('color_size_matrix');
	$('load_color_size_matrix').innerHTML = '<img src=ui/clock.gif align=absmiddle> Insertion in progress......';
	showdiv('load_color_size_matrix');
	center_div('load_color_size_matrix');

	ajax_add(Form.serialize(document.f_t)+Form.serialize(document.f_a)+'&a=ajax_add_size_color&show_discount='+show_discount);
	active_search_box = 'ajax_autocomplete';
	cancel_matrix();
	reset_sku_autocomplete();

}

function row_recalc(item_id,branch, skip_calc_all){
	if(!item_id)	return;
	
	if(skip_calc_all == undefined){
		calc_all_items();
	}
	
	// if found the following item having S/N
	if($('sn_item'+item_id) != undefined){
		// calculate the following branch qty from SN detail
		if(branch && branch != undefined){ // calculate for specific branch (when user change ctn or pcs)
			recalc_sn_used(item_id, branch);
		}else{ // calculate for all branches (when user change uom)
			var del_b = document.f_a.elements["deliver_branch[]"];
			if(typeof(del_b)!='undefined'){
		        for(var i=0; i<del_b.length; i++){
				    if(del_b[i].checked) recalc_sn_used(item_id, del_b[i].value);
				}
			}else{ // calculate for that sku item
				recalc_sn_used(item_id, document.f_a.do_branch_id.value);
			}
		}
	}
	
	//$('total_rcv').value = float(round(total_rcv, global_qty_decimal_points));
	if(consignment_modules && masterfile_branch_region && consignment_multiple_currency && document.f_a['exchange_rate'].value > 1) foreign_variable_handler(true);
}

function do_cancel(){
    if (check_login()) {
        if (confirm('Cancel this DO?')){
            needCheckExit = false;
            document.f_a.a.value='cancel';
            document.f_a.target = "";
            document.f_a.submit();
        }
    }
}

function do_delete(){
    if (check_login()) {
        document.f_a.reason.value = '';
        var p = prompt('Enter reason to Delete :');
        if (p==null || p.trim()=='') return;
        document.f_a.reason.value = p;
        if (confirm('Delete this DO?')){
            needCheckExit = false;
            document.f_a.a.value = "delete";
            document.f_a.submit();
        }
    }
}

var sku_autocomplete = undefined;
function reset_sku_autocomplete(){
	var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(document.f_a.search_type)+"&from_do=1&must_check_dept="+must_check_dept;
	if (sku_autocomplete != undefined){
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
		}});
	}
	$('autocomplete_sku').focus();
	clear_autocomplete();
}

clear_autocomplete = function(){
	document.f_a['sku_item_id'].value = '';
	document.f_a['sku_item_code'].value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
	if($('inp_autocomplete_qty'))   $('inp_autocomplete_qty').value = '';
}

function delete_item(id){
	var confirm_str = 'Remove this SKU from DO?';
	var bom_ref_num = '';
	
	if(sku_bom_additional_type){
		if(document.f_a['bom_ref_num['+id+']'] && document.f_a['bom_ref_num['+id+']'].value.trim() != ''){
			bom_ref_num = document.f_a['bom_ref_num['+id+']'].value.trim();
			
			confirm_str += '\nThis SKU is BOM Package SKU, all related SKU will be delete together';
		}
	}
	
 	if (!confirm(confirm_str)) return;
	
	var delete_id_list = [];
	 if(sku_bom_additional_type && bom_ref_num){
 		$$('#do_items td.td_bom_ref_num-'+bom_ref_num).each(function(td){
 			var tmp_id = td.title;
 			delete_id_list.push(tmp_id);
 		});
 	}else{
 		delete_id_list.push(id);
 	}
	
 	bid = document.f_a.branch_id.value;
	var params = {
		'a': 'ajax_delete_item',
 		'delete_id_list[]': delete_id_list,
		'branch_id': bid
 	};
	
	ajax_request("do.php",{
		method:'post',
		parameters: params,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			for(var i=0; i<delete_id_list.length; i++){
				var tmp_id = delete_id_list[i];
				
				if($('titem'+tmp_id)) Element.remove('titem'+tmp_id);
	            if($('sn_item'+tmp_id) != undefined){
					Element.remove('sn_item'+tmp_id); // remove if got S/N
					toggle_sn_details();
				}
			}
			calc_all_items();
			reset_row();
    	}
	});
}

function reset_row(){
	var e = $('do_items').getElementsByClassName('no');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^no_');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no_'+(i+1);
		}
	}

	$(active_search_box).value='';
	$(active_search_box).focus();
}


function calc_all_items(){
	DO_MODULE.recalc_all_items();
}

function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});   
}

function select_type(val){	
	if(val=='2'){
		$('delivery_open').style.display='';
		$('delivery_branches').style.display='none';
		active_btn();
	}
	else{
		$('oi_address').value='';
		$('oi_name').value='';
		$('delivery_branches').style.display='';
		$('delivery_open').style.display='none';
		active_btn();
	}	
}

function active_btn(){
	if(!chk_branch()){
		$('srefresh').style.display='none';
		$('refresh_btn').disabled=true;
		$('refresh_btn').hide();
		return false;
	}

	get_label_indicator();
	if ($('new_sheets') != undefined){
		$('new_sheets').style.display='none';
		$('tbl_sku').style.display='none';
		$('submitbtn').style.display='none';
		$('sn_title').style.display='none';
		$('sn_details').style.display='none';
	}
	
	// enable the refresh button
	$('srefresh').style.display='';
	$('refresh_btn').disabled=false;
	$('refresh_btn').show();
}

function chk_open_info(){
    if(document.f_a['branch_id'].value==''){
		alert('Please Select Branch Deliver From');
		return false;
	}
		
	if($('delivery_open')!=undefined && $('delivery_open').style.display!='none'){
		if (empty($('oi_name'), "You must enter Company Name")){
		    return false;
		}
		if (empty($('oi_address'), "You must enter Company Address")){
		    return false;
		}
	}
	return true;
}

var prev_price_indicate;
function refresh_cost(obj, no_need_confirm)
{
	if (no_need_confirm || confirm('Change Price Indicate?')){
        if (branch_gst_register_no != '') {
            var isvalid = check_is_under_gst();
        
            if (isvalid) {
                window.is_under_gst = 1;
                document.f_a['is_under_gst'].value = 1;
            }else{
                window.is_under_gst = 0;
                document.f_a['is_under_gst'].value = 0;
            }
        }
        document.f_a['branch_changed'].value = "1";
        
		ajax_request('do.php',{
			method: 'post',
            asynchronous: false,
			parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
				  $('span_chaging_price_indicator').show();
				  var form_arr = [];
				  var form_data = Form.serialize(document.f_a);
				
				  if ($('pi_1').disabled)	form_arr.push('pi_1');
				  if ($('sel_branch_id').disabled)	form_arr.push('sel_branch_id');
				  Form.disable(document.f_a);
					new Ajax.Updater('new_sheets', 'do.php', {
						parameters: form_data+'&a=ajax_refresh_cost&show_discount='+show_discount,
						evalScripts: true,
                        asynchronous: false,
						onComplete:  function (m) {
							calc_all_items();
							if(consignment_modules && masterfile_branch_region && consignment_multiple_currency) foreign_variable_handler(true);
							$('span_chaging_price_indicator').hide();
							get_label_indicator();
							Form.enable(document.f_a);
							for (var i in form_arr){
								$(form_arr[i]).disable();
							}
						}
					});
					
					prev_price_indicate = obj;		
				}
				else {
					alert(e.responseText.trim());
					prev_price_indicate.checked=true;
					return;
				}
			}
		});
	}
	else{
		prev_price_indicate.checked=true;
	}
}

function curtain_clicked(){
	$('color_size_matrix').hide();
    $('div_multi_add').hide();
    $('div_pc_table').hide();
}



function open_multi_add(){
	/*if($('autocomplete_sku_choices').style.display=='none'){
		alert('Nothing to add.');
		return false;
	}*/
	
	var all_li = $$('#autocomplete_sku_choices li');
	var sku_item_id_list = [];
	
	for(var i=0; i<all_li.length; i++){
		var li_title = all_li[i].title;
		if(!li_title)  continue;
		var sid = li_title.split(',')[0];
		
		sku_item_id_list.push(sid);
	}
	var del_b = document.f_a.elements["deliver_branch[]"];
	var deliver_branch = [];
	if(typeof(del_b)!='undefined'){
        for(var i=0; i<del_b.length; i++){
		    if(del_b[i].checked)	deliver_branch.push(del_b[i].value);
		}
	}
	
	
	var pi = document.f_a['price_indicate']
	var pi_value = '';
	
	for(var i=0; i<pi.length; i++){
		if(pi[i].checked){
			pi_value = pi[i].value;
			break;
		}
	}
	var open_info_name = ''
	if(document.f_a.elements["open_info[name]"]){
        open_info_name = document.f_a.elements["open_info[name]"].value
	}
	
	if(sku_item_id_list.length<=0){
        alert('Nothing to add.');
		return false;
	}
	
	if(!chk_branch()){
		return false;
	}
	
	var do_branch_id = '';
	if(document.f_a.do_branch_id) do_branch_id = document.f_a.do_branch_id.value;
	
	var branch_id = document.f_a['branch_id'].value;
	
	curtain(true);
	$('div_multi_add').show();
	center_div('div_multi_add');
	$('div_multi_add').update(_loading_);
	
	new Ajax.Updater('div_multi_add','do.php',{
		parameters:{
			a: 'multi_add',
			'sku_item_id_list[]': sku_item_id_list,
			price_indicate: pi_value,
			'deliver_branch[]': deliver_branch,
			open_info_name: open_info_name,
			do_branch_id: do_branch_id,
			branch_id: branch_id,
			do_id: $('inp_do_id').value,
			do_type: document.f_a['do_type'].value,
			show_discount: show_discount
		},
		evalScripts: true
	});
}

function refresh_tables(){
    //get the latest is_under_gst value
    if (branch_gst_register_no != '') {
        var isvalid = check_is_under_gst();
    
        if (isvalid) {
            window.is_under_gst = 1;
            document.f_a['is_under_gst'].value = 1;
        }else{
            window.is_under_gst = 0;
            document.f_a['is_under_gst'].value = 0;
        }
        
        var price_indicate_list = document.f_a['price_indicate'];
        var selected_price_indicate;
        for(var i=0; i<price_indicate_list.length; i++){
            if(price_indicate_list[i].checked){
                selected_price_indicate = price_indicate_list[i];
                break;
            }
        }
        if(selected_price_indicate){
            refresh_cost(selected_price_indicate, true);
        }
    }
    
	document.f_a.a.value = "refresh";
	document.f_a.target = "";
	
	if(chk_branch()){
		needCheckExit = false;
		document.f_a.submit();
	}
}

function change_do_branch_id(ele){
	var bid = ele.value;
	var from_bid = document.f_a['branch_id'].value;
	
	if(!chk_same_branch(ele)){
		return false;
	}

    if (branch_gst_register_no != '') {
        var isvalid = check_is_under_gst();
    
        if (isvalid) {
            window.is_under_gst = 1;
            document.f_a['is_under_gst'].value = 1;
        }else{
            window.is_under_gst = 0;
            document.f_a['is_under_gst'].value = 0;
        }
    }
    
	if(consignment_modules && masterfile_branch_region){
		consignment_branch_clicked();
		
		if(consignment_multiple_currency && $('new_sheets')!=undefined) foreign_variable_handler(true);
	}
	
	if($('new_sheets')==undefined || $('new_sheets').style.display=='none'){
		if(ele.value!='')   active_btn();
		return;
	}else{
        if(ele.value==''){
            $('srefresh').style.display='';
            $('refresh_btn').disabled=false;
            $('refresh_btn').show();
        
           // get_label_indicator();
            if ($('new_sheets') != undefined){
                $('new_sheets').style.display='none';
                $('tbl_sku').style.display='none';
                $('submitbtn').style.display='none';
                $('sn_title').style.display='none';
                $('sn_details').style.display='none';
            }
            return;
        }
    }
	change_branch_code_for_stock_balance2();
	
	var price_indicate_list = document.f_a['price_indicate'];
	var selected_price_indicate;
	for(var i=0; i<price_indicate_list.length; i++){
		if(price_indicate_list[i].checked){
			selected_price_indicate = price_indicate_list[i];
			break;
		}
	}
	if(selected_price_indicate){
		if(confirm('Do you want to auto reload Cost Price?')){
			refresh_cost(selected_price_indicate, true);
		}
	}
	
	if(do_transfer_skip_sn){
		refresh_tables();
		return;
	}
	
	// continue to check gst status if the form still not reload
	if(enable_gst && !consignment_modules){
		check_gst_date_changed();
	}
}

function change_branch_id(){
	var bid = '';
	if (document.f_a['do_branch_id'])
	    var bid = document.f_a['do_branch_id'].value;
	var from_bid = document.f_a['branch_id'].value;
	if ($('dept_id')) var dept_id = document.f_a['dept_id'].value;
	if($('new_sheets')==undefined || $('new_sheets').style.display=='none'){
		return false;
	}
	var inputs = $('new_sheets').getElementsBySelector('input.sku_items_list');
		
	if(inputs.length == 0) return false;

	if ($('span_branch_change_loading'))	$('span_branch_change_loading').update(_loading_);

	var sku_item_id = [];
	for(var i=0; i<inputs.length; i++){
		if(inputs[i].value)	sku_item_id.push(inputs[i].value);
	}
	
	sku_item_id = sku_item_id.uniq();
	ajax_request('do.php',{
	    method: 'post',
		parameters: {
		    a: 'change_do_branch',
		    branch_id: bid,
		    from_branch_id: from_bid,
		    department_id: dept_id,
		    do_date: document.f_a['do_date'].value,
			'sku_item_id[]': sku_item_id
		},
		onComplete: function(e){
			if ($('span_branch_change_loading'))	$('span_branch_change_loading').update('');
			eval("var json_arr = "+e.responseText);
			var json = json_arr['stock_balance'];
			var json_user = json_arr['user_list'];
			
			change_user_list_process(json_user);
			
			// update price
			for(var i=0; i<sku_item_id.length; i++){
			    // selling price
			    var sp = json[sku_item_id[i]]['selling_price'];
			    if(sp==undefined)   sp = 0;
			    
				$$('#do_items .selling_price_'+sku_item_id[i]).each(function(ele,index){
						ele.value = round(sp, 2);
				});

				// price type
				if(do_auto_split_by_price_type!=''){
				    var price_type = json[sku_item_id[i]]['price_type'];
					if(!price_type) price_type = '';
					$$('#do_items .span_price_type_'+sku_item_id[i]).each(function(ele,index){
						ele.update(price_type);
					});
					
					$$('#do_items .inp_price_type_'+sku_item_id[i]).each(function(ele,index){
						ele.value = price_type;
						var item_id = ele.title;
						ele.name = 'price_type['+item_id+']['+bid+']';
					});
				}
				
				// stock balance 1
				var stock_balance1 = json[sku_item_id[i]]['stock_balance1'];
				if(stock_balance1 == undefined)	stock_balance1 = '';
				
				$$('#do_items .stock_balance_1_'+sku_item_id[i]).each(function(ele,index){
					ele.value = stock_balance1;
				});

				// stock balance 2
				var stock_balance2 = json[sku_item_id[i]]['stock_balance2'];
				if(stock_balance2 == undefined)	stock_balance2 = '';
				$$('#do_items .stock_balance_2_'+sku_item_id[i]).each(function(ele,index){
					ele.value = stock_balance2;
				});
                
                if (show_parent_stock_balance) {
                    // parent stock balance 1
                    var parent_stock_balance1 = json[sku_item_id[i]]['parent_stock_balance1'];
                    if(parent_stock_balance1 == undefined)	parent_stock_balance1 = '';
                    
                    $$('#do_items .parent_stock_balance_1_'+sku_item_id[i]).each(function(ele,index){
                    	ele.value = parent_stock_balance1;
                    });
                    
                    // parent stock balance 2
                    var parent_stock_balance2 = json[sku_item_id[i]]['parent_stock_balance2'];
                    if(parent_stock_balance2 == undefined)	parent_stock_balance2 = '';
                    $$('#do_items .parent_stock_balance_2_'+sku_item_id[i]).each(function(ele,index){
                    	ele.value = parent_stock_balance2;
                    });
                }
			}
		}
	});
	change_branch_code_for_stock_balance1();
}

function change_user_list(){
	var param = Form.serialize(document.f_a)+'&a=change_user_list';
	
	ajax_request('do.php',{
	    method: 'post',
		parameters: param,
		onComplete: function(e){
			eval("var json = "+e.responseText);
			change_user_list_process(json);
		}
	});
}

function change_user_list_process(data){
	
	if (data){	
		var checkboxes='';
		var checked='';

		for (var branch_id in data){
			checkboxes='';
			for (var user_id in data[branch_id]){
				var ele = document.f_a['allowed_user['+branch_id+']['+user_id+']'];
				if (ele && ele.checked)	checked = 'checked';			
				checkboxes += "<input type=checkbox name=allowed_user["+branch_id+"]["+user_id+"] "+checked+" >"+data[branch_id][user_id]+"<br />"
				checked='';
			}
	
			if ($("user_select")){
				//single branch
				$('user_select').update(checkboxes);			
			}else{
				//multiple branch 
				$('user_select_'+branch_id).update(checkboxes);
			}
		}
	}
}

function chk_same_branch(ele){
	var b1 = document.f_a['branch_id'].value;
	var b2 = document.f_a['do_branch_id'].value;

    if(b1!=''||b2!=''){
		if(b1==b2){
			alert('Cannot Deliver to Same Branch');
			ele.selectedIndex = 0;
			if(consignment_modules && masterfile_branch_region){
				document.f_a.elements['address_deliver_to'].value = "";
				consignment_branch_clicked();
				foreign_variable_handler();
			}
			return false;
		}
	}
	
	change_branch_id();
	return true;
}

function copy_to_rcv(ele){
	var item_id = ele.title.split(',')[1];
	var rcv_qty = $('rcv_pcs'+item_id).value;
	
	if(!rcv_qty)    $('rcv_pcs'+item_id).value = ele.value;
}

change_branch_code_for_stock_balance1 = function(){	
	var bid = document.f_a['branch_id'].value;
	var branch_code = '';

	if(!bid) branch_code = current_branch_code;
	else branch_code = branch_id_code[bid];

	$('span_branch_code1').update(branch_code);
    if ($('span_parent_branch_code1') != null) {
        $('span_parent_branch_code1').update(branch_code);
    }

	if(consignment_modules && masterfile_branch_region && consignment_multiple_currency && document.f_a['do_branch_id'] != undefined){
		var do_branch_id = document.f_a['do_branch_id'].value;
		if(!do_branch_id || branch_currency_code[do_branch_id] == ""){
			if($('span_poc_currency_code')) $('span_poc_currency_code').update(currency_symbol);
			$('span_p_currency_code').update(currency_symbol);
			$('span_amt_currency_code').update(currency_symbol);
			if($('span_inv_amt_currency_code')) $('span_inv_amt_currency_code').update(currency_symbol);
		}else{
			if($('span_poc_currency_code')) $('span_poc_currency_code').update(branch_currency_code[do_branch_id]);
			$('span_p_currency_code').update(branch_currency_code[do_branch_id]);
			$('span_amt_currency_code').update(branch_currency_code[do_branch_id]);
			if($('span_inv_amt_currency_code')) $('span_inv_amt_currency_code').update(branch_currency_code[do_branch_id]);
		}
	}
}

change_branch_code_for_stock_balance2 = function(){

	if($('new_sheets')==undefined) return;

	if(document.f_a['do_branch_id'])    var bid = document.f_a['do_branch_id'].value;
	else var bid = '';
	var branch_code = '';

	if(!bid)   branch_code = 'N/A';
	else    branch_code = branch_id_code[bid];

	$('span_branch_code2').update(branch_code);
    if ($('span_parent_branch_code2') != null) {
        $('span_parent_branch_code2').update(branch_code);
    }

	if(consignment_modules && masterfile_branch_region && consignment_multiple_currency && document.f_a['do_branch_id'] != undefined){
		var do_branch_id = document.f_a['do_branch_id'].value;
		if(!do_branch_id || branch_currency_code[do_branch_id] == ""){
			if($('span_poc_currency_code')) $('span_poc_currency_code').update(currency_symbol);
			$('span_p_currency_code').update(currency_symbol);
			$('span_amt_currency_code').update(currency_symbol);
			if($('span_inv_amt_currency_code')) $('span_inv_amt_currency_code').update(currency_symbol);
			$$("#new_sheets td.foreign_amt").invoke("hide");
			$$("#new_sheets td.foreign_cost_price").invoke("hide");
		}else{
			if($('span_poc_currency_code')) $('span_poc_currency_code').update(branch_currency_code[do_branch_id]);
			$('span_p_currency_code').update(branch_currency_code[do_branch_id]);
			$('span_amt_currency_code').update(branch_currency_code[do_branch_id]);
			if($('span_inv_amt_currency_code')) $('span_inv_amt_currency_code').update(branch_currency_code[do_branch_id]);
		}
	}
}

function do_reset(){
    if (check_login()) {
        document.f_do_reset['reason'].value = '';
        var p = prompt('Enter reason to Reset :');
        if (p==null || p.trim()=='' ) return false;
        document.f_do_reset['reason'].value = p;

        if(!confirm('Are you sure to reset?'))  return false;

        document.f_do_reset.submit();
    }
	return false;
}

function discount_change(){
	var inp = document.f_a['discount']
    var discount_format = document.f_a['discount'].value.trim();
    
    // check discount pattern
	discount_format = validate_discount_format(discount_format);
	
	inp.value = discount_format;

	// recalculate total
	//recalc_total();
	
	if($('div_sheet_discount')){
        if(discount_format != ''){
        	$('div_sheet_discount').update('('+discount_format+')');
			$('tr_sheet_inv_discount_row').show();
			
			if(consignment_modules && masterfile_branch_region && consignment_multiple_currency){
				//$('div_sheet_foreign_discount').update('Discount ('+discount_format+')');
			} 
		}else{
			$('div_sheet_discount').update('');
			$('tr_sheet_inv_discount_row').hide();
			
			if(consignment_modules && masterfile_branch_region && consignment_multiple_currency){
				//$('div_sheet_foreign_discount').update('');
			}
		}
		
		// recalculate 
		calc_all_items();
		if(consignment_modules && masterfile_branch_region && consignment_multiple_currency){
			foreign_variable_recalc();
		} 
	}
	
}

function do_markup_changed(){
    var do_markup = document.f_a['do_markup'].value.trim();
    var discount_format = /^\d+(\.\d+){0,1}(\+\d+(\.\d+){0,1}){0,1}$/;
	if(!discount_format.test(do_markup)){
        document.f_a['do_markup'].value = '';
        do_markup = '';
	}
	
	// gst
	if(is_under_gst){
		var all_row_amt = $$('#do_items span.row_amt');
		for(var i=0; i<all_row_amt.length; i++){
			var item_id = $(all_row_amt[i]).title.split(',')[1];
		}
	}
	
	calc_all_items();
	if(consignment_modules && masterfile_branch_region && consignment_multiple_currency) foreign_variable_handler(true);
}

function show_available_replacement_items(){
	var sid = document.f_a['sku_item_id'].value;
	if(!sid){
		alert('No item selected.');
		return false;
	}
	var params = {
		'sid': sid,
		'can_confirm_item': 1,
		'exclude_self': 1
	}
	replacement_item.show_popup(params);
}

function confirm_replacement_item(sid){
	alert(sid);
	if(!sid)    return;
	document.f_a['sku_item_id'].value = sid;
	default_curtain_clicked();
	add_item('','');
}

function consignment_branch_clicked(obj){
	if(obj != undefined){
		if(document.f_a.elements['use_address_deliver_to'].checked == true){
			$('span_adt').style.display = "";
		}else{
			$('span_adt').style.display = "none";
		}
	}

	if(document.f_a['do_branch_id'].value){
		if(obj == undefined) document.f_a.elements['address_deliver_to'].value = branch_deliver_to[document.f_a['do_branch_id'].value];
		if(consignment_multiple_currency){
			document.f_a.elements['exchange_rate'].value = branch_exchange_rate[document.f_a['do_branch_id'].value];
		}
	}else{
		document.f_a.elements['address_deliver_to'].value = "";
		if(consignment_multiple_currency){
			document.f_a.elements['exchange_rate'].value = "";
		}
	}
}

foreign_variable_handler = function(need_recalc){
	if ($('do_items') == undefined) return; // if no items, return

	if(!consignment_multiple_currency || !document.f_a['do_branch_id'].value || branch_currency_code[document.f_a['do_branch_id'].value] == "" || branch_currency_code[document.f_a['do_branch_id'].value] == currency_symbol){
		document.f_a['exchange_rate'].readOnly = true;
		$('foreign_price').style.display = "none";
		$('foreign_ttl_amt').style.display = "none";
		if($('foreign_inv_amt')) $('foreign_inv_amt').style.display = "none";
		$$("#new_sheets .foreign_amt").invoke("hide");
		$$("#new_sheets .foreign_cost_price").invoke("hide");
		if($$("#new_sheets .foreign_inv_amt") != null) $$("#new_sheets .foreign_inv_amt").invoke("hide");
		if($$("#new_sheets .total_foreign_inv_amt") != null) $$("#new_sheets .total_foreign_inv_amt").invoke("hide");
		if($$("#new_sheets .total_foreign_amount") != null) $$("#new_sheets .total_foreign_amount").invoke("hide");
		var ttl_colspan = document.f_a['colspan_length'].value;
		$("total_colspan").colSpan = float(ttl_colspan);
		$("td_sub_total").colSpan = float(ttl_colspan);
		$("td_inv_discount").colSpan = float(ttl_colspan);
		$$('#tr_sub_total .td_sub_total_foreign_col').invoke("hide");
		$$("#tr_sheet_inv_discount_row .td_inv_discount_foreign_col").invoke("hide");
	}else{
		
		if(document.f_a['exchange_rate'] != undefined) document.f_a['exchange_rate'].readOnly = false;
		$('foreign_price').style.display = "";
		$('foreign_ttl_amt').style.display = "";
		if($('foreign_inv_amt')) $('foreign_inv_amt').style.display = "";
		$$("#new_sheets .foreign_amt").invoke("show");
		$$("#new_sheets .foreign_cost_price").invoke("show");
		if($$("#new_sheets .foreign_inv_amt") != null) $$("#new_sheets .foreign_inv_amt").invoke("show");
		if($$("#new_sheets .total_foreign_inv_amt") != null) $$("#new_sheets .total_foreign_inv_amt").invoke("show");
		if($$("#new_sheets .total_foreign_amount") != null) $$("#new_sheets .total_foreign_amount").invoke("show");
		var ttl_colspan = document.f_a['colspan_length'].value;
		$("total_colspan").colSpan = float(ttl_colspan)+1;
		$("td_sub_total").colSpan = float(ttl_colspan)+1;
		$("td_inv_discount").colSpan = float(ttl_colspan)+1;
		$$('#tr_sub_total .td_sub_total_foreign_col').invoke("show");
		$$("#tr_sheet_inv_discount_row .td_inv_discount_foreign_col").invoke("show");
	}
	if(need_recalc != undefined) {
		foreign_variable_recalc();
	}
}

foreign_variable_recalc = function(){
	if(document.f_a['exchange_rate'] == undefined) return;


	var exchange_rate = document.f_a['exchange_rate'].value;
	if(document.f_a['exchange_rate'].value <= 0){
		document.f_a['exchange_rate'].value = exchange_rate = 1;
	}
	
	var do_markup = 0;
	var do_markup1 = 0;
	var do_markup2 = 0;
	var do_markup_type = '';
	if(document.f_a['do_markup']){
        var do_markup = document.f_a['do_markup'].value;
        do_markup_type = getRadioValue(document.f_a['markup_type']);
		var do_markup1 = float(do_markup.split("+")[0]);
		if(do_markup.split("+").length>1)	do_markup2 = float(do_markup.split("+")[1]);
		if(do_markup_type=='down'){
			do_markup1 *= -1;
			do_markup2 *= -1;
		}
	}
	
	var price_length = document.f_a['price_indicate'].length;
	var price_type = "";

	for(var i = 0; i < price_length; i++){
		if(document.f_a['price_indicate'][i].checked) {
			price_type = document.f_a['price_indicate'][i].value;
			break;
		}
	}

	var price_indicate = getRadioValue(document.f_a['price_indicate']);
	var currency_multiply = 1/exchange_rate;
	
	var e = $('do_items').getElementsByClassName('uom');
	var foreign_cost = "";
	var total_foreign_amount = "";
	var cost = "";
	var last_id = "";
	var item_fraction=new Array();
	var item_foreign_cost=new Array();
	var item_qty=new Array();
	
	var row_ctn=new Array();
	var row_pcs=new Array();

	for(var i=0;i<e.length;i++){
		
		var temp_1 =new RegExp('^cost_price_');
		if (temp_1.test(e[i].id)){ // is cost
			if(cost){
				e[i].value = cost;
				cost = "";
			}

			if(price_type == 1){
				foreign_cost = round(e[i].value/exchange_rate, global_cost_decimal_points);
			}
			last_cost_id = e[i];
		}

		var temp_2 =new RegExp('^foreign_cost_price_');
		if (temp_2.test(e[i].id)){
			var line = e[i].title.split(",");
			if(foreign_cost){ // is from selling or other but not cost
				e[i].value = foreign_cost;
				foreign_cost = "";
			}

			if(price_type != 1 && branch_currency_code[document.f_a['do_branch_id'].value] && branch_currency_code[document.f_a['do_branch_id'].value] != currency_symbol){
				if(e[i].value == 0) e[i].value = last_cost_id.value;
				last_cost_id.value = round(e[i].value*exchange_rate, global_cost_decimal_points);
				last_cost_id.readOnly = true;
				e[i].readOnly = false;
			}else{
				if(!branch_exchange_rate[document.f_a['do_branch_id'].value] && e[i].value > 0 && last_cost_id.value == 0) last_cost_id.value = e[i].value;
				last_cost_id.readOnly = false;
				e[i].readOnly = true;
			}

			item_foreign_cost[line[1]] = float(e[i].value);
		}
		
 		var temp_3 = new RegExp('^qty_ctn');
		if (temp_3.test(e[i].id)){
			var line = e[i].title.split(",");
			if(!row_ctn[line[1]]){
				row_ctn[line[1]]=0;
			}
			row_ctn[line[1]]=float(e[i].value);
		}
		
 		var temp_4 = new RegExp('^qty_pcs');
		if (temp_4.test(e[i].id)){
			var line = e[i].title.split(",");
			if(!row_pcs[line[1]]){
				row_pcs[line[1]]=0;
			}
			row_pcs[line[1]]=float(e[i].value);
		}

		var temp_5 = new RegExp('^uom_fraction');
		if (temp_5.test(e[i].id)) {
			item_fraction[e[i].title]=float(e[i].value);
		}
		
  		var temp_6 = new RegExp('^row_qty');
		if (temp_6.test(e[i].id)) {
			var line =e[i].title.split(",");
			item_qty[line[1]]=float(e[i].innerHTML);
		}
				
 		var temp_7 = new RegExp('^row_foreign_amount');
		if (temp_7.test(e[i].id)){
			var line =e[i].title.split(",");
			item_id=line[1];
			var use_foreign_cost = item_foreign_cost[item_id];

			if(do_markup1){
                use_foreign_cost *= (1+do_markup1/100);
			}
			if(do_markup2){
                use_foreign_cost *= (1+do_markup2/100);
			}
			row_foreign_total = float(round(use_foreign_cost*item_qty[item_id]/item_fraction[item_id],2));
			$('row_foreign_amount'+item_id).innerHTML=round(row_foreign_total,2);
			total_foreign_amount=float(total_foreign_amount+row_foreign_total);
		}
	}

	// calculate invoice discount amount
	if(show_discount){
	    var total_foreign_inv_amt = 0;
		var all_row_foreign_amt = $$('#do_items span.row_foreign_amt');
		
		// get branch id list
		var do_branch_id_list = get_do_branch_list();
		
		// construct params for calculate invoice discount
		var disc_params = {};
		if(price_indicate==1){	// not cost
			disc_params['currency_multiply'] = currency_multiply;
		}
		if(do_branch_id_list.length>1){
			disc_params['discount_by_value_multiply'] = do_branch_id_list.length;
		}
		
		for(var i=0; i<all_row_foreign_amt.length; i++){
			var item_id = $(all_row_foreign_amt[i]).title.split(',')[1];
			var foreign_amt = float($(all_row_foreign_amt[i]).innerHTML);
			
			var row_foreign_inv_discount_format = $('inp_item_discount_'+item_id).value.trim();
			var row_foreign_inv_discount_amt = 0;
			var foreign_invoice_amt = foreign_amt;
			
			// calculate sheet discount
			row_foreign_inv_discount_amt = float(round(get_discount_amt(foreign_invoice_amt, row_foreign_inv_discount_format, disc_params),2));

			if(row_foreign_inv_discount_amt){
				foreign_invoice_amt -= row_foreign_inv_discount_amt;
			}
			
			foreign_invoice_amt = float(round(foreign_invoice_amt, 2));
			$('span_row_foreign_invoice_amt_'+item_id).innerHTML = round(foreign_invoice_amt, 2);
			total_foreign_inv_amt += foreign_invoice_amt;			
		}
		
		var sub_total_foreign_inv_amt = float(round(total_foreign_inv_amt, 2));
		
		// sub total for foreign invoice amt
		$('inp_sub_total_foreign_inv_amt').value = sub_total_foreign_inv_amt;
		$('span-sub_total_foreign_inv_amt').update(round(sub_total_foreign_inv_amt, 2));
		
		// sheet discount
		var inv_discount_format = document.f_a['discount'].value.trim();
		inv_discount_format = validate_discount_format(inv_discount_format);
		
		// calculate sheet discount
		var foreign_inv_discount_amt = float(round(get_discount_amt(total_foreign_inv_amt, inv_discount_format, disc_params),2));
		if(foreign_inv_discount_amt){
			total_foreign_inv_amt -= foreign_inv_discount_amt;
		}
		
		$('span_total_foreign_inv_amt').update(round(total_foreign_inv_amt,2));
		$('inp_total_foreign_inv_amt').value = total_foreign_inv_amt;
		
		document.f_a['inv_sheet_foreign_discount_amt'].value = round(foreign_inv_discount_amt, 2);
		$('span-foreign_inv_discount_amt').update(round(foreign_inv_discount_amt, 2));
	}

	$('display_total_foreign_amount').innerHTML=round(total_foreign_amount,2);
	$('total_foreign_amount').value=round(total_foreign_amount,3);
	
	if(price_type != 1) calc_all_items();
}

get_label_indicator=function(){
	var optlblvalue='';
	var optvalue='';
	$$('#getoptlabel_id input').each(function(opt){
		if (opt.checked){
			optvalue=opt.value;
			var opt_id = opt.id;

			$$('#getoptlabel_id label').each(function(lbl){
				if (opt_id == lbl.readAttribute('for')){
					optlblvalue=lbl.innerHTML;
					return;
				}
			});
			return;
		}
	});

	if ($('opt_label_id'))	$('opt_label_id').value=optlblvalue;
	
	$$('#do_items .pindicator').each(function(ele){
		ele.getElementsByTagName("span")[0].innerHTML=(optlblvalue);
		ele.getElementsByTagName("input")[0].value=(optvalue);			
	});
}

function get_do_branch_list(){
	var branch_id_list = [];
	
	if(document.f_a['deliver_branch[]']){	// got multiple branch checkbox
		inp_list = document.f_a['deliver_branch[]'];
		for(var i=0; i<inp_list.length; i++){
			if(inp_list[i].checked)	branch_id_list.push(inp_list[i].value);
		}
	}else if(document.f_a['do_branch_id']){	// only single branch
		if(document.f_a['do_branch_id'].value>0){
			branch_id_list.push(document.f_a['do_branch_id'].value);
		}
	}
	return branch_id_list;
	
}

function get_selected_price_indicator(){
	var price_indicate_list = document.f_a['price_indicate'];
	var selected_price_indicate;
	for(var i=0; i<price_indicate_list.length; i++){
		if(price_indicate_list[i].checked){
			selected_price_indicate = price_indicate_list[i];
			break;
		}
	}
	return selected_price_indicate;
}

function check_cannot_use_cost_indicator(ele){
	if(prev_price_indicate){
		if(prev_price_indicate != ele){
			alert('You must have cost privilege in order to change price from others to cost');
		}
	}
}

// function when do date changed
function on_do_date_changed(){
	// get the object
	var inp = document.f_a['do_date'];
	// check max/min limit
	upper_lower_limit(inp);
    
    if (branch_gst_register_no != '') {
        var isvalid = check_is_under_gst();
    
        if (isvalid) {
            window.is_under_gst = 1;
            document.f_a['is_under_gst'].value = 1;
        }else{
            window.is_under_gst = 0;
            document.f_a['is_under_gst'].value = 0;
        }
        
        var price_indicate_list = document.f_a['price_indicate'];
        var selected_price_indicate;
        for(var i=0; i<price_indicate_list.length; i++){
            if(price_indicate_list[i].checked){
                selected_price_indicate = price_indicate_list[i];
                break;
            }
        }
        if(selected_price_indicate){
            refresh_cost(selected_price_indicate, true);
        }
    }
    
	// check gst
	if(enable_gst)	check_gst_date_changed();
}


// function when do date is changed
function check_gst_date_changed(){
	var allow_gst = false;
	
	// gst is not enable
	if(!enable_gst || consignment_modules)	return;
	
	// gst is active and branch got register
	if(gst_is_active && branch_gst_register_no){
		if(skip_gst_validate){
			allow_gst = true;
		}else{		
			// got gst start date
			if(global_gst_start_date && branch_gst_start_date){
				// get Date
				var do_date = document.f_a["do_date"].value.trim();
				
				if(do_date){
					// check Date
					if(strtotime(do_date) > strtotime(global_gst_start_date) && strtotime(do_date) > strtotime(branch_gst_start_date)){
						// check gst interbranch
						if(document.f_a["do_branch_id"] != undefined){
							// single
							var opt = document.f_a["do_branch_id"].options[document.f_a["do_branch_id"].selectedIndex];
							var got_gst_interbranch = int($(opt).readAttribute('got_gst_interbranch'));
							if(got_gst_interbranch){
								allow_gst = true;
							}
						}else{
							// multi 
							for(var i=0; i < document.f_a["deliver_branch[]"].length; i++){
								if(document.f_a["deliver_branch[]"][i].checked){
									var got_gst_interbranch = int($(document.f_a["deliver_branch[]"][i]).readAttribute('got_gst_interbranch'));
									if(got_gst_interbranch){
										allow_gst = true;
									}else{
										allow_gst = false;
										break;
									}
								}
							}
						}
						allow_gst = true;
					}
				}
			}
		}
	}

	if(allow_gst){
		// date have gst
		if(!is_under_gst)	active_btn();
	}else{
		// date no gst
		if(is_under_gst)	active_btn();
	}
}

// function to update gst id/code/rate
function update_selected_gst(item_id){
	document.f_a["gst_id["+item_id+"]"].value = "";
	document.f_a["gst_code["+item_id+"]"].value = "";
	document.f_a["gst_rate["+item_id+"]"].value = "";
	
	var sel = document.f_a['item_gst['+item_id+']'];
	
	if(sel.selectedIndex >= 0){
		// got select
		var opt = sel.options[sel.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		
		document.f_a["gst_id["+item_id+"]"].value = gst_id;
		document.f_a["gst_code["+item_id+"]"].value = gst_code;
		document.f_a["gst_rate["+item_id+"]"].value = gst_rate;
	}
}

function do_branch_changed(){
    if (branch_gst_register_no != '') {
        var isvalid = check_is_under_gst();
    
        if (isvalid) {
            window.is_under_gst = 1;
            document.f_a['is_under_gst'].value = 1;
        }else{
            window.is_under_gst = 0;
            document.f_a['is_under_gst'].value = 0;
        }
        
        var price_indicate_list = document.f_a['price_indicate'];
        var selected_price_indicate;
        for(var i=0; i<price_indicate_list.length; i++){
            if(price_indicate_list[i].checked){
                selected_price_indicate = price_indicate_list[i];
                break;
            }
        }
        if(selected_price_indicate){
            refresh_cost(selected_price_indicate, true);
        }
    }
    
	active_btn();
}

function check_branch_by_group(){
	var sl_brn_grp = $('sel_brn_grp');
	var sel_grp_val = sl_brn_grp.options[sl_brn_grp.selectedIndex].value;
	
	if (sel_grp_val){
		var sel_brn_list = sel_grp_val.split(',');
		
		for (i=0, len=sel_brn_list.length; i<len; i++){	
			if (!$('dt_'+sel_brn_list[i]).checked){
				$('dt_'+sel_brn_list[i]).checked = true;
			}
		}
	} else {
		var all_brn = document.f_a['deliver_branch[]'];;
		
		for (i=0, len=all_brn.length; i<len; i++){
			if (!all_brn[i].checked){
				all_brn[i].checked = true;
			}
		}
	}
	
	do_branch_changed();
}

function uncheck_branch_by_group(){
	var sl_brn_grp = $('sel_brn_grp');
	var sel_grp_val = sl_brn_grp.options[sl_brn_grp.selectedIndex].value;
	
	if (sel_grp_val){
		var sel_brn_list = sel_grp_val.split(',');
	
		for (i=0, len=sel_brn_list.length; i<len; i++){
			if ($('dt_'+sel_brn_list[i]).checked){
				$('dt_'+sel_brn_list[i]).checked = false;
			}	
		}
	} else {
		var all_brn = document.f_a['deliver_branch[]'];
		
		for (i=0, len=all_brn.length; i<len; i++){
			if (all_brn[i].checked){
				all_brn[i].checked = false;
			}
		}
	}
	
	do_branch_changed();
}

function check_is_under_gst() {
    if (enable_gst && !consignment_modules && gst_is_active) {
        if(global_gst_start_date && branch_gst_start_date){
            // get Date
            var do_date = document.f_a["do_date"].value.trim();
            
            if(do_date){
                // check Date
                if(strtotime(do_date) > strtotime(global_gst_start_date) && strtotime(do_date) > strtotime(branch_gst_start_date)){
                    // check gst interbranch
                    if(document.f_a["do_branch_id"] != undefined){
                        var opt = document.f_a["do_branch_id"].options[document.f_a["do_branch_id"].selectedIndex];
                        var got_gst_interbranch = int($(opt).readAttribute('got_gst_interbranch'));
                        if(got_gst_interbranch){
                            return 1;
                        }else{
                            return 0;
                        }
                    }else{
                        for(var i=0; i < document.f_a["deliver_branch[]"].length; i++){
							if(document.f_a["deliver_branch[]"][i].checked){
								var got_gst_interbranch = int($(document.f_a["deliver_branch[]"][i]).readAttribute('got_gst_interbranch'));
								if(got_gst_interbranch){
									return 1;
								}else{
									return 0;
									break;
								}
							}
						}
                    }
                }else{
                    return 0;
                }
            }
        }else return;
    }else return;
}
</script>
{/literal}
{include file='do.script.tpl'}

<div id="div_multi_add" style="display:none;border:1px solid black;position:absolute;height:500px;width:700px;background:white;padding:3px;z-index:10000;overflow-y:auto;">
</div>

<div id="div_pc_table" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff; z-index:20000;">
</div>

{if $config.enable_replacement_items}{include file='replacement_items_popup.tpl'}{/if}

<div id=wait_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
<p align=center>
Please wait..
<br /><br />
<img src="ui/clock.gif" border="0" />
</p>
</div>

{if $form.approval_screen}
<form name="f_b" method=post>
<input type=hidden name=do_no value={$form.do_no}>
<input type=hidden name=branch_id value={$form.branch_id}>
<input type=hidden name=do_branch_id value={$form.do_branch_id}>
<input type=hidden name=id value="{$form.id}" id="inp_do_id">
<input type=hidden name=comment value="">
<input type=hidden name=a value="approve">
<input type=hidden name=approvals value={$form.approvals}>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type=hidden name="curr_date" value="{$form.do_date}">
</form>
{/if}

<h1>Transfer DO {if $form.do_no}(DO/{$form.do_no}){else}{if $form.id<$time_value}(ID#{$form.id}){/if}{/if}</h1>
<h3>Status:
{if $form.approved}
	Fully Approved
{elseif $form.status == 1}
	In Approval Cycle
{elseif $form.status == 5}
	Cancelled
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft Delivery Order
{/if}
</h3>

{if $form.related_do_list}
	<div class="stdframe" style="background:#C9FFFE;margin:1em 0;">
		<h4>Related DO</h4>
		{foreach from=$form.related_do_list item=rd name=f}
			<a href="do.php?a=view&branch_id={$rd.branch_id}&id={$rd.id}" target="_blank">
				{if $rd.do_no}
					{$rd.do_no}
				{else}
					{$rd.bcode}#{$rd.id}
				{/if}
			</a>

			{if !$smarty.foreach.f.last}, {/if}
		{/foreach}
	</div>
{/if}

{include file=approval_history.tpl}

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
<input type=hidden name="do_date" value="{$form.do_date}">
</form>

<form name="f_a" method=post ENCTYPE="multipart/form-data">
<input type=hidden name=a value="save">
<!--input type=hidden name=branch_id value={$form.branch_id}-->
<input type=hidden name="old_branch_id" value="{$form.old_branch_id}">
<input type=hidden name=id value="{$form.id}" id="inp_do_id">
<input type=hidden name=reason value="">
<!--input type=hidden name=create_type value="{$form.create_type}"-->
<input type=hidden name=do_no value={$form.do_no}>
<!--input type=hidden name=do_branch_id value="{$form.do_branch_id}"-->
<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
<input type=hidden name=old_branch_id value="{$form.old_branch_id}">
<input type=hidden name=branch_changed value="">
<input type=hidden name="do_type" value="transfer">
<input type="hidden" name="show_discount" value="{$show_discount}" />
<input type="hidden" name="sub_total_inv_amt" id="inp_sub_total_inv_amt" value="{$form.sub_total_inv_amt}" />
<input type="hidden" name="sub_total_foreign_inv_amt" id="inp_sub_total_foreign_inv_amt" value="{$form.sub_total_foreign_inv_amt}" />
<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}"/>
<input type="hidden" name="sub_total_gross_amt" value="{$form.sub_total_gross_amt}"/>
<input type="hidden" name="sub_total_gst_amt" value="{$form.sub_total_gst_amt}"/>
<input type="hidden" name="sub_total_amt" value="{$form.sub_total_amt}"/>
<input type="hidden" name="inv_sheet_gross_discount_amt" value="{$form.inv_sheet_gross_discount_amt}"/>
<input type="hidden" name="inv_sheet_gst_discount" value="{$form.inv_sheet_gst_discount}"/>
<input type="hidden" name="inv_sheet_discount_amt" value="{$form.inv_sheet_discount_amt}"/>
<input type="hidden" name="do_total_gross_amt" value="{$form.do_total_gross_amt}"/>
<input type="hidden" name="do_total_gst_amt" value="{$form.do_total_gst_amt}"/>
<input type="hidden" name="inv_sub_total_gross_amt" value="{$form.inv_sub_total_gross_amt}"/>
<input type="hidden" name="inv_sub_total_gst_amt" value="{$form.inv_sub_total_gst_amt}"/>
<input type="hidden" name="inv_total_gross_amt" value="{$form.inv_total_gross_amt}"/>
<input type="hidden" name="inv_total_gst_amt" value="{$form.inv_total_gst_amt}"/>
<input type="hidden" name="inv_sheet_foreign_discount_amt" value="{$form.inv_sheet_foreign_discount_amt}"/>

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}
<div id=errpr></div>

<table border=0 cellspacing=0 cellpadding=4>
{if $form.added}
    <tr>
        <th width=160 align=left>Added Date</th>
        <td>{$form.added}</td>
    </tr>
{/if}
<tr>
<th width=160 align=left>DO Date </th>
<td>
<input name="do_date" id="added1" size=12 onchange="on_do_date_changed();" maxlength=10 value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}">
{*if $form.status<1 || $form.status eq '2'*}
{if !$readonly && !$form.approval_screen}
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
{/if}
</td>
</tr>

{if $config.do_approval_by_department}
<tr>
<th width=160 align=left>Department</th>
	<td>
		<select name="dept_id" id="dept_id" onchange="change_user_list()">
			<option value=0>-- Please Select --</option>
			{foreach from=$departments item=dept}
				<option value={$dept.id} {if $form.dept_id eq $dept.id}selected{/if}>{$dept.description}</option>
			{/foreach}
		</select> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
	</td>
</tr>
{/if}

{if $show_discount}
	<tr>
	    <th align="left">Invoice Discount</th>
	    <td>
			<input type="text" name="discount" value="{$form.discount}" size="10" style="text-align:right;" onChange="discount_change();"/>
			<b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
		
		</td>
	</tr>
{/if}

{if $config.do_enable_do_markup}
	<tr>
	    <th align="left">
	        <table>
	            <tr><td rowspan="2"><b>DO</b></td><td><input type="radio" name="markup_type" value="up" {if $form.markup_type eq 'up' || !$form.markup_type}checked {/if} onChange="do_markup_changed();" /> <b>Markup</b></td></tr>
	            <tr><td><input type="radio" name="markup_type" value="down" {if $form.markup_type eq 'down'}checked {/if} onChange="do_markup_changed();" /> <b>Discount</b></td></tr>
	        </table>
		</th>
	    <td><input type="text" name="do_markup" value="{$form.do_markup}" size="10" style="text-align:right;" onChange="do_markup_changed();"/>%
        	{if $config.do_split_auto_add_do_discount and $form.do_branch_id}
			    <span style="color:blue">
			        (Once confirm, System will automatically insert branch trade discount if DO markup/discount is zero)
			    </span>
			{/if}
		</td>
	</tr>
{/if}
<tr>
<th align=left>PO No.</th>
<td>
<input name="po_no" id="po_no" maxlength=12 size=12 value="{$form.po_no}" onchange="uc(this);" {if $form.create_type eq 3}readonly{/if}> 
</td>
</tr>

{if $form.id<$time_value}
<tr>
	<td align=left><b>Owner</b></td>
	<td style="color:blue;">
	{$form.user}
	</td>
</tr>
{/if}

<tr>
	<td valign=top><b>Price From</b></td>
	<td id="getoptlabel_id">
		{if $config.sku_multiple_selling_price}
			{foreach from=$config.sku_multiple_selling_price key=i item=e}
			{if $i eq '1'}
				<span class="nowrap"><input type=radio name="price_indicate" id="pi_1" value="1" onchange="refresh_cost(this);" {if $form.price_indicate eq '1' || ($config.do_default_price_from eq 'cost' && !$form.price_indicate)}checked {/if} onClick="{if !$sessioninfo.privilege.SHOW_COST}check_cannot_use_cost_indicator(this);return false;{/if}" /><label for='pi_1'>Cost</label>&nbsp;&nbsp;</span>
			{elseif $i eq '2'}
				<span class="nowrap"><input type=radio name="price_indicate" id="pi_2" value="2" onchange="refresh_cost(this);" {if $form.price_indicate eq '2' || ($config.do_default_price_from eq 'selling' && !$form.price_indicate) || (!$config.do_default_price_from && !$form.price_indicate)}checked {/if}><label for='pi_2'>Selling (Normal)</label> &nbsp;&nbsp;</span>
			{elseif $i eq '3'}
				<span class="nowrap"><input type=radio name="price_indicate" id="pi_3" value="3" onchange="refresh_cost(this);" {if $form.price_indicate eq '3' || ($config.do_default_price_from eq 'last_do' and !$form.price_indicate)}checked {/if}><label for='pi_3'>Last DO</label> &nbsp;&nbsp;</span>
			{elseif $i eq '4'}
				{if $from_po}<span class="nowrap"><input type=radio name="price_indicate" id="pi_4" value="4" onchange="refresh_cost(this);" {if $form.price_indicate eq $i}checked {/if} {if !$sessioninfo.privilege.SHOW_COST}disabled {/if}><label for='pi_4'>PO Cost</label> &nbsp;&nbsp;</span>{/if}			
			{else}
			<span class="nowrap"><input id='sp_{$i}' type=radio name="price_indicate" id="pi_{$i}" value="{$i}" onchange="refresh_cost(this);" {if $form.price_indicate eq $i}checked {/if}><label for='sp_{$i}'>{$e}</label>&nbsp;</span>
			{/if}
			{/foreach}
		{/if}
	  <br />
	  <span id="span_chaging_price_indicator" style="background:yellow;padding:2px;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Price Updating...</span>
	</td>
</tr>

<tr>
<td valign=top><b>Remarks</b></td>
<td colspan=3>
<textarea rows="2" cols="68" name=remark onchange="uc(this);">{$form.remark}</textarea>
</td>
</tr>
<tr>
	<td valign=top><b>Deliver From</b></td>
	<td>
	    {assign var=can_change_from value=0}
	    {if $config.consignment_modules && is_new_id($form.id)}{assign var=can_change_from value=1}{/if}
	    <select id="sel_branch_id" {if !$can_change_from}disabled {else}name="branch_id"{/if}>
	        <option value="">-- Please Select --</option>
			{section name=i loop=$all_branch}
			{assign var=bid value=$all_branch[i].id}
			<option value="{$bid}" {if ($form.branch_id>0 and $form.branch_id eq $bid) or (!$form.branch_id and $all_branch[i].code eq $BRANCH_CODE)}selected {/if}>{$all_branch[i].code} - {$all_branch[i].description}</option>
			{/section}
	    </select>
		{if !$can_change_from}
		    <input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
		{/if}
	</td>
</tr>

<tr style="display:none;">
    <td valign=top><b>Deliver To</b></td>
	<td>
		<input type=radio name="create_type" value=1 checked onclick="select_type(this.value);" >{*Branches*}
	</td>
</tr>
<tr id="delivery_branches">
    <td valign=top><b>Deliver To</b></td>
	    {if (($form.deliver_branch || !$form.do_branch_id) && !$config.consignment_modules) || ($form.deliver_branch && $config.consignment_modules)}
		<td>
			<div id="div_multi_branch_selected">
				You may select multiple branches to deliver <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span><br>
				<br />
				{if $form.id>=$time_value}
				Select by: 
				<select name="sel_brn_grp" id="sel_brn_grp" >
					<option value="" >-- All --</option>
					{section name=j loop=$brn_grp_list}
					<option value="{$brn_grp_list[j].grp_items}" >{$brn_grp_list[j].code} - {$brn_grp_list[j].description}</option>
					{/section}
				</select>&nbsp;&nbsp;
				
				<input type="button" style="width:70px;" value="Select " onclick="check_branch_by_group();" />&nbsp;
				<input type="button" style="width:70px;" value="De-select" onclick="uncheck_branch_by_group();" /><br /><br />
				{/if}
				
				{if count($branch)<=10}
					<table class="small">
						{section name=i loop=$branch}
							{assign var=bid value=$branch[i].id}
							<tr>
								<td valign="top">
									<input class="branch" onchange="do_branch_changed();" type=checkbox name="deliver_branch[]" value="{$branch[i].id}" 
									{if is_array($form.deliver_branch) and in_array($branch[i].id,$form.deliver_branch) or (is_array($po_multi_deliver_to) and in_array($bid,$po_multi_deliver_to))}checked {/if} 
									id="dt_{$bid}" {if $form.id<$time_value}onclick="return false;"{/if}
									got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">
									&nbsp;<label for=dt_{$bid}>{$branch[i].code} {if $config.enable_gst && !$config.consignment_modules && $gst_interbranch.$bid}<sup class="small" style="color:red;">(GST)</sup>{/if}</label>
									&nbsp;&nbsp;
								</td>
								{if is_array($form.deliver_branch) and (in_array($bid,$form.deliver_branch))}
									<td>
										<table border=0>
											<tr>
												<td valign="top"><i>User Selection</i></td>
												<td>
													<div id=user_select_{$bid} style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
														{foreach from=$user_list.$bid key=id item=user_name}
															<input type="checkbox" name="allowed_user[{$bid}][{$id}]" {if $form.allowed_user.$bid.$id}checked{/if}>{$user_name}<br />
														{/foreach}
													</div>
												</td>
											</tr>
										</table>
									</td>
								{/if}
							</tr>
						{/section}
					</table>
				{else}
					<div style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
						<table>
						{section name=i loop=$branch}
							{assign var=bid value=$branch[i].id}
							{if $bid ne $form.branch_id}
							<tr>
								<td>
									<input class="branch" onchange="do_branch_changed();" type=checkbox name="deliver_branch[]" value="{$branch[i].id}" {if (is_array($form.deliver_branch) and in_array($branch[i].id,$form.deliver_branch)) or (is_array($po_multi_deliver_to) and in_array($bid,$po_multi_deliver_to))}checked {/if} id="dt_{$bid}" {if $form.id<$time_value}onclick="return false;"{/if}
									got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">&nbsp;<label for=dt_{$bid}>{$branch[i].code} - {$branch[i].description} {if $config.enable_gst && !$config.consignment_modules && $gst_interbranch.$bid}<sup class="small" style="color:red;">(GST)</sup>{/if}</label>
								</td>
								{if is_array($form.deliver_branch) and (in_array($bid,$form.deliver_branch))}
									<td>
										<table border=0 class="small">
											<tr>
												<td valign="top"><i>User Selection</i></td>
												<td>
													<div id=user_select_{$bid} style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
														{foreach from=$user_list.$bid key=id item=user_name}
															<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>{$user_name}<br />
														{/foreach}
													</div>
												</td>
											</tr>
										</table>
									</td>
								{/if}

							</tr>
							{/if}
						{/section}
						</table>
					</div>
				{/if}
			</div>
		</td>
		</tr>
	    {else}
			<td>
	            <div id="div_single_branch_selected">
		        <select name="do_branch_id" id="do_branch_id" onChange="change_do_branch_id(this);">
			        <option value="">-- Please Select --</option>
					{section name=i loop=$all_branch}
					{assign var=bid value=$all_branch[i].id}
						<option value="{$bid}" {if ($form.do_branch_id eq $bid)}selected {/if} got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">{$all_branch[i].code} - {$all_branch[i].description}</option>
					{/section}
			    </select>
			    <span id="span_branch_change_loading"></span>
			    </div>
			</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="checkbox" name="use_address_deliver_to" value="1" {if $form.use_address_deliver_to}checked{/if} onclick="consignment_branch_clicked(this)"> 
					<b> Use Deliver To Address from Branch</b><br />
					<span id="span_adt" {if !$form.use_address_deliver_to}style="display:none;"{/if}>
					<textarea rows="5" cols="30" name="address_deliver_to" placeholder="[if delivery address is different, please enter here]">{$form.address_deliver_to}</textarea>
					</span>

				</td>
			</tr>
			{if $config.consignment_modules && $config.masterfile_branch_region && $config.consignment_multiple_currency}
				<tr>
					<td><b>Exchange Rate</b></td>
					<td>
						<input type="text" name="exchange_rate" size="15" value="{if $form.exchange_rate ne 1}{$form.exchange_rate}{/if}" onchange="this.value=float(this.value); if(this.value == 0 || this.value == '') this.value = 1; foreign_variable_handler(true);" class="r">
					</td>
				</tr>
			{/if}
			<tr>
				<td valign="top"><b>User Selection</b></td>
				<td>
					<div id=user_select style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
						{assign var=bid value=$form.do_branch_id}
						{foreach from=$user_list.$bid key=id item=user_name}
							<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>{$user_name}<br />
						{/foreach}
					</div>
				</td>
			</tr>
	    {/if}
</table>

<div id=srefresh style="display:none; padding-top:10px; padding-left:130px; ">
<input class="btn btn-success" id=refresh_btn type=button onclick="void(refresh_tables())" value="click here to continue">
</div>
</td>
</tr>
</table>
</div>

<br>

{if $errm_link_code}
<div id=err>
<div class=errmsg>
<ul>
<li>
The following Link Code are INVALID :
</ul>
</div>
{foreach from=$errm_link_code item=e}
<b>{$e}</b><br>
{/foreach}
</div>
{/if}

<br>

{if $errm_sku_item_code}
<div id=err>
<div class=errmsg>
<ul>
<li>The following ARMS Code are INVALID :</li>
</ul>
</div>
{foreach from=$errm_sku_item_code item=e}
<b>{$e}</b><br>
{/foreach}
</div>
{/if}

{if $errm.item}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.item item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

{assign var=total_inv_amt value=0}
<br>

{if $have_select_delivery}
	<div id="new_sheets">
		{include file=do.new.sheet.tpl}
	</div>
	{if (!$form.status || $form.status=='2') && $form.create_type ne '3' && !$form.approval_screen && !$form.approved && !$readonly}	
		<table id=tbl_sku width=100% style="border:1px solid #999; padding:2px; background-color:#dddddd">
		{include file='scan_barcode_autocomplete.tpl' no_need_table=1 need_hr_bottom=1}
		<tr class=normal>
			<td width="90px" valign="top" nowrap>
			<input name="sku_item_id" size=3 type=hidden>
			<input name="sku_item_code" size=13 type=hidden>
			
			<b>Search SKU </b>
			</td>
			<td nowrap><input id="autocomplete_sku" name="sku" size=35 onclick="this.select()">
			
			<input class="btn btn-primary" type=button value="Add" onclick="add_item('', '{$bid}')">
			<input class="btn btn-primary" type=button value="Multiple Add" onclick="open_multi_add()">
			<input class="btn btn-primary" type=button value="Add Matrix" onclick="add_size_color()">
			<input class="btn btn-primary" type=button value="Add Parent & Child" onclick="add_parent_child()">
			{if $config.enable_replacement_items}
			    <input class="btn btn-primary" type=button value="Use Replacements" onclick="show_available_replacement_items();" />
			{/if}
			{if $config.do_allow_open_item}
				<input class="btn btn-primary" type=button value="Add Open Item" onclick="add_item('1', '{$bid}')">
			{/if}
			<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
			<br>
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
			</td>
		</tr>
		</table>
		<script>reset_sku_autocomplete();</script>
	{/if}
{/if}
	<span id="sn_title"><br /><h3>Serial No Details <img src="{if count($errm.sn) eq 0}/ui/expand.gif{else}/ui/collapse.gif{/if}" id="sn_dtl_icon" onclick="sh_serial_no(this);" align=absmiddle></h3></span>
	<div class="sn_details" id="sn_details" {if count($errm.sn) eq 0}style="display:none;"{/if}>
		{foreach from=$do_items item=item name=fitem}
			{if $item.serial_no || $item.have_sn}
				{include file="do.sn.new.tpl"}
			{/if}
		{/foreach}	
	</div>
</form>

<div id="load_color_size_matrix" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff; z-index:20000;">
</div>

<div id="color_size_matrix" style="position:absolute;left:0;top:0;display:none;width:800px;height:500px;padding:10px;border:1px solid #000; background:#fff; z-index:20000;">
</div>

<p id=submitbtn align=center>

{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
<input type=button value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
<input type=button value="Terminate" style="background-color:#900; color:#fff;" onclick="do_cancel()">
{/if}

{if !$form.approval_screen}
	{if !$readonly}
        {if $config.do_auto_split_by_price_type}
	    <span style="color:blue">Once confirm, DO will auto split base on price type.
			(Multiple Branches DO will not be split.)
		</span><br />
	    {/if}
		{if (!$form.status || $form.status==2) && $have_select_delivery}
		<input class="btn btn-success" name=bsubmit type=button value="Save & Close" onclick="do_save()" >
		{/if}
		
		{if $form.id<$time_value}
		<input class="btn btn-error" type=button value="Delete" onclick="do_delete()">
  		{/if}
        {*<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/do.php?page={$do_type}'">*}
        
		{if (!$form.status || $form.status==2) && $have_select_delivery}
		<input class="btn btn-primary" type=button value="Confirm" onclick="do_confirm()">
		{/if}
	{else}
	    {if $form.approved eq 1 && ($sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.DO_ALLOW_USER_RESET)}
	        <input type=button class="btn btn-warning" value="Reset" onclick="do_reset();">
	    {/if}
		<input type=button class="btn btn-error" value="Close" onclick="document.location='/do.php?page={$do_type}'">
	{/if}
{/if}
</p>

{include file="do.sn.new.tpl" is_setup_only=1}

{if !$form.approval_screen}
	{include file=footer.tpl}
{/if}

<script type="text/javascript">
DO_MODULE.initilize();

{if !$form.total_inv_amt && $show_discount}
	if($('inp_total_inv_amt'))  $('inp_total_inv_amt').value = current_total_inv_amt;
	if($('span_total_inv_amt')) $('span_total_inv_amt').update(round(current_total_inv_amt,2));
{/if}

{if $readonly || $form.approval_screen}
	Form.disable(document.f_a);
{else}
	prev_price_indicate = get_selected_price_indicator();
	//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku.
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	init_calendar();
	calc_all_items();
	window.onbeforeunload=confirmExit;
	
	{/literal}
{/if}

{literal}
toggle_sn_details();
if($('div_sn_by_range_popup')){
	new Draggable('div_sn_by_range_popup',{ handle: 'div_sn_by_range_popup_header'});
}
if($('div_pc_table')){
	new Draggable('div_pc_table',{ handle: 'div_pc_table'});
}
{/literal}
prev_price_indicate = $('pi_{$form.price_indicate|default:1}');

{if $po_multi_deliver_to}
	$('srefresh').show();
{/if}

{if $form.do_branch_id && $do_type eq 'transfer' && $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}foreign_variable_handler(true);{/if}

{if ($form.id>$time_value && count($do_items) == 0) || $readonly || $form.approval_screen}
	needCheckExit = false;
{/if}
</script>
