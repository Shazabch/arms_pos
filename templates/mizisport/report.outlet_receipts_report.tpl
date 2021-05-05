{*
3/24/2014 3:27 PM Justin
- Modified the wording from "Check" to "Cheque".
*}

{include file="header.tpl"}

{if !$no_header_footer}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.td_cc_db{
	background-color: #fdf;
}

.td_cash_check{
	background-color: #7ff;
}

.td_vc_cp{
	background-color: #fcc;
}
{/literal}
</style>

<script type="text/javascript">

{literal}
var OUTLET_RECEIPTS_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		this.check_allow_group_by_branch();
		
		// initial calendar
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
	// function to check whether allow to show group by branch
	check_allow_group_by_branch: function(){
		if(!this.f['branch_id'])	return false;	// branch id element not found
		
		var span_group_by_branch = $('span_group_by_branch');
		if(!span_group_by_branch)	return false;	// span not found
		
		if(this.f['branch_id'].value){	// choose single branch
			span_group_by_branch.hide();
			this.f['group_by_branch'].checked = false;
		}else{	// all branch 
			span_group_by_branch.show();
		}
	},
	// function when user change branch
	branch_changed: function(){
		this.check_allow_group_by_branch();
	},
	// function to validate form
	check_form: function(){
		return true;	// currently nothing to check
	},
	// function when user click show report
	show_report: function(type){
		this.f['output_excel'].value = '';
		
		if(!this.check_form())	return false;
		
		if(type == 'excel'){
			this.f['output_excel'].value = 1;
		}
		
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
	<form name="f_a" onSubmit="return false;" method="post" class="stdframe">
		<input type="hidden" name="load_report" value="1" />
		<input type="hidden" name="output_excel" value="0" />
		
		{if $BRANCH_CODE eq 'HQ'}
			<p>
				<b>Branch </b>
				<select name="branch_id" onChange="OUTLET_RECEIPTS_REPORT.branch_changed();">
					<option value="">-- All --</option>
					{foreach from=$branches_list key=bid item=b}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
					{/foreach}
				</select>
				
				<span id="span_group_by_branch" style="{if $smarty.request.branch_id}display:none;{/if}">
					<input type="checkbox" name="group_by_branch" value="1" {if $smarty.request.group_by_branch}checked {/if} />
					<b>Group by Branch</b>
					&nbsp;&nbsp;&nbsp;&nbsp;
				</span>
			</p>
		{/if}
		
		<b>Date From</b>
		<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" /> &nbsp;

		<b>To</b>
		<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
		
		<p>
			<input type="button" value='Show Report' onClick="OUTLET_RECEIPTS_REPORT.show_report();" /> &nbsp;&nbsp;
	
			{if $sessioninfo.privilege.EXPORT_EXCEL}
				<button onClick="OUTLET_RECEIPTS_REPORT.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
		</p>
		<ul>
			<li> Report maximum show 1 month of sales.</li>
		</ul>
	</form>
	<script type="text/javascript">OUTLET_RECEIPTS_REPORT.initialize();</script>
	<br />
{/if}

<h3>{$report_title}</h3>

{if $smarty.request.load_report && !$err}
	{if !$data}
	 * No Data *
	{else}
		{if in_array('credit_card', $allowed_payment_type_list)}
			{assign var=got_credit value=1}
		{/if}
		{if in_array('debit', $allowed_payment_type_list)}
			{assign var=got_credit value=1}
		{/if}
		{if in_array('check', $allowed_payment_type_list)}
			{assign var=got_check value=1}
		{/if}
		{if in_array('voucher', $allowed_payment_type_list)}
			{assign var=got_voucher value=1}
		{/if}
		{if in_array('coupon', $allowed_payment_type_list)}
			{assign var=got_coupon value=1}
		{/if}
		
		<table width="100%" class="report_table">
			<tr class="header">
				<th rowspan="2">
					{if $smarty.request.group_by_branch}
						Branch
					{else}
						Date
					{/if}
				</th>
				
				<th rowspan="2">Total Sales</th>
				
				{* Credit / Debit *}
				{if $got_credit}
					<th colspan="{count var=$pos_config.credit_card offset=2}">Credit / Debit</th>
				{/if}
				
				{* Cash / Check *}
				{assign var=cols value=2}
				{if $got_check}
					{assign var=cols value=$cols+1}
				{/if}
				<th colspan="{$cols}">Cash Sales</th>
				
				{* Voucher /Coupon *}
				{assign var=cols value=1}
				{assign var=got_others value=0}
				{if $got_voucher}
					{assign var=cols value=$cols+1}
				{/if}
				{if $got_coupon}
					{assign var=cols value=$cols+1}
				{/if}
				{if $got_voucher || $got_coupon}
					<th colspan="{$cols}">Others</th>
				{/if}
			</tr>
			<tr class="header">
			
				{* Credit / Debit *}
				{if $got_credit}
					{foreach from=$pos_config.credit_card item=cc_type}
						<th>{$cc_type}</th>
					{/foreach}
					<th>Debit</th>
					<th>Total</th>
				{/if}
				
				{* Cash / Check *}
				<th>Cash</th>
				{if $got_check}
					<th>Cheque</th>
				{/if}
				<th>Total</th>
				
				{* Voucher /Coupon *}
				{if $got_voucher}
					<th>Voucher</th>
				{/if}
				{if $got_coupon}
					<th>Coupon</th>
				{/if}
				{if $got_voucher || $got_coupon}
					<th>Total</th>
				{/if}
			</tr>
		
			{if BRANCH_CODE eq 'HQ' && $smarty.request.group_by_branch && !$smarty.request.branch_id}
				{* Group by Branch *}
				{foreach from=$data.by_branch key=bid item=branch_sales}
					<tr>
						<td>{$branches_list.$bid.code}</td>
						<td class="r">{$branch_sales.sales.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						
						{* Credit / Debit *}
						{if $got_credit}
							{foreach from=$pos_config.credit_card item=cc_type}
								{assign var=cc_type value=$cc_type|strtolower}
								<td class="r td_cc_db">{$branch_sales.sales.cc.$cc_type.amt|number_format:2|ifzero:'&nbsp;'}</td>
							{/foreach}
							<td class="r td_cc_db">{$branch_sales.sales.debit.amt|number_format:2|ifzero:'&nbsp;'}</td>
							
							<td class="r td_cc_db">{$branch_sales.sales.cc_db_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						
						{* Cash / Check *}
						<td class="r td_cash_check">{$branch_sales.sales.cash.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{if $got_check}
							<td class="r td_cash_check">{$branch_sales.sales.check.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						<td class="r td_cash_check">{$branch_sales.sales.cash_check_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						
						{* Voucher /Coupon *}
						{if $got_voucher}
							<td class="r td_vc_cp">{$branch_sales.sales.voucher.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						{if $got_coupon}
							<td class="r td_vc_cp">{$branch_sales.sales.coupon.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						{if $got_voucher || $got_coupon}
							<td class="r td_vc_cp">{$branch_sales.sales.vc_cp_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
					</tr>
				{/foreach}
			{else}
				{* By Date *}
				{foreach from=$data.by_date key=date item=date_sales}
					<tr>
						<td>{$date}</td>
						<td class="r">{$date_sales.sales.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						
						{* Credit / Debit *}
						{if $got_credit}
							{foreach from=$pos_config.credit_card item=cc_type}
								{assign var=cc_type value=$cc_type|strtolower}
								<td class="r td_cc_db">{$date_sales.sales.cc.$cc_type.amt|number_format:2|ifzero:'&nbsp;'}</td>
							{/foreach}
							<td class="r td_cc_db">{$date_sales.sales.debit.amt|number_format:2|ifzero:'&nbsp;'}</td>
							
							<td class="r td_cc_db">{$date_sales.sales.cc_db_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						
						{* Cash / Check *}
						<td class="r td_cash_check">{$date_sales.sales.cash.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{if $got_check}
							<td class="r td_cash_check">{$date_sales.sales.check.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						<td class="r td_cash_check">{$date_sales.sales.cash_check_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						
						{* Voucher /Coupon *}
						{if $got_voucher}
							<td class="r td_vc_cp">{$date_sales.sales.voucher.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						{if $got_coupon}
							<td class="r td_vc_cp">{$date_sales.sales.coupon.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
						{if $got_voucher || $got_coupon}
							<td class="r td_vc_cp">{$date_sales.sales.vc_cp_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
						{/if}
					</tr>
				{/foreach}
			{/if}
			
			<tr class="header">
				<th>Total</th>
				<td class="r">{$data.total.sales.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				
				{* Credit / Debit *}
				{if $got_credit}
					{foreach from=$pos_config.credit_card item=cc_type}
						{assign var=cc_type value=$cc_type|strtolower}
						<td class="r td_cc_db">{$data.total.sales.cc.$cc_type.amt|number_format:2|ifzero:'&nbsp;'}</td>
					{/foreach}
					
					<td class="r td_cc_db">{$data.total.sales.debit.amt|number_format:2|ifzero:'&nbsp;'}</td>
							
					<td class="r td_cc_db">{$data.total.sales.cc_db_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				
				{* Cash / Check *}
				<td class="r td_cash_check">{$data.total.sales.cash.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{if $got_check}
					<td class="r td_cash_check">{$data.total.sales.check.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				<td class="r td_cash_check">{$data.total.sales.cash_check_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				
				{* Voucher /Coupon *}
				{if $got_voucher}
					<td class="r td_vc_cp">{$data.total.sales.voucher.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				{if $got_coupon}
					<td class="r td_vc_cp">{$data.total.sales.coupon.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
				{if $got_voucher || $got_coupon}
					<td class="r td_vc_cp">{$data.total.sales.vc_cp_total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				{/if}
			</tr>
		</table>
	{/if}
{/if}

{include file="footer.tpl"}
