{*
8/2/2012 2:40 PM Andy
- Add cost and gp.

9/6/2012 4:17 PM Andy
- Add to support email format.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

{include file="header.tpl"}

{if $is_email}
<style>
{literal}
.report_table th, .report_table td{
	border: 1px solid black;
	border-collapse: collapse;
}
.r{
	text-align: right;
}
{/literal}
</style>
{/if}

{if !$no_header_footer}
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
.negative{
	font-weight: bold;
	color: red;
}
{/literal}
</style>

<script type="text/javascript">

{literal}

var SALES_SUMMARY_BY_DAY = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		Calendar.setup({
	        inputField     :    "inp_date_from",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_from",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	
	    Calendar.setup({
	        inputField     :    "inp_date_to",     // id of the input field
	        ifFormat       :    "%Y-%m-%d",      // format of the input field
	        button         :    "img_date_to",  // trigger for the calendar (button ID)
	        align          :    "Bl",           // alignment (defaults to "Bl")
	        singleClick    :    true
			//,
	        //onUpdate       :    load_data
	    });
	},
	// function to validate form before submit
	check_form: function(){
		var dt1 = strtotime(this.f['date_from'].value);
		var dt2 = strtotime(this.f['date_to'].value);
		
		if(dt2 < dt1){
			alert('Date to cannot early then date from.');
			return false;
		}
		
		var diff = day_diff(this.f['date_from'].value, this.f['date_to'].value);
		if(diff>90){
			alert('Report maximum can view sales last 90 days.');
			return false;
		}
		
		return true;
	},
	// function when user click show report
	submit_form: function(type){
		if(!this.check_form())	return false;
		
		this.f['submit_type'].value = '';
		if(type == 'excel')	this.f['submit_type'].value = type;
		
		this.f.submit();
	},
	// function when user click print
	print_form: function(){
		window.print();
	}
}
{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if !$no_header_footer}
<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="submit_type" value="" />
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> 
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Show Report" onClick="SALES_SUMMARY_BY_DAY.submit_form();" />
	<input type="button" value="Print" onClick="SALES_SUMMARY_BY_DAY.print_form();" />
	<button onClick="SALES_SUMMARY_BY_DAY.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	
	<br />
	<ul>
		<li> Report maximum can view sales last 90 days.</li>
		<li> This report only show finalised sales.</li>
	</ul>	
</form>

<script type="text/javascript">
	SALES_SUMMARY_BY_DAY.initialize();
</script>
{/if}

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
	 * No Data
	{else}
		<h3>{$report_title}</h3>
		
		<table class="report_table" cellpadding="0" cellspacing="0" {if $is_email}border="1"{/if}>
			<tr class="header">
				<th>Date</th>
				<th>Amount</th>
				<th>Cost</th>
				<th>GP</th>
			</tr>
			
			{foreach from=$data.data key=date item=r}
				<tr>
					<td>{$date}</td>
					<td class="r {if $r.amt<0}negative{/if}">{$r.amt|number_format:2}</td>
					
					<!-- cost -->
					<td class="r {if $r.cost<0}negative{/if}">{$r.cost|number_format:$config.global_cost_decimal_points}</td>
					
					<!-- GP -->
					{assign var=gp value=$r.amt-$r.cost}
					<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
				</tr>
			{/foreach}
			
			<tr class="header">
				<td><b>Total</b></td>
				<td class="r {if $data.total.amt<0}negative{/if}">{$data.total.amt|number_format:2}</td>
				
				<!-- cost -->
				<td class="r {if $data.total.cost<0}negative{/if}">{$data.total.cost|number_format:$config.global_cost_decimal_points}</td>
				
				<!-- GP -->
				{assign var=gp value=$data.total.amt-$data.total.cost}
				<td class="r {if $gp<0}negative{/if}">{$gp|number_format:$config.global_cost_decimal_points}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
