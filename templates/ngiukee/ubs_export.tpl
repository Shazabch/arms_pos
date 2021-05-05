{*
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
	<input type="hidden" name="a" value="ubs_export" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch: </b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=r}
				<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<b>Date: </b>
	<input type="text" name="date" value="{$smarty.request.date}" id="inp_date" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" /> &nbsp;&nbsp;
	<input type="submit" value="Download" />
</form>


<script>
{literal}
init_calendar();
{/literal}
</script>

<br />
{*if $smarty.request.load_summary and !$err}
	{if !$data}
		-- No Data --
	{else}
		<h2>{$report_title}</h2>
		
		<ul style="list-style:none;">
			<li> <span class="not_in_csv">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> Column not included in download file.</li>
			<li> Format 1:
				<a href="?a=download_export_file&format=csv1&rf={$data.filename}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download CSV
				</a>
			</li>
			<li>Format 2: 
				<a href="?a=download_export_file&format=txt1_invoice&rf={$data.filename}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download Invoice TXT
				</a>
				&nbsp;|&nbsp;
				<a href="?a=download_export_file&format=txt1_dn&rf={$data.filename}" target="_blank">
					<img src="/ui/icons/table_save.png" align="absmiddle" border="0" /> Download DN TXT
				</a>
			</li>
		</ul>
		<table width="100%" class="report_table">
			<thead>
				<tr class="header">
					<th>&nbsp;</th>
					<th>GRR No</th>
					<th>Vendor Code</th>
					<th>Vendor Name</th>
					<th>Doc No.</th>
					<th>Doc Type</th>
					<th>Doc Date</th>
					<th>P/O No</th>
					<th>Terms</th>
					<th>Description</th>
					<th>Amount</th>
					<th>Posting Account</th>
					<th>Branch Code</th>
				</tr>
			</thead>
			{foreach from=$data.items item=r name=f}
				<tr>
					<td class="not_in_csv">{$smarty.foreach.f.iteration}</td>
					<td class="not_in_csv">GRR{$r.grr_id|string_format:'%05d'}</td>
					<td>{$r.vendorcode}</td>
					<td>{$r.vendor_desc}</td>
					<td>{$r.doc_no}</td>
					<td>{$r.export_type}</td>
					<td>{$r.rcv_date}</td>
					<td>{$r.po_no}</td>
					<td>{$r.term}</td>
					<td>{$r.export_desc}</td>
					<td class="r">{$r.amount|number_format:2}</td>
					<td>{$r.posting_account}</td>
					<td>{$r.bcode}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
{/if*}
{include file='footer.tpl'}