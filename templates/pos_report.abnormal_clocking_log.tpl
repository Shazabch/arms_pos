{*
2017-08-28 15:24 PM Qiu Ying
- Enhanced to add new POS report "Abnormal Clocking Report"

10/24/2017 5:52 PM Andy
- Enhanced to display more details error information.
*}

{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
{literal}
var ABNORMAL_CLOCKING_LOG = {
	f: undefined,
	initialize: function(){
		this.f = document.myForm;
		
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
	},
	do_print: function (){
		window.print();
	},
	show_report: function(){
		this.f.submit();
	}
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>
<form method="post" name="myForm" class="form" onSubmit="return false();">
<input type="hidden" name="a" value="load_table" />
<table cellspacing="5" cellpadding="4" border="0">
	<tr>
		<td><b>Counter</b></td>
		<td colspan="3">
			<select name="counters">
				{*<option value="all">-- All --</option>*}
				{foreach from=$counters item=r}
					{capture assign=counter_all}{$r.branch_id}|all{/capture}
					{capture assign=counter_item}{$r.branch_id}|{$r.id}{/capture}
					{if $last_bid ne $r.branch_id}
						<option value="{$counter_all}" {if $form.counters eq $counter_all}selected {/if}>{$r.code}</option>
						{assign var=last_bid value=$r.branch_id}
					{/if}
					<option value="{$counter_item}" {if $form.counters eq $counter_item}selected {/if}>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$r.network_name}
					</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Error Type</b></td>
		<td colspan="3">
			<select name="error_type">
				<option value="" {if !$form.error_type}selected{/if}>All</option>
				{foreach from=$error_list key=k item=v}
					<option value="{$k}" {if $form.error_type eq $k}selected{/if}>{$v}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td><b>From</b></td>
		<td>
			<input size=10 type=text name=date_from value="{$form.date_from}" id="date_from">
			<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
		</td>
		<td><b>To</b></td>
		<td>
			<input size=10 type=text name=date_to value="{$form.date_to}" id="date_to">
			<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		</td>
	</tr>
	<tr>
		<td><b>Sort By</b></td>
		<td colspan="3">
			<select name="sort_by">
				<option value="by_counter" {if $form.sort_by eq 'by_counter'}selected{/if}>Counter</option>
				<option value="by_datetime"{if $form.sort_by eq 'by_datetime'}selected{/if}>Counter Date</option>
			</select>
		</td>
	</tr>
</table>

<input type="button" value="{#SHOW_REPORT#}" onClick="ABNORMAL_CLOCKING_LOG.show_report();" />
</form>

{literal}
<script type="text/javascript">
    ABNORMAL_CLOCKING_LOG.initialize();
</script>
{/literal}
<div id="report_details">
{if $form.a eq 'load_table'}
	{if !$table}
		No data
	{else}
		<h3>Branch: {$form.branch_code}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Counter: {$form.counter}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Error Type: {if $form.error_type eq 'all'}All{elseif $form.error_type eq 'from_server'}Time From Server{elseif $form.error_type eq 'from_sync_server'}Time From Sync Server{else} Manually Enter By User{/if}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: {$form.date_from} to {$form.date_to}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort By: {if $form.sort_by eq 'by_counter'}Counter{else}Counter Date{/if}</h3>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>Counter</th>
				<th>Cashier Entered / Confirmed Date Time</th>
				<th>Cashier</th>
				<th>Error Information</th>
			</tr>
			{foreach from=$table key=bid item=p}
				<tr>
					<td>{$p.network_name}</td>
					<td>{$p.counter_date}</td>
					<td>{$p.u}</td>
					<td>
						{if $p.from_server eq '2'}
							{$error_list.from_user}
							{if $p.more_info}
								<br />BIOS Time: {$p.more_info.date_from_bios}
								{if $p.more_info.server_date}
									<br />{if $r.adjust_type eq '0'}Sync {/if}Server Time: {$p.more_info.date_from_bios}
								{/if}
							{/if}
						{elseif $p.from_server eq '1'}
							{$error_list.from_server}
						{else}
							{$error_list.from_sync_server}
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	{/if}
{/if}
</div>
{include file=footer.tpl}
