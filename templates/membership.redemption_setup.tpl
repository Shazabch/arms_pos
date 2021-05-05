{*
3/2/2010 3:26:26 PM Andy
- Add receipt date period checking
- Add option to allow user can one time toggle multiple redemption items to delete

3/12/2010 11:40:10 AM Andy
- Delete button change and multiple delete function.
- Toggle active button change and multiple active/deactive function.
- Fix item cannot approve due to have amount but no end date bugs.
- Add use current date feature for receipt control.

8/11/2010 12:26:38 PM Justin
- Added the JS function to disable/enable receipt date start and end based on the "Use Current Date" and "Receipt Amount".
- Enhanced the checking for valid and receipt date start/end while found both fields is not empty.
  -> When found the valid and receipt date end is earlier than date start, show error message to indicate the date is invalid.

8/27/2010 3:11:17 PM Justin
- Added SKU filter.

9/15/2010 5:35:00 PM Justin
- Added new both status "Pending" and "Expired" filter.
- Changed the current status "Inactive" become "Draft".

9/22/2010 12:25:31 PM Justin
- Re-aligned the status filter.
- Rename the current "Expired" become "Expired/Canceled".

9/27/2010 3:39:05 PM Justin
- Modified the Activate/Deactivate update not to update instantly into database.
- Modified the Delete update not to delete instantly into database but hidden the following deleted items.
- Added confirm message when Activating/Deactivating items.
- Removed the clickable update from Activate and Deactivate images.
- Placed the delete button on beside of No sequence column.

10/8/2010 5:33:14 PM Justin
- Fixed the status back to its original icon when user decided not to update the status.

10/25/2010 6:30:40 PM Justin
- Renamed the Branch into Available Branch.

10/28/2010 5:40:29 PM Justin
- Fixed the delete items bug whenever user cancel to delete the selected items, the icon and bgcolor is not set to default.

11/12/2010 4:17:14 PM Justin
- Added activate/deactivate function for use on the status icon click.

4/27/2011 3:29:11 PM Justin
- Fixed the JS function not to delete "no data row" while user trying to add invalid SKU item.

4/28/2011 2:29:11 PM Justin
- Fixed the errors that cannot delete off the "No Data Row" while added new item.

5/18/2011 3:51:35 PM Andy
- Add pass document FORM name when include the autocomplete templates

1/14/2013 4:45 PM Justin
- Enhanced to enable/disable voucher value field.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

5/06/2019 11:46 AM Liew
- word "Filter Receipt with SKU" is wrong, change to use "SKU Filter"
- Add by Group not working" 
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.tr_fix_selection{
    background:#f0f0f0;
}

tr.item_deleted{
	background:grey;
}

.nl{
	font-size:11;
	color:#e00;
	text-align:center;
}

.highlight_row_title {
	background:none repeat scroll 0 0 #FFFF66 !important;
	border-bottom:1px solid #FF0000;
	border-top:1px solid #FF0000;
	color:#FF0000;
	font-weight:bold;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branches_id_list = [];
var items_row_count = 0;

{literal}var branches_info = {};{/literal}
{foreach from=$branches key=bid item=b}
    branches_id_list.push('{$bid}');
    branches_info['{$bid}'] = {literal}{}{/literal};
    branches_info['{$bid}']['code'] = '{$b.code}';
{/foreach}

{literal}
var selected_ele_info = {};
function select_ele(ele){
    selected_ele_info['value'] = ele.value;
}

function curtain_clicked(){
	$('div_edit_available_branches').hide();
}

function check_ab_can_change(ele){
	if($(ele).hasClassName('inp_fix_selection'))    return false;
}

function edit_available_branches(id,branch_id){
    var key = branch_id+'_'+id;
    //if($('inp_item_is_delete_'+key).value==1)   return;
	
	curtain(true);
	$('div_edit_available_branches').show();
	center_div('div_edit_available_branches');

	
	var bid_list = $('available_branches2,'+key).value.split(',');
	var all_inp = $$('#f_ab input.inp_ab');
	document.f_ab['branch_id'].value = branch_id;
	document.f_ab['id'].value = id;
	
	// reset input checked
	for(var i=0; i<all_inp.length; i++){
	    all_inp[i].checked = false;
	    if(all_inp[i].value==branch_id){
            $(all_inp[i]).addClassName('inp_fix_selection');
            $(all_inp[i].parentNode.parentNode).addClassName('tr_fix_selection');
		} 
	    else{
	        $(all_inp[i]).removeClassName('inp_fix_selection');
            $(all_inp[i].parentNode.parentNode).removeClassName('tr_fix_selection');
		}   
	}

	// mark input checked
	for(var i=0; i<bid_list.length; i++){
		$('inp_ab_selected,'+bid_list[i]).checked = true;
	}
}

function save_available_branches(){
	var all_inp = $$('#f_ab input.inp_ab');
	var bid_list = [];
	var branch_id = document.f_ab['branch_id'].value;
	var id = document.f_ab['id'].value;
	var key = branch_id+'_'+id;
	for(var i=0; i<all_inp.length; i++){
	  if($(all_inp[i]).checked){
        bid_list.push(all_inp[i].value);
	  }
	}

	if(bid_list.length<=0){
		alert('Please at least tick one branch');
		return false;
	}

	//if(!confirm('Click OK to save.'))   return false;
	$('available_branches2,'+key).value = bid_list.toString();
	$('div_ab_list,'+key).update('');
	$('span_ab_total_b,'+key).update('');
	var str = '';
	var ab_count = 0;
	for(var i=0; i<bid_list.length; i++){
	    ab_count++;
		var bcode = branches_info[bid_list[i]]['code'];
		if(ab_count<=5){
            str += bcode;
            if(i<bid_list.length-1) str += ', ';
		}
	}
	if(ab_count>5){
	    str += '...';
	}
	if(ab_count>1)  $('span_ab_total_b,'+key).update('('+ab_count+' branches)');
	$('div_ab_list,'+key).update(str);
	default_curtain_clicked();

}

function toggle_ab(ele){    // ab = available branches
	var c = ele.checked;
	var all_inp = $$('#f_ab input.inp_ab');

	for(var i=0; i<all_inp.length; i++){
	    if(!$(all_inp[i]).hasClassName('inp_fix_selection'))    all_inp[i].checked = c;
	}
}

function toggle_point_range(){
	var pr = document.f_b['point_range'].value;
	if(pr=='')	$('span_point_range').hide();
	else    $('span_point_range').show();
}

function toggle_sort_by(){
    var sb = document.f_b['sort_by'].value;
	if(sb=='')	$('span_sort_by').hide();
	else    $('span_sort_by').show();
}

function refresh_item_list(){
	if(document.f_b['refresh'].disabled)    return;
	
	if(document.f_b['filter_sku'].checked){
		if(document.f_b['sku_code_list1[]'].length == 0){
			alert("Please add one or more SKU item to search.");
			return false;
		}else{
			if($('sku_code_list1').selectedIndex<0){
				var sel = $('sku_code_list1');
				for(var i=0; i<sel.length; i++){
					sel.options[i].selected = true;
				}
			}
		}
	}

	document.f_b['refresh'].disabled = true;
	$('tb_div').update(_loading_);
	
	new Ajax.Updater('tb_div',phpself,{
		parameters: $(document.f_b).serialize(),
		onFailure: function(msg) {
			alert(msg.responseText);
		},
		onSuccess: function(msg){
            document.f_b['refresh'].disabled = false;
            reset_row_no();
		},
		evalScripts: true
	});
}

function save_redemption_items(){
	if(items_row_count>int(document.f_b['limit'].value))	document.f_b['limit'].value = items_row_count;
	
	document.f_b['refresh'].disabled = true;
	$(document.f_item['save_btn']).disable().value = 'Saving...';
	
	new Ajax.Request(phpself,{
		parameters: $(document.f_item).serialize(),
		onComplete: function(msg){
		    document.f_b['refresh'].disabled = false;
			$(document.f_item['save_btn']).enable().value = 'Save';
			
			if(msg.responseText.trim()=='OK'){
                //$('p_error_msg').update('');
                refresh_item_list();
                alert('Save Successfully');
                
			}else{
                $('p_error_msg').update(msg.responseText);
				alert("Save Failed");
			}
		}
	});
}

function toggle_all_status(ele){
	var c = ele.checked;
	var all_inp = $$('#tb_div input.chx_item');
	
	for(var i=0; i<all_inp.length; i++){
	    all_inp[i].checked = c;
	}
}

function add_autocomplete_callback(){
	$('span_adding_item').update(_loading_);
	return false;
}

function add_autocomplete_extra(){
	var all_td = $$('#tb_div td span.td_no');
	
	for(var i=0; i<all_td.length; i++){
		$(all_td[i]).update((i+1)+'.');
	}
	items_row_count = i;
	
	$('span_adding_item').update('');
	var tr_no_item = $('tr_no_item');
	if(tr_no_item){
        $(tr_no_item).remove();
		$('div_save_area').show();
	}
}

/*function toggle_delete_item(branch_id,item_id,ele){
	var parent_tr = ele.parentNode.parentNode;
	var key = branch_id+'_'+item_id;
	toggle_delete_row(parent_tr);
}*/

/*function toggle_delete_row(tr, must_delete, not_delete){
    var is_delete = 1;
	var is_disable = true;
    var all_inp = $(tr).getElementsBySelector('input');
    var key = tr.id.split(",")[1];
    
    if(must_delete){
        is_delete = 1;
		is_disable = true;
		$(tr).addClassName('item_deleted');
	}else if(not_delete){
        is_delete = 1;
		is_disable = false;
		$(tr).removeClassName('item_deleted');
	}else{
        if($(tr).hasClassName('item_deleted')){
	        $(tr).removeClassName('item_deleted');
	        is_delete = 0;
	        is_disable = false;
		}else{
			$(tr).addClassName('item_deleted');
		}
	}

	for(var i=0; i<all_inp.length; i++){
		if(!$(all_inp[i]).hasClassName('dont_disabled'))    all_inp[i].disabled = is_disable;
	}
	$('inp_item_is_delete_'+key).value = is_delete;
}*/

function init_calendar(inputfield, button){
	Calendar.setup({
		inputField     :    inputfield,
		ifFormat       :    "%Y-%m-%d",
		button         :    button,
		align          :    "Bl",
		singleClick    :    true
	});   
}

function receipt_amt_changed(ele){
	ele.value = round(ele.value, 2);
	var key = ele.id.split(",")[1];
	if(ele.value>0){
		if(document.f_item['use_curr_date['+key+']'].checked == false){
			$('inp_receipt_date_from_'+key).readOnly = false;
			$('inp_receipt_date_to_'+key).readOnly = false;
			$('img_receipt_date_from_'+key).style.display = '';
			$('img_receipt_date_to_'+key).style.display = '';
			$('use_curr_date'+key).disabled = false;
		}
	}else{
		$('inp_receipt_date_from_'+key).value = '';
		$('inp_receipt_date_to_'+key).value = '';
		$('inp_receipt_date_from_'+key).readOnly = true;
		$('inp_receipt_date_to_'+key).readOnly = true;
		$('img_receipt_date_from_'+key).style.display = 'none';
		$('img_receipt_date_to_'+key).style.display = 'none';
		$('use_curr_date'+key).disabled = true;
	}
}

function ajax_delete_item(branch_id,item_id,ele){
    if(ele.src.indexOf('clock')>0)  return false;
	if(!confirm('Are you sure to delete this item?'))  return false;
	
	var parent_tr = ele.parentNode.parentNode;
	$(parent_tr).addClassName('item_deleted');
	var item_array = [branch_id+'_'+item_id];
	ele.src = 'ui/clock.gif';

	$('tr_item,'+branch_id+'_'+item_id).hide();
	$('inp_item_is_delete,'+branch_id+'_'+item_id).value = 1;
	$('td_no,'+branch_id+'_'+item_id).className = '';
	reset_row_no();
}

function reset_row_no(){
	var all_td = $$('#tbl_items td span.td_no');
	for(var i=0; i<all_td.length; i++){
		$(all_td[i]).update((i+1)+'.');
	}
}

function toggle_active_status(branch_id,item_id,ele){
	if(ele.src.indexOf('clock')>0)  return false;
	var v = 1;
	if(ele.src.indexOf('/deact')>0)  v = 0;
	ele.src = 'ui/clock.gif';
	
	var item_array = [branch_id+'_'+item_id];
	var img = $('img_act,'+branch_id+','+item_id);

	if(v==1){
		img.title = 'Deactivate';
		img.src = 'ui/deact.png';
		$('inac_area,'+branch_id+','+item_id).innerHTML = '';
	}else{
		img.title = 'Activate';
		img.src = 'ui/act.png';
		$('inac_area,'+branch_id+','+item_id).innerHTML = '(Inactive)';
	}
	$('item_status,'+branch_id+','+item_id).value = v;
}

function status_set_selected_item(v){
	if(v==1) var msg_update = 'activate';
	else var msg_update = 'deactivate';

	var all_chx = $$('#tbl_items input.chx_item');
	var item_array = [];
	for(var i=0; i<all_chx.length; i++){
	    var chx = all_chx[i];
	    if(!chx.checked)    continue;
	    
	    var bid = $(chx).id.split(',')[1];
	    var id = $(chx).id.split(',')[2];
		var img = $('img_act,'+bid+','+id);
		if(img.src.indexOf('clock')>0)  continue;
		
		if(v==1&&img.src.indexOf('/act')>0){
            item_array.push(bid+'_'+id);
		}else if(v==0&&img.src.indexOf('/deact')>0){
            item_array.push(bid+'_'+id);
		}else   continue;
		img.src = 'ui/clock.gif';
	}

	if(item_array.length>0){
		if(!confirm('Are you sure to '+msg_update+' those selected item(s)?')){
			if(v==1){
				v=0;
			}else if(v==0){
				v=1;
			}
		}
	    for(var i=0; i<item_array.length; i++){
	        var bid = item_array[i].split('_')[0];
	        var id = item_array[i].split('_')[1];
			var img = $('img_act,'+bid+','+id);
			if(v==1){
				img.title = 'Deactivate';
				img.src = 'ui/deact.png';
				$('inac_area,'+bid+','+id).innerHTML = '';
			}else{
				img.title = 'Activate';
				img.src = 'ui/act.png';
				$('inac_area,'+bid+','+id).innerHTML = '(Inactive)';
			}
			$('item_status,'+bid+','+id).value = v;
		}
	}else{
		alert('No item to update');
	}
}


function delete_selected_item(){
    var all_chx = $$('#tbl_items input.chx_item');
	var item_array = [];

	for(var i=0; i<all_chx.length; i++){
	    var chx = all_chx[i];
	    if(!chx.checked)    continue;

	    var bid = $(chx).id.split(',')[1];
	    var id = $(chx).id.split(',')[2];
		var img = $('img_del,'+bid+','+id);
		
		if(img.src.indexOf('clock')>0)  continue;

		item_array.push(bid+'_'+id);
		$('tr_item,'+bid+'_'+id).addClassName('item_deleted');
		img.src = 'ui/clock.gif';
	}
	
	
	if(item_array.length>0){
		if(!confirm('Are you sure to delete those selected item(s)?')){
			for(var i=0; i<all_chx.length; i++){
			    var chx = all_chx[i];
			    if(!chx.checked)    continue;
			    var bid = $(chx).id.split(',')[1];
			    var id = $(chx).id.split(',')[2];
				var img = $('img_del,'+bid+','+id);
				img.src = '/ui/icons/delete.png';
				$('tr_item,'+bid+'_'+id).removeClassName('item_deleted');
			}
			return false;
		}
	    for(var i=0; i<item_array.length; i++){
	        var bid = item_array[i].split('_')[0];
	        var id = item_array[i].split('_')[1];

			$('tr_item,'+bid+'_'+id).hide();
			$('inp_item_is_delete,'+bid+'_'+id).value = 1;
			$('td_no,'+bid+'_'+id).className = '';
		}
	}else{
		alert('No item to delete');
	}

	reset_row_no();
}

function use_curr_date(branch_id, id){
	
	if(document.f_item['use_curr_date['+branch_id+'_'+id+']'].checked == true){
		$('inp_receipt_date_from_'+branch_id+'_'+id).value = '';
		$('inp_receipt_date_to_'+branch_id+'_'+id).value = '';
		$('inp_receipt_date_from_'+branch_id+'_'+id).readOnly = true;
		$('inp_receipt_date_to_'+branch_id+'_'+id).readOnly = true;
		$('img_receipt_date_to_'+branch_id+'_'+id).style.display = 'none';
		$('img_receipt_date_from_'+branch_id+'_'+id).style.display = 'none';
	}else{
		if($('receipt_amount,'+branch_id+'_'+id).value != 0){
			$('inp_receipt_date_from_'+branch_id+'_'+id).readOnly = false;
			$('inp_receipt_date_to_'+branch_id+'_'+id).readOnly = false;
			$('img_receipt_date_to_'+branch_id+'_'+id).style.display = '';
			$('img_receipt_date_from_'+branch_id+'_'+id).style.display = '';
		}
	}
}

function toggle_filter_sku(){
	var checked = document.f_b['filter_sku'].checked;
	if(checked) $('div_sku_items').show();
	else    $('div_sku_items').hide();
}

function use_voucher(branch_id, id, obj){
	if(obj.checked == true)	document.f_item['voucher_value['+branch_id+'_'+id+']'].disabled = false;
	else{
		document.f_item['voucher_value['+branch_id+'_'+id+']'].value = "";
		document.f_item['voucher_value['+branch_id+'_'+id+']'].disabled = true;
	}
}

</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
{if $msg}<p align=center><font color=red>{$msg}</font></p>{/if}


<!-- Special Div -->
<div id="div_edit_available_branches" style="position:absolute;z-index:10000;width:500px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
	<div id="div_edit_available_branches_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Branch Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_edit_available_branches_content" style="padding:2px;">
		{include file='membership.redemption_setup.available_branches.tpl'}
	</div>
</div>
<!-- End of Special Div -->

<form name="f_b" method="post" onSubmit="refresh_item_list();return false;">
	<input type="hidden" name="a" value="refresh_item_list" />
	
	{if $BRANCH_CODE eq 'HQ'}
	    <b>Available Branch</b>
	    <select name="branch_id">
	        <option value="">-- All --</option>
	        {foreach from=$branches key=bid item=b}
	    	    {if !$branches_group.have_group.$bid}
	    	    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
				{/if}
	    	{/foreach}
	    	{if $branches_group.header}
	    	<optgroup label="Branches Group">
		    	{foreach from=$branches_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
		    	    {foreach from=$branches_group.items.$bgid item=r}
		    	        <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
		    	    {/foreach}
		    	{/foreach}
            </optgroup>
	    	{/if}
	    	
	    </select>&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
	{/if}
	
	<b>Status</b>
	<select name=active>
		<option value="All" {if $smarty.request.active == 'All'}selected {/if}>-- All --</option>
		<option value="1" {if $smarty.request.active == '1'}selected {/if}>Draft</option>
		<option value="2" {if $smarty.request.active == '2'}selected {/if}>Pending</option>
		<option value="3" {if $smarty.request.active == '3'}selected {/if}>Active</option>
		<option value="4" {if $smarty.request.active == '4'}selected {/if}>Expired/Cancelled</option>
	</select>&nbsp;&nbsp;
	
	<b>Point Range</b>
	<select name="point_range" onChange="toggle_point_range();">
	    <option value="">-- All --</option>
	    <option value="between">Between</option>
	</select>&nbsp;&nbsp;
	<span id="span_point_range" style="display:none;">
	    <b>Min</b><input type="text" size="5" name="point_range_min" value="{$smarty.request.point_range_min}" />
	    <b>Max</b><input type="text" size="5" name="point_range_max" value="{$smarty.request.point_range_max|default:'100'}" />
	</span>&nbsp;&nbsp;
	
	<b>Sort by</b>
    <select name="sort_by" onChange="toggle_sort_by();">
	    <option value="">No Sorting</option>
	    <option value="sku_item_code">ARMS Code</option>
	    <option value="description">Description</option>
	    <option value="point">Point</option>
	    <option value="receipt_amount">Receipt Amount</option>
	</select>&nbsp;&nbsp;
	<span id="span_sort_by" style="display:none;">
	    <select name="order_by">
	        <option value="asc">Ascending</option>
	        <option value="desc">Descending</option>
	    </select>
	</span>&nbsp;&nbsp;
	
	<script>toggle_point_range();</script>
	<script>toggle_sort_by();</script>
	
	<p>
		<input type="checkbox" name="filter_sku" align="absmiddle" onChange="toggle_filter_sku();" {if $smarty.request.filter_sku}checked {/if} /> <b>SKU Filter</b>
		<div id="div_sku_items" style="border:1px solid #cfcfcf; background: #efefef;display:none;">
		{include file='sku_items_autocomplete_multiple_add2.tpl' skip_dept_filter=1 is_dbl_sku=1 parent_form='document.f_b'}
		</div>
	</p>
	
	<p>
	    <b>Show <input type="text" size="2" name="limit" onChange="miz(this);" value="30" class="r" /> Item(s)</b>&nbsp;&nbsp;
	    <input type="submit" name="refresh" value="Refresh" />
	</p>
</form>

<form name="f_item">
	<input type="hidden" name="a" value="ajax_save_item" />
    <div id="tb_div" class="stdframe"></div>
</form>

<div class=stdframe>
	<form name=f_a method=post onSubmit="return false;">
		<input type=hidden name=a value=ajax_get_sku_by_code>
		{include file=sku_items_autocomplete_multiple_add.tpl is_promo=1 check_item_list=1}
	</form>
	<span id="span_adding_item" style="background:#ff0;"></span>
</div>


<script>
{literal}
refresh_item_list();
new Draggable('div_edit_available_branches',{ handle: 'div_edit_available_branches_header'});
{/literal}
</script>

{include file=footer.tpl}
