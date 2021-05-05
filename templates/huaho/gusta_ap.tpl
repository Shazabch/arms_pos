{*
7/23/2018 10:16 AM Andy
- Hua Ho Gusta Accounting AP Format.
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

<script>
{literal}
var GUSTA_AP = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		this.init_calendar();
	},
	init_calendar: function(){
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
	// function to validate all form data is ok
	validate_form: function(){
		if(!this.f['date_from'].value){
			alert('Please Select Date From');
			this.f['date_from'].focus();
			return false;
		}
		
		if(!this.f['date_to'].value){
			alert('Please Select Date To');
			this.f['date_to'].focus();
			return false;
		}
		
		if(strtotime(this.f['date_from'].value) > strtotime(this.f['date_to'].value)){
			alert('Date From cannot over Date To');
			return false;
		}
		
		return true;
	},
	// function when user click export
	export_clicked: function(){
		if(!this.validate_form())	return;
		
		this.f['export_file'].value = 1;
		this.f.submit();
	},
	
	
}

{/literal}
</script>



<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	<ul>
{/if}

<form name="f_a" onSubmit="return false;" method="post" class="stdframe">
	<input type="hidden" name="export_file" value="0" />
	
	<table>
		{* Branch *}
		<tr>
			<td width="100"><b>Branch: </b></td>
			<td>
				{if $BRANCH_CODE eq 'HQ'}
					<select name="branch_id">
						{foreach from=$branches key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				{else}
					{$BRANCH_CODE}
				{/if}
			</td>
		</tr>
		
		{* Date *}
		<tr>
			<td><b>GRR Date: </b></td>
			<td>
				<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" /> &nbsp;
				To
				<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" /> &nbsp;
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="button" value="Export" onClick="GUSTA_AP.export_clicked();" />
			</td>
		</tr>
	</table>
</form><br />

{if $smarty.request.export_file and !$err}
	{if !$data}
		* No Data *
	{else}
		<h2>File Export Successfully</h2>
		Download: <a href="{$data.url_path}">{$data.filename}</a>
	{/if}
{/if}

<script>
	GUSTA_AP.initialise();
</script>

{include file='footer.tpl'}