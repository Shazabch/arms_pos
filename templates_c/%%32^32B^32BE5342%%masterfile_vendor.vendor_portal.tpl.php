<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:29
         compiled from masterfile_vendor.vendor_portal.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'masterfile_vendor.vendor_portal.tpl', 1441, false),)), $this); ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


<style>
<?php echo '

.tr_split_row td{

	border-left: 1px solid white;
	border-right: 1px solid white !important;
}

.tr_header2{
	background-color: #cfc;
}

.btn_add_branch_profit_row_more_percent_copy, .btn_branch_profit_row_copy, .btn_add_branch_bonus_row_copy, .btn_add_branch_bonus_row_more_percent_copy{
	background: #ecc;
	border:1px solid #fff;
	border-right:1px solid #333; 
	border-bottom:1px solid #333;
}

.btn_add_branch_profit_row_more_percent_paste, .btn_branch_profit_row_paste, .btn_add_branch_bonus_row_paste, .btn_add_branch_bonus_row_more_percent_paste{
	background: #edd;
	border:1px solid #fff;
	border-right:1px solid #333; 
	border-bottom:1px solid #333;
}

.btn_copied{
	border:1px inset #fff;
	background-color: #ccf;
}

'; ?>

</style>


<script type="text/javascript">
var phpself = '<?php echo $_SERVER['PHP_SELF']; ?>
';

<?php echo '

var VENDOR_PORTAL = {
	f_vp: undefined,
	is_updating: false,
	branch_profit_row_breakdown_copied_object: undefined,
	branch_profit_row_copied_object: undefined,
	branch_bonus_row_more_percent_copied_object: undefined,
	branch_bonus_row_copied_object: undefined,
	initialize: function(){
		this.initial_f_vp();
		
		//new Draggable(\'div_vendor_portal\',{ handle: \'div_vendor_portal_header\'});	
	},
	// the first thing to do after popup is show, initial event for popup form
	initial_f_vp: function(){
		this.f_vp = document.f_vp;
		var THIS = this;
		
		Calendar.setup({
		    inputField     :    "inp_start_date",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_start_date",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
		
		// setup calendar for expire date
		var inp_expire_date_list = $(this.f_vp).getElementsBySelector("input.inp_expire_date");
		for(var i=0; i<inp_expire_date_list.length; i++){
			var bid = inp_expire_date_list[i].id.split("-")[1];
			
			Calendar.setup({
			    inputField     :    "inp_expire_date-"+bid,     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_expire_date-"+bid,  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true
			});
		}
		
		var inp_profit_date_to_list = $(this.f_vp).getElementsBySelector("table.tbl_report_profit input.inp_profit_date_to");
		for(var i=0; i<inp_profit_date_to_list.length; i++){
			var bid = inp_profit_date_to_list[i].id.split("-")[1];
			var row_no = inp_profit_date_to_list[i].id.split("-")[2];
			
			this.init_calendar_for_branch_profit_row(bid, row_no);
		}
		
		SKU_CAT_AUTOCOMPLETE.initialize();
		
		this.is_updating = false;
		
		new Ajax.PeriodicalUpdater(\'\', "dummy.php", {frequency:1500});
	},
	// function to init calendar event for branch profit row
	init_calendar_for_branch_profit_row: function(bid, row_no){
		Calendar.setup({
		    inputField     :    "inp_profit_date_to-"+bid+\'-\'+row_no,     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_profit_date_to-"+bid+\'-\'+row_no,  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
	},
	close: function(){
		window.close();
	},
	// function when user toggle allow all branches checkbox
	toggle_all_allowed_branches: function(){
		var c = $(\'inp_toggle_all_allowed_branches\').checked;
		
		$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
			inp.checked = c;
		});	
	},
	// function when user toggle no expire checkbox
	toggle_no_expire: function(bid){
		var c = $(\'inp_no_expire-\'+bid).checked;
		
		if(c){
			$(\'span_expire_date-\'+bid).hide();
		}else{
			$(\'span_expire_date-\'+bid).show();
		}
	},
	// function when user change active status
	active_vendor_portal_changed: function(){
		var active = int(getRadioValue(this.f_vp[\'active_vendor_portal\']));
		
		var span_active_vendor_portal_msg = $(\'span_active_vendor_portal_msg\');
		if(active){
			$(span_active_vendor_portal_msg).hide();
		}else{
			$(span_active_vendor_portal_msg).show();
		}
	},
	// function when user click generate ticket
	generate_ticket_clicked: function(bid){
		var ticket = this.f_vp[\'login_ticket[\'+bid+\']\'].value;
		
		if(ticket){
			this.clear_ticket(bid);
		}else{
			this.generate_ticket(bid);
		}
	},
	// function to clear ticket
	clear_ticket: function(bid){
		this.f_vp[\'login_ticket[\'+bid+\']\'].value = \'\';
		$(\'btn_generate_ticket-\'+bid).value = \'Generate\';
		
		$(\'span_clone_ticket-\'+bid).hide();
	},
	// function to generate new ticket
	generate_ticket: function(bid, use_this_ticket){
		var alpha_list = [\'a\',\'b\',\'c\',\'d\',\'e\',\'f\',\'g\',\'h\',\'i\',\'j\',\'k\',\'l\',\'m\',\'n\',\'o\',\'p\',\'q\',\'r\',\'s\',\'t\',\'u\',\'v\',\'w\',\'x\',\'y\',\'z\'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var ticket_length = 10;
		var ticket = \'\';
		
		if(!use_this_ticket){
			// generate new ticket
			for(var i=0; i<ticket_length; i++){
				var j = Math.floor(Math.random()*(rand_char.length));
				ticket += rand_char[j];
			}
		}else{
			// use the given ticket
			ticket = use_this_ticket;
		}
		
		
		// assign
		this.f_vp[\'login_ticket[\'+bid+\']\'].value = ticket;
		$(\'btn_generate_ticket-\'+bid).value = \'Clear\';
		
		$(\'span_clone_ticket-\'+bid).show();
	},
	// function to validate form 
	check_form: function(){
		/*if(this.f_vp[\'login_ticket\'].value != \'\'){	// got ticket
			var checked_count = 0;
			$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
				if(inp.checked)	checked_count++;
			});
			if(checked_count<=0){
				alert(\'Please tick at least 1 allowed branch.\');
				return false;
			}
		}*/
		
		// get branches checkbox
		var inp_allowed_branches_list = $(this.f_vp).getElementsBySelector("input.inp_allowed_branches");
		
		// loop for each branches checkbox
		for(var i=0; i<inp_allowed_branches_list.length; i++){
			// found got tick
			if(inp_allowed_branches_list[i].checked){
				var bid = inp_allowed_branches_list[i].id.split("-")[1];	// get branch id
				
				// sku group
				if(!this.f_vp[\'sku_group_info[\'+bid+\']\'].value){
					alert(\'Please select SKU Group for all ticked branches.\');
					return false;
				}
				
				// login ticket
				if(!this.f_vp[\'login_ticket[\'+bid+\']\'].value){	// found no give login ticket
					alert(\'Please generate ticket for all ticked branches.\');
					return false;
				}
			}
		}
		
		if(!check_required_field(this.f_vp))	return false;
		
		// check duplicate branch profit other %
		var img_branch_profit_row_breakdown_duplicated_entry_list = $$(\'#tbl_branch_access_setting img.img_branch_profit_row_breakdown_duplicated_entry\');
		for(var i=0; i<img_branch_profit_row_breakdown_duplicated_entry_list.length; i++){
			if(img_branch_profit_row_breakdown_duplicated_entry_list[i].style.display==\'\'){	// got duplicate
				var id_info = img_branch_profit_row_breakdown_duplicated_entry_list[i].id.split(\'-\');
				var bid = id_info[1];
				var row_no = id_info[2];
				var type_row_no = id_info[3];
				
				alert(\'Found duplicate on branch profit.\');
				this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+type_row_no+\'][per]\'].focus();
				return false;
			}
		}
		
		// check duplicate branch bonus other %
		var img_branch_bonus_row_breakdown_duplicated_entry_list = $$(\'#tbl_branch_access_setting img.img_branch_bonus_row_breakdown_duplicated_entry\');
		for(var i=0; i<img_branch_bonus_row_breakdown_duplicated_entry_list.length; i++){
			if(img_branch_bonus_row_breakdown_duplicated_entry_list[i].style.display==\'\'){	// got duplicate
				var id_info = img_branch_bonus_row_breakdown_duplicated_entry_list[i].id.split(\'-\');
				var bid = id_info[1];
				var y = id_info[2];
				var m = id_info[3];
				var row_no = id_info[4];
				var type_row_no = id_info[5];
				
				alert(\'Found duplicate on branch bonus.\');
				this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+type_row_no+\'][per]\'].focus();
				return false;
			}
		}
		
		return true;
	},
	// function when user click update
	update_clicked: function(){
		var THIS = this;
		
		// validate form
		if(!this.check_form())	return;
		
		// update button status
		$$(\'#p_action_button input\').invoke(\'disable\');
		$(\'btn_update_vendor_portal\').value = \'Saving . . .\';
		
		this.is_updating = true;
		
		var params = $(this.f_vp).serialize();
		ajax_request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
			    THIS.is_updating = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\']){ // success
						alert(\'Update Successfully\');
						THIS.close();
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			    $$(\'#p_action_button input\').invoke(\'enable\');
			    $(\'btn_update_vendor_portal\').value = \'Save\';
			}
		});
	},
	// function to clone ticket for all branches
	clone_ticket: function(bid){
		var ticket = this.f_vp[\'login_ticket[\'+bid+\']\'].value.trim();
		if(!ticket)	return false;	// no ticket to clone
		
		if(!confirm(\'Are you sure to clone this ticket to all allowed branches?\'))	return false;
		
		// get branches checkbox
		var inp_allowed_branches_list = $(this.f_vp).getElementsBySelector("input.inp_allowed_branches");
		
		// loop for each branches checkbox
		for(var i=0; i<inp_allowed_branches_list.length; i++){
			// found got tick
			if(inp_allowed_branches_list[i].checked){
				var bid = inp_allowed_branches_list[i].id.split("-")[1];	// get branch id
				
				this.generate_ticket(bid, ticket);	// clone ticket
			}
		}
	},
	// function to get branch profit row no
	get_branch_profit_row_no_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()==\'tr\'){
                if($(parent_ele).hasClassName(\'tr_branch_profit_row\')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var row_num = int(parent_ele.id.split(\'-\')[2]);
		return row_num;
	},
	// function to get current max row no at branch profit
	get_branch_profit_max_row_no: function(bid){
		var max_row_no = 0;
		var tr_branch_profit_row_list = $$(\'#tbody_branch_report_profit-\'+bid+\' tr.tr_branch_profit_row\');
		
		for(var i = 0; i<tr_branch_profit_row_list.length; i++){
			var tmp_row_no = this.get_branch_profit_row_no_by_ele(tr_branch_profit_row_list[i]);
			
			if(tmp_row_no > max_row_no)	max_row_no = tmp_row_no;
		}

		return max_row_no;
	},
	// function when user click to add new branch profit row
	add_branch_profit_row_clicked: function(bid){
		if(!bid)	return false;
		
		var new_tr = $(\'tr_branch_profit_row-__TMP_BID__-__TMP_ROW_NO__\').cloneNode(true);
		
		var new_row_no = this.get_branch_profit_max_row_no(bid)+1;	// get new row num
		
		new_tr.id = "tr_branch_profit_row-"+bid+\'-\'+new_row_no;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_no);
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		$(new_tr).update(new_html);
		
		$(\'tbody_branch_report_profit-\'+bid).appendChild(new_tr);
		
		this.init_calendar_for_branch_profit_row(bid, new_row_no);
	},
	// function when user click delete branch profit row
	delete_branch_profit_row_clicked: function(bid, row_no){
		if(!bid || !row_no)	return false;
		
		if(!confirm(\'Are you sure?\'))	return false;
		
		$(\'tr_branch_profit_row-\'+bid+\'-\'+row_no).remove();
	},
	// function when user click add new branch bonus group
	add_new_branch_bonus_group_clicked: function(bid){
		if(!bid)	return false;
		
		var y = int($(\'inp_branch_bonus_y-\'+bid).value);
		var m = int($(\'inp_branch_bonus_m-\'+bid).value);
		
		if(y<2010){
			alert(\'The minimum year is 2010\');
			return false;
		}
		if(m<=0 || m>12){
			alert(\'Invalid month, must between 1 to 12\');
			return false;
		}
		
		if($("tbl_branch_bonus-"+bid+\'-\'+y+\'-\'+m)){
			alert(\'Year \'+y+\' Month \'+m+\' already exisys\');
			return false;
		}
		
		var new_div = $(\'div_branch_bonus-__TMP_BID__-__TMP_YEAR__-__TMP_MONTH__\').cloneNode(true);
		
		new_div.id = "div_branch_bonus-"+bid+\'-\'+y+\'-\'+m;	// change row id
		
		// get row html
		new_html = new_div.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		new_html = new_html.replace(/__TMP_YEAR__/g, y);
		new_html = new_html.replace(/__TMP_MONTH__/g, m);
		$(new_div).update(new_html);
		
		$(\'div_branch_bonus_group_list-\'+bid).appendChild(new_div);
	},
	
	///////// need check ///////
	// function to get branch row no by ele
	get_branch_bonus_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()==\'tr\'){
                if($(parent_ele).hasClassName(\'tr_branch_bonus_row\')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split(\'-\');
		var row_info = {
			\'y\': int(tmp[2]),
			\'m\': int(tmp[3]),
			\'row_no\': int(tmp[4])
		}

		return row_info;
	},
	// function to get max branch bonus row
	get_branch_bonus_max_row_no: function(bid, y, m){
		var max_row_no = 0;
		var tr_branch_bonus_row_list = $$(\'#tbody_branch_bonus-\'+bid+\'-\'+y+\'-\'+m+\' tr.tr_branch_bonus_row\');
		
		for(var i = 0; i<tr_branch_bonus_row_list.length; i++){
			var row_info = this.get_branch_bonus_info_by_ele(tr_branch_bonus_row_list[i]);
			var tmp_row_no = int(row_info[\'row_no\']);
			
			if(tmp_row_no > max_row_no)	max_row_no = tmp_row_no;
		}

		return max_row_no;
	},
	// function when user click add branch bonus row
	add_branch_bonus_row_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		var new_tr = $(\'tr_branch_bonus_row-__TMP_BID__-__TMP_YEAR__-__TMP_MONTH__-__TMP_ROW_NO__\').cloneNode(true);
		
		var new_row_no = this.get_branch_bonus_max_row_no(bid, y, m)+1;	// get new row num
		
		new_tr.id = "tr_branch_bonus_row-"+bid+\'-\'+y+\'-\'+m+\'-\'+new_row_no;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_no);
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		new_html = new_html.replace(/__TMP_YEAR__/g, y);
		new_html = new_html.replace(/__TMP_MONTH__/g, m);
		$(new_tr).update(new_html);
		
		$(\'tbody_branch_bonus-\'+bid+\'-\'+y+\'-\'+m).appendChild(new_tr);
	},
	// function when user click delete branch bonus row
	delete_branch_bonus_row_clicked: function(bid, y, m, row_no){
		if(!bid || !row_no)	return false;
		
		if(!confirm(\'Are you sure?\'))	return false;
		
		$(\'tr_branch_bonus_row-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no).remove();
	},
	// function when user click delete branch bonus group
	delete_branch_bonus_group_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		if(!confirm(\'Are you sure?\'))	return false;
		
		$(\'div_branch_bonus-\'+bid+\'-\'+y+\'-\'+m).remove();
	},
	// function when user click add more breakdown % for report profit
	add_branch_profit_row_more_percent_clicked: function(bid, row_no){
		var params = {
			\'use_for\': \'report_profit\',
			\'return_parms\': {\'bid\': bid, \'row_no\':row_no}
		};
		
		SKU_CAT_AUTOCOMPLETE.show(params);
	},
	// function when user click add more breakdown % for bonus
	add_branch_bonus_row_more_percent_clicked: function(bid, y, m, row_no){
		var params = {
			\'use_for\': \'bonus\',
			\'hide_sku_autocomplete\': 1,
			\'return_parms\': {\'bid\': bid, \'y\':y, \'m\':m, \'row_no\':row_no}
		};
		
		SKU_CAT_AUTOCOMPLETE.show(params);
	},
	// function to get current max branch profit row breakdown row
	get_max_branch_profit_row_breakdown_per_row: function(bid, row_no){
		var max_type_row_no = 0;
		
		var tr_branch_profit_row_breakdown_list = $$(\'#tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no+\' tr.tr_branch_profit_row_breakdown\');

		for(var i = 0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var row_info = this.get_branch_profit_row_breakdown_row_info_by_ele(tr_branch_profit_row_breakdown_list[i]);
			var type_row_no = int(row_info[\'type_row_no\']);
			
			if(type_row_no > max_type_row_no)	max_type_row_no = type_row_no;
		}

		return max_type_row_no;
	},
	// function to get current max branch bonus row breakdown row
	get_max_branch_bonus_row_breakdown_per_row: function(bid, row_no, y, m){
		var max_type_row_no = 0;		

		var tr_branch_bonus_row_breakdown_list = $$(\'#tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\' tr.tr_branch_bonus_row_breakdown\');

		for(var i = 0; i<tr_branch_bonus_row_breakdown_list.length; i++){
			var row_info = this.get_branch_bonus_row_breakdown_row_info_by_ele(tr_branch_bonus_row_breakdown_list[i]);
			var type_row_no = int(row_info[\'type_row_no\']);
			
			if(type_row_no > max_type_row_no)	max_type_row_no = type_row_no;
		}

		return max_type_row_no;
	},
	// function to get breakdown row % profit row info
	get_branch_profit_row_breakdown_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()==\'tr\'){
                if($(parent_ele).hasClassName(\'tr_branch_profit_row_breakdown\')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split(\'-\');
		var row_info = {
			\'row_no\': int(tmp[2]),
			\'type_row_no\': int(tmp[3])
		}

		return row_info;
	},
	// function to get breakdown row % bonus row info
	get_branch_bonus_row_breakdown_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()==\'tr\'){
                if($(parent_ele).hasClassName(\'tr_branch_bonus_row_breakdown\')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split(\'-\');
		var row_info = {
			\'y\': int(tmp[2]),
			\'m\': int(tmp[3]),
			\'row_no\': int(tmp[4]),
			\'type_row_no\': int(tmp[5])
		}

		return row_info;
	},
	// function when user click delete more breakdown % profit
	deleie_tr_branch_profit_row_breakdown: function(bid, row_no, type_row_no){
		if(!confirm(\'Are you sure?\'))	return false;
		
		$(\'tr_branch_profit_row_breakdown-\'+bid+\'-\'+row_no+\'-\'+type_row_no).remove();
		
		// check duplicate
		this.check_branch_profit_row_breakdown_duplicate(bid, row_no);
	},
	deleie_tr_branch_bonus_row_breakdown: function(bid, y, m, row_no, type_row_no){
		if(!confirm(\'Are you sure?\'))	return false;
		
		$(\'tr_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\'-\'+type_row_no).remove();
		
		this.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
	},
	// function to check whether got duplicated data for profit
	get_branch_profit_row_breakdown_duplicated_type_row_no: function(bid, row_no, type, value){
		var duplicated_type_row_no = 0;
		var tr_branch_profit_row_breakdown_list = $$(\'#tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no+\' tr.tr_branch_profit_row_breakdown\');
		
		for(var i=0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_profit_row_breakdown_list[i].id.split(\'-\')[3];
			
			if(this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+type_row_no+\'][type]\'].value == type && this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+type_row_no+\'][value]\'].value == value){
				duplicated_type_row_no = type_row_no;
				break;
			}
		}
		
		return duplicated_type_row_no;
	},
	// function to check whether got duplicated data for bonus
	get_branch_bonus_row_breakdown_duplicated_type_row_no: function(bid, row_no, y, m, type, value){
		var duplicated_type_row_no = 0;
		var tr_branch_bonus_row_breakdown_list = $$(\'#tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\' tr.tr_branch_bonus_row_breakdown\');
		
		for(var i=0; i<tr_branch_bonus_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_bonus_row_breakdown_list[i].id.split(\'-\')[5];
			
			if(this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+type_row_no+\'][type]\'].value == type && this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+type_row_no+\'][value]\'].value == value){
				duplicated_type_row_no = type_row_no;
				break;
			}
		}
		
		return duplicated_type_row_no;
	},
	// function to add more breakdown % profit
	add_branch_profit_row_more_percent: function(params){
		if(!params[\'type\'] && !params[\'value\']){
			alert(\'No data to be add.\');
			return false;
		}
		
		var THIS = this;
		var bid = params[\'bid\'];
		var row_no = params[\'row_no\'];
		
		var span_branch_profit_row_breakdown_loading = $(\'span_branch_profit_row_breakdown_loading-\'+bid+\'-\'+row_no);
		
		if(span_branch_profit_row_breakdown_loading.style.display==\'\'){
			alert(\'There are another process still running, please wait. . .\');
			return false;
		}
		
		var new_type_row_no = int(this.get_max_branch_profit_row_breakdown_per_row(bid, row_no))+1;
		
		// check not allow duplicate
		var duplicated_type_row_no = this.get_branch_profit_row_breakdown_duplicated_type_row_no(bid, row_no, params[\'type\'], params[\'value\'])
		if(duplicated_type_row_no>0){
			alert(\'Found duplicated entry. The row already exists.\');
			this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+duplicated_type_row_no+\'][per]\'].focus();
			return false;
		}
		
		params[\'a\'] = \'ajax_add_report_profit_breakdown_per\';
		params[\'new_type_row_no\'] = new_type_row_no;
		
		$(span_branch_profit_row_breakdown_loading).show();
		
		ajax_request(phpself, {
			method: \'post\',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_profit_row_breakdown_loading).hide();
							
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no, ret[\'html\']);
						
						// check duplicate
						THIS.check_branch_profit_row_breakdown_duplicate(bid, row_no);
						
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function to add more breakdown % bonus
	add_branch_bonus_row_more_percent: function(params){
		if(!params[\'type\'] && !params[\'value\']){
			alert(\'No data to be add.\');
			return false;
		}
		var THIS = this;
		var bid = params[\'bid\'];
		var row_no = params[\'row_no\'];
		var y = params[\'y\'];
		var m = params[\'m\'];
		var new_type_row_no = int(this.get_max_branch_bonus_row_breakdown_per_row(bid, row_no, y, m))+1;
		
		// check not allow duplicate
		var duplicated_type_row_no = this.get_branch_bonus_row_breakdown_duplicated_type_row_no(bid, row_no, y, m, params[\'type\'], params[\'value\'])
		if(duplicated_type_row_no>0){
			alert(\'Found duplicated entry. The row already exists.\');
			this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+duplicated_type_row_no+\'][per]\'].focus();
			return false;
		}
		
		params[\'a\'] = \'ajax_add_branch_bonus_breakdown_per\';
		params[\'new_type_row_no\'] = new_type_row_no;
		
		var span_branch_bonus_row_breakdown_loading = $(\'span_branch_bonus_row_breakdown_loading-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no).show();
		
		ajax_request(phpself, {
			method: \'post\',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_bonus_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no, ret[\'html\']);
						
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click copy profit row
	add_branch_profit_row_more_percent_copy_clicked: function(bid, row_no){
		if(!bid || !row_no)	return false;
		
		// clone object
		this.branch_profit_row_breakdown_copied_object = $(\'tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no).cloneNode();
				
		$$(\'#tbl_branch_access_setting button.btn_add_branch_profit_row_more_percent_copy\').invoke(\'removeClassName\', \'btn_copied\');
		$(\'btn_add_branch_profit_row_more_percent_copy-\'+bid+\'-\'+row_no).addClassName(\'btn_copied\');
		//alert(\'Data Copied\');
	},
	// function when user click paste profit row
	add_branch_profit_row_more_percent_paste_clicked: function(bid, row_no){
		if(!this.branch_profit_row_breakdown_copied_object || this.branch_profit_row_breakdown_copied_object.innerHTML.trim()==\'\'){
			alert(\'Nothing to paste, please copy same type of data first.\');
			return false;
		}
		
		var span_branch_profit_row_breakdown_loading = $(\'span_branch_profit_row_breakdown_loading-\'+bid+\'-\'+row_no);
		
		if(span_branch_profit_row_breakdown_loading.style.display==\'\'){
			alert(\'There are another process still running, please wait. . .\');
			return false;
		}
		var THIS = this;

		// update the copied object into tbody
		$(\'tbody_copy_report_profit_more_percent\').appendChild(this.branch_profit_row_breakdown_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_report_profit_more_percent).serialize();

		// clear the tbody
		$(\'tbody_copy_report_profit_more_percent\').update(\'\');
		
		// get new row no
		var new_type_row_no = int(this.get_max_branch_profit_row_breakdown_per_row(bid, row_no))+1;
		sz += \'&row_no=\'+row_no;
		sz += \'&new_type_row_no=\'+new_type_row_no;
		sz += \'&bid=\'+bid;
		
		// show loading icon
		$(span_branch_profit_row_breakdown_loading).show();
		
		// send to server to clone
		ajax_request(phpself, {
			method: \'post\',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_profit_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no, ret[\'html\']);
						
						// check duplicate
						THIS.check_branch_profit_row_breakdown_duplicate(bid, row_no);
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click to copy branch profit
	add_branch_profit_row_copy_clicked: function(bid){
		if(!bid)	return false;
		
		// copy html
		this.branch_profit_row_copied_object = $(\'tbody_branch_report_profit-\'+bid).cloneNode();
		
		$$(\'#tbl_branch_access_setting button.btn_branch_profit_row_copy\').invoke(\'removeClassName\', \'btn_copied\');
		$(\'btn_branch_profit_row_copy-\'+bid).addClassName(\'btn_copied\');
	},
	// function when user click to paste branch profit
	add_branch_profit_row_paste_clicked: function(bid){
		if(!this.branch_profit_row_copied_object || this.branch_profit_row_copied_object.innerHTML.trim()==\'\'){
			alert(\'Nothing to paste, please copy same type of data first.\');
			return false;
		}
		
		var span_branch_profit_row_loading = $(\'span_branch_profit_row_loading-\'+bid);
		
		if(span_branch_profit_row_loading.style.display==\'\'){
			alert(\'There are another process still running, please wait. . .\');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$(\'tbody_copy_report_profit\').appendChild(this.branch_profit_row_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_report_profit).serialize();

		// clear the tbody
		$(\'tbody_copy_report_profit\').update(\'\');
		
		var new_row_no = this.get_branch_profit_max_row_no(bid)+1;	// get new row num
		sz += \'&new_row_no=\'+new_row_no;
		sz += \'&bid=\'+bid;
		
		// show loading icon
		$(span_branch_profit_row_loading).show();
		
		// send to server to clone
		ajax_request(phpself, {
			method: \'post\',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_profit_row_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_report_profit-\'+bid, ret[\'html\']);
						
						// init calendar for new pasted row
						for(var key in ret[\'row_no_list\']){
							if (ret[\'row_no_list\'].hasOwnProperty(key)) {
								THIS.init_calendar_for_branch_profit_row(bid, ret[\'row_no_list\'][key]);
								
								// check duplicate
								THIS.check_branch_profit_row_breakdown_duplicate(bid, ret[\'row_no_list\'][key]);
							}
						}
						
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function to check branch profit more % duplicate
	check_branch_profit_row_breakdown_duplicate: function(bid, row_no){
		var type_value_list = {\'SKU\': {}, \'CATEGORY\': {}};
		var tr_branch_profit_row_breakdown_list = $$(\'#tbody_branch_profit_row_breakdown-\'+bid+\'-\'+row_no+\' tr.tr_branch_profit_row_breakdown\');
		
		for(var i=0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_profit_row_breakdown_list[i].id.split(\'-\')[3];
			
			var t = this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+type_row_no+\'][type]\'].value;
			var v = this.f_vp[\'sales_report_profit_by_date[\'+bid+\'][\'+row_no+\'][profit_per_by_type][\'+type_row_no+\'][value]\'].value;
			
			if(!type_value_list[t][v])	type_value_list[t][v] = [];
			
			//if(in_array(v, type_value_list[t])){	// found duplicated
			//	alert(v);
			//}else{
				type_value_list[t][v].push(type_row_no);
				$(\'img_branch_profit_row_breakdown_duplicated_entry-\'+bid+\'-\'+row_no+\'-\'+type_row_no).hide();
			//}
		}
		
		for(var key1 in type_value_list){
			for(var key2 in type_value_list[key1]){
				if(type_value_list[key1][key2].length>1){
					for(var i=0; i<type_value_list[key1][key2].length; i++){
						var type_row_no = type_value_list[key1][key2][i];
						//alert(bid+\'-\'+row_no+\'-\'+type_row_no)
						$(\'img_branch_profit_row_breakdown_duplicated_entry-\'+bid+\'-\'+row_no+\'-\'+type_row_no).show();
					}
				}
			}
		}
	},
	// function when user click copy bonus other %
	add_branch_bonus_row_more_percent_copy_clicked: function(bid, y, m, row_no){
		if(!bid || !y || !m || !row_no)	return false;
		
		// copy html
		this.branch_bonus_row_more_percent_copied_object = $(\'tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no).cloneNode();
		
	
		$$(\'#tbl_branch_access_setting button.btn_add_branch_bonus_row_more_percent_copy\').invoke(\'removeClassName\', \'btn_copied\');
		$(\'btn_add_branch_bonus_row_more_percent_copy-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no).addClassName(\'btn_copied\');
	},
	// function when user click paste bonus other %
	add_branch_bonus_row_more_percent_paste_clicked: function(bid, y, m, row_no){
		if(!this.branch_bonus_row_more_percent_copied_object || this.branch_bonus_row_more_percent_copied_object.innerHTML.trim()==\'\'){
			alert(\'Nothing to paste, please copy same type of data first.\');
			return false;
		}
		
		var span_branch_bonus_row_breakdown_loading = $(\'span_branch_bonus_row_breakdown_loading-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no);
		
		if(span_branch_bonus_row_breakdown_loading.style.display==\'\'){
			alert(\'There are another process still running, please wait. . .\');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$(\'tbody_copy_branch_bonus_more_percent\').appendChild(this.branch_bonus_row_more_percent_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_branch_bonus_more_percent).serialize();

		// clear the tbody
		$(\'tbody_copy_branch_bonus_more_percent\').update(\'\');
		
		// get new row no
		var new_type_row_no = int(this.get_max_branch_bonus_row_breakdown_per_row(bid, row_no, y, m))+1;
		sz += \'&row_no=\'+row_no;
		sz += \'&new_type_row_no=\'+new_type_row_no;
		sz += \'&bid=\'+bid+\'&y=\'+y+\'&m=\'+m;
		
		$(span_branch_bonus_row_breakdown_loading).show();
		
		// send to server to clone
		ajex_request(phpself, {
			method: \'post\',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_bonus_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no, ret[\'html\']);
						
						// check duplicate
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
							
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function to check branch bonus other % duplicated
	check_branch_bonus_row_breakdown_duplicate: function(bid, y, m, tmp_row_no){
		var row_no_list = [];
		
		if(tmp_row_no){
			row_no_list.push(tmp_row_no);
		}else{
			// get all row no
			var tr_branch_bonus_row_list = $$(\'#tbody_branch_bonus-\'+bid+\'-\'+y+\'-\'+m+\' tr.tr_branch_bonus_row\');
		
			for(var i = 0; i<tr_branch_bonus_row_list.length; i++){
				var row_info = this.get_branch_bonus_info_by_ele(tr_branch_bonus_row_list[i]);
				row_no_list.push(int(row_info[\'row_no\']));
			}
		}

		for(var j=0; j<row_no_list.length; j++){
			var row_no = row_no_list[j];
			var type_value_list = {\'SKU\': {}, \'CATEGORY\': {}};
			var tr_branch_bonus_row_breakdown_list = $$(\'#tbody_branch_bonus_row_breakdown-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\' tr.tr_branch_bonus_row_breakdown\');
			
			for(var i=0; i<tr_branch_bonus_row_breakdown_list.length; i++){
				var type_row_no = tr_branch_bonus_row_breakdown_list[i].id.split(\'-\')[5];
				
				var t = this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+type_row_no+\'][type]\'].value;
				var v = this.f_vp[\'sales_bonus_by_step[\'+bid+\'][\'+y+\'][\'+m+\'][\'+row_no+\'][bonus_per_by_type][\'+type_row_no+\'][value]\'].value;
				
				if(!type_value_list[t][v])	type_value_list[t][v] = [];
				
				//if(in_array(v, type_value_list[t])){	// found duplicated
				//	alert(v);
				//}else{
					type_value_list[t][v].push(type_row_no);
					$(\'img_branch_bonus_row_breakdown_duplicated_entry-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\'-\'+type_row_no).hide();
				//}
			}
			
			for(var key1 in type_value_list){
				for(var key2 in type_value_list[key1]){
					if(type_value_list[key1][key2].length>1){
						for(var i=0; i<type_value_list[key1][key2].length; i++){
							var type_row_no = type_value_list[key1][key2][i];
							//alert(bid+\'-\'+row_no+\'-\'+type_row_no)
							$(\'img_branch_bonus_row_breakdown_duplicated_entry-\'+bid+\'-\'+y+\'-\'+m+\'-\'+row_no+\'-\'+type_row_no).show();
						}
					}
				}
			}
		}
		
	},
	add_branch_bonus_row_copy_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		// copy html
		this.branch_bonus_row_copied_object = $(\'tbody_branch_bonus-\'+bid+\'-\'+y+\'-\'+m).cloneNode();
		
		$$(\'#tbl_branch_access_setting button.btn_add_branch_bonus_row_copy\').invoke(\'removeClassName\', \'btn_copied\');
		$(\'btn_add_branch_bonus_row_copy-\'+bid+\'-\'+y+\'-\'+m).addClassName(\'btn_copied\');
	},
	add_branch_bonus_row_paste_clicked: function(bid, y, m){
		if(!this.branch_bonus_row_copied_object || this.branch_bonus_row_copied_object.innerHTML.trim()==\'\'){
			alert(\'Nothing to paste, please copy same type of data first.\');
			return false;
		}
		
		var span_branch_bonus_row_loading = $(\'span_branch_bonus_row_loading-\'+bid+\'-\'+y+\'-\'+m);
		
		if(span_branch_bonus_row_loading.style.display==\'\'){
			alert(\'There are another process still running, please wait. . .\');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$(\'tbody_copy_branch_bonus\').appendChild(this.branch_bonus_row_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_branch_bonus).serialize();

		// clear the tbody
		$(\'tbody_copy_branch_bonus\').update(\'\');
		
		var new_row_no = this.get_branch_bonus_max_row_no(bid, y, m)+1;	// get new row num
		sz += \'&new_row_no=\'+new_row_no;
		sz += \'&bid=\'+bid+\'&y=\'+y+\'&m=\'+m;
		
		// show loading icon
		$(span_branch_bonus_row_loading).show();
		
		// send to server to clone
		ajex_request(phpself, {
			method: \'post\',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
				$(span_branch_bonus_row_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						new Insertion.Bottom(\'tbody_branch_bonus-\'+bid+\'-\'+y+\'-\'+m, ret[\'html\']);	
						
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m);
										
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			}
		});
	}
}

var SKU_CAT_AUTOCOMPLETE = {
	f: undefined,
	use_for: \'\',
	return_parms: {},
	initialize: function(){
		this.f = document.f_choose_item_type;
		var THIS = this;
		
		// initial sku autocomplete
		reset_sku_autocomplete();
		
		// initial category autocomplete
		CAT_AUTOCOMPLETE_MAIN_2.initialize({\'max_level\': 10, \'no_findcat_expand\':1}, function(cat_id){
			THIS.add_cat_autocomplete_clicked(cat_id);
		});
	},
	// function to show the popup
	show: function(params){
		reset_sku_autocomplete();
		CAT_AUTOCOMPLETE_MAIN_2.reset();
		
		if(!params){
			alert(\'Please provide params\');
			return false;
		}
		
		// check need to show sku autocomplete or not
		if(params[\'hide_sku_autocomplete\'])	$(\'div_sku_autocomplete\').hide();
		else	$(\'div_sku_autocomplete\').show();
		
		// mark this autocomplete is use for which control
		this.use_for = params[\'use_for\'];
		this.return_parms = params[\'return_parms\'];	// use for later return callback
		
		if(!this.use_for){
			alert(\'Please specify this autocomplete is use for which control\');
		}
		
		curtain(true);
		
		center_div($(\'div_choose_item_type_dialog\').show());
		
		if(!params[\'hide_sku_autocomplete\'])	$(\'autocomplete_sku\').focus();
		else	$(\'inp_search_cat_autocomplete_2\').focus();
	},
	// function when user click add sku
	add_sku_autocomplete_clicked: function(){
		var sid = $(\'sku_item_id\').value;
		
		if(!sid){
			alert(\'Please search and select 1 sku first.\');
			return false;
		}
		
		var params = this.return_parms;
		params[\'type\'] = \'SKU\';
		params[\'value\'] = sid;
		
		this.perform_add_autocomplete(params);
	},
	// function when user click add category
	add_cat_autocomplete_clicked: function(cat_id){
		if(!cat_id){
			alert(\'Please search and select 1 category first.\');
			return false;
		}
		
		var params = this.return_parms;
		params[\'type\'] = \'CATEGORY\';
		params[\'value\'] = cat_id;
		
		this.perform_add_autocomplete(params);
	},
	perform_add_autocomplete: function(params){
		if(this.use_for == \'report_profit\')	VENDOR_PORTAL.add_branch_profit_row_more_percent(params);
		else	VENDOR_PORTAL.add_branch_bonus_row_more_percent(params);
		
		default_curtain_clicked();
	}
}

function add_autocomplete(){
	SKU_CAT_AUTOCOMPLETE.add_sku_autocomplete_clicked();
}

'; ?>

</script>

<h1><?php echo $this->_tpl_vars['PAGE_TITLE']; ?>
</h1>

<?php if ($this->_tpl_vars['err']): ?>
	The following error(s) has occured:
	<ul class="err">
		<?php $_from = $this->_tpl_vars['err']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['e']):
?>
			<li> <?php echo $this->_tpl_vars['e']; ?>
</li>
		<?php endforeach; endif; unset($_from); ?>
	</ul>
<?php endif; ?>

<?php if (! $this->_tpl_vars['err'] && $this->_tpl_vars['form']): ?>
	<!-- choose sku or category DIALOG -->
	<div id="div_choose_item_type_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:650px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
		<div id="div_choose_item_type_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_choose_item_type_dialog_header">Choose Item Type</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_choose_item_type_dialog_content" style="padding:2px;">
			<form name="f_choose_item_type" onSubmit="return false" method="post">				
				<div id="div_sku_autocomplete">
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'sku_items_autocomplete.tpl', 'smarty_include_vars' => array('parent_form' => 'document.f_choose_item_type')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</div>
				
				<br />
				
				<div id="div_cat_autocomplete">
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'category_autocomplete2.tpl', 'smarty_include_vars' => array('parent_form' => 'document.f_choose_item_type','ext' => '_2')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</div>
			</form>
		</div>
	</div>
	<!-- End of choose sku or category DIALOG -->

	<h2><?php if ($this->_tpl_vars['form']['code']): ?><?php echo $this->_tpl_vars['form']['code']; ?>
 - <?php endif; ?><?php echo $this->_tpl_vars['form']['description']; ?>
</h2>
	
	<table style="display:none;">								
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_profit_row.tpl", 'smarty_include_vars' => array('bid' => '__TMP_BID__','row_no' => '__TMP_ROW_NO__')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_bonus_row.tpl", 'smarty_include_vars' => array('bid' => '__TMP_BID__','y' => '__TMP_YEAR__','m' => '__TMP_MONTH__','row_no' => '__TMP_ROW_NO__')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
	</table>
	
	<div style="display:none;">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_bonus_table.tpl", 'smarty_include_vars' => array('bid' => '__TMP_BID__','y' => '__TMP_YEAR__','m' => '__TMP_MONTH__')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
				<form name="f_copy_report_profit_more_percent">
			<input type="hidden" name="a" value="ajax_copy_report_profit_more_percent" />
			<table>
				<tbody id="tbody_copy_report_profit_more_percent">
				
				</tbody>
			</table>
		</form>
		
				<form name="f_copy_report_profit">
			<input type="hidden" name="a" value="ajax_copy_report_profit" />
			<table>
				<tbody id="tbody_copy_report_profit">
				
				</tbody>
			</table>
		</form>
		
				<form name="f_copy_branch_bonus_more_percent">
			<input type="hidden" name="a" value="ajax_copy_branch_bonus_more_percent" />
			<table>
				<tbody id="tbody_copy_branch_bonus_more_percent">
				
				</tbody>
			</table>
		</form>
		
				<form name="f_copy_branch_bonus">
			<input type="hidden" name="a" value="ajax_copy_branch_bonus" />
			<table>
				<tbody id="tbody_copy_branch_bonus">
				
				</tbody>
			</table>
		</form>
	</div>
	
	<form name="f_vp" onSubmit="return false;">
		<input type="hidden" name="a" value="ajax_update_vendor_portal" />
		<input type="hidden" name="vendor_id" value="<?php echo $this->_tpl_vars['form']['id']; ?>
" />
		
		<table>
			<!-- Active -->
			<tr>
				<td width="200"><b>Active</b>
					[<a href="javascript:void(alert('Activate to allow vendor to access Vendor Portal'));">?</a>]
				</td>
				<td>
					<input type="radio" name="active_vendor_portal" value="1" <?php if ($this->_tpl_vars['form']['active_vendor_portal']): ?>checked <?php endif; ?> onChange="VENDOR_PORTAL.active_vendor_portal_changed();" /> Yes
					<input type="radio" name="active_vendor_portal" value="0" <?php if (! $this->_tpl_vars['form']['active_vendor_portal']): ?>checked <?php endif; ?> onChange="VENDOR_PORTAL.active_vendor_portal_changed();" /> No
					<span style="color:red;<?php if ($this->_tpl_vars['form']['active_vendor_portal']): ?>display:none;<?php endif; ?>" id="span_active_vendor_portal_msg">
						(Vendor will not able to login even got login ticket)
					</span>
				</td>
			</tr>
			
						<tr>
				<td><b>Start Date</b></td>
				<td>
					<input type="text" name="start_date" id="inp_start_date" size="12" value="<?php echo $this->_tpl_vars['form']['start_date']; ?>
" title="Start Date" class="inp_start_date" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_start_date" style="cursor: pointer;" title="Select Date" />
				</td>
			</tr>
		</table>	
			
		<br />
		<b>Branch Access Settings</b>
					<table width="100%" cellpadding="0" cellspacing="0" class="report_table" style="background-color:#fff;" id="tbl_branch_access_setting">
				<tr class="header">
					<th><input type="checkbox" id="inp_toggle_all_allowed_branches" onchange="VENDOR_PORTAL.toggle_all_allowed_branches();" /></th>
					<th>SKU Group</th>
					<th>Login<br />Ticket</th>
					<th>Expire Date</th>
				</tr>
				<?php $_from = $this->_tpl_vars['branches_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fb'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fb']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['b']):
        $this->_foreach['fb']['iteration']++;
?>
					<tbody class="tbody_allowed_branches">
						<tr class="header">
							<td valign="top" colspan="5"><?php echo $this->_tpl_vars['b']['code']; ?>
</td>
						</tr>
						<tr class="tr_allowed_branches">
							<td valign="top" rowspan="3"><input type="checkbox" name="allowed_branches[<?php echo $this->_tpl_vars['bid']; ?>
]" value="<?php echo $this->_tpl_vars['bid']; ?>
" id="inp_allowed_branches-<?php echo $this->_tpl_vars['bid']; ?>
" class="inp_allowed_branches" <?php if ($this->_tpl_vars['form']['allowed_branches'][$this->_tpl_vars['bid']]): ?>checked <?php endif; ?> /></td>
							
							<!-- sku group -->
							<td valign="top">
								<select name="sku_group_info[<?php echo $this->_tpl_vars['bid']; ?>
]" style="width:250px">
									<option value="">-- Please Select SKU Group --</option>
									<?php $_from = $this->_tpl_vars['sku_group_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sg']):
?>
										<?php ob_start(); ?><?php echo $this->_tpl_vars['sg']['branch_id']; ?>
|<?php echo $this->_tpl_vars['sg']['sku_group_id']; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('sg_id', ob_get_contents());ob_end_clean(); ?>
										<option value="<?php echo $this->_tpl_vars['sg_id']; ?>
" <?php if ($this->_tpl_vars['form']['sku_group_info'][$this->_tpl_vars['bid']] == $this->_tpl_vars['sg_id']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['sg']['code']; ?>
 - <?php echo $this->_tpl_vars['sg']['description']; ?>
</option>
									<?php endforeach; endif; unset($_from); ?>
								</select>
							</td>
							
							<!-- Login Ticket -->
							<td nowrap>
								<input type="text" name="login_ticket[<?php echo $this->_tpl_vars['bid']; ?>
]" value="<?php echo $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['login_ticket']; ?>
" size="12" readonly="" />
								<span id="span_clone_ticket-<?php echo $this->_tpl_vars['bid']; ?>
" style="<?php if (! $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['login_ticket']): ?>display:none;<?php endif; ?>">
									<img src="/ui/icons/application_tile_vertical.png" title="Use this ticket for all branches" align="absmiddle" class="clickable" onClick="VENDOR_PORTAL.clone_ticket('<?php echo $this->_tpl_vars['bid']; ?>
')" />
								</span>
								<br />
								<br />
								<input class="btn btn-primary" type="button" id="btn_generate_ticket-<?php echo $this->_tpl_vars['bid']; ?>
" value="<?php if ($this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['login_ticket']): ?>Clear<?php else: ?>Generate<?php endif; ?>" onClick="VENDOR_PORTAL.generate_ticket_clicked('<?php echo $this->_tpl_vars['bid']; ?>
');" style="width:100px;" />
							</td>
							
							<!-- Expire Date -->
							<td valign="top">
								<span id="span_expire_date-<?php echo $this->_tpl_vars['bid']; ?>
" style="<?php if ($this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['expire_date'] == '9999-12-31'): ?>display:none;<?php endif; ?>">
									<?php $this->assign('default_expire_date', time()+31536000); ?>
									<?php if ($this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['expire_date'] == '9999-12-31'): ?>
										<?php $this->assign('expire_date', $this->_tpl_vars['default_expire_date']); ?>
									<?php else: ?>
										<?php $this->assign('expire_date', $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['expire_date']); ?>
									<?php endif; ?>
									<?php if (! $this->_tpl_vars['expire_date']): ?><?php $this->assign('expire_date', $this->_tpl_vars['default_expire_date']); ?><?php endif; ?>
									
									<input type="text" name="expire_date[<?php echo $this->_tpl_vars['bid']; ?>
]" id="inp_expire_date-<?php echo $this->_tpl_vars['bid']; ?>
" size="12" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['expire_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
" title="Expire Date" readonly class="inp_expire_date" />
									<img align="absmiddle" src="ui/calendar.gif" id="img_expire_date-<?php echo $this->_tpl_vars['bid']; ?>
" style="cursor: pointer;" title="Select Date" />
									<br />
								</span>
								
								<input type="checkbox" name="no_expire[<?php echo $this->_tpl_vars['bid']; ?>
]" value="1" <?php if ($this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['expire_date'] == '9999-12-31'): ?>checked <?php endif; ?> id="inp_no_expire-<?php echo $this->_tpl_vars['bid']; ?>
" onChange="VENDOR_PORTAL.toggle_no_expire('<?php echo $this->_tpl_vars['bid']; ?>
');" /> No Expire Date
							</td>
						</tr>
						<tr>
														<td>
								<b> Email [<a href="javascript:void(alert('You can enter multiple email separate by \',\'. Sample:\n==================\nadmin@example.com,user@example.com'))">?</a>]:</b>
								<input type="text" name="contact_email[<?php echo $this->_tpl_vars['bid']; ?>
]" value="<?php echo $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['contact_email']; ?>
" style="width:300px;" maxlength="200" />
							</td>
							
														<td colspan="2">
								<b>Link to Debtor: </b>
								<select name="link_debtor_id[<?php echo $this->_tpl_vars['bid']; ?>
]">
									<option value="">-- No Link --</option>
									<?php $_from = $this->_tpl_vars['debtor_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['debtor_id'] => $this->_tpl_vars['r']):
?>
										<option value="<?php echo $this->_tpl_vars['debtor_id']; ?>
" <?php if ($this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['link_debtor_id'] == $this->_tpl_vars['debtor_id']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['r']['code']; ?>
 - <?php echo $this->_tpl_vars['r']['description']; ?>
</option>
									<?php endforeach; endif; unset($_from); ?>
								</select>
							</td>
						</tr>
						
						<tr>
														<td valign="top">
								<b>Report Profit:</b>
								<br />
								
								<table id="tbl_report_profit-<?php echo $this->_tpl_vars['bid']; ?>
" class="tbl_report_profit report_table" cellpadding="2" cellspacing="0">
									<tr class="tr_header2">
										<th width="20">&nbsp;</th>
										<th width="120">Date To</th>
										<th width="50">% [<a href="javascript:void(alert('This % use for all category all sku.'))">?</a>]</th>
										<th>Other % [<a href="javascript:void(alert('This % can use to assign sepcified rate for certain category and sku. \n* Please note global % will still be calculate and may cause overlaped result in total %.'))">?</a>]</th>
									</tr>
									
									<tbody id="tbody_branch_report_profit-<?php echo $this->_tpl_vars['bid']; ?>
">									
										<?php $_from = $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['sales_report_profit_by_date']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fprofit'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fprofit']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['profit_data']):
        $this->_foreach['fprofit']['iteration']++;
?>
											<?php $this->assign('row_no', $this->_foreach['fprofit']['iteration']); ?>
											
											<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_profit_row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
										<?php endforeach; endif; unset($_from); ?>
									</tbody>
								</table>
								<br />
								<button class="btn btn-primary" onClick="VENDOR_PORTAL.add_branch_profit_row_clicked('<?php echo $this->_tpl_vars['bid']; ?>
');">+</button>
								<button class="btn btn-primary" onClick="VENDOR_PORTAL.add_branch_profit_row_copy_clicked('<?php echo $this->_tpl_vars['bid']; ?>
');" id="btn_branch_profit_row_copy-<?php echo $this->_tpl_vars['bid']; ?>
" class="btn_branch_profit_row_copy">Copy</button>
								<button class="btn btn-success" onClick="VENDOR_PORTAL.add_branch_profit_row_paste_clicked('<?php echo $this->_tpl_vars['bid']; ?>
');" class="btn_branch_profit_row_paste">Paste</button>
		
								<span id="span_branch_profit_row_loading-<?php echo $this->_tpl_vars['bid']; ?>
" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
							</td>
							
														<td colspan="2" valign="top">
								<b>Bonus:</b>
								
								Year <input type="text" size="4" id="inp_branch_bonus_y-<?php echo $this->_tpl_vars['bid']; ?>
" onChange="miz(this);" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
" /> 
								Month <input type="text" size="4" id="inp_branch_bonus_m-<?php echo $this->_tpl_vars['bid']; ?>
" onChange="miz(this);" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m")); ?>
" />
								<button class="btn btn-primary" onClick="VENDOR_PORTAL.add_new_branch_bonus_group_clicked('<?php echo $this->_tpl_vars['bid']; ?>
');">Add Monthly Bonus Group</button>
								<div id="div_branch_bonus_group_list-<?php echo $this->_tpl_vars['bid']; ?>
">
																		<?php $_from = $this->_tpl_vars['form']['branch_info'][$this->_tpl_vars['bid']]['sales_bonus_by_step']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['y'] => $this->_tpl_vars['m_bonus_list']):
?>
																				<?php $_from = $this->_tpl_vars['m_bonus_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m'] => $this->_tpl_vars['bonus_data_list']):
?>
											<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_bonus_table.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
										<?php endforeach; endif; unset($_from); ?>	
									<?php endforeach; endif; unset($_from); ?>
								</div>								
							</td>
						</tr>
						
						<?php if (! ($this->_foreach['fb']['iteration'] == $this->_foreach['fb']['total'])): ?>
							<tr class="tr_split_row">
								<td colspan="5">&nbsp;</td>
							</tr>
						<?php endif; ?>
					</tbody>
				<?php endforeach; endif; unset($_from); ?>
			</table>
				
			<p align="center" id="p_action_button">
				<input class="btn btn-success" type="button" value="Save" onClick="VENDOR_PORTAL.update_clicked();" id="btn_update_vendor_portal" />
			</p>
	</form>
	
	<script type="text/javascript">
		VENDOR_PORTAL.initialize();
	</script>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>