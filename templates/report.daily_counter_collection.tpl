{*
2/10/2011 10:11:29 AM Alex
- create by me

11/24/2011 4:56:49 PM Alex
- add column variance

4/27/2012 3:18:40 PM Andy
- Change column name "Nett Sales" to "Total Collection".

7/23/2012 4:40:34 PM Justin
- Enhanced for branch filter that can select "All" and when it is on "All", counter and split by counter filters is hidden.
- Enhanced report that show by branch while branch filter is "All" and on daily basis mode.

8/3/2012 2:34:34 PM Justin
- Bug fixed system that cannot show counter list and split by counter checkbox when login as subbranch.

11/2/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

3/21/2014 3:57 PM Justin
- Enhanced to show custom payment type label if found it is set.
- Modified the wording from "Finalize" to "Finalise".

4/26/2017 8:12 AM Khausalya
- Enhanced changes from RM to use config setting. 

6/25/2018 11:49 AM Justin
- Enhanced to load foreign currency from config instead of pos settings.
- Enhanced to sum up the total using php array instead of smarty.

6/12/2019 10:18 PM William
- Added new column Nett Sales.

06/30/2020 04:43 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.bold{
	font-weight: bold;
}
.weekend{
	color:red;
}
.col_foreign_curr{
    background: #ffc;
}
.col_variance{
    background: #F0FF00 !important;	
}


</style>
{/literal}

<script>
{literal}
function view_type_check(){
	if($('date_from').value > $('date_to').value){
		alert('Date Start cannot be late than Date End');
		return false;
	}
}

function get_counter_name(val){
	var branch_id=val;
	
	if(val == "all"){
		$('span_counter').hide();
		$('span_split_counter').hide();
		return;
	}else{
		$('span_counter').show();
		$('span_split_counter').show();
	}
	
	$('counter_id').update(_loading_);
	
	new Ajax.Updater('counter_id', 'report.daily_counter_collection.php',
		{
	    method: 'post',
	    parameters:{
			a: 'get_counter_name',
			branch_id: branch_id
		}
	});
}

{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">

{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id" id="branch_id" onchange='get_counter_name(this.value)'>
		<option value='all' {if $smarty.request.branch_id eq 'all'}selected{/if}>- All -</option>
	    {foreach from=$branches item=b}
	        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{else}
	<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}">
{/if}

<span id="span_counter" {if (!$smarty.request.branch_id || $smarty.request.branch_id eq 'all') && $BRANCH_CODE eq 'HQ'}style="display:none;"{/if}>
	<b>Counter</b>
	<span id="counter_id">
	<select name="counter_id" >
		{if !$counters}
			<option value=''>No Data</option>
		{else}
			{foreach name=counter_total from=$counters item=c}
			{/foreach}

			{if $smarty.foreach.counter_total.total >1 }
				<option value='all'>- All -</option>
			{/if}
			{foreach from=$counters item=c}
				<option value="{$c.counter_id}" {if $smarty.request.counter_id eq $c.counter_id}selected {/if}>{$c.network_name}</option>
			{/foreach}
		{/if}
	</select>
	</span>&nbsp;&nbsp;&nbsp;&nbsp;
</span>

<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
<span id="span_split_counter" {if (!$smarty.request.branch_id || $smarty.request.branch_id eq 'all') && $BRANCH_CODE eq 'HQ'}style="display:none;"{/if}>
	<input id="split_counter_id" name="split_counter" type="checkbox" value=1 {if $smarty.request.split_counter}checked {/if}> <label for="split_counter_id"><b>Split by counter</b></label>
</span>

<p>
	<input type="hidden" name="submit" value="1" />
	<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
	{/if}
</p>
</form>
{/if}

{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}

<b>Note:</b> *Nett Sales and Variance of sales only showed when current counter collection is finalised<br />
{foreach from=$data key=counter_id item=d}
	{assign var=total_all_foreign value=0}
	{assign var=total_all_amount value=0}
	{assign var=total_variance value=0}
	{assign var=total_nett_sales value=0}
	{if $counter_id}<h2>Counters: {$counters.$counter_id.network_name}</h2>{/if}
	<table class=report_table width=100%>
		<tr class="header">
			<th>
				{if $smarty.request.branch_id eq 'all' && $smarty.request.date_from eq $smarty.request.date_to}
					Branch
				{else}
					Date
				{/if}
			</th>
			<!-- Normal Payment Method -->
			{foreach name=dum from=$normal_payment_type item=payment_type}
				<th>{$pos_config.payment_type_label.$payment_type|default:$payment_type}</th>
			{/foreach}
			
			<!-- Foreign Currency -->
			{if $foreign_currency_list}
				{foreach name=curr from=$foreign_currency_list key=currency_type item=currency_info}
					<th>{$currency_type}</th>
				{/foreach}
				<!--th>Total ({$config.arms_currency.symbol})</th-->
			{/if}
			<th>Total Collection</th>
			<th>Nett Sales</th>
			<th class="col_variance">Variance</th>
			<th>
				{if $smarty.request.branch_id eq 'all' && $smarty.request.date_from ne $smarty.request.date_to}
					Unfinalised
				{else}
					Finalised
				{/if}
			</th>
		</tr>

		<col span='{$smarty.foreach.dum.total+1}'>
		<col span='{$smarty.foreach.curr.total+1}' class='col_foreign_curr'>

		{foreach from=$d key=type item=r}
			<tr>
				<th>{$type}</th>

				<!-- Normal Payment Method -->
				{foreach from=$normal_payment_type item=payment_type}
					<td class="r {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
						{$r.cash_domination.$payment_type.amt|number_format:2}
						{if $r.cash_domination.$payment_type.amt<>$r.cash_domination.$payment_type.o_amt}
							<br />
							<span class="small" style="color:black;">{$r.cash_domination.$payment_type.o_amt|number_format:2}</span>
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
				{if $foreign_currency_list}
					{foreach name=curr from=$foreign_currency_list key=currency_type item=currency_info}
						{assign var=payment_type value=$currency_type}
						<td class="r {if $r.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} ">
							{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
							{*<br />
							<span class="small_rm_amt" style="color:black;">{$config.arms_currency.symbol} {$r.cash_domination.foreign_currency.$payment_type.rm_amt|number_format:2}</span>*}

							<!-- Currency Float -->
							<br />
							<span class="small" style="color:grey;">
							C:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
							/ F:{$r.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
							</span>
						</td>
					{/foreach}
					<!--td class='r {if $fc_amt<0}negative{/if}' >{$fc_amt|number_format:2}</td-->
				{/if}
				
				<td class="r {if $r.cash_domination.nett_sales.amt<0}negative{/if}" nowrap>
					{if $foreign_currency_list}<span style="float:left;">{$config.arms_currency.symbol}</span>&nbsp;{/if}
					<span style="float:left;">{$r.cash_domination.nett_sales.amt|number_format:2}</span>
					{if $foreign_currency_list}
						<br />
						{foreach name=curr from=$foreign_currency_list key=currency_type item=currency_info name=fc}
							{assign var=payment_type value=$currency_type}
							{if $r.cash_domination.foreign_currency.$payment_type.foreign_amt ne 0}
								<span style="float:left;">{$payment_type}</span>&nbsp;
								<span style="float:left;">{$r.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}</span>
								{if !$smarty.foreach.fc.last}<br />{/if}
							{/if}
						{/foreach}
					{/if}
				</td> 
				<td class="r {if $r.nett_sales.amt<0}negative{/if}"><span style="float:left;">{$r.nett_sales.amt|number_format:2}</span></td>
				<td class="r col_variance {if $r.variance.amt<0}negative{/if}">{$r.variance.amt|number_format:2}</td>
				{assign var=total_variance value=$total_variance+$r.variance.amt}
				{assign var=total_nett_sales value=$total_nett_sales+$r.nett_sales.amt}
				{if $smarty.request.branch_id eq 'all' && $smarty.request.date_from ne $smarty.request.date_to}
					<td align="center" class="{if count($finalized.$bid.$type) eq count($branch_list)}positive{else}negative{/if}">
						{assign var=have_unfinalized value=0}
						{assign var=bcount value=0}
						
						{assign var=is_first_print value=1}
						{foreach from=$branch_list key=bid item=b}
							{assign var=bcount value=$bcount+1}
							{if !$finalized.$bid.$type}
								{if !$is_first_print}, {/if}
								{$b.branch_code}
								{assign var=is_first_print value=0} 
								{assign var=have_unfinalized value=1}
							{/if}
						{/foreach}
						{if !$have_unfinalized}No{/if}
					</td>
				{else}
					{if $finalized.$type}
						{assign var=fin value=1}
					{else}
						{assign var=fin value=0}
					{/if}
					<td align="center" class="{if $fin}positive{else}negative{/if}">{if $fin}Yes{else}No{/if}</td>				
				{/if}
			</tr>
		{/foreach}

		<!-- Total each counter -->
		<tr class='header'>
			<th>Total ({$config.arms_currency.symbol})</th>
			<!-- Normal Payment Method -->
			{foreach from=$normal_payment_type item=payment_type}
				<td class="r bold {if $r.cash_domination.$payment_type.amt<0}negative{/if}">
					{$total.$counter_id.cash_domination.$payment_type.amt|number_format:2}

					{if $total.$counter_id.cash_domination.$payment_type.amt<>$total.$counter_id.cash_domination.$payment_type.o_amt}
						<br />
						<span class="small" style="color:black;">{$total.$counter_id.cash_domination.$payment_type.o_amt|number_format:2}</span>
					{/if}
					<br />
					{if $payment_type eq 'Cash'}
						<span class="small" style="color:grey;">
						C:{$total.$counter_id.cash_domination.$payment_type.Float.amt+$total.$counter_id.cash_domination.$payment_type.amt|number_format:2}
						/ F:{$total.$counter_id.cash_domination.$payment_type.Float.amt|number_format:2}
						</span>
					{/if}
				</td>
			{/foreach}

			<!-- Foreign Currency -->
			{if $foreign_currency_list}
				{foreach name=curr from=$foreign_currency_list key=currency_type item=currency_info}
					{assign var=payment_type value=$currency_type}

					<td class="r bold {if $total.$counter_id.cash_domination.foreign_currency.$payment_type.foreign_amt<0}negative{/if} ">
						{$total.$counter_id.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}<!--br />
						<span class="small_rm_amt">{$config.arms_currency.symbol} {$total.$counter_id.cash_domination.foreign_currency.$payment_type.amt|number_format:2}</span-->

						<!-- Currency Float -->
						<br />
						<span class="bold small" style="color:grey;">
						C:{$total.$counter_id.cash_domination.foreign_currency.$payment_type.Float.foreign_amt+$total.$counter_id.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}
						/ F:{$total.$counter_id.cash_domination.foreign_currency.$payment_type.Float.foreign_amt|number_format:2}
						</span>
					</td>
				{/foreach}
				<!--td class='r bold {if $total_all_foreign<0}negative{/if}'>{$total_all_foreign|number_format:2}</td-->
			{/if}

			<td class='r bold {if $total.$counter_id.cash_domination.amt<0}negative{/if}' nowrap>
				{if $foreign_currency_list}<span style="float:left;">{$config.arms_currency.symbol}</span>&nbsp;{/if}
				<span style="float:left;">{$total.$counter_id.cash_domination.nett_sales.amt|number_format:2}</span>
				{if $foreign_currency_list}
					<br />
					{foreach name=curr from=$foreign_currency_list key=currency_type item=currency_info name=fc}
						{assign var=payment_type value=$currency_type}
						{if $total.$counter_id.cash_domination.foreign_currency.$payment_type.foreign_amt ne 0}
							<span style="float:left;">{$payment_type}</span>&nbsp;
							<span style="float:left;">{$total.$counter_id.cash_domination.foreign_currency.$payment_type.foreign_amt|number_format:2}</span>
							{if !$smarty.foreach.fc.last}<br />{/if}
						{/if}
					{/foreach}
				{/if}
			</td>
			<td class='r bold col_variance {if $total_all_amount<0}negative{/if}'><span style="float:left;">{$total_nett_sales|number_format:2}</span></td>
			<td class='r bold col_variance {if $total_all_amount<0}negative{/if}'>{$total_variance|number_format:2}</td>
		</tr>
	</table>
{/foreach}
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">

Calendar.setup({
    inputField     :    "date_from",     // id of the input field
    ifFormat       :    "%Y-%m-%d",      // format of the input field
    button         :    "t_added1",  // trigger for the calendar (button ID)
    align          :    "Bl",           // alignment (defaults to "Bl")
    singleClick    :    true
});

Calendar.setup({
    inputField     :    "date_to",     // id of the input field
    ifFormat       :    "%Y-%m-%d",      // format of the input field
    button         :    "t_added2",  // trigger for the calendar (button ID)
    align          :    "Bl",           // alignment (defaults to "Bl")
    singleClick    :    true
});
</script>
{/literal}
{/if}

{include file=footer.tpl}
