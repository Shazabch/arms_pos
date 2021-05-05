{*
Revision History
=================
5/22/2007 6:09:25 PM - yinsee
- allow update of uom and cost if item is manualy added

11/7/2007 6:13:52 PM gary
- modify add/delete item function, using XML method.

3/5/2008 11:28:35 AM gary
- change old po link to new po link.

6/9/2008 5:34:32 PM yinsee
- touch up font size and table style

6/30/2009 4:04 PM Andy
- add GRN Tax

10/27/2009 04:03:16 PM  edward
- resize % textbox

11/3/2009 5:16:28 PM edward
- change uomf int to float.

4/2/2010 5:42:07 PM Andy
- Add "Multiple add item" feature for GRN

4/21/2010 5:38:03 PM Andy
- GRN multiple add item use back the single add function.
- Add new "multiple add" btn and show popup to let user select multiple sku.

4/22/2010 1:57:39 PM Andy
- Fix Add item popup "URL Too Long" bugs

10/13/2010 3:34:01 PM Justin
- Added a hidden field for grr receive date

11/10/2010 10:33:56 AM Justin
- Changed the rounding for cost from 2 to 4 decimal points.

1/6/2011 4:03:12 PM Justin
- Added hidden fields for document no, GRR PO amount.

5/12/2011 11:18:21 AM Justin
- Added show status while adding sku item.

6/22/2011 11:04:25 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

6/24/2011 12:30:31 PM Justin
- Modified all the ctn and pcs fields for Account Verification to accept decimal points calculation.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

10/7/2011 11:04:58 AM Andy
- Add GRN Scan Barcode can accept new format (ARMS Code, MCode, Art.No, Link Code)
- Fix some bugs which cause after add item the cursor cannot focus back the input box.

8/24/2012 12:01 PM Justin
- Enhanced to show branch code and related invoice as if found it is PO and config is set.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

2/25/2014 10:43 AM Justin
- Enhanced to take off tr tag.
- Enhanced the module to use json instead of old method to insert new item.
- Enhanced to have checking of item whether same department with GRN, if not then print error (need config).

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

4/21/2014 10:01 AM Justin
- Enhanced to have checking on block items in GRN.

11/23/2015 3:26 PM DingRen
- PeriodicalUpdater too keep session alive

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

06/25/2020 2:25 PM Sheila
- Updated button css.
*}
{include file=header.tpl}

{assign var=time_value value=1000000000}

{literal}
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

#tbl_item  input, #tbl_item  select{
	border:1px solid #999;
	font-size: 10px;
	padding:2px;
}
input[disabled],input[readonly],select[disabled], textarea[disabled]{
	color:black;
	background:#ddd;
}
</style>
{/literal}
<script>
var grn_have_tax = '{$config.grn_have_tax}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var grn_item_other_dept_show_prompt = '{$config.grn_item_other_dept_show_prompt}';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
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
function sku_show_varieties(sku_id)
{
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

function recalc_row(iid)
{
	var n = float(document.f_a.elements['ctn['+iid+']'].value) * float(document.f_a.elements['uomf['+iid+']'].value) + float(document.f_a.elements['pcs['+iid+']'].value);

	document.f_a.elements['amt['+iid+']'].value = round2(n*(float(document.f_a.elements['cost['+iid+']'].value)/float(document.f_a.elements['uomf['+iid+']'].value)));
				
	recalc_totals();
}

function recalc_totals(){
	if ($('grn_items')==undefined) return;
	var sp = $('grn_items').getElementsByTagName("INPUT");
	var total_pcs = 0;
	var total_ctn = 0;
	var total_amt = 0;
	$A(sp).each(
		function (r,idx)
		{
			if (r.name.indexOf("ctn[")==0)
			{
				total_ctn += float(r.value);
				total_ctn = float(round(total_ctn, global_qty_decimal_points)); // to prevent the 99999999 sum problem
			}
			if (r.name.indexOf("pcs[")==0)
			{
				total_pcs += float(r.value);
				total_ctn = float(round(total_ctn, global_qty_decimal_points)); // to prevent the 99999999 sum problem
			}
			if (r.name.indexOf("amt[")==0)
			{
				total_amt += float(r.value);
			}
		});

	$('total_qty').innerHTML = 'Ctn: '+float(round(total_ctn, global_qty_decimal_points))+' / Pcs: '+float(round(total_pcs, global_qty_decimal_points));
	$('total_amt').value = round2(total_amt);
}
var old_cost;

function sel_uom(id, value){

	var sstr = '['+id+']';
	var s = value.split(",");
	
	old_cost=float(document.f_a.elements['cost'+sstr].value)/float(document.f_a.elements['uomf'+sstr].value);
	
	if(s[1]>1){
		fraction=s[1];
		old_pcs=document.f_a.elements['pcs'+sstr].value;
		new_pcs=float(old_pcs%fraction);
		remain=float(old_pcs)-new_pcs;
		ctn=float(remain/fraction)+float(document.f_a.elements['ctn'+sstr].value);
		
		document.f_a.elements['pcs'+sstr].value=float(round(new_pcs, global_qty_decimal_points));
		document.f_a.elements['ctn'+sstr].value=float(round(ctn, global_qty_decimal_points));
	}
	else{
		document.f_a.elements['ctn'+sstr].value='';
	}
	
	document.f_a.elements['uom_id'+sstr].value=s[0];
	document.f_a.elements['uomf'+sstr].value=s[1];
	document.f_a.elements['ctn'+sstr].disabled=(s[1]<=1);
	
	new_cost=old_cost*float(document.f_a.elements['uomf'+sstr].value);
	document.f_a.elements['cost'+sstr].value=round(new_cost, global_cost_decimal_points);
	
	recalc_row(id);
}

function sel_sell_uom(id, value){

	var sstr = '['+id+']';
	var s = value.split(",");
	document.f_a.elements['selling_uom_id'+sstr].value=s[0];
	//document.f_a.elements['uomf'+sstr].value=s[1];
	//document.f_a.elements['ctn'+sstr].disabled = (s[1]<=1);
	//recalc_row(id);
}

function do_confirm()
{
	if (!confirm('Finalise GRN?'))
	{
	    return;
	}
	
	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	
	ajax_request('goods_receiving_note.php',{
		method: 'post',
		parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
		onComplete: function(e){
			if (e.responseText.trim() == 'OK') {
				document.f_a.a.value='confirm';
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

function do_save()
{
	
	center_div('wait_popup');
	curtain(true,'curtain2');
	Element.show('wait_popup');
	
	ajax_request('goods_receiving_note.php',{
		method: 'post',
		parameters: Form.serialize(document.f_a)+'&a=check_tmp_item_exists',
		onComplete: function(e){
			if (e.responseText.trim() == 'OK') {
				document.f_a.a.value='save';
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
	if (check_login()) {
		if (!confirm('Discard changes and close?')){
			return;
		}
		document.location = '/goods_receiving_note.php';
	}
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
	//add_autocomplete_callback();
	$('grn_barcode').value='';
	$('span_autocomplete_adding').style.display = '';
	// ajax_add_item_row
	ajax_request(phpself, {
		parameters: Form.serialize(document.f_a)+'&a=ajax_add_item_row&grn_barcode='+value,
		onSuccess: function(m){
			$('span_autocomplete_adding').style.display = 'none';
		
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['html'] != undefined && json[tr_key]['ok'] == 1){
					new Insertion.Bottom($('grn_items'),json[tr_key]['html']);
					
					if(grn_item_other_dept_show_prompt && json[tr_key]['department_id'] != document.f_a['department_id'].value){
						if(!confirm("following item(s) not under GRN's department, insert anyway?\n\n"+json[tr_key]['si_desc'])){
							delete_item(json[tr_key]['item_id'], 1);
						}
					}
				}
				
				if(json[tr_key]['error'] != undefined){
					alert(json[tr_key]['error']);
				}
			}
			
			add_autocomplete_extra();
			$('grn_barcode').focus();
		},
		
		onComplete: function(m){
			$('span_autocomplete_adding').style.display = 'none';
			if($('div_multiple_add_popup'))	default_curtain_clicked();
		},
	});
	$('grn_barcode').focus();
}

// update autocompleter parameters when vendor_id or department_id changed
/*var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
	var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type);
	if (sku_autocomplete != undefined)
	{
	    sku_autocomplete.options.defaultParams = param_str;
	}
	else
	{
		sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
		afterUpdateElement: function (obj, li) {
		    s = li.title.split(",");
			document.f_a.sku_item_id.value = s[0];
			document.f_a.sku_item_code.value = s[1];
			
		}});
	}
	clear_autocomplete();
}*/

/*function clear_autocomplete(){
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
}*/
/*
function add_item(){
	if (int(document.f_a.sku_item_id.value)==0){
	    alert('No item selected');
	    return false;
	}
	var parms;
    parms = Form.serialize(document.f_a)+'&a=ajax_add_item';

	new Ajax.Request("goods_receiving_note.php",{
			method:'post',
			parameters: parms,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
                var tb = $('grn_items');
                var lbody;
				var xml = m.responseXML;
				
				if (!xml) { alert(m.responseText); return; }
				var records = xml.getElementsByTagName("record");
				$A(records).each(
				    function(r,idx){
					    var rowitem = tb.insertRow(-1);
					    rowitem.id = "titem"+xml_getData(r, "id").strip();
					    rowitem.innerHTML = xml_getData(r,'rowdata');
					}
				);
				reset_row();
				tax_changed();
			},
	});
}*/

// function to run after finsih add items
function add_autocomplete_extra(){
    reset_row();
	tax_changed();
}

function reset_row(){
	var e = $('grn_items').getElementsByClassName('no');
	var total_row=e.length;
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^no_');
	 	if (temp_1.test(e[i].id)){
			td_1=(i+1)+'.';
			e[i].innerHTML=td_1;
			e[i].id='no_'+(i+1);
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
	$('autocomplete_sku').select();
}

function delete_item(id, skip_caution){
 	if(skip_caution != 1){
		if (!confirm('Remove this SKU from GRN?')) return;
	}

	ajax_request("goods_receiving_note.php",{
		method:'post',
		parameters: 'a=ajax_delete_row&id='+id,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            Element.remove('titem'+id);
            reset_row();
            recalc_totals();
    	}
	});
}

function tax_changed(){
	if(!document.f_a['grn_tax'])	return;
	var tax = document.f_a.grn_tax.value;
	var inputs = $$("#grn_items input.original_cost");
	
	for(var i=0; i<inputs.length; i++){
		var original_cost = float(inputs[i].value);
		var new_cost = float(original_cost + (original_cost*(tax*0.01)));
		var item_id = inputs[i].id.split(',')[2];
	
		if($('inp,cost,'+item_id)){
			$('inp,cost,'+item_id).value = round(new_cost, global_cost_decimal_points);
		}
		recalc_row(item_id);
	}
	
	recalc_totals();
}

function set_new_original_cost(iid){
	if(!grn_have_tax)   return;
	
	$('inp,original_cost,'+iid).value = $('inp,cost,'+iid).value;
	tax_changed();
}

function add_autocomplete(){
	if (document.f_a.sku_item_id.value == '' || document.f_a.sku_item_id.value == 0){
	    alert('Please select an SKU to add');
        document.f_a.sku.focus();
        return;
	}
    var sku_item_id = $('sku_item_id').value;
    var sku_item_code = $('sku_item_code').value;
    if(!sku_item_id)    return false;
	var sku_code_list = [sku_item_id];
	clear_autocomplete();
    ajax_add_multiple_item(sku_code_list);
}

var item_dept_filter = 0;
var need_check_item_dept = 1;
function ajax_add_multiple_item(sku_code_list){

    var param_str = Form.serialize(document.f_a)+"&a=ajax_add_item_row";
    var s = $H({'sku_code_list[]': sku_code_list}).toQueryString();

	// found got config set to prompt confirmation box if item is from other dept
	if(grn_item_other_dept_show_prompt){
		if(need_check_item_dept){
			check_item_dept(sku_code_list);
			return;
		}else need_check_item_dept = true;
		
		param_str += "&item_dept_filter="+item_dept_filter;
	}

	$('span_autocomplete_adding').style.display = '';
	
 	// insert new row
	ajax_request(phpself,{
		method:'post',
		parameters: param_str+'&'+s,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
			eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['html'] != undefined && json[tr_key]['ok'] == 1){
					new Insertion.Bottom($('grn_items'),json[tr_key]['html']);
				}
				
				if(json[tr_key]['error'] != undefined){
					alert(json[tr_key]['error']);
				}
			}

			add_autocomplete_extra();
		},
		onComplete: function(m){
			$('span_autocomplete_adding').style.display = 'none';
			if($('div_multiple_add_popup'))	default_curtain_clicked();
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
}

function check_item_dept(sku_code_list){
    var param_str = Form.serialize(document.f_a) + '&a=ajax_check_item_dept';
	var s = $H({'sku_code_list[]': sku_code_list}).toQueryString();
	
 	// insert new row
	ajax_request(phpself,{
		method:'post',
		parameters: param_str+'&'+s,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
            eval("var json = "+m.responseText);
			for(var tr_key in json){
				if(json[tr_key]['item_diff_dept']){
					if(!confirm("following item(s) not under GRN's department, insert anyway?\n\n"+json[tr_key]['si_desc'])){
						item_dept_filter = 1;
						if(sku_code_list.length == 1) return;
					}else item_dept_filter = 0;
				}
			}
			need_check_item_dept = 0;
			ajax_add_multiple_item(sku_code_list);
		},
	});
}

</script>
{/literal}

<div id=wait_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align=center>
		Please wait..<br /><br /><img src="ui/clock.gif" border="0" />
	</p>
</div>

<h1>GRN (Goods Receiving Note) {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}</h1>

<form name="f_a" method=post>
<input type=hidden name=a value="save">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=vendor_id value="{$form.vendor_id}">
<input type=hidden name=grr_id value="{$form.grr_id}">
<input type=hidden name=rcv_date value="{$grr.rcv_date}">
<input type=hidden name=grr_item_id value="{$form.grr_item_id}">
<input type=hidden name=type value="{$grr.type}">
<input type="hidden" name="grn_get_weight" value="{$grr.grn_get_weight}" />

<br>

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>
<table  border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRR No</b></td><td>GRR{$grr.grr_id|string_format:"%05d"}/{$grr.grr_item_id}</td>
<td><b>Branch</b></td><td>{$form.branch_code}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td>
<td colspan=3>
<input type=hidden name=department_id value="{$form.department_id}">
{$grr.department}
</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
{if $config.grn_summary_show_related_invoice && $grr.type eq 'PO'}
<td valign="top"><b>Related Invoice</b></td><td>{$grr.related_invoice}</td>
{/if}
</tr><tr>
<td width=100><b>Document Type.</b></td><td width=100><font color=blue>{$grr.type}</font></td>
<td width=100><b>Document No.</b></td><td width=100><font color=blue><input type="hidden" name="doc_no" value="{$grr.doc_no}">{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100><b>PO Amount</b></td><td width=100><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td width=100><b>Partial Delivery</b></td><td width=100><font color=blue>{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}</font></td>
<input type="hidden" name="ttl_grr_amt" value="{$grr.po_amount|round2}">
{/if}
</tr>
{if $config.grn_have_tax}
	<tr>
	    <td><b>Tax</b></td>
	    <td><input type="text" name="grn_tax" value="{$form.grn_tax}" size="5" onChange="tax_changed();" /> %</td>
	</tr>
{/if}
</table>
</div>

<div style="padding:10px 0;">
<!-- SKU search -->
{*<table class="tl" cellpadding=2 cellspacing=0 border=0>
<tr>
	<th>Add SKU from Department</th> 
	<td><input type=button value="Select SKU" onclick="load_sku_list(vendor_id.value,{$grr.department_id})"></td>
</tr><tr>
	<th>Search SKU</th>
	<td>
		<input name="sku_item_id" size=3 type=hidden>
		<input name="sku_item_code" size=13 type=hidden>
		<input id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;">
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><input class="btn btn-primary" type=button value="Add" onclick="add_item()"></td>
</tr><tr>
	<td>&nbsp;</td>
	<td>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
	</td>
</tr>
</table>
*}
{if $config.check_block_grn_as_po}
	{include file='sku_items_autocomplete.tpl' multiple_add=1 block_list=1}
{else}
	{include file='sku_items_autocomplete.tpl' multiple_add=1 doc_block_type='grn'}
{/if}
{include file='scan_barcode_autocomplete.tpl'}
<hr noshade size=1>
<span id="span_autocomplete_adding" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Adding... Please wait</span>
</div>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<div style="overflow:auto;">
<table width=100% id=tbl_item style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
<thead>
<tr height=32 bgcolor=#ffffff class="small">
	<th>#</th>
	<th>ARMS</th>
	<th>Artno</th>
	<th>Mcode</th>
	<th width=80%>Description</th>
	<th>Selling</th>
	<th>Selling<br>UOM</th>
	{if $grr.type eq 'PO'}
	<th>PO<br>Cost</th>
	<th>Nett<br>Cost</th>
	{else}
	<th width=80>Cost</th>
	{/if}
	<th>Recv<br>UOM</th>
	<th>Recv<br>Ctn</th>
	<th>Recv<br>Pcs</th>
	<th>Amount</th>
	{if $grr.grn_get_weight}
	<th>Weight(kg)</th>
	{/if}
</tr>
</thead>

<tbody id="grn_items" class="multiple_add_container">
{foreach from=$form.items item=item name=fitem}
{include file=goods_receiving_note.new.list.tpl}
{/foreach}
</tbody>

<tfoot>
<tr bgcolor=#ffffff>
{if $grr.type eq 'PO'}
<td colspan=10 align=right><b>Total</b></td>
{else}
<td colspan=9 align=right><b>Total</b></td>
{/if}
<td colspan=2 align=center id=total_qty>&nbsp;</td>
<td class=r>
<input size=8 id=total_amt name=amount readonly class=r>
</td>
{if $grr.grn_get_weight}
<td>&nbsp;</td>
{/if}
</tr>
</tfoot>

</table>
</div>

<p align=center>
<input class="btn btn-success" type=button value="Save GRN" onclick="do_save()">

<input class="btn btn-primary" type=button value="Confirm" onclick="do_confirm()">
{if $form.id<$time_value}
<input class="btn btn-warning" type=button value="Cancel" onclick="do_cancel()">
{/if}

<input class="btn btn-error" type=button value="Close" onclick="do_close()">
</p>

</form>

<div id="sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000;background:#fff">
</div>

<script>
recalc_totals();
reset_row();
reset_sku_autocomplete();
_init_enter_to_skip(document.f_a);
new Draggable('sel_vendor_sku');
{literal}
new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
{/literal}
</script>
{include file=footer.tpl}
