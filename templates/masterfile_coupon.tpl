{*
11/25/2010 5:20:43 PM Alex
- created by me

11/26/2010 11:47:48 AM Alex
- add pagination

3/24/2011 12:07:21 PM Alex]
- use $config.coupon_amount_0_5_cent to check 0 or 5 cent of amount
- use $config.coupon_use_percentage to allow enter percentage discount

4/12/2011 3:11:48 PM Alex
- fix js checking 0 and 5 cents bugs
- change duration to 12 month

4/20/2011 6:18:51 PM Alex
- change date_to while edit date_from

9/11/2013 5:00 PM Justin
- Enhanced to rework some of the components such as add/edit Coupon to use pop up dialog.

11/28/2013 2:41 PM Justin
- Bug fixed on SKU item still exists in SKU item list even it is deleted.

11/29/2013 5:13 PM Andy
- Fix SKU list still can add/delete item after the coupon was printed (but edited sku are not saved).
- Fix showing wrong "Valid Date To".

5/11/2015 2:34 PM Andy
- Remove the coupon by percentage.

4/25/2017 10:36 AM Khausalya
- Enhanced changes from RM to use config setting. 

8/27/2019 1:54 PM Andy
- Added Discount by Percentage.
- Added Minimum Receipt Amount.
- Added Search Coupon feature.
- Enhanced to able to view coupon details while it is already activated.
- Enhanced to able to auto get new coupon code.

9/27/2019 11:06 AM Andy
- Change to not allow to search un-branded.
- Enhanced to prevent users to click save coupon multiple time while the process is running.

10/4/2019 9:27 AM Andy
- Added alert notification for Discount By, Min Purchase Amount, Min Receipt Amount and Membership Condition.

11/28/2019 4:43 PM Andy
- Added feature to control coupon only show for mobile registered member since day X to day Y or need profile info.

2/11/2020 5:58 PM Andy
- Added new coupon feature "Referral Program".

5/7/2020 10:10 AM Justin
- Bug fixed on last digit checking for amount is not working properly.

4/2/2021 4:37 PM Andy
- Fixed sometime last digit checking for amount will have bug due to javascript issue.
*}

{include file=header.tpl}
{literal}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
#coupon_details tr:nth-child(odd){
	background-color: #eeeeee;
}

.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var duration_valid = '{if $config.coupon_active_month_duration}{$config.coupon_active_month_duration}{else}6{/if}';
var coupon_amount = '{$config.coupon_amount_0_5_cent}';
var currency_symbol = '{$config.arms_currency.symbol}';
{literal}

var ven_autocomplete = undefined;
var bran_autocomplete = undefined;
var tab_page=1;

function view_form(status){
	if (status == 'show')
	    $('new_id').style.display="";
	else
	    $('new_id').style.display="none";
}

/* // currently not using
function delete_coupon(id,bid){
	var params = 'a=ajax_delete_item&id='+id+'&branch_id='+bid;
    ajax_call_reload(params,'delete',id,bid);
}*/

function activate_deactivate_coupon(id,bid,status){
	if (status == 'activate'){
		var params = 'a=ajax_activate_deactivate_item&id='+id+'&branch_id='+bid+'&active=1';
	}
	else{
		var params = 'a=ajax_activate_deactivate_item&id='+id+'&branch_id='+bid+'&active=0';
	}

	new Ajax.Request(phpself,{
		method:'post',
		parameters: params,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
		    res = m.responseText.trim();
		    if(m.responseText.trim()=='OK'){
			    COUPON_MAIN.list_sel();
			}else{
                alert(m.responseText);
			}
    	}
	});
	
}

var COUPON_MAIN = {
	initialize: function(){
		this.list_sel(1);
	},
	list_sel: function (n,s){
		if(n == undefined){
			// if no provide, use back the current tab
			n = tab_page
		}else{
			// change tab
			tab_page = n;
		}
		
		$$('#div_tab a.a_tab').each(function(a){
			$(a).removeClassName('active');
		});
		$('lst'+n).addClassName('active');
		
		var params = {
			a: 'ajax_load_coupon_list',
			t: n
		}
		
		var pg = '';
		if (s!=undefined) pg = '&s='+s;
		
		var search_str = '';
		if(n == 0){
			var tmp_search_str = $('inp_item_search').value.trim();

			if(tmp_search_str==''){
				alert('Cannot search empty string');
				return;
			}else 	search_str = tmp_search_str;
			
			params['search_str'] = search_str;
		}
		
		$('coupon_list_id').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

		// reload list
		new Ajax.Updater('coupon_list_id', phpself, {
			parameters: params,
			evalScripts: true
		});
	},
	// function when user click on search notification
	toggle_search_info: function(){
		alert("Search by Coupon Code.");
	},
	search_input_keypress: function (event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			this.list_sel(0);	// Search
		}
	},
	alert_min_amt_notification: function(){
		var str = 'Total Condition Purchased Amount';
		
		str += '\n=====================================\n';
		str += '- Total purchased amount sum from the condition items.\n';
		str += '- After deduct Receipt Discount and Mix and Match Discount.\n';
		str += '- If condition is All then will check total receipt amount.\n';
		str += '- Work in ARMS POS v200 and above.\n';
		
		alert(str);
	},
	alert_min_qty_notification: function(){
		var str = 'Total Condition Purchased Quantity';
		
		str += '\n=====================================\n';
		str += '- Total purchased quantity sum from the condition items.\n';
		str += '- If condition is All then will check total receipt quantity.\n';
		
		alert(str);
	},
	alert_min_receipt_amt_notification: function(){
		var str = 'Total Receipt Amount after Receipt and Mix & Match Discount';
		
		str += '\n=====================================\n';
		str += '- Total receipt amount sum from whole transaction.\n';
		str += '- After deduct Receipt Discount and Mix and Match Discount.\n';
		str += '- If both Purchase Amount and Receipt Amount are set, whichever higher will be used.\n';
		str += '- Work in ARMS POS v200 and above.\n';
		
		alert(str);
	},
	alert_discount_by_notification: function(){
		var str = 'Discount by Percentage only work in ARMS POS v200 and above';
				
		alert(str);
	},
	alert_member_condition_notification: function(){
		var str = 'Only work in ARMS POS v200 and above';
				
		alert(str);
	},
	// function to automatically get new coupon code
	auto_get_new_code: function(){
		$('btn_get_new_code').disabled = true;
				
		var params = {
			a: 'ajax_get_new_coupon_code'
		};
		
		new Ajax.Request(phpself, {
			method:'post',
			parameters: params,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('btn_get_new_code').disabled = false;
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['new_code']){ // success
						// Redirect to main page
						$('code').value = ret['new_code'];
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
	}
};

var COUPON_DIALOG = {
	f: undefined,
	f_prn: undefined,
	bran_autocomplete: undefined,
	ven_autocomplete: undefined,
	initialize: function(){
		this.f = document.f_a;
		this.f_prn = document.f_prn;

		if(!this.f){
			alert('Coupon dialog failed to initialize.');
			return false;
		}
	
		if(int(this.f['active'].value)==0){
			this.init_calendar();
		}
		
		this.reset_vendor_autocomplete();
		this.reset_brand_autocomplete();
		this.reset_sku_autocomplete();
		this.first_load();
		
		if(!this.f['id'].value){	// new coupon
			this.calculate_date_end();
		}
		
		new Draggable('div_print_coupon_dialog');
		new Draggable('div_coupon_dialog');
	},

	init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_valid_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_valid_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true

			//onUpdate       :    load_data
		});

		Calendar.setup({
			inputField     :    "inp_valid_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_valid_to",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true

			//onUpdate       :    load_data
		});
	},
	
	toggle_coupon_dialog: function(id, bid){
		
		var params = "a=ajax_load_coupon_dialog";
		
		if(id != undefined && bid != undefined) params += "&id="+id+"&bid="+bid;
		var THIS = this;
		
		$("div_coupon_dialog").update(_loading_);
		center_div($("div_coupon_dialog").show());
		
		new Ajax.Request(phpself,{
			method:'post',
			parameters: params,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
				var str = m.responseText.trim();
				var ret = {};
				var err_msg = '';
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']==1 && ret['html']){ // success
						// append html and show div
						$("div_coupon_dialog").update(ret['html']);
						THIS.initialize();
						THIS.first_load();
						center_div($("div_coupon_dialog"));
						return;
					}else{  // save failed
						if(ret['err_msg'])	err_msg = ret['err_msg'];
						else err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				if(err_msg)	alert(err_msg);
			}
		});
		
		curtain(true);
	},

	reset_vendor_autocomplete: function(){
		ven_autocomplete = new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", {
			afterUpdateElement: function (obj, li)
			{
				var  s = li.title.split(",");
				if(s[0]==0){
					$('autocomplete_vendor').value = '';
					return;
				}
				document.f_a.vendor_id.value = li.title;
			}
		});
	},

	reset_brand_autocomplete: function(){
		bran_autocomplete=new Ajax.Autocompleter("autocomplete_brand", "autocomplete_brand_choices", "ajax_autocomplete.php?a=ajax_search_brand", {
			parameters: "no_unbranded=1",
			afterUpdateElement: function (obj, li)
			{
				var  s = li.title.split(",");
				if(s[0]==0){
					$('autocomplete_brand').value = '';
					return;
				}
				document.f_a.brand_id.value = li.title;
			}
		});
	},
	
	reset_sku_autocomplete: function(){
		$('autocomplete_sku').onkeyup = function(k){
			if(k.keyCode==27) //escape
			{
				clear_autocomplete();
				return;
			}

			if (_timeout_autocomplete_!=false) clearTimeout(_timeout_autocomplete_);
			_timeout_autocomplete_ = false;
			
			val = this.value.trim();
			if (val<=0) return;
			_timeout_autocomplete_ = setTimeout('do_autocomplete()',500);
		};
		clear_autocomplete();
	},
	
	calculate_date_end: function(){
		var date_arr = $('inp_valid_from').value.split("-");

		var t = new Date(parseInt(date_arr[0]),(parseInt(date_arr[1]-1,10)),parseInt(date_arr[2],10));
		
		if ($('rdo_end_id').value == 'valid_to')
			var duration=parseInt(duration_valid);
		else
			var duration=parseInt($('inp_valid_duration').value);

		t.setMonth(t.getMonth()+duration);

		var d = (t.getDate()).toString();
		var m = (t.getMonth()+1).toString();
		var y = t.getFullYear();

		if (d.length==1) d="0"+d;
		if (m.length==1) m="0"+m;

		$('show_date_end').value = y+'-'+m+'-'+d;
		$('inp_valid_to').value = y+'-'+m+'-'+d;
	},
	
	do_save: function(){
		if(!this.validate_data()) return false;
	
		for(var i=0; i<$('sku_code_list').length; i++){
		    $('sku_code_list').options[i].selected = true;
		}
	
		//passArrayToInput();
		var params = 'a=ajax_save_coupon&'+Form.serialize(this.f);
		
		// Disable submit button
		this.enable_save_coupon_button(false);
		THIS = this;
		
		new Ajax.Request(phpself,{
			method:'post',
			parameters: params,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
				res = m.responseText.trim();
				THIS.enable_save_coupon_button(true);
				
				if(res=='OK'){
					alert("Coupon ["+document.f_a.code.value+"] sucessfully saved.");
					hidediv('div_coupon_dialog');
					curtain(false);
					COUPON_MAIN.list_sel(1);
				}else{
					alert(m.responseText);
				}
			}
		});
	},
	
	validate_data: function(){
		if(!this.f.code.value.trim()){
			alert("Please enter Coupon Code.");
			return false;
		}
		
		// Limit to member type
		if(this.f['member_limit_type'].value == 'member_type'){
			var selected_member_type_count = 0;
			$$('#div_member_limit_info-member_type input.chx_member_type').each(function(inp){
				if(inp.checked)	selected_member_type_count++;
			});
			if(!selected_member_type_count){
				alert('Please select at least one member type');
				return false;
			}
		}
		
		// Referral Program
		if(this.f['member_limit_type'].value == 'referral_program'){
			var referee_coupon_get = int(this.f['referee_coupon_get'].value);
			var referrer_coupon_get = int(this.f['referrer_coupon_get'].value);
			var referrer_count_need = int(this.f['referrer_count_need'].value);
			var referee_day_limit = int(this.f['referee_day_limit'].value);
			
			if(referrer_coupon_get < 0){
				alert("Negative Referrer Coupon is not allowed");
				this.f['referrer_coupon_get'].focus();
				return false;
			}
			
			if(referee_coupon_get < 0){
				alert("Negative Referee Coupon is not allowed");
				this.f['referee_coupon_get'].focus();
				return false;
			}
			
			if(referrer_count_need < 0){
				alert("Negative Referrer Needed is not allowed");
				this.f['referrer_count_need'].focus();
				return false;
			}
			
			if(referee_day_limit < 0){
				alert("Negative Referee Day is not allowed");
				this.f['referee_day_limit'].focus();
				return false;
			}
			
			if(referee_coupon_get <= 0 && referrer_coupon_get <= 0){
				alert("Please key in Referrer Get Coupon or Referee Get Coupon");
				this.f['referrer_coupon_get'].focus();
				return false;
			}
			
			if(referrer_coupon_get > 0 && referrer_count_need <= 0){
				alert("Please key in How many Referral Needed Per Coupon.");
				this.f['referrer_count_need'].focus();
				return false;
			}
		}
		
		// Member Option is showing
		if($('div_member_options').style.display==''){
			var member_limit_mobile_day_start = int(this.f['member_limit_mobile_day_start'].value);
			var member_limit_mobile_day_end = int(this.f['member_limit_mobile_day_end'].value);
			
			// Got Negative
			if(member_limit_mobile_day_start < 0 || member_limit_mobile_day_end < 0){
				alert('Negative Day is not allowed');
				return false;
			}
			
			// Got Value
			if(member_limit_mobile_day_start > 0 || member_limit_mobile_day_end > 0){
				if(!member_limit_mobile_day_start){
					alert('Please key in Day from (minimum = 1)');
					this.f['member_limit_mobile_day_start'].focus();
					return false;
				}
				
				if(!member_limit_mobile_day_end){
					alert('Please key in Day To (minimum = 1)');
					this.f['member_limit_mobile_day_end'].focus();
					return false;
				}
				
				if(member_limit_mobile_day_start > member_limit_mobile_day_end){
					alert('Day From cannot larger than Day To');
					this.f['member_limit_mobile_day_start'].focus();
					return false;
				}
			}
		}
		
		return true;
	},
	
	first_load: function(){
		//alert('first_load');
		this.toggle_date_type($('rdo_end_id'));

		var radio = document.f_a['setting'];
		
		for (var i=0; i< radio.length; i++){
			if (radio[i].checked == true){
				this.setting_changed(radio[i].value);
			}
		}
		
		if(int(this.f['active'].value)>0){
			$(this.f).disable();
		}else{
			if (this.f['is_print'].value > 0){
				this.f['code'].disable();
				this.f['dept_id'].disable();
				this.f['setting'][0].disable();
				this.f['setting'][1].disable();
				this.f['brand_id'].disable();
				this.f['vendor_id'].disable();
				this.f['autocomplete_brand'].disable();
				this.f['autocomplete_vendor'].disable();
				this.f['min_qty'].disable();
				this.f['min_amt'].disable();
				this.f['min_receipt_amt'].disable();
				this.f['discount_by'].disable();
				$$('#tbl_member_limit_type input.inp_member_limit_type').invoke('disable');
				$$('#div_member_limit_info-member_type input.chx_member_type').invoke('disable');
				
				$('td_sku_items').getElementsBySelector("input").each(function(ele){
					$(ele).disabled = true;
				});
			}
		}
		
		//alert('first_load done');
	},

	setting_changed: function(val){
		if(val == "dept_bran_vd"){
			document.f_a['setting'][0].checked = true; 		//department, brand and vendor
			document.f_a['setting'][1].checked = false;    //sku items

			$('vendor_id').enable();
			$('autocomplete_vendor').enable();

			$('brand_id').enable();
			$('autocomplete_brand').enable();
			
			$('dept_id').enable();

			$('td_sku_items').style.display = "none";
			$('sku_code_list').disable();
		}else{
			document.f_a['setting'][0].checked = false;    //department, brand and vendor
			document.f_a['setting'][1].checked = true;     //sku items

			$('brand_id').disable();
			$('autocomplete_brand').disable();

			$('vendor_id').disable();
			$('autocomplete_vendor').disable();
			
			$('dept_id').disable();
			
			$('td_sku_items').style.display = "";
			$('sku_code_list').enable();
		}
	},

	toggle_date_type: function(ele){
		var date_type=ele.value;

		if (date_type=='valid_duration'){
			$('date_duration_id').show();
			$('date_duration_id2').show();

			$('inp_valid_duration').enable();

			$('date_end_id').hide();
			$('inp_valid_to').disable();
		}else{
			$('date_duration_id').hide();
			$('date_duration_id2').hide();

			$('inp_valid_duration').disable();

			$('date_end_id').show();
			$('inp_valid_to').enable();
		}
	},
	
	toggle_print_coupon_dialog: function(id, bid){
		var params = "a=ajax_load_coupon_dialog&print_coupon=1";
		
		if(id != undefined && bid != undefined) params += "&id="+id+"&bid="+bid;
		var THIS = this;
		
		$('div_print_coupon_dialog').update(_loading_);
		center_div($('div_print_coupon_dialog').show());
		curtain(true);
		
		new Ajax.Request(phpself,{
			method:'post',
			parameters: params,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
				var str = m.responseText.trim();
				var ret = {};
				var err_msg = '';
				

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']==1 && ret['html']){ // success
						// append html and show div
						$("div_print_coupon_dialog").update(ret['html']);
						center_div($('div_print_coupon_dialog'));
						//THIS.toggle_amount_percentage();
						return;
					}else{  // save failed
						if(ret['err_msg'])	err_msg = ret['err_msg'];
						else err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
			}
		});
	},
	
	toggle_amount_percentage: function(){
		if ($('print_type_amount').checked){
			$('use_percentage_id').hide();
			$('use_amount_id').show();
		}else{
			$('use_percentage_id').show();
			$('use_amount_id').hide();
		}
	},
	
	print_ok: function(){
		var discount_by = document.f_prn['discount_by'].value;
		var value_desc = discount_by == 'per' ? 'Percentage' : 'Amount';
		
		//if ($('print_type_amount') && $('print_type_amount').checked){
			var print_value = float($('print_value').value);

			if (print_value<=0){
				alert("Error: Please key in "+value_desc+".");
				return false;
			}


			if(discount_by == 'per'){
				// Percentage
				if (print_value > 99.99){
					alert("Error: Discount Percentage cannot more than 99.99");
					return false;
				}
			}else{
				// Amount
				if (print_value >= 1000){
					alert("Error: Coupon amount must less than " + currency_symbol + " 999.95");
					return false;
				}
				
				//check 0 and 5 cent only
				var amount_arr = print_value.toString().split(".");
				if (amount_arr.length > 2){
					alert("Error: Invalid amount.");
				}else if (amount_arr.length == 2){
					var amt_code = round(print_value*100, 0);
					//console.log(print_value+' * 100 = '+amt_code);
					//console.log('amt_code = '+amt_code);
					amt_code = amt_code.toString();
					var last_amt_digit = amt_code.substr(amt_code.length-1);
					//console.log('last_amt_digit = '+last_amt_digit);
					if (last_amt_digit != "5" && last_amt_digit != "0"){
						alert("Error: The last digit of amount must be " + currency_symbol + " 0.05 or " + currency_symbol + " 0.10");
						return false;
					}
				}
			}
			

				
			/*}else if (print_value >= 1000){
				alert("Error: Coupon amount must less than " + currency_symbol + " 999.99");
				return false;
			}*/

		/*}else if ($('print_type_percentage') && $('print_type_percentage').checked){
			var print_percentage = float($('print_percentage').value);

			if (!print_percentage){
				alert("Error: Please key in percentage.");
				return false;
			}

			if (print_percentage > 99.99){
				alert("Error: Coupon percentage must less than 99.99%");
				return false;
			}
		}*/

		if (!float($('print_qty').value)){
			alert("Error: Please key in quantity.");
			return false;
		}

		if (float($('print_qty').value) > 500){
			alert("Error: Coupon quantity must less than 500 pieces.");
			return false;
		}

		if (!confirm('Are you sure?'))  return false;
		
		$('div_print_coupon_dialog').style.display = 'none';
		//document.f_prn.target = "ifprint";
		document.f_prn.target = "_blank";
		document.f_prn.submit();
		curtain(false);
		
		// refresh the list
		setTimeout(function(){ COUPON_MAIN.list_sel(); }, 1000);		
	},
	
	curtain_clicked: function(){
		hidediv('div_coupon_dialog');
		hidediv('div_print_coupon_dialog');
		curtain(false);
	},
	// function when user change member limit type
	member_limit_type_changed: function(){
		var member_limit_type = getRadioValue(this.f['member_limit_type']);
		
		$$('#div_member_limit_info div.div_member_limit_info').invoke('hide');
		
		var div_member_limit_info = $('div_member_limit_info-'+member_limit_type);
		if(!div_member_limit_info)	div_member_limit_info = $('div_member_limit_info-default');
		
		$(div_member_limit_info).show();
		
		if(member_limit_type){
			$('div_member_options').show();
		}else{
			$('div_member_options').hide();
		}
	},
	// function to enable or disable save coupon button
	enable_save_coupon_button: function(is_enable){
		if(!is_enable)	is_enable = false;
		$$('#p_save_coupon input').each(function(inp){
			inp.disabled = !is_enable;
		});
	}
}

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>
<p>
<!-- print dialog -->
<div id="div_print_coupon_dialog" class="curtain_popup" style="background:#fff;border:3px solid #000;width:480px;height:400px;position:absolute; padding:10px; display:none;">
{include file="masterfile_coupon.print.tpl"}
</div>

{if $privilege.MST_COUPON_EDIT}
<ul>
<li> <a href="./ui/3of9/mrvcode39extma.ttf">Click here to download and install the font for printing barcode</a></li>
<li> <a href="#" onclick="COUPON_DIALOG.toggle_coupon_dialog();">Create New Coupon</a></li>
</ul>
{/if}
</p>

{* Coupon Details Dialog *}
<div id="div_coupon_dialog" class="curtain_popup" style="position:absolute;z-index:10000;display:none;border:1px solid black;background-repeat:repeat-x;padding:0;">{include file="masterfile_coupon.new.tpl"}
</div>

<div style="padding:10px 0;">
	<div class="tab" style="height:25px;white-space:nowrap;" id="div_tab">
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:COUPON_MAIN.list_sel(1)" id="lst1" class="active a_tab">All Coupons</a>
		<a href="javascript:COUPON_MAIN.list_sel(2)" id="lst2" class="a_tab">Active Coupon</a>
		<a href="javascript:COUPON_MAIN.list_sel(3)" id="lst3" class="a_tab">Inactive Coupon</a>
		<a id="lst0" class="a_tab">
			Search [<span class="link" onclick="COUPON_MAIN.toggle_search_info();">?</span>] 
			<input id="inp_item_search" onKeyPress="COUPON_MAIN.search_input_keypress(event);" /> 
			<input type="button" value="Go" onClick="COUPON_MAIN.list_sel(0);" />
		</a>
	</div>
	<div id="coupon_list_id" style="border:1px solid #000">
		{include file="masterfile_coupon.list.tpl"}
	</div>
</div>

{include file=footer.tpl}

<script>
COUPON_MAIN.initialize();
</script>
