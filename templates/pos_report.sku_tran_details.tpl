{*
9/21/2012 3:12 PM Andy
- Add notice for user to know the limitation length of data.

10/24/2012 11:45 AM Andy
- Add "Group by SKU" feature. When group by sku only show qty in/out/balance.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

7/14/2020 10:54 AM William
- Enhanced to added new column Member No, Member Name, IC No, Phone Number.

7/30/2020 5:21 PM William
- Bug fixed SKU Out Qty not correct issue.
*}

{include file="header.tpl"}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.td_in{
	background-color: #aff;
}
.td_bal{
	background-color: #ff9;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var SKU_TRANS_DETAILS_REPORT = {
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
	    
	    reset_sku_autocomplete(true);
	},
	// function to validate form before submit
	check_form: function(){
		// check branch
		if(this.f['branch_id']){
			if(!this.f['branch_id'].value){
				alert('Please select branch.');
				return false;
			}
		}
		
		// check date
		// date from
		var dt1 = this.f['date_from'].value;
		// date to
		var dt2 = this.f['date_to'].value;
		
		var diff = day_diff(dt1, dt2);
		if(diff>90){
			alert('Report maximum show transaction in 90 days only.');
			return false;
		}
		
		// check sku item id
		if(!$('sku_item_id').value){
			alert('Please search and select SKU first.');
			return false;
		}
		
		return true;
	},
	// function when user click show report
	show_report: function(){
		if(!this.check_form())	return false;
		
		this.f.submit();
	}
};

{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

<form name="f_a" class="stdframe noprint" onSubmit="return false;" method="post">
	<input type="hidden" name="load_report" value="1" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch:</b>
		<select name="branch_id">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=bcode}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$bcode}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<b>Date From</b>
	<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size="12" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="checkbox" name="group_by_sku" value="1" {if $smarty.request.group_by_sku}checked {/if} /> <b>Group by SKU</b>
	
	<p>
		{include file="sku_items_autocomplete.tpl" no_add_button=1 sku_parent_form="document.f_a" no_show_varieties=1}
	</p>
	
	<p>
		<input type="button" value="{#SHOW_REPORT#}" onClick="SKU_TRANS_DETAILS_REPORT.show_report();" />
	</p>
	<ul>
		<li> Report maximum show 90 days of transaction.</li>
		<li> Member details will only be available once POS is finalised.</li>
	</ul>
	
</form>

<script type="text/javascript">
	SKU_TRANS_DETAILS_REPORT.initialize();
</script>

{if $smarty.request.load_report && !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
		<h3>{$report_title}</h3>
		
		<ul style="color:red;">
			<li> This report included unfinalise sales.</li>
			{if !$data.si_info.is_up_to_date}
				<li> Please note this item stock is not up to date.</li>
			{/if}
		</ul>
		<table class="report_table" width="100%">
			<tr>
				<td><b>SKU</b></td>
				<td>{$data.si_info.$main_use_sid.sku_item_code}</td>
				<td><b>MCode</b></td>
				<td>{$data.si_info.$main_use_sid.mcode}</td>
				<td><b>Description</b></td>
				<td>{$data.si_info.$main_use_sid.description}</td>
			</tr>
			{if !$smarty.request.group_by_sku}
				<tr>
					<td><b>Cost</b></td>
					<td>{$data.si_info.$main_use_sid.cost|number_format:$config.global_cost_decimal_points}</td>
					<td><b>Selling Price</b></td>
					<td>{$data.si_info.$main_use_sid.selling_price|number_format:2}</td>
					<td><b>AVG Cost</b></td>
					<td>{$data.si_info.$main_use_sid.avg_cost|number_format:$config.global_cost_decimal_points}</td>
				</tr>
			{/if}
		</table>
		
		<br />
		
		<table class="report_table" width="100%">
			<thead>
				{if $smarty.request.group_by_sku}
					<tr class="header">
						<th>Date</th>
						<th>Doc Type</th>
						<th >Doc No.</th>
						<th>Source</th>
						<th>Qty In</th>
						<th>Qty Out</th>
						<th>Balance Qty</th>
					</tr>
				{else}
					<tr class="header">
						<th rowspan="2">Date</th>
						<th rowspan="2">Doc Type</th>
						<th rowspan="2">Doc No.</th>
						<th rowspan="2">Source</th>
						<th colspan="3">In</th>
						<th colspan="3">Out</th>
						<th colspan="4">Balance</th>
						<th colspan="2">AVG</th>
					</tr>
					<tr class="header">
						<th>Qty</th>
						<th>S.Price</th>
						<th>Value</th>
						<th>Qty</th>
						<th>S.Amt</th>
						<th>Value</th>
						<th>Qty</th>
						<th>S.Price</th>
						<th>Unit Cost</th>
						<th>Value</th>
						<th>Cost</th>
						<th>Total AVG Cost</th>
					</tr>
				{/if}
			</thead>			
			
			{foreach from=$data.data item=r}
				<tr bgcolor="{cycle values='#ffffff,#dddddd'}">
					<td>{$r.date}</td>
					<td>{$r.doc_type}</td>
					<td>{$r.doc_no|default:'-'}</td>
					<td>{$r.source_label|default:'-'}
					{if $r.doc_type eq 'POS' && $r.member_no && $r.finalized eq 1}
						</br><span style="color:#00f"><b>Member No :</b>{$r.member_no|default:'-'}</span>
						</br><span style="color:#00f"><b>Member Name :</b>{$r.member_name|default:'-'}</span>
						</br><span style="color:#00f"><b>IC :</b>{$r.nric|default:'-'}</span>
						</br><span style="color:#00f"><b>Phone :</b>{$r.phone|default:'-'}</span>
						</br><span style="color:#00f"><b>Address :</b>{$r.address|default:'-'}</span>
					{/if}
					{if $r.doc_type eq 'POS' && $r.finalized eq 0}
						</br><span style="color:red">Not Finalise</span>
					{/if}
					</td>
					{if $smarty.request.group_by_sku}
						<td class="r td_in">{$r.in.qty|qty_nf}</td>
						<td class="r">{$r.out.qty|abs|qty_nf}</td>	
						<td class="r td_bal">{$r.bal.qty|qty_nf}</td>						
					{else}
						<!-- In -->
						{if $r.in.qty}
							<td class="r td_in">{$r.in.qty|qty_nf}</td>
						
							{if isset($r.in.amt)}
								<td class="r td_in">{$r.in.amt|number_format:2}</td>
							{else}
								{assign var=sp value=$r.in.qty*$r.in.selling_price}
								<td class="r td_in">{$sp|number_format:2}</td>
							{/if}
							
							
							{assign var=cp value=$r.in.qty*$r.in.cost}
							<td class="r td_in">{$cp|number_format:$config.global_cost_decimal_points}</td>
						{else}
							<td class="td_in">&nbsp;</td><td class="td_in">&nbsp;</td><td class="td_in">&nbsp;</td>
						{/if}
						
						<!-- Out -->
						{if $r.out.qty}
							<td class="r">{$r.out.qty|abs|qty_nf}</td>
						
							{if isset($r.out.amt)}
								<td class="r">{$r.out.amt|abs|number_format:2}</td>
							{else}
								{assign var=sp value=$r.out.qty*$r.out.selling_price}
								<td class="r">{$sp|abs|number_format:2}</td>
							{/if}
							
							
							{assign var=cp value=$r.out.qty*$r.out.cost}
							<td class="r">{$cp|abs|number_format:$config.global_cost_decimal_points}</td>
						{else}
							<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
						{/if}
						
						<!-- Balance -->
						<td class="r td_bal">{$r.bal.qty|qty_nf}</td>
						
						{assign var=sp value=$r.bal.qty*$r.bal.selling_price}
						<td class="r td_bal">{$sp|number_format:2}</td>

						<!-- Cost -->
						<td class="r td_bal">{$r.bal.cost|number_format:$config.global_cost_decimal_points}</td>
											
						{assign var=cp value=$r.bal.qty*$r.bal.cost}
						<td class="r td_bal">{$cp|number_format:$config.global_cost_decimal_points}</td>
						
						<!-- AVG -->
						<td class="r">{$r.avg.cost|number_format:$config.global_cost_decimal_points}</td>
						{assign var=cp value=$r.bal.qty*$r.avg.cost}
						<td class="r">{$cp|number_format:$config.global_cost_decimal_points}</td>
					{/if}
				</tr>
			{/foreach}
		</table>
	{/if}
{/if}
{include file="footer.tpl"}
