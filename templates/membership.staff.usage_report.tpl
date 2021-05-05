{*
06/29/2020 05:06 PM Sheila
- Updated button css.
*}

{include file="header.tpl"}

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

{/literal}
</style>

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var USAGE_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
	},
	check_form: function(){
		if(!this.f['branch_id'].value){
			alert('Please select branch.');
			return false;
		}
		
		return true;
	},
	// function when user submit form
	submit_form: function(t){
		if(!this.check_form())	return false;
		
		this.f['output_excel'].value = '';
		
		if(t == 'excel')	this.f['output_excel'].value = 1;
		
		this.f.submit();
	}
};
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

<form name="f_a" method="post" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="output_excel" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch: </b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$branches_list key=bid item=b}
				<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<b>Year: </b>
	<select name="year">
		{foreach from=$year_list item=y}
			<option value="{$y}" {if $y eq $smarty.request.year}selected {/if}>{$y}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Month: </b>
	<select name="month">
		{foreach from=$month_list key=m item=m_label}
			<option value="{$m}" {if $m eq $smarty.request.month}selected {/if}>{$m_label}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Staff Type: </b>
	<select name="staff_type">
		<option value="">-- All --</option>
		{foreach from=$config.membership_staff_type key=staff_type item=staff_label}
			<option value="{$staff_type}" {if $staff_type eq $smarty.request.staff_type}selected {/if}>{$staff_label}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input class="btn btn-primary" type="button" value='Show Report' onClick="USAGE_REPORT.submit_form();" /> &nbsp;&nbsp;

	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button class="btn btn-primary" onClick="USAGE_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
</form>
<script type="text/javascript">USAGE_REPORT.initialize();</script>
{/if}

{if $smarty.request.show_report && !$err}
	
	<h2>{$report_title}</h2>
	{if !$data}
		* No Data *
	{else}
		<table width="100%" class="report_table">
			<tr class="header">
				<th>NRIC</th>
				<th>Current Card No</th>
				<th>Member Name</th>
				<th>Staff Type</th>
				{foreach from=$data.date_list item=dt}
					<th>{$dt|date_format:"%d"}</th>
				{/foreach}
				<th>Total</th>
			</tr>
			
			{foreach from=$data.data.by_nric key=nric item=quota_date_list}
				{assign var=member_info value=$data.member_info.$nric}
				<tr>
					<td>{$nric}</td>
					<td>{$member_info.curr_card_no|default:'-'}</td>
					<td>{$member_info.name|default:'-'}</td>
					<td>{$config.membership_staff_type[$member_info.staff_type]|default:'-'}</td>
					
					{foreach from=$data.date_list item=dt}
						<td align="right">{$quota_date_list.$dt.quota_value|number_format:2|ifzero:'&nbsp'}</td>
					{/foreach}
					
					<td align="right">{$data.total.by_nric.$nric.quota_value|number_format:2|ifzero:'&nbsp'}</td>
				</tr>
			{/foreach}
			
			<tr class="header">
				<td align="right" colspan="4"><b>Total</b></td>
				
				{foreach from=$data.date_list item=dt}
					<td align="right">{$data.total.by_date.$dt.quota_value|number_format:2|ifzero:'&nbsp'}</td>
				{/foreach}
				
				<td align="right">{$data.total.total.quota_value|number_format:2|ifzero:'&nbsp'}</td>
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
