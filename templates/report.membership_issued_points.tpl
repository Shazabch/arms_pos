{*
1/11/2017 5:37 PM Andy
- Fixed issue type bug.

06/29/2020 04:26 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

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
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left: 50px;
}
</style>
{/literal}

<script>
{literal}
var REPORT = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
		// setup calendar
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
	// function when user submit report
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		this.f.submit();
	},
	// function when user tick or untick points issued type all
	toggle_issued_type_all: function(){
		var c = $('chx_issued_type_all').checked;
		$$('#span_issued_type input.chx_issued_type').each(function(ele){
			ele.checked = c;
		});
	}
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
	<form method="post" class="form" name="f_a" onSubmit="return false;">
		<input type="hidden" name="export_excel" />
		<input type="hidden" name="show_report" value="1" />
		
		{if $BRANCH_CODE eq 'HQ'}
			<span>
			<b>Branch</b>
				<select name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches key=bid item=r}
						{assign var=skip_this_branch value=0}
						{if $config.sales_report_branches_exclude}
							{if in_array($r.code,$config.sales_report_branches_exclude)}
								{assign var=skip_this_branch value=1}
							{else}
								{assign var=skip_this_branch value=0}
							{/if}
						{/if}
					
						{if !$branch_group.have_group.$bid and !$skip_this_branch}
							<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
					{/foreach}
					{if $branch_group.header}
						{capture assign=bg_item_padding}{section loop=5 name=i}&nbsp;{/section}{/capture}
						<optgroup label='Branch Group'>
						{foreach from=$branch_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
								{foreach from=$branch_group.items.$bgid item=r}
									{assign var=skip_this_branch value=0}
									{if $config.sales_report_branches_exclude}
										{if in_array($r.code,$config.sales_report_branches_exclude)}
											{assign var=skip_this_branch value=1}
										{else}
											{assign var=skip_this_branch value=0}
										{/if}
									{/if}
								
									{if !$skip_this_branch}
									<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$bg_item_padding}{$r.code} - {$r.description}</option>
									{/if}
									
								{/foreach}
							{/foreach}
						</optgroup>
					{/if}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
			</span>
		{/if}

		<span>
			<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
			<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
		</span>
		<span>
			<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
			<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
			&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		
		<p>
			<span>
				<b>Group Data by</b>
				<select name="group_type">
					{foreach from=$group_type_list key=k item=v}
						<option value="{$k}" {if $smarty.request.group_type eq $k}selected {/if}>{$v}</option>
					{/foreach}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
			</span>
			<span>
				<input type="checkbox" name="show_by_branch" value="1" {if $smarty.request.show_by_branch}checked {/if} />
				<b>Show by branch</b>
			</span>&nbsp;&nbsp;&nbsp;&nbsp;
		</p>
		
		<p>
			<span id="span_issued_type">
				<b>Points Issued Type:</b>
				<input type="checkbox" value="1" id="chx_issued_type_all" {if !isset($smarty.request.issued_type)}checked {/if} onChange="REPORT.toggle_issued_type_all();" /> All &nbsp;&nbsp;&nbsp;
				{foreach from=$issued_type_list key=k item=v}
					<input type="checkbox" class="chx_issued_type" value="1" name="issued_type[{$k}]" {if $smarty.request.issued_type.$k or !isset($smarty.request.issued_type)}checked {/if} /> {$v} &nbsp;&nbsp;&nbsp;
				{/foreach}
			</span>
		</p>
		
		<p>
			* Maximum 1 year of data.
		</p>
		
		<p>
			<button class="btn btn-primary" onClick="REPORT.submit_report();">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
				<button class="btn btn-primary" onClick="REPORT.submit_report('excel');">{#OUTPUT_EXCEL#}</button>
			{/if}
		</p>

	</form>
{/if}

{if $smarty.request.show_report and !$err}
	<h2>{$report_title}</h2>
	
	{if !$data}
		* No Data
	{else}
		{if $smarty.request.show_by_branch}
			{foreach from=$data.data.by_branch key=bid item=b_info}
				<h2>{$branches.$bid.code}</h2>
				{include file='report.membership_issued_points.table.tpl' report_data=$b_info}
			{/foreach}
		{else}
			{include file='report.membership_issued_points.table.tpl' report_data=$data.data}
		{/if}
	{/if}
{/if}

<script>
	REPORT.initialise();
</script>

{include file='footer.tpl'}