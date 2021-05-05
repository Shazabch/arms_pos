{if !$approval_screen}
	{include file='header.tpl'}
{/if}
<style>
{literal}
#tbody-promotion_plan_list tr:nth-child(even){
	background: #eee;
}

ul.ul_promotion_activity_tree, ul.ul_promotion_activity_tree ul{
    padding:0;
	margin:0;
	list-style:none;
}

ul.ul_promotion_activity_tree li{
    padding:0;
	margin:0;
}
.div_title_area{
    white-space:nowrap;
	display:block;
}

#ul_promotion_activity_tree-0 span.span_row_action img{
	opacity:0.5;
}
#ul_promotion_activity_tree-0 span.span_row_action img:hover{
	opacity:1;
}

#ul_promotion_activity_tree-0 li.li_activity_title div.div_title_area a.selected_activity_row{
	background: #00a0f0;
	color: #fff;
	font-weight: bold;
}
.branch_own_label{
    background:#0f8;
	padding: 2px 5px;
}
.date_diff{
	color:blue;
}
{/literal}
</style>

<script>
var allow_edit = '{$allow_edit}';
var phpself = '{$smarty.server.PHP_SELF}';
var YMP_HQ_EDIT = '{$YMP_HQ_EDIT}';
var FORM_LABEL = '{$form.label}';
{literal}
var MARKETING_PLAN_MAIN_MODULE = {
	id: 0,
	container_tabs: undefined,
	new_tab_id: '',
    initialize: function(id){
        this.id = id;
		
        $('#btn_close_module').live('click', function(){window.location = phpself;});   // button "close" function
		
		// initialize promotion plan dialog
		PROMOTION_PLAN_DIALOG_MODULE.initialize($('#div_promotion_plan_dialog'));
		
		// initialize promotion plan own details dialog
		PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.initialize($('#div_promotion_plan_own_details_dialog'));
		
		// initialize activity dialog
		ACTIVITY_DIALOG_MODULE.initialize($('#div_activity_dialog'));

		// click to open activity dialog
		$('#tbl_promotion_plan_list img.open_activity_dialog').live('click', function(){
            var promotion_plan_id = MARKETING_PLAN_MAIN_MODULE.get_id_by_ele(this);     // get the promotion plan  and branch id
            ACTIVITY_DIALOG_MODULE.open(promotion_plan_id);    // open dialog
		});
		
		// click to refresh whole activity list
		$('#btn_refresh_activity_list').live('click', function(){
            ACTIVITY_DIALOG_MODULE.reload_activity_list(0);   // call reload function
		});

		// click to refresh sub activity list
		$('#ul_promotion_activity_tree-0 img.img_refresh_sub_activity').live('click', function(){
			// get activity ID
			var activity_id = ACTIVITY_DIALOG_MODULE.get_activity_id_by_element(this);
			ACTIVITY_DIALOG_MODULE.reload_activity_list(activity_id);   // call reload function
		});

		// click to toggle sub activity list
		$('#ul_promotion_activity_tree-0 img.img_toggle_sub_activity').live('click', function(){
		    var activity_id = $(this).parent().parent().attr('activity_id');
            ACTIVITY_DIALOG_MODULE.toggle_sub_activity_list(activity_id);  // call function to handle expand or collapse
		});

		// event when user click on activity title
		$('#ul_promotion_activity_tree-0 a.a_activity_title').live('click', function(){
		    // get activity ID
		    var activity_id = ACTIVITY_DIALOG_MODULE.get_activity_id_by_element(this);
		    ACTIVITY_DIALOG_MODULE.open_activity(activity_id);  // call function to load activity details
		});
		
		$('p.btn_area').buttonset();
		
		if(allow_edit){
		    // add new promotion click event
			$('#btn_add_new_promo').live('click', function(){
                PROMOTION_PLAN_DIALOG_MODULE.open();    // open without passing promotion id and branch id
			});

			// click event for all input active/deactive
			$('#div_promotion_container input[id^="chx_promotion_active-"]:checkbox').live('change', function(){
			    MARKETING_PLAN_MAIN_MODULE.toggle_promotion_activation(this);   // call function
			});

		    // click edit promotion
			$('#tbl_promotion_plan_list img.open_promotion_plan').live('click', function(){
				var promotion_plan_id = MARKETING_PLAN_MAIN_MODULE.get_id_by_ele(this);     // get the promotion plan  and branch id
				PROMOTION_PLAN_DIALOG_MODULE.open(promotion_plan_id);   // open dialog
			});

			// click delete promotion
			$('#tbl_promotion_plan_list img.delete_promotion_plan').live('click', function(){
	            var promotion_plan_id = MARKETING_PLAN_MAIN_MODULE.get_id_by_ele(this);     // get the promotion plan  and branch id
	            MARKETING_PLAN_MAIN_MODULE.delete_promotion(promotion_plan_id);   // call delete function
			});
			
			// click to add own branch data
			$('#tbl_promotion_plan_list img.add_alternative_changes').live('click', function(){
                var promotion_plan_id = MARKETING_PLAN_MAIN_MODULE.get_id_by_ele(this);     // get the promotion plan  and branch id
                PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.open(promotion_plan_id);   // open dialog
			});

			// click to delete own branch promotion details
			$('#tbl_promotion_plan_list img.delete_promotion_plan_branch_details').live('click', function(){
                var id_obj = MARKETING_PLAN_MAIN_MODULE.get_branch_own_data_id_by_ele(this);     // get the promotion plan  and branch id
                PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.delete_own_details(id_obj['branch_id'], id_obj['promotion_plan_id']);   // open dialog
			});
			
			// click to edit own branch data
			$('#tbl_promotion_plan_list img.edit_promotion_plan_branch_details').live('click', function(){
                var id_obj = MARKETING_PLAN_MAIN_MODULE.get_branch_own_data_id_by_ele(this);     // get the promotion plan  and branch id
                PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.open(id_obj['promotion_plan_id'], id_obj['branch_id']);   // open dialog
			});

            // click event for all input branch details active/deactive
			$('#div_promotion_container input[id^="chx_promotion_active_branch_details-"]:checkbox').live('change', function(){
			    var id_obj = MARKETING_PLAN_MAIN_MODULE.get_branch_own_data_id_by_ele(this);     // get the promotion plan  and branch id
			    MARKETING_PLAN_MAIN_MODULE.toggle_promotion_branch_activation(id_obj['promotion_plan_id'], id_obj['branch_id']);   // call function
			});

            // initial color picker
			this.initial_color_picker();
		
		    // click to add new activity
			$('#btn_add_new_activity').live('click', function(){
				ACTIVITY_DIALOG_MODULE.add();   // call add function
			});
		
		    // click to add sub activity
			$('#ul_promotion_activity_tree-0 img.img_add_sub_activity').live('click', function(){
				// get activity ID
				var activity_id = ACTIVITY_DIALOG_MODULE.get_activity_id_by_element(this);
				ACTIVITY_DIALOG_MODULE.add(activity_id);   // call add function
			});
			
		    // event when user save activity
			$('#btn_save_promotion_activity').live('click', function(){
	            ACTIVITY_DIALOG_MODULE.save();  // call function to save
			});

			// event when user delete activity
			$('#btn_delete_promotion_activity').live('click', function(){
	            ACTIVITY_DIALOG_MODULE.delete_activity();  // call function to delete
			});
			
		    // event when user click confirm
			$('#btn_confirm_module').live('click', function(){
                MARKETING_PLAN_MAIN_MODULE.confirm_marketing_plan();    // call function to confirm
			});
			
			// event when user click revoke
			$('#btn_revoke_module').live('click', function(){
                MARKETING_PLAN_MAIN_MODULE.revoke_marketing_plan(); // call function to revoke
			});
			
			// event when user click reset
			$('#btn_reset_module').live('click', function(){
                MARKETING_PLAN_MAIN_MODULE.reset_marketing_plan();  // call function to reset
			});
		}
	},
	get_id_by_ele: function(ele){
		if(!ele)    return {};
		var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the row contain activity id
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).hasClass('is_id_row')){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return {};

		var promotion_plan_id = int($(parent_ele).attr('id').split('-')[1]);
		return promotion_plan_id;
	},
	get_branch_own_data_id_by_ele: function(ele){
        if(!ele)    return {};
		var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the row contain activity id
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).hasClass('is_id_row')){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return {};

		var id_arr = $(parent_ele).attr('id').split('-');
		return {'branch_id': id_arr[2], 'promotion_plan_id': id_arr[1]};
	},
	replace_promotion_plan_row: function(promotion_plan_id, html){
	    var row_id = '#tr_promotion_plan-'+promotion_plan_id;
		var row = $(row_id).get(0);    // try to get the row first

		if(row){    // exists, use replace
			$(row).after(html).remove();    // insert the current row and remove the old row
		}else{  // new, insert
			$('#tbody-promotion_plan_list').append(html); // append the row at bottom
		}
		
		var row_ele = $(row_id);    // get the new row element
		this.reset_promotion_row_num();   // regenerate row number
		blink_element(row_ele, 'red');    // blink the row
		this.initial_color_picker(row_ele); // initial color picker
	},
	reset_promotion_row_num: function(){  // function to regenerate row number
	    var total_row = 0;
		$('#tbody-promotion_plan_list span.row_no').each(function(i){   // loop all promotion plan
			$(this).text(i+1);    // set the row number
			total_row++;
		});
		if(total_row==0)	$('#tr_promotion_no_data').show();    // show the no data remark
		else    $('#tr_promotion_no_data').hide();    // hide the no data remark
	},

	toggle_promotion_activation: function(ele){
	    if(!ele)    return false;   // no element
        var promotion_plan_id = $(ele).attr('id').split('-')[1];
        var change_active_to = $(ele).attr('checked') ? 1 : 0;

        // construct params
		var params = {
			a: 'update_promotion_activation',
			marketing_plan_id: this.id,
			promotion_plan_id: promotion_plan_id,
			active: change_active_to
		}
		$(ele).attr('disabled', true);  // disbled input

		$.post(phpself, params, function(data){
			var msg = $.trim(data);
			if(msg=='OK'){  // update success
			    blink_element($('#tr_promotion_plan-'+promotion_plan_id));  // blink row color
			}else{  // failed
				custom_alert.alert(msg, 'Update Promotion Active/Deactive Failed');
			}
			$(ele).attr('disabled', false); // enable back input
		});
	},
	delete_promotion: function(promotion_plan_id){
        if(!confirm('Are you sure?'))  return false;

		// construct params
		var params = {
			a: 'delete_promotion_plan',
			marketing_plan_id: this.id,
			promotion_plan_id: promotion_plan_id
		};
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Deleting in progress, please wait...');
		// get the row element
		var tr_promotion_plan = $('#tr_promotion_plan-'+promotion_plan_id).get(0);
		
		 $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                custom_alert.close();  // close the notify dialog

	                // animate the row to show delete success
	                if(tr_promotion_plan){
	                    // delete all own branch details
	                    var own_branch_details = $(tr_promotion_plan).next().get(0);   // get next <tr>
	                    while(own_branch_details){  // while still got next <tr>
	                        // check whether is own branch details row
							if($(own_branch_details).attr('id').indexOf('tr_promotion_plan_branch_details-')>=0){
							    // it is the row we want to delete
								var row_to_delete = own_branch_details;
								own_branch_details = $(own_branch_details).next().get(0);
								// blink and delete the row
								blink_element(row_to_delete, '',  function(blinked_ele){
		                            $(blinked_ele).remove();
								});
								
							}else   break;
						}
	                    blink_element(tr_promotion_plan, 'red',  function(blinked_ele){
                            $(blinked_ele).remove();   // remove the row
							MARKETING_PLAN_MAIN_MODULE.reset_promotion_row_num();   // recount row number
						});
					}
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
	toggle_promotion_branch_activation: function(promotion_plan_id, branch_id){
        if(!promotion_plan_id || !branch_id)    return false;   // no id

		var ele = $('#chx_promotion_active_branch_details-'+promotion_plan_id+'-'+branch_id).get(0);
		if(!ele)    return false;   // no element
		var change_active_to = $(ele).attr('checked') ? 1 : 0;
		
        // construct params
		var params = {
			a: 'update_promotion_branch_activation',
			marketing_plan_id: this.id,
			promotion_plan_id: promotion_plan_id,
			branch_id: branch_id,
			active: change_active_to
		}
		$(ele).attr('disabled', true);  // disbled input

		$.post(phpself, params, function(data){
			var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                 	blink_element($('#tr_promotion_plan_branch_details-'+promotion_plan_id+'-'+branch_id));  // blink row color
                 	$(ele).attr('disabled', false); // enable back input
	                return;
				}else{  // save failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Update Promotion Active/Deactive Failed');
		    $(ele).attr('disabled', false); // enable back input
		});
	},
	get_promotion_info: function(promotion_plan_id, col){
		return $('#tr_promotion_plan-'+promotion_plan_id+' td[td_type="'+col+'"]').text();
	},
	get_promotion_count: function(){
		return $('#tbody-promotion_plan_list span.row_no').length;
	},
	confirm_marketing_plan: function(){
	    if(this.get_promotion_count()<=0){  // no promotion found in the list
            custom_alert.alert('No promotion found', 'Confirm Failed');
            return false;
		}
		if(!confirm('Are you sure?'))   return false;   // ask for confirmation
		
		// construct params
		var params = {
			a: 'confirm_marketing_plan',
			marketing_plan_id: this.id
		};
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean save success
                    var status = 'confirmed';
                    if(ret['approved']) status = 'fully approved';
                    custom_alert.info('Marketing plan '+status+'.', 'Save Successfully', function(){
						window.location = phpself;  // redirect location
					});
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
	revoke_marketing_plan: function(){
		if(!confirm('Are you sure?'))   return false;   // ask for confirmation
		
		// construct params
		var params = {
			a: 'revoke_marketing_plan',
			marketing_plan_id: this.id
		};
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean save success
                    custom_alert.info('Marketing plan revoke success.', 'Revoke Successfully', function(){
						window.location = phpself;  // redirect location
					});
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
	reset_marketing_plan: function(){   // reset marketing plan
	    var comment = $.trim(prompt('Please enter reason:'));   // ask user for reason
	    if(!comment)    return false;

        if(!confirm('Are you sure?'))   return false;   // ask for confirmation
        
        // construct params
		var params = {
			a: 'reset_marketing_plan',
			marketing_plan_id: this.id,
			comment: comment
		};
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');

        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean save success
                    custom_alert.info('Marketing plan reset success.', 'Reset Successfully', function(){
						window.location = phpself;  // redirect location
					});
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
	replace_promotion_plan_own_details_row: function(row){
		var promotion_plan_id = int(row['promotion_plan_id']);
		var branch_id = int(row['branch_id']);
		var html = row['html'];
		
		var parent_row_id = '#tr_promotion_plan-'+promotion_plan_id;
		var row_id = '#tr_promotion_plan_branch_details-'+promotion_plan_id+'-'+branch_id;
		
		var row = $(row_id).get(0);    // try to get the row first

		if(row){    // exists, use replace
			$(row).after(html).remove();    // insert the current row and remove the old row
		}else{  // new, insert
			$(parent_row_id).after(html); // append the row after parent row
		}
		
		$('#span_promotion_branch-'+promotion_plan_id+'-'+branch_id).addClass('red_strike');    // add a red strike for branch
		blink_element($(row_id), 'red');    // blink the row
	},
	// function to add color picker event to all needed div
	initial_color_picker: function(tr_row){
	    if(!tr_row) tr_row = $('#tbl_promotion_plan_list');
        $(tr_row).find('div.colorSelector').each(function(){  // loop for all div which need color picker
            // found this div not yet initial color picker
			if(!$(this).hasClass('added_colorpicker')){
			    // get the default color first
			    var default_color = $(this).attr('default_color');
			    // initial color picker
                $(this).ColorPicker({
	                color: default_color,   // current selecting color
	                onShow: function (colpkr) {
						$(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						$(colpkr).fadeOut(500);
						return false;
					},
	                // function when user click confirm color
				    onSubmit: function (hsb, hex, rgb, el) {
						$(el).find('div:first')
							.css('backgroundColor', '#' + hex)  // change the background color
							//.attr('title', '#'+hex);  // change title

      					$(el).ColorPickerHide();    // hide color picker
      					// call function to update to server
      					var promotion_plan_id = MARKETING_PLAN_MAIN_MODULE.get_id_by_ele(el);
      					MARKETING_PLAN_MAIN_MODULE.update_calendar_color(promotion_plan_id, '#' + hex);
					}
				});

				$(this).addClass('added_colorpicker')   // add class so it wont double initial event
			}
		});
	},
	// function to update calendar color
	update_calendar_color: function(promotion_plan_id, new_color){
	    if(!promotion_plan_id || !new_color)   return false;   // no id

		var div_calendar_color = $('#div_calendar_color-'+promotion_plan_id);
		var default_color = $(div_calendar_color).attr('default_color');

		// construct params
		var params = {
			a: 'update_promotion_plan_calendar_color',
			promotion_plan_id: promotion_plan_id,
			new_color: new_color
		};

		$.post(phpself, params, function(data){
			var msg = $.trim(data);
			if(msg=='OK'){  // update success
			    $(div_calendar_color).attr('default_color', new_color) // assign new default color
			                    .find('div:first')
			                    .attr('title', new_color);  // change title
			}else{  // failed
			    // change back to the color which before update
			    $(div_calendar_color).ColorPickerSetColor(default_color)
								.find('div:first')
			                    .css('backgroundColor', default_color)
				custom_alert.alert(msg, 'Update Promotion Plan Calendar Color Failed');
			}
		});
	}
}


var PROMOTION_PLAN_DIALOG_MODULE = {
	default_title: 'Promotion',
	dialog: undefined,
	form_element: undefined,
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
                    PROMOTION_PLAN_DIALOG_MODULE.btn_save_clicked();
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
	open: function(promo_id){
	    // escape 'undefined' error
	    if(!promo_id)   promo_id = 0;   
	    
	    var promo_type = promo_id ? 'Edit' : 'New';
	    // construct params to pass for ajax
	    var params = {
			a: 'open_promotion_plan',
			marketing_plan_id: MARKETING_PLAN_MAIN_MODULE.id,
			promotion_plan_id: promo_id
		}
		$(this.dialog).dialog('option', 'title', this.default_title+' - '+promo_type)   // change title
		            .html(_loading_)  // show loading icon
		            .load(phpself, params, function(){PROMOTION_PLAN_DIALOG_MODULE.dialog_load_finish();})  // call ajax to load data
					.dialog('open');    // open dialog
	},
	dialog_load_finish: function(){ // event when dialgo finish load
	    this.form_element = document.f_promotion_plan;
	    
	    if(!this.form_element){ // no <form> element
            custom_alert.alert('Promotion module cannot be load.', 'Error occur');
            return false;
		}
	    
        $(this.form_element['date_from']).datepicker();
		$(this.form_element['date_to']).datepicker();
		
		// event when user click tick/untick all branches
		$(this.form_element['toggle_all_branches']).change(function(){
            PROMOTION_PLAN_DIALOG_MODULE.toggle_all_branches();
		});
	},
    toggle_all_branches: function(){
		if(!this.form_element['toggle_all_branches'])   return false;
		var checked = $(this.form_element['toggle_all_branches']).attr('checked');
		
		$(this.form_element['for_branch_id_list[]']).each(function(i){  // loop all input
			if($(this).val()!=1){   // HQ cannot be change
				$(this).attr('checked', checked);
			}
		});
	},
	check_form: function(){ // function to validate form data
	    if(!this.form_element){ // <form> element not exists
			custom_alert.alert('Un-expected error occur. form element does not exists.');
			return false;
		}

		// check all required input field
		if(!check_required_input(this.form_element))	return false;

		var got_branches_checked = false;
		$(this.form_element['for_branch_id_list[]']).each(function(index, ele){
			if($(ele).attr('checked')){
                got_branches_checked = true;    // mark validate passed
                return false;   // break the loop
			}
		});

		if(!got_branches_checked){  // no branch is selected
		    custom_alert.alert('Please select at least one branch.');
		    return false;
		}
		return true;
	},
	btn_save_clicked: function(){   // event when user click save promotion
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
                if(ret['id']){ // got id return mean save success
                    if(ret['html']){    // got html return
						MARKETING_PLAN_MAIN_MODULE.replace_promotion_plan_row(ret['id'], ret['html']);   // update the promotion plan row
					}
	                custom_alert.close();  // close the notify dialog
	                PROMOTION_PLAN_DIALOG_MODULE.close();  // close the promotion plan dialog
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
	close: function(){
		$(this.dialog).dialog('close');
	}
}

var PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE = {
    default_title: 'Promotion Branch Details',
	dialog: undefined,
	form_element: undefined,
    initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 600, // set the width
			width:700,
			minHeight: 300,    // set the height
			height:400,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Save": function() {
                    PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.btn_save_clicked();
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
	close: function(){
		$(this.dialog).dialog('close');
	},
	open: function(promotion_plan_id, bid){
	    if(!promotion_plan_id)   return false;    // no id
		if(!bid)    bid = 0;    // escape integer to avoid undefined
		
		var open_type = bid > 0 ? 'Edit' : 'New';
	    // construct params to pass for ajax
	    var params = {
			a: 'open_promotion_plan_own_details',
			marketing_plan_id: MARKETING_PLAN_MAIN_MODULE.id,
			promotion_plan_id: promotion_plan_id,
			branch_id: bid
		}
		$(this.dialog).dialog('option', 'title', this.default_title+' - '+open_type)   // change title
		            .html(_loading_)  // show loading icon
		            .load(phpself, params, function(){PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.dialog_load_finish();})  // call ajax
					.dialog('open');    // open dialog
	},
	dialog_load_finish: function(){
        this.form_element = document.f_promotion_plan_own_details;

	    if(!this.form_element){ // no <form> element
            custom_alert.alert('Promotion module cannot be load.', 'Error occur');
            return false;
		}

        $(this.form_element['date_from']).datepicker();
		$(this.form_element['date_to']).datepicker();
		
		// check got available branch or not
		var branch_id_ele = $(this.form_element['branch_id']).get(0);
		if($.trim(branch_id_ele.tagName).toLowerCase()=='select'){  // only check if it is <select>
			if(branch_id_ele.length<=1){    // only got 1 or less entry
			    this.close();   // close the dialog
                custom_alert.alert('No branch is available for this action', 'Error occur');    // prompt the error
            	return false;
			}
		}
	},
	check_form: function(){ // function to validate form data
	    if(!this.form_element){ // <form> element not exists
			custom_alert.alert('Un-expected error occur. form element does not exists.');
			return false;
		}

		// check all required input field
		if(!check_required_input(this.form_element))	return false;

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
                if(ret['ok']){ // got id return mean save success
                    if(ret['html']){    // got html return
						MARKETING_PLAN_MAIN_MODULE.replace_promotion_plan_own_details_row(ret);   // update the promotion plan row
					}
	                custom_alert.close();  // close the notify dialog
	                PROMOTION_PLAN_OWN_DETAILS_DIALOG_MODULE.close();  // close dialog
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
	delete_own_details: function(branch_id, promotion_plan_id){
        if(!confirm('Are you sure?'))  return false;

		// construct params
		var params = {
			a: 'delete_promotion_plan_own_details',
			branch_id: branch_id,
			promotion_plan_id: promotion_plan_id
		};
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Deleting in progress, please wait...');
		// get the row element
		var tr_promotion_plan_own_details = $('#tr_promotion_plan_branch_details-'+promotion_plan_id+'-'+branch_id).get(0);

		 $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                custom_alert.close();  // close the notify dialog

	                // animate the row to show delete success
	                if(tr_promotion_plan_own_details){
	                    blink_element(tr_promotion_plan_own_details, 'red',  function(){
                            $(tr_promotion_plan_own_details).remove();   // remove the row
                            $('#span_promotion_branch-'+promotion_plan_id+'-'+branch_id).removeClass('red_strike');    // remove red strike from branch
						});
					}
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


var ACTIVITY_DIALOG_MODULE = {
	dialog: undefined,
	default_title: ' Promotion Activity',
	promotion_plan_id: '',
	marketing_plan_id: '',
	branch_id: 0,
	img_tree_e: '/ui/tree_e.png',
	img_tree_m: '/ui/tree_m.png',
	img_tree_l_tag: '<img src="/ui/tree_l.png" align="absmiddle" border="0" class="img_margin_left" />',
	img_tree_m_tag: '<img src="/ui/tree_m.png" align="absmiddle" border="0" class="img_margin_left" />',
	img_tree_e_tag: '<img src="/ui/tree_e.png" align="absmiddle" border="0" class="img_margin_left" />',
	img_pixel_tag: '<img src="/ui/pixel.gif" align="absmiddle" border="0" class="img_margin_left" width="24" />',
	activity_details_ajax: undefined,
	form_activity: undefined,
	autocomplete_user: undefined,
	initialize: function(div){
	    this.marketing_plan_id = MARKETING_PLAN_MAIN_MODULE.id; // store marketing plan id for easy references
	    
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 800, // set the width
			width:850,
			minHeight: 500,    // set the height
			height:600,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Close": function() {
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
	open: function(promotion_plan_id, branch_id){  // function to open activity dialog
	    if(!promotion_plan_id)  return false;   // cannot open if no id is given
		this.promotion_plan_id = promotion_plan_id;

		if(!branch_id)  branch_id = 0;  // escape integer
		this.branch_id = branch_id;
		
		// get promotion title
		var promotion_title = MARKETING_PLAN_MAIN_MODULE.get_promotion_info(promotion_plan_id, 'title');
		
		// construct params
		var params = {
		    a: 'open_promotion_activity_list',
			marketing_plan_id: this.marketing_plan_id,
			promotion_plan_id: this.promotion_plan_id,
			branch_id: branch_id
		}
		
		$(this.dialog).dialog('option', 'title', this.default_title+' - '+promotion_title)  // assign title
		            .html(_loading_)    // show loading icon
		            .load(phpself, params, function(){ACTIVITY_DIALOG_MODULE.dialog_load_finish();})    // call ajax
					.dialog('open');    // show popup
	},
	dialog_load_finish: function(){ // event when dialog finish load
	    // event when user change branch
	    $('#sel_promotion_activity_branch').change(function(){
			var bid = $(this).val();    // get the new select branch id
			var promotion_plan_id = ACTIVITY_DIALOG_MODULE.promotion_plan_id; // get back the old promotion id
			if(bid>0){
                ACTIVITY_DIALOG_MODULE.open(promotion_plan_id, bid);    // reload the dialog
			}
		});
        $('#span_activity_button_area').buttonset();
        this.check_ul_child_category_tree($('#ul_promotion_activity_tree-0'));  // generate category tree
	},
	add: function(root_id){    // function to add new activity
		if(!root_id)    root_id = 0;    // if no parent, set it as top
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
		var parent_ul = $('#ul_promotion_activity_tree-'+root_id).get(0);  // get the parent <ul>
		var parent_li = $(parent_ul).parent("li").get(0);  // get the parent <li>
		var parent_level = $(parent_ul).attr('level');
		var current_level = int(parent_level)+1;
		
		// construct params
		var params = {
			a: 'add_new_promotion_activity',
			marketing_plan_id: this.marketing_plan_id,
			promotion_plan_id: this.promotion_plan_id,
			branch_id: this.branch_id,
			root_id: root_id
		};
		
		// call ajax
		$.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['id']){ // got id return mean save success
                    if($.trim($(parent_ul).text())=='' && root_id>0){    // first time open, need to load the list first
                        ACTIVITY_DIALOG_MODULE.reload_activity_list(root_id, function(got_data){    // reload list first
                            //ACTIVITY_DIALOG_MODULE.add_new_activity_list(ret);    // call function to add activity by passing new ID
						});
					}else{  // direct add at bottom
                        ACTIVITY_DIALOG_MODULE.add_new_activity_list(ret);    // call function to add activity by passing new ID
					}
                    
	                custom_alert.close();  // close the notify dialog
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
	add_new_activity_list: function(activity_row){
		if(!activity_row)   return false;   // no data
		
		var activity_id = activity_row['id'];
	    if(!activity_id)    return false;   // no ID
	    
	    var root_id = activity_row['root_id'];
	    
        var parent_ul = $('#ul_promotion_activity_tree-'+root_id).get(0);  // get the parent <ul>
        if(!parent_ul)  return false;   // parent parent <ul> found
        
        var new_li = $('#li_activity_title-sample').clone();

		$(new_li).attr('id', 'li_activity_title-'+activity_id) // set new ID
		        .attr('activity_id', activity_id)  // set temporary activity ID
		        .appendTo(parent_ul);   // append to the list

		var img_tree_line = $(new_li).find('img.img_tree_line:first');  // get image tree line

		this.check_ul_child_category_tree(parent_ul);   // check and reconstruct parent <ul> tree

		$(new_li).find('ul.ul_promotion_activity_tree:first')   // find the added sub activity <ul>
				.attr('id', 'ul_promotion_activity_tree-'+activity_id)   // set the id for sub activity <ul>
		        .attr('level', activity_row['level']) // assign the activity level
		        
		// title
		$(new_li).find('span.title:first').text(activity_row['title']); // assign title
		
		if(root_id>0){
            $(parent_ul).show();
			$('#li_activity_title-'+root_id+' img.img_toggle_sub_activity:first').attr('src', '/ui/collapse.gif');   // change the image to collapse
		}
	},
	check_ul_child_category_tree: function(ul){ // function to reconstruct all <li> tree using <ul>
		$(ul).find("li.li_activity_title").each(function(i){
            ACTIVITY_DIALOG_MODULE.reconstruct_tree_line(this);
		});
		// add draggable and droppable to all activity
        if(allow_edit)	this.add_move_activity_feature();
	},
	reconstruct_tree_line: function(li){    // reconstruct this <li> tree
	    if(!li) return false;
	    var span_tree_line_area = $(li).find('span.span_tree_line_area:first').html('');    // clear the span
	    
	    var check_parent_li = li;
	    var first_prepend = true;
	    var tag_to_add = this.img_tree_l_tag;
	    
        while(check_parent_li){ // while still got parent activity
		    // check whether parent activity is last activity
		    if($(check_parent_li).next().get(0)){   // it is not the last
		        if(first_prepend)   tag_to_add = this.img_tree_m_tag;
                else	tag_to_add = this.img_tree_l_tag;
			}else{  // it is the last
			    if(first_prepend)   tag_to_add = this.img_tree_e_tag;
				else	tag_to_add = this.img_pixel_tag;
			}

			$(tag_to_add).prependTo(span_tree_line_area);   // add the image

			check_parent_li = $(check_parent_li).parent("ul").parent("li").get(0);  // get parent
			first_prepend = false;
		}
	},
	toggle_sub_activity_list: function(activity_id){    // event when user click expand\collapse sub activity
	    if(!activity_id)    return false;   // no ID
	    var img_toggle_sub_activity = $('#li_activity_title-'+activity_id+' img.img_toggle_sub_activity:first');    // get the image
	    var ul_sub_activity_list = $('#ul_promotion_activity_tree-'+activity_id);   // get the list
	    
		if($(img_toggle_sub_activity).attr('src').indexOf('collapse')>=0){  // is collapse
		    $(ul_sub_activity_list).hide();   // hide the list
			$(img_toggle_sub_activity).attr('src', '/ui/expand.gif');   // change the image to expand
		}else{  // is expand
            if($.trim($(ul_sub_activity_list).text())==''){   // is empty, need load
				this.reload_activity_list(activity_id, function(got_data){
					if(!got_data)   custom_alert.alert('No sub activity found.', 'No Data');
				}); // call ajax to load the list
			}else{
				$(ul_sub_activity_list).show();
				$(img_toggle_sub_activity).attr('src', '/ui/collapse.gif');   // change the image to collapse
			}
		}
	},
	reload_activity_list: function(activity_id, callback){    // function to load the list
		// activity_id equal 0 mean refresh root
		var ul_sub_activity_list = $('#ul_promotion_activity_tree-'+activity_id);   // get the list
		
		if(activity_id>0){  // root have no image, so image changing only apply for second level activity
            var img_toggle_sub_activity = $('#li_activity_title-'+activity_id+' img.img_toggle_sub_activity:first');    // get the image
		    if(img_toggle_sub_activity.attr('src').indexOf('clock')>=0) return false;   // currently is loading
		}
		
		// construct params
		var params = {
            a: 'open_promotion_activity_list',
			marketing_plan_id: this.marketing_plan_id,
			promotion_plan_id: this.promotion_plan_id,
			branch_id: this.branch_id,
			root_id: activity_id,
			show_row_only: 1
		};
		
		if(img_toggle_sub_activity)	$(img_toggle_sub_activity).attr('src', '/ui/clock.gif');    // show loading
		
		$.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok']){ // got ok return mean save success
                    var got_data = false;
                    if(ret['html']){    // got html to show
                        $(ul_sub_activity_list).html(ret['html']);  // add html
                        ACTIVITY_DIALOG_MODULE.check_ul_child_category_tree(ul_sub_activity_list);  // check tree
                        got_data = true;
					}
					
					if(!got_data){  // no data
						$(ul_sub_activity_list).html('');   // clear the list
					}
					$(ul_sub_activity_list).show(); // show the list
					ACTIVITY_DIALOG_MODULE.check_ul_child_category_tree(ul_sub_activity_list);  // check tree
					if(img_toggle_sub_activity)
                    	$(img_toggle_sub_activity).attr('src', '/ui/collapse.gif');    // show collapse
					else{
                        $('#div_activity_details').html('');    // if refresh root, clean the activity details
					}
					
					if(callback)    callback(got_data); // triggle callback
	                return;
				}else{  // save failed
				    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    alert(err_msg);
		    if(img_toggle_sub_activity)	$(img_toggle_sub_activity).attr('src', '/ui/expand.gif');    // show expand
		    if(callback)    callback(false); // triggle callback
		})
	},
	add_move_activity_feature: function(){  // function to add draggable and droppable event to all activity
        $('#ul_promotion_activity_tree-0 li.li_activity_title')
		.draggable({
            handle: 'a.a_activity_title:first',   // click on which element to start drag
            revert: 'invalid',  // whether to return previous position, invalid = revert if no drop to droppable area
            opacity: 0.35,  // opacity color when dragging
            helper: 'clone',    // what to drag. values: 'original', 'clone'
            containment: $('#div_promotion_activity_left_container') // constrain the drag only inside this element
		});


		$('#ul_promotion_activity_tree-0 div.div_title_area, #div_promotion_activity_left_container div.div_top_activity')
		.droppable({
            hoverClass: 'drophover',    // add class when element is drag over
            accept: 'li.li_activity_title', // element selector to accept
            tolerance: 'pointer',    // what potion of part to trigger dragover: values: 'fit', 'intersect', 'pointer', 'touch'
            drop: function(event, ui){   // event when element drag and drop here
                var from_activity_id = $(ui.draggable).attr('activity_id');
                var to_activity_id = ACTIVITY_DIALOG_MODULE.get_activity_id_by_element(this);
				ACTIVITY_DIALOG_MODULE.move_activity(from_activity_id, to_activity_id); // call function to move

				return false;
			}
		});
	},
	get_activity_id_by_element: function(ele){
        var parent_ele = $(ele).parent().get(0);

		while(parent_ele){    // loop parebt until it found the row contain activity id
		    if($.trim(parent_ele.tagName).toLowerCase()=='li'){
                if($(parent_ele).hasClass('li_activity_title')){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return false;
		var activity_id = $(parent_ele).attr('activity_id');
		return activity_id;
	},
	open_activity: function(activity_id){
	    if(!activity_id)    return false;   // no ID
	    var a_link_title = $('#li_activity_title-'+activity_id+' a.a_activity_title:first');    // get the link
	    
        // remove selection color on other activity row
	    $('#ul_promotion_activity_tree-0 a.a_activity_title').removeClass('selected_activity_row');
	    // add selection color on selected
		$(a_link_title).addClass('selected_activity_row');
		
		// construct params
		var params = {
			a: 'open_promotion_activity',
			marketing_plan_id: this.marketing_plan_id,
			promotion_plan_id: this.promotion_plan_id,
			branch_id: this.branch_id,
			activity_id: activity_id
		};
		
		if(this.activity_details_ajax){ // if got another ajax ongoing
			try{
				this.activity_details_ajax.abort(); // try to abort the previous ajax
			}catch(ex){

			}
		}
		$('#div_activity_details').html(_loading_);  // show loading icon
		this.current_pic_list = []; // clear the pic list
		this.activity_details_ajax = $.ajax({    // call ajax to load the page
			url: phpself,
			data: params,
			success: function(data){
                $('#div_activity_details').html(data);  // update the <div> with data
                ACTIVITY_DIALOG_MODULE.activity_details_load_finish();  // call a function when load finish
			},
			completed: function(xhr){
                ACTIVITY_DIALOG_MODULE.activity_details_ajax = undefined;   // clear the ajax object
			}
		});
	},
	add_current_pic_list: function(user_id){
		if(!user_id)    return false;   // no ID
		this.current_pic_list.push(user_id);    // add ID into array
	},
	activity_details_load_finish: function(){   // event when activity details load finish
		this.form_activity = document.f_promotion_activity; // assign <form> element to store
		if(!this.form_activity){
		    custom_alert.alert('Activity module cannot be load.');
            return false;   // no <form> element, cannot proceed
		} 
		
		// initial date picker
		if(allow_edit && (FORM_LABEL=='draft' || FORM_LABEL=='approved')){
            $(this.form_activity['date_from']).datepicker();
			$(this.form_activity['date_to']).datepicker();
		}
		
		var view_only = 1;
		if(YMP_HQ_EDIT && allow_edit && $(this.form_activity['can_assign_pic']).val()==1)   view_only = 0;
		
		// initialize user autocomplete
		var current_pic_list = [];
		if(this.current_pic_list)   current_pic_list = this.current_pic_list;
		this.autocomplete_user = new AUTOCOMPLETE_USER({
			'search_input': this.form_activity['inp_autocomplete_input'],    // search input
			'add_button': this.form_activity['btn_autocomplete_add'],    // button to handle add event
			'user_id_input_name': 'pic_user_id_list',   // customize input name
			'container': $('#div_autocomplete_user_list'),  // where the user element add to
			'current_list': current_pic_list,   // pre-generate some user in the container
			'user_templates': '<div class="div_autocomplete_user"><input type="hidden" name="pic_user_id_list[]" class="inp_user_id" /><span class="span_autocomplete_username">user</span><div style="float:right;"><img src="/ui/closewin.png" class="clickable img_autocomplete_delete" title="Delete" /></div></div>', // custom template
			'view_only': view_only // can edit or not
		});  
		
		$('#p_promotion_activity_button_area').buttonset();
		
		if(allow_edit && (FORM_LABEL=='draft' || FORM_LABEL=='approved')){

		}else	form_disable(this.form_activity);
	},
	move_activity: function(from_activity_id, to_activity_id){
	    //alert(from_activity_id+' to '+to_activity_id);
		if(!from_activity_id)    return false;   // no ID, cannot move

		if(!confirm('Are you sure? '))   return false;

		// construct params
        var params = {
			'a': 'move_promotion_activity',
			marketing_plan_id: this.marketing_plan_id,
			promotion_plan_id: this.promotion_plan_id,
			branch_id: this.branch_id,
			from_activity_id: from_activity_id,
			to_activity_id: to_activity_id
		}

		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...', 'Activity Moving...');

        // call ajax to move
        $.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok'] && ret['activity_row']){ // got ok return mean save success
                    // remove from current position
                    $('#li_activity_title-'+from_activity_id).remove();
                    ACTIVITY_DIALOG_MODULE.check_ul_child_category_tree($('#ul_promotion_activity_tree-0'));  // check tree
                    // get parent ul
                    var parent_ul = $('#ul_promotion_activity_tree-'+ret['activity_row']['root_id']).get(0);
                    if($.trim($(parent_ul).text())==''){    // not yet show, use refresh
                        ACTIVITY_DIALOG_MODULE.reload_activity_list(ret['activity_row']['root_id']);
					}else{
                        ACTIVITY_DIALOG_MODULE.add_new_activity_list(ret['activity_row']);  // append to new parent
					}
                    $('#div_activity_details').html('Activity moved.');
                    custom_alert.close();  // close the notify dialog
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
	check_form: function(){
		if(!this.form_activity){
			custom_alert.alert('Cannot found form element.');
			return false;
		}
		
		// check all required form
		var passed = check_required_input(this.form_activity);
		if(!passed) return false;
		
		return true;
	},
	save: function(){
	    // validate form
		if(!this.check_form())  return false;
		
		if(!confirm('Are you sure?'))   return false;
		
        // prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
        // construct params
        var params = $(this.form_activity).serialize();
        var activity_id = this.form_activity['activity_id'].value;
        var title = this.form_activity['title'].value;
        
        // call ajax
		$.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok']){ // got id return mean save success
                    ACTIVITY_DIALOG_MODULE.update_activity_tree_title(activity_id, title)
	                custom_alert.close();  // close the notify dialog
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
	update_activity_tree_title: function(activity_id, title){   // function to update activity tree title
	    if(!activity_id || !title)    return false;   // no ID or no title
        $('#li_activity_title-'+activity_id+' span.title:first').text(title);
	},
	delete_activity: function(){
	    if(!this.form_activity) return false;   // cannot proceed if cannot found form element
	    
		if(!confirm('Warning!!! All sub activities will also been delete.\nAre you sure? '))   return false;
		
		// construct params
		var activity_id = this.form_activity['activity_id'].value;
        var params = {
			'a': 'delete_promotion_activity',
			marketing_plan_id: this.form_activity['marketing_plan_id'].value,
			promotion_plan_id: this.form_activity['promotion_plan_id'].value,
			branch_id: this.form_activity['branch_id'].value,
			activity_id: activity_id
		}
        
        // prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');
        
        // call ajax to delete
        $.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok']){ // got ok return mean save success
                    var li_ele = $('#li_activity_title-'+activity_id).get(0);   // get <li> element
                    if(li_ele){
						$('#div_activity_details').text('Activity deleted.');    // show blank in container
						$(li_ele).fadeOut('slow', function(){   // fade out the element and delete
							$(li_ele).remove();
						});
					}
                    custom_alert.close();  // close the notify dialog
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
}
{/literal}
</script>

<div id="div_promotion_plan_dialog" style="display:none;"></div>
<div id="div_promotion_plan_own_details_dialog" style="display:none;"></div>
<div id="div_activity_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE}</h1>

{include file='approval_history.tpl' approval_history_data=$form.approval_history_data screen_id=$form.id}

<h4>Status:
	{if $form.label eq 'draft'}Draft
	{elseif $form.label eq 'waiting_approve'}Waiting For Approvals
	{elseif $form.label eq 'approved'}Fully Approved
	{elseif $form.label eq 'deleted'}Deleted
	{elseif $form.label eq 'rejected'}Rejected
	{elseif $form.label eq 'terminated'}Terminated
	{else}Unknown{/if}
</h4>
<div class="stdframe ui-corner-all" style="background:#fff">
	<h4>General Informations</h4>
	<table>
	    <tr>
	        <td width="100"><b>Date</b></td>
	        <td>{$form.date_from|default:'-'} <b>to</b> {$form.date_to|default:'-'}</td>
	    </tr>
	    <tr>
	        <td><b>Year</b></td>
	        <td>{$form.year|default:'-'}</td>
	    </tr>
	</table>
</div>

<br />

<div id="div_promotion_container" class="stdframe ui-corner-all" style="background:#fff;">
	{include file='yearly_marketing_plan.marketing_plan_details.promotion_plan_list.tpl' promotion_plan_list=$form.promotion_plan_list}
</div>

<br />

{if $approval_screen}
    <p class="c btn_area">
        <button id="btn_approve_marketing_plan" class="ui-corner-all"><img src="/ui/icons/accept.png" align="absmiddle" /> Approve</button>
		<button id="btn_reject_marketing_plan" class="ui-corner-all"><img src="/ui/icons/exclamation.png" align="absmiddle" /> Reject</button>
		<button id="btn_terminate_marketing_plan" class="ui-corner-all"><img src="/ui/icons/delete.png" align="absmiddle" /> Terminate</button>
	</p>
{else}
    <p class="btn_area">
	{if $allow_edit and ($form.label eq 'draft' or $form.label eq 'approved') and $YMP_HQ_EDIT}
		<button id="btn_add_new_promo"><img src="/ui/icons/cart_add.png" align="absmiddle" /> Add New Promotion</button>
	{/if}
	</p>
	
	<p class="c">
		<input id="btn_close_module" type="button" class="button_close ui-corner-all" value="Close" />
		{if $allow_edit and $form.label eq 'rejected' and $YMP_HQ_EDIT}
		    <input id="btn_revoke_module" type="button" class="button_revoke ui-corner-all" value="Revoke" />
		{/if}
		{if $allow_edit and $form.label eq 'draft' and $YMP_HQ_EDIT}
		    <input id="btn_confirm_module" type="button" class="button_confirm ui-corner-all" value="Confirm & send to approval" />
		{/if}
		{if $allow_edit and $form.label eq 'approved' and $YMP_HQ_EDIT}
		    <input id="btn_reset_module" type="button" class="button_reset ui-corner-all" value="Reset" />
		{/if}
	</p>
{/if}

{if !$approval_screen}
	{include file='footer.tpl'}
{/if}
<script>
{literal}
	$(function(){
	    {/literal}
        MARKETING_PLAN_MAIN_MODULE.initialize('{$form.id}');    // initialize module
        {literal}
	});
{/literal}
</script>
