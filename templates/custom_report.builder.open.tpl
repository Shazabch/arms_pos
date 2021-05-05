{*
3/25/2020 1:29 PM William
- Enhanced page filter disable checkbox "Allow All" when no select "Yes".
- Enhanced to add remark for function double click can change label display name.

3/31/2020 11:36 AM William
- Enhanced to add edit and delete function for "Report Group".

6/16/2020 2:35 PM William
- Enhanced to added new setting and new filter "Age Group".

6/30/2020 4:12 PM William
- Enhanced to add new column GP and GP%.
- Enhanced to block sku type, arms code, mcode, sku description, artno, old code, department description, category description, vendor code, vendor description, brand code, brand description when has member point earn.
- Enhanced to block vendor, brand, sku type, category, sku search filter when has member point earn.

12/21/2020 9:31 AM William
- Enhanced to add new report settings "Disable Row Total", "Disable Row Merge" and "Disable Column Merge".
*}
{include file="header.tpl"}
<link rel="stylesheet" type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css">
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

<style>
{literal}
ul.ul_dragable, ul.ul_droplist, #ul_report_shared_userlist { margin:0; padding:0; list-style-type: none;}

ul.ul_dragable li, li.li_report_field, li.li_share_user { 
	margin: 1px; padding: 2px; 
	border:1px solid #999; 
	white-space: nowrap; 
	background-color:#fff;
	float:left; 
	min-width:150px; 
	max-width:150px;
	cursor:pointer;
	padding-right: 20px;
	line-height: 25px;
}

ul.ul_dragable li:hover, li.li_report_field:hover, li.li_share_user:hover { background-color:#cf9;}

.div_droplist{
	border:1px solid #999;
}

.drop-hover{
	border:1px solid green !important;
}
img.img_delete_report_field, img.img_delete_share_user{
	opacity: 0.4;
}
img.img_delete_report_field:hover, img.img_delete_share_user:hover{
	opacity: 1;
}

.ul_page_filter{
	list-style: none;
	margin: 0;
	padding: 0;
}

.ul_page_filter li{
	margin: 0;
	padding: 0;
}

.option_unavailable{
	color: gray;
}

.ui-autocomplete {
    max-height: 300px;  //for jQuery autocomplete ul max height
	max-width:	240px;
    overflow-y: auto;   
    overflow-x: hidden; 
    z-index:1000 !important;
}
#div_report_group_setting{
	border: 1px solid #ccc;
	border-radius: 5px;
	background: #fff;
	width: 350px;
	padding: 10px;
	position: absolute;
	z-index: 100000;
	max-height:600px;
	overflow-y: auto;
}
.report_group_edit_list{
	display: table;
	width: 98%;
	padding: 3px;
	border: 1px solid #ccc;
	border-radius: 5px;
	margin: 5px 0;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var can_edit = int('{$can_edit}');
var needCheckExit = true;
{literal}
var JQ= {};
JQ = jQuery.noConflict(true);

var REPORT_BUILDER = {
	f: undefined,
	selected_share_user_obj: undefined,
	initialize: function(){
		this.f = document.f_a;
		var THIS = this;
		
		if(can_edit){	// can edit
			JQ(this.f['report_shared']).on('change', function(){
				THIS.report_shared_builder_changed();
			});
			
			// share user autocomplete
			JQ("#inp_share_user_search").autocomplete({
				source: "custom_report.builder.php?a=ajax_search_user",
				minLength: 1,
				html: true,
				select: function( event, ui ) {
					
					if(ui.item.id){
						THIS.selected_share_user_obj = ui.item;	// store the selected user info
						
						JQ('#inp_share_user_search').val(ui.item.u);	// update username to the search box
					}
					return false;
				},
				focus: function( event, ui) {
					return false;
				},
				search: function( event, ui ) {
					// reset the user info
					THIS.selected_share_user_obj = undefined
				}
			});
			// add share user
			JQ('#inp_add_share_user').on('click', function(){
				THIS.add_share_user_clicked();
			});
			// event for share user to sortable
			JQ('#ul_report_shared_userlist').sortable({
				containment: "#ul_report_shared_userlist"
			});

			// delete share user			
			JQ('#ul_report_shared_userlist').on('click', "img.img_delete_share_user", function(){
				var user_info = THIS.get_share_user_info_by_ele(this);
				THIS.delete_share_user(user_info['user_id']);
			});
			
			// page filter - date changed
			JQ(this.f['page_filter[special][date]']).on('change', function(){
				THIS.check_page_filter_date();
			});
			
			this.initial_report_builder();	// initial report builder drag and drop
			
			// check before exit
		    window.onbeforeunload=confirmExit;
		}else{	// cannot edit
			THIS.form_disable(this.f);
		}
	},
	
	form_disable: function(form){  //to disable from input when user view
		if(!form)   return false;
		JQ(form).find('input, textarea, select').attr('disabled', true);
	},
	
	// function when user change report share
	report_shared_builder_changed: function(){
		if(this.f['report_shared'].value == 3){
			JQ('#div_report_shared').show();
		}else{
			JQ('#div_report_shared').hide();
		}
	},
	// function when user click add share user
	add_share_user_clicked: function(){
		if(!this.selected_share_user_obj){
			alert('Please search and select user first.');
			return false;
		}
		
		var user_id = this.selected_share_user_obj.id;
		var username = this.selected_share_user_obj.u;
		
		if(JQ('#li_share_user-'+user_id).length>0){
			alert('The user "'+username+'" already exists in the list.');
			return false;
		}
			
		// get temp li
		var li_share_user = JQ('#ul_temp_share_user li').clone();
		
		JQ(li_share_user).attr('id', 'li_share_user-'+user_id).attr('username', username);
		
		// change the innerhtml
		var new_html = JQ(li_share_user).html();
		new_html = new_html.replace(/__USER_ID__/g, user_id);
		new_html = new_html.replace(/__USER_LABEL__/g, username);
		
		JQ(li_share_user).html(new_html);
		
		// append into list
		JQ('#ul_report_shared_userlist').append(li_share_user);
		JQ('#inp_share_user_search').val("");
	},
	
	// function get share user info
	get_share_user_info_by_ele: function(ele){
		var parent_ele = JQ(ele).get(0);

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='li'){
                if(JQ(parent_ele).hasClass('li_share_user')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return false;

		var id_info = JQ(parent_ele).attr('id').split('-');
		
		var ret = {
			'user_id': id_info[1],
			'username': JQ(parent_ele).attr('username')
		}

		return ret;
	},
	
	// function when user click delete share user
	delete_share_user: function(user_id){
		JQ('#li_share_user-'+user_id).remove();
	},
	
	// function when user click close button
	btn_close_clicked: function(){
		if(can_edit){
			if(!confirm('Close without save?'))	return false;
		}
		
		needCheckExit = false;
		window.location = phpself;
	},
	// function to initial drag drop effect for report builder
	initial_report_builder: function(){
		var THIS = this;
		
		JQ('#ul_dragable_list li').draggable({
			revert: true,
			revertDuration: 0,
			containment: "#div_report_builder",
			start: function( event, ui ) {
				JQ(ui.helper).css('z-index', 9999);
			},
			stop: function( event, ui ) {
				JQ(ui.helper).css('z-index', 0);
			}
		});
		
		this.check_page_filter_member_point();
		
		var area_list = ['col', 'row', 'data'];
		for(var i=0; i<area_list.length; i++){
			var area = area_list[i];
			
			// event for column/row/data to droppab;e
			JQ('#div_droplist-'+area).droppable({
				activeClass: "ui-state-highlight",
				hoverClass: "drop-hover",
				accept: ".li_report_option_area-"+area,
				drop: function( event, ui ) {
					THIS.field_dropped(this, ui.draggable);
				}
			});
			
			// event for droplist to sortable
			JQ('#ul_droplist-'+area).sortable({
				containment: "#div_droplist-"+area
			});
			
			JQ('#ul_droplist-'+area+' li.li_report_field').each(function(){
				THIS.init_field_event(this);
			});
		}
	},
	// function to get max field num
	get_max_field_num: function(field_area){
		var max_field_num = 0;
		var THIS = this;
		
		JQ('#ul_droplist-'+field_area+' li.li_report_field').each(function(){
			var field_info = THIS.get_field_info_by_ele(this);
			if(field_info['field_num'] > max_field_num)	max_field_num = field_info['field_num'];
		});
		
		return max_field_num;
	},
	// function to handle when user drag a field and drop on the table
	field_dropped: function(div_target_area, li_source_ele){
		// get fleld type, label and area
		var field_type = JQ(li_source_ele).attr('id').split('-')[1];
		var field_label = JQ(li_source_ele).attr('label');
		var field_area = JQ(div_target_area).attr('id').split('-')[1];
		var field_formula = '';
		var block_filter_list = ['checkbox_vendor_filter', 'checkbox_brand_filter', 'checkbox_sku_type_filter'];
		
		if(field_area == 'data'){	// field data will have formula
			if(field_type == 'gp_percent'){
				field_formula = '';
			}else if(field_type == 'member_point_earn'){
				var filter_block_count = 0;
				for(var i=0; i<block_filter_list.length; i++){
					if(JQ('#'+block_filter_list[i]).prop("checked") == true){
						filter_block_count += 1;
					}
				}
				if(JQ('#radio_sku_category_filter:checked').val() != '') filter_block_count += 1;
				
				if(filter_block_count == 0){
					field_formula = JQ('#sel_data_formula').val();
					field_label = field_formula+'('+field_label+')';
				}else{
					alert("Not allow to use member point earn data when SKU/Category, Vendor, Brand, SKU Type filter is active.");
					return false;
				}
			}else{
				field_formula = JQ('#sel_data_formula').val();
				field_label = field_formula+'('+field_label+')';
			}
		}
		
		if(this.check_field_type_exists(field_area, field_type, field_formula)){
			alert('The field "'+field_label+'" already exists in the list.');
			return false;
		}
		
		var new_field_num = this.get_max_field_num(field_area)+1;
		
		// get temp li
		var li_report_field = JQ('#ul_tmp_report_field li').clone();
		
		JQ(li_report_field).attr('id', 'li_report_field-'+field_area+'-'+new_field_num);
		
		// change the innerhtml
		var new_html = JQ(li_report_field).html();
		new_html = new_html.replace(/__FIELD_AREA__/g, field_area);
		new_html = new_html.replace(/__FIELD_NUM__/g, new_field_num);
		new_html = new_html.replace(/__FIELD_TYPE__/g, field_type);
		new_html = new_html.replace(/__FIELD_FORMULA__/g, field_formula);
		new_html = new_html.replace(/__ORG_FIELD_LABEL__/g, field_label);
		new_html = new_html.replace(/__FIELD_LABEL__/g, field_label);
		
		JQ(li_report_field).html(new_html);
		
		// append into list
		JQ(div_target_area).find('ul.ul_droplist').append(li_report_field);
		
		// add event
		this.init_field_event(li_report_field);
		
		this.check_page_filter_member_point();
	},
	// provide function for each report field
	init_field_event: function(li){
		var THIS = this;
		var field_info = THIS.get_field_info_by_ele(li);
		
		// delete event
		JQ(li).find('img.img_delete_report_field').on('click', function(){
			THIS.delete_field_option(field_info['field_area'], field_info['field_num']);
		});
		
		// tooltip
		JQ(li).tooltip({
			show: {
				delay: 500
			}
		});
		
		// change label event
		if(field_info['field_area'] == 'data'){
			JQ(li).attr('title', 'Double click to change label.')
				.on('dblclick', function(){
				THIS.field_double_clicked(field_info['field_area'], field_info['field_num']);
			});
		}
	},
	// function to check whether the field type already in the area
	check_field_type_exists: function(field_area, field_type, field_formula){
		
		var li_report_field_list = JQ('#ul_droplist-'+field_area+' li.li_report_field')
		for(var i=0; i<li_report_field_list.length; i++){
			// get field info
			var field_info = this.get_field_info_by_ele(li_report_field_list[i]);
			
			if(field_info['field_type'] == field_type){	// same field type
				if(!field_formula)	return true;	// already duplicated
				
				if(field_info['field_formula'] == field_formula)	return true;	// formula also duplicate
			}
		}
				
		return false;
	},
	// function to get field info by element
	get_field_info_by_ele: function(ele){
		var parent_ele = JQ(ele).get(0);

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='li'){
                if(JQ(parent_ele).hasClass('li_report_field')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return false;

		var id_info = JQ(parent_ele).attr('id').split('-');
		
		var ret = {
			'field_area': id_info[1],
			'field_num': int(id_info[2]),
			'field_type': JQ(parent_ele).find('input.inp_field_type').val(),
			'field_formula': JQ(parent_ele).find('input.inp_field_formula').val(), 
			'org_field_label':	 JQ(parent_ele).find('input.inp_org_field_label').val(),
			'field_label':	JQ(parent_ele).find('input.inp_field_label').val()
		}

		return ret;
	},
	// function to delete field option
	delete_field_option: function(field_area, field_num){
		var li = JQ('#li_report_field-'+field_area+'-'+field_num);
		if(!li)	return false;
		var field_info = this.get_field_info_by_ele(li);
		
		JQ('#li_report_field-'+field_area+'-'+field_num).remove();
		this.check_page_filter_member_point();
	},
	// function to validate form before submit
	check_form: function(){
		// title
		if(!this.f['report_title'].value.trim()){
			alert('Please key in Report Title');
			this.f['report_title'].focus();
			return false;
		}
		
		// report col
		if(JQ('#ul_droplist-col li').length<=0){
			alert('Please drag at least 1 field to "Column"');
			//blink_element(JQ('#div_droplist-col'));
			return false;
		}
		
		// report row
		if(JQ('#ul_droplist-row li').length<=0){
			alert('Please drag at least 1 field to "Row"');
			//blink_element(JQ('#div_droplist-row'));
			return false;
		}
		
		// report data
		if(JQ('#ul_droplist-data li').length<=0){
			alert('Please drag at least 1 field to "Data"');
			//blink_element(JQ('#div_droplist-data'));
			return false;
		}
		
		// check page filter
		var page_filter_date = getRadioValue(this.f['page_filter[special][date]']);
		if(page_filter_date == 'ymd'){
			if(JQ('#span_page_filter_ymd input:checked').length<=0){
				alert('Please select at least Year, Month.');
				JQ('#span_page_filter_ymd input').get(0).focus();
				return false;
			}
		}
		
		if(!page_filter_date){
			// check whether got tick other page filter
			if(JQ('#div_page_filter input.chx_page_filter-normal:checked').length<=0){
				alert('Please select at least 1 Page Filter.');
				JQ('#div_page_filter input.chx_page_filter-normal').get(0).focus();
				return false;
			}
			
		}
		
		// check whether got invalid option
		if(JQ('#div_droptarget li.option_unavailable').length>0){
			alert('Some report fields cannot be use.');
			//$(this.f['page_filter[special][report_type]']).get(0).focus();
			return false;
		}
		
		return true;
	},
	// function when user click save
	btn_save_clicked: function(){
		if(!this.check_form())	return false;
		
		var tmp_params = {
			'a': 'ajax_save'
		};
		
		var params_str = JQ(this.f).serialize()+'&'+JQ.param(tmp_params);
		var THIS = this;
		
		JQ('#input_save').attr('disabled', true);
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		
		JQ.post(phpself, params_str, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok'] && ret['id']){ // got 'ok' return mean save success
                	needCheckExit = false;
                	var redirect_url = phpself+'?t=save&id='+ret['id'];
                	
					document.location = redirect_url;
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
			Element.hide('wait_popup');
			curtain(false,'curtain2');
			JQ('#input_save').attr('disabled', false);
			alert(err_msg);
		});
	},
	btn_delete_clicked: function(){
		if(!confirm('Are you sure?'))	return false;
		
		var THIS = this;
		
		var params = {
			'a': 'ajax_delete',
			'id': this.f['id'].value
		};
		
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		JQ.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success

                	//custom_alert.close();
                	
                	needCheckExit = false;
                	document.location = phpself+'?t=delete&id='+THIS.f['id'].value;
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
			curtain(false,'curtain2');
			Element.hide('wait_popup');
		    alert(err_msg);
		});
	},
	// function to check page filter date
	check_page_filter_date: function(){
		var v = getRadioValue(this.f['page_filter[special][date]']);
		
		if(v == 'ymd'){
			JQ('#span_page_filter_ymd input').attr('disabled', false);
		}else{
			JQ('#span_page_filter_ymd input').attr('disabled', true).prop('checked', false);
		}
	},
	// function when user change page filter ymd
	page_filter_ymd_changed: function(ele){		
		if(this.f['page_filter[special][ymd][month]'].checked){
			this.f['page_filter[special][ymd][year]'].checked = true;
		}
	},
	// check if got member_point_earn need block sku items column
	check_page_filter_member_point: function(){
		var THIS = this;
		var block_list = ['sku_type', 'sku_item_code', 'sku_item_mcode', 'sku_desc', 'sku_artno', 'sku_link_code', 'dept_desc', 'cat3_desc', 'vendor_desc', 'vendor_code', 'brand_description', 'brand_code'];
		var block_data_count = 0;
		var block_col_row_count = 0;
		var table_list = ['col', 'row', 'data'];
		
		for(var i=0; i<table_list.length; i++){
			var tbl_type = table_list[i];
			JQ('#ul_droplist-'+tbl_type+' li').each(function(){	// loop each row
				var field_info = THIS.get_field_info_by_ele(this);
				
				if(field_info['field_area'] == 'data' && field_info['field_type']=='member_point_earn'){
					block_data_count+=1;
				}else if(field_info['field_area'] == 'col' && block_list.includes(field_info['field_type'])){
					block_col_row_count+=1;
				}else if(field_info['field_area'] == 'row' && block_list.includes(field_info['field_type'])){
					block_col_row_count+=1;
				}
			});
		}
		
		if(block_data_count > 0){
			for(var i=0; i<block_list.length; i++){
				JQ('#li_report_option-'+block_list[i]).addClass('option_unavailable');
				JQ('#li_report_option-'+block_list[i]).removeClass('li_report_option_area-col');
				JQ('#li_report_option-'+block_list[i]).removeClass('li_report_option_area-row');
			}
		}else{
			for(var i=0; i<block_list.length; i++){
				JQ('#li_report_option-'+block_list[i]).removeClass('option_unavailable');
				JQ('#li_report_option-'+block_list[i]).addClass('li_report_option_area-col');
				JQ('#li_report_option-'+block_list[i]).addClass('li_report_option_area-row');
			}
		}
		
		if(block_col_row_count > 0){
			JQ('#li_report_option-member_point_earn').addClass('option_unavailable');
			JQ('#li_report_option-member_point_earn').removeClass('li_report_option_area-data');
		}else{
			JQ('#li_report_option-member_point_earn').removeClass('option_unavailable');
			JQ('#li_report_option-member_point_earn').addClass('li_report_option_area-data');
		}
		
	},
	// function when user click preview
	btn_preview_clicked: function(){
		if(!this.check_form())	return false;
		
		this.f['a'].value = 'preview';
		this.f.target = '_blank';
		this.f.submit();
		
		this.f['a'].value = '';
		this.f.target = '';	
	},
	// function when user double click on the report field
	field_double_clicked: function(field_area, field_num){
		if(field_area != 'data')	return;
		
		// get the li	
		var li = JQ('#li_report_field-'+field_area+'-'+field_num);
		// get the info
		var field_info = this.get_field_info_by_ele(li);
		
		var str = 'Change Label:';
		str += "\n\nData Formula: "+field_info['org_field_label']+"\n- Maximum 30 characters";
		var new_label = prompt(str, field_info['field_label']);	// ask for new label
		
		if(!new_label || !new_label.trim())	return;
		new_label = new_label.trim();
		if(new_label.length>30){
			alert('Maximum 30 characters.');
			return false;
		}
		
		JQ(li).find('input.inp_field_label').val(new_label);
		JQ(li).find('span.span_label').text(new_label);
	},
	// function when user click add new report group
	add_new_report_group_clicked: function(){
		var new_report_group = prompt('Report Group Name:');
		if(!new_report_group || !new_report_group.trim())	return false;
		
		new_report_group = new_report_group.trim();
		
		// check if the value exists
		for(var i=1; i < this.f['report_group'].options.length; i++){
			var v = this.f['report_group'].options[i].value;
			
			if(v == new_report_group){	// found
				this.f['report_group'].selectedIndex = i;
				return false;
			}
		}
		
		// need to create new group
		var opt = JQ("<option>").attr('value', new_report_group).text(new_report_group).appendTo(this.f['report_group']).prop('selected', true);
	},
	//
	edit_report_group_clicked: function(){
		var params = {
			'a': 'load_report_group'
		}
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_report_group_setting').update(ret['html']);
						center_div('div_report_group_setting');
						curtain(true);
						Element.show('div_report_group_setting');
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}						
					}
				}catch(ex){
					alert(str);
				}
			}
		});
	},
	//update report group
	update_report_group_clicked: function(){
		//check the empty and duplicate report group value.
		var duplicate_count = {};
		var report_group_setting_val_list = document.f_r['report_group_setting_val_list[]'];
		if(typeof(report_group_setting_val_list) !='undefined'){
			for(var i=0; i< report_group_setting_val_list.length;i++){
				var report_group_val = report_group_setting_val_list[i].value;
				if(!duplicate_count[report_group_val]) duplicate_count[report_group_val] = 0;
				if(report_group_val == ''){
					alert('Please fill in the Report Group field.');
					return false;
				}else{
					duplicate_count[report_group_val] += 1;
					if(duplicate_count[report_group_val] > 1){
						alert('Report Group "'+report_group_val+'" already exists.');
						return false;
					}
				}
			}
		}
		
		var tmp_params = {
			'a': 'ajax_update_report_group',
		};
		var params_str = JQ(document.f_r).serialize()+'&'+JQ.param(tmp_params);
		
		JQ('#btn_save').attr('disabled', true);
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		JQ.post(phpself, params_str, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					for(var old_val in ret['report_group']){
						var report_group_option = JQ("#report_group option[value='"+old_val+"']");
						
						//change the value and text of report group
						if(ret['report_group'][old_val] != ''){
							report_group_option.val(ret['report_group'][old_val]);
							report_group_option.text(ret['report_group'][old_val]);
						}else{
							if(report_group_option.text() != '-- No Group --'){
								report_group_option.remove();
							}
						}
					}
					alert("Update Success");
					curtain(false);
					Element.hide('div_report_group_setting');
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
		    // prompt the error
			Element.hide('wait_popup');
			curtain(false,'curtain2');
			JQ('#btn_save').attr('disabled', false);
			if(err_msg) alert(err_msg);
		});
	},
	//delete report group
	delete_report_group_clicked: function(obj){
		obj.remove();
	},
	// disable allow_all checkbox when page filter not select yes
	page_filter_check: function(obj){
		var page_filter_name = obj.name;
		var checkbox_allow_all_name = page_filter_name.replace("active","allow_all");

		this.member_point_earn_page_filter_check(obj);
		if(obj.checked == true){
			this.f[checkbox_allow_all_name].disabled = false;
		}else{
			this.f[checkbox_allow_all_name].disabled = true;
		}
	},
	member_point_earn_page_filter_check: function(obj){
		var THIS = this;
		var block_filter_list = ['checkbox_vendor_filter', 'checkbox_brand_filter', 'checkbox_sku_type_filter'];
		var checkbox_filter_id = obj.getAttribute('id');
		var obj_type = obj.type;
		var drop_data_list = JQ('#ul_droplist-data li');
		var filter_block_count = 0;
		
		drop_data_list.each(function(){	// loop each row
			var field_info = THIS.get_field_info_by_ele(this);
			if(field_info['field_type']=='member_point_earn'){
				if(obj_type == 'checkbox' && block_filter_list.includes(checkbox_filter_id)){
					filter_block_count += 1;
					obj.checked = false;
				}else if(obj_type == 'radio' && obj.value != ''){
					filter_block_count += 1;
					JQ('#radio_sku_category_filter[value=""]').attr('checked', 'checked');
				}
			}
		});
		if(filter_block_count > 0)  alert('Not allow to use SKU/Category/Vendor/Brand/SKU Type filter when got member point earn data.');
	}
};

confirmExit = function(e) {
	if(!e) e = window.event;
	if(needCheckExit){
		return 'Data had not being saved.';
	}
};

function curtain_clicked(){
	curtain(false);
	Element.hide('div_report_group_setting');
	
}
function show_formula_help(){
	alert(' Sum (SUM)\n Total sum of selected Field\n \n Count (COUNT)\n Return the total transaction count that matches the row/column criteria. The count result is always the same regarding the field you choose, when the columns and rows are not changed.');
}

{/literal}
</script>

<div id="wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
<p align="center">
	Please wait..
	<br /><br />
	<img src="ui/clock.gif" border="0" />
</p>
</div>


{* Sample Element - Use to clone *}
<div  style="display:none;">
	{* Report Field *}
	<ul id="ul_tmp_report_field">
		{include file="custom_report.builder.open.report_field.tpl"}
	</ul>
	<ul id="ul_temp_share_user">
		{* Share User *}
		{include file="custom_report.builder.open.share_user.tpl"}
	</ul>
	
</div>


<h1>{$PAGE_TITLE} - {if $form.id}ID#{$form.id}{else}New{/if}</h1>

<div id="div_report_group_setting" style="display:none;">
{include file="custom_report.report_group_settings.tpl"}
</div>

<form name="f_a" onSubmit="return false;" method="post">
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="a" />
	
	<div class="stdframe">
		<h3>Settings</h3>
	
		<table>
			{* Report Title *}
			<tr>
				<td width="200"><b>Report Title</b></td>
				<td><input type="text" name="report_title" value="{$form.report_title}" style="width:400px;" maxlength="100" /></td>
			</tr>
			
			{* Owner *}
			{if $form.id > 0 and $sessioninfo.id ne $form.user_id}
				<tr>
					<td><b>Owner</b></td>
					<td>{$form.username}</td>
				</tr>
			{/if}
			
			{* Shared *}
			<tr valign="top">
				<td><b>Share Report Builder</b></td>
				<td>
					<input type="radio" name="report_shared" value="0" {if !$form.id or !$form.report_shared}checked {/if} /> No &nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="report_shared" value="1" {if $form.report_shared eq 1}checked {/if} /> All can view &nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="report_shared" value="2" {if $form.report_shared eq 2}checked {/if} /> All can edit &nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="report_shared" value="3" {if $form.report_shared eq 3}checked {/if} /> white List &nbsp;&nbsp;&nbsp;&nbsp;
					
					<div id="div_report_shared" style="{if $form.report_shared neq 3}display:none;{/if}">
						{if $can_edit}
							Search User: 
							<input id="inp_share_user_search" size="30" />
							<input id="inp_add_share_user" type="button" value="Add" />
						{/if}
						
						<div id="div_report_shared_userlist" style="border:1px solid black;height:100px;overflow:auto;background-color:#fff;">
							<ul id="ul_report_shared_userlist" style="height:100px;">
								{foreach from=$form.report_shared_additional_control_user key=user_id item=share_user}
									{include file="custom_report.builder.open.share_user.tpl" user_id=$user_id username=$user_list.$user_id.u user_label=$user_list.$user_id.u user_control_type=$share_user.control_type}
								{/foreach}
							</ul>
						</div>
					</div>
				</td>
			</tr>
			
			{* Report Group *}
			<tr>
				<td><b>Report Group</b> (<a href="javascript:void(alert('Only the Sales Report, Performance Report, Membership Report and SKU Report has check own report privilege.'))">?</a>)</td>
				<td>
					<select id="report_group" name="report_group">
						<option value="">-- No Group --</option>
						{foreach from=$report_group_list item=rg}
							<option value="{$rg|htmlentities}" {if $rg eq $form.report_group}selected {/if}>{$rg|htmlentities}</option>
						{/foreach}
					</select>
					{if $can_edit}
						<span class="link" onClick="REPORT_BUILDER.add_new_report_group_clicked();">
							Add New Group
						</span>/
						<span class="link" onClick="REPORT_BUILDER.edit_report_group_clicked();">
							Edit Report Group
						</span>
					{/if}
				</td>
			</tr>
			
			{* Report Settings *}
			<tr>
				<td><b>Disable Row Total</b></td>
				<td><input name="report_settings[disable_row_total]" {if $form.report_settings.disable_row_total}checked {/if} value="1" type="checkbox" /></td>
			</tr>
			<tr>
				<td><b>Disable Row Merge</b></td>
				<td><input name="report_settings[disable_row_merge]" {if $form.report_settings.disable_row_merge}checked {/if} value="1" type="checkbox" /></td>
			</tr>
			<tr>
				<td><b>Disable Column Merge</b></td>
				<td><input name="report_settings[disable_column_merge]" {if $form.report_settings.disable_column_merge}checked {/if} value="1" type="checkbox" /></td>
			</tr>
		</table>
	</div>
	
	<br />
	
	{* Page Filter *}
	<div class="stdframe" style="background-color:#fff;" id="div_page_filter">
		<h3>Page Filter</h3>
		
		<table width="100%">
			{* Date *}
			<tr valign="top">
				<td width="200"><b>Date</b></td>
				<td>
					<ul class="ul_page_filter">
						<li>
							<input type="radio" name="page_filter[special][date]" value="single_date" {if $form.page_filter.special.date eq 'single_date' or !$form.page_filter.special.date}checked {/if} /> Single Date Selection
						</li>
						<li>
							<input type="radio" name="page_filter[special][date]" value="date_range" {if $form.page_filter.special.date eq 'date_range'}checked {/if} /> Date Range (From/To)
						</li>
						<li>
							<input type="radio" name="page_filter[special][date]" value="ymd" {if $form.page_filter.special.date eq 'ymd'}checked {/if} /> Selection by (
							
							<span id="span_page_filter_ymd">
								<input type="checkbox" name="page_filter[special][ymd][year]" value="1" {if $form.page_filter.special.ymd.year}checked {/if} {if $form.page_filter.special.date ne 'ymd'}disabled {/if} onChange="REPORT_BUILDER.page_filter_ymd_changed(this);" /> Year &nbsp;&nbsp;&nbsp;
								<input type="checkbox" name="page_filter[special][ymd][month]" value="1" {if $form.page_filter.special.ymd.month}checked {/if} {if $form.page_filter.special.date ne 'ymd'}disabled {/if} onChange="REPORT_BUILDER.page_filter_ymd_changed(this);" /> Month &nbsp;&nbsp;&nbsp;
								)
							</span>							
						</li>
					</ul>
					
				</td>
			</tr>
			<tr valign="top">
				<td><b>Select SKU / Category</b></td>
				<td>
					<ul class="ul_page_filter">
						<li>
							<input type="radio" id="radio_sku_category_filter" onclick="REPORT_BUILDER.member_point_earn_page_filter_check(this)" name="page_filter[special][filter_type]" value="" {if !$form.page_filter.special.filter_type}checked {/if} /> None
						</li>
						<li>
							<input type="radio" id="radio_sku_category_filter" onclick="REPORT_BUILDER.member_point_earn_page_filter_check(this)" name="page_filter[special][filter_type]" value="sku" {if $form.page_filter.special.filter_type eq 'sku'}checked {/if} /> SKU
						</li>
						<li>
							<input type="radio" id="radio_sku_category_filter" onclick="REPORT_BUILDER.member_point_earn_page_filter_check(this)" name="page_filter[special][filter_type]" value="category" {if $form.page_filter.special.filter_type eq 'category'}checked {/if} /> Category
						</li>
					</ul>
				</td>
			</tr>
			
			<tr>
				<td><b>Select Branch</b></td>
				<td>
					<input id="checkbox_branch_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][branch][active]" value="1" {if $form.page_filter.normal.branch.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][branch][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.branch.allow_all}checked {/if} {if !$form.page_filter.normal.branch.active}disabled {/if}  /> Allow All )
				</td>
			</tr>
			
			<tr>
				<td><b>Select Vendor</b></td>
				<td>
					<input id="checkbox_vendor_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][vendor][active]" value="1" {if $form.page_filter.normal.vendor.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][vendor][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.vendor.allow_all}checked {/if} {if !$form.page_filter.normal.vendor.active}disabled {/if} /> Allow All )
				</td>
			</tr>
			
			<tr>
				<td><b>Select Brand</b></td>
				<td>
					<input id="checkbox_brand_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][brand][active]" value="1" {if $form.page_filter.normal.brand.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][brand][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.brand.allow_all}checked {/if} {if !$form.page_filter.normal.brand.active}disabled {/if} /> Allow All )
				</td>
			</tr>
			
			<tr>
				<td><b>Select SKU Type</b></td>
				<td>
					<input id="checkbox_sku_type_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][sku_type][active]" value="1" {if $form.page_filter.normal.sku_type.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][sku_type][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.sku_type.allow_all}checked {/if} {if !$form.page_filter.normal.sku_type.active}disabled {/if} /> Allow All )
				</td>
			</tr>
			
			
			<tr>
				<td><b>Select Race</b></td>
				<td>
					<input id="checkbox_race_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][race][active]" value="1" {if $form.page_filter.normal.race.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][race][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.race.allow_all}checked {/if} {if !$form.page_filter.normal.race.active}disabled {/if} /> Allow All )
				</td>
			</tr>
			
			<tr>
				<td><b>Select Member / Non-Member</b></td>
				<td>
					<input id="checkbox_member_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][member][active]" value="1" {if $form.page_filter.normal.member.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][member][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.member.allow_all}checked {/if} {if !$form.page_filter.normal.member.active}disabled {/if} /> Allow All )
				</td>
			</tr>
			<tr>
				<td><b>Age Group</b> (<a href="javascript:void(alert('Only available when the age group settings has set the age range.'))">?</a>)</td>
				<td>
					<input id="checkbox_age_group_filter" type="checkbox" onchange="REPORT_BUILDER.page_filter_check(this)" class="chx_page_filter-normal" name="page_filter[normal][age_group][active]" value="1" {if !$form.age_group_enable}disabled {/if} {if $form.page_filter.normal.age_group.active}checked {/if} />Yes &nbsp;&nbsp;
					( <input type="checkbox" name="page_filter[normal][age_group][allow_all]" class="chx_page_filter-normal" value="1" {if $form.page_filter.normal.age_group.allow_all}checked {/if} {if !$form.page_filter.normal.age_group.active || !$form.age_group_enable}disabled {/if} /> Allow All )
				</td>
			</tr>
		</table>
	</div>
	
	<br />
	
	{* Report Data *}
	<div id="div_report_builder" class="stdframe" style="background-color:#fff;">
		<h3>Report Table</h3>
		
		<table border="0" cellpadding="0" cellspacing="5">
			<tr>
				<td valign="top">
					<div id="div_droptarget">
						<table cellspacing="4" cellpadding="0">
							<tr>
								<td style="border:0px solid #999;">&nbsp;</td>
								
								{* Column *}
								<td>
									<b>Column</b>
									<div id="div_droplist-col" class="div_droplist" style="width:360px; height:100px;">
										<ul id="ul_droplist-col" class="ul_droplist" style="height:100px;overflow:auto;">
											{foreach from=$form.report_fields.col item=col_info name=fcol}
												{include file="custom_report.builder.open.report_field.tpl" field_area='col' field_type=$col_info.field_type org_field_label=$report_fields_list[$col_info.field_type].label field_label=$report_fields_list[$col_info.field_type].label field_num=$smarty.foreach.fcol.iteration field_formula=""}
											{/foreach}
										</ul>
									</div>
								</td>
							</tr>
							
							<tr>
								{* Row *}
								<td>
									<b>Row</b>
								    <div id="div_droplist-row" class="div_droplist" style="width:180px; height:350px;">
										<ul id="ul_droplist-row" class="ul_droplist" style="height:350px;overflow:auto;">
											{foreach from=$form.report_fields.row item=row_info name=frow}
												{include file="custom_report.builder.open.report_field.tpl" field_area='row' field_type=$row_info.field_type org_field_label=$report_fields_list[$row_info.field_type].label field_label=$report_fields_list[$row_info.field_type].label field_num=$smarty.foreach.frow.iteration field_formula=""}
											{/foreach}
										</ul>
									</div>
								</td>
								
								{* Data *}
								<td>
									<b>Data</b> (<a href="javascript:void(alert('Double Click the data field to change label display name.'))">?</a>)
								    <div id="div_droplist-data" class="div_droplist" style="width:360px; height:350px;">
								  		<ul id="ul_droplist-data" class="ul_droplist" style="height:350px;overflow:auto;">
								  			{foreach from=$form.report_fields.data item=data_info name=fdata}
								  				{assign var=org_field_label value="`$data_info.field_formula`(`$report_fields_list[$data_info.field_type].label`)"}
												{include file="custom_report.builder.open.report_field.tpl" field_area='data' field_type=$data_info.field_type org_field_label=$org_field_label field_label=$data_info.field_label|default:$org_field_label field_num=$smarty.foreach.fdata.iteration field_formula=$data_info.field_formula}
											{/foreach}
										</ul>
									</div>
								</td>
							</tr>
						</table>
					</div>
					
					{if $can_edit}
						<input type="button" value="Preview" onClick="REPORT_BUILDER.btn_preview_clicked();" style="font:bold 20px Arial; background-color:#00CED1; color:#fff;">
					{/if}
				</td>
				
				{* Field Option List *}
				<td valign="top">						
					<h4>Fields</h4>
					Drag a field into the table on your left<br>
					
					<ul class="ul_dragable" id="ul_dragable_list" style="padding:2px;">
						{foreach from=$report_fields_list key=k item=r}
							<li id="li_report_option-{$k}" class="li_report_option {if $r.accept_area.col && !$r.disabled}li_report_option_area-col{/if} {if $r.accept_area.row && !$r.disabled}li_report_option_area-row{/if} {if $r.accept_area.data && !$r.disabled}li_report_option_area-data{/if} {if $r.disabled}option_unavailable{/if}" label="{$r.label}">{$r.label} {if $r.description}[<a href='javascript:void(alert("{$r.description}"))'>?</a>]{/if}</li>
						{/foreach}
					</ul>
					
					<br style="clear:both">
					<p align="center">
						Data Formula: 
						<select id="sel_data_formula">
							<option value="SUM">Sum</option>
							<option value="COUNT">Count</option>
						</select> 
						(<a href="javascript:void(show_formula_help())">Help</a>)
					</p>
				</td>
			</tr>
		</table>
	</div>
</form>

<p align="center">
	{if $can_edit}
		<input type="button" id="input_save" value="Save" onClick="REPORT_BUILDER.btn_save_clicked();" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
		
		{if $form.id}
			<input type="button" value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="REPORT_BUILDER.btn_delete_clicked()">
		{/if}
	{/if}
	<input type="button" onClick="REPORT_BUILDER.btn_close_clicked();" value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;">
</p>

<script type="text/javascript">
{literal}
	REPORT_BUILDER.initialize();
{/literal}
</script>
{include file="footer.tpl"}