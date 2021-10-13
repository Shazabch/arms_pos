{*
REVISION HISTORY
==================
5/28/2010 11:52:39 AM Alex
- add function upper_lower_limit()

8/5/2011 11:58:48 AM Andy
- Add total discount and row discount.

8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

3/2/2012 10:27:46 AM Alex
- add scan barcode

3/4/2013 2:03 PM Andy
- Add get receipt list when load the sales order which has been exported to POS.

3/6/2013 4:57 PM Fithri
- when scan bom package item, split item into itemize
- if one of the item delete, all related bom items also delete
- if change one item qty, all other also change

5/14/2013 11:14 AM Andy
- Add selling type for sales order.
- Enhance to when un-tick use promo price, it will load back the normal selling price.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".

7/17/2014 10:46 AM Fithri
- when select debtor, automatically select mprice if the debtor's mprice is set & user cannot change it

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

4/6/2015 4:41 PM Andy
- Remove to store the GST Indicator.

9/14/2015 9.57: AM DingRen
- fix Selling Price "Normal" disabled wrongly

10/15/2015 2:53 PM Justin
- Enhanced to skip compulsory checking for batch code if config not set.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

3/4/2016 11:18 AM Andy
- Fix if user no privilege SHOW_COST will cause javascript error on change UOM.

2/27/2017 10:25 AM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

8/14/2017 3:44 PM Justin
- Enhanced to use DO calculation.

11/2/2017 10:13 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.

11/10/2017 1:54 PM Justin
- Bug fixed on wording "Clause" instead of "Claus".

8/24/2018 4:12 PM Andy
- Enhanced Sales Order to have Debtor Price feature.

9/21/2018 10:40 AM Andy
- Fixed "Use Debtor Price" din't automatically tick when debtor is selected.

8/11/2020 9:08 AM William
- Enhanced to update amount when column amt_need_update is 1.

3/3/2021 16:40 PM Sin Rou
- Hide "Batch Code" and text field in Sales Order "General Information when create new order".
*}

{if !$form.approval_screen}
	{include file='header.tpl'}
{else}
	<hr noshade size=2>
{/if}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


<script type="text/javascript">
{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var disallowed_mprice = '{$disallowed_mprice}';
var sales_order_require_batch_code = '{$config.sales_order_require_batch_code}';

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');
var amt_need_update = int('{$form.amt_need_update}');

{literal}
function init_calendar(){


	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
	});
	
}

curtain_clicked = function(){
 	$('div_search_debtor').hide();
 	$('receipt_details_container').hide();
 	$('div_pc_table').hide();
}

function show_search_debtor(){
	$$('#tbl_debtor_list tr.db_row').each(function(ele){
			$(ele).show();
		});

	curtain(true);
	center_div($('div_search_debtor').show());
	document.f_search_debtor['debtor_desc'].focus();
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
	    var desc = $(ele).getElementsBySelector('.db_desc')[0].innerHTML.toLowerCase();
	    if(desc.indexOf(str)>=0)    $(ele).show();
	    else    $(ele).hide();
	});
}

function choose_this_debtor(ele){
	var debtor_id = $(ele).getElementsBySelector('.db_id')[0].innerHTML;
	document.f_a['debtor_id'].value = debtor_id;
    default_curtain_clicked();
    active_btn(document.f_a['debtor_id']);
    
    var debtor_mprice_type = $(ele).readAttribute('debtor_mprice_type');
    document.f_a['selling_type'].value = debtor_mprice_type;
		
	selling_type_changed();
    
}

var sku_autocomplete = undefined;
function reset_sku_autocomplete(){
	var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined){
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		indicator: 'span_autocomplete_loading',
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
		}});
	}
	$('autocomplete_sku').focus();
}

function active_btn(ele){
	if($('div_sheets').style.display=='')    return false;
	if(ele.value>0){
        $('srefresh').style.display='';
		$('refresh_btn').disabled=false;
	}else{
        $('srefresh').style.display='none';
		$('refresh_btn').disabled=true;
	}
}

function refresh_tables(){
	document.f_a.a.value = "refresh";
	document.f_a.target = "";
	document.f_a.submit();
}

function uom_change(value,id){
	var a = value.split(",");
	var old_fraction = float($('uom_fraction,'+id).value);
	
	// cost
	var inp_cost_price = $('cost_price,'+id);
	//if(inp_cost_price){
		var old_cost = float(inp_cost_price.value)/old_fraction;
		var new_cost = old_cost*float(a[1]);
		inp_cost_price.value=round(new_cost,global_cost_decimal_points);
	//}
	
	// selling
	var old_selling = float($('selling_price,'+id).value)/old_fraction;
	var new_selling= old_selling*float(a[1]);	
    $('selling_price,'+id).value=round(new_selling,2);
    
	$('uom_id,'+id).value=a[0];
	$('uom_fraction,'+id).value=a[1];

	if(($('uom_fraction,'+id).value)=='1'){
		$('ctn,'+id).disable().value = '--';
	}
	else{
	    if($('ctn,'+id).value=='--')    $('ctn,'+id).value = '';
		$('ctn,'+id).enable();
	}
	row_recalc(id);
}

function delete_item(id){

 	var branch_id = document.f_a['branch_id'].value;
 	var order_id = document.f_a['id'].value;
	var bom_ref_num = document.f_a['bom_ref_num['+id+']'].value;
	var id_list = new Array;
	var multiple_delete;
	
	if (bom_ref_num == '' || bom_ref_num == '0') {
		if (!confirm('Remove this SKU from Order?')) return;
		id_list.push(id);
		multiple_delete = false;
	}
	else {
		if (!confirm("This SKU is BOM Package SKU.\nRemoving this item will remove all item(s) in same package.\n\nProceed?")) return;
		var bom_items = $$('input[bom_ref_num="'+bom_ref_num+'"]');
		bom_items.each(function(e){
			id_list.push(e.up('tr').id.split(',')[1]);
		});
		multiple_delete = true;
	}
	var id_list_str = JSON.stringify(id_list);

	ajax_request(phpself,{
		method:'post',
		parameters: 'a=ajax_delete_item&branch_id='+branch_id+'&order_id='+order_id+'&id_list='+id_list_str,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
		    if(m.responseText.trim()=='OK'){
                if (multiple_delete) {
					bom_items.each(function(e){
						e.up('tr').remove();
					});
				}
				else Element.remove('tr_item,'+id);
				recalc_total();
				reset_row_no();
			}else{
                alert(m.responseText);
			}
    	}
	});
}

function reset_row_no(){
    var span = $$('#docs_items span.no');
	for(var i=0;i<span.length;i++)	{
 		$(span[i]).update((i+1)+'.');
	}
}

function add_autocomplete_callback(){   // run before add item
	$('span_autocomplete_loading').update(_loading_).show();
}

function add_autocomplete_extra(){  // run after complete add item
	$('span_autocomplete_loading').hide();
	reset_row_no();
}

function row_recalc(id,recalculate_total){
	if (recalculate_total==undefined) recalculate_total=true;
	var row_qty = 0;
	var row_amt = 0;
	var ctn = 0;
	var pcs = 0;
	var fraction = float($('uom_fraction,'+id).value);
	var selling = float($('selling_price,'+id).value)/fraction;
	var discount_format = $('inp_item_discount-'+id).value.trim();
	var discount_amt = 0;
	var gross_amt = 0;
	var gst_amt = 0;

	if(!$('ctn,'+id).disabled)	ctn = float($('ctn,'+id).value);
	pcs = float(round($('pcs,'+id).value, global_qty_decimal_points));
	row_qty = (ctn*fraction) + pcs;
	row_qty = float(round(row_qty, global_qty_decimal_points));
	row_amt = float(row_qty*selling);
	
	// calculate discount amount
	discount_amt = float(get_discount_amt(row_amt, discount_format));
	if(discount_amt){
		row_amt -= discount_amt
	}

	$('row_qty,'+id).update(row_qty);
	$('row_amount,'+id).update(row_amt.toFixed(2));
	$('inp_item_discount_amount-'+id).value = round(discount_amt, 2);

	gross_amt=row_amt;

	if (is_under_gst) {
		$('span-gross_amt-'+id).innerHTML=round(gross_amt,2);
		var gst_rate = float(document.f_a["gst_rate["+id+"]"].value);
		gst_amt = float(gross_amt*gst_rate/100);

		row_amt = float(round(gross_amt+gst_amt, 2));
		var gross_amt_rounded = float(round(gross_amt, 2));
		gst_amt = float(round(row_amt - gross_amt_rounded, 2));
		//row_amt = gross_amt+gst_amt;

		$('span-gst_amt-'+id).update(round(gst_amt, 2));
		$('row_amount,'+id).update(row_amt.toFixed(2));

		$('inp_item_discount_amount2-'+id).value = 0;
	}

	document.f_a["line_gross_amt["+id+"]"].value = round(gross_amt, 2);
	document.f_a["line_gst_amt["+id+"]"].value = gst_amt;
	document.f_a["line_amt["+id+"]"].value = round(row_amt,2);
	
	// recalculate total
	recalc_total();
}

function recalc_total(){
	var total_ctn = 0;
	var total_pcs = 0;
	var total_amt = 0;
	var total_qty = 0;
	var sheet_discount = document.f_a['sheet_discount'].value.trim();
	var sheet_discount_amt = 0;
	var tmp_discount_amt = 0;
	var show_sub_total = false;

	var sheet_disc1 = float(sheet_discount.split("+")[0]);
	var sheet_disc2 = 0;
	if(sheet_discount.split("+").length>1)	sheet_disc2 = float(sheet_discount.split("+")[1]);
				
	// ctn
	var inp_ctn = $$('#docs_items input.ctn');
	for(var i=0; i<inp_ctn.length; i++){
		if(inp_ctn[i].value=='--')  continue;
		total_ctn += float(inp_ctn[i].value);
	}
	// pcs
	var inp_pcs = $$('#docs_items input.pcs');
	for(var i=0; i<inp_pcs.length; i++){
		total_pcs += float(inp_pcs[i].value);
	}
	// amt
	var span_amt = $$('#docs_items span.row_amt');
	for(var i=0; i<span_amt.length; i++){
		total_amt += float(span_amt[i].innerHTML);
	}
	// qty
	var span_qty = $$('#docs_items span.row_qty');
	for(var i=0; i<span_qty.length; i++){
		total_qty += float(span_qty[i].innerHTML);
	}
	
	// calculate sheet discount
	sheet_discount_amt = float(round(get_discount_amt(total_amt, sheet_discount),2));
	
	// add sub total first
	$('td_sub_total_amount').update(total_amt.toFixed(2));
	
	if(sheet_discount_amt){
		total_amt -= sheet_discount_amt;
		show_sub_total = true;
	}
	
	document.f_a['total_ctn'].value = total_ctn;
	document.f_a['total_pcs'].value = total_pcs;
	document.f_a['total_amount'].value = round(total_amt,2);
	document.f_a['total_qty'].value = total_qty;
	document.f_a['sheet_discount_amount'].value = sheet_discount_amt;

	$('span_total_ctn').update(total_ctn);
	$('span_total_pcs').update(total_pcs);
	$('th_total_amt').update(total_amt.toFixed(2));
	$('td_sheet_discount_amount').update((sheet_discount_amt*-1).toFixed(2));
	
	if (is_under_gst) {
		show_sub_total = false;

		total_amt=$('td_sub_total_amount').innerHTML;
		var total_gross_amt=0;
		// amt
		var span_amt = $$('#docs_items span.row_gross_amt');
		for(var i=0; i<span_amt.length; i++){
			total_gross_amt += float(span_amt[i].innerHTML);
		}

		var total_gst_amt=0;
		var span_amt = $$('#docs_items span.row_gst_amt');
		for(var i=0; i<span_amt.length; i++){
			total_gst_amt += float(span_amt[i].innerHTML);
		}

		// add sub total first
		document.f_a['total_gross_amt'].value = round(total_gross_amt,2);
		$('td_sub_total_gross_amount').update(total_gross_amt.toFixed(2));

		// calculate sheet discount
		sheet_discount_gross_amt = float(round(get_discount_amt(total_gross_amt, sheet_discount),2));

		if(sheet_discount_gross_amt){
			var p = (sheet_discount_gross_amt * 100) / total_gross_amt;

			total_gross_amt -= sheet_discount_gross_amt;

			sheet_discount_amt = total_amt * p /100;
			total_amt -= sheet_discount_amt;

			document.f_a['total_gross_amt'].value = round(total_gross_amt,2);
			document.f_a['total_amount'].value = round(total_amt,2);
			document.f_a['sheet_discount_amount'].value = round(sheet_discount_amt,2);

			$('th_total_amt').update(total_amt.toFixed(2));
			$('td_sheet_discount_amount').update((float(round(sheet_discount_amt*-1,2))).toFixed(2));

			show_sub_total = true;
		}

		$('td_sub_total_gst_amount').update(total_gst_amt.toFixed(2));
		$('td_sheet_discount_gst_amount').update((float(round(sheet_discount_gross_amt-sheet_discount_amt,2))).toFixed(2));
		$('td_total_gst_amount').update((float(round(total_amt-total_gross_amt,2))).toFixed(2));
		$('th_total_gross_amt').update(total_gross_amt.toFixed(2));
		$('td_sheet_discount_gross_amount').update((float(round(sheet_discount_gross_amt*-1,2))).toFixed(2));

		// get all item row
		var tr_item_list = $$('#docs_items tr.tr_item');
		var item_len = tr_item_list.length;
		var sub_total_gross_amt = 0;
		var sub_total_line_amt = 0;

		// loop for each row to calculate gross amt2, gst amt2, line total amt2
		for(var i=0,len=tr_item_list.length; i<len; i++){
			var item_id = tr_item_list[i].id.split(",")[1];

			var gst_rate = float(document.f_a["gst_rate["+item_id+"]"].value);
			var line_gross_amt = float(document.f_a["line_gross_amt["+item_id+"]"].value);
			var line_gst_amt = float(document.f_a["line_gst_amt["+item_id+"]"].value);
			var line_amt = float(document.f_a["line_amt["+item_id+"]"].value);

			/*var line_gross_amt2 = line_gross_amt;
			var line_gst_amt2 = line_gst_amt;
			var line_amt2 = line_amt;
			var item_discount_amount2 = 0;

			if (p!= undefined) {
				line_amt2 = line_amt * ((100 - p) / 100);
				line_gross_amt2 = line_amt2 / ((100 + gst_rate) / 100);
				line_gst_amt2 = line_amt2 - line_gross_amt2;

				item_discount_amount2=round(line_amt - line_amt2,4);
			}*/
			sub_total_gross_amt += float(round(line_gross_amt,2));
			sub_total_line_amt += float(round(line_amt,2));
		}
		
		// sheet discount
		var discount_format = document.f_a['sheet_discount'].value.trim();
		discount_format = validate_discount_format(discount_format);
		
		// calculate sheet discount
		var discount_amt = float(round(get_discount_amt(sub_total_line_amt, discount_format),2));
		var gross_discount_amt = 0;
		var gst_discount = 0;
		
		if(discount_amt != undefined){
			// find the discount percent
			sheet_discount_per = discount_amt / sub_total_line_amt;
			
			gross_discount_amt = round(sub_total_gross_amt * sheet_discount_per ,2);
			
			if(is_under_gst){
				gst_discount = float(round(discount_amt-gross_discount_amt,2));
			}
			
			var remaining_gross_discount_amt = gross_discount_amt;
			var remaining_gst_discount = gst_discount;
			var remaining_discount_amt = discount_amt;
			
			// update item amt 2
			for(var i=0; i<tr_item_list.length; i++){
				var item_id = tr_item_list[i].id.split(",")[1];
				
				var line_gross_amt = float(document.f_a["line_gross_amt["+item_id+"]"].value);
				var line_gst_amt = float(document.f_a["line_gst_amt["+item_id+"]"].value);
				var line_amt = float(document.f_a["line_amt["+item_id+"]"].value);
			
				var line_gross_amt2 = float(round(line_gross_amt*(1-sheet_discount_per),2));
				var line_amt2 = float(round(line_amt*(1-sheet_discount_per),2));
				
				var line_gross_amt2_rounded = float(round(line_gross_amt2, 2));
				var line_amt2_rounded = float(round(line_amt2, 2));
				
				var line_gst_amt2 = float(round(line_amt2_rounded-line_gross_amt2_rounded,2));
				var item_discount_amount2 = float(round(line_amt - line_amt2_rounded,2));
				
				remaining_gross_discount_amt = float(round(remaining_gross_discount_amt - (line_gross_amt - line_gross_amt2), 2));
				remaining_gst_discount = float(round(remaining_gst_discount - (line_gst_amt - line_gst_amt2), 2));
				remaining_discount_amt = float(round(remaining_discount_amt - (item_discount_amount2), 2));
				
				if(i == item_len-1){
					if(remaining_gross_discount_amt != 0){
						line_gross_amt2 -= remaining_gross_discount_amt;
						remaining_gross_discount_amt = 0;
					}
					if(remaining_gst_discount != 0){
						line_gst_amt2 -= remaining_gst_discount;
						remaining_gst_discount = 0;
					}
					if(remaining_discount_amt != 0){
						line_amt2 -= remaining_discount_amt;
						item_discount_amount2 += remaining_discount_amt;
						remaining_discount_amt = 0;
					}
				}
				
				document.f_a["line_gross_amt2["+item_id+"]"].value = round(line_gross_amt2,2);
				document.f_a["line_gst_amt2["+item_id+"]"].value = round(line_gst_amt2,2);
				document.f_a["line_amt2["+item_id+"]"].value = round(line_amt2,2);
				
				document.f_a["item_discount_amount2["+item_id+"]"].value = round(item_discount_amount2,2);
			}
		}

		document.f_a['total_gst_amt'].value = round((total_amt-total_gross_amt),2);
		document.f_a['sheet_gst_discount'].value = round((sheet_discount_amt-sheet_discount_gross_amt),2);
	}

	if(show_sub_total){
		$('tr_sub_total').show();
		$('tr_sheet_discount').show();
	}else{
		$('tr_sub_total').hide();
		$('tr_sheet_discount').hide();
	}
}

function check_save(){
	if(sales_order_require_batch_code && document.f_a['batch_code'].value.trim()==''){
		alert('Please enter batch code.');
		return false;
	}
	
	if(document.f_a['debtor_id'].value<=0){
        alert('Please select a debtor.');
		return false;
	}
	
	var span_no = $$('#docs_items span.no');
	if(span_no.length<=0){
        alert('Not item found in the list.');
		return false;
	}
	return true;
}

function do_save(){
	if (check_login()) {
        document.f_a.a.value='save';
		document.f_a.target = "";
		if(check_save()){
			document.f_a.submit();
		}
    }
}

function do_confirm(){
	if (check_login()) {
		if(check_save()){
			if (confirm('Finalise Sales Order and submit for approval?')){
				document.f_a.a.value = "confirm";
				document.f_a.target = "";
				document.f_a.submit();
			}
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

function do_delete(){
	if (check_login()) {
		document.f_a.reason.value = '';
		var p = prompt('Enter reason to Delete :');
		if (p.trim()=='' || p==null) return;
		document.f_a.reason.value = p;
		if (confirm('Delete this Order?')){
			document.f_a.a.value = "delete";
			document.f_a.submit();
		}
	}
}

var batch_code_autocomplete = undefined;

function reset_batch_code_autocomplete()
{
	var param_str = "a=ajax_search_batch_code&";
	batch_code_autocomplete = new Ajax.Autocompleter("inp_batch_code", "div_autocomplete_batch_code_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_batch_code',
	afterUpdateElement: function (obj, li) {
	    s = li.title;
	    $('span_loading_batch_code').hide();
	}});
}

function count_delivered_qty(){
	var all_td = $$('#docs_items td.delivered_qty');
	var qty = 0;
	for(var i=0; i<all_td.length; i++){
		qty += float(all_td[i].innerHTML);
	}
	$('th_total_delivered_qty').update(qty);
}

function add_grn_barcode_item(value)
{
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	add_autocomplete_callback();
	$('grn_barcode').value='';
	// ajax_add_item_row
	ajax_request(phpself, {
		parameters: Form.serialize(document.f_a)+'&a=ajax_add_item_row&grn_barcode='+value,
		onComplete: function(m){
			if (!/^(\s+)*<t/.test(m.responseText))	alert(m.responseText);
			else	new Insertion.Before($('tbl_footer'),m.responseText);
			$('grn_barcode').focus();
			add_autocomplete_extra();
		}
	});
	$('grn_barcode').focus();
}

function toggle_promo_price(ele){
	var c = ele.checked;
	//if(!c)  return false;
	
	// check whether got using mprice
	if(document.f_a['selling_type'].value){
		alert('You are using MPrice, you cant use promotion price.');
		ele.checked = false;
		return false;
	}
	
	if(!$('div_sheets'))    return false;   // no sheet
	var inp_selling = $$('#docs_items input.selling');
	if(inp_selling.length<=0) return false;   // no item
	
	$('span_promo_price_loading').update(_loading_);
	
	reload_item_price();
}

function sheet_discount_changed(){
	var inp = document.f_a['sheet_discount'];
	var discount_format = inp.value.trim();
	
	// check discount pattern
	discount_format = validate_discount_format(discount_format);
	
	// found if discount more than 100%, set it become maximum 100% 
	//if(discount != '' && discount > 100) discount = 100;
	
	inp.value = discount_format;
	$('span_sheet_discount').update(discount_format);
	
	// recalculate total
	recalc_total();
}

function item_discount_changed(item_id, inp){
	var discount_format = inp.value.trim();
	
	// check discount pattern
	discount_format = validate_discount_format(discount_format);
	
	inp.value = discount_format;
	
	// recalculate row
	row_recalc(item_id);
}

function trans_detail(counter_id,cashier_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('receipt_details_container');
	
    $('receipt_details_container').show();
	$('receipt_details').update('Please wait...');
	//return;

	new Ajax.Updater('receipt_details','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			branch_id: branch_id,
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function close_receipt_details(){
	default_curtain_clicked();
}
function qty_changed(inp) {
	
	var bom_ref_num = inp.readAttribute('bom_ref_num');
	var bom_qty_ratio = float(inp.readAttribute('bom_qty_ratio'));
	if (bom_ref_num == '' || bom_ref_num == '0') return;
	var bom_items = $$('input[bom_ref_num="'+bom_ref_num+'"]');
	var calc_by_int = false;
	var tmp_value;
	
	//determine multiply ratio
	var multiply_ratio = inp.value / bom_qty_ratio;
	
	var c;
	var el;
	for (c=0; c<bom_items.length; c++) {
		el = bom_items[c];
		if (el.readAttribute('doc_allow_decimal') == '0') {
			tmp_value = el.readAttribute('bom_qty_ratio') * multiply_ratio;
			if (int(tmp_value) != tmp_value) {
				calc_by_int = true;
				break;
			}
			else {
				el.value = tmp_value;
			}
		}
		else {
			el.value = float(round(el.readAttribute('bom_qty_ratio') * multiply_ratio,global_qty_decimal_points));
		}
		row_recalc(el.id.split(',')[1]);
	}
	
	if (calc_by_int) {
	
		//means qty entered is less than bom qty which is not allowed, so zerolize all bom items
		var zerolize = (inp.value < bom_qty_ratio);
		
		if (!zerolize && (inp.value % bom_qty_ratio) != 0) inp.value = ((int(inp.value/bom_qty_ratio))*bom_qty_ratio);
		
		//determine multiply ratio
		multiply_ratio = inp.value / bom_qty_ratio;
		
		bom_items.each(function(e){
			if (zerolize) e.value = 0;
			else e.value = e.readAttribute('bom_qty_ratio') * multiply_ratio;
			row_recalc(e.id.split(',')[1]);
		});
	}
}

function debtor_changed(sel){
	if(document.f_a['create_by_debtor_id'].value>0){
		if(document.f_a['create_by_debtor_id'].value != sel.value){
			alert('You cannot change debtor, this Sales Order is create by debtor.');
			sel.value = document.f_a['create_by_debtor_id'].value;
		}
		
		return false;
	}

	active_btn(sel);

	// check this debtor got mprice type or not
	if(document.f_a['debtor_id'].value){
		var opt = document.f_a['debtor_id'].options[document.f_a['debtor_id'].selectedIndex];
		var debtor_mprice_type = $(opt).readAttribute('debtor_mprice_type');
		var use_debtor_price = $(opt).readAttribute('use_debtor_price');

		//document.f_a['selling_type'].value = debtor_mprice_type;
		document.f_a['use_debtor_price'].checked = use_debtor_price == 1 ? true : false;
		
		if (debtor_mprice_type) {
			var mprice_exist = false;
			for (var i=0; i<document.f_a['selling_type'].length; i++) {
				if (document.f_a['selling_type'].options[i].value == debtor_mprice_type) {
					document.f_a['selling_type'].options[i].disabled = false;
					document.f_a['selling_type'].options[i].selected = 'selected';
					mprice_exist = true;
				}
				else {
					document.f_a['selling_type'].options[i].disabled = true;
				}
			}
			if (!mprice_exist) {
				//need to create a new <option> and select it
				option = document.createElement('option');
				option.setAttribute('value', debtor_mprice_type);
				option.setAttribute('id', 'opt_'+debtor_mprice_type);
				option.setAttribute('class', 'debtor_mprice');
				option.appendChild(document.createTextNode(debtor_mprice_type));
				$('mprice_type_group').appendChild(option);
				$('opt_'+debtor_mprice_type).disabled = false;
				$('opt_'+debtor_mprice_type).selected = 'selected';
			}
		}
		else {
			//to-do : disable disallowed mprice
			//to-do : disable the created <option>

			for (var i=0; i<document.f_a['selling_type'].length; i++) {
				if (disallowed_mprice!="" && document.f_a['selling_type'].options[i].value == disallowed_mprice) {
					document.f_a['selling_type'].options[i].disabled = true;
				}
				else {
					document.f_a['selling_type'].options[i].disabled = false;
				}
			}
			
			document.getElementsByClassName('debtor_mprice').each(function(el) {
				el.disabled = true;
			});
			
			document.f_a['selling_type'].options[0].selected = 'selected';
		}
		
		// got gst
		if(enable_gst && is_under_gst){
			// check whether this debtor is special exemption
			var special_exemption = int($(opt).readAttribute('special_exemption'));
			if(special_exemption){
				document.f_a['is_special_exemption'].checked = true;
				$('tr_special_excemption_rcr').show();
			}else{
				document.f_a['is_special_exemption'].checked = false;
				$('tr_special_excemption_rcr').hide();
			}
		}
		selling_type_changed();
	}
}

function selling_type_changed(){
	var ele = document.f_a['selling_type'];
	
	if(ele.value){	// got choose mprice
		document.f_a['use_promo_price'].checked = false;	// un-tick use promo
	}
	
	$('span_selling_type_loading').update(_loading_);
		
	reload_item_price();
}

function reload_item_price(){
	var inp_selling = $$('#docs_items input.selling');
	if(inp_selling.length<=0){
		$('span_selling_type_loading').update('');
		$('span_promo_price_loading').update('');
		return false;   // no item
	}
	
	var str_form = $(document.f_a).serialize();
	
	// not allow them to change this while reloading
	document.f_a['use_promo_price'].disabled = true;
	document.f_a['selling_type'].disabled = true;
	document.f_a['use_debtor_price'].disabled = true;
	
	ajax_request(phpself,{
		parameters: str_form+'&a=reload_item_price',
		method: 'post',
		onComplete: function(e){
            $('span_selling_type_loading').update('');
			$('span_promo_price_loading').update('');
			$('span_debtor_price_loading').update('');
	
			document.f_a['use_promo_price'].disabled = false;
			document.f_a['selling_type'].disabled = false;
			document.f_a['use_debtor_price'].disabled = false;
			
			eval("var json = "+e.responseText);
			for(var i=0; i<inp_selling.length; i++){
				var item_id = $(inp_selling[i]).id.split(",")[1];
				var uom_fraction = $('uom_fraction,'+item_id).value;
				inp_selling[i].value = round(float(json[item_id]['selling_price'])*uom_fraction,2);
				row_recalc(item_id);
			}
		}
	});
}

add_parent_child = function(){
	//var sid = int(document.f_a.sku_item_id.value);
	var grp_sku_code = '';
	
	var opts = $('autocomplete_sku_choices').getElementsByTagName('input');
	if(window.autocomplete_multiadd_validation){
		if(!autocomplete_multiadd_validation(opts))	return;
	}
	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked){
			if(document.f_a){
				grp_sku_code += c[1]+",";
			}
		}
	}
	
    if (!grp_sku_code){
		alert('No item selected');
		$('autocomplete_sku').value = '';
	    return false;
    }else{
		grp_sku_code = grp_sku_code.slice(0, -1);
	}
	$('div_pc_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('div_pc_table');
	center_div('div_pc_table');
	curtain(true);

	new Ajax.Updater('div_pc_table',phpself,{
		    method:'post',
		    parameters: 'a=ajax_load_parent_child&sku_code_list='+grp_sku_code,
		    evalScripts: true
	});
}

submit_parent_child = function(ele){
	var is_checked = false;
	$$('#tbl_pc_add input.pc_checkbox').each(function(chx){
		if(chx.checked){
			is_checked = true;
			// mark item as inserted and show errors if user add this item again
			//var sku_item_code = chx.readAttribute('sku_item_code');
			//var sku_desc = chx.readAttribute('sku_description');
			//add_sku_to_list(sku_item_code, sku_desc);
		}
	});

	if(!is_checked){
		alert("Please tick a item to add.");
		return false;
	}
	
	ele.disabled = true;
	ele.value = 'Processing...'
	
	var parms = Form.serialize(document.f_pc)+"&"+Form.serialize(document.f_a)+"&a=ajax_parent_child_add";
	
    ajax_request(phpself,{
        method: 'post',
		parameters: parms,
		onSuccess: function (m){
			if(!/^(\s+)*<t/.test(m.responseText)) alert(m.responseText);
			else new Insertion.Before($('tbl_footer'),m.responseText);
		},
		onComplete: function (m) {
            reset_row_no();
		},
	});
	default_curtain_clicked();
	clear_autocomplete();
}

// function when do date changed
function on_date_changed(){
	// get the object
	var inp = document.f_a['order_date'];
	// check max/min limit
	upper_lower_limit(inp);
	// check gst
	if(enable_gst)	check_gst_date_changed();
}

// function when do date is changed
function check_gst_date_changed(){
	var allow_gst = false;

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
				var do_date = document.f_a["order_date"].value.trim();

				if(do_date){
					// check Date
					if(strtotime(do_date) >= strtotime(global_gst_start_date) && strtotime(do_date) >= strtotime(branch_gst_start_date)){
						allow_gst = true;
					}
				}
			}
		}
	}

	if(allow_gst){
		// date have gst
		if(!is_under_gst)	need_refresh_sheet();
	}else{
		// date no gst
		if(is_under_gst)	need_refresh_sheet();
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

// function to check all gst
function check_all_item_gst(){
	// check again gst price for those default is not selected
	if(is_under_gst){
		var all_tr_item_row = $$('#docs_items tr.tr_item');
		for(var i=0; i<all_tr_item_row.length; i++){
			var item_id = $(all_tr_item_row[i]).id.split(',')[1];
			if(int(document.f_a["gst_id["+item_id+"]"].value)<=0){
				update_selected_gst(item_id);
				row_recalc(item_id,false);
			}
		}
	}
}

function update_all_item_amt(){
	var all_tr_item_row = $$('#docs_items tr.tr_item');
	for(var i=0; i<all_tr_item_row.length; i++){
		var item_id = $(all_tr_item_row[i]).id.split(',')[1];
		row_recalc(item_id,false);
	}
}

function need_refresh_sheet(){
	$('srefresh').style.display='';
	$('refresh_btn').disabled=false;
	$('refresh_btn').show();
	$('div_sheets').hide();
	$('p_submit_btn').hide();
}

function toggle_debtor_price(ele){
	var c = ele.checked;
	//if(!c)  return false;
	
	// check whether got using mprice
	if(!$('div_sheets'))    return false;   // no sheet
	var inp_selling = $$('#docs_items input.selling');
	if(inp_selling.length<=0) return false;   // no item
	
	$('span_debtor_price_loading').update(_loading_);
	
	reload_item_price();
}

{/literal}
</script>

<div id="div_pc_table" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff; z-index:20000;">
</div>

<!-- special div -->
<div id="div_search_debtor" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
	<div id="div_search_debtor_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Debtor  Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_search_debtor_content" style="padding:2px;">
	    <form name="f_search_debtor" onSubmit="filter_debtor_desc();return false;">
	        <b>Filter by Description:</b>
	        <input type="text" size="30" name="debtor_desc" />
	        <input type="submit" value="Refresh" />
	    </form>
		<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
		<table width="100%" id="tbl_debtor_list">
			<tr style="background:#ffc;" id="tr_header_debtor_list">
			    <th width="30">&nbsp;</th>
			    <th width="80">Code</th>
			    <th>Description</th>
			    <th>Address</th>
			</tr>
			<tbody style="background:#fff;">
			{foreach from=$debtor key=id item=r name=f}
			    <tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="choose_this_debtor(this);" debtor_mprice_type="{$r.debtor_mprice_type}">
			        <td>{$smarty.foreach.f.iteration}.
			            <span class="db_id" style="display:none;">{$r.id}</span>
					</td>
			        <td class="db_code">{$r.code}</td>
			        <td>{$r.description}
			            <span class="db_desc" style="display:none;">{$r.description}</span>
					</td>
			        <td>{$r.address|truncate:30:'...'}
			            <span class="db_address" style="display:none;">{$r.address}</span>
					</td>
			    </tr>
			{/foreach}
			</tbody>
		</table>
		</div>
		<p align="center">
			<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
		</p>

	</div>
</div>

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
<input type=hidden name=order_date value="{$form.order_date}">
</form>

<div id="receipt_details_container" style="position:absolute;background-color:#fff;border:2px solid black;padding:5px;height:400px;width:600px;z-index:10002;display:none;">
	<span style="float:right;padding:2px;">
		<img src="/ui/closewin.png" align="absmiddle" onClick="close_receipt_details();" class="clickable" />
	</span>
	<div id="receipt_details"></div>
</div>

<!-- end of special div -->
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				{if !$form.approved}
<h4>Sales Order {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}</h4>
{else}
<h4>Sales Order {if $form.order_no}({$form.order_no}){else}{if $form.id<$time_value}(ID#{$form.id}){/if}{/if}</h4>
{/if}

<h5>Status:
{if $form.delivered}
	Delivered
{elseif $form.approved}
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
	Draft Order
{/if}
</h5>
</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{include file=approval_history.tpl}

{if $form.approval_screen}
<form name="f_b" method=post>
<input type=hidden name=branch_id value="{$form.branch_id}" />
<input type=hidden name=id value="{$form.id}" id="inp_do_id" />
<input type=hidden name=comment value="">
<input type=hidden name=a value="approve">
<input type=hidden name=approvals value={$form.approvals}>
<input type=hidden name=approval_history_id value={$form.approval_history_id}>
{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}
</form>
{/if}

{if $do_list}
<div class="stdframe" style="background:#fff;margin:1em 0;">
    <h4>DO Informations</h4>
    {foreach from=$do_list item=do name=f}
        <a href="do.php?a=open&id={$do.id}&branch_id={$do.branch_id}" target="_blank">
        {if $do.do_no}{$do.do_no}
        {else}
            {$do.report_prefix}{$do.id|string_format:'%05d'}
        {/if}
        </a>

        {if !$smarty.foreach.f.last}, {/if}
    {/foreach}
</div>
{/if}

{if $form.receipt_details}
<div class="stdframe" style="background:#fff;margin:1em 0;">
	<h4>POS Informations</h4>
    {foreach from=$form.receipt_details item=rd name=f}
        <a href="javascript:void(0);" onclick="trans_detail('{$rd.counter_id}','{$rd.cashier_id}','{$rd.date}','{$rd.pos_id}','{$rd.branch_id}');">{receipt_no_prefix_format branch_id=$rd.branch_id counter_id=$rd.counter_id receipt_no=$rd.receipt_no}</a>

        {if !$smarty.foreach.f.last}, {/if}
    {/foreach}
</div>
{/if}

<form name="f_a" method=post ENCTYPE="multipart/form-data">
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<h4>General Information</h4>
			
			{if $errm.top}
			<div id=err><div class=errmsg><ul>
			{foreach from=$errm.top item=e}
			<div class="alert alert-danger rounded ">
				<li> {$e} </li>
			</div>
			{/foreach}
			</ul></div></div>
			{/if}
			
			<input type="hidden" name="a" value="save" />
			<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
			<input type="hidden" name="id" value="{$form.id}" />
			<input type="hidden" name="order_no" value="{$form.order_no}" />
			<input type="hidden" name="total_ctn" value="{$form.total_ctn}" />
			<input type="hidden" name="total_pcs" value="{$form.total_pcs}" />
			<input type="hidden" name="total_amount" value="{$form.total_amount}" />
			<input type="hidden" name="total_qty" value="{$form.total_qty}" />
			<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
			<input type="hidden" name="reason" />
			<input type="hidden" name="total_gross_amt" value="{$form.total_gross_amt}" />
			<input type="hidden" name="sheet_discount_amount" value="{$form.sheet_discount_amount}" />
			<input type="hidden" name="sheet_gst_discount" value="{$form.sheet_gst_discount}" />
			<input type="hidden" name="total_gst_amt" value="{$form.total_gst_amt}" />
			<input type="hidden" name="create_by_debtor_id" value="{$form.create_by_debtor_id}" />
			<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}"/>
			
			<table>
				<div class="row">
					<div class="col-md-6">
						<tr>
							<b class="form-label mt-2">Order Date<span class="text-danger"> *</span> </b>
							<div class="form-inline">
								<input class="form-control" name="order_date" id="added1" size=10 onchange="on_date_changed();"  maxlength=10  value="{$form.order_date|default:$smarty.now|date_format:"%Y-%m-%d"}" />
								{if !$readonly}
									&nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date" />
								{/if}
							</div>
							
						</tr>
					</div>
					
					 
					<div class="col-md-6">
						<tr {if $config.sales_order_hide_batch_code}style="display:none;"{/if}>
							<b class="form-label mt-2">Batch Code</b>
							<input class="form-control" name="batch_code" size=12 value="{$form.batch_code}" id="inp_batch_code" />
								{if $config.sales_order_require_batch_code}
									<img src="ui/rq.gif" align="absmiddle" />
								{/if}
								<span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
								<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
							
						</tr>
					</div>
					
				<div class="col-md-6">
					<tr>
						<b class="form-label mt-2">Customer PO</b>
						<input class="form-control" name="cust_po" size=12 value="{$form.cust_po}" />
					</tr>
				</div>
					
					<!-- Sheet Discount -->
				<div class="col-md-6">
					<tr>
						<b class="form-label mt-2">Discount</b>
						
							<div class="form-inline">
								<input class="form-control" name="sheet_discount" size="12" value="{$form.sheet_discount}" onChange="sheet_discount_changed();" />
							&nbsp;&nbsp;<b>[<a href="javascript:void(show_discount_help());">?</a>]</b>
							</div>

					</tr>
				</div>
					{if $form.id<$time_value}
						<div class="col-md-6">
							
								<b class="form-label mt-2">Owner</b>
								{$form.username}
					
					{/if}
					<div class="col-md-6">
						<tr>
							<b class="form-label mt-2">Remarks</b>
							<textarea class="form-control" rows="2" cols="68" name="remark" onchange="uc(this);">{$form.remark}</textarea>
						
						</tr>
					</div>
					{* Promotion Price *}
					<div class="col-md-6">
						<tr>
						<div class="form-inline">
							<b class="form-label mt-2">Use Promotion Price</b>
							&nbsp; [<a href="javascript:void(alert('Use selling price which included promotion and category discount.'))">?</a>]
							&nbsp;<input type="checkbox" name="use_promo_price" value="1" {if $form.use_promo_price}checked {/if} onChange="toggle_promo_price(this);" />
							<span id="span_promo_price_loading"></span>
						</div>
							
						</tr>
						
					</div>
					{* Debtor Price *}
				<div class="col-md-6">
					<tr>
						<div class="form-inline">
							<b class="form-label mt-2">Use Debtor Price</b> 
							&nbsp;&nbsp;[<a href="javascript:void(alert('{$LANG.SO_DEBTOR_PRICE_NOTIFICATION|escape:javascript}'))">?</a>]
						
							&nbsp;&nbsp;	<input type="checkbox" name="use_debtor_price" value="1" {if $form.use_debtor_price}checked {/if} onChange="toggle_debtor_price(this);" />
							&nbsp;&nbsp;<span id="span_debtor_price_loading"></span>
						</div>
						
					</tr>
				</div>
					
				<div class="col-md-6">
					<tr>
					<div class="form-inline">
						<b class="form-label mt-2">From</b>
						&nbsp;&nbsp;&nbsp;&nbsp;{if $form.branch_id}
							{$branches[$form.branch_id].code} - {$branches[$form.branch_id].description}
						{else}
							{$branches[$sessioninfo.branch_id].code} - {$branches[$sessioninfo.branch_id].description}
						   {/if}
					</div>
					</tr>
				</div>
					<div class="col-md-6">
						<tr>
							<b class="form-label mt-2">To <span class="text-danger"> *</span></b>
							
								<div class="form-inline">
									<span style="{if $form.create_by_debtor_id}display:none;{/if}">
										<select class="form-control" name="debtor_id" onChange="debtor_changed(this);">
											<option value="">-- Please Select --</option>
											{foreach from=$debtor item=r}
												<option value="{$r.id}" {if $form.debtor_id eq $r.id}selected {/if} debtor_mprice_type="{$r.debtor_mprice_type}" special_exemption="{$r.special_exemption}" use_debtor_price="{$r.use_debtor_price}">{$r.code} - {$r.description}</option>
											{/foreach}
										</select>
									</span>
									{if $form.create_by_debtor_id}
										{$debtor[$form.debtor_id].code} - {$debtor[$form.debtor_id].description}
									{/if}
									{if !$readonly}
										{if $form.create_by_debtor_id}
											<span style="color:blue;">(Debtor cannot be change due to this Sales Order is create by this debtor)</span>
										{else}
										&nbsp; &nbsp;	<img src="/ui/icons/magnifier.png" align="absmiddle" title="Search by Debtor description" class="clickable" onClick="show_search_debtor();" />
										{/if}
									{/if}
									{*<span id="span_debtor_change_loading"></span>*}
								</div>
							
						</tr>
					</div>
					{if $config.enable_gst && $form.is_under_gst}		
						<div class="col-md-6">
							<tr>
								GST Special Exemption [<a href="javascript:void(alert('- This will automatically apply to newly added item, the items already in the document will not be change.\n- This setting cannot be change manually, it follow the debtor special exemption setting.'));">?</a>]
									<input type="checkbox" name="is_special_exemption" value="1" {if $form.is_special_exemption}checked{/if} onClick="return false;" />
							</tr>
						</div>
						<div class="col-md-6">
							<tr id="tr_special_excemption_rcr" {if !$form.is_special_exemption}style="display:none;"{/if}>
								GST Special Exemption Relief Clause Remark
								<textarea name="special_exemption_rcr" cols="50" rows="4" class="required"  title="Special Exemption Relief Clause Remark">{$form.special_exemption_rcr}</textarea>
							</tr>
						</div>
					{/if}
				</div>
					<tr>
						<div class="form-inline">
							<b class="form-label mt-2">Selling Price Indicator</b>
							&nbsp;&nbsp;[<a href="javascript:void(alert('This feature cannot use mprice and promotion price together.'))">?</a>]
						</div>
						
					
							<span style="{if $form.create_by_debtor_id}display:none;{/if}">
								{if $form.debtor_id gt 0}
									{foreach from=$debtor item=r}
										{if $form.debtor_id eq $r.id}
											{assign var='selected_debtor' value=$r}
											{if $selected_debtor.debtor_mprice_type ne ""}
												{assign var='debtor_mprice_type' value=$selected_debtor.debtor_mprice_type}
											{/if}
										{/if}
									{/foreach}
								{/if}
								<select class="form-control" name="selling_type" onChange="selling_type_changed();">
									<option value="" {if $debtor_mprice_type neq ""}disabled{/if}>Normal</option>
									<optgroup id="mprice_type_group" label="MPrice">
										{foreach from=$mprice_type_list item=mprice_type}
											<option value="{$mprice_type}"  {if $debtor_mprice_type neq "" && $debtor_mprice_type neq $mprice_type}disabled{/if}  {if $mprice_type eq $form.selling_type}selected {/if}>{$mprice_type}</option>
										{/foreach}
									</optgroup>				
								</select>
							</span>
							
							{if $form.create_by_debtor_id}
								{$form.selling_type|default:'normal'}
							{/if}
							
							<span id="span_selling_type_loading"></span>
					
					</tr>
				</div>
			</table>
			
			<div id="srefresh" style="display:none; padding-top:10px; padding-left:130px; ">
			<input id="refresh_btn" type="button" onclick="void(refresh_tables())" class="btn btn-success" value="click here to continue">
			</div>
			
			</div>
	</div>
</div>


<div id="div_sheets" style="{if !$form.debtor_id}display:none;{/if}">{include file='sales_order.open.sheet.tpl'}

{if !$readonly}
	<div>
		<div class="card mx-3">
			<div class="card-body">
				{include file='scan_barcode_autocomplete.tpl' need_hr_out_bottom=1}
			</div>
		</div>
		<div class="card mx-3">
			<div class="card-body">
				{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 add_parent_child=1}
			</div>
		</div>
		<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</div>
	<script>reset_sku_autocomplete();</script>
{/if}
</div>
</form>

<p id="p_submit_btn" align="center">
    {if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
		<input type="button" value="Approve" class="btn btn-success" onclick="do_approve()">
		<input type="button" value="Reject" class="btn btn-info" onclick="do_reject()">
		<input type="button" value="Terminate" class="btn btn-danger" onclick="do_cancel()">
	{/if}

	{if !$form.approval_screen}
		{if !$readonly}
			{if (!$form.status || $form.status==2) and $form.branch_id}
			<input name="bsubmit" type="button" value="Save & Close" class="btn btn-warning" onclick="do_save()" />
			{/if}

			{if $form.id<$time_value}
				<input type="button" value="Delete" class="btn btn-danger" onclick="do_delete()" />
			{else}
				<input type="button" value="Close" class="btn btn-info" onclick="document.location='/sales_order.php'" />
			{/if}

			{if (!$form.status || $form.status==2) and $form.branch_id}
			<input type="button" value="Confirm" class="btn btn-success" onclick="do_confirm()" />
			{/if}
		{else}
		    {if $form.approved and ($sessioninfo.level>=$config.doc_reset_level)}
		        <input type="button" value="Reset" class="btn btn-danger" onclick="do_reset();" />
		    {/if}
			<input type="button" value="Close" class="btn btn-info" onclick="document.location='/sales_order.php'" />
		{/if}
	{/if}
</p>
{include file='footer.tpl'}

<script>
{if $readonly}
	Form.disable(document.f_a);
{else}
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
	reset_batch_code_autocomplete();
	init_calendar();
	if(is_under_gst)	check_all_item_gst();
	recalc_total();
	if(amt_need_update)  update_all_item_amt();
{/if}

</script>
