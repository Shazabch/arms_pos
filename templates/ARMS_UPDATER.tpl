{*
1/6/2014 5:16 PM Justin
- Enhanced to have separator.

5/9/2014 11:29 AM Justin
- Enhanced to have list premium & armsgo servers by type.
- Enhanced to have "Retry" button whenever the file is under "connecting more than 10 seconds" and "failed".

10/23/2015 10:31 AM Andy
- Enhanced to able to categorise consignment server.

07/13/2016 10:15 Edwin
- Enhanced on able to check all in one customer type only

2/21/2018 5:35 PM Andy
- Enhanced to able to svn commit php7.

3/22/2018 4:04 PM Andy
- Fixed svn commit php7 button still showing after user change the upload file.
*}

{include file="ARMS_UPDATER.header.tpl"}

<style>
{literal}

#div_control_panel{
	height:30px;
	background-color: #FDF5E6;
	position: fixed;
	bottom: 0;
	left: 0;
	z-index: 1000;
	width: 100%;
	padding: 3px;
	border-top: 2px outset grey;
}

#div_server_list-premium{
	min-height:100px;
	background-color: #F5F5F5;
	position: fixed;
	bottom: 45px;
	left: 5px;
	z-index: 1000;
	width: 80%;
	padding: 3px;
	border: 2px outset grey;
}

#div_server_list-armsgo{
	min-height:100px;
	background-color: #F5F5F5;
	position: fixed;
	bottom: 45px;
	left: 50px;
	z-index: 1000;
	width: 80%;
	padding: 3px;
	border: 2px outset grey;
}

#div_server_list-consign{
	min-height:100px;
	background-color: #F5F5F5;
	position: fixed;
	bottom: 45px;
	left: 300px;
	z-index: 1000;
	width: 40%;
	padding: 3px;
	border: 2px outset grey;
}

.btn_control_panel{
	font-size:1.5em; 
	color:#fff; 
	background:#f90;
}

.err_partial_upload, .err_partial_upload th, .err_partial_upload td{
	border-color: red !important;
	color: red !important;
}

.err_file_partial_upload{
	font-weight: bold;
	background-color: yellow;
}

.iframe_server_uploading{
	width: 100%;
	height: 100px;
}

div.div_server_preparing{
	background-color: #FDF5E6;
	color: #000;
	padding:5px;
	width: 80px;
}

div.div_server_connecting{
	background-color: #E0FFFF;
	color: #000;
	padding:5px;
	width: 80px;
}

div.div_server_connected{
	background-color: #E0FFFF;
	color: #006400;
	padding:5px;
	width: 80px;
	border: 1px solid #006400;
}

div.div_server_uploading{
	background-color: #CAFF70;
	color: #006400;
	padding:5px;
	width: 80px;
	border: 1px solid #006400;
}

div.div_server_done{
	background-color: #00CD00;
	color: #fff;
	padding:5px;
	width: 80px;
	border: 1px solid #006400;
	font-weight: bold;
}

div.div_server_failed{
	background-color: yellow;
	color: red;
	padding:5px;
	width: 80px;
	border: 1px solid red;
	font-weight: bold;
}

.div_retry{
	display:none;
}

img.img_mod1{
	filter: invert(100%);
}
{/literal}
</style>
<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';


{literal}

$(function(){
	ARMS_UPDATER.initialize();
});

window.addEventListener('message', receiveMessageFromIframe, false);
function receiveMessageFromIframe(event) {
	if(event.data['a']){
		switch(event.data['a']){
			case 'upload_status':
				ARMS_UPDATER.update_server_upload_status(event.data['server_name'], event.data['status'], event.data['params']);
				break;
		}
	}
}

var ARMS_UPDATER = {
	f_filter: undefined,
	f: undefined,
	save_timer: undefined,
	update_list_id_list: {},
	retry_upload: 0,
	initialize: function(){
		this.f_filter = document.f_filter;
		this.f = document.f_a;
		
		this.check_update_list_arrow();
		
		$(this.f_filter['receive_date_from']).datepicker();
		$(this.f_filter['receive_date_to']).datepicker();
		this.apply_update_list_datepicker();
		
		var THIS = this;
		
		// event when user edit something
		$('#tbody_update_list').find('textarea.txt_changes_log, textarea.txt_extras, select.sel_status, input.inp_receive_date, input.inp_username, input.inp_title, input.separator_description').live('change', function(){THIS.reset_save_timeout(this);});
		
		// event when delete update row
		$('#tbody_update_list img.img_delete').live('click', function(){THIS.delete_update_row(THIS.get_list_id_by_ele(this));});
		
		// event to move up/down update row
		$('#tbody_update_list img.img_move_up').live('click', function(){THIS.swap_update_row(THIS.get_list_id_by_ele(this), 'up');});
		$('#tbody_update_list img.img_move_down').live('click', function(){THIS.swap_update_row(THIS.get_list_id_by_ele(this), 'down');});
		
		// file list dialog
		FILE_LIST_DIALOG_MODULE.initialize($('#div_file_list'));
		
		// upload file dialog
		UPLOAD_FILE_DIALOG_MODULE.initialize($('#div_uploading_file'));
		
		// event when user click edit file list
		$('#div_upload_list span.span_edit_file_list').live('click', function(){FILE_LIST_DIALOG_MODULE.open(THIS.get_list_id_by_ele(this))});	
		
		// event when delete separator row
		$('#tbody_update_list img.img_delete_separator').live('click', function(){THIS.delete_separator_row(THIS.get_list_id_by_ele(this));});
	},
	// function when user click refresh update list
	refresh_update_list: function(){
		this.f_filter.submit();
	},
	// function when add new row is click
	add_new_update_row: function(){
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		
		var params = {
			a: 'ajax_add_update_row'
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html'] && ret['list_id']){ // got 'ok' return mean save success
					$('#tbody_update_list').prepend(ret['html']);
					THIS.apply_update_list_datepicker(ret['list_id']);	// apply datepicker
					THIS.check_update_list_arrow();
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// function to apply datepicker to receive date input box
	apply_update_list_datepicker: function(list_id){
		var inp_receive_date_list;
		
		if(list_id){
			inp_receive_date_list = $(this.f['update_list['+list_id+'][receive_date]']);
		}else{
			inp_receive_date_list = $('#tbody_update_list input.inp_receive_date');
		}
		
		$(inp_receive_date_list).each(function(){
			if(!$(this).hasClass('hasDatepicker')){
				$(this).datepicker();
			}
		});
	},
	// function to get update list row id
	get_list_id_by_ele: function(ele){
		var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the tr contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClass('tr_update_list_row')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var list_id = parent_ele.id.split('-')[1];
		
		return list_id;
	},
	// function to reset countdown to perform save
	reset_save_timeout: function(obj){
		if(this.save_timer)	clearTimeout(this.save_timer);	// delete the previous countdown
		
		if(obj){
			var list_id = this.get_list_id_by_ele(obj);
			if(list_id){
				this.update_list_id_list[list_id] = (new Date()).getTime();	// mark this row need update
			}
		}
		
		var THIS = this;
		this.save_timer = setTimeout(function(){THIS.save_form();}, 2000);	// update after 2 seconds
		this.file_row_changed();

	},
	// function to save form
	save_form: function(){
		if($.isEmptyObject(this.update_list_id_list))	return false;	// nothing to update
		
		var params =$(this.f).serialize();
		var params2 = {
			a: 'ajax_save_form',
			update_list_id_list: this.update_list_id_list
		}
		
		params = params +'&'+$.param(params2)
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					if(ret['update_list_id_list']){
						for(var tmp_list_id in ret['update_list_id_list']){
							if(ret['update_list_id_list'][tmp_list_id] == THIS.update_list_id_list[tmp_list_id]){	// this row successfully updated
								delete THIS.update_list_id_list[tmp_list_id];	// delete the update list
							}
						}
					}
					THIS.file_row_changed();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
		
	},
	// function to delete update row
	delete_update_row: function(list_id){
		if(!list_id)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		
		var params = {
			a: 'ajax_delete_update_row',
			list_id: list_id
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					$('#tr_update_list_row-'+list_id).remove();
					
					if(THIS.update_list_id_list[list_id]){
						delete THIS.update_list_id_list[list_id];
					}
					
					THIS.check_update_list_arrow();
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// function to check arrow key
	check_update_list_arrow: function(){
		var tr_update_list_row_list = $('#tbody_update_list tr.tr_update_list_row');
		
		for(var i=0,len=tr_update_list_row_list.length; i<len; i++){
			var tr_update_list_row = tr_update_list_row_list[i];
			
			var img_move_up = $(tr_update_list_row).find('img.img_move_up');
			var img_move_down = $(tr_update_list_row).find('img.img_move_down');
			
			$(img_move_up).css('visibility', i == 0 ? 'hidden' : '');
			$(img_move_down).css('visibility', i == len-1 ? 'hidden' : '');
		}
	},
	// function when user swap update row
	swap_update_row: function(list_id, direction){
		if(!list_id)	return false;
		
		var main_update_list_row = $('#tr_update_list_row-'+list_id);	// get row
		var swap_update_list_row;
		
		if(direction == 'up'){
			swap_update_list_row = $(main_update_list_row).prev();
		}else{
			swap_update_list_row = $(main_update_list_row).next();
		}

		// cannot find row to swap
		if(!swap_update_list_row || swap_update_list_row.length<=0){
			alert('Nothing to swap.');
			return false;
		}
		var list_id2 = this.get_list_id_by_ele(swap_update_list_row);

		// change sequence
		var seq1 = this.f['update_list['+list_id+'][sequence]'].value;
		var seq2 = this.f['update_list['+list_id2+'][sequence]'].value;
		
		this.f['update_list['+list_id+'][sequence]'].value = seq2;
		this.f['update_list['+list_id2+'][sequence]'].value = seq1;
		
		// swap element
		swap_ele(main_update_list_row.get(0), swap_update_list_row.get(0));
		
		// check arrow direction
		this.check_update_list_arrow();
		
		// reset save timeout
		this.reset_save_timeout(main_update_list_row.get(0));
		this.reset_save_timeout(swap_update_list_row.get(0));
	},
	// function when user tick/untick all file list
	toggle_file_table_upload_list: function(list_id){
		if(!list_id)	return false;
		
		var checked = $('#chx_toggle_file_need_upload-'+list_id).attr('checked');
		$('#div_file_table-'+list_id+' input.chx_file_need_upload-'+list_id).attr('checked', checked);
		this.file_row_changed();
	},
	// function when user click delete file
	delete_file_row: function(file_row_id){
		if(!file_row_id)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		var params = {
			a: 'ajax_delete_file_row',
			file_row_id: file_row_id
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					$('#tr_file_row-'+file_row_id).remove();
					THIS.file_row_changed();
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// call this function whenever file row changed
	file_row_changed: function(){
		this.hide_confirm_upload();
		this.count_selected_file();
	},
	// function when user click on server button (premium, arms-go or consignment)
	toggle_server_list_popup: function(type){
		var div = $('#div_server_list-'+type);
		if(div.length<=0)	return false;
		
		if(div.css('display')=='none'){
			$('div.div_server_list').hide();
			div.show();
		}else{
			div.hide();
		}
	},
	// function when user click close on server popup
	close_server_popup: function(type){
		$('#div_server_list-'+type).hide();
	},
	// function when user click all on server popup
	toggle_all_server: function(type){
		var checked = $('#chx_server_all-'+type).attr('checked');
		
		$('#div_server_list-'+type+' input.chx_server').attr('checked', checked);
		$('#div_server_list-'+type+' input.chx_row_server').attr('checked', checked);
		this.update_server_selected_count(type);
	},
	toggle_row_server: function(type, svn_server){
		var checked = $('#chx_server_row-'+type+'-'+svn_server).attr('checked');
		
		$('#div_server_list-'+type+' input.'+type+'-'+svn_server).attr('checked', checked);
		this.update_server_selected_count(type);
	},
	// event when got server checkbox changed
	update_server_selected_count: function(type){
		var count = $('#div_server_list-'+type+' input.chx_server:checked').length;
		var span = $('#span_server_selected_count-'+type);
		
		if(count>0){
			span.text('('+count+')');
		}else{
			span.text('');
		}
	},
	// function to validate form before submit file
	check_submit_file: function(){
		// no file is selected
		if($(this.f).find('input.chx_file_need_upload:checked').length<=0){
			alert('Please select at least 1 file to upload');
			return false;
		}
		
		// no server is selected
		if($('input.chx_server:checked').length<=0){
			alert('Please select at least server to upload');
			return false;
		}
		
		return true;
	},
	// function when user click upload
	btn_check_upload_clicked: function(){
		if(!this.check_submit_file())	return false;
		
		var str = "Are you sure?\n\n";
		var filename_list = [];
		
		$(this.f).find('input.chx_file_need_upload:checked').each(function(){
			var tmp_file_id = this.value;
			var tmp_filename = $('#inp_filename-'+tmp_file_id).val();
			
			if(!in_array(tmp_filename, filename_list)){
				str += tmp_filename+"\n";
				filename_list.push(tmp_filename);
			}
		});
		
		if(!confirm(str))	return false;
		
		$('table.tbl_file_table').removeClass('err_partial_upload');	// remove error class
		$('ul.ul_file_errmsg').text('');	// remove error message
		$('#div_upload_list tr.tr_file_row').removeClass('err_file_partial_upload');	// remove file error
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		var params = $(this.f).serialize();
		var params2 = {
			a: 'ajax_validate_submit_files'
		};
		params = params+'&'+$.param(params2);
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
                	$('#btn_confirm_upload').show();
                	$('#btn_confirm_svn_upload').show();
                	$('#btn_confirm_svn_php7_upload').show();
                	custom_alert.info('There is no error found, you can continue to upload.', 'No Error Found');
	                return;
				}else{  // failed
					if(ret['error']){	// got error provided
						$('#btn_confirm_upload').show();
						$('#btn_confirm_svn_upload').show();
						$('#btn_confirm_svn_php7_upload').show();
						if(ret['error']['partial_upload']){
							for(var tmp_list_id in ret['error']['partial_upload']){	// loop for each list_id which got partial upload
								$('#tbl_file_table-'+tmp_list_id).addClass('err_partial_upload');
								$('#ul_file_errmsg-'+tmp_list_id).append('<li>Only partial file selected to upload.</li>');
								
								if(ret['error']['partial_upload'][tmp_list_id]['file_id_list']){	// got provide file id list
									for(var tmp_file_id in ret['error']['partial_upload'][tmp_list_id]['file_id_list']){
										$('#tr_file_row-'+tmp_file_id).addClass('err_file_partial_upload');
									}
								}
							}
						}
						
						
						custom_alert.alert('There are some warning, please check.', 'Error occur');
						return;
					}
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// function when user check/uncheck file update checkbox
	need_upload_changed: function(file_id){
		if(!file_id)	return false;
		
		this.file_row_changed();
	},
	// function to hide confirm upload
	hide_confirm_upload: function(){
		$('#btn_confirm_upload').hide();
		$('#btn_confirm_svn_upload').hide();
		$('#btn_confirm_svn_php7_upload').hide();
	},
	// function to count selected file
	count_selected_file: function(){
		$('#span_file_selected_count').text($('#div_upload_list input.chx_file_need_upload:checked').length);
	},
	// function when user click confirm upload
	btn_confirm_upload_clicked: function(){
		if(!this.check_submit_file())	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// get file id list to upload
		var file_id_list = [];
		$(this.f).find('input.chx_file_need_upload:checked').each(function(){
			file_id_list.push(this.value);
		});
		var total_server_selected = $('input.chx_server:checked').length;
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		var params = $(document.f_server_list_premium).serialize()+'&'+$(document.f_server_list_armsgo).serialize()+'&'+$(document.f_server_list_consign).serialize();
		var params2 = {
			a: 'ajax_confirm_submit_files',
			file_id_list: file_id_list
		};

		params = params+'&'+$.param(params2);
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html']){ // got 'ok' return mean save success
                	custom_alert.close();
                	UPLOAD_FILE_DIALOG_MODULE.open(ret['html'], {'total_server_selected': total_server_selected});
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// function to start upload process
	start_upload: function(server_name, is_retry){
		var THIS = this;
		
		if(is_retry != undefined) retry_upload = 1;
		else retry_upload = 0;
		
		setTimeout(function(){
			$("#div_retry_"+server_name).hide();
			THIS.update_server_upload_status(server_name, 'connecting');
			$('#f_upload_server_'+server_name).submit();
		}, 500);
		
		setTimeout(function(){
			if($('#inp_server_upload_status-'+server_name).val() == "connecting"){
				$("#div_retry_"+server_name).show();
			}
		}, 10000);
	},
	// function to update server upload status
	update_server_upload_status: function(server_name, status, params){
		var class_list = {
			'preparing': {'class': 'div_server_preparing', 'html':'Preparing'}, 
			'connecting': {'class': 'div_server_connecting', 'html':'Connecting'},
			'connected':  {'class': 'div_server_connected', 'html':'Connected'},
			'uploading': {'class': 'div_server_uploading', 'html':'Uploading'},
			'done': {'class': 'div_server_done', 'html':'Done'},
			'failed': {'class': 'div_server_failed', 'html':'Failed'}
		};

		var div = $('#div_server_upload_status-'+server_name);
		for(var key in class_list){
			div.removeClass(class_list[key]['class']);
		}
		
		if(class_list[status]){
			div.addClass(class_list[status]['class']).html(class_list[status]['html']);
			if(params){
	
			}
			
			var POST_PARAMS = {
				'a': 'ajax_update_server_upload_status',
				'server_name': server_name,
				'tgz_filename': document['f_upload_server_'+server_name]['tgz_filename'].value,
				'status': status
			}
			
			var THIS = this;
		
			$.post(phpself, POST_PARAMS, function(data){
			    var ret = {};
			    var err_msg = '';
	
			    try{
	                ret = $.parseJSON(data); // try decode json object
	                if(ret['ok']){ // got 'ok' return mean save success
	                	if(status == 'done' || status == 'failed'){
							if(status=="failed"){
								$("#div_retry_"+server_name).show();
							}
	                		UPLOAD_FILE_DIALOG_MODULE.mark_server_complete_status(server_name, status);
	                	}
		                return;
					}else{  // failed
	                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
	                    else    err_msg = data;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = data;
				}
	
				if(!err_msg)	err_msg = 'No Respond from Server.'
			    // prompt the error
			    //custom_alert.alert(err_msg, 'Error occur');
			});
		}else{
			div.update(status);	// unknown status
		}
		
		$('#inp_server_upload_status-'+server_name).val(status);
		
		// check all 
		/*var stopped_count = 0;
		var inp_server_upload_status_list = $('#div_uploading_file input.inp_server_upload_status');
		
		inp_server_upload_status_list.each(function(){
			if(this.value == 'done' || this.value == 'failed'){
				stopped_count++;
			}
		});
		
		if(stopped_count == inp_server_upload_status_list.length){
			UPLOAD_FILE_DIALOG_MODULE.all_stopped = true;
		}*/
	},
	// function when iframe onload called
	check_server_upload_finish: function(server_name){
		alert($('#inp_server_upload_status-'+server_name).val())
		if($('#inp_server_upload_status-'+server_name).val()=='connecting'){	// still under connecting??
			this.update_server_upload_status(server_name, 'failed');
		}
	},
	// function to add new separator
	add_new_separator_row: function(){
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		
		var sequence_row = 1;
		var sequence_list = $('#tbody_update_list input.sequence');
		
		for(var i=0,len=sequence_list.length; i<len; i++){
			sequence_row = sequence_list[i].value;
			
			break;
		}
		
		var params = {
			a: 'ajax_add_separator_row',
			seq: sequence_row
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html']){ // got 'ok' return mean save success
					$('#tbody_update_list').prepend(ret['html']);
					THIS.check_update_list_arrow();
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	
	// function to delete update row
	delete_separator_row: function(list_id){
		if(!list_id)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		
		var params = {
			a: 'ajax_delete_separator_row',
			list_id: list_id
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					$('#tr_update_list_row-'+list_id).remove();
					
					if(THIS.update_list_id_list[list_id]){
						delete THIS.update_list_id_list[list_id];
					}
					
					THIS.check_update_list_arrow();
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	
	// function when user click confirm upload
	btn_confirm_svn_upload_clicked: function(svn_type){
		if(svn_type == undefined)	svn_type = '';
		if(!this.check_submit_file())	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		// get file id list to upload
		var file_id_list = [];
		$(this.f).find('input.chx_file_need_upload:checked').each(function(){
			file_id_list.push(this.value);
		});
		var total_server_selected = $('input.chx_server:checked').length;
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		var params = {
			a: 'ajax_svn_confirm_submit_files',
			file_id_list: file_id_list,
			svn_type: svn_type
		};
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html']){ // got 'ok' return mean save success
                	custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(data.indexOf("Committed revision")!=-1){
				$(THIS.f).find('input.chx_file_need_upload:checked').each(function(){
					if(svn_type == 'php7'){
						$(this).closest('tr').find('.is_svn_php7').show();
					}else{
						$(this).closest('tr').find('.is_svn').show();
					}
					
				});
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
};

var FILE_LIST_DIALOG_MODULE = {
	default_title: 'File list',
	dialog: undefined,
	onSelect: undefined,
	f: undefined,
	initialize: function(div){
		var THIS = this;
		
		// assign form
		this.f = document.f_file_list;
		
		this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 300, // set the width
			width:400,
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
				"OK": function() {
                    THIS.btn_ok_clicked();
				},
				"Cancel": function() {
					//$(this).dialog("close");
					THIS.close();
				}
			},
			open: function(event, ui) {
			    // after open
			},
			beforeClose: function(event, ui){   // triggle when popup is attemping to close
	            // nothing to do?
			}
		});
		
		
		if(!this.f){
			custom_alert.alert('File List Dialog cannot be load.');
			return false;
		}

		return this;
	},
	// close popup
	close: function(){
        $(this.dialog).dialog("close");
	},
	// open popup
	open: function(list_id){
		if(!list_id)	return false;	
		
		$('#div_file_list_content').text('');
		$(this.dialog).dialog('open');
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');

		var params = {
			a: 'ajax_load_update_file_list',
			list_id: list_id
		}
		
		var THIS = this;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html']){ // got 'ok' return mean save success
					$('#div_file_list_content').html(ret['html']);
					custom_alert.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
	// function when user click save file list
	btn_ok_clicked: function(){
		if(!this.f['list_id']){
			alert('Form id not exists');
			return false;
		}
		
		// prompt processing	
		custom_alert.prompt_in_progress('Processing. . .');
		
		var params = $(this.f).serialize();
		var params2 = {
			a: 'ajax_save_update_file_list'
		}
		
		params += '&'+$.param(params2);
		
		var THIS = this;
		var list_id = this.f['list_id'].value;
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['html']){ // got 'ok' return mean save success
                	$('#div_file_table-'+list_id).html(ret['html']);
                	ARMS_UPDATER.file_row_changed();
					custom_alert.close();
					THIS.close();
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

			if(!err_msg)	err_msg = 'No Respond from Server.'
		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	},
}


var UPLOAD_FILE_DIALOG_MODULE = {
	default_title: 'Upload File Process',
	dialog: undefined,
	onSelect: undefined,
	f: undefined,
	all_stopped: false,
	total_server_selected: 0,
	server_completed: 0,
	server_failed: 0,
	initialize: function(div){
		var THIS = this;
		
		// assign form
		this.f = document.f_file_list;
		
		this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 300, // set the width
			width:700,
			minHeight: 300,    // set the height
			height:600,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			dialogClass: 'no_close',
			buttons: {  // create a set of buttons under button areas
				/* "OK": function() {
                    THIS.btn_ok_clicked();
				}, */
				"Close": function() {
					//$(this).dialog("close");
					THIS.close();
				}
			},
			open: function(event, ui) {
			    // after open
			},
			beforeClose: function(event, ui){   // triggle when popup is attemping to close
	            // nothing to do?
			}
		});
		
		
		if(!this.f){
			custom_alert.alert('File List Dialog cannot be load.');
			return false;
		}

		return this;
	},
	// close popup
	close: function(){
		if(!this.all_stopped){
			if(!confirm('Are you sure to terminate the upload?'))	return false;
		}
		$('#div_uploading_file_content').text('');
        $(this.dialog).dialog("close");
	},
	// open popup
	open: function(html, params){
		if(params){
			if(params['total_server_selected']){
				this.server_completed = 0;
				this.server_failed = 0;
				this.total_server_selected = int(params['total_server_selected']);
			}
		}
		this.renew_title();
		// setter
		this.all_stopped = false;
		
		$('#div_uploading_file_content').html(html);
		$(this.dialog).dialog('open');
	},
	// function to renew title
	renew_title: function(){
		var title = this.default_title;
		if(this.total_server_selected){
			title += ' ('+this.server_completed+'/'+this.total_server_selected+')';
			
			if(this.server_failed>0){
				title += ' '+this.server_failed+' Failed';
			}
		}
		
		$(this.dialog).dialog( "option", "title", title);	
	},
	
	// function when server upload complete
	mark_server_complete_status: function(server_name, status){
		if(status == 'done'){
			this.server_completed++;
			if(retry_upload == 1 && $('#inp_server_upload_status-'+server_name).val() != "connecting") this.server_failed--;
		}else if(status=='failed' && retry_upload == 0){
			this.server_failed++;
		}
		
		// mark all done
		if(this.server_completed + this.server_failed >= this.total_server_selected){
			this.all_stopped = true;
			custom_alert.info('All Process Done. '+this.server_completed+' Success, '+this.server_failed+' Failed.');
			
		}	
		
		this.renew_title();
	},
}

{/literal}
</script>

<div id="div_top_right_loading" style="position:fixed;display:none;background:#ff9;opacity:0.6;top:0px;padding:5px 10px;font-weight:bold;z-index:10000;">
	<img src="/ui/alarm.png" align="absmiddle" />
	Loading…
</div>

{* File List Dialog *}
<div id="div_file_list" style="display:none;">
	<form name="f_file_list" onSubmit="return false;">		
		<div id="div_file_list_content">
		
		</div>
	</form>
</div>

{* Upload File Dialog *}
<div id="div_uploading_file" style="display:none;">
	<div id="div_uploading_file_content">
	
	</div>	
</div>

{* Premium Server List Popup *}
<div id="div_server_list-premium" class="div_server_list" style="display:none;">
	<img src="/ui/del.png" style="float:right;" onClick="ARMS_UPDATER.close_server_popup('premium');" />
	<br style="clear:both;" />
	
	<form name="f_server_list_premium" onSubmit="return false;">
		<span><input type="checkbox" id="chx_server_all-premium" onChange="ARMS_UPDATER.toggle_all_server('premium');" /> <label for="chx_server_all-premium">All</label></span>
		<br /><br />
		{foreach from=$preset_server_list.premium key=svn_server item=p_server_list}
			<div><b>{$svn_server}</b></div>
			<span><input type="checkbox" id="chx_server_row-premium-{$svn_server}" class="chx_row_server" onChange="ARMS_UPDATER.toggle_row_server('premium', '{$svn_server}');" /> <label for="chx_server_row-premium-{$svn_server}">All</label></span>
			{foreach from=$p_server_list key=server_name item=server}
				<span>
					<input type="checkbox" id="chx_server-{$server_name}" name="server_name[{$server_name}]" class="chx_server premium-{$svn_server}" onChange="ARMS_UPDATER.update_server_selected_count('premium');" value="1" />
					<label for="chx_server-{$server_name}">{$server_name}</label>
				</span>
			{/foreach}
			<br /><br />
		{/foreach}
	</form>
</div>

{* ARMS GO Server List Popup *}
<div id="div_server_list-armsgo" class="div_server_list" style="display:none;">
	<img src="/ui/del.png" style="float:right;" onClick="ARMS_UPDATER.close_server_popup('armsgo');" />
	<br style="clear:both;" />
	
	<form name="f_server_list_armsgo" onSubmit="return false;">
		<span><input type="checkbox" id="chx_server_all-armsgo" onChange="ARMS_UPDATER.toggle_all_server('armsgo');" /> <label for="chx_server_all-armsgo">All</label></span>
		<br /><br />
		{foreach from=$preset_server_list.armsgo key=svn_server item=p_server_list}
			<div><b>{$svn_server}</b></div>
			<span><input type="checkbox" id="chx_server_row-armsgo-{$svn_server}" class="chx_row_server" onChange="ARMS_UPDATER.toggle_row_server('armsgo', '{$svn_server}');" /> <label for="chx_server_row-armsgo-{$svn_server}">All</label></span>
			{foreach from=$p_server_list key=server_name item=server}
				<span>
					<input type="checkbox" id="chx_server-{$server_name}" name="server_name[{$server_name}]" class="chx_server armsgo-{$svn_server}" onChange="ARMS_UPDATER.update_server_selected_count('armsgo');" value="1" />
					<label for="chx_server-{$server_name}">{$server_name}</label>
				</span>
			{/foreach}
			<br /><br />
		{/foreach}
	</form>
</div>

{* CONSIGNMENT Server List Popup *}
<div id="div_server_list-consign" class="div_server_list" style="display:none;">
	<img src="/ui/del.png" style="float:right;" onClick="ARMS_UPDATER.close_server_popup('consign');" />
	<br style="clear:both;" />
	
	<form name="f_server_list_consign" onSubmit="return false;">
		<span><input type="checkbox" id="chx_server_all-consign" onChange="ARMS_UPDATER.toggle_all_server('consign');" /> <label for="chx_server_all-consign">All</label></span>
		<br /><br />
		{foreach from=$preset_server_list.consignment key=svn_server item=p_server_list}
			<div><b>{$svn_server}</b></div>
			<span><input type="checkbox" id="chx_server_row-consign-{$svn_server}" class="chx_row_server" onChange="ARMS_UPDATER.toggle_row_server('consign', '{$svn_server}');" /> <label for="chx_server_consign-{$svn_server}">All</label></span>
			{foreach from=$p_server_list key=server_name item=server}
				<span>
					<input type="checkbox" id="chx_server-{$server_name}" name="server_name[{$server_name}]" class="chx_server consign-{$svn_server}" onChange="ARMS_UPDATER.update_server_selected_count('consign');" value="1" />
					<label for="chx_server-{$server_name}">{$server_name}</label>
				</span>
			{/foreach}
			<br /><br />
		{/foreach}
	</form>
</div>

<h2>{$PAGE_TITLE}</h2>

<form name="f_filter" method="post" class="stdframe">
	<b>Status: </b>
	<select name="status">
		{foreach from=$status_list key=k item=v}
			<option value="{$k}" {if $smarty.request.status eq $k}selected {/if}>{$v}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>File contain: </b>
	<input type="text" name="file_pattern" size="20" value="{$smarty.request.file_pattern}" />&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Received Date: </b>
	<input type="text" name="receive_date_from" size="10" value="{$smarty.request.receive_date_from}" />
	<b>To</b>
	<input type="text" name="receive_date_to" size="10" value="{$smarty.request.receive_date_to}" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Refresh" onClick="ARMS_UPDATER.refresh_update_list();" />
</form>

<br />

<input type="button" value="Add New Row" id="btn_add_update_row" onClick="ARMS_UPDATER.add_new_update_row();" style="font-size:1.5em; color:#fff; background:#091;" />&nbsp;&nbsp;&nbsp;
<input type="button" value="Add New Separator" id="btn_add_separator_row" onClick="ARMS_UPDATER.add_new_separator_row();" style="font-size:1.5em; color:#fff; background:#a91;" />

<br /><br />

<form name="f_a" onSubmit="return false;">

<div id="div_upload_list">
	<table class="report_table" width="100%">
		<tr class="header">
			<th>&nbsp;</th>
			<th>Title / Changes Log / Extras</th>
			<th>Status</th>
			<th>Received</th>
			<th>PIC</th>
			<th>Files</th>
		</tr>
		
		<tbody id="tbody_update_list">
			{foreach from=$file_update_list key=seq_id item=list_info}
				{assign var=list_id value=$list_info.id}
				{*assign var=curr_week value=$list_info.receive_date|date_format:"%W"}
				{if $prv_week ne $curr_week}
					<tr>
						<td colspan="6"><b>Week {$list_info.receive_date|date_format:"%W"}</b></td>
					</tr>
				{/if*}
				{if !$list_info.is_separator}
					{include file="ARMS_UPDATER.list_row.tpl"}
				{else}
					{include file="ARMS_UPDATER.list_separator_row.tpl"}
				{/if}
				{assign var=prv_week value=$list_info.receive_date|date_format:"%W"}
			{/foreach}
		</tbody>
	</table>
</div>

</form>

</div>

{* Control Panel *}
<div id="div_control_panel">
	<button class="btn_control_panel" onClick="ARMS_UPDATER.toggle_server_list_popup('premium');" style="width:150px;white-space:nowrap;">Premium <span id="span_server_selected_count-premium"></span></button>
	<button class="btn_control_panel" onClick="ARMS_UPDATER.toggle_server_list_popup('armsgo');" style="width:150px;white-space:nowrap;">ARMS-GO <span id="span_server_selected_count-armsgo"></span></button>
	<button class="btn_control_panel" onClick="ARMS_UPDATER.toggle_server_list_popup('consign');" style="width:150px;white-space:nowrap;">Consignment <span id="span_server_selected_count-consign"></span></button>
	
	{* Upload *}
	<button style="font-size:1.5em; color:#fff; background:#09c;" onClick="ARMS_UPDATER.btn_check_upload_clicked();">Check for Upload</button>
	<button style="font-size:1.5em; color:#fff; background:#091;display:none;" onClick="ARMS_UPDATER.btn_confirm_upload_clicked();" id="btn_confirm_upload">Confirm Upload</button>
	<button style="font-size:1.5em; color:#fff; background:#FF33E6;display:none;" onClick="ARMS_UPDATER.btn_confirm_svn_upload_clicked();" id="btn_confirm_svn_upload">SVN Commit</button>
	<button style="font-size:1.5em; color:#fff; background:#FF33E6;display:none;" onClick="ARMS_UPDATER.btn_confirm_svn_upload_clicked('php7');" id="btn_confirm_svn_php7_upload">SVN Commit (PHP7.1)</button>
	
	<div style="float:right;padding-right:20px;"><span id="span_file_selected_count">0</span> File(s)</div>
</div>

{include file="ARMS_UPDATER.footer.tpl"}