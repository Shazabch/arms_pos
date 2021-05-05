{*
12/28/2016 3:52 PM Andy
- Fixed sometime will sum up multiple time due to one cn got multiple return receipt.

1/9/2017 11:10 AM Qiu Ying
- Enhanced the template layout of GST Credit Note Report

06/30/2020 04:43 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}
{literal}
<style>
</style>
{/literal}
{if !$no_header_footer}
	<!-- calendar stylesheet -->
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
	<!-- main calendar program -->
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<!-- language for the calendar -->
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript">
	
	{literal}
	var GST_CREDIT_NOTE_REPORT = {
		f: undefined,
		initialize: function(){
            this.f = document.f_a;
			Calendar.setup({
				inputField	:	"date_from",		// id of the input field
				ifFormat	:	"%Y-%m-%d",			// format of the input field
				button		:	"img_date_from",	// trigger for the calendar (button ID)
				align		:	"Bl",				// alignment (defaults to "Bl")
				singleClick	:	true
			});

			Calendar.setup({
				inputField	:	"date_to",			// id of the input field
				ifFormat	:	"%Y-%m-%d",			// format of the input field
				button		:	"img_date_to",		// trigger for the calendar (button ID)
				align		:	"Bl",				// alignment (defaults to "Bl")
				singleClick	:	true
			});
		},
        submit_form: function(t){
			this.f['export_excel'].value = 0;            
            if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
            this.f.submit();
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
    <form class="noprint stdframe" name="f_a">
    <input type="hidden" name="a" value="show_report">
    <input type="hidden" name="export_excel" />
    <table>
    <tr>
		{if $BRANCH_CODE eq "HQ"}
			<td><b>Branch:</b></td>
			<td>
				<select name="branch_id">
					<option value="">-- ALL --</option>
					{foreach from=$branches key=k item=i}
						<option value="{$k}" {if $form.branch_id eq $k}selected{/if}>{$i}</option>
					{/foreach}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		{else}
			<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
		{/if}
        <td><b>Date From :</b></td>
        <td>
            <input type="text" name="date_from" id="date_from" size="10" value="{$form.date_from}" readonly/>
            <img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date From" />&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td><b>Date To :</b></td>
        <td>
            <input type="text" name="date_to" id="date_to" size="10" value="{$form.date_to}" readonly/>
            <img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date To" />
        </td>
    </tr>
    <tr>
        <td><label><input type="radio" name="report_type" value="summary"{if $smarty.request.report_type eq 'summary' or $smarty.request.report_type eq ''}checked{/if}>Summary</label></td>
        <td><label><input type="radio" name="report_type" value="detail"{if $smarty.request.report_type eq 'detail'}checked{/if}>Detail</label></td>
    </tr>
    </table>
	<br>
    <button class="btn btn-primary" onClick="GST_CREDIT_NOTE_REPORT.submit_form();">Show Report</button>
	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button class="btn btn-primary" onClick="GST_CREDIT_NOTE_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	<br><br>
	<ul>
		<li> Report maximum show 30 days of transaction.</li>
		<li> This report included unfinalise sales.</li>
	</ul>
    </form>
	<br>
{/if}

{if $data}
	<h3>{$report_title}</h3>
	{foreach from=$data key=b item=d}
		<h3>{$branches.$b}</h3>
		<table class="report_table small_printing" width="100%">
			<thead>
				<tr class="header">
					<th colspan="3">Credit Note</th>
					<th colspan="3">Receipt</th>
					<th colspan="4">Return Receipt</th>
					<th rowspan="2">Amt Before GST</th>
					<th rowspan="2">GST Amt</th>
					<th rowspan="2">Amt Inc GST</th>
				</tr>
				<tr class="header">
					{*Credit Note*}
					<th>Date{if $show_detail}<br>(Arms Code){/if}</th>
					<th>Number{if $show_detail}<br>(MCode){/if}</th>
					<th>Reference No{if $show_detail}<br>(Artno){/if}</th>
					{*Receipt*}
					<th>Counter Name</th>
					<th>Number</th>
					<th>Reference No</th>
					{*Return Receipt*}
					<th>Counter Name</th>
					<th>Number</th>
					<th>Reference No</th>
					<th>Date</th>
				</tr>
			</thead>
			{foreach from=$d item=i}
				<tr {if $show_detail}style="background: #DDEEFF"{/if}>
					<td>{$i.date}</td>
					<td>{$i.credit_note_no}</td>
					<td>{$i.credit_note_ref_no}</td>
					<td>{$i.network_name}</td>
					<td>{if !$no_header_footer}<a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$i.receipt_ref_no}'));">{/if}{$i.receipt_no}</a></td>
					<td>{$i.receipt_ref_no}</td>
					{if !$show_detail}
						{* Return Receipt *}
						<td>
							{foreach from=$i.return_info item=ri}
								{$ri.return_network_name}<br />
							{/foreach}
						</td>
						<td>
							{foreach from=$i.return_info item=ri}
								{$ri.return_receipt_no}<br />
							{/foreach}
						</td>
						<td>
							{foreach from=$i.return_info item=ri}
								{$ri.return_receipt_ref_no}<br />
							{/foreach}
						</td>
						<td>
							{foreach from=$i.return_info item=ri}
								{$ri.return_date}<br />
							{/foreach}
						</td>
					{else}
						<td colspan="4"></td>
					{/if}
					<td align="right">{$i.amt_before_gst|number_format:2}</td>
					<td align="right">{$i.gst_amt|number_format:2}</td>
					<td align="right">{$i.amt_inc_gst|number_format:2}</td>
				</tr>
				{if $show_detail}
					{foreach from=$i.item item=c}
						<tr>
							<td>{$c.sku_item_code}</td>
							<td>{$c.mcode|default:'-'}</td>
							<td>{$c.artno|default:'-'}</td>
							<td colspan="3">{$c.description}</td>
							<td>{$c.return_network_name}</td>
							<td>{$c.return_receipt_no}</td>
							<td>{$c.return_receipt_ref_no}</td>
							<td>{$c.return_date}</td>
							<td align="right">{$c.amt_before_gst|number_format:2}</td>
							<td align="right">{$c.gst_amt|number_format:2}</td>
							<td align="right">{$c.amt_inc_gst|number_format:2}</td>
						</tr>
					{/foreach}
				{/if}
			{/foreach}
			<tr style="background: #fe9; font-weight: bold">
				<td colspan="10" align="right">Total</td>
				<td align="right">{$total.$b.amt_before_gst|number_format:2}</td>
				<td align="right">{$total.$b.gst_amt|number_format:2}</td>
				<td align="right">{$total.$b.amt_inc_gst|number_format:2}</td>
			</tr>
		</table>
	{/foreach}
{else}
    {if $form.form_submit}
    	<ul><li>No data</li></ul>
    {/if}
{/if}
{include file='footer.tpl'}

<script type="text/javascript">
{literal}
GST_CREDIT_NOTE_REPORT.initialize();
{/literal}
</script>