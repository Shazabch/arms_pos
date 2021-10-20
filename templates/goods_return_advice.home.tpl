{*
Revision History
================
4/19/2007 yinsee
- add return type column

8/20/2009 3:22:25 PM Andy
- add reset remark

7/2/2010 4:11:39 PM Alex
- fix search bugs

8/25/2010 6:55:21 PM Alex
- add filter show inactive sku

11/1/2010 5:29:22 PM Alex
- move print function to goods_return_advice.list.tpl

5/4/2011 11:23:20 AM Justin
- Added the rounding feature for both Qty and Cost fields while adding SKU for Return.

6/22/2011 11:05:28 AM Andy
- Make SKU autocomplete default select artno as search type when consignment mode.

8/17/2011 11:40:21 AM Justin
- Added pagination for GRA items.

8/18/2011 11:11:42 AM Justin
- Fixed the delete function not working.

10/28/2011 4:17:03 PM Andy
- Add can generate GRA by import CSV.
- Move GRA Return Type to PHP become a variable, no longer hard code.

2/29/2012 4:29:30 PM Alex
- add scan barcode 

7/19/2012 5:18:23 PM Justin
- Added new tab "Disposed".

7/27/2012 3:32 PM Andy
- Add checking for non-returnable sku and block it to use.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search by autocomplete.

8/7/2012 10:10:43 AM Justin
- Enhanced to to align error message into middle of division.

7/2/2013 11:36 AM Justin
- Enhanced to show the list of batch no while click on print checklist.
- Enhanced to have 2 new tabs: Waiting for Approval & Approved.

07/18/2013 10:13 AM Justin
- Bug fixed on listing tab.

07/19/2013 11:24 AM Justin
- Enhanced to show different info while config "gra_no_approval_flow" while is turned on.

11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field

5/29/2014 4:11 PM Justin
- Enhanced import from CSV to have few options of choosing import format and delimiter.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/18/2015 11:34 AM Justin
- Enhanced to allow user choose GST Code and Inv/DO document when add return item.

4/22/2015 5:43 PM Justin
- Enhanced to always allow user to key in Inv/DO No. when add new GRA item.

4/29/2015 4:52 PM Justin
- Enhanced to pickup remark from hidden field while generating D/N.
- Enhanced to remove invoice no. and date.

5/8/2015 9:56 AM Justin
- Enhanced to allow user maintain invoice date while add new return item.

11/3/2015 1:59 PM DingRen
- add GRA approval link

11/11/2015 6:03 PM DingRen
- remove GRA approval link

7/20/2017 5:40 PM Justin
- Enhanced to have "Show All GRN Documents" checkbox that will display all GRN documents.

3/2/2018 11:29 AM HockLee
- Add new note to tell user to click the Print Checklist in order to display the GRA on GRA Checkout page. 

4/9/2018 4:09 PM Justin
- Enhanced scan barcode to allow inactive SKU item.

4/26/2018 4:22 PM Justin
- Enhanced to have foreign currency feature.

11/5/2018 9:18 AM Justin
- Removed the readonly for Inv/DO Date when adding SKU for return and added validation for the date format.

11/16/2018 10:36 AM Justin
- Bug fixed on Inv/DO Date checking will also check against empty value.

5/21/2019 10:53 AM William
- Enhance "GRA" word to use report_prefix.

04/22/2020 11:13 AM Sheila
- Modified layout to compatible with new UI.

06/24/2020 03:22 PM Sheila
- Updated button css

*}
{include file=header.tpl}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{literal}
<style>
#div_packing_no_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div_packing_no_list ul li:hover {
	background:#ff9;
}

#div_packing_no_list ul li.current {
	background:#9ff;
}

#div_packing_no_list:hover ul {
	visibility:visible;
}

.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}

<script type="text/javascript">
var gra_enable_disposal = int('{$config.gra_enable_disposal}');
var sku_non_returnable = int('{$config.sku_non_returnable}');
var phpself = '{$smarty.server.PHP_SELF}';
var gra_no_approval_flow = int('{$config.gra_no_approval_flow}');
var is_under_gst = int('{$is_under_gst|default:0}');
var foreign_currency = '{$config.foreign_currency}';
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

{if $smarty.request.t eq 0}
	var tab = 1;
{else}
	var tab = '{$smarty.request.t}';
{/if}
{literal}

// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

function reset_sku_autocomplete()
{
	var param_str = "a=ajax_search_sku&show_inactive=1&type="+getRadioValue(document.f_a.search_type);
	if(sku_non_returnable)	param_str += '&block_non_returnable=1';
	
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
			
			if (s[0]>0)
			{
				document.f_a.qty.value = '';
				document.f_a.qty.focus();
				load_vendor_list(s[0]);
			}
		}});
	}
	clear_autocomplete();
}

function clear_autocomplete()
{
	if ($('grn_barcode')){
		$('grn_barcode').value = '';
	}
	document.f_a.sku_item_id.value = '';
	document.f_a.sku_item_code.value = '';
	document.f_a.qty.value = '';
	document.f_a.cost.value = '';
	$('vendor_sel').innerHTML = '';
	$('autocomplete_sku').value = '';
	$('autocomplete_sku').focus();
	document.f_a['doc_no'].value = "";
	document.f_a['doc_date'].value = "";
	if(foreign_currency) document.f_a['currency_code'].value = "";
}

function load_vendor_list(sku_item_id)
{
	$('vendor_sel').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	
	var show_all_grn_docs = 0;
	if(document.f_a['show_all_grn_docs'] != undefined && document.f_a['show_all_grn_docs'].checked == true) show_all_grn_docs = 1;
	
    new Ajax.Updater(
    	"vendor_sel",
		"goods_return_advice.php",
	    {
			parameters: 'a=ajax_get_grn_vendor_list&sku_item_id='+sku_item_id+'&show_all_grn_docs='+show_all_grn_docs,
			evalScripts:true,
			onComplete: function(e){
				eval("var json = "+e.responseText.trim());
				var err = json['err'];
				if (err){
					var sku_item_id = '';
					var sku_item_code = '';
					var description = '';
					var qty_pcs = '';
					alert(err);
				}else{
					var sku_item_id = json['sku_item_id'];
					var sku_item_code = json['sku_item_code'];
					var description = json['receipt_description'];
					var qty_pcs = json['qty_pcs'];
				}
				
				document.f_a.sku_item_id.value = sku_item_id;
				document.f_a.sku_item_code.value = sku_item_code;
				$('autocomplete_sku').value = description; 
				if (qty_pcs > 0) document.f_a.qty.value = qty_pcs;

				load_vendor_list(sku_item_id);
			}
		});
}

function add_item()
{
	if (empty_or_zero(document.f_a.sku_item_id, 'Select SKU to return'))
	{
	    $('autocomplete_sku').focus();
	    return false;
	}
	if (empty_or_zero(document.f_a.qty, 'Please enter Quantity'))
	{
	    return false;
	}

	new Ajax.Updater('gra_items', 'goods_return_advice.php',
	{
		parameters: 'a=ajax_add_gra_item&'+Form.serialize(document.f_a),
		evalScripts: true
	});
	clear_autocomplete();
}

function del_item(id)
{
	if (confirm('Remove item from GRA?'))
	{
		var sku_type = "*";
		for(i=1;i<=3;i++){
			if ($('items_lst'+i).className =="active") sku_type = $('items_lst'+i).innerHTML;
		}
		if(sku_type == "ALL") sku_type = "*";
		new Ajax.Updater('gra_items', 'goods_return_advice.php',
		{
			parameters: 'a=ajax_del_gra_item&id='+id+"&sku_type="+sku_type,
			evalScripts: true
		});
	}
}

function returntype_sel(val)
{
    $('return_type_other').value = '';
    if(val=='other')
    {
        $('return_type_other').style.display = '';
        document.f_a.return_type_other.focus();
	}
	else
	    $('return_type_other').style.display = 'none';
}

function toggle_import_csv(){
	/*new Effect.toggle('div_import_csv','slide', {
		duration: 0.5
	});*/
	if($('div_import_csv').style.display == "none") $('div_import_csv').show();
	else $('div_import_csv').hide();
}

function import_csv(){
	if(!document.f_import_csv['f'].value){
		alert('Please select an csv.');
		return false;
	}
	
	if (!/\.csv/i.test(document.f_import_csv['f'].value))
	{
		alert("Selected file must be a valid CSV file.");
		return false;
	}
	
	if(!confirm('Are you sure to import and generate GRA?'))	return false;
	document.f_import_csv.submit();
}

function add_grn_barcode_item(value){
	var param_str = Form.serialize(document.f_a) + '&grn_barcode='+value+'&a=ajax_scan_grn_barcode&show_inactive=1';

	new Ajax.Request('goods_return_advice.php',{
	parameters: param_str,
	onComplete: function(e){
		eval("var json = "+e.responseText.trim());
		var err = json['err'];
		if (err){
			var sku_item_id = '';
			var sku_item_code = '';
			var description = '';
			var qty_pcs = '';
			alert(err);
		}else{
			var sku_item_id = json['sku_item_id'];
			var sku_item_code = json['sku_item_code'];
			var description = json['receipt_description'];
			var qty_pcs = json['qty_pcs'];
		}
		
		document.f_a.sku_item_id.value = sku_item_id;
		document.f_a.sku_item_code.value = sku_item_code;
		$('autocomplete_sku').value = description; 
		if (qty_pcs > 0) document.f_a.qty.value = qty_pcs;

		load_vendor_list(sku_item_id);
	}
});
}

function search_tab_clicked(obj){
	$('lst'+tab).className = '';
	$('search_area').show();
	obj.className = 'active';
	$('gra_list').update();
}

function do_print_checklist_dialog(id, bid){
	$('gra_id').value = id;
	$('branch_id').value = bid;
	$('div_packing_list').show();
	$('div_packing_no_list').hide();
	center_div('div_packing_list');
	curtain(true);
}

function show_type_option(){
	var params = {
		'a': 'ajax_load_packing_list',
		id: $('gra_id').value,
		bid: $('branch_id').value
	};

	$('ul_tab').update("Loading...");
	prm = '&'+$H(params).toQueryString();
	new Ajax.Request(phpself, {
		parameters: prm,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			ret = JSON.parse(str); // try decode json object
			if(ret['ok']==1 && ret['html']){ // success
				// append html
				$('ul_tab').update(ret['html']);
				
				uc($('batch_no'));
				type = $('batch_no').value;
				if($('div_packing_no_list').style.display=='none'){
					$('div_packing_no_list').show();	
					//Position.clone($('type_2'), $('div_type_list'), {setHeight: false, setWidth:false});
					//chklabel = $('issue_name').value;
					$$('#div_packing_no_list li').each(function (obj,idx){
						var batch_no = obj.readAttribute('batch_no');
						if (batch_no == type){
							obj.className = 'current';
							obj.scrollToPosition;
						}
						else{
							obj.className = '';		
						}
					});	
				}
				else{
					$('div_packing_no_list').hide();
				}
				return;
			}else{  // save failed
				if($('div_packing_no_list').style.display=='none') $('div_packing_no_list').show();
				else $('div_packing_no_list').hide();
				$('ul_tab').update("");
			}

			// prompt the error
			if(err_msg) alert(err_msg);	
		}
	});
}

function gra_select_packing_no(obj, type){
	$('batch_no').value=type;

	$('div_packing_no_list').hide();
	
}

function do_print_checklist(){
	var id = $('gra_id').value;
	var bid = $('branch_id').value;
	var bno = $('batch_no').value;

	if (!id || !bid){
		alert("GRA not found.");
		return false;
	}
	ifprint.location = 'goods_return_advice.php?id='+id+'&bid='+bid+'&a=print_checklist&bno='+bno;
	//window.open('goods_return_advice.php?id='+id+'&bid='+bid+'&a=print_checklist&bno='+a);
	curtain_clicked();
}

function curtain_clicked(){
	$('batch_no').value = "";
	$('div_packing_list').hide();
	$('print_dn_dialog').hide();
	curtain(false);
}

function toggle_dn_printing_menu(gra_id, branch_id){
	$("dn_gra_id").value = gra_id;
	$("dn_bid").value = branch_id;
	if($("remark_"+gra_id+"_"+branch_id) != undefined && $("remark_"+gra_id+"_"+branch_id).value.trim() != ""){
		$('dn_remark').value = $("remark_"+gra_id+"_"+branch_id).value.trim();
	}else $('dn_remark').value = "";
	
	showdiv("print_dn_dialog");
	center_div("print_dn_dialog");
	curtain(true);
}

function print_dn_ok(){
	if(!$('dn_gra_id').value || !$('dn_bid').value){
		alert("GRA ID or BID not found");
		return false;
	}
	
	var url = "goods_return_advice.php?a=print_arms_dn&id="+$('dn_gra_id').value+"&branch_id="+$('dn_bid').value+"&remark="+URLEncode(escape($('dn_remark').value));
	window.open(url, '_blank');
	
	list_sel(1);
	curtain_clicked();
}

function update_selected_gst(obj){
	document.f_a["gst_id"].value = "";
	document.f_a["gst_code"].value = "";
	document.f_a["gst_rate"].value = "";
	document.f_a["gst_indicator"].value = "";

	if(obj.selectedIndex >= 0){
		// got select
		var opt = obj.options[obj.selectedIndex];
		var gst_id = $(opt).readAttribute("gst_id");
		var gst_code = $(opt).readAttribute("gst_code");
		var gst_rate = $(opt).readAttribute("gst_rate");
		var gst_indicator = $(opt).readAttribute("gst_indicator");

		document.f_a["gst_id"].value = gst_id;
		document.f_a["gst_code"].value = gst_code;
		document.f_a["gst_rate"].value = gst_rate;
		document.f_a["gst_indicator"].value = gst_indicator;
	}
}

function gst_changed(vid){
	// return if user is not checking this vendor radio button and trying to change document
	if($("vendor_id_"+vid).checked == false) return;

	toggle_doc_no(vid);

	if(is_under_gst == 0) return;
	update_selected_gst(document.f_a['gst_sel']);
}

function toggle_doc_no(vid){
	if(document.f_a['doc_no_sel['+vid+']'] != undefined){
		document.f_a['doc_no'].value = document.f_a['doc_no_sel['+vid+']'].value;
		var opt = document.f_a['doc_no_sel['+vid+']'].options[document.f_a['doc_no_sel['+vid+']'].selectedIndex];
		var doc_date = $(opt).readAttribute("doc_date");
		var cost_price = $(opt).readAttribute("cost_price");
		var gi_gst_id = $(opt).readAttribute("gst_id");
		document.f_a['doc_date'].value = doc_date;
		document.f_a['cost'].value = cost_price;
		if(is_under_gst != 0 && gi_gst_id > 0) document.f_a['gst_sel'].value = gi_gst_id;
		
		if(foreign_currency){
			var currency_code = $(opt).readAttribute("currency_code");
			if(currency_code) alert("This GRN contains foreign currency, therefore system will use it on this return item.");
			document.f_a['currency_code'].value = currency_code;
		}
	}else{
		document.f_a['doc_no'].value = "";
		document.f_a['doc_date'].value = "";
		document.f_a['cost'].value = document.f_a['mst_cost_price_'+vid].value;
		if(is_under_gst != 0){
			var mst_gst_id = $('mst_gst_id_'+vid).value;
			document.f_a['gst_sel'].value = mst_gst_id;
		}
		
		if(foreign_currency) document.f_a['currency_code'].value = "";
	}
}

function toggle_grn_docs(obj){
	// need to call ajax to reload the vendor grn list...
	
	var sid = document.f_a['sku_item_id'].value;
	if(sid > 0){
		$('vendor_sel').update("<img src=ui/clock.gif align=absmiddle> Loading...");
		load_vendor_list(sid);
	}
}

function doc_date_changed(obj){
	if(obj.value.trim() != "") upper_lower_limit(obj);
}

</script>
{/literal}
{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $smarty.request.t eq 'save'}
<div class="card mx-3">
	<div class="card-body">	
<img src=/ui/approved.png align=absmiddle> GRA saved as {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"}<br>
{elseif $smarty.request.t eq 'cancel'}
<img src=/ui/cancel.png align=absmiddle> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was cancelled<br>
{elseif $smarty.request.t eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} confirmed, <span class=hilite>Printing of GRA will begin automatically</span><!--, you can select the GRA from below (under Completed tab) to print again.<br-->
{elseif $smarty.request.t eq 'reset'}
<img src=/ui/notify_sku_reject.png align=absmiddle> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was reset.
</div>
</div>
{/if}
	
<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:140px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<img src="ui/print64.png" hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_gra" checked> GRA Note (A5)<Br>
<div><Br>
<input type=checkbox name="own_copy" checked> Own Copy<Br>
<input type=checkbox name="vendor_copy" checked> Vendor Copy<Br>
</div>
<p align=center><input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>

<!-- print DN dialog -->
<div id="print_dn_dialog" style="position:absolute;z-index:10000;background:#fff;border:3px solid #000;width:350px;padding:10px;display:none;">
<table border="0" width="100%">
	<tr>
		<td colspan="2"><h3>Print D/N:</h3></td>
	</tr>
	<tr>
		<td valign="top"><b>Remark: </b></td>
		<td><textarea id="dn_remark"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="hidden" id="dn_gra_id" value="" />
			<input type="hidden" id="dn_bid" value="" />
			<input type="button" value="Print" onclick="print_dn_ok()"> 
			<input type="button" value="Cancel" onclick="curtain_clicked()">
		</td>
	</tr>
</table>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<!-- packing list menu -->
<div id="div_packing_list" class="curtain_popup" style="position:absolute;width:400px;height:220px;border:1px solid black;display:none;background-color:#fff;">
<h4>Packing List Menu</h4>
Enter Packing List #, leave blank to print the latest.<br />
<input id="batch_no" name="batch_no" size="50" onchange="uc(this);" value="{$form.batch_no}" maxlength="50"><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align="top" onclick="show_type_option();">
<div id="div_packing_no_list" style="display:none;background:#fff;border:1px solid #000;height:100px;width:325px;overflow:auto;">
<ul id="ul_tab">
</ul>
</div>
<br /><br />
<div align="center">
	<input type="hidden" value="" id="gra_id" />
	<input type="hidden" value="" id="branch_id" />
	<input type="button" value="Print" onClick="do_print_checklist();" />
	<input type="button" value="Cancel" onClick="curtain_clicked();" />
</div>
</div>
<!-- End of Reject type menu -->

<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;" class="list-group list-group-flush">
			<li class="list-group-item list-group-item-action"> <img src=ui/add.png align=absmiddle> <a href="javascript:;" onclick="togglediv('sku_list')">SKU for Return</a>
				<div id=sku_list class="stdframe p-2" style="{if !$smarty.request.sku}display:none;{/if}margin:5px 0;background:#fff;">
			
				<!-- sku search -->
				<form name=f_a>
				<table class="tl" cellpadding=2 cellspacing=0 border=0>
				{include file='scan_barcode_autocomplete.tpl' no_button=1 no_need_table=1}
				<tr>
					<th class="form-label">Search SKU<span class="text-danger" title="Required Field"> *</span></th>
					<td>
						<input name="sku_item_id" size=3 type=hidden>
						<input name="sku_item_code" size=13 type=hidden>
						<input class="form-control" id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;"> 
						<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
					<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
					</td>
				</tr>
				<tr><th class="form-label">Quantity (Pcs)<span class="text-danger" title="Required Field"> *</span></th><td>
					<input class="form-control" size=3 name="qty" onchange="this.value=float(this.value);"> </td></tr>
				<tr><th valign="top" class="form-label">Return To</th>
			
				<td>
					<b class="text-dark"><input type="checkbox" name="show_all_grn_docs" value="1" onclick="toggle_grn_docs(this);" /><span >&nbsp;Show All GRN Documents</span></b>
					<div id=vendor_sel></div>
					<div class="form-inline">
						<b class="text-dark"><input id=selvid type=radio name="vendor_id" value="-1"> Use selected Vendor</b>
					&nbsp;&nbsp;&nbsp;&nbsp;<select class="form-control" name="other_vendor_id" onchange="$('selvid').checked=true">
					{section name=i loop=$vendors}
					<option value={$vendors[i].id}>{$vendors[i].description}</option>
					{/section}
					</select>
					</div>
				</td>
				</tr>
				<tr id=tr_cost><th class="form-label">Cost</th><td>
					<input class="form-control" size=5 name="cost" onchange="mf(this, {$dp});"></td></tr>
				<tr>
					<th class="form-label">Return Type</th>
					<td>
						<select class="form-control" name=return_type onchange="returntype_sel(this.value)">
							{foreach from=$return_type_list item=rt}
								<option>{$rt}</option>
							{/foreach}
						</select>
						<span id=return_type_other style="display:none;color:blue"> Please enter: <input size=20 maxlength=20 name=return_type_other></span>
					</td>
				</tr>
				{if $is_under_gst}
					<tr>
						<th class="form-label">GST Code</th>
						<td>
							<select class="form-control" name="gst_sel" onchange="update_selected_gst(this);">
								{foreach from=$gst_list item=gst}
									<option gst_id="{$gst.id}" gst_code="{$gst.code}" gst_rate="{$gst.rate}" gst_indicator="{$gst.indicator_receipt}" {if $item.gst_id eq $gst.id and $item.gst_code eq $gst.code and $item.gst_rate eq $gst.rate}selected {/if} value="{$gst.id}">{$gst.code} - {$gst.description}</option>
								{/foreach}
							</select>
							<input type="hidden" name="gst_id" value="" />
							<input type="hidden" name="gst_code" value="" />
							<input type="hidden" name="gst_rate" value="" />
							<input type="hidden" name="gst_indicator" value="" />
						</td>
					</tr>
				{/if}
				<tr>
					<th class="form-label">Inv / DO No.</th>
					<td>
					<div class="form-inline">
						<input class="form-control" type="text" name="doc_no" size="15" value="" onchange="uc(this);" />&nbsp;&nbsp;&nbsp;
						<b>Inv / DO Date</b>&nbsp;
						<input class="form-control" type="text" name="doc_date" id="doc_date" size="8" value="{$smarty.now|date_format:'%Y-%m-%d'}" onchange="doc_date_changed(this);" onclick="if(this.value)this.select();" />
						<img align="absmiddle" src="ui/calendar.gif" id="dd_added" style="cursor: pointer;" title="Select Document Date">
					</div>
					</td>
				</tr>
				{if $config.foreign_currency}
					<tr>
						<td><b class="form-label">Currency</b></td>
						<td>
							<select class="form-control" name="currency_code">
								<option value="" selected>Base Currency</option>
								<optgroup label="Foreign Currency">
									{foreach from=$foreignCurrencyCodeList item=code}
										<option value="{$code}">{$code}</option>
									{/foreach}
								</optgroup>
							</select>
						</td>
					</tr>
				{/if}
				
				<tr><td>&nbsp;</td><td><input type=button btn class="btn btn-primary mt-2" value="Add to GRA items" onclick="add_item()"></td></tr>
				</table>
				</form>
				
				<div id=gra_items>
				{include file=goods_return_advice.items.tpl}
				</div>
				
				</div>
				</li>
				<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="{$smarty.server.PHP_SELF}?a=open&id=0">Create new GRA</a></li>
				<li class="list-group-item list-group-item-action">
					<span class="link" onClick="toggle_import_csv();"> <img src="/ui/icons/drive_add.png" align="absmiddle" /> Import by CSV</span>
					
					<div class="stdframe" id="div_import_csv" style="display:none;">
						
						<form name="f_import_csv" enctype="multipart/form-data" method="post" target="_blank" onSubmit="return false;">
							<input type="hidden" name="a" value="import_by_csv" />
							<div class="card mx-3">
								<div class="card-body">
									<table>
										<tr>
											<td><b class="form-label">Vendor</b></td>
											<td>
												<select class="form-control" name="vendor_id">
													{foreach from=$vendors item=r}
														<option value="{$r.id}">{$r.description}</option>
													{/foreach}
												</select>
											</td>
										</tr>
										<tr>
											<td><b class="form-label">Return Type</b></td>
											<td>
												<select class="form-control" name="return_type">
													{foreach from=$return_type_list item=rt}
														<option>{$rt}</option>
													{/foreach}
												</select>
											</td>
										</tr>
										<tr>
											<th align="left" class="form-label">Import Format</th>
											<td>
												<input type="radio" name="import_format" value="1" checked /> Default (MCODE or {$config.link_code_name|default:'Old Code'} | ARMS CODE | Qty)<br />
												<input type="radio" name="import_format" value="2" /> GRN Barcode (barcode)<br />
												<input type="radio" name="import_format" value="3" /> Standard (ARMS CODE or MCODE or {$config.link_code_name|default:'Old Code'} | Qty)
											</td>
										</tr>
										<tr>
											<th align="left" class="form-label">Delimiter</th>
											<td>
												<select class="form-control" name="delimiter">
													<option value="|">| (Pipe)</option>
													<option value="," selected>, (Comma)</option>
													<option value=";">; (Semicolon)</option>
												</select>
											</td>
										</tr>
										
										<tr>
											<td><b class="form-label mt-2">File</b></td>
											<td>
												<input type="file" class="mt-2" name="f" />
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td><input type="button" class="btn btn-primary mt-2" value="Import" onClick="import_csv();" />
										</tr>
									</table>
								</div>
							</div>
						</form>
					</div>
				</li>
			
				{*if $sessioninfo.privilege.GRA_APPROVAL}
					<li class="list-group-item list-group-item-action"><img src="/ui/notify_sku_approve.png" align="absmiddle" /><a href="/goods_return_advice.approval.php">GRA Approval</a></li>
				{/if*}
			
			</ul>
			
			<div class="alert alert-primary rounded mt-2">
				<ul style="list-style-type: none;"><li>Existing GRA</li>
					<li style="list-style:none;"> Note: Please click the Print Checklist in order to display the GRA on GRA Checkout page.</li>
					</ul>
			</div>
			
	</div>
</div>
 {literal}
<script>
function list_sel(n,s)
{
	var i;
	if(!gra_no_approval_flow) {
		var ttl_tabs = 6;
	}else{
		var ttl_tabs = 4;
	}
	tab = n;
	
	//if(gra_enable_disposal) ttl_tabs += 1;

	for(i=0;i<=ttl_tabs;i++)
	{
		if(!gra_enable_disposal && i == 4){
			continue;
		}
		if (i==n)
		    $('lst'+i).addClassName('selected');
		else
		    $('lst'+i).removeClassName('selected');
	}
	$('gra_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	if (n==0) pg +='&search='+ $('search').value+'&search_vendor_id='+document.f_l['search_vendor_id'].value;
	else $('search_area').hide();

	new Ajax.Updater('gra_list', 'goods_return_advice.php', {
		parameters: 'a=ajax_load_gra_list&t='+n+'&'+pg,
		evalScripts: true
		});
}
</script>
{/literal}
<div style="padding:10px 0;">
	<form name="f_l" onsubmit="list_sel(0,0);return false;">

	<div class=tab style="white-space:nowrap;">
		<div class="row mx-3 mb-2">
		<div class="col">
			<a href="javascript:list_sel(1)" id="lst1" class="btn btn-outline-primary btn-rounded ">Saved GRA</a>
		{if !$config.gra_no_approval_flow}
			<a href="javascript:list_sel(5)" id="lst5" class="btn btn-outline-primary btn-rounded ">Waiting for Approval</a>
		{/if}
		<a href="javascript:list_sel(3)" id="lst3" class="btn btn-outline-primary btn-rounded ">Cancelled/Terminated</a>
		{if !$config.gra_no_approval_flow}
			<a href="javascript:list_sel(6)" id="lst6" class="btn btn-outline-primary btn-rounded ">Approved</a>
		{/if}
		<a href="javascript:list_sel(2)" id="lst2" class="btn btn-outline-primary btn-rounded ">Completed</a>
		{if $config.gra_enable_disposal}
			<a href="javascript:list_sel(4)" id="lst4" class="btn btn-outline-primary btn-rounded ">Disposed</a>
		{/if}
		</div>
		<div class="col">
			<a name="find" class="btn btn-outline-primary btn-rounded" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">
				Find GRA / Vendor
			</a>
		</div>
</div>
	</div>
	<div class="card mx-3">
		<div class="card-body">
			<div >
				<div id="search_area" {if (!$smarty.request.search && !$smarty.request.vendor_id) && $smarty.request.t ne '0'}style="display:none;"{/if}>
					<div class="table-responsive">
						<table>
							<tr>
								<th align="left">Vendor</th>
								<td colspan="2">
									<input name="search_vendor_id" type="hidden" size="1" value="{$smarty.request.search_vendor_id}" readonly>
									<input id="autocomplete_vendor" name="vendor" value="{$smarty.request.vendor}" size=50>
									<div id="autocomplete_vendor_choices" class="autocomplete"></div><br />
								</td>
							</tr>
							<tr>
								<th align="left">Find GRA</th>
								<td><input name="search" id="search" name="find" value="{$smarty.request.search}"></td>
								<td align="right"><input class="btn btn-primary" type="submit" value="Go"></td>
							</tr>
						</table>
					</div>
				</div>
				<div id="gra_list" align="center">
					{include file=goods_return_advice.list.tpl}
				</div>
			</div>
		</div>
	</div>
	</form>
</div>

<script>
reset_sku_autocomplete();
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_l.search_vendor_id.value = li.title; }});

Calendar.setup({
	inputField     :    "doc_date",
	ifFormat       :    "%Y-%m-%d",
	button         :    "dd_added",
	align          :    "Bl",
	singleClick    :    true
});
{/literal}
list_sel(1);
{if $smarty.request.t eq 'confirm'}
//if (confirm('Do you like to print the GRA now?')) do_print({$smarty.request.id},{$sessioninfo.branch_id},true);
$('gra_id').value = '{$smarty.request.id}';
$('branch_id').value = '{$sessioninfo.branch_id}';
do_print_checklist();
{/if}
new Draggable('div_packing_list');
//init_calendar();

</script>

{include file=footer.tpl}
