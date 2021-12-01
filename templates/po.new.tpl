{*
5/22/2008 6:00:02 PM yinsee
- return "0" and silence instead of popup message saying "No Last PO found for this item";

6/9/2008 3:17:19 PM yinsee
- fix remark2 bug
- fix po delete row bug (branch_id not passed)

6/24/2008 1:01:27 PM yinsee
- fix print icon appear on reject/terminated

8/8/2008 3:48:35 PM yinsee
- add GP(%) display for single branch PO 

9/6/2008 12:33:11 PM yinsee
- hide Show SKU button for approval_screen mode

7/30/2009 5:12:08 PM Andy
- Add Reset function

10/20/2009 9:52:00 AM jeff
- add hq purchase po option set in config

3/18/2010 5:33:50 PM Andy
- Fix sometime PO getting wrong item row number after delete item.

7/26/2010 5:10:03 PM Andy
- Fix PO branch getting wrong GP%.

8/5/2010 6:08:50 PM Andy
- PO add IBT checkbox. (Need config)

8/6/2010 3:38:22 PM Andy
- Fix approval screen still can edit bugs.
- Fix some minor javascript error.

8/9/2010 3:32:18 PM Andy
- Fix and remove some human mistake word.

11/4/2010 4:05:07 PM Alex
- add show varieties condition at reset_sku

1/13/2011 4:18:10 PM Andy
- Add PO limit user to only can add parent sku if found $config['po_only_add_parent_sku'].

1/24/2011 12:35:43 PM Alex
- add branch ids at get_item_sales_trend()

1/26/2011 12:09:47 PM Alex
- add item sales trend selected branch and all branch

2/28/2011 12:05:40 PM Alex
- add close button at rejected PO

9/9/2011 3:08:46 PM Alex
- fix calculation bugs

9/14/2011 12:42:14 PM Alex
- fix total selling and total cost calculation bugs

9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/4/2011 11:47:43 AM Justin
- Added new printing option "GRN Performance Report".
- Auto hidden/appear this report option base on the GRN approved status.
- Re-aligned the printing options to fill into table.

10/14/2011 3:28:12 PM Justin
- Modified the print option "GRN Performance Report" to base on config.

11/9/2011 12:32:22 PM Justin
- Fixed the "Revoke" button only appear when in Cancelled PO.

3/1/2012 11:39:29 AM Alex
- add add_grn_barcode_item() function for scan barcode

3/30/2012 11:39:32 AM Justin
- Added new feature to prompt a confirmation when user about to leave the page.
- Modified the ajax call when add po item, use JSON instead of XML.
- Modified the ajax call to add last po item during add po item instead of call another ajax to insert last po item.
- Added loading message while adding po item.

4/4/2012 1:45:24 PM Andy
- Change to no need show calendar icon if it is under view mode.
- Add show relationship between PO and SO.

4/6/2012 9:51:32 AM Justin
- Fixed the bugs confirmation of about to leave page shown out viewing the document only.
- Fixed the FOC menu did not close after click save.
- Fixed some of the functions did not call after add po item.

4/12/2012 11:01:40 AM Andy
- Fix PO cannot delete after reset.

4/19/2012 2:18:33 PM Andy
- Rename "Cancelled" as "Cancelled/Deleted".

8/10/2012 11:12 AM Andy
- Add purchase agreement control.

8/13/2012 5:39 PM Andy
- Fix user cannot create new PO even got open buy privilege under purchase agreement mode.

9/5/2012 3:32 PM Justin
- Bug fixed on system will allow user to edit UOM after added new item.

10/17/2012 4:47 PM Andy
- Enhance PO to checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.
- Enhance when user change ctn, qty or uom, will divide to get the ratio and apply to all sku in same bom package list.
- Enhance when user delete one of the bom package sku, all related sku will be delete at the same time.

12/13/2012 4:37 PM Justin
- Enhanced to auto fill up cancellation date base on the config "po_agreement_cancellation_days".

3/1/2013 10:59 AM Fithri
- Bugfix: PO add vendor item, the edited qty will missing
- add vendor item change to ajax method

3/11/2013 4:21 PM Andy
- Fix when select vendor or change department, the page always jumping to focus on search sku, change to only focus when user change search sku type.
- Enhance to show curtain layer when user click "show sku of vendor".
- Enhance when add/delete item will check to enable/disable department dropdown.

5/15/2013 1:56 PM Fithri
- scan barcode only allow sku in same department
- scan barcode also check blocked po items

5/22/2013 11:49 AM Justin
- Bug fixed on system cannot show out uncheckout GRA notice while edit or return from error.

5/31/2013 5:27 PM Justin
- Bug fixed on uncheckout GRA checking no longer working while search vendor.

7/29/2013 11:42 AM Andy
- Change PO approval history to use shared approval history include file.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

9/17/2013 6:14 PM Justin
- enhanced to show error message in detail while user click on "Show SKU of this Vendor".

10/9/2013 3:52 PM Justin
- Enhanced to have delivery, cancellation date and partial delivery controls over branches by HQ while user chosen HQ payment.

10/16/2013 9:25 AM Justin
- Bug fixed on the javascript error.

10/21/2013 3:51 PM Justin
- Enhanced to show notice for the use of User Selection.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

12/27/2013 10:29 AM Fithri
- when create PO at branch allow to have user selection to send PM/email

1/20/2013 3:06 PM Andy
- Remove "-- Please Select --" option from department dropdown for branch mode.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.

3/6/2015 11:33 AM Andy
- Fix if not under gst, user change cost will not trigger the recalculate.

3/11/2015 10:04 AM Justin
- Bug fixed on unable to choose global delivery and cancellation date while using HQ purchase.

3/27/2015 1:04 PM Andy
- Enhance to recalculate all items and all total on first load.

4/7/2015 4:11 PM Andy
- Fix PO Amount Include GST wrong profit calculation.

5/15/2015 10:52 AM Justin
- Bug fixed on the curtain could not be closed once print performa PO.

5/20/2015 4:05 PM Justin
- Bug fixed on total amount did not round to 2 decimal points.

6/4/2015 2:21 PM Justin
- Bug fixed on PO that do not have GST will cause form couldn't recalculate total.

10/23/2015 6:01 PM Andy
- Enhanced to show popup information for HQ Payment and Branch Payment.

10/29/2015 5:17 PM DingRen
- Fix not recalculate GST after delete item

11/16/2015 10:01 AM DingRen
- highlight the message for GRA Outstanding item

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

12/04/2015 4:07PM DingRen
- fix remark and internal remak unable to edit wher item is from PO Agreement

4/26/2016 3:48 PM Andy
- Fix if Purchase Agreement Item mixed with normal item will cause the PO cannot be saved.

07/01/2016 11:00 Edwin
- Able to show "Parent Sales Trend" in PO items.

07/18/2016 16:30 Edwin
- Enhanced on delivery date format changed to YYYY-MM-DD.
- Enhanced on PO items tax code change to flat rate if vendor's gst type is flat rate.

1/11/2017 3:11 PM Andy
- Enhanced to check gst selling price when branch is under gst.

2/24/2017 4:44 PM Andy
- Fix item discount calculation.

2/27/2017 9:45 AM Zhi Kai
- Change wording of 'General Informations' to 'General Information'. 

4/18/2017 11:37 AM Andy
- Change GST calculation method to follow GRN.
- Change to use data stored in database instead of recalculate everytime.

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 

6/5/2017 11:40 AM Andy
- Fix gst wrong calculated if purchase uom fraction more than 1.

8/15/2017 3:52 PM Andy
- Fixed wrong gst if got discount.

12/13/2017 1:55 PM Andy
- Fixed scan barcode not working if user don't have privilege "PO_ADD_OTHER_DEPT".

1/18/2018 3:59 PM Justin
- Bug fixed on multi server mode will able to confirm, reset, save or cancel PO for other branches.

3/26/2018 4:01 PM Andy
- Added Foreign Currency feature.
- Enhanced to hide PO Amount Include GST GP and GP % if the branch is not under GST.

7/30/2018 4:41 PM Andy
- Fixed Item Sales Trend cannot show.

3:06 PM 11/21/2018 Justin
- Enhanced to have new function to auto focus on ctn and pcs.

12/3/2018 2:11 PM Justin
- Enhanced to auto select the value when auto focus.

12/6/2019 1:38 PM William
- Added new "Category Sales Trend" and display when PO has po item.

2/4/2020 9:22 AM William
- Fixed bug "po_branch_id" html format not correct.

04/21/2020 10:26 AM Sheila
- Modified layout to compatible with new UI.

05/15/2020 6:09 PM Sheila
- Updated table color_size_matrix height.

06/24/2020 10:09 AM Sheila
- Updated button css

8/4/2020 6:08 PM Andy
- Added fixed header and column.

11/3/2020 9:09 AM William
- Enhanced to let PO can add item by upload csv.

11/4/2020 5:41 PM Andy
- Fixed item context menu and po cost popup z-index.
- Made Category Sales Trend to colspan=2.
*}

{assign var=time_value value=1000000000}

{if $readonly}
	{assign var=allow_edit value=0}
{else}
	{assign var=allow_edit value=1}
{/if}

{if !$form.approval_screen}
{include file=header.tpl}
{else}
<hr noshade size=2>
{/if}
<script type="text/javascript">
//var show_last_po="{$config.po_show_last_po}";
var b_code="{$BRANCH_CODE}";
var open_mode="{$smarty.get.a}";
var allow_sales_order = int('{$config.allow_sales_order}');

{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var po_only_add_parent_sku = '{$config.po_only_add_parent_sku}';

var enable_po_agreement = int('{$config.enable_po_agreement}');
var PO_AGREEMENT_OPEN_BUY = int('{$sessioninfo.privilege.PO_AGREEMENT_OPEN_BUY}');
var sku_bom_additional_type = int('{$config.sku_bom_additional_type}');
var po_agreement_cancellation_days = int('{$config.po_agreement_cancellation_days}');
var po_option = int('{$form.po_option}');
var readonly = '{$readonly}';
var approval_screen = '{$approval_screen}';
var po_branch_id = '{$form.po_branch_id}';
var po_allow_hq_purchase = '{$config.po_allow_hq_purchase}';
var is_under_gst = int('{$form.is_under_gst}');
var enable_gst = int('{$config.enable_gst}');
var branch_is_under_gst = int('{$form.branch_is_under_gst}');
var currency_symbol = '{$config.arms_currency.symbol}';
var branch_arr= new Array();
{if $form.po_branch_id}
	branch_arr.push({$form.po_branch_id});
{elseif $form.deliver_to}
	{foreach from=$form.deliver_to item=branches_id}
		branch_arr.push({$branches_id});
	{/foreach}
{else}
	branch_arr.push({$form.branch_id});
{/if}

var got_foreign_currency = {if $config.foreign_currency}1{else}0{/if};


</script>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
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

{*
.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}
*}

.addbutton {
	//background:#ece !important;
	border:1px solid #fff !important;
	border-right:1px solid #333 !important;
	border-bottom:1px solid #333 !important;
}

input[disabled] {
  color:black;
}
input[readonly] {
  color:black;
}
select[disabled] {
  color:black;
}
.bom_legend{
	color:grey;
}

#vendor_checkout{
    background:#ffff00;
    font-weight: bold;
    padding:5px;
}
#span_pending_currency_rate{
	background-color: yellow;
}
.category_sales_trend tr td{
	padding:3px;
}

</style>
{/literal}

{if $config.po_sticky_column}
{literal}
<style>
.fixed_header thead th{
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 2;
  background-color: #fff;
}

.fixed_header tbody td[scope=row] {
  position: -webkit-sticky;
  position: sticky;
  left: 0;
  z-index: 1;
  background-color:#ffa;
}

.fixed_header thead th[scope=row] {
  position: -webkit-sticky;
  position: sticky;
  left: 0;
  z-index: 3;
  background-color: #fff;
}

</style>
{/literal}
{/if}

{literal}
<script type="text/javascript">
var max_branches = 25;
var context_info;
var needCheckExit = true;

function hide_context_menu()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;	 
	Element.hide('item_context_menu');
}

function show_context_menu(obj, id, item_id, is_foc)
{
	context_info = { element: obj, id: id, sku_item_id: item_id, is_foc: is_foc};
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	Element.show('item_context_menu');
	
	if(open_mode=='open')
		$('item_context_menu_foc').style.display = is_foc ? "" : "none";
	
	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}
	
	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function check_date(obj){	
	text=obj.value;
	if(text && isNumeric(text) && text.length=='6'){
		day=text.slice(0,2);
		month=text.slice(2,4);
		year=text.slice(4,6);
		year='20'+year;

		if(day<32 && month<13 && day>0 && month>0){
			obj.value=day+'/'+month+'/'+year;
		}
		else{
			alert('Invalid day/month format.');
			obj.value='';
			obj.focus();
		}
	}
}

function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) 
  	return false;
  return true;
}

function row_recalc(id, skip_recalc_total){
	var foc = 0;
	var qty = 0;
	var ctn = 0;
	var total_sell = 0;
	var total_gst_sell = 0;
    var b_total_sell=new Array();
    var b_total_cost=new Array();
    var b_total_qty=new Array();
	var qty_decimal_points = 0;
	var currency_code = got_foreign_currency ? document.f_a['currency_code'].value : '';
	var currency_rate = got_foreign_currency ? float(document.f_a['currency_rate'].value) : 1;
	
	// if multiple branch, sum the qty and update
	if ($('q'+id) == undefined){
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];
		    if ($('q'+id+'['+i+']') != undefined){
		        if (int($('ouomf'+id).value) > 1 && float($('ql'+id+'['+i+']').value) >= float($('ouomf'+id).value)){
				    $('q'+id+'['+i+']').value = float($('q'+id+'['+i+']').value) + int(round($('ql'+id+'['+i+']').value / $('ouomf'+id).value, global_qty_decimal_points));
                    $('ql'+id+'['+i+']').value = float(round($('ql'+id+'['+i+']').value % $('ouomf'+id).value, global_qty_decimal_points));
				}
				if (int($('ouomf'+id).value) > 1 && float($('fl'+id+'['+i+']').value) >= float($('ouomf'+id).value)){
				    $('f'+id+'['+i+']').value = float($('f'+id+'['+i+']').value) + int(round($('fl'+id+'['+i+']').value / $('ouomf'+id).value,  global_qty_decimal_points));
                    $('fl'+id+'['+i+']').value = float(round($('fl'+id+'['+i+']').value % $('ouomf'+id).value, global_qty_decimal_points));
				}
				 q1 = float(round($('q'+id+'['+i+']').value*$('ouomf'+id).value, global_qty_decimal_points)) + float($('ql'+id+'['+i+']').value);
				 q1 = float(round(q1, global_qty_decimal_points));
				 qty += q1;
				 q2 = float(round($('f'+id+'['+i+']').value*$('ouomf'+id).value, global_qty_decimal_points)) + float($('fl'+id+'['+i+']').value);
				 q2 = float(round(q2, global_qty_decimal_points));
				 foc += q2;
				 ctn += float($('q'+id+'['+i+']').value)+float($('f'+id+'['+i+']').value);

				 b_total_qty[i]=q1;
				 b_total_sell[i]=$('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);				 
				 b_total_cost[i]=$('op'+id).value*q1/float($('ouomf'+id).value);
				 
				 total_sell += $('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
				 $('br_sp['+i+']['+id+']').innerHTML = $('sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
				 $('br_cp['+i+']['+id+']').innerHTML = $('op'+id).value*q1/float($('ouomf'+id).value);
				 
				 // calculate row gst selling price
				 if(branch_is_under_gst == 1){
					total_gst_sell += $('gst_sp'+id+'['+i+']').value*(q1+q2)/float($('suomf'+id).value);
				 }
				 
			}
		}
	}
	else{
		if (int($('ouomf'+id).value)>1 && float($('ql'+id).value) >= float($('ouomf'+id).value)){
		    $('q'+id).value = float($('q'+id).value) + int(round($('ql'+id).value / $('ouomf'+id).value, global_qty_decimal_points));
	        $('ql'+id).value = float(round($('ql'+id).value % $('ouomf'+id).value, global_qty_decimal_points));
		}
		if (int($('ouomf'+id).value)>1 && float($('fl'+id).value) >= float($('ouomf'+id).value)){
		    $('f'+id).value = float($('f'+id).value) + int(round($('fl'+id).value / $('ouomf'+id).value, global_qty_decimal_points));
	        $('fl'+id).value = float(round($('fl'+id).value % $('ouomf'+id).value, global_qty_decimal_points));
		}
		qty = float($('q'+id).value)*$('ouomf'+id).value + float($('ql'+id).value);
		qty = float(round(qty, global_qty_decimal_points));
		foc = float($('f'+id).value)*$('ouomf'+id).value + float($('fl'+id).value);
		foc = float(round(foc, global_qty_decimal_points));
		ctn = float($('q'+id).value)+float($('f'+id).value);
		ctn = float(round(ctn, global_qty_decimal_points));
		
		total_sell = $('sp'+id).value*(qty+foc)/float($('suomf'+id).value);

		$('br_sp['+id+']').innerHTML = total_sell;
		$('br_cp['+id+']').innerHTML = $('op'+id).value*qty/float($('ouomf'+id).value);
		
		// calculate row gst selling price
		 if(branch_is_under_gst == 1){
			total_gst_sell += $('gst_sp'+id).value*(qty+foc)/float($('suomf'+id).value);
		 }
	}
	
	$('qty'+id).innerHTML = qty;
	$('foc'+id).innerHTML = foc;
	$('ctn'+id).innerHTML = ctn;

	$('total_sell'+id).innerHTML = round2(total_sell);
	if(branch_is_under_gst == 1)	$('total_gst_sell-'+id).value = round2(total_gst_sell);
	
	amount = $('op'+id).value*qty/float($('ouomf'+id).value);
	if ($('is_foc'+id).value != 1){
		$('gamount'+id).innerHTML = round2(amount);	
	}
	if ($('tax'+id).value != ''){
		$('tax'+id).value = float($('tax'+id).value);
		amount *= (float($('tax'+id).value)+100)/100;
	}

	camount = amount;
	amount = parse_formula(amount, $('disc'+id), false);
	if ($('disc'+id).value.indexOf("%")>=0){
		$('disc_amount'+id).innerHTML = round2(camount - amount);
		total_disc=round2(camount - amount);
	}
	else{
		$('disc_amount'+id).innerHTML = '';
		total_disc=round2(camount - amount);
	}
	
	//assign each branch GP% -gary 5/14/2008 4:58:12 PM
	if ($('q'+id) == undefined){
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];

		    if ($('q'+id+'['+i+']') != undefined){
	    		branch_disc=total_disc*b_total_qty[i]/qty;
				branch_gp=(b_total_sell[i]-((b_total_cost[i]-float(branch_disc))*currency_rate))/b_total_sell[i];
				$('branch_gp'+id+'['+i+']').value=round(branch_gp*100,2);
		    }
		}
	}
	
	var base_amount = 0;
		
	if ($('is_foc'+id).value != 1){
		amount = float(round2(amount));
		$('amount'+id).innerHTML = round2(amount) + "<br><font color=blue>" + round(amount/(foc+qty),global_cost_decimal_points) + "</font>";
		
		// Foreign Currency
		if(currency_code){
			base_amount = float(round2(amount*currency_rate));
			$('span_base_item_nett_amt-'+id).update(round2(base_amount));
		}
	}

	// Gross Profit
	var total_profit = 0;
	if ($('is_foc'+id).value !=1){
		if(currency_code){	// Foreign Currency
			total_profit = total_sell - base_amount;
		}else{
			total_profit = total_sell - amount;
		}
	}else{
		total_profit = total_sell;
	}
	$('total_profit'+id).update(round2(total_profit)).removeClassName('negative_value');
	
	if (total_profit<=0){
		$('total_profit'+id).addClassName('negative_value');
	}

	// Profit Percent
	var total_margin = round2(total_profit/total_sell*100);
	$('total_margin'+id).update(total_margin+'%').removeClassName('negative_value');
	if ($('total_marginc'+id)!=undefined) $('total_marginc'+id).value = round2(total_profit/total_sell*100);
	
	if (total_profit<=0){
		$('total_margin'+id).addClassName('negative_value');
	}
	    
	if(skip_recalc_total==undefined){
		recalc_totals();
	}
	
	
	if(is_under_gst == 1 && $('is_foc'+id).value != 1) calculate_gst(id);
}

function parse_formula(value, obj, add){
    obj.value = obj.value.regex(/[^0-9\.%+]/g,'');
    obj.value = obj.value.regex(/\+$/,'');
	if (obj.value != ''){
		$A(obj.value.split("+")).each( function(r,idx) {
		    if (add){
				if (r.indexOf("%")>0){
					value *= (100+float(r))/100;
				}			        
				else{
				    value = float(value) + float(r);				
				}
			}
			else{
				if (r.indexOf("%")>0){
			        value *= (100-float(r))/100;				
				}
				else{
				    value = float(value) - float(r);				
				}

			}
		});
	}
	return value;
}

// recalculate totals
function recalc_totals(){
	/// get each SPAN under the table
	if(!$('po_items_list')) return;
	var sp = $('po_items').getElementsByTagName("SPAN");
	var aa = 0;
	var ga = 0;
	var ts = 0;
	var tp = 0;
	var qty = 0;
	var foc = 0;
	var ctn = 0;
	var cnt = 1;
	var ttb = new Array();
	var ttb_branch = new Array();
	var currency_code = got_foreign_currency ? document.f_a['currency_code'].value : '';
	var currency_rate = got_foreign_currency ? float(document.f_a['currency_rate'].value) : 1;
	
	$A(sp).each(function (r,idx){
		if (r.id.indexOf("count")==0){
			r.innerHTML = cnt + ".";
			cnt++;
		}
		else if (r.id.indexOf("br_sp")==0){
			vid = r.id.substr(0,r.id.lastIndexOf('['));

			if (isNaN(ttb[vid])) ttb[vid] = 0;
			if (ttb_branch){
				var exists=false;
				for(var i=0;i<ttb_branch.length;i++){
					if (ttb_branch[i] == vid){
						exists=true;
						break;
					}
				}
			}

			if (!exists) ttb_branch.push(vid);	

			ttb[vid] += float(r.innerHTML);
		}
		else if (r.id.indexOf("br_cp")==0){
			vid = r.id.substr(0,r.id.lastIndexOf('['));
			if (isNaN(ttb[vid])) ttb[vid] = 0;

			if (ttb_branch){
				var exists=false;
				for(var i=0;i<ttb_branch.length;i++){
					if (ttb_branch[i] == vid){
						exists=true;
						break;
					}
				}
			}
			if (!exists) ttb_branch.push(vid);	

			ttb[vid] += float(r.innerHTML);
		}
		else if (r.id.indexOf("amount")==0){
		    if (r.innerHTML != 'FOC')
			    aa += float(r.innerHTML);
		}
		else if (r.id.indexOf("gamount")==0){
		    if (r.innerHTML != 'FOC')
				ga += float(r.innerHTML);
		}
		else if (r.id.indexOf("total_sell")==0){
		    if (r.innerHTML != 'FOC')
				ts += float(r.innerHTML);
		}
		else if (r.id.indexOf("total_profit")==0){
		    if (r.innerHTML != 'FOC')
				tp += float(r.innerHTML);
		}
		else if (r.id.indexOf("qty")==0){
			qty += float(r.innerHTML);
		}
		else if (r.id.indexOf("foc")==0){
			foc += float(r.innerHTML);
		}
		else if (r.id.indexOf('ctn')==0){
			ctn += float(r.innerHTML);
		}
	});
	
	var total_gst_sell = 0;
	if(branch_is_under_gst == 1){
		$$('#po_items input.inp_total_gst_sell').each(function(inp){
			total_gst_sell += float(inp.value);
		});
		
		document.f_a['total_gst_sell'].value = round2(total_gst_sell);
	}

	$('total_ctn').innerHTML = 'Ctn: ' + ctn;
	$('total_pcs').innerHTML = 'Pcs: ' + (qty+foc);
	$('total_check').value = ctn+qty+foc;
	$('total_gross_amount').innerHTML = round2(ga);
	
	// Sub Total Nett Amount
	aa = float(round2(aa));
	$('total_amount').innerHTML = round2(aa);
	if(currency_code){
		var base_aa = float(round2(aa*currency_rate));
		$('span_base_total_amount').update(round2(base_aa));
	}
	
	$('total_sell').innerHTML = round2(ts);
	$('total_profit').removeClassName('negative_value');
	if(tp<=0)	$('total_profit').addClassName('negative_value');
	$('total_profit').innerHTML = round2(tp);
	$('total_margin').style.color = (tp<=0) ? '#f00' : '#000';
	$('total_margin').innerHTML = round2(tp/ts*100)+'%';

	$(ttb_branch).each(
		function (key){
			$(key).innerHTML = round2(ttb[key]);
		}
	);
	
	// po total	
	var a = aa;
	a = parse_formula(a, $('misc_cost'), true);
	a = parse_formula(a, $('sdiscount'), false);
	var b = a; // b is vendor's PO amount, skip calculation from remark#2
	a = parse_formula(a, $('rdiscount'), false);
	a = parse_formula(a, $('ddiscount'), false);
	a += float($('transport_cost').value);
	b += float($('transport_cost').value);
	
	a = float(round2(a));
	var base_a = 0;
	b = float(round2(b));
	
	// PO Amount
	$('final_amount').innerHTML = round2(a);
	if(currency_code){
		base_a = float(round2(a*currency_rate));
		$('span_base_final_amount').update(round2(base_a));
	}
	$('final_amount2').innerHTML = round2(b);
	document.f_a.po_amount.value = round2(a);

	var ts = float($('total_sell').innerHTML);
	var pf = ts - a;
	if(currency_code)	pf = ts - base_a;
	$('final_profit').removeClassName('negative_value');
	if(pf<=0)	$('final_profit').addClassName('negative_value');
	$('final_profit').update(round2(pf));
	$('final_margin').style.color = (pf<=0) ? '#f00' : '#000';
	$('final_margin').innerHTML = round2(pf/ts*100) + '%';
}

function uom_change(value,type,id){
	var a = value.split(",");
	var old_fraction = document.f_a.elements[type+'_uom_fraction['+id+']'].value;

	document.f_a.elements[type+'_uom_id['+id+']'].value = a[0];
	document.f_a.elements[type+'_uom_fraction['+id+']'].value = a[1];
	//$('suomf'+id).value=a[1];
	
	// recalculate the selling or purchase price when uom changed
	//document.f_a.elements[type+'_price['+id+']'].value *= a[1]/old_fraction;
	if (type == 'selling'){
		//document.f_a.elements[type+'_price['+id+']'].value = round(document.f_a.elements[type+'_price['+id+']'].value,2);	
	}
	else{
		document.f_a.elements[type+'_price['+id+']'].value *= a[1]/old_fraction;
	    document.f_a.elements[type+'_price['+id+']'].value = round(document.f_a.elements[type+'_price['+id+']'].value,3);	
	}

	if (type == 'order' && document.f_a.elements['resell_price['+id+']'] != undefined){
		document.f_a.elements['resell_price['+id+']'].value *= a[1]/old_fraction;
        document.f_a.elements['resell_price['+id+']'].value = round(document.f_a.elements['resell_price['+id+']'].value,3);
	}

	// hide loose  if uom is 1
	//added gary 6/18/2007 1:57:46 PM to avoid selling uom control
	if(type=='order'){
		disabled = (a[1] == 1)
		// if multiple branch, sum the qty and update
		if ($('q'+id) == undefined){
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];

			    if ($('q'+id+'['+i+']') != undefined){
					if (a[1]==1){
						$('q'+id+'['+i+']').value = '--';
						$('f'+id+'['+i+']').value = '--';
					}
					$('q'+id+'['+i+']').disabled = disabled;
					$('f'+id+'['+i+']').disabled = disabled;
				}
			}
		}
		else{
			if (a[1]==1){
	            $('q'+id).value = '--';
	            $('f'+id).value = '--';
			}
			$('q'+id).disabled = disabled;
			$('f'+id).disabled = disabled;
		}
	}
	
	if(sku_bom_additional_type){	// got bom package config
		if ($('q'+id) == undefined){	// multiple branch
			for (var b=0;b<branch_arr.length;b++){
				var bid = branch_arr[b];
				
				update_bom_package_qty(id, bid);
			}
		}else{	// single branch
			update_bom_package_qty(id);
		}
	}
}


function init_calendar(sstr){

	Calendar.setup({
	    inputField     :    "po_dt",     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "img_po_dt",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	Calendar.setup({
	    inputField     :    "dt1"+sstr,     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "t_dt1"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});

	Calendar.setup({
	    inputField     :    "dt2"+sstr,     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "t_dt2"+sstr,  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	
	if(b_code == "HQ" && !readonly && !approval_screen && (!po_branch_id || po_branch_id == 0) && po_allow_hq_purchase){
		Calendar.setup({
			inputField     :    "hq_dd",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_hq_dd",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			onUpdate	   :	hq_date_updated
		});
		
		Calendar.setup({
			inputField     :    "hq_cd",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_hq_cd",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			onUpdate	   :	hq_date_updated
		});
	}
}

function show_vendor_sku(){

	if (!$('po_items_list')) {
		alert('Please select Delivery Branches and click continue to show item list first');
		return;
	}
	
	curtain(true);

	$('sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('sel_vendor_sku');
	center_div('sel_vendor_sku');
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	new Ajax.Updater('sel_vendor_sku','po.php',{
	    method:'post',
	    parameters: Form.serialize(document.f_a)+'&a=ajax_show_vendor_sku',
	    evalScripts: true
	});
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
}

function cancel_vendor_sku(){
	//hidediv('sel_vendor_sku');
	default_curtain_clicked();
}

function do_vendor_sku(){
	hidediv('sel_vendor_sku');
	$('fake_sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Insertion in progress......';
	showdiv('fake_sel_vendor_sku');
	center_div('fake_sel_vendor_sku');
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	/*
	new Ajax.Updater('sel_vendor_sku','po.php',{
	    method:'post',
	    parameters: Form.serialize(document.f_s)+'&a=ajax_add_vendor_sku&vendor_id='+document.f_a.vendor_id.value+'&branch_id='+document.f_a.branch_id.value+'&id='+document.f_a.id.value+Form.serialize(document.f_a),
	    evalScripts: true
	});
	*/
	
	ajax_request("po.php",{
		method:'post',
		parameters: Form.serialize(document.f_s)+'&a=ajax_add_vendor_sku&vendor_id='+document.f_a.vendor_id.value+'&branch_id='+document.f_a.branch_id.value+'&id='+document.f_a.id.value+Form.serialize(document.f_a),
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
			var tb = $('po_items');
			new Insertion.Bottom(tb, m.responseText);
			
			check_dept_can_changed();
		},
		onComplete: function(){
			//hidediv('fake_sel_vendor_sku');
			default_curtain_clicked();
			refresh_foc_annotations();
			recalc_totals();
		}
	});
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
}

function cancel_matrix(){
	hidediv('color_size_matrix');
}

function do_matrix(){
	hidediv('color_size_matrix');
	$('fake_sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Insertion in progress......';
	showdiv('fake_sel_vendor_sku');
	center_div('fake_sel_vendor_sku');
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	new Ajax.Updater('color_size_matrix','po.php',{
	    method:'post',
	    parameters: Form.serialize(document.f_t)+'&a=ajax_add_size_color&vendor_id='+document.f_a.vendor_id.value+'&branch_id='+document.f_a.branch_id.value+'&id='+document.f_a.id.value+Form.serialize(document.f_a),
	    evalScripts: true
	});
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
}

function sku_show_varieties(sku_id){
	showdiv('sel_vendor_sku');
	new Ajax.Updater('sel_vendor_sku','po.php',{
	    method:'post',
	    parameters: 'a=ajax_expand_sku&showheader=1&sku_id='+sku_id,
	    evalScripts: true
	});
}

function toggle_vendor_sku(sku_id,id){
	if ($('xp'+id).innerHTML == "varieties"){
		$('xp'+id).innerHTML = "hide varieties";
		$('cb'+id).disabled = true;
		$('cb'+id).checked = false;
		insert_after = $('li'+id);

		new Ajax.Updater(insert_after,"po.php",{
			method:'post',
			parameters: '&a=ajax_expand_sku&sku_id='+sku_id,
		    evalScripts: true,
	 	    insertion: Insertion.Bottom
		});
  	}
  	else{
  		$('xp'+id).innerHTML = "varieties";
		$('cb'+id).disabled = false;
  		Element.remove('ul'+sku_id);
	}
}

function active_btn(){
	if($('srefresh') != undefined) $('srefresh').style.display='';
	if ($('po_items_list') != undefined){
		$('po_items_list').style.display='none';
		$('submitbtn').style.display='none';
	}
	if($('btn_add_item_by_csv') != undefined){
		$('btn_add_item_by_csv').hide();
	}
}

function refresh_tables(){
	needCheckExit = false;
	document.f_a.a.value = "refresh";
	document.f_a.submit();
}

var sku_autocomplete = undefined;

function reset_sku_autocomplete(focus_search_box){
	if ($('autocomplete_sku')==undefined) return;
	
	var param_str = "a=ajax_search_sku&show_varieties=1&from_po=1&block_list=1&vendor_id="+document.f_a.vendor_id.value+"&type="+getRadioValue(document.f_a.search_type);
	if ($('all_dept') == undefined || !$('all_dept').checked){  // only filter own dept
		param_str += "&dept_id="+document.f_a.department_id.value;
	}
	if(po_only_add_parent_sku){
		param_str += "&is_parent_only=1";
	}
	if(enable_po_agreement){	// got use purchase agreement
		param_str += '&block_got_purchase_agreement=1';
	}

	if (sku_autocomplete != undefined){
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {
			parameters:param_str, 
			paramName: "value",
			afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
		    if(s[0]==0){
		        $('autocomplete_sku').value = '';
                return;
			}
			
			document.f_a.sku_item_id.value =s[0];
			document.f_a.sku_item_code.value = s[1];
		}});
	}
	if(focus_search_box)	$('autocomplete_sku').focus();
}

// add PO item
function add_item(is_foc){
	if (is_foc == undefined){
		is_foc = false;
	}
	if (document.f_a.sku_item_id.value == '' || document.f_a.sku_item_id.value == 0){
	    alert('Please select an SKU to add');
        document.f_a.sku.focus();
        return;
	}
	
	//clear scan barcode data
	if (document.f_a.grn_barcode){
		document.f_a.grn_barcode.value='';
	}
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	var parms;
	if (!is_foc){
		parms = Form.serialize(document.f_a) + '&a=ajax_add_po_row';	
	}
	else{
	    parms = Form.serialize(document.f_a) + '&' + Form.serialize(document.f_foc)+'&a=ajax_add_foc_row';	
	}
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();

	ajax_add(parms, is_foc);
}

function ajax_add(parms, is_foc){
	$('span_loading').update(_loading_);
 	// insert new row
	ajax_request("po.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
            var tb = $('po_items');
            var lbody;
			/*var xml = m.responseXML;
			if (!xml) { alert(m.responseText); return; }			
			        		
			var records = xml.getElementsByTagName("record");
			var tmp_id;			
			$A(records).each(
			    function(r,idx){
					var rowitem = tb.insertRow(-1);	
					tmp_id=xml_getData(r, "id");
					rowitem.id = "titem"+xml_getData(r, "id").strip();
				    rowitem.innerHTML = xml_getData(r,'rowdata');
				}
			);*/

			eval("var json = "+m.responseText);

			for(var tr_key in json){
				if(json[tr_key]['html']){
					new Insertion.Bottom(tb, json[tr_key]['html']);
				}
				
				//if(json[tr_key]['last_po_html'] && json[tr_key]['last_po_html'] != 0 && json[tr_key]['last_po_html'] != undefined) new Insertion.Bottom(tb, json[tr_key]['last_po_html']);
			}

			if (document.f_a.grn_barcode){
				if (document.f_a.grn_barcode.value != ''){
					document.f_a.grn_barcode.select();
					document.f_a.grn_barcode.focus();				
				}
			}else{
				document.f_a.sku.select();
				document.f_a.sku.focus();
			}

			//set the dept selection to disable
			//document.f_a.dept_id.disabled=true;
			check_dept_can_changed();
			refresh_foc_annotations();
			//reset_row_num();
		},		
		onComplete: function(){
			$('span_loading').update('');
			if (is_foc) $('sel_foc_cost').style.display = 'none';
		}
	});	
}

/* No longer used since it is done during add new po items */
/*function add_last_po_row(id){
 	// insert last po row
	ajax_request("po.php",{
		method:'post',
		parameters: Form.serialize(document.f_a) + '&a=ajax_add_last_po_row',
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			if (m.responseText=='0') return;
            var tb = $('po_items');
            var lbody;
			
			//eval("var json = "+m.responseText);

			//for(var tr_key in json){
			//	new Insertion.Bottom(tb, json[tr_key]['html']);
			//}
			var xml = m.responseXML;
			if (!xml) { alert(m.responseText); return; }			
			        		
			var records = xml.getElementsByTagName("record");			
			$A(records).each(
			    function(r,idx){
					var rowitem = tb.insertRow(-1);	
					rowitem.id = "last_po_item"+id.strip();
					//rowitem.style.background="#ffffcc";
				    rowitem.innerHTML = xml_getData(r,'rowdata');
				}
			);
		},		
	});
}*/

// refresh all foc annotations
function refresh_foc_annotations(){
    ajax_request("po.php",{
		method:'post',
		parameters: 'a=ajax_refresh_foc_annotations&po_id='+document.f_a.id.value,
		onComplete: function(m) {
		    var xml = m.responseXML;
		    if (!xml) { alert(m.responseText); return; }
			var records = xml.getElementsByTagName("record");
		 	// update annotations
			$A(records).each(
			    function(r,idx){
					var i = xml_getData(r,'id').strip();
					var t = xml_getData(r,'tag').strip();
					var f = xml_getData(r,'fid').strip();
			        if ($('foc_id'+i) != undefined)
						$('foc_id'+i).innerHTML = f;
			        if ($('foc_annotation'+i) != undefined)
			        	$('foc_annotation'+i).innerHTML = t;
				}
			)
		}
	});
}

function delete_item(id){
	if(allow_sales_order && document.f_a['so_branch_id['+id+']'].value>0 && document.f_a['so_item_id['+id+']'].value){
		alert('This item is link with Sales Order. It cannot be delete.');
		return;
	}
	
	var confirm_str = 'Remove this SKU from PO?';
	var bom_ref_num = '';
	
	if(sku_bom_additional_type){
		if(document.f_a['bom_ref_num['+id+']'] && document.f_a['bom_ref_num['+id+']'].value.trim() != ''){
			bom_ref_num = document.f_a['bom_ref_num['+id+']'].value.trim();
			
			confirm_str += '\nThis SKU is BOM Package SKU, all related SKU will be delete together';
		}
	}
	
 	if (!confirm(confirm_str)) return;
 	bid = document.f_a.branch_id.value;
 	
 	var delete_id_list = [];
 	
 	if(sku_bom_additional_type && bom_ref_num){
 		$$('#po_items_list tr.tr_bom_ref_num-'+bom_ref_num).each(function(tr){
 			var tmp_id = get_po_item_id_by_ele(tr)
 			delete_id_list.push(tmp_id);
 		});
 	}else{
 		delete_id_list.push(id);
 	}
 	
 	var params = {
 		'delete_id_list[]': delete_id_list
 	};
 	
	ajax_request("po.php?a=ajax_delete_po_row&branch_id="+bid,{
		method:'post',
		parameters: params,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			for(var i=0; i<delete_id_list.length; i++){
				var tmp_id = delete_id_list[i];
				
				if($('titem'+tmp_id))	Element.remove('titem'+tmp_id);
	            if($('last_po_item'+tmp_id))	Element.remove('last_po_item'+tmp_id);
			}

            refresh_foc_annotations();
            recalc_totals();
            if(is_under_gst == 1)	calculate_all_gst();
            //Element.show('add_sku_row');
            
            check_dept_can_changed();
    	}
	});
}

function get_item_po_history(id){
	//Position.clone(obj, $('price_history_popup'), {setHeight: false, setWidth:false});
	center_div('price_history_popup');
	Element.show('price_history_popup');
	$('price_history_list_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('price_history_list_popup','ajax_sku_popups.php',{
		    parameters: 'a=sku_po_history&id='+id,
		    evalScripts:true
	});
}

function get_item_sales_trend(id,type){
    var branch_ids = '';
	var po_branch_id = $('po_branch_id').value;

	//check use single branch or multiple branches
	if (type == 'po_branch_parent' || type == 'po_branch_item'){
		if (po_branch_id > 0) branch_ids = po_branch_id;
		else{
			var arr_branches=[];

			if	($$('.branch')){
	            $$('.branch').each(function(ele,obj){
					if (ele.checked) arr_branches.push(ele.value);
				});

				branch_ids = arr_branches.join(',');
			}
		}

		if (!branch_ids) branch_ids = $('branch_id_id').value;
	}
	
	
    if (type == 'all_branch_parent') {
        params = 'a=sku_sales_trend&id='+id+'&use_parent=1';
    }else if(type == 'po_branch_parent') {
        params = 'a=sku_sales_trend&id='+id+'&use_parent=1&branch_ids='+branch_ids;
    }else {
        params = 'a=sku_sales_trend&id='+id+'&branch_ids='+branch_ids;
    }
    
	center_div('price_history_popup');
	Element.show('price_history_popup');
	$('price_history_list_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('price_history_list_popup','ajax_sku_popups.php',{
		    parameters: params,
		    evalScripts:true
	});
}


function sel_foc_cost(){
	if (document.f_a.elements["sku_item_id"].value == '' || document.f_a.elements["sku_item_id"].value == 0){
	    alert('Please select an SKU to add');
        document.f_a.elements["sku"].focus();
        return;
	}

	new Ajax.Updater("sel_foc_cost","po.php",{
		method:'post',
		parameters: 'a=ajax_sel_foc_cost&branch_id='+document.f_a.branch_id.value+'&po_id='+document.f_a.id.value,
	    evalScripts: true,
		onComplete: function(){
			div_center_mouse('sel_foc_cost');
			$('sel_foc_cost').style.display = '';		
		}
	});
}

function cancel_foc(){
    $('sel_foc_cost').style.display = 'none';
}

function save_foc_item(id){
	if (id == 0 || id == undefined){
		add_item(true);	
	}
	else{
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
		
	    ajax_request("po.php",{
			method:'post',
			parameters: Form.serialize(document.f_foc)+'&a=ajax_update_foc_row&id='+id+'&branch_id='+document.f_a.branch_id,
		    evalScripts: true,
			onComplete: function(m) {
			    alert(m.responseText);
			 	$('sel_foc_cost').style.display = 'none';
			},
			onSuccess: function(m) {
				refresh_foc_annotations();
			}
		});	
		
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
	}
}

function get_price_history(element){
	if (document.f_a.elements["sku_item_id"].value == '' || document.f_a.elements["sku_item_id"].value == 0){
	    alert('Please select an SKU and click the history button again');
        document.f_a.elements["sku"].focus();
        return;
	}
	
	var id = document.f_a.elements["sku_item_id"].value;
	Position.clone(element, $('price_history_popup'), {setHeight: false, setWidth:false, offsetTop: -parseInt($('price_history_popup').style.height)});
	Element.show('price_history_popup');
	$('price_history_list_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('price_history_list_popup','ajax_sku_popups.php',{
		parameters: 'a=sku_po_history&id='+id
	});
}

function show_sku_detail(){
    code = document.f_a.elements['sku_item_id'].value;
    if (code=='' || code==0){
		alert('You have not select any item.');
	    return;
    }
    window.open('masterfile_sku.php?a=view&id='+code+'&from_po=1');
}

function edit_foc(id,sku_item_id){
	new Ajax.Updater("sel_foc_cost","po.php",{
		method:'post',
		parameters: '&a=ajax_sel_foc_cost&id='+id+'&branch_id='+document.f_a.branch_id.value+'&po_id='+document.f_a.id.value+'&sid='+sku_item_id,
	    evalScripts: true,
		onComplete: function(){
			div_center_mouse('sel_foc_cost');
			$('sel_foc_cost').style.display = '';
		}
	});
}

function check_a(){
	if (!(document.f_a.vendor_id.value)){
		alert("You must select a vendor");	
		$('autocomplete_vendor').focus();
	    return false;
	}
	if (empty(document.f_a.po_date, "You must enter PO Date")){
	    return false;
	}
	if (empty(document.f_a.dept_id, "Please select Department")){
	    return false;
	}
	if (b_code!='HQ'){
		if (empty(document.f_a.delivery_date, "You must enter Delivery Date")){
		    return false;
		}
		if (empty(document.f_a.cancel_date, "You must enter Cancellation Date")){
		    return false;
		}
	}
	else{
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];
	        if ($("dt_"+i) != undefined && $("dt_"+i).checked){
	            if (empty(document.f_a.elements["delivery_date["+i+"]"], "You must enter Delivery Date")){
				    return false;
				}
				if (empty(document.f_a.elements["cancel_date["+i+"]"], "You must enter Cancellation Date")){
				    return false;
				}
			}
		}
	}
	return true;
}

function do_save(){
	document.f_a.a.value='save';
	document.f_a.target = "";
	if(check_a()){
		
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
		var str_params = Form.serialize(document.f_a)+'&a=check_tmp_item_exists';
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
		
		ajax_request('po.php',{
			method: 'post',
			parameters: str_params,
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
					Form.enable(document.f_a);
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
	if (confirm('Finalise PO and submit for approval?')){
		
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
		var str_params = Form.serialize(document.f_a)+'&a=check_tmp_item_exists';
		if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
		
		ajax_request('po.php',{
			method: 'post',
			parameters: str_params,
			onComplete: function(e){
				if (e.responseText.trim() == 'OK') {
					needCheckExit = false;
					Form.enable(document.f_a);
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

function do_delete(){
	if (confirm('Delete this PO?') && check_login()){
		needCheckExit = false;
		Form.enable(document.f_a);
		document.f_b.a.value='delete';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_cancel(){
	if (confirm('Cancel this PO?') && check_login()){
		needCheckExit = false;
		Form.enable(document.f_a);
		document.f_b.a.value='cancel';
		document.f_b.target = "";
		document.f_b.submit();
	}
}

function do_revoke(){
	if (confirm('Copy details from this PO to a new PO?') && check_login())
	{
{/literal}
		document.location='{$smarty.server.PHP_SELF}?a=revoke&id={$form.id}&branch_id={$form.branch_id}';
{literal}
	}
}

function do_print(){
	if (check_login()) {
        //code
        if (document.f_b.id.value == '' || document.f_b.id.value == 0){
            alert('You must SAVE the PO before it can be printed.');
            exit;
        }
        curtain(true);
        show_print_dialog();
    }
}

function show_print_dialog(){
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	$('print_dialog').style.display = 'none';
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	window.open('{/literal}{$smarty.server.PHP_SELF}{literal}?&a=print&'+Form.serialize(document.fprn));
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
	
	curtain(false);
}

function print_cancel(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function show_related_sku(){
    code = document.f_a.elements['sku_item_id'].value;
    if (code=='' || code==0) {
		alert('You have not select any item.');
	    return;
    }
	$('sel_vendor_sku').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('sel_vendor_sku');
	center_div('sel_vendor_sku');
			
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	new Ajax.Updater('sel_vendor_sku','po.php',{
		    method:'post',
		    parameters: Form.serialize(document.f_a)+'&a=ajax_show_related_sku',
		    evalScripts: true
	});

	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
}


function size_color_form(){
    code = document.f_a.elements['sku_item_id'].value;
    if (code=='' || code==0) {
		alert('You have not select any item.');
	    return;
    }
	$('color_size_matrix').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('color_size_matrix');
	center_div('color_size_matrix');

	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	new Ajax.Updater('color_size_matrix','ajax_sku_popups.php',{
		    method:'post',
		    parameters: Form.serialize(document.f_a)+'&a=ajax_color_size_matrix&type=po',
		    evalScripts: true
	});
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
}

function discount_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => discount of 10 percent\n";
	msg += "10  => discount of "+currency_symbol+"10\n";
	msg += "10%+10 => discount 10%, follow by "+currency_symbol+"10\n";
	msg += "10+10% => discount "+currency_symbol+"10, then discount 10%\n";

	alert(msg);
}

function cost_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => add 10 percent\n";
	msg += "10  => add "+currency_symbol+"10\n";
	msg += "10%+10 => add 10%, follow by "+currency_symbol+"10\n";
	msg += "10+10% => add "+currency_symbol+"10, then 10%\n";

	alert(msg);
}

function set_dept_val(dept_id){
	document.f_a.department_id.value=dept_id;
}

function do_reset(){
    if (check_login()) {
        //code

        document.f_do_reset['reason'].value = '';
        var p = prompt('Enter reason to Reset :');
        if (p==null || p.trim()=='' ) return false;
        document.f_do_reset['reason'].value = p;

        if(!confirm('Are you sure to reset?'))  return false;

        needCheckExit = false;
        document.f_do_reset.submit();
    }
	return false;
}

function vendor_check(){
    hidediv("vendor_checkout");
	new Ajax.Updater("vendor_checkout", "ajax_autocomplete.php?a=ajax_vendor_checkout&vendor_id="+document.f_a.vendor_id.value,{ onComplete:function(){
            if($("vendor_checkout").innerHTML!="") showdiv("vendor_checkout");
        }
    });


}

function add_grn_barcode_item(value){

	if (document.f_a.grn_barcode){
		if (document.f_a.grn_barcode.value == ''){
		    alert('Please scan a barcode to add');
		    return;
		}
	}
	
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	$('grn_barcode').value='';
	
	document.f_a.sku.value="";
	document.f_a.sku_item_id.value="";
	document.f_a.sku_item_code.value="";
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form(1);
	
	var chx_all_dept = $('all_dept');
	if (chx_all_dept != undefined && chx_all_dept.checked) var search_other_department = '1';
	else var search_other_department = '0';
	
	var param_str = Form.serialize(document.f_a)+'&a=ajax_add_po_row&grn_barcode='+value+'&search_other_department='+search_other_department;
	
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	PO_PA_MODULE.disable_form();
	
	ajax_add(param_str, '');
	
}

function get_po_item_id_by_ele(ele){
	var parent_ele = ele;

	while(parent_ele){    // loop parebt until it found the tr contain po item id
	    if(parent_ele.tagName.toLowerCase()=='tr'){
            if($(parent_ele).hasClassName('tr_po_item_row')){    // found the div
				break;  // break the loop
			}
		}
		// still not found, continue to get from parent node
        parent_ele = parent_ele.parentNode;
	}
	
	if(!parent_ele) return 0;

	var po_item_id = $(parent_ele).readAttribute('po_item_id');
	return po_item_id;
}

function qty_changed(inp){
	var po_item_id = get_po_item_id_by_ele(inp);
	var bid = 0;
	
	if ($('q'+po_item_id) == undefined){	// multiple branch
		// get the changed branch id
		bid = $(inp).readAttribute('item_for_bid');
	}
	
	if(sku_bom_additional_type){	// got bom package config
		update_bom_package_qty(po_item_id, bid);	
	}
}

function update_bom_package_qty(po_item_id, bid){
	var order_uom_fraction = float($('ouomf'+po_item_id).value);
	var total_pcs = 0;
	
	if(document.f_a['bom_ref_num['+po_item_id+']']){
		if(document.f_a['bom_ref_num['+po_item_id+']'].value.trim() != ''){	// is bom package
			var bom_ref_num = document.f_a['bom_ref_num['+po_item_id+']'].value.trim();
			var bom_qty_ratio = float(document.f_a['bom_qty_ratio['+po_item_id+']'].value);
			var multiply_ratio = 0;
			var ctn = 0;
			var pcs = 0;
			
			if ($('q'+po_item_id) == undefined){	// multiple branch
				// get the changed branch id
				//bid = $(inp).readAttribute('item_for_bid');
				
				ctn = float($('q'+po_item_id+'['+bid+']').value);
				pcs = float($('ql'+po_item_id+'['+bid+']').value);
			}else{	// single branch
				ctn = float($('q'+po_item_id).value);
				pcs = float($('ql'+po_item_id).value);
			}
			total_pcs = (ctn * order_uom_fraction) + pcs;
			multiply_ratio = float(round(total_pcs / bom_qty_ratio,4));
			
			// get all the same bom ref num tr
			var tr_bom_ref_num_list = $$('#po_items_list tr.tr_bom_ref_num-'+bom_ref_num);
			
			if(int(multiply_ratio) != multiply_ratio){	// not allow decimal
				var group_allow_decimal_qty = true;
			
				// loop to check item can decimal qty or not
				for(var i=0; i<tr_bom_ref_num_list.length; i++){
					// get the row po item id
					var tmp_po_item_id = get_po_item_id_by_ele(tr_bom_ref_num_list[i]);
					var tmp_doc_allow_decimal = int(document.f_a['doc_allow_decimal['+tmp_po_item_id+']'].value);

					if(!tmp_doc_allow_decimal){
						group_allow_decimal_qty = false;
						break;
					}
				}
				
				if(!group_allow_decimal_qty)	multiply_ratio = int(multiply_ratio);	// group cannot hv decimal, make int
			}
			
			// loop to update qty
			for(var i=0; i<tr_bom_ref_num_list.length; i++){
				// get the row po item id
				var tmp_po_item_id = get_po_item_id_by_ele(tr_bom_ref_num_list[i]);
				
				var tmp_bom_qty_ratio = float(document.f_a['bom_qty_ratio['+tmp_po_item_id+']'].value);
				
				var tmp_order_uom_fraction = float($('ouomf'+tmp_po_item_id).value);
				
				var tmp_ctn = 0;
				var tmp_pcs = 0;
				var tmp_total_pcs = tmp_bom_qty_ratio * multiply_ratio;
				 
				if(tmp_order_uom_fraction > 1){
					tmp_ctn = Math.floor(tmp_total_pcs / tmp_order_uom_fraction);
					tmp_pcs = tmp_total_pcs - (tmp_ctn*tmp_order_uom_fraction);
				}else{
					tmp_pcs = tmp_total_pcs;
				}
				
				if ($('q'+tmp_po_item_id) == undefined){	// multiple branch
					$('q'+tmp_po_item_id+'['+bid+']').value = tmp_ctn;
					$('ql'+tmp_po_item_id+'['+bid+']').value = tmp_pcs;
				}else{	// single branch
					if(tmp_order_uom_fraction > 1){
						$('q'+tmp_po_item_id).value = tmp_ctn;
					}else{
						$('q'+tmp_po_item_id).value = '--';
					}
					
					$('ql'+tmp_po_item_id).value = tmp_pcs;
				}
				
				// recal row
				if(tmp_po_item_id != po_item_id) row_recalc(tmp_po_item_id);
			}
		}
	}
}

function show_sku_grn_info() {
	alert('For an item to appear in the list, it must have receive GRN before (for this vendor and department)');
}

confirmExit = function(e) {
	if(!e) e = window.event;
	if(needCheckExit){
		//e.cancelBubble is supported by IE - this will kill the bubbling process.
		/*e.cancelBubble = true;
		e.returnValue = 'Are You sure you want to leave at this time? Sales will be in-correct if finalize does not fully complete. '; //This is displayed on the dialog
	
		//e.stopPropagation works in Firefox.
		if (e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		}*/
		
		return 'Data had not being saved.';
	}
}

var PO_PA_MODULE = {
	allow_change_header: false,
	initialize: function(){
		if(PO_AGREEMENT_OPEN_BUY){
			this.allow_change_header = true;	// got this privilege default can edit
			
			// check whether got use purchase agreement item
			var pa_branch_id_list = $$('#tbl_items input.pa_branch_id');
			if(pa_branch_id_list){
				for(var i=0; i<pa_branch_id_list.length; i++){
					if(int(pa_branch_id_list[i].value)>0){	// this rule got use pa
						this.allow_change_header = false;
					}else{
					
					}
				}
			}
		}	
		
		
		if(!this.allow_change_header)	this.disable_form();
	},
	disable_form: function(enable){
		if(enable){
			Form.enable(document.f_a);	// enable form
		}else{
			if($('img_po_dt') != undefined) $('img_po_dt').hide();	// cannot change PO Date
			
			Form.disable(document.f_a);
			
			$$('#user_select input').each(function(ele){
				ele.disabled = false;	// enable all user selection
			});
			
			// check whether got use purchase agreement item
			var pa_branch_id_list = $$('#tbl_items input.pa_branch_id');
			
			if(pa_branch_id_list){
				for(var i=0; i<pa_branch_id_list.length; i++){
                    var item_id = $(pa_branch_id_list[i]).readAttribute('item_id');
					if(int(pa_branch_id_list[i].value)>0){	// this rule got use pa
						
					}else{

						disable_sub_ele($('titem'+item_id), true);
					}
                    $('rem'+item_id).disabled=false;
                    $('rem2'+item_id).disabled=false;
				}
			}
		}

		$$('.uom_disabled').each(function(ele){
			ele.disabled = true;	// enable all user selection
		});

		disable_sub_ele($('tbl_footer'), true);	// enable user to key in discount and remark at bottom
		
		// enable show sku of vendor button
		//$('inp_show_vendor_sku').disabled = false;
	}
}

function delivery_date_changed(bid, obj){
	var delivery_date = obj.value;
	var exp_delivery_date = delivery_date.split("-");
	var tmp_cancel_date = new Date(exp_delivery_date[0], exp_delivery_date[1]-1, exp_delivery_date[2]);
	var addon_times = int(po_agreement_cancellation_days) * 3600000 * 24;
	tmp_cancel_date.setTime(tmp_cancel_date.getTime()+addon_times);
	var new_cancel_date = tmp_cancel_date.getFullYear()+'-'+('0'+(tmp_cancel_date.getMonth()+1)).slice(-2)+'-'+('0'+tmp_cancel_date.getDate()).slice(-2);
//  var exp_delivery_date = delivery_date.split("/");
//	var tmp_cancel_date = new Date(exp_delivery_date[2], exp_delivery_date[1]-1, exp_delivery_date[0]);
//	var addon_times = int(po_agreement_cancellation_days) * 3600000 * 24;
//	tmp_cancel_date.setTime(tmp_cancel_date.getTime()+addon_times);
//	var new_cancel_date = tmp_cancel_date.getDate()+'/'+(tmp_cancel_date.getMonth()+1)+'/'+tmp_cancel_date.getFullYear();
	if(document.f_a['cancel_date['+bid+']'] != undefined) document.f_a['cancel_date['+bid+']'].value = new_cancel_date;
	else document.f_a['cancel_date'].value = new_cancel_date;
}

function curtain_clicked(){
	$('sel_vendor_sku').hide();
	$('fake_sel_vendor_sku').hide();
}

function check_dept_can_changed(){
	if(enable_po_agreement && !PO_PA_MODULE.allow_change_header)	return;	// not allow to change header at all.
	
	var tr_po_item_row_list = $$('#po_items_list tr.tr_po_item_row');
	var can_change = true;
	if(tr_po_item_row_list.length>0){
		can_change = false;
		$('category_sales_trend_td').show();
	}else{
		$('category_sales_trend_td').hide();
	}
	document.f_a.dept_id.disabled = !can_change;
}

function hq_purchase_clicked(obj){
	if(obj != undefined) po_option = obj.value;

	if(po_option == 3){
		$('hq_delivery_date').style.display = "";
		$('hq_cancel_date').style.display = "";
		$('hq_partial_delivery').style.display = "";
		
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];
			if ($("dt_"+i) != undefined && $("dt_"+i).checked){
				document.f_a["delivery_vendor["+i+"]"].value = 0;
				document.f_a["delivery_vendor_name["+i+"]"].readOnly = true;
				document.f_a["delivery_vendor_name["+i+"]"].value = "-same as above-";
				if(document.f_a["hq_partial_delivery"].checked == true) document.f_a["partial_delivery["+i+"]"].checked = true;
				else document.f_a["partial_delivery["+i+"]"].checked = false;
				document.f_a["partial_delivery["+i+"]"].readOnly = true;
				document.f_a["dt1["+i+"]"].readOnly = true;
				document.f_a["dt1["+i+"]"].value = document.f_a["hq_delivery_date"].value;
				document.f_a["dt2["+i+"]"].readOnly = true;
				document.f_a["dt2["+i+"]"].value = document.f_a["hq_cancel_date"].value;
				$("t_dt1["+i+"]").style.display = "none";
				$("t_dt2["+i+"]").style.display = "none";
				
			}
		}
	}else{
		$('hq_delivery_date').style.display = "none";
		$('hq_cancel_date').style.display = "none";
		$('hq_partial_delivery').style.display = "none";
		
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];
			if ($("dt_"+i) != undefined && $("dt_"+i).checked){
				document.f_a["delivery_vendor_name["+i+"]"].readOnly = false;
				document.f_a["partial_delivery["+i+"]"].readOnly = false;
				document.f_a["dt1["+i+"]"].readOnly = false;
				document.f_a["dt2["+i+"]"].readOnly = false;
				$("t_dt1["+i+"]").style.display = "";
				$("t_dt2["+i+"]").style.display = "";
			}
		}
	}
	if(obj != undefined) po_option = obj.value;
}

function hq_date_updated(cal){
	//alert(cal.params.inputField.value);
	var cal_name = cal.params.inputField.name;
	var cal_val = cal.params.inputField.value;

	for (var b=0;b<branch_arr.length;b++){
		var i = branch_arr[b];
		if ($("dt_"+i) != undefined && $("dt_"+i).checked){
			if (cal_name == "hq_delivery_date"){
				document.f_a["delivery_date["+i+"]"].value = cal_val;
				if(po_agreement_cancellation_days){
					var exp_delivery_date = cal_val.split("-");
					var tmp_cancel_date = new Date(exp_delivery_date[0], exp_delivery_date[1]-1, exp_delivery_date[2]);
					var addon_times = int(po_agreement_cancellation_days) * 3600000 * 24;
					tmp_cancel_date.setTime(tmp_cancel_date.getTime()+addon_times);
					var new_cancel_date = tmp_cancel_date.getFullYear()+'-'+('0'+(tmp_cancel_date.getMonth()+1)).slice(-2)+'-'+('0'+tmp_cancel_date.getDate()).slice(-2);
//                  var exp_delivery_date = cal_val.split("/");
//					var tmp_cancel_date = new Date(exp_delivery_date[2], exp_delivery_date[1]-1, exp_delivery_date[0]);
//					var addon_times = int(po_agreement_cancellation_days) * 3600000 * 24;
//					tmp_cancel_date.setTime(tmp_cancel_date.getTime()+addon_times);
//					var new_cancel_date = tmp_cancel_date.getDate()+'/'+(tmp_cancel_date.getMonth()+1)+'/'+tmp_cancel_date.getFullYear();
					document.f_a['cancel_date['+i+']'].value = new_cancel_date;
					document.f_a['hq_cancel_date'].value = new_cancel_date;
				}
			}else{
				document.f_a["cancel_date["+i+"]"].value = cal_val;
			}
		}
	}
}

function partial_delivery_clicked(obj){
	if(po_option == 3) return false;
	else return true;
}

function hq_partial_delivery_clicked(obj){
	for (var b=0;b<branch_arr.length;b++){
		var i = branch_arr[b];
		if ($("dt_"+i) != undefined && $("dt_"+i).checked){
			if (obj.checked == true){
				document.f_a["partial_delivery["+i+"]"].checked = true;
			}else{
				document.f_a["partial_delivery["+i+"]"].checked = false;
			}
		}
	}
}

function calculate_all_gst(){
	/*$('po_items_list').getElementsByClassName("gst_label").each(function(inp){
		if(is_gst_active == 0){
			inp.style.display = "none";
		}else{
			inp.style.display = "";
		}
	});*/


	// do looping for all items
	if($('po_items_list')){
		var ttl_gst_amt = 0;
		$('po_items_list').getElementsByClassName("item_amt_incl_gst").each(function(inp){
			ttl_gst_amt += float(inp.value);
		});
		
		$('total_gst_amount').update(round2(ttl_gst_amt));
		// po GST total	
		var a = float(ttl_gst_amt);
		a = parse_formula(a, $('misc_cost'), true);
		a = parse_formula(a, $('sdiscount'), false);
		var b = a; // b is vendor's PO amount, skip calculation from remark#2
		a = parse_formula(a, $('rdiscount'), false);
		a = parse_formula(a, $('ddiscount'), false);
		a += float($('transport_cost').value);
		b += float($('transport_cost').value);
		$('final_gst_amount').innerHTML = round2(a);
		$('final_gst_amount2').innerHTML = round2(b);

		var ts = 0;
		//var ts = float($('total_sell').innerHTML);
		if(branch_is_under_gst == 1)	ts = float(document.f_a['total_gst_sell'].value);
		var pf = ts - a;
		$('final_gst_profit').style.color = (pf<=0) ? '#f00' : '#000';
		$('final_gst_profit').innerHTML = round2(pf);
		$('final_gst_margin').style.color = (pf<=0) ? '#f00' : '#000';
		$('final_gst_margin').innerHTML = round2(pf/ts*100) + '%';
		
		if(!branch_is_under_gst){
			$('final_gst_profit').hide();
			$('final_gst_margin').hide();
		}
	}

}

function calculate_gst(id, obj){
	if(!is_under_gst)	return;
	
	// sum up the amount to get the discount + tax unit order price
	var q1=0, q2=0, qty=0, foc=0, ctn=0;
	if ($('q'+id) == undefined){
		for (var b=0;b<branch_arr.length;b++){
			var i = branch_arr[b];
		    if ($('q'+id+'['+i+']') != undefined){
				 q1 = float(round($('q'+id+'['+i+']').value*$('ouomf'+id).value, global_qty_decimal_points)) + float($('ql'+id+'['+i+']').value);
				 q1 = float(round(q1, global_qty_decimal_points));
				 qty += q1;
				 q2 = float(round($('f'+id+'['+i+']').value*$('ouomf'+id).value, global_qty_decimal_points)) + float($('fl'+id+'['+i+']').value);
				 q2 = float(round(q2, global_qty_decimal_points));
				 foc += q2;
				 ctn += float($('q'+id+'['+i+']').value)+float($('f'+id+'['+i+']').value);
			}
		}
	}else{
		qty = float($('q'+id).value)*$('ouomf'+id).value + float($('ql'+id).value);
		qty = float(round(qty, global_qty_decimal_points));
		foc = float($('f'+id).value)*$('ouomf'+id).value + float($('fl'+id).value);
		foc = float(round(foc, global_qty_decimal_points));
		ctn = float($('q'+id).value)+float($('f'+id).value);
		ctn = float(round(ctn, global_qty_decimal_points));
	}

	var amount = float($('op'+id).value*qty)/float($('ouomf'+id).value);
	if ($('tax'+id).value != ''){
		$('tax'+id).value = float($('tax'+id).value);
		amount *= (float($('tax'+id).value)+100)/100;
	}

	amount = parse_formula(amount, $('disc'+id), false);
	amount = round(amount, 2);
	if ($('is_foc'+id).value != 1){
		var gst_rate = document.f_a['cost_gst_rate['+id+']'].value;
		/*var order_price = round(amount/(foc+qty), global_cost_decimal_points);
		var gst_amt=float(order_price) * float(gst_rate) / 100;
		gst_amt = round(gst_amt, global_cost_decimal_points);
		var row_gst=round2(gst_amt * (foc+qty));
		var row_gst_amt=float(amount)+float(row_gst);*/
		//var ouomf = float($('ouomf'+id).value);
		//var order_price = float($('op'+id).value)/ouomf;
		//var item_gst = order_price * float(gst_rate) / 100;
		var row_gst_amt= float(round2(amount * gst_rate / 100));
		var amt_incl_gst = float(amount)+row_gst_amt;
		var item_gst_incl_foc = amount/(foc+qty)*float(gst_rate) / 100;
		
		
		$('cost_gst_rate_amt'+id).update(round(item_gst_incl_foc, global_cost_decimal_points));
		$('ttl_cost_gst_amt'+id).update(round2(amt_incl_gst));	
		document.f_a['item_amt_incl_gst['+id+']'].value = amt_incl_gst;
	}
	
	calculate_all_gst();
}

function calculate_selling_gst(id, bid){
	var gst_rate = float(document.f_a['selling_gst_rate['+id+']'].value);
	var inclusive_tax = document.f_a['inclusive_tax['+id+']'].value;
	
	// get selling price

	if (inclusive_tax=='no') {
		if(bid > 0) var selling_price = float(document.f_a['selling_price_allocation['+id+']['+bid+']'].value);
		else var selling_price = float(document.f_a['selling_price['+id+']'].value);

		//var gst_selling_price=float(selling_price*100)/(100+float(gst_rate));
		//var gst_amt=float(gst_selling_price) * float(gst_rate) / 100;
		
		var gst_amt=float(selling_price) * float(gst_rate) / 100;
		var gst_amt=round(gst_amt, 2);
		var gst_selling_price=float(selling_price)+float(gst_amt);

		if(bid > 0){
			document.f_a['gst_selling_price_allocation['+id+']['+bid+']'].value = round(gst_selling_price, 2);
		}else{
			document.f_a['gst_selling_price['+id+']'].value = round(gst_selling_price, 2);
			document.f_a['tmp_gst_selling_price['+id+']'].value = round(selling_price, 2);
		}
		
	}else{
		if(bid > 0) var gst_selling_price = float(document.f_a['gst_selling_price_allocation['+id+']['+bid+']'].value);
		else var gst_selling_price = float(document.f_a['gst_selling_price['+id+']'].value);
		
		/*var gst_amt=float(gst_selling_price) * float(gst_rate) / 100;
		var gst_amt=round(gst_amt, 2);
		var selling_price=float(gst_selling_price)+float(gst_amt);*/
		
		var selling_price=float(gst_selling_price*100)/(100+float(gst_rate));
		var gst_amt=float(selling_price) * float(gst_rate) / 100;

		if(bid > 0) document.f_a['selling_price_allocation['+id+']['+bid+']'].value = round(selling_price, 2);
		else document.f_a['selling_price['+id+']'].value = round(selling_price, 2);
	}
	
	row_recalc(id);
}

/*function vendor_changed(vd_info){
	if($('po_items_list')){
		$('po_items_list').getElementsByClassName("gst_field").each(function(inp){
			var po_item_id = $(inp).readAttribute('item_id');
			if(vd_info['gst_register'] == 1){ // enable all gst dropdown list for user to maintain
				inp.onfocus = function(){ };
				inp.options[0].style.display = "none";
				if(document.f_a['item_gst_id['+po_item_id+']'].value > 0) var item_gst_id = document.f_a['item_gst_id['+po_item_id+']'].value;
				else var item_gst_id = vd_info['gst_register'];
				inp.value = item_gst_id;
			}else{
				inp.options[0].style.display = "";
				inp.onfocus = function(){ this.blur(); };
				inp.value = "";
			}
		});
		
		calculate_all_gst();
	}
}

function search_vendor_gst_info(){
	/*var vendor_info = ajax_search_vendor_gst_info(document.f_a.vendor_id.value);
	if(vendor_info['gst_register'] != undefined){
		vendor_changed(vendor_info);
	}else{
		alert(vendor_info);
	}
}*/

// function when user change gst dropdown selection
function on_item_gst_changed(sel, item_id, type){
	document.f_a[type+"_gst_id["+item_id+"]"].value = "";
	document.f_a[type+"_gst_code["+item_id+"]"].value = "";
	document.f_a[type+"_gst_rate["+item_id+"]"].value = "";
	
	if(sel.selectedIndex >= 0){
		// got select
		var opt = sel.options[sel.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		
		document.f_a[type+"_gst_id["+item_id+"]"].value = gst_id;
		document.f_a[type+"_gst_code["+item_id+"]"].value = gst_code;
		document.f_a[type+"_gst_rate["+item_id+"]"].value = gst_rate;
	}
	
	// recalculate row
	row_recalc(item_id); 
}

function recalculate_all_items(){
	var po_item_row_list = $$('#po_items tr.tr_po_item_row');
	for(var i=0,len=po_item_row_list.length; i<len; i++){
		var po_item_row = po_item_row_list[i];
		var po_item_id = $(po_item_row).readAttribute('po_item_id');
		
		// calculate items but no need calculate total
		row_recalc(po_item_id, true);
	}
	if(po_item_row_list.length > 0){
		recalc_totals();
		if(is_under_gst == 1)	calculate_all_gst();
	}
}

function item_cost_changed(item_id){
	// get the input
	var inp = $('op'+item_id);
	
	// round value
	inp.value = round(inp.value, global_cost_decimal_points);
	
	// check positive only
	positive_check(inp);
	
	
	// calculate gst
	if(is_under_gst){
		calculate_gst(item_id, inp, 'cost');
	}	
	
	// recalculate item
	row_recalc(item_id);
}

function po_date_changed(){
	var inp = document.f_a['po_date'];
	upper_lower_limit(inp); 
	if(enable_gst){
		active_btn();
	}
	
	if(got_foreign_currency){
		reload_currency_rate();
	}
}

function currency_code_changed(){
	var currency_code = document.f_a['tmp_currency_code'].value.trim();
	document.f_a['currency_code'].value = currency_code;
	
	
	// Show / Hide Currency Rate input
	if(currency_code != ''){
		// other currency
		$('tr_currency_rate').show();
	}else{
		// base currency
		$('tr_currency_rate').hide();
	}
	
	// Reload Currency Rate
	reload_currency_rate();
	
	if(is_under_gst)	active_btn();
}

function reload_currency_rate(){
	var currency_code = document.f_a['currency_code'].value;
	var po_date = document.f_a['po_date'].value;
	
	if(currency_code == ''){	// base currency
		document.f_a['currency_rate'].value = 1;
		return;
	}
	
	// Invalid PO Date
	if(!po_date){
		alert('Invalid PO Date');
		document.f_a['currency_rate'].value = '';
	}
	
	$('span_currency_rate_loading').update(_loading_);
	
	new Ajax.Request('po.php', {
		method: 'post',
		parameters: {
			a: 'ajax_reload_currency_rate',
			currency_code: currency_code,
			po_date: po_date
		},
		onComplete: function(msg){
			$('span_currency_rate_loading').update('');
			
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					var rate = float(ret['rate']);
					if(is_new_id(document.f_a['id'].value)){
						// Only new PO can change currency rate
						if(rate>0){
							document.f_a['currency_rate'].value = ret['rate'];
						}else{
							err_msg = 'Currency Rate is zero, please check currency rate table with your admin.';
							document.f_a['currency_rate'].value = 0;
						}
					}else{
						// Existing PO not allow to change rate
						var current_rate = float(document.f_a['currency_rate'].value);
						var prompt_diff = false;
						
						if(rate != current_rate){
							prompt_diff = true;
							// Check whether is allow to change
							var can_change_currency_rate = int(document.f_a['can_change_currency_rate'].value);
							if(can_change_currency_rate){
								// can change
								var confirm_change = confirm('Current Rate ['+current_rate+'], change to New Rate ['+rate+'] ?');
								if(confirm_change){
									document.f_a['currency_rate'].value = rate;
									prompt_diff = false;
								}
							}
						}
						
						$('span_pending_currency_rate').update('');
						document.f_a['pending_currency_rate'].value = '-1';
						
						if(prompt_diff){
							// not allow to change
							document.f_a['pending_currency_rate'].value = rate;
							$('span_pending_currency_rate').update('New Rate: '+rate);
						
							alert('The new Currency Rate should be ['+rate+'], but current PO is ['+current_rate+']');
						}
					}
					
					if(!err_msg)	return;
				}else{  // save failed
					if(ret['err'])	err_msg = ret['err'];
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

function change_currency_rate(){
	PRIV_CHECK.check_privilege('PO_CHANGE_CURRENCY_RATE', change_currency_rate_callback);
}

function change_currency_rate_callback(override_by_user_id){
	var new_rate = prompt("Please key in new currency rate.");
	if(new_rate == undefined)	return false;
	
	new_rate = float(new_rate);
	if(new_rate<=0){
		alert('Currency Rate must more than 0.');
		return;
	}
	
	document.f_a['currency_rate'].value = new_rate;
	document.f_a['currency_rate_override_by_user_id'].value = override_by_user_id;
}

function view_change_currency_rate_history(){
	curtain(true);
	center_div($('div_change_currency_rate_history_dialog').show());
}

function qty_keypressed(obj, e) {
	var e = (typeof event != 'undefined') ? window.event : e; // IE : Moz 
	if (e.keyCode == 13) {
		var qty_ele = $$('#po_items input.qty_fields');
		for (var i = 0; i < qty_ele.length; i++) {
			var q = i + 1; // next field
			if (qty_ele[q] != undefined && obj == qty_ele[i]){
				var pass = false;
				do{ // get the next qty field which is not readonly and disable
					if(qty_ele[q].disabled == true || qty_ele[q].readOnly == true){
						q = float(q) + 1;
					}else{
						pass = true;
					}
				}while(pass!=true);
				qty_ele[q].focus();
				qty_ele[q].select();
				break;
			}
		}
		return false;
	}
}

function show_upload_csv_popup(){
	center_div('wait_popup');
	Element.show('wait_popup');
	
	var chx_all_dept = $('all_dept');
	if (chx_all_dept != undefined && chx_all_dept.checked) var search_other_department = '1';
	else var search_other_department = '0';
	
	document.f_a['a'].value = 'ajax_open_add_item_by_csv_popup';
	var form_data = this.f_a.serialize()+'&search_other_department='+search_other_department;
	
	new Ajax.Request(phpself, {
		method: 'post',
		parameters: form_data,
		onComplete: function(msg){			    
			// insert the html at the div bottom
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

{* Div POPUP *}
<div id="wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align=center>
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>

{* Change Currency Rate History Popup *}
{if $form.currency_rate_history}
	<div id="div_change_currency_rate_history_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:500px;height:auto;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_change_currency_rate_history_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Change Currency Rate History</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_change_currency_rate_history_dialog_content" style="padding:2px;background-color:#fff;max-height:500px;overflow-y:auto;">
			<table width="100%" class="report_table">
				<tr class="header">
					<th>Old Rate</th>
					<th>New Rate</th>
					<th>By User</th>
					<th>Override By</th>
					<th>Timestamp</th>
				</tr>
				{foreach from=$form.currency_rate_history item=rate_history}
					<tr>
						<td class="r">{$rate_history.old_rate}</td>
						<td class="r">{$rate_history.new_rate}</td>
						<td align="center">{$rate_history.by_u}</td>
						<td align="center">{if $rate_history.override_by_user_id ne $rate_history.user_id}{$rate_history.override_by_u}{else}-{/if}</td>
						<td align="center">{$rate_history.timestamp}</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
{/if}

{* Add item by csv Popup *}
<div id="div_upload_csv" class="curtain_popup" style="z-index:10000;max-width:600px;height:550px;display:none;max-height:550px;overflow:auto;min-width:550px;">
	{include file='po.upload_csv.tpl'}
</div>

{include file='check_privilege_override.tpl'}

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
</form>

<div id=item_context_menu style="display:none;position:absolute;z-index:100;">
<ul id=ul_menu class=contextmenu>
	{if $allow_edit}
	<li><a href="javascript:void(0)" onclick="hide_context_menu();delete_item(context_info.id)"><img src=/ui/icons/delete.png class=clickable align=absmiddle> Delete Item form PO</a></li>
	<li><a href="javascript:void(0)" onclick="hide_context_menu();showdiv('note'+context_info.id);$('rem'+context_info.id).focus();"><img src=/ui/note16.png align=absmiddle> Add Remark</a></li>
	<li><a href="javascript:void(0)" onclick="hide_context_menu();showdiv('note2'+context_info.id);$('rem2'+context_info.id).focus();"><img src=/ui/inote16.png align=absmiddle> Add Internal Remark</a></li>
	<li id=item_context_menu_foc><a href="javascript:void(0)" onclick="hide_context_menu();edit_foc(context_info.id,context_info.sku_item_id)"><img src=/ui/icons/book_edit.png align=absmiddle>  Edit FOC costing</a></li>
	{/if}
	<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_po_history(context_info.sku_item_id)"><img src=/ui/icons/clock.png align=absmiddle> PO Cost History</a></li>
    <li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id,'po_branch_parent')"><img src=/ui/icons/chart_bar.png align=absmiddle> Parent Sales Trend (PO branches only)</a></li>
	<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id,'all_branch_parent')"><img src=/ui/icons/chart_bar.png align=absmiddle> Parent Sales Trend (All branches)</a></li>
    <li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id,'po_branch_item')"><img src=/ui/icons/chart_bar.png align=absmiddle> Item Sales Trend (PO branches only)</a></li>
	<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_sales_trend(context_info.sku_item_id,'all_branch_item')"><img src=/ui/icons/chart_bar.png align=absmiddle> Item Sales Trend (All branches)</a></li>
</ul>
</div>

<div id="price_history_popup" style="display:none;position:absolute;width:725px;height:300px;background:#fff;border:1px solid #000;padding:5px;z-index:100;">
	<div id="price_history_list_popup" style="width:725px;height:270px;overflow:auto;"></div>
	<div align=center style="padding-top:5px">
	<input class="btn btn-warning" type=button onclick="Element.hide(this.parentNode.parentNode)" value="Close">
	</div>
</div>

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;">
<form name=fprn>
<table border="0" width="100%">
	<tr>
		<td rowspan="2"><img src="ui/print64.png" hspace=10 align=left></td>
		<td><h3>Print Options</h3></td>
	</tr>
	
	<tr>
		<td>
			<input type=checkbox name="print_vendor_copy" checked> Vendor's Copy<Br>
			<input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)<br>
			{if $form.approved eq '1' && !$config.po_disable_grn_perform_report}
				<input type=checkbox name="print_grn_perform_report" checked> GRN Performance Report<br>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type=button value="Print" onclick="print_ok()">
			<input type=button value="Cancel" onclick="print_cancel()">
		</td>
	</tr>
</table>
<input type=hidden name=load value=1>
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=branch_id value="{$form.branch_id}">
</form>
</div>

<iframe style="visibility:hidden" width=1 height=1 name=ifprint id=ifprint></iframe>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Purchase Order {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}
			</h4>
			
{if $form.id<$time_value}
			<h5 class="content-title mb-0 my-auto ml-4 text-primary">
				Status:
{if $form.status == 1}
	{if $form.approved}
		Fully Approved  
		{if $form.active} 
		(PO No: {$form.po_no})
		{else}
		(Branches PO: 
		{foreach from=$hq_po_list item=pn name=pn}
		{if $smarty.foreach.pn.iteration>1} ,{/if}
		<a href="/po.php?a=view&id={$pn.po_id}&branch_id={$pn.branch_id}" target="_blank">
		{$pn.po_no} {if $pn.b_name}({$pn.b_name}){/if}
		</a>
		{/foreach}
		)
		{/if}
	{else}
	In Approval Cycle
	{/if}
{elseif $form.status == 5}
	Cancelled / Deleted
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft Purchase Order
{/if}
{/if}

{if $form.revoke_id}(This PO has been revoked to PO ID#{$form.revoke_id} <a href="?a=open&id={$form.revoke_id}&branch_id={$form.branch_id}"><img src=ui/view.png border=0 title="Click here to open the new PO" align=absmiddle></a>){/if}
			</h5>
			<span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{* if $approval_history}
<br>
<div class="stdframe" style="background:#fff">
<h4>Approval History</h4>
{section name=i loop=$approval_history}
<p>
{if $approval_history[i].status==0}
	<img src=ui/notify_sku_reject.png width=16 height=16 align=absmiddle title="Reset">
{elseif $approval_history[i].status==1}
	<img src=ui/approved.png width=16 height=16>
{elseif $approval_history[i].status==2}
	<img src=ui/rejected.png width=16 height=16>
{else}
	<img src=ui/terminated.png width=16 height=16>
{/if}
{$approval_history[i].timestamp} by {$approval_history[i].u}<br>
{$approval_history[i].log}
</p>
{/section}
</div>
{/if *}

{include file=approval_history.tpl}
<br>

<form name="f_a" method="post" ENCTYPE="multipart/form-data">
<input type="hidden" name="a" value="save">
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
<input type="hidden" name="po_amount" value="{$form.po_amount}">
<input type="hidden" name="id" value="{$form.id}">
<input type="hidden" name="user_id" value="{$form.user_id|default:$sessioninfo.id}">
<input type="hidden" id="branch_id_id" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}">
<input type="hidden" name="is_request" value="{$form.is_request}">
<input type="hidden" name="active" value="{$form.active}">
<input type="hidden" id="po_branch_id" name="po_branch_id" value="{$form.po_branch_id}">
<input type="hidden" name="po_branch" value="{$form.po_branch}">
<input type="hidden" name="readonly" value="{$readonly}">
<input type="hidden" name="po_create_type" value="{$form.po_create_type}" />
<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}">
<input type="hidden" name="branch_is_under_gst" value="{$form.branch_is_under_gst}">
<input type="hidden" name="total_gst_sell" value="0">

{if $config.foreign_currency}
	<input type="hidden" name="can_change_currency_rate" value="{$form.can_change_currency_rate|default:0}">
{/if}
<div class="card mx-3">
	<div class="card-body">
		
		<h4>General Information</h4>

	{if $errm.top}
	<div class="alert alert-danger mx-3 rounded">
		<div id=err><div class=errmsg><ul>
			{foreach from=$errm.top item=e}
			<div class="alert alert-danger rounded">
				<li> {$e} </li>
			</div>
			{/foreach}
			</ul></div></div>
	</div>
	{/if}
	
	<table border="0" cellspacing="0" cellpadding="4" width="100%">
	<tr>
		<td><b class="form-label">Vendor<span class="text-danger" title="Required Field"> *</span></b></td>
		<td colspan=3>
			<div class="form-inline">
				<input class="form-control" name="vendor_id" size=1 value="{$form.vendor_id}" readonly>
			&nbsp;<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$form.vendor}" size=50>
			{if !$form.approval_screen}
			<div id="autocomplete_vendor_choices" class="autocomplete"></div>
			
		&nbsp;&nbsp;	<input class="btn btn-primary fs-08" type=button value="Show SKU of this Vendor" onclick="show_vendor_sku()" id="inp_show_vendor_sku" />
			&nbsp;&nbsp;
			<b>[<a href="javascript:void(show_sku_grn_info());">?</a>]</b>
			{/if}
			<div id="vendor_checkout" style="Display:none;"></div>
			</div>
		</td>
		
		{if $config.foreign_currency}
			<td valign="top" align="right" rowspan="3">
				<table>
					<tr>
						<td width="100">
							<b>Currency</b> 
							[<a href="javascript:void(alert('{$LANG.PO_CURRENCY_CODE_NOTIFY|escape:javascript}'))">?</a>]
						</td>
						<td>
							<input type="hidden" name="currency_code" value="{$form.currency_code}" />
							<select name="tmp_currency_code" {if !$can_change_currency_code}disabled{/if} onChange="currency_code_changed();" style="width:150px;">
								<option value="">Base Currency</option>
								<optgroup label="Foreign Currency">
									{foreach from=$foreignCurrencyCodeList item=code}
										<option value="{$code}" {if $form.currency_code eq $code}selected {/if}>{$code}</option>
									{/foreach}
								</optgroup>
							</select>
						</td>
					</tr>
					<tr id="tr_currency_rate" {if $form.currency_code eq ''}style="display:none;"{/if}>
						<td valign="top" nowrap>
							<b>Exchange Rate</b>
							{if $form.currency_rate_history}
								<img src="/ui/icons/script.png" align="absmiddle" class="clickable" title="View Change Currency Rate History" onClick="view_change_currency_rate_history();" />
							{/if}
						</td>
						<td valign="top">
							<input type="hidden" name="pending_currency_rate" value="{$form.pending_currency_rate|default:-1}" />
							<input type="hidden" name="currency_rate_override_by_user_id" value="{$form.currency_rate_override_by_user_id}" />
							
							{if $form.can_change_currency_rate}
								<img src="/ui/ed.png" title="Change Rate" class="clickable" onClick="change_currency_rate();" />
							{/if}
							<input type="text" name="currency_rate" size="7" value="{$form.currency_rate|default:1}" readonly class="freadonly" />
							<span id="span_currency_rate_loading"></span>
							<br />
							<span id="span_pending_currency_rate">
								{if $form.pending_currency_rate>=0}New Rate: {$form.pending_currency_rate}{/if}
							</span>
						</td>
					</tr>
				</table>
			</td>
		{/if}
	</tr>
	
	<tr>
		<td><b class="form-label">Department<span class="text-danger" title="Required Field"> *</span></b></td>
		<td>
			<select class="form-control" name="dept_id" onchange="set_dept_val(this.value);reset_sku_autocomplete();refresh_tables();" {if $disabled_dept}disabled{/if}>
					{* if $sessioninfo.branch_id neq 1}
					<option value="">-- Please Select --</option>
					{/if *}
				{section name=i loop=$dept}
					<option value={$dept[i].id} {if $form.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
				{/section}
			</select>
			<input type=hidden name=department_id value="{$form.department_id|default:$dept[0].id}">
		</td>
		<td>&nbsp;</td>
		<td id="category_sales_trend_td" {if !$disabled_dept}style="display:none;"{/if} colspan="2">
			<span><b>Category Sales Trend</b> {if $form.branch_id eq 1}(PO branch only){/if}</span>
			<table class="category_sales_trend" style="border:1px solid black;">
				<tr>
					<td align="center"><span style="border:1px solid #ccc;background:#e6e6e6;padding:0px 3px;"><b>1M</b></span></td>
					<td align="center"><span style="border:1px solid #ccc;background:#e6e6e6;padding:0px 3px;"><b>3M</b></span></td>
					<td align="center"><span style="border:1px solid #ccc;background:#e6e6e6;padding:0px 3px;"><b>6M</b></span></td>
					<td align="center"><span style="border:1px solid #ccc;background:#e6e6e6;padding:0px 3px;"><b>12M</b></span></td>
				</tr>
				<tr>
					<td align="center">{$form.category_sales_trend.qty.1|default:'0'}</td>
					<td align="center">{$form.category_sales_trend.qty.3|default:'0'}</td>
					<td align="center">{$form.category_sales_trend.qty.6|default:'0'}</td>
					<td align="center">{$form.category_sales_trend.qty.12|default:'0'}</td>
				</tr>
				<tr>
					<td align="center">{$form.category_sales_trend.qty.1|qty_nf}</td>
					<td align="center">{$form.category_sales_trend.qty.3/3|qty_nf}</td>
					<td align="center">{$form.category_sales_trend.qty.6/6|qty_nf}</td>
					<td align="center">{$form.category_sales_trend.qty.12/12|qty_nf}</td>
				</tr>
			</table>
		</td>
	</tr>
	{if $config.po_enable_ibt}
		<tr>
			<td><b>IBT</b></td>
			<td><input type="checkbox" name="is_ibt" value="1" {if $form.is_ibt}checked {/if} /></td>
		</tr>
	{/if}
	
	<tr>
		<td><b class="form-label">PO Date</b></td>
		<td>
			<div class="form-inline">
				<input class="form-control" id="po_dt" name="po_date" value="{if $form.po_date>0}{$form.po_date|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}" onchange="po_date_changed();" onclick="if(this.value)this.select();" />
			
				{if $allow_edit}
					&nbsp;&nbsp;<img align=absbottom src="ui/calendar.gif" id="img_po_dt" style="cursor: pointer;" title="Select Date"/>
				{/if}
			</div>
			
		</td>
	</tr>
	
	{if !$allow_edit}
	<tr>
		<td><b class="form-label">PO Owner</b></td>
		<td>{$form.user|upper}</td>
	</tr>
	{/if}
	
	{if $form.branch_id==1 && !$form.po_branch_id}
		<tr>
			<td valign=top{if $config.po_allow_hq_purchase} rowspan=2{/if}><b class="form-label">PO Option</b></td>
			<td><input type=radio name="po_option" value="2" {if $form.po_option == 2 or !$form.po_option}checked{/if} {if $sessioninfo.branch_id==1}onclick="hq_purchase_clicked(this);"{/if}>
			HQ purchase on behalf of Branches 
			<font color=#990000><b>(Branch Payment)</b></font>
			[<a href="javascript:void(alert('- After approved will split into multiple branch PO.\n- GRR/GRN need to do by branch.'));">?</a>]
			</td>
		</tr>
		{if $config.po_allow_hq_purchase}
			<tr>
				<td><input type=radio name="po_option" value="3" {if $form.po_option == 3}checked{/if} onclick="hq_purchase_clicked(this);">
				HQ purchase <font color=#990000><b>(HQ Payment)</b></font>
				[<a href="javascript:void(alert('- No split into multiple branch PO.\n- GRR/GRN need to do by HQ.'));">?</a>]
				</td>
			</tr>
		
			<tr id="hq_delivery_date" {if $form.po_option ne 3 || $readonly}style="display:none;"{/if}>
				<td><b class="form-label">Delivery Date</b></td>
				<td>
					<div class="form-inline">
	<input class="form-control" id="hq_dd" name="hq_delivery_date" value="{$form.hq_delivery_date}" onclick="if(this.value)this.select();" onchange="hq_purchase_clicked();" size="10" maxlength="10" />
					
					{if $allow_edit}
						&nbsp;<img align="absbottom" src="ui/calendar.gif" id="img_hq_dd" style="cursor: pointer;" title="Select Date"/>
					{/if}					
					</div>
					<br>
				</td>
			</tr>
	
			<tr id="hq_cancel_date" {if $form.po_option ne 3 || $readonly}style="display:none;"{/if}>
				<td><b class="form-label">Cancellation Date</b></td>
				<td>
					<div class="form-inline">
						<input class="form-control" id="hq_cd" name="hq_cancel_date" value="{$form.hq_cancel_date}"  onclick="if(this.value)this.select();" onchange="hq_purchase_clicked();" size="10" maxlength="10" />
					
					{if $allow_edit}
					&nbsp;&nbsp;	<img align="absbottom" src="ui/calendar.gif" id="img_hq_cd" style="cursor: pointer;" title="Select Date"/>
					{/if}
					</div>
					<br>
				</td>
			</tr>
			
			<tr id="hq_partial_delivery" {if $form.po_option ne 3 || $readonly}style="display:none;"{/if}>
				<td><b class="form-label">Partial Delivery</b></td>
				<td>
					<input name="hq_partial_delivery" type="checkbox" {if $form.hq_partial_delivery}checked{/if} id="hq_pd" onclick="hq_partial_delivery_clicked(this);"> <label for="hq_pd">Allowed</label>
				</td>
			</tr>
		{/if}
		
		<tr>
			<td valign=top><b class="form-label">Delivery Branches</b></td>
			<td>You may select multiple branches to deliver <span class="text-danger" title="Required Field"> *</span><br>
				<table class="small" border=0 id=tbl_branch>
				{section name=i loop=$branch}
				{assign var=bid value=$branch[i].id}
				<tr>
					<td valign=top>
					<input onchange="active_btn();" type=checkbox id=dt_{$branch[i].id} name="deliver_to[]" value="{$branch[i].id}" class="branch" {if is_array($form.deliver_to) and in_array($branch[i].id,$form.deliver_to)}checked{/if}>&nbsp;{$branch[i].code}
					</td>
					<td>
						<table border=0 {if !is_array($form.deliver_to) or !in_array($branch[i].id,$form.deliver_to)}style="display:none"{/if}>
						<tr>
							<td colspan=6>
							<div class="form-inline">
							<i class="form-label">Deliver by</i> 
							&nbsp;<input class="form-control" size=1 name=delivery_vendor[{$branch[i].id}] value="{$form.delivery_vendor[$bid]|default:0}" readonly> 
							&nbsp;<input class="form-control" size=50 id="vendor[{$branch[i].id}]" name=delivery_vendor_name[{$branch[i].id}] value="{$form.delivery_vendor_name[$bid]|default:"-same as above-"}" onclick="this.select()">
							</div>
							<div id="autocomplete_vendor[{$branch[i].id}]" class="autocomplete"></div>
							<script>
							new Ajax.Autocompleter("vendor[{$branch[i].id}]", "autocomplete_vendor[{$branch[i].id}]", "ajax_autocomplete.php?a=ajax_search_vendor&type=po", {literal}{ paramName:"vendor", afterUpdateElement: function (obj, li) { {/literal}document.f_a.elements['delivery_vendor[{$branch[i].id}]'].value = li.title; {literal}}}{/literal});
							</script>
							</td>
						</tr>
						<tr>
							<div class="form-inline">
								<td><i class="form-label">Delivery Date <span class="text-danger"> *</span></i></td>
							<td>
								<input class="form-control mt-2" type="text" name="delivery_date[{$bid}]" id="dt1[{$bid}]" value="{$form.delivery_date[$bid]}" size=12 onchange="check_date(this); {if $config.po_agreement_cancellation_days}delivery_date_changed('{$bid}', this);{/if}" onclick="if(this.value)this.select();"> 
								
	
								{if $allow_edit}
								&nbsp;<img align=absbottom src="ui/calendar.gif" id="t_dt1[{$bid}]" style="cursor: pointer;" title="Select Date"/>
								{/if}
							</td>
							<td><i class="form-label">Cancellation Date	 <span class="text-danger"> *</span></i></td>
							<td>
								<input class="form-control mt-2" type="text" name="cancel_date[{$bid}]" id="dt2[{$bid}]" value="{$form.cancel_date[$bid]}" size=12 onchange="check_date(this);" onclick="if(this.value)this.select();">
								
								{if $allow_edit}
								&nbsp;<img align=absbottom src="ui/calendar.gif" id="t_dt2[{$bid}]" style="cursor: pointer;" title="Select Date"/>
								{/if}
							</td>
							<td>
								<div class="form-label form-inline">
									<input name="partial_delivery[{$bid}]" type="checkbox" {if $form.partial_delivery[$bid]}checked{/if} onclick="return partial_delivery_clicked(this);" id="pd{$bid}"> 
								&nbsp;Allow Partial Delivery
								</div>
							</td>
							</div>
						</tr>
						
						<tr>
						<td valign=top><i class="form-label">User Selection</i></td>
						<td colspan=4>
							<i class="form-label mb-1">Tick below to send PM / E-mail to users</i><br />
							<div id=user_select style="padding:20px;height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
							{section name=u loop=`$user_list.$bid.user`}
							 {assign var=u value=`$smarty.section.u.iteration-1`}
							 {assign var=id value=`$user_list.$bid.user_id.$u`}
							<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>&nbsp;{$user_list.$bid.user.$u}<br>
							{/section}
							</div>
						</td>
						</tr>
						
						</table>
					</td>
				</tr>
				{if !$form.approval_screen}
				<script>init_calendar('[{$bid}]');</script>
				{/if}
				{/section}
				</table>
			</td>
		</tr>	
	{else}
	{assign var=bid value=$form.po_branch_id}
		<tr>
			<td><b>Delivery Branch</b></td>
			<td>{$form.po_branch|default:$form.branch|default:$BRANCH_CODE}</td>
		</tr>
		<tr>
			<td><b class="form-label">Delivery Date</b></td>
			<td>
				<input class="form-control" type="text" name="delivery_date" id="dt1" value="{$form.delivery_date}" size=12 {if $config.po_agreement_cancellation_days}onchange="delivery_date_changed('{$bid}', this);"{/if} /> 
				{if $allow_edit}
				<img align=absbottom src="ui/calendar.gif" id="t_dt1" style="cursor: pointer;" title="Select Date"/>
				{/if}
				<div>yyyy-mm-dd</div>
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Cancellation Date</b></td>
			<td>
				<input class="form-control" type="text" name="cancel_date" id="dt2" value="{$form.cancel_date}" size=12 /> 
				{if $allow_edit}
				<img align=absbottom src="ui/calendar.gif" id="t_dt2" style="cursor: pointer;" title="Select Date"/>
				{/if}
				<div>yyyy-mm-dd</div>
			</td>
		</tr>
		{if $form.po_branch_id && $form.branch_id==1}
		<tr>
		<td valign=top><b>User Selection</b></td>
		<td colspan=4>
			<b>Tick below to send PM / E-mail to users</b><br />
			<div id=user_select style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
			{section name=u loop=`$user_list.$bid.user`}
			{assign var=u value=`$smarty.section.u.iteration-1`}
			{assign var=id value=`$user_list.$bid.user_id.$u`}
			<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>{$user_list.$bid.user.$u}<br>
			{/section}
			</div>
		</td>
		</tr>
		{else} {* branch also have user selection *}
		{assign var=bid value=$form.branch_id}
		<tr>
		<td valign=top><b>User Selection</b></td>
		<td colspan=4>
			<b>Tick below to send PM / E-mail to users</b><br />
			<div id=user_select style="height:100px;width:200px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;">
			{section name=u loop=`$user_list.$bid.user`}
			{assign var=u value=`$smarty.section.u.iteration-1`}
			{assign var=id value=`$user_list.$bid.user_id.$u`}
			<input type=checkbox name=allowed_user[{$bid}][{$id}] {if $form.allowed_user.$bid.$id}checked{/if}>{$user_list.$bid.user.$u}<br>
			{/section}
			</div>
		</td>
		</tr>
		{/if}	
		<tr>
			<td><b>Partial Delivery</b></td>
			<td>
				<input name="partial_delivery" type="checkbox" {if $form.partial_delivery}checked{/if} id="pd"> <label for="pd">Allowed</label>
			</td>
		</tr>
		
		{if !$form.approval_screen}
			<script>init_calendar('');</script>
		{/if}
	{/if}
		<tr>
			<td colspan="2">
				<div id=srefresh style="display:none; padding-top:10px">
					<input class="btn btn-primary" type=button onclick="void(refresh_tables())" value="click here to continue">
				</div>
			</td>
		</tr>
		{if $BRANCH_CODE!='HQ' || count($form.deliver_to)>0 || $form.po_branch_id}
		<tr>
			<td><input class="btn btn-primary" id="btn_add_item_by_csv" type="button" value="Add items by CSV" onclick="show_upload_csv_popup();"></td>
		</tr>
		{/if}
	</table>
	
	</div>
	</div>
</div>
<br>
{if $BRANCH_CODE!='HQ' || count($form.deliver_to)>0 || $form.po_branch_id}
	<div id="po_items_list">
	{include file=po.new.sheet.tpl}
	</div>
{/if}
</form>

<div id="sel_foc_cost" style="position:absolute;left:0;top:0;display:none;width:400px;height:250px;padding:10px;border:1px solid #000; background:#fff">
</div>

<div id="fake_sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff;z-index:10000;">
</div>

<div id="sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000; background:#fff;z-index:10000;">
</div>

<div id="color_size_matrix" style="position:absolute;left:0;top:0;display:none;width:800px;height:455px;padding:10px;border:1px solid #000; background:#fff">
</div>

<div id="test" style="position:absolute;left:0;top:0;display:none;width:800px;height:450px;padding:10px;border:1px solid #000; background:#fff">
</div>

{if $BRANCH_CODE!='HQ' || count($form.deliver_to)>0 || $form.po_branch_id}
	<form name="f_b" method=post ENCTYPE="multipart/form-data">
	<input type=hidden name=a>
	<input type=hidden name=approval_history_id value="{$form.approval_history_id}">
	<input type=hidden name=po_amount value="{$form.po_amount}">
	<input type=hidden name=id value="{$form.id}">
	<input type=hidden name=user_id value="{$form.user_id|default:$sessioninfo.id}">
	<input type=hidden name=branch_id value="{$form.branch_id|default:$sessioninfo.branch_id}">
	<input type=hidden name=is_request value="{$form.is_request}">
	<input type=hidden name=active value="{$form.active}">
	<input type=hidden name=po_branch_id value="{$form.po_branch_id}">
	<input type=hidden name=po_branch value="{$form.po_branch}">
	<input type=hidden name=readonly value="{$readonly}">
	</form>

	{if $form.approval_screen}
		<form name="f_c" method=post>
		<input type=hidden name=a value="save_approval">
		<input type=hidden name=approve_comment value="">
		<input type=hidden name=id value="{$form.id}">
		<input type=hidden name=remark2 value="{$form.remark2}">
		<input type=hidden name=branch_id value="{$form.branch_id}">
		<input type=hidden name=approvals value={$form.approvals}>
		<input type=hidden name=approval_history_id value={$form.approval_history_id}>
		{if $approval_on_behalf}
		<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
		<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
		{/if}
		</form>
	{/if}

	<p id=submitbtn align=center>

	{if $form.is_approval and $form.status==1 and $form.approved==0 and $form.approval_screen}
	<input type=button value="Approve" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_approve({$form.last_approver})">
	<input type=button value="Reject" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_reject({$form.last_approver})">
	<input type=button value="Terminate" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_terminate({$form.last_approver})">
	{/if}

	{if !$form.approval_screen}
		{if $config.single_server_mode || (!$config.single_server_mode && $form.branch_id eq $sessioninfo.branch_id)}
			{if $allow_edit}
			<input class="btn btn-success" name=bsubmit type=button value="Save & Close" onclick="do_save()">
				{if $form.status == 2}
				<input class="btn btn-error"  type=button value="Close" onclick="document.location='/po.php'">
				{/if}
			{/if}
		
			{if $form.id>$time_value|| !$allow_edit}
				{* if $form.approved and ($sessioninfo.level>=$config.doc_reset_level) and !$form.deliver_to *}
				{if $form.approved and ($sessioninfo.level>=$config.doc_reset_level)}
					<input class="btn btn-warning" type=button value="Reset" onclick="do_reset();">
				{/if}

				<input class="btn btn-danger" type=button value="Close" onclick="document.location='/po.php'">
			{/if}
		
			{if $form.approval_history_id>0}
				{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.user_id==$sessioninfo.id && !$form.delivered && $form.active}
					<input class="btn btn-warning" type=button value="Cancel" onclick="do_cancel()">	
				{/if}
			{/if}
			
			{if !is_new_id($form.id) && ($form.active || !$form.status) && $allow_edit}
			<input type=button class="btn btn-error" value="Delete" onclick="do_delete()">
			{/if}
		
			{if $allow_edit and $form.approved <> 1}
			<input type=button class="btn btn-primary" value="Confirm" onclick="do_confirm()">
			{/if}
		
			{if $form.id<$time_value && $form.active && $form.status<=1}
			<input class="btn btn-primary" type=button value="Print{if $form.status==0 || $form.status==2} Draft{elseif !$form.approved} Proforma{/if} PO" onclick="do_print()">
			{/if}

			{if $form.status && $form.revoke_id==0 && !$form.active && $form.status eq 4 || $form.status eq 5}
			<input class="btn btn-primary" type=button value="Revoke" onclick="do_revoke()">
			{/if}
		{else}
			{if $form.id<$time_value && $form.active && $form.status<=1}
				<input class="btn btn-primary" type=button value="Print{if $form.status==0 || $form.status==2} Draft{elseif !$form.approved} Proforma{/if} PO" onclick="do_print()">
			{/if}
			<input type=button class="btn btn-error" value="Close" onclick="document.location='/po.php'">
		{/if}
	
	{/if}
	</p>
{else}
* No branch was selected for PO
{/if}

<script>
{if $form.approval_screen}
	Form.disable(document.f_a);
	{if $form.last_approver}
		document.f_a.remark2.disabled=false;
	{/if}
{else}
{literal}
	new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&block=po", {
		afterUpdateElement: function (obj, li){
			var  s = li.title.split(",");
		    if(s[0]==0){
		        $('autocomplete_vendor').value = '';
		        return;
			}
			document.f_a.vendor_id.value = li.title;
			reset_sku_autocomplete();
			vendor_check();
			active_btn();
		}
	});
	new Draggable('sel_foc_cost', {starteffect:undefined,endeffect:undefined});
{/literal}
	if(document.f_a.vendor_id.value) vendor_check();
	window.onbeforeunload=confirmExit;
	
	{if $allow_edit}
		//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku.
		{literal}
		new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
		{/literal}
		recalculate_all_items();
		reset_sku_autocomplete();
		
		{if $config.enable_po_agreement}
			PO_PA_MODULE.initialize();
		{/if}
		if(po_option == 3) hq_purchase_clicked();
	{else}
		Form.disable(document.f_a);
	{/if}
{/if}
{if ($form.id>$time_value && count($po_items) == 0) || $form.approval_screen || $readonly}
	needCheckExit = false;
{/if}
new Draggable('price_history_popup');
</script>

{if !$form.approval_screen}
	{include file=footer.tpl}
{/if}
