{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var REMINDER_MAIN_MODULE = {
	initialize: function(){
     	// initial reminder dialog
	    REMINDER_DIALOG_MODULE.initialize($('#div_reminder_dialog'));
	    
	    // initial reminder task list dialog
	    REMINDER_TASK_LIST_DIALOG_MODULE.initialize($('#div_reminder_task_list_dialog'));
	    
	    // event when user click create new reminder
		$('#a_open_new_reminder').live('click', function(){
            REMINDER_DIALOG_MODULE.open();  // call function to show popup - without any parameters means new reminder
		});
		
		// event when user click edit reminder
		$('#div_reminder_list a.a_open_reminder').live('click', function(){
			var id_obj = REMINDER_MAIN_MODULE.get_id_by_element(this);  // get the row id
			if(!id_obj['branch_id'] || !id_obj['id'])   return false;   // no id found
			REMINDER_DIALOG_MODULE.open(id_obj['branch_id'], id_obj['id']); // call function
		});
		
		// event when user delete reminder
		$('#div_reminder_list a.a_delete_reminder').live('click', function(){
            var id_obj = REMINDER_MAIN_MODULE.get_id_by_element(this);  // get the row id
			if(!id_obj['branch_id'] || !id_obj['id'])   return false;   // no id found
			REMINDER_MAIN_MODULE.remove_reminder(id_obj['branch_id'], id_obj['id']); // call function
		});
		
		// event when user click pick reminder task
		$('#img_pick_reminder_task').live('click', function(){
            REMINDER_DIALOG_MODULE.pick_reminder_task();    // call function
		});
		
		// event when user toggle reminder activation
		$("#div_reminder_list input[id^='chx_reminder_active-']:checkbox").live('change', function(){
			REMINDER_MAIN_MODULE.toggle_reminder_activation(this);  // call function
		});
	},
	get_id_by_element: function(ele){   // function to retrive branch id & id by passing element
	    if(!ele)    return {};
        var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the row contain activity id
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).attr('id').indexOf('tr_reminder_row-')>=0){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return {};
		
		var id_arr = $(parent_ele).attr('id').split('-');
		return {'branch_id': id_arr[1], 'id': id_arr[2]};
	},
	remove_reminder: function(bid, id){    // function to delete reminder
        // escape value
		bid = int(bid);
	    id = int(id);
	    
	    if(!bid || !id) return false;   // no ID
	    
	    if(!confirm('Are you sure?'))   return false;   // ask for confirmation

		// construct params
		var params = {
			a: 'remove_reminder',
			branch_id: bid,
			id: id
		};

		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean delete success
                    var row_ele = $('#tr_reminder_row-'+bid+'-'+id);
                    blink_element(row_ele, 'red', function(){
                        $(row_ele).remove();   // remove the row
						REMINDER_MAIN_MODULE.reset_reminder_row_num();   // recount row number
					});
					custom_alert.close();   // close the prompt dialog
	                return;
				}else{  // save failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	reset_reminder_row_num: function(){ // function recalculate the row number
	    var row_count = 0;
		$('#tbody_reminder_list span.row_no').each(function(i){
			$(this).text(i+1);
			row_count++;
		});
		
		if(row_count>0) $('#div_reminder_list tr.tr_no_data').hide();   // hide no data row
		else    $('#div_reminder_list tr.tr_no_data').show();
	},
	replace_reminder_row: function(bid, id, html){
	    if(!bid || !id || !html)    return false;   // cannot proceed if lack parameters
        var row = $('#tr_reminder_row-'+bid+'-'+id).get(0);    // try to get the row first

		if(row){    // exists, use replace
			$(row).after(html).remove();    // insert the current row and remove the old row
		}else{  // new, insert
			$('#tbody_reminder_list').append(html); // append the row at bottom
		}
		this.reset_reminder_row_num();   // regenerate row number
		blink_element($('#tr_reminder_row-'+bid+'-'+id));  // blink the row
	},
	toggle_reminder_activation: function(ele){
	    if(!ele)    return false;   // no element
	    
        var id_obj = REMINDER_MAIN_MODULE.get_id_by_element(ele);  // get the row id
		if(!id_obj['branch_id'] || !id_obj['id'])   return false;   // no id found

		var bid = id_obj['branch_id'];
		var id = id_obj['id'];
        var change_active_to = $(ele).attr('checked') ? 1 : 0;

        // construct params
		var params = {
			a: 'update_reminder_activation',
			branch_id: bid,
			id: id,
			active: change_active_to
		}
		
		$(ele).attr('disabled', true);  // disbled input

		$.post(phpself, params, function(data){
			var msg = $.trim(data);
			if(msg=='OK'){  // update success
				blink_element($('#tr_reminder_row-'+bid+'-'+id));    // blink row color
			}else{  // failed
				custom_alert.alert(msg, 'Update Reminder Active/Deactive Failed');
			}
			$(ele).attr('disabled', false); // enable back input
		});
	},
}

var REMINDER_DIALOG_MODULE = {
    dialog: undefined,
	default_title: 'Reminder',
	form_element: undefined,
	branch_id: 0,
	id: 0,
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 600, // set the width
			width:700,
			minHeight: 400,    // set the height
			height:650,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Save": function() {
                    REMINDER_DIALOG_MODULE.btn_save_clicked();
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
	reset: function(){
        $(this.dialog).dialog('option', 'title', this.default_title+' - New')   // reset title
        this.form_element = undefined; // clear the form element
        
        this.branch_id = 0;
        this.id = 0;
	},
	close: function(){
		$(this.dialog).dialog('close');
	},
	open: function(bid, id){
		// escape value
		bid = int(bid);
	    id = int(id);
	    
	    this.reset();
	    // record id and branch id
	    this.branch_id = bid;
	    this.id = id;
	    
	    if(id)  $(this.dialog).dialog('option', 'title', this.default_title+' - Edit'); // change title to "edit", if it is existing data

		$(this.dialog).html(_loading_)  // show loading icon
		            .load(phpself+'?a=open_reminder&branch_id='+bid+'&id='+id, function(){  // call ajax to load data
						REMINDER_DIALOG_MODULE.dialog_load_finish();    // load finish, callback function
					})
					.dialog('open');    // show dialog
	},
	dialog_load_finish: function(){ // initialize event when dialog load finish
	    this.form_element = document.f_reminder;    // assign form element
	    if(!this.form_element){ // form not found
             custom_alert.alert('Reminder module cannot be load.', 'Error occur');
             return false;
		}
		
		// initial datepicker
        $(this.form_element['date_from']).datepicker();
		$(this.form_element['date_to']).datepicker();
		
		// event when user change references task
		$(this.form_element['ref_task']).change(function(){
            REMINDER_DIALOG_MODULE.references_task_changed();   // call function
		});
	},
	reset_pick_reminder_task: function(){   // function use to clear and reset the hidden ref_info element
	    if(!this.form_element)  return false;   // no <form>
	    
	    // empty following fields
        $(this.form_element['ref_info[task_name]']).val('');
        $(this.form_element['ref_table']).val('');
        
        // remove all the info element
        $('#span_reminder_all_ref_info').html('');
        
	},
	references_task_changed: function(){
		var task = $.trim($(this.form_element['ref_task']).val());
		
		this.reset_pick_reminder_task();    // reset all hidden info
		
		if(task==''){   // custom task
			$('#tr_reminder_pick_task').hide();  // hide the row
		}else{  // build in task
		    $('#tr_reminder_pick_task').show(); // show the row
			if(task=='promotion_activity'){
				// nothing to do yet
			}
		}
	},
	pick_reminder_task: function(){
        if(!this.form_element)  return false;   // no <form>
        
        var task = $.trim($(this.form_element['ref_task']).val());  // get task value
        
        if(task==''){   // it is custom task
            custom_alert.alert('Custom task cannot pick.', 'Error occur');
            return false;
		}
		
		REMINDER_TASK_LIST_DIALOG_MODULE.open(task);
	},
	reminder_task_picked: function(data){   // data return after user click confirm select task
		if(!data)   return false;   // no data
		
		var ref_info_html = $.trim(data['ref_info_html']);
		var ref_table = $.trim(data['ref_table']);
		var task_name = $.trim(data['task_name']);
		var ref_id = $.trim(data['ref_id']);
		
		if(ref_info_html=='' || ref_table=='' || task_name=='' || ref_id==''){
            custom_alert.alert('Reminder task list cannot be assign.', 'Error occur');
			return false;
		}
		
		$(this.form_element['ref_info[task_name]']).val(task_name);
		$(this.form_element['ref_table']).val(ref_table);
		$(this.form_element['ref_id']).val(ref_id);
		$('#span_reminder_all_ref_info').html(ref_info_html);
	},
	check_form: function(){
        if(!this.form_element){ // <form> element not exists
			custom_alert.alert('Un-expected error occur. form element does not exists.');
			return false;
		}

		// check all required input field
		if(!check_required_input(this.form_element))	return false;
		
		var task = $.trim($(this.form_element['ref_task']).val());  // get task value

        if(task!=''){   // it is not custom task
            if($(this.form_element['ref_info[task_name]']).val()==''){
                custom_alert.alert('Please pick a task', 'Error occur');
                return false;
			}
		}
		
		return true;
	},
    btn_save_clicked: function(){
        if(!this.check_form())  return false;   // validate form

		if(!confirm('Are you sure?'))   return false;
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Saving in progress, please wait...');

        // construct params
        var params = $(this.form_element).serialize();
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['id'] && ret['branch_id']){ // got id return mean save success
                    if(ret['html']){    // got html return
						REMINDER_MAIN_MODULE.replace_reminder_row(ret['branch_id'], ret['id'], ret['html']);   // update the marketing plan row
					}
	                custom_alert.close();  // close the notify dialog
	                REMINDER_DIALOG_MODULE.close();  // close the dialog
	                return;
				}else{  // save failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
};

var REMINDER_TASK_LIST_DIALOG_MODULE = {
    dialog: undefined,
	default_title: 'Reminder Task List',
	form_element: undefined,
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 500, // set the width
			width:600,
			minHeight: 400,    // set the height
			height:550,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Confirm": function() {
                    REMINDER_TASK_LIST_DIALOG_MODULE.btn_confirm_clicked();
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
	reset: function(){
        $(this.dialog).dialog('option', 'title', this.default_title+' - Select')   // reset title
        this.form_element = undefined; // clear the form element
	},
	close: function(){
        $(this.dialog).dialog('close');
	},
	open: function(task){
		if(!task)   return false;   // no task
		
		this.reset();
		
		// get branch id and id
		var bid = REMINDER_DIALOG_MODULE.branch_id;
		var id = REMINDER_DIALOG_MODULE.id;
		
		// construct params
		var params = {
            task: task,
            branch_id: bid,
            id: id
		};
		
		$(this.dialog).html(_loading_)  // show loading icon
		            .load(phpself+'?a=open_reminder_task_list', params, function(){  // call ajax to load data
						REMINDER_TASK_LIST_DIALOG_MODULE.dialog_load_finish();    // load finish, callback function
					})
					.dialog('open');    // show dialog
	},
	dialog_load_finish: function(){ // initialize event when dialog load finish
        this.form_element = document.f_reminder_task_list;    // assign form element
	    if(!this.form_element){ // form not found
             custom_alert.alert('Reminder task list cannot be load.', 'Error occur');
             return false;
		}
		
	},
	btn_confirm_clicked: function(){
        var checked_inp  = $(this.form_element).find("input.task_list:checked").get(0); // no radio is selected
        if(!checked_inp){
            custom_alert.alert('No task is selected.', 'Error occur');
			return false;
		}
		
		// construct data to be pass
		var data = {};
		data['ref_info_html'] = $(checked_inp).siblings('span.span_all_ref_info:first').html();
		data['ref_table'] = $(checked_inp).siblings('input.task_ref_table:first').val();
		data['task_name'] = $(checked_inp).siblings('input.task_name:first').val();
		data['ref_id'] = $(checked_inp).siblings('input.ref_id:first').val();
		
        REMINDER_DIALOG_MODULE.reminder_task_picked(data);  // pass data to reminder dialog module
        this.close();    // close dialog
	}
};
{/literal}
</script>

<div id="div_reminder_dialog" style="display:none;"></div>
<div id="div_reminder_task_list_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE}</h1>

<ul>
	<li>
		<img src="/ui/new.png" align="absmiddle" />
		<a href="javascript:void(0);" id="a_open_new_reminder">Create New Reminder</a>
	</li>
</ul>

<div id="div_reminder_list" class="stdframe ui-corner-all" style="background: #fff;">
	{include file='reminder.list.tpl'}
</div>
{include file='footer.tpl'}

<script>
{literal}
	$(function(){
        REMINDER_MAIN_MODULE.initialize();  // initial module
	});
{/literal}
</script>
