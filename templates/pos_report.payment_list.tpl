{*
10/6/2011 3:11:44 PM Alex
- created

10/21/2011 9:44:46 AM Alex
- add default "-"

11/12/2012 4:57 PM Andy
- Improve report program structure.
- Add default value "-- Please Select --" for counter and payment type dropdown.
- Add report can check for form selection and return if found error.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

06/29/2016 15:00 Edwin
- Bug fixed on incorrect receipt amount.

6/26/2018 11:39 AM Justin
- Enhanced the report to have the ability to filter payment type using foreign currency.

06/30/2020 04:43 PM Sheila
- Updated button css.
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
.bold{
	font-weight:bold;
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
</style>
{/literal}

<script type="text/javascript">

{literal}
var branch_allowed_payment_type_list = {};
{/literal}

{foreach from=$branch_allowed_payment_type_list key=bid item=ptype_list}
	{foreach from=$ptype_list key=ptype item=allowed}
		var tmp_bid = '{$bid}';
		var tmp_ptype = '{$ptype}';
		
		{literal}
			if(!branch_allowed_payment_type_list[tmp_bid]){
				branch_allowed_payment_type_list[tmp_bid] = [];
			}
			
			branch_allowed_payment_type_list[tmp_bid].push(tmp_ptype);
		{/literal}
		
		
	{/foreach}
{/foreach}

{literal}
function print_report(){
//	document.f_a.target="_blank";
	window.print();
}

function reset_report(){
//	document.f_a.target="";
}

function init_calendar(){
    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
}

function items_details(branch_id,counter_id,id,date){
	
	curtain(true);
    center_div($('div_item_details'));

    $('div_item_details').show()
	$('div_item_content').update(_loading_);

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
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

function counters_changed(){
	check_and_refresh_payment_type_list();
}

function check_and_refresh_payment_type_list(don_reset_to_none){
	var c_value = document.f_a['counters'].value;
	var bid = 0;
	var counter_id = 0;
	var allowed_ptype_list = [];

	if(c_value){ // got select branch or counter
		bid = c_value.split('|')[0];
		counter_id = c_value.split('|')[1];

		// this branch got set
				
		if(bid && branch_allowed_payment_type_list[bid]){
			allowed_ptype_list = branch_allowed_payment_type_list[bid];
		}
	}
	
	// hide normal payment type
	$$('#f_a option.opt_normal_ptype').invoke('hide');
	// hide credit card
	$('opt_credit_card').hide();
	$('optgroup_credit_card').hide();
	if($('opt_foreign_currency') != undefined){
		$('opt_foreign_currency').hide();
		$('optgroup_foreign_currency').hide();
	}
	
	// loop for each payment type
	$A(allowed_ptype_list).each(function(ptype){
		if(ptype == 'credit_card'){	// credit card
			$('opt_credit_card').show();
			$('optgroup_credit_card').show();
		}else if(ptype == 'foreign_currency'){
			$('opt_foreign_currency').show();
			$('optgroup_foreign_currency').show();
		}else{	// other normal payment
			var opt_id = 'opt_normal_ptype-'+ptype;
			if($(opt_id)){
				$(opt_id).show();
			}
		}
	});
	
	if(!don_reset_to_none)	document.f_a['payment_type'].selectedIndex = 0;
}

function check_form(){
	if(document.f_a['counters'].value == ''){
		alert('Please select counter.');
		return false;
	}
	
	if(document.f_a['payment_type'].value == ''){
		alert('Please select payment type.');
		return false;
	}
	
	return true;
}

function submit_form(type){
	if(!check_form())	return false;
	
	document.f_a['export_excel'].value = '';
	
	if(type == 'excel'){
		document.f_a['export_excel'].value = 1;
	}
	document.f_a.submit();
}
{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class="errmsg">
{foreach from=$err item=e}
<li> {$e} </li>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<div class="noprint">
<div id="div_item_details" style="display:none;width:700px;height:450px;">
	<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
	<h3 align="center">Receipt Details</h3>
	<div id="div_item_content"></div>
</div>
	<form name="f_a" id="f_a" method="post" class="form" onSubmit="return false;">
		<input type="hidden" name="ajax" value="1">
		<input type="hidden" name="show_report" value="1">
		<input type="hidden" name="export_excel" />
		
		{*<input type="hidden" name=report_title value="{$report_title}">*}
	
		<b>Counter</b> 
		<select name="counters" onChange="counters_changed();">
		<option value="">-- Please Select --</option>
		{foreach from=$counters item=r}
			{capture assign=counter_all}{$r.branch_id}|all{/capture}
			{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
			{if $last_bid ne $r.branch_id}
			    <option value="{$counter_all}" {if $smarty.request.counters eq $counter_all}selected {/if}>{$r.code}</option>
			    {assign var=last_bid value=$r.branch_id}
			{/if}
			<option value="{$counter_item}" {if $smarty.request.counters eq $counter_item}selected {/if}>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$r.network_name}
			</option>
		{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;

		<b>Payment Type</b>
		<select name="payment_type">
			<option value="">-- Please Select --</option>
		    {foreach from=$payment_type key=pt item=pt_label}
		    	<option {if $pt ne 'Cash'}class="opt_normal_ptype" id="opt_normal_ptype-{$pt|lower}"{/if} value="{$pt}" {if $smarty.request.payment_type eq $pt}selected {/if} >{$pt_label}</option>
		    {/foreach}
		    
			{if ($pos_config.credit_card)}
				<option class="bold" value="credit_card" id="opt_credit_card" {if $smarty.request.payment_type eq 'credit_card'}  selected {/if}>Credit Card</option>
				<optgroup id="optgroup_credit_card">
					{foreach from=$pos_config.credit_card item=cc}
						<option value="{$cc}" {if $smarty.request.payment_type eq $cc} selected {/if}>{$cc}</option>
					{/foreach}
				</optgroup>
			{/if}
			{if ($config.foreign_currency)}
				<option class="bold" value="foreign_currency" id="opt_foreign_currency" {if $smarty.request.payment_type eq 'foreign_currency'}  selected {/if}>Foreign Currency</option>
				<optgroup id="optgroup_foreign_currency">
					{foreach from=$config.foreign_currency key=fc_code item=fc_settings}
						<option value="{$fc_code}" {if $smarty.request.payment_type eq $fc_code} selected {/if}>{$fc_code}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>Date</b> <input size=10 type=text name="date_to" value="{$smarty.request.date_to}" id="date_to">
		<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		&nbsp;&nbsp;&nbsp;&nbsp;
		<p>
		<b>Search Remark</b> <input type=text name="search_remark" value="{$smarty.request.search_remark}">
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button class="btn btn-primary" onClick="submit_form();">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
		{/if}
	
		<input name=print type="button" value="Print Report" onclick="print_report();">
		</p>
	</form>
	<script type="text/javascript">check_and_refresh_payment_type_list(true);</script>
</div>
{/if}
	<h2>{$report_title}</h2>
{if $table}
	{if $data}	
		{foreach from=$data key=branch_id item=n_data}
			{foreach from=$n_data key=counter_name item=nn_data}
			{if $smarty.request.counter_type ne 'single'}
			<h3>Counter: {$counter_name}</h3>
			{/if}
				<table id="tbl_report" class="report_table" {if $no_header_footer}border="1"{/if}>
					<tr class="header">
						<th>Transaction Time</th>
						<th>Cashier</th>
						<th>Receipt No.</th>
						{if $smarty.request.payment_type eq 'credit_card'}
						<th>Type</th>
						{/if}
						<th>Remark</th>
						<th>Payment Amount</th>
						<th>Receipt Amount</th>
						<th>Approved by</th>
					</tr>
					{foreach from=$nn_data key=receipt_no item=nnn_data}
						{foreach from=$nnn_data key=pp_id item=r}
						<tr class="thover">
							<td>{$r.pos_time|date_format:'%H:%M:%S'|default:"-"}</td>
							<td>{$r.pos_username|default:"-"}</td>
							<td>
								{if $no_header_footer}
									{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$receipt_no}
								{else}
									<a href="javascript:items_details('{$r.branch_id}','{$r.counter_id}','{$r.id}','{$r.date}')">{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$receipt_no}</a>
								{/if}
							</td>
							{if $smarty.request.payment_type eq 'credit_card'}
							<td>{$r.type|default:"-"}</td>
							{/if}
							<td>{$r.remark|default:"-"}</td>
							<td class="r">{$r.amount_payment|number_format:2}</td>
							<td class="r">{$r.receipt_amount|number_format:2}</td>
							<td>{$r.pp_username|default:"-"}</td>					
						</tr>
						{/foreach}	
					{/foreach}
				</table>
			{/foreach}
		{/foreach}		
	{else}
		-- No Data --
	{/if}
{/if}

{if !$no_header_footer}
<script>
init_calendar();
</script>
{include file=footer.tpl}
{/if}
