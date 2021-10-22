{*
4/5/2017 11:57 AM Qiu Ying
- Change information message when click on close button in View mode

4/6/2017 9:47 AM Qiu Ying
- Bug fixed on moving the jQuery CSS box, it does not move to the position as expected. 
- Bug fixed on disable form input not function well when click on preview button

7/28/2017 11:12 Qiu Ying
- Enhanced to load pre-list templates

8/10/2017 09:51 AM Qiu Ying
- Bug fixed on input field value shown in an abnormal way when containing special characters

2017-08-23 11:00 AM Qiu Ying
- Enhanced to add auto count as prelist template

11/2/2017 5:24 PM Andy
- Revert to hide Row Format "Master No Repeat".
- Inactive Auto Count Preset Cash Sales Format.

06/25/2020 10:44 AM Sheila
- Updated button css
*}
{include file=header.tpl}

{literal}
<style>
ul.ul_dragable, ul.ul_droplist, #ul_report_shared_userlist { margin:0; padding:0; list-style-type: none;}

ul.ul_dragable li, li.li_report_field, li.li_share_user { 
	margin: 1px; padding: 2px; 
	border:1px solid #999; 
	white-space: nowrap; 
	background-color:#fff;
	float:left; 
	min-width:180px; 
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
	color: red;
	background-color: yellow !important;
	border-color: red !important;
}

#ul_droplist-header li{
	position:relative;
	margin: 1px;
	padding: 2px;
	white-space: nowrap;
	
	float:left;
	min-width:140px;
	cursor:pointer;
	padding-right: 20px;
	line-height: 25px;
	
}
.div_droplist{
	width:100%;
	height:200px;
}

.ul_droplist{
	height:200px;
	overflow:auto;
}
.validateTips { color:red }
fieldset { padding:0; border:0; margin-top:25px; }
</style>
{/literal}
<link type="text/css" href="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var view_only = int('{$view_only}');
{literal}
var JQ = {};
JQ = jQuery.noConflict(true);
var tmp;
var SETUP_CUSTOM_ACC_EXPORT_OPEN = {
	f_a: undefined,
	initialize: function(){
		this.f_a = document.f_a;
		var THIS = this;
		JQ("#dialog").dialog({
			autoOpen: false,
			resizable: false,
			modal : true,
			show: {
				effect: "blind",
				duration: 100
			},
			hide: {
				effect: "blind",
				duration: 100
			}
		});
		
		JQ("#divPreview").dialog({
			autoOpen: false,
			modal : true,
			show: {
				effect: "blind",
				duration: 100
			},
			hide: {
				effect: "blind",
				duration: 100
			},
			resizable: false,
			maxWidth:1000,
			width: 1000	
		});
		
		JQ("#dialog_form").dialog({
			autoOpen: false,
			height: "auto",
			width: "auto",
			modal: true,
			resizable: false,
			buttons: {
				"Save": THIS.edit_field,
				"Close": function() {
					  JQ("#dialog_form").dialog("close");
				}
			},
			close: function() {
				JQ("#dialog_form").find("form")[0].reset();
			}
		});
		JQ("#dialog_form").find("form").on("submit", function(event) {
			event.preventDefault();
			THIS.edit_field();
		});
		THIS.load_data_field();
		if(view_only){
			if (this.check_form()) {
				tmp = JQ(this.f_a).serialize();
				JQ(this.f_a).find('input, textarea, select').attr('disabled', true);
			}
		}
	},
	edit_field: function(){
		var valid = true;
		var field_area = JQ("#tmp_field_area").val();
		var field_num = JQ("#tmp_field_num").val();
		var field_label_type = JQ("#tmp_field_label_type").val();
		var li = JQ('#li_report_field-'+field_area+'-'+field_num);
		var new_label = JQ("#dialog_name").val();
		
		if (field_label_type == 'open_field'){
			if (new_label == ''){
				JQ(".validateTips").text("Title Cannot Empty");
				valid = false;
			}
			if(new_label.length > 25){
				JQ(".validateTips").text("Title Cannot Exceed 25 Characters");
				valid = false;
			}
			
			var new_value = JQ("#dialog_value").val();			
			if(new_value.length > 40){
				JQ(".validateTips").text("Value Cannot Exceed 40 Characters");
				valid = false;
			}
			
			var new_remark = JQ("#dialog_remark").val();			
			if(new_remark.length > 200){
				JQ(".validateTips").text("Remark Cannot Exceed 200 Characters");
				valid = false;
			}
		}else if (field_label_type == 'cancel'){
			var new_cancel = JQ("#dialog_cancel").val();
			if (new_cancel == ''){
				JQ(".validateTips").text("Cancelled Cannot Empty");
				valid = false;
			}
			
			if(new_cancel.length > 25){
				JQ(".validateTips").text("Cancelled Cannot Exceed 25 Characters");
				valid = false;
			}
			
			var new_active = JQ("#dialog_active").val();
			if (new_active == ''){
				JQ(".validateTips").text("Active Cannot Empty");
				valid = false;
			}
			
			if(new_active.length > 25){
				JQ(".validateTips").text("Active Cannot Exceed 25 Characters");
				valid = false;
			}
		}else if (field_label_type == 'inv_seq_num' || field_label_type == 'seq_num'){
			var new_value = JQ("#dialog_value").val();
			if (new_value == ''){
				JQ(".validateTips").text("Leading Zero Cannot Empty");
				valid = false;
			}
			
			if (isNaN(new_value)){
				JQ(".validateTips").text("Only Accept Numeric Characters");
				valid = false;
			}
			
			if(new_value < 0 ||  new_value > 5){
				JQ(".validateTips").text("Leading Zero must between 0 and 5");
				valid = false;
			}
		}
		
		if (valid){
			if (field_label_type == 'cancel'){
				JQ(li).find('input.inp_field_cancel').val(JQ("#dialog_cancel").val());
				JQ(li).find('input.inp_field_active').val(JQ("#dialog_active").val());
			}else if (field_label_type == 'open_field' || field_label_type == 'inv_seq_num' || field_label_type == 'seq_num'){
				JQ(li).find('input.inp_field_value').val(JQ("#dialog_value").val());
				
				if (field_label_type == 'open_field'){
					JQ(li).find('input.inp_field_desc').val(JQ("#dialog_remark").val());
				}
			}
			
			JQ(li).find('input.inp_field_label').val(new_label);
			JQ(li).find('span.span_label').text(new_label);
			JQ("#dialog_form").dialog("close");
		}
		
		return true;
	},
	
	is_other: function(){
		var is_checked = this.f_a['is_other'].checked;
		if (is_checked){
			this.f_a["delimiter"].disabled = true;
			this.f_a["other_delimiter"].disabled = false;
			JQ("#delimiter")[0].selectedIndex = 0;
		}else{
			this.f_a["delimiter"].disabled = false;
			this.f_a["other_delimiter"].disabled = true;
			this.f_a["other_delimiter"].value = "";
		}
	},

	add_header_column: function(){
		var newli = document.createElement('li');
		newli.style.cssText = "position:relative;margin: 1px; padding: 2px; white-space: nowrap; background-color:#fff;float:left; min-width:140px; cursor:pointer;padding-right: 20px;line-height: 25px;border:1px solid #999";
        newli.innerHTML = "<input type='text' name='header[]' style='border:0' maxlength='100' class='inp_header' /><img style='top:20%;right:0;position:absolute;' src='ui/icons/cancel.png' class='img_delete_report_field' title='Delete' name='delete_header[]' onclick='SETUP_CUSTOM_ACC_EXPORT_OPEN.delete_header_column(this);' />";
		document.getElementById("ul_droplist-header").appendChild(newli);
		JQ(newli).find('input.inp_header').focus();		
	},
	
	delete_header_column: function(e){
		document.getElementById("ul_droplist-header").removeChild(e.parentNode);
	},
	
	field_dropped: function(div_target_area, li_source_ele){
		var field_type = JQ(li_source_ele).attr('id').split('-')[1];
		var field_label_type = JQ(li_source_ele).attr('field_label_type');
		var field_desc = JQ(li_source_ele).attr('field_desc');
		var field_label = JQ(li_source_ele).attr('label');
		var field_area = JQ(div_target_area).attr('id').split('-')[1];
		var field_active = JQ(li_source_ele).attr('field_active');
		var field_cancel = JQ(li_source_ele).attr('field_cancel');
		var field_value = JQ(li_source_ele).attr('field_value');
		var new_field_num = this.get_max_field_num(field_area)+1;
		
		var li_report_field = JQ('#ul_tmp_report_field li').clone();
		
		JQ(li_report_field).attr('id', 'li_report_field-'+field_area+'-'+new_field_num);
		var new_html = JQ(li_report_field).html();
		new_html = new_html.replace(/__FIELD_AREA__/g, field_area);
		new_html = new_html.replace(/__FIELD_NUM__/g, new_field_num);
		new_html = new_html.replace(/__FIELD_TYPE__/g, field_type);
		new_html = new_html.replace(/__ORG_FIELD_LABEL__/g, field_label);
		new_html = new_html.replace(/__FIELD_LABEL__/g, field_label);
		new_html = new_html.replace(/__FIELD_LABEL_TYPE__/g, field_label_type);
		new_html = new_html.replace(/__FIELD_DESC__/g, field_desc);
		new_html = new_html.replace(/__FIELD_ACTIVE__/g, field_active);
		new_html = new_html.replace(/__FIELD_CANCEL__/g, field_cancel);
		new_html = new_html.replace(/__FIELD_VALUE__/g, field_value);
		
		JQ(li_report_field).html(new_html);
		JQ(div_target_area).find('ul.ul_droplist').append(li_report_field);
		this.init_field_event(li_report_field);
	},
	
	get_max_field_num: function(field_area){
		var max_field_num = 0;
		var THIS = this;
		JQ('#ul_droplist-'+field_area+' li.li_report_field').each(function(){
			var field_info = THIS.get_field_info_by_ele(this);
			if(field_info['field_num'] > max_field_num)	max_field_num = field_info['field_num'];
		});
		
		return max_field_num;
	},
	get_field_info_by_ele: function(ele){
		var parent_ele = JQ(ele).get(0);

		while(parent_ele){    // loop parent until it found the div contain group id
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
			'org_field_label':	 JQ(parent_ele).find('input.inp_org_field_label').val(),
			'field_label':	JQ(parent_ele).find('input.inp_field_label').val(),
			'field_label_type':	JQ(parent_ele).find('input.inp_field_label_type').val(),
			'field_desc':	JQ(parent_ele).find('input.inp_field_desc').val(),
			'field_value':	JQ(parent_ele).find('input.inp_field_value').val(),
			'field_active':	JQ(parent_ele).find('input.inp_field_active').val(),
			'field_cancel':	JQ(parent_ele).find('input.inp_field_cancel').val()			
		}
		return ret;
	},
	
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
		var str = '';
		if(view_only){
			str = 'Double click to view field.';
		}else{
			str = 'Double click to set field.';
		}
		JQ(li).attr('title', str)
			.on('dblclick', function(){
			THIS.field_double_clicked(field_info['field_area'], field_info['field_num']);
		});
	},
	field_double_clicked: function(field_area, field_num){
		// get the li	
		var li = JQ('#li_report_field-'+field_area+'-'+field_num);
		// get the info
		var field_info = this.get_field_info_by_ele(li);
		if (field_info['field_label_type'] == 'view' || view_only){
			JQ('.ui-dialog-buttonpane button:contains("Save")').button().hide();
		}else{
			JQ('.ui-dialog-buttonpane button:contains("Save")').button().show();
		}
		
		JQ("#dialog_form").dialog("option", "title", field_info['org_field_label']);
		JQ("#dialog_name").val(field_info['field_label']);
		JQ(".validateTips").text("");
		JQ("#tmp_field_area").val(field_area);
		JQ("#tmp_field_num").val(field_num);
		JQ("#tmp_field_label_type").val(field_info['field_label_type']);
		var tmp_value;
		if (field_info['field_value'] == '__FIELD_VALUE__' && (field_info['field_type'] == "seq_num" || field_info['field_label_type'] == "inv_seq_num")){
			tmp_value = "0";
		}else if(field_info['field_type'] == "open_field" && (field_info['field_value'] == '__FIELD_VALUE__' || field_info['field_value'] == 'zero')){
			if (field_info['field_value'] == '__FIELD_VALUE__'){
				tmp_value = "";
			}else if (field_info['field_value'] == 'zero'){
				tmp_value = "0";
			}
		}else{
			tmp_value = field_info['field_value'];
		}
		JQ("#dialog_value").val(tmp_value);
		
		var tmp_desc;
		if(field_info['field_type'] == "open_field" && (field_info['field_desc'] == '__FIELD_DESC__' || field_info['field_desc'] == 'zero')){
			if (field_info['field_desc'] == '__FIELD_VALUE__'){
				tmp_desc = "";
			}else if (field_info['field_desc'] == 'zero'){
				tmp_desc = "0";
			}
		}else{
			tmp_desc = field_info['field_desc'];
		}
		JQ("#dialog_remark").val(tmp_desc);
		
		var tmp_active;
		if (field_info['field_active'] == '__FIELD_ACTIVE__'){
			tmp_active = "0";
		}else{
			tmp_active = field_info['field_active'];
		}
		JQ("#dialog_active").val(tmp_active);
		JQ("#span_active").text(" * Default is " + tmp_active);
		var tmp_cancel;
		if (field_info['field_cancel'] == '__FIELD_CANCEL__'){
			tmp_cancel = "0";
		}else{
			tmp_cancel = field_info['field_cancel'];
		}
		JQ("#dialog_cancel").val(tmp_cancel);
		JQ("#span_cancel").text(" * Default is " + tmp_cancel);
		
		if (field_info['field_label_type'] == 'open_field'){
			JQ(".class_open_field").show();
			JQ(".class_cancel").hide();
			JQ(".class_seq_num").hide();
			JQ(".class_inv_seq_num").hide();
			if(view_only){
				JQ("#dialog_name").prop( "disabled", true);
				JQ("#dialog_remark").prop( "disabled", true);
				JQ("#dialog_value").prop( "disabled", true);
			}else{
				JQ("#dialog_name").prop( "disabled", false);
				JQ("#dialog_remark").prop( "disabled", false);
				JQ("#dialog_value").prop( "disabled", false);
			}
			
			JQ("#dialog_field_type").text("Value");
		}else if (field_info['field_label_type'] == 'view'){
			JQ(".class_open_field").hide();
			JQ(".class_cancel").hide();
			JQ(".class_seq_num").hide();
			JQ(".class_inv_seq_num").hide();
			JQ("#dialog_name").prop( "disabled", true );
			JQ("#dialog_remark").prop( "disabled", true );
			JQ("#btnSave").hide();
		}else if (field_info['field_label_type'] == 'seq_num'){
			JQ(".class_open_field").show();
			JQ(".class_cancel").hide();
			JQ(".class_seq_num").show();
			JQ(".class_inv_seq_num").hide();
			JQ("#dialog_name").prop( "disabled", true );
			JQ("#dialog_remark").prop( "disabled", true );
			if(view_only){
				JQ("#dialog_value").prop( "disabled", true);
			}else{
				JQ("#dialog_value").prop( "disabled", false);
			}
			JQ("#dialog_field_type").text("Leading Zero");
		}else if (field_info['field_label_type'] == 'inv_seq_num'){
			JQ(".class_open_field").show();
			JQ(".class_cancel").hide();
			JQ(".class_seq_num").hide();
			JQ(".class_inv_seq_num").show();
			JQ("#dialog_name").prop( "disabled", true );
			JQ("#dialog_remark").prop( "disabled", true );
			if(view_only){
				JQ("#dialog_value").prop( "disabled", true);
			}else{
				JQ("#dialog_value").prop( "disabled", false);
			}
			JQ("#dialog_field_type").text("Leading Zero");
		}else if (field_info['field_label_type'] == 'cancel'){
			JQ(".class_open_field").hide();
			JQ(".class_cancel").show();
			JQ(".class_seq_num").hide();
			JQ(".class_inv_seq_num").hide();
			JQ("#dialog_name").prop( "disabled", true );
			JQ("#dialog_remark").prop( "disabled", true );
			if(view_only){
				JQ("#dialog_cancel").prop( "disabled", true);
				JQ("#dialog_active").prop( "disabled", true);
			}else{
				JQ("#dialog_active").prop( "disabled", false);
				JQ("#dialog_cancel").prop( "disabled", false);
			}
		}
		
		JQ("#dialog_form").dialog("open");
	},                       
	delete_field_option: function(field_area, field_num){
		var li = JQ('#li_report_field-'+field_area+'-'+field_num);
		if(!li)	return false;
		var field_info = this.get_field_info_by_ele(li);
		
		JQ('#li_report_field-'+field_area+'-'+field_num).remove();
	},
	view_info: function(title, desc){
		JQ("#dialog").dialog("option", "title", title);
		JQ("#dialog_desc").html(desc);
		JQ("#dialog").dialog( "open" );
	},
	save: function(){
		if (!this.check_form()) {
			return false;
		}
		
		this.f_a['a'].value = 'ajax_save';
        this.f_a.submit();
	},
	check_form: function(){
		var THIS = this;
		if(!this.f_a['title'].value.trim()){
			alert('Please Key In Title');
			this.f_a['title'].focus();
			return false;
		}else{
			if (this.f_a['title'].value.trim().length > 100){
				alert('Title Cannot Exceed 100 Characters');
				this.f_a['title'].focus();
				return false;
			}
		}
		
		if(!this.f_a['file_format'].value.trim()){
			alert('Please Select A File Format');
			this.f_a['file_format'].focus();
			return false;
		}
		
		if(this.f_a['is_other'].checked){
			if(!this.f_a['other_delimiter'].value.trim()){
				alert('Please Key In Other Delimiter');
				this.f_a['other_delimiter'].focus();
				return false;
			}
			
			if(this.f_a['other_delimiter'].value.trim().length > 1 ){
				alert('Other Delimiter Cannot Exceed 1 Character');
				this.f_a['other_delimiter'].focus();
				return false;
			}
		}else{
			if(!this.f_a['delimiter'].value.trim()){
				alert('Please Select A Delimiter');
				this.f_a['delimiter'].focus();
				return false;
			}
		}
		
		if(!this.f_a['date_format'].value.trim()){
			alert('Please Select A Date Format');
			this.f_a['date_format'].focus();
			return false;
		}
		
		if(!this.f_a['row_format'].value.trim()){
			alert('Please Select A Row Format');
			this.f_a['row_format'].focus();
			return false;
		}
		
		if(!this.f_a['data_type'].value.trim()){
			alert('Please Select A Data Type');
			this.f_a['data_type'].focus();
			return false;
		}
		
		var invalid = false;
		if(JQ('#ul_droplist-header li').length > 0){
			JQ('#ul_droplist-header li :input').each(function(index){
				if (JQ(this).val() == ''){
					invalid = true;
					alert('Please Key In Header');
					JQ(this).focus();
					return false;
				}
			});
		}
		
		if (invalid == true){
			return false;
		}
		
		var tmp_list=[], tmp_field = [];
		if (this.f_a['row_format'].value == 'single_line'){
			tmp_list[0] = "#ul_droplist-master";
			tmp_field[0] = "Column";
		}else if (this.f_a['row_format'].value == 'two_row'){
			tmp_list[0] = "#ul_droplist-master";
			tmp_list[1] = "#ul_droplist-detail";
			tmp_field[0] = "Master";
			tmp_field[1] = "Details";
		}else if (this.f_a['row_format'].value == 'ledger_format'){
			tmp_list[0] = "#ul_droplist-master";
			tmp_field[0] = "Row";
		}else if (this.f_a['row_format'].value == 'no_repeat_master'){
			tmp_list[0] = "#ul_droplist-master";
			tmp_list[1] = "#ul_droplist-detail";
			tmp_field[0] = "Master";
			tmp_field[1] = "Details";
		}
		
		for(var i=0; i<tmp_list.length; i++){
			if(JQ(tmp_list[i] + " li").length <= 0){
				alert('Please Drag At Least 1 Field To "' + tmp_field[i] + '"');
				return false;
			}
			
			if(JQ(tmp_list[i] + " li").length > 0){
				JQ(tmp_list[i] + " li").each(function(index){
					var field_lable_type = JQ(this).find('input.inp_field_label_type').val();
					var field_title = JQ(this).find('input.inp_field_label').val();
					
					if (field_lable_type == "inv_seq_num" || field_lable_type == "seq_num"){
						var field_value = JQ(this).find('input.inp_field_value').val();
						if (field_value == ''){
							alert(field_title + ' Cannot Empty. Please Double Click On It To Set The Value.');
							invalid = true;
							return false;
						}else if (field_value == '__FIELD_VALUE__'){
							JQ(this).find('input.inp_field_value').val("0");
						}
					}else if (field_lable_type == "cancel"){
						var field_active = JQ(this).find('input.inp_field_active').val();
						if (field_active == ''){
							alert(field_title + ' Cannot Empty. Please Double Click On It To Set The Value.');
							invalid = true;
							return false;
						}
						
						var field_cancel = JQ(this).find('input.inp_field_cancel').val();
						if (field_cancel == ''){
							alert(field_title + ' Cannot Empty. Please Double Click On It To Set The Value.');
							invalid = true;
							return false;
						}  
					}else if (field_lable_type == "open_field"){
						var field_value = JQ(this).find('input.inp_field_value').val();
						if (field_value == '__FIELD_VALUE__'){
							JQ(this).find('input.inp_field_value').val("");
						}else if (field_value == 'zero'){
							JQ(this).find('input.inp_field_value').val("0");
						}
					}
				});
			}
			
			if (invalid == true){
				return false;
			}
		}
			
		return true;
	},
	close: function(){
		if(view_only){
			if(!confirm('Are You Sure You Want To Close?'))	return false;
		}else{
			if(!confirm('Close Without Save?'))	return false;
		}
		
		window.location = phpself;
	},
	change_row_format: function(e){
		var THIS = this;
		if (e.value == "single_line"){
			JQ("#h_master").text("Column");
			JQ(".class_detail").hide();
			JQ(".class_detail2").hide();
			JQ("#div_report_builder").show();
			THIS.load_data_type();
		}else if (e.value == "two_row"){
			JQ("#h_master").text("Master");
			JQ("#h_detail").text("Details");
			JQ(".class_detail").show();
			JQ(".class_detail2").hide();
			JQ("#div_report_builder").show();
			THIS.load_data_type();
		}else if (e.value == "ledger_format"){
			JQ("#h_master").text("Row");
			JQ(".class_detail").hide();
			JQ(".class_detail2").hide();
			JQ("#div_report_builder").show();
			THIS.load_data_type();
		}else if (e.value == "no_repeat_master"){
			JQ("#h_master").text("Master");
			JQ("#h_detail").text("Details");
			JQ(".class_detail").show();
			JQ(".class_detail2").hide();
			JQ("#div_report_builder").show();
			THIS.load_data_type();
		}else{
			JQ("#div_report_builder").hide();
			JQ("#data_type").prop("disabled", true);
			JQ("#data_type").prop('selectedIndex', 0);
		}
		JQ("#ul_droplist-master").find("li").remove();
		JQ("#ul_droplist-detail").find("li").remove();
	},
	change_data_type: function(e){
		var THIS = this;
		if(e.value == ""){
			JQ("#div_report_builder").hide();
		}else{
			JQ("#div_report_builder").show();
			THIS.load_data_field();
		}
		JQ("#ul_droplist-master").find("li").remove();
		JQ("#ul_droplist-detail").find("li").remove();
	},
	load_data_type:function(){
		var THIS = this;
		var data="row_format=" + JQ('#row_format').val();
		JQ.ajax({
			type:"post",
			url:"custom.setup_acc_export.php?a=load_data_type",
			data: data,
			success:function(data){
				JQ('#get_data_type').html(data);
			}
		});
		JQ("#div_report_builder").hide();
	},
	load_data_field:function(){
		var THIS = this;
		var data="data_type=" + JQ('#data_type').val() + "&row_format=" + JQ('#row_format').val();
		JQ.ajax({
			type:"post",
			url:"custom.setup_acc_export.php?a=change_data_type",
			data: data,
			success:function(data){
				JQ('#divRight').html(data);
				JQ('.ul_dragable').tooltip();
				if(!view_only){
					JQ('.ul_dragable li').draggable({
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
					JQ('#ul_droplist-header').sortable();
				}
				
				var area_list = ['master', 'detail', 'detail2'];
				for(var i=0; i<area_list.length; i++){
					var area = area_list[i];
					var tmp_area = area;
						
					if(area == 'detail' || area == 'detail2'){
						tmp_area = 'master';
					}
					JQ('#div_droplist-'+area).droppable({
						activeClass: "ui-state-highlight",
						hoverClass: "drop-hover",
						accept: ".li_report_option_area-"+tmp_area,
						drop: function( event, ui ) {
							THIS.field_dropped(this, ui.draggable);
						}
					});
					
					if(!view_only){
						JQ('#ul_droplist-'+area).sortable({
							containment: "#div_droplist-"+area
						});
					}
					
					JQ('#ul_droplist-'+area+' li.li_report_field').each(function(){
						THIS.init_field_event(this);
					});
				}
				
			}
		});
	},
	preview:function(){
		var THIS = this;
		if(!view_only){
			if (!this.check_form()) {
				return false;
			}
			tmp = JQ(this.f_a).serialize();
		}

		JQ.ajax({
			type:"post",
			url:"custom.setup_acc_export.php",
			data: tmp + "&a=preview",
			success:function(data){
				JQ('#divPreview').html(data);
				JQ("#divPreview").dialog("open");
			}
		});
	},
	templates_changed:function(e){
		if(e.value != ""){
			var have_data = false;
			if(JQ('#ul_droplist-header li').length > 0){
				have_data = true;
			}
			
			var tmp_list=[], tmp_field = [];
			if (this.f_a['row_format'].value == 'single_line'){
				tmp_list[0] = "#ul_droplist-master";
			}else if (this.f_a['row_format'].value == 'two_row'){
				tmp_list[0] = "#ul_droplist-master";
				tmp_list[1] = "#ul_droplist-detail";
			}else if (this.f_a['row_format'].value == 'ledger_format'){
				tmp_list[0] = "#ul_droplist-master";
			}else if (this.f_a['row_format'].value == 'no_repeat_master'){
				tmp_list[0] = "#ul_droplist-master";
				tmp_list[1] = "#ul_droplist-detail";
			}
			
			for(var i=0; i<tmp_list.length; i++){
				if(JQ(tmp_list[i] + " li").length > 0){
					have_data = true;
					break;
				}
			}
				
			if(have_data){
				if(!confirm("Are you sure you want to load pre-list template?")){
					return;
				}
			}
		}
		
		this.f_a['a'].value = 'ajax_load_templates';
		this.f_a.submit();
	}
};	
{/literal}	
</script>

{* Sample Element - Use to clone *}
<div  style="display:none;">
	{* Report Field *}
	<ul id="ul_tmp_report_field">
		{include file="custom.setup_acc_export.report_field.tpl" tmp_field="__TMP_FIELD__"}
	</ul>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Accounting Export Format {if $form.id}(ID#{$form.id}){else}(New){/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<form name="f_a" onSubmit="return false;" method="post" id="test">
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="branch_id" value="{if $form.branch_id}{$form.branch_id}{else}{$sessioninfo.branch_id}{/if}" />
	<input type="hidden" name="a" />
	
<div class="card mx-3">
	<div class="card-body">
		
			{if $templates_list}
		<p {if $view_only}style="display:none;"{/if}>
			<b class="form-label">Pre-list Templates</b>
			<select class="form-control" name="template_list" id="template_list" onchange="SETUP_CUSTOM_ACC_EXPORT_OPEN.templates_changed(this);">
				<option value="">-- Select --</option>
				{foreach from=$templates_list key=k item=items}
					<option value="{$items.tid}" {if $form.tid eq $items.tid}selected{/if}>{$items.t_title}</option>
				{/foreach}
			</select>
		</p>
	{else}
		<br />
	{/if}

	<table class="stdframe"  cellspacing="5" cellpadding="4" border="0">
		<div class="row">
		<tr>
			<td><h3>Settings</h3><td>
			<td></td>
		</tr>
		<div class="col-md-6">
			<tr>
				<td><b class="form-label">Title</b></td>
				<td><input class="form-control" type="text" name="title" value="{$form.title|escape:'html'}" size="40" maxlength="100"/></td>
			</tr>
		</div>
		<div class="col-md-6">
			<tr>
				<td><b class="form-label">File Format</b></td>
				<td>
					<select class="form-control" name="file_format">
						<option value="">-- Select --</option>
						{foreach from=$file_format_list key=k item=items}
							<option value="{$k}" {if $form.file_format eq $k}selected{/if}>{$items}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		</div>
		<tr>
			<td><b class="form-label">Delimiter</b></td>
			<td>
				<div class="form-inline">
					<select class="form-control" name="delimiter" id="delimiter" {if $form.delimiter && ($form.delimiter neq "," && $form.delimiter neq "|" && $form.delimiter neq ";")}disabled{/if}>
						<option value="">-- Select --</option>
						<option value="," {if $form.delimiter eq ","}selected{/if}>,</option>
						<option value="|" {if $form.delimiter eq "|"}selected{/if}>|</option>
						<option value=";" {if $form.delimiter eq ";"}selected{/if}>;</option>
					</select>
					<span>
					<div class="form-inline ">
						&nbsp;&nbsp;&nbsp;<input type="checkbox" name="is_other" id ="is_other" value="1" onchange="SETUP_CUSTOM_ACC_EXPORT_OPEN.is_other();" {if $form.delimiter && ($form.delimiter neq "," && $form.delimiter neq "|" && $form.delimiter neq ";")}checked{/if}>&nbsp;<b class="text-dark">Other</b>&nbsp;&nbsp;<input class="form-control" maxlength="1" size="5" type="text" name="other_delimiter" id ="other_delimiter"  {if $form.delimiter && ($form.delimiter neq "," && $form.delimiter neq "|" && $form.delimiter neq ";")}value="{$form.delimiter|escape:'html'}"{else}disabled="true"{/if}/>
					</div>
					</span>
				</div>
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Date Format</b></td>
			<td>
				<select class="form-control" name="date_format">
					<option value="">-- Select --</option>
					<option value="d/m/Y" {if $form.date_format eq "d/m/Y"}selected{/if}>DD/MM/YYYY</option>
					<option value="j/n/Y" {if $form.date_format eq "j/n/Y"}selected{/if}>D/M/YYYY</option>
					<option value="d/m/y" {if $form.date_format eq "d/m/y"}selected{/if}>DD/MM/YY</option>
					<option value="Y-m-d" {if $form.date_format eq "Y-m-d"}selected{/if}>YYYY-MM-DD</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Time Format</b></td>
			<td>
				<select class="form-control" name="time_format">
					<option value="">-- Select --</option>
					<option value="h:i" {if $form.time_format eq "h:i"}selected{/if}>hh:mm (12 hr)</option>
					<option value="H:i" {if $form.time_format eq "H:i"}selected{/if}>hh:mm (24 hr)</option>
					<option value="Hi" {if $form.time_format eq "Hi"}selected{/if}>hhmm (24 hr)</option>
				</select>
			</td>
		</tr>
		<tr>
		
			<td><b class="form-label">Row Format</b></td>
			<td>
				<select class="form-control" id="row_format" name="row_format" onchange="SETUP_CUSTOM_ACC_EXPORT_OPEN.change_row_format(this);">
					<option value="">-- Select --</option>
					<option value="single_line" {if $form.row_format eq "single_line"}selected{/if}>Single Line</option>
					<option value="two_row" {if $form.row_format eq "two_row"}selected{/if}>Two Row</option>
					<option value="ledger_format" {if $form.row_format eq "ledger_format"}selected{/if}>Ledger Format</option>
					{*<option value="no_repeat_master" {if $form.row_format eq "no_repeat_master"}selected{/if}>No Repeat Master Format</option>*}
				</select>
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Data Type</b></td>
			<td id="get_data_type">
				{if $form.data_type}
					{include file="custom.setup_acc_export.data_type.tpl"}
				{else}
					<select class="form-control" id="data_type" name="data_type" disabled>
						<option value="">-- Select --</option>
					</select>
				{/if}
			</td>
		</tr>
		<tr>
			<td><b class="form-label">Grouped By</b></td>
			<td class="text-dark">Receipt</td>
		</tr>
		{if $form.id}
			<tr>
				<td><b class="form-label">Created By</b></td>
				<td>
					{$form.code}
				</td>
			</tr>
		{/if}
	</div>
	</table>

		
	</div>
</div>	
	<div class="card mx-3">
		<div class="card-body">
			<div class="stdframe"  id="div_header_builder">
				<table cellspacing="5" cellpadding="4" width="100%">
					<tr>
						<td><h3>Header Setting</h3></td>
						<td></td>
					<tr>
					<tr>
						<td>
							<div id="div_droplist-header" class="div_droplist" style="width:auto; height:100px;">
								<ul id="ul_droplist-header" name="ul_droplist-header" class="ul_droplist" style="height:100px;overflow:auto;">
									{if $form.header_column}
										{foreach from=$form.header_column key=k item=items}
											<li>
												<input type='text' class="form-control" name='header[]' {if $view_only}style='border:0;background-color:white;color:black'{else}style='border:0'{/if} value="{$items|escape:'html'}" maxlength="100"/>
												{if !$view_only}
													<img style='top:20%;right:0;position:absolute;' src='ui/icons/cancel.png' class='img_delete_report_field' title='Delete' name='delete_header[]' onclick='SETUP_CUSTOM_ACC_EXPORT_OPEN.delete_header_column(this);' />
												{/if}
											</li>
										{/foreach}
									{/if}
								</ul>
							</div>
						</td>
						<td valign="top" width="50px">
							<input type="button" class="btn btn-primary" value="Add" onclick="SETUP_CUSTOM_ACC_EXPORT_OPEN.add_header_column();"/>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<br />
	<div class="stdframe" id="div_report_builder" {if $form.data_type && $form.row_format}style="overflow:auto"{else}style="overflow:auto;display:none"{/if}>
		<div class="card mx-3">
			<div class="card-body">
				<div id="divLeft" style="float:left;width:50%">
					<table cellspacing="5" cellpadding="4" width="100%" id="tblLeft">
						{if $form.row_format eq "single_line"}
							{assign var=tmp_name value="Column"}
						{elseif $form.row_format eq "two_row" || $form.row_format eq "no_repeat_master"}
							{assign var=tmp_name value="Master"}
							{assign var=tmp_name_detail value="Details"}
						{elseif $form.row_format eq "ledger_format"}
							{assign var=tmp_name value="Row"}
							{assign var=tmp_name_detail value="Data"}
						{/if}
						<tr>
							<td><h4 id="h_master">{$tmp_name}</h4></td>
						</tr>
						<tr>
							<td valign="top">
								<div id="div_droplist-master" class="div_droplist">
									<ul id="ul_droplist-master" class="ul_droplist">
										{foreach from=$form.data_column.master item=col_info name=fcol}
											{if $col_info.field_type == "open_field"}
												{if is_numeric($col_info.field_desc) && $col_info.field_desc == 0 && $col_info.field_desc !== "0.00"}
													{assign var="tmp_desc" value="zero"}
												{else}
													{assign var="tmp_desc" value=$col_info.field_desc}
												{/if}
												
												{if is_numeric($col_info.field_value) && $col_info.field_value == 0 && $col_info.field_value !== "0.00"}
													{assign var="tmp_val" value="zero"}
												{else}
													{assign var="tmp_val" value=$col_info.field_value}
												{/if}
											{else}
												{assign var="tmp_desc" value=$data_field[$col_info.field_type].field_desc}
												{assign var="tmp_val" value=$col_info.field_value}
											{/if}
											{include file="custom.setup_acc_export.report_field.tpl" field_area='master' field_active=$col_info.field_active field_cancel=$col_info.field_cancel field_value=$tmp_val field_desc=$tmp_desc field_label_type=$col_info.field_label_type field_type=$col_info.field_type org_field_label=$col_info.org_field_label field_label=$col_info.field_label field_num=$smarty.foreach.fcol.iteration}
										{/foreach}
									</ul>
								</div>
							</td>
						</tr>
						<tr {if $form.row_format eq "single_line" || $form.row_format eq "ledger_format"}style="display:none" class="class_detail"{else}class="class_detail"{/if}>
							<td><h4 id="h_detail">{$tmp_name_detail}</h4></td>
						</tr>
						<tr {if $form.row_format eq "single_line" || $form.row_format eq "ledger_format"}style="display:none" class="class_detail"{else}class="class_detail"{/if}>
							<td valign="top">
								<div id="div_droplist-detail" class="div_droplist">
									<ul id="ul_droplist-detail" class="ul_droplist">
										{foreach from=$form.data_column.detail item=col_info name=fcol}
											{if $col_info.field_type == "open_field"}
												{if is_numeric($col_info.field_desc) && $col_info.field_desc == 0 && $col_info.field_desc !== "0.00"}
													{assign var="tmp_desc" value="zero"}
												{else}
													{assign var="tmp_desc" value=$col_info.field_desc}
												{/if}
												
												{if is_numeric($col_info.field_value) && $col_info.field_value == 0 && $col_info.field_value !== "0.00"}
													{assign var="tmp_val" value="zero"}
												{else}
													{assign var="tmp_val" value=$col_info.field_value}
												{/if}
											{else}
												{assign var="tmp_desc" value=$data_field[$col_info.field_type].field_desc}
												{assign var="tmp_val" value=$col_info.field_value}
											{/if}
											{include file="custom.setup_acc_export.report_field.tpl" field_area='detail' field_active=$col_info.field_active field_cancel=$col_info.field_cancel field_value=$tmp_val field_desc=$tmp_desc field_label_type=$col_info.field_label_type field_type=$col_info.field_type org_field_label=$col_info.org_field_label field_label=$col_info.field_label field_num=$smarty.foreach.fcol.iteration}
										{/foreach}
									</ul>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div id="divRight" style="float:left;width:50%">
					{if $form.data_type}
						{include file="custom.setup_acc_export.drag_field.tpl"}
					{/if}
				</div>
			</div>
		</div>
	</div>
</form>
<p align="center" id="saving" style="clear:both">
	<input class="btn btn-primary" id="btnPreviewFormat" name="btnPreviewFormat" type=button value="Preview" onclick="SETUP_CUSTOM_ACC_EXPORT_OPEN.preview();">
	{if !$view_only}
		<input class="btn btn-success" id="btnSaveFormat" name="btnSaveFormat" type=button value="Save" onclick="SETUP_CUSTOM_ACC_EXPORT_OPEN.save();">
	{/if}
	<input class="btn btn-danger" id="btnClose" name="btnClose" type=button value="Close" onclick="SETUP_CUSTOM_ACC_EXPORT_OPEN.close();">
</p>

<div id="divPreview" title="Preview">
	{include file="custom.setup_acc_export.preview.tpl"}
</div>

<div id="dialog" title="Basic dialog" >
<p id="dialog_desc">Test</p>
</div>

<div id="dialog_form" title="Basic dialog">
<p class="validateTips"></p>
<form>
<table>
	<fieldset>
	<tr>
		<input type="hidden" name="tmp_field_area" id="tmp_field_area" value="" />
		<input type="hidden" name="tmp_field_num" id="tmp_field_num" value="" />
		<input type="hidden" name="tmp_field_label_type" id="tmp_field_label_type" value="" />
		<td>Title</td>
		<td><input type="text" name="dialog_name" id="dialog_name" value="" style="width:200px" class="text ui-widget-content" maxlength="25"></td>
	</tr>
	{* Open Field or Invoice Sequence No or Sequence No *}
	<tr style="display:none" class="class_open_field">
		<td id="dialog_field_type"></td>
		<td><input type="text" name="dialog_value" id="dialog_value" value="" class="text ui-widget-content" maxlength="40"></td>
	</tr>
	{* Cancel *}
	<tr style="display:none" class="class_cancel">
		<td>Active</td>
		<td><input type="text" name="dialog_active" id="dialog_active" value="" class="text ui-widget-content" maxlength="100"><span style="color:blue;" id="span_active"></span></td>
	</tr>
	<tr style="display:none" class="class_cancel">
		<td>Cancelled</td>
		<td><input type="text" name="dialog_cancel" id="dialog_cancel" value="" class="text ui-widget-content" maxlength="100"><span style="color:blue;" id="span_cancel"></span></td>
	</tr>
	{* All *}
	<tr>
		<td>Remark</td>
		<td><textarea rows="5" cols="90" name="dialog_remark" id="dialog_remark" class="text ui-widget-content"></textarea></td>
	</tr>
	<tr>
		<td><input id="btnSave" type="submit" tabindex="-1" style="position:absolute; top:-1000px"></td>
	</tr>
	</fieldset>
</table>
</form>
</div>
<script type="text/javascript">
	SETUP_CUSTOM_ACC_EXPORT_OPEN.initialize();
</script>
{include file=footer.tpl}
