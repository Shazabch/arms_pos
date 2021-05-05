{*
8/11/2010 12:22:01 PM Justin
- Placed the item listing on the new file called membership.redemption.redemption_item_list.tpl.
- Added delete function to delete added redemption item (for config only).
- Reset the valid date start and date end whenever found it is '0000-00-00'.
- Modified the Condition column to have follow different information:
  -> Valid Start Date
  -> Valid End Date
  -> Points
  -> Receipt Amount
- Added delete function to delete added redemption item (for config only).

8/18/2010 11:06:53 AM Justin
- Added the display of scanned IC image.
- Swapped the Point Accumulated field from top and place beside Point Left's field.

8/25/2010 6:34:28 PM Justin
- Amended the item list to include Cash column.
- Each time the user key in qty for a particular added item, system will recalculate and show user the Cash Needed.

9/17/2010 5:14:41 PM Justin
- Added a prompt out window to show the scanned IC image whenever successfully to login from redemption menu.
- Allowed user to continue or go back to previous page by click on either Continue or Back button.

9/24/2010 4:49:00 PM Justin
- Removed the auto prompt out window of Scanned IC image display which already moved to previous page.
- Added a new hidden window that to be appeared that required user to key in Cash Paid by customer.
- This window only appeared while have cash required from redemption items, it contains Cash Need, Cash Paid and Change.
- Change will updated depends on how much Cash Paid is keyed in.
- Will keep this Cash Paid as part of the info for that redemption.

10/28/2010 4:45:25 PM Justin
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

12/27/2010 10:39:58 AM Justin
- Added barcode scan feature.

4/20/2011 11:41:21 AM Justin
- Fixed the bugs where cannot close the branch popup window.

8/16/2011 10:43:21 AM Justin
- Added the grab focus for amount paid.

10/12/2011 5:30:46 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

1/11/2013 5:21 PM Justin
- Enhanced to include voucher on the redemption item list.

4/3/2013 4:06 PM Justin
- Bug fixed on the voucher table will not disappear while user change qty become 0.

12/30/2013 3:38 PM Justin
- Bug fixed on some times rows for vouchers cannot add automatically when change the qty.
*}

{include file='header.tpl'}

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
{literal}
.red{
	color: red;
}

#cash_paid_div table input{
	background-color:white;
}
{/literal}
</style>

<script>
var total_point = int('{$membership_info.points}');
var membership_redemption_use_enhanced = '{$config.membership_redemption_use_enhanced}';
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
var temp_ele = {};

function round_qty(value){
	return float(round(value, global_qty_decimal_points));
}

function set_selected_item(ele){
    var item_id = ele.id.split(",")[1];
    temp_ele['qty'] = round_qty($(ele).value);
}

/*
function update_qty(ele){
//	miz(ele);
	var item_id = ele.id.split(",")[1];
	var qty = round_qty(ele.value);
	// check if qty less than 0
	if(qty<0){
        qty = 0;
        ele.value = '';
	}   
	
	var point_left = int($('td_point_left').innerHTML);
	var total_point_need = int($('td_point_need').innerHTML);
	var total_cash_need = int($('td_cash_need').innerHTML);
	var total_qty = round_qty($('td_total_qty').innerHTML);

	var pt_need = int($('condition_pt,'+item_id).value);
	var cash_need = float(round($('condition_cash,'+item_id).value,2));
	
	if(pt_need>0){  // need points
	    // recover previous reduced points
        var recover_point = round_qty(temp_ele['qty'])*pt_need;
        point_left += recover_point;
        total_point_need -= recover_point;

        if(qty>0){// start reduce points for selected item
            var reduce_point = round(pt_need*qty);
            point_left -= reduce_point;
            total_point_need += reduce_point;
        }
        
        $('td_point_left').update(point_left);
        $('td_point_need').update(total_point_need);
        
        if(point_left<=0)   $('td_point_left').addClassName('red');
        else    $('td_point_left').removeClassName('red');
	}
	
	if(cash_need>0){    // need cash
        // recover previous reduced cash
        var recover_cash = round_qty(temp_ele['qty'])*cash_need;
        total_cash_need -= recover_cash;
        
        if(qty>0){
			var need_add_cash = cash_need*qty;
			total_cash_need += need_add_cash;
		}
		
		$('td_cash_need').update(round(total_cash_need,2));
		if(total_cash_need>0)   $('tr_cash_need').show();
		else    $('tr_cash_need').hide();
	}
	
	total_qty = total_qty - round_qty(temp_ele['qty']) + qty;
	$('td_total_qty').update(total_qty);
	
	set_selected_item(ele);
	
}
*/
function do_confirm(){
	var point_left = int($('td_point_left').innerHTML);
	var total_qty = round_qty($('td_total_qty').innerHTML);
	var total_cash = round($('td_cash_need').innerHTML, 2);
	
	// check qty
	if(total_qty<=0){
		alert('No Redemption to make.');return false;
	}
	
	// check point
	if(point_left<0){
        alert('You have not enough points to make this redemption.');return false;
	}

	// check for receipt info
	var all_inp_with_receipt = $$('#tbody_item_list input.item_need_receipt');

	for(var i=0; i<all_inp_with_receipt.length; i++){
		var item_id = all_inp_with_receipt[i].value;
		// check this item got make redemption or not
		if(round_qty($('qty,'+item_id).value)>0){ // got make redemption
			if($('receipt_no,'+item_id).value==''){
				alert('Please Enter Receipt No');
				$('receipt_no,'+item_id).focus();
				return false;
			}
			if($('receipt_date,'+item_id).value==''){
				alert('Please Enter Receipt Date');
				$('receipt_date,'+item_id).focus();
				return false;
			}
			if($('counter_no,'+item_id).value==''){
				alert('Please Enter Counter No');
				$('counter_no,'+item_id).focus();
				return false;
			}
		}
	}
	
	// if found the redeem items has cash need, prompt out a window to require user key in cash paid
	if(total_cash > 0 && $('ttl_amt_paid').value == ''){
		if($('cash_paid_div').style.display == ''){
			alert("Please key in Cash Paid");
		}else{
			$('ttl_amt_need').value = total_cash; 
			showdiv('cash_paid_div');
			$('ttl_amt_paid').focus();
			curtain(true);
		}
		return false;
	}else{
		if($('paid_amt_change').value < 0){
			alert("Insufficient Cash Paid.");
			return false;
		}else{
			document.f_a.ttl_cash_paid.value = round($('ttl_amt_paid').value, 2);
		}
	}

	if(!confirm('Click OK to confirm.')) return false;
	
	document.f_a.submit();
}

function recalc_all(){
	var all_inp_qty = $$('#tbody_item_list input.inp_qty');
	var total_qty = 0;
	var total_pt_need = 0;
	var total_cash_need = 0;
	var point_left = total_point;
	
	for(var i=0; i<all_inp_qty.length; i++){
		var qty = round_qty(all_inp_qty[i].value);
		var item_id = all_inp_qty[i].id.split(",")[1];
		
		if(qty>0){
		    var pt_need = int($('condition_pt,'+item_id).value);
		    var cash_need = float($('condition_cash,'+item_id).value);
		    
		    if(pt_need>0){
                var reduce_point = round_qty(pt_need*qty);
	            point_left -= reduce_point;
	            total_pt_need += reduce_point;
			}
			
			if(cash_need>0){
				var need_add_cash = cash_need*qty;
				total_cash_need += need_add_cash;
			}
            total_qty += qty;
		}
	}

	if(point_left<=0)   $('td_point_left').addClassName('red');
	else    $('td_point_left').removeClassName('red');
	
	$('td_point_left').update(point_left);
    $('td_point_need').update(total_pt_need);
	$('td_total_qty').update(total_qty);
	$('td_cash_need').update(round(total_cash_need,2));
	if(total_cash_need>0)   $('tr_cash_need').show();
	else    $('tr_cash_need').hide();
}

function delete_item(id){
	if(!confirm('Are you sure you wan to delete item "'+$('sku_item_code,'+id).value+'"?')) return;
	Element.remove('item_'+id);
	recalc_all();
}

function toggle_img_div(id){
	
	if (document.getElementById(id).style.display == 'none'){
		curtain(true);
		show_div(id);
	}else{
		curtain(false);
		hide_div(id);
	}

	return (document.getElementById(id).style.display == 'none');
}

function show_div(id)
{
	var div = document.getElementById(id);
	if (div.style.display=='none') div.style.display='';

	if (div.style.position == 'absolute')
	    div.style.top = (parseInt(document.body.scrollTop)+50)+'px';

	curtain(true);
}

function hide_div(id){
	
	if (document.getElementById(id).style.display!='none'){
	    document.getElementById(id).style.display='none';
	    curtain(false);
	}
}

function curtain_clicked(){
	hidediv('mr_item_imp');
	hidediv('ic_org');
	hidediv('cash_paid_div');
    $('ttl_amt_paid').value = '';
    $('paid_amt_change').value = '';
    curtain(false);
}

function calc_amt_change(ele){
	var total_cash = round($('td_cash_need').innerHTML, 2);

	$('ttl_amt_need').value = total_cash; 
	$('paid_amt_change').value = round(parseFloat(ele.value) - parseFloat(total_cash), 2);
}

function barcode_scan(val){
	if(!val) return;
	var item_list = "";

	// retrieve current already added items
	var all_item_id = $$('#tbody_item_list input.item_id');
	for(var i=0; i<all_item_id.length; i++){
		if (all_item_id[i].value){
			item_list += all_item_id[i].value+",";
		}
	}

	if(all_item_id.length > 0) item_list =  item_list.slice(0,-1);

	// search and display barcode items
	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a)+"&a=barcode_scan&item_list="+item_list,
		evalScripts: true,
		onFailure: function(m) {
			//alert(m.responseText);
		},
		onSuccess: function(m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['mri_list']){
					$('mri_list').innerHTML = json[tr_key]['mri_list'];
					showdiv("mr_item_imp");
					curtain(true);
				}else if(json[tr_key]['mri_row']){
					new Insertion.Before($('tbl_footer'),json[tr_key]['mri_row']);
				}else if(json[tr_key]['err']){
					alert(json[tr_key]['err']);
				}
			}
		},
		onComplete: function(m) {
			document.f_a.mr_barcode.value = "";
		}
	});
}

function add_mri()
{
	var disc = new Array();
	var d = '';
	var is_existed = false;
	var sku_code_title = '';
	var grp_sku_code = '';
	
	$('mr_item_imp').style.display='none';
	var opts = $('mr_item_imp').getElementsByTagName('input');

	for(var i=0;i<opts.length;i++)
	{
		var c = opts[i].value.split(",");
		if (opts[i].checked){
			add_sku_to_list(c[0],opts[i].title);
			if(document.f_a){
				if(nric) grp_sku_code += c[1]+",";
			}
		}
	}

	sku_list = document.getElementById("sku_code_list");
	
	for (var i=0;i<sku_list.options.length;i++)
	{
		// in order to use this feature, please add a hidden field with follow:
		// a) make sure send a variable $check_item_list when u include this file and tbody id=tbody_item_list
		// b) name of "item_sku_item_id[" and a field title 
		// c) title of "sku item code" the following hidden field
		if(check_item_list){
			var all_sku_item_id = $('tbody_item_list').getElementsByTagName('input');

			$A(all_sku_item_id).each(
				function (r,idx){
					if (r.name.indexOf("item_sku_item_id[")==0){
						if(r.value == sku_list.options[i].value){
							sku_code_title = r.title;
							is_existed = true;
						}
					}
				}
			);
			
			if(is_existed){
				if(!confirm("SKU Item Code "+sku_code_title+" is existed, add to list?")){
					sku_list.options[i].selected=false;
				}else{
					sku_list.options[i].selected=true;
				}
				is_existed = false;
			}else{
				sku_list.options[i].selected=true;
			}
		}else{
			sku_list.options[i].selected=true;
		}
	}
	
//	alert(Form.serialize(document.f_a).replace(/\&/g,"\n"));

	clear_autocomplete();
	
	if (window.add_autocomplete_callback) disc = add_autocomplete_callback();
	if (disc) d = '&d='+escape(disc.join());
	if(grp_sku_code){
		grp_sku_code = grp_sku_code.slice(0, -1);
		d += '&grp_sku_code='+grp_sku_code;
	}
   	parms = Form.serialize(document.f_a) + '&a=ajax_add_item_row'+d;	

 	// insert new row
 	// insert new row
	new Ajax.Request(phpself,{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
			if (!/^(\s+)*<t/.test(m.responseText) && m.responseText != '') alert(m.responseText);
			else if (/<html>/.test(m.responseText)) alert("Item is currently available, please try again later");
			else{
			    if($('tbl_footer')){
                    new Insertion.Before($('tbl_footer'),m.responseText);
				}else{
                    
                    new Insertion.Bottom($$('.multiple_add_container').first(),m.responseText);
				}
			}

			if (window.add_autocomplete_extra) disc = add_autocomplete_extra();
		},		
		onComplete: function(){
			sku_list.length = 0;
		}
	});	
}

function assign_voucher_row(key){
	// calculate the total SN based in branch
	var sn_msg = '';

	var new_qty = int(document.f_a['qty['+key+']'].value);

	var prev_qty = int(document.f_a['prev_qty['+key+']'].value);
	var curr_qty = new_qty - prev_qty;
	
	//alert("curr qty="+curr_qty);
	//alert("prev qty="+prev_qty);

	if(curr_qty > 0){
		$('voucher_item_table_'+key).show();
		for(var i=prev_qty; i<new_qty; i++){
			add_row(key, i);
		}
	}else{
		if(new_qty != 0){
			for(var i=prev_qty-1; i>=new_qty; i--){
				Element.remove($('voucher_item_row_'+key+'_'+i));
			}
		}else{
			for(var i=0; i<prev_qty; i++){
				Element.remove($('voucher_item_row_'+key+'_'+i));
			}
		}

		if(new_qty == 0) $('voucher_item_table_'+key).hide();
	}

	document.f_a.elements['prev_qty['+key+']'].value = new_qty;
	//document.f_a.elements['ttl_sn['+sku_item_id+']['+sbid+']'].value = ttl_qty_used;
}

function add_row(key, row){
	var new_tr = $('temp_voucher_row').cloneNode(true).innerHTML;
	new_tr = new_tr.replace(/__key/g, key);
	new_tr = new_tr.replace(/__row/g, row);

	new Insertion.Bottom($('voucher_item_list_'+key), new_tr);
	var row_no = float(row)+1;
	$('voucher_row_no_'+key+'_'+row).update(row_no+".");
}
{/literal}
</script>

{if $err}
<ul style="color:red;background:#f0f0f0;border:1px solid red;">
	{foreach from=$err item=e}
	    <li>{$e}</li>
	{/foreach}
</ul>
{/if}

<!-- voucher row -->
<table style="display:none;">
	<tbody id="temp_voucher_row" class="temp_voucher_row">
		<tr id="voucher_item_row___key___row">
			<td id="voucher_row_no___key___row">&nbsp;</td>
			<td align="center"><input type="text" name="voucher_code[__key][__row]" /></td>
		</tr>
	</tbody>
</table>

<div id="mr_item_imp" class="curtain_popup" style="position:absolute;z-index:20000;width:750px;height:430px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_mri_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Redemption Item List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div style="padding:10px;" id="mr_sku_item_choices">
		<form name="mr_item_list" method="post" padding="10px;">
			<h2>Please select:</h2>
			<div id="mri_list" style="height:310px; border:1px solid #ccc; overflow:auto;"></div>
		</form>
		<br />
		<div align="center">
			<input type="button" value="Add" onclick="add_mri(); curtain(false);">
			<input type="button" value="Cancel" onclick="default_curtain_clicked();">
		</div>
	</div>
</div>

<div id="ic_org" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:150px; left:150px;z-index:20000;">
	<span style="float: right;">
		<img align="absmiddle" class="clickable" onclick="default_curtain_clicked();" src="/ui/closewin.png">
	</span>
	<div class=small style="position:absolute; right:10px;">
		<a href="javascript:void(default_curtain_clicked())"><img src=ui/closewin.png border=0 align=absmiddle></a>
	</div>
	<img src="{$membership_info.ic_path}"><br /><br />
</div>

<div style="position: absolute; z-index: 20000; width: 180px; height: 150px; border: 2px solid rgb(206, 0, 0); background-color: rgb(255, 255, 255); background-image: url(&quot;/ui/ndiv.jpg&quot;); background-repeat: repeat-x; padding: 0pt ! important; top: 208px; left: 540px; display:none;" class="curtain_popup" id="cash_paid_div">
	<div style="border: 2px ridge rgb(206, 0, 0); color: white; background-color: rgb(206, 0, 0); padding: 2px; cursor: default;" id="div_stock_take_direct_add_multiple_header2"><span>Cash Paid</span>
		<span style="float: right;">
			<img align="absmiddle" class="clickable" onclick="default_curtain_clicked();" src="/ui/closewin.png">
		</span>
	</div>
	<br />
	<table cellspacing="0" border="0" padding="0">
		<tr>
			<td>Cash Required:</td>
			<td><input type="text" size="10" name="ttl_amt_need" id="ttl_amt_need" class="r" readonly></td>
		</tr>
		<tr>
			<td>Cash Paid:</td>
			<td><input type="text" size="10" name="ttl_amt_paid" id="ttl_amt_paid" class="r" onchange="this.value=round(this.value,2); calc_amt_change(this);"></td>
		</tr>
		<tr>
			<td>Change:</td>
			<td><input type="text" size="10" name="paid_amt_change" id="paid_amt_change" class="r" readonly></td>
		</tr>
	</table>
	<p align="center">
		<input type=button value="Confirm" onclick="do_confirm()">
		<input type=button value="Back" onclick="default_curtain_clicked()">
	</p>
</div>

<table align=right >
<tr><td align=center>
<h4>Scanned IC Image</h4>
<img src="{$membership_info.ic_path}" width=200 style="border:1px solid #999; padding:8px; background-color:#fff;cursor:pointer;z-index:100" onClick="toggle_img_div('ic_org');"><br>
click to see full size
</td></tr>
</table>

<p>
<h1>Membership Info</h1>
<table><tr><td>
<table cellspacing=0 cellpadding=4>
<tr><td><b>Name</b></td><td>{$membership_info.designation} {$membership_info.name}</td></tr>
<tr><td><b>NRIC</b></td><td>{$membership_info.nric}</td></tr>
{if $membership_info.points_update>0}
<tr><td><b>Points Update</b></td><td>{$membership_info.points_update|date_format:"%e/%m/%Y"}</td></tr>
{/if}
<tr><td><b>Current {$config.membership_cardname} Number</b></b></td><td>{$membership_info.card_no}</td></tr>
<tr><td><b>Issue Branch</b></td><td>{$membership_info.history.branch_code}</td></tr>
<tr><td><b>Issue Date</b></td><td>{$membership_info.issue_date|date_format:"%e/%m/%Y"}</td></tr>
<tr><td><b>Next Expiry Date</b></td><td>{$membership_info.next_expiry_date|date_format:"%e/%m/%Y"}</td></tr>
</table>
</td><td>
<img src="{$membership_info.history.card_type|string_format:$config.membership_cardimg}" hspace=40>
</td>
</tr></table>
</p>

<a href="membership.redemption.php">
<img src="/ui/icons/arrow_undo.png" align="absmiddle" border="0 /"> Enter New Card No
</a>
<br />

<table style="border:1px solid #999; padding:5px; background-color:#fe9;float:left;margin-right:20px;" width="20%">
	<tr>
	    <th><h1>Points Accumulated</h1></th>
	    <td style="font-size:20px;background:white;padding:10px;text-align:center;" id="td_point_acc">{$membership_info.points}</td>
	    
	</tr>
</table>

<table style="border:1px solid #999; padding:5px; background-color:#fe9;float:left;margin-right:20px;" width="20%">
	<tr>
	    <th><h1>Points Left</h1></th>
	    <td style="font-size:20px;background:white;padding:10px;text-align:center;" id="td_point_left" {if $membership_info.points<=0}class="red"{/if}>{$membership_info.points}</td>
	    
	</tr>
</table>

<table style="border:1px solid #999; padding:5px; background-color:#fe9;float:left;margin-right:20px;" >
	<tr>
	    <th><h1>Total Points Need</h1></th>
	    <td style="font-size:20px;background:white;padding:10px;text-align:center;" id="td_point_need">0</td>
	</tr>
	<tr id="tr_cash_need" style="display:none;">
	    <th><h1>Cash Need</h1></th>
	    <td style="font-size:20px;background:white;padding:10px;text-align:center;" id="td_cash_need" class="c">0</td>
	</tr>
</table>

<table style="border:1px solid #999; padding:5px; background-color:#fe9;float:left;margin-right:20px;" >
	<tr>
	    <th><h1>Total Qty</h1></th>
	    <td style="font-size:20px;background:white;padding:10px;text-align:center;" id="td_total_qty">0</td>
	</tr>
</table>
<br style="clear:both;" />
{if !$config.membership_redemption_use_enhanced}
<h1>Available Item:</h1>
{else}
<h1>Add Item via Search SKU</h1>
{/if}
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="check_and_show_items" />
<input type="hidden" name="card_no" value="{$membership_info.card_no}" />
<input type="hidden" name="nric" value="{$membership_info.nric}" />
<input type="hidden" name="ttl_cash_paid" />
<input type="hidden" name="save" value="1" />
<input type="hidden" name="proceed" value="1" />
<table width="100%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border" border=0 cellspacing=1 cellpadding=1 id="tbody_item_list">
	<thead>
		<tr bgcolor="#ffffff">
			<th width="2%" rowspan="2">{if !$config.membership_redemption_use_enhanced}#{/if}</th>
			<th width="7%" rowspan="2">Arms Code</th>
			<th width="50%" rowspan="2">Desciption</th>
			<th width="3%" rowspan="2">Selling<br>Price</th>
			<th width="20%" colspan="5">Conditions</th>
			<th width="3%" rowspan="2">Qty</th>
			<th width="12%" colspan="3">Receipt Info</th>
		</tr>
		<tr bgcolor="#ffffff">
			<th nowrap>Start Date</th>
			<th nowrap>End Date</th>
			<th nowrap>Points</th>
			<th nowrap>Receipt Amount</th>
			<th nowrap>Cash</th>
	        <th nowrap>Receipt No</th>
	        <th>Date</th>
	        <th nowrap>Counter No</th>
		</tr>
	</thead>
	{if !$config.membership_redemption_use_enhanced}
		{foreach from=$item_list item=item name=f}
			{include file='membership.redemption.redemption_item_list_row.tpl'}
		{foreachelse}
		    <td colspan="9" style="padding:50px 20px;">No Item</td>
		{/foreach}
	{else}
		{foreach from=$item_list item=item name=f}
			{include file='membership.redemption.redemption_item_list_row.tpl'}
		{/foreach}
		<tfoot id="tbl_footer">
			<tr class=normal bgcolor="{#TB_ROWHEADER#}" id="add_sku_row">
				<td colspan="13" nowrap>
					{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 nric=$membership_info.card_no}
				</td>
			</tr>
			<tr class=normal bgcolor="{#TB_ROWHEADER#}" id="add_sku_row">
				<td colspan="13" nowrap>
					<div style="padding:3px;">
<b>Scan Barcode : </b> <input name="mr_barcode" onkeypress="if(event.keyCode==13)barcode_scan(this.value);" /> <input type="button" value="Add" onclick="barcode_scan(document.f_a.mr_barcode.value);" />
					</div>
				</td>
			</tr>
		</tfoot>
	{/if}
</table>

<p align="center">
    <input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/membership.redemption.php'">
    <input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
</p>
</form>

<script>
{literal}
if(membership_redemption_use_enhanced) reset_sku_autocomplete();
recalc_all();
new Draggable('ic_org');
new Draggable('cash_paid_div');
new Draggable('mr_item_imp',{ handle: 'div_mri_header'});
{/literal}
</script>
{include file='footer.tpl'}
