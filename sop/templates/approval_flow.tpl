{include file='header.tpl'}

<style>
{literal}
button.btn1{
	width:70px;
}

select.sel_approvals:disabled{
	background: #f0f0f0;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

var APPROVAL_FLOW_MAIN_MODULE = {
    f_search_approval_flow: undefined,
    initialize: function(){
        // initialize approval flow dialog
		APPROVAL_FLOW_DIALOG_MODULE.initialize($('#div_approval_flow_dialog'));
		
        // user click add new approval flow
		$('#a_new_approval_flow').live('click', function(){
            APPROVAL_FLOW_DIALOG_MODULE.open(0);    // call function to open dialog
		});
		
		this.f_search_approval_flow = document.f_search_approval_flow;    // store the form element
		
		// user refresh the approval flow list
		$(this.f_search_approval_flow).submit(function(){
		    APPROVAL_FLOW_MAIN_MODULE.reload_approval_flow_list();  // call function to reload
			return false;
		});
		
		// when user click edit approval flow
		$('#div_approval_flow_list img.img_edit_approval_flow').live('click', function(){
		    var approval_flow_id = APPROVAL_FLOW_MAIN_MODULE.get_approval_flow_id_by_element(this);
            APPROVAL_FLOW_DIALOG_MODULE.open(approval_flow_id); // call function to open dialog
		});
		
		// user click activate/deactivate approval flow
		$('#div_approval_flow_list img.img_act_approval_flow').live('click', function(){
            APPROVAL_FLOW_MAIN_MODULE.toggle_activate_approval_flow(this);  // call function
            
		});
		this.reload_approval_flow_list();   // load the list
	},
	reload_approval_flow_list: function(){  // function to reload to whole approval flow list
	    // construct params
	    var params = $(this.f_search_approval_flow).serialize();
	    
		$('#div_approval_flow_list').load(phpself, params);
	},
	get_approval_flow_id_by_element: function(ele){ // function to retrieve approval flow id by passing child element
		if(!ele)    return false;
		
		var parent_ele = $(ele).get(0);
		while(parent_ele){  // loop for parent
			if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
				if($(parent_ele).hasClass('tr_approval_flow_row')){ // found the row
					return $(parent_ele).attr('approval_flow_id');
				}
			}
			parent_ele = $(parent_ele).parent().get(0);    // get further parent
		}
		return false;
	},
	toggle_activate_approval_flow: function(img){  // function when user activate/deactivate approval flow
		if(!img)    return false;   // no element
		var approval_flow_id = this.get_approval_flow_id_by_element(img);   // get ID
		if(!approval_flow_id)   return false;   // can't get ID
		
		if($(img).attr('src').indexOf('clock')>=0)  return false;   // no row is loading
		var active = 1;
		var to_src = '/ui/deact.png';
		if($(img).attr('src').indexOf('deact')>=0){
             active = 0; // is deactivate
             to_src = '/ui/act.png';
		} 
		var default_src = $(img).attr('src');
		$(img).attr('src', '/ui/clock.gif');    // change icon to loading
		
		// construct params
		var params = {
			'a': 'toggle_activate_approval_flow',
			approval_flow_id: approval_flow_id,
			active: active
		}
		
		$.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

            try{
				ret = $.parseJSON(data); // try decode json object
				if(ret['ok']){ // got ok return mean save success
                    $(img).attr('src', to_src);
				    custom_alert.close();  // close the notify dialog
				    return;
				}else{  // save failed
				    err_msg = ret['failed_reason'];
				}
			}catch(ex){ // failed to decode json, it is plain text response
				if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				else    err_msg = data;
			}

		    // prompt the error
		    $(img).attr('src', default_src);
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
};

var APPROVAL_FLOW_DIALOG_MODULE = {
    default_title: 'Approval Flow',
	dialog: undefined,
	form_element: undefined,
    initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 600, // set the width
			width:700,
			minHeight: 400,    // set the height
			height:500,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Save": function() {
                    APPROVAL_FLOW_DIALOG_MODULE.save_approval_flow();   // call function to save
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			},
			open: function(event, ui) {
			    //MARKETING_PLAN_DIALOG_MODULE.reset();   // reset promo module
			},
			beforeClose: function(event, ui){   // triggle when popup is attemping to close
	            // nothing to do?
			}
		});
		return this;
	},
	open: function(approval_flow_id){   // function to open dialog popup
	    if(!approval_flow_id)   promo_id = 0;   // escape 'undefined' error
	    var status = approval_flow_id ? 'Edit' : 'New';
	    
		// construct params
		var params = {
			'a': 'open_approval_flow',
			approval_flow_id: approval_flow_id
		};
		
		$(this.dialog).dialog('option', 'title', this.default_title+' - '+status)   // change title
		            .html(_loading_)  // show loading icon
		            .load(phpself, params, function(){APPROVAL_FLOW_DIALOG_MODULE.dialog_load_finish();})  // call ajax to load data
					.dialog('open');    // open dialog
	},
	dialog_load_finish: function(){ // function when dialog load finish
	    this.form_element = document.f_approval_flow;   // assign form element
		this.check_approval_flow_type();    // check approval type
		this.check_approval_order_type();   // check order type
		if(!this.form_element)  return false;
		var form_element = this.form_element;
		
		// generate button
		$('#btn_approvals_up').button();  
		$('#btn_approvals_down').button();
		$('#btn_approvals_add').button();
		$('#btn_approvals_remove').button();
		$('#btn_notify_users_add').button();
		$('#btn_notify_users_remove').button();
		
		// initial event when user change approval flow type
		$(form_element['flow_type']).change(function(){
            APPROVAL_FLOW_DIALOG_MODULE.flow_type_changed();    // call function
		});
		
		// initial event when user change approval order
		$(form_element['approval_order']).change(function(){
            APPROVAL_FLOW_DIALOG_MODULE.order_type_changed();   // call function
		});
		
		// initial event for button when user click move user
		$('#btn_approvals_add').click(function(){
			APPROVAL_FLOW_DIALOG_MODULE.move_user(form_element['user_id_list[]'], form_element['approvals[]']);    // call function to move
		});
		$('#btn_approvals_remove').click(function(){
			APPROVAL_FLOW_DIALOG_MODULE.move_user(form_element['approvals[]'], form_element['user_id_list[]']);    // call function to move
		});
		$('#btn_notify_users_add').click(function(){
			APPROVAL_FLOW_DIALOG_MODULE.move_user(form_element['user_id_list[]'], form_element['notify_users[]']);    // call function to move
		});
		$('#btn_notify_users_remove').click(function(){
			APPROVAL_FLOW_DIALOG_MODULE.move_user(form_element['notify_users[]'], form_element['user_id_list[]']);    // call function to move
		});
		
		// user click move approval up
		$('#btn_approvals_up').click(function(){APPROVAL_FLOW_DIALOG_MODULE.move_approval('up');});
		$('#btn_approvals_down').click(function(){APPROVAL_FLOW_DIALOG_MODULE.move_approval('down');});
	},
	disable_element: function(ele_name_arr, is_disable){
	    if(!this.form_element)  return false;   // no form
		if(!ele_name_arr)   return false;   // no element
		if(!is_disable)  is_disable = false;  // escape boolean, avoid undefined value
		
		for(var i=0; i<ele_name_arr.length; i++){   // loop all element name
		    var ele = this.form_element[ele_name_arr[i]];   // get the element
		    if(ele)	$(ele).attr('disabled', is_disable);    // make sure element is exists, and assign disabled
		}
	},
	flow_type_changed: function(){
		this.check_approval_flow_type();    // check which element to on or off
	},
	order_type_changed: function(){
		this.check_approval_order_type();   // check which element to on or off
	},
	check_approval_flow_type: function(){   // function to check approval type, see what element need to disable
	    if(!this.form_element)  return false;   // no form
		var flow_type = $(this.form_element['flow_type']).val();
		
		if(flow_type==''){  // no select
		    this.disable_element(['dept_id','sku_type','approval_order'], true);    // disable all element
		}else if(flow_type=='YEARLY_MARKETING_PLAN' || flow_type=='FESTIVAL_DATE'){
            this.disable_element(['dept_id','sku_type','approval_order'], true);    // disable 
            this.disable_element(['approval_order'], false);    // enable
		}
	},
	check_approval_order_type: function(){
		if(!this.form_element)  return false;   // no form
		var order_type = $(this.form_element['approval_order']).val();
		
		if(order_type==4){
            this.disable_element(['approvals[]'], true);
		}else{
            this.disable_element(['approvals[]'], false);
		}
	},
	move_user: function(from_sel, to_sel){  // function to move user from <select> to another <select>
		if(!from_sel || !to_sel){
            custom_alert.alert('Cannot found the list to move', 'Error occur');
            return false;
		}
		
		$(from_sel).find("option:selected").appendTo(to_sel);   // take all selected option and append to target <select>
	},
	move_approval: function(order){ // function when user click "up" or "down" approval
		if(!order)  return false;   // no order
		var top_selected_opt = $(this.form_element['approvals[]']).find("option:selected:first").get(0);    // get first approval
		var btm_selected_opt = $(this.form_element['approvals[]']).find("option:selected:last").get(0); // get last approval
		
		if(!top_selected_opt || !btm_selected_opt)  return false;   // no selected element
		
		var all_selected_opt = $(this.form_element['approvals[]']).find("option:selected"); // get all approvals
		
		if(order=='up'){
			// get previous approval before the first selected approval
			var prev_opt = $(top_selected_opt).prev().get(0);
			if(prev_opt)$(all_selected_opt).insertBefore(prev_opt); // put all selected approval infront of this
			else    $(all_selected_opt).prependTo(this.form_element['approvals[]']);    // put at the very first
		}else{
		    // get next approval after the last selected approval
			var next_opt = $(btm_selected_opt).next().get(0);
			if(next_opt)$(all_selected_opt).insertAfter(next_opt); // put all selected approval after of this
			else    $(all_selected_opt).appendTo(this.form_element['approvals[]']);    // put at the very last
		}
	},
	check_form: function(){ // function to validate form before submit
		if(!check_required_input(this.form_element))  return false;	// check all required field
		
		if($(this.form_element['approval_order']).val()!=4){   // need approval, 4 = 'no approval'
            if(this.form_element['approvals[]'].length<=0){
				custom_alert.alert('You must select at least one approval');
				return false;
			}
		}
		return true;
	},
	save_approval_flow: function(){
	    if(!this.check_form()){ // validate form
			return false;
		}
		
		if(!confirm('Are you sure?'))   return false;
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Saving in progress, please wait...');
        
		$(this.form_element['approvals[]']).children().attr('selected', true);  // select all approvals
		$(this.form_element['notify_users[]']).children().attr('selected', true);  // select all notify user
		
  		// construct params
		var params = $(this.form_element).serialize();
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

            try{
				ret = $.parseJSON(data); // try decode json object
				if(ret['ok']){ // got ok return mean save success
                    APPROVAL_FLOW_MAIN_MODULE.reload_approval_flow_list();  // reload the list
				    custom_alert.close();  // close the notify dialog
				    $(APPROVAL_FLOW_DIALOG_MODULE.dialog).dialog('close');  // close the dialog
				    return;
				}else{  // save failed
				    err_msg = ret['failed_reason'];
				}
			}catch(ex){ // failed to decode json, it is plain text response
				if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				else    err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
}
{/literal}
</script>

<div id="div_approval_flow_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE}</h1>

<ul>
	<li> <a href="javascript:void(0);" id="a_new_approval_flow"><img src="/ui/new.png" title="New" align="absmiddle" border="0"> Add Flow</a></li>
</ul>

<form name="f_search_approval_flow" class="stdframe" style="background:#fff;">
	<input type="hidden" name="a" value="reload_approval_flow_list" />
	<b>Branch: </b>
	<select name="branch_id">
	    <option value="">-- All --</option>
		{foreach from=$branches key=bid item=r}
			<option value="{$bid}">{$r.code}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Flow Type: </b>
	<select name="flow_type">
		{foreach from=$flow_type key=t item=r}
		    <option value="{$t}">{$r.label}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Department: </b>
	<select name="dept_id">
	    <option value="">-- All --</option>
	    {foreach from=$depts item=r}
	        <option value="{$r.id}">{$r.description}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>SKU Type: </b>
	<select name="sku_type">
	    <option value="">-- All --</option>
	    {foreach from=$sku_types item=r}
	        <option value="{$r.code}">{$r.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="submit" value="Refresh" />
</form>

<br />
<div id="div_approval_flow_list" class="ui-corner-all stdframe" style="background:#fff;">
	{include file='approval_flow.list.tpl'}
</div>

{include file='footer.tpl'}

<script>
	{literal}
	$(function(){
        APPROVAL_FLOW_MAIN_MODULE.initialize(); // initialize module
	});
	{/literal}
</script>
