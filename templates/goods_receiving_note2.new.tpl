{*
9:16 AM 7/4/2011 Yinsee
- Fixed the grn barcoder focus to add missing '.

7/27/2011 11:54:42 AM Justin
- Added UOM selection, Ctn and Return Ctn columns.
- Added new JS function to for these fields.
- Amended previous functions to compatible with these changes.
- Fixed the JS sum up value with decimal points problem.

8/2/2011 12:10:21 PM Justin
- Fixed the genereate GRA not able to tick if the document type is not PO.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- Added new function to replace onchange function for ctn and pcs while adding new SKU item base on allow decimal points.

9/8/2011 3:36:32 PM Justin
- Fixed the hidden field for capture GRR amount to always capture from total GRR amount.
- Modified the cost which taken from database to insert into popup form.
- Modified the GRN report printing engine not to be compulsory but optional.

9/20/2011 4:30:43 PM Justin
- Added the hidden field for storing document no.

9/22/2011 4:42:59 PM Justin
- Added item sequence counting from JS.
- Fixed the bugs where system always allow decimal points for ctn and pcs.

9/23/2011 3:09:32 PM Justin
- Added new config to disable/enable auto show cost in menu when item not in PO.

9/28/2011 9:40:43 AM Justin
- Fixed the bugs where system cannot re-align the "PO Items" sequence no.

9/30/2011 5:05:43 PM Justin
- Modified not to prompt window for user to key in last SKU item when add multiple SKU items.
- Modified system auto round up base on SKU item that contains qty decimal points.

2/28/2012 3:48:43 PM Justin
- Added new feature to accept IBT DO and treat it as matching like PO items.
- Added new feature to allow user key in reject reason and store into data as approval history.
- Added to show approval history.

3/2/2012 10:34:32 AM Alex
- add option for scan grn barcode

7/12/2012 9:50:23 AM Justin
- Enhanced to have UOM control by config and packing uom fraction.

7/19/2012 6:37:23 PM Justin
- Fixed uom problem.

8/7/2012 5:56 PM Justin
- Enhanced to show error message when found error during scan barcode.

8/15/2012 11:29 AM Justin
- Bug fixed on item not in ARMS feature not working.
- Added new ability to hide "No Data" row when item is being rechecked.
- Enhanced to set the item back to undelivered PO item when delete item.

8/22/2012 2:29 PM Justin
- Bug fixed on Item not in ARMS cannot be deleted after added it.
- Enhanced to show SKU item list as if user add item by GRN barcode and found more than 1 record.
- Bug fixed on recheck function not working anymore after other enhancements has been made.
- Bug fixed on after invalid SKU item has become valid, item disappeared after user saved the GRN.

8/24/2012 12:01 PM Justin
- Enhanced to show branch code and related invoice as if found it is PO and config is set.

8/29/2012 11:18 AM Justin
- Bug fixed on system skipped to lookup for item matching for Matched with PO.

10/18/2012 4:25 PM Justin
- Enhanced to do checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.
- Enhanced when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhanced when user delete one of the bom package sku, all related sku will be delete at the same time.

1/17/2013 2:20 PM Justin
- Enhanced to disable save and confirm button once being clicked.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

4/21/2014 10:01 AM Justin
- Enhanced to have checking on block items in GRN.

3/14/2015 9:42 AM Justin
- Enhanced to have generate and print DN feature.

3/16/2015 2:19 PM Justin
- Enhanced reconcile feature to have match amount include GST.

3/25/2015 2:24 PM Justin
- Bug fixed on the current vs suggested selling price placed in the wrong place and comparing the wrong values.

4/11/2015 2:48 PM Justin
- Enhanced to have GRR GST amount on hidden field.

4/13/2015 4:09 PM Justin
- Enhanced to show GST amount on summary.

5/6/2015 5:58 PM Justin
- Enhanced to have temp row for GRR.
- Bug fixed on extra dn remark that did not decode.

5/19/2015 11:23 AM Justin
- Bug fixed on recalculate total will stop while user key in grr items from end to start.

6/1/2015 11:30 AM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.

9/4/2015 3:47 PM Andy
- Change to always generate gra when got config "use_grn_future_allow_generate_gra"

9/28/2015 5:28 PM DingRen
- add "Load all PO Items" button and function

11/23/2015 3:26 PM DingRen
- PeriodicalUpdater too keep session alive

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

02/15/2016 11:58 Edwin
- Bug fixed on load duplicate PO items when PO Items had appeared in the list.
- Bug fixed on remark checking (short/over) on PO items when open first time.

02/19/2016 13:44 Edwin
- Bug fixed on remark prompt incorrectly when two identical items are present
- Load PO items based on PO items id.

03/15/2016 11:30 Edwin
- Enhanced on change focus on add SKU items window when enter is pressed

04/01/2016 10:30 Edwin
- Bug fixed on approved or rejected grn without checking whether these actions were executed more than once

1/12/2017 4:14 PM Andy
- Enhanced to use branch_is_under_gst to check gst selling price.

2/7/2017 5:46 PM Andy
- Change the script to decode json when add item.

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.

5/4/2017 16:43 Qiu Ying
- Enhanced to remove config grn_have_tax in GRN Future

5/11/2017 11:39 AM Justin
- Enhanced to show returned items table while at account verification.

5/15/2017 1:19 PM Justin
- Enhanced to have FOC qty and discount calculation feature for Account Verification.

5/16/2017 10:01 AM Justin
- Enhanced to show po qty base on user privilege "GRN_SHOW_PO_QTY" when SKU Manage stage.

5/17/2017 10:58 AM Justin
- Bug fixed on FOC and discount calculation issue.
- Enhanced to the popup for FOC and discount calculation dialog.
- Bug fixed on the FOC and discount calculation dialog issue.

5/19/2017 11:03 AM Justin
- Bug fixed on cost calculate from FOC qty will causes the new cost become negative figures.

5/22/2017 10:49 AM Justin
- Enhanced to have gst information for returned items.

6/1/2017 10:35 AM Justin
- Bug fixed on FOC qty able to key in negative figures.
- Bug fixed on Cost calculation will able to become negative figures.
- Enhanced to remove the choice of cost price for user to key in.

6/16/2017 3:42 PM Justin
- Bug fixed on BOM items will not be deleted if user cancelled to key in BOM qty.

6/21/2017 3:50 PM Justin
- Bug fixed on item still added into the list after click cancel from prompt qty menu.

6/22/2017 2:01 PM Justin
- Enhanced to have new feature that can skip existed SKU items while calling out the multi add menu.

7/19/2017 11:04 AM Justin
- Bug Fixed on item still show out price comparison even though there are no price difference.

4/16/2018 5:46 PM Justin
- Enhanced to show foreign currency info.

8/9/2018 5:36 PM Justin
- Enhanced to show images attached from GRR.

8/16/2018 12:08 PM Andy
- Enhanced to check and show error if users perform certain action from different branch.

8/21/2018 10:02 AM Justin
- Bug fixed on add item using barcode will prompt html errors.

8/27/2018 2:35 PM Justin
- Enhanced to bring back the GRN Tax.

9/24/2018 5:52 PM Justin
- Enhanced to always show the Generate D/N feature regardless it is under GST or not.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.

4/8/2019 1:31 PM Andy
- Removed "_init_enter_to_skip" to fixed users cant search sku if key in character "-".

5/22/2019 3:22 PM William
- Enhance "GRN" word to use report_prefix.

6/9/2020 1:26 PM William
- Bug fixed "item sku not in arms" when use scan barcode to scan multi times "item sku not in arms", the "item sku not in arms" will not response.

06/25/2020 2:32 PM Sheila
- Updated button css.

7/16/2020 4:00 PM William
- Bug fixed GRN Decimal quantity checking not working.

9/4/2020 10:19 AM Andy
- Fixed PO Suggested Selling Price comparison bug, changed to round2 before compare.

11/6/2020 9:51 AM William
- Enhanced to let grn type "other" and "invoice" able to add item by upload csv file.
*}

{include file=header.tpl}

{assign var=time_value value=1000000000}

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<style>

.pv{
	color:#fff;
	background:#0c0;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

.nv{
	color:#fff;
	background:#e00;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

.sh{
    background-color:#ff9;
}

.stdframe.active{
 	background-color:#fea;
	border: 1px solid #f93;
}

.tbl_item input, .tbl_item select{
	border:1px solid #999;
	font-size: 10px;
	padding:2px;
}

input[disabled], input[readonly], select[disabled], textarea[disabled]{
	color:black;
	background:#ddd;
}

.tbl_item thead tr{
	height: 30;
	background: #ffffff;
	font-size: 80%;
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
</style>
{/literal}
<script>
var grn_have_tax = '{$config.grn_have_tax}';
var grn_check_selling_price = '{$config.grn_check_selling_price}';
var phpself = '{$smarty.server.PHP_SELF}';
var type = '{$grr.type}';
var is_ibt_do = '{$grr.is_ibt_do}';
var allow_grn_wo_po = '{$grr.allow_grn_without_po}';
var action_type = '{$smarty.request.action}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var grn_future_disable_show_cost = '{$config.grn_future_disable_show_cost}';
var doc_allow_edit_uom = '{$config.doc_allow_edit_uom}';
var sku_bom_additional_type = int('{$config.sku_bom_additional_type}');
var is_under_gst = '{$form.is_under_gst}';
var skip_ask_qty=false;
var new_grn = {if isset($new)}1{else}0{/if};

{literal}
var item_sequence = 0;
if(type == 'PO' || is_ibt_do){
	var tbl_start = 1; // is GRN with PO, shows all 4 tables
	var tbl_end = 5;
}else{
	var tbl_start = 3; // is GRN without PO, shows 2 tables
	var tbl_end = 4;
}

function refresh_tables(){
    if (check_login()) {
        document.f_a.a.value = "refresh";
        document.f_a.target = "";
        document.f_a.submit();
    }
}

// expand the item's varieties
function toggle_vendor_sku(sku_id,id)
{
	if ($('xp'+id).innerHTML == "varieties")
	{
		$('xp'+id).innerHTML = "hide varieties";
		$('cb'+id).disabled = true;
		$('cb'+id).checked = false;
		insert_after = $('li'+id);

		new Ajax.Updater(
		    insert_after,
		    "po.php",
		    {
				method:'post',
				parameters: '&a=ajax_expand_sku&sku_id='+sku_id,
			    evalScripts: true,
		 	    insertion: Insertion.Bottom
			});		
		/*
		new Ajax.Updater(
		    insert_after,
		    "purchase_order.php",
		    {
				method:'post',
				parameters: '&a=ajax_expand_sku&sku_id='+sku_id,
			    evalScripts: true,
		 	    insertion: Insertion.Bottom
			});
		*/
  	}
  	else
  	{
  		$('xp'+id).innerHTML = "varieties";
		$('cb'+id).disabled = false;
  		Element.remove('ul'+sku_id);
	}
}

// load sku for this vendor+dept
function load_sku_list(vid,dept_id)
{
	if (dept_id == '')
	{
	    alert('No department selected');
	    return;
	}
	$('sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('sel_vendor_sku');

	new Ajax.Updater("sel_vendor_sku", "goods_receiving_note.php",
		{
		    method: "post",
			parameters: 'a=ajax_show_vendor_sku&vendor_id='+vid+'&department_id='+dept_id,
		    evalScripts: true
		});
}

// cancel Vendor SKU window
function cancel_vendor_sku()
{
	hidediv('sel_vendor_sku');
}

// add all items selected from Vendor SKU window
function do_vendor_sku(){
	new Ajax.Updater(
	    'sel_vendor_sku',
		'goods_receiving_note.php',
		{
		    method:'post',
		    parameters: Form.serialize(document.f_s)+Form.serialize(document.f_a)+'&a=ajax_add_vendor_sku&vendor_id='+document.f_a.vendor_id.value,
		    evalScripts: true
		}
	);
}

// show child sku
function sku_show_varieties(sku_id){
	showdiv('sel_vendor_sku');
	new Ajax.Updater(
	    'sel_vendor_sku',
		'goods_receiving_note.php',
		{
		    method:'post',
		    parameters: 'a=ajax_expand_sku&showheader=1&sku_id='+sku_id,
		    evalScripts: true
		}
	);
}

function recalc_row(iid, doc_type, is_pi, obj){
	
	if(doc_type > 0 && doc_type < 4){
		if(document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value>1){
			old_pcs=float(document.f_a.elements[doc_type+'_pcs['+iid+']'].value);
			new_pcs=float(old_pcs%document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value);
			remain=float(old_pcs)-new_pcs;
			ctn=float(remain/document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value)+float(document.f_a.elements[doc_type+'_ctn['+iid+']'].value);
			
			if (document.f_a.elements[doc_type+'_pcs['+iid+']'].value != ''){
				document.f_a.elements[doc_type+'_pcs['+iid+']'].value = float(round(new_pcs, global_qty_decimal_points));
			}
			document.f_a.elements[doc_type+'_ctn['+iid+']'].value = float(round(ctn, global_qty_decimal_points));
		}
	}
	
	if(sku_bom_additional_type) bom_ratio_calculation(iid, doc_type); // found this item is from bom
	
	// variance calculation
	if(doc_type < 3){
		var n = (float(document.f_a.elements[doc_type+'_ctn['+iid+']'].value) * float(document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value)) + float(document.f_a.elements[doc_type+'_pcs['+iid+']'].value) - float(document.f_a.elements[doc_type+'_po_qty['+iid+']'].value);
		
		if (obj != undefined){
			var tmp_n = float(n) - float(document.f_a.elements[doc_type+'_return_ctn['+iid+']'].value * document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value) - float(document.f_a.elements[doc_type+'_return_pcs['+iid+']'].value);

			if(tmp_n < 0){
				if(n < 0) var msg = "No qty can return";
				else  var msg = "Pcs: "+n+" can return";
				alert("Cannot set Return Qty more than extra rcv qty ("+msg+")");
				if(obj.name.indexOf(doc_type+"_return_ctn[")==0) document.f_a.elements[doc_type+'_return_ctn['+iid+']'].value = 0;
				else document.f_a.elements[doc_type+'_return_pcs['+iid+']'].value = 0;
			}else{
				if(obj.name.indexOf(doc_type+"_return_pcs[")==0 && document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value>1){
					old_pcs=float(document.f_a.elements[doc_type+'_return_pcs['+iid+']'].value);
					new_pcs=float(old_pcs%document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value);
					remain=float(old_pcs)-new_pcs;
					ctn=float(remain/document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value)+float(document.f_a.elements[doc_type+'_return_ctn['+iid+']'].value);
					
					if (document.f_a.elements[doc_type+'_return_pcs['+iid+']'].value != ''){
						document.f_a.elements[doc_type+'_return_pcs['+iid+']'].value = float(round(new_pcs, global_qty_decimal_points));
					}
					document.f_a.elements[doc_type+'_return_ctn['+iid+']'].value = float(round(ctn, global_qty_decimal_points));
				}
				n = tmp_n;
			}
		}
		
		document.f_a.elements[doc_type+'_qty_var['+iid+']'].value = n;
		
		if(is_pi != undefined && !is_pi){
			if(n > 0){
				$(doc_type+'_qty_var'+iid).innerHTML = "<span class=pv>Over</span>";
			}else if(n < 0){
				$(doc_type+'_qty_var'+iid).innerHTML = "<span class=nv>Short</span>";
			}else{
				$(doc_type+'_qty_var'+iid).innerHTML = "&nbsp;";
			}
		}
	}
	if(doc_type > 0 && doc_type < 4){
		var b = float(document.f_a.elements[doc_type+'_ctn['+iid+']'].value) + float(document.f_a.elements[doc_type+'_pcs['+iid+']'].value) / float(document.f_a.elements[doc_type+'_uom_fraction['+iid+']'].value);
		document.f_a.elements[doc_type+'_amt['+iid+']'].value = round2(b*(float(document.f_a.elements[doc_type+'_cost['+iid+']'].value)));
	}

	recalc_totals(doc_type);
}

function recalc_totals(doc_type){
	if(doc_type != undefined){
		var tbl_start=doc_type;
		var tbl_end=doc_type;
	}else{
		if(type == 'PO' || is_ibt_do){
			var tbl_start = 1; // is GRN with PO, shows all 4 tables
			var tbl_end = 4;
		}else{
			var tbl_start = 3; // is GRN without PO, shows 2 tables
			var tbl_end = 4;
		}
	}
	
	for(var doc_type=tbl_start; doc_type<=tbl_end; doc_type++){
		if ($('grn_items_'+doc_type)==undefined) continue;
		var sp = $('grn_items_'+doc_type).getElementsByTagName("INPUT");

		var total_pcs = 0;
		var total_return_pcs = 0;
		var total_ctn = 0;
		var total_return_ctn = 0;
		var total_po_qty = 0;
		var total_qty_var = 0;
	
		$A(sp).each(
			function (r,idx)
			{
				if (r.name.indexOf(doc_type+"_ctn[")==0)
				{
					total_ctn += float(r.value);
					total_ctn = float(round(total_ctn, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
				if (r.name.indexOf(doc_type+"_pcs[")==0)
				{
					total_pcs += float(r.value);
					total_pcs = float(round(total_pcs, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
				if (r.name.indexOf(doc_type+"_return_ctn[")==0)
				{
					total_return_ctn += float(r.value);
					total_return_ctn = float(round(total_return_ctn, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
				if (r.name.indexOf(doc_type+"_return_pcs[")==0)
				{
					total_return_pcs += float(r.value);
					total_return_pcs = float(round(total_return_pcs, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
				if (r.name.indexOf(doc_type+"_po_qty[")==0)
				{
					total_po_qty += float(r.value);
					total_po_qty = float(round(total_po_qty, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
				if (r.name.indexOf(doc_type+"_qty_var[")==0)
				{
					total_qty_var += float(r.value);
					total_qty_var = float(round(total_qty_var, global_qty_decimal_points)); // to prevent the 99999999 sum problem
				}
			}
		);
		
		$('total_qty_'+doc_type).innerHTML = 'Ctn: '+total_ctn+' Pcs: '+total_pcs;
		if($('total_return_qty_'+doc_type)) $('total_return_qty_'+doc_type).innerHTML = 'Ctn: '+total_return_ctn+' Pcs: '+total_return_pcs;
		if((type == 'PO' || is_ibt_do) && doc_type < 3){
            if (new_grn === 0) {
                if(total_qty_var > 0) $('total_qty_var_'+doc_type).innerHTML = "<span class=pv>Over</span>";
                else if(total_qty_var < 0) $('total_qty_var_'+doc_type).innerHTML = "<span class=nv>Short</span>";
                else $('total_qty_var_'+doc_type).innerHTML = "&nbsp;";
            }
	    }
	}
}

function do_confirm(){
	//var auto_approve = true;
	var no_approval = '';
	var gtotal_rows = 0;
	var err_msg = '';
	var isi_err_msg = '';
	var sp_err_msg = '';
	var ttl_grn_amt = '';

	if(validate_data() == false) return false;

	/*if(action_type == "edit") tbl_start = 1;
	else if(type == "PO" && division == 3) tbl_start = 5;*/

	// check items
	for(doc_type=tbl_start; doc_type<=tbl_end; doc_type++){
		if ($('grn_items_'+doc_type)==undefined) continue;
		var have_items;
		var e = $('grn_items_'+doc_type).getElementsByTagName("INPUT");
		var total_row=e.length;

		//if(doc_type > tbl_start && doc_type <= tbl_end){
		gtotal_rows = float(gtotal_rows) + float(total_row);
		//}

		// check if found items on valid or invalid sku item, do not skip approval flow
		/*if(type == "PO" && (doc_type == 0 || doc_type == 3 || doc_type == 4 || doc_type == 5) && total_row > 0){
			auto_approve = false;
		}*/
		if(action_type == "edit") continue;

		$A(e).each(
			function (r,idx){
				if(doc_type == 4 && r.name.indexOf(doc_type+"_item_return[")==0){
					if(r.checked == false && r.value != 1 && action_type == "verify"){
						isi_err_msg = "* All items from SKU not in ARMS must be tick! \n";
						throw $break;
					}
				}
				if(grn_check_selling_price && r.name.indexOf(doc_type+"_reason[")==0){
					if(!r.value){
						sp_err_msg = "* All remarks for Suggested Selling Price must be specify! \n";
						throw $break;
					}
				}
			}
		);
	}
    
	if(!action_type){
		var confirm_msg = "Confirm GRN and submit for Approval?";
	}else{
		if(!gtotal_rows){
			err_msg += "* No items were found. \n";
		}	

		err_msg += isi_err_msg + sp_err_msg;
		var confirm_msg = "Are you sure want to confirm?";
	}
	
	if(err_msg){
		alert("You have encountered below errors: \n\n"+err_msg);
		return false;
	}

	if (!confirm(confirm_msg)) return;

	show_print_dialog('confirm');
}

function do_cancel(){
    if (check_login()) {
        if (!confirm('Cancel this GRN?')){
            return;
        }
        document.f_a.a.value='cancel';
        document.f_a.submit();
    }
}

function do_close(){
	if (!confirm('Discard changes and close?')){
	    return;
	}
	document.location = '/goods_receiving_note.php';
}

function add_grn_barcode_item(value, po_item_id){
	value = trim(value);
    if (po_item_id != null) {
        po_item_id = trim(po_item_id);
    }
	var new_qty = 0;
	var bom_qty = 0;
	var n_terminate = false;
	var gid = '';
	var si_existed = '';
	var count_rows = 1;
	var ttl_po_qty = 0;
	var po_set_id = '';
	var lsi_id = '';
	var extra_variance = '';
	var grn_item_code = '';
	var po_si_code = '';
	var po_total_rows = 0;
	var focus_field = "grn_barcode";
	var sid = 0;
	var first_bom_item_id = 0;
	var first_bom_doc_type = 0;

	if((type == 'PO' || is_ibt_do) && $('tbl_item_0') != undefined){
		var tbl_start = 1; // is GRN with PO, shows all 4 tables
		po_si_code = $('tbl_item_0').getElementsByClassName("0_sku_item_code");
		po_total_rows = po_si_code.length;
		
		// found have PO items
		if(po_total_rows > 0){
			$A(po_si_code).each(
				function (r,idx){
					if (r.name.indexOf("0_sku_item_code[")==0){
						// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
						var ri = r.title.split(",");
						var val_13d =  value.slice(0,-1);
                        var poi_id = $('0_po_item_id['+ri[0]+']').value;
	
						// found if barcode scan is matched with PO item's sku item code, link code, artno or mcode
						if((ri[1] == value || ri[1] == val_13d || ri[2] == value || ri[3] == value || ri[4] == value) && poi_id == po_item_id ){
							//delete_item(ri[0], 0, undefined, 1);
							gid = ri[0];
							throw $break;
						}
					}
				}
			);
		}
	}else{
		var tbl_start = 3; // is GRN without PO, shows 2 tables
	}

	if (value=='' && $('grn_barcode') != undefined){
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}else if(value.charAt(0) == 0 && value.charAt(1) == 0){
		new_qty = 0;
		var sid = value.substring(2,8);
	}

	// search and allow user to key in extra qty for existed sku item.
	if(!gid){
		for(var doc_type=tbl_start; doc_type<=4; doc_type++){
			if ($('grn_items_'+doc_type)==undefined) continue;
			var all_si_code = $('tbl_item_'+doc_type).getElementsByClassName(doc_type+"_sku_item_code");
			var all_total_rows = all_si_code.length;
			
			if(all_total_rows > 0){
				$A(all_si_code).each(
					function (r,idx){
						if (r.name.indexOf(doc_type+"_sku_item_code[")==0){
							// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
							var ri = r.title.split(",");
							if(doc_type != 4) var val_13d = value.slice(0,-1);
							else var val_13d = value;
							if(ri[0] == undefined) ri[0] = value;
							if(((doc_type <= 3 && document.f_a.elements[doc_type+'_sku_item_id['+ri[0]+']'].value == sid) || ri[1] == value || ri[1] == val_13d || ri[2] == value || ri[3] == value || ri[4] == value || ri[1] == val_13d) && document.f_a[doc_type+'_item_group['+ri[0]+']'].value != 2){
								if(typeof(document.f_a[doc_type+'_bom_ref_num['+ri[0]+']'])!='undefined' && document.f_a[doc_type+'_bom_ref_num['+ri[0]+']'].value > 0){
									alert("This is BOM item, cannot add extra qty.");
									n_terminate = true;
									return false;
								}
	
								if($(doc_type+'_item_return'+ri[0]) && doc_type == 1){
									return false;
								}
								if(!new_qty){ // if found 1st time do not have input for new
									if(!lsi_id) new_qty = prompt("Found existed SKU item '"+ri[1]+"', please enter Qty:");
									
									if(new_qty == null){
										n_terminate = true;
										return false;
									}

									if(typeof(document.f_a[doc_type+'_doc_allow_decimal['+ri[0]+']'])!='undefined' && document.f_a.elements[doc_type+'_doc_allow_decimal['+ri[0]+']'].value != 0){
										new_qty = float(round(new_qty, global_qty_decimal_points));
									}else new_qty = round(new_qty, 0);

									if(new_qty < 0){ // check keyed in qty whether is positive or not
										alert("Must be positive Qty!");
										throw $break;
									}
									
									if(new_qty == 0) throw $break;
									
					            	$(doc_type+'_pcs'+ri[0]).value = float($(doc_type+'_pcs'+ri[0]).value) + float(new_qty);
									curtain(false);
					            }
				            	recalc_row(ri[0], doc_type);
								lsi_id = ri[0];
								
								if(allow_grn_wo_po==0 && doc_type < 3){
									if(float(document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value) > 0){
										extra_variance = document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value;
										//alert("current = "+document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value);
										grn_item_code = ri[1];
									}else{
										extra_variance = 0;
									}
								}
								
				            	n_terminate = true;
								return false;
							}
						}
					}
				);
						//if(all_total_rows > 0 && new_qty <= 0) return false;
			}

			if(n_terminate){
				if(extra_variance > 0 && grn_item_code && new_qty){
					var all_si_code = $('tbl_item_3').getElementsByClassName("3_sku_item_code");
					var all_total_rows = all_si_code.length;
	
					if(all_total_rows > 0){
						$A(all_si_code).each(
							function (r,idx){
								if (r.name.indexOf("3_sku_item_code[")==0){
									// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
									var ri = r.title.split(",");
	
									if(ri[1] == grn_item_code){
										$('3_pcs'+ri[0]).value = float($('3_pcs'+ri[0]).value) + float(extra_variance);
										recalc_row(ri[0], 3);
									}
								}
							}
						);
					}else if(extra_variance > 0){
						ajax_add_return_item(lsi_id, extra_variance);
					}
					$(doc_type+'_pcs'+lsi_id).value = float($(doc_type+'_pcs'+lsi_id).value) - float(extra_variance);
					recalc_row(lsi_id, doc_type);
				}
				

				if($('grn_barcode') != undefined){
					$('grn_barcode').value = "";
					$('grn_barcode').focus();
				}
				return false;
			}
		}
	}

	$('span_autocomplete_adding').style.display = '';
	
	if($('grn_barcode') != undefined) $('grn_barcode').value='';
	// ajax_add_item_row
	ajax_request(phpself, {
        asynchronous: false,
		method:'post',
		parameters: Form.serialize(document.f_a)+'&a=ajax_add_item_row&grn_barcode='+value+'&new_qty='+new_qty+'&gid='+gid,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			//alert(m.responseText);
			var str = m.responseText.trim();
			
			try{
				
				var json = JSON.parse(str);
				
				
				//eval("var json = "+m.responseText); console.log(json);
				for(var tr_key in json){
					
					if(json[tr_key]['data'] != undefined){
						if(!json[tr_key]['data']['sku_list']){
							if(n_terminate){
								$('grn_barcode').value  = "";
								$('grn_barcode').focus();
								return false;
							}

							if(json[tr_key]['data']['existed_gi_id']){
								var gi_id = json[tr_key]['data']['existed_gi_id'];
								var sku_item_code = json[tr_key]['data']['existed_si_code'];
								var doc_type = json[tr_key]['data']['existed_item_group'];
								var pcs = json[tr_key]['data']['existed_pcs'];
								new_qty = float(prompt("Found existed SKU item '"+sku_item_code+"', please enter Qty:", pcs));
								
								if(new_qty == null) return false;

								if(document.f_a.elements[doc_type+'_doc_allow_decimal['+gi_id+']'].value != 0){
									new_qty = float(round(new_qty, global_qty_decimal_points));
								}else new_qty = round(new_qty, 0);

								if(new_qty < 0){ // check keyed in qty whether is positive or not
									alert("Must be positive Qty!");
								}else{
									$(doc_type+'_pcs'+gi_id).value = round(float($(doc_type+'_pcs'+gi_id).value) + float(new_qty), global_qty_decimal_points);
									curtain(false);
								}
								continue;
							}

							if(json[tr_key]['data']['error'] != undefined){
								alert(json[tr_key]['data']['error']);
								if(json[tr_key]['data']['doc_type'] == undefined){
									curtain(false);
									return false;
								}
							}

							if($('no_data_'+json[tr_key]['data']['doc_type']) != undefined) $('no_data_'+json[tr_key]['data']['doc_type']).hide();
							
							if(json[tr_key]['html'] != undefined) new Insertion.Bottom($$('.multiple_add_container_'+json[tr_key]['data']['doc_type']).first(),json[tr_key]['html']);
							
							if(json[tr_key]['data']['doc_type'] != 4){
								item_sequence++;
								document.f_a.elements[json[tr_key]['data']['doc_type']+'_item_seq['+json[tr_key]['data']['id']+']'].value = item_sequence;
							}
							
							if(json[tr_key]['data']['is_bom_item'] == 1 || json[tr_key]['data']['is_bom'] == 1){ // is import from bom
								$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = float(round(float(json[tr_key]['data']['pcs']), global_qty_decimal_points));
							}

							if((json[tr_key]['data']['is_bom_item'] == 0 && json[tr_key]['data']['is_bom'] == 0) || json[tr_key]['data']['doc_type'] == 4){
								if(json[tr_key]['data']['doc_type'] == 3 || json[tr_key]['data']['doc_type'] == 4){
									if(json[tr_key]['data']['doc_type'] == 4){
										alert("'"+value+"' is an invalid SKU Item!");
										$('si_id').value = value;
										hidediv('tr_ctn');
										showdiv('tr_description');
										$('si_msg').innerHTML = "<h3>SKU not in ARMS</h3>";
									}else{
										$('si_id').value = json[tr_key]['data']['id'];
										if(doc_allow_edit_uom == 0 && json[tr_key]['data']['packing_uom_fraction'] > 1) hidediv('tr_ctn');
										else if(json[tr_key]['data']['packing_uom_fraction'] == 1) hidediv('tr_ctn');
										else if(json[tr_key]['data']['uom_fraction'] > 1) showdiv('tr_ctn');
										hidediv('tr_description');
										var msg_info = "";
										if((type == "PO" || is_ibt_do) && allow_grn_wo_po) msg_info = "Items not in "+type;
										else if(type == "PO" || is_ibt_do) msg_info = "SKU Return List";
										else msg_info = "Received items";
										$('si_msg').innerHTML = "<h3>"+msg_info+"</h3>";
									}
									curtain(true);
									showdiv('si_menu');
									center_div('si_menu');

									$('sku_doc_type').value = json[tr_key]['data']['doc_type'];
									$('si_sell').innerHTML = round(json[tr_key]['data']['sell'], 2);
									if (json[tr_key]['data']['branch_under_gst'] == 1) {
										$('gst_status').innerHTML = "(Excl. GST)";
									}
									if(!grn_future_disable_show_cost) $('si_cost').value = round(json[tr_key]['data']['cost'], global_cost_decimal_points);
									else document.f_a.elements[json[tr_key]['data']['doc_type']+'_cost['+json[tr_key]['data']['id']+']'].value = 0;

									$('doc_allow_decimal').value = json[tr_key]['data']['dad'];
									if(json[tr_key]['data']['dad'] != 0){
										$('si_ctn').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
										$('si_pcs').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
										$('si_ctn').value = float(round(json[tr_key]['data']['ctn'], global_qty_decimal_points));
										$('si_pcs').value = float(round(json[tr_key]['data']['pcs'], global_qty_decimal_points));
									}else{
										$('si_ctn').onchange = function(){ mi(this); };
										$('si_pcs').onchange = function(){ mi(this); };
										$('si_ctn').value = int(json[tr_key]['data']['ctn']);
										$('si_pcs').value = int(json[tr_key]['data']['pcs']);
									}
									focus_field = "si_cost";
								}else{
									if (!skip_ask_qty) {
									  var qty = float(prompt("Please enter Qty:"));
									  if(json[tr_key]['data']['dad'] != 0) qty = float(round(qty, global_qty_decimal_points));
									  else qty = int(qty);
									  $(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = qty;
									  recalc_row(json[tr_key]['data']['id'], json[tr_key]['data']['doc_type']);
									  add_autocomplete_extra(json[tr_key]['data']['doc_type']);
									  curtain(false);
									}
								}
							}else if(json[tr_key]['data']['grn_barcode_type'] == 1){
								if(bom_qty != null && !bom_qty) bom_qty = prompt("Please enter BOM Qty:");
								
								$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = float(round(json[tr_key]['data']['pcs'] * bom_qty, global_qty_decimal_points));
								
								// need to capture one of the bom item id since user do have cancelled to add qty
								if(bom_qty == null){
									delete_item(json[tr_key]['data']['id'], json[tr_key]['data']['doc_type'], 1);
								}
							}

							// verify if the return check all is tick
							if($('check_return_'+json[tr_key]['data']['doc_type']) != null){
								if($('check_return_'+json[tr_key]['data']['doc_type']).checked == true){
									check_all_return(json[tr_key]['data']['doc_type']);
								}
							}
							add_autocomplete_extra(json[tr_key]['data']['doc_type']);
							
						}else{
							$('sku_list').innerHTML = json[tr_key]['html'];
							curtain(true);
							showdiv('sku_list');
							center_div('sku_list');
						}
					}
				}
				
			}catch(ex){
				alert(str);
			}
			
		},
		onComplete: function(m) {
			$(focus_field).focus();
			$('span_autocomplete_adding').style.display = 'none';
		}
	});
}

// function to run after finish add items
function add_autocomplete_extra(doc_type){
	if(doc_type == undefined){
	    reset_row();
		recalc_totals();
		//tax_changed();
	}else{
	    reset_row(doc_type);
	    recalc_totals(doc_type);
		//tax_changed(doc_type);
	}
	if(doc_type != undefined && doc_type >= 3){
		$('si_cost').focus();
	}
}

function reset_row(doc_type){
	if(doc_type != undefined){
		var tbl_start=doc_type;
		var tbl_end=doc_type;
	}else{
		if(type == 'PO' || is_ibt_do){
			var tbl_start = 0; // is GRN with PO, reset all 5 tables
			var tbl_end = 5;
		}else{
			var tbl_start = 3; // is GRN without PO, reset only 2 tables
			var tbl_end = 4;
		}
	}
	
	for(var doc_type=tbl_start; doc_type<=tbl_end; doc_type++){
		if ($('grn_items_'+doc_type)==undefined) continue;
		var e = $('grn_items_'+doc_type).getElementsByClassName(doc_type+'_no');
		var total_row=e.length;

		for(var i=0;i<e.length;i++)	{
	 		var temp_1 =new RegExp('^'+doc_type+'_no_');
		 	if (temp_1.test(e[i].id)){
				td_1=(i+1)+'.';
				e[i].innerHTML=td_1;
				e[i].id=doc_type+'_no_'+(i+1);
				e[i].title='No. '+(i+1);
				/*
				if (((i+1+total_row)%2)==1){
					$('titem'+e[i].title).bgColor="#ffffff";
				}
				else {
					$('titem'+e[i].title).bgColor="#dddddd";
				}
				*/
			}
		}
	}
	if(action_type == "edit") $('autocomplete_sku').select();
}

function delete_item(id, doc_type, is_isi){
	var confirm_str = 'Remove this SKU from GRN?';
	var bom_ref_num = '';
	var prm = '';
	var delete_id_list = [];
	
	if(sku_bom_additional_type){
		if(document.f_a[doc_type+'_bom_ref_num['+id+']'] && document.f_a[doc_type+'_bom_ref_num['+id+']'].value.trim() > 0){
			bom_ref_num = document.f_a[doc_type+'_bom_ref_num['+id+']'].value.trim();
			
			confirm_str += '\nThis SKU is BOM Package SKU, all related SKU items will be deleted together';
		}
	}

	if((doc_type > 0 && doc_type < 3) || (doc_type == 3 && is_isi == "") || (doc_type == 4 && is_isi == "")){ if (!confirm(confirm_str)) return; }
	
	 if(sku_bom_additional_type && bom_ref_num){
 		$$('.bom_ref_num_grp_'+bom_ref_num).each(function(tr){
 			var item_id = $(tr).readAttribute('item_id');
 			delete_id_list.push(item_id);
 		});
 	}else{
 		delete_id_list.push(id);
 	}
 	
 	var params = {
 		'item_id_list[]': delete_id_list,
		'a': 'ajax_delete_row',
		'grn_id': document.f_a['id'].value
 	};
	
	ajax_request(phpself,{
		method:'post',
		parameters: params,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			if(bom_ref_num){
				var bom_ref_num_list = $$('.bom_ref_num_grp_'+bom_ref_num);
			
				// loop to check item can decimal qty or not
				for(var i=0; i<bom_ref_num_list.length; i++){
					// get the row grn item id + doc type
					var item_doc_type = $(bom_ref_num_list[i]).readAttribute('doc_type');
					var item_id = $(bom_ref_num_list[i]).readAttribute('item_id');
						
					if($(item_doc_type+'_titem'+item_id)) Element.remove(item_doc_type+'_titem'+item_id);
					if($('5_titem'+item_id)) Element.remove('5_titem'+item_id);
				}
				reset_row();
				recalc_totals();
			}else{
				Element.remove(doc_type+'_titem'+id);
				if($('5_titem'+id)) Element.remove('5_titem'+id);
				reset_row(doc_type);
				recalc_totals(doc_type);
			}
    	}
	});
}

/* only use for Cutemaree
function tax_changed(){
	var tax = document.f_a.grn_tax.value;
	for(var doc_type=tbl_start; doc_type<=4; doc_type++){
		var inputs = $$("#grn_items_"+doc_type+" input.original_cost");
		for(var i=0; i<inputs.length; i++){
			var original_cost = float(inputs[i].value);
			var new_cost = float(original_cost + (original_cost*(tax*0.01)));
			var item_id = inputs[i].id.split(',')[2];
			if($(doc_type+'_inp,cost,'+item_id) != undefined){
				$(doc_type+'_inp,cost,'+item_id).value = round(new_cost,4);
			}
			recalc_row(item_id, doc_type);
		}
	}
	recalc_totals();
}
*/

function set_new_original_cost(iid, doc_type){
	if(!grn_have_tax)   return;
	
	$(doc_type+'_inp,original_cost,'+iid).value = $(doc_type+'_inp,cost,'+iid).value;
	//tax_changed();
}

function add_autocomplete(){	
	// is invalid SKU
	if(document.f_a.sku_item_id.value == '' && document.f_a.sku.value != ''){
		document.f_a.sku_item_id.value = 0;
	}
    var sku_item_id = $('sku_item_id').value;
    var sku_item_code = $('sku_item_code').value;
    if(!sku_item_id) return false;
	var sku_code_list = [sku_item_id];
    ajax_add_multiple_item(sku_code_list);
    clear_autocomplete();
}

function ajax_add_multiple_item(sku_code_list, is_recheck){
	if(document.f_a.elements['sku_item_id'].value == 0 && sku_code_list == 0){
		alert("Please select SKU item.");
		return;
	}
	
    var param_str = Form.serialize(document.f_a) + '&a=ajax_add_item_row';
	
	if(is_recheck != undefined && is_recheck){
		param_str += "&is_recheck="+is_recheck;
	}

    var s = $H({'sku_code_list[]': sku_code_list}).toQueryString();
	var gid = '';
	var n_terminate = 0;
	var sku_item = $('sku_item_code').value;
	if(!is_recheck){
		if(!sku_item) sku_item = $('autocomplete_sku').value;
		if(!sku_item) sku_item = $('list_sku_code'+sku_code_list).value;
	}
	var count_rows = 1;
	var new_qty = 0;
	var si_existed = '';
	var ttl_po_qty = 0;
	var po_set_id = '';
	var extra_variance = 0;
	var grn_item_code = '';
	var lsi_id = '';
	var po_si_code = '';
	var po_total_rows = 0;
	var bom_qty = 0;
	var first_bom_item_id = 0;
	var first_bom_doc_type = 0;
	var item_existed = 0;

	if(type == 'PO' || is_ibt_do){
		var tbl_start = 1; // is GRN with PO, shows all 4 tables
		po_si_code = $('tbl_item_0').getElementsByClassName("0_sku_item_code");
		po_total_rows = po_si_code.length;
		// found have PO items
		if(po_total_rows > 0){
			$A(po_si_code).each(
				function (r,idx){
					if (r.name.indexOf("0_sku_item_code[")==0){
						// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
						var ri = r.title.split(",");
						var val_13d =  sku_item.slice(0,-1);
	
						// found if barcode scan is matched with PO item's sku item code, link code, artno or mcode
						if(ri[1] == sku_item || ri[1] == val_13d || ri[2] == sku_item || ri[3] == sku_item || ri[4] == sku_item){
			              gid = ri[0];
			              throw $break;
						}
					}
				}
			);
		}
	}else{
		var tbl_start = 3; // is GRN without PO, shows 2 tables
	}

	// following is use to check for the existing SKU item that already existed
	// and add extra qty instead of call ajax to create new row
	// search and allow user to key in extra qty for existed sku item.
	if(!is_recheck && !gid){
		for(var doc_type=tbl_start; doc_type<=4; doc_type++){
			if ($('grn_items_'+doc_type)==undefined) continue;
			var all_si_code = $('tbl_item_'+doc_type).getElementsByClassName(doc_type+"_sku_item_code");
			var all_total_rows = all_si_code.length;
			
			if(all_total_rows > 0){
				$A(all_si_code).each(
					function (r,idx){
						if (r.name.indexOf(doc_type+"_sku_item_code[")==0){
							// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
							var ri = r.title.split(",");
							if(doc_type != 4) var val_13d = sku_item.slice(0,-1);
							else var val_13d = sku_item;
							if(ri[0] == undefined) ri[0] = sku_item;
							if(ri[1] == sku_item || ri[1] == val_13d || ri[2] == sku_item || ri[3] == sku_item || ri[4] == sku_item || ri[1] == sku_item){
								if(document.f_a[doc_type+'_bom_ref_num['+ri[0]+']'].value > 0){
									alert("This is BOM item, cannot add extra qty.");
									n_terminate = true;
									return false;
								}
	
								if($(doc_type+'_item_return'+ri[0]) && doc_type == 1){
									return false;
								}
								if(!new_qty){ // if found 1st time do not have input for new
									if(!lsi_id) new_qty = prompt("Found existed SKU item '"+sku_item+"', please enter Qty:");
									
									if(new_qty == null){
										n_terminate = true;
										return false;
									}
									
									if(document.f_a.elements[doc_type+'_doc_allow_decimal['+ri[0]+']'].value != 0){
										new_qty = float(round(new_qty, global_qty_decimal_points));
									}else new_qty = round(new_qty, 0);

									if(new_qty < 0){ // check keyed in qty whether is positive or not
										alert("Must be positive Qty!");
										throw $break;
									}
									
					            	$(doc_type+'_pcs'+ri[0]).value = float($(doc_type+'_pcs'+ri[0]).value) + float(new_qty);
									curtain(false);
	
					            }
				            	recalc_row(ri[0], doc_type);
								lsi_id = ri[0];
								
								if(allow_grn_wo_po==0 && doc_type < 3){
									if(float(document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value) > 0){
										extra_variance = document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value;
										//alert("current = "+document.f_a.elements[doc_type+'_qty_var['+ri[0]+']'].value);
										grn_item_code = ri[1];
									}else{
										extra_variance = 0;
									}
								}
								
				            	n_terminate = true;
								return false;
							}
						}
					}
				);
			}

			if(n_terminate){
				if(extra_variance > 0 && grn_item_code && new_qty){
					var all_si_code = $('tbl_item_3').getElementsByClassName("3_sku_item_code");
					var all_total_rows = all_si_code.length;
	
					if(all_total_rows > 0){
						$A(all_si_code).each(
							function (r,idx){
								if (r.name.indexOf("3_sku_item_code[")==0){
									// split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
									var ri = r.title.split(",");
	
									if(ri[1] == grn_item_code){
										$('3_pcs'+ri[0]).value = float($('3_pcs'+ri[0]).value) + float(extra_variance);
										recalc_row(ri[0], 3);
									}
								}
							}
						);
					}else if(extra_variance > 0){
						ajax_add_return_item(lsi_id, extra_variance);
					}
					$(doc_type+'_pcs'+lsi_id).value = float($(doc_type+'_pcs'+lsi_id).value) - float(extra_variance);
					recalc_row(lsi_id, doc_type);
				}
				

				if($('grn_barcode') != undefined){
					$('grn_barcode').value = "";
					$('grn_barcode').focus();
				}
				return false;
			}
		}
	}

	$('span_autocomplete_adding').style.display = '';
	
	if($('grn_barcode') != undefined) $('grn_barcode').value='';
	// ajax_add_item_row
	
	ajax_request(phpself, {
		method:'post',
		parameters: param_str+'&'+s+'&gid='+gid,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(n_terminate && $('grn_barcode') != undefined){
					$('grn_barcode').value  = "";
					$('grn_barcode').focus();
					return false;
				}

				if(json[tr_key]['data']['error'] != undefined){
					alert(json[tr_key]['data']['error']);
					if(json[tr_key]['data']['doc_type'] == undefined){
						curtain(false);
						return false;
					}
				}
				
				if($('no_data_'+json[tr_key]['data']['doc_type']) != undefined) $('no_data_'+json[tr_key]['data']['doc_type']).hide();

				if(json[tr_key]['html'] != undefined) new Insertion.Bottom($$('.multiple_add_container_'+json[tr_key]['data']['doc_type']).first(),json[tr_key]['html']);
				
				if(json[tr_key]['data']['doc_type'] != 4){
					item_sequence++;
					document.f_a.elements[json[tr_key]['data']['doc_type']+'_item_seq['+json[tr_key]['data']['id']+']'].value = item_sequence
				}
				
				if(json[tr_key]['data']['is_bom_item'] == 1 || json[tr_key]['data']['is_bom'] == 1){ // is import from bom
					$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = float(round(float(json[tr_key]['data']['pcs']), global_qty_decimal_points));
				}
				
				if(json[tr_key]['data']['is_bom_item'] == 0 && json[tr_key]['data']['is_bom'] == 0){
					if((json[tr_key]['data']['doc_type'] == 3 || json[tr_key]['data']['doc_type'] == 4) && json[tr_key]['data']['si_count'] == 1){
						if(is_recheck != 1){
							if(json[tr_key]['data']['doc_type'] == 4){
								alert("'"+value+"' is an invalid SKU Item!");
								$('si_id').value = value;
								hidediv('tr_ctn');
								showdiv('tr_description');
								$('si_msg').innerHTML = "<h3>SKU not in ARMS</h3>";
								$('si_pcs').focus();
							}else{
								$('si_id').value = json[tr_key]['data']['id'];
								if(doc_allow_edit_uom == 0 && json[tr_key]['data']['packing_uom_fraction'] > 1) hidediv('tr_ctn');
								else if(json[tr_key]['data']['packing_uom_fraction'] == 1) hidediv('tr_ctn');
								else if(json[tr_key]['data']['uom_fraction'] > 1) showdiv('tr_ctn');
								hidediv('tr_description');
								if((type == "PO" || is_ibt_do) && allow_grn_wo_po) msg_info = "Items not in "+type;
								else if(type == "PO" || is_ibt_do) msg_info = "SKU Return List";
								else msg_info = "Received items";
								$('si_msg').innerHTML = "<h3>"+msg_info+"</h3>";
							}
							showdiv('si_menu');
							center_div('si_menu');
							curtain(true);
							
							$('sku_doc_type').value = json[tr_key]['data']['doc_type'];
                            $('si_sell').innerHTML = round(json[tr_key]['data']['sell'], 2);
                            if (json[tr_key]['data']['branch_under_gst'] == 1) {
                                $('gst_status').innerHTML = "(Excl. GST)";
                            }
							if(!grn_future_disable_show_cost) $('si_cost').value = round(json[tr_key]['data']['cost'], global_cost_decimal_points);
							else document.f_a.elements[json[tr_key]['data']['doc_type']+'_cost['+json[tr_key]['data']['id']+']'].value = 0;
							
							$('doc_allow_decimal').value = json[tr_key]['data']['dad'];
							if(json[tr_key]['data']['dad'] != 0){
								$('si_ctn').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
								$('si_pcs').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
								$('si_ctn').value = float(round(json[tr_key]['data']['ctn'], global_qty_decimal_points));
								$('si_pcs').value = float(round(json[tr_key]['data']['pcs'], global_qty_decimal_points));

							}else{
								$('si_ctn').onchange = function(){ mi(this); };
								$('si_pcs').onchange = function(){ mi(this); };
								$('si_ctn').value = int(json[tr_key]['data']['ctn']);
								$('si_pcs').value = int(json[tr_key]['data']['pcs']);
							}
						}else{
							$(json[tr_key]['data']['doc_type']+'_cost'+json[tr_key]['data']['id']).value = document.f_s.elements['cost['+json[tr_key]['data']['sid']+']'].value;
							$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = document.f_s.elements['pcs['+json[tr_key]['data']['sid']+']'].value;
							delete_item(document.f_s.elements['code['+json[tr_key]['data']['sid']+']'].value, 4, 1);
							hidediv('sku_list');
							curtain(false);
						}
					}else if(json[tr_key]['data']['doc_type'] <= 2){
						var qty = float(prompt("Please enter Qty:"));
						if(json[tr_key]['data']['dad'] != 0) qty = float(round(qty, global_qty_decimal_points));
						else qty = int(qty);
						$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = qty;
						recalc_row(json[tr_key]['data']['id'], json[tr_key]['data']['doc_type']);
						add_autocomplete_extra(json[tr_key]['data']['doc_type']);
						curtain(false);
					}
				}else{
					if(bom_qty != null && !bom_qty) bom_qty = prompt("Please enter BOM Qty:");
					
					$(json[tr_key]['data']['doc_type']+'_pcs'+json[tr_key]['data']['id']).value = float(round(json[tr_key]['data']['pcs'] * bom_qty, global_qty_decimal_points));
					
					// need to capture one of the bom item id since user do have cancelled to add qty
					if(bom_qty == null){
						delete_item(json[tr_key]['data']['id'], json[tr_key]['data']['doc_type'], 1);
					}
				}

				// verify if the return check all is tick
				if($('check_return_'+json[tr_key]['data']['doc_type']) != null){
					if($('check_return_'+json[tr_key]['data']['doc_type']).checked == true){
						check_all_return(json[tr_key]['data']['doc_type']);
					}
				}
				add_autocomplete_extra(json[tr_key]['data']['doc_type']);
			}
		},
		onComplete: function(m){
			$('span_autocomplete_adding').style.display = 'none';
		}
	});
}

function submit_multi_add(ele){
	var sku_code_list = [];
	$$('#tbl_multi_add input.chx_sid_list').each(function(chx){
		if(chx.checked) sku_code_list.push(chx.value);
	});
	if(sku_code_list.length<=0) return false;
	ele.value = 'Adding...';
	ele.disabled = true;
	ajax_add_multiple_item(sku_code_list);
	multiple_window_close();
}

function show_print_dialog(type){
    if (check_login()) {
        document.f_a.a.value = type;
        if(!action_type){
            if(validate_data() == false) return false;
        
            center_div('wait_popup');
            curtain(true,'curtain2');
            Element.show('wait_popup');
    
            ajax_request('goods_receiving_note.php',{
                method: 'post',
                parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
                onComplete: function(e){
                    if (e.responseText.trim() == 'OK') {
                        document.f_a.a.value = type;
                        document.f_a.submit();
                    }else {
                        Element.hide('wait_popup');
                        curtain(false);
                        curtain(false,'curtain2');
                        alert(e.responseText.trim());
                    }
                }
            });
            return;
        }
        
        if(type == "confirm"){ // do this checking during confirmation stage
            for(doc_type=tbl_start; doc_type<=tbl_end; doc_type++){
                if ($('grn_items_'+doc_type)==undefined) continue;
                var have_items;
                var e = $('grn_items_'+doc_type).getElementsByClassName(doc_type+'_no');
                var total_row=e.length;

                if(doc_type > 0 && doc_type <= tbl_end){
                    if(total_row > 0){
                        have_items = 1;
                    }
                }
            }

            if(!have_items){
                alert("You have encountered below errors: \n\n* No SKU Item(s) found.");
                return;
            }
        }

        curtain(true);
        showdiv('print_dialog');
        center_div('print_dialog');
    }
}

function print_ok(print_grn){
	if(print_grn) document.f_a.grn_rpt_print.value = 1;
	else document.f_a.grn_rpt_print.value = 0;

	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	
	ajax_request('goods_receiving_note.php',{
		method: 'post',
		parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
		onComplete: function(e){
			if (e.responseText.trim() == 'OK') {
				document.f_a['save'].disabled = true;
				document.f_a['confirm'].disabled = true;
				document.f_a.submit();
				//$('print_dialog').style.display = 'none';
				//curtain(false,'curtain2');
				return;
			}
			else {
				Element.hide('wait_popup');
				curtain(false);
				curtain(false,'curtain2');
				alert(e.responseText.trim());
				$('print_dialog').style.display = 'none';
				return;
			}
		}
	});
}

function cancel()
{
	hidediv('sku_list');
	if($('sku_doc_type').value == 3 || $('sku_doc_type').value == 4){
		$('si_cost').value = '';
		$('si_ctn').value = '';
		$('si_pcs').value = '';
		$('doc_allow_decimal').value = '';
		$('si_description').value = '';
		if(document.f_a.elements[$('sku_doc_type').value+'_pcs['+$('si_id').value+']'].value == 0){
			delete_item($('si_id').value, $('sku_doc_type').value, 1);
			item_sequence--;
		}
		$('sku_doc_type').value = '';
	}
	hidediv('si_menu');
	curtain(false);
}

function curtain_clicked(){
    $('print_dialog').hide();
    $('sku_list').hide();
    $('si_menu').hide();
    $('generate_dn_dialog').hide();
    $('div_cost_calc').hide();
	$('si_cost').value = '';
	$('si_ctn').value = '';
	$('si_pcs').value = '';
	$('doc_allow_decimal').value= '';
	$('si_description').value = '';
	if($('reconcile_menu') != undefined) hidediv('reconcile_menu');
	if(document.rcc['amt_strike'] != undefined) document.rcc['amt_strike'].value = '';
	
    curtain(false);
}

function check_all_return(doc_type){
	var item_return = $('tbl_item_'+doc_type).getElementsByClassName(doc_type+"_item_return");
	var total_rows = item_return.length;

	if(total_rows > 0){
		$A(item_return).each(
			function (r,idx){
				if($('check_return_'+doc_type).checked == true){
					r.value = 1;
					r.checked = true;
				}else{
					r.value = 0;
					r.checked = false;
				}
			}
		);
	}
}

function check_return(doc_type, id){
	if($(doc_type+'_item_return'+id).checked == true){
		$(doc_type+'_item_return'+id).value = 1;
	}else{
		$(doc_type+'_item_return'+id).value = 0;
	}
}

function ajax_add_variance_item(sid, variance, parent_id, doc_type){
	if(!confirm('Are you sure want to return this extra qty?')) return;
	var param_str = Form.serialize(document.f_a) + '&a=ajax_add_variance_item';
	ajax_request(phpself, {
		method:'post',
		parameters: param_str+'&sid='+sid+'&variance='+variance+'&parent_id='+parent_id+'&doc_type='+doc_type,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				new Insertion.Bottom($$('.multiple_add_container_'+doc_type).first(),json[tr_key]['html']);
				$(doc_type+'_qty_var'+parent_id).innerHTML = "&nbsp;";
				document.f_a.elements[doc_type+'_pcs['+parent_id+']'].value = float(document.f_a.elements[doc_type+'_pcs['+parent_id+']'].value) - float(variance);
				
				// calculate the total amount after make variance return
				var b = float(document.f_a.elements[doc_type+'_pcs['+parent_id+']'].value) / float(document.f_a.elements[doc_type+'_uom_fraction['+parent_id+']'].value);
				document.f_a.elements[doc_type+'_amt['+parent_id+']'].value = round2(b*(float(document.f_a.elements[doc_type+'_cost['+parent_id+']'].value)));
			}
		},
		onComplete: function(m) {
			reset_row(doc_type);
		}
	});
}

// only use for invalid SKU item
function is_confirm(){
	id = $('si_id').value;
	doc_type = $('sku_doc_type').value;
	$(doc_type+'_cost'+id).value = $('si_cost').value;
	if($('doc_allow_decimal').value != 0){
		$(doc_type+'_pcs'+id).value = float(round($('si_pcs').value, global_qty_decimal_points));
	}else{
		$(doc_type+'_pcs'+id).value = parseInt($('si_pcs').value);
	}
	if(doc_type == 4){
		$(doc_type+'_description'+id).value = $('si_description').value.toUpperCase();
		$('si_description').value = '';
	}else{
		if($('si_ctn').value > 0){
			if($('doc_allow_decimal').value != 0){
				$(doc_type+'_ctn'+id).value = float(round($('si_ctn').value, global_qty_decimal_points));
			}else{
				$(doc_type+'_ctn'+id).value = parseInt($('si_ctn').value);
			}
		}
	}
	recalc_row(id, doc_type);
	$('si_cost').value = '';
	$('si_pcs').value = '';
	$('doc_allow_decimal').value = '';
	$('si_menu').style.display = 'none';
	recalc_totals(doc_type);
	if($('grn_barcode') != undefined) $('grn_barcode').focus();
	curtain_clicked();
	//curtain(false);
}

// auto add up a return sku item 
function ajax_add_return_item(id, qty_var){
	var param_str = Form.serialize(document.f_a)+'&a=ajax_add_variance_item';
	ajax_request(phpself, {
		method:'post',
		parameters: param_str+'&parent_id='+id+"&variance="+qty_var+"&doc_type=3",
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				new Insertion.Bottom($$('.multiple_add_container_3').first(),json[tr_key]['html']);
				recalc_row(json[tr_key]['data']['id'], 3);
				add_autocomplete_extra(3);
			}
		}
	}); 
}

function do_reset(){
    if (check_login()) {
        if(!confirm("Are you sure want to reset?")) return;

        document.f_a.a.value='reset';
        document.f_a.submit();
    }
}

function do_reject(){
    if (check_login()) {
        ajax_request('goods_receiving_note.php',{
            method: 'post',
            parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
            onComplete: function(e){
                if (e.responseText.trim() == 'OK') {
                    var reject_reason = trim(prompt("Please enter reject reason:"));
                    if(!reject_reason) return;
                    
                    center_div('wait_popup');
                    curtain(true,'curtain2');
                    Element.show('wait_popup');
                    document.f_a.reject_reason.value = reject_reason;
                    document.f_a.a.value='reject';
                    document.f_a.submit();
                }else {
                    Element.hide('wait_popup');
                    curtain(false);
                    curtain(false,'curtain2');
                    alert(e.responseText.trim());
                }
            }
        });
    }
}

function ajax_recheck_nsi(item_code){
	var error_show = false;
	var e = $('grn_items_4').getElementsByTagName("INPUT");
	var total_row=e.length;
	
	if(total_row == 0){
		alert("Cannot recheck, no record found!");
		return;
	}
	
	var param_str = Form.serialize(document.f_a)+'&a=ajax_recheck_nsi';
	
	if(item_code && item_code != undefined) param_str += '&item_code='+item_code;
	ajax_request(phpself, {
		method:'post',
		parameters: param_str,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['code_err'] && error_show == false){
					alert(json[tr_key]['code_err']);
					error_show = true;
					continue;
				}else if(json[tr_key]['sku_list']){
					$('sku_list').innerHTML = json[tr_key]['html'];
					curtain(true);
					showdiv('sku_list');
					center_div('sku_list');
					continue;
				}

				if($('no_data_'+json[tr_key]['data']['doc_type']) != undefined) $('no_data_'+json[tr_key]['data']['doc_type']).hide();
				
				new Insertion.Bottom($$('.multiple_add_container_'+json[tr_key]['data']['doc_type']).first(),json[tr_key]['html']);
				
				
				delete_item(json[tr_key]['data']['code'], 4, 1);

				// verify if the return check all is tick
				if($('check_return_'+json[tr_key]['data']['doc_type']) != null){
					if($('check_return_'+json[tr_key]['data']['doc_type']).checked == true){
						check_all_return(json[tr_key]['data']['doc_type']);
					}
				}

				add_autocomplete_extra(json[tr_key]['data']['doc_type']);
			}
		}
	});
}

function multi_add_sku_items(ele, is_recheck){
	var sku_code_list = [];
	$$('#div_sku input.radio_sid_list').each(function(rdo){
		if(rdo.checked) sku_code_list.push(rdo.value);
	});
	if(sku_code_list.length<=0) return false;
	ele.value = 'Adding...';
	ele.disabled = true;
	ajax_add_multiple_item(sku_code_list, is_recheck);
	ele.value = 'Save';
	ele.disabled = false;
	$('sku_list').hide();
}

function sel_uom(id, value, doc_type){
	var sstr = '['+id+']';
	var s = value.split(",");
	
	old_cost=float(document.f_a.elements[doc_type+'_cost'+sstr].value)/float(document.f_a.elements[doc_type+'_uom_fraction'+sstr].value);
	
	if(s[1]>1){
		fraction=s[1];
		old_pcs=document.f_a.elements[doc_type+'_pcs'+sstr].value;
		new_pcs=float(old_pcs%fraction);
		remain=float(old_pcs)-new_pcs;
		ctn=float(remain/fraction)+float(document.f_a.elements[doc_type+'_ctn'+sstr].value);
		
		document.f_a.elements[doc_type+'_pcs'+sstr].value=float(round(new_pcs, global_qty_decimal_points));
		document.f_a.elements[doc_type+'_ctn'+sstr].value=float(round(ctn, global_qty_decimal_points));
	}
	else{
		document.f_a.elements[doc_type+'_ctn'+sstr].value='';
	}
	
	document.f_a.elements[doc_type+'_uom_id'+sstr].value=s[0];
	document.f_a.elements[doc_type+'_uom_fraction'+sstr].value=s[1];
	document.f_a.elements[doc_type+'_ctn'+sstr].disabled=(s[1]<=1);
	
	new_cost=old_cost*float(document.f_a.elements[doc_type+'_uom_fraction'+sstr].value);
	document.f_a.elements[doc_type+'_cost'+sstr].value=round(new_cost,global_cost_decimal_points);
	
	recalc_row(id);
}

function bom_ratio_calculation(id, doc_type){
	if(!id) return;
	
	if(document.f_a[doc_type+'_bom_ref_num['+id+']'] && document.f_a[doc_type+'_bom_ref_num['+id+']'].value.trim() > 0){
		// is bom package
		var bom_ref_num = document.f_a[doc_type+'_bom_ref_num['+id+']'].value.trim();
		var bom_qty_ratio = float(document.f_a[doc_type+'_bom_qty_ratio['+id+']'].value);
		var multiply_ratio = 0;
		var doc_allow_decimal = int(document.f_a[doc_type+'_doc_allow_decimal['+id+']'].value);
		
		var ctn = float(document.f_a[doc_type+'_ctn['+id+']'].value);
		var pcs = float(document.f_a[doc_type+'_pcs['+id+']'].value);
		var uom_fraction = float(document.f_a[doc_type+'_uom_fraction['+id+']'].value);
		var total_pcs = (ctn * uom_fraction) + pcs;
		
		multiply_ratio = float(round(total_pcs / bom_qty_ratio,4));
		
		var bom_ref_num_list = $$('.bom_ref_num_grp_'+bom_ref_num);
		
		if(int(multiply_ratio) != multiply_ratio){	// not allow decimal
			var group_allow_decimal_qty = true;
		
			// loop to check item can decimal qty or not
			for(var i=0; i<bom_ref_num_list.length; i++){
				// get the row grn item id + doc type
				var doc_type = $(bom_ref_num_list[i]).readAttribute('doc_type');
				var item_id = $(bom_ref_num_list[i]).readAttribute('item_id');
				var tmp_doc_allow_decimal = int(document.f_a[doc_type+'_doc_allow_decimal['+item_id+']'].value);

				if(!tmp_doc_allow_decimal){
					group_allow_decimal_qty = false;
					break;
				}
			}
			
			if(!group_allow_decimal_qty) multiply_ratio = int(multiply_ratio);	// group cannot hv decimal, make int
		}
		
		// loop to update qty
		for(var i=0; i<bom_ref_num_list.length; i++){
			// get the row grn item id + doc type
			var doc_type = $(bom_ref_num_list[i]).readAttribute('doc_type');
			var item_id = $(bom_ref_num_list[i]).readAttribute('item_id');
			
			var item_bom_qty_ratio = float(document.f_a[doc_type+'_bom_qty_ratio['+item_id+']'].value);
			
			var item_uom_fraction = float(document.f_a[doc_type+'_uom_fraction['+item_id+']'].value);
			
			var item_ctn = 0;
			var item_pcs = 0;
			var item_total_pcs = item_bom_qty_ratio * multiply_ratio;
			 
			if(item_uom_fraction > 1){
				item_ctn = Math.floor(item_total_pcs / item_uom_fraction);
				item_pcs = item_total_pcs - (item_ctn * item_uom_fraction);
			}else{
				item_pcs = item_total_pcs;
			}
			
			if(item_uom_fraction > 1){
				document.f_a[doc_type+'_ctn['+item_id+']'].value = item_ctn;
			}else{
				document.f_a[doc_type+'_ctn['+item_id+']'].value = "";
			}
			
			document.f_a[doc_type+'_pcs['+item_id+']'].value = item_pcs;
			
			// recal row
			//if(tmp_po_item_id != po_item_id) row_recalc(tmp_po_item_id);
		}
	}
}

function validate_data(){
	if(!action_type && is_under_gst == 1){
		if((!document.f_a['dn_number'].value.trim() && document.f_a['dn_amount'].value.trim() == 0) || (document.f_a['dn_number'].value.trim() && document.f_a['dn_amount'].value.trim() != 0)) return true;
		else{
			alert("Please provide both DN Number and Amount.");
			return false;
		}
	}
	return true;
}

function toggle_dn_dialog(){
	document.f_dn['use_extra_dn'].checked = false;
	document.f_dn['extra_dn_amount'].readOnly = true;
	document.f_dn['extra_dn_amount'].value = "";
	document.f_dn['extra_dn_remark'].readOnly = true;
	document.f_dn['extra_dn_remark'].value = "Goods Receiving Variance";
	showdiv("generate_dn_dialog");
	curtain(true);
}

function generate_dn(){
	if(!confirm("Are you sure want to generate?\nPlease note that all GRN information will be saved and refreshed.")) return;
	document.f_a['save'].disabled = true;
	document.f_a['confirm'].disabled = true;
	
	ajax_request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+"&"+Form.serialize(document.f_dn)+'&a=ajax_generate_dn&extra_dn_remark='+escape(document.f_dn['extra_dn_remark'].value),
		onComplete: function(msg){
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					// do nothing
					document.location='/goods_receiving_note.php?a=open&id='+document.f_a['id'].value+'&branch_id='+document.f_a['grn_bid'].value;
					return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			// prompt the error
			if(err_msg) alert(err_msg);
			document.location='/goods_receiving_note.php?a=open&id='+document.f_a['id'].value+'&branch_id='+document.f_a['grn_bid'].value;
		}
	});
}

function toggle_extra_dn(){
	var obj = document.f_dn['use_extra_dn'];
	
	if(obj.checked == true){
		document.f_dn['extra_dn_amount'].readOnly = false;
		if(is_under_gst) document.f_dn['extra_dn_gst_amount'].readOnly = false;
		document.f_dn['extra_dn_remark'].readOnly = false;
		if(is_under_gst) on_extra_dn_gst_changed(document.f_dn['extra_dn_gst_sel']);
	}else{
		document.f_dn['extra_dn_amount'].readOnly = true;
		if(is_under_gst) document.f_dn['extra_dn_gst_amount'].readOnly = true;
		document.f_dn['extra_dn_remark'].readOnly = true;
	}
}

function on_extra_dn_gst_changed(sel){
	document.f_dn["extra_gst_id"].value = "";
	document.f_dn["extra_gst_code"].value = "";
	document.f_dn["extra_gst_rate"].value = "";
	
	if(sel.selectedIndex >= 0){
		// got select
		var opt = sel.options[sel.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		
		document.f_dn["extra_gst_id"].value = gst_id;
		document.f_dn["extra_gst_code"].value = gst_code;
		document.f_dn["extra_gst_rate"].value = gst_rate;
	}
	
	// recalculate row
	calc_extra_dn_amt(document.f_dn['amount']);
}

function calc_extra_dn_amt(obj){
	
	// calculate selling price after/before GST
	var inclusive_tax = "no";
	var gst_rate = float(document.f_dn['extra_gst_rate'].value);
	if(obj != undefined && obj.name == "extra_dn_gst_amount"){ // found user changing GST selling price
		// calculate gst amount
		var gst_amount = float(obj.value);
		
		if (inclusive_tax=='no') {
			var amount=(gst_amount*100)/(100+gst_rate);
			var gst=float(amount) * gst_rate / 100;
		}
		else{
			var gst=float(gst_amount) * gst_rate / 100;
			var amount=float(gst_amount+gst);
		}

		document.f_dn['extra_dn_amount'].value = round(amount, 2);
	}else{
		var amount = float(document.f_dn['extra_dn_amount'].value);
		
		if (inclusive_tax=='yes') {
			var gst_amount=(amount*100)/(100+gst_rate);
			var gst=float(gst_amount) * gst_rate / 100;
		}
		else{
			var gst=float(amount) * gst_rate / 100;
			var gst_amount=float(amount+gst);
		}
		
		document.f_dn['extra_dn_gst_amount'].value=round(gst_amount,2);
	}
	
	document.f_dn['extra_dn_gst'].value = round(gst, 2);
}

function load_po_items() {
    var po_si_code = $('tbl_item_0').getElementsByClassName("0_sku_item_code");
    var po_total_rows = po_si_code.length;
    var po_item_list = [];
    
    var po_si_code_list = $('tbl_item_1').getElementsByClassName("1_sku_item_code");
  
    $A(po_si_code_list).each(
        function (i) {
            var ri = i.title.split(",");
            po_item_list.push($('1_po_item_id['+ri[0]+']').value);
        }
    );
  
    $$('input[type="radio"][name="grn_barcode_type"][value="1"]')[0].checked=true;

    // found have PO items
    if(po_total_rows > 0){
        skip_ask_qty=true;
        $A(po_si_code).each(
            function (r,idx){
                if (r.name.indexOf("0_sku_item_code[")==0){
                    // split title for information 0=id, 1=sku_item_code, 2=link_code,3=artno,4=mcode
                    var ri = r.title.split(",");
                    if (po_item_list.indexOf($('0_po_item_id['+ri[0]+']').value) == -1) {
                        add_grn_barcode_item(ri[1], $('0_po_item_id['+ri[0]+']').value);
                    }
                }
            }
        );
        skip_ask_qty=false;
    }
}

function check_cost_keypress(event){
	var kc = event.keyCode;
	var which = event.which;
	var str = String.fromCharCode(which);

	switch(kc)
	{
		case 13:    // enter
			$('si_pcs').focus();
			$('si_pcs').select();
			break;
		default:    // other
			break;
	}
}

function check_pcs_keypress(event){
	var kc = event.keyCode;
	var which = event.which;
	var str = String.fromCharCode(which);

	switch(kc)
	{
		case 13:    // enter
			is_confirm();
			break;
		default:    // other
			break;
	}
}

function calc_new_cost(){
	var id = $('adjust_item_id').value;
	
	if(id == "") return;

	var cost_price = $('cp_adjust').value;
	var disc_format = $('disc_adjust').value;

	if(document.f_a['acc_ctn['+id+']'].value=='' && document.f_a['acc_pcs['+id+']'].value==''){
		var rcv_qty = float(document.f_a['ctn['+id+']'].value) * float(document.f_a['uomf['+id+']'].value) + float(document.f_a['pcs['+id+']'].value);
	}else{
		var rcv_qty = float(document.f_a['acc_ctn['+id+']'].value) * float(document.f_a['uomf['+id+']'].value) + float(document.f_a['acc_pcs['+id+']'].value);
	}
	rcv_qty = float(round(rcv_qty, global_qty_decimal_points));
	var foc_qty = (float($('foc_ctn_adjust').value) * float(document.f_a['uomf['+id+']'].value)) + float($('foc_pcs_adjust').value);
	var row_amt = float(cost_price) * float(rcv_qty) / float(document.f_a['uomf['+id+']'].value);
	var row_disc_amt = float(get_discount_amt(row_amt, disc_format));
	var new_qty = float(rcv_qty) - float(foc_qty);
	if(new_qty < 0) new_qty = 0;
	var new_cost_price = round(((float(new_qty) * float(cost_price)) - float(row_disc_amt)) / float(rcv_qty), global_cost_decimal_points);
	var unit_disc_amt = float(row_disc_amt) / float(rcv_qty);
	var foc_amt = round(float(cost_price) - float(new_cost_price) - float(unit_disc_amt), global_cost_decimal_points);
	
	if($('foc_ctn_adjust').value!='' || $('foc_pcs_adjust').value!='') $('foc_amt_adjust').value = round(foc_amt, global_cost_decimal_points);
	else $('foc_amt_adjust').value = "";
	$('disc_amt_adjust').value = round(unit_disc_amt, global_cost_decimal_points);
	if(new_cost_price < 0) new_cost_price = 0;
	$('new_cp_adjust').value = round(new_cost_price, global_cost_decimal_points);
}

function confirm_calc_new_cost_confirm(){
	var id = $('adjust_item_id').value;
	
	if(id == "") return;
	
	document.f_a['acc_cost['+id+']'].value = $('new_cp_adjust').value;
	document.f_a['acc_foc_ctn['+id+']'].value = $('foc_ctn_adjust').value;
	document.f_a['acc_foc_pcs['+id+']'].value = $('foc_pcs_adjust').value;
	document.f_a['acc_foc_amt['+id+']'].value = $('foc_amt_adjust').value;
	document.f_a['acc_disc['+id+']'].value = $('disc_adjust').value;
	document.f_a['acc_disc_amt['+id+']'].value = $('disc_amt_adjust').value;
	
	if(document.f_a['acc_disc['+id+']'].value != ''){
		$('span_acc_disc_'+id).update("<br />Disc: "+document.f_a['acc_disc['+id+']'].value);
		showdiv('span_acc_disc_'+id);
	}else{
		$('span_acc_disc_'+id).update("");
		hidediv('span_acc_disc_'+id);
	}
	
	if(document.f_a['acc_foc_ctn['+id+']'].value!='' || document.f_a['acc_foc_pcs['+id+']'].value!=''){
		var msg = "<br />FOC Ctn: "+float(document.f_a['acc_foc_ctn['+id+']'].value)+"&nbsp;&nbsp;Pcs: "+float(document.f_a['acc_foc_pcs['+id+']'].value);
		$('span_acc_foc_'+id).update(msg);
		showdiv('span_acc_foc_'+id);
	}else{
		$('span_acc_foc_'+id).update("");
		hidediv('span_acc_foc_'+id);
	}
	
	hidediv('div_cost_calc');
	curtain(false);
	calc_acc_total();
}

function discount_change(obj){
    var discount_format = obj.value.trim();
    
    // check discount pattern
	discount_format = validate_discount_format(discount_format);
	
	obj.value = discount_format;
	
	calc_new_cost();
}

function negative_check(obj){
	if(isNaN(obj.value)) return;
	obj.value = Math.abs(obj.value);
}

var GRN_MODULE = {
	initialize : function(){
		//_init_enter_to_skip(document.f_a);
		new Draggable('sel_vendor_sku');
		new Draggable('generate_dn_dialog');
		new Draggable('div_cost_calc');
		
		if(action_type == "edit") reset_sku_autocomplete();
		
		recalc_totals();
		reset_row();
		
		new Ajax.PeriodicalUpdater('', "dummy.php", { frequency:1500 });
	},
	
	check_skip_sku_item_id: function(){
		var si_id_list = [];
		for(var doc_type=1; doc_type<=3; doc_type++){
			if ($('grn_items_'+doc_type)==undefined) continue;
			var all_si_code = $('tbl_item_'+doc_type).getElementsByClassName(doc_type+"_sku_item_id");
			var all_si_rows = all_si_code.length;
			
			if(all_si_rows > 0){
				$A(all_si_code).each(
					function (r,idx){
						si_id_list.push(r.value);
					}
				);
			}
		}
	
		return si_id_list;
	},
	
	download_image: function(obj,fp){
		$('_download').src = "goods_receiving_record.php?a=download_photo&f="+fp;
	}
}

function check_skip_sku_item_id(){
	return GRN_MODULE.check_skip_sku_item_id();
}

function show_upload_csv_popup(){
	center_div('wait_popup');
	Element.show('wait_popup');
	
	document.f_a['a'].value = 'ajax_open_csv_popup';
    var form_data = this.f_a.serialize();
	
	new Ajax.Request(phpself, {
		method: 'post',
		parameters: form_data,
		onComplete: function(msg){			    
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			Element.hide('wait_popup');
			
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
</script>
{/literal}
{if $smarty.request.action eq 'verify'}
	{assign var=div1_id value=$form.div1_approved_by}
	{assign var=div2_id value=$form.div2_approved_by}
	{assign var=div3_id value=$form.div3_approved_by}
{elseif !$smarty.request.action}
	{assign var=div4_id value=$form.div4_approved_by}
{/if}

<div id="print_dialog" style="background:#fff;border:3px solid #000;width:250px;height:120px;position:absolute; padding:10px; display:none;z-index:20000;">
	<div class="small" style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
	</div>
<img src="ui/print64.png" hspace=10 align=left> <h3>GRN Report</h3>
Do you wish to print GRN report?<br>
<p align=center>
	<input type="button" value="Yes" onclick="print_ok(1)">
	<input type="button" value="No" onclick="print_ok(0)">
</p>
</div>

<div id=wait_popup style="display:none;position:absolute;z-index:20001;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align=center>
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>

<div id=sku_list style="position:absolute;left:0;top:0;display:none;width:600px;height:410px;padding:10px;border:1px solid #000;background:#fff;z-index:20000;">
</div>

<div id="div_upload_csv" class="curtain_popup" style="z-index:10000;max-width:600px;height:550px;display:none;max-height:550px;overflow:auto;min-width:550px;">
	{include file='goods_receiving_note2.upload_csv.tpl'}
</div>

<div id="si_menu" style="background:#fff;border:3px solid #000;width:250px;height:200px;position:absolute; padding:10px; display:none;z-index:20000;">
<div id="si_msg"></div>
<form name="si_form"  onsubmit="return false;">
	<table border="0">
		<tr align="left">
			<th nowrap style="min-width:100px;">Selling Price:<div id="gst_status"></div></th>
			<td style="text-align: right" id="si_sell"></td>
		</tr>    
		<tr align="left">
			<th>Cost:</th>
			<th>
				<input id="si_cost" name="si_cost" size=5 onchange="this.value=round(this.value,{$config.global_cost_decimal_points});" onfocus="this.select();" onkeypress="check_cost_keypress(event);" class="r">
			</th>
		</tr>
		<tr align="left" id="tr_ctn">
			<th>Ctn:</th>
			<th><input id="si_ctn" name="si_ctn" size=5 class="r"></th>
		</tr>
		<tr align="left">
			<th>Pcs:</th>
			<th><input id="si_pcs" name="si_pcs" size=5 class="r" onkeypress="check_pcs_keypress(event);"></th>
		</tr>
		<tr align="left" id="tr_description">
			<th>Description:</th>
			<td><input id="si_description" name="si_description" size=25></td>
		</tr>
		<tr align="center">
			<td colspan="2">
			</td>
		</tr>
	</table>
	<p align="center">
		<input type="button" value="Ok" onClick="is_confirm();">
		<input type="button" value="Cancel" onclick="cancel()">
		<input type="hidden" id="si_id" name="si_id">
		<input type="hidden" id="sku_doc_type" name="sku_doc_type">
		<input type="hidden" id="doc_allow_decimal">
	</p>
</form>
</div>

<form name="rcc" onsubmit="strike_row(); return false;">
<div id="reconcile_menu" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:200px; left:200px;">
	<div class="small" style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
	</div>
	<div class="stdframe">
		<p>
			<h4>Reconcile Menu:</h4><br>
			Amount: <input type="text" size="10" name="amt_strike" id="amt_strike" style="text-align:right;">
		</p> 
		<p {if !$form.is_under_gst}style="display:none;"{/if}>
			<input type="checkbox" name="match_gst_amt" id="match_gst_amt" value="1" /> Match Amount Includes GST
		</p> 
		<p align="center" id="choices">
			<input type="submit" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Ok">
		</p>
	</div>
</div>
</form>

<form name="f_dn">
<div id="generate_dn_dialog" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:200px; left:200px; z-index:20000;">
	<div class="small" style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
	</div>
	<div class="stdframe">
		<table>
			<tr>
				<th align="left" colspan="2"><h4>Generate D/N Menu:</h4></th>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="use_extra_dn" value="1" onclick="toggle_extra_dn();" /> <b>Extra D/N Row</b></td>
			</tr>
			<tr>
				<th align="left">Amount:</th>
				<td><input type="text" size="10" name="extra_dn_amount" onchange="mf(this, 2);positive_check(this);" {if $form.is_under_gst}onkeyup="calc_extra_dn_amt(this);"{/if} style="text-align:right;"></td>
			</tr>
			{if $form.is_under_gst}
				<tr>
					<th align="left">GST:</th>
					<td><input type="text" size="10" name="extra_dn_gst" style="text-align:right;" readonly></td>
				</tr>
				<tr>
					<th align="left">Amount Incl. GST:</th>
					<td><input type="text" size="10" name="extra_dn_gst_amount" onchange="mf(this, 2);positive_check(this);" onkeyup="calc_extra_dn_amt(this);" style="text-align:right;" readonly></td>
				</tr>
				<tr>
					<th align="left">GST Code:</th>
					<td>
						<select name="extra_dn_gst_sel" class="small" onchange="on_extra_dn_gst_changed(this);">
							{foreach from=$gst_list key=rid item=gst}
								<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $acc_gst_id eq $gst.id and $acc_gst_code eq $gst.code and $acc_gst_rate eq $gst.rate}selected {/if}>{$gst.code} ({$gst.rate}%)</option>
							{/foreach}
						</select>
						<input type="hidden" name="extra_gst_id" value="" />
						<input type="hidden" name="extra_gst_code" value="" />
						<input type="hidden" name="extra_gst_rate" value="" />
					</td>
				</tr>
			{/if}
			<tr>
				<th align="left">Description:</th>
				<td><textarea name="extra_dn_remark" readonly>Goods Receiving Variance</textarea></td>
			</tr> 
			<tr>
				<td colspan="2" align="center"><input type="button" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Generate" onclick="generate_dn();"></td>
			</tr>
		</table>
	</div>
</div>
</form>

<table style="display:none;">
	<tbody id="temp_row" class="temp_row">
		<tr bgcolor="__bgcol__">
			<td>__rowno__</td>
			<td>
				<input name="grr_item_id[__id__]" type="hidden">
				<input id="doc___id__" name="doc_no[__id__]" size="15" onchange="uc(this);grr_add_row(__id__);check_doc_no(__id__); check_duplicate_doc_no(__id__); grr_recalc_row();" maxlength="20">
			</td>
			<td nowrap>
				<input name="doc_date[__id__]" id="doc_date___id__" maxlength="10" size="8" readonly>
				<img align="absmiddle" src="ui/calendar.gif" id="dd_added___id__" style="cursor: pointer;" title="Select Document Date">
			</td>
			<td align="center">
				<input onclick="hideamt('__id__'); check_doc_no(__id__); grr_recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="PO" {if $grr.currency_code && $grr.use_po_currency}disabled{/if}>
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); grr_recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="INVOICE">
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); grr_recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="DO">
			</td>
			<td align="center">
				<input onclick="showamt('__id__'); grr_recalc_row(); check_duplicate_doc_no(__id__);" type="radio" name="type[__id__]" class="doc_type[__id__]" value="OTHER">
			</td>
			<td><input name="ctn[__id__]" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points})); grr_recalc_row();" size="7" class="r"></td>
			<td><input name="pcs[__id__]" onchange="this.value=float(round(this.value, {$config.global_cost_decimal_points})); grr_recalc_row();" size="7" class="r"></td>
			<td><input name="amount[__id__]" onchange="mf(this); grr_recalc_row();" size="10" class="r" maxlength="10"></td>
			<td class="gst_info"><input name="gst_amount[__id__]" onchange="mf(this); grr_recalc_row();" size="10" class="r gst_fields" maxlength="10"></td>
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
			<td class="tax_info"><input name="tax[__id__]" onchange="mf(this); grr_recalc_row();" size="10" class="r grr_item_tax" maxlength="10" /></td>
			<td><input size="50" class="small" name="remark[__id__]"></td>
		</tr>
	</tbody>
</table>

<div id="div_cost_calc" style="display:none; left:0; top:0; padding:10px; background-color: #fff; border:4px solid #999; position:absolute; width:200px; z-index:20000;">
	<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
	<div class="stdframe">
		<input type="hidden" id="adjust_item_id" value="">
		<table border="0" cellspacing="0" cellpadding="4">
			<tr>
				<td colspan="2"><h4>{if $grr.type eq 'PO'}Order Price{else}Cost{/if} Adjustment:</h4></td>
			</tr> 
			<tr>
				<td><b>{if $grr.type eq 'PO'}Order Price{else}Cost{/if}</b></td>
				<td align="right">
					<label id="lbl_cp_adjust">&nbsp;</label>
					<input type="hidden" id="cp_adjust" value="">
				</td>
			</tr>
			<tr>
				<td><b>Discount [<a href="javascript:void(show_discount_help());">?</a>]</b></td>
				<td>
					<input type="text" size="10" id="disc_adjust" style="text-align:right;" onchange="discount_change(this);">
					<input type="hidden" size="10" id="disc_amt_adjust" value="" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<fieldset>
					<legend><b>FOC</b></legend>
						<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="30%"><b>Ctn</b></td>
								<td width="70%" align="right"><input type="text" size="5" id="foc_ctn_adjust" style="text-align:right;" onchange="mf(this, {$config.global_qty_decimal_points}); negative_check(this); calc_new_cost();"></td>
							</tr>
							<tr>
								<td width="30%"><b>Pcs</b></td>
								<td width="70%" align="right"><input type="text" size="5" id="foc_pcs_adjust" style="text-align:right;" onchange="mf(this, {$config.global_qty_decimal_points}); negative_check(this); calc_new_cost();"></td>
							</tr>
							<tr>
								<td width="30%"><b>Amount</b></td>
								<td width="70%" align="right"><input type="text" size="5" id="foc_amt_adjust" style="text-align:right;" readonly /></td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td><b>New {if $grr.type eq 'PO'}Order Price{else}Cost{/if}</b></td>
				<td><input type="text" size="10" id="new_cp_adjust" style="text-align:right;" class="r" readonly /></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="button" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Ok" onclick="confirm_calc_new_cost_confirm();">
				</td>
			</tr>
		</table>
	</div>
</div>

<!-- download grr attachment -->
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>
					Goods Receiving Note {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}
	{if $smarty.request.action eq 'verify'}
		- SKU Manage
	{elseif $smarty.request.action eq 'grr_edit'}
		- Pending Documents
	{/if}
	{if $smarty.request.action eq 'edit' && $form.authorized eq '1'}
		(Confirmed)
	{/if}
				</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{include file=approval_history.tpl}

<form name="f_a" method="post" {if $smarty.request.action eq 'grr_edit'}action="{$smarty.server.PHP_SELF}" onsubmit="return check_a()"{/if}>
<input type="hidden" name="a" value="{if $smarty.request.action eq 'grr_edit'}grr_{/if}save" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="grn_bid" value="{$form.branch_id}" />
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
<input type="hidden" name="vendor_id" value="{$form.vendor_id}" />
<input type="hidden" name="grr_id" value="{$form.grr_id}" />
<input type="hidden" name="rcv_date" value="{$grr.rcv_date}" />
<input type="hidden" name="grr_item_id" value="{$form.grr_item_id}" />
<input type="hidden" name="type" value="{$grr.type}" />
<input type="hidden" name="doc_no" value="{$grr.doc_no}" />
<input type="hidden" name="grn_rpt_print" value="" />
<input type="hidden" name="grn_get_weight" value="{$grr.grn_get_weight}" />
<input type="hidden" name="allow_grn_without_po" value="{$grr.allow_grn_without_po}" />
<input type="hidden" name="action" value="{$smarty.request.action}" />
<input type="hidden" name="is_ibt_do" value="{$grr.is_ibt_do}" />
<input type="hidden" name="reject_reason" value="" />
<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}" />
<input type="hidden" name="branch_is_under_gst" value="{$form.branch_is_under_gst}" />

<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
<h4>General Information</h4>
<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRR No</b></td><td>{$form.report_prefix}{$grr.grr_id|string_format:"%05d"}</td>
<td><b>Branch</b></td><td>{$form.branch_code}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td><div id="grr_amount_dis">{$grr.grr_amount|number_format:2}</div></td>
<td><b>Received Qty</b></td><td><div id="grr_qty_dis">Ctn:{$grr.grr_ctn|qty_nf} / Pcs:{$grr.grr_pcs|qty_nf}</div></td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td>
<td colspan="3">
<input type="hidden" name="department_id" value="{$form.department_id}">
{$grr.department}
</td>
{if $grr.invoice_no}
	<td><b>Invoice No.</b></td>
	<td style="color:blue" colspan="3">{$grr.invoice_no}</td>
{/if}
</tr><tr>
<td valign="top"><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td valign="top"><b>Lorry No</b></td><td>{$grr.transport}</td>
{if $config.grn_summary_show_related_invoice && $grr.type eq 'PO'}
<td valign="top"><b>Related Invoice</b></td><td>{$grr.related_invoice}</td>
{/if}
</tr>
<tr>
<td width=100 valign="top"><b>Document Type</b></td><td width=100 valign="top"><font color="blue">{$grr.type}</font></td>
<td width=100 valign="top"><b>Document No.</b></td><td width=150 valign="top"><font color="blue"><input type="hidden" name="doc_no" value="{$grr.doc_no}">{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100 valign="top"><b>PO Amount{if $grr.po_is_under_gst}<br />(Exclude GST){/if}</b></td><td width=100 valign="top"><font color="blue">{$grr.po_amount|number_format:2}</font></td>
<td width=100 valign="top"><b>Partial Delivery</b></td><td width=150 valign="top"><font color="blue">{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}</font></td>
</tr>
{/if}
{if $grr.currency_code}
	<tr>
		<td><b>Currency</b></td>
		<td>
			<font color="blue">
				{if !$grr.currency_code}
					Base Currency
				{else}
					{$grr.currency_code}
				{/if}
			</font>
		</td>
		{if $grr.currency_code}
			<td><b>Exchange Rate</b></td>
			<td>
				<font color="blue">{$grr.currency_rate|default:1}</font>
			</td>
		{/if}
	</tr>
{/if}

<input type="hidden" name="ttl_grr_amt" value="{$grr.grr_amount|round2}">
<input type="hidden" name="ttl_grr_gst_amt" value="{$grr.grr_gst_amount|round2}">

<tr>
	<td><b>Tax</b> <a href="javascript:void(alert('{$LANG.GRR_TAX_PERCENT_INFO|escape:javascript}'));">[?]</a></td>
	<td>
		<span>{$form.grn_tax|default:0} %</span>
		<input type="hidden" name="grn_tax" value="{$form.grn_tax}" />
	
	</td>
	<td><b>Total GRR Tax</b></td>
	<td>
		<span id="span_grr_tax">{$grr.grr_tax|number_format:2|default:0}</span>
		<input type="hidden" name="grr_tax" value="{$grr.grr_tax|default:0}" />
	</td>
</tr>

{if $photo_items}
	<tr>
		<td colspan="8">
			{foreach from=$photo_items item=p name=i}
				<div class="imgrollover">
					<div align="center" width="auto" height="auto">
						<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p.image_file|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$p.image_file|escape:javascript}');" title="View">
					</div>
					
					{if $form.grr_id}
						<img src="/ui/application_put.png" align="absmiddle" valign="right" onclick="GRN_MODULE.download_image(this.parentNode,'{$p.download_file|urlencode}')"> Download
					{/if}
				</div>
			{/foreach}
		</td>
	</tr>
{/if}
</table>

{if $config.use_grn_future_allow_generate_gra}
	* This GRN will generate all returned items to become GRA once approved.
	<input type="hidden" name="generate_gra" value="1" />
{/if}
</div>
	</div>
</div>

{if $smarty.request.action eq 'edit'}
<div style="padding:10px 0;">		
	{if $config.check_block_grn_as_po}
		{include file='sku_items_autocomplete.tpl' multiple_add=1 block_list=1}
	{else}
		{include file='sku_items_autocomplete.tpl' multiple_add=1 doc_block_type='grn'}
	{/if}
	
	{include file='scan_barcode_autocomplete.tpl'}
	{if ($grr.type eq 'INVOICE' || $grr.type eq 'OTHER') && ($smarty.request.action eq 'edit' && !$form.authorized) }
	<input id="btn_add_item_by_csv" type="button" value="Add items by CSV" onclick="show_upload_csv_popup();">
	{/if}
	<hr noshade size=1>
</div>
{else}
	<input type="hidden" name="sku_item_id" value=0>
	<input type="hidden" name="sku_item_code" id="sku_item_code" value="">
	<input type="hidden" name="grn_barcode" id="grn_barcode" value="">
	<input type="hidden" name="autocomplete_sku" id="autocomplete_sku" value="">
{/if}

<span id="span_autocomplete_adding" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Adding... Please wait</span>

{if $errm.top}
<div class="alert alert-danger mx-3 rounded">
	<div id=err><div class=errmsg><ul>
		{foreach from=$errm.top item=e}
		<li> {$e}
		{/foreach}
		</ul></div></div>
</div>
{/if}

{* set tables that to be print on the list *}

{* if $grr.type eq 'PO'}
	{assign var=tbl_val value=-1}
	{assign var=tbl_loop value=6}
{else}
	{assign var=tbl_val value=2}
	{assign var=tbl_loop value=2}
{/if *}

{if $smarty.request.action eq 'edit' || ($smarty.request.action eq 'verify' && ($sessioninfo.privilege.GRN_VAR_DIV || $sessioninfo.privilege.GRN_SIV_DIV || $sessioninfo.privilege.GRN_PC_DIV))}
{if $smarty.request.action eq 'verify'}
<p class="alert alert-primary rounded mx-3"><b>
	Confirm required: <br />
	* All SKU not in ARMS must become {if $form.type eq 'PO' || $grr.is_ibt_do}Items not in {$grr.type}{else}Received items{/if} OR mark as Return. 
	{if $config.grn_check_selling_price && ($grr.type eq 'PO' || $grr.is_ibt_do)}
	<br />* All Remarks must be key in for Suggested Selling Price Item(s).
	{/if}
</b></p>
{/if}
{* print different tables based on the table loop set *}
{if $grr.type eq 'PO' || $grr.is_ibt_do}
<div style="overflow:auto;{if !$config.grn_future_show_po}display:none;{/if}">

<div class="card mx-3 card-body">
	<div class="table-responsive">
		<table width="100%" class="tbl_item table mb-0 text-md-nowrap  table-hover" id="tbl_item_0" style="background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
			<thead class="bg-gray-100">
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">ARMS Code</th>
				<th rowspan="2">Artno</th>
				<th rowspan="2">Mcode</th>
				{if $config.link_code_name && $config.docs_show_link_code}
					<th rowspan="2">{$config.link_code_name}</th>
				{/if}
				<th rowspan="2" width="80%">Description</th>
				<th colspan="2">Order Qty</th>
			</tr>
			<tr align="center">
				<th>Ctn</th>
				<th>Pcs</th>
			</tr>
			</thead>
		
			<tbody id="grn_items_0" class="multiple_add_container_0">
			{assign var=doc_type value=0}
			{foreach from=$form.items item=item name=fitem}
				{if $item.item_group eq '0'}
					<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="0_titem{$item.id}" id="0_titem{$item.id}">
					{include file=goods_receiving_note2.new.list.tpl}
					</tr>
				{/if}
			{/foreach}
			</tbody>
			
		</table>
	</div>
</div>
</div>

{assign var=confirmed value=0}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>
					Matched with {$grr.type}
		{if $div1_id}
			{assign var=confirmed value=1}
			<input type="hidden" name="div1_confirmed" value="1">
			(Confirmed)
		{/if}
        {if $allow_auto_load_po_items}
        <input type="button" value="Load all PO Items" onclick="load_po_items();"/>
        {/if}
				</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

	<div class="card mx-3 card-body">
		<div class="table-responsive">
			<table width="100%" class="tbl_item" id="tbl_item_1"  class="input_no_border body" cellspacing=1 cellpadding=4>
				<thead class="bg-gray-100">
				<tr>
					<th rowspan="2">#</th>
					<th rowspan="2">ARMS Code</th>
					<th rowspan="2">Artno</th>
					<th rowspan="2">Mcode</th>
					{if $config.link_code_name && $config.docs_show_link_code}
						<th rowspan="2">{$config.link_code_name}</th>
					{/if}
					<th rowspan="2" width="80%">Description</th>
					<th rowspan="2">Packing<br />UOM</th>
					{if $smarty.request.action eq 'verify' && $sessioninfo.privilege.GRN_SHOW_PO_QTY}
						<th colspan="2">{$grr.type} Qty</th>			
					{/if}
					<th colspan="2">Rcv Qty</th>
					{if $smarty.request.action eq 'verify'}
						<th colspan="2">Return Qty</th>
					{/if}
					<th rowspan="2">Remark</th>
				</tr>
				<tr align="center">
					{if $smarty.request.action eq 'verify'}
						<th>Ctn</th>
						<th>Pcs</th>
						{if $sessioninfo.privilege.GRN_SHOW_PO_QTY}
							<th>Ctn</th>
							<th>Pcs</th>		
						{/if}
					{/if}
					
					<th>Ctn</th>
					<th>Pcs</th>
				</tr>
				</thead>
			
				<tbody id="grn_items_1" class="multiple_add_container_1">
					{assign var=doc_type value=1}
					{assign var=have_items value=0}
					{assign var=ttl_po_ctn value=0}
					{foreach from=$form.items item=item name=fitem}
						{if $item.item_group eq '1' || $item.item_group eq '2' || ($item.item_group eq '0' && $smarty.request.action eq 'verify')}
							{if $item.from_isi}
								<tr bgcolor="#AFC7C7" title="{$item.sku_item_code} is new SKU item" onmouseover="this.bgColor='#CFECEC';" onmouseout="this.bgColor='#AFC7C7';" class="titem_1" id="1_titem{$item.id}">
							{else}
								<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="titem_1" id="1_titem{$item.id}">
							{/if}
							{include file=goods_receiving_note2.new.list.tpl existed_item=1}
							</tr>
							{assign var=have_items value=1}
							{if $sessioninfo.privilege.GRN_SHOW_PO_QTY}
								{assign var=ttl_po_ctn value=$ttl_po_ctn+$item.po_ctn}
								{assign var=ttl_po_pcs value=$ttl_po_pcs+$item.po_pcs}
							{/if}
						{/if}
					{/foreach}
					{if !$have_items && $smarty.request.action ne 'edit'}
						<tr align="center" id="no_data_{$doc_type}">
							<td colspan="10">No data</td>
						</tr>
					{/if}
				</tbody>
			
				<tfoot>
					<tr bgcolor=#ffffff>
						{assign var=colspan value=6}
						{if $config.link_code_name && $config.docs_show_link_code}
							{assign var=colspan value=$colspan+1}
						{/if}
						<td colspan="{$colspan}" align="right"><b>Total</b></td>
						{if $smarty.request.action eq 'verify' && $sessioninfo.privilege.GRN_SHOW_PO_QTY}
							<td colspan="2" align="right" nowrap>Ctn: {$ttl_po_ctn|qty_nf|default:'0'} Pcs: {$ttl_po_pcs|qty_nf|default:'0'}</td>
						{/if}
						<td colspan="2" align="right" id="total_qty_1">&nbsp;</td>
						{if $smarty.request.action eq 'verify'}
							<td colspan="2" align="right" id="total_return_qty_1">&nbsp;</td>
						{/if}
						<td align="center"><div id="{if $new ne 1}total_qty_var_1{/if}" class=r>&nbsp;</div></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
{/if}

{assign var=confirmed value=0}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>
					{if ($grr.type eq 'PO' || $grr.is_ibt_do) && !$grr.allow_grn_without_po}
		SKU Return List 
	{elseif $grr.type eq 'PO' || $grr.is_ibt_do}
		Items not in {$grr.type} 
	{else}
		Received items 
	{/if}
	{if (($grr.type eq 'PO'  || $grr.is_ibt_do) && $div1_id) || ($grr.type ne 'PO' && $div2_id)}
		{assign var=confirmed value=1}
		<input type="hidden" name="div2_confirmed" value="1">
		(Confirmed)
	{/if}
				</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3 card-body">
	<div class="table-responsive">
		<table width="100%" class="tbl_item" id="tbl_item_3" style="background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
			<thead>
			<tr>
				{assign var=col_d value=8}
				<th rowspan="2">
					Return
					{if (($grr.type eq 'PO'  || $grr.is_ibt_do) && $grr.allow_grn_without_po && !$form.div2_approved_by) || ($grr.type ne 'PO' && !$form.div2_approved_by)}
						<br><input type=checkbox id="check_return_3" name="check_return_3" onclick="check_all_return('3')" title="Return All">
					{/if}
				</th>
				<th rowspan="2">#</th>
				<th rowspan="2">ARMS Code</th>
				<th rowspan="2">Artno</th>
				<th rowspan="2">Mcode</th>
				{if $config.link_code_name && $config.docs_show_link_code}
					<th rowspan="2">{$config.link_code_name}</th>
				{/if}
				<th rowspan="2" width="80%">Description</th>
				<th rowspan="2">Cost Price</th>
				<th rowspan="2">Packing<br />UOM</th>
				<th colspan="2">Rcv Qty</th>
			</tr>
			<tr align="center">
				<th>Ctn</th>
				<th>Pcs</th>
			</tr>
			</thead>
		
			<tbody id="grn_items_3" class="multiple_add_container_3">
				{assign var=doc_type value=3}
				{assign var=have_items value=0}
				{foreach from=$form.items item=item name=fitem}
					{if $item.item_group eq '3'}
						{if $item.from_isi}
							<tr bgcolor="#AFC7C7" title="{$item.sku_item_code} is new SKU item" onmouseover="this.bgColor='#CFECEC';" onmouseout="this.bgColor='#AFC7C7';" class="titem_3" id="3_titem{$item.id}">
						{else}
							<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="titem_3" id="3_titem{$item.id}">
						{/if}
						{include file=goods_receiving_note2.new.list.tpl}
						</tr>
						{assign var=have_items value=1}
					{/if}
				{/foreach}
				{if !$have_items && $smarty.request.action ne 'edit'}
					<tr align="center" id="no_data_{$doc_type}">
						<td colspan="9">No data</td>
					</tr>
				{/if}
			</tbody>
		
			<tfoot>
				<tr bgcolor=#ffffff>
					{if $config.link_code_name && $config.docs_show_link_code}
						{assign var=col_d value=$col_d+1}
					{/if}
				
					<td colspan="{$col_d|default:7}" align=right><b>Total</b></td>
					<td align=center colspan="2" id="total_qty_3">&nbsp;</td>
				</tr>
			</tfoot>	
		</table>
	</div>
</div>


{assign var=confirmed value=0}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>
					SKU not in ARMS 
	{if $div2_id}
		{assign var=confirmed value=1}
		<input type="hidden" name="div3_confirmed" value="1">
		(Confirmed)
	{elseif $smarty.request.action eq 'verify' && $form.non_sku_items}
		<input type=button name="recheck_btn" value="Recheck All" style="font:bold 14px Arial; background-color:#09a; color:#fff;" onclick="ajax_recheck_nsi()">
	{/if}
				</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="tbl_item" id="tbl_item_4" style=" background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
				<thead class="bg-gray-100">
				<tr>
					{assign var=col_d value=5}
					<th>
						Return
						{if $sessioninfo.privilege.GRN_SIV_DIV && !$div2_id}
							<br><input type=checkbox id="check_return_4" name="check_return_4" onclick="check_all_return('4')" title="Return All">
						{/if}
					</th>
					<th>#</th>
					<th width="20%">Code</th>
					<th width="60%">Description</th>
					<th>Cost Price</th>
					<th>Rcv<br />Qty (Pcs)</th>
				</tr>
				</thead>
			
				<tbody id="grn_items_4" class="multiple_add_container_4">
					{assign var=doc_type value=4}
					{assign var=have_items value=0}
					{if $form.non_sku_items}
						{foreach from=$form.non_sku_items.code key=sku_code item=qty name=fitem}
							{assign var=n value=$smarty.foreach.fitem.iteration-1}
							<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="titem_4" id="4_titem{$form.non_sku_items.code.$n|default:$sku_code}">
							{include file=goods_receiving_note2.new.list.tpl}
							</tr>
							{assign var=have_items value=1}
						{/foreach}
						{if !$have_items && $smarty.request.action ne 'edit'}
							<tr align="center" id="no_data_{$doc_type}">
								<td colspan="6">No data</td>
							</tr>
						{/if}
					{/if}
				</tbody>
			
				<tfoot>
					<tr bgcolor=#ffffff>
						<td colspan="{$col_d|default:4}" align=right><b>Total</b></td>
						<td align=center id="total_qty_4">&nbsp;</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
{/if}

{assign var=confirmed value=0}
{if ($grr.type eq 'PO' || $grr.is_ibt_do) && $sessioninfo.privilege.GRN_PC_DIV && $smarty.request.action eq 'verify'}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>
					{$grr.type} Suggested Selling Price 
	{if $div3_id}
		(Confirmed)
		{assign var=confirmed value=1}
	{/if}
				</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="tbl_item" id="tbl_item_5" style="background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
				<thead>
				<tr>
					<th>#</th>
					<th>ARMS Code</th>
					<th>Artno</th>
					<th>Mcode</th>
					<th width=80%>Description</th>
					<th>PO No</th>
					<th>PO<br />Date</th>
					<th>Current<br>Selling<br>Price</th>
					<th>Suggested<br>Selling<br>Price</th>
					{if $config.grn_check_selling_price}
						<th>Remark</th>
					{/if}
				</tr>
				</thead>
			
				<tbody id="grn_items_5" class="multiple_add_container_5">
					{assign var=doc_type value=5}
					{assign var=have_items value=0}
					{foreach from=$form.items item=item name=fitem}
						{if $item.item_group eq '1' || $item.item_group eq '2'}
							{if $form.branch_is_under_gst && $item.inclusive_tax eq 'yes'}
								{assign var=selling_price value=$item.gst_selling_price}
							{else}
								{assign var=selling_price value=$item.selling_price}
							{/if}
							
							{if $config.grn_check_selling_price && $item.po_item_id ne '' && $selling_price ne ''}
								{if $config.grn_check_selling_price eq "LOWER" && $selling_price|round2 >= $item.curr_selling_price|round2}
								{elseif $config.grn_check_selling_price eq "HIGHER" && $selling_price|round2 <= $item.curr_selling_price|round2}
								{elseif $config.grn_check_selling_price eq "DIFF" && $selling_price|round2 eq $item.curr_selling_price|round2}
								{else}
									<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="titem_5" id="5_titem{$item.id}">
									{include file=goods_receiving_note2.new.list.tpl}
									</tr>
									{assign var=have_items value=1}
								{/if}
							{/if}
						{/if}
					{/foreach}
					{if !$have_items && $smarty.request.action ne 'edit'}
						<tr align="center" id="no_data_{$doc_type}">
							<td colspan="8">No data</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
	</div>
</div>
{/if}

{if $form.grr_items}
	<div id="grr_items_list">
		{include file="goods_receiving_note2.grr.new.list.tpl"}
	</div>
{/if}

{if $sessioninfo.privilege.GRN_ACCV_DIV && !$smarty.request.action}
	<div id="tblist">
		{include file="goods_receiving_note2.view.list.tpl" is_correction=1}
	</div>
	
	{if ($form.non_sku_items || $have_grn_returned_items) && $config.use_grn_future}
		{assign var=ttl_pcs value=0}
		{assign var=ttl_nsi_gross_amt value=0}
		{assign var=ttl_nsi_gst_amt value=0}
		{assign var=ttl_nsi_amt value=0}
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<div class="content-title mb-0 my-auto ml-4 text-primary">
						<h4>Returned Item(s)</h4>
						
					</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>

		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table width=100% cellpadding=2 cellspacing=1 border=0 >
						<thead>
						<tr height=32 bgcolor="#ffee99" class="small">
							<th>#</th>
							<th width="20%">Code</th>
							<th width="60%">Description</th>
							<th>Cost Price</th>
							{if $form.is_under_gst}
								<th>GST Code</th>
							{/if}
							<th>Rcv Qty<br />(Pcs)</th>
							<th>Amount</th>
							{if $form.is_under_gst}
								<th>GST</th>
								<th>Amount<br />Include GST</th>
							{/if}
						</tr>
						</thead>
					
						<tbody id="tbditems">
							{if $form.non_sku_items}
								{foreach from=$form.non_sku_items key=sku_code item=item name=fitem}
									{assign var=n value=$smarty.foreach.fitem.iteration-1}
									{if $form.non_sku_items.code.$n}
										<!--{$ri_count++}-->
										{assign var=ttl_pcs value=$ttl_pcs+$form.non_sku_items.qty.$n}
										{assign var=row_gross_amt value=$form.non_sku_items.qty.$n*$form.non_sku_items.cost.$n}
										{assign var=row_gross_amt value=$row_gross_amt|round2}
										{assign var=ttl_nsi_gross_amt value=$ttl_nsi_gross_amt+$row_gross_amt}
										<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
											<td nowrap width="2%" align="right">{$ri_count}.</td>
											<td>{$form.non_sku_items.code.$n}</td>
											<td>{$form.non_sku_items.description.$n}</td>
											<td align="right">{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points:".":""}</td>
											{if $form.is_under_gst}
												<td nowrap>{$form.non_sku_items.gst_code.$n} ({$form.non_sku_items.gst_rate.$n|default:'0'}%)</td>
											{/if}
											<td class="r" width="5%">{$form.non_sku_items.qty.$n|default:0}</td>
											<td class="r" width="5%">{$row_gross_amt|round2}</td>
											{if $form.is_under_gst}
												{assign var=row_gst_rate value=$form.non_sku_items.gst_rate.$n}
												{assign var=row_gst_amt value=$row_gross_amt*$row_gst_rate/100}
												{assign var=row_gst_amt value=$row_gst_amt|round:2}
												{assign var=row_amt value=$row_gross_amt+$row_gst_amt}
												{assign var=ttl_nsi_gst_amt value=$ttl_nsi_gst_amt+$row_gst_amt}
												{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$row_amt}
												
												<td class="r">{$row_gst_amt|number_format:2}</td>
												<td class="r">{$row_amt|number_format:2}</td>
											{/if}
										</tr>
									{/if}
								{/foreach}
							{/if}
							{if $have_grn_returned_items}
								{foreach from=$form.items item=item name=i key=iid}
									{if $item.item_check}
										<!--{$ri_count++}-->
										{assign var=row_qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
										{assign var=ttl_pcs value=$ttl_pcs+$row_qty}
										{assign var=row_gross_amt value=`$item.cost*$row_qty/$item.uom_fraction`}
										{assign var=row_gross_amt value=$row_gross_amt|round2}
										{assign var=ttl_nsi_gross_amt value=$ttl_nsi_gross_amt+$row_gross_amt|round2}
										<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
											<td nowrap width="2%" align="right">{$ri_count}.</td>
											<td>{$item.sku_item_code}</td>
											<td>{$item.description}</td>
											<td align="right">{$item.cost|number_format:$config.global_cost_decimal_points:".":""}</td>
											{if $form.is_under_gst}
												<td nowrap>{$item.gst_code} ({$item.gst_rate|default:'0'}%)</td>
											{/if}
											<td class="r" width="5%">{$row_qty|qty_nf}</td>
											<td class="r" width="5%">{$row_gross_amt|round2}</td>
											{if $form.is_under_gst}
												{assign var=row_gst_rate value=$item.gst_rate}
												{assign var=row_gst_amt value=$row_gross_amt*$row_gst_rate/100}
												{assign var=row_gst_amt value=$row_gst_amt|round:2}
												{assign var=row_amt value=$row_gross_amt+$row_gst_amt}
												{assign var=ttl_nsi_gst_amt value=$ttl_nsi_gst_amt+$row_gst_amt}
												{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$row_amt}
												
												<td class="r">{$row_gst_amt|number_format:2}</td>
												<td class="r">{$row_amt|number_format:2}</td>
											{/if}
										</tr>
									{/if}
								{/foreach}
							{/if}
						</tbody>
					
						<tfoot>
							<tr height="24" bgcolor="#ffee99">
								{assign var=colspan value=4}
								{if $form.is_under_gst}
									{assign var=colspan value=$colspan+1}
								{/if}
								{if $config.link_code_name && $config.docs_show_link_code}
									{assign var=colspan value=$colspan+1}
								{/if}
								<td colspan="{$colspan}" align="right"><b>Total</b></td>
								<td align="right" id="total_qty">{$ttl_pcs|default:0}</td>
								<td align="right" id="total_amt">{$ttl_nsi_gross_amt|default:0}</td>
								{if $form.is_under_gst}	
									<td align="right">{$ttl_nsi_gst_amt|number_format:2|default:0}</td>						
									<td align="right">{$ttl_nsi_amt|number_format:2|default:0}</td>						
								{/if}
							</tr>
			
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	{/if}
{/if}

<p align=center {if $smarty.request.action eq 'grr_edit'}style="display:none;"{/if}>
{if ($smarty.request.action ne 'edit' && (($sessioninfo.privilege.GRN_VAR_DIV && !$div1_id) || ($sessioninfo.privilege.GRN_SIV_DIV && !$div2_id) || ($sessioninfo.privilege.GRN_PC_DIV && !$div3_id) || ($sessioninfo.privilege.GRN_ACCTV_DIV && !$div4_id))) || ($smarty.request.action eq 'edit' && !$form.authorized)}
	<input class="btn btn-success" type=button name="save" value="Save" onclick="show_print_dialog('save')">
	<input class="btn btn-primary" type=button name="confirm" value="Confirm" onclick="do_confirm()">
{/if}

{if $form.authorized eq '1' && (($sessioninfo.privilege.GRN_VAR_DIV && $smarty.request.action eq 'verify' && ($grr.type eq 'PO' || $grr.is_ibt_do)) || ($sessioninfo.privilege.GRN_SIV_DIV && $smarty.request.action eq 'verify' && $grr.type ne 'PO') || ($sessioninfo.privilege.GRN_ACCV_DIV && !$smarty.request.action))}
	<input class="btn btn-danger" type=button name="reject" value="Reject" onclick="do_reject()">
{/if}
{if $form.id<$time_value && $smarty.request.action eq 'edit'}
<input class="btn btn-warning" type=button name="cancel" value="Cancel" onclick="do_cancel()">
{/if}

<input class="btn btn-danger" class="btn btn-danger" type=button name="close" value="Close" onclick="do_close()">
</p>

</form>




<div id="sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000;background:#fff">
</div>

<script>
GRN_MODULE.initialize();
</script>
{include file=footer.tpl}