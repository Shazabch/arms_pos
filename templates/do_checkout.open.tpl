{*
REVISON HISTORY
+++++++++++++++
10/5/2007 4:28:13 PM gary
- add "DO/" for diaplaying DO no.

2/20/2008 11:24:24 AM gary
- add show owner column to view.

8/6/2009 5:46:16 PM Andy
- edit do_type assign by php

8/12/2009 12:32:06 PM Andy
- add context menu and popup cost history

11/10/2009 3:07:46 PM Andy
- Add to show invoice discount

3/4/2010 3:55:31 PM Andy
- Fix sometime Credit Sales DO cannot show item in checkout page (debtor_id bugs)

5/10/2010 3:12:32 PM Andy
- Add DO Markup.

5/17/2010 1:50:22 PM Andy
- Add DO auto split by price type can automatically insert DO Discount base on branch trade discount. (need config)
- DO Markup can now be use as DO Discount as well.

6/2/2011 11:47:40 AM Justin
- Amended the DO header to show Address Deliver To if found got config of consignment_modules and masterfile_branch_region.
- Added new JS feature to hide/show all foreign currency fields.

2/6/2012 4:52:43 PM Justin
- Added to show S/A list when found from DO.

4/23/2012 1:31:42 PM Alex
- add print do checkout button

4/26/2012 4:30:15 PM Alex
- Change deliver to based on do_type 

5/9/2012 5:32:43 PM Justin
- Fixed bug of system shows wrong Sales Agent.

3/20/2014 1:30 PM Justin
- Enhanced to to show checklist qty & variance.

3/23/2015 10:37 AM Andy
- Fix wrong colspan on consignment modules when got currency.

3/8/2016 2:23 PM Qiu Ying
- Put added timestamp above DO Date
- DO Date can change and save in checkout page.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

3/3/2017 9:43 AM Andy
- Change 'General Informations' to 'General Information'.

3/29/2017 4:20 PM Justin
- Enhanced to have new privilege checking for user to reset DO.

4/4/2017 9:56 AM Justin
- Bug fixed the "Back to Checklist" button always shows out even the config is off.
- Enhanced the reset button to also check current branch against DO's branch.
- Enhanced to have new button "Load Last Driver Info" which able to loads last driver information from a checkout DO.
- Enhanced to show title when mouseover to the lorry icon.

4/14/2017 8:55 AM Qiu Ying
- Bug fixed on load last driver still enable in view mode

4/19/2017 3.35 PM Khausalya 
- Enhanced changes from RM to use config setting. 

9/25/2018 5:35 PM Andy
- Enhanced DO Printing to use shared templates.

10/5/2018 3:06 PM Justin
- Enhanced to have Shipment Method and Tracking Code during checkout.

26/3/2020 10:58 AM William
- Enhanced to add upload image feature.

05/13/2020 6:31PM Sheila
- Updated button color

09/06/2020 10:33AM Sheila
- Updated "Back to Checklist" button size

*}
{include file=header.tpl}

{assign var=do_type value=$form.do_type}

{if $do_type eq 'transfer'}
    {assign var=show_discount value=$config.do_transfer_have_discount}
{elseif $do_type eq 'open'}
    {assign var=show_discount value=$config.do_cash_sales_have_discount}
{else}
    {assign var=show_discount value=$config.do_credit_sales_have_discount}
{/if}

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="js/do.js"></script>

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
#div_sku_details{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}
#div_sku_details_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_sku_details_content{
    padding:2px;
}

.pv{
	color:#fff;
	background:#0c0;
	font-weight:bold;
	font-size:1.1em;
	padding:0 4px;
}

.nv{
	color:#fff;
	background:#e00;
	font-weight:bold;
	font-size:1.1em;
	padding:0 4px;
}
#upload_popup{
	border: 2px solid #000;
	background: #fff;
	width: 300px;
	height: 155px;
	padding: 10px;
	position: absolute;
	text-align: center;
	z-index: 10000;
}
.div_img{
	float: left;
	height: auto;
	width:110px;
	border: 1px solid #999;
	margin: 4px 5px;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var current_branch_code = '{$BRANCH_CODE}';
var current_total_inv_amt = 0;
var consignment_modules = "{$config.consignment_modules}";
var masterfile_branch_region = "{$config.masterfile_branch_region}";
var consignment_multiple_currency = "{$config.consignment_multiple_currency}";
var create_type = "{$form.create_type}";
var show_discount = '{$show_discount}';
var do_invoice_separate_number = {if $config.do_invoice_separate_number}true{else}false{/if};

var branch_id_code = [];
var branch_exchange_rate = [];
var branch_currency_code = [];
var do_inv_no = [];
var view_only = "{$view_only}";
var is_checkout = int("{$form.checkout}");
var currency_symbol = "{$config.arms_currency.symbol}";

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');
{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';


{foreach from=$all_branch item=b}
    branch_id_code['{$b.id}'] = '{$b.code}';
	{if $config.consignment_modules && $config.masterfile_branch_region && $config.consignment_multiple_currency}
		{if $form.do_branch_id eq $b.id}
			{assign var=exchange_rate value=$form.exchange_rate}
		{else}
			{assign var=exchange_rate value=$b.exchange_rate}
		{/if}
		branch_exchange_rate['{$b.id}'] = "{$exchange_rate|escape:javascript}";
		branch_currency_code['{$b.id}'] = "{$b.currency_code|escape:javascript}";
	{/if}
{/foreach}

{literal}

//================copy from do checkout too
function do_print(id, bid,dummy,markup){
	DO_PRINT.do_print(id,bid,is_checkout,markup,is_under_gst);
}

/*function print_ok()
{
	$('print_dialog').style.display = 'none';
	//document.f_print.target = "ifprint";
	document.f_print['branch_id'].value = g_bid;
	document.f_print['id'].value = g_do_id;
	document.f_print.submit();
	
	//$('ifprint').src = '/do.php?id='+g_do_id+'&branch_id='+g_bid+'&'+Form.serialize(document.f_print);
	//window.open('/do.php?id='+g_do_id+'&branch_id='+g_bid+'&'+Form.serialize(document.f_print));
	curtain(false);
}*/

//=====================================================
function do_confirm(){
	{/literal}
	{if !$config.do_checkout_no_need_lorry_info}
	if (empty(document.f_a.elements['checkout_info[lorry_no]'], 'Please enter Lorry No')) return false;
	if (empty(document.f_a.elements['checkout_info[name]'], 'Please enter Driver Name')) return false;
	{/if}
	{literal}
//	if (empty(document.f_a.elements['checkout_info[nric]'], 'Please enter Driver IC No')) return false;
	
	if (confirm('Confirm DO?')){
		//btn.disabled=true;
		document.f_a.submit();
	}		
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

change_branch_code_for_stock_balance2 = function(){
	if(document.f_a['do_branch_id'])    var bid = document.f_a['do_branch_id'].value;
	else var bid = '';
	var branch_code = '';

	if(!bid)   branch_code = 'N/A';
	else    branch_code = branch_id_code[bid];

	$('span_branch_code2').update(branch_code);
	if ($('span_parent_branch_code2') != null) {
		$('span_parent_branch_code2').update(branch_code);
	}
}

function do_reset(){
    document.f_do_reset['reason'].value = '';
	var p = prompt('Enter reason to Reset :');
	if (p==null || p.trim()=='' ) return false;
	document.f_do_reset['reason'].value = p;

	if(!confirm('Are you sure to reset?'))  return false;

	document.f_do_reset.submit();
	return false;
}

show_context_menu = function(obj, sku_item_id)
{
	context_info = { element: obj, sku_item_id: sku_item_id};
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	Element.show('item_context_menu');

	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}

	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

hide_context_menu = function()
{
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;
	Element.hide('item_context_menu');
}

get_item_cost_history = function(sku_item_id){
	$('div_sku_details_content').update(_loading_);
	curtain(true);
	center_div('div_sku_details');
	$('div_sku_details').show();

	new Ajax.Updater('div_sku_details_content','do.php',{
	    method: 'post',
		parameters:{
			a:'ajax_get_credit_sales_item_cost_history',
			sku_item_id: sku_item_id,
			debtor_id: document.f_a['debtor_id'].value,
			branch_id: document.f_a['branch_id'].value
		}
	});
}

function curtain_clicked(){
	$('div_sku_details').hide();
	$('print_dialog').style.display = 'none';
	$('upload_popup').hide();
	curtain(false);
}

foreign_variable_handler = function(need_recalc){

	var do_branch_id = document.f_a['do_branch_id'].value;
	if(!consignment_multiple_currency || !do_branch_id || branch_currency_code[do_branch_id] == "" || branch_currency_code[do_branch_id] == currency_symbol){
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
		document.f_a['exchange_rate'].readOnly = false;
		$('foreign_price').style.display = "";
		$('foreign_ttl_amt').style.display = "";
		if($('foreign_inv_amt')) $('foreign_inv_amt').style.display = "";
		$$("#new_sheets .foreign_amt").invoke("show");
		$$("#new_sheets .foreign_cost_price").invoke("show");
		if($$("#new_sheets .foreign_inv_amt") != null) $$("#new_sheets .foreign_inv_amt").invoke("show");
		if($$("#new_sheets .total_foreign_inv_amt") != null) $$("#new_sheets .total_foreign_inv_amt").invoke("show");
		if($$("#new_sheets .total_foreign_amount") != null) $$("#new_sheets .total_foreign_amount").invoke("show");
		//$("total_colspan").colSpan = float($("total_colspan").colSpan)+1;
		if($('span_poc_currency_code')) $('span_poc_currency_code').update(branch_currency_code[do_branch_id]);
		$('span_p_currency_code').update(branch_currency_code[do_branch_id]);
		$('span_amt_currency_code').update(branch_currency_code[do_branch_id]);
		if($('span_inv_amt_currency_code')) $('span_inv_amt_currency_code').update(branch_currency_code[do_branch_id]);
		
		var ttl_colspan = document.f_a['colspan_length'].value;
		$("total_colspan").colSpan = float(ttl_colspan)+1;
		$("td_sub_total").colSpan = float(ttl_colspan)+1;
		$("td_inv_discount").colSpan = float(ttl_colspan)+1;
		$$('#tr_sub_total .td_sub_total_foreign_col').invoke("show");
		$$("#tr_sheet_inv_discount_row .td_inv_discount_foreign_col").invoke("show");
	}
	
	if(need_recalc != undefined) foreign_variable_recalc();
}

function do_checklist(){
	if(!confirm("Are you sure want to go back to Checklist page?")) return;
	
	var id = document.f_a['id'].value;
	var bid = document.f_a['branch_id'].value;
	document.location='/do_checkout.php?a=open&id='+id+'&branch_id='+bid;
}

//=========copy from do
function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true
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
	var do_date = document.f_a['do_date'].value;
	if (is_under_gst) {
		if(strtotime(do_date) < strtotime(global_gst_start_date) && strtotime(do_date) < strtotime(branch_gst_start_date)){
			var temp_date = 0;
			if (branch_gst_start_date) {
				temp_date = branch_gst_start_date;
			}else{
				temp_date = global_gst_start_date;
			}
			alert('DO Date must greater than ' + temp_date);
			document.f_a['do_date'].value = date_now;
		}
	}

}

function load_driver_info(){
	// show loading icons
	$(load_di_btn).disabled = true;
	
	// construct params
	var params = {
		a: 'ajax_load_driver_info'
	};
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){
			// enable back the button
			$('load_di_btn').disabled = false;
			
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					if(ret['lorry_no'] != "") document.f_a['checkout_info[lorry_no]'].value = ret['lorry_no'];
					if(ret['name'] != "") document.f_a['checkout_info[name]'].value = ret['name'];
					if(ret['nric'] != "") document.f_a['checkout_info[nric]'].value = ret['nric'];
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

function add_image(){   //upload image popup
	curtain(true);
	center_div('upload_popup');
	$('upload_popup').show();
}

function upload_check(){
	var filename = document.upl['fnew'].value;
	var file = document.upl['fnew'].files[0];
	
	if (!/\.jpg|\.jpeg/i.test(filename)){	 //check file format type
		alert("Selected file must be a valid JPEG or jpg file");
		document.upl['fnew'].value = '';
		return;
	}
	if (file.size > 1048576){				 //check file size limit
		alert("Upload file size cannot more than 1 MB.");
		document.upl['fnew'].value =  '';
		return;
	}
	return true;
}

function upload_img_callback(img_path){
	var do_checkout_img_list = $('do_checkout_img_list');
	
	do_checkout_img_list.insertAdjacentHTML('beforeend', '<div class="div_img"><img width="100" height="90" align="absmiddle" vspace="4" hspace="4" src=\"'+img_path+'\" border="0" onclick="show_img_full(\''+img_path+'\')" title="View"><br><span style="padding:3px;cursor:pointer;" title="Delete" onclick="delete_image(this.parentNode, \''+img_path+'\')"> <img src=\"/ui/del.png\" align="absmiddle">  Delete</span></div>');
	
	document.upl.fnew.value = '';
	curtain_clicked();
}

function upload_img_error_msg(err_msg){	    //error message return of upload img
	alert(err_msg);
}

function delete_image(obj, file_path){     //delete do checkout image
	if (confirm('Are you sure?')){
		var id = document.upl['id'].value;
		var bid = document.upl['branch_id'].value;
		ajax_request('do_checkout.php',{
			method: 'post',
			parameters: 'a=ajax_remove_image&f='+encodeURIComponent(file_path)+'&id='+id+'&bid='+bid,
			onComplete: function(m) {

				if (m.responseText == 'OK'){
					obj.remove();

				}else alert(m.responseText);
			}
		});
	}
}

function show_img_full(path){    //view full size image of do checkout
	var img_str = "<img src='"+path+"' width='640' onload=\"center_div('img_full');\">";
	popup_div('img_full', img_str);
}

function update_do_checkout(){
	document.f_a.a.value='update_do_checkout';
	document.f_a.submit();
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>{$PAGE_TITLE} (DO/{$form.do_no})</h4>
				
			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div id="div_sku_details" style="position:absolute;z-index:10000;width:500px;height:450px;display:none;border:2px solid #CE0000;">
	<div id="div_sku_details_header"><span style="float:left;">SKU Items Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_details_content"></div>
</div>

<div id="upload_popup" style="display:none;">
	<form onsubmit="return upload_check()" name="upl" target="_ifs" enctype="multipart/form-data" method="post">
	<h4>Select an image to add</h4>
	<input type="hidden" name="a" value="upload_img">
	<input type="hidden" name="branch_id" value="{$form.branch_id}">
	<input type="hidden" name="id" value="{$form.id}" >
	
	<input name="fnew" type="file"><br>
	<br>
	<ul>
		<li>Please select valid JPG/JEPG image.</li>
		<li>File maximum size is 1 MB.</li>
	</ul>
	<input type="submit" value="Upload"> <input type="button" value="Cancel" onclick="curtain_clicked()">
	</form>
	<iframe name="_ifs" width=1 height=1 style="visibility:hidden"></iframe>
</div>

<div id="item_context_menu" style="display:none;position:absolute;">
<ul id="ul_menu" class="contextmenu">
<li><a href="javascript:void(0)" onclick="hide_context_menu();get_item_cost_history(context_info.sku_item_id)"><img src="/ui/icons/clock.png" align="absmiddle"> Credit Sales Cost History</a></li>
</ul>
</div>

<form name="f_do_reset" method="post" style="display:none;" action="do.php">
<input type="hidden" name="a" value="do_reset">
<input type="hidden" name="branch_id" value="{$form.branch_id}">
<input type="hidden" name="id" value="{$form.id}" >
<input type="hidden" name="reason" value="">
<input type="hidden" name="do_date" value="{$form.do_date}">
<input type="hidden" name="page_type" value="checkout">
</form>

<form name="f_a" method="post" ENCTYPE="multipart/form-data" onsubmit="return false;">
<input type="submit" style="display:none;"><!-- cheat form submit to have a submit button so that can use others button for other purposes -->
<input type="hidden" name="a" value="confirm">
<input type="hidden" name="branch_id" value="{$form.branch_id}">
<input type="hidden" name="id" value="{$form.id}">
<input type="hidden" name="reason" value="">
<input type="hidden" name="do_no" value="{$form.do_no}">
<input type="hidden" name="do_branch_id" value="{$form.do_branch_id}">
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
<input type="hidden" name="debtor_id" value="{$form.debtor_id}">
<input type="hidden" name="price_indicate" value="{$form.price_indicate}">
<input type="hidden" name="discount" value="{$form.discount}">
<input type="hidden" name="first_checkout_date" value="{$form.first_checkout_date}">
<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}">

<div class="card mx-3">
<div class="card-body">
	<div class="stdframe" >
		<h4 class="form-label">General Information<br/> <a style="font-size:x-small" target="_blank" href="do.php?a=open&id={$form.id}&branch_id={$form.branch_id}&type={$do_type}">(View DO Information)</a></h4>
		
		{if $errm.top}
		<div class="alert alert-danger rounded">
			<div id=err><div class=errmsg><ul>
				{foreach from=$errm.top item=e}
				<li> {$e}</li>
				{/foreach}
				</ul></div></div>
		</div>
		{/if}
		
		
		<table border=0 cellspacing=0 cellpadding=4>
		<tr align=left>
		<td>
			<table>
			<tr>
				<th width=80 align=left class="form-label">Added Date</th>
				<td>{$form.added}</td>
			</tr>
			<tr>
			<th width=80 align=left class="form-label">DO Date</th>
			<td width=150>
			{if !$view_only}
				<input name="do_date" id="added1" size=12 onchange="on_do_date_changed();" maxlength=10 value="{if $form.first_checkout_date}{$form.do_date|date_format:"%Y-%m-%d"}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}">
				<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				
			{else}
				{$form.do_date|date_format:"%Y-%m-%d"}
			{/if}
			</td>
			
			{if $form.po_no}
				<th align=left width=80 class="form-label">PO No.</th>
				<td width=150>
				{$form.po_no}
				</td>
			{/if}	
			
			<th align=left width=80 class="form-label">Owner</th>
			<td style="color:blue;" align=left width="150">
			{$form.user}
			</td>
			{if $config.do_enable_do_markup}
				<th align="left" class="form-label">DO Markup(+) / Discount(-)</th>
				<td><div class="form-inline">
					<input class="form-control" type="text" value="{$form.do_markup}" size="3" style="text-align:right;" readonly />%
				</div></td>
			{/if}
			</tr>
			{if !$form.first_checkout_date && !$view_only}
				<tr>
					<td></td>
					<td colspan=3 nowrap style="background-color:yellow"><img align="absmiddle" src="ui/icons/information.png" /> System have automatically help you to change DO Date to today.</td>
				</tr>			
			{/if}
			</table>
		</td>
		</tr>
		
		<tr>
		<td>
			<table>
			{if $do_type eq 'transfer'}
				<td valign=top width=80><b class="form-label">Deliver From</b></td>
				<td valign=top>
				{$form.from_branch_name}
				- {$form.from_branch_description}
				</td>
			{/if}
			<tr>
				<td valign=top width=80><b class="form-label">Deliver To</b></td>
				<td valign=top>
				{if $do_type eq 'credit_sales'}
					{assign var=debtor_id value=$form.debtor_id}
					Debtor: {$debtor.$debtor_id.code} - {$debtor.$debtor_id.description}
				{elseif $do_type eq 'open'}
					Company Name: {$form.open_info.name} - {$form.open_info.address}
				{else}
					{$form.do_branch_name|default:$form.open_info.name}{if $form.do_branch_description} - {$form.do_branch_description}{/if}
				{/if}
				</td>
			</tr>
			{if $config.consignment_modules && $config.masterfile_branch_region && $do_type eq 'transfer'}
				<tr>
					<td>&nbsp;</td>
					<td>
						{if $form.use_address_deliver_to}
							<img src="/ui/checked.gif" id="sn_dtl_icon" align="absmiddle">
						{else}
							<img src="/ui/unchecked.gif" id="sn_dtl_icon" align="absmiddle">
						{/if}
						<b> Use Deliver To Address from Branch</b>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td id="span_adt" {if !$form.use_address_deliver_to}style="display:none;"{/if}>
						<textarea class="form-control" rows="5" cols="30" name="address_deliver_to" readonly>{$form.address_deliver_to}</textarea>
					</td>
				</tr>
				{if $config.consignment_multiple_currency}
					<tr>
						<td><b class="form-label">Exchange Rate</b></td>
						<td>
							<input type="text" name="exchange_rate" size="15" value="{$form.exchange_rate}" onchange="this.value=float(this.value)" class="r form-control" readonly>
						</td>
					</tr>
				{/if}
			{/if}
			{if $config.masterfile_enable_sa && $form.mst_sa}
				<tr>
					<td><b class="form-label">Sales Agent</b></td>
					<td>
						<div style="width:400px;height:100px;border:1px solid #ddd;overflow:auto;" id="do_sa_list">
							{foreach from=$form.mst_sa name=i key=r item=sa_id}
								{$sa_list.$sa_id.code} - {$sa_list.$sa_id.name}<br />
							{/foreach}
						</div>
					</td>
				</tr>
			{/if}
			</table>
		</td>
		</tr>
		
		<tr>
		<td>
			<table>
			<tr>
			<td valign="middle" width="80">
			<b class="form-label">Lorry No.</b> {if !$config.do_checkout_no_need_lorry_info}*{/if}</td>
			
			<td width="150">
				<input class="form-control" name="checkout_info[lorry_no]" value="{$form.checkout_info.lorry_no|escape:'html'}" size="10" onchange="uc(this);">
				{if !$view_only}
					<button class="btn btn-primary" id="load_di_btn" onclick="load_driver_info();" title="Load Last Driver Info"><img src="ui/icons/lorry_go.png"></button>
				{/if}
			</td>
			
			<td valign="middle" width="80" align="left">
			<b class="form-label">Driver Name</b> {if !$config.do_checkout_no_need_lorry_info}*{/if}</td>
			
			<td width="350">
			<input class="form-control" name="checkout_info[name]" value="{$form.checkout_info.name|escape:'html'}" size="40" onchange="uc(this);"></td>
			
			<td valign="middle" width="80" align="left"><b class="form-label">IC No.</b></td>
			<td><input class="form-control" name="checkout_info[nric]" value="{$form.checkout_info.nric|escape:'html'}" size="15" onchange="uc(this);"></td>
			</tr>
			<tr>
				<td nowrap><b class="form-label">Shipment Method</b></td>
				<td><input class="form-control" name="shipment_method" value="{$form.shipment_method|escape:'html'}" size="20" onchange="uc(this);"></td>
				<td nowrap><b class="form-label">Tracking Code</b></td>
				<td><input class="form-control" name="tracking_code" value="{$form.tracking_code|escape:'html'}" size="25" onchange="uc(this);"></td>
			</tr>
			<tr>
				<td><b class="form-label">Upload Image</b></td>
				<td>{if $enable_edit or !$view_only}<img src="/ui/add.png"  onclick="add_image();" />{/if}</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
		
		</td>
		</tr>
		</table>
		<div style="display:flex;">
			<div id="do_checkout_img_list" style="display:block;">
			{foreach from=$form.image_list item=img}
			<div class="div_img">
				<img width="100" height="90" align="absmiddle" vspace="4" hspace="4" src="{$img}" border="0" onclick="show_img_full('{$img}')"  title="View"><br>
			
				{if $enable_edit || !$view_only}<span style="padding:4px;cursor:pointer;" title="Delete" onclick="delete_image(this.parentNode,'{$img}')"> <img src="/ui/del.png" align="absmiddle">  Delete</span>{/if}
			</div>
			{/foreach}
			</div>
		</div>
		
		</div>
</div>
</div>

<br>
{if (count($form.deliver_branch)>0 || $form.do_branch_id || $form.debtor_id)}
<div id="new_sheets">
<div class="card mx-3 mt-3">
	<div class="card-body">
		{include file=do.new.sheet.tpl}
	</div>
</div>

<br>
<div class="card mx-3">
	<div class="card-body">
		<h4 class="form-label">Additional Remark</h4>
<textarea class="form-control" style="width:100%;height:100px;" name=checkout_remark>
{$form.checkout_remark|escape}
</textarea>
	</div>
</div>
</div>
{/if}

</form>

<form name="f_sn">
<span id="sn_title" name="sn_title"><br /><h4>Serial No Details <img src="/ui/expand.gif" id="sn_dtl_icon" onclick="sh_serial_no(this);" align=absmiddle></h4></span>
<div class="sn_details" id="sn_details" style="display:none;">
	{foreach from=$do_items item=item name=fitem}
		{if $item.serial_no || $item.have_sn}
			{include file="do.sn.new.tpl"}
		{/if}
	{/foreach}
</div>
</form>

<p id=submitbtn align=center>
{if $form.checkout eq 1 && ($sessioninfo.level>=$config.doc_reset_level || $sessioninfo.privilege.DO_ALLOW_USER_RESET) && ($form.branch_id eq $sessioninfo.branch_id || $config.consignment_modules) && !$enable_edit}
    <input class="btn btn-warning" type=button value="Reset" onclick="do_reset();">
{/if}
{if $enable_edit}
<input class="btn btn-success" type="button" value="Save" onclick="update_do_checkout();" />
{/if} 
<input class="btn btn-danger" type=button value="Close" onclick="document.location='/do_checkout.php'">

{if !$view_only}
	{if $config.do_checkout_scan_item_variance}
		<input class="btn btn-warning" type=button value="Back to Checklist" onclick="do_checklist()">
	{/if}
<input class="btn btn-primary" type=button value="Confirm & Print" onclick="do_confirm()">
{elseif !$enable_edit}
<input class="btn btn-primary" type=button value="Print{if $form.checkout} DO Checkout{/if}" onclick="do_print('{$form.id}','{$form.branch_id}','','{$form.invoice_markup}')">
{/if}
</p>

<!-- print dialog -->
{include file="do.print_dialog.tpl"}
<!--end print dialog-->

{include file=footer.tpl}
<script>
{if !$view_only}
init_calendar();
{/if}
{if !$form.total_inv_amt && $show_discount}
	$('inp_total_inv_amt').value = current_total_inv_amt;
	$('span_total_inv_amt').update(round(current_total_inv_amt,2));
{/if}

{if $view_only}
Form.disable(document.f_a);
{/if}
Form.disable(document.f_sn);
{literal}
new Draggable('div_sku_details',{ handle: 'div_sku_details_header'});
if($('sn_details').innerHTML.trim() == '') $('sn_title').hide();
{/literal}
{if $enable_edit}
	{literal}
	document.f_a['shipment_method'].disabled=false;
	document.f_a['tracking_code'].disabled=false;
	document.f_a['a'].disabled=false;
	{/literal}
{/if}
{if $form.do_branch_id && $do_type eq 'transfer' && $config.consignment_modules && is_array($config.consignment_multiple_currency)}foreign_variable_handler();{/if}
DO_PRINT.initialise();
</script>
