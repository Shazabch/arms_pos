{*
REVISON HISTORY
+++++++++++++++
10/5/2007 4:18:37 PM - gary
-add remarks textarea.

10/5/2007 4:28:13 PM gary
- add "DO/" for diaplaying DO no.

12/3/2007 5:05:08 PM gary
- add <hr> tag for approval screen.

12/18/2007 12:14:16 PM gary
- add option for delivery (to branches or other company).

1/4/2008 3:20:44 PM gary
- add price indicate.

2/14/2008 12:50:40 PM gary
- remove the file upload pass f and c variable.

2/20/2008 12:01:03 PM gary
- add show owner column to view.

3/17/2008 2:42:15 PM gary
- fix the total amount bug when editing.

4/14/2008 9:40:21 AM gary
- add dummy to avoid timeout.

6/9/2008 11:09:14 AM yinsee
- add support for GRN Barcode (GMARK)
- fix po delete row bug (branch_id not passed)

9/11/2008 5:59:03 PM yinsee
- add DO Price as price indicator 

7/21/2009 4:46:25 PM Andy
- Add do_reset function

8/12/2009 12:33:06 PM Andy
- Module Modify, this template is now only for 'open' type DO

11/6/2009 3:05:29 PM Andy
- add invoice discount. per sheet and per item

11/11/2009 3:32:50 PM edward
- add show status

11/16/2009 10:22:17 AM Andy
- fix no invoice javascript error

12/23/2009 6:16:04 PM Andy
- Add Debtor selection

2/22/2010 12:09:40 PM Andy
- Block user to save DO while price changing is in process
- Fix price indicator bugs

5/10/2010 3:09:01 PM Andy
- Add DO Markup.
- Fix DO stock balance show "undefined" bugs.
- Fix if got invoice discount but no item will have javascript error.

5/14/2010 11:18:02 AM Andy
- Add Sales Person Name in Credit/Cash Sales DO. (need config)

5/17/2010 1:50:22 PM Andy
- Add DO auto split by price type can automatically insert DO Discount base on branch trade discount. (need config)
- DO Markup can now be use as DO Discount as well.

5/31/2010 4:16:32 PM Alex
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

6/20/2011 3:21:12 PM Alex/Justin
- Fixed the missing of SKU search engine when change Price From.

6/22/2011 11:00:39 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

6/23/2011 10:19:01 AM Alex
- add span and class nowrap for options

8/9/2011 4:58:30 PM Andy
- Change DO Invoice Discount format.

8/19/2011 2:59:21 PM Justin
- Added clear search SKU engine feature.

9/19/2011 3:49:34 PM Andy
- Add show item error.

10/3/2011 5:55:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/24/2011 5:46:54 PM Justin
- Added process to handle rounding amount.

12/6/2011 10:08:32 AM Justin
- Added SA list for user to maintain.
- Added validation for sa list.

1/16/2012 5:07:43 PM Justin
- Amended div for SA list to be shorter.
- Modified to show "as above" for all do items S/A drop-down list when add SA from header.

2/13/2012 5:12:54 PM Justin
- Hidden the "Sales Person" text field whenever Sales Agent is activated.

2/29/2012 11:18:05 AM Alex
- add options for scan GRN Barcode

3/30/2012 11:39:32 AM Justin
- Added new feature to prompt a confirmation when user about to leave the page.

4/6/2012 3:26:41 PM Alex
- change department "All" to "Please Select"

4/25/2012 6:08:11 PM Alex
- edit change_do_branch_id() function to change price type while change date

4/30/2012 5:31:29 PM Alex
- disable the certain element in form while javascript enable whole form

7/26/2012 11:32 AM Andy
- Add will check user department privilege when add search/add item (need config)

7/27/2012 5:27 PM Andy
- Fix price indicator bug if user have no cost privilege.

8/7/2012 5:56 PM Justin
- Enhanced to show error message when found error during scan barcode.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

12/6/2012 5:16 PM Andy
- Add can filter debtor by description.

12/10/2012 2:31:00 PM Fithri
- Select debtor icon change to button
- Add another button for add branch

12/17/2012 2:49:00 PM Fithri
- Fix bug unable to close Choose Branch/Debtor popup.

12/27/2012 10:32:00 AM Fithri
- when select branch, company name show with branch description

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

10/8/2013 3:06 PM Justin
- Enhanced to use debtor name instead of debtor code for company name.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

6/23/2014 4:52 PM Justin
- Enhanced to have new feature that can insert S/N by range.

7/9/2014 11:33 AM Justin
- Enhanced to have new feature that can change customer info from S/N Details at once from new menu.

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

1/24/2015 4:31 PM Justin
- Enhanced search engine to search debtor code as well.

1/27/2015 11:50 AM Justin
- Enhanced to auto assign debtor's/branch's contact no and email when having serial no.

3/23/2015 10:28 AM Andy
- Fix JS error on checking consignment modules.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.
- Create a new JS class DO_MODULE and merge some JS.

5/8/2015 2:32 PM Andy
- Enhanced to put the default value for do_type.

7/29/2015 10:09 AM Justin
- Bug fixed on system could not resolve the "&" while importing the company name and address from debtor/branch.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

2/16/2016 3:40 PM Andy
- Enhance the row calculate function to can skip calculate all.

3/8/2016 2:23 PM Qiu Ying
- Put added timestamp above DO Date

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

02/14/2017 11:04 AM Zhi Kai
-Change wording from 'General Informations' to 'General Information'.

3/29/2017 4:20 PM Justin
- Enhanced to allow user to key in extra qty instead of prompt error message while found item is existed.
- Enhanced to have new privilege checking for user to reset DO.

4/7/2017 6:09 PM Justin
- Enhanced to have checking for invalid and negative quantity, prompt error message to the user.

5/29/2017 3:44 PM Justin
- Enhanced to update DO items to have BOM information.

11/2/2017 10:13 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.

11/10/2017 1:54 PM Justin
- Bug fixed on wording "Clause" instead of "Claus".

12/5/2017 9:23 AM Justin
- Enhanced debtor list to have Address (Deliver).
- Enhanced to have "Use different Deliver To" which will allow user to key in delivery company name and address.

12/22/2017 2:48 PM Justin
- Enhanced when choose debtor or branch for "Company Name" from billing section, will auto tick the "Use different Deliver To" if found got set "Address (Deliver)".
- Bug fixed on "Debtor (Bill)" have got the wrong address while chosen a Debtor from list.

5/21/2018 11:26 AM Justin
- Bug fixed on choose company using "Choose Branch" option will not uncheck special exemption.

11/22/2018 10:53 AM Justin
- Updated do.js to version 1.

4/19/2019 11:18 AM Justin
- Bug fixed on choose Delivery Address from "Branch" will then return javascript error while not on GST mode.

5/30/2019 11:45 PM Andy
- Enhanced to show Related DO.

5/31/2019 10:18 AM Andy
- Enhanced to show branch code in Related DO link.

3/26/2020 3:15 PM William
- Enhanced to show "Sellerhub Invoice No" when do create from marketplace.

04/20/2020 06:02 PM Sheila
- Modified layout to compatible with new UI.

4/30/2020 6:00 PM William
- Fixed bug Sellerhub Invoice No hidden when submit got error message.

6/23/2020 10:30 AM Sheila
- Updated button css


7/29/2020 6:00 PM Sheila
- Updated button css
*}

{assign var=show_discount value=$config.do_cash_sales_have_discount}
<script>

var bid='{$form.branch_id}';
var create_type='{$form.create_type}';
var current_branch_code = '{$BRANCH_CODE}';
var do_auto_split_by_price_type = '{$config.do_auto_split_by_price_type}';
var show_discount = '{$show_discount}';
var current_total_inv_amt = 0;
var do_type = '{$smarty.request.do_type|default:$form.do_type}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var do_enable_cash_sales_rounding = '{$config.do_enable_cash_sales_rounding}';
var enable_sn_bn = '{$config.enable_sn_bn}';

{if $config.do_allow_credit_sales}
var do_allow_credit_sales = true;
{else}
var do_allow_credit_sales = false;
{/if}

var branch_id_code = [];
{foreach from=$all_branch item=b}
    branch_id_code['{$b.id}'] = '{$b.code}'
{/foreach}

</script>

{assign var=time_value value=1000000000}

{if $form.open_info}
	{assign var=have_select_delivery value=1}
{else}
	{assign var=have_select_delivery value=0}	
{/if}

{assign var=do_type value='open'}

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
</style>

<script type="text/javascript">
{/literal}
{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var must_check_dept = int('{$config.do_must_check_dept}');
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var consignment_modules = int('{$config.consignment_modules}');
var sku_bom_additional_type = int('{$config.sku_bom_additional_type}');

{if $config.masterfile_enable_sa && is_array($form.mst_sa)}
	var sa_from_header = true;
{else}
	var sa_from_header = false;
{/if}

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');
var curr_delivery_type = "bill";

{literal}

var active_search_box = 'ajax_autocomplete';
var needCheckExit = true;

function do_save(){
	document.f_a.a.value='save';
	document.f_a.target = "";
	if(check_a() && chk_open_info()){
		
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

function do_confirm(){
	if(check_a() && chk_open_info()){
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

function check_a(){
	if (empty(document.f_a.do_date, "You must enter DO Date")){
	    return false;
	}
	return true;
}

function ajax_add(parms){
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
					if(json[tr_key]['bom_ref_num'] > 0){ // it is duplicated and match with bom package items
						var bom_ref_num = json[tr_key]['bom_ref_num'];
						alert("The following BOM package is existed, please refer to highlighted area.");
						
						var td_bom_ref_num_list = $$('#do_items td.td_bom_ref_num-'+bom_ref_num);
						
						for(var i=0; i<td_bom_ref_num_list.length; i++){
							var tmp_tr_ele = td_bom_ref_num_list[i].parentNode;
							$(tmp_tr_ele).addClassName('highlight_row');
						}
						return;
					}else{
						var item_id = json[tr_key]['item_id'];
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
					}
				}
			
				if(json[tr_key]['error'] != undefined){
					alert(json[tr_key]['error']);
				}
			
			   	if(json[tr_key]['sn']){
					new Insertion.Bottom($$('.sn_details').first(), json[tr_key]['sn']);
					toggle_sn_details();
	        	}
			
        		if(json[tr_key]['rowdata']) new Insertion.Bottom($('do_items'),json[tr_key]['rowdata']);
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
	    return false;
	}
	ajax_add(Form.serialize(document.f_a)+'&a=ajax_add_item&oi='+oi+'&bid='+bid);
	active_search_box = 'ajax_autocomplete';
	clear_autocomplete();
}

function add_size_color(){
    if (int(document.f_a.sku_item_id.value)==0){
		alert('No item selected');
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

	ajax_add(Form.serialize(document.f_t)+Form.serialize(document.f_a)+'&a=ajax_add_size_color');
	active_search_box = 'ajax_autocomplete';
	cancel_matrix();

}

function row_recalc(item_id,branch_id, skip_calc_all){
	if(!item_id)	return;
	
	if(skip_calc_all == undefined){
		calc_all_items();
	}
	
	
	// if found the following item having S/N
	if($('sn_item'+item_id) != undefined){
		// calculate the following branch qty from SN detail
		recalc_sn_used(item_id, 0);
	}
}

function do_cancel(){
    if (check_login()) {
        if (confirm('Cancel this DO?')){
            needCheckExit=false;
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
            needCheckExit=false;
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

function refresh_tables(){
	needCheckExit = false;
	document.f_a.a.value = "refresh";
	document.f_a.target = "";
	if(chk_open_info()){
		document.f_a.submit();	
	}
}

function chk_open_info(){
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
	/*
	var row_ctn=0, total_ctn=0;
	var row_pcs=0, total_pcs=0;
	
	var do_total_gross_amt = 0;
	var do_total_gst_amt = 0;
	var total_amount=0;
	var total_qty=0;
	var total_inv_amt = 0;
	
	var sub_total_gross_amt = 0;
	var sub_total_gst_amt = 0;
	var sub_total_amt = 0;
	
	var inv_sub_total_gross_amt = 0;
	var inv_sub_total_gst_amt = 0;
	var sub_total_inv_amt = 0;
	
	var inv_gross_discount_amt = 0;
	var inv_gst_discount_amt = 0;
	var inv_discount_amt = 0;
	var inv_sheet_discount_per = 0;
	
	var inv_total_gross_amt = 0;
	var inv_total_gst_amt = 0;
	
	//var do_markup = 0;
	//if(document.f_a['do_markup'])   do_markup = float(document.f_a['do_markup'].value)/100;
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
	
	if ($('do_items')==undefined) return;
	// get all item row
	var all_row_amt = $$('#do_items span.row_amt');
	
	// check again gst price for those default is not selected
	if(is_under_gst){
		for(var i=0; i<all_row_amt.length; i++){
			var item_id = $(all_row_amt[i]).title.split(',')[1];
			
			if(int(document.f_a["gst_id["+item_id+"]"].value)<=0){
				update_selected_gst(item_id);
			}
		}
	}
	
	// loop for each row
	for(var i=0; i<all_row_amt.length; i++){
		var item_id = $(all_row_amt[i]).title.split(',')[1];
		
		// get cost price
		var item_cost = float(document.f_a["cost_price["+item_id+"]"].value);
		// get ctn
		var row_ctn = 0;
		$$('#titem'+item_id+' input.inp_qty_ctn').each(function(inp){
			row_ctn += float(inp.value);
		});
		
		// get pcs
		var row_pcs = 0;
		$$('#titem'+item_id+' input.inp_qty_pcs').each(function(inp){
			row_pcs += float(inp.value);
		});
		// get uom fraction
		item_fraction = float(document.f_a["uom_fraction["+item_id+"]"].value);
		var row_qty = (row_ctn * item_fraction) + row_pcs;
		total_qty += row_qty;
		$('row_qty'+item_id).update(float(row_qty));
		
		//console.log("row_qty = "+row_qty);
		
		var use_cost = item_cost;
		if(do_markup1){
			use_cost *= (1+do_markup1/100);
		}
		if(do_markup2){
			use_cost *= (1+do_markup2/100);
		}
		
		// gst
		var gross_amt = float(round(use_cost*row_qty/item_fraction,2));
		
		sub_total_gross_amt += gross_amt;
		
		var gst_amt = 0;
		if(is_under_gst){
			$('span-gross_amt-'+item_id).innerHTML=round(gross_amt,2);
			var gst_rate = float(document.f_a["gst_rate["+item_id+"]"].value);
			gst_amt = float(round(gross_amt*gst_rate/100,2));
			$('span-gst_amt-'+item_id).update(round(gst_amt, 2));
			
			sub_total_gst_amt += gst_amt;
		}
		
		row_total = float(round2(gross_amt+gst_amt));
		$('row_amount'+item_id).innerHTML=round(row_total,2);
		
		sub_total_amt += row_total;
		total_ctn += row_ctn;
		total_pcs += row_pcs;
		
		total_amount += row_total;
		
		document.f_a["line_gross_amt["+item_id+"]"].value = gross_amt;
		document.f_a["line_gst_amt["+item_id+"]"].value = gst_amt;
		document.f_a["line_amt["+item_id+"]"].value = row_total;
		
		var inv_line_gross_amt = gross_amt;
		var inv_line_gst_amt = 0;
		var inv_line_amt = gross_amt;
		
		if(show_discount){
			// get inv row discount
			var row_inv_discount_format = $('inp_item_discount_'+item_id).value.trim();
			// calculate row inv discount amt
			var row_inv_discount_amt = float(round(get_discount_amt(inv_line_gross_amt, row_inv_discount_format),2));
			if(row_inv_discount_amt){
				inv_line_gross_amt = float(round2(inv_line_gross_amt - row_inv_discount_amt));
			}
			
			inv_sub_total_gross_amt += inv_line_gross_amt;
			
			if(is_under_gst){
				$('span-gross_invoice_amt-'+item_id).update(round(inv_line_gross_amt,2));
				var gst_rate = float(document.f_a["gst_rate["+item_id+"]"].value);
				inv_line_gst_amt = float(round(inv_line_gross_amt * (gst_rate/100), 2));
				$('span-invoice_gst_amt-'+item_id).update(round(inv_line_gst_amt,2));
				
				inv_sub_total_gst_amt += inv_line_gst_amt;
			}
			
			inv_line_amt = float(round2(inv_line_gross_amt + inv_line_gst_amt));
			
			sub_total_inv_amt += inv_line_amt;
			
			$('span_row_invoice_amt_'+item_id).innerHTML = round(inv_line_amt, 2);
			total_inv_amt += inv_line_amt;
			
			// update discount amt
			document.f_a["item_discount_amount["+item_id+"]"].value = row_inv_discount_amt;
			document.f_a["item_discount_amount2["+item_id+"]"].value = 0;
			
			// update all inv related amt
			document.f_a["inv_line_gross_amt["+item_id+"]"].value = inv_line_gross_amt;
			document.f_a["inv_line_gst_amt["+item_id+"]"].value = inv_line_gst_amt;
			document.f_a["inv_line_amt["+item_id+"]"].value = inv_line_amt;
			
			var inv_line_gross_amt2 = inv_line_gross_amt;
			var inv_line_gst_amt2 = inv_line_gst_amt;
			var inv_line_amt2 = inv_line_amt;
			
			document.f_a["inv_line_gross_amt2["+item_id+"]"].value = inv_line_gross_amt2;
			document.f_a["inv_line_gst_amt2["+item_id+"]"].value = inv_line_gst_amt2;
			document.f_a["inv_line_amt2["+item_id+"]"].value = inv_line_amt2;
		}		
	}
	
	// sub total
	if(is_under_gst){
		// sub total gross amt
		$('span-sub_total_gross_amt').update(round2(sub_total_gross_amt));
		// sub total gst amt
		$('span-sub_total_gst_amt').update(round2(sub_total_gst_amt));
	}
	// sub total amt
	$('span-sub_total_amt').update(round2(sub_total_amt));
	
	if(show_discount){
		if(is_under_gst){
			// sub total inv gross amt
			$('span-sub_total_gross_inv_amt').update(round2(inv_sub_total_gross_amt));
			// sub total inv gst amt
			$('span-sub_total_inv_gst_amt').update(round2(inv_sub_total_gst_amt));
		}
		// sub total inv amt
		$('span-sub_total_inv_amt').update(round2(sub_total_inv_amt));
	}
	
	
	// calculate sheet invoice discount amount
	if(show_discount){		
		// sheet discount
		var inv_discount_format = document.f_a['discount'].value.trim();
		inv_discount_format = validate_discount_format(inv_discount_format);
		
		// calculate sheet discount
		var inv_gross_discount_amt = float(round(get_discount_amt(inv_sub_total_gross_amt, inv_discount_format),2));
		var inv_gst_discount = 0;
		
		if(inv_gross_discount_amt){
			// find the inv discount percent
			inv_sheet_discount_per = inv_gross_discount_amt / inv_sub_total_gross_amt;
			//console.log("Discount % = "+inv_sheet_discount_per);
			if(is_under_gst){
				inv_gst_discount = float(round2(float(round2(sub_total_inv_amt*inv_sheet_discount_per))-inv_gross_discount_amt));
			}
			
			// update item amt 2
			for(var i=0; i<all_row_amt.length; i++){
				var item_id = $(all_row_amt[i]).title.split(',')[1];
				
				var inv_line_gross_amt = float(document.f_a["inv_line_gross_amt["+item_id+"]"].value);
				var inv_line_gst_amt = float(document.f_a["inv_line_gst_amt["+item_id+"]"].value);
				var inv_line_amt = float(document.f_a["inv_line_amt["+item_id+"]"].value);
				
				var inv_line_gross_amt2 = float(round(inv_line_gross_amt*(1-inv_sheet_discount_per),4));
				var inv_line_amt2 = float(round(inv_line_amt*(1-inv_sheet_discount_per),4));
				var inv_line_gst_amt2 = float(round(inv_line_amt2-inv_line_gross_amt2,4));
				
				var item_discount_amount2 = float(round(inv_line_gross_amt - inv_line_gross_amt2,4));
				
				document.f_a["inv_line_gross_amt2["+item_id+"]"].value = inv_line_gross_amt2;
				document.f_a["inv_line_gst_amt2["+item_id+"]"].value = inv_line_gst_amt2;
				document.f_a["inv_line_amt2["+item_id+"]"].value = inv_line_amt2;
				
				document.f_a["item_discount_amount2["+item_id+"]"].value = item_discount_amount2;
			}
		}
		
		inv_total_gross_amt = float(round2(inv_sub_total_gross_amt - inv_gross_discount_amt));
		inv_total_gst_amt = float(round2(inv_sub_total_gst_amt - inv_gst_discount));
		
		var inv_discount_amt = float(round2(inv_gross_discount_amt+inv_gst_discount));
		if(inv_discount_amt){
			total_inv_amt -= inv_discount_amt;
		}
		
		// got gst
		if(is_under_gst){
			$('span-inv_gross_discount_amt').update(round2(inv_gross_discount_amt));
			$('span-inv_gst_discount_amt').update(round2(inv_gst_discount));
		}
		
		$('span-inv_discount_amt').update(round2(inv_discount_amt));

		$('span_total_inv_amt').update(round(total_inv_amt,2));
		$('inp_total_inv_amt').value = total_inv_amt;
		
		document.f_a["inv_sheet_gross_discount_amt"].value = inv_gross_discount_amt;
		document.f_a["inv_sheet_gst_discount"].value = inv_gst_discount;
		document.f_a["inv_sheet_discount_amt"].value = inv_discount_amt;
	}
	
	document.f_a["sub_total_gross_amt"].value = sub_total_gross_amt;
	document.f_a["sub_total_gst_amt"].value = sub_total_gst_amt;
	document.f_a["sub_total_amt"].value = sub_total_amt;
	
	document.f_a["inv_sub_total_gross_amt"].value = inv_sub_total_gross_amt;
	document.f_a["inv_sub_total_gst_amt"].value = inv_sub_total_gst_amt;
	document.f_a["sub_total_inv_amt"].value = sub_total_inv_amt;
	
	do_total_gross_amt = sub_total_gross_amt;
	do_total_gst_amt = sub_total_gst_amt;
	
	// total
	$('t_ctn').innerHTML=float(round(total_ctn, global_qty_decimal_points));
	$('t_pcs').innerHTML=float(round(total_pcs, global_qty_decimal_points));
	$('total_ctn').value=float(round(total_ctn, global_qty_decimal_points));
	$('total_pcs').value=float(round(total_pcs, global_qty_decimal_points));
	$('display_total_amount').update(round(total_amount,2));
	document.f_a["do_total_gross_amt"].value = do_total_gross_amt;
	document.f_a["do_total_gst_amt"].value = do_total_gst_amt;
	
	document.f_a["inv_total_gross_amt"].value = inv_total_gross_amt;
	document.f_a["inv_total_gst_amt"].value = inv_total_gst_amt;
	
	$('total_amount').value=round(total_amount,3);
	$('total_qty').value = float(round(total_qty, global_qty_decimal_points));

	if(is_under_gst){
		$('span-do_total_gross_amt').update(round2(do_total_gross_amt));
		$('span-do_total_gst_amt').update(round2(do_total_gst_amt));
	}
	
	if(show_discount){
		if(is_under_gst){
			$('span-inv_total_gross_amt').update(round2(inv_total_gross_amt));
			$('span-inv_total_gst_amt').update(round2(inv_total_gst_amt));
		}
	}
	
	if(do_enable_cash_sales_rounding == 1){
		var total_rounded_amount = rounding(total_amount);
		var rounding_amount = float(total_rounded_amount - total_amount);
		
		if(show_discount){
			var total_rounded_inv_amt = rounding(total_inv_amt);
			var rounding_inv_amt = float(total_rounded_inv_amt - total_inv_amt);
			$('span_rounding_inv_amt').update(round(rounding_inv_amt, 2));
			$('span_ttl_bf_round_inv_amt').update(round(total_inv_amt, 2));
			$('total_round_inv_amt').value = round(rounding_inv_amt, 2);
			$('span_total_inv_amt').update(round(total_rounded_inv_amt, 2));
			$('inp_total_inv_amt').value = round(total_rounded_inv_amt, 2);
		}

		$('span_rounding_amt').update(round(rounding_amount, 2));
		$('span_ttl_bf_round_amt').update(round(total_amount, 2));
		$('total_round_amt').value = round(rounding_amount, 2);
		$('display_total_amount').update(round(total_rounded_amount, 2));
		$('total_amount').value = round(total_rounded_amount, 2);
	}*/
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

function active_btn(){
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
			
	/*var obj = $('tbl_branch').getElementsByClassName('branch');
	for(j=0;j<obj.length;j++){
		if (obj[j].checked){
			$('srefresh').style.display='';
			$('refresh_btn').disabled=false;
			return;
   		}
   		else{  
			$('srefresh').style.display='none';
			$('refresh_btn').disabled=true;
		}
	}*/
}

function select_type(val){	
	if ($('new_sheets') != undefined){
		$('new_sheets').style.display='none'; 
		$('tbl_sku').style.display='none'; 
		$('submitbtn').style.display='none';
	}
	if(val=='2'){
		var obj = $('tbl_branch').getElementsByClassName('branch');
		for(j=0;j<obj.length;j++){
			obj[j].checked=false;
		}
		$('delivery_open').style.display='';
		$('delivery_branches').style.display='none';
		$('srefresh').style.display='';
		$('refresh_btn').disabled=false;
	}
	else{
		$('oi_address').value='';
		$('oi_name').value='';
		$('delivery_branches').style.display='';
		$('delivery_open').style.display='none';
		active_btn();	
	}	
}

var prev_price_indicate;
function refresh_cost(obj){
	if (confirm('Change Price Indicate?')){
	
		ajax_request('do.php',{
			method: 'post',
			parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
					  var form_arr = [];
					  $('span_chaging_price_indicator').show();
					  var form_data = Form.serialize(document.f_a);
					  if ($('pi_1').disabled)	form_arr.push('pi_1');
					  Form.disable(document.f_a);
						new Ajax.Updater('new_sheets', 'do.php', {
							parameters: form_data+'&a=ajax_refresh_cost',
							evalScripts: true,
							onComplete:  function (m) {
								calc_all_items();
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
    $('div_multi_add').hide();
    $('color_size_matrix').hide();
    $('div_choose_branch_to_add').hide();
    if (do_allow_credit_sales) $('div_choose_debtor_to_add').hide();
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
			branch_id: branch_id,
			do_branch_id: do_branch_id,
			do_id: $('inp_do_id').value,
			show_discount: show_discount
			
		},
		evalScripts: true
	});
}

function change_do_branch_id(){
	var bid = document.f_a['branch_id'].value;
	var from_bid = document.f_a['branch_id'].value;
	
	var inputs = $('new_sheets').getElementsBySelector('input.sku_items_list');

	var sku_item_id = [];
	
	for(var i=0; i<inputs.length; i++){
		if(inputs[i].value)
			sku_item_id.push(inputs[i].value);
	}
	
	sku_item_id = sku_item_id.uniq();
	
	ajax_request('do.php',{
	    method: 'post',
		parameters: {
		    a: 'change_do_branch',
		    branch_id: bid,
		    from_branch_id: from_bid,
		    do_date: document.f_a['do_date'].value,
			'sku_item_id[]': sku_item_id
		},
		onComplete: function(e){
			eval("var json_arr = "+e.responseText);
			var json = json_arr['stock_balance'];
			// update price
			for(var i=0; i<sku_item_id.length; i++){
			    // selling price
			    var sp = json[sku_item_id[i]]['selling_price'];
			    if(sp==undefined)   sp = 0;
				//$('selling_price,'+sku_item_id[i]).value = sp;
				$$('#do_items .selling_price_'+sku_item_id[i]).each(function(ele,index){
						ele.value = round(sp, 2);
				});

				// price type
				if(do_auto_split_by_price_type!=''){
                    var price_type = json[sku_item_id[i]]['price_type'];
					if(!price_type || price_type==null) price_type = '';
					//$('span,price_type,'+sku_item_id[i]).update(price_type);
					//$('inp,price_type,'+sku_item_id[i]).value = price_type;
					//var item_id = $('inp,price_type,'+sku_item_id[i]).title;
					//$('inp,price_type,'+sku_item_id[i]).name = 'price_type['+item_id+']['+bid+']';
					
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
				//$('stock_balance,1,'+sku_item_id[i]).value = stock_balance1;
				$$('#do_items .stock_balance_1_'+sku_item_id[i]).each(function(ele,index){
					ele.value = stock_balance1;
				});

				
				// stock balance 2
				var stock_balance2 = json[sku_item_id[i]]['stock_balance2'];
				if(stock_balance2 == undefined)	stock_balance2 = '';
				//$('stock_balance,2,'+sku_item_id[i]).value = stock_balance2;
				$$('#do_items .stock_balance_2_'+sku_item_id[i]).each(function(ele,index){
					ele.value = stock_balance2;
				});

			}
		}
	});
}

change_branch_code_for_stock_balance1 = function(){
	var bid = document.f_a['branch_id'].value;
	var branch_code = '';
	
	if(!bid)   branch_code = current_branch_code;
	else    branch_code = branch_id_code[bid];
	
    $('span_branch_code1').update(branch_code);
    if ($('span_parent_branch_code1') != null) {
        $('span_parent_branch_code1').update(branch_code);
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
		}else{
			$('div_sheet_discount').update('');
			$('tr_sheet_inv_discount_row').hide();
		}
		
		// recalculate 
		calc_all_items();
	}
}

function choose_debtor_to_add(type){
	curtain(true);
	curr_delivery_type = type;
	center_div($('div_choose_debtor_to_add').show());
	
	//if($('tbl_debtor_list'))	fxheaderInit('tbl_debtor_list',300);
}

function choose_branch_to_add(type){
	curtain(true);
	curr_delivery_type = type;
	center_div($('div_choose_branch_to_add').show());
}

function choose_this_debtor(ele){
	var db_code = $(ele).getElementsBySelector('.db_code')[0].innerHTML;
	var db_desc = $(ele).getElementsBySelector('.db_desc')[0];
	var db_address = $(ele).getElementsBySelector('.db_address')[0];
	var db_delivery_address = $(ele).getElementsBySelector('.db_delivery_address')[0];
	var db_contact = $(ele).getElementsBySelector('.db_contact')[0].innerHTML;
	var db_email = $(ele).getElementsBySelector('.db_email')[0].innerHTML;
	
	if(curr_delivery_type == "bill"){
		$('oi_name').value = (db_desc.textContent || db_desc.innerText);
		$('oi_address').value = (db_address.textContent || db_address.innerText);
		$('oi_contact').value = db_contact;
		$('oi_email').value = db_email;
		
		if((db_delivery_address.textContent || db_delivery_address.innerText) != ""){
			$('oi_delivery_name').value = (db_desc.textContent || db_desc.innerText);
			$('oi_delivery_address').value = (db_delivery_address.textContent || db_delivery_address.innerText);
			document.f_a['use_address_deliver_to'].checked = true;
		}else{
			$('oi_delivery_name').value = "";
			$('oi_delivery_address').value = "";
			document.f_a['use_address_deliver_to'].checked = false;
		}
		consignment_branch_clicked(document.f_a['use_address_deliver_to']);
	}else{
		$('oi_delivery_name').value = (db_desc.textContent || db_desc.innerText);
		$('oi_delivery_address').value = (db_delivery_address.textContent || db_delivery_address.innerText);
		if(!$('oi_delivery_address').value.trim()) $('oi_delivery_address').value = (db_address.textContent || db_address.innerText);
		$('oi_delivery_contact').value = db_contact;
		$('oi_delivery_email').value = db_email;
	}
	
	if(enable_sn_bn && curr_delivery_type == "bill"){
		toggle_sn_details(document.f_a['mst_sn_name']);
		toggle_sn_details(document.f_a['mst_sn_address']);
		toggle_sn_details(document.f_a['mst_sn_cn']);
		toggle_sn_details(document.f_a['mst_sn_email']);
	}
	
	// got gst
	if(enable_gst && is_under_gst && curr_delivery_type == "bill"){
		// check whether this debtor is special exemption
		var special_exemption = int($(ele).readAttribute('special_exemption'));
		if(special_exemption){
			document.f_a['is_special_exemption'].checked = true;
			$('tr_special_excemption_rcr').show();
		}else{
			document.f_a['is_special_exemption'].checked = false;
			$('tr_special_excemption_rcr').hide();
		}
	}
	default_curtain_clicked();
}

function choose_this_branch(ele){
	var br_code = $(ele).getElementsBySelector('.br_code')[0].innerHTML;
	var br_desc = $(ele).getElementsBySelector('.br_desc')[0];
	var br_address = $(ele).getElementsBySelector('.br_address')[0];
	var br_delivery_address = $(ele).getElementsBySelector('.br_delivery_address')[0];
	var br_contact = $(ele).getElementsBySelector('.br_contact')[0].innerHTML;
	var br_email = $(ele).getElementsBySelector('.br_email')[0].innerHTML;
	
	if (br_desc) br_code = br_code + ' - ' + (br_desc.textContent || br_desc.innerText);

	if(curr_delivery_type == "bill"){
		$('oi_name').value = br_code;
		$('oi_address').value = (br_address.textContent || br_address.innerText);
		$('oi_contact').value = br_contact;
		$('oi_email').value = br_email;
		
		// always uncheck is special exemption since branch doesn't have this control
		if(document.f_a['is_special_exemption'] != undefined){
			document.f_a['is_special_exemption'].checked = false;
			$('tr_special_excemption_rcr').hide();
		}
	}else{
		$('oi_delivery_name').value = br_code;
		$('oi_delivery_address').value = (br_address.textContent || br_address.innerText);
		$('oi_delivery_contact').value = br_contact;
		$('oi_delivery_email').value = br_email;
	}
	
	if((br_delivery_address.textContent || br_delivery_address.innerText) != ""){
		$('oi_delivery_name').value = (br_desc.textContent || br_desc.innerText);
		$('oi_delivery_address').value = (br_delivery_address.textContent || br_delivery_address.innerText);
		document.f_a['use_address_deliver_to'].checked = true;
	}else{
		$('oi_delivery_name').value = "";
		$('oi_delivery_address').value = "";
		document.f_a['use_address_deliver_to'].checked = false;
	}
	consignment_branch_clicked(document.f_a['use_address_deliver_to']);
	
	if(enable_sn_bn && curr_delivery_type == "bill"){
		toggle_sn_details(document.f_a['mst_sn_name']);
		toggle_sn_details(document.f_a['mst_sn_address']);
		toggle_sn_details(document.f_a['mst_sn_cn']);
		toggle_sn_details(document.f_a['mst_sn_email']);
	}
	
	default_curtain_clicked();
}

function do_markup_changed(){
    var do_markup = document.f_a['do_markup'].value.trim();
    var discount_format = /^\d+(\.\d+){0,1}(\+\d+(\.\d+){0,1}){0,1}$/;
	if(!discount_format.test(do_markup)){
        document.f_a['do_markup'].value = '';
        do_markup = '';
	}
	
	calc_all_items();
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
	if(!sid)    return;
	document.f_a['sku_item_id'].value = sid;
	default_curtain_clicked();
	add_item('','');
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

function check_sa(from_header){
	if(from_header){ // is chosen sa from header
		if(sa_from_header == true || $('do_items') == undefined) return;

		var di_sa = $('do_items').getElementsByClassName("di_sa");

		$A(di_sa).each(
			function (r,idx){
				r.value = "";
				r.options[0].innerHTML = "As Above";
			}
		);
		sa_from_header = true;
	}else{ // is chosen sa from item
		if(sa_from_header == false) return;

		var do_sa = $('do_sa_list').getElementsByClassName("do_sa_list");

		$A(do_sa).each(
			function (r,idx){
				r.checked = false;
			}
		);

		var di_sa = $('do_items').getElementsByClassName("di_sa");

		$A(di_sa).each(
			function (r,idx){
				r.options[0].innerHTML = "--";
			}
		);
		sa_from_header = false;
	}
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

function filter_debtor_desc(){
	var str = document.f_search_debtor['debtor_desc'].value.trim().toLowerCase();
	if(str==''){
        $$('#tbl_debtor_list tr.db_row').each(function(ele){
			$(ele).show();
		});
		return false;
	}
	
	$$('#tbl_debtor_list tr.db_row').each(function(ele){
	    var code = $(ele).getElementsBySelector('.db_code')[0].innerHTML.toLowerCase();
	    var desc = $(ele).getElementsBySelector('.db_desc')[0].innerHTML.toLowerCase();
	    if(desc.indexOf(str)>=0 || code.indexOf(str)>=0)    $(ele).show();
	    else    $(ele).hide();
	});
}

function filter_branch_desc(){
	var str = document.f_search_branch['branch_desc'].value.trim().toLowerCase();
	if(str==''){
        $$('#tbl_branch_list tr.br_row').each(function(ele){
			$(ele).show();
		});
		return false;
	}
	
	$$('#tbl_branch_list tr.br_row').each(function(ele){
	    var desc = $(ele).getElementsBySelector('.br_desc')[0].innerHTML.toLowerCase();
	    if(desc.indexOf(str)>=0)    $(ele).show();
	    else    $(ele).hide();
	});
}

// function when do date changed
function on_do_date_changed(){
	// get the object
	var inp = document.f_a['do_date'];
	// check max/min limit
	upper_lower_limit(inp);
	// check gst
	if(enable_gst)	check_gst_date_changed();
}

// function when do date is changed
function check_gst_date_changed(){
	var allow_gst = false;
	//alert(is_under_gst);
	// gst is not enable
	if(!enable_gst)	return;
	
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
					if(strtotime(do_date) >= strtotime(global_gst_start_date) && strtotime(do_date) >= strtotime(branch_gst_start_date)){
						allow_gst = true;
					}
				}
			}
		}
	}
	//alert(allow_gst);
	if(allow_gst){
		// date have gst
		if(!is_under_gst)	active_btn();
	}else{
		// date no gst
		if(is_under_gst)	active_btn();
	}
}

// function when user change gst dropdown selection
function on_item_gst_changed(sel, item_id){
	// update the selected gst
	update_selected_gst(item_id);
		
	// recalculate row
	row_recalc(item_id); 
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

function toggle_special_exemption(){
	var is_special_exemption = document.f_a["is_special_exemption"];
	if(is_special_exemption.checked == true){
		$('tr_special_excemption_rcr').show();
	}else{
		$('tr_special_excemption_rcr').hide();
	}
}

function consignment_branch_clicked(obj){
	if(obj != undefined){
		if(document.f_a.elements['use_address_deliver_to'].checked == true){
			$$(".tr_adt").each(function(tr){
				tr.style.display = "";
			});
		}else{
			$$(".tr_adt").each(function(tr){
				tr.style.display = "none";
			});
		}
	}
}

</script>
{/literal}
{include file='do.script.tpl'}

<!-- Special Div -->
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

{if $config.do_allow_credit_sales}
	<div id="div_choose_debtor_to_add" style="display:none;position:absolute;z-index:10000;width:750px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
		<div id="div_choose_debtor_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
			<span style="float:left;">Available Debtor  Details</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_choose_debtor_to_add_content" style="padding:2px;">
			<form name="f_search_debtor" onSubmit="filter_debtor_desc();return false;">
		        <b>Filter by Description:</b>
		        <input type="text" size="30" name="debtor_desc" />
		        <input type="submit" value="Refresh" />
		    </form>
		    <form name="f_choose_debtor" onSubmit="return false;">
		    <div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
			<table id="tbl_debtor_list" width="100%">
				<tr style="background:#ffc;">
				    <th width="30">&nbsp;</th>
				    <th width="80">Code</th>
				    <th>Description</th>
				    <th width="30%">Address (Bill)</th>
				    <th width="30%">Address (Deliver)</th>
				</tr>
				<tbody style="background:#fff;">
				{foreach from=$debtor key=id item=r name=f}
				    <tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="choose_this_debtor(this);" special_exemption="{$r.special_exemption}">
				        <td>{$smarty.foreach.f.iteration}.</td>
				        <td>{$r.code}
							<span class="db_code" style="display:none;">{$r.code}</span>
						</td>
				        <td>{$r.description|truncate:30:'...'}
				            <span class="db_desc" style="display:none;">{$r.description}</span>
						</td>
				        <td>{$r.address|truncate:30:'...'}
				            <span class="db_address" style="display:none;">{$r.address}</span>
				            <span class="db_contact" style="display:none;">{$r.phone_1|default:$r.phone_2}</span>
				            <span class="db_email" style="display:none;">{$r.contact_email}</span>
						</td>
				        <td>{$r.delivery_address|truncate:30:'...'}
				            <span class="db_delivery_address" style="display:none;">{$r.delivery_address}</span>
						</td>
				    </tr>
				{/foreach}
				</tbody>
			</table>
			</div>
			<p align="center">
				<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
			</p>
			</form>
		</div>
	</div>
{/if}

<div id="div_choose_branch_to_add" style="display:none;position:absolute;z-index:10000;width:750px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
	<div id="div_choose_branch_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Branch  Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_choose_branch_to_add_content" style="padding:2px;">
		<form name="f_search_branch" onSubmit="filter_branch_desc();return false;">
			<b>Filter by Description:</b>
			<input type="text" size="30" name="branch_desc" />
			<input type="submit" value="Refresh" />
		</form>
		<form name="f_choose_branch" onSubmit="return false;">
		<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
		<table id="tbl_branch_list" width="100%">
			<tr style="background:#ffc;">
				<th width="30">&nbsp;</th>
				<th width="80">Code</th>
				<th>Description</th>
				<th width="30%">Address (Bill)</th>
				<th width="30%">Address (Deliver)</th>
			</tr>
			<tbody style="background:#fff;">
			{foreach from=$branch key=id item=r name=f}
				<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable br_row" onClick="choose_this_branch(this);">
					<td>{$smarty.foreach.f.iteration}.</td>
					<td class="br_code">{$r.code}</td>
					<td>{$r.description|truncate:30:'...'}
						<span class="br_desc" style="display:none;">{$r.description}</span>
					</td>
					<td>{$r.address|truncate:30:'...'}
						<span class="br_address" style="display:none;">{$r.address}</span>
					    <span class="br_contact" style="display:none;">{$r.phone_1|default:$r.phone_2}</span>
				        <span class="br_email" style="display:none;">{$r.contact_email}</span>
					</td>
					<td>{$r.deliver_to|truncate:30:'...'}
						<span class="br_delivery_address" style="display:none;">{$r.deliver_to}</span>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		</div>
		<p align="center">
			<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
		</p>
		</form>
	</div>
</div>
	
<!-- End of Special Div-->
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
{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}
</form>
{/if}

{if !$form.approved}
<h1>Cash Sales DO {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}
</h1>
{else}
<h1>Cash Sales DO {if $form.do_no}(DO/{$form.do_no}){else}{if $form.id<$time_value}(ID#{$form.id}){/if}{/if}
</h1>
{/if}
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
<input type=hidden name=branch_id id="branch_id" value={$form.branch_id|default:$sessioninfo.branch_id}>
<input type=hidden name=id value="{$form.id}" id="inp_do_id">
<input type=hidden name=reason value="">
<input type=hidden name=create_type value="{$form.create_type}">
<input type=hidden name=do_no value={$form.do_no}>
<input type=hidden name=do_type value="{$do_type}">
<!--input type=hidden name=do_branch_id value={$form.do_branch_id}-->
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
<input type="hidden" name="show_discount" value="{$show_discount}" />
<input type="hidden" name="sub_total_inv_amt" id="inp_sub_total_inv_amt" value="{$form.sub_total_inv_amt}" />
<input type="hidden" name="sub_total_foreign_inv_amt" id="inp_sub_total_foreign_inv_amt" value="{$form.sub_total_foreign_inv_amt}"> 
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
<input type="hidden" name="is_mkt" value="{$form.is_mkt}">
<input type="hidden" name="mkt_inv_no" value="{$form.mkt_inv_no}">

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
<th width=160 align=left>DO Date</th>
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
	<select name="dept_id">
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
	    <td><input type="text" name="discount" value="{$form.discount}" size="10" style="text-align:right;" onChange="discount_change();"/> <b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
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
		</td>
	</tr>
{/if}

{if $config.do_cash_sales_show_sales_person_name && !$config.masterfile_enable_sa}
    <tr>
		<th align=left>Sales Person</th>
		<td><input name="sales_person_name" maxlength=100 size=30 value="{$form.sales_person_name}" onchange="uc(this);" id="inp_sales_person_name" /> <span id="span_loading_sales_person_name" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span></td>
		<div id="div_inp_sales_person_name" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</tr>
{/if}

{if $config.masterfile_enable_sa}
	<tr>
		<th align="left" valign="top">Sales Agent</td>
		<td>
			<div style="width:400px;height:100px;border:1px solid #ddd;overflow:auto;" id="do_sa_list">
				{foreach from=$sa_list name=i key=r item=sa}
					{assign var=sa_id value=$sa.id}
					<input type="checkbox" name="do_sa[{$sa_id}]" value="{$sa_id}" {if $form.mst_sa.$sa_id}checked {/if} onchange="check_sa(true);" class="do_sa_list">&nbsp;<label>{$sa.code} - {$sa.name}</label><br />
				{/foreach}
			</div>
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
	<td id='getoptlabel_id'>
		{if $config.sku_multiple_selling_price}
			{foreach from=$config.sku_multiple_selling_price key=i item=e}
			{if $i eq '1'}
				<span class="nowrap"><input type=radio name="price_indicate" id="pi_1" value="1" onchange="refresh_cost(this);" {if $form.price_indicate eq '1' || ($config.do_default_price_from eq 'cost' && !$form.price_indicate)}checked {/if} onClick="{if !$sessioninfo.privilege.SHOW_COST}check_cannot_use_cost_indicator(this);return false;{/if}" /><label for='pi_1'>Cost</label>&nbsp;&nbsp;</span>
			{elseif $i eq '2'}
				<span class="nowrap"><input type=radio name="price_indicate" id="pi_2" value="2" onchange="refresh_cost(this);" {if $form.price_indicate eq '2' || ($config.do_default_price_from eq 'selling' && !$form.price_indicate && $sessioninfo.privilege.SHOW_COST) || (!$config.do_default_price_from && !$form.price_indicate)}checked {/if}><label for='pi_2'>Selling (Normal)</label> &nbsp;&nbsp;</span>
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
{if $form.is_mkt}
<tr>
<td valign=top><b>Sellerhub Invoice No</b></td>
<td>{$form.mkt_inv_no}</td>
</tr>
{/if}

	<tr style="display:none;">
	<td valign=top ><b>Deliver To</b></td>
	<td>
	{if $do_type eq 'transfer'}
		<input type=radio name="create_type" value=1 checked onclick="return false;">Branches
	{elseif $do_type eq 'open'}
		<input type=radio name="create_type" value=2 checked onclick="return false;">Open
	{/if}
	</td>
	</tr>	

	{*<tr id=delivery_branches {if $do_type ne 'transfer'}style="display:none;"{/if}>
		<td colspan=4>
		<table id=tbl_branch width="100%">
		<tr>
		<td valign=top>&nbsp;</td>
		<td>
		You may select multiple branches to deliver <br>
		{if count($branch)<=10}
		{section name=i loop=$branch}
		{assign var=bid value=$branch[i].id}
		<input class="branch" onchange="active_btn();" type=checkbox name="deliver_branch[]" value="{$branch[i].id}" {if is_array($form.deliver_branch) and in_array($branch[i].id,$form.deliver_branch)}checked{/if} id=dt_{$bid} {if $form.id<$time_value}onclick="return false;"{/if}>&nbsp;<label for=dt_{$bid}>{$branch[i].code}</label>&nbsp;&nbsp;
		{/section}
		{else}
		<div style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
		    {section name=i loop=$branch}
				{assign var=bid value=$branch[i].id}
				<input class="branch" onchange="active_btn();" type=checkbox name="deliver_branch[]" value="{$branch[i].id}" {if is_array($form.deliver_branch) and in_array($branch[i].id,$form.deliver_branch)}checked{/if} id=dt_{$bid} {if $form.id<$time_value}onclick="return false;"{/if}>&nbsp;<label for=dt_{$bid}>{$branch[i].code} - {$branch[i].description}</label><br />
			{/section}
		</div>
		{/if}
		</td>
		</tr>
		</table>
		</td>
	</tr>*}
	
	<tr id=delivery_open {if $do_type ne 'open'}style="display:none;"{/if}>
	<td valign=top><b>Deliver To</b></td>
	<td colspan=4>
	<table>
	<tr><td width=100>Company Name</td>
	<td>
		<input id="oi_name" name="open_info[name]" value="{$form.open_info.name}" size=51 onchange="uc(this); {if $config.enable_sn_bn}toggle_sn_details(document.f_a['mst_sn_name']);{/if}"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
		{if $config.do_allow_credit_sales and !$form.approved and !$form.checkout and $form.status eq 0}
			<input type="button" value="Choose Debtor" onclick="choose_debtor_to_add('bill');" />
		{/if}
		<input type="button" value="Choose Branch" onclick="choose_branch_to_add('bill');" />
	</td>
	</tr>
	
	<tr><td valign="top" width="100">Address (Bill)</td>
	<td>
	<textarea id="oi_address" name="open_info[address]" rows="5" cols="38" onchange="uc(this); {if $config.enable_sn_bn}toggle_sn_details(document.f_a['mst_sn_address']);{/if}">{$form.open_info.address}</textarea>
	<input type="hidden" name="open_info[contact_no]" id="oi_contact" value="{$form.open_info.contact_no}" />
	<input type="hidden" name="open_info[email]" id="oi_email" value="{$form.open_info.email}" />
	</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="checkbox" name="use_address_deliver_to" value="1" {if $form.use_address_deliver_to}checked{/if} onclick="consignment_branch_clicked(this);" /> <b>Use different Deliver To</b></td>
	</tr>
	<tr class="tr_adt" {if !$form.use_address_deliver_to}style="display:none;"{/if}>
		<td>Company Name</td>
		<td>
			<input id="oi_delivery_name" name="open_info[delivery_name]" value="{$form.open_info.delivery_name}" size="51" onchange="uc(this);"  placeholder="[Please enter if delivery company name is different]">
			{if $config.do_allow_credit_sales and !$form.approved and !$form.checkout and $form.status eq 0}
				<input type="button" value="Choose Debtor" onclick="choose_debtor_to_add('deliver');" />
			{/if}
			<input type="button" value="Choose Branch" onclick="choose_branch_to_add('deliver');" />
		</td>
	</tr>
	<tr class="tr_adt" {if !$form.use_address_deliver_to}style="display:none;"{/if}>
		<td valign="top">Address (Deliver)</td>
		<td>
			<textarea rows="5" cols="38" name="open_info[delivery_address]" id="oi_delivery_address" placeholder="[Please enter if delivery address is different]">{$form.open_info.delivery_address}</textarea>
			<input type="hidden" name="open_info[delivery_contact_no]" id="oi_delivery_contact" value="{$form.open_info.delivery_contact_no}" />
			<input type="hidden" name="open_info[delivery_email]" id="oi_delivery_email" value="{$form.open_info.delivery_email}" />
		</td>
	</tr>
	</table>
	</td>
	</tr>
	
	{if $config.enable_gst && $form.is_under_gst}
		<tr>
			<td>
				<b>GST Special Exemption [<a href="javascript:void(alert('- This will automatically apply to newly added item, the items already in the document will not be change.'));">?</a>]</b>
			</td>
			<td>
				<input type="checkbox" name="is_special_exemption" value="1" {if $form.is_special_exemption}checked{/if} onclick="toggle_special_exemption();" />
			</td>
		</tr>
		<tr id="tr_special_excemption_rcr" {if !$form.is_special_exemption}style="display:none;"{/if}>
			<td valign="top">
				<b>GST Special Exemption Relief Clause Remark</b>
			</td>
			<td>
				<textarea name="special_exemption_rcr" cols="50" rows="4" class="required"  title="Special Exemption Relief Clause Remark">{$form.special_exemption_rcr}</textarea>
			</td>
		</tr>
	{/if}
</table>

<div id=srefresh style="padding-top:10px; padding-left:130px; ">
<input class="btn btn-success" id=refresh_btn type=button onclick="void(refresh_tables())" style="{if $form.open_info}display:none;{/if}" value="click here to continue" >
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
<li>The following Link Code are INVALID :</li>
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
		<b>Search SKU</b>
		</td>
		<td>
		<input id="autocomplete_sku" name="sku" size=35 onclick="this.select()">
		
		<input class="btn btn-primary" type=button value="Add" onclick="add_item('', '{$bid}')">
		<input class="btn btn-primary" type=button value="Multiple Add" onclick="open_multi_add()">
		<input class="btn btn-primary" type=button value="Add Matrix" onclick="add_size_color()">
		<input class="btn btn-primary" type=button value="Add Parent & Child" onclick="add_parent_child()">
		{if $config.enable_replacement_items}
		    <input class="btn btn-primary" type=button value="Use Replacements" onclick="show_available_replacement_items();" />
		{/if}
		{if $config.do_allow_open_item}
			<input class="btn btn-warning" type=button value="Add Open Item" onclick="add_item('1', '{$bid}')">
		{/if}
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
		<br>
		<img src=ui/pixel.gif width=40 height=1>
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
		{if !$readonly && !$form.approval_screen}
			{include file="do.sn.new.tpl" show_mst_sn_menu=1}
		{/if}
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
	    {if $config.do_auto_split_by_price_type && count($form.deliver_branch)<=1}
	    <span style="color:blue">Once confirm, DO will auto split base on price type.</span><br />
	    {/if}
		{if (!$form.status || $form.status==2) && $have_select_delivery}
		<input class="btn btn-success" name=bsubmit type=button value="Save & Close" onclick="do_save()" >
		{*<input class="btn btn-success" name=bsubmit type=button value="Save & Close" onclick="do_save()" >*}
		{/if}
		
		{if $form.id<$time_value}
		<input class="btn btn-error" type=button value="Delete" onclick="do_delete()">
		{*<input class="btn btn-error" type=button value="Delete" onclick="do_delete()">*}
		{else}
		<input class="btn btn-warning" type=button value="Close" onclick="document.location='/do.php?page={$do_type}'">
		{*<input class="btn btn-warning" type=button value="Close" onclick="document.location='/do.php?page={$do_type}'">*}
		{/if}
		
		{if (!$form.status || $form.status==2) && $have_select_delivery}
		<input type=button class="btn btn-primary" value="Confirm" onclick="do_confirm()">
		{*<input class="btn btn-primary" type=button value="Confirm" onclick="do_confirm()">*}
		{/if}
	{else}
	    {if $form.approved eq 1 && ($sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.DO_ALLOW_USER_RESET)}
	        <input type=button class="btn btn-warning" value="Reset" onclick="do_reset();">
	        {*<input class="btn btn-warning" type=button value="Reset" onclick="do_reset();">*}
	    {/if}

		<input class="btn btn-error" type=button value="Close" onclick="document.location='/do.php?page={$do_type}'">
		{*<input type=button class="btn btn-primary" value="Close" onclick="document.location='/do.php?page={$do_type}'">*}
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
	{/literal}
	init_calendar();
	calc_all_items();
	window.onbeforeunload=confirmExit;
{/if}
prev_price_indicate = $('pi_{$form.price_indicate|default:1}');

{literal}
if($('div_choose_debtor_to_add')){
    new Draggable('div_choose_debtor_to_add',{ handle: 'div_choose_debtor_to_add_header'});
}
if($('div_choose_branch_to_add')){
    new Draggable('div_choose_branch_to_add',{ handle: 'div_choose_branch_to_add_header'});
}

toggle_sn_details();
if($('div_sn_by_range_popup')){
	new Draggable('div_sn_by_range_popup',{ handle: 'div_sn_by_range_popup_header'});
}
{/literal}

// sales person autocomplete
{if $config.do_cash_sales_show_sales_person_name and !$readonly and !$form.approval_screen and !$config.masterfile_enable_sa}
{literal}
	new Ajax.Autocompleter("inp_sales_person_name", "div_inp_sales_person_name", "do.php?a=ajax_get_sales_person_name", {
		paramName: "value",
        indicator: 'span_loading_sales_person_name'
	});
 {/literal}
{/if}

{if ($form.id>$time_value && count($do_items) == 0) || $readonly || $form.approval_screen}
	needCheckExit = false;
{/if}
</script>
