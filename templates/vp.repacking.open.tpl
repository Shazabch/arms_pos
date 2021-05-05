{*
1/18/2013 2:15 PM Andy
- Change to calculate total lose item cost to calculate even no pack item.
- Remove department dropdown, auto use the first item to become department if found repacking still dont have dept id.

2/4/2013 11:12 AM Justin
- Enhanced to allow user key in qty with decimal points based on doc_allow_decimal.
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#p_action_button input[disabled]{
	background-color: grey !important;
}

</style>
{/literal}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');
var global_qty_decimal_points = int('{$config.global_qty_decimal_points}');
var global_cost_decimal_points = int('{$config.global_cost_decimal_points}');

{literal}

var REPACKING_FORM = {
	f: undefined,
	id: 0,
	needCheckExit: false,
	initialize: function(){
		var THIS = this;
		this.f = document.f_a;
		this.id = this.f['id'].value;
		
		// get all group
		var div_item_group_list = $$('#div_item_list div.div_item_group');
				
		for(var i=0; i<div_item_group_list.length; i++){ // loop for each group
			var group_id = this.get_group_id_by_ele(div_item_group_list[i]);	// get group id
			this.calc_lose_total_cost(group_id);
		}
		if(can_edit){
			// auto create group if not found
			this.check_and_auto_create_group();
	
			// init calendar
			Calendar.setup({
			    inputField     :    "inp_repacking_date",     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_repacking_date",  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true
			});
	
			// initial choose item popup		
			CHOOSE_ITEM_POPUP.initialize();
			
			this.needCheckExit = true;
			window.onbeforeunload = function(e){
				if(!e) e = window.event;
				if(THIS.needCheckExit){	// need checking?
					return 'Data had not being saved.';
				}
			};
		}else{
			// cannot edit
			$(this.f).disable();
		}
	},
	// function to auto create a group
	check_and_auto_create_group: function(){
		var div_item_group_list = $$('#div_item_list div.div_item_group');

		if(div_item_group_list.length<=0){
			this.add_new_group();
		}
	},
	// function when user click add new group
	add_new_group_clicked: function(){
		this.add_new_group();
	},
	// function to get group id by element
	get_group_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='div'){
                if($(parent_ele).hasClassName('div_item_group')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var group_id = int($(parent_ele).id.split('-')[1]);
		return group_id;
	},
	// function to get max group id
	get_max_group_id: function(){
		var max_group_id = 0;
		var div_item_group_list = $$('#div_item_list div.div_item_group');
		
		for(var i=0; i<div_item_group_list.length; i++){	// loop for each row

			var group_id = this.get_group_id_by_ele(div_item_group_list[i]);
			
			if(group_id > max_group_id)	max_group_id = group_id;
		}
		return max_group_id;
	},
	// function to create a group row
	add_new_group: function(){
		var curr_max_group_id = this.get_max_group_id();

		var new_group_id = curr_max_group_id + 1;
		
		var new_group_ele = $('div_tmp_item_group').cloneNode(true);
		
		//new_tr.id = "tr_branch_profit_row-"+bid+'-'+new_row_no;	// change row id

		// get row html
		new_html = new_group_ele.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_GROUP_ID__/g, new_group_id);
		$(new_group_ele).update(new_html);
		
		new Insertion.Bottom('div_item_list', new_group_ele.innerHTML);
		
	},
	// function when user click delete item group
	remove_item_group_clicked: function(group_id){
		if(!confirm('Are you sure?'))	return false;
		
		$('div_item_group-'+group_id).remove();
	},
	// function when user click add lose item
	add_lose_item_clicked: function(group_id){
		CHOOSE_ITEM_POPUP.open('lose', group_id);
	},
	// function when user click add pack item
	add_pack_item_clicked: function(group_id){
		CHOOSE_ITEM_POPUP.open('pack', group_id);
	},
	// function to get lose row info
	get_lose_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_lose_item_row')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var ids = $(parent_ele).id.split('-');
		var row_info = {
			'group_id': ids[1],
			'row_id': ids[2]
		};
		return row_info;
	},
	// function to get current max lose row id
	get_max_lose_row_id: function(group_id){
		var max_row_id = 0;
		var tr_lose_item_row_list = $$('#tbody_lose_item_list-'+group_id+' tr.tr_lose_item_row');
		
		for(var i=0; i<tr_lose_item_row_list.length; i++){
			var row_info = this.get_lose_row_info_by_ele(tr_lose_item_row_list[i]);
			var row_id = row_info['row_id'];
			
			if(row_id > max_row_id)	max_row_id = row_id;
		}
		return max_row_id;
	},
	// function to check whether the sku item id already have in the group
	check_sku_exists_in_group: function(group_id, sid, type){
		if(type == 'pack'){	// check pack item
		
		}else if (type == 'lose'){	// check lose item
			var tr_lose_item_row_list = $$('#tbody_lose_item_list-'+group_id+' tr.tr_lose_item_row');
			
			for(var i=0; i<tr_lose_item_row_list.length; i++){	// loop for each lose row
				var row_info = this.get_lose_row_info_by_ele(tr_lose_item_row_list[i]);
				var row_id = row_info['row_id'];
				
				var tmp_sid = this.f['items['+group_id+'][lose]['+row_id+'][sku_item_id]'].value;
				if(tmp_sid == sid){
					return true;
				}
			}
		}
		return false;
	},
	// function to add new lose item
	add_lose_item: function(group_id, sid){
		if(!group_id || !sid)	return false;
		
		if(this.check_sku_exists_in_group(group_id, sid, 'lose')){
			alert('The item already exists in the group.');
			return false;
		}
		
		$('btn_add_new_lose_item-'+group_id).disabled = true;
		$('span_add_new_lose_item_loading-'+group_id).show();
		
		var curr_max_row_id = this.get_max_lose_row_id(group_id);
		
		var params = {
			a: 'ajax_add_new_lose_item',
			group_id: group_id,
			sid: sid,
			curr_max_row_id: curr_max_row_id
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $('btn_add_new_lose_item-'+group_id).disabled = false;
				$('span_add_new_lose_item_loading-'+group_id).hide();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_lose_item_list-'+group_id, ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click remove row
	remove_lose_item_row_clicked: function(group_id, row_id){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_lose_item_row-'+group_id+'-'+row_id).remove();
	},
	// function when user change lose item qty
	lose_item_qty_changed: function(group_id, row_id, doc_allow_decimal){
		var inp = this.f['items['+group_id+'][lose]['+row_id+'][qty]'];
		
		//mf(inp);
		if (doc_allow_decimal == 1) inp.value = float(round(inp.value, global_qty_decimal_points));
		else mi(inp);
		
		if(inp.value<0)	inp.value = 0;
		
		var cost = float(this.f['items['+group_id+'][lose]['+row_id+'][cost]'].value);
		var qty = float(this.f['items['+group_id+'][lose]['+row_id+'][qty]'].value);
		var row_cost = float(round(cost*qty , global_cost_decimal_points));
		
		// update span 
		$('span_lose_item_row_total_cost-'+group_id+'-'+row_id).update(round(row_cost, global_cost_decimal_points));
		
		// update hidden input
		$('inp_lose_item_row_cost-'+group_id+'-'+row_id).value = row_cost;
		
		// recalculate pack item cost
		this.calculate_pack_item_cost(group_id);
	},
	// function to add pack item
	add_pack_item: function(group_id, sid, sku_id){
		if(!group_id || !sid)	return false;

		// check whether got same sku id use in lose item, not allow parent chiled
		var inp_lose_item_sku_id_list = $$('#tbody_lose_item_list-'+group_id+' input.inp_lose_item_sku_id-'+group_id);
		for(var i=0; i<inp_lose_item_sku_id_list.length; i++){
			if(inp_lose_item_sku_id_list[i].value == sku_id){	// found same sku id
				alert('Lose Item and Pack Item cannot have same parent/child item.');
				return false;
			}
		}
		
		// check if already got pack item, ask to delete it
		if($$('#tbody_pack_item_list-'+group_id+' tr.tr_pack_item_row').length>0){	// already have pack item
			if(!confirm('You can only have 1 pack item, replace the current Pack Item?'))	return false;
			
			this.remove_pack_item(group_id, 1);	// only got row 1
		}
		
		$('btn_add_new_pack_item-'+group_id).disabled = true;
		$('span_add_new_pack_item_loading-'+group_id).show();
		
		var curr_max_row_id = 0;	// always re-use
		
		var params = {
			a: 'ajax_add_new_pack_item',
			group_id: group_id,
			sid: sid,
			curr_max_row_id: curr_max_row_id
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $('btn_add_new_pack_item-'+group_id).disabled = false;
				$('span_add_new_pack_item_loading-'+group_id).hide();
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_pack_item_list-'+group_id, ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click to remove pack item row
	remove_pack_item_row_clicked: function(group_id, row_id){
		if(!confirm('Are you sure?'))	return false;
		
		// call function to remove
		this.remove_pack_item(group_id, row_id);
	},
	// function to remove pack item
	remove_pack_item: function(group_id, row_id){
		$('tr_pack_item_row-'+group_id+'-'+row_id).remove();
	},
	// function when user change pack item qty
	pack_item_qty_changed: function(group_id, row_id, doc_allow_decimal){
		var inp = this.f['items['+group_id+'][pack]['+row_id+'][qty]'];

		//mf(inp);
		if (doc_allow_decimal == 1) inp.value = float(round(inp.value, global_qty_decimal_points));
		else mi(inp);
		
		if(inp.value<0)	inp.value = 0;
		
		// recalculate pack item cost
		this.calculate_pack_item_cost(group_id);
	},
	// function when user change pack item misc cost
	pack_item_misc_cost_changed: function(group_id, row_id){
		var inp = this.f['items['+group_id+'][pack]['+row_id+'][misc_cost]'];
		
		mf(inp);
		
		if(inp.value<0)	inp.value = 0;
		
		// recalculate pack item cost
		this.calculate_pack_item_cost(group_id);
	},
	// function to calculate lose total cost
	calc_lose_total_cost: function(group_id){
		var tr_lose_item_row_list = $$('#tbody_lose_item_list-'+group_id+' tr.tr_lose_item_row');
		var total_lose_item_cost = 0;
		
		for(var i=0; i<tr_lose_item_row_list.length; i++){	// loop for each lose row
			var row_info = this.get_lose_row_info_by_ele(tr_lose_item_row_list[i]);
			var row_id = row_info['row_id'];
			var row_cost = float($('inp_lose_item_row_cost-'+group_id+'-'+row_id).value);
			
			total_lose_item_cost += row_cost;
		}
		
		$('inp_lose_total_cost-'+group_id).value = total_lose_item_cost;
		$('span_lose_total_cost-'+group_id).update(round(total_lose_item_cost, global_cost_decimal_points));
		return total_lose_item_cost;
	},
	// function to recalculate pack item cost
	calculate_pack_item_cost: function(group_id){
		var total_lose_item_cost = this.calc_lose_total_cost(group_id);
		
		var tr_pack_item_row = $('tr_pack_item_row-'+group_id+'-1');	// get first row only
		
		if(!tr_pack_item_row)	return false;	// no pack item yet
		
		var inp_calc_cost = this.f['items['+group_id+'][pack][1][calc_cost]'];
		
		var misc_cost = float(this.f['items['+group_id+'][pack][1][misc_cost]'].value);
		var pack_qty = float(this.f['items['+group_id+'][pack][1][qty]'].value);
		var calc_cost = 0;
		
		if(pack_qty > 0){	// got pack qty		
			var total_cost = total_lose_item_cost + misc_cost;
			calc_cost = float(round(total_cost / pack_qty, global_cost_decimal_points));
		}
		
		inp_calc_cost.value = round(calc_cost, 5);
		
		var pack_row_cost = calc_cost * pack_qty;
		
		// update total cost
		$('span_pack_item_row_total_cost-'+group_id+'-1').update(round(pack_row_cost, global_cost_decimal_points));
	},
	// function when user click close page
	close_page_clicked: function(params){
		this.close_page(params);
	},
	// function to close page
	close_page: function(params){
		if(can_edit){
			this.needCheckExit = false;
		}
		
		var path = phpself;
		
		if(params){
			if(params['extend_url'])	path += '?'+params['extend_url'];
		}
		
		document.location = path;
	},
	// function to check form before submit
	check_form: function(){
		if(!this.f['repacking_date'].value){	// check date
			alert('Please select date.');
			return false;
		}
		
		// check all required field
		if(!check_required_field(this.f))	return false;

		// check all group, must have at least 1 lose and pack item
		var div_item_group_list = $$('#div_item_list div.div_item_group');
		if(div_item_group_list.length<=0){	// no group
			alert('Repacking must have at least 1 group.');
			return false;
		}
				
		for(var i=0; i<div_item_group_list.length; i++){ // loop for each group
			var group_id = this.get_group_id_by_ele(div_item_group_list[i]);	// get group id
			
			var tr_lose_item_row_list = $$('#tbody_lose_item_list-'+group_id+' tr.tr_lose_item_row');
			if(tr_lose_item_row_list.length<=0){
				alert('Every group must have at least 1 Lose Item');
				return false;
			}
			
			var tr_pack_item_row_list = $$('#tbody_pack_item_list-'+group_id+' tr.tr_pack_item_row');
			if(tr_pack_item_row_list.length<=0){
				alert('Every group must have 1 Pack Item');
				return false;
			}
		}
		
		return true;
	},
	// function when user click save/confirm
	submit_form: function(act){
		var THIS = this;
		if(!this.check_form())	return false;
		
		var extend_url = '&t=saved';
		if(act == 'confirm'){
			if(!confirm('Are you sure? You cannot change anything after confirm.'))	return false;
			extend_url = '&t=completed';
		}
		
		var params = $(this.f).serialize();
		params += '&a=ajax_'+act;
		
		$$('#p_action_button input').invoke('disable');
		$('span_page_processing').show();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$$('#p_action_button input').invoke('enable');
				$('span_page_processing').hide();
					
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['repacking_id']){ // success
						alert('Save Successfully');
						
						extend_url += '&save_id='+ret['repacking_id'];
						THIS.close_page({'extend_url':extend_url});
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click delete form
	delete_form_clicked: function(){
		//if (check_login()) {
            if(!confirm('Are you sure to delete?'))	return false;
			this.needCheckExit = false;

			document.f_tmp['a'].value = 'delete_repacking';
			document.f_tmp.submit();
		//}
	}
};

var CHOOSE_ITEM_POPUP = {
	sku_autocomplete: undefined,
	open_type: '',
	group_id: 0,
	initialize: function(){
		this.f = document.f_choose_item_type;
		var THIS = this;
		
		sku_autocomplete = new SKU_AUTOCOMPLETE(this.f, 'vp-', function(sid, ret_params){
			THIS.add_item_clicked(sid, ret_params);			
		});
		
		new Draggable('div_choose_item_dialog',{ handle: 'div_choose_item_dialog_header'});	
	},
	// function when user click add 
	add_item_clicked: function(sid, ret_params){
		if(!sid){
			alert('Please select an item first.');
			return false;
		}
		
		this.close();
		
		if(this.open_type == 'pack'){
			REPACKING_FORM.add_pack_item(this.group_id, sid, ret_params['sku_id']);
		}else{
			REPACKING_FORM.add_lose_item(this.group_id, sid);
		}
	},
	// function to show the popup
	open: function(open_type, group_id){
		if(!open_type)	open_type = 'lose';
		this.open_type = open_type;
		this.group_id = group_id;
		
		curtain(true);
		center_div($('div_choose_item_dialog').show());
		
		sku_autocomplete.reset_sku_autocomplete();
		$('vp-autocomplete_sku').focus()
	},
	// function to close the popup
	close: function(){
		default_curtain_clicked();
	}
};


{/literal}
</script>

<h1>{$PAGE_TITLE} ({if is_new_id($form.id)}New{else}ID#{$form.id}{/if})</h1>

{if !is_new_id($form.id)}
	{if !$form.active}
		<h3>In-active (Deleted)</h3>
	{elseif $form.status eq 1 and $form.approved eq 1}
		<h3>Completed</h3>
	{else}
		<h3>Draft</h3>
	{/if}
{/if}

{include file="approval_history.tpl"}

<div style="display:none;">
	<form name="f_tmp" method="post">
		<input type="hidden" name="a" />
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
	</form>
	
	<div id="div_tmp_item_group">
		{include file="vp.repacking.open.item_group.tpl" group_id="__TMP_GROUP_ID__"}
	</div>
</div>

<!-- choose sku DIALOG -->
<div id="div_choose_item_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:720px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_choose_item_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_choose_item_type_dialog_header">Choose Item</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_choose_item_dialog_content" style="padding:2px;">
		<form name="f_choose_item_type" onSubmit="return false" method="post">				
			<div id="div_sku_autocomplete">
				{include file='vp.sku_items_autocomplete.tpl'}
			</div>
		</form>
	</div>
</div>
<!-- End of choose sku or category DIALOG -->

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="branch_id" value="{$form.branch_id|default:$vp_session.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="dept_id" value="{$form.dept_id}" />
	
	<div class="stdframe">
		<table>
			{* Date *}
			<tr>
				<td width="100"><b>Date</b></td>
				<td>
					<input type="text" name="repacking_date" id="inp_repacking_date" size="12" value="{$form.repacking_date|date_format:"%Y-%m-%d"}" title="Repacking Date" readonly class="inp_repacking_date" {if !$can_edit}readOnly {/if} />
					
					{if $can_edit}
						<img align="absmiddle" src="ui/calendar.gif" id="img_repacking_date" style="cursor: pointer;" title="Select Date" />
					{/if}
				</td>
			</tr>
			
			{* Department *}
			{*<tr>
				<td><b>Department</b></td>
				<td>
					<select name="dept_id">
						{foreach from=$dept_list key=dept_id item=r}
							<option value="{$dept_id}" {if $form.dept_id eq $dept_id}selected {/if}>{$r.description}</option>
						{/foreach}
					</select>
				</td>
			</tr>*}

			{* Remark *}
			<tr>
				<td valign="top"><b>Remark</b></td>
				<td>
					<textarea name="remark" style="width:300px;height:100px;">{$form.remark}</textarea>
				</td>
			</tr>
		</table>
	</div>
	
	<h1>Item List</h1>
	
	<div id="div_item_list" class="" style="background-color: #fff;">	
		{foreach from=$form.group_list key=group_id item=item_list}
			{include file="vp.repacking.open.item_group.tpl"}
		{/foreach}
	</div>

	{if $can_edit}
		<input type="button" value="Add New Group" onClick="REPACKING_FORM.add_new_group_clicked();" id="btn_add_new_group" />
		<span id="span_add_new_group_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	{/if}
</form>

<p align="center" id="p_action_button">
	{if $can_edit}
		<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onClick="REPACKING_FORM.submit_form('save');" />
		{if !is_new_id($form.id)}
			<input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="REPACKING_FORM.delete_form_clicked();">
		{/if}
		<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="REPACKING_FORM.submit_form('confirm');">
	{/if}
	<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="REPACKING_FORM.close_page_clicked();" />
	
	<br />
	<span id="span_page_processing" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Processing…</span>
</p>

<script type="text/javascript">
	{literal}
	REPACKING_FORM.initialize();
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
</script>
{include file="footer.tpl"}
