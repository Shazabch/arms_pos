{*
4/26/2013 3:57 PM Justin
- Enhanced to show sales target and actual commission.

7/8/2013 10:46 AM Justin
- Bug fixed on change log do not cover with * and causing smarty error.

5/12/2014 5:13 PM Justin
- Enhanced to have total qty column.

11/6/2019 4:22 PM Justin
- Enhanced to show "Commission by Sales / Qty Range".

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
</style>
{/literal}

<script>
{literal}

function print_statement(comp_code, bid, obj){
	if(obj.src.indexOf('clock')>0) return false;

	document.f_print.a.value='print_statement';
	document.f_print.company_code.value=comp_code;
	document.f_print.branch_id.value=bid;
	document.f_print.target = '_blank';
	document.f_print.submit();
	obj.src = '/ui/print.png';
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
<form method="post" name="f_print">
	<input type="hidden" name="a">
	<input type="hidden" name="company_code">
	<input type="hidden" name="branch_id">
	<input type="hidden" name="date_from" value="{$smarty.request.date_from}">
	<input type="hidden" name="date_to" value="{$smarty.request.date_to}">
	<input type="hidden" name="sales_type" value="{$smarty.request.sales_type}">
	<input type="hidden" name="sa_id" value="{$smarty.request.sa_id}">
</form>
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
* View in maximum 1 year.<br />
* This report requires sales to be finalised and will available for viewing on the next day 9AM.
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

{if !$table && !$range_table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	{assign var=colspan_add value=8}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
		<tr class="header">
			<th width="5%">Date</th>
			<th width="10%">S/A Code</th>
			<th width="10%">S/A Name</th>
			<th width="10%">Sales Amount</th>
			<th width="10%">Sales Qty</th>
			{if !$use_comm_ratio}
				{if $sessioninfo.show_cost}
					<th width="10%">Cost</th>
					{assign var=colspan_add value=$colspan_add+1}
				{/if}
				{if $sessioninfo.show_report_gp}
					<th width="10%">GP</th>
					<th width="10%">GP(%)</th>
					{assign var=colspan_add value=$colspan_add+2}
				{/if}
			{/if}
			<th width="10%">Entitled Commission<br /> Amount</th>
			<th width="10%">Sales Target<br /> Amount</th>
			<th width="10%">Actual Commission<br /> Amount</th>
		</tr>
		<tbody>
			{foreach from=$table item=bid_list key=company_code}
				{foreach from=$bid_list item=ym_list key=bid}
					{foreach from=$ym_list item=sa_list key=ym}
						{foreach from=$sa_list item=sa key=sa_id}
							{if !$prv_company_code || ($sa.branch_code ne $prv_branch_code || $company_code ne $prv_company_code)}
								<tr>
									<th align="left" colspan="{$colspan_add}">
										<img src="/ui/print.png" width="15" onclick="print_statement('{$company_code}', '{$bid}', this);" title="Show Detail" class="clickable">
										{$company_code} {if $sa.company_name}- {$sa.company_name}{/if} {if $BRANCH_CODE eq HQ}({$sa.branch_code}){/if}
									</th>
								</tr>
							{/if}
							<tr bgcolor="#eeeeee">
								<td nowrap>
									{$sa.month|str_month} - {$sa.year}
								</td>
								<td>{$sa.sa_code|default:'&nbsp;'}</td>
								<td>{$sa.sa_name|default:'&nbsp;'}</td>
								<td class="r">{$sa.sales_amt|number_format:2}</td>
								<td class="r">{$sa.sales_qty|qty_nf}</td>
								{if !$use_comm_ratio}
									{if $sessioninfo.show_cost}
										<td class="r">{$sa.cost|number_format:$config.global_cost_decimal_points}</td>
									{/if}
									{if $sessioninfo.show_report_gp}
										<td class="r">
											{assign var=gp value=$sa.sales_amt-$sa.cost}
											{$gp|number_format:2}
										</td>
										<td class="r">
											{if $sa.sales_amt}
												{assign var=gp_per value=$gp/$sa.sales_amt*100}
											{else}
												{assign var=gp_per value=0}
											{/if}
											{$gp_per|number_format:2}
										</td>
									{/if}
								{/if}
								<td align="right">{$sa.commission_amt|number_format:2}</td>
								<td align="right">{$sa.sales_target|default:0|number_format:2}</td>
								<td align="right">
									{if $sa.sales_amt < $sa.sales_target}
										0.00
									{else}
										{$sa.commission_amt|number_format:2}
									{/if}
								</td>
							</tr>
							{assign var=prv_company_code value=$company_code}
							{assign var=prv_branch_code value=$sa.branch_code}
						{/foreach}
					{/foreach}
				{/foreach}
			{/foreach}
		</tbody>
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
