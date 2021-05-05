{*
8/23/2011 3:17:26 PM Andy
- Add print button and print additional extra content.
- Add "Total" for Abnormal Transaction - Backend.

9/13/2011 4:03:55 PM Andy
- Show branch code and date at when print.
- Make right outline border bold for page 2 printing table.

9/20/2011 2:20:14 PM Andy
- Make all outline border bold for page 2 printing table.
- Add checking only allow to show report if counter collection already finalized.

12/14/2011 10:43:47 AM Andy
- Change "Abnormal Transaction - Back End" to "Counter Collection Summary - Final".
- Change "Abnormal Transaction - Front End" to "Counter Activity".
- Add header "Debit" and "Credit" at printing summary.
- Add grey color for those cell got data under "Counter Activity".
- Add grey color for row "Counter Collection Summary - Final" if found "Remark" got variance.
- Modify "Cash Advance" at "Counter Collection Summary - Final" to turn positive/negative opposite.
- Modify "Total Sales by Payment Type" to 3 rows. (Counter Sales, Variance and Total Sales)

4/27/2012 5:02:58 PM Andy
- Add "Top Up" information.
- Add highlight for those column if got data or negative.
- Change no need to show column title for every counter, only show 1 time at table top.

5/22/2012 5:43:43 PM Justin
- Enhanced to highlight Rounding and Variance columns/rows if contains any amount instead of highlight when it is negative only.
- Changed the Short/Over Collection that place wrongly.
- Removed the empty column for Additional Print Area.
- Changed the wording "G=D+E-F-D2" to "G=D-(E+F)-D2".

10/11/2012 10:28:00 AM Fithri
- add mix and match and discount
- modify printing template

10/16/2012 2:41:00 PM Fithri
- counter collection details report, variance show highlight color if got figure

10/16/2012 4:00:00 PM Fithri
- counter collection details report, change some labels and change hightlight colour to black (from red) when printed

11/23/2012 4:16 PM Andy
- Change mix & match and discount from negative to positive.
- Add mix & match discount into denomination.

1/7/2012 10:16 AM Andy
- Fix no border when printing.

1/8/2013 3:30 PM Justin
- Enhanced to rename Counter Sales into Actual Sales and Actual Sales into Sales.

5/22/2013 10:47 AM Fithri
- add some new rows & change some labels

11/6/2013 3:25 PM Justin
- Enhanced to include various of enhancements given by customers.

12/20/2013 5:33 PM Andy
- Fix discount amount still in negative value at area "Counter Collection Summary".
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
.col_foreign_curr{
    background: #ffc;
}

.small_rm_amt{
    font-size: 10px;
}

.positive{
	color:blue;
	font-weight:bold;
}

.col_highlight{
	background-color: #dfdfdf;
}
.tr_variance{
	color: blue;
}
.tr_variance td, .tr_variance th, .tr_summary_diff td, .tr_summary_diff th{
	border-bottom:2px solid black !important;
}
.r{
	text-align: right;
}
.xtra_separator td{
	border-top:2px solid black;
}
.user_keyin_box{
	height:20px;
	min-width:100px;
	border:1px solid black;
}
.div_box{
	border:1px solid black;padding:2px;
}
.blur_bg{
	background-color:#ddd;
}

.tb2{
	border:2px solid black !important;
}
{/literal}
</style>

<style>
{if $is_print}
{literal}
.negative {
	color:#000;
	font-weight:bold;
}
{/literal}
{else}
{literal}
.negative {
	color:#f00;
	font-weight:bold;
}
{/literal}
{/if}
</style>

{if !$is_print}
<script>
{literal}
function init_calendar(){
	Calendar.setup({
	    inputField     :    "inp_date",     // id of the input field
	    ifFormat       :    "%Y-%m-%d",      // format of the input field
	    button         :    "img_date",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}

function check_form(){
	if(document.f_a['date'].value.trim()==''){
		alert('Please select date');
		document.f_a['date'].focus();
		return false;
	}
	return true;
}

function print_form(){
	if(!check_form())	return false;
	
	document.f_a.target = '_blank';
	document.f_a['is_print'].value = 1;
	document.f_a.submit();
	
	document.f_a.target = '';
	document.f_a['is_print'].value = 0;
}
{/literal}
</script>
{else}
	<body onload="window.print()">
	<div class=printarea>
{/if}

<h1>{$PAGE_TITLE} {if $is_print}({$BRANCH_CODE}, {$smarty.request.date}){/if}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$is_print}
<form name="f_a" class="stdframe" onSubmit="return check_form();">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="is_print" value="0" />
	
	<b>Select Date:</b> 
	<input id="inp_date" name="date" value="{$smarty.request.date}" size="10" /> 
	<img align="absbottom" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="submit" value="Refresh" />
	<input type="button" value="Print" onClick="print_form();" />
</form>

<script>init_calendar();</script>
{/if}

{assign var=mm_discount_col_value value=$mm_discount_col_value|lower}

<br />
{if $smarty.request.load_report and !$err}
	{if !$data}-- No Data --
	{else}
	
		<!-- Total -->
		<table class="report_table {if $is_print}tb{/if}" {if $is_print}border="1" cellspacing="0" cellpadding="4"{/if}>
			<tr class="header">
				<th colspan="6">Total</th>
			</tr>
			<tr class="header">
				<th>Actual Sales</th>
				<th>Sales</th>
				<th>Collection</th>
				<th>Rounding</th>
				<th>Variance</th>
				<th>Over</th>
			</tr>
			<tr>
				<!-- Counter Sales -->
				<td class="r {if $data.total.counter_sales.amt<0}negative col_highlight{/if}">{$data.total.counter_sales.amt|number_format:2}</td>
				
				<!-- Actual Sales -->
				<td class="r {if $data.total.actual_sales.amt<0}negative col_highlight{/if}">{$data.total.actual_sales.amt|number_format:2}</td>
				
				<!-- Collection -->
				<td class="r {if $data.total.collection.amt<0}negative col_highlight{/if}">{$data.total.collection.amt|number_format:2}</td>
				
				<!-- Rounding -->
				<td class="r {if $data.total.rounding.amt<0}negative{/if} {if $data.total.rounding.amt}col_highlight{/if}">{$data.total.rounding.amt|number_format:2}</td>
				
				<!-- Variance -->
				<td class="r {if $data.total.variance.amt<0}negative{/if} {if $data.total.variance.amt}col_highlight{/if}">{$data.total.variance.amt|number_format:2}</td>
				
				<!-- Over -->
				<td class="r {if $data.total.over.amt<0}negative col_highlight{/if}">{$data.total.over.amt|number_format:2}</td>
			</tr>
		</table>
		
		<br />
		
		<!-- Total Sales by Payment Type -->
		<table class="report_table {if $is_print}tb{/if}" {if $is_print}border="1" cellspacing="0" cellpadding="4"{/if}>
			<tr class="header">
				<th rowspan="2">&nbsp;</th>
				
				{capture assign=c1}{count var=$pos_config.normal_payment_type}{/capture}
				{capture assign=c2}{count var=$data.foreign_currency_list}{/capture}
				{if $got_mm_discount}
				{assign var=added_col value=2}
				{else}
				{assign var=added_col value=1}
				{/if}
				<th colspan="{$c1+$c2+$added_col}">Total Sales by Payment Type</th>
			</tr>
			<tr class="header">
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<th>{$pos_config.normal_payment_type_label.$payment_type}</th>
				{/foreach}
				
					<th>Discount</th>
					{if $got_mm_discount}<th>Mix & Match<br />Discount</th>{/if}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<th class="col_foreign_curr">{$currency_type|strtoupper}</th>
				{/foreach}
			</tr>
			
			<!-- Counter Sales -->
			<tr>
				<td class="col_header"><b>Counter Sales</b></td>
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<td class="r {if $data.total.payment_type.$payment_type.amt<0}negative col_highlight{/if}">{$data.total.payment_type.$payment_type.amt|number_format:2}</td>
				{/foreach}
					
					<td class="r {if $data.total.payment_type.discount.amt<0}negative col_highlight{/if}">{$data.total.payment_type.discount.amt|number_format:2}</td>
					
					{if $got_mm_discount}<td class="r {if $data.total.payment_type.$mm_discount_col_value.amt<0}negative col_highlight{/if}">{$data.total.payment_type.$mm_discount_col_value.amt|number_format:2}</td>{/if}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<td class="r col_foreign_curr {if $data.total.payment_type.$currency_type.amt<0}negative col_highlight{/if}">{$data.total.payment_type.$currency_type.amt|number_format:2}</td>
				{/foreach}
			</tr>
			
			<!-- Variance -->
			<tr>
				<td class="col_header"><b>Variance</b></td>
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<td class="r {if $data.total.payment_type.$payment_type.variance<0}negative{/if} {if $data.total.payment_type.$payment_type.variance}col_highlight{/if}">{$data.total.payment_type.$payment_type.variance|number_format:2}</td>
				{/foreach}
				
					<td>-</td>
					{if $got_mm_discount}<td>-</td>{/if}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<td class="r col_foreign_curr {if $data.total.payment_type.$currency_type.variance<0}negative col_highlight{/if}">{$data.total.payment_type.$currency_type.variance|number_format:2}</td>
				{/foreach}
			</tr>
			
			<!-- Total Sales -->
			<tr>
				<td class="col_header"><b>Total Sales</b></td>
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<td class="r {if $data.total.payment_type.$payment_type.act_amt<0}negative col_highlight{/if}">{$data.total.payment_type.$payment_type.act_amt|number_format:2}</td>
				{/foreach}
				
					<td>-</td>
					{if $got_mm_discount}<td>-</td>{/if}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<td class="r col_foreign_curr {if $data.total.payment_type.$currency_type.act_amt<0}negative col_highlight{/if}">{$data.total.payment_type.$currency_type.act_amt|number_format:2}</td>
				{/foreach}
			</tr>
		</table>
		
		<!-- Summary -->
		<br />
		<h2>Counter Collection Summary</h2>
		<table width="100%" class="report_table {if $is_print}tb{/if}" {if $is_print}border="1" cellspacing="0" cellpadding="4"{/if}>
			{foreach from=$data.summary key=counter_id item=counter_data name=summary_loop}
				{if $smarty.foreach.summary_loop.first}
					<tr class="header">
						<th>Counter</th>
						<th>&nbsp;</th>
						{foreach from=$pos_config.normal_payment_type item=payment_type}
							<th>{$pos_config.normal_payment_type_label.$payment_type}</th>
						{/foreach}
						
						<!-- Currency -->
						{foreach from=$data.foreign_currency_list key=currency_type item=currency}
							<th class="col_foreign_curr">{$currency_type|strtoupper}</th>
						{/foreach}
						
						<th>Discount</th>
						{if $got_mm_discount}<th>Mix & Match<br />Discount</th>{/if}
						<th>Rounding</th>
						<th>Over</th>
						<th>Total Sales</th>
					</tr>
				{/if}
				<tr>
					{assign var=row_span value=4}
					{if $counter_data.top_up}{assign var=row_span value=$row_span+1}{/if}
					<th rowspan="{$row_span}"><h1>{$pos_counters.$counter_id.network_name}</h1></th>
					
					<!-- Sales -->
					<th align="left">Sales</th>
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<td class="r {if $counter_data.cashier_sales.$payment_type.amt<0}negative col_highlight{/if}">
							{$counter_data.cashier_sales.$payment_type.amt|number_format:2}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<td class="col_foreign_curr r {if $counter_data.cashier_sales.$currency_type.amt<0}negative col_highlight{/if}">
							{if $counter_data.cashier_sales.$currency_type.currency_amt}
								{$counter_data.cashier_sales.$currency_type.currency_amt|number_format:2}<br />
								<span class="small_rm_amt">RM {$counter_data.cashier_sales.$currency_type.amt|number_format:2}</span>
							{else}
							-
							{/if} 
						</td>
					{/foreach}
					
					<!-- Discount -->
					<td class="r {if $counter_data.cashier_sales.discount.amt<0}negative col_highlight{/if}">
						{$counter_data.cashier_sales.discount.amt|number_format:2}
					</td>
					
					{if $got_mm_discount}
					<!-- Mix & Match -->
					<td class="r {if $counter_data.cashier_sales.$mm_discount_col_value.amt<0}negative col_highlight{/if}">
						{$counter_data.cashier_sales.$mm_discount_col_value.amt|number_format:2}
					</td>
					{/if}
					
					<!-- Rounding -->
					<td class="r {if $counter_data.cashier_sales.rounding.amt<0}negative{/if} {if $counter_data.cashier_sales.rounding.amt}col_highlight{/if}">
						{$counter_data.cashier_sales.rounding.amt|number_format:2}
					</td>
					
					<!-- Over -->
					<td class="r {if $counter_data.cashier_sales.over.amt<0}negative{/if} {if $counter_data.cashier_sales.over.amt}col_highlight{/if}">
						{$counter_data.cashier_sales.over.amt|number_format:2}
					</td>
					
					<!-- Total Sales -->
					<td class="r {if $counter_data.cashier_sales.total_sales.amt<0}negative col_highlight{/if}">{$counter_data.cashier_sales.total_sales.amt|number_format:2}</td>
				</tr>
				
				<!-- Top Up -->
				{if $counter_data.top_up}
					<tr>
						<th align="left">Top Up</th>
						{foreach from=$pos_config.normal_payment_type item=payment_type}
							<td class="r {if $counter_data.cashier_sales.$payment_type.amt<0}negative col_highlight{/if}">
								{$counter_data.top_up.$payment_type.amt|number_format:2}
							</td>
						{/foreach}
						
						<!-- Currency -->
						{foreach from=$data.foreign_currency_list key=currency_type item=currency}
							<td class="col_foreign_curr r">-</td>
						{/foreach}
						
						<td class="r">-</td>
						<td class="r">-</td>
						
						<!-- Total Sales -->
						<td class="r {if $counter_data.top_up.total_sales.amt<0}negative col_highlight{/if}">{$counter_data.top_up.total_sales.amt|number_format:2}</td>
					</tr>
				{/if}
				
				<!-- Cash Advance -->
				<tr>
					<th align="left">Cash Advance</th>
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						
						<td class="r {if $counter_data.cash_advance.$payment_type.amt<0}negative{/if}">
							{if $payment_type eq 'cash'}
								{$counter_data.cash_advance.$payment_type.amt|number_format:2}
							{else}
								-
							{/if}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<td class="col_foreign_curr r">-</td>
					{/foreach}
					
					<td class="r">-</td>
					{if $got_mm_discount}<td class="r">-</td>{/if}
					<td class="r">-</td>
					<td class="r">-</td>
					
					<!-- Total Sales -->
					<td class="r {if $counter_data.cash_advance.total_sales.amt<0}negative{/if}">{$counter_data.cash_advance.total_sales.amt|number_format:2}</td>
				</tr>
				
				<!-- Denomination -->
				<tr>
					<th align="left">Denomination</th>
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<td class="r {if $counter_data.cash_domination.$payment_type.amt<0}negative col_highlight{/if}">
							{$counter_data.cash_domination.$payment_type.amt|number_format:2}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<td class="col_foreign_curr r {if $counter_data.cash_domination.$currency_type.amt<0}negative col_highlight{/if}">
							{if $counter_data.cash_domination.$currency_type.currency_amt}
								{$counter_data.cash_domination.$currency_type.currency_amt|number_format:2}<br />
								<span class="small_rm_amt">RM {$counter_data.cash_domination.$currency_type.amt|number_format:2}</span>
							{else}
								-
							{/if} 
						</td>
					{/foreach}
					
					<td class="r">-</td>
					{if $got_mm_discount}<td class="r {if $counter_data.cash_domination.$mm_discount_col_value.amt<0}negative col_highlight{/if}">{$counter_data.cash_domination.$mm_discount_col_value.amt|number_format:2}</td>{/if}
					<td class="r">-</td>
					<td class="r">-</td>
					
					<!-- Total Sales -->
					<td class="r {if $counter_data.cash_domination.total_sales.amt<0}negative col_highlight{/if}">{$counter_data.cash_domination.total_sales.amt|number_format:2}</td>
				</tr>
				
				<!-- Variance -->
				<tr class="tr_variance">
					<th align="left">Variance</th>
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<td class="r {if $counter_data.variance.$payment_type.amt<0}negative{/if} {if $counter_data.variance.$payment_type.amt}col_highlight{/if}">
							{$counter_data.variance.$payment_type.amt|number_format:2}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<td class="col_foreign_curr r {if $counter_data.variance.$currency_type.amt<0}negative col_highlight{/if}">
							{if $counter_data.variance.$currency_type.currency_amt}
								{$counter_data.variance.$currency_type.currency_amt|number_format:2}<br />
								<span class="small_rm_amt">RM {$counter_data.variance.$currency_type.amt|number_format:2}</span>
							{else}
								-
							{/if}
						</td>
					{/foreach}
					
					<td class="r">-</td>
					{if $got_mm_discount}<td class="r">-</td>{/if}
					<td class="r">-</td>
					<td class="r">-</td>
					
					<!-- Total Sales -->
					<td class="r {if $counter_data.variance.total_sales.amt<0}negative{/if} {if $counter_data.variance.total_sales.amt}col_highlight{/if}">{$counter_data.variance.total_sales.amt|number_format:2}</td>
				</tr>
			{/foreach}
		</table>
		
		<!-- Abnormal Transaction - Back End -->
		<br />
		<h2>Counter Collection Summary - Final</h2>
		<table width="100%" class="report_table {if $is_print}tb{/if}" {if $is_print}border="1" cellspacing="0" cellpadding="4"{/if}>
			{foreach from=$pos_counters key=counter_id item=counter name="summary_final_loop"}
				{assign var=counter_data value=$data.summary.$counter_id}
				{assign var=counter_abnormal value=$data.abnormal_tran.backend.$counter_id}
				
				{if $smarty.foreach.summary_final_loop.first}
					<tr class="header">
						<th>Counter</th>
						<!-- Normal Payment Type -->
						{foreach from=$pos_config.normal_payment_type item=payment_type}
							<th colspan="2">{$pos_config.normal_payment_type_label.$payment_type}</th>
						{/foreach}
						
						<!-- Currency -->
						{foreach from=$data.foreign_currency_list key=currency_type item=currency}
							<th colspan="2" class="col_foreign_curr">{$currency_type|strtoupper}</th>
						{/foreach}
						
						{if $got_top_up}<th colspan="2">Top Up</th>{/if}
						<th colspan="2">Cash Advance</th>
						<th colspan="2">Denomination</th>
						<th colspan="2">REMARK</th>
						
					</tr>
				{/if}
				<tr>
					<th rowspan="3"><h1>{$counter.network_name}</h1></th>
					<!-- Normal Payment Type -->
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<th>Original</th>
						<th>Amend</th>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<th class="col_foreign_curr">Original</th>
						<th class="col_foreign_curr">Amend</th>
					{/foreach}
					
					<!-- Top Up -->
					{if $got_top_up}
						<th>Original</th>
						<th>Amend</th>
					{/if}
					
					<!-- Cash Advance -->
					<th>Original</th>
					<th>Amend</th>
					
					<!-- Denomination -->
					<th>Original</th>
					<th>Amend</th>
					
					<!-- REMARK -->
					<th>+</th>
					<th>-</th>
				</tr>
				
				<tr>
					<!-- Normal Payment Type -->
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<!-- Original -->
						<td class="r {if $counter_data.cashier_sales.$payment_type.old_amt<0}negative{/if}">
							{$counter_data.cashier_sales.$payment_type.old_amt|number_format:2}
						</td>
						
						<!-- Amend -->
						<td class="r {if $counter_data.cashier_sales.$payment_type.amt<0}negative{/if}">
							{$counter_data.cashier_sales.$payment_type.amt|number_format:2}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<!-- Original -->
						<td class="col_foreign_curr r {if $counter_data.cashier_sales.$currency_type.old_amt<0}negative{/if}">
							{if $counter_data.cashier_sales.$currency_type.old_currency_amt}
								{$counter_data.cashier_sales.$currency_type.old_currency_amt|number_format:2}<br />
								<span class="small_rm_amt">RM {$counter_data.cashier_sales.$currency_type.old_amt|number_format:2}</span>
							{else}
								-
							{/if} 
						</td>
						
						<!-- Amend -->
						<td class="col_foreign_curr r {if $counter_data.cashier_sales.$currency_type.amt<0}negative{/if}">
							{if $counter_data.cashier_sales.$currency_type.currency_amt}
								{$counter_data.cashier_sales.$currency_type.currency_amt|number_format:2}<br />
								<span class="small_rm_amt">RM {$counter_data.cashier_sales.$currency_type.amt|number_format:2}</span>
							{else}
								-
							{/if} 
						</td>
					{/foreach}
					
					<!-- Top Up -->
					{if $got_top_up}
						<!-- Original -->
						<td class="r {if ($counter_data.top_up.total_sales.old_amt)<0}negative{/if}">
							{$counter_data.top_up.total_sales.old_amt|number_format:2}
						</td>
						
						<!-- Amend -->
						<td class="r {if ($counter_data.top_up.total_sales.amt)<0}negative{/if}">
							{$counter_data.top_up.total_sales.amt|number_format:2}
						</td>
					{/if}
					
					<!-- Cash Advance -->
					<!-- Original -->
					<td class="r {if ($counter_data.cash_advance.total_sales.old_amt*-1)<0}negative{/if}">
						{$counter_data.cash_advance.total_sales.old_amt*-1|number_format:2}
					</td>
					
					<!-- Amend -->
					<td class="r {if ($counter_data.cash_advance.total_sales.amt*-1)<0}negative{/if}">
						{$counter_data.cash_advance.total_sales.amt*-1|number_format:2}
					</td>
					
					<!-- Denomination -->
					<!-- Original -->
					<td class="r {if $counter_data.cash_domination.total_sales.old_amt<0}negative{/if}">
						{$counter_data.cash_domination.total_sales.old_amt|number_format:2}
					</td>
					<!-- Amend -->
					<td class="r {if $counter_data.cash_domination.total_sales.amt<0}negative{/if}">
						{$counter_data.cash_domination.total_sales.amt|number_format:2}
					</td>
					
					<!-- REMARK -->
					<!-- + -->
					<td class="r {if $counter_abnormal.remark.inc.amt>0}positive{/if}">
						{$counter_abnormal.remark.inc.amt|number_format:2}
					</td>
					<!-- - -->
					<td class="r {if $counter_abnormal.remark.dec.amt<0}negative{/if}">
						{$counter_abnormal.remark.dec.amt|number_format:2}
					</td>
				</tr>
				
				<tr class="tr_summary_diff">
					<!-- Normal Payment Type -->
					{foreach from=$pos_config.normal_payment_type item=payment_type}
						<td class="{if $counter_abnormal.$payment_type.diff.amt>0}positive{elseif $counter_abnormal.$payment_type.diff.amt<0}negative{/if} {if $counter_abnormal.$payment_type.diff.amt}col_highlight{/if}" colspan="2" align="center">
							{if $counter_abnormal.$payment_type.diff.amt>0}+{/if}{$counter_abnormal.$payment_type.diff.amt|number_format:2}
						</td>
					{/foreach}
					
					<!-- Currency -->
					{foreach from=$data.foreign_currency_list key=currency_type item=currency}
						<td class="col_foreign_curr {if $counter_abnormal.$currency_type.diff.amt>0}positive{elseif $counter_abnormal.$currency_type.diff.amt<0}negative{/if} {if $counter_abnormal.$currency_type.diff.amt}col_highlight{/if}" colspan="2" align="center">
							{if $counter_abnormal.$currency_type.diff.currency_amt>0}+{/if}{$counter_abnormal.$currency_type.diff.currency_amt|number_format:2}<br />
							<span class="small_rm_amt">RM {$counter_abnormal.$currency_type.diff.amt|number_format:2}</span>
						</td>
					{/foreach}
					
					<!-- Top Up -->
					{if $got_top_up}
						<td class="{if $counter_abnormal.top_up.diff.amt>0}positive{elseif $counter_abnormal.top_up.diff.amt<0}negative{/if} {if $counter_abnormal.top_up.diff.amt}col_highlight{/if}" colspan="2" align="center">
							{if $counter_abnormal.top_up.diff.amt>0}+{/if}{$counter_abnormal.top_up.diff.amt|number_format:2}
						</td>
					{/if}
					
					<!-- Cash Advance -->
					<td class="{if $counter_abnormal.cash_advance.diff.amt>0}positive{elseif $counter_abnormal.cash_advance.diff.amt<0}negative{/if} {if $counter_abnormal.cash_advance.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $counter_abnormal.cash_advance.diff.amt>0}+{/if}{$counter_abnormal.cash_advance.diff.amt|number_format:2}
					</td>
					
					<!-- Denomination -->
					<td class="{if $counter_abnormal.cash_domination.diff.amt>0}positive{elseif $counter_abnormal.cash_domination.diff.amt<0}negative{/if} {if $counter_abnormal.cash_domination.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $counter_abnormal.cash_domination.diff.amt>0}+{/if}{$counter_abnormal.cash_domination.diff.amt|number_format:2}
					</td>
					
					<!-- REMARK -->
					<td class="{if $counter_abnormal.remark.diff.amt>0}positive{elseif $counter_abnormal.remark.diff.amt<0}negative{/if} {if $counter_abnormal.remark.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $counter_abnormal.remark.diff.amt>0}+{/if}{$counter_abnormal.remark.diff.amt|number_format:2}
					</td>
				</tr>
			{/foreach}
			
			<!-- Total -->
			<tr class="header">
				<th rowspan="4"><h1>Total</h1></th>
				<!-- Normal Payment Type -->
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<th colspan="2">{$pos_config.normal_payment_type_label.$payment_type}</th>
				{/foreach}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<th colspan="2" class="col_foreign_curr">{$currency_type|strtoupper}</th>
				{/foreach}
				
				<!-- Top Up -->
				{if $got_top_up}<th colspan="2">Top Up</th>{/if}
				<th colspan="2">Cash Advance</th>
				<th colspan="2">Denomination</th>
				<th colspan="2">REMARK</th>
			</tr>
			<tr>
				<!-- Normal Payment Type -->
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<th>Original</th>
					<th>Amend</th>
				{/foreach}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<th class="col_foreign_curr">Original</th>
					<th class="col_foreign_curr">Amend</th>
				{/foreach}
				
				<!-- Top Up -->
				{if $got_top_up}
					<th>Original</th>
					<th>Amend</th>
				{/if}
				
				<!-- Cash Advance -->
				<th>Original</th>
				<th>Amend</th>
				
				<!-- Denomination -->
				<th>Original</th>
				<th>Amend</th>
				
				<!-- REMARK -->
				<th>+</th>
				<th>-</th>
			</tr>
			
			<tr>
				<!-- Normal Payment Type -->
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<!-- Original -->
					<td class="r {if $data.total.summary.cashier_sales.$payment_type.old_amt<0}negative{/if}">
						{$data.total.summary.cashier_sales.$payment_type.old_amt|number_format:2}
					</td>
					
					<!-- Amend -->
					<td class="r {if $data.total.summary.cashier_sales.$payment_type.amt<0}negative{/if}">
						{$data.total.summary.cashier_sales.$payment_type.amt|number_format:2}
					</td>
				{/foreach}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<!-- Original -->
					<td class="col_foreign_curr r {if $data.total.summary.cashier_sales.$currency_type.old_amt<0}negative{/if}">
						{if $data.total.summary.cashier_sales.$currency_type.old_currency_amt}
							{$data.total.summary.cashier_sales.$currency_type.old_currency_amt|number_format:2}<br />
							<span class="small_rm_amt">RM {$data.total.summary.cashier_sales.$currency_type.old_amt|number_format:2}</span>
						{else}
							-
						{/if} 
					</td>
					
					<!-- Amend -->
					<td class="col_foreign_curr r {if $data.total.summary.cashier_sales.$currency_type.amt<0}negative{/if}">
						{if $data.total.summary.cashier_sales.$currency_type.currency_amt}
							{$data.total.summary.cashier_sales.$currency_type.currency_amt|number_format:2}<br />
							<span class="small_rm_amt">RM {$data.total.summary.cashier_sales.$currency_type.amt|number_format:2}</span>
						{else}
							-
						{/if} 
					</td>
				{/foreach}
				
				<!-- Top Up -->
				{if $got_top_up}
					<!-- Original -->
					<td class="r {if ($data.total.top_up.old_amt)<0}negative{/if}">
						{$data.total.top_up.old_amt|number_format:2}
					</td>
					
					<!-- Amend -->
					<td class="r {if ($data.total.top_up.amt)<0}negative{/if}">
						{$data.total.top_up.amt|number_format:2}
					</td>
				{/if}
				
				<!-- Cash Advance -->
				<!-- Original -->
				<td class="r {if ($data.total.cash_advance.old_amt*-1)<0}negative{/if}">
					{$data.total.cash_advance.old_amt*-1|number_format:2}
				</td>
				
				<!-- Amend -->
				<td class="r {if ($data.total.cash_advance.amt*-1)<0}negative{/if}">
					{$data.total.cash_advance.amt*-1|number_format:2}
				</td>
				
				<!-- Denomination -->
				<!-- Original -->
				<td class="r {if $data.total.cash_domination.old_amt<0}negative{/if}">
					{$data.total.cash_domination.old_amt|number_format:2}
				</td>
				<!-- Amend -->
				<td class="r {if $data.total.cash_domination.amt<0}negative{/if}">
					{$data.total.cash_domination.amt|number_format:2}
				</td>
				
				<!-- REMARK -->
				<!-- + -->
				<td class="r {if $data.abnormal_tran.backend.total.remark.inc.amt>0}positive{/if}">
					{$data.abnormal_tran.backend.total.remark.inc.amt|number_format:2}
				</td>
				<!-- - -->
				<td class="r {if $data.abnormal_tran.backend.total.remark.dec.amt<0}negative{/if}">
					{$data.abnormal_tran.backend.total.remark.dec.amt|number_format:2}
				</td>
			</tr>
			
			<tr class="">
				<!-- Normal Payment Type -->
				{assign var=counter_abnormal value=$data.abnormal_tran.backend.total}
				{foreach from=$pos_config.normal_payment_type item=payment_type}
					<td class="{if $counter_abnormal.$payment_type.diff.amt>0}positive{elseif $counter_abnormal.$payment_type.diff.amt<0}negative{/if} {if $counter_abnormal.$payment_type.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $dcounter_abnormal.$payment_type.diff.amt>0}+{/if}{$counter_abnormal.$payment_type.diff.amt|number_format:2}
					</td>
				{/foreach}
				
				<!-- Currency -->
				{foreach from=$data.foreign_currency_list key=currency_type item=currency}
					<td class="col_foreign_curr {if $counter_abnormal.$currency_type.diff.amt>0}positive{elseif $counter_abnormal.$currency_type.diff.amt<0}negative{/if} {if $counter_abnormal.$currency_type.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $counter_abnormal.$currency_type.diff.currency_amt>0}+{/if}{$counter_abnormal.$currency_type.diff.currency_amt|number_format:2}<br />
						<span class="small_rm_amt">RM {$counter_abnormal.$currency_type.diff.amt|number_format:2}</span>
					</td>
				{/foreach}
				
				<!-- Top Up -->
				{if $got_top_up}
					<td class="{if $counter_abnormal.top_up.diff.amt>0}positive{elseif $counter_abnormal.top_up.diff.amt<0}negative{/if} {if $counter_abnormal.top_up.diff.amt}col_highlight{/if}" colspan="2" align="center">
						{if $counter_abnormal.top_up.diff.amt>0}+{/if}{$counter_abnormal.top_up.diff.amt|number_format:2}
					</td>
				{/if}
				
				<!-- Cash Advance -->
				<td class="{if $counter_abnormal.cash_advance.diff.amt>0}positive{elseif $counter_abnormal.cash_advance.diff.amt<0}negative{/if} {if $counter_abnormal.cash_advance.diff.amt}col_highlight{/if}" colspan="2" align="center">
					{if $counter_abnormal.cash_advance.diff.amt>0}+{/if}{$counter_abnormal.cash_advance.diff.amt|number_format:2}
				</td>
				
				<!-- Denomination -->
				<td class="{if $counter_abnormal.cash_domination.diff.amt>0}positive{elseif $counter_abnormal.cash_domination.diff.amt<0}negative{/if} {if $counter_abnormal.cash_domination.diff.amt}col_highlight{/if}" colspan="2" align="center">
					{if $counter_abnormal.cash_domination.diff.amt>0}+{/if}{$counter_abnormal.cash_domination.diff.amt|number_format:2}
				</td>
				
				<!-- REMARK -->
				<td class="{if $counter_abnormal.remark.diff.amt>0}positive{elseif $counter_abnormal.remark.diff.amt<0}negative{/if} {if $counter_abnormal.remark.diff.amt}col_highlight{/if}" colspan="2" align="center">
					{if $counter_abnormal.remark.diff.amt>0}+{/if}{$counter_abnormal.remark.diff.amt|number_format:2}
				</td>
			</tr>
		</table>
		
		<!-- Abnormal Transaction - Front End -->
		<br />
		<h2>Counter Activity</h2>
		<table width="100%" class="report_table {if $is_print}tb{/if}" {if $is_print}border="1" cellspacing="0" cellpadding="4"{/if}>
			{foreach from=$pos_counters key=counter_id item=counter name="abn_loop"}
				{assign var=counter_data value=$data.summary.$counter_id}
				
				{if $smarty.foreach.abn_loop.first}
					<tr class="header">
						<th rowspan="2">Counter</th>
						<th>Drawer</th>
						<th colspan="2">Cancelled</th>
						<th colspan="2">Receipt Discount</th>
						{foreach from=$abnormal_front_end_type key=ab_type item=ab}
							<th colspan="2">{$ab}</th>
						{/foreach}
						<th rowspan="2">Over</th>
					</tr>
					<tr class="header">		
						<th nowrap>Open Count / Trans = Diff</th>
						<th>Amt</th>
						<th>Trans</th>
						<th>Amt</th>
						<th>Trans</th>
						{foreach from=$abnormal_front_end_type key=ab_type item=ab}
							<th>Amt</th>
							<th>Qty</th>
						{/foreach}
					</tr>
				{/if}
				
				<tr>
					<th><h2>{$counter.network_name}</h2></th>
					<!-- Drawer -->
					<td align="center" nowrap class="{if $counter_data.drawer_open_count ne $counter_data.valid_pos.count}col_highlight{/if}">
						<span class="{if $counter_data.drawer_open_count<$counter_data.valid_pos.count}positive{elseif $counter_data.drawer_open_count>$counter_data.valid_pos.count}negative{/if}">
						{$counter_data.drawer_open_count|number_format}
						</span>
						/
						{$counter_data.valid_pos.count|number_format}
						
						{if $counter_data.drawer_open_count ne $counter_data.valid_pos.count}
							= {$counter_data.drawer_open_count-$counter_data.valid_pos.count} 
						{/if}
					</td>
					
					<!-- Cancelled -->
					<td class="r {if $counter_data.cancelled_pos.amt<0}negative{/if} {if $counter_data.cancelled_pos.amt}col_highlight{/if}">
						{$counter_data.cancelled_pos.amt|number_format:2}
					</td>
					<td class="r {if $counter_data.cancelled_pos.count<0}negative{/if} {if $counter_data.cancelled_pos.count}col_highlight{/if}">
						{$counter_data.cancelled_pos.count|number_format}
					</td>
					
					<!-- Receipt Discount -->
					<td class="r {if $counter_data.cashier_sales.discount.amt<0}negative{/if} {if $counter_data.cashier_sales.discount.amt}col_highlight{/if}">
						{$counter_data.cashier_sales.discount.amt|number_format:2}
					</td>
					<td class="r {if $counter_data.cashier_sales.discount.tran_count<0}negative{/if} {if $counter_data.cashier_sales.discount.tran_count}col_highlight{/if}">
						{$counter_data.cashier_sales.discount.tran_count|number_format}
					</td>
					
					{foreach from=$abnormal_front_end_type key=ab_type item=ab}
						<td class="r {if $counter_data.items.$ab_type.amt<0}negative{/if} {if $counter_data.items.$ab_type.amt}col_highlight{/if}">
							{$counter_data.items.$ab_type.amt|number_format:2}
						</td>
						<td class="r {if $counter_data.items.$ab_type.qty<0}negative{/if} {if $counter_data.items.$ab_type.qty}col_highlight{/if}">
							{$counter_data.items.$ab_type.qty|num_format:2}
						</td>
					{/foreach}
					
					<!-- Over -->
					<td class="r {if $counter_data.cashier_sales.over.amt<0}negative{/if} {if $counter_data.cashier_sales.over.amt}col_highlight{/if}">
						{$counter_data.cashier_sales.over.amt|number_format:2}
					</td>
				</tr>
			{/foreach}
		</table>
		
		{if $is_print}
			</div>
			
			{if $config.counter_collection_details_extra_print_area}
				<!-- Additional Print Area -->
				<br />
				<div class="printarea">
					<table class="tb tb2" border="1" cellspacing="0" cellpadding="4">
						<tr>
							<td colspan="3">&nbsp;</td>
							<th>Debit</th>
							<th>Credit</th>
						</tr>
						<tr>
							<td>Total Cash Collections</td>
							<td>A1</td>
							<td class="r {if $data.xtra.cash_collected.amt<0}negative{/if}">{$data.xtra.cash_collected.amt|number_format:2}</td>
							<td nowrap>3530 / 000</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Less: Collections for Plastic Bags</td>
							<td>A2</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr class="xtra_separator">
							<td>Net Cash collection for sales</td>
							<td>A = A1-A2</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Cash Coupon</td>
							<td>B</td>
							<td class="r {if $data.total.payment_type.coupon.amt<0}negative{/if}">{$data.total.payment_type.coupon.act_amt|number_format:2}</td>
							<td>5500 / 007</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Aneka Voucher</td>
							<td>C1</td>
							<td class="r {if $data.total.payment_type.voucher.amt<0}negative{/if}">{$data.total.payment_type.voucher.act_amt|number_format:2}</td>
							<td>4140 / 000</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Hian Shop Voucher</td>
							<td>C2</td>
							<td>&nbsp;</td>
							<td>5500 / 099</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Mix and Match</td>
							<td>C3</td>
							<td class="r {if $data.total.payment_type.$mm_discount_col_value.amt<0}negative{/if}">{if $data.total.payment_type.$mm_discount_col_value.amt}{$data.total.payment_type.$mm_discount_col_value.amt|number_format:2}{else}&nbsp;{/if}</td>
							<td>5800 / 000</td>
							<td>&nbsp;</td>
						</tr>
						<tr class="xtra_separator">
							<td>Total Cash Collections</td>
							<td>D = A1+B+C1+C2+C3</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						{*
						<tr>
							<td>Add: Short Collection *****</td>
							<td>E</td>
							<td class="r {if $data.total.variance.over.amt<0}negative{/if}">{$data.total.variance.short.amt|number_format:2}</td>
							<td>9214 / 001 (-)</td>
							<td>&nbsp;</td>
						</tr>
						*}
						<!-- add -->
						<tr>
							<td>Add: Short Collection (Cash)</td>
							<td>E</td>
							<td class="r">
								{if $data.total.payment_type.cash.variance < 0}
									{$data.total.payment_type.cash.variance*-1|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>9214 / 001 (-)</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Add: Short Collection (Coupon)</td>
							<td>F</td>
							<td class="r">
								{if $data.total.payment_type.coupon.variance < 0}
									{$data.total.payment_type.coupon.variance*-1|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>9214 / 003 (-)</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Add: Short Collection (Aneka Voucher)</td>
							<td>G</td>
							<td class="r">
								{if $data.total.payment_type.voucher.variance < 0}
									{$data.total.payment_type.voucher.variance*-1|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>9214 / 004 (-)</td>
							<td>&nbsp;</td>
						</tr>
						<!-- add -->
						{*
						<tr>
							<td>Less: Over Collection *****</td>
							<td>F</td>
							<td class="r {if $data.total.variance.short.amt<0}negative{/if}">{$data.total.variance.over.amt|number_format:2}</td>
							<td>&nbsp;</td>
							<td>9214 / 001 (+)</td>
						</tr>
						*}
						<!-- add -->
						<tr>
							<td>Less: Over Collection (Cash)</td>
							<td>H</td>
							<td class="r">
								{if $data.total.payment_type.cash.variance > 0}
									{$data.total.payment_type.cash.variance|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>&nbsp;</td>
							<td>9214 / 001 (+)</td>
						</tr>
						<!-- add -->
						<tr>
							<td>Less: Over Collection (Customer Money)</td>
							<td>I</td>
							<td class="r {if $data.total.over.amt<0}negative{/if}">{if $data.total.over.amt}{$data.total.over.amt|number_format:2}{else}&nbsp;{/if}</td>
							<td>&nbsp;</td>
							<td>9214 / 002 (+)</td>
						</tr>
						<!-- add -->
						<tr>
							<td>Less: Over Collection (Coupon)</td>
							<td>J</td>
							<td class="r">
								{if $data.total.payment_type.coupon.variance > 0}
									{$data.total.payment_type.coupon.variance|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>&nbsp;</td>
							<td>9214 / 003 (+)</td>
						</tr>
						<tr>
							<td>Less: Over Collection (Aneka Voucher)</td>
							<td>K</td>
							<td class="r">{if $data.total.payment_type.voucher.variance > 0}{$data.total.payment_type.voucher.variance|number_format:2}{else}&nbsp;{/if}</td>
							<td>&nbsp;</td>
							<td>9214 / 004 (+)</td>
						</tr>
						<!-- add -->
						<tr class="xtra_separator">
							<td>Actual Cash Sales </td>
							<td>L = D+E+F+G-H-I-J-K</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>5250 / 000</td>
						</tr>
						<tr class="xtra_separator">
							<td>Plastic Bags - Charity bodies</td>
							<td>M = A2</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>4100 / C</td>
						</tr>
						<tr class="xtra_separator">
							<td>Credit Card Collection</td>
							<td>N</td>
							<td class="r {if $data.total.payment_type.credit_cards.act_amt<0}negative{/if}">{$data.total.payment_type.credit_cards.act_amt|number_format:2}</td>
							<td>3160 / 000</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Add: Short Collection</td>
							<td>O</td>
							<td class="r">
								{if $data.total.payment_type.credit_cards.variance < 0}
									{$data.total.payment_type.credit_cards.variance*-1|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>&nbsp;</td>
							<td>9214 / 001(+)</td>
						</tr>
						<tr>
							<td>Less: Over Collection</td>
							<td>P</td>
							<td class="r">
								{if $data.total.payment_type.credit_cards.variance > 0}
									{$data.total.payment_type.credit_cards.variance|number_format:2}
								{else}
									&nbsp;
								{/if}
							</td>
							<td>9214 / 001(-)</td>
							<td>&nbsp;</td>
						</tr>
						<tr class="xtra_separator">
							<td>Actual Credit Card Sales</td>
							<td>Q = N+O-P</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>5200 / 000</td>
						</tr>
						<tr>
							<td>Credit Sales (for Jitra use only)</td>
							<td>R</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>5260 / 000</td>
						</tr>
						<tr class="xtra_separator">
							<td>Grand Total Sales BEFORE rounding</td>
							<td>S = L+M+Q+R</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>Rounding : Cash Sales (+/-)</td>
							<td>T</td>
							<td class="r {if $data.xtra.rounding.cash.amt<0}negative{/if}">{$data.xtra.rounding.cash.amt|number_format:2}</td>
							<td>9606 / 000</td>
							<td>5250 / 000</td>
						</tr>
						<tr>
							<td>Rounding : Credit Card Sales (+/-)</td>
							<td>U</td>
							<td class="r {if $data.xtra.rounding.credit_cards.amt<0}negative{/if}">{if $data.xtra.rounding.credit_cards.amt}{$data.xtra.rounding.credit_cards.amt|number_format:2}{else}&nbsp;{/if}</td>
							<td>9607 / 000</td>
							<td>5200 / 000</td>
						</tr>
						<tr class="xtra_separator">
							<td>Grand Total Sales AFTER rounding</td>
							<td>V = S-(T+U)</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</table>
					
					<br />
					<table>
						<tr valign="top">
							<td>
								<b>For Account department use:</b>
								<div class="div_box">
									Document No. :<br />
									Batch/Trans No. :<br /><br /><br /><br /><br />
								</div><br /><br />
								<div class="div_box">
									<h2 align="center">Account Dept</h2>
									<br /><br /><br /><br /><br /><br /><br />
								</div>
								<div class="div_box">
									<h3>Date</h3>
								</div>
							</td>
							<td width="30">&nbsp;</td>
							<td colspan="3">
								<table>
									<tr>
										<td><b>Summary:</b></td>
										<th>RM</th>
									</tr>
									<tr>
										<td>Cash collection amount</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Bank in amount (Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Bank in amount (Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Bank in amount (Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Bank in amount (Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Bank in amount (Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>Different</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td colspan="2"><br /><br />Cash payments (must attach the Petty Cash reimbursement Form):</td>
									</tr>
									<tr>
										<td>1.</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>2.</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
									<tr>
										<td>3.</td>
										<td><div class="user_keyin_box"></div></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<div class="div_box">
									<h2 align="center">Chief Cashier</h2><br /><br /><br /><br /><br />
								</div>
								<div class="div_box">
									<h3>Date</h3>
								</div>
							</td>
							<td>&nbsp;</td>
							<td>
								<div class="div_box" style="min-width:150px;">
									<h2 align="center">MIS</h2><br /><br /><br /><br /><br />
								</div>
								<div class="div_box">
									<h3>Date</h3>
								</div>
							</td>
							<td width="50">&nbsp;</td>
							<td>
								<div class="div_box">
									<h2 align="center">Branch Manager</h2><br /><br /><br /><br /><br />
								</div>
								<div class="div_box">
									<h3>Date</h3>
								</div>
							</td>
						</tr>
					</table>
				</div>
			{/if}
		{/if}
	{/if}
{/if}


{if !$is_print}
	{include file='footer.tpl'}
{/if}
