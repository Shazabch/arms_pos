{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

03/24/2016 17:45 Edwin
- Enchanced on showing Receipt Reference Number in tables and details pop out
- Modified from "Used Receipt" to "Receipt (Claimed)"
*}

{include file="header.tpl"}


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
{literal}
.col_rcv{
	background-color: #FFFAF0;
}
.col_used{
	background-color: #F0FFF0;
}
.col_cancel{
	background-color: #E0FFFF;
}
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
}
.calendar, .calendar table {
	z-index:100000;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function curtain_clicked(){
	$('div_item_details').hide();
}

var DEPOSIT_LISTING = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// setup calendar
		Calendar.setup({
			inputField     :    "inp_date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
		Calendar.setup({
			inputField     :    "inp_date_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_to",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
		
		new Draggable('div_deposit_history',{ handle: 'div_deposit_history_header'});
		new Draggable('div_deposit_cancel',{ handle: 'div_deposit_cancel_header'});
	},
	// function when user change deposit status
	deposit_status_changed: function(){
		var deposit_status = this.f['deposit_status'].value;
		
		$$('#span_date_label span').invoke('hide');
		$('span_date_label-'+deposit_status).show();
		
		if(deposit_status == 'used'){
			$('span_filter_issued_branch').show();
		}else{
			$('span_filter_issued_branch').hide();
		}
	},
	// function when user submit form
	submit_form: function(){
		this.f.submit();
	},
	// function when user click to show deposit history
	show_deposit_history: function(bid, date, counter_id, pos_id){
		if(!bid || !date || !counter_id || !pos_id){
			alert('Invalid Deposit');
			return false;
		}
		
		// construct params
		var params = {
		    a: 'ajax_show_deposit_history',
			bid: bid,
			date: date,
			counter_id: counter_id,
			pos_id: pos_id
		};
		
		$('div_deposit_history_content').update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){		    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_deposit_history_content').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'No respond from server';
			    alert(err_msg);
			    $('div_deposit_history_content').update(err_msg);
			}
		});
		
		curtain(true);
		center_div($('div_deposit_history').show());
	},
	// function when user click receipt no
	items_details: function (branch_id,counter_id,id,date){
		
		curtain(true);
	    center_div($('div_item_details'));
	
	    $('div_item_details').show()
		$('div_item_content').update(_loading_+' Please wait...');
	
		new Ajax.Updater('div_item_content','counter_collection.php',
		{
		    method: 'post',
		    parameters:{
				a: 'item_details',
				counter_id: counter_id,
				branch_id: branch_id,
				pos_id: id,
				date: date
			}
		});
	},
	// function when user click cancel deposit
	cancel_deposit_clicked: function(bid, date, counter_id, pos_id){		
		if(!bid || !date || !counter_id || !pos_id){
			alert('Invalid Deposit');
			return false;
		}
		
		// construct params
		var params = {
		    a: 'ajax_show_cancel_deposit',
			bid: bid,
			date: date,
			counter_id: counter_id,
			pos_id: pos_id
		};
		var THIS = this;
		$('div_deposit_cancel_content').update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){		    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_deposit_cancel_content').update(ret['html']);
	                    THIS.init_cancel_date_calendar();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'No respond from server';
			    alert(err_msg);
			    $('div_deposit_cancel_content').update(err_msg);
			}
		});
		
		curtain(true);
		center_div($('div_deposit_cancel').show());
	},
	// function to initial calendar for cancel date selection input
	init_cancel_date_calendar: function(){
		// setup calendar
		Calendar.setup({
			inputField     :    "inp_cancel_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_cancel_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});
	},
	// function when user click confirm cancel deposit
	confirm_cancel_deposit: function(){
		var cancel_type = getRadioValue(document.f_cancel_deposit['cancel_type']);
		
		if(cancel_type == 2){
			if(!document.f_cancel_deposit['cancel_date'].value.trim()){
				alert('Please select cancellation date.');
				document.f_cancel_deposit['cancel_date'].focus();
				return false;
			}
			
			if(!document.f_cancel_deposit['cancel_counter_id'].value){
				alert('Please select counter.');
				document.f_cancel_deposit['cancel_counter_id'].focus();
				return false;
			}
		}
		
		if(!confirm('Are you sure to cancel this Deposit?'))	return false;
		
		
		
		// construct params
		var params = $(document.f_cancel_deposit).serialize()+'&a=ajax_cancel_deposit';
		
		var THIS = this;
		$('span_processing_cancel_deposit').show();
		$$('#p_cancel_action input').invoke('disable');
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){		    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				$('span_processing_cancel_deposit').hide();
				$$('#p_cancel_action input').invoke('enable');
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
	                	alert('Deposit Successfully Cancelled, please manually refresh the listing.');
	                	default_curtain_clicked();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'No respond from server';
			    alert(err_msg);
			}
		});
	}
};

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
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class="errmsg">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}

{* Deposit History *}
<div id="div_deposit_history" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0 !important;" class="curtain_popup">
	<div id="div_deposit_history_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Deposit History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_deposit_history_content" style="padding:2px;">
	    
	</div>
</div>

{* Item Details *}
<div id="div_item_details" style="display:none;width:700px;height:500px;">
<div style="float:right;"><img onclick="default_curtain_clicked();" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>

{* Deposit Cancel *}
<div id="div_deposit_cancel" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0 !important;" class="curtain_popup">
	<div id="div_deposit_cancel_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Cancel Deposit</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_deposit_cancel_content" style="padding:2px;">
	    
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" method="post">
			<input type="hidden" name="show_data" value="1" />
			
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">Deposit Issued Branch: &nbsp;</b>
				{if $BRANCH_CODE eq 'HQ'}
					<select class="form-control" name="deposit_branch_id">
						<option value="">-- All --</option>
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $bid eq $smarty.request.deposit_branch_id}selected{/if}>{$b.code}</option>
						{/foreach}
					</select>
				{else}
					{$BRANCH_CODE}
				{/if}
				</div>
				
				
				<div class="col-md-3">
					<b class="form-label">Status: &nbsp;</b>
				<select class="form-control" name="deposit_status" onChange="DEPOSIT_LISTING.deposit_status_changed();">
					{foreach from=$deposit_status_list key=k item=v}
						<option value="{$k}" {if $k eq $smarty.request.deposit_status}selected {/if}>{$v}</option>
					{/foreach}
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Receipt: </b>
				<input class="form-control" type="text" name="filter_receipt" size="8" value="{$smarty.request.filter_receipt}" />
				
				</div>
			</div>

			<span id="span_filter_issued_branch" style="{if $smarty.request.deposit_status ne 'used'}display:none;{/if}">
				<p>
					<div class="row">
						<div class="col-md-3">
							<b class="form-label">Used at Branch: </b>
						{if $BRANCH_CODE eq 'HQ'}
							<select class="form-control" name="used_branch_id">
								<option value="">-- All --</option>
								{foreach from=$branch_list key=bid item=b}
									<option value="{$bid}" {if $bid eq $smarty.request.used_branch_id}selected {/if}>{$b.code}</option>
								{/foreach}
							</select>
						{else}
							{$BRANCH_CODE}
						{/if}
						</div>
						
					
						<div class="col-md-3">
							<b class="form-label">Filter Issued Branch [<a href="javascript:void(alert('- No: It will ignore Deposit Issued Branch.\n- Yes: It will filter Deposit Issued Branch.'));">?</a>]: </b>
						<select class="form-control" name="used_filter_deposit_branch">
							<option value="0">No</option>
							<option value="1" {if $smarty.request.used_filter_deposit_branch}selected {/if}>Yes</option>
						</select>
						</div>
					</div>
				</p>
			</span>
				
			<p>
				<b>
					<span id="span_date_label">
						<span id="span_date_label-rcv" style="{if $smarty.request.deposit_status and $smarty.request.deposit_status ne 'rcv'}display:none;{/if}"><b class="form-label">Received</b></span>
						<span id="span_date_label-used" style="{if $smarty.request.deposit_status ne 'used'}display:none;{/if}">Used</span>
						<span id="span_date_label-cancel" style="{if $smarty.request.deposit_status ne 'cancel'}display:none;{/if}">Cancellation</span>
					</span>
					<div class="form-inline">
						<b class="form-label mt-4">Date:</b> &nbsp;
				</b>
				&nbsp;<input class="form-control" id="inp_date_from" name="date_from" value="{$smarty.request.date_from}" size="10" /> 
				&nbsp;<img align="absbottom" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
				&nbsp;<b class="form-label">to&nbsp;</b>
				<input class="form-control" id="inp_date_to" name="date_to" value="{$smarty.request.date_to}" size="10" /> 
				&nbsp;<img align="absbottom" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
				
					</div>
		
				<input class="btn btn-primary" type="button" value="Refresh" onClick="DEPOSIT_LISTING.submit_form();" />
			</p>
		</form>
	</div>
</div>
<script type="text/javascript">DEPOSIT_LISTING.initialize();</script>


{if $smarty.request.show_data && !$err}
	<br />
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$Report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	{if !$deposit_list}
		* No Data *
	{else}
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
						<thead class="bg-gray-100">
							<tr class="header">
								<th colspan="2">&nbsp;</th>
								<th>Receipt (Deposit)</th>
								<th>Receipt Reference Number (Deposit)</th>
								<th>Branch</th>
								<th>Counter</th>
								<th>Date</th>
								<th>Cashier</th>
								<th>Deposit Amount</th>
								
								{if $smarty.request.deposit_status eq 'used'}
									<th>Receipt (Claimed)</th>
									<th>Receipt Reference Number (Claimed)</th>
									<th>Used Branch</th>
									<th>Used Counter</th>
									<th>Used Date</th>
									<th>Used Amount</th>
								{/if}
								
								{if $smarty.request.deposit_status eq 'cancel'}
									<th>Cancelled by</th>
									<th>Receipt</th>
									{*<th>Receipt Reference Number</th>*}
									<th>Cancelled Date</th>
								{/if}
								
								<th>Last Update</th>
							</tr>
							
						</thead>
						{foreach from=$deposit_list item=deposit name=fdp}
							<tbody class="fs-08">
								<tr>
									<td width="20">{$smarty.foreach.fdp.iteration}</td>
									<td width="40" nowrap>
										{* View History *}
										<img src="/ui/view.png" title="View History" class="clickable" onClick="DEPOSIT_LISTING.show_deposit_history('{$deposit.branch_id}',  '{$deposit.date}', '{$deposit.counter_id}', '{$deposit.pos_id}');" />
										
										{if $deposit.status eq 0 and $deposit.branch_id eq $sessioninfo.branch_id}
											{* Can Cancel *}
											<img src="/ui/cancel.png" title="Cancel Deposit" class="clickable" onClick="DEPOSIT_LISTING.cancel_deposit_clicked('{$deposit.branch_id}',  '{$deposit.date}', '{$deposit.counter_id}', '{$deposit.pos_id}');" />
										{/if}
									</td>
									
									{* Deposit Receipt *}
									<td align="center" class="col_rcv">
										{if $deposit.receipt_no}
											<a href="javascript:void(DEPOSIT_LISTING.items_details('{$deposit.branch_id}', '{$deposit.counter_id}', '{$deposit.pos_id}', '{$deposit.date}'));">
												{receipt_no_prefix_format branch_id=$deposit.branch_id counter_id=$deposit.counter_id receipt_no=$deposit.receipt_no}
											</a>
										{else}
											-
										{/if}
									</td>
									
									{* Deposit Receipt Number *}
									<td align="center" class="col_rcv">{$deposit.receipt_ref_no}</td>
									
									{* Deposit Branch *}
									<td align="center" class="col_rcv">{$deposit.deposit_branch_code}</td>
									
									{* Deposit Counter *}
									<td align="center" class="col_rcv">{$deposit.deposit_counter}</td>
									
									{* Deposit Date *}
									<td align="center" class="col_rcv">{$deposit.date}</td>
									
									{* Deposit Cashier *}
									<td align="center" class="col_rcv">{$deposit.deposit_cashier_name}</td>
									
									{* Deposit Amount *}
									<td align="right" class="col_rcv">{$deposit.deposit_amount|number_format:2}</td>
									
									{if $smarty.request.deposit_status eq 'used'}
										{* Used Receipt *}
										<td align="center" class="col_used">
											{if $deposit.used_receipt_no}
												<a href="javascript:void(DEPOSIT_LISTING.items_details('{$deposit.used_branch_id}', '{$deposit.used_counter_id}', '{$deposit.used_pos_id}', '{$deposit.used_date}'));">
													{receipt_no_prefix_format branch_id=$deposit.branch_id counter_id=$deposit.counter_id receipt_no=$deposit.used_receipt_no}
												</a>
											{else}
												-
											{/if}
										</td>
										
										{* Used Receipt Reference Number *}
										<td align="center" class="col_used">{$deposit.used_receipt_ref_no|default:'-'}</td>
										
										{* Used Branch *}
										<td align="center" class="col_used">{$deposit.used_branch_code|default:'-'}</td>
										
										{* Used Counter *}
										<td align="center" class="col_used">{$deposit.used_counter|default:'-'}</td>
										
										{* Used Date *}
										<td align="center" class="col_used">
											{$deposit.used_date|ifzero:'-'}		
										</td>
										
										{* Used Amount *}
										<td align="right" class="col_used">{$deposit.real_used_amt|number_format:2}</td>
									{/if}
									
									
									{if $smarty.request.deposit_status eq 'cancel'}
										{* Cancel by *}
										<td align="center" class="col_cancel">
											{if $deposit.status eq 1}{$deposit.status_verified_u|default:'-'}{else}-{/if}
										</td>
										
										{* Cancel Receipt *}
										<td align="center" class="col_cancel">
											{if $deposit.cancel_receipt_no}
												<a href="javascript:void(DEPOSIT_LISTING.items_details('{$deposit.cancel_branch_id}', '{$deposit.cancel_counter_id}', '{$deposit.cancel_pos_id}', '{$deposit.cancel_pos_date}'));">
													{receipt_no_prefix_format branch_id=$deposit.branch_id counter_id=$deposit.counter_id receipt_no=$deposit.cancel_receipt_no}
												</a>
											{else}
												-
											{/if}
										</td>
										
										{* Cancel Receipt Reference Number *}
										{*<td align="center" class="col_cancel">{$deposit.cancel_receipt_ref_no|ifzero:'-'}</td>*}
										
										{* Cancel Date *}
										<td align="center" class="col_cancel">{$deposit.cancel_date|ifzero:'-'}</td>
									{/if}
									
									<td align="center">{$deposit.last_update}</td>
								</tr>
							</tbody>
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	{/if}
{/if}
{include file="footer.tpl"}
