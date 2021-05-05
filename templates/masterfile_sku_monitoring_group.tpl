{*
1/27/2011 3:47:28 PM Andy
- Add department checking for SKU monitoring group items.
- Add filter available user list by branch.

1/28/2011 2:00:50 PM Andy
- Reduce the dialog popup size.

12/16/2011 3:30:54 PM Justin
- Added sort by header feature after reload table.

06/26/2020 1:37 Sheila
- Fixed table height
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
.calendar, .calendar table {
	z-index:100000;
}
.not_update{
	color:red;
}
.ul_list li:nth-child(even){
	background: #eee;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var s = 0;
var str = '';
var regen_in_process = false;

{literal}
var group_changed = false;
function open(id){
	curtain(true);
	$('div_group_details_content').update(_loading_);
	center_div($('div_group_details').show());
	
	new Ajax.Updater('div_group_details_content', phpself+'?a=open&id='+id,{
		evalScripts: true
	});
}

function handle_multiple_window_close(){
	$('div_multiple_add_popup').hide();
}

function add_autocomplete(){
	if(document.f_a['dept_id'].value == ''){
		alert('Please select department first');
		return;
	}
	// is invalid SKU
	if(document.f_a.sku_item_id.value == '' && document.f_a.sku.value != ''){
		document.f_a.sku_item_id.value = 0;
	}
    var sku_item_id = $('sku_item_id').value;
    var sku_item_code = $('sku_item_code').value;
    if(!sku_item_id)    return false;
	var sku_code_list = [sku_item_id];
    ajax_add_multiple_item(sku_code_list);
    clear_autocomplete();
}

function ajax_add_multiple_item(sku_code_list){
    var param_str = Form.serialize(document.f_a) + '&a=ajax_add_item_row';
    var s = $H({'sku_code_list[]': sku_code_list}).toQueryString();

	// ajax_add_item_row
	new Ajax.Request(phpself, {
		method:'post',
		parameters: param_str+'&'+s,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			new Insertion.Bottom($('sel_sku_list'), m.responseText);
			$('div_multiple_add_popup').hide();
			$('btn_remove_sku').disabled = false;
		    $('btn_clear_sku').disabled = false;
		    fix_duplicated_sku();
		}
	});
	group_changed = true;
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

function remove_item(){
	var sel = $('sel_sku_list');
	if(sel.selectedIndex<0){
		alert('Please select a sku item from the list');
	}

	while(sel.selectedIndex>=0){
        var selectedIndex = sel.selectedIndex;
		sel.remove(selectedIndex);
		if(sel.length<=0){
		    $('btn_remove_sku').disabled=true;
		    $('btn_clear_sku').disabled=true;
		}
	}
	group_changed = true;
}

function clear_item(){
	var sel = $('sel_sku_list');
    while(sel.length>0){
		sel.remove(0);
	}
	$('btn_remove_sku').disabled=true;
	$('btn_clear_sku').disabled=true;
	group_changed = true;
}

function close_group_window(){
	if(group_changed){
		if(!confirm('Are you sure to close it without save?')){
			return false;
		}
	}
	default_curtain_clicked();
	group_changed = false;
}

function save_group(){
	if(document.f_a['group_name'].value.trim()==''){
		alert("Please enter Group Name.");
		return false;
	}
	if(document.f_a['dept_id'].value == ''){
		alert("Please select department.");
		return false;
	}

	$('btn_save_group').value = 'Saving...';
	$('btn_save_group').disabled = true;
	$('btn_close_group').disabled = true;
	toggle_select_all(true);
	new Ajax.Request(phpself+'?a=save_group',{
		parameters: $(document.f_a).serialize(),
		onComplete: function(e){
			if(e.responseText=='OK'){
                group_changed = false;
                reload_table();
                close_group_window();
			}else{
				alert(e.responseText);
				$('btn_save_group').value = 'Save';
				$('btn_save_group').disabled = false;
				$('btn_close_group').disabled = false;
				
			}
		}
	});
	toggle_select_all(false);
}

function toggle_select_all(selected){
	if(!selected)   selected = false;
	var sel = $('sel_sku_list');
	for(var i=0; i<sel.length; i++){
		sel.options[i].selected = selected;
	}
}

function reload_table(){
    $('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table', phpself+'?a=load_group_list',{
		parameters:{
			s: s,
			str: str
		},
		onComplete: function(m){
			ts_makeSortable($('sku_monitor_tbl'));
		}
	});
}

function fix_duplicated_sku(){
	var sel = $('sel_sku_list');
	var sku_item_id_list = {};  // initial a blank list to store sku item id
	var rm_count = 0;
	for(var i=0; i<sel.length; i++){    // loop for the selection
		var sid = sel.options[i].value;
		if(!sku_item_id_list[sid])  sku_item_id_list[sid] =0
		sku_item_id_list[sid]++;    // add counter to to variable
	}
	for(var sid in sku_item_id_list){
		var sku_count = sku_item_id_list[sid];
		if(sku_count>1){
            var all_opt = $$('#sel_sku_list option[value="'+sid+'"]');
			for(var i=1; i<all_opt.length; i++){
				$(all_opt[i]).remove();
				rm_count++;
			}
		}
	}
	if(rm_count>0)  alert(rm_count+' item(s) duplicated and was skipped.');
}

function delete_group(id){
	if(!confirm('Are you sure?'))   return false;
	
	new Ajax.Request(phpself+'?a=ajax_delete_group&id='+id,{
	    onComplete: function(e){
	        if(e.responseText=='OK'){
            	reload_table();
            }else{
                alert(e.responseText);
			}
		}
	});
}

function page_change(sel){
	s = sel.value;
	reload_table();
}

function search_group(){
	s = 0;
	str = document.f_search['str'].value.trim();
	reload_table();
	return false;
}

function view_batch(id){
	curtain(true);
	center_div($('div_group_batch_details').show());
	
	$('div_group_batch_details_content').update(_loading_);
	new Ajax.Updater("div_group_batch_details_content", phpself+'?a=ajax_load_group_batch&id='+id, {
		evalScripts: true
	});
}

function regen_batch(id){
	if(!id) return;
	if(regen_in_process){
		alert('Please wait...');
		return;
	}
	
	if(!confirm('Are you sure to regenerate batch group?')) return;
	var img = $('img_regen_batch,'+id);
	$(img).src = 'ui/clock.gif';
	regen_in_process = true;
	
	new Ajax.Request(phpself+'?a=regen_batch&id='+id,{
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='OK'){
                reload_table();
                alert('Batch regeneration complete.');
			}else{
				alert(msg);
			}
			regen_in_process = false;
			$(img).src = 'ui/icons/arrow_refresh.png';
		}
	});
}

function view_batch_item(id,y,m){
	if(!id||!y||!m){
		alert('Invalid parameters');
		return false;
	}
	
	center_div($('div_group_batch_items_details').show());
	$('div_group_batch_items_details_content').update(_loading_);
	
	new Ajax.Updater('div_group_batch_items_details_content', phpself+'?a=ajax_load_batch_items',{
		parameters: {
			'id': id,
			'year': y,
			'month': m
		}
	});
}

function view_item_batch(smg_id, sid){
	if(!smg_id||!sid){
        alert('Invalid parameters');
		return false;
	}
	
	center_div($('div_group_batch_items_details').show());
	$('div_group_batch_items_details_content').update(_loading_);

	new Ajax.Updater('div_group_batch_items_details_content', phpself+'?a=ajax_load_items_batch',{
		parameters: {
			'id': smg_id,
			'sid':  sid
		}
	});
}

function import_sku_by_text(){
	var txt = document.f_a['text_import'].value.trim();
	var dept_id = document.f_a['dept_id'].value;
	
	if(txt=='') return;
	if(!dept_id){
		alert('Please select department.');
		return;
	}
	$('span_text_import_loading').show();
	$('btn_import_sku_by_text').disabled = true;
	
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_import_sku_by_text',
			'txt': txt,
			dept_id: dept_id
		},
		onComplete: function(e){
            var msg = e.responseText.trim();

			try{    // try decode JSON string
                var json_data = JSON.parse(msg);
                alert(json_data['msg']);
                if(json_data['html']){
                    new Insertion.Bottom($('sel_sku_list'), json_data['html']);
					$('btn_remove_sku').disabled = false;
				    $('btn_clear_sku').disabled = false;
				    fix_duplicated_sku();
				}
			}catch(ex){ // failed to decode, prompt the error
            	alert(msg);
			}
			$('btn_import_sku_by_text').disabled = false;
			$('span_text_import_loading').hide();
		}
	})
}

function list_sel_batch(tab){
	$$('#div_batch_container .tab .a_tab').each(function(ele){  // remove "active" class from all tab
		$(ele).removeClassName('active');
	});
	$('lst_batch_'+tab).addClassName('active'); // add 'active' class name to selected tab
	
	$$('#div_batch_container .tbl').each(function(ele){ // hide all table
		$(ele).hide();
	});
	
	$('tbl_batch_'+tab).show(); // show the selected table
}

function group_dept_changed(){
	if(!check_can_change_dept())    return;
	
	var dept_id = document.f_a['dept_id'].value;
	document.f_a['old_dept_id'].value = dept_id;    // record old dept id
	reset_sku_autocomplete();
	reload_available_user();
	
}

function reload_available_user(){
    var dept_id = document.f_a['dept_id'].value;
    if(!dept_id){
		$('div_allowed_users').update('');
		return;
	}
    var default_bid = document.f_a['default_bid'].value;
	var smg_id = int(document.f_a['id'].value);

	$('div_allowed_users').update(_loading_);
	new Ajax.Updater('div_allowed_users', phpself, {
		parameters:{
			'a': 'ajax_load_available_users',
			'dept_id': dept_id,
			'smg_id': smg_id,
			default_bid: default_bid
		}
	});
}

function toggle_all_allowed_user(chx){
	var c = chx.checked;
	var all_inp = document.f_a['allowed_user[]'];
	if(!all_inp)    return; // no items
	
	if(!all_inp.length){    // no length, only single user
        all_inp.checked = c;
	}else{
        for(var i=0; i<all_inp.length; i++){
			all_inp[i].checked = c;
		}
	}
	
}

function check_can_change_dept(){
	var item_length = int(document.f_a['sku_item_id_list[]'].length);
	if(item_length>0){
		alert('You cannot change department once you already have item, please clear the item list first.');
		document.f_a['dept_id'].value = document.f_a['old_dept_id'].value;  // select back the old dept
		return false;
	}
	return true;
}
{/literal}
</script>

<!-- SKU Monitroing Group Details -->
<div id="div_group_details" class="curtain_popup" style="position:absolute;z-index:10000;width:800px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_group_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Monitoring Group Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="close_group_window();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_group_details_content" style="padding:2px;"></div>
</div>
<!-- End of SKU Monitroing Group Details -->

<!-- SKU Monitroing Group Batch Details -->
<div id="div_group_batch_details" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:570px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_group_batch_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Monitoring Group Batch List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_group_batch_details_content" style="padding:2px;"></div>
</div>
<!-- End of SKU Monitroing Group Batch Details -->

<!-- SKU Monitroing Group Batch Details -->
<div id="div_group_batch_items_details" class="curtain_popup" style="position:absolute;z-index:10001;width:450px;height:350px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_group_batch_items_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Monitoring Group Batch Item List</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="$('div_group_batch_items_details').hide();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_group_batch_items_details_content" style="padding:2px;"></div>
</div>
<!-- End of SKU Monitroing Group Batch Details -->

<h1>{$PAGE_TITLE}</h1>
<p>
	<a href="javascript:void(open(0));" accesskey='A'><img src="ui/new.png" align="absmiddle" border="0" /> Add New Group</a> (Alt + A)
</p>

<br>
<div>
	<form name="f_search" onSubmit="return search_group();">
		<b>Search by Group Name</b>
		<input type="text" name="str" />
		<input type="submit" value="Search" />
	</form>
</div>

<div>
<span class="not_update">*</span> Batch Group not up to date.
</div>
<div id="div_table" class="stdframe">
    {include file='masterfile_sku_monitoring_group.table.tpl'}
</div>

{include file='footer.tpl'}

{literal}
<script>
new Draggable('div_group_details',{ handle: 'div_group_details_header'});
new Draggable('div_group_batch_details',{ handle: 'div_group_batch_details_header'});
new Draggable('div_group_batch_items_details',{ handle: 'div_group_batch_items_details_header'});
</script>
{/literal}
