{*
12/19/2019 9:24 AM Andy
- Fixed screen no need to hide curtain if clock success.
- Enhanced to blur from barcode input when user submit clock.
*}

{include file='header.tpl' no_menu_templates=1}

<style>
{literal}
input.inp_btn{
	width: 90px;
	height: 60px;
	font-size: 20pt;
}

#div_datetime{
	padding: 10px;
	margin: auto;
}

#div_time{
	text-align: center;
	font-size: 70pt;
	color: blue;
}
#div_date{
	text-align: center;
	font-size: 20pt;
	color: blue;
}

#div_barcode_error{
	height: 20px;
	color: red;
	font-weight: bold;
}

input.btn_close{
	width: 130px;
	height: 130px;
	font-size: 30pt;
	color: red;
}

#div_select_date{
	position:fixed;
	background:#ff9;
	padding: 20px 30px;
	z-index:10000;
	border: 5px outset grey;
}

input.btn_select_date{
	width: 100%;
	height: 100px;
	font-size: 20pt;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var now = '{$smarty.now}';
var cutoff_total_min = int('{$cutoff_total_min}');
var grace_min = 180;

{literal}
var ATTENDANCE_CLOCK = {
	f: undefined,
	dateObj: undefined,
	time_interval: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// assign date object
		this.dateObj = new Date(now * 1000);
		
		// Update date & time
		this.set_datetime_html();
		
		// check again server time in 60s
		this.countdown_refresh_time();
		
		// Begin to automatically add 1 second to clock
		setInterval(function(){ 
			ATTENDANCE_CLOCK.auto_update_clock_1s();
		}, 1000);
		
		this.f['barcode'].focus();
	},
	// core function to update date & time html
	set_datetime_html: function(){
		var d = (this.dateObj.getFullYear())+'-'+(("0"+(this.dateObj.getMonth()+1)).slice(-2)) +"-"+ (("0"+this.dateObj.getDate()).slice(-2));
		var t = (("0"+(this.dateObj.getHours())).slice(-2)) +":"+ (("0"+(this.dateObj.getMinutes())).slice(-2));//+":"+(("0"+(this.dateObj.getSeconds())).slice(-2));
		$('div_time').update(t);
		$('div_date').update(d);
	},
	// core function to refresh using server time after 60 second
	countdown_refresh_time: function(){
		// Check again server time after 60 second
		setTimeout(function(){
			ATTENDANCE_CLOCK.check_server_datetime();
		}, 60000);
	},
	// function to check server time
	check_server_datetime: function(){
		var THIS = this;
		var params = {
			a: 'get_server_time'
		}
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
				THIS.countdown_refresh_time();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['time']){ // success
						// assign date object
						THIS.dateObj = new Date(ret['time'] * 1000);
						// Update Date & Time
						THIS.set_datetime_html();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
				if(!err_msg)	err_msg = 'Unable to Get Server Time';
			    alert(err_msg);
				
				// Reload the Page
				location.reload();
			}
		});
	},
	// core function to automatically update clock each second
	auto_update_clock_1s: function(){
		this.dateObj.setSeconds( this.dateObj.getSeconds() + 1 );
		this.set_datetime_html();
	},
	// function when user click on button number
	numpad_clicked: function(num){
		this.reset_barcode_error();
		this.f['barcode'].value = this.f['barcode'].value+num;
		this.f['barcode'].focus();
	},
	// function when user click on button clear
	clear_clicked: function(){
		this.reset_barcode_error();
		this.f['barcode'].value = '';
		this.f['barcode'].focus();
	},
	// function when user click on button backspace
	backspace_clicked: function(){
		this.reset_barcode_error();
		var str = this.f['barcode'].value.trim();
		
		if(str){
			this.f['barcode'].value = str.substring(0, str.length-1);
		}
		this.f['barcode'].focus();
	},
	// function when user click on button close
	/*close_clicked: function(){
		//window.open('','_self').close()
		//window.close('','_parent','');
	},*/
	// function to clear barcode error
	reset_barcode_error: function(){
		$('div_barcode_error').update('');
	},
	// function when user press something in barcode
	check_barcode_key: function(event){
		if (event == undefined) event = window.event;
		if(event.key == 'Enter'){  // enter
			this.submit_clicked();
		}else if(event.key == 'Escape'){  // escape
			this.clear_clicked();
		}
	},
	// function when user click on button enter
	submit_clicked: function(){
		this.f['barcode'].blur();
		
		if(this.f['barcode'].value.trim()==''){
			$('div_barcode_error').update('Please enter barcode');
			this.f['barcode'].focus();
			return;
		}
		
		// Check Cross Day
		if(this.check_cross_day()){
			
		}else{
			this.submit_user_attendance_record();
		}
	},
	// core function to check it have cross day
	check_cross_day: function(){
		//curr_d = this.dateObj.getDate();
		//var new_dateObj = new Date(this.dateObj.getMilliseconds());
		//new_dateObj.setMinutes = new_dateObj.getMinutes()+cutoff_total_min;
		
		var today_hour = int(this.dateObj.getHours());
		var today_min = int(this.dateObj.getMinutes());
		var today_total_min = (today_hour * 60) + today_min;
		//alert(today_total_min);
		
		// Ask for Cross Day
		if(today_total_min <= cutoff_total_min + grace_min){
			var prev_dateObj = new Date(this.dateObj.getFullYear(), this.dateObj.getMonth(), this.dateObj.getDate()-1);
			//alert(prev_dateObj.toLocaleDateString());
			
			var pre_date = toYMD(prev_dateObj);
			var today = toYMD(this.dateObj);
			$('btn_prev_date').setAttribute('selected_date', pre_date);
			$('btn_prev_date').value = 'Previous: '+pre_date;
			
			$('btn_today_date').setAttribute('selected_date', today);
			$('btn_today_date').value = 'Today: '+today;
			
			// show wait popup
			GLOBAL_MODULE.show_wait_popup();
			center_div($('div_select_date').show());
			
			$('btn_prev_date').focus();
			return true;
		}
		return false;
	},
	// function when user click on button previous day or today
	shift_selected_date_clicked: function(inp){
		var selected_date = inp.getAttribute('selected_date');
		if(selected_date){
			this.submit_user_attendance_record(selected_date);
			$('div_select_date').hide();
		}
	},
	// core function to submit user attendance record
	submit_user_attendance_record: function(selected_date){
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = $(this.f).serialize();
		if(selected_date)	params += '&selected_date='+selected_date;
		
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
	                if(ret['ok'] && ret['user_id'] && ret['date']){ // success
						// Update HTML
						document.location = '?a=show_info&user_id='+ret['user_id']+'&date='+ret['date'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				GLOBAL_MODULE.hide_wait_popup();

			    // prompt the error
			    alert(err_msg);
				
				// Focus back the barcode after 0.1s, prevent chrome bug to triggle ENTER
				setTimeout(function(){
					THIS.f['barcode'].focus();
				}, 100);
				
			}
		});
	},
	// function when user press something on crossday selection popup
	check_crossday_key: function(event){
		if (event == undefined) event = window.event;
		//alert(event.key);
		if(event.key == 'Escape'){  // Escape
			this.close_crossday_popup();
		}else if(event.key == 'ArrowDown'){  // Down
			$('btn_today_date').focus();
		}else if(event.key == 'ArrowUp'){  // Up
			$('btn_prev_date').focus();
		}
	},
	close_crossday_popup: function(){
		// show wait popup
		GLOBAL_MODULE.hide_wait_popup();
		$('div_select_date').hide();
		this.f['barcode'].focus();
	}
};
{/literal}
</script>

<div id="div_select_date" style="display:none;">
	<h1>Please Select Your Shift Date</h1>
	
	<table width="100%">
		{* Previous Day *}
		<tr>
			<td>
				<input type="button" class="btn_select_date" id="btn_prev_date" value="Date 1" onClick="ATTENDANCE_CLOCK.shift_selected_date_clicked(this);" onKeyUp="ATTENDANCE_CLOCK.check_crossday_key(event);" />
			</td>
		</tr>
		
		{* Today *}
		<tr>
			<td>
				<input type="button" class="btn_select_date" id="btn_today_date" value="Date 2" onClick="ATTENDANCE_CLOCK.shift_selected_date_clicked(this);" onKeyUp="ATTENDANCE_CLOCK.check_crossday_key(event);" />
			</td>
		</tr>
	</table>
</div>

<table width="100%" border="0">
	<tr>
		{* Left *}
		<td align="center" width="25%" valign="top">
			<div id="div_datetime" class="stdframe">
				<div id="div_date"></div>
				<div id="div_time"></div>
			</div>
		</td>
		
		{* Center *}
		<td width="50%">
			<div style="text-align:center;" id="div_info">
				<div class="stdframe" id="div_keypad" style="width: 450px; margin:auto;">
					<form name="f_a" onSubmit="return false;">
						<input type="hidden" name="a" value="ajax_submit_clock" />
						
						<h1>Enter your barcode</h1>
						<input type="password" name="barcode" style="font-size: 20pt;text-align: center;" onKeyUp="ATTENDANCE_CLOCK.check_barcode_key(event);" />
						<br /><br />
						
						<div id="div_barcode_error">
							
						</div>
						
						<div style="text-align:center;">
							<table border="0" style="margin:auto;" cellpadding="0" cellspacing="0">
								<tr>
									<td><input type="button" class="inp_btn" value="1" onClick="ATTENDANCE_CLOCK.numpad_clicked('1');" /></td>
									<td><input type="button" class="inp_btn" value="2" onClick="ATTENDANCE_CLOCK.numpad_clicked('2');" /></td>
									<td><input type="button" class="inp_btn" value="3" onClick="ATTENDANCE_CLOCK.numpad_clicked('3');" /></td>
								</tr>
							
								<tr>
									<td><input type="button" class="inp_btn" value="4" onClick="ATTENDANCE_CLOCK.numpad_clicked('4');" /></td>
									<td><input type="button" class="inp_btn" value="5" onClick="ATTENDANCE_CLOCK.numpad_clicked('5');" /></td>
									<td><input type="button" class="inp_btn" value="6" onClick="ATTENDANCE_CLOCK.numpad_clicked('6');" /></td>
								</tr>
								
								<tr>
									<td><input type="button" class="inp_btn" value="7" onClick="ATTENDANCE_CLOCK.numpad_clicked('7');" /></td>
									<td><input type="button" class="inp_btn" value="8" onClick="ATTENDANCE_CLOCK.numpad_clicked('8');" /></td>
									<td><input type="button" class="inp_btn" value="9" onClick="ATTENDANCE_CLOCK.numpad_clicked('9');" /></td>
								</tr>
								
								<tr>
									<td><input type="button" class="inp_btn" value="Clear" onClick="ATTENDANCE_CLOCK.clear_clicked();" /></td>
									<td><input type="button" class="inp_btn" value="0" onClick="ATTENDANCE_CLOCK.numpad_clicked('0');" /></td>
									<td><input type="button" class="inp_btn" value="x" onClick="ATTENDANCE_CLOCK.backspace_clicked();" /></td>
								</tr>
								
								<tr>
									<td colspan="3">
										<input type="button" class="inp_btn" value="Enter" style="width:100%;" onClick="ATTENDANCE_CLOCK.submit_clicked();" />
									</td>
								</tr>
							</table>
							
						</div>
					</form>
				</div>
			</div>
		</td>
		
		{* Right *}
		<td width="25%" align="center" valign="top">
			{*<input type="button" class="btn_close" value="Close" onClick="ATTENDANCE_CLOCK.close_clicked('0');" />*}
		</td>
	</tr>
	
</table>





<script>ATTENDANCE_CLOCK.initialize();</script>
{include file='footer.tpl'}