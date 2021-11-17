{*
4/15/2010 3:57:13 PM Andy
- Status column changes, F = Finalized, NF = Not yet Finalize, ND = No Data
- Fix negative zero problems

4/19/2010 12:35:51 PM Andy
- Add column to show finalize time

5/20/2010 1:37:36 PM Andy
- Change weekend color.

5/3/2012 3:27:55 PM Andy
- Add "Top Up", "Over", "Receipt Discount", "Mix & Match Discount", "Cash Advance" and "Trade In Write-off"

8/29/2012 5:17 PM Andy
- Fix export not working bug.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

11/27/2014 5:11 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.
- Added Variance 2 to compare with nett sales 2.

6/28/2016 1:11 PM Andy
- Rename Top Up to Cash In.

6/28/2018 5:50 PM Justin
- Enhanced to load foreign currency list base on sales and config.

12/4/2018 4:58 PM Justin
- Bug fixed on Currency Adjust column always show out even though not using foreign currency.

06/30/2020 04:43 PM Sheila
- Updated button css.

10/13/2020 3:57 PM William
- Change GST word to use Tax.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}
<style>
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	background:#ffc;
}
</style>
{/literal}

<script>
{literal}
function view_type_changed(){
	var view_type = getRadioValue(document.f_a['view_type']);
	if(view_type=='month')    $('span_month').hide();
	else    $('span_month').show();
}

function submit_form(type){
	document.f_a['show_type'].value = '';
	if(type)	document.f_a['show_type'].value = type;
	
	document.f_a.submit();
}
{/literal}
</script>
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a" onSubmit="return false;">
			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="show_type" />
			
		<div class="row">
			{if $BRANCH_CODE eq 'HQ'}
		<div class="col">
			<b class="form-label">Branch</b>
		<select class="form-control" name="branch_id">
			 {foreach from=$branches key=bid item=b}
				{if !$branches_group.have_group.$bid}
					<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/if}
			{/foreach}
			{foreach from=$branches_group.header key=bgid item=bg}
				<optgroup label="{$bg.code}">
					{foreach from=$branches_group.items.$bgid key=bid item=b}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
					{/foreach}
				</optgroup>
			{/foreach}
		</select>
		</div>
		{/if}
		
		<div class="col">
			<b class="form-label">Year</b>
		<select class="form-control" name="year">
			{foreach from=$years item=r}
				<option {if $smarty.request.year eq $r.year}selected {/if} value="{$r.year}">{$r.year}</option>
			{/foreach}
		</select>
		</div>
		
		<div class="col">
			<span id="span_month">
				<b class="form-label">Month</b>
				<select class="form-control" name="month">
					{foreach from=$months key=m item=month}
						<option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
					{/foreach}
				</select>
				</span>
		</div>
		
		<div class="col">
			<div class="form-label mt-4">
				<b>View By</b>
			<input type="radio" name="view_type" value="day" {if !$smarty.request.view_type or $smarty.request.view_type eq 'day'}checked {/if} onChange="view_type_changed();" /> Day
			<input type="radio" name="view_type" value="month" {if $smarty.request.view_type eq 'month'}checked {/if} onChange="view_type_changed();" /> Month
			</div>
		</div>
		</div>
		<p>
		
		<button class="btn btn-primary mt-2" class="btn-primary" onClick="submit_form();">{#SHOW_REPORT#}</button>
		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-info mt-2" class="btn-primary" onClick="submit_form('excel');">{#OUTPUT_EXCEL#}</button>
		{/if}
		</p>
		</form>
	</div>
</div>

<script>view_type_changed();</script>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<p>
<div class="card mx-3">
	<div class="card-body">
		F = Finalised &nbsp;&nbsp;&nbsp;
NF = Not Yet Finalise &nbsp;&nbsp;&nbsp;
ND = No Data
<br />
Nett Sales<sup>2</sup>= Nett Sales excluded charges, taxes and rounding.
	</div>
</div>
</p>

{assign var=total_f value=0}
{assign var=total_nf value=0}
{assign var=total_nd value=0}
{assign var=fc_count value=0}
{assign var=sub_rowspan value=0}

{if $got_foreign_currency}
	{assign var=fc_count value=$foreign_currency_list|@count}
	{assign var=rowspan value=3}
	{assign var=sub_rowspan value=2}
{else}
	{assign var=rowspan value=2}
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%">
				<thead class="bg-gray-100">
					<tr class="header">
						<th rowspan="{$rowspan}">Date</th>
						<th colspan="2">Items</th>
						{assign var=cols value=10}
						{if $smarty.request.view_type eq 'day'}{assign var=cols value=$cols+1}{/if}
						{if $got_mm_discount}{assign var=cols value=$cols+1}{/if}
						{if $got_top_up}{assign var=cols value=$cols+1}{/if}
						{if $got_trade_in_writeoff}{assign var=cols value=$cols+1}{/if}
						{if $got_service_charge}{assign var=cols value=$cols+1}{/if}
						{if $got_gst}{assign var=cols value=$cols+1}{/if}
						{if $got_foreign_currency}{assign var=cols value=$cols+1}{/if}
						{assign var=cols value=$cols+$fc_count}
						
						<th colspan="{$cols}">Counter Collection</th>
						<th rowspan="{$rowspan}">Total Category Sales</th>
						<th rowspan="{$rowspan}">Variances<br>(Compare to gross sales)</th>
						<th rowspan="{$rowspan}">Variances<sup>2</sup><br>(Compare to nett sales<sup>2</sup>)</th>
					</tr>
					<tr class="header">
						<th rowspan="{$sub_rowspan}">Item Amt</th>
						<th rowspan="{$sub_rowspan}">Item Discount</th>
						<th rowspan="{$sub_rowspan}">Gross Sales</th>
						<th rowspan="{$sub_rowspan}">Rounding</th>
						{if $got_foreign_currency}
							<th rowspan="{$sub_rowspan}">Currency Adjust</th>
						{/if}
						<th rowspan="{$sub_rowspan}">Over</th>
						<th rowspan="{$sub_rowspan}">Receipt Discount</th>
						{if $got_mm_discount}
							<th rowspan="{$sub_rowspan}">Mix & Match Discount</th>
						{/if}
						{if $got_service_charge}
							<th rowspan="{$sub_rowspan}">Service Charge</th>
						{/if}
						{if $got_gst}
							<th rowspan="{$sub_rowspan}">Tax</th>
						{/if}
						<th rowspan="{$sub_rowspan}">Nett Sales</th>
						<th rowspan="{$sub_rowspan}">Nett Sales<sup>2</sup></th>
						<th rowspan="{$sub_rowspan}">Cash Advance</th>
						{if $got_top_up}
							<th rowspan="{$sub_rowspan}">Cash In</th>
						{/if}
						<th {if $got_foreign_currency}colspan="{$fc_count+1}"{/if}>Collection</th>
						<th rowspan="{$sub_rowspan}">
							Variances
							{if !$got_foreign_currency}
								<br />(Collection compare to Nett Sales)
							{/if}
						</th>
						{if $got_trade_in_writeoff}
							<th rowspan="{$sub_rowspan}">Trade In Write-Off</th>
						{/if}
						<th rowspan="{$sub_rowspan}">Status</th>
						{if $smarty.request.view_type eq 'day'}<th rowspan="{$sub_rowspan}">Finalise Time</th>{/if}
					</tr>
					
					{if $got_foreign_currency}
						<tr class="header">
							<th>{$config.arms_currency.symbol}</th>
							{foreach from=$foreign_currency_list key=curr_code item=fc_rate}
								<th>{$curr_code}</th>
							{/foreach}
						</tr>
					{/if}
				</thead>
				{foreach from=$date_label key=date_key item=d}
					<tr class="{if $d.day==='0' or $d.day eq 6}weekend{/if}">
						<td>{$d.date}</td>
						<td class="r {if $table.$date_key.item_amt<0}negative{/if}">{$table.$date_key.item_amt|number_format:2}</td>
						<td class="r {if $table.$date_key.item_discount<0}negative{/if}">{$table.$date_key.item_discount|number_format:2}</td>
						
						<td class="r {if $table.$date_key.cc_sales<0}negative{/if}">{$table.$date_key.cc_sales|number_format:2}</td>
						<td class="r {if $table.$date_key.rounding<0}negative{/if}">{$table.$date_key.rounding|number_format:2}</td>
						{if $got_foreign_currency}
							<td class="r {if $table.$date_key.currency_adjust<0}negative{/if}">{$table.$date_key.currency_adjust|number_format:2}</td>
						{/if}
						<td class="r {if $table.$date_key.over<0}negative{/if}">{$table.$date_key.over|number_format:2}</td>
						<td class="r {if $table.$date_key.discount<0}negative{/if}">{$table.$date_key.discount|number_format:2}</td>
						{if $got_mm_discount}
							<td class="r {if $table.$date_key.mix_match_discount<0}negative{/if}">{$table.$date_key.mix_match_discount|number_format:2}</td>
						{/if}
						{if $got_service_charge}
							<td class="r {if $table.$date_key.service_charges<0}negative{/if}">{$table.$date_key.service_charges|number_format:2}</td>
						{/if}
						{if $got_gst}
							<td class="r {if $table.$date_key.total_gst_amt<0}negative{/if}">{$table.$date_key.total_gst_amt|number_format:2}</td>
						{/if}
						<td class="r {if $table.$date_key.cc_actual_sales<0}negative{/if}">{$table.$date_key.cc_actual_sales|number_format:2}</td>
						
						{* Nett Sales 2*}
						<td class="r {if $table.$date_key.nett_sales2<0}negative{/if}">{$table.$date_key.nett_sales2|number_format:2}</td>
						
						<td class="r {if $table.$date_key.cash_advance<0}negative{/if}">{$table.$date_key.cash_advance|number_format:2}</td>
						{if $got_top_up}
							<td class="r {if $table.$date_key.top_up<0}negative{/if}">{$table.$date_key.top_up|number_format:2}</td>
						{/if}
						<td class="r {if $table.$date_key.collection<0}negative{/if}">{$table.$date_key.collection|number_format:2}</td>
						{if $got_foreign_currency}
							{foreach from=$foreign_currency_list key=curr_code item=fc_rate}
								<td class="r {if $table.$date_key.fc_collection.$curr_code<0}negative{/if}">{$table.$date_key.fc_collection.$curr_code|number_format:2}</td>
							{/foreach}
						{/if}
						<td class="r {if $table.$date_key.cc_variances|round2>0}positive{elseif $table.$date_key.cc_variances|round2<0}negative{/if}">{$table.$date_key.cc_variances|number_format:2|ifzero:'0.00'}</td>
						{if $got_trade_in_writeoff}
							<td class="r {if $table.$date_key.writeoff_amt<0}negative{/if}">{$table.$date_key.writeoff_amt|number_format:2}</td>
						{/if}
						<td align="center">
							{if $smarty.request.view_type eq 'month'}
								{if !$table.$date_key.status.f and !$table.$date_key.status.nf and !$table.$date_key.status.nd}-
								{else}
									{if $table.$date_key.status.f}
										<span class="positive">{$table.$date_key.status.f|number_format}F</span>
										{assign var=total_f value=$total_f+$table.$date_key.status.f}
										{if $table.$date_key.status.nf or $table.$date_key.status.nd}/{/if}
									{/if}
									{if $table.$date_key.status.nf}
										<span class="negative">{$table.$date_key.status.nf|number_format}NF</span>
										{assign var=total_nf value=$total_nf+$table.$date_key.status.nf}
										{if $table.$date_key.status.nd}/{/if}
									{/if}
									{if $table.$date_key.status.nd}
										{$table.$date_key.status.nd|number_format}ND
										{assign var=total_nd value=$total_nd+$table.$date_key.status.nd}
									{/if}
								{/if}
							{else}
								{if $table.$date_key.status}
									{if $table.$date_key.status eq 'F'}
										<span class="positive">{$table.$date_key.status}</span>
										{assign var=total_f value=$total_f+1}
									{else}
										<span class="negative">{$table.$date_key.status}</span>
										{assign var=total_nf value=$total_nf+1}
									{/if}
								{else}-{assign var=total_nd value=$total_nd+1}
								{/if}
							{/if}
						</td>
						{if $smarty.request.view_type eq 'day'}
							<td align="center">{$table.$date_key.finalize_time|ifzero:''|default:'-'}</td>
						{/if}
						<td class="r">{$table.$date_key.sales_cache|number_format:2}</td>
						<td class="r {if $table.$date_key.variances|round2>0}positive{elseif $table.$date_key.variances|round2<0}negative{/if}">{$table.$date_key.variances|number_format:2|ifzero:'0.00'}</td>
						
						{* Variances 2 *}
						<td class="r {if $table.$date_key.variances2|round2>0}positive{elseif $table.$date_key.variances2|round2<0}negative{/if}">{$table.$date_key.variances2|number_format:2|ifzero:'0.00'}</td>
					</tr>
				{/foreach}
				<tr class="header">
					<th class="r">Total</th>
					<th class="r {if $total.item_amt<0}negative{/if}">{$total.item_amt|number_format:2}</th>
					<th class="r {if $total.item_discount<0}negative{/if}">{$total.item_discount|number_format:2}</th>
					
					<th class="r {if $total.cc_sales<0}negative{/if}">{$total.cc_sales|number_format:2}</th>
					<th class="r {if $total.rounding<0}negative{/if}">{$total.rounding|number_format:2}</th>
					{if $got_foreign_currency}
						<th class="r {if $total.currency_adjust<0}negative{/if}">{$total.currency_adjust|number_format:2}</th>
					{/if}
					<th class="r {if $total.over<0}negative{/if}">{$total.over|number_format:2}</th>
					<th class="r {if $total.discount<0}negative{/if}">{$total.discount|number_format:2}</th>
					{if $got_mm_discount}
						<th class="r {if $total.mix_match_discount<0}negative{/if}">{$total.mix_match_discount|number_format:2}</th>
					{/if}
					{if $got_service_charge}
						<th class="r {if $total.service_charges<0}negative{/if}">{$total.service_charges|number_format:2}</th>
					{/if}
					{if $got_gst}
						<th class="r {if $total.total_gst_amt<0}negative{/if}">{$total.total_gst_amt|number_format:2}</th>
					{/if}
					
					<th class="r {if $total.cc_actual_sales<0}negative{/if}">{$total.cc_actual_sales|number_format:2}</th>
					
					{* Nett Sales 2 *}
					<th class="r {if $total.nett_sales2<0}negative{/if}">{$total.nett_sales2|number_format:2}</th>
					
					<th class="r {if $total.cash_advance<0}negative{/if}">{$total.cash_advance|number_format:2}</th>
					{if $got_top_up}
						<th class="r {if $total.top_up<0}negative{/if}">{$total.top_up|number_format:2}</th>
					{/if}
					<th class="r {if $total.collection<0}negative{/if}">{$total.collection|number_format:2}</th>
					{if $got_foreign_currency}
						{foreach from=$foreign_currency_list key=curr_code item=fc_rate}
							<th class="r {if $total.fc_collection.$curr_code<0}negative{/if}">{$total.fc_collection.$curr_code|number_format:2}</th>
						{/foreach}
					{/if}
					<th class="r {if $total.cc_variances>0}positive{elseif $total.cc_variances<0}negative{/if}">{$total.cc_variances|number_format:2}</th>
					{if $got_trade_in_writeoff}
						<th class="r {if $total.writeoff_amt<0}negative{/if}">{$total.writeoff_amt|number_format:2}</th>
					{/if}
					<th>
						{if $total_f}
							<span class="positive">{$total_f|number_format}F</span>
							{if $total_nf or $total_nd}/{/if}
						{/if}
						{if $total_nf}
							<span class="negative">{$total_nf|number_format}NF</span>
							{if $total_nd}/{/if}
						{/if}
						{if $total_nd}
							{$total_nd}ND
						{/if}
					</th>
					{if $smarty.request.view_type eq 'day'}<th>-</th>{/if}
			
					<th class="r">{$total.sales_cache|number_format:2}</th>
					<th class="r {if $total.variances|round2>0}positive{elseif $total.variances|round2<0}negative{/if}">{$total.variances|number_format:2|ifzero:'0.00'}</th>
					
					{* Variances 2 *}
					<th class="r {if $total.variances2|round2>0}positive{elseif $total.variances2|round2<0}negative{/if}">{$total.variances2|number_format:2|ifzero:'0.00'}</th>
				</tr>
			</table>
		</div>
	</div>
</div>
{/if}
{include file=footer.tpl}
