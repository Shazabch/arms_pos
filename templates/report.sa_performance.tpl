{*
5/16/2012 2:41:34 PM Justin
- Added new note "Report info show in monthly basis".

8/29/2012 6:06 PM Justin
- Enhanced to have sales amount sorting and greater/less than specific amount that assigned by user filters.

5/12/2014 2:27 PM Justin
- Enhanced to have total qty column.

12/20/2017 5:26 PM Justin
- Enhanced the report to show details from sales cache instead of real time data.
- Enhanced the report to show data no longer base on monthly basis but daily.
- Enhanced to have KPI Performance Summary.

3/13/2018 3:37 PM Justin
- Enhanced to show usage message for Sales Amount filter.
- Bug fixed on branch info does not show out when filter with Sales Agent.

12/24/2019 10:52 AM Justin
- Enhanced to show a note indicate when the sales can be seen.

06/29/2020 02:11 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

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
/*.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}*/

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.clr_red {
	color:#FF0000;
}

.clr_blue {
	color:#306EFF;
}
</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var branch_id = "{$smarty.request.branch_id}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var sales_type = "{$smarty.request.sales_type}";
var sa_id = "{$smarty.request.sa_id}";
{literal}
function toggle_date_details(obj, sa_id, bid, year, month){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dtl_"+sa_id+"_"+bid+"_"+year+month);

	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).show();
		}
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).hide();
		}
	}
	
	if(all_tr.length>0)	return false;
	
	var q = $(document.f_a).serialize();
	
	var params = {
		'a': 'ajax_show_date_details',
		sa_id: sa_id,
		bid: bid,
		year: year,
		month: month,
	}
	q += '&'+$H(params).toQueryString();
	
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself, {
		method: 'post',
		parameters: q,
		onComplete: function(e){
			new Insertion.After($("mst_"+sa_id+"_"+bid+"_"+year+month), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}


{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a">
<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
		    <option value="">-- All --</option>
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
		    {/foreach}
		    {if $branch_group.header}
		        <optgroup label="Branch Group">
					{foreach from=$branch_group.header item=r}
					    {capture assign=bgid}bg,{$r.id}{/capture}
						<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
</p>
<p>
	<b>Sales From</b>
	<select name="sales_type">
		<option value="">-- All --</option>
		<option value="open" {if $smarty.request.sales_type eq 'open'}selected{/if}>DO - Cash Sales</option>
		<option value="credit_sales" {if $smarty.request.sales_type eq 'credit_sales'}selected{/if}>DO - Credit Sales</option>
		<option value="pos"{if $smarty.request.sales_type eq 'pos'}selected{/if}>POS</option>
	</select>
	<!--b>Department</b>
	<select name="department_id">
		<option value=0>-- All --</option>
		{foreach from=$departments item=dept}
			<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>SKU Type</b>
	<select name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=t}
			<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
		{/foreach}
	</select-->
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Sales Agent</b>
	<select name="sa_id">
	   <option value="">-- All --</option>
		{foreach from=$sa item=sa}
			<option value="{$sa.id}" {if $smarty.request.sa_id eq $sa.id}selected {/if}>{$sa.code} - {$sa.name}</option>
		{/foreach}
	</select>
</p>
<p>
	<b>Sales Amount by Month [<a href="javascript:void(alert('This filter will not affect the KPI Performance results'));">?</a>]</b>
	<select name="amt_filter_type">
		<option value="" {if !$smarty.request.amt_filter_type}selected{/if}>Please Select</option>
	   <option value="greater" {if $smarty.request.amt_filter_type eq "greater"}selected{/if}>Greater</option>
	   <option value="lower" {if $smarty.request.amt_filter_type eq "lower"}selected{/if}>Lower</option>
	</select>
	<b>Than</b>
	<input type="text" name="amt_filter" value="{$smarty.request.amt_filter}" onchange="mf(this);" size="15" class="r" />
	&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Sales Amount Sort By</b>
	<select name="sort_by">
	   <option value="" {if !$smarty.request.sort_by}selected{/if}>-- None --</option>
	   <option value="highest" {if $smarty.request.sort_by eq "highest"}selected{/if}>Highest</option>
	   <option value="lowest" {if $smarty.request.sort_by eq "lowest"}selected{/if}>Lowest</option>
	</select>
</p>
<p>
* View in maximum 1 year.<br />
* This report requires sales to be finalised and will available for viewing on the next day 9AM.
</b></p>
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err && !$kpi_table}<p align="center">-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th rowspan="2">Date</th>
			<th colspan="2">Sales</th>
			<th rowspan="2">Target Sales <br />Amount</th>
			<th rowspan="2">Balance to <br />Achieve</th>
			<th rowspan="2">Durations to <br />Achieve</th>
			<th rowspan="2">Average Sales <br />need to<br />Achieve / Day</th>
		</tr>
		
		<tr class="header">
			<th>Qty</th>
			<th>Amount</th>
		</tr>
		
		<tbody>
			{foreach from=$table item=bid_list key=sa_id}
				{foreach from=$bid_list item=ym_list key=bid}
					{foreach from=$ym_list item=sa key=ym}
						{if (!$prv_sa_id || ($sa.sa_id ne $prv_sa_id || $sa.branch_code ne $prv_branch_code))}
							<tr>
								<th align="left" colspan="8">{$sa.sa_code} - {$sa.sa_name} {if $BRANCH_CODE eq HQ}({$sa.branch_code}){/if}</th>
							</tr>
						{/if}
						<tr bgcolor="#eeeeee" id="mst_{$sa_id}_{$bid}_{$sa.year}{$sa.month}">
							<td>
								<img src="/ui/expand.gif" width="10" onclick="toggle_date_details(this, '{$sa_id}', '{$bid}', '{$sa.year}', '{$sa.month}');" title="Show Detail" class="clickable">
								{$sa.month|str_month} - {$sa.year}
							</td>
							<td class="r">{$sa.curr_sales_qty|qty_nf}</td>
							<td class="r">{$sa.curr_sales_amt|number_format:2}</td>
							<td align="right">{$sa.target_sales_amt|default:0|number_format:2}</td>
							<td align="center">
								{assign var=achieve_bal_amt value=$sa.target_sales_amt-$sa.curr_sales_amt}
								{if $achieve_bal_amt <= 0}
									Over
								{else}
									<div class="clr_red" align="right">{$achieve_bal_amt|number_format:2}</div>
								{/if}
							</td>
							<td align="right">
								{if $achieve_bal_amt <= 0}
									<div align="center">-</div>
								{elseif $sa.remaining_days < 0}
									0
								{else}
									{$sa.remaining_days|number_format:0}
								{/if}
							</td>
							<td align="right">
								{if $achieve_bal_amt <= 0}
									<div align="center">-</div>
								{elseif $sa.remaining_days < 0}
									{$achieve_bal_amt|number_format:2}
								{else}
									{if $sa.remaining_days eq 0}
										{assign var=avg_ach_bal_amt value=$achieve_bal_amt}
									{else}
										{assign var=avg_ach_bal_amt value=$achieve_bal_amt/$sa.remaining_days}
									{/if}
									{$avg_ach_bal_amt|number_format:2}
								{/if}
							</td>
						</tr>
						{assign var=prv_sa_id value=$sa.sa_id}
						{assign var=prv_branch_code value=$sa.branch_code}
					{/foreach}
				{/foreach}
			{/foreach}
		</tbody>
	</table>
{/if}

{if $kpi_table}
	<br />
	<h2>Sales Agent KPI Performance</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th rowspan="2" width="40%">Sales Agent</th>
			<th rowspan="2">Transaction Count</th>
			<th colspan="2">Sales</th>
			<th colspan="2">Average Per Transaction</th>
		</tr>
		<tr class="header">
			<th>Qty</th>
			<th>Amount</th>
			<th>Qty</th>
			<th>Amount</th>
		</tr>
	{foreach from=$kpi_table item=f key=sa_id}
		<tr>
			<td>{$f.sa_code} - {$f.sa_name}</td>
			<td align="right">{$f.transaction_count}</td>
			<td align="right">{$f.curr_sales_qty|qty_nf}</td>
			<td align="right">{$f.curr_sales_amt|number_format:2}</td>
			<td align="right">
				{assign var=avg_trans value=$f.curr_sales_qty/$f.transaction_count}
				{$avg_trans|number_format:2}
			</td>
			<td align="right">
				{assign var=avg_sales_amt value=$f.curr_sales_amt/$f.transaction_count}
				{$avg_sales_amt|number_format:2}
			</td>
		</tr>
	{/foreach}
	</table>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
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
</script>
{/literal}
{/if}

{include file=footer.tpl}
