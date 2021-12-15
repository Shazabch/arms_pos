{*
7/5/2011 4:21:48 PM Justin
- Fixed the problem of unable to calculate the proper Adjusted Total.

7/27/2011 11:07:32 AM Justin
- Added Return Ctn column.
- Added Allow Decimal points feature for Account Ctn and Pcs.
- Show all return ctn and pcs even when not in correction sheet.
- Fixed the JS sum up value with decimal points problem.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

9/5/2011 11:18:43 AM Justin
- Fixed the Acc ctn and pcs cannot left empty once keyed in value.
- Added to show all contents (acc info) when it is being approved.

9/8/2011 3:47:43 PM Justin
- Modified the Account amount to become editable.

9/9/2011 10:48:32 AM Justin
- Fixed the account amount fields not to use number format.

11/10/2011 11:21:43 AM Justin
- Fixed the po amount should multiply with  po cost instead of cost from grn items.

2/24/2012 5:21:54 PM Justin
- Added to show output like PO table when received by IBT DO.

4/20/2012 5:40:56 PM Alex
- add packing uom code after description

7/20/2012 10:38:34 AM Justin
- Enhanced to have UOM control by config and packing uom fraction.

8/3/2012 3:04:34 PM Justin
- Enhanced PO amount to use GRN cost instead PO cost.

9/6/2012 1:13 PM Justin
- Enhanced to disable UOM selection while found config "doc_disable_edit_uom".

9/25/2012 4:09 PM Justin
- Enhanced to include new privilege + config in order to edit account correction.

2:55 PM 9/26/2012 Justin
- Changed the privilege name from "GRN_ACC_CORRECTION_EDIT" to "GRN_ACC_CORRECTION_EDIT_PRICE".

10/25/2012 5:28 PM Justin
- Enhanced to show "BOM Package" beside SKU description when found it is from BOM item.

4/25/2013 3:38 PM Justin
- Enhanced to return errors while found user trying to key in negative figure.

3/14/2015 9:42 AM Justin
- Enhanced to have GST feature.
- Enhanced to have DN No and Amount field while under GST mode.

3/16/2015 2:19 PM Justin
- Enhanced reconcile feature to have match amount include GST.

3/25/2015 2:33 PM Justin
- Enhanced to have between nett selling price or GST selling price while doing current vs suggested selling price.

4/3/2015 6:14 PM Justin
- Enhanced to have checking not to insert Account GST info while it is chosen the same as current one.

4/7/2015 11:28 AM Justin
- Enhanced to disable the old D/N section.

4/11/2015 2:48 PM Justin
- Enhanced to have invoice qty under account correction.

4/13/2015 10:26 AM Justin
- Bug fixed on DN amount deduct on GRN balance if not under GST status.
- Enhanced to have GST amount on summary.

4/21/2015 5:20 PM Justin
- Bug fixed on Account GST amount is not tally with receiving GST.

4/29/2015 6:13 PM Justin
- Enhanced to put colors on GRN Amount (After Adjust) and Balance fields.
- Enhanced to put a line between GRN and invoice amount.
- Enhanced to have generate extra D/N row.
- Enhanced to allow user key in Invoice Price by itemise.

5/8/2015 2:32 PM Justin
- Bug fixed on selling price some times will show zero.

5/25/2015 4:38 PM Justin
- Bug fixed on GST from account correction sometimes will calculate wrongly.

6/1/2015 11:30 AM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.

6/25/2015 2:18 PM Justin
- Bug fixed on javascript loading problems.

6/26/2015 2:42 PM Justin
- Bug fixed on rounding amount didn't add up with amount after adjust.

7/16/2015 4:13 PM Joo Chia
- Disable GST code if is approval.
- Check if form is under GST before calculate_all_gst.

7/28/2015 2:29 PM Justin
- Bug fixed on PO amount will calculate wrongly due to it is based on wrong cost price.

9/7/2015 2:27 PM Andy
- Fix grn amount calculation to properly calculate return ctn/pcs.

9/23/2015 11:18 PM DingRen
- when enable use_grn_future_allow_generate_gra do not deduct return ctn/pcs.

10/13/2015 9:37 AM DingRen
- highlight Account Correction price is the amount is different from po cost

11/18/2015 4:28 PM DingRen
- Fix account verification wrong calculation on approval

12/14/2015 3:20 PM Qiu Ying
- GRN add misc cost and discount etc, similar to PO

12/14/2015 5:20 PM Qiu Ying
- Add GST rounding adjustment

12/23/2015 3:58 PM Qiu Ying
- Fix GST rounding adjustment

1/12/2017 4:14 PM Andy
- Enhanced to use branch_is_under_gst to check gst selling price.

3/23/2017 10:17 AM Andy
- Fix displayed wrong PO Discount Amount in Remarks.

4/20/2017 1:48 PM Khausalya
- Enhanced changes from RM to use config setting. 

5/10/2017 10:17 AM Justin
- Bug fixed on "Returned Amount" did not includes GST amount.
- Enhanced to show "Returned GST Amount" next to "Returned Amount" row.

5/15/2017 1:19 PM Justin
- Enhanced to have FOC qty and discount calculation feature for Account Verification.

5/17/2017 10:58 AM Justin
- Bug fixed on FOC and discount calculation issue.
- Bug fixed on using wrong cost price for FOC and discount calculation.
- Bug fixed on curtain is not open up and always narrow to top of the page when click on FOC and discount calculation icon. 

5/19/2017 9:50 AM Justin
- Enhanced to auto copy over the discount and FOC qty to popup dialog when open it.

6/1/2017 10:35 AM Justin
- Bug fixed on FOC qty able to key in negative figures.
- Bug fixed on account cost will not highlight as red when insert as zero.
- Bug fixed on ctn and pcs will not copy over for FOC qty calculation while user never clicks on "OK".

6/2/2017 1:40 PM Justin
- Bug fixed on account cost will always hightlight even though it doesn't have cost changes but ctn and pcs.

8/28/2018 11:22 AM Justin
- Enhanced to bring back the GRN Tax.
- Enhanced to calculate Tax Amount.

9/24/2018 5:52 PM Justin
- Enhanced to always show the Invoice column and Generate D/N feature regardless it is under GST or not.

9/26/2018 10:34 AM Justin
- Bug fixed on alignment issue when GRN contains PO.

10/30/2018 9:14 AM Justin
- Bug fixed on D/N amount sum up bug when Debit Note Record from PO showed out.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.

06/22/2020 03:30 PM Sheila
- Fixed table boxes alignment and width.
- Updated button color.

*}
{literal}
<style>
.pv {
	color:#fff;
	background:#0c0;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

.nv {
	color:#fff;
	background:#e00;
	font-weight:bold;
	font-size:1.3em;
	padding:0 5px;
}

textarea {
	font:11px Arial;
}
</style>
{/literal}

{literal}
<input type="hidden" name="item_id[]" value="0">
{/literal}
<script type="text/javascript">
global_cost_decimal_points = '{$config.global_cost_decimal_points}';
global_qty_decimal_points = '{$config.global_qty_decimal_points}';
is_under_gst = int('{$form.is_under_gst|default:0}');
open_type = '{$smarty.request.a}';
var currency_symbol = '{$config.arms_currency.symbol}';

{literal}
function calc_grn_total(id){
    var the_id = '['+id+']';
	document.f_a.elements['amt'+the_id].value = round2(document.f_a.elements['cost'+the_id].value * (document.f_a.elements['ctn'+the_id].value+document.f_a.elements['pcs'+the_id].value/document.f_a.elements['uomf'+the_id].value));

	diff = (document.f_a.elements['ctn'+the_id].value*document.f_a.elements['uomf'+the_id].value) + document.f_a.elements['pcs'+the_id].value - document.f_a.elements['qty2'+the_id].value;
	diff = float(round(diff, global_qty_decimal_points));

	document.f_a.elements['var'+the_id].value = diff;
	if (diff > 0)
		$('td_var'+the_id).innerHTML = '<span class=pv>+'+diff+'</span>';
    else if(diff < 0)
		$('td_var'+the_id).innerHTML = '<span class=nv>'+diff+'</span>';
	else
	    $('td_var'+the_id).innerHTML = '&nbsp;';

	var total = 0;
	var items = document.f_a.elements["item_id[]"];
	var i, tctn = 0, tpcs = 0, trctn = 0, trpcs = 0;

	for (i=0;i<items.length;i++)
	{
	    if (items[i].value==0) continue;

	    the_id = '['+items[i].value+']';
	    tctn += float(document.f_a.elements['ctn'+the_id].value);
		tctn = float(round(tctn, global_qty_decimal_points));
	    tpcs += float(document.f_a.elements['pcs'+the_id].value);
		tpcs = float(round(tpcs, global_qty_decimal_points));
	    trctn += float(document.f_a.elements['return_ctn'+the_id].value);
		trctn = float(round(trctn, global_qty_decimal_points));
	    trpcs += float(document.f_a.elements['return_pcs'+the_id].value);
		trpcs = float(round(trpcs, global_qty_decimal_points));
		total += float(document.f_a.elements['amt'+the_id].value);
	}
	$('grn_qty').innerHTML = 'Ctn:'+tctn+' Pcs:'+tpcs;
	$('grn_return_qty').innerHTML = 'Ctn:'+trctn+' Pcs:'+trpcs;
	$('grn_amt').innerHTML = round2(total);

	calc_acc_total();
}

//function calc_acc_total()
calc_acc_total = function()
{
	var total = 0;
	var items = document.f_a.elements["item_id[]"];
	var i;
	var qty;
    var return_total = 0;

	for (i=0;i<items.length;i++){
	    if (items[i].value==0) continue;
	    var the_id = '['+items[i].value+']';

	    // if no account adjustment,
		if (document.f_a.elements["acc_ctn"+the_id].value=='' && document.f_a.elements["acc_pcs"+the_id].value=='' && document.f_a.elements["acc_cost"+the_id].value==''){
			var qty = 0;
			var cost = 0;
			cost = document.f_a.elements["cost"+the_id].value;

            //qty = float(document.f_a.elements["ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["pcs"+the_id].value) - float(document.f_a.elements["return_ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) - float(document.f_a.elements["return_pcs"+the_id].value);
            qty = float(document.f_a.elements["ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["pcs"+the_id].value);

            return_total+=float(document.f_a.elements["return_amt"+the_id].value);

            document.f_a.elements["acc_cost"+the_id].parentNode.style.backgroundColor="#ccff99";
        }else{
		    var qty = 0;
		    var cost = 0;
		    if (document.f_a.elements["acc_ctn"+the_id].value=='' && document.f_a.elements["acc_pcs"+the_id].value=='')
		        qty = float(document.f_a.elements["ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["pcs"+the_id].value);
			else
			    qty = float(document.f_a.elements["acc_ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) + float(document.f_a.elements["acc_pcs"+the_id].value);

            if (document.f_a.elements["acc_cost"+the_id].value=='')
		        cost = document.f_a.elements["cost"+the_id].value;
			else
				cost = document.f_a.elements["acc_cost"+the_id].value;

            //qty = float(qty) - float(document.f_a.elements["return_ctn"+the_id].value * document.f_a.elements["uomf"+the_id].value) - float(document.f_a.elements["return_pcs"+the_id].value);
            qty = float(qty);
            
            var po_cost=float(document.f_a.elements["acc_cost"+the_id].getAttribute("data-po_cost"));
            var acc_cost=float(document.f_a.elements["acc_cost"+the_id].value); console.log(acc_cost);
            if (po_cost!=0 && acc_cost>=0 && po_cost!=acc_cost && document.f_a.elements["acc_cost"+the_id].value != "") {
                document.f_a.elements["acc_cost"+the_id].parentNode.style.backgroundColor="red";
            }
            else{
                document.f_a.elements["acc_cost"+the_id].parentNode.style.backgroundColor="#ccff99";
            }
		}

		var current_total = float(cost*qty/document.f_a.elements["uomf"+the_id].value);
		total = float(total) + float(round2(current_total));
		
		if (document.f_a['acc_cost'+the_id].value==''){
			document.f_a['acc_foc_ctn'+the_id].value = 0;
			document.f_a['acc_foc_pcs'+the_id].value = 0;
			document.f_a['acc_foc_amt'+the_id].value = 0;
			document.f_a['acc_disc'+the_id].value = 0;
			document.f_a['acc_disc_amt'+the_id].value = 0;
			$('span_acc_disc_'+items[i].value).update("");
			hidediv('span_acc_disc_'+items[i].value);
			$('span_acc_foc_'+items[i].value).update("");
			hidediv('span_acc_foc_'+items[i].value);
		}
		
		if(is_under_gst == 1) calculate_gst(items[i].value);
	}

    document.f_a.return_total.value=round2(return_total);
    
    document.f_a.acc_adjustment.value = round2(total);
	
	calc_balance();
}

//function update_var2(id)
update_var2 = function(id)
{
	var sstr = '['+id+']';
	
	var inv_qty = 0;
	if(is_under_gst && document.f_a.elements['inv_qty'+sstr].value!=''){
		inv_qty = document.f_a.elements['inv_qty'+sstr].value;		
	}else if(document.f_a.elements['acc_pcs'+sstr].value=='' && document.f_a.elements['acc_ctn'+sstr].value==''){
		$('var2'+sstr).innerHTML = '';
		return;
	}
	
	if(inv_qty){ // found got invoice qty
		var rcv_qty = 0;
		if(document.f_a.elements['acc_pcs'+sstr].value!='' || document.f_a.elements['acc_ctn'+sstr].value!=''){
			rcv_qty = (float(document.f_a.elements['acc_pcs'+sstr].value)+float(document.f_a.elements['acc_ctn'+sstr].value*document.f_a.elements['uomf'+sstr].value));
		}else{
			//rcv_qty = (float(document.f_a.elements['pcs'+sstr].value)+float(document.f_a.elements['ctn'+sstr].value*document.f_a.elements['org_uom'+sstr].value) - float(document.f_a.elements["return_ctn"+sstr].value * document.f_a.elements["uomf"+sstr].value) - float(document.f_a.elements["return_pcs"+sstr].value));
            rcv_qty = (float(document.f_a.elements['pcs'+sstr].value)+float(document.f_a.elements['ctn'+sstr].value*document.f_a.elements['org_uom'+sstr].value));
        }
		var2 = float(rcv_qty - inv_qty);
	}else{ // do as usual
		//var var2 = (float(document.f_a.elements['acc_pcs'+sstr].value)+float(document.f_a.elements['acc_ctn'+sstr].value*document.f_a.elements['uomf'+sstr].value))-(float(document.f_a.elements['pcs'+sstr].value)+float(document.f_a.elements['ctn'+sstr].value*document.f_a.elements['org_uom'+sstr].value) - float(document.f_a.elements["return_ctn"+sstr].value * document.f_a.elements["uomf"+sstr].value) - float(document.f_a.elements["return_pcs"+sstr].value));
        var var2 = (float(document.f_a.elements['acc_pcs'+sstr].value)+float(document.f_a.elements['acc_ctn'+sstr].value*document.f_a.elements['uomf'+sstr].value))-(float(document.f_a.elements['pcs'+sstr].value)+float(document.f_a.elements['ctn'+sstr].value*document.f_a.elements['org_uom'+sstr].value));
    }
	var2 = float(round(var2, global_qty_decimal_points));
	
	if (var2>0)
		$('var2'+sstr).innerHTML = '<span class=pv>+'+var2+'</span>';
	else if (var2<0)
		$('var2'+sstr).innerHTML = '<span class=nv>'+var2+'</span>';
	else
		$('var2'+sstr).innerHTML = '';
}

function uom_change(value,id){

	var a = value.split(",");
	
    old_cost = float($('cost_'+id).value/$('uom_f_'+id).value);
    new_cost = float(old_cost*a[1]);
    
	if(a[1]>1){
		old_pcs=float($('acc_pcs_'+id).value);
		new_pcs=float(old_pcs%a[1]);
		remain=float(old_pcs)-new_pcs;
		ctn=(remain/a[1])+float($('acc_ctn_'+id).value);
		
		if ($('acc_pcs_'+id).value!=''){
			$('acc_pcs_'+id).value=float(round(new_pcs, global_qty_decimal_points));		
		}
		$('acc_ctn_'+id).value=float(round(ctn, global_qty_decimal_points));
	}
	
    $('cost_'+id).value=round(new_cost,global_cost_decimal_points);
	$('uom_f_'+id).value=a[1];
	$('uom_id_'+id).value=a[0];

	if(($('uom_f_'+id).value)=='1'){
		$('acc_ctn_'+id).value='';
		$('acc_ctn_'+id).disabled=true;
	}
	else{
    	$('acc_ctn_'+id).disabled=false;
    	if($('acc_ctn_'+id).value==''){		    		
    		$('acc_ctn_'+id).value='';	
		}
	}

	update_var2(id);

}

function calculate_gst(id, obj){
	var gst_rate = document.f_a['acc_gst_rate['+id+']'].value;
	
	// sum up the amount to get the discount + tax unit order price
	var qty=0;
	var cost=0;
	if(document.f_a['acc_ctn['+id+']'].value=='' && document.f_a['acc_pcs['+id+']'].value==''){
		//qty = float(document.f_a['ctn['+id+']'].value) * float(document.f_a['uomf['+id+']'].value) + float(document.f_a['pcs['+id+']'].value) - float(document.f_a["return_ctn["+id+"]"].value) * float(document.f_a["uomf["+id+"]"].value) - float(document.f_a["return_pcs["+id+"]"].value);
        qty = float(document.f_a['ctn['+id+']'].value) * float(document.f_a['uomf['+id+']'].value) + float(document.f_a['pcs['+id+']'].value);
    }else{
		qty = float(document.f_a['acc_ctn['+id+']'].value) * float(document.f_a['uomf['+id+']'].value) + float(document.f_a['acc_pcs['+id+']'].value);
	}
	qty = float(round(qty, global_qty_decimal_points));

	if(document.f_a['acc_cost['+id+']'].value==''){
		cost_price = round(document.f_a['cost['+id+']'].value, global_cost_decimal_points);
	}else{
		cost_price = round(document.f_a['acc_cost['+id+']'].value, global_cost_decimal_points);
	}
		
	var gst_amt=float(cost_price) * float(gst_rate) / 100;
	//gst_amt = round(gst_amt, global_cost_decimal_points);
	//var curr_total=round2(float(cost_price) * float(qty));
	//alert(gst_amt);
	//var gst_cost_price=float(cost_price)+float(gst_amt);
	
	//gst_cost_price = round2(gst_cost_price);
	
	$('acc_gst_rate_amt'+id).update(round(gst_amt, global_cost_decimal_points));
	//document.f_a['acc_gst['+id+']'].value = round2((float(curr_total) * float(gst_rate) / 100) / float(document.f_a['uomf['+id+']'].value));
	document.f_a['acc_gst['+id+']'].value = round2(float(gst_amt) * float(qty) / float(document.f_a['uomf['+id+']'].value));
	//document.f_a['gst_cost_price['+id+']'].value = round2(gst_cost_price);
	
	var row_amt = round2(float(cost_price) * float(qty) / float(document.f_a['uomf['+id+']'].value));
	var row_gst_amt = round2(float(document.f_a['acc_gst['+id+']'].value) + float(row_amt));
	document.f_a['acc_gst_amt['+id+']'].value = row_gst_amt;
	
	if(open_type == "open") calculate_all_gst();
}

function calculate_all_gst(){
	// do looping for all items
	if($('tbditems')){
		var ttl_gst_amt = 0, ttl_gst = 0;
		
		var items = document.f_a.elements["item_id[]"];
		var i;

		for (i=0;i<items.length;i++){
			if (items[i].value==0) continue;
			var curr_id = '['+items[i].value+']';
			ttl_gst += float(document.f_a['acc_gst'+curr_id].value);
			ttl_gst_amt += float(document.f_a['acc_gst_amt'+curr_id].value);

			//console.log(float(document.f_a['acc_gst_amt'+curr_id].value));
		}

		if(document.f_a['ttl_gst'] != undefined) document.f_a['ttl_gst'].value = round2(ttl_gst);
		//if(document.f_a['ttl_acc_gst'] != undefined) document.f_a['ttl_acc_gst'].value = round2(ttl_gst);
		if(document.f_a['ttl_gst_amt'] != undefined){
			//console.log('ttl_gst_amt = '+ttl_gst_amt);
			document.f_a['ttl_gst_amt'].value = round2(ttl_gst_amt);
			//document.f_a['grn_amount2'].value = round2(float(document.f_a.rounding_amt.value)+float(ttl_gst_amt));
			calc_balance();
		}
		gst_rounding_adjustment();
	}
}

function on_item_gst_changed(sel, item_id){
	document.f_a["acc_gst_id["+item_id+"]"].value = "";
	document.f_a["acc_gst_code["+item_id+"]"].value = "";
	document.f_a["acc_gst_rate["+item_id+"]"].value = "";
	
	if(sel.selectedIndex >= 0){
		// got select
		var opt = sel.options[sel.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		
		document.f_a["acc_gst_id["+item_id+"]"].value = gst_id;
		document.f_a["acc_gst_code["+item_id+"]"].value = gst_code;
		document.f_a["acc_gst_rate["+item_id+"]"].value = gst_rate;
	}
	
	// recalculate row
	calc_acc_total();
}

function gst_rounding_adjustment(){
	var result = round2(float(document.f_a["ttl_gst"]. value) + float(document.f_a["gst_rounding_adj"].value));
	document.f_a["ttl_acc_gst"].value = result;
}

{/literal}
{if $is_correction && $smarty.request.a eq "open"}
	{literal}
	
	function strike_row(){
		var amt_strike = round(document.rcc.amt_strike.value, 2);
		var match_gst_amt = document.rcc.match_gst_amt.checked;

		if(!document.rcc.amt_strike.value){
			alert("You must key in amount to reconcile!");
			document.rcc.amt_strike.focus();
			return false;
		}

		var fields = $('tblist').getElementsByTagName("INPUT");
		var val = '';
		var parent_tr = '';
		//var row_count = 0;
		var rcc_status = '';
		var is_rcc = false;

		$A(fields).each(
			function (r,idx){

				if(r.name.indexOf("rcc_status[")==0){
					rcc_status = r.value;
				}
			
				if (((!match_gst_amt && r.name.indexOf("amt[")==0) || (match_gst_amt && r.name.indexOf("gst_amt[")==0)) && is_rcc == false){
					//row_count++;
					var amount = round(r.value, 2); 
					if(amount == amt_strike && rcc_status == 0){
						val = r.title.split(",");
						parent_tr = $(r).parentNode.parentNode;

						//if(confirm("Found matched SKU Item Code "+val[1]+" in Row ["+row_count+"], reconcile?")){
						$(parent_tr).setStyle({'text-decoration': 'line-through'});
						document.f_a['rcc_status['+val[0]+']'].value = 1;
						is_rcc = true;
					}
				}
				
				if(is_rcc) throw $break;
			}
		);
		
		if(!is_rcc){
			alert("No Match Found.");
		}

		document.rcc.amt_strike.value = '';
		document.rcc.amt_strike.focus();
	}
	
	function clear_all_strikes(){
		if(!confirm("Are you sure want to clear all reconciles?")) return false;
		
		var fields = $('tblist').getElementsByTagName("INPUT");
		var parent_tr = '';

		$A(fields).each(
			function (r,idx){
				if (r.name.indexOf("amt[")==0){
					parent_tr = $(r).parentNode.parentNode;
					$(parent_tr).setStyle({'text-decoration': ''});
				}
				if (r.name.indexOf("rcc_status[")==0){
					r.value = 0;
				}
			}
		);
	}
	
	function discount_help()
	{
		msg = '';
		msg += "Sample input\n";
		msg += "------------\n";
		msg += "10% => discount of 10 percent\n";
		msg += "10  => discount of " + currency_symbol + "10\n";
		msg += "10%+10 => discount 10%, follow by " + currency_symbol + "10\n";
		msg += "10+10% => discount " + currency_symbol + "10, then discount 10%\n";

		alert(msg);
	}

	function cost_help()
	{
		msg = '';
		msg += "Sample input\n";
		msg += "------------\n";
		msg += "10% => add 10 percent\n";
		msg += "10  => add " + currency_symbol + "10\n";
		msg += "10%+10 => add 10%, follow by " + currency_symbol + "10\n";
		msg += "10+10% => add " + currency_symbol + "10, then 10%\n";

		alert(msg);
	}
	
	function toggle_calc_new_cost_dialog(id){
		curtain(true);
		showdiv('div_cost_calc');
		center_div('div_cost_calc');
		
		var cost_price = round(document.f_a['cost['+id+']'].value, global_cost_decimal_points);

		$('adjust_item_id').value = id;
		$('cp_adjust').value = cost_price;
		$('lbl_cp_adjust').update(round(cost_price, 2));
		
		if(document.f_a['uomf['+id+']'].value > 1) $('foc_ctn_adjust').readOnly = false;
		else $('foc_ctn_adjust').readOnly = true;
		
		var doc_allow_decimal = document.f_a['doc_allow_decimal['+id+']'].value;
		
		if(doc_allow_decimal > 0){
			$('foc_ctn_adjust').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); negative_check(this); calc_new_cost(); };
			$('foc_pcs_adjust').onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); negative_check(this); calc_new_cost(); };
		}else{
			$('foc_ctn_adjust').onchange = function(){ mi(this); negative_check(this); calc_new_cost(); };
			$('foc_pcs_adjust').onchange = function(){ mi(this); negative_check(this); calc_new_cost(); };
		}
		
		if(document.f_a['acc_foc_amt['+id+']'].value > 0 || document.f_a['acc_disc_amt['+id+']'].value > 0){
			if(document.f_a['acc_foc_ctn['+id+']'].value > 0 || document.f_a['acc_foc_pcs['+id+']'].value > 0){
				$('foc_ctn_adjust').value = document.f_a['acc_foc_ctn['+id+']'].value;
				$('foc_pcs_adjust').value = document.f_a['acc_foc_pcs['+id+']'].value;
			}
			if(document.f_a['acc_foc_amt['+id+']'].value > 0) $('foc_amt_adjust').value = document.f_a['acc_foc_amt['+id+']'].value;
			$('disc_adjust').value = document.f_a['acc_disc['+id+']'].value;
			$('disc_amt_adjust').value = document.f_a['acc_disc_amt['+id+']'].value;
			$('new_cp_adjust').value = document.f_a['acc_cost['+id+']'].value;
		}else{
			$('foc_ctn_adjust').value = "";
			$('foc_pcs_adjust').value = "";
			$('foc_amt_adjust').value = "";
			$('disc_adjust').value = "";
			$('disc_amt_adjust').value = "";
			$('new_cp_adjust').value = "";
		}
	}
	{/literal}
{/if}
{literal}
</script>
{/literal}

{if !$smarty.request.action && $is_correction}
	<br />
	<input type="button" value="Reconcile" style="font:bold 16px Arial; background-color:#4e387e; color:#fff;" onclick="showdiv('reconcile_menu'); document.rcc.amt_strike.focus();">
	<input type="button" style="font:bold 16px Arial; background-color:#900; color:#fff;" value="Clear All" onclick="clear_all_strikes();">
{/if}

<div class="table-responsive">
	<table width="100%" class="table mb-0 text-md-nowrap  table-hover" >
		<tr class="small">
			<th rowspan="2">&nbsp;</th>
			<th rowspan="2">ARMS</th>
			<th rowspan="2">Artno</th>
			<th rowspan="2">Mcode</th>
			{if $config.link_code_name && $config.docs_show_link_code}
				<th nowrap rowspan="2">{$config.link_code_name}</th>
			{/if}
			<th rowspan="2">Description</th>
			<th rowspan="2">Selling</th>
			<th rowspan="2">Selling UOM</th>
		{if $grr.type eq 'PO' || $grr.is_ibt_do}
			<th rowspan="2" width="30">Order<br>Price</th>
			{if $form.is_under_gst}
				<th rowspan=2 width=30>GST<br />Code</th>
			{/if}
			<th colspan="3" bgcolor="#C2DDFE">{if $grr.type eq 'PO'}Purchased{else}Delivered{/if}</th>
			<th colspan="2" bgcolor="#C2DDFE">FOC</th>
			<th rowspan="2" width="30" bgcolor="#C2DDFE">{$grr.type}<br>Amount</th>
		{else}
			<th rowspan="2" width="30" {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>Cost</th>
			{if $form.is_under_gst}
				<th rowspan=2 width=30>GST<br />Code</th>
			{/if}
		{/if}
			<th colspan="{if ($grr.type eq 'PO'|| $grr.is_ibt_do) && $is_correction}3{else}3{/if}" bgcolor="#FFCCFF">Received</th>
			<th colspan="2" bgcolor="#FFCCFF">Return</th>
			<th rowspan="2" bgcolor="#FFCCFF" width="30">Amount</th>
			{if $form.is_under_gst}
				<th rowspan=2 bgcolor=#FFCCFF width=30>GST</th>
				<th rowspan=2 bgcolor=#FFCCFF width=30>Amount<br />Include<br />GST</th>
			{/if}
		{if $grr.grn_get_weight}<th rowspan="2" bgcolor="#FFCCFF" width="30">Weight<br>(kg)</th>{/if}
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
			<th rowspan="2" bgcolor="#FFCCFF" width="30">GRN/{$grr.type}<br>Variance<br>(Pcs)</th>
		{/if}
		{if $acc_col || $is_correction || $manager_col || $confirm_col}
			{assign var=acc_colspan value=2}
			{if $form.is_under_gst}
				{assign var=acc_colspan value=$acc_colspan+3}
			{/if}
			<th bgcolor="#99cc66" colspan="{$acc_colspan}">Account Correction</th>
			
			<th bgcolor="#ffee99" colspan="2">Invoice</th>
		{/if}
		{if $is_correction || $manager_col}
			<th bgcolor="#99cc66" rowspan="2">ACC/GRN<br>Variance<br>(Pcs)</th>
		{/if}
		</tr>
		<tr class="small" bgcolor="#ffee99">
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
			<th width="30" bgcolor="#C2DDFE">UOM</th>
			<th width="30" bgcolor="#C2DDFE">Ctn</th>
			<th width="30" bgcolor="#C2DDFE">Pcs</th>
			<th width="30" bgcolor="#C2DDFE">Ctn</th>
			<th width="30" bgcolor="#C2DDFE">Pcs</th>
		{/if}
			<th width="30" bgcolor="#FFCCFF">UOM</th>
			<th width="30" bgcolor="#FFCCFF">Ctn</th>
			<th width="30" bgcolor="#FFCCFF">Pcs</th>
			<th width="30" bgcolor="#FFCCFF">Ctn</th>
			<th width="30" bgcolor="#FFCCFF">Pcs</th>
		{if $acc_col || $is_correction || $manager_col || $confirm_col}
			<th bgcolor="#99cc66">Qty</th>
			<th bgcolor="#99cc66">Price</th>
			{if $form.is_under_gst}
				<th bgcolor=#99cc66>GST Code</th>
				<th bgcolor=#99cc66>GST</th>
				<th bgcolor=#99cc66>Amount<br />Include GST</th>
			{/if}
				
			<th bgcolor="#ffee99">Qty</th>
			<th bgcolor="#ffee99">Price</th>
		{/if}
		</tr>
		<tbody id="tbditems">
		{assign var=return_total value=0}
		{assign var=total value=0}
		{assign var=tctn value=0}
		{assign var=tpcs value=0}
		{assign var=trctn value=0}
		{assign var=trpcs value=0}
		{assign var=tpctn value=0}
		{assign var=tppcs value=0}
		
		{foreach from=$form.items item=item name=i key=iid}
		{if !$item.item_check}
		
		{assign var=row value=$row+1}
		{assign var=return_qty value=`$item.return_ctn*$item.uom_fraction+$item.return_pcs`}
		{assign var=return_row_amt value=`$item.cost*$return_qty/$item.uom_fraction`}
		{assign var=return_row_amt value=$return_row_amt|round2}
		{assign var=return_total value=`$return_total+$return_row_amt`}
		{assign var=return_total value=$return_total|round2}
		{assign var=qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
		
		{assign var=row_amt value=`$item.cost*$qty/$item.uom_fraction`}
		{assign var=row_amt value=$row_amt|round2}
		{assign var=total value=`$total+$row_amt`}
		{assign var=total value=$total|round2}
		
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
			{assign var=qty_foc value=`$item.po_foc_ctn*$item.po_uomf+$item.po_foc_pcs`}
			{assign var=qty_order value=`$item.po_order_ctn*$item.po_uomf+$item.po_order_pcs`}
			{assign var=qty2 value=`$qty_foc+$qty_order`}
			{if $qty2 ne '0'}
				{assign var=cost1 value=`$item.po_cost*$qty2/$item.po_uomf`}
			{else}
				{assign var=cost1 value=0}
			{/if}
			{assign var=total2 value=`$total2+$cost1`}
		{/if}
		{assign var=tctn value=`$tctn+$item.ctn`}
		{assign var=tpcs value=`$tpcs+$item.pcs`}
		{assign var=trctn value=`$trctn+$item.return_ctn`}
		{assign var=trpcs value=`$trpcs+$item.return_pcs`}
		{assign var=tpctn value=`$tpctn+$item.po_ctn`}
		{assign var=tppcs value=`$tppcs+$item.po_pcs`}
		{cycle name=c0 values="#EEF6FF,#DEE6FF" assign=rowcolor}
		{cycle name=c1 values="#FFF0FF,#FFDDFF" assign=rowcolor2}
		<tr height=24 {if $item.ctn+$item.pcs<=0}style="background:#fdd;color:#f00"{else}{cycle name="r1" values=",bgcolor=#eeeeee"}{/if} {if $smarty.request.highlight_item_id eq $item.sku_item_id}class="highlight_row"{/if}>
			<td>{$row}. </td>
			<td nowrap>{$item.sku_item_code}</td>
			<td align="center" nowrap>{$item.artno|default:"-"}</td>
			<td align="center" nowrap>{$item.mcode|default:"-"}</td>
			{if $config.link_code_name && $config.docs_show_link_code}
				<td align="center" nowrap>{$item.link_code|default:"-"}</td>
			{/if}
			<td>{$item.description} {if $item.bom_ref_num > 0}<font color="grey">(BOM Package)</font>{/if} {include file=details.uom.tpl uom=$item.packing_uom_code}
			{if ($grr.type eq 'PO'|| $grr.is_ibt_do) && ($item.po_disc_remark || $item.po_tax)}
				<div class="small">(
				{if $item.po_disc_remark}Discount: {$item.po_disc_remark}{/if}
				{if $item.po_tax}{if $item.po_disc_remark} / {/if}Sales Tax: {$item.po_tax}%{/if}
				)</div>
			{/if}
			</td>
			
			{assign var=selling_price value=$item.selling_price}
			{assign var=nett_sp value=$item.selling_price}
			{if $form.branch_is_under_gst && $item.inclusive_tax eq 'yes'}
				{assign var=selling_price value=$item.gst_selling_price}
			{/if}
			
			{assign var=curr_selling_price value=$item.curr_selling_price}
		
			<td align="right" {if $is_correction eq '1' && $item.po_item_id && $selling_price ne $curr_selling_price}class="red_area" title="Different Selling Price between GRN and Change Price"{/if}>
				{$selling_price|number_format:2}
				{if $nett_sp ne $selling_price}
					<br />
					<span style="color: grey;" class="small r">NSP: {$nett_sp|number_format:2}</span>
				{/if}
				{if $is_correction eq '1' && $item.po_item_id && $selling_price ne $curr_selling_price}
					<br />
					<span style="color: blue;" class="small r">S.P: {$curr_selling_price|number_format:2}</span>
				{/if}
			</td>
			<td align="center">EACH</td>	
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
			<td align="right">
				{if $item.po_cost eq 'FOC'}
					FOC
				{else}
					{$item.po_cost|number_format:$config.global_cost_decimal_points}
				{/if}
				<br>
				  {if $item.cost eq 'FOC'}
					(FOC)
				{else}
					({$item.cost|number_format:$config.global_cost_decimal_points})
				{/if}
			</td>
			{if $form.is_under_gst}
				{assign var=gst_id value=$item.gst_id}
				{if $item.po_cost ne 'FOC'}
					{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
					{assign var=gst_cost_price value=$item.cost+$gst_amt}
					{assign var=gst_cost_price value=$gst_cost_price|round:2}
					
					<!-- calculate gst amount for returned qty -->
					{assign var=return_row_gst_amt value=$gst_amt*$return_qty/$item.uom_fraction}
					{assign var=return_row_gst_amt value=$return_row_gst_amt|round2}
					{assign var=return_row_amt value=$return_row_amt+$return_row_gst_amt}
					{assign var=return_row_amt value=$return_row_amt|round2}
					{assign var=return_total value=$return_total+$return_row_gst_amt}
					{assign var=return_total value=$return_total|round2}
					{assign var=return_gst_total value=$return_gst_total+$return_row_gst_amt}
					{assign var=return_gst_total value=$return_gst_total|round2}
				{/if}
				<td align=left nowrap>
					{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
					({$gst_amt|number_format:$config.global_cost_decimal_points})
				</td>
			{/if}
			<td bgcolor="{$rowcolor}">{$item.po_uom}</td>
			<td bgcolor="{$rowcolor}" align="right">{$item.po_order_ctn|qty_nf}</td>
			<td bgcolor="{$rowcolor}" align="right">{$item.po_order_pcs|qty_nf}</td>
			<td bgcolor="{$rowcolor}" align="right">{$item.po_foc_ctn|qty_nf}</td>
			<td bgcolor="{$rowcolor}" align="right">{$item.po_foc_pcs|qty_nf}</td>
			<td bgcolor="{$rowcolor}" align="right">{$cost1|number_format:2}</td>
		{else}
			<td align="right"  {if !$sessioninfo.privilege.SHOW_COST}style="display:none;"{/if}>
				{if $item.cost eq 'FOC'}
					   FOC
				{else}
					{$item.cost|number_format:$config.global_cost_decimal_points}
				{/if}
			</td>
			{if $form.is_under_gst}
				{assign var=gst_id value=$item.gst_id}
				{if $item.cost ne 'FOC'}
					{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
					{assign var=gst_cost_price value=$item.cost+$gst_amt}
					{assign var=gst_cost_price value=$gst_cost_price|round:2}
				{/if}
				<td align=left nowrap>
					{$item.gst_code} ({$item.gst_rate|default:'0'}%)<br />
					({$gst_amt|number_format:$config.global_cost_decimal_points})
				</td>
			{/if}
		{/if}
			<td bgcolor="{$rowcolor2}" align="center">
				{if $is_correction}
					<select name="sel_uom[{$item.id}]" id="sel_uom{$item.id}" onchange="uom_change(this.value,'{$item.id}');calc_acc_total();" {if $cu_id || (!$config.doc_allow_edit_uom and $item.packing_uom_fraction ne 1) || $config.doc_disable_edit_uom}disabled{/if}>
					{section name=i loop=$uom}
					<option value="{$uom[i].id},{$uom[i].fraction}" {if $item.order_uom==$uom[i].code}selected{/if}>{$uom[i].code}
					</option>
					{/section}
					</select>
				{else}
					{$item.order_uom}
				{/if}
			</td>
		{* show reconfirm column *}
		
		<td bgcolor="{$rowcolor2}" align="right">{$item.ctn|qty_nf}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$item.pcs|qty_nf}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$item.return_ctn|qty_nf}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$item.return_pcs|qty_nf}</td>
		<td bgcolor="{$rowcolor2}" align="right">{$row_amt|number_format:2}</td>
		
		{if $form.is_under_gst}
			{assign var=row_gst value=$qty*$gst_amt/$item.uom_fraction}
			{assign var=row_gst value=$row_gst|round2}
			{assign var=ttl_gst value=$ttl_gst+$row_gst}
			<td bgcolor="{$rowcolor2}" align="right">{$row_gst|number_format:2}</td>
		
			{assign var=row_gst_amt value=$row_gst+$row_amt}
			{assign var=ttl_gst_amt value=$ttl_gst_amt+$row_gst_amt}
			<td bgcolor="{$rowcolor2}" align="right">{$row_gst_amt|number_format:2}</td>
		{/if}
		
		{if $grr.grn_get_weight}<td bgcolor="{$rowcolor2}" align="right">{$item.weight|number_format}</td>{/if}
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
			<td id="td_var[{$item.id}]" bgcolor="{$rowcolor2}" align="right" align="center">
			<input type="hidden" name="var[{$item.id}]" value="{$qty-$item.po_qty}">
			{assign var=po_qty value=`$item.po_ctn*$item.po_uomf+$item.po_pcs`}
			
			{if $qty>$po_qty}
			{assign var=tvar value=`$tvar+$qty-$item.po_qty`}
			<span class="pv">+{$qty-$po_qty|qty_nf}</span>
			{elseif $qty<$item.po_qty}
			{assign var=tvar value=`$tvar+$item.po_qty-$qty`}
			<span class="nv">{$qty-$po_qty|qty_nf}</span>
			{else}
			&nbsp;
			{/if}
			</td>
		{/if}
		
		{* show account correction *}
		{if $is_correction || $manager_col}
			<td bgcolor="#ccff99" nowrap>
			<input type="hidden" name="po_item_id[{$item.id}]" value="{$item.po_item_id}">
			<input type="hidden" name="item_id[]" value="{$item.id}">
			<input type="hidden" id="cost_{$item.id}" name="cost[{$item.id}]" value="{$item.cost}">
			<input type="hidden" name="org_uom[{$item.id}]" value="{$item.uom_fraction}">
			<input type="hidden" id="uom_f_{$item.id}" name="uomf[{$item.id}]" value="{$item.uom_fraction}">
			<input type="hidden" id="uom_id_{$item.id}" name="uom_id[{$item.id}]" value="{$item.uom_id}">
			<input type="hidden" name="ctn[{$item.id}]" value="{$item.ctn}">
			<input type="hidden" name="pcs[{$item.id}]" value="{$item.pcs}">
			<input type="hidden" name="return_ctn[{$item.id}]" value="{$item.return_ctn}">
			<input type="hidden" name="return_pcs[{$item.id}]" value="{$item.return_pcs}">
			<input type="hidden" name="rcc_status[{$item.id}]" value="{$item.rcc_status}">
			<input type="hidden" name="return_amt[{$item.id}]" value="{$return_row_amt}" title="{$item.id},{$item.sku_item_code}">
			<input type="hidden" name="amt[{$item.id}]" value="{$row_amt}" title="{$item.id},{$item.sku_item_code}">
			<input type="hidden" name="gst_amt[{$item.id}]" value="{$row_gst_amt}" title="{$item.id},{$item.sku_item_code}">
			<input type="hidden" name="doc_allow_decimal[{$item.id}]" value="{$item.doc_allow_decimal}">
			<input type="hidden" name="acc_foc_ctn[{$item.id}]" value="{$item.acc_foc_ctn}">
			<input type="hidden" name="acc_foc_pcs[{$item.id}]" value="{$item.acc_foc_pcs}">
			<input type="hidden" name="acc_foc_amt[{$item.id}]" value="{$item.acc_foc_amt}">
			<input type="hidden" name="acc_disc[{$item.id}]" value="{$item.acc_disc}">
			<input type="hidden" name="acc_disc_amt[{$item.id}]" value="{$item.acc_disc_amt}">
			
			<table width="100%" style="border: 0 !important; color: red">
				<tr>
					<td align="center">
						Ctn <input style="width:40px" onclick="this.select()" class="r small" id="acc_ctn_{$item.id}" name="acc_ctn[{$item.id}]" {if $item.doc_allow_decimal}size="10"{else}size="1"{/if} onchange="{if $item.doc_allow_decimal}if(this.value) this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);calc_acc_total();update_var2({$item.id});" value="{$item.acc_ctn}" {if $item.uom_fraction<=1}disabled{/if} {if $cu_id}readonly{/if}>
					</td>
					<td align="center">
					Pcs <input style="width:40px" onclick="this.select()" class="r small" id="acc_pcs_{$item.id}" name="acc_pcs[{$item.id}]" {if $item.doc_allow_decimal}size="10"{else}size="1"{/if} onchange="{if $item.doc_allow_decimal}if(this.value) this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);calc_acc_total();uom_change($('sel_uom{$item.id}').value,{$item.id});" value="{$item.acc_pcs}" {if $cu_id}readonly{/if}>
					</td>
				</tr>
			</table>
			</td>
			<td bgcolor="#ccff99" nowrap>
			<input class="r small" data-po_cost="{if $item.po_cost eq 'FOC'}0{else}{$item.cost|number_format:$config.global_cost_decimal_points}{/if}" id="acc_cost_{$item.id}" name="acc_cost[{$item.id}]" size="5" onchange="mf(this, {$config.global_cost_decimal_points});positive_check(this);calc_acc_total();" value="{$item.acc_cost}" {if $cu_id || ($config.grn_require_acc_correction_privilege && !$sessioninfo.privilege.GRN_ACC_CORRECTION_EDIT_PRICE)}readonly{/if}>
			{if !$cu_id}
				<img src="ui/icons/calculator.png" onclick="toggle_calc_new_cost_dialog({$item.id});" title="Calculate New Cost" border="0">
			{/if}
			<span id="span_acc_disc_{$item.id}" class="small" style="font-weight:bold; {if !$item.acc_disc}display:none;{/if}"><br />Disc: {$item.acc_disc}</span>
			<span id="span_acc_foc_{$item.id}" class="small" style="font-weight:bold; {if !$item.acc_foc_ctn && !$item.acc_foc_pcs}display:none;{/if}"><br />FOC Ctn: {$item.acc_foc_ctn}&nbsp;&nbsp;Pcs: {$item.acc_foc_pcs}</span>
			</td>
			
			{if $form.is_under_gst}
				{if $item.acc_gst_id}
					{assign var=acc_gst_id value=$item.acc_gst_id}
					{assign var=acc_gst_code value=$item.acc_gst_code}
					{assign var=acc_gst_rate value=$item.acc_gst_rate}
				{else}
					{assign var=acc_gst_id value=$item.gst_id}
					{assign var=acc_gst_code value=$item.gst_code}
					{assign var=acc_gst_rate value=$item.gst_rate}
				{/if}
				{if $item.acc_ctn ne '' || $item.acc_pcs ne ''}
					{assign var=acc_qty value=`$item.acc_ctn*$item.uom_fraction+$item.acc_pcs`}
				{else}
					{assign var=acc_qty value=`$qty`}
				{/if}
		
				{if $item.acc_cost}
					{assign var=acc_cost value=$item.acc_cost}
					{assign var=acc_row_amt value=`$item.acc_cost*$qty/$item.uom_fraction`}
					{assign var=acc_row_amt value=$acc_row_amt|round2}
				{else}
					{assign var=acc_cost value=$item.cost}
					{assign var=acc_row_amt value=$row_amt}
				{/if}
				
				{assign var=acc_gst_amt value=$acc_cost*$acc_gst_rate/100}
				{assign var=acc_row_gst value=$acc_qty*$acc_gst_amt/$item.uom_fraction}
				{assign var=acc_row_gst value=$acc_row_gst|round2}
				{assign var=acc_row_gst_amt value=$acc_row_gst+$acc_row_amt}
				{assign var=acc_ttl_gst value=$acc_ttl_gst+$acc_row_gst}
				{assign var=acc_ttl_gst_amt value=$acc_ttl_gst_amt+$acc_row_gst_amt}
				<!-- GST code -->
				<td bgcolor="#ccff99">
					<select name="acc_item_gst[{$item.id}]" id="acc_item_gst{$item.id}" item_id="{$item.id}" class="gst_field small" onchange="on_item_gst_changed(this, {$item.id});" {if $cu_id}disabled{/if} >
						{foreach from=$gst_list key=rid item=gst}
							<option value="{$gst.id}" gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" {if $acc_gst_id eq $gst.id and $acc_gst_code eq $gst.code and $acc_gst_rate eq $gst.rate}selected {/if}>{$gst.code} ({$gst.rate}%)</option>
						{/foreach}
					</select>
					<br />
					<font color="blue"><span id="acc_gst_rate_amt{$item.id}" class="small">{$acc_gst_amt|number_format:$config.global_cost_decimal_points}</span></font>
					<input type="hidden" name="acc_gst_id[{$item.id}]" value="{$acc_gst_id}" />
					<input type="hidden" name="acc_gst_code[{$item.id}]" value="{$acc_gst_code}" />
					<input type="hidden" name="acc_gst_rate[{$item.id}]" value="{$acc_gst_rate}" />
					<input type="hidden" name="gst_id[{$item.id}]" value="{$item.gst_id}" />
					<input type="hidden" name="gst_code[{$item.id}]" value="{$item.gst_code}" />
					<input type="hidden" name="gst_rate[{$item.id}]" value="{$item.gst_rate}" />
				</td>
				<!-- GST price -->
				<td bgcolor="#ccff99" align="right">
					{if $item.po_cost eq 'FOC'}
						FOC
					{else}
						<input name="acc_gst[{$item.id}]" size="5" value="{$acc_row_gst|number_format:2:'.':''}" readonly class="r small row_acc_gst">
					{/if}
				</td>
				<td bgcolor="#ccff99" class="r"><input name="acc_gst_amt[{$item.id}]" size="5" value="{$acc_row_gst_amt|number_format:2:".":""}" readonly class="r small row_acc_gst_amt"></td>
			{/if}
			<td bgcolor="#ffee99" align="center" nowrap>
				<input onclick="this.select()" class="r small" id="inv_qty_{$item.id}" name="inv_qty[{$item.id}]" {if $item.doc_allow_decimal}size="10"{else}size="2"{/if} onchange="{if $item.doc_allow_decimal}if(this.value) this.value=float(round(this.value, {$config.global_qty_decimal_points}));{else}mi(this);{/if}positive_check(this);update_var2({$item.id});" value="{$item.inv_qty}" {if $cu_id}readonly{/if}>
			</td>
			<td bgcolor="#ffee99" align="center" nowrap>
				<input onclick="this.select()" class="r small" id="inv_cost_{$item.id}" name="inv_cost[{$item.id}]" size="5" onchange="mf(this, {$config.global_cost_decimal_points});positive_check(this);" value="{$item.inv_cost}" {if $cu_id}readonly{/if}>
			</td>
		{/if}
		
		{if $is_correction || $manager_col}
			<td bgcolor="#ccff99" align="center" id="var2[{$item.id}]">
				&nbsp;
				<script>update_var2('{$item.id}');</script>
			</td>
		{/if}
		</tr>
		{/if}
		{/foreach}
		</tbody>
		
		{assign var=colspan value=7}
		{if $form.is_under_gst}
			{assign var=colspan value=$colspan+1}
		{/if}
		{if !$sessioninfo.privilege.SHOW_COST && $grr.type ne 'PO' && !$grr.is_ibt_do}
		{else}
			{assign var=colspan value=$colspan+1}
		{/if}
		
		{if $config.link_code_name && $config.docs_show_link_code}
			{assign var=colspan value=$colspan+1}
		{/if}
		
		<tr height="24" bgcolor="#ffee99">
		<td colspan="{$colspan}" align="right"><b>Total</b></td>
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}
		<td colspan="5" bgcolor="#C2DDFE" align="right">Ctn:{$tpctn|qty_nf} Pcs:{$tppcs|qty_nf}</td>
		<td bgcolor="#C2DDFE" align="right">{$total2|number_format:2}</td>
		{/if}
		<td bgcolor="#ffccff" colspan="3" align="right"><span id="grn_qty">Ctn:{$tctn|qty_nf} Pcs:{$tpcs|qty_nf} </span></td>
		<td bgcolor="#ffccff" colspan="2" align="right"><span id="grn_return_qty">Ctn:{$trctn|qty_nf} Pcs:{$trpcs|qty_nf} </span></td>
		<td bgcolor="#ffccff" align="right"><span id="grn_amt">{$total|number_format:2}</span></td>
		{if $form.is_under_gst}
			<td bgcolor=#ffccff align=right>{$ttl_gst|number_format:2}</td>
			<td bgcolor=#ffccff align=right>{$ttl_gst_amt|number_format:2}</td>
		{/if}
		{if $grr.grn_get_weight}<td bgcolor="#ffccff" align="right">&nbsp;</td>{/if}
		{if $grr.type eq 'PO'|| $grr.is_ibt_do}<td bgcolor="#ffccff" align="right">&nbsp;</td>{/if}
		{if $is_correction || $manager_col}
		
			<td nowrap bgcolor="#ccff99" colspan="2" align="right">
		
			Adjusted Total: <input style="margin-right: 6px" size="12" name="acc_adjustment" class="r" value="{$form.acc_adjustment|default:$total|string_format:'%.2f'}" readonly></td>
			{if $form.is_under_gst}
				<td bgcolor=#ccff99>&nbsp;</td>
				<td bgcolor=#ccff99><input size="5" name="ttl_gst" class=r value="{$acc_ttl_gst|number_format:2:'.':''}" readonly></td>
				<td bgcolor=#ccff99><input size="5" name="ttl_gst_amt" class=r value="{$acc_ttl_gst_amt|number_format:2:'.':''}" readonly></td>
			{/if}
			<td bgcolor="#ffee99" class="r" nowrap>&nbsp;</td>
			<td bgcolor="#ffee99" class="r" nowrap>&nbsp;</td>
			<td bgcolor="#ccff99">&nbsp;</td>
			
		{/if}
		</tr>
		
		{if $is_correction || $manager_col}
		{literal}
		<script>
		//function calc_balance()
		calc_balance = function()
		{
			if(float(document.f_a.grn_tax.value) > 0){
				var tax_amount = round(float(document.f_a.acc_adjustment.value) * float(document.f_a.grn_tax.value) / 100, 2);
				document.f_a.tax.value = tax_amount;
			}
		
			if(is_under_gst != 0){ 
				document.f_a.grn_amount2.value=round2(float(document.f_a.rounding_amt.value)+float(document.f_a.ttl_gst_amt.value)+float(document.f_a.tax.value));
			}else{
				document.f_a.grn_amount2.value=round2(float(document.f_a.rounding_amt.value)+float(document.f_a.acc_adjustment.value)+float(document.f_a.tax.value));
			}
			
			document.f_a.ga.value = round2(float(document.f_a.account_amount.value)+float(document.f_a.buyer_adjustment.value)-float(document.f_a.dn_amount.value)+float(document.f_a.action_adjustment.value));
		
			document.f_a.grn_amount3.value=round2(float(document.f_a.grn_amount2.value)-float(document.f_a.return_total.value));
		
			if(document.f_a.ga.value==document.f_a.grn_amount2.value)
				$('as').innerHTML = '<img src=/ui/approved.png align=absmiddle>';
			else
				$('as').innerHTML = '<img src=/ui/cancel.png align=absmiddle>';
		}
		</script>
		{/literal}
		{if !$smarty.request.action && $is_correction && $grr.type eq 'PO'}
			{assign var=colspan value=9}
			{assign var=hr_colspan value=3}
		{else}
			{assign var=colspan value=14}
			{assign var=hr_colspan value=8}
		{/if}
		
		{assign var=hr_colspan1 value=8}
		{assign var=acc_colspan value=8}
		{if $grr.type eq 'PO' || $grr.is_ibt_do}
			{assign var=colspan value=$colspan+7}
			{assign var=acc_colspan value=$acc_colspan+7}
			{assign var=hr_colspan1 value=$hr_colspan1+6}
		{/if}
		{if $form.is_under_gst}
			{assign var=colspan value=$colspan+3}
			{assign var=acc_colspan value=$acc_colspan+3}
			{assign var=hr_colspan value=$hr_colspan+1}
			{assign var=hr_colspan1 value=$hr_colspan1+3}
		{/if}
		
		{if $config.link_code_name && $config.docs_show_link_code}
			{assign var=colspan value=$colspan+1}
			{assign var=acc_colspan value=$acc_colspan+1}
			{assign var=hr_colspan value=$hr_colspan+1}
		{/if}
		
		<tr>
		{if !$smarty.request.action && $is_correction && $grr.type eq 'PO'}
		<td rowspan="12" colspan="5">
			<table frame="box">
				<!-- misc cost -->
				<tr class=normal>
					<th nowrap align=right >
						Misc Cost [<a href="javascript:void(cost_help())">?</a>]
					</th>
					<th>&nbsp;</th>
					<th align=right>
					{if $form_po.misc_cost}
						{$form_po.misc_cost}
					{else}-{/if}
					</th><th></th><th></th>
				</tr>
		
				<!-- final discount -->
				<tr class=normal>
					<th nowrap align=right>
						Discount [<a href="javascript:void(discount_help())">?</a>]
					</th>
					<th>&nbsp;</th>
					<th align=right>
					{if $form_po.sdiscount}
						{$form_po.sdiscount}
					{else}-{/if}
					</th><th></th><th></th>
				</tr>
		
				<!-- "special" discount -->
				<tr class=normal>
					<th nowrap align=right>
						Discount from Remark#2 [<a href="javascript:void(discount_help())">?</a>]
					</th>
					<th>&nbsp;</th>
					<th align=right>
					{if $form_po.rdiscount}
						{$form_po.rdiscount}
					{else}-{/if}
					</th>
				</tr>
		
				<!-- "special" discount -->
				<tr class=normal>
					<th nowrap align=right>
						Deduct Cost from Remark#2 [<a href="javascript:void(discount_help())">?</a>]
					</th>
					<th>&nbsp;</th>
					<th align=right> 
					{if $form_po.ddiscount}
						{$form_po.ddiscount}
					{else}-{/if}
					</th>
				</tr>
		
				<!-- transportation cost -->
				<tr class=normal>
					<th nowrap align=right>
						Transportation Charges
					</th>
					<th>&nbsp;</th>
					<th align=right>
					{if $form_po.transport_cost}
						{$form_po.transport_cost}
					{else}-{/if}
					</th>
				</tr>
		
				<!-- total amount -->
				<tr class=normal>
					<th nowrap align=right>
						PO Amount
					</th>
					<th>&nbsp;</th>
					<th align=right id=final_amount class=large>
					{$total_po.final_amount|number_format:2}
					</th>
					{assign var=final_profit value=$total_po.sell-$total_po.final_amount}
					<th align=right id=final_profit class=large style="{if $final_profit<=0}color:#f00{/if}">
					{$final_profit|number_format:2}
					</th>
					<th align=right id=final_margin class=large style="{if $final_profit<=0}color:#f00{/if}">
					{if $total_po.sell}
					{$final_profit/$total_po.sell*100|number_format:2}%
					{/if}
					</th>
				</tr>
		
				{if $form_po.is_under_gst}
					<!-- total GST amount -->
					<tr class=normal>
						<th nowrap align=right >
							PO Amount Include GST
						</th>
						<th>&nbsp;</th>
						<th align=right id=final_gst_amount class=large>
						{$total_po.final_gst_amount|number_format:2}
						</th>
						{assign var=final_profit value=$total_po.gst_sell-$total_po.final_gst_amount}
						<th align=right id=final_gst_profit class=large style="{if $final_profit<=0}color:#f00{/if}">
						{$final_profit|number_format:2}
						</th>
						<th align=right id=final_gst_margin class=large style="{if $final_profit<=0}color:#f00{/if}">
						{if $total_po.gst_sell}
						{$final_profit/$total_po.gst_sell*100|number_format:2}%
						{/if}
						</th>
					</tr>
				{/if}
		
				<!-- supplier amount -->
				<tr class=normal>
					<th nowrap align=right>
					Supplier PO Amount
					</th>
					<th>&nbsp;</th>
					<th align=right id=final_amount2 class=large>
					{$total_po.final_amount2|number_format:2}
					</th>
				</tr>
		
				{if $form_po.is_under_gst}
					<!-- supplier GST amount -->
					<tr class=normal>
						<th nowrap align=right>
						Supplier PO Amount Include GST
						</th>
						<th>&nbsp;</th>
						<th align=right id=final_gst_amount2 class=large>
						{$total_po.final_gst_amount2|number_format:2}
						</th>
					</tr>
				{/if}
			</table>
		</td>
		{/if}
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>Tax Amount (+)</b>
		<input size="8" class="r" style="color:blue; background-color:#bbffff;" name="tax" readonly value="{$form.tax|number_format:2:'.':''}"></td>
		</tr>
		
		<td nowrap colspan="{$colspan}" align="right"><b>Rounding Error Adjustment (+)</b>
		<input size="8" class="r" style="color:blue" onclick="clear0(this)" name="rounding_amt" value="{$form.rounding_amt|number_format:2:'.':''}" onchange="mf(this);calc_balance();" {if $cu_id}readonly{/if}></td>
		{if $form.is_under_gst}
			{*<th>GST</th>*}
			<th><input type="text" class="r" size="8" name="gst_rounding_adj" value="{$form.rounding_gst_amt|number_format:2:'.':''}" onclick="clear0(this)" onchange="mfz(this,2);gst_rounding_adjustment();"></th>
		{/if}
		</tr>
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>GRN Amount (After Tax Amount & Adjust)</b>
		{if $form.is_under_gst}
			{assign var=grn_amount2 value=$acc_ttl_gst_amt+$form.tax}
		{else}
			{assign var=grn_amount2 value=$form.acc_adjustment+$form.rounding_amt+$form.tax}
		{/if}
		<input size="8" class="r" style="color:blue; background-color:#fc3;" name="grn_amount2" readonly value="{$grn_amount2|number_format:2:'.':''}"></td>
		<td {if !$form.is_under_gst}style="display:none;"{/if} align="center">
			<input type="text" size="8" class="r" name="ttl_acc_gst" {if $form.final_gst_amt}value="{$form.final_gst_amt|number_format:2:'.':''}"{else}value="{$acc_ttl_gst|number_format:2:'.':''}"{/if} readonly>
		</td>
		{if !$manager_col && !$cu_id}
			<td colspan="2">
				<input type="button" value="Generate D/N" onclick="toggle_dn_dialog();">
			</td>
		{/if}
		</tr>
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>Return Amount (-)</b>
		<input size="8" class="r" style="color:blue; background-color:#bbffff;" name="return_total" readonly value="{$return_total|number_format:2:'.':''}"></td>
		<td {if !$form.is_under_gst}style="display:none;"{/if} align="center">
			<input type="text" size="8" class="r" name="return_gst_total" value="{$return_gst_total|number_format:2:'.':''}" readonly>
		</td>
		</tr>
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>GRN Amount (After Return)</b>
		<input size="8" class="r" style="color:blue; background-color:#bbffff;" name="grn_amount3" readonly value="{$grn_amount2-$return_total|number_format:2:'.':''}"></td>
		</tr>
		
		<tr>
		<td colspan="{$hr_colspan}">&nbsp;</td>
		<td colspan="{$hr_colspan1}"><hr noshade size="1" /></td>
		</tr>
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>Invoice/DO Amount</b>
		<input size="8" class="r" style="color:blue" name="account_amount" value="{$form.account_amount|number_format:2:'.':''}" {if $manager_col || $cu_id}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
		<td {if !$form.is_under_gst}style="display:none;"{/if} align="center"><input type="text" size="8" class="r" name="total_inv_gst_amount" value="{$grr.grr_gst_amount|number_format:2:'.':''}" readonly></td>
		</tr>
		
		<tr>
			<td nowrap colspan="{$colspan}" align="right"><b>Account Adjustment</b>
			<input size="8" class="r" name="buyer_adjustment" onclick="clear0(this)" value="{$form.buyer_adjustment|number_format:2:'.':''}" {if $manager_col || $cu_id}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
		</tr>
		<tr>
			<td nowrap colspan="{$colspan}" align="right"><b>Debit Note Amount</b>
			<input size="8" class="r" name="dn_amount" onclick="clear0(this)" value="{$form.dn_amount|number_format:2:'.':''}" {if $manager_col || $cu_id || $dn_is_generated}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
		
			<td nowrap colspan="2" align="left"><b>No.</b>
			<input size="8" class="r" name="dn_number" onclick="clear0(this)" value="{$form.dn_number}" {if $manager_col || $cu_id || $dn_is_generated}readonly{/if}></td>
		</tr>
		
		<tr>
		<td nowrap colspan="{$colspan}" align="right"><b>Other Adjustment</b>
		<input size="8" class="r" name="action_adjustment" onclick="clear0(this)" value="{$form.action_adjustment|number_format:2:'.':''}" {if $manager_col || $cu_id}readonly{else}onchange="mfz(this,2);calc_balance();"{/if}></td>
		</tr>
		
		<tr>
		{assign var=gross_amt value=$form.account_amount+$form.buyer_adjustment-$form.dn_amount+$form.action_adjustment}
		<td nowrap colspan="{$colspan}" align="right"><b>Balance</b> (Invoice/DO Amount + Account Adjustment - Debit Note Amount + Other Adjustment)
		<span id="as"></span> <input class="r" size="8" name="ga" value="{$gross_amt|number_format:2:'.':''}" style=" background-color:#fc3;" readonly>
		</td>
		</tr>
		
		
		<tr>
		<td colspan="{$acc_colspan}" align="right"><b>Account Action</b></td>
		<td colspan="6"><textarea rows="5" cols="40" name="acc_action" {if $cu_id}readonly{/if}>{$form.acc_action|escape}</textarea></td>
		</tr>
		
		<script>
		calc_acc_total();
		calc_balance();
		if(open_type == "view" && is_under_gst == 1) calculate_all_gst();
		</script>
		{/if}
		</table>
</div>

{if $grr.sdiscount_amt || $grr.rdiscount_amt || $grr.po_remark1[0] || $grr.po_remark2[0]}
<!-- show D/N table if there is discount from remark in PO  -->
<br>
<h2>PO Remarks</h2>
<table width="100%" cellpadding="4" cellspacing="0" border="0">
<tr>
<td valign="top" style="border:1px solid #000">
<b>PO Remark #1 (Discount Amt ({$grr.sdiscount}): {$grr.sdiscount_amt|number_format:2|ifzero:"-"})</b><br>
<span class="small">{$grr.po_remark1[0]|nl2br}</span>
</td>
<td>&nbsp;</td>
<td valign="top" style="border:1px solid #000">
<b>PO Remark #2 (Discount Amt ({$grr.rdiscount}): {$grr.rdiscount_amt|number_format:2|ifzero:"-"})</b><br>
<span class="small">{$grr.po_remark2[0]|nl2br}</span>
</td>
{*if !$form.is_under_gst}
	<td>&nbsp;</td>
	<td valign="top" style="border:1px solid #000">
		{if $is_correction}
			<input type="checkbox" name="dn_issued" value="1" {if $form.dn_issued}checked{/if}> <b>D/N Issued (please tick)</b><br>
			<b>D/N Number:</b> <input name="dn_number" size="5" value="{$form.dn_number}"> &nbsp;
			<b>D/N Amount:</b> <input name="dn_amount" size="5" value="{$form.dn_amount}"><br>
			If not issued, enter reason:<br>
			<textarea name="dn_reason" rows="5" cols="40">{$form.dn_reason|escape}</textarea>
		{else}
			{if $form.dn_issued}
			<b>D/N Number:</b> {$form.dn_number}<br>
			<b>D/N Amount:</b> {$config.arms_currency.symbol}{$form.dn_amount|number_format:2}
			{else}
			{$form.dn_reason|nl2br}
			{/if}
		{/if}
	</td>
{/if*}
</tr>
</table>
{/if}

<script>

{if $is_correction && $smarty.request.a eq 'open'}
	{literal}
	var set_fields = $('tblist').getElementsByTagName("INPUT");
	var parent_tr = '';

	$A(set_fields).each(
		function (ele,idx){
			if (ele.name.indexOf("rcc_status[")==0){
				if(ele.value == 1){
					parent_tr = $(ele).parentNode.parentNode;
					$(parent_tr).setStyle({'text-decoration': 'line-through'});
				}
			}
		}
	);
	{/literal}
	new Draggable('reconcile_menu');
	document.f_a['acc_action'].focus();
{/if}
</script>