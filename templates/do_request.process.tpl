{*
 11/3/2009 5:40:44 PM Andy
 - Add Reject function
 
 6/13/2012 11:39:23 AM Justin
 - Added to show only HQ selection while logged on branch was a franchise.
 
 11/26/2012 10:13:00 AM Fithri
 - New Tab "Exported to PO" for item (deliver qty < default request qty && po_qty >0)
 - can tick and print picking list"
 
12/10/2012 2:09 PM Andy
- Add Expected Delivery Date. can be disabled by config "do_request_no_expected_delivery_date".
- Add when process DO Request can filter expected delivery date.
- Add can sort by expected delivery date.

12/12/2012 5:00 PM Andy
- Add legend to let user know the maximus expected delivery date.
- Remove clicking on expected delivery date sorting.

12/14/2012 11:37 AM Andy
- Remove the expect do date restriction on Process DO Request.
- Add default "expect do date to" if got config "do_request_process_maximum_filter_expect_delivery_date_day".

2/8/2013 2:12 PM Justin
- Enhanced to show user a list of reject reason while found config.
- Enhanced to provide confirmation before do reject.

2/19/2013 4:39 PM Justin
- Enhanced to add new sorting type "Department".

3/5/2013 5:04 PM Andy
- Change after 1.5 second of print picking list, then only reload the item list.

03/24/2016 09:30 Edwin
- Added on decline to print picking list when supply branch's stock balance <= 0 if config do_request_process_restrict_print_if_no_stock is enabled

04/12/2016 15:45 Edwin
- Revert back to enable select/unselect all item function

3/21/2019 5:36 PM Andy
- Enhanced to show DO Request SKU Photo.

5/27/2019 5:25 PM Justin
- Enhanced to have reject items by selection.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#tbody_item_list tr:hover{
	background-color:#ffa;
}
td.editable{
	background-color:yellow;
}
#ul_process_action_select{
	list-style:none;
	margin:0;
	padding:0;
}
#ul_process_action_select li{
	float:left;
	margin: 1px;
	padding: 5px;
	border:1px solid #ddd;
}
#ul_process_action_select li:hover{
	border:1px solid black;
	cursor: pointer;
}
#ul_process_action_select li.active{
	border:1px solid black;
	background-color:#fe9;
}

#div_reject_reason_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div_reject_reason_list ul li:hover {
	background:#ff9;
}

#div_reject_reason_list ul li.current {
	background:#9ff;
}

#div_reject_reason_list:hover ul {
	visibility:visible;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;
var branch_id = 0;
var clear_selected = 0;

var do_request_no_expected_delivery_date = int('{$config.do_request_no_expected_delivery_date}');
var do_request_reject_reason = '{$config.do_request_reject_reason}';

{literal}
var search_str = '';
var is_escape = false;
var last_obj;
var process_action_popup_timer;
var process_action_ele;
var item_id_list = [];
var PROCESSING = '<span style="background-color:#fe9;padding:2px;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>';
var curr_sort = {};
var curr_sort_order = {};

function list_sel(selected){
	if(selected==4){
		var tmp_search_str = $('inp_item_search').value.trim();
		
		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
	}
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}	
	
	var all_tab = $$('.tab .a_tab');
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');
	
	var params = {
		search_str: search_str,
		branch_id: branch_id,
		clear_selected: clear_selected,
		sort_by: document.f_a['sort_by'].value,
		sort_order: document.f_a['sort_order'].value
	}
	
	if(!do_request_no_expected_delivery_date){
		var expected_do_date_from = document.f_a['expect_do_date_from'].value;
		var expect_do_date_to = document.f_a['expect_do_date_to'].value;
		
		params['expected_do_date_from'] = expected_do_date_from;
		params['expect_do_date_to'] = expect_do_date_to;
	}
	
	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters: params,
		onComplete: function(msg){
			
		},
		evalScripts: true
	});
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(4);
	}
}

function print_picking_list(picking_list_id,exported_po){
	if(!branch_id){
		alert('Please select request branch');
		return;
	}
	if(!confirm('Click OK to print.'))	return;
	document.fprint['from_branch'].value = branch_id;
	if(picking_list_id) document.fprint['picking_list_id'].value = picking_list_id;
	if (exported_po) {
		document.fprint['exported_to_po'].value = '1';
	}
	else {
		document.fprint['exported_to_po'].value = '0';
	}
	document.fprint.submit();
	document.fprint['picking_list_id'].value = '';
	//alert('ready to reload');
	setTimeout(function(){
		list_sel();
	},1500);
}

function request_branch_change(){
	reload_list();
}

function do_edit(obj){
	is_escape = false;
	last_obj = obj;
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	$('edit_popup').show();
	$('edit_text').select();
	$('edit_text').focus();
}

function checkKey(event){
    if (event == undefined) event = window.event;
	//alert(event.keyCode);
	if(event.keyCode==13){  // enter
		save();
	}else if(event.keyCode==27){    // escape
	    remove_focus();
	}
	event.stopPropagation();
}

function remove_focus(){
	is_escape = true;
    document.f_a['branch_id'].focus();
}

function save(){
	var old_value = int(last_obj.innerHTML);
	var new_value = int($('edit_text').value);
	
	if(new_value<0){
		//alert('Invalid Qty');
		$('edit_popup').hide();
		remove_focus();
		return;
	}
	
	$('edit_popup').hide();
	
	if(is_escape)   return;
	if(old_value!=new_value){
	    var data = last_obj.id.split(',');
	    var data_type = data[0];
	    var id = data[1];
		var branch_id = data[2];
		

		last_obj.update('Saving..');
		var obj = last_obj;
		new Ajax.Request(phpself+'?a=ajax_update_field&ajax=1',{
			method: 'post',
			parameters:{
				id: id,
				branch_id: branch_id,
				qty: new_value,
				data_type: data_type
			},
			onComplete: function(msg){
				if(msg.responseText=='OK'){
					$(obj).update(new_value);
				}else{
					alert(msg.responseText);
					$(obj).update(old_value);
				}
			}
		});
	}
	remove_focus();
}

function generate_do(){
	if(!confirm('Only those items entered DO Qty will generate to DO, other items will go back to saved list. Click OK to continue generate.')){
		return false;
	}
	window.open(phpself+'?a=generate_do&from_branch='+branch_id);
	list_sel();
}

function show_generate_po_popup(){
	curtain(true);
	center_div('div_generate_po_popup');
	$('div_generate_po_popup').show();
	$('div_generate_po_popup_content').update(_loading_);
	new Ajax.Updater('div_generate_po_popup_content',phpself+'?a=ajax_show_generate_po&ajax=1',{
		parameters:{
			from_branch: branch_id
		},
		method: 'post',
		evalScripts: true
	});
}

function toggle_all_item_selected(ele){
	var c = ele.checked;
	if(c) var s = 1;
	else var s =0;
	
	var id_list = [];
	
	// get all input element
	var all_chx = $$('#f_table_list input.inp_item');
	
	if(all_chx.length<=0)	return;
	
	$(ele).disable();
	$('span_label_processing').show();
	// disable all input to prevent user edit
	for(var i=0; i<all_chx.length; i++){
		$(all_chx[i]).disable();
		id_list.push($(all_chx[i]).value);
	}
	
	// send ajax
	new Ajax.Request(phpself+'?a=ajax_change_item_status&ajax=1&',{
		methos: 'post',
		parameters: {
			from_branch: branch_id,
			'id_list[]': id_list,
			selected: s
		},
		onComplete: function(msg){
			var ret = msg.responseText;
			if(ret=='OK'){
				// enable back all input & update to latest status
				for(var i=0; i<all_chx.length; i++){
					$(all_chx[i]).enable().checked = c;
				}
			}else{
				alert(ret);
				// enable back all input
				for(var i=0; i<all_chx.length; i++){
					$(all_chx[i]).enable();
				}
				ele.checked = !c;
			}
			
			$(ele).enable();
			$('span_label_processing').hide();
		}
	});
}

function toggle_item_selected(ele){
	var c = ele.checked;
	if(c) var s = 1;
	else var s = 0;
	
	var id_list = [];
	id_list.push(ele.value);
	$(ele).disable();
	
	// send ajax
	new Ajax.Request(phpself+'?a=ajax_change_item_status&ajax=1&',{
		methos: 'post',
		parameters: {
			from_branch: branch_id,
			'id_list[]': id_list,
			selected: s
		},
		onComplete: function(msg){
			var ret = msg.responseText;
			if(ret=='OK'){
				// enable back input & update to latest status
				$(ele).enable().checked = c;
			}else{
				alert(ret);
				// enable back input
				$(ele).enable().checked = !c;
			}
		}
	});
}

function toggle_all_sel(c){
	if(c) var s = 1;
	else var s = 0;
	
	var all_btn = $$('#items_list .btn_toggle_all_sel');
	$('span_label_processing').show();
	
	// disable all button
	for(var i=0; i<all_btn.length; i++){
		$(all_btn[i]).disabled = true;
	}
	
	var all_chx = $$('#f_table_list input.inp_item');
	// disable all input to prevent user edit
	for(var i=0; i<all_chx.length; i++){
		$(all_chx[i]).disable();
	}
	
	// send ajax
	new Ajax.Request(phpself+'?a=ajax_change_item_status&ajax=1&',{
		methos: 'post',
		parameters: {
			from_branch: branch_id,
			change_all: 1,
			selected: s
		},
		onComplete: function(msg){
			var ret = msg.responseText;
			if(ret=='OK'){
				// enable back all input & update to latest status
				for(var i=0; i<all_chx.length; i++){
					$(all_chx[i]).enable().checked = c;
				}
			}else{
				alert(ret);
				// enable back all input
				for(var i=0; i<all_chx.length; i++){
					$(all_chx[i]).enable();
				}
			}
			for(var i=0; i<all_btn.length; i++){
				$(all_btn[i]).disabled = false;
			}
			$('span_label_processing').hide();
		}
	});
}

function reject_item(id){
	if(do_request_reject_reason){ // is use prelist reject reason
		if(!$('reject_reason').value || $('reject_reason').value.trim() == ""){
			alert("Please key in/select a reject reason.");
			return;
		}
		
		if(!confirm("Are you want to reject?")) return;
		var reason = $('reject_reason').value;
		id = $('reject_id').value;
	}else var reason = prompt("Please enter the reason.");
	
	reason = reason.trim();
	
	var rejected_id_list = [];
	if(!id){ // means it was rejected for more than one selection
		// get all input element
		var all_chx = $$('#f_table_list input.inp_item');
		
		if(all_chx.length<=0)	return;
		
		// disable all input to prevent user edit
		for(var i=0; i<all_chx.length; i++){
			if($(all_chx[i]).checked == true) rejected_id_list.push($(all_chx[i]).value);
		}
	}else rejected_id_list.push(id); // only one being rejected
	
	if(rejected_id_list.length == 0){
		alert("No item were selected for reject.");
		return;
	}
		
	if(reason){
		new Ajax.Request(phpself+'?a=ajax_reject_item&ajax=1',{
		method: 'post',
		parameters:{
			from_branch: branch_id,
			'item_id_list[]': rejected_id_list,
			reason: reason
		},
		onComplete: function(msg){
			var ret = msg.responseText;
			if(ret=='OK'){
				list_sel();
			}else{
				alert(ret);
			}
			curtain_clicked();
		}
		});
	}
}

function reject_picking_list(picking_list_id){
	if(!confirm('Click OK to continue.'))   return;
    $('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=reject_picking_list&ajax=1&pid='+picking_list_id,{
		parameters:{
			branch_id: branch_id,
			p: page_num
		},
		onComplete: function(msg){

		},
		evalScripts: true
	});
}

function init_calendar(){
	// expected delivery date
	if(!do_request_no_expected_delivery_date){
		Calendar.setup({
			inputField     :    "inp_expect_do_date_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_expect_do_date_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		Calendar.setup({
			inputField     :    "inp_expect_do_date_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_expect_do_date_to",
			align          :    "Bl",
			singleClick    :    true
		});
	}
}

function reload_list(){
	page_num = 0;
	clear_selected = 1;
	
	branch_id = document.f_a['branch_id'].value;
	if(branch_id>0){
		$('div_container').show();
		list_sel();
	}else 	$('div_container').hide();
	clear_selected = 0;
}

/*
function sort_reloadTable(col,grp)
{
	if (curr_sort[grp]==undefined || curr_sort[grp] != col)
	{
		curr_sort[grp] = col;
		curr_sort_order[grp] = 'asc';
	}
	else
	{
		curr_sort_order[grp] =  (curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
	}

	SetCookie('_tbsort_'+grp, curr_sort[grp],1);
	SetCookie('_tbsort_'+grp+'_order', curr_sort_order[grp],1);

	// ajax reload
	list_sel();
}

function SetCookie(cookieName,cookieValue,nDays) {
	 var today = new Date();
	 var expire = new Date();
	 if (nDays==null || nDays==0) nDays=1;
	 expire.setTime(today.getTime() + 3600000*24*nDays);
	 document.cookie = cookieName+"="+escape(cookieValue)
	                 + ";expires="+expire.toGMTString();
}*/

function show_reject_reason_dialog(id){
	$('reject_id').value = id;
	$('div_reject_reason').show();
	center_div('div_reject_reason');
	curtain(true);
}

function show_type_option(){
	uc($('reject_reason'));
	type = $('reject_reason').value;
	if($('div_reject_reason_list').style.display=='none'){
		$('div_reject_reason_list').show();	
		//Position.clone($('type_2'), $('div_type_list'), {setHeight: false, setWidth:false});
		//chklabel = $('issue_name').value;
		$$('#div_reject_reason_list li').each(function (obj,idx){
			if (obj.innerHTML == type){
				obj.className = 'current';
				obj.scrollToPosition;
			}
			else{
				obj.className = '';		
			}
		});	
	}
	else{
		$('div_reject_reason_list').hide();
	}
}

function do_select_reject_reason(obj, type){
	$('reject_reason').value=type;

	$('div_reject_reason_list').hide();
	
}

function curtain_clicked(){
	$('reject_reason').value = "";
	$('div_reject_reason').hide();
	curtain(false);
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{include file='shared_sku_photo.script.tpl'}

<!-- Special Div -->
<div id="edit_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id="edit_text" size=5 onblur="save()" onKeyPress="checkKey(event)" style="text-align:right;">
</div>

<div style="display:none;">
<form name="fprint" target="iframe_print" method="get">
	<input type="hidden" name="a" value="print_picking_list" />
	<input type="hidden" name="from_branch" />
	<input type="hidden" name="picking_list_id" />
	<input type="hidden" name="exported_to_po" />
</form>
</div>

<iframe name="iframe_print" style="width:1px;height:1px;visibility:hidden;"></iframe>

<div id="div_generate_po_popup" class="curtain_popup" class="curtain_popup" style="padding:0;position:absolute;width:700px;height:500px;background-color:#fff;border:2px solid #CE0000;display:none;z-index:10000;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x">
	<div id="div_generate_po_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Generate PO</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_generate_po_popup_content" style="padding:2px;"></div>
</div>

<div id="price_history" class="curtain_popup" style="position:absolute;width:400px;height:400px;border:1px solid black;display:none;z-index:10001;background-color:#fff;">
<div id="price_history_list" style="overflow: auto; height: 350px;"></div>
<div align="center"><input type="button" value="Close" onClick="$('price_history').hide();" /></div>
</div>
<!-- End of Special Div -->

<!-- Reject type menu -->
<div id="div_reject_reason" class="curtain_popup" style="position:absolute;width:400px;height:320px;border:1px solid black;display:none;background-color:#fff;">
<h4>Reject Menu</h4>
<input id="reject_reason" name="reject_reason" size=50 onchange="uc(this);" value="{$form.reject_reason}" maxlength="50"><img src="/ui/option_button.jpg" style="border:1px solid #bad3fc;padding:1px;" align=top onclick="show_type_option();">
<div id="div_reject_reason_list" style="display:none;background:#fff;border:1px solid #000;height:200px;width:325px;overflow:auto;">
<ul id="tab">
{foreach from=$config.do_request_reject_reason key=r item=type}
	<li onclick="do_select_reject_reason(this, '{$type|upper}');" id="selected_type">{$type|upper}</li>
{/foreach}
</ul>
</div>
<br /><br />
<div align="center">
	<input type="hidden" value="" id="reject_id" />
	<input type="button" value="Reject" onClick="reject_item('');" />
	<input type="button" value="Close" onClick="curtain_clicked();" />
</div>
</div>
<!-- End of Reject type menu -->

{if $smarty.request.do_id}
	<p><img src="/ui/approved.png" align="absmiddle" /> <a href="do.php?a=open&id={$smarty.request.do_id}&branch_id={$smarty.request.branch_id2}" target="_blank">DO#{$smarty.request.do_id}</a> Generated.</p>
{/if}

<form name="f_a" onSubmit="return false;" class="stdframe" style="background-color:#fff;">
	<table>
		<tr>
			<td><b>Select Request Branch</b></td>
			<td>
				<select name="branch_id" onChange="request_branch_change();">
				<option value="">-- Please Select --</option>
				{foreach from=$branch item=b}
					{assign var=bid value=$b.id}
					{if $b.code ne $BRANCH_CODE && ($sessioninfo.branch_type ne "franchise" || $sessioninfo.branch_type eq "franchise" && $b.id eq 1)}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description} {*({$branches_items.$bid.item_count|default:0})*}</option>
					{/if}
				{/foreach}
			</select>		
			</td>
		</tr>
		
		{* Expected Delivery Date *}
		{if !$config.do_request_no_expected_delivery_date}
			<tr>
				<td><b>Expected Delivery Date</b></td>
				<td>
					<input name="expect_do_date_from" id="inp_expect_do_date_from" size="12" maxlength="10" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date_from" style="cursor: pointer;" title="Select Date" />
					To
					<input name="expect_do_date_to" id="inp_expect_do_date_to" size="12" maxlength="10" value="{$default_expect_do_date_to}" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date_to" style="cursor: pointer;" title="Select Date" />
				</td>
			</tr>
		{/if}
		
		<tr>
			<td><b>Sort By</b></td>
			<td>
				<select name="sort_by">
					<option value="dri.last_update">Last Update</option>
					<option value="dri.added">Request Date</option>
					<option value="si.sku_item_code">ARMS Code</option>
					<option value="si.artno">Art No.</option>
					<option value="si.mcode">MCode</option>
					<option value="category">Category</option>
					<option value="department">Department</option>
				</select>
				
				<select name="sort_order">
					<option value="asc">Ascending</option>
					<option value="desc">Descending</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
				<input type="button" value="Refresh" onClick="reload_list();" />
			</td>
		</tr>
	</table>
	
	{*<ul>
		{if $config.do_request_process_maximum_filter_expect_delivery_date_day}
			<li> Maximum can show Expected Delivery date with {$config.do_request_process_maximum_filter_expect_delivery_date_day} day(s) ahead.</li>
		{/if}
	</ul>*}
</form>
<br />

<form name="f_table_list" id="f_table_list" onSubmit="return false;">
<div id="div_container" style="display:none;">
	<div class=tab style="height:25px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(list_sel(1))" id=lst1 class="active a_tab">Request Items</a>
	<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab">Picking List</a>
	<a href="javascript:void(list_sel(5))" id=lst5 class="a_tab">Rejected</a>
	
	{if $config.do_enable_request_print_exported_po}
	<a href="javascript:void(list_sel(6))" id=lst6 class="a_tab">Exported to PO</a>
	{/if}
	
	<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab">Completed</a>
	<a class="a_tab" id=lst4>Find Items <input id="inp_item_search" onKeyPress="search_input_keypress(event);" /> <input type="button" value="Go" onClick="list_sel(4);" /></a>
	<span id="span_label_processing" style="background-color:#fe9;padding:2px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>

	<div id="items_list" style="border:1px solid #000;">
	</div>
</div>
</form>

<script type="text/javascript">
{if $smarty.request.t} tab_num = '{$smarty.request.t}';{/if}
{if $smarty.request.branch_id}
    request_branch_change();
{else}
    list_sel();
{/if}


{literal}
new Draggable('div_generate_po_popup',{ handle: 'div_generate_po_popup_header'});
new Draggable('div_reject_reason');
init_calendar();
{/literal}
</script>
{include file='footer.tpl'}
