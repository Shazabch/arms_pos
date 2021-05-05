{*
2/7/2012 10:54:41 AM Andy
- Reconstruct CC Trans output format.

2/29/2012 2:31:13 PM Andy
- Add Download Format 2 for GL Journal Entry

4/4/2012 10:39:11 AM Andy
- Add new export format 3 (Customer Invoice).
- Show format 1 as Sales Invoice.
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
<style>
.not_in_csv{
	background-color:#fcf;
}
</style>
{/literal}

<script>
{literal}
function init_calendar(){
	Calendar.setup({
        inputField     :    "inp_date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_date",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" method="post" class="stdframe">
	<input type="hidden" name="load_summary" value="1" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch: </b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=r}
				{if !$branches_group.have_group.$bid}
					<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
				{/if}
			{/foreach}
			{if $branches_group.header}
				{foreach from=$branches_group.header key=bgid item=bg}
					<optgroup label='{$bg.code}'>
			    	    {foreach from=$branches_group.items.$bgid item=r}
			    	        <option value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
			    	    {/foreach}
		    	    </optgroup>
		    	{/foreach}
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<b>Sales Date: </b>
	<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" /> &nbsp;
	&nbsp;&nbsp;&nbsp;
	
	<input type="checkbox" name="split_cc_type" value="1" {if !$smarty.request.load_summary || $smarty.request.split_cc_type}checked {/if} /> Split by Credit Cards Type
	<p>
		<input type="submit" value="View Summary" />
	</p>
</form>

<script>
{literal}
init_calendar();
{/literal}
</script>

<br />
{if $smarty.request.load_summary and !$err}
	{if !$data}
		-- No Data --
	{else}
		<h2>{$report_title}</h2>
		<ul style="list-style:none;">
			<li> <span class="not_in_csv">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Column not included in download file.</li>
			<li> Format 1 (Sales Invoice):
				<a href="?a=download_export_file&format=txt1{if BRANCH_CODE eq 'HQ'}&branch_id={$smarty.request.branch_id}{/if}&date={$smarty.request.date}&split_cc_type={$smarty.request.split_cc_type}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download TXT
				</a>
			</li>
			<li>
				Format 2 (GL Journal Entry):
				<a href="?a=download_export_file&format=txt2{if BRANCH_CODE eq 'HQ'}&branch_id={$smarty.request.branch_id}{/if}&date={$smarty.request.date}&split_cc_type={$smarty.request.split_cc_type}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download TXT
				</a>
			</li>
			<li>
				Format 3 (Customer Invoice):
				<a href="?a=download_export_file&format=txt3{if BRANCH_CODE eq 'HQ'}&branch_id={$smarty.request.branch_id}{/if}&date={$smarty.request.date}&split_cc_type={$smarty.request.split_cc_type}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download TXT
				</a>
			</li>
		</ul>
		<table width="100%" class="report_table">
			<thead>
				<tr class="header">
					<th>&nbsp;</th>
					<th>Account Code</th>
					<th>DocNo.</th>
					<th>Date</th>
					<th>Amount</th>
					<th>Type</th>
				</tr>
			</thead>
			{foreach from=$data.items item=r name=f}
				<tr>
					<td class="not_in_csv">{$smarty.foreach.f.iteration}</td>
					<td>{$r.acc_code}</td>
					<td>{$r.docno}</td>
					<td>{$r.date}</td>
					<td class="r">{$r.amt|number_format:2}</td>
					<td>{$r.payment_type}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
{/if}

{include file='footer.tpl'}