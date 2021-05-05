{*
5/5/2015 3:35 PM Andy
- Enhanced to able to click on receipt number to show transaction details popup.

5/5/2015 4:43 PM Andy
- Enhanced to show non-gst item.

06/30/2020 04:43 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

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
.tr_tax_row{
	background-color: #fcf;
}
{/literal}
</style>

<script>

{literal}

var RS_GST_REPORT = {
	f: undefined,
	initialize: function(){
		var THIS = this;
		this.f = document.f_a;
		
		// setup calendar
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
		// check branch
		/*if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch.');
				return false;
			}
		}*/
		
		// check date
		// date from
		var dt1 = this.f['date_from'].value;
		// date to
		var dt2 = this.f['date_to'].value;
		
		var diff = day_diff(dt1, dt2);
		if(diff>30){
			alert('Report maximum show transaction in 30 days only.');
			return false;
		}
		
		return true;
	},
	// function when user click show report
	show_report: function(t){
		this.f['is_export'].value = 0;
		if(t == 'excel')	this.f['is_export'].value = 1;
		
		if(!this.check_form())	return false;
		
		this.f.submit();
	}
};
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
	<form name="f_a" class="stdframe noprint" onSubmit="return false;" method="post">
		<input type="hidden" name="load_data" value="1" />
		<input type="hidden" name="is_export" />
	
		{if $BRANCH_CODE eq 'HQ'}
			<p>			
				<b>Branch: </b><select name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches key=bid item=bcode}
						<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$bcode}</option>
					{/foreach}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;			
			</p>
		{/if}
		<p>
			<b>From</b> 
			<input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" />
			<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			
			<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" />
			<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
			&nbsp;&nbsp;&nbsp;&nbsp;
		</p>
		
		<p>
			<input class="btn btn-primary" type="button" value="{#SHOW_REPORT#}" onClick="RS_GST_REPORT.show_report();" />
			
			{if $sessioninfo.privilege.EXPORT_EXCEL}
				<button class="btn btn-primary" onClick="RS_GST_REPORT.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
		</p>
		
		<ul>
			<li> Report maximum show 30 days of transaction.</li>
			<li> This report included unfinalise sales.</li>
		</ul>
	</form>
	<script type="text/javascript">
		RS_GST_REPORT.initialize();
	</script>
{/if}

{if $smarty.request.load_data && !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
		<h3>{$report_title}</h3>
		
		<h2>Total By GST : </h2>
		<table class="report_table" width="100%">
			<tr class="header">
				<th>Date</th>
				<th>Receipt No.</th>
				<th>Receipt Ref</th>
				<th>Amt</th>
				<th>GST</th>
				<th>Amt Incl. GST</th>
			</tr>
			{if $data.got_non_gst}
				<tr class="tr_tax_row">
					<td colspan="6">Non GST</td>
				</tr>
				{foreach from=$data.date_list item=d}
					{foreach from=$data.data.gst_list.non_gst.date_list.$d key=receipt_ref_no item=r}
						<tr>
							<td>{$d}</td>
							<td><a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$receipt_ref_no}'));">{$r.receipt_no}</a></td>
							<td>{$r.receipt_ref_no}</td>
							<td align="right">{$r.before_tax_price|number_format:2}</td>
							<td align="right">{$r.tax_amount|number_format:2}</td>
							<td align="right">{$r.amt_included_gst|number_format:2}</td>
						</tr>
					{/foreach}
				{/foreach}
				<tr class="header">
					<th align="right" colspan="3">Total</th>
					<th align="right">{$data.data.gst_list.non_gst.total.before_tax_price|number_format:2}</th>
					<th align="right">{$data.data.gst_list.non_gst.total.tax_amount|number_format:2}</th>
					<th align="right">{$data.data.gst_list.non_gst.total.amt_included_gst|number_format:2}</th>
				</tr>
			{/if}
			
			
			{foreach from=$data.gst_list key=gst_key item=gst_info}
				<tr class="tr_tax_row">
					<td colspan="6">{$gst_info.tax_code} @{$gst_info.tax_rate}%</td>
				</tr>
				{foreach from=$data.date_list item=d}
					{if $data.data.gst_list.$gst_key.date_list.$d}
						{foreach from=$data.data.gst_list.$gst_key.date_list.$d key=receipt_ref_no item=r}
							<tr>
								<td>{$d}</td>
								<td><a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$receipt_ref_no}'));">{$r.receipt_no}</a></td>
								<td>{$r.receipt_ref_no}</td>
								<td align="right">{$r.before_tax_price|number_format:2}</td>
								<td align="right">{$r.tax_amount|number_format:2}</td>
								<td align="right">{$r.amt_included_gst|number_format:2}</td>
							</tr>
						{/foreach}
					{/if}
				{/foreach}
				<tr class="header">
					<th align="right" colspan="3">Total</th>
					<th align="right">{$data.data.gst_list.$gst_key.total.before_tax_price|number_format:2}</th>
					<th align="right">{$data.data.gst_list.$gst_key.total.tax_amount|number_format:2}</th>
					<th align="right">{$data.data.gst_list.$gst_key.total.amt_included_gst|number_format:2}</th>
				</tr>
			{/foreach}
			
		</table>
		
		<br />
		
		<h2>Total By Receipt :</h2>
		<table class="report_table" width="100%">
			<tr class="header">
				<th>Date</th>
				<th>Receipt No.</th>
				<th>Receipt Ref</th>
				<th>Amt</th>
				<th>GST</th>
				<th>Amt Incl. GST</th>
				<th>Rounding</th>
				<th>Total Collected</th>
			</tr>
			{foreach from=$data.date_list item=d}
				{if $data.total.date_list.$d}
					{foreach from=$data.total.date_list.$d key=receipt_ref_no item=r}
						<tr>
							<td>{$d}</td>
							<td><a href="javascript:void(GLOBAL_MODULE.show_trans_detail('{$receipt_ref_no}'));">{$r.receipt_no}</a></td>
							<td>{$receipt_ref_no}</td>
							<td align="right">{$r.before_tax_price|number_format:2}</td>
							<td align="right">{$r.tax_amount|number_format:2}</td>
							<td align="right">{$r.amt_included_gst|number_format:2}</td>
							<td align="right">{$r.rounding_amt|number_format:2|ifzero:'&nbsp;'}</td>
							<td align="right">{$r.amt_collected|number_format:2}</td>
						</tr>
					{/foreach}
				{/if}
			{/foreach}
			<tr class="header">
				<th align="right" colspan="3">Total</th>
				<th align="right">{$data.total.total.before_tax_price|number_format:2}</th>
				<th align="right">{$data.total.total.tax_amount|number_format:2}</th>
				<th align="right">{$data.total.total.amt_included_gst|number_format:2}</th>
				<th align="right">{$data.total.total.rounding_amt|number_format:2|ifzero:'&nbsp;'}</th>
				<th align="right">{$data.total.total.amt_collected|number_format:2}</th>
			</tr>
		</table>
		
		<br />
		<h2>Summary</h2>
		<table class="report_table" width="100%">
			<tr class="header">
				<th>GST</th>
				<th>Amt</th>
				<th>GST</th>
				<th>Amt Incl. GST</th>
				<th>Total Collected</th>
			</tr>
			{if $data.got_non_gst}
				<tr>
					<td>Non GST</td>
					<td align="right">{$data.total.gst_list.non_gst.before_tax_price|number_format:2}</td>
					<td align="right">{$data.total.gst_list.non_gst.tax_amount|number_format:2}</td>
					<td align="right">{$data.total.gst_list.non_gst.amt_included_gst|number_format:2}</td>
					<td align="right">{$data.total.gst_list.non_gst.amt_included_gst|number_format:2}</td>
				</tr>
			{/if}
			
			{foreach from=$data.gst_list key=gst_key item=r}
				<tr>
					<td>{$r.tax_code}@ {$r.tax_rate|default:0}%</td>
					<td align="right">{$data.total.gst_list.$gst_key.before_tax_price|number_format:2}</td>
					<td align="right">{$data.total.gst_list.$gst_key.tax_amount|number_format:2}</td>
					<td align="right">{$data.total.gst_list.$gst_key.amt_included_gst|number_format:2}</td>
					<td align="right">{$data.total.gst_list.$gst_key.amt_included_gst|number_format:2}</td>
				</tr>
			{/foreach}
			
			{* Rounding *}
			{if $data.total.rounding_amt}
				<tr class="header">
					<th align="right">Rounding</th>
					<th colspan="3">&nbsp;</th>
					<th align="right">{$data.total.rounding_amt|number_format:2|ifzero:'&nbsp;'}</th>
				</tr>
			{/if}
			
			{* Total Collected *}
			<tr class="header">
				<th align="right">Total</th>
				<th align="right">{$data.total.total.before_tax_price|number_format:2}</th>
				<th align="right">{$data.total.total.tax_amount|number_format:2}</th>
				<th align="right">{$data.total.total.amt_included_gst|number_format:2}</th>
				<th align="right">{$data.total.total.amt_collected|number_format:2}</th>
			</tr>
		</table>
	{/if}
{/if}

{include file='footer.tpl'}