{*
9/23/2011 10:55:45 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

12/11/2014 3:40 PM Justin
- Enhanced to have GST information.

4/18/2017 2:35 PM Justin
- Enhanced to show guideline message while click on "[?]" from Sales Trend > AVG column.

12/11/2017 11:07 AM Andy
- Separate item into different row when have multiple PO.
- Show PO Date for each PO.

4/23/2018 5:00 PM Andy
- Added Foreign Currency feature.

06/24/2020 02:49 PM Sheila
- Updated button css
*}
{include file=header.tpl}
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
span.span_po_date{
	color: blue;
}
</style>
{/literal}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
<div class="alert alert-danger rounded mx-3">
<b>The following error(s) has occured:</b>
<ul class=err style="list-style-type: none;">
{foreach from=$err item=e}
<li> {$e} </li></div>
{/foreach}
</ul>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form method=post class=form name="f_a">
			<input type="hidden" name="a" value="showForm">
			
			<div class="row">
				<div class="col-md-6">
					<b class="form-label">Date From</b>
					<div class="form-inline">
						<input class="form-control" size=22 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
					</div>
				</div>
			
			<div class="col-md-6">
				<b class="form-label">To</b>
			<div class="form-inline">
				<input class="form-control" size=22 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			</div>
			
			
			<p>
				<b class="form-label">Department</b> {dropdown name=dept_id values=$departments selected=$smarty.request.dept_id key=id value=description}&nbsp;&nbsp;&nbsp;&nbsp;
				<b class="form-label">Vendor</b> {dropdown name=vendor_id all="-- All --" values=$vendors selected=$smarty.request.vendor_id key=id value=description}
			</p>
			<p>
			{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> {dropdown name=branch_id all="-- All --" values=$branches selected=$smarty.request.branch_id key=id value=code}
			{/if}
		<div class="form-inline">
			<b class="form-label">Quantity Purchase<span class="text-danger" title="Required Field"> *</span> >=</b>
			<input class="form-control" type="text" size="1" name="qty_per" value="{$smarty.request.qty_per|default:'0'}"> %
		</div>
			</p>
			<input class="btn btn-primary" type="submit" name="subm" value="Show">
			</form>
	</div>
</div>

{if !$data}
{if $smarty.request.subm && !$err}<p align=center>-- No data --</p>{/if}
{else}

{if $config.foreign_currency}* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width=100% class="report_table">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="2">#</th>
						<th rowspan="2" nowrap>Arms Code</th>
						<th rowspan="2" nowrap>SKU Description</th>
						<th rowspan="2" nowrap>Qty<br>(Pcs)</th>
						<th rowspan="2" nowrap>FOC<br>(Pcs)</th>
						<th colspan="5" nowrap>Sales Trend</th>
						<th rowspan="2">Nett<br>Amount</th>
						<th rowspan="2">Total<br>Selling</th>
						<th rowspan="2">Gross<br>Profit</th>
						<th rowspan="2">Profit(%)</th>
					</tr>
					<tr class="header">
						<th>Avg [<a href="javascript:void(0)" onclick="alert('{$LANG.SALES_TREND_AVG_INFO|escape:javascript}')">?</a>]</th>
						<th>1M</th>
						<th>3M</th>
						<th>6M</th>
						<th>12M</th>
					</tr>
				</thead>
				{foreach from=$data item=r name=fitem}
					{assign var=sid value=$r.sid}
				<tbody class="fs-08">
					<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
						<td>{$smarty.foreach.fitem.iteration}.</td>
						<td>{$si_info.$sid.sku_item_code}</td>
						<td>{$si_info.$sid.description}<br />
							<font class="small">
								<a href="po.php?a=view&id={$r.po_id}&branch_id={$r.branch_id}&highlight_item_id={$sid}" target="_blank">{$r.po_no}</a>
								<span class="span_po_date">({$r.po_date})</span>
							</font>
						</td>
						<td class="r">{$r.purchase|qty_nf|ifzero:'-'}</td>
						<td class="r">{$r.foc_purchase|qty_nf|ifzero:'-'}</td>
						<td class="r">{$r.sales_avg|qty_nf|ifzero:'-'}</td>
						<td class="r">
							{$r.sales_trend.qty.1|qty_nf:".":""|ifzero}<br />
							{$r.sales_trend.qty.1|qty_nf:".":""|ifzero}
						</td>
						<td class="r">
							{$r.sales_trend.qty.3|qty_nf:".":""|ifzero}<br />
							{$r.sales_trend.qty.3/3|qty_nf:".":""|ifzero}
						</td>
						<td class="r">
							{$r.sales_trend.qty.6|qty_nf:".":""|ifzero}<br />
							{$r.sales_trend.qty.6/6|qty_nf:".":""|ifzero}
						</td>
						<td class="r">
							{$r.sales_trend.qty.12|qty_nf:".":""|ifzero}<br />
							{$r.sales_trend.qty.12/12|qty_nf:".":""|ifzero}
						</td>
						<td class="r">
							{if $r.currency_code and $r.nett_amt}
								{$r.currency_code} {$r.nett_amt|number_format:2}<br />
								<span class="converted_base_amt">{$config.arms_currency.code} {$r.base_item_nett_amt|number_format:2}*</span>
							{else}
								{$r.nett_amt|number_format:2|ifzero:'-'}
							{/if}
						</td>
						<td class="r">{$r.total_sales|number_format:2|ifzero:'-'}</td>
						<td class="r {if $r.currency_code}converted_base_amt{/if}">{$r.gross_profit|number_format:2|ifzero:'-'}{if $r.currency_code}*{/if}</td>
						<td class="r">{$r.gross_profit_per|number_format:2|ifzero:'-':'%'}</td>
					</tr>	
				</tbody>
				{/foreach}
				</table>
		</div>
	</div>
</div>
{/if}

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
{include file=footer.tpl}
