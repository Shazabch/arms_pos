{*
7/7/2010 4:51:46 PM Andy
- Fix currency table bugs.
- Add printing feature.

7/14/2010 3:54:21 PM Andy
- Fix a bugs when printing table have no line.

7/15/2010 10:26:57 AM Andy
- Counter Collection 3 add currency float.

7/20/2010 10:59:54 AM Andy
- Add privilege to check whether user can do un-finalize counter collection or not. (System admin always can)
- Show Total Over at grand total table.

7/30/2010 11:46:55 AM Andy
- Fix counter collection when negivate away still prompt the last popup message.

1/7/2011 5:02:45 PM Andy
- Fix change cash domination cannot store odata bugs.

1/12/2011 5:18:55 PM Andy
- Fix sometime counter collection show no sales details if have pos cash domination.

4/13/2011 4:17:05 PM Andy
- Add popup details for over amount transaction.

7/15/2011 1:10:04 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/12/2011 5:29:48 PM Andy
- Add mix and match promotion at counter collection related module.
- Add popup sales details for "Discount" and "Mix & Match Discount"

11/10/2011 6:14:33 PM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

11/24/2011 4:46:46 PM Alex
- add checking if got invalid code cannot finalize 

1/10/2012 3:33:43 PM Justin
- Removed the extra " for Currency Summary table.

2/13/2012 6:23:43 PM Justin
- Added new table to show deposit info by counter.

3/9/2012 1:00:32 PM Andy
- Change finalize counter collection to submit by ajax, also add popup to show progress percentage.
- Add prompt for user to confirm leave page while finalizing is in progress.

3/13/2012 9:53:43 AM Justin
- Fixed the bugs where shows out some display error msg.

4/10/2012 5:24:52 PM Alex
- add type column for invalid sku

8/6/2012 11:12 AM Andy
- Add show total by counter.

4/9/2012 2:45:00 PM Fithri
- Counter Collection - change Counter Summary table header (to light blue)

9/14/2012 4:34:00 PM Fithri
- add config to check must hv cash denom before finalize.
- add config to able to show multiple cashier name instead of only last cashier name

9/21/2012 3:27 PM Andy
- Fix some wording mistake.

9/26/2012 3:35 PM Andy
- Add link to CO2 module from counter collection.

10/8/2012 10:30 AM Andy
- Add checking user privilege for cancel receipt (need config counter_collection_need_privilege_cancel_bill).

10/26/2012 4:04 PM Andy
- Add "Membership Counter Info".
- Add cash advance into variance calculation.
- Add "Cash From POS" and "Cash Advance" for "Member Counter Info"

11/2/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

7/3/2013 4:44 PM Andy
- Enhance to show invalid member info.

7/4/2013 1:45 PM Andy
- Enhance to show cancel at backend in transaction list.
- Enhance to let user choose can print counter collection by session or summary.
- Fix print multiple cashier name bug.
- Fix Active Deposit and Cancelled Deposit cannot show in Counter Summary.

8/20/2013 10:15 AM Andy
- Fix counter collection deposit info.

10/10/2013 3:29 PM Andy
- Change load finalize process status to use setTimeout().

11/26/2013 5:08 PM Andy
- Fix cash denomination negative zero bug.

1/6/2014 4:38 PM Justin
- Enhanced to show counter by cashiers.

1/14/2014 4:50 PM Justin
- Enhanced to use back the cashier ID for new cash advance insertion and record down the user who added the data as if found got config.

1/22/2014 2:11 PM Justin
- Enhanced to split cashier while view sales details, cash advance and adjustment.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.
- Modified the wording from "Finalize" to "Finalise".

11/27/2014 5:00 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.
- Rename the column "Discount" to "Receipt Discount".

4/22/2015 11:36 AM Andy
- Increase the width of transaction details.

5/22/2015 11:54 AM Andy
- Fix screen display unused character.

10/15/2015 4:42 PM Andy
- Fix finalise percentage sometime could become 0.00%.

12/3/2015 5:34 PM Andy
- Enhanced to allow user to view transaction details even total amount is zero.

6/28/2016 1:11 PM Andy
- Rename Top Up to Cash In.

11/22/2016 11:18 AM Andy
- Fixed foreign currency will missing if pos settings removed.
- Fixed column "RM" should only appear when got foreign currency.
- Add Special Cash Refund / Change.

3/15/2017 9:56 AM Justin
- Bug fixed on the cash advance and cash in that displayed at the wrong column.

4/5/2017 4:26 PM Justin
- Enhanced to check against unsynced/missing data to disallow user to finalise if found any.
- Enhanced to show unsynced/missing data table by counter name.
- Bug fixed on "Unfinalise" button will be missing if found got any invalid data (such as invalid SKU item).

4/19/2017 2:50 PM Khausalya
- Enhanced changes from RM to use config setting. 

10/5/2017 9:53 AM Andy
- Change to get counter error from posManager.

6/20/2018 3:48 PM Justin
- Enhanced to load foreign currency list base on sales and config.

7/11/2019 9:51 AM William
- Fixed bug cashier sales column run when comes to certain digit.

05/07/2020 6:21 PM Sheila
- Fixed overlapping button

10/9/2020 2:41 PM William
- Enhanced to change GST word to Tax.

*}

{assign var=show_session value=1}
{assign var=show_summary value=1}

{if !$smarty.request.print}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var user_id = '{$sessioninfo.id}';
var COUNTER_COLLECTION_FINALIZED = '{$LANG.COUNTER_COLLECTION_FINALIZED}';

{literal}
function init_calendar(sstr){
	Calendar.setup({
	    inputField     :    "date_select",     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "t_date_select",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
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

function sales_details(counter_id, date, type, s, e, uid){
    curtain(true);
    center_div($('div_sales_details').show());
    $('div_sales_content').update(_loading_+' Please wait...');

	var params = {
		a: 'sales_details',
		counter_id: counter_id,
		date: date,
		type: type,
		s: s,
		e: e,
		cashier_id: uid
	};
	
	new Ajax.Updater('div_sales_content',phpself,
	{
		parameters: params,
	    method: 'post'
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_sales_details');
	hidediv('div_item_details');
	hidediv('div_print_popup');
}

function trans_detail(counter_id,cashier_id,date,pos_id)
{
	curtain(true);
	center_div('div_item_details');

    $('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

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
function do_print()
{
	if(!document.form_print['print_by_session'].checked && !document.form_print['print_summary'].checked){
		alert('Please select at least 1 printing type.');
		return false;
	}
	
	if (confirm('Are you sure?'))
	{
		//document.f_a.print.value = 1;
		//document.f_a.target = "ifprint";
		//document.f_a.submit();
		//document.f_a.target = "";
		//document.f_a.print.value = 0;
		
		document.form_print['date_select'].value = document.f_a['date_select'].value;
		document.form_print.target = '_blank';
		document.form_print.submit();
	}
}
var needCheckExit = false;
var check_finalize_interval;
var last_per = 0;
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
	
	check_finalize_timeout = setTimeout('check_finalize_process_status()',1000);
	//check_finalize_interval = setInterval('check_finalize_process_status()', 1000);
	needCheckExit = true;
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
			//clearInterval(check_finalize_interval);
			clearTimeout(check_finalize_timeout);
			
			var str = e.responseText.trim();
			needCheckExit = false;
			
			if(str == 'OK'){
				$('div_process_finalize_popup_content').update('Processing: 100.00%');
				window.location = "counter_collection.php?date_select="+date+"&finalize=1&msg="+URLEncode(COUNTER_COLLECTION_FINALIZED);
			}else if(str == 'Data Sync Error'){
				alert("Unable to finalise, system is having missing/unsynced data from POS counters.");
				window.location = "counter_collection.php?date_select="+date+"#div_data_sync_error";
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

function check_finalize_process_status(){
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
			if(check_finalize_timeout){
				if(per > last_per){
					$('div_process_finalize_popup_content').update('Processing: '+round(per,2)+'%');
				}
				last_per = per;
				check_finalize_timeout = setTimeout('check_finalize_process_status()',1000);
			}
		}
	});	
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


function hide_item_details(){
	hidediv('div_item_details');
	if($('div_sales_details').style.display=='none'){
		curtain_clicked();
	}
}

function show_print_popup(){
	curtain(true);
	center_div($('div_print_popup').show());
}

function special_cash_change_notice(){
	alert('Cash Refund or Changes Amount from the receipt which not paid by Cash.\nE.g.\n- Goods return and got cash refund. (When the Receipt Payment type is not Cash)\n');
}
{/literal}
</script>
{literal}
<style>
#div_sales_details,#div_item_details, #div_process_finalize_popup, #div_print_popup{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
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
.col_foreign_curr{
    background: #ffc;
}
.col_rm{
	background: #ffc;
}
.col_nett_sales2{
	background: #9f9;
}
.small_rm_amt{
    font-size: 10px;
}
.tr_variance{
	background: #cccccc;
	color: blue;
}
.old_amt{
	color: blue;
}
.sales{
	background: #cfc;
}
.deposit_cancel{
	background: #a0cfec;
}
.advance{
	background: #cff;
}
.collection{
	background: #f0d0ff;
}
.variance{
	background: #f0ff00;
}
.gross_sales{
	background: #ffd080;
}
.col_over{
	background: #ccf;
}
.big_font{
    font-size: 20px;
    background-color: #eeee00;
    border:2px solid black;
}

.noborderrow td{
	border-bottom: 0 !important;
}
.tr_summary th{
	background: #66ccff;
}

.sup_cancel_at_backend{
	color: red;
	background-color: yellow;
}
table.report_table td{
	min-width: max-content
}
</style>
{/literal}

<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:450px;">
	<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
	<div id="div_sales_content">
	</div>
</div>
<!-- End of Transaction Details-->

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:750px;height:450px;">
	<div style="float:right;"><img onclick="hide_item_details();" src="/ui/closewin.png" /></div>
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

{* Print Popup *}
<div id="div_print_popup" style="display:none;width:280px;height:80px;">
	<form name="form_print">
		<input type="hidden" name="print" value="1" />
		<input type="hidden" name="date_select" />
		
		<table>
			<tr>
				<td align="center" width="100">
					<img src="ui/print64.png" />
				</td>
				<td>
					<input type="checkbox" name="print_by_session" value="1" checked /> Print by session <br />
					<input type="checkbox" name="print_summary" value="1" checked /> Print Summary <br />
				
					<br />
					<input type="button" value="Print" onClick="do_print();" />	
				</td>
			</tr>
		</table>
		
			
	</form>
</div>

{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}<script>alert("{$msg}");</script>{/if}

<p align=center><font color=red>{$msg}</font></p>

{if $err}
	<div class="alert alert-danger mx-3 rounded">
		The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<iframe name=ifprint style="visibility:hidden" width=1 height=1></iframe>

<div class="card mx-3">
	<div class="card-body">
		<form class="form" name="f_a" method="get">
			<input name="a" value="view_by_date" type="hidden">
			<input name="print" value="0" type="hidden">
			<div class="form-inline">
				<b class="form-label">Select Date&nbsp;</b> 
			<input class="form-control" id="date_select" name="date_select" value="{$smarty.request.date_select}" size=30> 
			&nbsp;<img align="absbottom" src="ui/calendar.gif" id="t_date_select" style="cursor: pointer;" title="Select Date"/>
			&nbsp;&nbsp;<input class="btn btn-primary" name="fsearch_submit" type="button" value="Refresh" onclick="do_search();" />
			&nbsp;&nbsp;<input class="btn btn-info" type="button" value="Print" onclick="show_print_popup();">
			</div>
		</form>
	</div>
</div>

{if $config.counter_collection_enable_co2_module and $sessioninfo.privilege.POS_REPORT}
	<ul>
		<li>
			<div class="card mx-3">
				<div class="card-body">
					<a href="pos_report.counter_collection_co2.php?load_report=1&date={$smarty.request.date_select}" target="_blank">Access to CO2 ({$smarty.request.date_select})</a>	
				</div>
			</div>
		</li>
	</ul>
{/if}

{if $counters_error || $ss_error}
	<h2>Found Sync Errors <button onclick="$('div_cc_error').toggle()">Show / Hide</button></h2>
	<div id="div_cc_error">
		{include file="pos_live.cc_tracking_error.tpl" no_branch_code=1}
	</div>
	<br>
{/if}

{else}
	{if !$smarty.request.print_by_session}
		{assign var=show_session value=0}
	{/if}
	{if !$smarty.request.print_summary}
		{assign var=show_summary value=0}
	{/if}

	{include file='header.print.tpl'}
	
	<body onload="window.print()">
	<div class="printarea">
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Counter Collection of {$smarty.request.date_select} ({$BRANCH_CODE}) {if $is_finalized}(Finalised){/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $got_top_up}
	<div class="alert alert-primary mx-3 mt-2">
		* Please take note 'Cash Top Up' has been renamed to 'Cash In'.
	</div>
{/if}

<!-- Grand Total -->
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="tb nobreak report_table table mb-0 text-md-nowrap  table-hover" cellpadding="4" cellspacing="0" border="0">
				<thead class="bg-gray-100">
					<tr class="header">
						<th colspan="2">&nbsp;</th>
						<th>Nett Sales</th>
						{if $got_top_up}
							<th>Total Cash In</th>
						{/if}
						<th>Total Advance</th>
						<th>Total Collection</th>
						<th>Total Variance</th>
						<th>Total Over</th>
						
						{if $got_service_charge}
							<th>Service Charge</th>
						{/if}
						{if $got_gst}
							<th>Tax</th>
						{/if}
						<th>Nett Sales<sup>2</sup><br />Excluded Charges, Taxes & Rounding</th>
					</tr>
				</thead>
				<tr style="font-size:1em;">
					<td colspan="2">Grand Total ({$config.arms_currency.symbol})</td>
					<td class="r sales {if $total.total.nett_sales.amt>=0}positive{else}negative{/if}">{$total.total.nett_sales.amt|number_format:2}</td>
					{if $got_top_up}
						<td class="r sales {if $total.total.top_up.amt>=0}positive{else}negative{/if}">{$total.total.top_up.amt|number_format:2}</td>
					{/if}
					<td class="r advance {if $total.total.cash_advance.amt>=0}positive{else}negative{/if}">{$total.total.cash_advance.amt|number_format:2}</td>
					
					<td class="r collection {if $total.total.cash_domination.amt>=0}positive{else}negative{/if}{if $got_foreign_currency} small{/if}" nowrap>
						{if $got_foreign_currency}<span style="float:left;">{$config.arms_currency.symbol}</span>&nbsp;{/if}{$total.total.cash_domination.amt|number_format:2}
						{if $got_foreign_currency}
							<br />
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate name=fc}
								{assign var=payment_type value=$currency_type}
								<span style="float:left;">{$payment_type}</span>&nbsp;{$total.total.cash_domination.$payment_type.foreign_amt|number_format:2}
								{if !$smarty.foreach.fc.last}<br />{/if}
							{/foreach}
						{/if}
					</td>
			
					<td class="r variance {if $total.total.variance.amt>=0}positive{else}negative{/if}">{$total.total.variance.amt|number_format:2}</td>
					<td class="r col_over {if $total.payment_type.Over.amt>=0}positive{else}negative{/if}">{$total.payment_type.Over.amt|number_format:2}</td>
					{if $got_service_charge}
						<td class="r col_service_charge {if $total.total.service_charges.amt>=0}positive{else}negative{/if}">{$total.total.service_charges.amt|number_format:2}</td>
					{/if}
					{if $got_gst}
						<td class="r col_gst_amt {if $total.total.total_gst_amt.amt>=0}positive{else}negative{/if}">{$total.total.total_gst_amt.amt|number_format:2}</td>
					{/if}
					<td class="r col_nett_sales2 {if $total.total.nett_sales2.amt>=0}positive{else}negative{/if}">{$total.total.nett_sales2.amt|number_format:2}</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<!--
{if $currency_data.total.nett_sales.rm_amt || $currency_data.total.adj.rm_amt || $currency_data.total.cash_advance.rm_amt || $currency_data.total.cash_domination.rm_amt}
<h1>Currency Summary</h1>
<table class="tb nobreak" cellpadding="4" cellspacing="0" border="0">
	<tr style="background:#fe9;">
		<th colspan="2">&nbsp;</th>
		<th>Nett Sales</th>
		<th>Total Advance</th>
		<th>Total Collection</th>
		<th>Total Variance</th>
		<th>{$config.arms_currency.symbol} Variance</th>
	</tr>
	{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
	    {assign var=curr_list value=$currency_data.$currency_type}
	    {section loop=$curr_list.currency_rate_count name=sc}
	        <tr style="font-size:2em;">
	        {assign var=curr_index value=$smarty.section.sc.index}
	        {if $curr_index eq 0}
		        <td rowspan="{$curr_list.currency_rate_count}"><b>{$currency_type}</b></td>
		    {/if}
		        <td>@{$curr_list.list.$curr_index.rate|number_format:$config.foreign_currency_decimal_points}</td>
		        <td class="r {if $curr_list.list.$curr_index.total.nett_sales.foreign_amt>=0}positive{else}negative{/if}">{$curr_list.list.$curr_index.total.nett_sales.foreign_amt|number_format:2}</td>
		        <td class="r {if $curr_list.list.$curr_index.total.cash_advance.foreign_amt>=0}positive{else}negative{/if}">{$curr_list.list.$curr_index.total.cash_advance.foreign_amt|number_format:2}</td>
		        <td class="r {if $curr_list.list.$curr_index.total.cash_domination.foreign_amt>=0}positive{else}negative{/if}">{$curr_list.list.$curr_index.total.cash_domination.foreign_amt|number_format:2}</td>
		        <td class="r {if $curr_list.list.$curr_index.total.variance.foreign_amt>=0}positive{else}negative{/if}">{$curr_list.list.$curr_index.total.variance.foreign_amt|number_format:2}</td>
		        <td class="r {if $curr_list.list.$curr_index.total.variance.rm_amt>=0}positive{else}negative{/if}">{$curr_list.list.$curr_index.total.variance.rm_amt|number_format:2}</td>
		    </tr>
	    {/section}
	{/foreach}
	<tr style="font-size:2em;">
	    <td class="r" colspan="6">Total in {$config.arms_currency.symbol}</td>
	    <td class="r {if $currency_data.total.variance.rm_amt>=0}positive{else}negative{/if}">{$currency_data.total.variance.rm_amt|number_format:2}</td>
	</tr>
</table>
{/if}
-->
{if $got_foreign_currency}
	<br />
	<img src="ui/rq.gif" height="15" align="absbottom"><b>&nbsp;&nbsp;Indicate the Nett Sales may have rounding variances due to no Cash Denomination were created</b>
{/if}

<br />

<!-- ALl Counter -->
{if $show_session}
{foreach from=$data key=counter_id item=d}
{foreach from=$d key=show_type item=c}
    <h1>
		<div style="float:right;">
			{if !$config.counter_collection_need_privilege_cancel_bill or ($config.counter_collection_need_privilege_cancel_bill and $sessioninfo.privilege.CC_CANCEL_BILL)}
				{if !$smarty.request.print and $allow_edit and !$is_finalized}
					<input type="button" value="Cancel Receipt" onclick="window.location = '/counter_collection.php?a=cancel_receipt&counter_id={$counter_id}&date={$smarty.request.date_select}';" />
				{/if}
			{/if}
		</div>
		<div class="clear"></div>
    </h1>
    {assign var=next_start_time value=''}
    {foreach from=$c key=dom_id item=r}
        {assign var=start_time value=$pos_cash_domination.$counter_id.$show_type.$dom_id.start_time|default:$next_start_time}
        {assign var=end_time value=$pos_cash_domination.$counter_id.$show_type.$dom_id.end_time}
        {assign var=end_time_value value=$end_time|strtotime}
        {assign var=next_start_time_value value=$end_time_value+1}
        {assign var=next_start_time value=$next_start_time_value|date_format:"%Y-%m-%d %H:%M:%S"}
        
        {assign var=user_id value=$r.last_cashier_id}
        <h1>
	        Counter: {$counters.$counter_id.network_name}, Time: {$start_time|date_format:"%r"} to {$end_time|date_format:"%r"|default:'N/A'}, 
	        Cashier: 
	        {if $config.counter_collection_show_all_cashier && !$config.counter_collection_split_counter_by_cashier}
	        	{* $r.cashier_list|default:'-' *}
	        	{foreach from=$r.arr_cashier_id_list item=cashier_id name=fcashier}
					{if $cashier_id}
						{if !$smarty.foreach.fcashier.first},{/if}
						{$username.$cashier_id|default:'-'}
					{/if}
	        	{/foreach}
	        
	        {else}
	        	{$username[$r.last_cashier_id]|default:'-'}
	        {/if}
        </h1>

        <table class="report_table nobreak" width=100% cellpadding=4 cellspacing=0 border="1">
            <tr class="header">
                <th>&nbsp;</th>
                <!-- Normal Payment Method -->
                {foreach from=$normal_payment_type item=payment_type}
	                <th>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</th>
                {/foreach}
                
                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<th>Nett Sales ({$config.arms_currency.symbol})</th>
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						<th>{$currency_type}</th>
					{/foreach}
					<!--th>{$config.arms_currency.symbol}</th-->
				{/if}
                <th>Nett Sales</th>
                <th>Receipt<br />Discount</th>
                
                <!-- Mix & Match Discount -->
                {if $got_mm_discount}
                	<th>Mix & Match <br>Discount</th>
                {/if}
				
				{* Service Charge *}
				{if $got_service_charge}
					<th>Service Charge</th>
				{/if}
				
				{* GST *}
				{if $got_gst}
					<th>Tax</th>
				{/if}
				
                <th>Rounding</th>
				{if $got_foreign_currency}
					<th>Currency Adjust</th>
				{/if}
                <th>Over</th>
                <th>Gross Sales</th>
				<th>Nett Sales<sup>2</sup></th>
            </tr>
            
            <!-- cashier sales -->
            <tr>
                <td><b>Cashier Sales</b></td>
                <!-- Normal Payment Method -->
                {foreach from=$normal_payment_type item=payment_type}
                    <td class="r {if $r.cashier_sales.$payment_type.amt<0}negative{/if}">
                        {if ($r.cashier_sales.$payment_type.got_data or $r.cashier_sales.$payment_type.amt or $r.adj.$payment_type.amt) and !$smarty.request.print}
							<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','{$payment_type}','{$start_time}','{$end_time}', '{$user_id}')">
						{/if}
						{$r.cashier_sales.$payment_type.amt|number_format:2}
					</td>
                {/foreach}
                
                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<td class="r sales">{$r.cashier_sales.nett_sales.npt_amt|number_format:2}</td>
					{*assign var=rm_amt value=0*}
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.cashier_sales.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
							{if $r.cashier_sales.foreign_currency.$payment_type.rm_amt and !$smarty.request.print}
								<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','{$payment_type}','{$start_time}','{$end_time}', '{$user_id}')">
							{/if}
							
							{$r.cashier_sales.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
							<!--span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cashier_sales.foreign_currency.$payment_type.rm_amt|number_format:2}</span-->
							{*assign var=rm_amt value=$rm_amt+$r.cashier_sales.foreign_currency.$payment_type.rm_amt*}
						</td>
					{/foreach}
					
					<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
				{/if}
                
                <!-- Nett Sales -->
                <td class="r sales {if $r.cashier_sales.nett_sales.amt<0}negative{/if}">{$r.cashier_sales.nett_sales.amt|number_format:2}</td>
                <td class="r {if $r.cashier_sales.Discount.amt<0}negative{/if}">
                	{if ($r.cashier_sales.Discount.amt or $r.adj.Discount.amt) and !$smarty.request.print}
						<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','Discount','{$start_time}','{$end_time}', '{$user_id}')">
					{/if}
						
					{$r.cashier_sales.Discount.amt|number_format:2}
				</td>
                <!-- Mix & Match Discount -->
                {if $got_mm_discount}
                	<td class="r {if $r.cashier_sales.$mm_discount_col_value.amt<0}negative{/if}">
						{if ($r.cashier_sales.$mm_discount_col_value.amt or $r.adj.$mm_discount_col_value.amt or $r.others.got_mm_discount) and !$smarty.request.print}
							<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-{$mm_discount_col_value}','{$start_time}','{$end_time}', '{$user_id}')">
						{/if}
						{$r.cashier_sales.$mm_discount_col_value.amt|number_format:2}
					</td>
                {/if}
                
				{* Service Charge *}
				{if $got_service_charge}
					<td class="r {if $r.cashier_sales.service_charges.amt<0}negative{/if}">
						{if ($r.cashier_sales.service_charges.amt) and !$smarty.request.print}
							<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-service_charges','{$start_time}','{$end_time}', '{$user_id}')" />
						{/if}
						{$r.cashier_sales.service_charges.amt|number_format:2}
					</td>
				{/if}
				
				{* GST *}
				{if $got_gst}
					<td class="r {if $r.cashier_sales.total_gst_amt.amt<0}negative{/if}">
						{if ($r.cashier_sales.total_gst_amt.amt) and !$smarty.request.print}
							<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-total_gst_amt','{$start_time}','{$end_time}', '{$user_id}')" />
						{/if}
						{$r.cashier_sales.total_gst_amt.amt|number_format:2}
					</td>
				{/if}
				
                <td class="r {if $r.cashier_sales.Rounding.amt<0}negative{/if}">{$r.cashier_sales.Rounding.amt|number_format:2}</td>
				{if $got_foreign_currency}
					<td class="r {if $r.cashier_sales.Currency_adjust.amt<0}negative{/if}">{$r.cashier_sales.Currency_adjust.amt|number_format:2}</td>
				{/if}
                <td class="r col_over {if $r.cashier_sales.Over.amt<0}negative{/if}">
                    {if ($r.cashier_sales.Over.amt) and !$smarty.request.print}
						<img align="left" src="/ui/view.png" border="0" class="clickable"  onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-over','{$start_time}','{$end_time}', '{$user_id}')" />
					{/if}
					{$r.cashier_sales.Over.amt|number_format:2}
				</td>
                <td class="r gross_sales {if $r.cashier_sales.gross_sales.amt<0}negative{/if}">{$r.cashier_sales.gross_sales.amt|number_format:2}</td>
				
				{* Nett Sales 2 *}
				<td class="r col_nett_sales2 {if $r.cashier_sales.nett_sales2.amt<0}negative{/if}">{$r.cashier_sales.nett_sales2.amt|number_format:2}</td>
            </tr>

			<!-- Top Up -->
			{if isset($r.top_up)}
				<tr>
					<td><b>Cash In</b>
						{if !$smarty.request.print and $allow_edit and !$is_finalized}
							<a href="/counter_collection.php?a=change_advance&counter_id={$counter_id}&cashier_id={$user_id}&date={$smarty.request.date_select}&s={$start_time}&e={$end_time}&type=TOP_UP"><img src="/ui/ed.png" align=absmiddle border=0 title="Edit Advance"></a>
						{/if}
					</td>
					
					{foreach from=$normal_payment_type item=payment_type name=pt}
						{if $payment_type eq "Cash"}
							<td class="r {if $r.top_up.Cash.amt<0}negative{/if}">{$r.top_up.Cash.amt|number_format:2}</td>
						{else}
							<td class="r">-</td>
						{/if}
					{/foreach}
	                
	                <!-- Foreign Currency -->
					{if $got_foreign_currency}
						<td class="r">{$r.top_up.Cash.amt|number_format:2}</td>
						{*assign var=rm_amt value=0*}
						{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
							{assign var=payment_type value=$currency_type}
							<td class="r {if $r.top_up.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
								{$r.top_up.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
								{*<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.top_up.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
								{assign var=rm_amt value=$rm_amt+$r.top_up.foreign_currency.$payment_type.rm_amt*}
							</td>
						{/foreach}
						<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
	                {/if}
					
	                <!-- Nett Sales -->
						{if $got_foreign_currency}
							<td class="r">-</td>
						{else}
							<td class="r {if $r.top_up.nett_sales.amt<0}negative{/if}">{$r.top_up.nett_sales.amt|number_format:2}</td>
						{/if}
                	
                	{assign var=cols value=4}
                	{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
					{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
					{if $got_gst}{assign var=cols value=$cols+1}{/if}
					{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
                	<td colspan="{$cols}">&nbsp;</td>
				</tr>
			{/if}
			
            <!-- Cash Advance -->
            <tr>
                <td><b>Cash Advance</b>
                    {if !$smarty.request.print and $allow_edit and !$is_finalized}
						<a href="/counter_collection.php?a=change_advance&counter_id={$counter_id}&cashier_id={$user_id}&date={$smarty.request.date_select}&s={$start_time}&e={$end_time}&type=ADVANCE"><img src="/ui/ed.png" align=absmiddle border=0 title="Edit Advance"></a>
					{/if}
				</td>
                <!--td class="r {if $r.cash_advance.Cash.amt<0}negative{/if}">{$r.cash_advance.Cash.amt|number_format:2}</td-->
				{foreach from=$normal_payment_type item=payment_type name=pt}
					{if $payment_type eq "Cash"}
						<td class="r {if $r.cash_advance.Cash.amt<0}negative{/if}">{$r.cash_advance.Cash.amt|number_format:2}</td>
					{else}
						<td class="r">-</td>
					{/if}
				{/foreach}
                
                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<td class="r advance">{$r.cash_advance.nett_sales.npt_amt|number_format:2}</td>
					{*assign var=rm_amt value=0*}
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.cash_advance.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
							{$r.cash_advance.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
							{*<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cash_advance.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
							{assign var=rm_amt value=$rm_amt+$r.cash_advance.foreign_currency.$payment_type.rm_amt*}
						</td>
					{/foreach}
					<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
				{/if}
 
                <!-- Nett Sales -->
				{if $got_foreign_currency}
					<td class="r">-</td>
				{else}
					<td class="r advance {if $r.cash_advance.nett_sales.amt<0}negative{/if}">{$r.cash_advance.nett_sales.amt|number_format:2}</td>
				{/if}
                {assign var=show_xtra_table value=0}
                {assign var=got_deposit value=0}
                {assign var=got_trade_in value=0}
                {assign var=got_cash_change value=0}
                {if $r.deposit_received_amt || $r.deposit_used_amt || $r.deposit_cancel_rcv_amt || $r.deposit_cancel_used_amt || isset($r.trade_in) || $r.cash_change}
                	{assign var=show_xtra_table value=1}
					{assign var=cols value=0}
					
					{if $r.deposit_received_amt || $r.deposit_used_amt || $r.deposit_cancel_rcv_amt || $r.deposit_cancel_used_amt}
						{assign var=got_deposit value=1}
					{/if}
					{if isset($r.trade_in)}
						{assign var=got_trade_in value=1}
					{/if}
					{if $r.cash_change}
						{assign var=got_cash_change value=1}
					{/if}
				{else}
					{assign var=cols value=5}
				{/if}
                {if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
				{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
				{if $got_gst}{assign var=cols value=$cols+1}{/if}
				{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
				<td colspan="{$cols}">&nbsp;</td>
				{if $show_xtra_table}
					<th rowspan="2" colspan="4" style="padding:0;">
						<table width="100%" cellspacing="0" class="tb nobreak">
							<tr class="header">
								{if $got_deposit}
									<th colspan="3">Today Deposit</th>
									<th rowspan="2">Cancel<br />Previous<br />Deposit</th>
								{/if}
								{if $got_trade_in}
									<th colspan="2">Trade In</th>
								{/if}
								{if $got_cash_change}
									<th rowspan="2">Special<br />Cash Refund / Change 
									[<a href="javascript:void(special_cash_change_notice());">?</a>]
									</th>
								{/if}
							</tr>
							<tr class="header">
								{if $got_deposit}
									<th>Received</th>
									<th>Used</th>
									<th>Refund</th>
								{/if}
								{if $got_trade_in}
									<th>Received</th>
									<th>Write-Off</th>
								{/if}
							</tr>
							<tr class="noborderrow">
								{if $got_deposit}
									<td class="r">
										{if $r.deposit_received_amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_r','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.deposit_received_amt|number_format:2}
									</td>
									<td class="r">
										{if $r.deposit_used_amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_u','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.deposit_used_amt|number_format:2}
									</td>
									<td class="r {if $r.deposit_refund_amt>0}negative{/if}">
										{if $r.deposit_refund_amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_rf','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.deposit_refund_amt*-1|number_format:2}
									</td>
									<td class="r {if $r.deposit_cancel_rcv_amt<0}negative{/if}">
										{if $r.deposit_cancel_rcv_amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-deposit_cr','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.deposit_cancel_rcv_amt|number_format:2}
									</td>
								{/if}
								{if $got_trade_in}
									<td class="r {if $r.trade_in.amt<0}negative{/if}">
										{if $r.trade_in.amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-trade_in','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.trade_in.amt|number_format:2}
									</td>
									<td class="r {if $r.trade_in.writeoff_amt<0}negative{/if}">
										{if $r.trade_in.writeoff_amt}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-trade_in_writeoff','{$start_time}','{$end_time}, '{$user_id}'')">
										{/if}
										{$r.trade_in.writeoff_amt|number_format:2}
									</td>
								{/if}
								{if $got_cash_change}
									<td class="r {if $r.cash_change.amt*-1<0}negative{/if}">
										{if $r.cash_change}
											<img align="left" src="/ui/view.png" border="0" class="clickable" onClick="sales_details({$counter_id},'{$smarty.request.date_select}','special-cash_change','{$start_time}','{$end_time}', '{$user_id}')">
										{/if}
										{$r.cash_change.amt*-1|number_format:2}
									</td>
								{/if}
							</tr>
						</table>
					</th>
				{/if}
            </tr>
            
            <!-- Adjustment -->
            <tr>
                <td><b>Adjustment</b>
                    {if !$smarty.request.print and $allow_edit and !$is_finalized}
						<a href="/counter_collection.php?a=change_payment_type&counter_id={$counter_id}&cashier_id={$user_id}&date={$smarty.request.date_select}&s={$start_time}&e={$end_time}"><img src="/ui/ed.png" title="Perform Adjustment" border=0 align=absmiddle></a>
					{/if}
				</td>
                <!-- Normal Payment Method -->
                {foreach from=$normal_payment_type item=payment_type}
                    <td class="r {if $r.adj.$payment_type.amt<0}negative{/if}">{$r.adj.$payment_type.amt|number_format:2}</td>
                {/foreach}

                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<td class="r sales {if $r.adj.nett_sales.npt_amt<0}negative{/if}">{$r.adj.nett_sales.npt_amt|number_format:2}</td>
					{*assign var=rm_amt value=0*}
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.adj.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
							{$r.adj.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
							{*<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.adj.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
							{assign var=rm_amt value=$rm_amt+$r.adj.foreign_currency.$payment_type.rm_amt*}
						</td>
					{/foreach}
					<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
				{/if}
				
				<!-- Nett Sales -->
					{if $got_foreign_currency}
						<td class="r">-</td>
					{else}
						<td class="r sales {if $r.adj.nett_sales.amt<0}negative{/if}">{$r.adj.nett_sales.amt|number_format:2}</td>
					{/if}
                <td class="r {if $r.adj.Discount.amt<0}negative{/if}">{$r.adj.Discount.amt|number_format:2}</td>

				{if $show_xtra_table}
					{assign var=cols value=2}
				{else}
					{assign var=cols value=4}
				{/if}
                {if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
				{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
				{if $got_gst}{assign var=cols value=$cols+1}{/if}
				{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
					
				{if $show_xtra_table}
				{else}
					<td colspan="{$cols}">&nbsp;</td>
				{/if}
            </tr>
            
            <!-- Counter Collection -->
            <tr>
                <td><b>Counter Collection</b>
                    {if !$smarty.request.print and $allow_edit and !$is_finalized}
						<a href="/counter_collection.php?a=change_x&counter_id={$counter_id}&date={$smarty.request.date_select}&s={$start_time}&e={$end_time}&cashier_id={$user_id}&clear_drawer=1&id={$dom_id}"><img src="/ui/ed.png" title="Edit X" border=0 align=absmiddle></a>
					{/if}
				</td>
				
                <!-- Normal Payment Method -->
                {foreach from=$normal_payment_type item=payment_type}
                    <td class="r {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
						{$r.cash_domination.$payment_type.amt|number_format:2}
						{if $r.cash_domination.$payment_type.amt<>$r.cash_domination.$payment_type.o_amt}
						    <br />
						    <span class="old_amt small">{$r.cash_domination.$payment_type.o_amt|number_format:2}</span>
						{/if}
						<br />
						{if $payment_type eq 'Cash'}
						    <span class="small" style="color:grey;">
	                        C:{$r.cash_domination.Float.amt+$r.cash_domination.Cash.amt|number_format:2}
							/ F:{$r.cash_domination.Float.amt|number_format:2}
						    </span>
					    {/if}
					</td>
                {/foreach}

                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<td class="r collection {if $r.cash_domination.nett_sales.npt_amt<0}negative{/if}">{$r.cash_domination.nett_sales.npt_amt|number_format:2}</td>
					{*assign var=rm_amt value=0*}
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
							{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
							{*<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cash_domination.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
							{assign var=rm_amt value=$rm_amt+$r.cash_domination.foreign_currency.$payment_type.rm_amt}

							<!-- Currency Float -->
							<br />*}
							<span class="small" style="color:grey;">
							C:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
							/ F:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
							</span>
						</td>
					{/foreach}
					<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
				{/if}
				
				<!-- Nett Sales -->
				
				<td class="r collection {if $r.cash_domination.nett_sales.amt<0}negative{/if}{if $got_foreign_currency} small{/if}" nowrap>
					{if $got_foreign_currency}<span style="float:left;">{$config.arms_currency.symbol}</span>&nbsp;{/if}{$r.cash_domination.nett_sales.amt|number_format:2}
					{if $got_foreign_currency}
						<br />
						{foreach from=$foreign_currency_list key=currency_type item=currency_rate name=fc}
							{assign var=payment_type value=$currency_type}
							<span style="float:left;">{$payment_type}</span>&nbsp;{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
							{if !$smarty.foreach.fc.last}<br />{/if}
						{/foreach}
					{/if}
				</td>
                
                {assign var=cols value=5}
                {if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
				{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
				{if $got_gst}{assign var=cols value=$cols+1}{/if}
				{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
                <td colspan="{$cols}">&nbsp;</td>
            </tr>
            
            <!-- Variance -->
            <tr class="tr_variance">
                <td><b>Variance</b></td>
                <!-- Normal Payment Method -->
                {foreach from=$normal_payment_type item=payment_type}
                    <td class="r {if $r.variance.$payment_type.amt<0}negative{/if}">{$r.variance.$payment_type.amt|number_format:2}</td>
                {/foreach}

                <!-- Foreign Currency -->
				{if $got_foreign_currency}
					<td class="r variance {if $r.variance.nett_sales.npt_amt<0}negative{/if}">{$r.variance.nett_sales.npt_amt|number_format:2}</td>
					{*assign var=rm_amt value=0*}
					{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.variance.foreign_currency.$payment_type.foreign_amt<0}negative{/if}">
							{$r.variance.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
							<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.variance.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
							{*assign var=rm_amt value=$rm_amt+$r.variance.foreign_currency.$payment_type.rm_amt*}
						</td>
					{/foreach}
					<!--td class="r">{$rm_amt|number_format:2}</td-->
				{/if}
				
				<!-- Nett Sales -->
                <td class="r variance {if $r.variance.nett_sales.amt<0}negative{/if}">
					{if $got_foreign_currency && !$dom_id}
						<img src="ui/rq.gif" height="15" align="absbottom">
					{/if}
					{$r.variance.nett_sales.amt|number_format:2}
				</td>
                
                {assign var=cols value=5}
                {if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
				{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
				{if $got_gst}{assign var=cols value=$cols+1}{/if}
				{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
                <td colspan="{$cols}">&nbsp;</td>
            </tr>
        </table>
    {/foreach}
{/foreach}
{/foreach}

<br />
{/if}

{if $show_summary}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Counter Summary</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{foreach from=$total.total_by_counter key=counter_id item=r}
	<h1>Counter: {$counters.$counter_id.network_name}</h1>
	
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table nobreak table mb-0 text-md-nowrap  table-hover" width="100%" >
					<thead class="bg-gray-100">
						<tr class="tr_summary">
							<th>&nbsp;</th>
							<!-- Normal Payment Method -->
							{foreach from=$normal_payment_type item=payment_type}
								<th>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</th>
							{/foreach}
							
							<!-- Foreign Currency -->
							{if $got_foreign_currency}
								<th>Nett Sales ({$config.arms_currency.symbol})</th>
								{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
									<th>{$currency_type}</th>
								{/foreach}
								<!--th>{$config.arms_currency.symbol}</th-->
							{/if}
							<th>Nett Sales</th>
							<th>Receipt<br />Discount</th>
							
							<!-- Mix & Match Discount -->
							{if $got_mm_discount}
								<th>Mix & Match <br>Discount</th>
							{/if}
							
							{* Service Charge *}
							{if $got_service_charge}
								<th>Service Charge</th>
							{/if}
							
							{* GST *}
							{if $got_gst}
								<th>Tax</th>
							{/if}
							
							<th>Rounding</th>
							{if $got_foreign_currency}
								<th>Currency Adjust</th>
							{/if}
							<th>Over</th>
							<th>Gross Sales</th>
							<th>Nett Sales<sup>2</sup></th>
						</tr>
					</thead>
					
					<!-- Cashier Sales -->
					<tbody class="fs-08">
						<tr>
							<td><b>Cashier Sales</b></td>
							<!-- Normal Payment Method -->
							{foreach from=$normal_payment_type item=payment_type}
								<td class="r {if $r.cashier_sales.$payment_type.amt<0}negative{/if}">
									{$r.cashier_sales.$payment_type.amt|number_format:2}
								</td>
							{/foreach}
							
							<!-- Foreign Currency -->
							{if $got_foreign_currency}
								<td class="r sales {if $r.cashier_sales.nett_sales.npt_amt<0}negative{/if}">{$r.cashier_sales.nett_sales.npt_amt|number_format:2}</td>
								{*assign var=rm_amt value=0*}
								{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
									{assign var=payment_type value=$currency_type}
									<td class="r {if $r.cashier_sales.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">					
										{$r.cashier_sales.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
										<!--span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cashier_sales.foreign_currency.$payment_type.rm_amt|number_format:2}</span-->
										{*assign var=rm_amt value=$rm_amt+$r.cashier_sales.foreign_currency.$payment_type.rm_amt*}
									</td>
								{/foreach}
								<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
							{/if}
							
							<!-- Nett Sales -->
							<td class="r sales {if $r.cashier_sales.nett_sales.amt<0}negative{/if}">{$r.cashier_sales.nett_sales.amt|number_format:2}</td>
							<td class="r {if $r.cashier_sales.Discount.amt<0}negative{/if}">					
								{$r.cashier_sales.Discount.amt|number_format:2}
							</td>
							<!-- Mix & Match Discount -->
							{if $got_mm_discount}
								<td class="r {if $r.cashier_sales.$mm_discount_col_value.amt<0}negative{/if}">
									{$r.cashier_sales.$mm_discount_col_value.amt|number_format:2}
								</td>
							{/if}
							
							{* Service Charge *}
							{if $got_service_charge}
								<td class="r {if $r.cashier_sales.service_charges.amt<0}negative{/if}">
									{$r.cashier_sales.service_charges.amt|number_format:2}
								</td>
							{/if}
							
							{* GST *}
							{if $got_gst}
								<td class="r {if $r.cashier_sales.total_gst_amt.amt<0}negative{/if}">
									{$r.cashier_sales.total_gst_amt.amt|number_format:2}
								</td>
							{/if}
							
							<td class="r {if $r.cashier_sales.Rounding.amt<0}negative{/if}">{$r.cashier_sales.Rounding.amt|number_format:2}</td>
							{if $got_foreign_currency}
								<td class="r {if $r.cashier_sales.Currency_adjust.amt<0}negative{/if}">{$r.cashier_sales.Currency_adjust.amt|number_format:2}</td>
							{/if}
							<td class="r col_over {if $r.cashier_sales.Over.amt<0}negative{/if}">
								{$r.cashier_sales.Over.amt|number_format:2}
							</td>
							<td class="r gross_sales {if $r.cashier_sales.gross_sales.amt<0}negative{/if}">{$r.cashier_sales.gross_sales.amt|number_format:2}</td>
							
							<td class="r col_nett_sales2 {if $r.cashier_sales.nett_sales2.amt<0}negative{/if}">{$r.cashier_sales.nett_sales2.amt|number_format:2}</td>
						</tr>
					</tbody>
					
					<!-- Top Up -->
					<tbody class="fs-08">
						{if isset($r.top_up)}
						<tr>
							<td><b>Cash In</b></td>
							
							{foreach from=$normal_payment_type item=payment_type name=pt}
								{if $payment_type eq "Cash"}
									<td class="r {if $r.top_up.Cash.amt<0}negative{/if}">{$r.top_up.Cash.amt|number_format:2}</td>
								{else}
									<td class="r">-</td>
								{/if}
							{/foreach}
							
							<!-- Foreign Currency -->
							{if $got_foreign_currency}
								<td class="r {if $r.top_up.nett_sales.npt_amt<0}negative{/if}">{$r.top_up.nett_sales.npt_amt|number_format:2}</td>
								{*assign var=rm_amt value=0*}
								{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
									{assign var=payment_type value=$currency_type}
									<td class="r {if $r.top_up.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
										{$r.top_up.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
										<!--span class="small_rm_amt">{$config.arms_currency.symbol} {$r.top_up.foreign_currency.$payment_type.rm_amt|number_format:2}</span-->
										{*assign var=rm_amt value=$rm_amt+$r.top_up.foreign_currency.$payment_type.rm_amt*}
									</td>
								{/foreach}
								<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
							{/if}
							
							<!-- Nett Sales -->
							{if $got_foreign_currency}
								<td class="r">-</td>
							{else}
								<td class="r {if $r.top_up.nett_sales.amt<0}negative{/if}">{$r.top_up.nett_sales.amt|number_format:2}</td>
							{/if}
							
							{assign var=cols value=5}
							{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
							{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
							{if $got_gst}{assign var=cols value=$cols+1}{/if}
							{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
							<td colspan="{$cols}">&nbsp;</td>
						</tr>
					{/if}
					
					<!-- Cash Advance -->
					<tr>
						<td><b>Cash Advance</b></td>
						   {foreach from=$normal_payment_type item=payment_type name=pt}
							{if $payment_type eq "Cash"}
								<td class="r {if $r.cash_advance.Cash.amt<0}negative{/if}">{$r.cash_advance.Cash.amt|number_format:2}</td>
							{else}
								<td class="r">-</td>
							{/if}
						{/foreach}
						
						<!-- Foreign Currency -->
						{if $got_foreign_currency}
							<td class="r advance {if $r.cash_advance.nett_sales.npt_amt<0}negative{/if}">{$r.cash_advance.nett_sales.npt_amt|number_format:2}</td>
							{*assign var=rm_amt value=0*}
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
								{assign var=payment_type value=$currency_type}
								<td class="r {if $r.cash_advance.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
									{$r.cash_advance.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
									<!--span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cash_advance.foreign_currency.$payment_type.rm_amt|number_format:2}</span-->
									{*assign var=rm_amt value=$rm_amt+$r.cash_advance.foreign_currency.$payment_type.rm_amt*}
								</td>
							{/foreach}
							<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
						{/if}
			
						<!-- Nett Sales -->
						{if $got_foreign_currency}
							<td class="r advance">-</td>
						{else}
							<td class="r advance {if $r.cash_advance.nett_sales.amt<0}negative{/if}">{$r.cash_advance.nett_sales.amt|number_format:2}</td>
						{/if}
						{assign var=show_xtra_table value=0}
						{assign var=got_deposit value=0}
						{assign var=got_trade_in value=0}
						{assign var=got_cash_change value=0}
						
						{if $r.deposit.rcv || $r.deposit.used || $r.deposit.cancel_rcv || $r.deposit.cancel_used || isset($r.trade_in) || $r.cash_change}
							{assign var=show_xtra_table value=1}
							{assign var=cols value=0}
							{if $r.deposit.rcv || $r.deposit.used || $r.deposit.cancel_rcv || $r.deposit.cancel_used}
								{assign var=got_deposit value=1}
							{/if}
							{if isset($r.trade_in)}
								{assign var=got_trade_in value=1}
							{/if}
							{if $r.cash_change}
								{assign var=got_cash_change value=1}
							{/if}
						{else}
							{assign var=cols value=5}
						{/if}
						{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
						{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
						{if $got_gst}{assign var=cols value=$cols+1}{/if}
						{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
							
						<td colspan="{$cols}">&nbsp;</td>
						{if $show_xtra_table}
							<th rowspan="2" colspan="4" style="padding:0;">
								<table width="100%" cellspacing="0" class="tb nobreak">
									<tr class="header">
										{if $got_deposit}
											<th colspan="3">Today Deposit</th>
											<th rowspan="2">Cancel<br />Previous<br />Deposit</th>
										{/if}
										{if $got_trade_in}
											<th colspan="2">Trade In</th>
										{/if}
										{if $got_cash_change}
											<th rowspan="2">Special<br />Cash Refund / Change
												[<a href="javascript:void(special_cash_change_notice());">?</a>]
											</th>
										{/if}
									</tr>
									<tr class="header">
										{if $got_deposit}
											<th>Received</th>
											<th>Used</th>
											<th>Refund</th>
										{/if}
										{if $got_trade_in}
											<th>Received</th>
											<th style="border-right:0;">Write-Off</th>
										{/if}
									</tr>
									<tr class="noborderrow">
										{if $got_deposit}
											<td class="r">
												{$r.deposit.rcv|number_format:2}
											</td>
											<td class="r">
												{$r.deposit.used|number_format:2}
											</td>
											<td class="r {if $r.deposit.refund>0}negative{/if}">
												{$r.deposit.refund*-1|number_format:2}
											</td>
											<td class="r {if $r.deposit.cancel_rcv<0}negative{/if}">
												{$r.deposit.cancel_rcv|number_format:2}
											</td>
										{/if}
										{if $got_trade_in}
											<td class="r {if $r.trade_in.amt<0}negative{/if}">
												{$r.trade_in.amt|number_format:2}
											</td>
											<td class="r {if $r.trade_in.writeoff_amt<0}negative{/if}" style="border-right:0;">
												{$r.trade_in.writeoff_amt|number_format:2}
											</td>
										{/if}
										{if $got_cash_change}
											<td class="r {if $r.cash_change.amt*-1<0}negative{/if}">
												{$r.cash_change.amt*-1|number_format:2}
											</td>
										{/if}
									</tr>
								</table>
							</th>
						{/if}
					</tr>
					
					<!-- Adjustment -->
					<tr>
						<td><b>Adjustment</b></td>
						<!-- Normal Payment Method -->
						{foreach from=$normal_payment_type item=payment_type}
							<td class="r {if $r.adj.$payment_type.amt<0}negative{/if}">{$r.adj.$payment_type.amt|number_format:2}</td>
						{/foreach}
			
						<!-- Foreign Currency -->
						{if $got_foreign_currency}
							<td class="r sales {if $r.adj.nett_sales.npt_amt<0}negative{/if}">{$r.adj.nett_sales.npt_amt|number_format:2}</td>
							{*assign var=rm_amt value=0*}
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
								{assign var=payment_type value=$currency_type}
								<td class="r {if $r.adj.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
									{$r.adj.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
									<!--span class="small_rm_amt">{$config.arms_currency.symbol} {$r.adj.foreign_currency.$payment_type.rm_amt|number_format:2}</span-->
									{*assign var=rm_amt value=$rm_amt+$r.adj.foreign_currency.$payment_type.rm_amt*}
								</td>
							{/foreach}
							<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
						{/if}
						
						<!-- Nett Sales -->
						{if $got_foreign_currency}
							<td class="r sales">-</td>
							<td class="r">-</td>
						{else}
							<td class="r sales {if $r.adj.nett_sales.amt<0}negative{/if}">{$r.adj.nett_sales.amt|number_format:2}</td>
							<td class="r {if $r.adj.Discount.amt<0}negative{/if}">{$r.adj.Discount.amt|number_format:2}</td>
						{/if}
			
						{if $show_xtra_table}
							{assign var=cols value=2}
						{else}
							{assign var=cols value=4}
						{/if}
						{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
						{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
						{if $got_gst}{assign var=cols value=$cols+1}{/if}
						{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
						{if $show_xtra_table}
						{else}
							<td colspan="{$cols}">&nbsp;</td>
						{/if}
					</tr>
					
					<!-- Counter Collection -->
					<tr>
						<td><b>Counter Collection</b></td>
						
						<!-- Normal Payment Method -->
						{foreach from=$normal_payment_type item=payment_type}
							<td class="r {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
								{$r.cash_domination.$payment_type.amt|number_format:2}
								{if $r.cash_domination.$payment_type.amt<>$r.cash_domination.$payment_type.o_amt}
									<br />
									<span class="old_amt small">{$r.cash_domination.$payment_type.o_amt|number_format:2}</span>
								{/if}
								<br />
								{if $payment_type eq 'Cash'}
									<span class="small" style="color:grey;">
									C:{$r.cash_domination.Float.amt+$r.cash_domination.Cash.amt|number_format:2}
									/ F:{$r.cash_domination.Float.amt|number_format:2}
									</span>
								{/if}
							</td>
						{/foreach}
			
						<!-- Foreign Currency -->
						{if $got_foreign_currency}
							<td class="r collection {if $r.cash_domination.nett_sales.npt_amt<0}negative{/if}">{$r.cash_domination.nett_sales.npt_amt|number_format:2}</td>
							{*assign var=rm_amt value=0*}
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
								{assign var=payment_type value=$currency_type}
								<td class="r {if $r.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
									{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
									{*<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.cash_domination.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
									{assign var=rm_amt value=$rm_amt+$r.cash_domination.foreign_currency.$payment_type.rm_amt}
			
									<!-- Currency Float -->
									<br />*}
									<span class="small" style="color:grey;">
									C:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
									/ F:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
									</span>
								</td>
							{/foreach}
							<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
						{/if}
						
						<!-- Nett Sales -->
						<td class="r collection {if $r.cash_domination.nett_sales.amt<0}negative{/if}{if $got_foreign_currency} small{/if}" nowrap>
							{if $got_foreign_currency}<span style="float:left;">{$config.arms_currency.symbol}</span>&nbsp;{/if}{$r.cash_domination.nett_sales.amt|number_format:2}
							{if $got_foreign_currency}
								<br />
								{foreach from=$foreign_currency_list key=currency_type item=currency_rate name=fc}
									{assign var=payment_type value=$currency_type}
									<span style="float:left;">{$payment_type}</span>&nbsp;{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
									{if !$smarty.foreach.fc.last}<br />{/if}
								{/foreach}
							{/if}
						</td>
						
						{assign var=cols value=5}
						{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
						{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
						{if $got_gst}{assign var=cols value=$cols+1}{/if}
						{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
						<td colspan="{$cols}">&nbsp;</td>
					</tr>
					
					<!-- Variance -->
					<tr class="tr_variance">
						<td><b>Variance</b></td>
						<!-- Normal Payment Method -->
						{foreach from=$normal_payment_type item=payment_type}
							<td class="r {if $r.variance.$payment_type.amt<0}negative{/if}">{$r.variance.$payment_type.amt|number_format:2}</td>
						{/foreach}
			
						<!-- Foreign Currency -->
						{if $got_foreign_currency}
							<td class="r variance {if $r.variance.nett_sales.npt_amt<0}negative{/if}">{$r.variance.nett_sales.npt_amt|number_format:2}</td>
							{*assign var=rm_amt value=0*}
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
								{assign var=payment_type value=$currency_type}
								<td class="r {if $r.variance.foreign_currency.$payment_type.foreign_amt<0}negative{/if}">
									{$r.variance.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
									<span class="small_rm_amt">{$config.arms_currency.symbol} {$r.variance.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
									{assign var=rm_amt value=$rm_amt+$r.variance.foreign_currency.$payment_type.rm_amt}
								</td>
							{/foreach}
							<!--td class="r">{$rm_amt|number_format:2}</td-->
						{/if}
						
						<!-- Nett Sales -->
						<td class="r variance {if $r.variance.nett_sales.amt<0}negative{/if}">{$r.variance.nett_sales.amt|number_format:2}</td>
						
						{assign var=cols value=5}
						{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
						{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
						{if $got_gst}{assign var=cols value=$cols+1}{/if}
						{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
						<td colspan="{$cols}">&nbsp;</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/foreach}

{if isset($total.total.deposit) or isset($total.total.trade_in)}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Other Info</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table nobreak" cellpadding="4" cellspacing="0" >
					<thead class="bg-gray-100">
						<tr class="header">
							<th colspan="3">Today Deposit</th>
							<th rowspan="2">Cancel<br />Previous<br />Deposit</th>
							<th colspan="2">Trade In</th>
						</tr>
						<tr class="header">
							<th>Received</th>
							<th>Used</th>
							<th>Refund</th>
							{*<th>Received</th>
							<th>Used</th>*}
							<th>Received</th>
							<th>Write-Off</th>
						</tr>
					</thead>
					<tbody class="fs-08">
						<tr>
							<td class="r">{$total.total.deposit.rcv|number_format:2}</td>
							<td class="r">{$total.total.deposit.used|number_format:2}</td>
							<td class="r {if $total.total.deposit.refund>0}negative{/if}">{$total.total.deposit.refund*-1|number_format:2}</td>
							<td class="r {if $total.total.deposit.cancel_rcv<0}negative{/if}">{$total.total.deposit.cancel_rcv|number_format:2}</td>
							{*<td class="r">{$total.total.deposit.cancel_used|number_format:2}</td>*}
							<td class="r {if $total.total.trade_in.amt<0}negative{/if}">{$total.total.trade_in.amt|number_format:2}</td>
							<td class="r {if $total.total.trade_in.writeoff_amt<0}negative{/if}">{$total.total.trade_in.writeoff_amt|number_format:2}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/if}

{if $config.counter_collection_show_membership_receipt and $mem_data}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Membership Counter Info</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table">
					<thead class="bg-gray-100">
						<tr class="header">
							<th>Counter</th>
							<th>Cash</th>
							<th>Collection</th>
							<th>Variance</th>
						</tr>
					</thead>
					
					{foreach from=$mem_data.by_counter key=counter_id item=mem_data_r}
						<tbody class="fs-08">
							<tr>
								<td>{$counters.$counter_id.network_name}</td>
								<td class="r {if $mem_data_r.cash.amt<0}negative{/if}">{$mem_data_r.cash.amt|number_format:2}</td>
								<td class="r {if $mem_data_r.dom.cash.amt<0}negative{/if}">{$mem_data_r.dom.cash.amt|number_format:2}</td>
								<td class="r {if $mem_data_r.variance.cash.amt<0}negative{/if}">{$mem_data_r.variance.cash.amt|number_format:2}</td>
							</tr>
						</tbody>
					{/foreach}
					
					<tr class="header">
						<td class="r"><b>Total</b></td>
						<td class="r {if $mem_data.all_counter.cash.amt<0}negative{/if}">{$mem_data.all_counter.cash.amt|number_format:2}</td>
						<td class="r {if $mem_data.all_counter.dom.cash.amt<0}negative{/if}">{$mem_data.all_counter.dom.cash.amt|number_format:2}</td>
						<td class="r {if $mem_data.all_counter.variance.cash.amt<0}negative{/if}">{$mem_data.all_counter.variance.cash.amt|number_format:2}</td>
					</tr>
					
					<tr>
						<td>+ Cash From POS</td>
						<td class="r {if $total.payment_type.Cash.amt<0}negative{/if}">{$total.payment_type.Cash.amt|number_format:2}</td>
						<td class="r {if $total.cash_domination.Cash.amt<0}negative{/if}">{$total.cash_domination.Cash.amt|number_format:2}</td>
						<td class="r">-</td>
					</tr>
					<tr>
						<td>- Cash Advance</td>
						<td class="r">-</td>
						<td class="r {if $total.total.cash_advance.amt<0}negative{/if}">{$total.total.cash_advance.amt|number_format:2}</td>
						<td class="r">-</td>
					</tr>
					
					<tr class="header">
						<td class="r"><b>Total Added to POS</b></td>
						<td class="r {if $mem_data.all_counter.added_pos.cash.amt<0}negative{/if}">{$mem_data.all_counter.added_pos.cash.amt|number_format:2}</td>
						<td class="r {if $mem_data.all_counter.added_pos.dom.cash.am<0}negative{/if}">{$mem_data.all_counter.added_pos.dom.cash.amt|number_format:2}</td>
						<td class="r {if $mem_data.all_counter.added_pos.variance.cash.amt<0}negative{/if}">{$mem_data.all_counter.added_pos.variance.cash.amt|number_format:2}</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
{/if}
{/if}

<br />
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table nobreak table mb-0 text-md-nowrap  table-hover" width=100% cellpadding=4 cellspacing=0 >
				<thead class="bg-gray-100">
					<tr class="header" >
						<th>&nbsp;</th>
						 <!-- Normal Payment Method -->
						{foreach from=$normal_payment_type item=payment_type}
							<th>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</th>
						{/foreach}
						
						<!-- Foreign Currency -->
						{if $got_foreign_currency}
							{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
								<th>{$currency_type}</th>
							{/foreach}
							<!--th>{$config.arms_currency.symbol}</th-->
						{/if}
						
						<th>Nett Sales</th>
						<th>Receipt<br />Discount</th>
						{if $got_mm_discount}
							<th>Mix & Match <br>Discount</th>
						{/if}
						{if $got_service_charge}
							<th>Service Charge</th>
						{/if}
						{if $got_gst}
							<th>Tax</th>
						{/if}
						<th>Rounding</th>
						{if $got_foreign_currency}
							<th>Currency Adjust</th>
						{/if}
						<th>Over</th>
						<th>Gross Sales</th>
						<th>Nett Sales<sup>2<sup></th>
					</tr>
				</thead>
				
				<!-- Total Payment -->
				<tr style="font-size:1em;">
					<td><b>Total Payment</b></td>
					<!-- Normal Payment Method -->
					{foreach from=$normal_payment_type item=payment_type}
						<td class="r {if $total.payment_type.$payment_type.amt<0}negative{/if}">{$total.payment_type.$payment_type.amt|number_format:2}</td>
					{/foreach}
			
					<!-- Foreign Currency -->
					{if $got_foreign_currency}
						{*assign var=rm_amt value=0*}
						{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
							{assign var=payment_type value=$currency_type}
							<td class="r {if $total.payment_type.foreign_currency.$payment_type.foreign_amt<0}negative{/if} col_foreign_curr">
								{$total.payment_type.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
								<span class="small_rm_amt">{$config.arms_currency.symbol} {$total.payment_type.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
								{*assign var=rm_amt value=$rm_amt+$total.payment_type.foreign_currency.$payment_type.rm_amt*}
							</td>
						{/foreach}
						<!--td class="r col_rm">{$rm_amt|number_format:2}</td-->
					{/if}
					
					<!-- Nett Sales -->
					<td class="r sales {if $total.payment_type.nett_sales.amt>=0}positive{else}negative{/if}">{$total.payment_type.nett_sales.amt|number_format:2}</td>
					<td class="r {if $total.payment_type.Discount.amt<0}negative{/if}">{$total.payment_type.Discount.amt|number_format:2}</td>
					
					{if $got_mm_discount}
						<td class="r {if $total.payment_type.$mm_discount_col_value.amt<0}negative{/if}">{$total.payment_type.$mm_discount_col_value.amt|number_format:2}</td>
					{/if}
					
					{if $got_service_charge}
						<td class="r {if $total.payment_type.service_charges.amt<0}negative{/if}">{$total.payment_type.service_charges.amt|number_format:2}</td>
					{/if}
					
					{if $got_gst}
						<td class="r {if $total.payment_type.total_gst_amt.amt<0}negative{/if}">{$total.payment_type.total_gst_amt.amt|number_format:2}</td>
					{/if}
					
					<td class="r {if $total.payment_type.Rounding.amt<0}negative{/if}">{$total.payment_type.Rounding.amt|number_format:2}</td>
					{if $got_foreign_currency}
						<td class="r {if $total.payment_type.Currency_adjust.amt<0}negative{/if}">{$total.payment_type.Currency_adjust.amt|number_format:2}</td>
					{/if}
					<td class="r col_over {if $total.payment_type.Over.amt<0}negative{/if}">{$total.payment_type.Over.amt|number_format:2}</td>
					<td class="r gross_sales {if $total.payment_type.gross_sales.amt<0}negative{/if}">{$total.payment_type.gross_sales.amt|number_format:2}</td>
					<td class="r col_nett_sales2 {if $total.total.nett_sales2.amt<0}negative{/if}">{$total.total.nett_sales2.amt|number_format:2}</td>
				</tr>
				
				<!-- Total Variance -->
				<tr style="font-size:1em;" class="tr_variance">
					<td><b>Total Variance</b></td>
					<!-- Normal Payment Method -->
					{foreach from=$normal_payment_type item=payment_type}
						<td class="r {if $total.variance.$payment_type.amt<0}negative{/if}">{$total.variance.$payment_type.amt|number_format:2}</td>
					{/foreach}
			
					<!-- Foreign Currency -->
					{if $got_foreign_currency}
						{*assign var=rm_amt value=0*}
						{foreach from=$foreign_currency_list key=currency_type item=currency_rate}
							{assign var=payment_type value=$currency_type}
							<td class="r {if $total.variance.foreign_currency.$payment_type.foreign_amt<0}negative{/if}">
								{$total.variance.foreign_currency.$payment_type.foreign_amt|number_format:2}<br />
								<span class="small_rm_amt">{$config.arms_currency.symbol} {$total.variance.foreign_currency.$payment_type.rm_amt|number_format:2}</span>
								{*assign var=rm_amt value=$rm_amt+$total.variance.foreign_currency.$payment_type.rm_amt*}
							</td>
						{/foreach}
						<!--td class="r">{$rm_amt|number_format:2}</td-->
					{/if}
					
					<td class="r variance {if $total.total.variance.amt>=0}positive{else}negative{/if}">{$total.total.variance.amt|number_format:2}</td>
					
					{assign var=cols value=5}
					{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
					{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
					{if $got_gst}{assign var=cols value=$cols+1}{/if}
					{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
					<td colspan="{$cols}">&nbsp;</td>
				</tr>
			</table>
		</div>
	</div>
</div>

{if !$smarty.request.print}
{assign var=can_show_finalize value=1}

{if $invalid_mem_data}
	<p>
		<div class="big_font" style="width:700px;margin:auto;text-align:center;">
			Found Invalid Member Card <br />(Please make sure the member is exists before finalize)
		</div>	
		<br />
		<div style="width:700px;margin:auto;text-align:center;">
			<div class="card mx-3">
				<div class="card-body">
					<div class="table-responsive">
						<table class="report_table" style="margin:auto;">
							<thead class="bg-gray-100">
								<tr class="header">
									<th>Member No</th>
									<th>Receipt</th>
								</tr>
							</thead>
							{foreach from=$invalid_mem_data key=cardno item=r}
								<tbody class="fs-08">
									<tr>
										<td>{$cardno}</td>
										<td>
											{foreach from=$r.receipt_list name=fr item=receipt}
												{if !$smarty.foreach.fr.first}, {/if}
												<a href="javascript:void(0)" onclick="trans_detail('{$receipt.counter_id}','{$receipt.cashier_id}','{$receipt.date}','{$receipt.id}','{$receipt.branch_id}')">{receipt_no_prefix_format branch_id=$receipt.branch_id counter_id=$receipt.counter_id receipt_no=$receipt.receipt_no}</a>
											{/foreach}
										</td>
									</tr>
								</tbody>
							{/foreach}
						</table>
					</div>
				</div>
			</div>
		</div>
		
	</p>
	{if !$is_finalized}{assign var=can_show_finalize value=0}{/if}
{/if}

{if $total_invalid_items>0}
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
	{if !$is_finalized}{assign var=can_show_finalize value=0}{/if}
	
	<p align="center">
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table">
					<thead class="bg-gray-100">
						<tr class="header">
							<th>No. of Transaction</th>
							<th>Barcode</th>
							<th>Description</th>
							<th>Price ({$config.arms_currency.symbol}) per unit</th>
							<th>Type</th>
							<th>Approve by</th>
						</tr>
					</thead>
					{foreach from=$invalid_items key=barcode item=data}
						{foreach from=$data key=selling_price item=other}
						<tbody class="fs-08">
							<tr>
								<td>{$other.transactions_total}</td>
								<td>{$barcode}</td>
								<td>{$other.info.sku_description}</td>
								<td class="r">{$selling_price|default:"0"|number_format:2|ifzero:"-"}</td>
								<td>{$other.info.type}</td>
								<td>{$other.info.open_code_user}</td>
							</tr>
						</tbody>
						{/foreach}
					{/foreach}
				</table>
			</div>
		</div>
	</div>
{/if}

{if count($sync_status) > 0}
	{if !$is_finalized}{assign var=can_show_finalize value=0}{/if}
	
	<p align="center">	
	<div class="big_font" id="div_data_sync_error" style="width:700px;margin:auto;text-align:center;">
		Found missing/unsynced sales data as below:
		<p align="center">
		<div class="card mx-3">
			<div class="card-body">
				<div class="table-responsive">
					<table class="report_table">
						<thead class="bg-gray-100">
							<tr class="header">
								<th>Counter Name</th>
								<th>Unsynced Sales</th>
								<th>Sales Missing</th>
							</tr>
						</thead>
						{foreach from=$sync_status key=cid item=r}
							<tbody class="fs-08">
								<tr>
									<td>{$r.counter_name}</td>
									<td>{$r.ttl_unsync_record}</td>
									<td>{$r.ttl_ms_record}</td>
								</tr>
							</tbody>
						{/foreach}
					</table>
				</div>
			</div>
		</div>
		</p>
	</div>
	</p>
{/if}

{/if}

{if $can_show_finalize}
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
{include file='footer.tpl'}
<script>
	init_calendar();
</script>
{else}
</div>
</body>
</html>
{/if}
