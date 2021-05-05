{*
10/26/2012 6:18 PM Andy
- Add to show Cash from membership counter.
- Fix a bug which physical cash amt dint re-calculate.

11/3/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

12/12/2012 4:16 PM Justin
- Enhanced to include extra payment type from config.

12:14 PM 12/18/2012 Justin
- Bug fixed on after click on add row, cannot show calendar list when click on calendar icon.

1/3/2013 6:17 PM Justin
- Fix payment type wrongly count 2 times of Cash (Membership Counter).

1/17/2013 3:51 PM Justin
- Enhanced to take out the Cash (Membership Counter) to become stand alone table.
- Enhanced to exclude the sum up for Cash (membership Counter) amount.

3/24/2014 5:27 PM Justin
- Modified the wording from "Check" to "Cheque" and "Finalize" to "Finalise".

4/13/2017 14:33 Qiu Ying
- Bug fixed on Counter Collection Payment Type Missing

4/25/2017 2:44 PM Khausalya
- Enhanced changes from RM to use config setting. 

3/15/2019 3:59 PM Andy
- Fix javascript error if payment type not exists.
*}

{assign var=is_print value=$smarty.request.is_print}
{if $is_print}
	{include file='header.print.tpl'}
{else}
	{include file='header.tpl'}
{/if}

{if !$is_print}
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/if}


<style>
{literal}

input[disabled]{
	background-color: gray !important;
}
{/literal}
</style>

{if !$is_print}
<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';
var counter_collection_show_membership_receipt = int('{$config.counter_collection_show_membership_receipt}');

var type_list = [];
{foreach from=$payment_type item=type}
	{if $type ne "mem_cash_amt"}
		type_list.push('{$type}');
	{/if}
{/foreach}

{literal}

var CO2 = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
		    inputField     :    "inp_date",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
	},
	check_form: function(){
		if(this.f['date'].value.trim()==''){
			alert('Please select date');
			this.f['date'].focus();
			return false;
		}
		return true;
	},
	submit_form: function(t){
		this.f['is_print'].value = 0;
		
		if(!this.check_form())	return false;
		
		if(t == 'is_print'){
			this.f.target = '_blank';
			this.f['is_print'].value = 1;
		}
		
		this.f.submit();
		this.f.target = '';
	}
}

var CO2_FORM = {
	f: undefined,
	initialize: function(){
		if(!document.f_b)	return false;
		
		this.f = document.f_b;
		
		this.row_changed();
		
		// init calendar
		var tr_co_item_list = $$('#tbody_co_items_list tr.tr_co_item');
		
		for(var i = 0; i<tr_co_item_list.length; i++){
			var tmp_row_num = this.get_row_num_by_ele(tr_co_item_list[i]);
		
			this.init_calendar_for_row(tmp_row_num);
		}
		
		// recalc total variance
		this.recalc_total_collection(true);
	},
	// init calendar event
	init_calendar_for_row: function(row_num){
		Calendar.setup({
		    inputField     :    "inp_date_as_pos-"+row_num,     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_as_pos-"+row_num,  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
	},
	// function when user change amount
	amount_changed: function(row_num){
		this.f['row_amt['+row_num+']'].value = round2(this.f['row_amt['+row_num+']'].value);
		
		this.row_changed();
	},
	// function to get row num by element
	get_row_num_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_co_item')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var row_num = int(parent_ele.id.split('-')[1]);
		return row_num;
	},
	// function to get current maximum row num value
	get_max_row_num: function(){
		var max_row_num = 0;
		var tr_co_item_list = $$('#tbody_co_items_list tr.tr_co_item');
		
		for(var i = 0; i<tr_co_item_list.length; i++){
			var tmp_row_num = this.get_row_num_by_ele(tr_co_item_list[i]);
			
			if(tmp_row_num > max_row_num)	max_row_num = tmp_row_num;
		}
		
		return max_row_num;
	},
	// function when user click add more row
	add_more_row_clicked: function(){
		var new_tr = $('tr_co_item-__TMP_ROW_NO__').cloneNode(true);
		
		var new_row_num = this.get_max_row_num()+1;	// get new row num
		
		new_tr.id = "tr_co_item-"+(new_row_num);	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_num);
		$(new_tr).update(new_html);
		
		$('tbody_co_items_list').appendChild(new_tr);
		this.init_calendar_for_row(new_row_num);
		
		this.row_changed();
	},
	// function when user click delete row
	delete_row_clicked: function(row_num){
		if(!row_num)	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_co_item-'+row_num).remove();
		
		this.row_changed();
	},
	// function when user click swap row
	swap_row: function(direction, row_num){
		var swap_tr = undefined;
		var tr_co_item = $('tr_co_item-'+row_num);
		
		if(direction=='up'){
			swap_tr = tr_co_item.previous('tr.tr_co_item');
		}else{
	    	swap_tr = tr_co_item.next('tr.tr_co_item');
		}
		
		if(!swap_tr)    return; // nothing to swap
		
		swap_ele(tr_co_item, swap_tr);
		
		this.row_changed();
	},
	// function to check all imgage swap up/down
	check_swap_img_visibility: function(){
		// get all item row list
		var tr_co_item_list = $$('#tbody_co_items_list tr.tr_co_item');
		
		// loop row
		for(var i = 0; i<tr_co_item_list.length; i++){
			var tmp_row_num = this.get_row_num_by_ele(tr_co_item_list[i]);
			
			var img_swap_up = $('img_swap_up-'+tmp_row_num);
			var img_swap_down = $('img_swap_down-'+tmp_row_num);
			
			var hidden_up = '';
			var hidden_down = '';
			
			if(i == 0){
				hidden_up = 'hidden';
			}
			if(i == tr_co_item_list.length-1){
				hidden_down = 'hidden';
			}
			
			img_swap_up.style.visibility = hidden_up;
			img_swap_down.style.visibility = hidden_down;
		}
	},
	// function to call when add/swap/remove row
	row_changed: function(){
		// check swap img
		this.check_swap_img_visibility();
		
		// recalculate physical cash amt
		this.recalc_phy_cash_amt();
	},
	// function to recalculate physical cash amt
	recalc_phy_cash_amt: function(){
		// get all item row list
		var tr_co_item_list = $$('#tbody_co_items_list tr.tr_co_item');
		
		var phy_cash_amt = 0;
		
		// loop row
		for(var i = 0; i<tr_co_item_list.length; i++){
			var tmp_row_num = this.get_row_num_by_ele(tr_co_item_list[i]);
			
			phy_cash_amt += float(this.f['row_amt['+tmp_row_num+']'].value);
		}
		
		$('span-phy-cash_amt').update(number_format(phy_cash_amt, 2));
		$('inp-phy-cash_amt').value = phy_cash_amt;
		
		//this.recalc_row_variance('cash_amt');
		this.collection_phy_changed('cash_amt');
	},
	// function to check form before submit
	check_form: function(){
		return true;
	},
	// function when user click save
	save_form_clicked: function(){
		var THIS = this;
		
		if(!this.check_form())	return false;
		
		var params = $(this.f).serialize();

		THIS.toggle_action_area(false);	//disable action 
		$('span_action_processing').show();
		//alert((new Date()).getTime());
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				// hide the loading icon
			    THIS.toggle_action_area(true);
			    $('span_action_processing').hide();
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
	                	alert('Save Successfully');
						window.location = '?load_report=1&is_print=0&date='+THIS.f['date'].value;	// refresh the page
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
	},
	// function to enable/disable save button
	toggle_action_area: function(enable){
		$$('#p_action_area input').each(function(inp){
			inp.disabled = !enable;
		});
	},
	// function when user changed collection physical value
	collection_phy_changed: function(type){
		if(!type)	return false;
		
		mfz($('inp-phy-'+type));	// round number
		
		this.recalc_row_variance(type);	// recalc this row
		
		this.recalc_total_collection();	// recalc total variance
	},
	// function to recalculate row variance
	recalc_row_variance: function(type){
		if(!type || !$('inp-sys-'+type))	return false;
		var sys_amt = float($('inp-sys-'+type).value);
		var phy_amt = float($('inp-phy-'+type).value);
		var variance_amt = float(round(phy_amt - sys_amt, 2));
		
		$('span-variance-'+type).update(number_format(variance_amt, 2));
		$('inp-variance-'+type).value = variance_amt;
	},
	// function to recalculate total variance or row vairance
	recalc_total_collection: function(recalc_all_row){
		//var type_list = ['cash_amt', 'check_amt', 'credit_amt', 'debit_amt', 'voucher_amt', 'coupon_amt', 'others_amt'];
		
		var THIS = this;
		
		// need recalculate all row variance first
		if(recalc_all_row){
			$A(type_list).each(function(type){
				THIS.recalc_row_variance(type);
			});
		}
		
		var sys_total_amt = 0;
		var phy_total_amt = 0;
		
		$A(type_list).each(function(type){
			if($('inp-sys-'+type)){
				var sys_amt = float($('inp-sys-'+type).value);
				var phy_amt = float($('inp-phy-'+type).value);
				
				sys_total_amt += sys_amt;
				phy_total_amt += phy_amt;
			}			
		});
		
		$('span-sys-total_amt').update(number_format(sys_total_amt, 2));
		$('inp-sys-total_amt').value = sys_total_amt;
		
		$('span-phy-total_amt').update(number_format(phy_total_amt, 2));
		$('inp-phy-total_amt').value = phy_total_amt;
		
		var variance_amt = float(round(phy_total_amt - sys_total_amt, 2));
		$('span-variance-total_amt').update(number_format(variance_amt, 2));
		$('inp-variance-total_amt').value = variance_amt;
	}
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE} {if $is_print}({$BRANCH_CODE}, {$smarty.request.date}){/if} {if $data.finalized} - Finalised{/if}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$is_print}
<form name="f_a" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="is_print" value="0" />
	
	<b>Select Date:</b> 
	<input id="inp_date" name="date" value="{$smarty.request.date}" size="10" /> 
	<img align="absbottom" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Refresh" onClick="CO2.submit_form();" />
	
	{* <input type="button" value="Print" onClick="CO2.submit_form('is_print');" /> *}
</form>
{/if}

<br />
{if $smarty.request.load_report and !$err}
	{if !$data}-- No Data --
	{else}
		{* assign var=is_print value=0 *}
		
		{if $is_print || $data.finalized}
			{assign var=noedit value=1}
		{else}
			{assign var=noedit value=0}
		{/if}
		
		{if !$is_print}
			<table style="display:none;">
				{assign var=co_row_num value="__TMP_ROW_NO__"}
				{include file="pos_report.counter_collection_co2.co_item_row.tpl"}
			</table>
		{/if}
		
		<form name="f_b" onSubmit="return false;">
			<input type="hidden" name="a" value="save_form" />
			<input type="hidden" name="date" value="{$smarty.request.date}" />
			
			<table class="report_table {if $is_print}tb{/if}" id="tbl_co_items_list"  {if $is_print}width="100%" cellpadding="2" cellspacing="0" {/if}>
				<tr class="header">
					{if !$is_print and !$data.finalized}
						<th>&nbsp;</th>
					{/if}
					<th>Date (As Per Pos) <br />(YYYY-MM-DD)</th>
					<th>Collection No</th>
					<th>Amount ({$config.arms_currency.symbol})</th>
					
					{if $is_print}
						<th>Chief Cashier Initial</th>
						<th>Duty Manager Initial</th>
						<th>Safiguard Siri No & acknowledgement</th>
						<th>Bank & Bank In Date</th>
						<th>Remarks</th>
						<th>For A/C Use BIS Ref</th>
					{/if}
				</tr>
				
				<tbody id="tbody_co_items_list">
					{assign var=co_row_num value=0}
				
					{foreach from=$data.item_list item=co_item}
						{assign var=co_row_num value=$co_row_num+1}
						
						{include file="pos_report.counter_collection_co2.co_item_row.tpl"}
					{/foreach}
					
					{if !$is_print}
						{assign var=co_item value=""}
						
						{section loop=5 name=s start=$co_row_num}
							{assign var=co_row_num value=$co_row_num+1}
							{include file="pos_report.counter_collection_co2.co_item_row.tpl"}
						{/section}
					{/if}
				</tbody>				
			</table>
			{if !$is_print && !$noedit}			
				<br />
				<button style="font:bold 20px Arial; background-color:#091; color:#fff;" onClick="CO2_FORM.add_more_row_clicked();">Add New Row</button>
			{/if}
			
			<br /><br />
			<table class="report_table {if $is_print}tb{/if}" {if $is_print}cellpadding="2" cellspacing="0"{/if}>
				<tr class="header">
					<th>Till Collection</th>
					<th>System</th>
					<th>Physical</th>
					<th>Variance</th>
				</tr>
				
				{assign var=sys_total_amt value=0}
				{assign var=phy_total_amt value=0}
				
				<!-- Cash -->
				<tr>
					<td>Cash</td>
					
					<!-- System -->
					<td align="right">
						{$data.data.all_counter.cashier_sales.cash.amt|number_format:2}
						
						{if !$is_print && !$noedit}
							<input type="hidden" id="inp-sys-cash_amt" value="{$data.data.all_counter.cashier_sales.cash.amt}" />
						{/if}
					</td>
					
					<!-- Physical -->
					<td align="right">
						{if $is_print || $noedit}
							{$data.form.cash_amt|number_format:2}
						{else}
							<span id="span-phy-cash_amt">{$data.form.cash_amt|number_format:2}</span>
							<input type="hidden" name="cash_amt" id="inp-phy-cash_amt" value="{$data.form.cash_amt}" />
						{/if}
						
						
					</td>
					
					<!-- Variance -->
					<td align="right">
						{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.cash.amt}
						{assign var=phy_total_amt value=$phy_total_amt+$data.form.cash_amt}
						
						{assign var=row_variance value=$data.form.cash_amt-$data.data.all_counter.cashier_sales.cash.amt}
						{assign var=row_variance value=$row_variance|round2}
						
						{if $is_print || $noedit}
							{$row_variance|number_format:2}
						{else}
							<span id="span-variance-cash_amt">-</span>
							<input type="hidden" id="inp-variance-cash_amt" />
						{/if}
						
					</td>
				</tr>
				
				<!-- Check -->
				{if in_array("check_amt", $payment_type) || $data.data.all_counter.cashier_sales.check.amt ne 0 || $data.form.check_amt ne 0}
					<tr>
						<td>Cheque</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.check.amt|number_format:2}
							
							{if !$is_print && !$noedit}
								<input type="hidden" id="inp-sys-check_amt" value="{$data.data.all_counter.cashier_sales.check.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.check_amt|number_format:2|ifzero:'&nbsp;'}
							{else}					
								<input type="text" name="check_amt" id="inp-phy-check_amt" value="{$data.form.check_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('check_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.check.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.check_amt}
							
							{assign var=row_variance value=$data.form.check_amt-$data.data.all_counter.cashier_sales.check.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-check_amt">-</span>
								<input type="hidden" id="inp-variance-check_amt" />
							{/if}
						</td>
					</tr>
				{/if}
				
				<!-- Credit Card -->
				{if in_array("credit_card_amt", $payment_type) || $data.data.all_counter.cashier_sales.credit_cards.amt ne 0 || $data.form.credit_card_amt ne 0}
					<tr>
						<td>Credit Card</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.credit_cards.amt|number_format:2}
							
							{if !$is_print && !$noedit}
								<input type="hidden" id="inp-sys-credit_card_amt" value="{$data.data.all_counter.cashier_sales.credit_cards.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.credit_card_amt|number_format:2|ifzero:'&nbsp;'}
							{else}
								<input type="text" name="credit_card_amt" id="inp-phy-credit_card_amt" value="{$data.form.credit_card_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('credit_card_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.credit_cards.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.credit_card_amt}
							
							{assign var=row_variance value=$data.form.credit_card_amt-$data.data.all_counter.cashier_sales.credit_cards.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print|| $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-credit_card_amt">-</span>
								<input type="hidden" id="inp-variance-credit_card_amt" />
							{/if}
						</td>
					</tr>
				{/if}
				
				<!-- Debit Card -->
				{if in_array("debit_amt", $payment_type) || $data.data.all_counter.cashier_sales.debit.amt ne 0 || $data.form.debit_amt ne 0}
					<tr>
						<td>Debit Card</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.debit.amt|number_format:2}
							
							{if !$is_print || !$noedit}
								<input type="hidden" id="inp-sys-debit_amt" value="{$data.data.all_counter.cashier_sales.debit.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.debit_amt|number_format:2|ifzero:'&nbsp;'}
							{else}
								<input type="text" name="debit_amt" id="inp-phy-debit_amt" value="{$data.form.debit_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('debit_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.debit.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.debit_amt}
							
							{assign var=row_variance value=$data.form.debit_amt-$data.data.all_counter.cashier_sales.debit.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-debit_amt">-</span>
								<input type="hidden" id="inp-variance-debit_amt" />
							{/if}
						</td>
					</tr>
				{/if}
				
				<!-- Voucher -->
				{if in_array("voucher_amt", $payment_type) || $data.data.all_counter.cashier_sales.voucher.amt ne 0 || $data.form.voucher_amt ne 0}
					<tr>
						<td>Voucher</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.voucher.amt|number_format:2}
							
							{if !$is_print && !$noedit}
							<input type="hidden" id="inp-sys-voucher_amt" value="{$data.data.all_counter.cashier_sales.voucher.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.voucher_amt|number_format:2|ifzero:'&nbsp;'}
							{else}
								<input type="text" name="voucher_amt" id="inp-phy-voucher_amt" value="{$data.form.voucher_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('voucher_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.voucher.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.voucher_amt}
							
							{assign var=row_variance value=$data.form.voucher_amt-$data.data.all_counter.cashier_sales.voucher.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-voucher_amt">-</span>
								<input type="hidden" id="inp-variance-voucher_amt" />
							{/if}
						</td>
					</tr>
				{/if}
				
				<!-- Coupon -->
				{if in_array("coupon_amt", $payment_type) || $data.data.all_counter.cashier_sales.coupon_amt.amt ne 0 || $data.form.coupon_amt ne 0}
					<tr>
						<td>Coupon</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.coupon.amt|number_format:2|ifzero:'&nbsp;'}
							
							{if !$is_print && !$noedit}
								<input type="hidden" id="inp-sys-coupon_amt" value="{$data.data.all_counter.cashier_sales.coupon.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.coupon_amt|number_format:2|ifzero:'&nbsp;'}
							{else}
								<input type="text" name="coupon_amt" id="inp-phy-coupon_amt" value="{$data.form.coupon_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('coupon_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.coupon.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.coupon_amt}
							
							{assign var=row_variance value=$data.form.coupon_amt-$data.data.all_counter.cashier_sales.coupon.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-coupon_amt">-</span>
								<input type="hidden" id="inp-variance-coupon_amt" />
							{/if}
						</td>
					</tr>
				{/if}

				<!-- extra payment type -->
				{foreach from=$extra_payment_type item=ptype}
					{assign var=pp_type value="`$ptype`_amt"}
					<tr>
						<td>{$all_extra_payment_type.$ptype}</td>
						
						<!-- System -->
						<td align="right">
							{$data.data.all_counter.cashier_sales.$ptype.amt|number_format:2|ifzero:'&nbsp;'}
							
							{if !$is_print && !$noedit}
								<input type="hidden" id="inp-sys-{$pp_type}" value="{$data.data.all_counter.cashier_sales.$ptype.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.extra.$pp_type|number_format:2|ifzero:'&nbsp;'}
							{else}
								<input type="text" name="extra[{$pp_type}]" id="inp-phy-{$pp_type}" value="{$data.form.extra.$pp_type|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('{$pp_type}');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.$ptype.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.extra.$pp_type}
							
							{assign var=row_variance value=$data.form.extra.$pp_type-$data.data.all_counter.cashier_sales.$ptype.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-{$pp_type}">-</span>
								<input type="hidden" id="inp-variance-{$pp_type}" />
							{/if}
						</td>
					</tr>
				{/foreach}
				
				<!-- Others -->
				<tr>
					<td>Others</td>
					
					<!-- System -->
					<td align="right">
						{* $data.data.all_counter.cashier_sales.others.amt|number_format:2 *}
						0.00
						{if !$is_print && !$noedit}
							<input type="hidden" id="inp-sys-others_amt" value="0" />
						{/if}
					</td>
					
					<!-- Physical -->
					<td align="right">
						{if $is_print || $noedit}
							{$data.form.others_amt|number_format:2|ifzero:'&nbsp;'}
						{else}
							<input type="text" name="others_amt" id="inp-phy-others_amt" value="{$data.form.others_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('others_amt');" />
						{/if}
					</td>
					
					<!-- Variance -->
					<td align="right">
						{assign var=sys_total_amt value=$sys_total_amt+$data.data.all_counter.cashier_sales.others.amt}
						{assign var=phy_total_amt value=$phy_total_amt+$data.form.others_amt}
						
						{assign var=row_variance value=$data.form.others_amt-$data.data.all_counter.cashier_sales.others.amt}
						{assign var=row_variance value=$row_variance|round2}
						
						{if $is_print || $noedit}
							{$row_variance|number_format:2}
						{else}
							<span id="span-variance-others_amt">-</span>
							<input type="hidden" id="inp-variance-others_amt" />
						{/if}
					</td>
				</tr>
				
				<!-- Total Variance -->
				<tr class="header">
					<td>Total Variance</td>
					
					<!-- System -->
					<td align="right">
						{if $is_print || $noedit}
							{$sys_total_amt|number_format:2}
						{else}
							<span id="span-sys-total_amt"></span>
							<input type="hidden" id="inp-sys-total_amt" value="0" />
						{/if}
					</td>
					
					<!-- Physical -->
					<td align="right">
						{if $is_print || $noedit}
							{$phy_total_amt|number_format:2}
						{else}
							<span id="span-phy-total_amt">-</span>
							<input type="hidden" id="inp-phy-total_amt" />
						{/if}
					</td>
					
					<!-- Variance -->
					<td align="right">
						{assign var=row_variance value=$phy_total_amt-$sys_total_amt}
						{assign var=row_variance value=$row_variance|round2}
						
						{if $is_print || $noedit}
							{$row_variance|number_format:2}
						{else}
							<span id="span-variance-total_amt">-</span>
							<input type="hidden" id="inp-variance-total_amt" name="total_variance" />
						{/if}
					</td>
				</tr>
			</table>
			
			{if $config.counter_collection_show_membership_receipt}
				<br />
				<table class="report_table {if $is_print}tb{/if}" {if $is_print}cellpadding="2" cellspacing="0"{/if}>
					<tr class="header">
						<th>Till Collection</th>
						<th>System</th>
						<th>Physical</th>
						<th>Variance</th>
					</tr>
					<!-- Cash -->
					<tr>
						<td>Cash (Membership Counter)</td>
						
						<!-- System -->
						<td align="right">
							{$data.mem_data.all_counter.cash.amt|number_format:2}
							
							{if !$is_print && !$noedit}
								<input type="hidden" id="inp-sys-mem_cash_amt" value="{$data.mem_data.all_counter.cash.amt}" />
							{/if}
						</td>
						
						<!-- Physical -->
						<td align="right">
							{if $is_print || $noedit}
								{$data.form.mem_cash_amt|number_format:2|ifzero:'&nbsp;'}
							{else}					
								<input type="text" name="mem_cash_amt" id="inp-phy-mem_cash_amt" value="{$data.form.mem_cash_amt|number_format:2:".":""}" size="10" style="text-align:right;" onChange="CO2_FORM.collection_phy_changed('mem_cash_amt');" />
							{/if}
						</td>
						
						<!-- Variance -->
						<td align="right">
							{assign var=sys_total_amt value=$sys_total_amt+$data.mem_data.all_counter.cash.amt}
							{assign var=phy_total_amt value=$phy_total_amt+$data.form.mem_cash_amt}
							
							{assign var=row_variance value=$data.form.mem_cash_amt-$data.mem_data.all_counter.cash.amt}
							{assign var=row_variance value=$row_variance|round2}
							
							{if $is_print || $noedit}
								{$row_variance|number_format:2}
							{else}
								<span id="span-variance-mem_cash_amt">
									{assign var=mem_cash_var value=$data.form.mem_cash_amt-$data.mem_data.all_counter.cash.amt}
									{$mem_cash_var|number_format:2|default:'-'}
								</span>
								<input type="hidden" id="inp-variance-mem_cash_amt" />
							{/if}
							
						</td>
					</tr>
				</table>
			{/if}
		</form>
	
		
		<p align="center" id="p_action_area">
			{if !$is_print && !$noedit}
				<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="CO2_FORM.save_form_clicked();" />
			{/if}
			{if !$is_print}
				<input type="button" value="Print" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="CO2.submit_form('is_print');" />
			{/if}
			<br />
			<span id="span_action_processing" style="display:none;background: yellow;padding:2px;">
				<img src="/ui/clock.gif" align="absmiddle" /> Processing…
			</span>
		</p>
	
	{/if}
{/if}

{if !$is_print}
	<script>
		CO2.initialize();
		{if !$noedit}
			CO2_FORM.initialize();
		{/if}
	</script>
	{include file='footer.tpl'}
{else}
	<script type="text/javascript">window.print();</script>
{/if}
