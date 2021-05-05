{*
9/12/2018 11:29 AM Andy
- Rewrite to use class Module.
- Enhanced to get points from membership_points
*}

{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<script>

MEMBER_EXPORT_POINTS = {
	initialise: function(){
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
	submit_form: function(){
		var date_filter_type = getRadioValue(document.f_a['date_filter_type']);
		
		if(date_filter_type == 2){
			var d = document.f_a['date'].value;
			if(!d){
				alert("Please select a date.");
				return false;
			}
		}
		
		document.f_a.submit();
	}
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
	{foreach from=$err item=e}
	<li> {$e}
	{/foreach}
	</ul>
{/if}
<form method="post" name="f_a" onSubmit="return false;">
	<input type="hidden" name="export_point" value="1" />

	<table>
		{if BRANCH_CODE eq 'HQ'}
			<tr>
				<td width="100"><b>Branch: </b></td>
				<td>
					<select name="branch_id" />
						<option value="0">-- All --</option>
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		{/if}
		
		<tr>
			<td valign="top"><b>Select Date</b></td>
			<td>
				<p>
					<input type="radio" name="date_filter_type" value="1" {if !$smarty.request.date_filter_type or $smarty.request.date_filter_type eq 1}checked {/if} /> All</p>
				<p>
					<input type="radio" name="date_filter_type" value="2" {if $smarty.request.date_filter_type eq 2}checked {/if}/> Single Day
					<select name="date">
						<option value="">-- Please Select --</option>
						{foreach from=$date_list item=d}
							<option value="{$d}" {if $smarty.request.date eq $d}selected {/if}>{$d}</option>
						{/foreach}
					</select>
				</p>
				<p>
					<input type="radio" name="date_filter_type" value="3" {if $smarty.request.date_filter_type eq 3}checked {/if}/>
					From
					<input type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly size="12" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
					To
					<input type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly size="12" />
					<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
				</p>
			</td>
		</tr>
		<tr>
			<td valign="top"><b>Other Settings</b></td>
			<td>
				<input type='checkbox' name='show_branch' value='1' {if !$smarty.request.export_point or $smarty.request.show_branch}checked {/if} /> Show Branch&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='checkbox' name='show_date' value='1' {if !$smarty.request.export_point or $smarty.request.show_date}checked {/if} /> Show Date&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="button" value="Export" onClick="MEMBER_EXPORT_POINTS.submit_form();" />
			</td>
		</tr>
	</table>
</form>

{if $msg}{$msg}{/if}

<script>MEMBER_EXPORT_POINTS.initialise();</script>
{include file='footer.tpl'}