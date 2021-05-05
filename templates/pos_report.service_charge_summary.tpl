{*
06/30/2020 04:43 PM Sheila
- Updated button css.

10/13/2020 2:16 PM William
- Enhanced to change GST word to Tax.
*}

{include file=header.tpl}
{if !$no_header_footer}
	<!-- calendar stylesheet -->
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
	{literal}
	<style>
	option.bg{
		font-weight:bold;
		padding-left:10px;
	}

	option.bg_item{
		padding-left:20px;
	}
	</style>
	{/literal}
	<!-- main calendar program -->
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<!-- language for the calendar -->
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	<!-- the following script defines the Calendar.setup helper function, which makes
	   adding a calendar a matter of 1 or 2 lines of code. -->
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	<script src="js/jquery-1.7.2.min.js"></script>
	<script>
	var phpself = '{$smarty.server.PHP_SELF}';
	{literal}
	var JQ = {};
	JQ = jQuery.noConflict(true);

	function show_loading() {
		JQ('#result-table').html('<img src=/ui/clock.gif align=absmiddle> Loading...');
		return true;
	}
	{/literal}
	</script>
{/if}

<h1>{$PAGE_TITLE}</h1>
{if $err}
	The following error(s) has occured:
	<ul class=err>
		{foreach from=$err item=e}
			<li>{$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<form method=post class=form name="f_a">
    <input type=hidden name=submit value=1>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b> 
		<select name="branch_id">
			{foreach from=$branches key=bid item=b}
				<option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
			{/foreach}
		</select>
		<br/>
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}"/>
	{/if}
	
	<b>Group By</b>
	<select name="group_by">
		<option value="daily" {if $form.group_by eq 'daily'}selected {/if}>Daily</option>
		<option value="monthly" {if $form.group_by eq 'monthly'}selected {/if}>Monthly</option>
	</select>
	<br/>
	
	<b>From</b>
	<input size=10 type=text name=date_from value="{$form.date_from}" id="date_from">
	<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;&nbsp;&nbsp;
	<b>To</b> <input size=10 type=text name=date_to value="{$form.date_to}" id="date_to">
	<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;&nbsp;&nbsp;
	<br/>
	<p>
		<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
		{/if}
	</p>
</form>
{/if}

{if !$table}
	{if $smarty.request.submit && !$err}<div id="result-table"><p align=center>--No Data--</p>{/if}
{else}
	{if !$no_header_footer}
		<div id="result-table">
	{else}
		<h1>{$report_title}</h1>
	{/if}
		
	<table width=100% cellpadding=4 cellspacing=1 border=0 style="padding:1px;border:1px solid #000" id="tbl_do">
		<tr bgcolor="#ffee99">
			<th>Date/Month</th>
			<th>Total Sales (Exclude Tax)</th>
			<th>Total Service Charge</th>
			<th>Tax On Service Charge</th>
			<th>Service Charge Included Tax</th>
		</tr>
		{foreach from=$table item=value}
			<tr bgcolor='{cycle values="#ffffff,#eeeeee"}'>
				<td style="text-align: center;">{$value.date}</td>
				<td style="text-align: right;">{$value.total_sales|number_format:2}</td>
				<td style="text-align: right;">{$value.total_sc|number_format:2}</td>
				<td style="text-align: right;">{$value.gst_on_sc|number_format:2}</td>
				<td style="text-align: right;">{$value.sc_included_gst|number_format:2}</td>
			</tr>
		{/foreach}
	</table>
	{if !$no_header_footer}
		</div>
	{/if}
{/if}

{include file=footer.tpl}
{literal}
<script type="text/javascript">
  Calendar.setup({
      inputField     :    "date_from",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "t_added1",  // trigger for the calendar (button ID)
      align          :    "Bl",           // alignment (defaults to "Bl")
      singleClick    :    true,
      onClose        :    function(cal){
        JQ('#data-form').find('select').removeAttr('style');
        cal.hide();
      }
  });

  Calendar.setup({
      inputField     :    "date_to",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "t_added2",  // trigger for the calendar (button ID)
      align          :    "Bl",           // alignment (defaults to "Bl")
      singleClick    :    true,
      onClose        :    function(cal){
        JQ('#data-form').find('select').removeAttr('style');
        cal.hide();
      }
  });
</script>
{/literal}
