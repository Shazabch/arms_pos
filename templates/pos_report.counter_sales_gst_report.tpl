{*
4/14/2015 9:52 AM Andy
- Enhanced to can select all branches.

4/29/2015 4:43 PM Andy
- Enhanced to hide form selection when printing.
- Enhanced to can export excel.
- Enhanced to can view item details.

4/30/2015 12:03 PM Andy
- Check and only show view icon when got sales amt.

03-Mar-2016 15:08 Edwin
- Add features to showing "Rounding" & "Amt After Rounding" into "Counter Sales GST Report"

4/8/2016 2:00 PM Andy
- Fix to hide view item details icon when export.

4/11/2017 1:32 PM Justin
- Enhanced to have "Sales Exclude Goods Return" checkbox.
- Enhanced to show "Goods Return" and "Sales Before Goods Return" columns when "Sales Exclude Goods Return" checkbox is ticked.

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

{/literal}
</style>

<script>

{literal}
var CS_GST_REPORT = {
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
<form name="f_a" class="stdframe noprint" onSubmit="return false;" method="post">
	<input type="hidden" name="load_data" value="1" />
	<input type="hidden" name="is_export" />
	
	<p>			
		{if $BRANCH_CODE eq 'HQ'}
				<b>Branch: </b><select name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches key=bid item=bcode}
						<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$bcode}</option>
					{/foreach}
				</select>&nbsp;&nbsp;&nbsp;&nbsp;			
		{/if}
		<b>From</b> 
		<input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
		&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
	<p>
		<input type="checkbox" name="show_by_tax_code" value="1" {if $smarty.request.show_by_tax_code}checked{/if} /> <b>Show by Tax Code</b>
		<input type="checkbox" name="sales_exclude_gr" value="1" {if $smarty.request.sales_exclude_gr}checked{/if} /> <b>Sales Exclude Goods Return</b>
	</p>
	
	<p>
		<input class="btn btn-primary" type="button" value="{#SHOW_REPORT#}" onClick="CS_GST_REPORT.show_report();" />
		
		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button class="btn btn-primary" onClick="CS_GST_REPORT.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
		{/if}
	</p>
	
	<ul>
		<li> Report maximum show 30 days of transaction.</li>
		<li> This report included unfinalise sales.</li>
	</ul>
</form>
	
<script type="text/javascript">
	CS_GST_REPORT.initialize();
</script>
{/if}

{if $smarty.request.load_data && !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
		<h3>{$report_title}</h3>
		
		<table class="report_table" style="table-layout: fixed" width="100%">
			<tr class="header">
				<th rowspan="2">Date</th>
				{if $data.got_non_gst}
					<th>Non GST</th>
				{/if}
				{foreach from=$data.gst_list key=gst_key item=gst_info}
					<th colspan="2">{$gst_info.tax_indicator} @{$gst_info.tax_rate}%</th>
				{/foreach}
				{assign var=colspan value=5}
				{if $smarty.request.sales_exclude_gr}
					{assign var=colspan value=$colspan+2}					
				{/if}
				<th colspan="{$colspan}">Total</th>
			</tr>
			<tr class="header">
				{if $data.got_non_gst}
					<th>Amt</th>
				{/if}
				{foreach from=$data.gst_list key=gst_key item=gst_info}
					<th>Amt</th>
					<th>GST</th>
				{/foreach}
				<th>Amt</th>
				<th>GST</th>
				<th>Amt Incl. GST</th>
				<th>Rounding</th>
				<th>Amt After Rounding</th>
				{if $smarty.request.sales_exclude_gr}
					<th>Goods Return Amt</th>
					<th>Sales Before<br />Goods Return</th>
				{/if}
			</tr>
			
			{foreach from=$data.date_list item=d}
				<tr bgcolor='{cycle values="#ffffff,#dddddd"}'>
					<td>{$d}</td>
					{if $data.got_non_gst}
						<td align="right" nowrap>
							{if $data.data.$d.gst_list.non_gst.before_tax_price<>0}
								{if !$no_header_footer}
									<span class="noprint">
										<a href="?a=show_items&branch_id={$smarty.request.branch_id}&date={$d}&gst_indicator=non_gst&show_by_tax_code={$smarty.request.show_by_tax_code}"
										target="_blank">
											<img src="/ui/view.png" align="absmiddle" title="View Items" />
										</a>
									</span>
								{/if}
							{/if}
							{$data.data.$d.gst_list.non_gst.before_tax_price|number_format:2}
						</td>
					{/if}
					{foreach from=$data.gst_list key=gst_key item=gst_info}
						<td align="right">
							{if $data.data.$d.gst_list.$gst_key.before_tax_price<>0}
								{if !$no_header_footer}
									<span class="noprint">
										<a href="?a=show_items&branch_id={$smarty.request.branch_id}&date={$d}&gst_indicator={$gst_info.tax_indicator}&show_by_tax_code={$smarty.request.show_by_tax_code}"
										target="_blank">
											<img src="/ui/view.png" align="absmiddle" title="View Items" />
										</a>
									</span>
								{/if}
							{/if}
							{$data.data.$d.gst_list.$gst_key.before_tax_price|number_format:2}
						</td>
						<td align="right">{$data.data.$d.gst_list.$gst_key.tax_amount|number_format:2}</td>
					{/foreach}
					<td align="right">{$data.data.$d.total.before_tax_price|number_format:2}</td>
					<td align="right">{$data.data.$d.total.tax_amount|number_format:2}</td>
					<td align="right">{$data.data.$d.total.amt_included_gst|number_format:2}</td>
					<td align="right">{$data.data.$d.total.rounding|number_format:2}</td>
					<td align="right">{$data.data.$d.total.amt_after_rounding|number_format:2}</td>
					{if $smarty.request.sales_exclude_gr}
						<td align="right">{$data.data.$d.total.goods_return_amt|number_format:2}</td>
						<td align="right">{$data.data.$d.total.amt_after_rounding+$data.data.$d.total.goods_return_amt|number_format:2}</td>
					{/if}
				</tr>
			{/foreach}
			
			<tr class="header">
				<th>Total</th>
				{if $data.got_non_gst}
					<td align="right">{$data.total.gst_list.non_gst.before_tax_price|number_format:2}</td>
				{/if}
				{foreach from=$data.gst_list key=gst_key item=gst_info}
					<td align="right">{$data.total.gst_list.$gst_key.before_tax_price|number_format:2}</td>
					<td align="right">{$data.total.gst_list.$gst_key.tax_amount|number_format:2}</td>
				{/foreach}
				
				<td align="right">{$data.total.total.before_tax_price|number_format:2}</td>
				<td align="right">{$data.total.total.tax_amount|number_format:2}</td>
				<td align="right">{$data.total.total.amt_included_gst|number_format:2}</td>
				<td align="right">{$data.total.total.rounding|number_format:2}</td>
				<td align="right">{$data.total.total.amt_after_rounding|number_format:2}</td>
				{if $smarty.request.sales_exclude_gr}
					<td align="right">{$data.total.total.goods_return_amt|number_format:2}</td>
					<td align="right">{$data.total.total.amt_after_rounding+$data.total.total.goods_return_amt|number_format:2}</td>
				{/if}
			</tr>
		</table>
	{/if}
	
{/if}

{include file='footer.tpl'}