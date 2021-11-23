{*
6/15/2020 2:29 PM Andy
- Added sortable to column "Propose Stock Take Date".
*}

{include file='header.tpl'}

<style>
{literal}
td.td_pic_username{
	background-color: #fcf;
}

td.curr_pic_username{
	color: blue;
	font-weight: bold;
	border: 2px solid red;
}

input.inp_propose_st_date{
	width: 80px;
	margin-right: 10px;
}
input.inp_propose_st_date[readOnly]{
	background-color: #f3f3f0 !important;
}

{/literal}
</style>
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CC_ASSGN_LIST = {
	tab_num: 1,
	page_num: 0,
	curr_sort: [],
	curr_sort_order: [],
	initialize: function(){
		// check cookies
		this.check_cookies();
		
		// Auto select first available tab
		this.select_first_tab();
		
		CC_CLONE_DIALOG.initialize();
	},
	check_cookies: function(){
		// sort cookies
		var sort_col = my_getCookie('_tbsort_cycle_count');
		if(sort_col){
			this.curr_sort['cycle_count'] = sort_col;
			this.curr_sort_order['cycle_count'] = my_getCookie('_tbsort_cycle_count_order');
		}
	},
	// Core function to auto select first tab
	select_first_tab: function(){
		// Auto select first tab
		for(var i=1;i<=9;i++){
			if($('lst'+i)){
				this.tab_num = i;
				break;
			}
		}
		// load the list
		this.list_sel();
	},
	list_sel: function(t){
		if(t == undefined){
			// maintain same tab
			if($('sel_page')){
				this.page_num = $('sel_page').value;
			}
		}else{
			// changed tab
			this.tab_num = t;
			this.page_num = 0;
		}
		
		var cc_list = $('cc_list');
		if(!cc_list) return;
		var search_str = '';
		if(this.tab_num == 0){
			var tmp_search_str = $('inp_item_search').value.trim();

			if(tmp_search_str==''){
				alert('Cannot search empty string');
				return;
			}else 	search_str = tmp_search_str;
		}

		var all_tab = $$('#div_tab .a_tab');
		for(var i=0;i<all_tab.length;i++){
			$(all_tab[i]).removeClassName('selected');
		}
		$('lst'+this.tab_num).addClassName('selected');
		
		$('cc_list').update(_loading_);
		new Ajax.Updater('cc_list', phpself+'?a=ajax_list_sel&t='+this.tab_num+'&p='+this.page_num,{
			parameters:{
				search_str: search_str
			},
			onComplete: function(msg){

			},
			evalScripts: true
		});
	},
	search_input_keypress: function (event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			this.list_sel(0);	// Search
		}
	},
	toggle_search_info: function(){
		alert("Search by Cycle Count No.");
	},
	// function when user click on change stock take person
	change_owner_clicked: function (branch_id, id, printed)	{
		var p = prompt('Enter the username for new stock take person:');
		if ( p==null || p.trim()=='') return;

		var params = {
			a: 'ajax_change_pic',
			branch_id: branch_id,
			id: id,
			new_owner: p
		};
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Reload
						THIS.list_sel();
						alert('Stock Take Person Changed.');
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
	},
	// function when user click on print sku list
	print_sku_list: function(branch_id, id, printed){
		printed = int(printed);
		if(!printed){
			if(!confirm('Are you sure to print the list?\n\n- SKU will not be changed once it was printed.')){
				return false;
			}
		}else{
			if(!confirm('Click OK to Print')){
				return false;
			}
		}
		
		window.open(phpself+'?a=print_cycle_count&branch_id='+branch_id+'&id='+id);
	},
	sort_list: function(col,grp){
		if (this.curr_sort[grp]==undefined || this.curr_sort[grp] != col)
		{
			this.curr_sort[grp] = col;
			this.curr_sort_order[grp] = 'asc';
		}
		else
		{
			this.curr_sort_order[grp] =  (this.curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
		}

		my_setCookie('_tbsort_'+grp, this.curr_sort[grp],1);
		my_setCookie('_tbsort_'+grp+'_order', this.curr_sort_order[grp],1);

		//console.log(this.curr_sort);
		this.list_sel();
	},
};

var CC_CLONE_DIALOG = {
	initialize: function(){
	},
	open: function(bid, cc_id){
		$('div_cc_clone_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_cc_clone_dialog').show());
		
		var THIS = this;
		var params = {
			a: 'ajax_show_clone_cycle_count',
			branch_id: bid,
			id: cc_id
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Redirect to main page
						$('div_cc_clone_dialog_content').update(ret['html']);
						THIS.update_propose_st_date_list();
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
				THIS.close();
			}
		});
	},
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when user change tab
	tab_changed: function(tab_name){
		// de-activate tab
		$$('#div_clone_tab a.a_tab').each(function(ele){
			$(ele).removeClassName('selected');
		});
		// only active the selected tab
		$('clone_tab-'+tab_name).addClassName('selected');
		
		// hide all details
		$$('#div_clone_contain div.div_clone_details').invoke('hide');
		// only show the selected details		
		$('div_clone_details-'+tab_name).show();
	},
	// function when user change clone method
	clone_method_changed: function(){
		var clone_method = getRadioValue(document.f_clone['clone_method']);
		
		// Hide all settings
		$$('#div_clone_contain div.div_clone_settings').invoke('hide');
		
		// only show selected settings
		$('div_clone_settings-'+clone_method).show();
	},
	// function when user click on start clone
	start_clone_clicked: function(clone_method){
		if(!clone_method){
			alert('Invalid Clone Method');
		}
		
		if(!confirm('Are you sure?')){
			return false;
		}
		
		// mark start process
		this.set_processing(true);
		
		var f;
		if(clone_method == 'advanced'){
			f = document.f_clone_settings_advanced;
		}else{
			f = document.f_clone_settings_normal;
		}
		var params = $(f).serialize()+'&a=ajax_clone_cycle_count';
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
				// mark finishe process
				THIS.set_processing(false);
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['id_list']){ // success
						alert('Clone Successfully');
						
						// select first tab
						CC_ASSGN_LIST.select_first_tab();
						// close this popup
						THIS.close();
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
		
		
	},
	// core function to set in process or not
	set_processing: function(is_processing){
		if(is_processing){
			$$('#div_cc_clone_dialog_content input.btn_process').each(function(btn){
				btn.disabled = true;
			});
			$('div_processing').update(_loading_).show();
		}else{
			$$('#div_cc_clone_dialog_content input.btn_process').each(function(btn){
				btn.disabled = false;
			});
			$('div_processing').hide();
		}
	},
	// function when user changed clone copy
	clone_copy_changed: function(){
		var inp_clone_copy = document.f_clone_settings_advanced['clone_copy'];
		mi(inp_clone_copy);
		
		// minimum = 1
		if(inp_clone_copy.value<1)	inp_clone_copy.value = 1;
		else if(inp_clone_copy.value>10)	inp_clone_copy.value = 10;
		
		// updat stock take list
		this.update_propose_st_date_list();
	},
	// function when user changed duration value
	duration_value_changed: function(){
		var inp_duration_value = document.f_clone_settings_advanced['duration_value'];
		mi(inp_duration_value);
		
		// minimum = 1
		if(inp_duration_value.value<1)	inp_duration_value.value = 1;
		
		// updat stock take list
		this.update_propose_st_date_list();
	},
	// function when user change duration type
	duration_type_changed: function(){
		var inp_duration_value = document.f_clone_settings_advanced['duration_value'];
		var duration_type = document.f_clone_settings_advanced['duration_type'].value;
		
		if(duration_type == 'manual'){
			inp_duration_value.disabled = true;
			
			// Just remove readonly
			$$('#div_propose_st_date_list input.inp_propose_st_date').each(function(inp){
				inp.readOnly = false;
			});
		}else{
			inp_duration_value.disabled = false;
			
			// Just remove readonly
			$$('#div_propose_st_date_list input.inp_propose_st_date').each(function(inp){
				inp.readOnly = true;
			});
			
			// updat stock take list
			this.update_propose_st_date_list();
		}
	},
	// Core function to update propose stock take date list
	update_propose_st_date_list: function(){
		var clone_copy = document.f_clone_settings_advanced['clone_copy'].value;
		var duration_type = document.f_clone_settings_advanced['duration_type'].value;
		var duration_value = int(document.f_clone_settings_advanced['duration_value'].value);
		
		// Clear all content
		//$('div_propose_st_date_list').update('');
		var curr_count = 0;
		$$('#div_propose_st_date_list input.inp_propose_st_date').each(function(inp){
			curr_count++;
			if(curr_count > clone_copy){
				$(inp).addClassName('need_delete');
			}
		});
		
		// Clone input if current input left than new
		for(var i=curr_count;i<clone_copy; i++){
			// clone receipt condition row
			var tmp_inp = htmlToElement($('tmp_inp_propose_st_date').outerHTML);
			
			// Remove ID
			tmp_inp.id = '';
			
			// Give it a name
			tmp_inp.name = 'propose_st_date_list[]';
			
			if(duration_type == 'manual'){
				tmp_inp.readOnly = false;
			}
			
			// add the clone html
			new Insertion.Bottom('div_propose_st_date_list', tmp_inp.outerHTML);
		}
		
		
		// Remove un-nedded
		$$('#div_propose_st_date_list input.need_delete').each(function(inp){
			$(inp).remove();
		});
		
		// Calculate propose stock take date
		if(duration_type != 'manual'){
			var new_date = new Date(document.f_clone_settings_advanced['max_series_date'].value);
			
			$$('#div_propose_st_date_list input.inp_propose_st_date').each(function(inp){
				if(duration_type == 'm'){
					// Month
					new_date.setMonth(new_date.getMonth()+duration_value);
				}else if(duration_type == 'w'){
					// Week
					new_date.setDate(new_date.getDate()+(duration_value*7));
				}else if(duration_type == 'd'){
					// Day
					new_date.setDate(new_date.getDate()+duration_value);
				}
				
				inp.value = toYMD(new_date);
			});	
		}
	},
	// function when user change propose stock take date
	propose_st_date_changed: function(inp){
		//alert(inp.value);
		var d = new Date(inp.value);
		var y = d.getFullYear();
		if(isNaN(y)){
			inp.value = '';
		}
	}
};
{/literal}
</script>

{* Clone Cycle Count Dialog *}
<div id="div_cc_clone_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:470px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_cc_clone_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Clone Cycle Count</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="CC_CLONE_DIALOG.close();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_cc_clone_dialog_content" style="padding:2px;height:430px;overflow-y:auto;">
	</div>
</div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $smarty.request.t eq 'saved'}
	<p><img src="ui/approved.png" align="absmiddle"> Cycle Count Saved as ID#{$smarty.request.id}</p>
{elseif $smarty.request.t eq 'confirmed'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> Cycle Count ID#{$smarty.request.id} confirmed. </p>
{elseif $smarty.request.t eq 'cancelled'}
	<p><img src="ui/cancel.png" align="absmiddle" /> Cycle Count ID#{$smarty.request.id} was cancelled</p>
{elseif $smarty.request.t eq 'approved'}
	<p><img src="ui/approved.png" align="absmiddle"> Cycle Count ID#{$smarty.request.id} was Fully Approved.</p>
{elseif $smarty.request.t eq 'completed'}
	<p><img src="ui/approved.png" align="absmiddle"> Cycle Count ID#{$smarty.request.id} was Completed.</p>
{elseif $smarty.request.t eq 'reset'}
    <p><img src="ui/notify_sku_reject.png" align="absmiddle"> Cycle Count ID#{$smarty.request.id} was reset.</p>
{elseif $smarty.request.t eq 'send_st'}
	<p><img src="ui/approved.png" align="absmiddle"> Cycle Count ID#{$smarty.request.id} Sent to Store Stock Take.</p>
{/if}


	<div class="card mx-3">
		<div class="card-body">
			{if $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT}
	<img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Cycle Count Assignment</a>
	{/if}
		</div>
	</div>




<div class="row mx-3 mb-2">
	<div id="div_tab" class="row tab" style="white-space:nowrap;">
			{if $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT}
			&nbsp;&nbsp;&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(1))" id="lst1" class="a_tab btn btn-outline-primary btn-rounded">Saved</a>
			&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(2))" id="lst2" class="a_tab btn btn-outline-primary btn-rounded">Waiting for Approval</a>
			&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(3))" id="lst3" class="a_tab btn btn-outline-primary btn-rounded">Rejected</a>
			&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(4))" id="lst4" class="a_tab btn btn-outline-primary btn-rounded">Cancelled</a>
		{/if}
		&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(5))" id="lst5" class="a_tab btn btn-outline-primary btn-rounded">Approved</a>
		&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(6))" id="lst6" class="a_tab btn btn-outline-primary btn-rounded">Printed</a>
		&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(7))" id="lst7" class="a_tab btn btn-outline-primary btn-rounded">WIP</a>
		&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(8))" id="lst8" class="a_tab btn btn-outline-primary btn-rounded">Completed</a>
		&nbsp;<a href="javascript:void(CC_ASSGN_LIST.list_sel(9))" id="lst9" class="a_tab btn btn-outline-primary btn-rounded">Sent to Store Stock Take</a>
		
			<a class="a_tab form-inline mt-2 ml-3" id="lst0">
				Search [<span class="link" onclick="CC_ASSGN_LIST.toggle_search_info();">?</span>] 
				&nbsp;<input class="form-control" id="inp_item_search" onKeyPress="CC_ASSGN_LIST.search_input_keypress(event);" /> 
				&nbsp;<input type="button" class="btn btn-primary" value="Go" onClick="CC_ASSGN_LIST.list_sel(0);" />
			</a>
		
		<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
</div>

<div id="cc_list" >
</div>

<script>CC_ASSGN_LIST.initialize();</script>
{include file='footer.tpl'}