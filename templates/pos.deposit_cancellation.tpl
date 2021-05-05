{*
8/20/2013 10:15 AM Andy
- Remove popup draggable.
- Fix popup cannot auto centerlize.
*}

{include file="header.tpl"}
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
.sold {
	color:#f00;
}

.deposit_tbl tr:nth-child(even){
	background-color:#dddddd;
}

.deposit_tbl {
	border-top:1px solid #000;
	border-right:1px solid #000;
	white-space:no-wrap;
}

.deposit_tbl tr.header td, .deposit_tbl tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.deposit_tbl tr.deposit_dtl:hover{
	background:#ffffcc !important;
}

.deposit_tbl textarea {
	background-color:#fff;
}

#div_item_details, #div_deposit_cancel_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}

</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var bid = '{$smarty.request.branch_id}';

{literal}
// update autocompleter parameters when vendor_id or department_id changed
var sku_autocomplete = undefined;

var DEPOSIT_CANCELLATION_MODULE = {
	curr_id: undefined,
	form_element: undefined,
	sa_id: undefined,
	prv_bid: undefined,
	initialize : function(){
		var THIS = this;
		// event when user click "add"
		$('search_deposit').observe('click', function(){
            THIS.search_deposit(this, 1);
		});

		// event when user click "Ok" from Deposit Cancellation dialog
		$('dc_ok_btn').observe('click', function(){
            THIS.cancel_deposit();
		});

				// event when user click "Ok" from Deposit Cancellation dialog
		$('dc_cancel_btn').observe('click', function(){
            curtain_clicked();
		});
		
		center_div('div_item_details');
		//new Draggable('div_item_details');
		center_div('div_deposit_cancel_details');
		//new Draggable('div_deposit_cancel_details');
		
		THIS.calendar_setup();
	},

	calendar_setup : function(){
		Calendar.setup({
			inputField     :    "date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
	},
	
	search_deposit : function(obj, tab){
		var params = "";

		if(!document.f_a.date.value){
			alert('Please select Date.');
			return false;
		}

		if(obj != undefined) obj.disabled = true;

		if(tab != undefined){
			params += "&tab="+tab;
			if(tab == 3){
				if($('inp_deposit_search').value) params += "&str_search="+$('inp_deposit_search').value;
				else{
					alert("Please enter Receipt No to search.");
					return false;
				}
			}
		}

		new Ajax.Updater('deposit_div', phpself, {
			method:'post',
			parameters: Form.serialize(document.f_a)+params,
			evalScripts: true,
			onFailure: function(m){
				alert(m.responseText);
			},
			onSuccess: function(m){
				if(obj != undefined) obj.disabled = false;
			}
		});
	},

	sa_table_appear : function(type){
		if(type == "add"){
			$('bmsg').update("Complete below form and click Add");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
			document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 1, 1); };
			document.f_b.ticket_btn.value = "Generate";
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
		}
		$('err_msg').update();
		hidediv('err_msg');

		Effect.SlideDown('div_sa_table', {
			duration: 0.5
		});
		curtain(true);
	},
	
	search_input_keypress : function(event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			DEPOSIT_CANCELLATION_MODULE.search_deposit('', 3);
		}
	},
	
	disableEnterKey : function(e){
		var key;

		if(window.event) key = window.event.keyCode;	//IE
		else key = e.which;	//firefox

		if(key == 13) return false;
		else return true;
	},

	reset_row : function(){
		var e = $('deposit_items').getElementsByClassName('no');
		var total_rows=e.length;

		for(var i=0;i<total_rows;i++)	{
			var temp_1 =new RegExp('^no');
			if (temp_1.test(e[i].id)){
				td_1=(i+1)+'.';
				e[i].innerHTML=td_1;
				e[i].id='no'+(i+1);
			}
		}

		$('autocomplete_sku').select();
	},
	
	cancel_deposit_dialog: function (pos_id, bid, counter_id, date, amt, status){
		var THIS = this;
		
		curtain(true);
		center_div('div_deposit_cancel_details');
		$('div_deposit_cancel_details').show();
		
		
		document.f_cancel.pos_id.value = pos_id;
		document.f_cancel.branch_id.value = bid;
		document.f_cancel.counter_id.value = counter_id;
		document.f_cancel.date.value = date;
		document.f_cancel.status.value = status;
		document.f_cancel.deposit_amount.value = amt;
		document.f_cancel.cancel_reason.focus();
	},
	
	cancel_deposit : function(){
		//var date = document.f_a.date.value;
		if(!document.f_cancel.new_counter_id.value || !document.f_cancel.cancel_reason.value.trim()) return;
		if(!confirm('Are you sure want to cancel this deposit?')) return;
		
		new Ajax.Updater('deposit_div', phpself, {
			method:'post',
			parameters: Form.serialize(document.f_cancel),
			evalScripts: true,
			onFailure: function(m){
				alert(m.responseText);
			},
			onSuccess: function(m){
				if(m.responseText != "OK") alert(m.responseText);
			},
			onComplete: function(m){
				//if($('delete_msg'))	$('delete_msg').remove();
				DEPOSIT_CANCELLATION_MODULE.search_deposit('', 1);
				curtain_clicked();
			}
		});
	},
	
	trans_detail : function(counter_id,cashier_id,date,pos_id,branch_id)
	{
		curtain(true);
		
		center_div('div_item_details');
		$('div_item_details').show();
		$('div_item_content').update(_loading_+' Please wait...');

		new Ajax.Updater('div_item_content','counter_collection.php',
		{
			method: 'post',
			parameters:{
				a: 'item_details',
				branch_id: branch_id,
				counter_id: counter_id,
				pos_id: pos_id,
				cashier_id: cashier_id,
				date: date
			}
		});
	},
}
function curtain_clicked(){
	hidediv('div_item_details');
	hidediv('div_deposit_cancel_details');
	curtain(false);
}

{/literal}
</script>

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>

<!-- Deposit Cancellation dialog -->
<form name="f_cancel" method="post" onSubmit="return false;">
<div id="div_deposit_cancel_details" style="display:none;width:350px;height:200px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<h3>Deposit Cancellation Menu</h3>
Please complete below form and click OK to cancel deposit:<br /><br />
<div id="div_deposit_cancel_content">
	<table width="100%">
		<tr>
			<th>Counter:</th>
			<td>
				<select name="new_counter_id">
					<option value="">-Please Select-</option>
					{foreach from=$counters key=r item=counter}
						<option value="{$counter.id}">{$counter.network_name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Cancel Reason:</th>
			<td><input name="cancel_reason" size="30" maxlength="45"></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<br />
				<button id="dc_ok_btn">Ok</button>
				<button id="dc_cancel_btn">Cancel</button>
			</td>
		</tr>
	</table>
	<input type="hidden" name="a" value="cancel_deposit">
	<input type="hidden" name="pos_id">	
	<input type="hidden" name="branch_id">
	<input type="hidden" name="counter_id">
	<input type="hidden" name="date">
	<input type="hidden" name="status">
	<input type="hidden" name="deposit_amount">
</div>
</div>
</form>

<h1>{$PAGE_TITLE}</h1>
<div class="stdframe" style="background:#fff;">
<div id="history_popup" style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id="history_popup_content"></div>
</div>
<form name="f_a" method="post">
<input type="hidden" name="a" value="search">
<table>
	{*if $BRANCH_CODE eq 'HQ'}
		<th>Branch</th>
		<td>
			<select name="branch_id" id="branch_id" onKeyPress="return DEPOSIT_CANCELLATION_MODULE.disableEnterKey(event);" onchange="DEPOSIT_CANCELLATION_MODULE.search_deposit('', 1);">
				{foreach from=$branches item=r}
				    <option value="{$r.id}" {if $smarty.request.branch_id eq $r.id} selected {/if}>{$r.code}</option>
				    {if $smarty.request.branch eq $r.id}
						{assign var=bcode value=$r.code}
				    {/if}
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
	{else*}
		<input type="hidden" name="branch_id" id="branch_id" value="{$sessioninfo.branch_id}">
	{*/if*}
	<th>Select Date</th>
	<td>
		<input id="date" name="date" value="{$smarty.request.date|default:$smarty.now|date_format:'%Y-%m-%d'}" size=10 readonly > <img align=absbottom src="ui/calendar.gif" id="t_date" style="cursor: pointer;" title="Select Date" />
	</td>
	<td>
		<input type="button" id="search_deposit" value="Search">
		<span id='loading_id'></span>
	</td>
</tr>

</table>

<input name="item_del_list" type="hidden">
<div id="deposit_div"></div>
</form>
{include file="footer.tpl"}
{literal}
<script>
DEPOSIT_CANCELLATION_MODULE.initialize();
</script>
{/literal}
