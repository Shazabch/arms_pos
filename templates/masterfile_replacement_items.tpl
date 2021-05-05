{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature when reload table.
*}

{include file='header.tpl'}

<style>
{literal}

{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var s = 0;
var str = '';

{literal}
var group_changed = false;
function open(id){
	curtain(true);
	$('div_ri_details_content').update(_loading_);
	center_div($('div_ri_details').show());
	
	new Ajax.Updater('div_ri_details_content', phpself+'?a=open&id='+id,{
		evalScripts: true
	});
}

function handle_multiple_window_close(){
	$('div_multiple_add_popup').hide();
}

function add_autocomplete(){
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
			ts_makeSortable($('replmt_item_tbl'));
		}
	});
}

function fix_duplicated_sku(){
	var sel = $('sel_sku_list');
	var sku_item_id_list = {};  // initial a blank list to store sku item id
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
			}
		}
	}
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

{/literal}
</script>

<div id="div_ri_details" class="curtain_popup" style="position:absolute;z-index:10000;width:800px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_ri_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Replacement Item Group Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="close_group_window();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_ri_details_content" style="padding:2px;"></div>
</div>

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

<div id="div_table" class="stdframe">
    {include file='masterfile_replacement_items.table.tpl'}
</div>

{include file='footer.tpl'}

{literal}
<script>
new Draggable('div_ri_details',{ handle: 'div_ri_details_header'});
</script>
{/literal}
