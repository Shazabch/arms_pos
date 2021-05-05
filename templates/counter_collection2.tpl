{*
REVISION HISTORY
================

11/17/2009 12:10:56 PM edward
- add alert $msg

6/16/2010 11:39:46 AM yinsee
- add toggle to show/hide errors

7/20/2010 10:59:54 AM Andy
- Add privilege to check whether user can do un-finalize counter collection or not. (System admin always can)

7/22/2010 3:39:33 PM Andy
- Date format change from d/m/y to Y-m-d

7/30/2010 11:46:55 AM Andy
- Fix counter collection when negivate away still prompt the last popup message.

4/13/2011 4:17:05 PM Andy
- Add popup details for over amount transaction.

7/15/2011 1:09:22 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/13/2011 11:16:10 AM Andy
- Add mix and match promotion at counter collection related module.

11/11/2011 11:09:46 AM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

11/24/2011 4:42:13 PM Alex
- change counter collection Mix n Match Discount column same as Discount column in counter collection 2

11/24/2011 4:46:46 PM Alex
- add checking if got invalid code cannot finalize 

2/13/2012 6:23:43 PM Justin
- Added new table to show deposit info by counter.

3/9/2012 1:00:47 PM Andy
- Change finalize counter collection to submit by ajax, also add popup to show progress percentage.
- Add prompt for user to confirm leave page while finalizing is in progress.

4/10/2012 5:24:52 PM Alex
- add type column for invalid sku

9/21/2012 3:27 Andy
- Fix some wording mistake.

10/8/2012 10:30 AM Andy
- Add checking user privilege for cancel receipt (need config counter_collection_need_privilege_cancel_bill).

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.
- Modified the wording from "Finalize" to "Finalise".
*}

{if !$smarty.request.print}
{include file=header.tpl}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var loading = '<img src="/ui/clock.gif" />';
var user_id = '{$sessioninfo.id}';
var COUNTER_COLLECTION_FINALIZED = '{$LANG.COUNTER_COLLECTION_FINALIZED}';

{literal}

function delete_item(id)
{
	if (confirm('Are you sure to remove?'))
	{
		window.location = '/counter_collection.php?a=delete&id='+id;
	}
}

function do_search(){
	document.f_a.a.value='view_by_date';
	document.f_a.target = "";
	document.f_a.submit();	
}

function do_finalize(){
	if (confirm('Are you sure to finalise?'))
	{
		document.f_a.a.value='finalize';
		document.f_a.target = "";
		document.f_a.submit();	
	}
}

function do_unfinalize(){
	if (confirm('Are you sure to un-finalise?'))
	{
		document.f_a.a.value='unfinalize';
		document.f_a.target = "";
		document.f_a.submit();	
	}
}

function do_print()
{
	if (confirm('Are you sure?'))
	{
		document.f_a.print.value = 1;
		document.f_a.target = "ifprint";
		document.f_a.submit();
		document.f_a.target = "";
		document.f_a.print.value = 0;
	}
}
function init_calendar(sstr){
	Calendar.setup({
	    inputField     :    "date_select",     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "t_date_select",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}

function sales_details(counter_id, date, type, s, e,cashier_id){
    curtain(true);
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(loading+' Please wait...');
	var params = {
		a: 'sales_details',
		counter_id: counter_id,
		date: date,
		type: type,
		s: s,
		e: e,
		cashier_id: cashier_id
	}
	new Ajax.Updater('div_sales_content',phpself,
	{
		parameters: params,
	    method: 'post'
	});
}

function trans_detail(counter_id,cashier_id,date,pos_id)
{
	curtain(true);
	center_div('div_item_details');
	
    $('div_item_details').show();
	$('div_item_content').update(loading+' Please wait...');

	new Ajax.Updater('div_item_content',phpself,
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_sales_details');
	hidediv('div_item_details');
}

function autoz(counter_id,cd,n,date,cd_time,total,user_id)
{
/*	new Ajax.Updater('autoz',phpself,
	{
	    method: 'post',
	    parameters:{
			a: 'ajax_autoz',
			counter_id: counter_id,
			cd: cd,
			n: n,
			date: date,
			cd_time:cd_time,
			total:total
		},
		evalScripts: true
	});
*/
	window.location = "/counter_collection.php?a=ajax_autoz&counter_id="+counter_id+"&cd="+cd+"&n="+n+"&date="+date+"&user_id="+user_id+"&cd_time="+cd_time+"&total="+total;	
}

var needCheckExit = false;
var check_finalize_interval;
function do_finalize2(){
	if(!confirm('Are you sure?'))	return false;
	
	curtain(true, 'curtain2');
	center_div($('div_process_finalize_popup').show());
	$('div_process_finalize_popup_content').update('Finalising Starting...');
	var date = document.f_a['date_select'].value;
	
	var params = {
		a: 'ajax_finalize',
		date_select: date
	}
	
	check_finalize_interval = setInterval('check_finalize_process_status()', 1000);
	needCheckExit = true;
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
			clearInterval(check_finalize_interval);
			var str = e.responseText.trim();
			needCheckExit = false;
			
			if(str == 'OK'){
				$('div_process_finalize_popup_content').update('Processing: 100.00%');
				window.location = "counter_collection.php?date_select="+date+"&finalize=1&msg="+URLEncode(COUNTER_COLLECTION_FINALIZED);
			}else{
				alert(str);
			}
			$('div_process_finalize_popup').hide();
			curtain(false,'curtain2');
			//window.location.reload();
			
		},
		evalScripts: true
	});
}

var finalize_process_checking;
function check_finalize_process_status(){
	if(!finalize_process_checking){
		var params = {
			uid: user_id,
			modulename:'counter_collection',
			taskname: 'finalize',
			statusname: 'per'
		};
		finalize_process_checking = new Ajax.Request('http_con.php?a=ajax_get_cc_finalize_status&SKIP_CONNECT_MYSQL=1', {
			parameters: params,
			onComplete:function(e){
				var per = float(e.responseText);
				$('div_process_finalize_popup_content').update('Processing: '+round(per,2)+'%');
				finalize_process_checking = undefined;
			}
		});
	}
	
}

function confirmExit(e) {
	if(!e) e = window.event;
	if(needCheckExit){
		//e.cancelBubble is supported by IE - this will kill the bubbling process.
		/*e.cancelBubble = true;
		e.returnValue = 'Are You sure you want to leave at this time? Sales will be in-correct if finalize does not fully complete. '; //This is displayed on the dialog
	
		//e.stopPropagation works in Firefox.
		if (e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		}*/
		
		return 'Are You sure you want to leave at this time? Sales will be in-correct if finalise does not fully complete. ';
	}
	
}
window.onbeforeunload=confirmExit;

{/literal}
</script>
{literal}
<style>
#div_sales_details,#div_item_details, #div_process_finalize_popup{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
#invalid_sku{
    background-color: #eeee00;
    border:2px solid black;
}

.big_font{
    font-size: 20px;
    background-color: #eeee00;
    border:2px solid black;
}

</style>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{/literal}
{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}<script>alert("{$msg}");</script>{/if}
<p align=center><font color=red>{$msg}</font></p>

<h1>{$PAGE_TITLE}</h1>

<iframe name=ifprint style="visibility:hidden" width=1 height=1></iframe>
<form class=form name=f_a method="get">
<input name=a value=view_by_date type=hidden>
<input name=print value=0 type=hidden>
<b>Select Date</b> <input id=date_select name=date_select value="{$smarty.request.date_select}" size=10> <img align=absbottom src="ui/calendar.gif" id="t_date_select" style="cursor: pointer;" title="Select Date"/>
<input name=fsearch_submit type=button value="Refresh" onclick="do_search();">
<input type=button value="Print" onclick="do_print();">
</form>
<br>

{if $collection_error}
	<h2>Counter Errors ({count var=$collection_error}) <button onclick="$('error-table').toggle()">Show / Hide</button></h2>
	<table id="error-table" class="tb nobreak" style="display:none" width=100% cellpadding=4 cellspacing=0 border=0>
	<tr style="background:#fe9;">
	<th>Branch</th>
	<th>Counter</th>
	<th>Date</th>
	<th>Error</th>
	</tr>
	{foreach from=$collection_error item=c}
	<tr style="background:{cycle values="#fff,#ff9"};">
	<td>{$c.code}</td>
	<td>{$c.network_name}</td>
	<td>{$c.date}</td>
	<td>{$c.error}</td>
	</tr>
	{/foreach}
	</table>
	<br>
{/if}


{else}
	{include file='header.print.tpl'}
	
<body onload="window.print()">
<div class=printarea>
{/if}
<style>
{literal}
.positive {
	color:#00f;
}

.negative {
	color:#f00;
	font-weight:bold;
}

.nobreak td {
	white-space:nowrap;
}
{/literal}
</style>

<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Transaction Details-->
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->

<!-- Process Finalize Popup -->
<div id="div_process_finalize_popup" style="display:none;width:350px;height:50px;">
	<div id="div_process_finalize_popup_content" style="text-align:center;height:100%;vertical-align:middle;font-size:30px;">
	</div>
</div>
<!-- Process Finalize Popup -->

{*<div id=disc>Checking for data discrepency...</div>*}


<h1>Counter Collection of {$smarty.request.date_select} {if $is_finalized}(Finalised){/if}</h1>

<table class="tb nobreak" width=100% cellpadding=4 cellspacing=0 border=0>
<tr style="background:#fe9;">
<th>&nbsp;</th>
<th remark="Total sales = sum(price-discount) ">Total Sales</th>
<th remark="Total actual sales = sum(amount_tender - amount_changed)">Total Actual Sales</th>
<th>Total Collection</th>
<th>Total Rounding</th>
{if $got_top_up}
	<th>Total Top Up</th>
{/if}
<th>Total Variance</th>
<th>Total Over</th>
</tr>
<tr style="font-size:2em;">
<td>Grand Total</td>
<td align=right class={if $grandtotal.total_sales >= 0}positive{else}negative{/if}>{$grandtotal.total_sales|number_format:2}</td>
<td align=right class={if $grandtotal.total_actual_sales >= 0}positive{else}negative{/if}>{$grandtotal.total_actual_sales|number_format:2}</td>
<td align=right class={if $grandtotal.collection-$grandtotal.advance >= 0}positive{else}negative{/if}>{$grandtotal.collection-$grandtotal.advance|number_format:2}</td>
<td align=right class={if $grandtotal.rounding >= 0}positive{else}negative{/if}>{$grandtotal.rounding|number_format:2}</td>
{if $got_top_up}
	<td align="right" class="{if $grandtotal.rounding >= 0}positive{else}negative{/if}">{$grandtotal.top_up|number_format:2}</td>
{/if}
<td align=right class="{if $grandtotal.collection-$grandtotal.advance-$grandtotal.total_actual_sales-$grandtotal.top_up >= 0}positive{else}negative{/if}">{$grandtotal.collection-$grandtotal.advance-$grandtotal.total_actual_sales-$grandtotal.top_up|number_format:2}</td>
<td align=right class={if $grandtotal.over < 0}negative{/if}>{$grandtotal.over|number_format:2}</td>
</tr>
</table>

{foreach from=$data key=counter_id item=d}
	{foreach from=$d key=cd item=d1}
		<br>
		<h1 title="{$counter_id}">
		<div style="float:right;">
			{if !$config.counter_collection_need_privilege_cancel_bill or ($config.counter_collection_need_privilege_cancel_bill and $sessioninfo.privilege.CC_CANCEL_BILL)}
				{if !$smarty.request.print and $allow_edit and !$is_finalized and $sessioninfo.level == 9999}
					<input type=button value="Cancel Receipt" onclick="window.location = '/counter_collection.php?a=cancel_receipt&counter_id={$counter_id}&date={$smarty.request.date_select}';">
				{/if}
			{/if}
		</div>
		Counter: {$counters.$counter_id}</h1>

		
		{if isset($counter_data.$counter_id.$cd.deposit) || isset($counter_data.$counter_id.$cd.trade_in)}
			<!-- Deposit Detail -->
			<table  class="report_table nobreak" width="20%" cellpadding=4 cellspacing=0 border="1">
				<tr style="background:#fe9;">
					<th colspan="2">Active Deposit</th>
					<th colspan="2">Deposit Cancelled</th>
					<th colspan="2">Trade In</th>
				</tr>
				<tr style="background:#fe9;">
					<th>Received</th>
					<th>Used</th>
					<th>Received</th>
					<th>Used</th>
					<th>Received</th>
					<th>Write-Off</th>
				</tr>
				<tr>
					<td class="r">
						{if $counter_data.$counter_id.$cd.deposit.rcv}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_r','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.deposit.rcv|default:0|number_format:2}
					</td>
					<td class="r">
						{if $counter_data.$counter_id.$cd.deposit.used}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_u','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.deposit.used|default:0|number_format:2}
					</td>
					<td class="r">
						{if $counter_data.$counter_id.$cd.deposit.cancel_rcv}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_cr','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.deposit.cancel_rcv|default:0|number_format:2}
					</td>
					<td class="r">
						{if $counter_data.$counter_id.$cd.deposit.cancel_used}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_cu','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.deposit.cancel_used|default:0|number_format:2}
					</td>
					
					<td class="r {if $counter_data.$counter_id.$cd.trade_in.amt<0}negative{/if}">
						{if $counter_data.$counter_id.$cd.trade_in.amt}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-trade_in','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.trade_in.amt|default:0|number_format:2}
					</td>
					<td class="r {if $counter_data.$counter_id.$cd.trade_in.writeoff_amt<0}negative{/if}">
						{if $counter_data.$counter_id.$cd.trade_in.writeoff_amt}
							<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-trade_in_writeoff','{$counter_data.$counter_id.$cd.start_time}','{$counter_data.$counter_id.$cd.end_time}')">
						{/if}
						{$counter_data.$counter_id.$cd.trade_in.writeoff_amt|default:0|number_format:2}
					</td>
				</tr>
			</table>
			<br />
		{/if}
		
		<table class="tb nobreak" width=100% cellpadding=4 cellspacing=0 border=0>
			{foreach from=$d1 key=cashier_id item=d2}
			    {assign var=start_time value=$trans.$counter_id.$cd.$cashier_id.start_time}
			    {assign var=end_time value=$trans.$counter_id.$cd.$cashier_id.end_time}
			    
			<tr style="background:#fe9;">
				<th title="{$cashier_id}">Cashier:{$username.$cashier_id}<br>
				Time: {$trans.$counter_id.$cd.$cashier_id.start_time|date_format:"%r"} to {$trans.$counter_id.$cd.$cashier_id.end_time|date_format:"%r"}</th>
				{foreach from=$pos_config.payment_type item=type}
				<th>{$pos_config.payment_type_label.$type|default:$type}</th>
				{/foreach}
				
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<th>Mix & Match <br>Discount</th>
				{/if}
				<th>Rounding</th>
				<th>Total</th>
				<th>Over</th>
			</tr>
			<tr>
				<td><b>Cashier Sales</b></td>
				{assign var=total_cashier_sales value=0}
				{foreach from=$pos_config.payment_type item=type}
				{assign var=total_cashier_sales value=$total_cashier_sales+$d2.$type}
				<td align=right>{if $d2.$type <> 0 and !$smarty.request.print }<img align=left src="/ui/view.png" border=0 class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','{$type}','{$trans.$counter_id.$cd.$cashier_id.start_time}','{$trans.$counter_id.$cd.$cashier_id.end_time}',{$cashier_id})">{/if} {$d2.$type|number_format:2}</td>
				{/foreach}
				
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align="right" {if $d2.$mm_discount_col_value <0}class="negative"{/if}>
						{if ($d2.$mm_discount_col_value <> 0 or $counter_data.$counter_id.$cd.others.got_mm_discount) and !$smarty.request.print }
							<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-{$mm_discount_col_value}','{$trans.$counter_id.$cd.$cashier_id.start_time}','{$trans.$counter_id.$cd.$cashier_id.end_time}',{$cashier_id})" />
						{/if}
						{$d2.$mm_discount_col_value|number_format:2}
						{assign var=total_cashier_sales value=$total_cashier_sales+$d2.$mm_discount_col_value}
					</td>
				{/if}
				{assign var=total_cashier_sales value=$total_cashier_sales-$d2.Rounding}
				<td align=right {if $d2.Rounding <0}class=negative{/if}>{$d2.Rounding|number_format:2}</td>
				<td align=right>{$total_cashier_sales|number_format:2}</td>
				<td align=right>
                    {if ($over.$counter_id.$cd.$cashier_id) and !$smarty.request.print}
						<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-over','{$start_time}','{$end_time}')" />
					{/if}
					{$over.$counter_id.$cd.$cashier_id|number_format:2}
				</td>
			</tr>
			
			{if $top_up_data.$counter_id}
				<tr>
					<td><b>Top Up</b>
						{if !$smarty.request.print and $allow_edit and !$is_finalized}
							<a href="/counter_collection.php?a=change_advance&counter_id={$counter_id}&cd={$cd}&date={$smarty.request.date_select}&s={$trans.$counter_id.$cd.$cashier_id.start_time}&e={$trans.$counter_id.$cd.$cashier_id.end_time}&cashier_id={$cashier_id}&type=TOP_UP">
								<img src="/ui/ed.png" align=absmiddle border=0 title="Edit Advance">
							</a>
						{/if}
					</td>
					<td align=right class={if $top_up_data.$counter_id.$cd.$cashier_id<0}negative{/if}>{$top_up_data.$counter_id.$cd.$cashier_id|number_format:2}</td>
					{section name=i loop=6}<td align=right>-</td>{/section}
					<!-- Mix & Match Discount -->
					{if $got_mm_discount}
						<td align="right">-</td>
					{/if}
					<td align=right class={if $top_up_data.$counter_id.$cd.$cashier_id<0}negative{/if}>{$top_up_data.$counter_id.$cd.$cashier_id|number_format:2}</td>
					<td align=right>-</td>
				</tr>
			{/if}
			
			<tr>
				<td><b>Cash Advance</b> {if !$smarty.request.print and $allow_edit and !$is_finalized}<a href="/counter_collection.php?a=change_advance&counter_id={$counter_id}&cd={$cd}&date={$smarty.request.date_select}&s={$trans.$counter_id.$cd.$cashier_id.start_time}&e={$trans.$counter_id.$cd.$cashier_id.end_time}&cashier_id={$cashier_id}&type=ADVANCE"><img src="/ui/ed.png" align=absmiddle border=0 title="Edit Advance"></a>{/if}</td>
				<td align=right class={if $cash_advance.$counter_id.$cd.$cashier_id<0}negative{/if}>{$cash_advance.$counter_id.$cd.$cashier_id|number_format:2}</td>
				{section name=i loop=6}<td align=right>-</td>{/section}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align="right">-</td>
				{/if}
				<td align=right class={if $cash_advance.$counter_id.$cd.$cashier_id<0}negative{/if}>{$cash_advance.$counter_id.$cd.$cashier_id|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			<tr>
				<td><b>Adjustment</b>{if !$smarty.request.print and $allow_edit and !$is_finalized} <a href="/counter_collection.php?a=change_payment_type&counter_id={$counter_id}&date={$smarty.request.date_select}&s={$trans.$counter_id.$cd.$cashier_id.start_time}&e={$trans.$counter_id.$cd.$cashier_id.end_time}&cashier_id={$cashier_id}"><img src="/ui/ed.png" title="Perform Adjustment" border=0 align=absmiddle></a>{/if}</td>
				{assign var=total_adjustment value=0}
				{foreach from=$pos_config.payment_type item=type}
				{assign var=total_adjustment value=$total_adjustment+$adjustment.$counter_id.$cd.$cashier_id.$type}
				<td align=right class={if $adjustment.$counter_id.$cd.$cashier_id.$type<0}negative{/if}>{$adjustment.$counter_id.$cd.$cashier_id.$type|number_format:2}</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align="right" class={if $adjustment.$counter_id.$cd.$cashier_id.$mm_discount_col_value >= 0}positive{else}negative{/if}>{$adjustment.$counter_id.$cd.$cashier_id.$mm_discount_col_value|number_format:2}</td>
				{/if}
				<td align=right>-</td>
				<td align=right class={if total_adjustment<0}negative{/if}>{$total_adjustment|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			<tr>
				<td valign=top><b>Counter Collection</b>{if !$smarty.request.print and $allow_edit and !$is_finalized}<a href="/counter_collection.php?a=change_x&counter_id={$counter_id}&date={$smarty.request.date_select}&id={$cd}&s={$trans.$counter_id.$cd.$cashier_id.start_time}&e={$trans.$counter_id.$cd.$cashier_id.end_time}&cashier_id={$cashier_id}&clear_drawer=1"><img src="/ui/ed.png" title="Edit X" border=0 align=absmiddle></a>{/if}</td>
				{assign var=total_x value=0}
				{foreach from=$pos_config.payment_type item=type}
				{assign var=total_x value=$total_x+$cash_domination.$counter_id.$cd.$cashier_id.$type}
				<td align=right valign=top class={if $cash_domination.$counter_id.$cd.$cashier_id.$type<0}negative{else}positive{/if}>
				{strip}
					{$cash_domination.$counter_id.$cd.$cashier_id.$type|number_format:2}
					{if $odata.$counter_id.$cd.$cashier_id.$type and $odata.$counter_id.$cd.$cashier_id.$type <> $cash_domination.$counter_id.$cd.$cashier_id.$type}<br><font class=small color=blue>O:odata{$odata.$counter_id.$cd.$cashier_id.$type|number_format:2}</font>{/if}
					{if $type eq 'Cash' and ($xtra.$counter_id.$cd.$cashier_id.Cash > 0 or $xtra.$counter_id.$cd.$cashier_id.Float > 0)}<font color=grey class=small><br>C:{$xtra.$counter_id.$cd.$cashier_id.Cash} / F:{$xtra.$counter_id.$cd.$cashier_id.Float}</font>{/if}
				{/strip}
				</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align="right" class="{if $cash_domination.$counter_id.$cd.$cashier_id.$mm_discount_col_value >= 0}positive{else}negative{/if}">{$cash_domination.$counter_id.$cd.$cashier_id.$mm_discount_col_value|number_format:2}</td>
				{/if}
				<td valign=top align=right>-</td>
				<td valign=top align=right class={if $total_x<0}negative{else}positive{/if}>{$total_x|number_format:2}</td>
				<td valign=top align=right>-</td>
			</tr>
			<tr bgcolor="#ffdddd">
				<td><b>Variance</b></td>
				{assign var=total_variance value=0}	
				{foreach from=$pos_config.payment_type item=type}
				{assign var=total_variance value=$total_variance+$variance.$counter_id.$cd.$cashier_id.$type}
				<td align=right class={if $variance.$counter_id.$cd.$cashier_id.$type >= 0}positive{else}negative{/if}>{$variance.$counter_id.$cd.$cashier_id.$type|number_format:2}</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align=right class={if $variance.$counter_id.$cd.$cashier_id.$mm_discount_col_value >= 0}positive{else}negative{/if}>{$variance.$counter_id.$cd.$cashier_id.$mm_discount_col_value|number_format:2}</td>
					{assign var=total_variance value=$total_variance+$variance.$counter_id.$cd.$cashier_id.$mm_discount_col_value}
				{/if}
				<td align=right>-</td>
				<td align=right class={if $total_variance >= 0}positive{else}negative{/if}>{$total_variance|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			{/foreach}
			<tr style="background:#fe9;">
				<th>&nbsp;</th>
				{foreach from=$pos_config.payment_type item=type}
				<th>{$pos_config.payment_type_label.$type|default:$type}</th>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<th>Mix & Match <br>Discount</th>
				{/if}
				<th>Rounding</th>
				<th>Total</th>
				<th>Over</th>
			</tr>
			<tr><td><b>Total Cashier Sales</b></td>
				{assign var=total_cashier_sales value=0}
				{foreach from=$pos_config.payment_type item=type}
					{assign var=total_cashier_sales value=$total_cashier_sales+$counter_data.$counter_id.$cd.$type.sales}
				<td align=right>{$counter_data.$counter_id.$cd.$type.sales|number_format:2}</td>
				{/foreach}
				
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					{assign var=total_cashier_sales value=$total_cashier_sales+$counter_data.$counter_id.$cd.$mm_discount_col_value.sales}
					<td align="right">{$counter_data.$counter_id.$cd.$mm_discount_col_value.sales|number_format:2}</td>	
				{/if}
				
				{assign var=total_cashier_sales value=$total_cashier_sales-$counter_data.$counter_id.$cd.Rounding.sales}
				<td align=right {if $counter_data.$counter_id.$cd.Rounding.sales<0}class=negative{/if}>{$counter_data.$counter_id.$cd.Rounding.sales|number_format:2}</td>
				<td align=right>{$total_cashier_sales|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			
			{if isset($counter_data.$counter_id.$cd.top_up)}
				<tr>
					<td><b>Total Top Up</b></td>
					{assign var=total_top_up value=0}
					{foreach from=$pos_config.payment_type item=type}
						<td align=right {if $counter_data.$counter_id.$cd.top_up<0 and $type eq 'Cash'}class="negative"{/if}>
						{if $type eq 'Cash'}
							{assign var=total_top_up value=$total_top_up+$counter_data.$counter_id.$cd.top_up}
							{$counter_data.$counter_id.$cd.top_up|number_format:2}
						{else}-
						{/if}
						</td>
					{/foreach}
					<!-- Mix & Match Discount -->
					{if $got_mm_discount}
						<td align="right">-</td>
					{/if}
					<td align=right>-</td>
					<td align=right {if $total_top_up<0}class="negative"{/if}>{$total_top_up|number_format:2}</td>
					<td align=right>-</td>
				</tr>
			{/if}
			
			<tr>
				<td><b>Total Cash Advance</b></td>
				{assign var=total_advance value=0}
				{foreach from=$pos_config.payment_type item=type}
					<td align=right {if $counter_data.$counter_id.$cd.advance<0 and $type eq 'Cash'}class=negative{/if}>
					{if $type eq 'Cash'}
						{assign var=total_advance value=$total_advance+$counter_data.$counter_id.$cd.advance}
						{$counter_data.$counter_id.$cd.advance|number_format:2}
					{else}-
					{/if}
					</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					<td align="right">-</td>
				{/if}
				<td align=right>-</td>
				<td align=right {if $total_advance<0}class=negative{/if}>{$total_advance|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			<tr>
				<td><b>Total Adjustment</b></td>
				{assign var=total_adjustment value=0}
				{foreach from=$pos_config.payment_type item=type}
					<td align=right {if $counter_data.$counter_id.$cd.$type.adjustment<0}class=negative{/if}>
						{assign var=total_adjustment value=$total_adjustment+$counter_data.$counter_id.$cd.$type.adjustment}
						{$counter_data.$counter_id.$cd.$type.adjustment|number_format:2}
					</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					{assign var=total_adjustment value=$total_adjustment+$counter_data.$counter_id.$cd.$mm_discount_col_value.adjustment}
					<td align="right" class="{if $counter_data.$counter_id.$cd.$mm_discount_col_value.adjustment<0}negative{else}positive{/if}">{$counter_data.$counter_id.$cd.$mm_discount_col_value.adjustment|number_format:2}</td>
				{/if}
				<td align=right>-</td>
				<td align=right {if $total_adjustment<0}class=negative{/if}>{$total_adjustment|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			<tr>
				<td><b>Total Counter Collection</b></td>
				{assign var=total_cash_domination value=0}
				{foreach from=$pos_config.payment_type item=type}
					<td align=right class={if $counter_data.$counter_id.$cd.$type.cash_domination<0}negative{else}positive{/if}>
						{assign var=total_cash_domination value=$total_cash_domination+$counter_data.$counter_id.$cd.$type.cash_domination}
						{$counter_data.$counter_id.$cd.$type.cash_domination|number_format:2}
					</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					{assign var=total_cash_domination value=$total_cash_domination+$counter_data.$counter_id.$cd.$mm_discount_col_value.cash_domination}
					<td align="right" class="{if $counter_data.$counter_id.$cd.$mm_discount_col_value.cash_domination<0}negative{else}positive{/if}">{$counter_data.$counter_id.$cd.$mm_discount_col_value.cash_domination|number_format:2}</td>
				{/if}
				<td align=right>-</td>
				<td align=right class={if $total_cash_domination<0}negative{else}positive{/if}>{$total_cash_domination|number_format:2}</td>
				<td align=right>-</td>
			</tr>
			<tr bgcolor="#cccccc">
				<td><b>Total Variance</b></td>
				{assign var=total_variance value=0}
				{foreach from=$pos_config.payment_type item=type}
					<td align=right class={if $counter_data.$counter_id.$cd.$type.variance<0}negative{else}positive{/if}>
						{assign var=total_variance value=$total_variance+$counter_data.$counter_id.$cd.$type.variance}
						{$counter_data.$counter_id.$cd.$type.variance|number_format:2}
					</td>
				{/foreach}
				<!-- Mix & Match Discount -->
				{if $got_mm_discount}
					{assign var=total_variance value=$total_variance+$counter_data.$counter_id.$cd.$mm_discount_col_value.variance}
					<td align="right" class="{if $counter_data.$counter_id.$cd.$mm_discount_col_value.variance<0}negative{else}positive{/if}">{$counter_data.$counter_id.$cd.$mm_discount_col_value.variance|number_format:2}</td>
				{/if}
				<td align=right>-</td>
				<td align=right class={if $total_variance<0}negative{else}positive{/if}>{$total_variance|number_format:2}</td>
				<td align=right>-</td>
			</tr>
		</table>
	{/foreach}
{/foreach}

<br>
<h1>Total Sales by Payment Type</h1>
<table class="tb nobreak" width=100% cellpadding=4 cellspacing=0 border=0>
<tr style="background:#fe9;">
	{foreach from=$pos_config.payment_type item=type}
	<th>{$pos_config.payment_type_label.$type|default:$type}</th>
	{/foreach}
	<!-- Mix & Match Discount -->
	{if $got_mm_discount}
		<th>Mix & Match <br>Discount</th>
	{/if}			
</tr>
<tr style="font-size:2em;">
	{foreach from=$pos_config.payment_type item=type}
		<td align=right class={if $counter_sales.$type >= 0}positive{else}negative{/if}>{$counter_sales.$type|number_format:2}</td>
	{/foreach}
	<!-- Mix & Match Discount -->
	{if $got_mm_discount}
		<td align="right" class="{if $counter_sales.$mm_discount_col_value >= 0}positive{else}negative{/if}">{$counter_sales.$mm_discount_col_value|number_format:2}</td>
	{/if}
</tr>
</table>

{if $deposit_data or $trade_in_data}
	<br />
	<h1>Other Info</h1>
	<table class="report_table nobreak" cellpadding="4" cellspacing="0" border="1">
		<tr class="header">
			<th colspan="2">Active Deposit</th>
			<th colspan="2">Deposit Cancelled</th>
			<th colspan="2">Trade In</th>
		</tr>
		<tr class="header">
			<th>Received</th>
			<th>Used</th>
			<th>Received</th>
			<th>Used</th>
			<th>Received</th>
			<th>Write-Off</th>
		</tr>
		<tr>
			<td class="r">{$deposit_data.rcv|number_format:2}</td>
			<td class="r">{$deposit_data.used|number_format:2}</td>
			<td class="r">{$deposit_data.cancel_rcv|number_format:2}</td>
			<td class="r">{$deposit_data.cancel_used|number_format:2}</td>
			<td class="r {if $trade_in_data.amt<0}negative{/if}">{$trade_in_data.amt|number_format:2}</td>
			<td class="r {if $trade_in_data.writeoff_amt<0}negative{/if}">{$trade_in_data.writeoff_amt|number_format:2}</td>
		</tr>
	</table>
{/if}

{if $total_invalid_items>0}
	<p align="center">
	<table class="report_table">
		<tr class="header">
			<th>No. of Transaction</th>
			<th>Barcode</th>
			<th>Description</th>
			<th>Price (RM) per unit</th>
			<th>Type</th>
			<th>Approve by</th>
		</tr>
		{foreach from=$invalid_items key=barcode item=data}
			{foreach from=$data key=selling_price item=other}
			<tr>
				<td>{$other.transactions_total}</td>
				<td>{$barcode}</td>
				<td>{$other.info.sku_description}</td>
				<td class="r">{$selling_price|default:"0"|number_format:2|ifzero:"-"}</td>
				<td>{$other.info.type}</td>
				<td>{$other.info.open_code_user}</td>
			</tr>
			{/foreach}
		{/foreach}
	</table>
	
	<p align="center">	
	<div class="big_font" style="width:700px;margin:auto;text-align:center;">
		Found {$total_invalid_items} invalid SKU. Please 
		
		<a href="pos.invalid_sku.php?branch_id={$sessioninfo.branch_id}&date_select={$smarty.request.date_select}&a=refresh_data">{if $sessioninfo.privilege.POS_VERIFY_SKU}Verify{else}View{/if} invalid SKU.
		</a>
		{if $total_trade_in_sku>0}
			<br />
			{$total_trade_in_sku} of them are Trade In Item
			{if $sessioninfo.privilege.POS_TRADE_IN_WRITEOFF}
				, you may also 
				<a href="pos.trade_in.write_off.php?date={$smarty.request.date_select}&show_data=1">
					Manage Write-Off.
				</a>
			{/if}
		{/if}
	</div>
	</p>	
{else}
	{if !$smarty.request.print}
		{if $allow_edit and !$is_finalized and $data}
			<p align="center">
				{*<input type=button value="Finalise" style="font:bold 20px Arial;background-color:#f90;color:#fff;" onclick="do_finalize()">*}
				<input type="button" value="Finalise" style="font:bold 20px Arial;background-color:#f90;color:#fff;" onclick="do_finalize2()" />
			</p>
	
		{elseif ($sessioninfo.privilege.CC_UNFINALIZE or $sessioninfo.level>=9999) and $is_finalized and $data}
			<p align="center">
				<input type=button value="Un-Finalise" style="font:bold 20px Arial;background-color:#f90;color:#fff;" onclick="do_unfinalize()">
			</p>
		{/if}
	{/if}
{/if}

{if !$smarty.request.print}
{include file=footer.tpl}
<script>
init_calendar();
//new Ajax.Updater('disc','counter_collection.discrepency.php?date={$smarty.request.date_select}');
</script>
{else}
</div>
</body>
</html>
{/if}