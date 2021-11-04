{*
11/15/2012 12:03 PM Andy
- Add Report Profit other % (category and sku).

12/4/2012 11:08 AM Andy
- Add "Start Date".

12/17/2012 4:50 PM Andy
- Add can copy/paste report profit other %.
- Add can copy/paste repot profit whole table.
- Add checking to duplicated branch profit other %.
- Add can copy/paste bonus other %.
- Add can copy/paste bonus table.
- Add checking to duplicated branch bonus other %.

12/27/2012 4:38 PM Andy
- Fix if paste branch profit or bonus, it dont have duplicate notification icon.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

06/26/20 4:00 PM Sheila
- Updated button css
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


<style>
{literal}

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

{/literal}
</style>


<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var VENDOR_PORTAL = {
	f_vp: undefined,
	is_updating: false,
	branch_profit_row_breakdown_copied_object: undefined,
	branch_profit_row_copied_object: undefined,
	branch_bonus_row_more_percent_copied_object: undefined,
	branch_bonus_row_copied_object: undefined,
	initialize: function(){
		this.initial_f_vp();
		
		//new Draggable('div_vendor_portal',{ handle: 'div_vendor_portal_header'});	
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
		
		new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	},
	// function to init calendar event for branch profit row
	init_calendar_for_branch_profit_row: function(bid, row_no){
		Calendar.setup({
		    inputField     :    "inp_profit_date_to-"+bid+'-'+row_no,     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_profit_date_to-"+bid+'-'+row_no,  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
	},
	close: function(){
		window.close();
	},
	// function when user toggle allow all branches checkbox
	toggle_all_allowed_branches: function(){
		var c = $('inp_toggle_all_allowed_branches').checked;
		
		$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
			inp.checked = c;
		});	
	},
	// function when user toggle no expire checkbox
	toggle_no_expire: function(bid){
		var c = $('inp_no_expire-'+bid).checked;
		
		if(c){
			$('span_expire_date-'+bid).hide();
		}else{
			$('span_expire_date-'+bid).show();
		}
	},
	// function when user change active status
	active_vendor_portal_changed: function(){
		var active = int(getRadioValue(this.f_vp['active_vendor_portal']));
		
		var span_active_vendor_portal_msg = $('span_active_vendor_portal_msg');
		if(active){
			$(span_active_vendor_portal_msg).hide();
		}else{
			$(span_active_vendor_portal_msg).show();
		}
	},
	// function when user click generate ticket
	generate_ticket_clicked: function(bid){
		var ticket = this.f_vp['login_ticket['+bid+']'].value;
		
		if(ticket){
			this.clear_ticket(bid);
		}else{
			this.generate_ticket(bid);
		}
	},
	// function to clear ticket
	clear_ticket: function(bid){
		this.f_vp['login_ticket['+bid+']'].value = '';
		$('btn_generate_ticket-'+bid).value = 'Generate';
		
		$('span_clone_ticket-'+bid).hide();
	},
	// function to generate new ticket
	generate_ticket: function(bid, use_this_ticket){
		var alpha_list = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var ticket_length = 10;
		var ticket = '';
		
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
		this.f_vp['login_ticket['+bid+']'].value = ticket;
		$('btn_generate_ticket-'+bid).value = 'Clear';
		
		$('span_clone_ticket-'+bid).show();
	},
	// function to validate form 
	check_form: function(){
		/*if(this.f_vp['login_ticket'].value != ''){	// got ticket
			var checked_count = 0;
			$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
				if(inp.checked)	checked_count++;
			});
			if(checked_count<=0){
				alert('Please tick at least 1 allowed branch.');
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
				if(!this.f_vp['sku_group_info['+bid+']'].value){
					alert('Please select SKU Group for all ticked branches.');
					return false;
				}
				
				// login ticket
				if(!this.f_vp['login_ticket['+bid+']'].value){	// found no give login ticket
					alert('Please generate ticket for all ticked branches.');
					return false;
				}
			}
		}
		
		if(!check_required_field(this.f_vp))	return false;
		
		// check duplicate branch profit other %
		var img_branch_profit_row_breakdown_duplicated_entry_list = $$('#tbl_branch_access_setting img.img_branch_profit_row_breakdown_duplicated_entry');
		for(var i=0; i<img_branch_profit_row_breakdown_duplicated_entry_list.length; i++){
			if(img_branch_profit_row_breakdown_duplicated_entry_list[i].style.display==''){	// got duplicate
				var id_info = img_branch_profit_row_breakdown_duplicated_entry_list[i].id.split('-');
				var bid = id_info[1];
				var row_no = id_info[2];
				var type_row_no = id_info[3];
				
				alert('Found duplicate on branch profit.');
				this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+type_row_no+'][per]'].focus();
				return false;
			}
		}
		
		// check duplicate branch bonus other %
		var img_branch_bonus_row_breakdown_duplicated_entry_list = $$('#tbl_branch_access_setting img.img_branch_bonus_row_breakdown_duplicated_entry');
		for(var i=0; i<img_branch_bonus_row_breakdown_duplicated_entry_list.length; i++){
			if(img_branch_bonus_row_breakdown_duplicated_entry_list[i].style.display==''){	// got duplicate
				var id_info = img_branch_bonus_row_breakdown_duplicated_entry_list[i].id.split('-');
				var bid = id_info[1];
				var y = id_info[2];
				var m = id_info[3];
				var row_no = id_info[4];
				var type_row_no = id_info[5];
				
				alert('Found duplicate on branch bonus.');
				this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+type_row_no+'][per]'].focus();
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
		$$('#p_action_button input').invoke('disable');
		$('btn_update_vendor_portal').value = 'Saving . . .';
		
		this.is_updating = true;
		
		var params = $(this.f_vp).serialize();
		ajax_request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    THIS.is_updating = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Update Successfully');
						THIS.close();
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
			    $$('#p_action_button input').invoke('enable');
			    $('btn_update_vendor_portal').value = 'Save';
			}
		});
	},
	// function to clone ticket for all branches
	clone_ticket: function(bid){
		var ticket = this.f_vp['login_ticket['+bid+']'].value.trim();
		if(!ticket)	return false;	// no ticket to clone
		
		if(!confirm('Are you sure to clone this ticket to all allowed branches?'))	return false;
		
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
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_branch_profit_row')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var row_num = int(parent_ele.id.split('-')[2]);
		return row_num;
	},
	// function to get current max row no at branch profit
	get_branch_profit_max_row_no: function(bid){
		var max_row_no = 0;
		var tr_branch_profit_row_list = $$('#tbody_branch_report_profit-'+bid+' tr.tr_branch_profit_row');
		
		for(var i = 0; i<tr_branch_profit_row_list.length; i++){
			var tmp_row_no = this.get_branch_profit_row_no_by_ele(tr_branch_profit_row_list[i]);
			
			if(tmp_row_no > max_row_no)	max_row_no = tmp_row_no;
		}

		return max_row_no;
	},
	// function when user click to add new branch profit row
	add_branch_profit_row_clicked: function(bid){
		if(!bid)	return false;
		
		var new_tr = $('tr_branch_profit_row-__TMP_BID__-__TMP_ROW_NO__').cloneNode(true);
		
		var new_row_no = this.get_branch_profit_max_row_no(bid)+1;	// get new row num
		
		new_tr.id = "tr_branch_profit_row-"+bid+'-'+new_row_no;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_no);
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		$(new_tr).update(new_html);
		
		$('tbody_branch_report_profit-'+bid).appendChild(new_tr);
		
		this.init_calendar_for_branch_profit_row(bid, new_row_no);
	},
	// function when user click delete branch profit row
	delete_branch_profit_row_clicked: function(bid, row_no){
		if(!bid || !row_no)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_branch_profit_row-'+bid+'-'+row_no).remove();
	},
	// function when user click add new branch bonus group
	add_new_branch_bonus_group_clicked: function(bid){
		if(!bid)	return false;
		
		var y = int($('inp_branch_bonus_y-'+bid).value);
		var m = int($('inp_branch_bonus_m-'+bid).value);
		
		if(y<2010){
			alert('The minimum year is 2010');
			return false;
		}
		if(m<=0 || m>12){
			alert('Invalid month, must between 1 to 12');
			return false;
		}
		
		if($("tbl_branch_bonus-"+bid+'-'+y+'-'+m)){
			alert('Year '+y+' Month '+m+' already exisys');
			return false;
		}
		
		var new_div = $('div_branch_bonus-__TMP_BID__-__TMP_YEAR__-__TMP_MONTH__').cloneNode(true);
		
		new_div.id = "div_branch_bonus-"+bid+'-'+y+'-'+m;	// change row id
		
		// get row html
		new_html = new_div.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		new_html = new_html.replace(/__TMP_YEAR__/g, y);
		new_html = new_html.replace(/__TMP_MONTH__/g, m);
		$(new_div).update(new_html);
		
		$('div_branch_bonus_group_list-'+bid).appendChild(new_div);
	},
	
	///////// need check ///////
	// function to get branch row no by ele
	get_branch_bonus_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_branch_bonus_row')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split('-');
		var row_info = {
			'y': int(tmp[2]),
			'm': int(tmp[3]),
			'row_no': int(tmp[4])
		}

		return row_info;
	},
	// function to get max branch bonus row
	get_branch_bonus_max_row_no: function(bid, y, m){
		var max_row_no = 0;
		var tr_branch_bonus_row_list = $$('#tbody_branch_bonus-'+bid+'-'+y+'-'+m+' tr.tr_branch_bonus_row');
		
		for(var i = 0; i<tr_branch_bonus_row_list.length; i++){
			var row_info = this.get_branch_bonus_info_by_ele(tr_branch_bonus_row_list[i]);
			var tmp_row_no = int(row_info['row_no']);
			
			if(tmp_row_no > max_row_no)	max_row_no = tmp_row_no;
		}

		return max_row_no;
	},
	// function when user click add branch bonus row
	add_branch_bonus_row_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		var new_tr = $('tr_branch_bonus_row-__TMP_BID__-__TMP_YEAR__-__TMP_MONTH__-__TMP_ROW_NO__').cloneNode(true);
		
		var new_row_no = this.get_branch_bonus_max_row_no(bid, y, m)+1;	// get new row num
		
		new_tr.id = "tr_branch_bonus_row-"+bid+'-'+y+'-'+m+'-'+new_row_no;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_no);
		new_html = new_html.replace(/__TMP_BID__/g, bid);
		new_html = new_html.replace(/__TMP_YEAR__/g, y);
		new_html = new_html.replace(/__TMP_MONTH__/g, m);
		$(new_tr).update(new_html);
		
		$('tbody_branch_bonus-'+bid+'-'+y+'-'+m).appendChild(new_tr);
	},
	// function when user click delete branch bonus row
	delete_branch_bonus_row_clicked: function(bid, y, m, row_no){
		if(!bid || !row_no)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_branch_bonus_row-'+bid+'-'+y+'-'+m+'-'+row_no).remove();
	},
	// function when user click delete branch bonus group
	delete_branch_bonus_group_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('div_branch_bonus-'+bid+'-'+y+'-'+m).remove();
	},
	// function when user click add more breakdown % for report profit
	add_branch_profit_row_more_percent_clicked: function(bid, row_no){
		var params = {
			'use_for': 'report_profit',
			'return_parms': {'bid': bid, 'row_no':row_no}
		};
		
		SKU_CAT_AUTOCOMPLETE.show(params);
	},
	// function when user click add more breakdown % for bonus
	add_branch_bonus_row_more_percent_clicked: function(bid, y, m, row_no){
		var params = {
			'use_for': 'bonus',
			'hide_sku_autocomplete': 1,
			'return_parms': {'bid': bid, 'y':y, 'm':m, 'row_no':row_no}
		};
		
		SKU_CAT_AUTOCOMPLETE.show(params);
	},
	// function to get current max branch profit row breakdown row
	get_max_branch_profit_row_breakdown_per_row: function(bid, row_no){
		var max_type_row_no = 0;
		
		var tr_branch_profit_row_breakdown_list = $$('#tbody_branch_profit_row_breakdown-'+bid+'-'+row_no+' tr.tr_branch_profit_row_breakdown');

		for(var i = 0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var row_info = this.get_branch_profit_row_breakdown_row_info_by_ele(tr_branch_profit_row_breakdown_list[i]);
			var type_row_no = int(row_info['type_row_no']);
			
			if(type_row_no > max_type_row_no)	max_type_row_no = type_row_no;
		}

		return max_type_row_no;
	},
	// function to get current max branch bonus row breakdown row
	get_max_branch_bonus_row_breakdown_per_row: function(bid, row_no, y, m){
		var max_type_row_no = 0;		

		var tr_branch_bonus_row_breakdown_list = $$('#tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no+' tr.tr_branch_bonus_row_breakdown');

		for(var i = 0; i<tr_branch_bonus_row_breakdown_list.length; i++){
			var row_info = this.get_branch_bonus_row_breakdown_row_info_by_ele(tr_branch_bonus_row_breakdown_list[i]);
			var type_row_no = int(row_info['type_row_no']);
			
			if(type_row_no > max_type_row_no)	max_type_row_no = type_row_no;
		}

		return max_type_row_no;
	},
	// function to get breakdown row % profit row info
	get_branch_profit_row_breakdown_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_branch_profit_row_breakdown')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split('-');
		var row_info = {
			'row_no': int(tmp[2]),
			'type_row_no': int(tmp[3])
		}

		return row_info;
	},
	// function to get breakdown row % bonus row info
	get_branch_bonus_row_breakdown_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_branch_bonus_row_breakdown')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var tmp = parent_ele.id.split('-');
		var row_info = {
			'y': int(tmp[2]),
			'm': int(tmp[3]),
			'row_no': int(tmp[4]),
			'type_row_no': int(tmp[5])
		}

		return row_info;
	},
	// function when user click delete more breakdown % profit
	deleie_tr_branch_profit_row_breakdown: function(bid, row_no, type_row_no){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_branch_profit_row_breakdown-'+bid+'-'+row_no+'-'+type_row_no).remove();
		
		// check duplicate
		this.check_branch_profit_row_breakdown_duplicate(bid, row_no);
	},
	deleie_tr_branch_bonus_row_breakdown: function(bid, y, m, row_no, type_row_no){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no+'-'+type_row_no).remove();
		
		this.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
	},
	// function to check whether got duplicated data for profit
	get_branch_profit_row_breakdown_duplicated_type_row_no: function(bid, row_no, type, value){
		var duplicated_type_row_no = 0;
		var tr_branch_profit_row_breakdown_list = $$('#tbody_branch_profit_row_breakdown-'+bid+'-'+row_no+' tr.tr_branch_profit_row_breakdown');
		
		for(var i=0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_profit_row_breakdown_list[i].id.split('-')[3];
			
			if(this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+type_row_no+'][type]'].value == type && this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+type_row_no+'][value]'].value == value){
				duplicated_type_row_no = type_row_no;
				break;
			}
		}
		
		return duplicated_type_row_no;
	},
	// function to check whether got duplicated data for bonus
	get_branch_bonus_row_breakdown_duplicated_type_row_no: function(bid, row_no, y, m, type, value){
		var duplicated_type_row_no = 0;
		var tr_branch_bonus_row_breakdown_list = $$('#tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no+' tr.tr_branch_bonus_row_breakdown');
		
		for(var i=0; i<tr_branch_bonus_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_bonus_row_breakdown_list[i].id.split('-')[5];
			
			if(this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+type_row_no+'][type]'].value == type && this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+type_row_no+'][value]'].value == value){
				duplicated_type_row_no = type_row_no;
				break;
			}
		}
		
		return duplicated_type_row_no;
	},
	// function to add more breakdown % profit
	add_branch_profit_row_more_percent: function(params){
		if(!params['type'] && !params['value']){
			alert('No data to be add.');
			return false;
		}
		
		var THIS = this;
		var bid = params['bid'];
		var row_no = params['row_no'];
		
		var span_branch_profit_row_breakdown_loading = $('span_branch_profit_row_breakdown_loading-'+bid+'-'+row_no);
		
		if(span_branch_profit_row_breakdown_loading.style.display==''){
			alert('There are another process still running, please wait. . .');
			return false;
		}
		
		var new_type_row_no = int(this.get_max_branch_profit_row_breakdown_per_row(bid, row_no))+1;
		
		// check not allow duplicate
		var duplicated_type_row_no = this.get_branch_profit_row_breakdown_duplicated_type_row_no(bid, row_no, params['type'], params['value'])
		if(duplicated_type_row_no>0){
			alert('Found duplicated entry. The row already exists.');
			this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+duplicated_type_row_no+'][per]'].focus();
			return false;
		}
		
		params['a'] = 'ajax_add_report_profit_breakdown_per';
		params['new_type_row_no'] = new_type_row_no;
		
		$(span_branch_profit_row_breakdown_loading).show();
		
		ajax_request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_profit_row_breakdown_loading).hide();
							
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_profit_row_breakdown-'+bid+'-'+row_no, ret['html']);
						
						// check duplicate
						THIS.check_branch_profit_row_breakdown_duplicate(bid, row_no);
						
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
	// function to add more breakdown % bonus
	add_branch_bonus_row_more_percent: function(params){
		if(!params['type'] && !params['value']){
			alert('No data to be add.');
			return false;
		}
		var THIS = this;
		var bid = params['bid'];
		var row_no = params['row_no'];
		var y = params['y'];
		var m = params['m'];
		var new_type_row_no = int(this.get_max_branch_bonus_row_breakdown_per_row(bid, row_no, y, m))+1;
		
		// check not allow duplicate
		var duplicated_type_row_no = this.get_branch_bonus_row_breakdown_duplicated_type_row_no(bid, row_no, y, m, params['type'], params['value'])
		if(duplicated_type_row_no>0){
			alert('Found duplicated entry. The row already exists.');
			this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+duplicated_type_row_no+'][per]'].focus();
			return false;
		}
		
		params['a'] = 'ajax_add_branch_bonus_breakdown_per';
		params['new_type_row_no'] = new_type_row_no;
		
		var span_branch_bonus_row_breakdown_loading = $('span_branch_bonus_row_breakdown_loading-'+bid+'-'+y+'-'+m+'-'+row_no).show();
		
		ajax_request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_bonus_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no, ret['html']);
						
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
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
	// function when user click copy profit row
	add_branch_profit_row_more_percent_copy_clicked: function(bid, row_no){
		if(!bid || !row_no)	return false;
		
		// clone object
		this.branch_profit_row_breakdown_copied_object = $('tbody_branch_profit_row_breakdown-'+bid+'-'+row_no).cloneNode();
				
		$$('#tbl_branch_access_setting button.btn_add_branch_profit_row_more_percent_copy').invoke('removeClassName', 'btn_copied');
		$('btn_add_branch_profit_row_more_percent_copy-'+bid+'-'+row_no).addClassName('btn_copied');
		//alert('Data Copied');
	},
	// function when user click paste profit row
	add_branch_profit_row_more_percent_paste_clicked: function(bid, row_no){
		if(!this.branch_profit_row_breakdown_copied_object || this.branch_profit_row_breakdown_copied_object.innerHTML.trim()==''){
			alert('Nothing to paste, please copy same type of data first.');
			return false;
		}
		
		var span_branch_profit_row_breakdown_loading = $('span_branch_profit_row_breakdown_loading-'+bid+'-'+row_no);
		
		if(span_branch_profit_row_breakdown_loading.style.display==''){
			alert('There are another process still running, please wait. . .');
			return false;
		}
		var THIS = this;

		// update the copied object into tbody
		$('tbody_copy_report_profit_more_percent').appendChild(this.branch_profit_row_breakdown_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_report_profit_more_percent).serialize();

		// clear the tbody
		$('tbody_copy_report_profit_more_percent').update('');
		
		// get new row no
		var new_type_row_no = int(this.get_max_branch_profit_row_breakdown_per_row(bid, row_no))+1;
		sz += '&row_no='+row_no;
		sz += '&new_type_row_no='+new_type_row_no;
		sz += '&bid='+bid;
		
		// show loading icon
		$(span_branch_profit_row_breakdown_loading).show();
		
		// send to server to clone
		ajax_request(phpself, {
			method: 'post',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_profit_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_profit_row_breakdown-'+bid+'-'+row_no, ret['html']);
						
						// check duplicate
						THIS.check_branch_profit_row_breakdown_duplicate(bid, row_no);
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
	// function when user click to copy branch profit
	add_branch_profit_row_copy_clicked: function(bid){
		if(!bid)	return false;
		
		// copy html
		this.branch_profit_row_copied_object = $('tbody_branch_report_profit-'+bid).cloneNode();
		
		$$('#tbl_branch_access_setting button.btn_branch_profit_row_copy').invoke('removeClassName', 'btn_copied');
		$('btn_branch_profit_row_copy-'+bid).addClassName('btn_copied');
	},
	// function when user click to paste branch profit
	add_branch_profit_row_paste_clicked: function(bid){
		if(!this.branch_profit_row_copied_object || this.branch_profit_row_copied_object.innerHTML.trim()==''){
			alert('Nothing to paste, please copy same type of data first.');
			return false;
		}
		
		var span_branch_profit_row_loading = $('span_branch_profit_row_loading-'+bid);
		
		if(span_branch_profit_row_loading.style.display==''){
			alert('There are another process still running, please wait. . .');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$('tbody_copy_report_profit').appendChild(this.branch_profit_row_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_report_profit).serialize();

		// clear the tbody
		$('tbody_copy_report_profit').update('');
		
		var new_row_no = this.get_branch_profit_max_row_no(bid)+1;	// get new row num
		sz += '&new_row_no='+new_row_no;
		sz += '&bid='+bid;
		
		// show loading icon
		$(span_branch_profit_row_loading).show();
		
		// send to server to clone
		ajax_request(phpself, {
			method: 'post',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_profit_row_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_report_profit-'+bid, ret['html']);
						
						// init calendar for new pasted row
						for(var key in ret['row_no_list']){
							if (ret['row_no_list'].hasOwnProperty(key)) {
								THIS.init_calendar_for_branch_profit_row(bid, ret['row_no_list'][key]);
								
								// check duplicate
								THIS.check_branch_profit_row_breakdown_duplicate(bid, ret['row_no_list'][key]);
							}
						}
						
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
	// function to check branch profit more % duplicate
	check_branch_profit_row_breakdown_duplicate: function(bid, row_no){
		var type_value_list = {'SKU': {}, 'CATEGORY': {}};
		var tr_branch_profit_row_breakdown_list = $$('#tbody_branch_profit_row_breakdown-'+bid+'-'+row_no+' tr.tr_branch_profit_row_breakdown');
		
		for(var i=0; i<tr_branch_profit_row_breakdown_list.length; i++){
			var type_row_no = tr_branch_profit_row_breakdown_list[i].id.split('-')[3];
			
			var t = this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+type_row_no+'][type]'].value;
			var v = this.f_vp['sales_report_profit_by_date['+bid+']['+row_no+'][profit_per_by_type]['+type_row_no+'][value]'].value;
			
			if(!type_value_list[t][v])	type_value_list[t][v] = [];
			
			//if(in_array(v, type_value_list[t])){	// found duplicated
			//	alert(v);
			//}else{
				type_value_list[t][v].push(type_row_no);
				$('img_branch_profit_row_breakdown_duplicated_entry-'+bid+'-'+row_no+'-'+type_row_no).hide();
			//}
		}
		
		for(var key1 in type_value_list){
			for(var key2 in type_value_list[key1]){
				if(type_value_list[key1][key2].length>1){
					for(var i=0; i<type_value_list[key1][key2].length; i++){
						var type_row_no = type_value_list[key1][key2][i];
						//alert(bid+'-'+row_no+'-'+type_row_no)
						$('img_branch_profit_row_breakdown_duplicated_entry-'+bid+'-'+row_no+'-'+type_row_no).show();
					}
				}
			}
		}
	},
	// function when user click copy bonus other %
	add_branch_bonus_row_more_percent_copy_clicked: function(bid, y, m, row_no){
		if(!bid || !y || !m || !row_no)	return false;
		
		// copy html
		this.branch_bonus_row_more_percent_copied_object = $('tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no).cloneNode();
		
	
		$$('#tbl_branch_access_setting button.btn_add_branch_bonus_row_more_percent_copy').invoke('removeClassName', 'btn_copied');
		$('btn_add_branch_bonus_row_more_percent_copy-'+bid+'-'+y+'-'+m+'-'+row_no).addClassName('btn_copied');
	},
	// function when user click paste bonus other %
	add_branch_bonus_row_more_percent_paste_clicked: function(bid, y, m, row_no){
		if(!this.branch_bonus_row_more_percent_copied_object || this.branch_bonus_row_more_percent_copied_object.innerHTML.trim()==''){
			alert('Nothing to paste, please copy same type of data first.');
			return false;
		}
		
		var span_branch_bonus_row_breakdown_loading = $('span_branch_bonus_row_breakdown_loading-'+bid+'-'+y+'-'+m+'-'+row_no);
		
		if(span_branch_bonus_row_breakdown_loading.style.display==''){
			alert('There are another process still running, please wait. . .');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$('tbody_copy_branch_bonus_more_percent').appendChild(this.branch_bonus_row_more_percent_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_branch_bonus_more_percent).serialize();

		// clear the tbody
		$('tbody_copy_branch_bonus_more_percent').update('');
		
		// get new row no
		var new_type_row_no = int(this.get_max_branch_bonus_row_breakdown_per_row(bid, row_no, y, m))+1;
		sz += '&row_no='+row_no;
		sz += '&new_type_row_no='+new_type_row_no;
		sz += '&bid='+bid+'&y='+y+'&m='+m;
		
		$(span_branch_bonus_row_breakdown_loading).show();
		
		// send to server to clone
		ajex_request(phpself, {
			method: 'post',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_bonus_row_breakdown_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no, ret['html']);
						
						// check duplicate
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m, row_no);
							
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
	// function to check branch bonus other % duplicated
	check_branch_bonus_row_breakdown_duplicate: function(bid, y, m, tmp_row_no){
		var row_no_list = [];
		
		if(tmp_row_no){
			row_no_list.push(tmp_row_no);
		}else{
			// get all row no
			var tr_branch_bonus_row_list = $$('#tbody_branch_bonus-'+bid+'-'+y+'-'+m+' tr.tr_branch_bonus_row');
		
			for(var i = 0; i<tr_branch_bonus_row_list.length; i++){
				var row_info = this.get_branch_bonus_info_by_ele(tr_branch_bonus_row_list[i]);
				row_no_list.push(int(row_info['row_no']));
			}
		}

		for(var j=0; j<row_no_list.length; j++){
			var row_no = row_no_list[j];
			var type_value_list = {'SKU': {}, 'CATEGORY': {}};
			var tr_branch_bonus_row_breakdown_list = $$('#tbody_branch_bonus_row_breakdown-'+bid+'-'+y+'-'+m+'-'+row_no+' tr.tr_branch_bonus_row_breakdown');
			
			for(var i=0; i<tr_branch_bonus_row_breakdown_list.length; i++){
				var type_row_no = tr_branch_bonus_row_breakdown_list[i].id.split('-')[5];
				
				var t = this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+type_row_no+'][type]'].value;
				var v = this.f_vp['sales_bonus_by_step['+bid+']['+y+']['+m+']['+row_no+'][bonus_per_by_type]['+type_row_no+'][value]'].value;
				
				if(!type_value_list[t][v])	type_value_list[t][v] = [];
				
				//if(in_array(v, type_value_list[t])){	// found duplicated
				//	alert(v);
				//}else{
					type_value_list[t][v].push(type_row_no);
					$('img_branch_bonus_row_breakdown_duplicated_entry-'+bid+'-'+y+'-'+m+'-'+row_no+'-'+type_row_no).hide();
				//}
			}
			
			for(var key1 in type_value_list){
				for(var key2 in type_value_list[key1]){
					if(type_value_list[key1][key2].length>1){
						for(var i=0; i<type_value_list[key1][key2].length; i++){
							var type_row_no = type_value_list[key1][key2][i];
							//alert(bid+'-'+row_no+'-'+type_row_no)
							$('img_branch_bonus_row_breakdown_duplicated_entry-'+bid+'-'+y+'-'+m+'-'+row_no+'-'+type_row_no).show();
						}
					}
				}
			}
		}
		
	},
	add_branch_bonus_row_copy_clicked: function(bid, y, m){
		if(!bid || !y || !m)	return false;
		
		// copy html
		this.branch_bonus_row_copied_object = $('tbody_branch_bonus-'+bid+'-'+y+'-'+m).cloneNode();
		
		$$('#tbl_branch_access_setting button.btn_add_branch_bonus_row_copy').invoke('removeClassName', 'btn_copied');
		$('btn_add_branch_bonus_row_copy-'+bid+'-'+y+'-'+m).addClassName('btn_copied');
	},
	add_branch_bonus_row_paste_clicked: function(bid, y, m){
		if(!this.branch_bonus_row_copied_object || this.branch_bonus_row_copied_object.innerHTML.trim()==''){
			alert('Nothing to paste, please copy same type of data first.');
			return false;
		}
		
		var span_branch_bonus_row_loading = $('span_branch_bonus_row_loading-'+bid+'-'+y+'-'+m);
		
		if(span_branch_bonus_row_loading.style.display==''){
			alert('There are another process still running, please wait. . .');
			return false;
		}
		var THIS = this;

		// update the copied html into tbody
		$('tbody_copy_branch_bonus').appendChild(this.branch_bonus_row_copied_object);
		
		// serialize the form
		var sz = $(document.f_copy_branch_bonus).serialize();

		// clear the tbody
		$('tbody_copy_branch_bonus').update('');
		
		var new_row_no = this.get_branch_bonus_max_row_no(bid, y, m)+1;	// get new row num
		sz += '&new_row_no='+new_row_no;
		sz += '&bid='+bid+'&y='+y+'&m='+m;
		
		// show loading icon
		$(span_branch_bonus_row_loading).show();
		
		// send to server to clone
		ajex_request(phpself, {
			method: 'post',
			parameters: sz,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(span_branch_bonus_row_loading).hide();
					    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						new Insertion.Bottom('tbody_branch_bonus-'+bid+'-'+y+'-'+m, ret['html']);	
						
						THIS.check_branch_bonus_row_breakdown_duplicate(bid, y, m);
										
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
	}
}

var SKU_CAT_AUTOCOMPLETE = {
	f: undefined,
	use_for: '',
	return_parms: {},
	initialize: function(){
		this.f = document.f_choose_item_type;
		var THIS = this;
		
		// initial sku autocomplete
		reset_sku_autocomplete();
		
		// initial category autocomplete
		CAT_AUTOCOMPLETE_MAIN_2.initialize({'max_level': 10, 'no_findcat_expand':1}, function(cat_id){
			THIS.add_cat_autocomplete_clicked(cat_id);
		});
	},
	// function to show the popup
	show: function(params){
		reset_sku_autocomplete();
		CAT_AUTOCOMPLETE_MAIN_2.reset();
		
		if(!params){
			alert('Please provide params');
			return false;
		}
		
		// check need to show sku autocomplete or not
		if(params['hide_sku_autocomplete'])	$('div_sku_autocomplete').hide();
		else	$('div_sku_autocomplete').show();
		
		// mark this autocomplete is use for which control
		this.use_for = params['use_for'];
		this.return_parms = params['return_parms'];	// use for later return callback
		
		if(!this.use_for){
			alert('Please specify this autocomplete is use for which control');
		}
		
		curtain(true);
		
		center_div($('div_choose_item_type_dialog').show());
		
		if(!params['hide_sku_autocomplete'])	$('autocomplete_sku').focus();
		else	$('inp_search_cat_autocomplete_2').focus();
	},
	// function when user click add sku
	add_sku_autocomplete_clicked: function(){
		var sid = $('sku_item_id').value;
		
		if(!sid){
			alert('Please search and select 1 sku first.');
			return false;
		}
		
		var params = this.return_parms;
		params['type'] = 'SKU';
		params['value'] = sid;
		
		this.perform_add_autocomplete(params);
	},
	// function when user click add category
	add_cat_autocomplete_clicked: function(cat_id){
		if(!cat_id){
			alert('Please search and select 1 category first.');
			return false;
		}
		
		var params = this.return_parms;
		params['type'] = 'CATEGORY';
		params['value'] = cat_id;
		
		this.perform_add_autocomplete(params);
	},
	perform_add_autocomplete: function(params){
		if(this.use_for == 'report_profit')	VENDOR_PORTAL.add_branch_profit_row_more_percent(params);
		else	VENDOR_PORTAL.add_branch_bonus_row_more_percent(params);
		
		default_curtain_clicked();
	}
}

function add_autocomplete(){
	SKU_CAT_AUTOCOMPLETE.add_sku_autocomplete_clicked();
}

{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $err}
	<div class="alert alert-danger rounded mx-3">
		The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

{if !$err && $form}
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
					{include file='sku_items_autocomplete.tpl' parent_form='document.f_choose_item_type'}
				</div>
				
				<br />
				
				<div id="div_cat_autocomplete">
					{include file='category_autocomplete2.tpl' parent_form='document.f_choose_item_type' ext='_2'}
				</div>
			</form>
		</div>
	</div>
	<!-- End of choose sku or category DIALOG -->
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{if $form.code}{$form.code} - {/if}{$form.description}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	<table style="display:none;">								
		{include file="masterfile_vendor.vendor_portal.branch_profit_row.tpl" bid='__TMP_BID__' row_no='__TMP_ROW_NO__'}
		
		{include file="masterfile_vendor.vendor_portal.branch_bonus_row.tpl" bid='__TMP_BID__' y='__TMP_YEAR__' m='__TMP_MONTH__' row_no='__TMP_ROW_NO__'}
		
	</table>
	
	<div style="display:none;">
		{include file="masterfile_vendor.vendor_portal.branch_bonus_table.tpl" bid='__TMP_BID__' y='__TMP_YEAR__' m='__TMP_MONTH__'}
		
		{* hidden place use to copy branch profit other %*}
		<form name="f_copy_report_profit_more_percent">
			<input type="hidden" name="a" value="ajax_copy_report_profit_more_percent" />
			<table>
				<tbody id="tbody_copy_report_profit_more_percent">
				
				</tbody>
			</table>
		</form>
		
		{* hidden place use to copy branch profit table *}
		<form name="f_copy_report_profit">
			<input type="hidden" name="a" value="ajax_copy_report_profit" />
			<table>
				<tbody id="tbody_copy_report_profit">
				
				</tbody>
			</table>
		</form>
		
		{* hidden place use to copy bonus other % *}
		<form name="f_copy_branch_bonus_more_percent">
			<input type="hidden" name="a" value="ajax_copy_branch_bonus_more_percent" />
			<table>
				<tbody id="tbody_copy_branch_bonus_more_percent">
				
				</tbody>
			</table>
		</form>
		
		{* hidden place use to copy bonus table *}
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
		<input type="hidden" name="vendor_id" value="{$form.id}" />
		
		<div class="card mx-3">
			<div class="card-body">
				<table>
					<!-- Active -->
					<tr>
						<td width="250"><b class="form-label">Active</b>
							[<a href="javascript:void(alert('Activate to allow vendor to access Vendor Portal'));">?</a>]
						</td>
						<td>
							<input type="radio" name="active_vendor_portal" value="1" {if $form.active_vendor_portal}checked {/if} onChange="VENDOR_PORTAL.active_vendor_portal_changed();" /> Yes
							<input type="radio" name="active_vendor_portal" value="0" {if !$form.active_vendor_portal}checked {/if} onChange="VENDOR_PORTAL.active_vendor_portal_changed();" /> No
							<span style="color:red;{if $form.active_vendor_portal}display:none;{/if}" id="span_active_vendor_portal_msg">
								(Vendor will not able to login even got login ticket)
							</span>
						</td>
					</tr>
					
					{* Start Date *}
					<tr>
						<td><b class="form-label">Start Date</b></td>
						<td>
							<div class="form-inline">
								<input type="text" name="start_date" id="inp_start_date" size="20" value="{$form.start_date}" title="Start Date" class="inp_start_date form-control" />
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_start_date" style="cursor: pointer;" title="Select Date" />
							</div>
						</td>
					</tr>
				</table>	
					
			</div>
		</div>
		<div class="card mx-3">
			<div class="card-body">
				<b>Branch Access Settings</b>
		{* <div style="width:98%;height:350px;border:2px inset grey;overflow-y:auto;"> *}
			<table width="100%" cellpadding="0" cellspacing="0" class="report_table" style="background-color:#fff;" id="tbl_branch_access_setting">
				<tr class="header">
					<th><input type="checkbox" id="inp_toggle_all_allowed_branches" onchange="VENDOR_PORTAL.toggle_all_allowed_branches();" /></th>
					<th>SKU Group</th>
					<th>Login<br />Ticket</th>
					<th>Expire Date</th>
				</tr>
				{foreach from=$branches_list key=bid item=b name=fb}
					<tbody class="tbody_allowed_branches">
						<tr class="header">
							<td valign="top" colspan="5">{$b.code}</td>
						</tr>
						<tr class="tr_allowed_branches">
							<td valign="top" rowspan="3"><input type="checkbox" name="allowed_branches[{$bid}]" value="{$bid}" id="inp_allowed_branches-{$bid}" class="inp_allowed_branches" {if $form.allowed_branches.$bid}checked {/if} /></td>
							
							<!-- sku group -->
							<td valign="top">
								<select class="form-control" name="sku_group_info[{$bid}]" style="width:250px">
									<option value="">-- Please Select SKU Group --</option>
									{foreach from=$sku_group_list item=sg}
										{capture assign=sg_id}{$sg.branch_id}|{$sg.sku_group_id}{/capture}
										<option value="{$sg_id}" {if $form.sku_group_info.$bid eq $sg_id}selected {/if}>{$sg.code} - {$sg.description}</option>
									{/foreach}
								</select>
							</td>
							
							<!-- Login Ticket -->
							<td nowrap>
								<div class="form-inline mb-4">
									<input class="form-control" type="text" name="login_ticket[{$bid}]" value="{$form.branch_info.$bid.login_ticket}" size="12" readonly="" />
								<span id="span_clone_ticket-{$bid}" style="{if !$form.branch_info.$bid.login_ticket}display:none;{/if}">
									<img src="/ui/icons/application_tile_vertical.png" title="Use this ticket for all branches" align="absmiddle" class="clickable" onClick="VENDOR_PORTAL.clone_ticket('{$bid}')" />
								</span>
								&nbsp;&nbsp;<input class="btn btn-primary" type="button" id="btn_generate_ticket-{$bid}" value="{if $form.branch_info.$bid.login_ticket}Clear{else}Generate{/if}" onClick="VENDOR_PORTAL.generate_ticket_clicked('{$bid}');" style="width:100px;" />
								</div>
								
								
							</td>
							
							<!-- Expire Date -->
							<td valign="top">
								<span id="span_expire_date-{$bid}" style="{if $form.branch_info.$bid.expire_date eq '9999-12-31'}display:none;{/if}">
									{assign var=default_expire_date value=$smarty.now+31536000}
									{if $form.branch_info.$bid.expire_date eq '9999-12-31'}
										{assign var=expire_date value=$default_expire_date}
									{else}
										{assign var=expire_date value=$form.branch_info.$bid.expire_date}
									{/if}
									{if !$expire_date}{assign var=expire_date value=$default_expire_date}{/if}
									
									<div class="form-inline">
										<input  type="text" name="expire_date[{$bid}]" id="inp_expire_date-{$bid}" size="20" value="{$expire_date|date_format:"%Y-%m-%d"}" title="Expire Date" readonly class="form-control inp_expire_date" />
									&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_expire_date-{$bid}" style="cursor: pointer;" title="Select Date" />
									</div>
							
								</span>
								
								<input type="checkbox" name="no_expire[{$bid}]" value="1" {if $form.branch_info.$bid.expire_date eq '9999-12-31'}checked {/if} id="inp_no_expire-{$bid}" onChange="VENDOR_PORTAL.toggle_no_expire('{$bid}');" /> No Expire Date
							</td>
						</tr>
						<tr>
							{* Email *}
							<td>
								<b class="form-label"> Email [<a href="javascript:void(alert('You can enter multiple email separate by \',\'. Sample:\n==================\nadmin@example.com,user@example.com'))">?</a>]:</b>
								<input class="form-control" type="text" name="contact_email[{$bid}]" value="{$form.branch_info.$bid.contact_email}" style="width:300px;" maxlength="200" />
							</td>
							
							{* Link to Debtor *}
							<td colspan="2">
								<b class="form-label">Link to Debtor: </b>
								<select class="form-control" name="link_debtor_id[{$bid}]">
									<option value="">-- No Link --</option>
									{foreach from=$debtor_list key=debtor_id item=r}
										<option value="{$debtor_id}" {if $form.branch_info.$bid.link_debtor_id eq $debtor_id}selected {/if}>{$r.code} - {$r.description}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						
						<tr>
							{* Report Profit *}
							<td valign="top">
								<b class="form-label mt-2">Report Profit:</b>
								<br />
								{* <input class="form-control" name="sales_report_profit[{$bid}]" value="{$form.sales_report_profit.$bid}" size=3>% *}

								<table id="tbl_report_profit-{$bid}" class="tbl_report_profit report_table" cellpadding="2" cellspacing="0">
									<tr class="tr_header2">
										<th width="20">&nbsp;</th>
										<th width="120">Date To</th>
										<th width="50">% [<a href="javascript:void(alert('This % use for all category all sku.'))">?</a>]</th>
										<th>Other % [<a href="javascript:void(alert('This % can use to assign sepcified rate for certain category and sku. \n* Please note global % will still be calculate and may cause overlaped result in total %.'))">?</a>]</th>
									</tr>
									
									<tbody id="tbody_branch_report_profit-{$bid}">									
										{foreach from=$form.branch_info.$bid.sales_report_profit_by_date item=profit_data name=fprofit}
											{assign var=row_no value=$smarty.foreach.fprofit.iteration}
											
											{include file="masterfile_vendor.vendor_portal.branch_profit_row.tpl"}
										{/foreach}
									</tbody>
								</table>
								<br />
								<button class="btn btn-primary btn-sm" onClick="VENDOR_PORTAL.add_branch_profit_row_clicked('{$bid}');">+</button>
								<button class="btn btn-primary btn-sm" onClick="VENDOR_PORTAL.add_branch_profit_row_copy_clicked('{$bid}');" id="btn_branch_profit_row_copy-{$bid}" class="btn_branch_profit_row_copy">Copy</button>
								<button class="btn btn-success btn-sm" onClick="VENDOR_PORTAL.add_branch_profit_row_paste_clicked('{$bid}');" class="btn_branch_profit_row_paste">Paste</button>
		
								<span id="span_branch_profit_row_loading-{$bid}" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
							</td>
							
							{* Bonus *}
							<td colspan="2" valign="top">
								<b class="form-label mt-2">Bonus:</b>
								
								<div class="form-inline">
									Year&nbsp; <input class="form-control" type="text" size="4" id="inp_branch_bonus_y-{$bid}" onChange="miz(this);" value="{$smarty.now|date_format:"%Y"}" /> 
								&nbsp;Month&nbsp; <input class="form-control" type="text" size="4" id="inp_branch_bonus_m-{$bid}" onChange="miz(this);" value="{$smarty.now|date_format:"%m"}" />
								&nbsp;&nbsp;<button class="btn btn-primary" onClick="VENDOR_PORTAL.add_new_branch_bonus_group_clicked('{$bid}');">Add Monthly Bonus Group</button>
								</div>
								<div id="div_branch_bonus_group_list-{$bid}">
									{* loop year list *}
									{foreach from=$form.branch_info.$bid.sales_bonus_by_step key=y item=m_bonus_list}
										{* loop month list *}
										{foreach from=$m_bonus_list key=m item=bonus_data_list}
											{include file="masterfile_vendor.vendor_portal.branch_bonus_table.tpl"}
										{/foreach}	
									{/foreach}
								</div>								
							</td>
						</tr>
						
						{if !$smarty.foreach.fb.last}
							<tr class="tr_split_row">
								<td colspan="5">&nbsp;</td>
							</tr>
						{/if}
					</tbody>
				{/foreach}
			</table>
		{* </div> *}
			</div>
		</div>
		
			<p align="center" id="p_action_button">
				<input class="btn btn-success" type="button" value="Save" onClick="VENDOR_PORTAL.update_clicked();" id="btn_update_vendor_portal" />
			</p>
	</form>
	
	<script type="text/javascript">
		VENDOR_PORTAL.initialize();
	</script>
{/if}
{include file="footer.tpl"}
