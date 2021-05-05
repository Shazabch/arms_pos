{*
6/8/2018 3:10 PM Justin
- Enhanced to have "base currency rate" (to be used for POS counter).

7/9/2018 2:53 PM Andy
- Enhanced base currency.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
.calendar, .calendar table {
	z-index:100000;
}
.rate_number{
	color: blue;
}
.lower_converted_base_rate{
	color: red;
}
</style>
<script type="text/javascript">
{/literal}
var phpself = '{$smarty.server.PHP_SELF}';
var foreign_currency_decimal_points = int('{$config.foreign_currency_decimal_points|default:8}');

{literal}
var FOREIGN_CURRENCY = {
	initialize : function(){
		this.f = document.f_fc;
		CURRENCY_HISTORY_DIALOG.initialize();
		
		
		new Draggable('div_foreign_currency_dialog',{ handle: 'div_foreign_currency_dialog_header'});
	},
		
	// function when user click on edit currency rate
	open_foreign_currency_dialog: function(curr_code){
		CURRENCY_RATE_EDIT_DIALOG.open(curr_code);
	},	
	// function to hide all dialog
	dialog_close: function(){
		CURRENCY_RATE_EDIT_DIALOG.close();
		CURRENCY_HISTORY_DIALOG.close();
		curtain(false);
	},
	// function when user click on view history
	open_history_form: function(curr_code){
		CURRENCY_HISTORY_DIALOG.open(curr_code);
		
	}
}

var CURRENCY_RATE_EDIT_DIALOG = {
	f: undefined,
	initialize: function(){
	
	},
	open: function(curr_code){
		// update html to loading
		$('div_foreign_currency_dialog_content').update(_loading_);
		center_div($('div_foreign_currency_dialog').show());
		curtain(true);
		var THIS = this;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				a: 'ajax_open_edit_rate',
				curr_code: curr_code
			},
			onComplete: function(msg){
				$('div_foreign_currency_dialog_content').update('');
				
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_foreign_currency_dialog_content').update(ret['html']);
						center_div($('div_foreign_currency_dialog'));
						THIS.f = document.f_fc;
		                return;
					}else{  // save failed
						if(ret['err'])	err_msg = ret['err'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
				THIS.close();
			}
		});
	},
	close: function(){
		$('div_foreign_currency_dialog').hide();
		curtain(false);
	},
	// function when user change rate or base_rate
	rate_changed: function(rate_type){
		var inp = this.f['new_'+rate_type];
		var new_rate = float(round(inp.value, foreign_currency_decimal_points));
		if(new_rate <= 0)	new_rate = '';
		inp.value = new_rate;
		
		this.refresh_converted_base_rate_notice();
	},
	// function to refresh converted base rate notice
	refresh_converted_base_rate_notice: function(){
		var span = $('span_converted_base_rate_notice');
		
		var rate = float(this.f['new_rate'].value);
		if(rate <= 0)	rate = float(this.f['old_rate'].value);
		
		var base_rate = float(this.f['new_base_rate'].value);
		if(base_rate<=0)	base_rate = float(this.f['old_base_rate'].value);
			
		if(rate > 0 || base_rate > 0){
			var converted_base_rate = float(round(1 / rate, foreign_currency_decimal_points));
		
			$(span).update(converted_base_rate).removeClassName('lower_converted_base_rate');
			$('span_converted_base_rate_low').hide();
			
			if(base_rate < converted_base_rate){
				$(span).addClassName('lower_converted_base_rate');
				$('span_converted_base_rate_low').show();
			}
		}else{
			$(span).update('');
		}		
	},
	// function when user click on update currency rate
	update_currency: function(){
		var old_rate = float(this.f['old_rate'].value);
		var new_rate = float(this.f['new_rate'].value);
		var old_base_rate = float(this.f['old_base_rate'].value);
		var new_base_rate = float(this.f['new_base_rate'].value);
		var err_msg = '';
		var same_rate = true;
		var same_base_rate = true;
		
		if(new_rate <= 0 && new_base_rate <= 0){
			err_msg = "System detected both Rate 1 and Rate 2 are empty.";
		}
		
		if(!err_msg && new_rate > 0 && old_rate != new_rate){
			same_rate = false;
		}
		if(!err_msg && new_base_rate > 0 && old_base_rate != new_base_rate){
			same_base_rate = false;
		}
		
		if(!err_msg && same_rate && same_base_rate){
			err_msg = "Currency Rate is the same, please assign different currency rate.";
		}
			
		
		if(err_msg){
			alert(err_msg);
			this.f['new_rate'].focus();
			return;
		}

		// better to have confirmation in case customer clicked wrongly
		if(!confirm("Are you sure want to update the exchange rate?")) return;
		
		this.f.submit();
	},
}

var CURRENCY_HISTORY_DIALOG = {
	f: undefined,
	initialize: function(){
		this.f = document.f_history;
		this.init_calendar();
	},
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_history_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_history_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
	},
	// function to open currency history popup
	open: function(curr_code){
		// assign code
		this.f['curr_code'].value = curr_code;
		$('span_curr_code-history').update(curr_code);

		// clear date
		this.f['history_date'].value = '';
		
		// reload history
		this.reload_history();
		
		// show dialog
		center_div($('div_foreign_currency_history').show());
		curtain(true);
	},
	close: function(){
		$('div_foreign_currency_history').hide();
		curtain(false);
	},
	// core function to load history
	reload_history: function(){
		// update html to loading
		$('div_foreign_currency_history_table').update(_loading_);
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: $(this.f).serialize(),
			onComplete: function(msg){
				$('div_foreign_currency_history_table').update('');
				
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_foreign_currency_history_table').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['err'])	err_msg = ret['err'];
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
}

{/literal}
</script>

{* CURRENCY RATE DIALOG *}
<div id="div_foreign_currency_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:500px;height:auto;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_foreign_currency_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Update Foreign Currency</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="FOREIGN_CURRENCY.dialog_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_foreign_currency_dialog_content" style="padding:2px;">
		
	</div>
</div>

{* Currency History Popup *}
<div id="div_foreign_currency_history" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:auto;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_foreign_currency_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Foreign Currency History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="CURRENCY_HISTORY_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_foreign_currency_history_content" style="padding:2px;">
		<form name="f_history" method="post" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_search_history" />
			<input type="hidden" name="curr_code" value="" />
			
			<table width="100%">
				<tr>
					<td width="100"><b>Currency Code: </b></td>
					<td><span id="span_curr_code-history"></span></td>
					<td width="100"><b>Filter Date: </b></td>
					<td>
						<input id="inp_history_date" name="history_date" value="" size="10" maxlength="10" />
						<img align="absbottom" src="ui/calendar.gif" id="img_history_date" style="cursor: pointer;" title="Select Date" />
					</td>
					<td class="r">
						<input type="button" value="Find" onClick="CURRENCY_HISTORY_DIALOG.reload_history();" />
					</td>
				</tr>
			</table>
			
			<div id="div_foreign_currency_history_table" class="stdframe" style="height:400px;background-color: #fff;overflow-y:auto;">
			</div>
		</form>
	</div>
</div>

<h1>{$PAGE_TITLE}</h1>

{if $err}
<div><div class="errmsg"><ul>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}


{if $smarty.request.t eq 'updated'}
	<p style="color:blue;">
		{if $smarty.request.old_rate ne $smarty.request.new_rate}
			{$LANG.FOREIGN_CURRENCY_UPDATED|sprintf:$smarty.request.code:$smarty.request.old_rate:$smarty.request.new_rate}<br />
		{/if}
		{if $smarty.request.old_base_rate ne $smarty.request.new_base_rate}
			{$LANG.FOREIGN_CURRENCY_UPDATED_RATE2|sprintf:$smarty.request.code:$smarty.request.old_base_rate:$smarty.request.new_base_rate}<br />
		{/if}
	</p>
{/if}

Base Currency: {$config.arms_currency.code}

<table class="report_table">
	<tr class="header">
		<th>&nbsp;</th>
		<th>Foreign Currency Code</th>
		<th>
			Rate <sup class="rate_number">1</sup><br />
			(Foreign to {$config.arms_currency.code})<br />
			[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_RATE_NOTICE|escape:javascript}'));">?</a>]
		</th>
		<th>
			Rate <sup class="rate_number">2</sup><br />
			({$config.arms_currency.code} to Foreign)<br />
			[<a href="javascript:void(alert('{$LANG.FOREIGN_CURRENCY_BASE_RATE_NOTICE|escape:javascript}'));">?</a>]
		</th>
		<th>Last Change Date</th>
		<th>User</th>
	</tr>
	{foreach from=$codeList item=fc_code}
		<tr>
			<td nowrap>
				<a href="javascript:FOREIGN_CURRENCY.open_foreign_currency_dialog('{$fc_code}');">
					<img src="ui/ed.png" title="Edit Exchange Rate for {$fc_code}" border="0">
				</a>
				<a href="javascript:FOREIGN_CURRENCY.open_history_form('{$fc_code}');">
					<img src="ui/icons/table.png" border="0" title="View History" />
				</a>
			</td>
			<td nowrap>{$fc_code}</td>
			<td nowrap align="right" class="rate_highlight">
				{if $currencyData.$fc_code.rate}
					1 {$fc_code} = {$currencyData.$fc_code.rate|default:'-'} {$config.arms_currency.code}
				{else}
					-
				{/if}
			</td>
			<td nowrap align="right" class="base_rate_highlight">
				{if $currencyData.$fc_code.base_rate}
					1 {$config.arms_currency.code} = {$currencyData.$fc_code.base_rate|default:'-'} {$fc_code}
				{else}
					-
				{/if}
			</td>
			<td nowrap align="center">{$currencyData.$fc_code.last_update|default:'-'}</td>
			<td nowrap align="center">{$currencyData.$fc_code.username|default:'-'}</td>
		</tr>
	{/foreach}
</table>



{include file='footer.tpl'}

<script>
{literal}
FOREIGN_CURRENCY.initialize();
{/literal}
</script>