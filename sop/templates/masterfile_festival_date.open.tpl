{if !$approval_screen}{include file='header.tpl'}{/if}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var FORM_LABEL = '{$form.label}';

{literal}
var FESTIVAL_DATE_MAIN_MODULE = {
	year: 0,
	form_element: undefined,
	initialize: function(){
		// assign form element
	    this.form_element = document.f_festival_date;

		// form element not found
	    if(!this.form_element){
			custom_alert.alert('Festival Date Master File Module failed to load.');
			return false;
		}
		
		// assign year
	    this.year = $(this.form_element['year']).val();
	    
	    // initialize festival date dialog
	    FESTIVAL_DATE_DIALOG_MODULE.initialize($('#div_festival_date_dialog'));
	    
	    // event when user click add new festival date
		$('#a_add_new_festival_date').live('click', function(){
            FESTIVAL_DATE_DIALOG_MODULE.open(0);    // open dialog
		});
		
		// event when user click edit festival date
		$('#tbody_festival_date_list img.open_festival_date').live('click', function(){
			var festival_date_id = FESTIVAL_DATE_MAIN_MODULE.get_id_by_ele(this);
			FESTIVAL_DATE_DIALOG_MODULE.open(festival_date_id);
		});
		
		// event when user click delete festival date
		$('#tbody_festival_date_list img.delete_festival_date').live('click', function(){
            var festival_date_id = FESTIVAL_DATE_MAIN_MODULE.get_id_by_ele(this);
            FESTIVAL_DATE_MAIN_MODULE.delete_festival_date(festival_date_id);
		});
		
		// initial color picker
		this.initial_color_picker();
		
		// click event for all input active/deactive
		$('#tbody_festival_date_list input[id^="chx_festival_date_active-"]:checkbox').live('change', function(){
		    FESTIVAL_DATE_MAIN_MODULE.toggle_festival_date_activation(this);   // call function
		});
		
		// event when user click confirm
		$('#btn_confirm_module').live('click', function(){
            FESTIVAL_DATE_MAIN_MODULE.confirm_festival_sheet();    // call function to confirm
		});
		
		// event when user click reset
		$('#btn_reset_module').live('click', function(){
            FESTIVAL_DATE_MAIN_MODULE.reset_festival_sheet();  // call function to reset
		});
		
		// event when user click revoke
		$('#btn_revoke_module').live('click', function(){
            FESTIVAL_DATE_MAIN_MODULE.revoke_festival_sheet(); // call function to revoke
		});
		
		
	},
	// function to get festival date id by passing child element
	get_id_by_ele: function(ele){
		if(!ele)    return {};
		var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the row contain id
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).hasClass('is_id_row')){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return {};

		var festival_date_id = int($(parent_ele).attr('id').split('-')[1]);
		return festival_date_id;
	},
	// function to insert/replace festival date row into container list
	replace_festival_date_row: function(festival_date_id, html){
	    if(!html)   return false;
	    
        var row_id = '#tr_festival_date-'+festival_date_id;
		var row = $(row_id).get(0);    // try to get the row first

		if(row){    // exists, use replace
			$(row).after(html).remove();    // insert the current row and remove the old row
		}else{  // new, insert
			$('#tbody_festival_date_list').append(html); // append the row at bottom
		}
		
		this.reset_festival_date_row_num();   // regenerate row number
		
		var row_ele = $(row_id);    // get the new row element
		blink_element(row_ele, 'red');    // blink the row
		this.initial_color_picker(row_ele); // initial color picker
	},
	// function to regenerate festival date row num
	reset_festival_date_row_num: function(){
        var total_row = 0;
		$('#tbody_festival_date_list span.row_no').each(function(i){   // loop all row
			$(this).text(i+1);    // set the row number
			total_row++;
		});
		if(total_row==0)	$('#tr_festival_date_no_data').show();    // show the no data remark
		else    $('#tr_festival_date_no_data').hide();    // hide the no data remark
	},
	// function to delete festival date
	delete_festival_date: function(festival_date_id){
        if(!confirm('Are you sure?'))  return false;

		// construct params
		var params = {
			a: 'delete_festival_date',
			festival_date_id: festival_date_id
		};

		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Deleting in progress, please wait...');
		// get the row element
		var tr_festival_date = $('#tr_festival_date-'+festival_date_id).get(0);
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                custom_alert.close();  // close the notify dialog
	                // animate the row to show delete success
	                if(tr_festival_date){
	                    blink_element(tr_festival_date, 'red',  function(blinked_ele){
                            $(blinked_ele).remove();   // remove the row
							FESTIVAL_DATE_MAIN_MODULE.reset_festival_date_row_num();   // recount row number
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
	// function when user change activation
	toggle_festival_date_activation: function(ele){
        if(!ele)    return false;   // no element
        var festival_date_id = $(ele).attr('id').split('-')[1];
        var change_active_to = $(ele).attr('checked') ? 1 : 0;

        // construct params
		var params = {
			a: 'update_festival_date_activation',
			festival_date_id: festival_date_id,
			active: change_active_to
		}
		$(ele).attr('disabled', true);  // disbled input

		$.post(phpself, params, function(data){
			var msg = $.trim(data);
			if(msg=='OK'){  // update success
			    blink_element($('#tr_festival_date-'+festival_date_id));  // blink row color
			}else{  // failed
				custom_alert.alert(msg, 'Update Festival Date Active/Deactive Failed');
			}
			$(ele).attr('disabled', false); // enable back input
		});
	},
	// function to get total festival in list
	get_festival_date_count: function(){
        return $('#tbody_festival_date_list span.row_no').length;
	},
	// event when user click confirm
	confirm_festival_sheet: function(){
        if(this.get_festival_date_count()<=0){  // no row found in the list
            custom_alert.alert('No festival date found', 'Confirm Failed');
            return false;
		}
		if(!confirm('Are you sure?'))   return false;   // ask for confirmation

		// construct params
		var params = {
			a: 'confirm_festival_sheet',
			year: this.year
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
                    custom_alert.info('Festival Date '+status+'.', 'Save Successfully', function(){
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
	reset_festival_sheet: function(){
        var comment = $.trim(prompt('Please enter reason:'));   // ask user for reason
	    if(!comment)    return false;

        if(!confirm('Are you sure?'))   return false;   // ask for confirmation

        // construct params
		var params = {
			a: 'reset_festival_sheet',
			year: this.year,
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
                    custom_alert.info('Festival Date reset success.', 'Reset Successfully', function(){
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
	revoke_festival_sheet: function(){
        if(!confirm('Are you sure?'))   return false;   // ask for confirmation

		// construct params
		var params = {
			a: 'revoke_festival_sheet',
			year: this.year
		};

		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');

        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean save success
                    custom_alert.info('Festival Date revoke success.', 'Revoke Successfully', function(){
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
	// function to add color picker event to all needed div
	initial_color_picker: function(tr_row){
	    if(!tr_row) tr_row = $('#tbody_festival_date_list');
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
      					var festival_date_id = FESTIVAL_DATE_MAIN_MODULE.get_id_by_ele(el);
      					FESTIVAL_DATE_MAIN_MODULE.update_calendar_color(festival_date_id, '#' + hex);
					}
				});

				$(this).addClass('added_colorpicker')   // add class so it wont double initial event
			}
		});
	},
	// function to update calendar color
	update_calendar_color: function(festival_date_id, new_color){
	    if(!festival_date_id || !new_color)   return false;   // no id
		
		var div_calendar_color = $('#div_calendar_color-'+festival_date_id);
		var default_color = $(div_calendar_color).attr('default_color');
		
		// construct params
		var params = {
			a: 'update_festival_date_calendar_color',
			festival_date_id: festival_date_id,
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
				custom_alert.alert(msg, 'Update Festival Date Calendar Color Failed');
			}
		});
	}
};

var FESTIVAL_DATE_DIALOG_MODULE = {
	dialog: undefined,
	default_title: 'Festival Date',
	form_element: undefined,
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 400, // set the width
			width:500,
			minHeight: 200,    // set the height
			height:200,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Save": function() {
                    FESTIVAL_DATE_DIALOG_MODULE.btn_save_clicked();
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
		this.form_element = undefined;
        $(this.dialog).dialog('option', 'title', this.default_title+' - New')   // reset title
	},
	close: function(){
        $(this.dialog).dialog('close');
	},
	open: function(id){
	    // escape integer
	    id = int(id);
	    
	    this.reset();
	    if(id)  $(this.dialog).dialog('option', 'title', this.default_title+' - Edit'); // change title to "edit", if it is existing data
	    
	    // construct params
	    var params = {
			a: 'open_festival_date',
			id: id,
			year: FESTIVAL_DATE_MAIN_MODULE.year
		}
	    
		$(this.dialog).html(_loading_)  // show loading icon
		            .load(phpself, params, function(){FESTIVAL_DATE_DIALOG_MODULE.dialog_load_finish();})  // call ajax to load data
					.dialog('open');    // show dialog
	},
	// function when dialog load finish
	dialog_load_finish: function(){
	    // assign form element
		this.form_element = document.f_festival_date_dialog;
		
		// form element not found
		if(!this.form_element){
			custom_alert.alert('Festival Date Dialog failed to load.');
			return false;
		}
		
		// initial date picker
		$(this.form_element['date_from']).datepicker();
		$(this.form_element['date_to']).datepicker();
	},
	// function to validate all form data before submit
	check_form: function(){
		// check all require field
		if(!check_required_input(this.form_element)) return false;
		
		return true;
	},
	// event when user click save
	btn_save_clicked: function(){
	    // validate form
		if(!this.check_form())  return false;
		
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
						FESTIVAL_DATE_MAIN_MODULE.replace_festival_date_row(ret['id'], ret['html']);   // update the row
					}
	                custom_alert.close();  // close the notify dialog
	                FESTIVAL_DATE_DIALOG_MODULE.close()  // close the dialog
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
{/literal}
</script>

<div id="div_festival_date_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE} - {$form.year}</h1>

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

<form name="f_festival_date" onSubmit="return false;">
	<input type="hidden" name="year" value="{$form.year}" />
	<input type="hidden" name="a" />
</form>

<ul>
	{if $form.label eq 'draft' or $form.label eq 'approved'}
		<li><img src="/ui/new.png" align="absmiddle" /> <a href="javascript:void(0);" id="a_add_new_festival_date">Add New Festival Date</a></li>
	{/if}
</ul>

<div id="div_festival_date_container" class="stdframe ui-corner-all" style="background:#fff;">
	{include file='masterfile_festival_date.open.festival_date_list.tpl' festival_date_list=$form.festival_date_list}
</div>

{if $approval_screen}
    <p class="c btn_area">
        <button id="btn_approve_festival_sheet" class="ui-corner-all"><img src="/ui/icons/accept.png" align="absmiddle" /> Approve</button>
		<button id="btn_reject_festival_sheet" class="ui-corner-all"><img src="/ui/icons/exclamation.png" align="absmiddle" /> Reject</button>
		{* No Terminate
		<button id="btn_terminate_festival_sheet" class="ui-corner-all"><img src="/ui/icons/delete.png" align="absmiddle" /> Terminate</button>
		*}
	</p>
{else}
	<p class="c">
		<input id="btn_close_module" type="button" class="button_close ui-corner-all" value="Close" onClick="window.location='{$smarty.server.PHP_SELF}'" />
		{if $form.label eq 'rejected'}
		    <input id="btn_revoke_module" type="button" class="button_revoke ui-corner-all" value="Revoke" />
		{/if}
		{if $form.label eq 'draft'}
		    <input id="btn_confirm_module" type="button" class="button_confirm ui-corner-all" value="Confirm & send to approval" />
		{/if}
		{if $form.label eq 'approved'}
		    <input id="btn_reset_module" type="button" class="button_reset ui-corner-all" value="Reset" />
		{/if}
	</p>
{/if}

{if !$approval_screen}{include file='footer.tpl'}{/if}

<script>

{literal}
	$(function(){
        FESTIVAL_DATE_MAIN_MODULE.initialize();   // initialize main module
	})
{/literal}
</script>
