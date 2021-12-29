{*
++++++++++++++++
revision history
++++++++++++++++
8/2/2007 1:49:35 PM - yinsee + gary
- curr_po_id change to curr_po_no (get from doc_no)
- line 377

9/21/2007 3:16:44 PM - gary
- add checking po_no when onchange in doc_no field.
- function check_doc_no().

8/3/2009 3:33:24 PM Andy
- change lock to view GRR

8/20/2009 3:21:43 PM Andy
- add reset remark

2/1/2010 6:57:27 PM Andy
- Add feature to allow process DO (click to generate grr and grn)

2/5/2010 1:56:05 PM Andy
- Add config['grr_process_do'] to control show/hide Process DO

5/28/2010 11:52:39 AM Alex
- add function upper_lower_limit

7/27/2010 5:23:54 PM Alex
- add delete button while edit a grr

9/6/2010 4:53:35 PM Justin
- Added a notification for selected vendor to show the link that allow user to view all uncheckout gra when found records.

6/28/2011 10:58:11 AM Justin
- Modified the report print to show preview page first before print out from printer.

8/4/2011 3:49:21 PM Justin
- Modified the PO Doc No to base on document type while only put as hidden.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/7/2011 11:16:43 AM Justin
- Fixed the bugs where wrongly capture the PO Document No while return from form errors.

9/9/2011 6:52:43 PM Justin
- Modified to have use GRN Future calculation for ttl ctn, pcs and amt for GRR base on below:
  => Invoice + Other
  => DO + other
  => other

9/14/2011 2:02:27 PM Justin
- Fixed the sum up issues while using GRN Future.

7/24/2012 11:31 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

07/15/2013 05:14 PM Justin
- Added "View available DO" feature.

8/15/2013 2:19 PM Andy
- Remove the checking of config "grr_process_do".

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

3/13/2014 2:46 PM Justin
- Enhanced to have new feature that can insert department & vendor automatically when user provides document no by PO/DO.

4/11/2015 11:12 AM Justin
- Enhanced to have GST calculation.

4/22/2015 10:58 AM Justin
- Bug fixed on GST checking get wrong vendor ID.

5/6/2015 5:09 PM Justin
- Enhanced to have document date and GST code selection.
- Enhanced to allow user key in same invoice no if having different tax codes.

5/8/2015 3:57 PM Justin
- Bug fixed on GST amount should not sum up from DO document while GRR having invoice.

5/13/2015 3:20 PM Justin
- Enhanced to allow user can edit previous GRR.

5/18/2015 11:41 AM Justin
- Enhanced to skip those GRR for user to edit while it is created from Transfer DO.

5/18/2015 2:28 PM Justin
- Enhanced to allow user can save GRR without vendor while it is created via Transfer DO.

5/19/2015 11:23 AM Justin
- Bug fixed on recalculate total will stop while user key in grr items from end to start.

5/25/2015 2:00 PM Justin
- Bug fixed on gst rate will start zero if edit back existing GRR.

6/10/2015 3:14 PM Justin
- Enhanced to allow user to reset the GRR first instead of delete it whenever found it is already being used to generate GRN.

6/12/2015 2:36 PM Justin
- Enhanced to have validation on GST amount must have figures when GST rate is not zero percent.
- Enhanced to add note for user to understand what they actually need to key in on amount and GST amount.

6/16/2015 3:44 PM Justin
- Bug fixed on system will still enable GST input when user choose document "PO".

7/15/2015 5:28 PM Andy
- Change to only list latest 3 month GRR.
- Enhanced to show notice if the GRR list got 100 items.

7/22/2015 6:07 PM Joo Chia
- Add in [?] link for Total Amount to alert amount included GST if is under GST.

8/4/2015 2:50 PM Joo Chia
- Fix show/hide GST amount and GST code fields when select doc type radio button.
- Fix show/hide GST amount fields when re-choose vendor (remove gst_fields as class).
- Add in checking on user level to show RESET button.

11/16/2015 10:01 AM DingRen
- highlight the message for GRA Outstanding item

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

01/05/2015 3:06 PM DingRen
- auto bring down to GRR Items after search PO/DO

03/04/2016 17:20 Edwin
- Bug fixed on GRN checking amount overwrite to new GRN amount when it fail to save.
- Bug fixed on GRN invoice counter checker failure after prompt error due to more than one invoice detected

03/29/2016 17:10 Edwin
- Enhanced on allow to save when two same invoice document with different GST code

5/3/2016 5:00 PM Andy
- Enhanced to not allow edit on inactive GRR.

5/4/2016 10:50 AM Andy
- Fix GRR should allow edit if still active and got grn.

5/4/2016 11:09 AM Andy
- Fix duplicate view icon.

05/06/2016 10:30 Edwin
- Bug fixed on save rejected when documents sequence changed.

9/20/2016 13:49 Qiu Ying
- Enhanced to put in [?] to show description for Amount and Total Amount

10/19/2016 16:20 Qiu Ying
- Add remark in "Search from PO/DO" field 

2/7/2017 11:41 Zhi Kai
- Change the name of column 'Amount [?]' to 'Amount incl gst'

4/18/2017 09:20 AM Qiu Ying
- Enhanced to prompt error message when PO cancellation date overdue & upload image

4/25/2017 14:17 Qiu Ying
- Bug fixed on DO number keep enter severed time and this will become a multi reference number


6/20/2017 14:41 Qiu Ying
- Bug fixed on PO number keep enter severed time and this will become a multi reference number

7/18/2017 14:31  Qiu Ying
- Bug fixed on adding note in Photo Attachment
- Enhanced to download the saved attachment
- Enhanced to save pdf file and display pdf file as image in GRR

11/15/2017 9:20 AM Justin
- Enhanced to have "Allow Multiple Department" checkbox.

11/24/2017 5:20 PM Justin
- Enhanced GRR not sum up DO and OTHER amounts when found having PO document.

4/9/2018 5:50 PM Kuan Yeh
- Enhanced GRR adding notes each GRR contain 1 invoice

4/19/2018 3:35 PM Justin
- Enhanced to have foreign currency feature.

7/9/2018 3:00 PM Justin
- Bug fixed on system will allow user to amend the currency type even the GRR have been created into GRN as when no PO attached on the GRR.

8/27/2018 5:59 PM Andy
- Add SST feature.

10/4/2018 5:12 PM Justin
- Enhanced to show warning message when Total GRR Tax is not within certain percentage range.

10/12/2018 1:15 PM Justin
- Enhanced allow user to receive overdue PO (need to provide username and password for privilege checking).

10/19/2018 10:22 AM Justin
- Bug fixed on wrong calculation for tax validation.

5/23/2019 1:43 PM William
- Enhance "GRR" word to use report_prefix.

10/21/2019 1:21 PM Andy
- Fixed GRR unable to delete photo.

06/25/2020 2:25 PM Sheila
- Updated button css.
*}
{include file=header.tpl}
{include file=check_privilege_override.tpl}
{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}


#vendor_checkout{
    background:#ffff00;
    font-weight: bold;
    padding:5px;
}

div.imgrollover
{
	float:left;
	height:105px;
	overflow:hidden;
	border:1px solid transparent;
	padding:2px;
}

div.imgrollover:hover
{
	background:#fff;
	height:130px;
	border:1px solid #999;
	padding:2px;
}

#upload_popup {
	border:2px solid #000;
	background:#fff;
	width:300px;
	height:120px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}
</style>
{/literal}
<script type="text/javascript">

{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var use_grn_future = '{$config.use_grn_future}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var phpself = '{$smarty.server.PHP_SELF}';
var is_under_gst = 0;
var grn_used = '{$form.grn_used|default:0}';
var foreign_currency = '{$config.foreign_currency}';
var GRR_EDIT_CURRENCY_RATE = '{$sessioninfo.privilege.GRR_EDIT_CURRENCY_RATE}';
var grr_tax_amt_var_percent = '{$config.grr_tax_amt_var_percent}';

{literal}

var total_row = 0;
var curr_po_info = {};
var need_fill_po_data = false;
var curr_idx = undefined;

function init_calendar(n){
	if(n == undefined){
		if(grn_used == 0){
			Calendar.setup({
				inputField     :    "rcv",
				ifFormat       :    "%Y-%m-%d",
				button         :    "t_added1",
				align          :    "Bl",
				singleClick    :    true,
				onUpdate	   :	rcv_date_updated
			});
		}
	}else{
		Calendar.setup({
			inputField     :    "doc_date_"+n,
			ifFormat       :    "%Y-%m-%d",
			button         :    "dd_added_"+n,
			align          :    "Bl",
			singleClick    :    true
		});
	}
}

function hideamt(n)
{
    document.f_a.elements["amount["+n+"]"].value=0;
    document.f_a.elements["ctn["+n+"]"].value=0;
    document.f_a.elements["pcs["+n+"]"].value=0;
    document.f_a.elements["gst_amount["+n+"]"].value=0;
    Element.hide(document.f_a.elements["amount["+n+"]"]);
    Element.hide(document.f_a.elements["ctn["+n+"]"]);
    Element.hide(document.f_a.elements["pcs["+n+"]"]);
    Element.hide(document.f_a.elements["gst_amount["+n+"]"]);
    Element.hide(document.f_a.elements["gst_sel["+n+"]"]);
    Element.hide(document.f_a.elements["tax["+n+"]"]);
}

function showamt(n)
{
	is_under_gst = document.f_a['is_under_gst'].value;

    Element.show(document.f_a.elements["amount["+n+"]"]);
    Element.show(document.f_a.elements["ctn["+n+"]"]);
    Element.show(document.f_a.elements["pcs["+n+"]"]);
   	if(is_under_gst == 1 && getRadioValue(document.f_a.elements["type["+n+"]"])!='PO'){	
		Element.show(document.f_a.elements["gst_amount["+n+"]"]);
		Element.show(document.f_a.elements["gst_sel["+n+"]"]);
	}
	Element.show(document.f_a.elements["tax["+n+"]"]);
	document.f_a["po_override_by_user_id["+n+"]"].value = 0;
}

function add_row(n){
	if(document.f_a.elements["doc_no["+(n+1)+"]"] != undefined) return;
	
	var n = total_row++;

	var new_tr = $('temp_row').cloneNode(true).innerHTML;
	new_tr = new_tr.replace(/__id__/g, n);
	new_tr = new_tr.replace(/__arid__/g, (n+1));
	new_tr = new_tr.replace(/__rowno__/g, (n+1)+".");
	
	// set background color for item on even
	if (n%2) bgcolor = '#eee';
	else bgcolor = '';
	new_tr = new_tr.replace(/__bgcol__/g, bgcolor);

	new Insertion.Bottom($('tb'), new_tr);
	
	init_calendar(n);
	toggle_tax_info();
}

function check_doc_no(idx){	
	var obj = document.f_a['doc_no['+idx+']'];
	doc_no=trim(obj.value);
	
	var doc_type = document.f_a['type['+idx+']'].value;
	
	
	// need to exit if not PO document
	if(doc_no.trim() == "" || doc_type == "" || doc_type != "PO") return;
	
	// reset the override user ID
	document.f_a['po_override_by_user_id['+idx+']'].value = 0;
	
	var params = {
		'a': 'process_po_no',
		doc_no: doc_no,
		rcv_date: document.f_a['rcv_date'].value
	}
	
	var q = $H(params).toQueryString();
	
	new Ajax.Request(phpself, {
		parameters: q,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';
			
			try{
				ret = JSON.parse(str); // try decode json object
				
				if(!ret['err']){
					// found have actual po no
					if(ret['new_doc_no']){
						obj.value = ret['new_doc_no'];
						
						// need to check again for the duplicate document no since it got replaced
						if(check_duplicate_doc_no(idx) == true) return;
					}
					
					if(foreign_currency && grn_used == 0){
						// got currency rate
						var reset_currency = 0;
						
						if(document.f_a['use_po_currency'].value == 1 && document.f_a['currency_rate'].value != ret['currency_rate'] && check_po_currency(idx) == false){
							//check_po_currency(idx);
							return;
						}else{
							if(ret['currency_code'] != null && ret['currency_rate'] != null){ // success
								// use the PO currency only when the following GRR does not have any PO with other rates
								if(document.f_a['use_po_currency'].value != 1) alert("This PO contains foreign currency, therefore system will use it in this GRR.");
								document.f_a['sel_currency_code'].value = ret['currency_code'];
								document.f_a['currency_code'].value = ret['currency_code'];
								document.f_a['currency_rate'].value = ret['currency_rate'];
								document.f_a['use_po_currency'].value = 1;
								document.f_a['currency_rate_override_by_user_id'].value = 0;
							}else{
								// always set to base currency if found have errors
								document.f_a['use_po_currency'].value = 1;
								reset_currency = 1;
							}
						}

						toggle_foreign_currency(reset_currency);
						check_gst_status();
					}
					
					// meet overdue cancellation PO, show override window
					if(ret['po_cancelled'] == 1){
						// show error message to user first
						alert(ret['po_cancelled_msg']);
						
						// verify if user has the privilege to override cancellation po
						need_fill_po_data = false;
						override_overdue_po(idx);
						// need to always stop here because PO needs to check overdue stuff
						//return;
					}
					
					return;
				}else{  // load currency rate failed
					if(ret['err']){
						err_msg = ret['err'];
						obj.value = "";
					}
					else err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			// prompt the error
			alert(err_msg);
		}
	});
}

function recalc_row()
{
	var total = 0;
	var totalctn = 0;
	var totalpcs = 0;
	var total_gst = 0;
	var tax_register = int(document.f_a['tax_register'].value);
	
	if(use_grn_future) {
		var inv_ctn=0, inv_pcs=0, inv_amt=0, have_po=0, have_inv=0, do_ctn=0, do_pcs=0, do_amt=0, other_ctn=0, other_pcs=0, other_amt=0, inv_gst=0, do_gst=0, other_gst=0;
		var e = $('tb').getElementsByClassName("doc_type[0]");
		var type_length = e.length;
	}
	var total_grr_tax = 0;
	var inv_tax = do_tax = other_tax = 0;

	for (i=0;i<total_row;i++) {
		if (document.f_a.elements["amount["+i+"]"] != undefined && document.f_a.elements["doc_no["+i+"]"].value != '') {
			if(use_grn_future){
				for(var j=0; j<type_length; j++) {
					if(document.f_a.elements["type["+i+"]"][j].checked) {
						if(document.f_a.elements["type["+i+"]"][j].value == "PO"){
							have_po = 1;
						}if(document.f_a.elements["type["+i+"]"][j].value == "INVOICE"){
							inv_ctn += float(round(document.f_a.elements["ctn["+i+"]"].value, global_qty_decimal_points));
							inv_pcs += float(round(document.f_a.elements["pcs["+i+"]"].value, global_qty_decimal_points));
							inv_amt += float(document.f_a.elements["amount["+i+"]"].value);
							if(document.f_a.elements["gst_amount["+i+"]"] != undefined) {
								inv_gst += float(document.f_a.elements["gst_amount["+i+"]"].value);
							}
							have_inv = 1;
							if(tax_register)	inv_tax += float(document.f_a.elements["tax["+i+"]"].value);
						}else if(document.f_a.elements["type["+i+"]"][j].value == "DO") {
							do_ctn += float(round(document.f_a.elements["ctn["+i+"]"].value, global_qty_decimal_points));
							do_pcs += float(round(document.f_a.elements["pcs["+i+"]"].value, global_qty_decimal_points));
							do_amt += float(document.f_a.elements["amount["+i+"]"].value);
							if(document.f_a.elements["gst_amount["+i+"]"] != undefined) {
								do_gst += float(document.f_a.elements["gst_amount["+i+"]"].value);
							}
							if(tax_register)	do_tax += float(document.f_a.elements["tax["+i+"]"].value);
						}else if(document.f_a.elements["type["+i+"]"][j].value == "OTHER") {
							other_ctn += float(round(document.f_a.elements["ctn["+i+"]"].value, global_qty_decimal_points));
							other_pcs += float(round(document.f_a.elements["pcs["+i+"]"].value, global_qty_decimal_points));
							other_amt += float(document.f_a.elements["amount["+i+"]"].value);
							if(document.f_a.elements["gst_amount["+i+"]"] != undefined) {
								other_gst += float(document.f_a.elements["gst_amount["+i+"]"].value);
							}
							if(tax_register)	other_tax += float(document.f_a.elements["tax["+i+"]"].value);
						}
					}					
				}
			}else {
				total += float(document.f_a.elements["amount["+i+"]"].value);
				totalctn += float(round(document.f_a.elements["ctn["+i+"]"].value, global_qty_decimal_points));
				totalpcs += float(round(document.f_a.elements["pcs["+i+"]"].value, global_qty_decimal_points));
			}
		}
	}
	
	if(use_grn_future) {
		if(have_inv) {
			total += float(inv_amt);
			totalctn += float(inv_ctn);
			totalpcs += float(inv_pcs);
			total_gst += float(inv_gst);
			total_grr_tax += float(inv_tax);
		}else if(have_po == 0){
			total += float(do_amt);
			totalctn += float(do_ctn);
			totalpcs += float(do_pcs);
			total_gst += float(do_gst);
			total_grr_tax += float(do_tax);
		}
		
		if(have_po == 0){
			total += float(other_amt);
			totalctn += float(other_ctn);
			totalpcs += float(other_pcs);
			total_gst += float(other_gst);
			total_grr_tax += float(other_tax);
			
			if(foreign_currency && grn_used == 0){
				if(document.f_a['sel_currency_code'].disabled == true){
					document.f_a['use_po_currency'].value = 0;
					$('tr_currency_rate').style.display = "none";
					toggle_foreign_currency(1);
				}
			}
		}
	}

    if (grn_used == 1) {
        if (total == document.f_a.old_grr_amount.value) {
            $('amt_checker').hide();
        }else{
            $('amt_checker').show();
        }
        
        if (is_under_gst) {
            if (total_gst == document.f_a.old_grr_gst_amount.value) {
                $('gst_amt_checker').hide();
            }else{
                $('gst_amt_checker').show();
            }
        }
    }
	
	document.f_a.grr_amount.value = float(round2(total));
	document.f_a.grr_ctn.value = float(round(totalctn, global_qty_decimal_points));
	document.f_a.grr_pcs.value = float(round(totalpcs, global_qty_decimal_points));
	document.f_a.grr_gst_amount.value = float(round(total_gst, 2));
	document.f_a['grr_tax'].value = float(round(total_grr_tax, 2));
	
	validate_tax_amt_var();
}

// check input and submit form
function check_a()
{
	if (grn_used == 0 && empty_or_zero(document.f_a.vendor_id, 'Please select a Vendor'))
	{
		return false;
	}

	if (empty_or_zero(document.f_a.department_id,'Please select Department'))
	{
		return false;
	}

	if (empty(document.f_a.transport,'Please enter Lorry No'))
	{
		return false;
	}
		
	var i;
	var count = 0;
	var is_date_err = 0;
	for (i=0;i<total_row;i++)
	{
		if (document.f_a.elements["doc_no["+i+"]"].value == '')
		{
			continue;
		}
		count++;
		if (rdcb_empty(document.f_a.elements["type["+i+"]"], 'You must select document type'))
		{
			return false;
		}
		if (getRadioValue(document.f_a.elements["type["+i+"]"])!='PO')
		{
			if (empty(document.f_a.elements["amount["+i+"]"],'Please enter document\'s amount'))
			{
				return false;
			}
		}
	}

	if (count==0)
	{
		alert('You must enter at least 1 document');
		return false;
	}
		
	if (check_login()) {
        document.f_a.bsubmit.disabled=true;
        if (document.f_a.bdelete){
            document.f_a.bdelete.disabled=true;
        }
        document.f_a.submit();
    }
}

function do_print(bno,bid)
{
	document.fprnt.id.value=bno;
	document.fprnt.branch_id.value=bid;
	curtain(true);
	document.fprnt.print_worksheet.checked=false;
	alert('GRN Worksheet is unselected by default.\nTick GRN Worksheet if you want to print it.');
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	$('print_dialog').style.display = 'none';
	//document.fprnt.target = "ifprint";
	document.fprnt.target = "_blank";
	document.fprnt.submit();
	curtain(false);
}

function print_all()
{
	document.fprnt.print_vendor_copy.checked = true;
	document.fprnt.print_branch_copy.checked = true;
	print_ok();
}

function print_cancel(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function close_available_dialog(){
	$('available_po').style.display = 'none';
	$('available_do').style.display = 'none';
}

function show_available_po(element){
	if (empty_or_zero(document.f_a.vendor_id, 'Please select a Vendor')){
	    return false;
	}
	
	if (empty_or_zero(document.f_a.department_id,'Please select Department')){
	    return false;
	}
	//curtain(true);
	Position.clone(element, $('available_po'), {setHeight: false, setWidth:false, offsetBottom: -parseInt($('available_po').style.height)});
	Element.show('available_po');
	$('available_po').innerHTML = "<img src=\"ui/clock.gif\" align=\"absmiddle\"> Loading...";

	new Ajax.Updater('available_po',phpself,{
	    method:'post',
	    parameters: Form.serialize(document.f_a) +'&a=ajax_load_available_po',
	    evalScripts: true
	});

}

function submit_do_no(){
  var do_no = document.f_do['do_no'].value.trim();
  if(do_no=='') return false;
  
}

function toggle_process_grr(){
  if($('div_do_no').style.display=='')  $('div_do_no').hide();
  else  $('div_do_no').show();
}

/*****************alex********************/
function delete_a(){
    if (check_login()) {
        document.f_a.a.value='delete';
        document.f_a.bsubmit.disabled=true;
        document.f_a.bdelete.disabled=true;
        document.f_a.submit();
    }
}

function vendor_check(){
    hidediv("vendor_checkout");
	new Ajax.Updater("vendor_checkout", "ajax_autocomplete.php?a=ajax_vendor_checkout&vendor_id="+document.f_a.vendor_id.value,{ onComplete:function(){
            if($("vendor_checkout").innerHTML!="") showdiv("vendor_checkout");
        }
    });
}

function show_available_do(element){
	Position.clone(element, $('available_do'), {setHeight: false, setWidth:false, offsetBottom: -parseInt($('available_do').style.height)});
	Element.show('available_do');
	$('available_do').innerHTML = "<img src=\"ui/clock.gif\" align=\"absmiddle\"> Loading...";

	new Ajax.Updater('available_do',phpself,{
	    method:'post',
	    parameters: Form.serialize(document.f_a) +'&a=ajax_load_available_do',
	    evalScripts: true
	});

}

function ajax_search_doc_info(event){
	var k = event.keyCode;
	var search_doc_no = document.f_a['search_doc_no'].value;
	search_doc_no = search_doc_no.trim();
	//var search_doc_type = $$('input:checked[type=radio][name=search_doc_type]')[0].value;
	var search_doc_type = getRadioValue(document.f_a['search_doc_type']);
	var rcv_date = document.f_a['rcv_date'].value;
	if(!search_doc_no.trim() || k!=13) return;
	
	parms='a=ajax_search_doc_info&doc_no='+search_doc_no+"&doc_type="+search_doc_type+"&rcv_date="+rcv_date;
	ajax_request(phpself,
	{
		method:'post',
		parameters: parms,
		evalScripts: true
		,
		onFailure: function(m) {
			alert(m.responseText);
			obj.value='';
			obj.focus();
		},
		onSuccess: function (m) {
			eval("var json = "+m.responseText);
            tr_key=0;

			// if no such document no
			if(json[tr_key]['no_data'] == 1){
				alert("No record was found for Document No ["+search_doc_no+"]");
				throw $break;
			}

			// if the vendor was not found
			if(json[tr_key]['no_vendor'] == 1){
				alert("Vendor was not found for Document No ["+search_doc_no+"]");
				throw $break;
			}
			
			// ensure the user is not key in the existing document no
			var total = $$('#tb tr.gra_items').length-1;
			for(var i = 0; i < total; i++){
				var tmp_doc_type = getRadioValue(document.f_a['type['+i+']']);
				if(tmp_doc_type == search_doc_type && document.f_a['doc_no['+i+']'].value == search_doc_no){
					alert("Doc No ["+document.f_a['doc_no['+i+']'].value+"] is existed in this GRR");
					throw $break;
				}
			}
			
			// do below if it is searching PO document
			if(search_doc_type == "PO"){
				// found the user trying to combine 2 different currency rates of PO
				if(foreign_currency && document.f_a['use_po_currency'].value == 1 && document.f_a['currency_rate'].value != json[tr_key]['currency_rate']){
					alert("GRR could not have more than one PO with multiple exchange rates.");
					throw $break;
				}
			
				// meet overdue cancellation PO, show override window
				if(json[tr_key]['po_cancelled'] == 1){
					curr_po_info = json[tr_key]; // to be used after override is success
					
					// show error message to user first
					alert(json[tr_key]['po_cancelled_msg']);
					
					// verify if user has the privilege to override cancellation po
					need_fill_po_data = true;
					override_overdue_po();
					// need to always stop here because PO needs to check overdue stuff
					throw $break;
				}
			}
			
			if(json[tr_key]['vendor_id'] > 0){
				document.f_a['vendor_id'].value = json[tr_key]['vendor_id'];
				document.f_a['vendor'].value = json[tr_key]['vd_desc'];
			}
			
			if(json[tr_key]['department_id'] > 0){
				document.f_a['department_id'].value = json[tr_key]['department_id'];
			}
			
			if(search_doc_type == "PO" && foreign_currency){
				if(json[tr_key]['currency_rate'] != 1){
					alert("This PO contains foreign currency, therefore system will use it in this GRR.");
					document.f_a['sel_currency_code'].value = json[tr_key]['currency_code'];
					document.f_a['currency_code'].value = json[tr_key]['currency_code'];
					document.f_a['currency_rate'].value = json[tr_key]['currency_rate'];
					document.f_a['use_po_currency'].value = 1;
					toggle_foreign_currency();
				}
			}
			
			if(json[tr_key]['tax_register']>0){
				document.f_a['tax_register'].value = json[tr_key]['tax_register'];
				document.f_a['tax_percent'].value = float(json[tr_key]['tax_percent']);
			}

			if (search_doc_type=='DO') {
				for(i in json[tr_key]['items']){
					add_new_row(json[tr_key]['items'][i],search_doc_type);
				}
			}else add_new_row(json[tr_key],search_doc_type);
			
			check_tax_status();
			check_gst_status();
		},

	});
}

function check_gst_status(){
	if(foreign_currency){
		if(document.f_a['currency_code'].value != ""){
			// set to no gst if found use foreign currency
			document.f_a['is_under_gst'].value = 0;
			$('tr_currency_rate').style.display = "";
			toggle_tax_info();
			return;
		}else $('tr_currency_rate').style.display = "none";
	}
	
	if(document.f_a['vendor_id'].value == 0 || document.f_a['rcv_date'].value == "") return;
	
	if(document.f_a['tax_register'].value > 0){	// Already Tax Registered
		document.f_a['is_under_gst'].value = 0;
		toggle_tax_info();
		return;
	}	

	var prms = "a=ajax_check_gst_status&id="+document.f_a['vendor_id'].value+"&date="+document.f_a['rcv_date'].value;
	ajax_request(phpself, {
		method:'post',
		parameters: prms,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {	
			var str = m.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['is_under_gst'] != undefined){ // success
					document.f_a['is_under_gst'].value = ret['is_under_gst'];
					toggle_tax_info();
					return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}
		},
	});
}

function toggle_tax_info(){
	is_under_gst = document.f_a['is_under_gst'].value;
	var tax_register = int(document.f_a['tax_register'].value);
	
	// GST Column and Field
	var gst_info = $('grr_tbl').getElementsByClassName("gst_info");
	gst_info_count = gst_info.length;
	if(gst_info_count > 0){
		$A(gst_info).each(
			function (tr,idx){				
				if(!is_under_gst || is_under_gst == 0){ // hide all GST info if this vendor is not under gst registered.
					$(tr).style.display = "none";
					
				}else{ // show all gst info
					$(tr).style.display = "";
				}
			}
		);
	}

	var gst_fields = $('grr_tbl').getElementsByClassName("gst_fields");
	gst_fields_count = gst_fields.length;
	if(gst_fields_count > 0){
		$A(gst_fields).each(
			function (tr,idx){				
				if(!is_under_gst || is_under_gst == 0){ // hide all GST info if this vendor is not under gst registered.
					$(tr).style.display = "none";
					$(tr).value = 0;
				}else{ // show all gst info
					$(tr).style.display = "";
				}
			}
		);
	}
	
	// Tax Column and Field
	var tax_info = $('grr_tbl').getElementsByClassName("tax_info");
	tax_info_count = tax_info.length;
	if(tax_info_count > 0){
		$A(tax_info).each(
			function (tr,idx){
				if(!is_under_gst || is_under_gst == 0){ // hide all GST info if this vendor is not under gst registered.
					$(tr).style.display = "";
				}else{
					$(tr).style.display = "none";
				}
			}
		);
	}
	
	$$('input.grr_item_tax').each(function(inp){
		if(!tax_register){
			inp.value = 0;
			inp.readOnly = true;
		}else{
			inp.readOnly = false;
		}
	});
}

function rcv_date_updated(cal){
	check_gst_status();
}

function check_duplicate_doc_no(n){
	if(n == undefined) return;
	
	var doc_no = document.f_a['doc_no['+n+']'].value; 
	var doc_date = document.f_a['doc_date['+n+']'].value; 
	var doc_type = getRadioValue(document.f_a['type['+n+']']);
	var gst_id = document.f_a['gst_id['+n+']'].value;
	var total = $$('#tb tr.gra_items').length-1; // last one is always blank
    
	if(total && doc_no!='') {
		var is_duplicated=0, inv_err = 0;
		for(var i=0; i<total; i++) {
			var tmp_doc_no = trim(document.f_a['doc_no['+i+']'].value);
			var tmp_doc_type = getRadioValue(document.f_a['type['+i+']']);
			var tmp_doc_date = trim(document.f_a['doc_date['+i+']'].value);
			var tmp_gst_id = document.f_a['gst_id['+i+']'].value;
			if(i != n) {
				if(tmp_doc_no==doc_no && tmp_doc_type == doc_type && tmp_gst_id == gst_id) { // found having same doc no, type and gst
					is_duplicated = 1;
				}
                if(doc_type == "INVOICE" && tmp_doc_type == "INVOICE") {
                    if (tmp_doc_no==doc_no && tmp_gst_id == gst_id || tmp_doc_no!=doc_no) {
                        inv_err = 1;
                    }
                }
			}
		}
        
		if(is_duplicated==1 || inv_err==1) {
			if(is_duplicated==1) {alert("Doc No ["+doc_no+"] is existed in this GRR");}
			else {alert("Could not have more than one Invoice at GRR");}
            var radio = document.f_a['type['+n+']'];
            for(var i=0; i<radio.length; i++) {
                radio[i].checked = false;
            }
			return true;
		}
	}
	
	return false;
}

function on_item_gst_changed(sel, n){
	document.f_a["gst_id["+n+"]"].value = "";
	document.f_a["gst_code["+n+"]"].value = "";
	document.f_a["gst_rate["+n+"]"].value = "";
	
	if(sel.selectedIndex >= 0){
		// got select
		var opt = sel.options[sel.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		
		document.f_a["gst_id["+n+"]"].value = gst_id;
		document.f_a["gst_code["+n+"]"].value = gst_code;
		document.f_a["gst_rate["+n+"]"].value = gst_rate;
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

function add_new_row(obj,type) {
    var lastChild = $$('.gra_items');
    var row=lastChild[lastChild.length-1];
    var idx=$(row).readAttribute("data-idx");

    if (type=="DO") {		
		if(idx > 0){
			var total = $$('#tb tr.gra_items').length-1;
			for(var i = 0; i < total; i++){
				if(document.f_a['doc_no['+i+']'].value == obj.do_no){
					alert("Doc No ["+document.f_a['doc_no['+i+']'].value+"] is existed in this GRR")
					return;
				}
			}
		}
		
        document.f_a['doc_no['+idx+']'].value = obj.do_no;
        document.f_a['doc_date['+idx+']'].value = obj.do_date;
        document.f_a['type['+idx+']'].value = type;
        document.f_a['ctn['+idx+']'].value = obj.ctn;
        document.f_a['pcs['+idx+']'].value = obj.pcs;
        document.f_a['amount['+idx+']'].value = obj.amount;
        document.f_a['gst_amount['+idx+']'].value = obj.gst;
        document.f_a['gst_sel['+idx+']'].value = obj.gst_id;

        var event = new Event('change');
        document.f_a['gst_sel['+idx+']'].dispatchEvent(event);
    }

    if (type=="PO") {
		if(idx > 0){
			var total = $$('#tb tr.gra_items').length-1;
			for(var i = 0; i < total; i++){
				if(document.f_a['doc_no['+i+']'].value == "PO" && document.f_a['doc_no['+i+']'].value == obj.po_no){
					alert("Doc No ["+document.f_a['doc_no['+i+']'].value+"] is existed in this GRR");
					return;
				}
			}
			
			
			
		}
		
        document.f_a['doc_no['+idx+']'].value = obj.po_no;
        document.f_a['doc_date['+idx+']'].value = obj.po_date;
        document.f_a['type['+idx+']'].value = type;
        hideamt(idx);
    }


    add_row(idx);
    recalc_row();
}

function del_image(obj,fp)
{
	ajax_request('goods_receiving_record.php',
		{
			method: 'get',
			parameters: 'a=ajax_remove_photo&f='+fp,
			onComplete: function(m) {
				if (m.responseText == 'OK')
				{
					obj.remove();
				}
				else
					alert(m.responseText);
			}
		}
	);
}

function download_image(obj,fp)
{
	$('_download').src = phpself+"?a=download_photo&f="+fp;
}

function add_image()
{
	if(document.f_a['tmp'] && document.f_a['tmp'].value != "undefined"){
		document.upl.tmp_time.value= document.f_a['tmp'].value;	
	}
	
	$('upload_popup').show();
	center_div('upload_popup');
	curtain(true);
}

function curtain_clicked()
{
	$('upload_popup').hide();
	curtain(false);
}

function upload_check()
{
	if (!/\.jpg|\.jpeg|\.png|\.pdf/i.test(document.upl.fnew.value))
	{
		alert("Selected file must be a valid JPEG image, PNG image or PDF file");
		return false;
	}
	
	return true;
}

function upload_callback(content)
{
	new Insertion.Bottom($('item_photos'), content.innerHTML);
	document.upl.fnew.value = '';
	curtain_clicked();
}

function reload_currency_rate(){
	if(document.f_a['use_po_currency'].value == 1) return; // no need to get exchange rate if PO have currency rate

	var currency_code = document.f_a['sel_currency_code'].value;
	document.f_a['currency_code'].value = currency_code;
	var date = document.f_a['rcv_date'].value;
	
	// always insert currency rate as 1 if found user choose base currency or it's empty
	if(currency_code.trim() == ""){
		document.f_a['currency_rate'].value = 1;
		check_gst_status();
		return;
	}
	
	if(date.trim() == ""){
		alert("Please choose a receive date");
		document.f_a['currency_rate'].value = '';
		return;
	}
	
	$('span_currency_rate_loading').update(_loading_);
	
	var params = {
		'a': 'loadCurrencyRate',
		date: date,
		currency_code: currency_code
	}
	//q += '&'+$H(params).toQueryString();
	var q = $H(params).toQueryString();
	
	new Ajax.Request(phpself, {
		parameters: q,
		method: 'post',
		onComplete: function(msg){
			$('span_currency_rate_loading').update('');

			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';
			
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['rate']){ // success
					// got currency rate
					document.f_a['currency_rate'].value = ret['rate'];
					check_gst_status();
					return;
				}else{  // load currency rate failed
					if(ret['err'])	err_msg = ret['err'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			// prompt the error
			alert(err_msg);
			
			// always set to base currency if found have errors
			toggle_foreign_currency(1);
			check_gst_status();
		}
	});
}

function toggle_foreign_currency(is_reset){
	// found requires to reset the currency to base
	if(is_reset == 1){
		document.f_a['sel_currency_code'].value = "";
		document.f_a['currency_code'].value = "";
		document.f_a['currency_rate'].value = 1;
	}
	
	if(document.f_a['use_po_currency'].value == 0){
		document.f_a['sel_currency_code'].disabled = false;
	}else{
		document.f_a['sel_currency_code'].disabled = true;
	}
	
	// show / hide currency rate menu
	if(document.f_a['currency_code'].value == "") $('tr_currency_rate').style.display = "none";
	else $('tr_currency_rate').style.display = "";
	
	// always need to unset the override user id since the currency rate has been reloaded
	document.f_a['currency_rate_override_by_user_id'].value = 0;
}

function check_po_currency(idx){
	// check if user trying to add 2 po with multiple currencies in one GRR
	var total = $$('#tb tr.gra_items').length-1; // last one is always blank
	var first_po_idx = null;
    
	// check if the list already have other PO
	if(total) {
		var has_po = 0;
		for(var i=0; i<total; i++) {
			var tmp_doc_no = trim(document.f_a['doc_no['+i+']'].value);
			var tmp_doc_type = getRadioValue(document.f_a['type['+i+']']);
			var tmp_doc_date = trim(document.f_a['doc_date['+i+']'].value);
			var tmp_gst_id = document.f_a['gst_id['+i+']'].value;
			if(i != idx && tmp_doc_type == "PO") { // found grr items already have po
				if(idx == undefined || i != idx){
					has_po = 1;
					break;
				}
			}
		}
		
		if(has_po == 1 && document.f_a['use_po_currency'].value == 1){
			alert("GRR could not have more than one PO with multiple exchange rates.");
			document.f_a['po_override_by_user_id['+idx+']'].value = 0;
			
			if(idx != undefined){
				var radio = document.f_a['type['+idx+']'];
				for(var i=0; i<radio.length; i++) {
					radio[i].checked = false;
				}
			}
			return false;
		}
	}
	
	return true;
}

function change_currency_rate(){
	PRIV_CHECK.check_privilege('GRR_CHANGE_CURRENCY_RATE', change_currency_rate_callback);
}

function change_currency_rate_callback(override_by_user_id){
	var new_rate = float(prompt("Please key in new exchange rate."));
	if(new_rate<=0){
		alert('Exchange Rate must more than 0.');
		return;
	}
	
	document.f_a['currency_rate'].value = new_rate;
	document.f_a['currency_rate_override_by_user_id'].value = override_by_user_id;
}

function check_tax_status(){
	var tax_register = int(document.f_a['tax_register'].value);
	
	if(tax_register){
		document.f_a['tax_percent'].readOnly = false;
	}else{
		document.f_a['tax_percent'].readOnly = true;
		document.f_a['tax_percent'].value = 0;
	}
	
}

function tax_percent_changed(){
	var inp = document.f_a['tax_percent'];
	inp.value = float(inp.value);
	if(inp.value<=0)	inp.value = 0;
	
	validate_tax_amt_var();
}

function validate_tax_amt_var(){
	// check if the grr tax is lower / higher than document total tax keyed in by user
	if(document.f_a['tax_register'].value > 0 && (document.f_a['tax_percent'].value > 0 || document.f_a['grr_tax'].value > 0)){
		var grr_tax_amt = round2(float(round2(document.f_a['grr_amount'].value)) / (100 + float(document.f_a['tax_percent'].value)) * float(document.f_a['tax_percent'].value));
		var doc_tax_amt = float(round2(document.f_a['grr_tax'].value));
		
		var grr_min_tax_amt = round2(float(grr_tax_amt) * (100 - float(grr_tax_amt_var_percent)) / 100);
		var grr_max_tax_amt = round2(float(grr_tax_amt) * (100 + float(grr_tax_amt_var_percent)) / 100);
		
		if(doc_tax_amt < grr_min_tax_amt || doc_tax_amt > grr_max_tax_amt){
			$('tax_amt_checker').update("*Total GRR Tax is not within "+grr_tax_amt_var_percent+"% range (should between "+round2(grr_min_tax_amt)+" to "+round2(grr_max_tax_amt)+")");
			$('tax_amt_checker').show();
			
		}else $('tax_amt_checker').hide();
	}else $('tax_amt_checker').hide();
}

function override_overdue_po(idx){
	if(idx != undefined) curr_idx = idx;
	else curr_idx = undefined;

	PRIV_CHECK.check_privilege('GRR_ALLOW_EXPIRED_PO', override_overdue_po_callback);
}

function override_overdue_po_callback(override_by_user_id){

	// load last row index
	if(curr_idx == undefined){
		var lastChild = $$('.gra_items');
		var row=lastChild[lastChild.length-1];
		curr_idx=$(row).readAttribute("data-idx");
	}
	
	document.f_a['po_override_by_user_id['+curr_idx+']'].value = override_by_user_id;
	
	if(override_by_user_id > 0 && need_fill_po_data == true) fill_data_from_po();
}

function fill_data_from_po(){
	var po_info = curr_po_info;
	
	if(po_info['vendor_id'] > 0){
		document.f_a['vendor_id'].value = po_info['vendor_id'];
		document.f_a['vendor'].value = po_info['vd_desc'];
	}
	
	if(po_info['department_id'] > 0){
		document.f_a['department_id'].value = po_info['department_id'];
	}
	
	if(foreign_currency && document.f_a['use_po_currency'].value != 1){
		document.f_a['use_po_currency'].value = 1;
		if(po_info['currency_rate'] != 1){
			alert("This PO contains foreign currency, therefore system will use it in this GRR.");
			document.f_a['sel_currency_code'].value = po_info['currency_code'];
			document.f_a['currency_code'].value = po_info['currency_code'];
			document.f_a['currency_rate'].value = po_info['currency_rate'];
		}
		toggle_foreign_currency();
	}
	
	if(po_info['tax_register']>0){
		document.f_a['tax_register'].value = po_info['tax_register'];
		document.f_a['tax_percent'].value = float(po_info['tax_percent']);
	}

	add_new_row(po_info, "PO");
	
	check_tax_status();
	check_gst_status();
}

</script>
{/literal}

<!-- popup div -->
<div id="upload_popup" style="display:none;">
<form onsubmit="return upload_check()" name="upl" target="_ifs" enctype="multipart/form-data" method="post">
<h4>Select an image or PDF file to add</h4>
<input type="hidden" name="a" value="add_photo">
<input type="hidden" name="tmp_time" value="">

<input name="fnew" type="file"><br>
*must be a valid JPEG image, PNG image or PDF file
<br><br><input type="submit" value="Upload"> <input type="button" value="Cancel" onclick="curtain_clicked()">
</form>
<iframe name="_ifs" width="1" height="1" style="visibility:hidden"></iframe>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $smarty.request.t eq 'save'}
<img src=/ui/approved.png align="absmiddle"> GRR saved as {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"}<br>
{elseif $smarty.request.t eq 'reset'}
<img src=/ui/notify_sku_reject.png align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was reset.
{elseif $smarty.request.t eq 'delete'}
<img src=/ui/notify_sku_terminate.png align="absmiddle" width="15"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was deleted.
{/if}

<div id="available_po" style="position:absolute;left:0;top:0;display:none;width:400px;height:320px;padding:10px;border:1px solid #000; background:#fff;z-index:10000;">
</div>

<div id="available_do" style="position:absolute;left:0;top:0;display:none;width:400px;height:320px;padding:10px;border:1px solid #000; background:#fff;z-index:10000;">
</div>

<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:250px;height:100px;position:absolute; padding:10px; display:none;">
<form name="fprnt" method="get">
<img src="ui/print64.png" hspace="10" align="left"> <h3>Print Options</h3>
<input type="hidden" name="a" value="print">
<input type="hidden" name="id" value="">
<input type="hidden" name="branch_id" value="">
<input type="checkbox" name="print_grr" checked> GRR<Br>
<input type="checkbox" name="print_worksheet"> GRN Worksheet<br>
<p align="center"><input type="button" value="Print" onclick="print_ok()"> <input type="button" value="Cancel" onclick="print_cancel()"></p>
</form>
</div>

<iframe width="1" height="1" style="visibility:hidden" name="ifprint"></iframe>

<table style="display:none;">
	<tbody id="temp_row" class="temp_row">
		<tr class="gra_items" bgcolor="__bgcol__" data-idx="__id__">
			<td>__rowno__</td>
			<td>
				<input name="id[__id__]" type="hidden">
				<input name="po_override_by_user_id[__id__]" type="hidden">
				<input id="doc___id__" name="doc_no[__id__]" size="15" onchange="uc(this);add_row(__id__); check_duplicate_doc_no(__id__); check_doc_no(__id__); recalc_row();" maxlength="20">
			</td>
			<td nowrap>
				<input name="doc_date[__id__]" id="doc_date___id__" maxlength="10" size="8" readonly>
				<img align="absmiddle" src="ui/calendar.gif" id="dd_added___id__" style="cursor: pointer;" title="Select Document Date">
			</td>
			<td align="center">
				<input onclick="hideamt('__id__'); check_duplicate_doc_no(__id__); check_doc_no(__id__); recalc_row();" type="radio" name="type[__id__]" class="doc_type[__id__]" value="PO">
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="INVOICE">
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="DO">
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="OTHER">
			</td>
			<td><input name="ctn[__id__]" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points})); recalc_row();" size="7" class="r"></td>
			<td><input name="pcs[__id__]" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points})); recalc_row();" size="7" class="r"></td>
			<td><input name="amount[__id__]" onchange="mf(this); recalc_row();" size="10" class="r" maxlength="10"></td>
			<td class="gst_info"><input name="gst_amount[__id__]" onchange="mf(this); recalc_row();" size="10" class="r" maxlength="10"></td>
			<td class="gst_info">
				<select name="gst_sel[__id__]" onchange="on_item_gst_changed(this, __id__); check_duplicate_doc_no(__id__);">
					{foreach from=$gst_list key=rid item=gst}
						<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $acc_gst_id eq $gst.id and $acc_gst_code eq $gst.code and $acc_gst_rate eq $gst.rate}selected {/if}>{$gst.code} ({$gst.rate}%)</option>
					{/foreach}
				</select>
				<input type="hidden" name="gst_id[__id__]" value="{$gst_list.0.id}" />
				<input type="hidden" name="gst_code[__id__]" value="{$gst_list.0.code}" />
				<input type="hidden" name="gst_rate[__id__]" value="{$gst_list.0.rate}" />
			</td>
			
			{* Tax *}
			<td class="tax_info"><input name="tax[__id__]" onchange="mf(this); recalc_row();" size="10" class="r grr_item_tax" maxlength="10" /></td>
			
			<td><input size="50" class="small" name="remark[__id__]"></td>
		</tr>
	</tbody>
</table>

<form name="f_do_reset" method="post" style="display:none;">
<input type="hidden" name="a" value="do_reset">
<input type="hidden" name="branch_id" value="{$form.branch_id}">
<input type="hidden" name="id" value="{$form.grr_id}" >
<input type="hidden" name="reason" value="">
<input type="hidden" name="rcv_date" value="{$form.rcv_date}">
</form>

{* if !$config.single_server_mode and $config.grr_process_do}
<p>
  <form name="f_do" method="post" onSubmit="return submit_do_no();">
    <input type="hidden" name="a" value="process_do_no">
    <img src="ui/grr_tb.png" align="absmiddle"> <a href="javascript:void(0)" onClick="toggle_process_grr();">Process GRR by DO NO</a>
    {if $smarty.request.err_msg}<span style="color:red;">({$smarty.request.err_msg})</span>{/if}
    {if $smarty.request.msg}<span style="color:blue;">({$smarty.request.msg})</span>{/if}
    
    <div id="div_do_no" style="display:none;"> 
    <b>Enter DO NO:</b>
    <input type="text" name="do_no" size="10" /> 
    <input type="submit" value="Submit" />
    </div>
    
  </form>
</p>
{/if *}

<form>

	
	<div class="card mx-3">
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 mt-2">
					<img src="ui/grr_tb.png" align="absmiddle">
				<a href="javascript:void(togglediv('tlist'))">&nbsp;Show/Hide recent GRR</a>
				</div>

				<div class="col-md-6">
					<div class="form-inline">
						<b class="form-label">Find Document No.</b>
						&nbsp;<input class="form-control" name="find_grr" value="{$smarty.request.find_grr}" size="22"> 
						&nbsp;<input class="btn btn-primary mt-2 mt-md-0 fs-08" type="submit" value="Find">
					</div>
				</div>

			</div>
		</div>
	</div>	
</form>
		
<div id="tlist" {if !$smarty.request.find_grr}style="display:none"{/if}>
{assign var=nr_colspan value=12}
{assign var=rmk_colspan value=4}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" style= cellpadding="4"class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr>
						<td>&nbsp;</td>
						<th>GRR No.</th>
						<th>Department</th>
						<th>Vendor Code</th>
						{if $config.enable_vendor_account_id}
							<th>Account ID</th>
							{assign var=nr_colspan value=$nr_colspan+1}
							{assign var=rmk_colspan value=$rmk_colspan+1}
						{/if}
						<th>Vendor</th>
						<th>Lorry No.</th>
						<th>Total Ctn</th>
						<th>Total Pcs</th>
						<th>Amount</th>
						<th>Received</th>
						<th>By</th>
						<th width="16">&nbsp;</th>
					</tr>
				</thead>
				<tbody class="fs-08 mt-2" {if count($grr) > 5}style="height:200px;overflow:auto;"{/if}>
				{assign var=grr_id value=''}
				{section name=i loop=$grr}
				{if $grr_id ne $grr[i].grr_id}
				{assign var=grr_id value=`$grr[i].grr_id`}
				<tr style="font-weight:bold;" bgcolor="{cycle values=',#eeeeee'}">
					<td>
					<a href="javascript:void(do_print({$grr[i].grr_id},{$grr[i].branch_id}))"><img src="ui/print.png" title="Print GRR & GRN Worksheet" border="0"></a>
					{if $grr[i].status==0 and $grr[i].active==1}
						<a href="{$smarty.server.PHP_SELF}?a=open&id={$grr[i].grr_id}&branch_id={$grr[i].branch_id}"><img src="ui/ed.png" border="0" title="Edit this GRR"></a>
					{elseif $grr[i].status>0 and $grr[i].active==1}
						{*<img src="ui/lock.png" border="0" title="GRR is used" class="clickable" onclick="alert('This GRR is already used by GRN, editing is not allowed.')">*}
						{if $grr[i].grn_id}
						<a href="{$smarty.server.PHP_SELF}?a=open&id={$grr[i].grr_id}&branch_id={$grr[i].branch_id}"><img src="ui/ed.png" border="0" title="Edit this GRR"></a>
						{else}
						<a href="{$smarty.server.PHP_SELF}?a=view&id={$grr[i].grr_id}&branch_id={$grr[i].branch_id}" target="_blank"><img src="ui/view.png" border="0" title="View this GRR" ></a>
						{/if}
					{else}
						<a href="{$smarty.server.PHP_SELF}?a=view&id={$grr[i].grr_id}&branch_id={$grr[i].branch_id}" target="_blank"><img src="ui/view.png" border="0" title="View this GRR" ></a>
					{/if}
				
					</td>
					<td>
					{if $grr[i].active eq 0}
					<img src="ui/icons/page_delete.png" border="0" title="Inactive GRR" >
					{/if} {$grr[i].report_prefix}{$grr[i].grr_id|string_format:"%05d"}</td>
					<td>{$grr[i].department}</td>
					<td>{$grr[i].vendor_code}</td>
					{if $config.enable_vendor_account_id}
						<td>{$grr[i].account_id}</td>
					{/if}
					<td>{$grr[i].vendor}</td>
					<td>{$grr[i].transport}</td>
					<td align="right">{$grr[i].grr_ctn|qty_nf}</td>
					<td align="right">{$grr[i].grr_pcs|qty_nf}</td>
					<td align="right">
						{if !$grr[i].currency_code}
							{$grr[i].grr_amount|number_format:2}
						{else}
							{$grr[i].currency_code} {$grr[i].grr_amount|number_format:2}
							<br />
							{assign var=base_grr_amount value=$grr[i].grr_amount*$grr[i].currency_rate}
							{assign var=base_grr_amount value=$base_grr_amount|round2}
							<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
						{/if}
					</td>
					<td align="right">{$grr[i].rcv_date|date_format:"%Y-%m-%d"}</td>
					<td align="center">{$grr[i].rcv_u}</td>
				</tr>
				{/if}
				<tr class="small" bgcolor="{cycle values=',#eeeeee'}">
					<td>{$grr[i].type}</td>
					<td>{$grr[i].doc_no}</td>
					<td colspan="{$rmk_colspan}">Remark: {$grr[i].remark|default:"-"}</td>
					{if $grr[i].type eq 'PO'}
					<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
					{else}
						<td align="right">{$grr[i].ctn|qty_nf}</td>
						<td align="right">{$grr[i].pcs|qty_nf}</td>
						<td align="right">
							{if !$grr[i].currency_code}
								{$grr[i].amount|number_format:2}
							{else}
								{$grr[i].currency_code} {$grr[i].amount|number_format:2}
								<br />
								{assign var=base_grr_amount value=$grr[i].amount*$grr[i].currency_rate}
								{assign var=base_grr_amount value=$base_grr_amount|round2}
								<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
							{/if}
						</td>
					{/if}
					<td>&nbsp;</td>
					<td align="center">{$grr[i].u}</td>
				</tr>
					{if $smarty.section.i.iteration eq 100}
						<tr>
							<td colspan="{$nr_colspan}" align="center" style="background-color:#ffc;">This list will only show latest 100 GRR in this 3 months, use "Find Document" to search those older GRR.</td>
						</tr>
					{/if}
				{/section}
				{if count($grr) eq 0}
					<tr><td colspan="{$nr_colspan}" align="center">No record found</td></tr>
				{/if}
				</tbody>
				</table>
		</div>
	</div>
</div>
<br>
</div>


		<div id="grr_tbl">
			<form name="f_a" method="post" action="{$smarty.server.PHP_SELF}" onsubmit="return check_a()">
			<input type="hidden" name="a" value="save">
			<input type="hidden" name="edit_on" value="{$form.edit_on}">
			<input type="hidden" name="grr_id" value="{$form.grr_id}">
			<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}">
			<input type="hidden" name="user_id" value="{$form.user_id|default:$sessioninfo.id}">
			<input type="hidden" name="is_under_gst" value="{$form.is_under_gst|default:0}">
			<input type="hidden" name="grn_used" value="{$form.grn_used|default:0}" />
			<input type="hidden" name="use_po_currency" value="{$form.use_po_currency|default:0}" />
			<input type="hidden" name="tax_register" value="{$form.tax_register|default:0}" />
			
			{if $errm.top}
			<div id="err"><ul class="errmsg" style="list-style-type:none;">
			{foreach from=$errm.top item=e}
			<div class="alert alert-danger rounded mx-3">
				<li> {$e} </li>
			</div>
			
			{/foreach}
			</ul></div>
			{/if}
			<div class="card mx-3">
				<div class="card-body">
			<table class="stdframe"  cellpadding="4" cellspacing="0" border="0">
			<tr>
				<div class="row">
					<div class="col-md-2">
						<b class="form-label mt-2">Search from PO/DO</b>
					</div>
					<div class="col-md-3">
							<div class="form-inline">
								<input class="form-control" type="text" id="search_doc_no" name="search_doc_no" value="" onkeyup="ajax_search_doc_info(event);" size="10" {if $form.grn_used}readonly{/if} />
							<span id="loading_area"></span>&nbsp;
							<input type="radio" name="search_doc_type" value="PO" {if $form.edit_on}disabled{else}checked{/if} />&nbsp;PO&nbsp;
							<input type="radio" name="search_doc_type" value="DO" {if $form.edit_on}disabled{else}checked{/if} />&nbsp;DO
							</div>
					</div>
				<div class="col-md-2">
						{if $config.do_skip_generate_grn}
						<input class="btn btn-primary mt-2 mt-md-0" type="button" value="Find available DO" onclick="show_available_do(this)">
						{/if}
				</div>
				</div>
				{if $config.foreign_currency}
					<td valign="top" align="right" colspan="4" rowspan="2">
						<table>
							<tr>
								<td><b class="form-label">Currency</b></td>
								<td>
									<select class="form-control" name="sel_currency_code" {if $form.grn_used || $form.use_po_currency}disabled{/if} onchange="reload_currency_rate();">
										<option value="" {if !$form.currency_code}selected{/if}>Base Currency</option>
										<optgroup label="Foreign Currency">
											{foreach from=$foreignCurrencyCodeList item=code}
												<option value="{$code}" {if $form.currency_code eq $code}selected {/if}>{$code}</option>
											{/foreach}
										</optgroup>
									</select>
									
									
									<input type="hidden" name="currency_code" value="{$form.currency_code}" />
								</td>
							</tr>
							<tr id="tr_currency_rate" {if !$form.currency_code}style="display:none;"{/if}>
								<td><b class="form-label">Exchange Rate</b></td>
								<td>
									<input class="form-control" type="hidden" name="currency_rate_override_by_user_id" value="{$form.currency_rate_override_by_user_id}" />
									{if !$sessioninfo.privilege.GRR_CHANGE_CURRENCY_RATE && !$form.grn_used}
										<img src="/ui/ed.png" align="absmiddle" onclick="change_currency_rate();" title="Change Rate">
									{/if}
									<input class="form-control" type="text" name="currency_rate" size="7" value="{$form.currency_rate|default:1}" {if !$sessioninfo.privilege.GRR_CHANGE_CURRENCY_RATE}readonly{else}onchange="this.value=float(this.value);"{/if} />
									<span id="span_currency_rate_loading"></span>
								</td>
							</tr>
						</table>
					</td>
				{/if}
			</tr>
			<tr>
				
				<div class="alert alert-primary mt-3" style="max-width: 400px;">
					Note: Press Enter after key in PO/DO number
				</div>
			</tr>
				<div class="row">
					<div class="col-md-2">
						<b class="form-label"> Vendor<span class="text-danger" title="Required Field"> *</span></b>
					</div>
					<div class="col-md-3">
							<input class="from-control" id="vendor_id" name="vendor_id" type="hidden" value="{$form.vendor_id}" readonly>
							<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=80 {if $form.edit_on}disabled{/if} >
							<input class="form-control" id="autocomplete_vendor" type="hidden" name="vendor_descrip" value="{$form.vendor}" size=80 >
							<div id="autocomplete_vendor_choices" class="autocomplete"></div>
					</div>
					<div class="col-md-2">
						{if !$form.grn_used}
							<input class="btn btn-primary ml-0 ml-md-2 mt-2 mt-md-0 " type="button" value="Find available PO" onclick="show_available_po(this)" class="grr_info">
							{/if}
					</div>
				</div>
			<tr>
				<td colspan="2"><div id="vendor_checkout" style="Display:none;"></div></td>
			</tr>
				<div class="row mt-3">
					<div class="col-md-2">
						<b class="form-label mt-2">Department<span class="text-danger" title="Required Field"> *</span></b>
					</div>
					<div class="col-md-3 mt-2">
						<input type="checkbox" name="allow_multi_dept" {if $form.allow_multi_dept}checked{/if} value="1" /> <b class="text-dark">&nbsp;Allow Different Department PO</b>
					</div>
					<div class="col-md-4 mt-2 mt-md-0">
						<select class="form-control" name="department_id" {if $form.grn_used}onfocus="this.blur();"{/if}>
							&nbsp;<option value="0">-- Select Department --</option>
							{section name=i loop=$dept}
							<option value="{$dept[i].id}" {if $form.department_id == $dept[i].id}selected{/if}>{$dept[i].description}</option>
							{/section}
						</select> 
					</div>
				</div>
			
				<div class="row mt-3">

					<div class="col-md-4">
						<b class="form-label">Lorry No.<span class="text-danger" title="Required Field"> *</span></b>
					<input class="form-control" name="transport" onchange="ucz(this)" value="{$form.transport}" size="10" maxlength="10" class="grr_info" {if $form.grn_used}readonly{/if}>
					</div>
					
				
					<div class="col-md-4">
						<b class="form-label">Received By<span class="text-danger"> *</span></b>
					<select class="form-control" name="rcv_by" {if $form.grn_used}onfocus="this.blur();"{/if}>
					{section name=i loop=$rcv}
					<option value="{$rcv[i].id}" {if ((!$form.rcv_by && $rcv[i].id == $sessioninfo.id) || ($form.rcv_by && $rcv[i].id == $form.rcv_by))}selected{/if}>{$rcv[i].u}</option>
					{/section}
					</select>
					</div>
				
					<div class="col-md-4">
						<b class="form-label">Received Date<span class="text-danger" title="Required Field"> *</span></b>
					<div class="form-inline">
						<input class="form-control" name="rcv_date" id="rcv" onchange="upper_lower_limit(this); check_gst_status(); {if !$form.grn_used}reload_currency_rate();{/if}" maxlength="10" value="{if $form.rcv_date}{$form.rcv_date|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}" size="10" {if $form.grn_used}readonly{/if}>
					{if !$form.grn_used}
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
					{/if}
					</div>
					</div>

				</div>
			
			
				<div class="row mt-2">
					<div class="col-md-4">
						<b class="form-label">Total Carton</b>
					<input  name="grr_ctn" value="{$form.grr_ctn}" size="10" class="r form-control" readonly>
					</div>
					<div class="col-md-4">
						<b class="form-label">Total Pcs</b>
					<input  name="grr_pcs" value="{$form.grr_pcs}" size="10" class="r form-control" readonly>
					</div>
				</div>

			
					<div class="row mt-2">
						<div class="col-md-4">
							<div class="from-inline form-label"><b>Total Amount</b> <a href="javascript:void(alert('Amount Inclusive Tax.'));">[?]</a></div>
						<input name="grr_amount" value="{$form.grr_amount}" size="10" class="r form-control" readonly>
						<input type="hidden" name="old_grr_amount" value="{$form.old_grr_amount}" />
						<span id="amt_checker" style="background-color: yellow; color: red; font-weight: bold; display: none">*must equal to {$form.old_grr_amount|number_format:2}</span>
						</div>
					
						<div class="col-md-4 gst_info ">
							<b class="form-label">Total GST Amount</b>
						<input  name="grr_gst_amount" value="{$form.grr_gst_amount}" size="10" class="form-control r gst_fields" readonly>
						<input type="hidden" name="old_grr_gst_amount" value="{$form.old_grr_gst_amount}" />
						<span id="gst_amt_checker" style="background-color: yellow; color: red; font-weight: bold; display: none">*must equal to {$form.old_grr_gst_amount|number_format:2}</span>
						</div>
					</div>
			
			
			<div class="row tax_info mt-2">
					<div class="col-md-4">
						<div class="form-label form-inline"><b >Tax</b> <a href="javascript:void(alert('{$LANG.GRR_TAX_PERCENT_INFO|escape:javascript}'));">&nbsp;[?]</a></div>
						<div class="form-inline">
							<input name="tax_percent" value="{$form.tax_percent|default:0}" size="23" class="form-control r" readonly onChange="tax_percent_changed();" /> %
						</div>
					</div>
					
					<div class="col-md-4">
						<b class="form-label">Total GRR Tax</b>
					<input clas name="grr_tax" value="{$form.grr_tax|default:0}" size="10" class="r readonly />
					<span id="tax_amt_checker" style="background-color: yellow;font-weight: bold; display: none;"></span> 
					</div>
			</div>

			<div class="row mt-3">
				<div class="col-md-6">
					
					<h5 class="form-label">Photo Attachment [<a href="javascript:void(alert('If the pdf preview failed to load, please contact system admin'))">?</a>] <img src="/ui/add.png" align="absmiddle" onclick="add_image()"></h5>
					<div id="item_photos">
						{if $tmp}
							<input name="tmp" value="{$tmp}" type="hidden"/>
						{/if}
						{foreach from=$photo_items item=p name=i}
							<div class="imgrollover">
								<div align="center" width="auto" height="auto">
									<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p.image_file|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$p.image_file|escape:javascript}');" title="View">
								</div>
								<img src="/ui/del.png" align="absmiddle" onclick="if (confirm('Are you sure?'))del_image(this.parentNode,'{$p.image_file|urlencode}')"> Delete
								{if $form.grr_id}
									<img src="/ui/application_put.png" align="absmiddle" valign="right" onclick="download_image(this.parentNode,'{$p.download_file|urlencode}')"> Download
								{/if}
							</div>
						{/foreach}
					</div>
				</div>
			</div>
				
			</table>
			</div>
			</div>
			<div class="card mx-3">
				<div class="card-body">
		
			<div class="alert alert-primary rounded">* Each GRR can only contain 1 invoice.<br />
				{if $config.foreign_currency}
					* The Exchange Rate will not be changed regardless of the change of Receiving Date if the document contains a PO with Foreign Currency.<br />
				{/if}</div>

		
			 
			<div class="table-responsive">
				<table id="tb"   class="report_table table mb-0 text-md-nowrap  table-hover">
					<thead class="bg-gray-100">
						<tr  class="small">
							<th rowspan="2">&nbsp;</th>
							<th rowspan="2">Reference No</th>
							<th rowspan="2">Document Date</th>
							<th colspan="4">Document Type</th>
							<th rowspan="2">Received<br>Carton</th>
							<th rowspan="2">Received<br>Pcs</th>
							<th rowspan="2">Amount<br />Incl Tax<a href="javascript:void(alert('Please key in amount that Included Tax.'));">[?]</a></th>
							<th rowspan="2" class="gst_info">GST Amount <a href="javascript:void(alert('Please key in tax amount only.'));">[?]</a></th>
							<th rowspan="2" class="gst_info">GST Code</th>
							
							{* Tax *}
							<th rowspan="2" class="tax_info">Tax Amount</th>
							<th rowspan="2">Remark</th>
						</tr>
						<tr  class="small bg-gray-100">
							<th width="50">PO</td>
							<th width="50">INVOICE</td>
							<th width="50">DO</td>
							<th width="50">OTHER</td>
						</tr>
					</thead>
					{assign var=idx value=0}
					{foreach from=$form.id key=n item=dummy name=i}
					{if $form.doc_no[$n] ne '' or $form.amount[$n]}
					<tbody class="fs-08">
						<tr class="gra_items" id="tr_{$idx}" bgcolor="{if $errm[$n]}#ff9999{else}{cycle name='r0' values=',#eeeeee'}{/if}" data-idx="{$idx}">
							<td nowrap>{$idx+1}.
							{if $form.id[$n]}<img src="/ui/remove16.png" valign="absmiddle" class="clickable" onclick="if (confirm('Are you sure?')) {literal}{{/literal} $('doc_{$idx}').value='';Element.hide('tr_{$idx}');recalc_row();{literal}}{/literal}" title="delete row">{/if}
							</td>
							<td>
								<input class="form-control" type="hidden" name="id[{$idx}]" value="{$form.id[$n]}">
								<input type="hidden" name="po_override_by_user_id[{$idx}]" value="{$form.po_override_by_user_id[$n]}">
								
								{if $form.type[$n] eq 'PO' && $form.id[$n]}
									<input type="hidden" name="curr_po_no[{$idx}]" value="{$form.curr_po_no[$n]}">
								{/if}
								<input class="form-control" id="doc_{$idx}" name="doc_no[{$idx}]" value="{$form.doc_no[$n]}" size="15" onchange="uc(this);add_row({$idx}); check_duplicate_doc_no({$idx}); check_doc_no({$idx}); recalc_row();" maxlength="20">
								
							</td>
							<td nowrap>
								<div class="form-inline">
									<input class="form-control" name="doc_date[{$idx}]" id="doc_date_{$idx}" value="{$form.doc_date[$n]|ifzero:''}" maxlength="10" size="8" readonly>
								<img align="absmiddle" src="ui/calendar.gif" id="dd_added_{$idx}" style="cursor: pointer;" title="Select Document Date">
								</div>
	
								<script>init_calendar('{$idx}');</script>
							</td>
							<td align="center">
								<input type="radio" class="doc_type[{$idx}]" onclick="hideamt({$idx}); check_duplicate_doc_no({$idx}); check_doc_no({$idx}); recalc_row();" name="type[{$idx}]" value="PO" {if $form.type[$n] eq 'PO'}checked{/if}>
							</td>
							<td align="center">
								<input type="radio" class="doc_type[{$idx}]" onclick="showamt({$idx}); recalc_row(); check_duplicate_doc_no({$idx});" name="type[{$idx}]" value="INVOICE" {if $form.type[$n] eq 'INVOICE'}checked{/if}>
							</td>
							<td align="center">
								<input type="radio" class="doc_type[{$idx}]" onclick="showamt({$idx}); recalc_row(); check_duplicate_doc_no({$idx});" name="type[{$idx}]" value="DO" {if $form.type[$n] eq 'DO'}checked{/if}>
							</td>
							<td align="center">
								<input type="radio" class="doc_type[{$idx}]" onclick="showamt({$idx}); recalc_row(); check_duplicate_doc_no({$idx});" name="type[{$idx}]" value="OTHER" {if $form.type[$n] eq 'OTHER'}checked{/if}>
							</td>
							<td><input name="ctn[{$idx}]" value="{$form.ctn[$n]}" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points})); recalc_row();" size="7" class="r form-control" {if $form.type[$n] eq 'PO'}style="display:none"{/if}></td>
							<td><input name="pcs[{$idx}]" value="{$form.pcs[$n]}" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points})); recalc_row();" size="7" class="r form-control" {if $form.type[$n] eq 'PO'}style="display:none"{/if}></td>
							<td><input name="amount[{$idx}]" value="{$form.amount[$n]}" onchange="mf(this); recalc_row();" size="10" maxlength="10" class="r form-control" {if $form.type[$n] eq 'PO'}style="display:none"{/if}></td>
							<td class="gst_info">
								<input  name="gst_amount[{$idx}]" value="{$form.gst_amount[$n]}" onchange="mf(this); recalc_row();" size="10" maxlength="10" class="r form-control" {if $form.type[$n] eq 'PO'}style="display:none"{/if}></td>
							<td class="gst_info">
								<select class="form-control" name="gst_sel[{$idx}]" onchange="on_item_gst_changed(this, {$idx}); check_duplicate_doc_no({$idx});">
									{foreach from=$gst_list key=rid item=gst}
										<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $form.gst_id[$n] eq $gst.id and $form.gst_code[$n] eq $gst.code and $form.gst_rate[$n] eq $gst.rate}selected {/if}>{$gst.code} ({$gst.rate}%)</option>
									{/foreach}
								</select>
								<input class="form-control" type="hidden" name="gst_id[{$idx}]" value="{$form.gst_id[$n]|default:$gst_list.0.id}" />
								<input class="form-control" type="hidden" name="gst_code[{$idx}]" value="{$form.gst_code[$n]|default:$gst_list.0.code}" />
								<input class="form-control" type="hidden" name="gst_rate[{$idx}]" value="{if $form.gst_id[$n]}{$form.gst_rate[$n]}{else}{$gst_list.0.rate}{/if}" />
							</td>
							
							{* Tax *}
							<td class="tax_info">
								<input name="tax[{$idx}]" value="{$form.tax[$n]}" onchange="mf(this); recalc_row();" size="10" maxlength="10" class="r grr_item_tax form-control" {if $form.type[$n] eq 'PO'}style="display:none"{/if} />
							</td>
							
							<td><input size="50" class="small form-control" name="remark[{$idx}]" value="{$form.remark[$n]}"></td>
						</tr>
					</tbody>
					
					<script>
					total_row = {$idx+++1};
					</script>
					
					{if $errm[$n]}
					<tr>
					<td>&nbsp;</td>
					<td colspan="8" class="errmsg">
					<font class="small">
					{foreach from=$errm[$n] item=e}
					&middot; {$e}<br>
					{/foreach}
					</font>
					</td></tr>
					{/if}
					
					{/if}
					{/foreach}
					</table>
			</div>
				
			</div>
		</div>
			<p align="center">
			<input class="btn btn-success mt-3" id="submitbtn" name="bsubmit" type="button" value="Save GRR" onclick="check_a()">
			{if $form.grn_used}
			{if $sessioninfo.level>=$config.doc_reset_level}
				<input type="button" value="Reset" class="btn btn-danger mt-3" onclick="do_reset();">
			{/if}
			{elseif $form.edit_on}
				<input id="deletebtn" class="btn btn-danger mt-3" name="bdelete" type="button" value="Delete GRR" style="font:bold 20px Arial; background-color:#f00; color:#fff;" onclick="delete_a()">
			{/if}
			</p>
			
			</form>
			</div>
	
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>
{include file=footer.tpl}

{literal}

<script>
init_calendar();
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&block=grr",
{ afterUpdateElement: function (obj, li)
{
	var  s = li.title.split(",");
	if(s[0]==0){	// No Vendor Selected
		$('autocomplete_vendor').value = '';
		return;
	}
	
	var tax_register = $(li).readAttribute('tax_register');
	var tax_percent = $(li).readAttribute('tax_percent');
	
	document.f_a.vendor_id.value = li.title;	// Vendor ID
	document.f_a['tax_register'].value = tax_register;	// Tax Register
	document.f_a['tax_percent'].value = tax_percent; // Tax Percent	
	
	vendor_check();
	check_tax_status();
	check_gst_status();
	validate_tax_amt_var();
}});
add_row();
recalc_row();
toggle_tax_info();

//new Draggable('available_po');
</script>
{/literal}